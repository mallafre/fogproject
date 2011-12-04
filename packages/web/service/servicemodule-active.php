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

/*
 *  Possible return codes
 *  "#!db" => Database error
 *  "#!im" => Invalid MAC Format
 *  "#!ng" => Module is disabled globally
 *  "#!nh" => Module is disabled on this host 
 *  "#!um" => Unknown Module
 *  "#!ok" => Use it.
 *  "#!er" => Other error. 
 *
 */

$moduleName 	= $_GET["moduleid"];
$mac = strtolower($_GET["mac"]);

$arMacs 	= HostManager::parseMacList($mac);

if ( $arMacs == null || count( $arMacs ) == 0 )
	die( "#!im" );
	
if ( $moduleName == null )
	die( "#!um" );

/* Start slow migration to new abstacted DB classes */

$strGlobalSettingName = "";
$strHostSettingName = "";
if ( strcasecmp( $moduleName, "dircleanup" ) == 0 )
{
	$strGlobalSettingName = "FOG_SERVICE_DIRECTORYCLEANER_ENABLED";
	$strHostSettingName = "dircleanup";
}
else if ( strcasecmp( $moduleName, "usercleanup" ) == 0 )
{
	$strGlobalSettingName = "FOG_SERVICE_USERCLEANUP_ENABLED";
	$strHostSettingName = "usercleanup";
}
else if ( strcasecmp( $moduleName, "displaymanager" ) == 0 )
{
	$strGlobalSettingName = "FOG_SERVICE_DISPLAYMANAGER_ENABLED";
	$strHostSettingName = "displaymanager";
}
else if ( strcasecmp( $moduleName, "autologout" ) == 0 )
{
	$strGlobalSettingName = "FOG_SERVICE_AUTOLOGOFF_ENABLED";
	$strHostSettingName = "autologout";
}
else if ( strcasecmp( $moduleName, "greenfog" ) == 0 )
{
	$strGlobalSettingName = "FOG_SERVICE_GREENFOG_ENABLED";
	$strHostSettingName = "greenfog";
}
else if ( strcasecmp( $moduleName, "hostnamechanger" ) == 0 )
{
	$strGlobalSettingName = "FOG_SERVICE_HOSTNAMECHANGER_ENABLED";
	$strHostSettingName = "hostnamechanger";
}
else if ( strcasecmp( $moduleName, "snapin" ) == 0 )
{
	$strGlobalSettingName = "FOG_SERVICE_SNAPIN_ENABLED";
	$strHostSettingName = "snapin";
}
else if ( strcasecmp( $moduleName, "clientupdater" ) == 0 )
{
	$strGlobalSettingName = "FOG_SERVICE_CLIENTUPDATER_ENABLED";
	$strHostSettingName = "clientupdater";
}
else if ( strcasecmp( $moduleName, "hostregister" ) == 0 )
{
	$strGlobalSettingName = "FOG_SERVICE_HOSTREGISTER_ENABLED";
	$strHostSettingName = "hostregister";
}
else if ( strcasecmp( $moduleName, "printermanager" ) == 0 )
{
	$strGlobalSettingName = "FOG_SERVICE_PRINTERMANAGER_ENABLED";
	$strHostSettingName = "printermanager";
}
else if ( strcasecmp( $moduleName, "taskreboot" ) == 0 )
{
	$strGlobalSettingName = "FOG_SERVICE_TASKREBOOT_ENABLED";
	$strHostSettingName = "taskreboot";
}
else if ( strcasecmp( $moduleName, "usertracker" ) == 0 )
{
	$strGlobalSettingName = "FOG_SERVICE_USERTRACKER_ENABLED";
	$strHostSettingName = "usertracker";
}
else
	die( "#!um" );


// First lets see if the module is disabled globally.
$glEn = $FOGCore->getSetting( $strGlobalSettingName );
if ( $glEn == "1" )
{
	// OK, we are enabled globally, now make sure the host isn't disabled
	$hostMan = $FOGCore->getClass('HostManager');
	$arhost = null;
	try
	{
		$host = $hostMan->getHostByMacAddresses( $arMacs );
	}
	catch( Exception $e )
	{
		die( "#!er:" . $e->getMessage()  );	
	}
	
	try
	{
		if ( $host != null )
		{
			if ( $hostMan->isServiceModuleEnabledForHost($host, $strHostSettingName) )
				die("#!ok");
			else
				die ("#!nh");	 
		}
		else
			die( "#!er: "._("Host not found!")  );	
	}
	catch( Exception $e )
	{
		die( "#!er:" . $e->getMessage() );	
	}		
}
else
	die( "#!ng" );
	

 
?>
