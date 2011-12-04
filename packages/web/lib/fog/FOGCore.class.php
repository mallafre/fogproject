<?php
/*
 *  FOG is a computer imaging solution.
 *  Copyright (C) 2009  Chuck Syperski & Jian Zhang
 *
 *   This program is free software: you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation, either version 3 of the License, or
 *   any later version.
 *
 *   This program is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 *
 */
class FOGCore
{
	const TASK_UNICAST_SEND 	= 'd';
	const TASK_UNICAST_UPLOAD 	= 'u';
	const TASK_WIPE		 	= 'w';
	const TASK_DEBUG	 	= 'x';
	const TASK_MEMTEST	 	= 'm';
	const TASK_TESTDISK	 	= 't';
	const TASK_PHOTOREC	 	= 'r';
	const TASK_MULTICAST 		= 'c';
	const TASK_VIRUSSCAN	 	= 'v';
	const TASK_INVENTORY	 	= 'i';
	const TASK_PASSWORD_RESET 	= 'J';
	const TASK_ALL_SNAPINS	 	= 's';
	const TASK_SINGLE_SNAPIN 	= 'l';
	const TASK_WAKE_ON_LAN	 	= 'o';

	public $db;
	
	function __construct( $conn )
	{
		$this->db = $conn;
	}
	
	private function cleanOldUnrunScheduledTasks()
	{
		if ( $this->db != null )
		{
			$sql = "UPDATE 
					scheduledTasks 
				SET
					stActive = '0'
				WHERE 
					stType = '" . ScheduledTask::TASK_TYPE_SINGLE . "' and 
					stDateTime < (UNIX_TIMESTAMP() - " . Timer::TASK_SINGLE_FLEXTIME . ") and 
					stActive = '1'";
			
			mysql_query( $sql, $this->db ) or die( mysql_error($this->db) );
		}
	}
	
	function stopScheduledTask( $task )
	{
		if ( $task != null && $this->db != null )
		{
			if ( is_numeric( $task->getID() ) )
			{
				$sql = "UPDATE 
						scheduledTasks 
					SET
						stActive = '0'
					WHERE 
						stID = '" . $task->getID() . "'";
			
				if ( mysql_query( $sql, $this->db ) )
					return true;
			}
		}
		return false;
	}
	
	function getScheduledTasksByStorageGroupID( $groupid, $blIgnoreNonImageReady=false )
	{
		$arTasks = array();
		if ( $this->db != null && ((is_numeric( $groupid ) && $groupid >= 0) || $groupid = "%" ))
		{
			$this->cleanOldUnrunScheduledTasks();
			
			$sql = "SELECT 
					* 
				FROM 
					scheduledTasks 
				WHERE 
					stActive = '1'";
				

			$res = mysql_query( $sql, $this->db ) or die( mysql_error($this->db) );
			//echo mysql_num_rows( $res ) ;
			while( $ar = mysql_fetch_array( $res ) )
			{
				$timer = null;
				if ( $ar["stType"] == ScheduledTask::TASK_TYPE_SINGLE )
				{
					$timer = new Timer( $ar["stDateTime"] );
 				}
				else if ($ar["stType"] == ScheduledTask::TASK_TYPE_CRON )
				{
					$timer = new Timer( $ar["stMinute"], $ar["stHour"], $ar["stDOM"], $ar["stMonth"], $ar["stDOW"] );				
				}
				
				if ( $timer != null )
				{
					$group=null;
					$host=null;
					
					if ( $ar["stIsGroup"] == "0" )
						$host = new Host($ar["stGroupHostID"]);
					else if ( $ar["stIsGroup"] == "1" )
						$group = new Group($ar["stGroupHostID"]);
					
					if ( $group != null || $host != null )
					{
						if ( $host != null )
						{
							if ( ($host->isValid() || $blIgnoreNonImageReady) && ( $groupid == "%" || $host->getImage()->getStorageGroup()->getID() == $groupid  ) )
							{
								$task = new ScheduledTask( $host, $group, $timer, $ar["stTaskType"], $ar["stID"] );
								$task->setShutdownAfterTask( $ar["stShutDown"] == 1 );
								$task->setOther1( $ar["stOther1"] );
								$task->setOther2( $ar["stOther2"] );
								$task->setOther3( $ar["stOther3"] );
								$task->setOther4( $ar["stOther4"] );
								$task->setOther5( $ar["stOther5"] );
								$arTasks[] = $task;
							}
						}
						else if ( $group != null )
						{
							if ( $group->getHostCount() > 0  )
							{
								$arRm = array();
								$hosts = $group->getHosts();
								for( $i = 0; $i < count($hosts); $i++ )
								{
									 if ( $hosts[$i] != null )
									 {
									 	$h = $hosts[$i];
									 	if ( ! ($h->isValid() &&  $h->getImage()->getStorageGroup()->getID() == $groupid ) )
									 	{
									 		$arRm[] = $h;
									 	}	
									 }
								}
								
								//echo ( "Before: " . $group->getHostCount() );
								for( $i = 0; $i < count($arRm); $i++ )
								{
									$group->removeHost( $arRm[$i] );
								}
								//echo ( "After: " . $group->getHostCount() );
								
								$task = new ScheduledTask( $host, $group, $timer, $ar["stTaskType"], $ar["stID"] );
								$task->setShutdownAfterTask( $ar["stShutDown"] == 1 );
								$task->setOther1( $ar["stOther1"] );
								$task->setOther2( $ar["stOther2"] );
								$task->setOther3( $ar["stOther3"] );
								$task->setOther4( $ar["stOther4"] );
								$task->setOther5( $ar["stOther5"] );
								$arTasks[] = $task;								
							}
						}
					}				
				}
			}

			
		}
		return $arTasks;
	}
	
