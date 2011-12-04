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
 *  "#!ok" => Job Exists -> GO!
 *  "#!nj" => No Job Exists
 *
 */

$mac = strtolower($_GET["mac"]);
$arMacs 	= HostManager::parseMacList($mac);

if ( $arMacs == null || count( $arMacs ) == 0 )
	die( "#!im" );
	
$taskMan = $FOGCore->getClass('TaskManager');
$hostMan = $FOGCore->getClass('HostManager');

try
{
	$host = $hostMan->getHostByMacAddresses( $arMacs );
	
	if ( $host != null )
	{	
		if ( $taskMan->getCountOfActiveTasksForHost( $host ) > 0)
			echo "#!ok";
		else
			echo "#!nj";
	}
	else
		die( "#!er: "._("Host Not found.") );	
}
catch( Exception $e )
{
	die( "#!er:" . $e->getMessage()  );	
}
		
?>
