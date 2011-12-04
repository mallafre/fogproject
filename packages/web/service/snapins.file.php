<?php
/*
 *  FOG is a computer imaging solution.
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

$hostManager = $FOGCore->getClass('HostManager');
$serviceManager = $FOGCore->getClass('ClientServiceManager');
$taskMan = $FOGCore->getClass('TaskManager');

$tmpMac = strtolower($_GET["mac"]);
$arMacs = HostManager::parseMacList($tmpMac);

if ( $arMacs == null || count( $arMacs ) == 0 )
	die( "#!im" );

if ( isset( $_GET["taskid"] ) )
{
	try
	{
		$host = $hostManager->getHostByMacAddresses($arMacs);
		if ( $host != null )
		{
			$taskid = $_GET["taskid"];
			if ( ! $taskMan->getCountOfActiveTasksForHost( $host ) > 0)
			{
				$snapinTask = $serviceManager->getSnapinTaskById($taskid);
				if ( $snapinTask != null && $snapinTask->getHostId() == $host->getID() )
				{
					if ( file_exists( $snapinTask->getSnapin()->get('file') ) && is_readable(  $snapinTask->getSnapin()->get('file') ) )
					{
						header ("Cache-Control: must-revalidate, post-check=0, pre-check=0");
						header ("Content-Description: File Transfer");
						header ("Content-Type: application/octet-stream");
						header("Content-Length: " . filesize( $snapinTask->getSnapin()->get('file')));
						header("Content-Disposition: attachment; filename=" . basename($snapinTask->getSnapin()->get('file')));
						@readfile( $snapinTask->getSnapin()->get('file')); 	
						$serviceManager->completeSnapinTask($snapintask, -1, "Pending...");
					}		
					
				}
			}	
		}
	}
	catch( Exception $e ) {}
	
}

?>
