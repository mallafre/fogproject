<?php

//
// Blackout - 6:57 PM 5/05/2012
//
// blame.php
// Triggered:	On download when NFS mount fails (every 5 seconds)
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
	//$mac = str_replace( "-", ":", $mac );

	$jobid = getTaskIDByMac( $conn, $mac, 3 );
	if ( $jobid == "" )
		$jobid = getTaskIDByMac( $conn, $mac, '1, 2' );	
	$hostid = getHostID( $conn, $mac );

	if ( $jobid != null && $hostid != null && is_numeric( $jobid ) && is_numeric( $hostid ) )
	{
		$nfsGroupID = getNFSGroupIDByTaskID( $conn, $jobid );
		if ( $nfsGroupID )
		{	
			// get details about the storage node
			$sql = "SELECT 
					* 
				FROM 
					tasks 
				WHERE 
					taskID = '$jobid'";

			$res = mysql_query( $sql, $conn ) or die( mysql_error() );
			if ( mysql_num_rows( $res ) == 1 )
			{
				while( $ar = mysql_fetch_array( $res ) )
				{
					$nodeid = trim(mysql_real_escape_string($ar["taskNFSMemberID"]));
					if ( $nodeid != null )
					{
						$blUpdate = false;
						$blamed = getAllBlamedNodes( $conn, $jobid, $hostid );
						if ( ! in_array( $nodeid, $blamed ) )
						{
							$sql = "INSERT INTO nfsFailures (nfNodeID, nfTaskID, nfHostID, nfGroupID, nfDateTime) values ('$nodeid', '$jobid', '$hostid', '$nfsGroupID', NOW() )";
							if ( mysql_query( $sql, $conn ) )
							{
								$blUpdate = true;
							}
							else
							{
								echo _("Database error").": " . mysql_error();
							}
						}
						else
							$blUpdate = true;
							
						if ( $blUpdate )
						{
							// Set state back to 'Queued' 
							$sql = "UPDATE tasks set taskStateID = '1' WHERE taskID = '$jobid'";
							if ( mysql_query( $sql, $conn ) )
								echo "##";
							else 	
								echo _("Database error".": " . mysql_error());
						}
					}
				}
			}
			else
				echo _("Invalid number of tasks returned!");
		}
		else
			echo _("Unable to find a valid storage node, based on the job id.");
	}
	else
		echo _("Unable to find a valid task ID or host ID based on the clients mac address of").": " . $mac;
}
else
	echo _("Invalid MAC");
?>
