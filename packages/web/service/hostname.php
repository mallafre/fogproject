<?php

// Blackout - 11:14 AM 30/09/2011
 
// Require FOG Base
require_once('../commons/config.php');
require_once(BASEPATH . '/commons/init.php');
require_once(BASEPATH . '/commons/init.database.php');

/*
 *  Possible return codes
 *  "#!db" => Database error
 *  "#!im" => Invalid MAC Format
 *  "#!ih" => Invalid Host format
 *  "#!nf" => Mac/Hostname not found.
 *  "#!er" => Mac/Hostname not found.
 *  "#!ok=[hostname]" => Hostname found
 *
 */

$mac = strtolower($_GET["mac"]);
$arMacs 	= HostManager::parseMacList($mac);

if ( $arMacs == null || count( $arMacs ) == 0 )
{
	die( "#!im" );
}

$hostMan = $FOGCore->getClass('HostManager');

try
{
	$matchingHost = $hostMan->getHostByMacAddresses( $arMacs );
	
	if (!$matchingHost)
	{
		//die( "#!nf" );
		throw new Exception('No Host found');
	}
	if (!$matchingHost->isHostnameSafe())
	{
		//die( "#!ih" );
		throw new Exception('Invalid Hostname');
	}
	if ($matchingHost->get('useAD') != '1')
	{
		throw new Exception('Join domain disabled on this Host');
	}
	
	echo "#!ok=" . $matchingHost->get('name') . "\n";
	echo "#AD=" . ($matchingHost->get('useAD') ? "1" : "0" ) . "\n";
	echo "#ADDom=" . $matchingHost->get('ADDomain') . "\n";					
	echo "#ADOU=" . $matchingHost->get('ADOU') . "\n";	
	echo "#ADUser=" . $matchingHost->get('ADUser') . "\n";					
	echo "#ADPass=" . $matchingHost->get('ADPass') ;

}
catch( Exception $e )
{
	die( "#!er: " . $e->getMessage() );
}