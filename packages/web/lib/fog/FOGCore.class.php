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
class FOGCore extends FOGBase
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
	
	private function cleanOldUnrunScheduledTasks()
	{
		if ( $this->conn != null )
		{
			$sql = "UPDATE 
					scheduledTasks 
				SET
					stActive = '0'
				WHERE 
					stType = '" . ScheduledTask::TASK_TYPE_SINGLE . "' and 
					stDateTime < (UNIX_TIMESTAMP() - " . Timer::TASK_SINGLE_FLEXTIME . ") and 
					stActive = '1'";
			
			mysql_query( $sql, $this->conn ) or die( mysql_error($this->conn) );
		}
	}
	
	function stopScheduledTask( $task )
	{
		if ( $task != null && $this->conn != null )
		{
			if ( is_numeric( $task->getID() ) )
			{
				$sql = "UPDATE 
						scheduledTasks 
					SET
						stActive = '0'
					WHERE 
						stID = '" . $task->getID() . "'";
			
				if ( mysql_query( $sql, $this->conn ) )
					return true;
			}
		}
		return false;
	}
	
	function getScheduledTasksByStorageGroupID( $groupid, $blIgnoreNonImageReady=false )
	{
		$arTasks = array();
		if ( $this->conn != null && ((is_numeric( $groupid ) && $groupid >= 0) || $groupid = "%" ))
		{
			$this->cleanOldUnrunScheduledTasks();
			
			$sql = "SELECT 
					* 
				FROM 
					scheduledTasks 
				WHERE 
					stActive = '1'";
				

			$res = mysql_query( $sql, $this->conn ) or die( mysql_error($this->conn) );
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
								$task = new ScheduledTask( $host, $group, $timer, $ar["stTaskTypeID"], $ar["stID"] );
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
								
								$task = new ScheduledTask( $host, $group, $timer, $ar["stTaskTypeID"], $ar["stID"] );
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
	
	public function redirect($url = '')
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
	
	public function setMessage($txt, $data = array())
	{
		$_SESSION['FOG_MESSAGES'] = (!is_array($txt) ? array(vsprintf($txt, $data)) : $txt);
		
		return $this;
	}
	
	public function getMessages()
	{
		print "<!-- FOG Variables -->\n";
		
		foreach ((array)$_SESSION['FOG_MESSAGES'] AS $message)
		{
			msgBox($message);
		}
		
		unset($_SESSION['FOG_MESSAGES']);
	}
	
	public function logHistory($string)
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
	
	public function getSetting($key)
	{
		return $this->DB->query("SELECT settingValue FROM globalSettings WHERE settingKey = '%s' LIMIT 1", array($key))->fetch()->get('settingValue');
	}
	
	public function setSetting($key, $value)
	{
		return $this->DB->query("UPDATE globalSettings SET settingValue = '%s' WHERE settingKey = '%s'", array($value, $key))->queryResult();
	}
	
	public function isAJAXRequest()
	{
		return (strtolower(@$_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' ? true : false);
	}
	
	public function isPOSTRequest()
	{
		return (strtolower(@$_SERVER['REQUEST_METHOD']) == 'post' ? true : false);
	}
	
	/*
	public function error($txt, $data = array())
	{
		//if (!$this->isAJAXRequest() && !preg_match('#/service/#', $_SERVER['PHP_SELF']))
		if (!FOGCore::isAJAXRequest() && !preg_match('#/service/#', $_SERVER['PHP_SELF']))
		{
			printf('<div class="debug-error">FOG ERROR: %s</div>%s', (count($data) ? vsprintf($txt, $data) : $txt), "\n");
		}
	}
	
	public function info($txt, $data = array())
	{
		if (!FOGCore::isAJAXRequest() && !preg_match('#/service/#', $_SERVER['PHP_SELF']))
		{
			printf('<div class="debug-info">FOG INFO: %s</div>%s', (count($data) ? vsprintf($txt, $data) : $txt), "\n");
		}
	}
	*/
	
	
	// From Core.class.php
	// Blackout - 6:43 AM 4/12/2011
	function getMACManufacturer( $macprefix )
	{
		if ( $this->conn && strlen( $macprefix ) == 8 )
		{
			$sql = "SELECT
					ouiMan
				FROM 
					oui
				WHERE
					ouiMACPrefix = '" . $this->DB->sanitize( $macprefix ) . "'";
			if ( $this->DB->query($sql) )
			{
				while( $ar = $this->DB->fetch()->get() )
				{	
					return $ar["ouiMan"];
				}
			}
		}
		return _("n/a");
	}
	
	function addUpdateMACLookupTable( $macprefix, $strMan )
	{
		if ( $this->conn && strlen( $macprefix ) == 8 && $strMan != null && strlen( $strMan ) > 0 )
		{
			if ( $this->doesMACLookCodeExist( $macprefix ) )
			{
				// update
				$sql = "UPDATE
						oui
					SET
						ouiMan = '" . $this->DB->sanitize( $strMan ) . "'
					WHERE
						ouiMACPrefix = '" . $this->DB->sanitize( $macprefix ) . "'";
				$this->DB->query($sql)->affected_rows();
				return true;
			}
			else
			{
				// insert
				$sql = "INSERT INTO
						oui
							(ouiMACPrefix, ouiMan)
						VALUES
							('" . $this->DB->sanitize( $macprefix ) . "', '" . $this->DB->sanitize( $strMan ) . "')";
				return $this->DB->query($sql)->affected_rows() == 1;
			}
		}
		return false;
	}
	
	private function doesMACLookCodeExist( $macprefix )
	{
		if ( $this->conn != null  )
		{
			if ( strlen( $macprefix ) == 8 )
			{
				$sql = "SELECT
						count(*) as cnt 
					FROM 
						oui
					WHERE
						ouiMACPrefix = '" . $this->DB->sanitize( $macprefix ) . "'";
				if ( $this->DB->query($sql) )
				{
					while( $ar = $this->DB->fetch()->get() )
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
		if ( $this->conn != null )
		{
			$sql = "DELETE 
				FROM 
					oui
				WHERE
					1=1";
			return ( $this->DB->query($sql)->affected_rows() );

		}
		return false;
	}
	
	function getMACLookupCount()
	{
		if ( $this->conn != null )
		{
			$sql = "SELECT 
					COUNT(*) AS cnt
				FROM 
					oui";
			if ( $this->DB->query($sql) )
			{
				while( $ar = $this->DB->fetch()->get() )
				{
					return $ar["cnt"];
				}
			}
		}
		return -1;
	}
	
	// Blackout - 10:26 AM 25/05/2011
	// Used from one of my classes - hacked to make it work
	// TODO: Make a FOG Utilities Class - include this
	public function fetchURL($URL)
	{
		if ($this->DB && $GLOBALS['FOGCore']->getSetting('FOG_PROXY_IP'))
		{
			$Proxy = $GLOBALS['FOGCore']->getSetting('FOG_PROXY_IP') . ':' . $GLOBALS['FOGCore']->getSetting('FOG_PROXY_PORT');
		}
		
		$userAgent = 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.6.12) Gecko/20110319 Firefox/4.0.1 ( .NET CLR 3.5.30729; .NET4.0E)';
		$timeout = 10;
		$maxRedirects = 20;
		
		$contextOptions = array(
					'ssl'	=> array(
							'allow_self_signed' => true
							),
					'http'	=> array(
							'method' 	=> 'GET',
							'user_agent' 	=> $userAgent,
							'timeout' 	=> $timeout,
							'max_redirects'	=> $maxRedirects,
							'header' 	=> array(
										'Accept-language: en',
										'Pragma: no-cache'
									)
							)
					);

		// Proxy
		if ($Proxy)
		{
			$contextOptions['http']['proxy'] = 'tcp://' . $Proxy;
			$contextOptions['http']['request_fulluri'] = true;
		}

		// Get data
		if ($response = trim(@file_get_contents($URL, false, stream_context_create($contextOptions))))
		{
			return $response;
		}
		else
		{
			return false;
		}
	}

	public function resolveHostname($host)
	{
		return ($this->getSetting('FOG_USE_SLOPPY_NAME_LOOKUPS') ? gethostbyname($host) : $host);
	}
	
	public function makeTempFilePath()
	{
		return tempnam(sys_get_temp_dir(), 'FOG');
	}
	
	public function wakeOnLAN($mac)
	{
		return; // DEBUG
		
		// HTTP request to WOL script
		$this->fetchURL(sprintf('http://%s%s?wakeonlan=%s', $this->getSetting('FOG_WOL_HOST'), $this->getSetting('FOG_WOL_PATH'), ($mac instanceof MACAddress ? $mac->getMACWithColon() : $mac)));
	}
	
	public function formatTime($time, $format = '')
	{
		// Convert to unix date if not already
		if (!is_numeric($time))
		{
			$time = strtotime($time);
		}
		
		// Forced format
		if ($format)
		{
			return date($format, $time);
		}
		
		// Today
		if (date('d-m-Y', $time) == date('d-m-Y'))
		{
			return 'Today, ' . date('g:ia', $time);
		}
		// Yesterday
		elseif (date('d-m-Y', $time) == date('d-m-Y', strtotime('-1 day')))
		{
			return 'Yesterday, ' . date('g:i a', $time);
		}
		// Short date
		elseif (date('m-Y', $time) == date('m-Y'))
		{
			return date('jS, g:ia', $time);
		}
		
		// Long date
		return date('m-d-Y g:ia', $time);
	}
	
	// Blackout - 2:40 PM 25/05/2011
	function SystemUptime()
	{
		$data = trim(shell_exec('uptime'));
		
		$load = end(explode(' load average: ', $data));
		
		$uptime = explode(',', end(explode(' up ', $data)));
		$uptime = (count($uptime) > 1 ? $uptime[0] . ', ' . $uptime[1] : 'uptime not found');
		
		return array('uptime' => $uptime, 'load' => $load);
	}
}