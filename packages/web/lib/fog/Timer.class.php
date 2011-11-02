<?php

class Timer
{
	const TASK_SINGLE_FLEXTIME = 180;

	private $debug;
	private $blSingle;
	private $strMin, $strHour, $strDOM, $strMonth, $strDOW;
	private $lngSingle;

	function __construct( $minute, $hour=null, $dom=null, $month=null, $dow=null )
	{
		if ( $minute != null && $hour == null && $dom == null && $month == null && $dow == null )
		{
			// Single task based on timestamp
			$this->lngSingle = $minute;
			$this->debug = false;
			$this->blSingle = true;
		}
		else
		{
			$this->strMin = $minute;
			$this->strHour = $hour;
			$this->strDOM = $dom;
			$this->strMonth = $month;
			$this->strDOW = $dow;
			$this->debug = false;
			$this->blSingle = false;
		}
	}
	
	function isSingleRun() { return $this->blSingle; }
	
	function getSingleRunTime()
	{
		return $this->lngSingle;
	}
	
	function toString()
	{
		if ( $this->blSingle )
			return date( "r",  $this->lngSingle );
		else
			return $this->strMin . " " . $this->strHour . " " . $this->strDOM . " " . $this->strMonth . " " . $this->strDOW;
	}
	
	public function setDebug( $blDebug )
	{
		$this->debug = $blDebug;
	}
	
	private function shouldSingleRun()
	{
		if ( $this->blSingle && $this->lngSingle != null )
		{
			//if ( time() < ($this->lngSingle +  self::TASK_SINGLE_FLEXTIME) )
			//	echo "\nPasses A\n";
		
			//echo time() ." < " . ($this->lngSingle +  self::TASK_SINGLE_FLEXTIME);
			
			//if ( (time() >= $this->lngSingle ) )
			//	echo "\n\nPasses B\n";
				
			//echo 	 "\n" . (time() . " >= ".  $this->lngSingle );
				
			//return false;
			return ( time() < ($this->lngSingle +  self::TASK_SINGLE_FLEXTIME) && (time() >= $this->lngSingle )  );
		}
		return false;
	}
	
	public function shouldRunNow()
	{
		if ( $this->blSingle )
		{
			return $this->shouldSingleRun();
		}
		else
		{ 
			if ( $this->passesMinute() )
			{
				$this->d( "passed minute" );
				if ( $this->passesHour() )
				{
					$this->d( "passed hour" );
					if ( $this->passesDOM() )
					{
						$this->d( "passed DOM" );
						if ( $this->passesMonth() )
						{
							$this->d( "passed Month" );
							if ( $this->passesDOW() )
							{
								$this->d( "passed DOW" );
								$this->d( "task should run." );
								return true;
							}
							else
							{
								$this->d( "Failed DOW" );
							}
						}
						else
						{
							$this->d( "Failed Month" );
						}
					}
					else
					{
						$this->d( "Failed DOM" );
					}
				}
				else
				{
					$this->d( "Failed hour" );
				}
			}
			else
			{
				$this->d( "Failed minute" );
			}
		}
		return false;
	}
	
	private function splitOnCommas( $s )
	{
		return explode(  ",", $s);
	}
	
	private function splitOnDash( $s )
	{
		return explode( "-", $s );
	}
	
	private function splitOnSlash( $s )
	{
		return explode( "/", $s );
	}
	
	private function containsDash( $s )
	{
		return strpos( $s  ,  "-" ) !== false;
	}
	
	private function containsSlash( $s )
	{
		return strpos( $s, "/" ) !== false;
	}
	
