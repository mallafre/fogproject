<?php

//
// Blackout - 6:57 PM 5/05/2012
//
// mc_checkin.php
// Triggered:	On multicast download (every 5 seconds)
// Actions:	Checks queue
//		Determines if Host is allowed to start imaging
//		Echos '##' when host is allowed to image
//

require('../commons/config.php');
require(BASEPATH . '/commons/init.php');
require(BASEPATH . '/commons/init.database.php');

$conn = mysql_connect( DATABASE_HOST, DATABASE_USERNAME, DATABASE_PASSWORD);
if ( $conn )
{
	if ( ! mysql_select_db( DATABASE_NAME, $conn ) ) die( _("Unable to select database") );
}
else
{
	die( _("Unable to connect to Database") );
}

$mac = strtolower($_GET["mac"]);

if ( ! isValidMACAddress( $mac ) )
{
	die( _("Invalid MAC address format!") );
}

if ( $mac != null  )
{
	$hostid = getHostID( $conn, $mac );
	cleanIncompleteTasks( $conn, $hostid );	
	if ( queuedTaskExists( $conn, $mac ) )
	{
		$jobid = getTaskIDByMac( $conn, $mac );
		if ( $hostid != null && $jobid != null )
		{
			if ( checkIn( $conn, $jobid ) )
			{
				if ( doImage( $conn, $jobid ) )
					echo "##";
				else
					echo _("Error attempting to start imaging process");				
				exit;			
			}
			else
			{
				echo _("Error: Checkin Failed.");
			}
		}
		else
		{
			echo _("Unable to locate host in database, please ensure that mac address is correct.");
		}
	}
	else
	{
		echo (_("No job was found for MAC Address").": $mac");
	}
}
else
	echo _("Invalid MAC Address");
?>
