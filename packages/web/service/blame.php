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

	$jobid = getTaskIDByMac( $conn, $mac, 1 );
	if ( $jobid == "" )
		$jobid = getTaskIDByMac( $conn, $mac, 0 );	
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
							$sql = "UPDATE tasks set taskState = '0' WHERE taskID = '$jobid'";
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