	function redirect($url = '')
	{
		if ($url == '')
		{
			$url = $_SERVER['PHP_SELF'] . ($_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : '');
		}
	
		if (headers_sent())
		{
			printf('<meta http-equiv="refresh" content="0; url=%s">', $url);
		}
		else
		{
			header("Location: $url");
		}
		exit;
	}
	
	function setMessage($txt, $data = array())
	{
		$_SESSION['FOG_MESSAGES'] = (!is_array($txt) ? array(vsprintf($txt, $data)) : $txt);
		
		return $this;
	}
	
	function getMessages()
	{
		print "<!-- FOG Variables -->\n";
		
		foreach ((array)$_SESSION['FOG_MESSAGES'] AS $message)
		{
			msgBox($message);
		}
		
		unset($_SESSION['FOG_MESSAGES']);
	}
	
	function logHistory($string)
	{
		global $conn, $currentUser;
		$uname = "";
		if ( $currentUser != null )
			$uname = mysql_real_escape_string( $currentUser->get('name') );
			
		$sql = "insert into history( hText, hUser, hTime, hIP ) values( '" . mysql_real_escape_string( $string ) . "', '" . $uname . "', NOW(), '" . $_SERVER[REMOTE_ADDR] . "')";
		@mysql_query( $sql, $conn );
	}
	
	function searchManager($manager = 'Host', $keyword = '*')
	{
		$manager = ucwords(strtolower($manager)) . 'Manager';
		
		//$Manager = new $manager();
		// TODO: Replace this when all Manager classes no longer need the database connection passed
		$Manager = new $manager( $GLOBALS['conn'] );
		
		return $Manager->search($keyword);
	}
	
	// Moved from functions - Blackout - 11:36 AM 26/09/2011
	function getSetting( $key )
	{
		$conn = $GLOBALS['conn'];
		if ( $conn != null )
		{
			$key = mysql_real_escape_string( $key );
			$sql = "SELECT settingValue FROM globalSettings WHERE settingKey = '$key'";
			$res = mysql_query( $sql, $conn ) or criticalError( mysql_error(), _("FOG :: Database error!") );
			while( $ar = mysql_fetch_array( $res ) )
			{
				return trim($ar["settingValue"]);
			}
		}
		return "";
	}

	// Moved from functions - Blackout - 11:36 AM 26/09/2011
	function setSetting( $key, $value )
	{
		$conn = $GLOBALS['conn'];
		if ( $conn != null )
		{
			$key = mysql_real_escape_string( $key );
			$value = mysql_real_escape_string( $value );
			$sql = "UPDATE globalSettings SET settingValue =  '$value' WHERE settingKey = '$key' limit 1";
			if ( mysql_query( $sql, $conn ) )
				return true;
			else 
				return false;
		}
		return false;
	}
	