	private function passesMinute()
	{
		$strCurMin = date( "i" );
	
		if ( trim ( $this->strMin ) == "*" ) return true;
		
		$arValues = array();
		$arCommas = $this->splitOnCommas( $this->strMin );
		
		for ( $i = 0; $i < count($arCommas); $i++ )
		{
			$curPiece = trim($arCommas[$i]);
			
			if ( $this->containsDash( $curPiece ) )
			{
				$arDashes = $this->splitOnDash( $curPiece );
				if ( count($arDashes) == 2 )
				{
					if ( is_numeric( trim($arDashes[0]) ) && is_numeric( trim($arDashes[1]) ) )
					{
						for( $t = trim($arDashes[0]); $t <= trim($arDashes[1]); $t++ )
						{
							$arValues[] = $t;
						}
					}
				}
			}
			else if  ( $this->containsSlash( $curPiece ) )
			{
				$arSlash = $this->splitOnSlash( $curPiece );
				if ( count( $arSlash ) == 2 )
				{
					if ( trim( $arSlash[0] ) == "*" && is_numeric( trim( $arSlash[1] ) ) )
					{
						$divisor = trim( $arSlash[1] );
						// 00 - 59
						for ( $i = 0; $i < 60; $i++ )
						{
							if ( $i % $divisor == 0 )
								$arValues[] = $i;
						}
					}
				}
			}
			else
			{
				if ( is_numeric( $curPiece ) )
				{
					$arValues[] = $curPiece;
				}
			}
		}
		
		for( $i = 0; $i < count( $arValues ); $i++ )
		{
			if ( trim($strCurMin) == $arValues[$i] ) return true;
		}
		
		if ( $this->debug )
			print_r( $arValues );
			
		return false;
	}
	
	private function passesHour()
	{
		$strCurHour = date( "H" );
		
		if ( trim( $this->strHour) == "*" ) return true;
		
		$arValues = array();
		$arCommas = $this->splitOnCommas( $this->strHour );
		
		for ( $i = 0; $i < count($arCommas); $i++ )
		{
			$curPiece = trim($arCommas[$i]);
			
			if ( $this->containsDash( $curPiece ) )
			{
				$arDashes = $this->splitOnDash( $curPiece );
				if ( count($arDashes) == 2 )
				{
					if ( is_numeric( trim($arDashes[0]) ) && is_numeric( trim($arDashes[1]) ) )
					{
						for( $t = trim($arDashes[0]); $t <= trim($arDashes[1]); $t++ )
						{
							$arValues[] = $t;
						}
					}
				}
			}
			else if  ( $this->containsSlash( $curPiece ) )
			{
				$arSlash = $this->splitOnSlash( $curPiece );
				if ( count( $arSlash ) == 2 )
				{
					if ( trim( $arSlash[0] ) == "*" && is_numeric( trim( $arSlash[1] ) ) )
					{
						$divisor = trim( $arSlash[1] );
						// 00 - 59
						for ( $i = 0; $i < 23; $i++ )
						{
							if ( $i % $divisor == 0 )
								$arValues[] = $i;
						}
					}
				}
			}
			else
			{
				if ( is_numeric( $curPiece ) )
				{
					$arValues[] = $curPiece;
				}
			}			
		}
		
		for( $i = 0; $i < count( $arValues ); $i++ )
		{
			if ( trim($strCurHour) == $arValues[$i] ) return true;
		}
		
		if ( $this->debug )
			print_r( $arValues );
			
		return false;		
	}
	
	private function passesDOM()
	{
		$strCurDOM = date( "j" );
		
		if ( trim( $this->strDOM) == "*" ) return true;
		
		$arValues = array();
		$arCommas = $this->splitOnCommas( $this->strDOM );
		
		for ( $i = 0; $i < count($arCommas); $i++ )
		{
			$curPiece = trim($arCommas[$i]);
			
			if ( $this->containsDash( $curPiece ) )
			{
				$arDashes = $this->splitOnDash( $curPiece );
				if ( count($arDashes) == 2 )
				{
					if ( is_numeric( trim($arDashes[0]) ) && is_numeric( trim($arDashes[1]) ) )
					{
						for( $t = trim($arDashes[0]); $t <= trim($arDashes[1]); $t++ )
						{
							$arValues[] = $t;
						}
					}
				}
			}
			else if  ( $this->containsSlash( $curPiece ) )
			{
				$arSlash = $this->splitOnSlash( $curPiece );
				if ( count( $arSlash ) == 2 )
				{
					if ( trim( $arSlash[0] ) == "*" && is_numeric( trim( $arSlash[1] ) ) )
					{
						$divisor = trim( $arSlash[1] );
						// 00 - 59
						for ( $i = 0; $i < 32; $i++ )
						{
							if ( $i % $divisor == 0 )
								$arValues[] = $i;
						}
					}
				}
			}
			else
			{
				if ( is_numeric( $curPiece ) )
				{
					$arValues[] = $curPiece;
				}
			}			
		}
		
		for( $i = 0; $i < count( $arValues ); $i++ )
		{
			if ( trim($strCurDOM) == $arValues[$i] ) return true;
		}
		
		if ( $this->debug )
			print_r( $arValues );
			
		return false;				
	}
	
