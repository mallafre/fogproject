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

$tmpMac = strtolower($_GET["mac"]);
$arMacs = HostManager::parseMacList($tmpMac);

if ( $arMacs == null || count( $arMacs ) == 0 )
	die( "#!im" );

// first get the global display setting values
$x = $FOGCore->getSetting( "FOG_SERVICE_DISPLAYMANAGER_X" );
$y = $FOGCore->getSetting( "FOG_SERVICE_DISPLAYMANAGER_Y" );
$r = $FOGCore->getSetting( "FOG_SERVICE_DISPLAYMANAGER_R" );

// now see if we have some host specific settings
try
{
	$host = $hostManager->getHostByMacAddresses($arMacs);
	$sRes = $serviceManager->getScreenResolutionSettingsForHost( $host );
	if ( $sRes != null && $sRes->isValid() )
	{
		$x = $sRes->getWidth();
		$y = $sRes->getHeight();
		$r = $sRes->getRefresh();
	}
}
catch( Exception $e )
{
	die( "#!er:" . $e->getMessage() );	
}
$string = $x . "x" . $y . "x" . $r;
die( base64_encode( $string ) );