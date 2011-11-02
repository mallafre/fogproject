<?php

// Require FOG Base
require_once('../commons/config.php');
require_once(BASEPATH . '/commons/init.php');


function getData($interface)
{
	$fp = fopen(PROCNETDEV, "r");
	
	while ($line = fgets($fp, 256))
	{
		$temp = split(":", trim($line));
		if ($temp[0] == $interface) 
		{
			$line = preg_split("/[\s]+/", trim($temp[1]));
			
			fclose($fp);
			return array($line[0], $line[8]);
		}

	}
	
	fclose($fp);
}

define("PROCNETDEV", "/proc/net/dev");
define("SLEEPSEC", 1);

$Data = array();

if (!file_exists(PROCNETDEV) || !is_readable(PROCNETDEV))
{
	$Data['error'] = (file_exists(PROCNETDEV) ? PROCNETDEV . ' is not readable' : PROCNETDEV . ' doesnt not exist');
}
else
{
	// Blackout - 12:14 PM 2/06/2011
	// Removed so a mysql connection is not requried
	// $dev MUST be passed, other NFS_ETH_MONITOR is assumed!! - this comes from config.php
	// If NFS_ETH_MONITOR is not defined, eth0
	//$dev = $GLOBALS['FOGCore']->getSetting( "FOG_NFS_ETH_MONITOR" );
	
	$dev = ($_REQUEST['dev'] ? trim($_REQUEST['dev']) : (defined('NFS_ETH_MONITOR') ? NFS_ETH_MONITOR : 'eth0'));
		
	list($intLastRx, $intLastTx) = getData($dev);
	sleep(SLEEPSEC);
	list($intCurRx, $intCurTx) = getData($dev);
	
	if ( is_numeric( $intCurRx ) && is_numeric($intLastRx) )
	{
		// Calculate speed in Kilobytes per second
		$rx = ceil(($intCurRx - $intLastRx) / SLEEPSEC / 1024);
		$tx = ceil(($intCurTx - $intLastTx) / SLEEPSEC / 1024);
		
		// Sometimes we get negative numbers - no idea why
		$Data = array(	'rx' => ($rx > 0 ? $rx : 0),
				'tx' => ($tx > 0 ? $tx : 0));
		
	}
	else
	{
		$Data = array('rx' => 0, 'tx' => 0);
	}
}

if ($dev) $Data['dev'] = $dev;

print json_encode($Data);