	function getClass($class)
	{
		$args = func_get_args();
		array_pop($args);
		
		if (count($args))
		{
			// TODO: Make this work
			// http://au.php.net/ReflectionClass
			
			//$r = new ReflectionClass($class);
			//return new $r->newInstanceArgs($args);
			
			return new $class($args);
		}
		else
		{
			return new $class();
		}
	}
	
	function isAJAXRequest()
	{
		return (strtolower(@$_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' ? true : false);
	}
	
	function error($txt, $data = array())
	{
		if (!$this->isAJAXRequest())
		{
			printf('<div class="debug-error">FOG ERROR: %s</div>%s', (count($data) ? vsprintf($txt, $data) : $txt), "\n");
		}
	}
	
	function info($txt, $data = array())
	{
		if (!$this->isAJAXRequest() && !preg_match('#/service/#', $_SERVER['PHP_SELF']))
		{
			printf('<div class="debug-info">FOG INFO: %s</div>%s', (count($data) ? vsprintf($txt, $data) : $txt), "\n");
		}
	}
	
	
	// From Core.class.php
	// Blackout - 6:43 AM 4/12/2011
	function getMACManufacturer( $macprefix )
	{
		if ( $this->db && strlen( $macprefix ) == 8 )
		{
			$sql = "SELECT
					ouiMan
				FROM 
					oui
				WHERE
					ouiMACPrefix = '" . $this->db->sanitize( $macprefix ) . "'";
			if ( $this->db->query($sql) )
			{
				while( $ar = $this->db->fetch()->get() )
				{	
					return $ar["ouiMan"];
				}
			}
		}
		return _("n/a");
	}
	
	function addUpdateMACLookupTable( $macprefix, $strMan )
	{
		if ( $this->db && strlen( $macprefix ) == 8 && $strMan != null && strlen( $strMan ) > 0 )
		{
			if ( $this->doesMACLookCodeExist( $macprefix ) )
			{
				// update
				$sql = "UPDATE
						oui
					SET
						ouiMan = '" . $this->db->sanitize( $strMan ) . "'
					WHERE
						ouiMACPrefix = '" . $this->db->sanitize( $macprefix ) . "'";
				$this->db->query($sql)->affected_rows();
				return true;
			}
			else
			{
				// insert
				$sql = "INSERT INTO
						oui
							(ouiMACPrefix, ouiMan)
						VALUES
							('" . $this->db->sanitize( $macprefix ) . "', '" . $this->db->sanitize( $strMan ) . "')";
				return $this->db->query($sql)->affected_rows() == 1;
			}
		}
		return false;
	}
	
	private function doesMACLookCodeExist( $macprefix )
	{
		if ( $this->db != null  )
		{
			if ( strlen( $macprefix ) == 8 )
			{
				$sql = "SELECT
						count(*) as cnt 
					FROM 
						oui
					WHERE
						ouiMACPrefix = '" . $this->db->sanitize( $macprefix ) . "'";
				if ( $this->db->query($sql) )
				{
					while( $ar = $this->db->fetch()->get() )
					{	
						return $ar["cnt"] > 0;
					}
				}
				else
					throw new Exception( _("Unable to lookup mac prefix!") );
			}
			else
				throw new Exception( _("Invalid mac prefix")." " . $macprefix );	
		}
		else
			throw new Exception( _("Unable to lookup mac prefix!") );
	}
	
	function clearMACLookupTable()
	{
		if ( $this->db != null )
		{
			$sql = "DELETE 
				FROM 
					oui
				WHERE
					1=1";
			return ( $this->db->query($sql)->affected_rows() );

		}
		return false;
	}
	
	function getMACLookupCount()
	{
		if ( $this->db != null )
		{
			$sql = "SELECT 
					COUNT(*) AS cnt
				FROM 
					oui";
			if ( $this->db->query($sql) )
			{
				while( $ar = $this->db->fetch()->get() )
				{
					return $ar["cnt"];
				}
			}
		}
		return -1;
	}
}