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

require_once('../commons/config.php');
require_once(BASEPATH . '/commons/init.php');
require_once(BASEPATH . '/commons/init.database.php');

/*
 *  Possible return codes
 *  "#!db" => Database error
 *  "#!im" => Invalid MAC Format
 *  "#!ih" => Invalid Host format
 *  "#!ma" => Mac address already exists.
 *  "#!er" => Other error.
 *  "#!ok" => registration successful.
 *  "#!ig" => Ignored, probably because we only have one mac
 *
 */

function startsWith($haystack, $needle)
{
    return strpos($haystack, $needle) === 0;
}

function isIgnoredMac( $mac, $list)
{
	if ( $mac != null && $list != null )
	{
		for( $i = 0; $i < count( $list ); $i++ )
		{
			if ( startsWith($mac->getMACWithColon(), $list[$i]) )
				return true;
		}
	}
	return false;
}

if ( !(isset($_GET["version"]) && $_GET["version"] == "2") )
	die( "#!er - Invalid Version Number, please update this module." );

$mac = strtolower($_GET["mac"]);
$arMacs 	= HostManager::parseMacList($mac);

if ( $arMacs == null || count( $arMacs ) == 0 )
	die( "#!im" );

$hostMan = $FOGCore->getClass('HostManager');

if ( count( $arMacs ) > 1 )
{
	$maxPending = $FOGCore->getSetting("FOG_QUICKREG_MAX_PENDING_MACS");
	$strIgnored = $FOGCore->getSetting("FOG_QUICKREG_PENDING_MAC_FILTER");
	
	$arIgnoreList = array();
	$parts = explode( ",", $strIgnored );
	for( $i = 0; $i < count( $parts); $i++ )
	{
		if ( $parts[$i] != null )
			$arIgnoreList[] = trim($parts[$i]);
	}

	$primaryHost = null;
	for( $i = 0; $i < count( $arMacs ); $i++ )
	{
		$mac = $arMacs[$i];
		if ( $mac != null && ! isIgnoredMac( $mac, $arIgnoreList) )
		{
			$tmpHost = $hostMan->getHostByMacAddress( $mac, true );
			if ( $tmpHost != null )
			{
				if ( $primaryHost == null )
				{
					$primaryHost = $tmpHost;
				}
				else
				{
					die( "#!ig" );
				}
			}
		}
	}

	$arAdditionalMacs = array();
	if ( $primaryHost != null )
	{
		$blAnyRegistered = false;
		for( $i = 0; $i < count( $arMacs ); $i++ )
		{
			$mac = $arMacs[$i];
			if ( $mac != null && ! isIgnoredMac( $mac, $arIgnoreList) )
			{
				if ($primaryHost->get('mac') != $mac && !in_array($mac, $primaryHost->get('additionalMACs')))
				//if ( ! $primaryHost->hasMac( $mac ) )
				{
					// see if any host already has this mac registered
					$tmpHost = $hostMan->getHostByMacAddress( $mac );
					if ( $tmpHost == null )
					{
						//make sure host doesn't already have too many pending
						// mac addresses
						$pending = $hostMan->getPendingMacAddressesForHost( $primaryHost );
						if ( $pending == null || count($pending) < $maxPending )
						{
							// add pending mac to host
							if ( $hostMan->addMACToPendingForHost( $primaryHost, $mac ) )
								$blAnyRegistered = true;
						}
					}
				}
			}
		}

		if ( $blAnyRegistered  )
		{
			die( "#!ok" );
		}
		else
		{
			die( "#!ig" );
		}
	}
	else
	{
		die( "#!ig" );
	}
}
else
{
	echo "#!ig";
}