	private function passesMonth()
	{
		$strCurMon = date( "n" );
		
		if ( trim( $this->strMonth) == "*" ) return true;
		
		$arValues = array();
		$arCommas = $this->splitOnCommas( $this->strMonth );
		
		for ( $i = 0; $i < count($arCommas); $i++ )
		{
			$curPiece = trim($arCommas[$i]);
			
			if ( $this->containsDash( $curPiece ) )
			{
				$arDashes = $this->splitOnDash( $curPiece );
				if ( count($arDashes) == 2 )
				{
					if ( is_numeric( trim($arDashes[0]) ) && is_numeric( trim($arDashes[1]) ) )
					{
						for( $t = trim($arDashes[0]); $t <= trim($arDashes[1]); $t++ )
						{
							$arValues[] = $t;
						}
					}
				}
			}
			else if  ( $this->containsSlash( $curPiece ) )
			{
				$arSlash = $this->splitOnSlash( $curPiece );
				if ( count( $arSlash ) == 2 )
				{
					if ( trim( $arSlash[0] ) == "*" && is_numeric( trim( $arSlash[1] ) ) )
					{
						$divisor = trim( $arSlash[1] );
						// 00 - 59
						for ( $i = 0; $i < 12; $i++ )
						{
							if ( $i % $divisor == 0 )
								$arValues[] = $i;
						}
					}
				}
			}
			else
			{
				if ( is_numeric( $curPiece ) )
				{
					$arValues[] = $curPiece;
				}
			}			
		}
		
		for( $i = 0; $i < count( $arValues ); $i++ )
		{
			if ( trim($strCurMon) == $arValues[$i] ) return true;
		}
		
		if ( $this->debug )
			print_r( $arValues );
			
		return false;				
	}
	
	private function passesDOW()
	{
		$strCurDOW = date( "N" );
		
		if ( $strCurDOW == 7 ) $strCurDOW == 0;
		
		if ( trim( $this->strDOW) == "*" ) return true;
		
		$arValues = array();
		$arCommas = $this->splitOnCommas( $this->strDOW );
		
		for ( $i = 0; $i < count($arCommas); $i++ )
		{
			$curPiece = trim($arCommas[$i]);
			
			if ( $this->containsDash( $curPiece ) )
			{
				$arDashes = $this->splitOnDash( $curPiece );
				if ( count($arDashes) == 2 )
				{
					if ( is_numeric( trim($arDashes[0]) ) && is_numeric( trim($arDashes[1]) ) )
					{
						for( $t = trim($arDashes[0]); $t <= trim($arDashes[1]); $t++ )
						{
							$arValues[] = $t;
						}
					}
				}
			}
			else if  ( $this->containsSlash( $curPiece ) )
			{
				$arSlash = $this->splitOnSlash( $curPiece );
				if ( count( $arSlash ) == 2 )
				{
					if ( trim( $arSlash[0] ) == "*" && is_numeric( trim( $arSlash[1] ) ) )
					{
						$divisor = trim( $arSlash[1] );
						// 00 - 59
						for ( $i = 0; $i < 6; $i++ )
						{
							if ( $i % $divisor == 0 )
								$arValues[] = $i;
						}
					}
				}
			}
			else
			{
				if ( is_numeric( $curPiece ) )
				{
					$arValues[] = $curPiece;
				}
			}			
		}
		
		for( $i = 0; $i < count( $arValues ); $i++ )
		{
			if ( trim($strCurDOW) == $arValues[$i] ) return true;
		}
		
		if ( $this->debug )
			print_r( $arValues );
			
		return false;			
	}
	
	private function d( $s )
	{
		if ( $this->debug )
			echo ( $s . "\n" );
	}
}

?>
