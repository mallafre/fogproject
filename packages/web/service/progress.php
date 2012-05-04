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

$mac = $_POST["mac"];
$status = $_POST["status"];


if ( ! isValidMACAddress( $mac ) )
{
	die( _("Invalid MAC address format!") );
}

if ( $mac != null && $status != null )
{
	$hostid = getHostID( $conn, $mac );
	$jobid = getTaskIDByMac( $conn, $mac, 2 );

	if ( $jobid == "" )
		$jobid = getTaskIDByMac( $conn, $mac );

	$str = base64_decode( $status );
	$arStr = explode( "@", $str );
	
	$sql = "SELECT
			taskType
		FROM
			tasks
		WHERE 
			taskID = '" . $jobid . "'";
	$res = mysql_query( $sql, $conn ) or die( mysql_error() );
	if ( $ar = mysql_fetch_array( $res ) )
	{
		// Download or Deploy Task
		if ( strtolower($ar["taskType"]) == "d" || strtolower($ar["taskType"]) == "u")
		{

			if ( count( $arStr ) == 6 )
			{		
				$bpm = mysql_real_escape_string($arStr[0]);
				$elapsed = mysql_real_escape_string( $arStr[1] );
				$remaining = mysql_real_escape_string( $arStr[2] );
				$datacopied = mysql_real_escape_string( $arStr[3] );
				$datatotal = mysql_real_escape_string( $arStr[4] );
				$percent = mysql_real_escape_string( $arStr[5] );
				
				$sql = "UPDATE 
						tasks 
					SET 
						taskBPM = '$bpm',
						taskTimeElapsed = '$elapsed', 
						taskTimeRemaining = '$remaining',
						taskDataCopied = '$datacopied',
						taskPercentText = '$percent',
						taskDataTotal = '$datatotal'
					WHERE
						taskID = '$jobid'";
				
				if ( ! mysql_query( $sql, $conn ) )
					die( mysql_error() );
			}
			else
				die( _("invalid entry count").": " . count( $arStr ) );
		}
	}
}
else
	echo _("Invalid MAC Address");
?>
