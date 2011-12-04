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

$mac 		= strtolower($_GET["mac"]);
$arMacs 	= HostManager::parseMacList($mac);

if ( $arMacs == null || count( $arMacs ) == 0 )
	die( "#!im" );

$hostMan = $FOGCore->getClass('HostManager');
$serviceManager = $FOGCore->getClass('ClientServiceManager');

// first get the global settings
$tme = $FOGCore->getSetting( "FOG_SERVICE_AUTOLOGOFF_MIN" );
try
{
	$host = $hostMan->getHostByMacAddresses( $arMacs );

	if ( $host != null  )
	{
		$hostSetting = $serviceManager->getAutoLogOutTimeForHost( $host );
		if ( $hostSetting > 0 && is_numeric( $hostSetting ) )
			$tme = $hostSetting;			
	}
	else
		die( "#!nf" );
}
catch( Exception $e )
{
	die( "#!er: " . $e->getMessage() );
}	
die( base64_encode( $tme ) );
?>
