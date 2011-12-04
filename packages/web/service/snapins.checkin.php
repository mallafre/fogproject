<?php
/*
 *  FOG - Free, Open-Source Ghost is a computer imaging solution.
 *  Copyright (C) 2007  Chuck Syperski & Jian Zhang
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

// Require FOG Base
require_once('../commons/config.php');
require_once(BASEPATH . '/commons/init.php');
require_once(BASEPATH . '/commons/init.database.php');

/*
 *  Possible return codes
 *  "#!db" => Database error
 *  "#!im" => Invalid MAC Format
 *  "#!er" => Other error.
 *  "#!it" => Image task exists -> no snapins to be installed
 *  "#!ns" => No Snapins found
 *  "#!ok" => Job Exists -> GO!
 *
 */

$hostManager = $FOGCore->getClass('HostManager');
$serviceManager = $FOGCore->getClass('ClientServiceManager');
$taskMan = $FOGCore->getClass('TaskManager');

$tmpMac = strtolower($_GET["mac"]);
$arMacs = HostManager::parseMacList($tmpMac);

if ( $arMacs == null || count( $arMacs ) == 0 )
	die( "#!im" );

try
{
	$host = $hostManager->getHostByMacAddresses($arMacs);
	
	if ( $host != null )
	{	
		
		if ( ! $taskMan->getCountOfActiveTasksForHost( $host ) > 0)
		{
			if ( isset( $_GET["exitcode"] ) && is_numeric( $_GET["exitcode"] ) && isset( $_GET["taskid"] ) && is_numeric( $_GET["taskid"] ) )
			{
				$snapinTask = $serviceManager->getSnapinTaskById($_GET["taskid"]);
				if ( $snapinTask != null )
				{
					$serviceManager->completeSnapinTask($snapinTask, $_GET["exitcode"], $_GET["exitdesc"]);
					echo "#!ok";
				}
			}
			else
			{
				$snapins = $serviceManager->getAllActiveSnapinsForHost($host);
				if ( $snapins != null && count( $snapins ) > 0 )
				{

					$task = $snapins[0];
					if ( $task != null )
					{
							
						$serviceManager->checkInSnapinTask( $task );
						echo "#!ok\n";
						echo "JOBTASKID=" . trim($task->getId()) . "\n" ;
						echo "JOBCREATION=" . trim($task->getCreationDate()) . "\n";
						echo "SNAPINNAME=" . trim($task->getSnapin()->get('name')) . "\n";
						echo "SNAPINARGS=" . trim( $task->getSnapin()->get('args') ) . "\n";
						echo "SNAPINBOUNCE=" . (trim( $task->getSnapin()->get('reboot') ) ? "1" : "0") . "\n";
						echo "SNAPINFILENAME=" . trim( basename($task->getSnapin()->get('file')) ) . "\n";
						echo "SNAPINRUNWITH=" . trim( $task->getSnapin()->get('runWith') ) . "\n";
						echo "SNAPINRUNWITHARGS=" . trim( $task->getSnapin()->get('runWithArgs') );						
					}
					else
						echo "#!ns";
				}
				else 
					echo "#!ns";
			}
		}
		else
			echo "#!it";
	}
	else
		echo( "#!er:Host Not Found!" );
}
catch( Exception $e )
{
	die( "#!er:" . $e->getMessage() );	
}
?>
