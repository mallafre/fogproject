<?php

// Require FOG Base
require_once('../commons/config.php');
require_once(BASEPATH . '/commons/init.php');
require_once(BASEPATH . '/commons/init.database.php');

define( "SPACE_DEFAULT_STORAGE", "/images/" );
//define( "SPACE_DEFAULT_WEBROOT", WEB_ROOT );
define( "SPACE_DEFAULT_WEBROOT", "/fog/" );

$Data = array();

if ($_REQUEST['id'])
{
	try
	{
		require_once(BASEPATH . '/commons/init.php');
	
		if ($Node = @mysql_fetch_array(mysql_query("SELECT * FROM nfsGroupMembers WHERE ngmID='$id'", $conn)))
		{
			// make HTTP request
			$URL = "http://" . $Node['ngmHostname'] . SPACE_DEFAULT_WEBROOT . "status/freespace.php";
			//print $URL;
			
			if ($Response = $FOGCore->fetchURL($URL))
			{
				// Backwards compatibility for old versions of FOG
				if (preg_match('#(.*)@(.*)#', $Response, $match))
				{
					$Data = array('free' => $match[1], 'used' => $match[2]);
				}
				else
				{
					$Response = json_decode($Response, true);
					$Data = array('free' => $Response['free'], 'used' => $Response['used']);
				}
			}
			else
			{
				throw new Exception('Failed to connect to ' . $Node['ngmMemberName']);
			}
		}
		else
		{
			throw new Exception('Database error: ' . mysql_error());
		}
	}
	catch (Exception $e)
	{
		$Data['error'] = $e->getMessage();
	}
}
else
{
	// return data
	$free = ( disk_free_space( SPACE_DEFAULT_STORAGE ) );
	$freegb = round( ( ( ($free / 1024) / 1024) /1024), 2);

	$used = ( disk_total_space( SPACE_DEFAULT_STORAGE ) - disk_free_space( SPACE_DEFAULT_STORAGE ) );
	$usedgb = round( ( ( ($used / 1024) / 1024) /1024), 2);

	$Data = array('free' => $freegb, 'used' => $usedgb);
}

print json_encode($Data);