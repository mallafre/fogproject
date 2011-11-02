<?php

// Require FOG Base - the relative path to config.php changes in AJAX files as these files are included and accessed directly
require_once((defined('BASEPATH') ? BASEPATH . '/commons/config.php' : '../../commons/config.php'));
require_once(BASEPATH . '/commons/init.php');
require_once(BASEPATH . '/commons/init.database.php');

// Allow AJAX check
if (!$_SESSION['AllowAJAXTasks'])
{
	die('FOG Session Invalid');
}

// Blackout - 1:34 PM 2/06/2011
$Data = array();

$StorageNodes = mysql_query("SELECT * FROM nfsGroupMembers WHERE ngmIsEnabled = '1' AND ngmGraphEnabled='1'", $conn ) or die( mysql_error() );

// Loop each storage node -> grab stats
while ($Node = mysql_fetch_array($StorageNodes))
{
	// TODO: Need to move interface to per storage group server
	$URL = "http://" . $Node['ngmHostname'] . $GLOBALS['FOGCore']->getSetting("FOG_NFS_BANDWIDTHPATH") . '?dev=' . $Node['ngmInterface'];
	
	// Fetch bandwidth stats from remote server
	if ($FetchedData = Fetch($URL))
	{
		// Legacy client
		if (preg_match('/(.*)##(.*)/U', $FetchedData, $match))
		{
			$Data[$Node['ngmMemberName']] = array('rx' => $match[1], 'tx' => $match[2]);
		}
		else
		{
			$Data[$Node['ngmMemberName']] = json_decode($FetchedData, true);
		}
	}	
}

print json_encode($Data);