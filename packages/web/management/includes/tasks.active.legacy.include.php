<?php
/*
 *  FOG is a computer imaging solution.
 *  Copyright (C) 2007  Chuck Syperski & Jian Zhang
 *
 *   This program is free software: you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation, either version 3 of the License, or
 *   (at your option) any later version.
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
	
if ( IS_INCLUDED !== true ) die( _("Unable to load system configuration information.") );

if ( $_GET[rmtask] != null && is_numeric($_GET[rmtask]) )
{
	if ( ! ftpDelete( $GLOBALS['FOGCore']->getSetting( "FOG_TFTP_PXE_CONFIG_DIR" ) . "01-" . str_replace ( ":", "-", strtolower($_GET[mac]) ) ) )
	{
		msgBox( _("Unable to delete PXE file") );
	}
	
	$sql = "delete from tasks where taskID = '" . mysql_real_escape_string( $_GET[rmtask] ) . "' limit 1";
	if ( mysql_query( $sql, $conn ) )
	{
		msgBox( _("Task removed, but if the task was in progress or the computer already booted to the Linux Image, you will need to reboot it!") );
		lg( _("Task deleted")." :: $_GET[rmtask]" );
	}
	else
		criticalError( mysql_error(), _("FOG :: Database error!") );
}

if ( $_GET["forcetask"] != null && is_numeric($_GET["forcetask"]) )
{
	$sql = "update tasks set taskForce = '1' where taskID = '" . mysql_real_escape_string( $_GET["forcetask"] ) . "'";
	if ( mysql_query( $sql, $conn ) )
	{
		msgBox( _("Task updated to force!") );
		lg( _("Task set to Force")." :: $_GET[forcetask]" );	
	}
	else
		criticalError( mysql_error(), _("FOG :: Database error!") );
}

?>
<h2><?php print _("All Active Tasks"); ?></h2>
<?php

$sql = "select 
		* 
		from tasks 
		inner join hosts on (taskHostID = hostID)
		left outer join images on (hostImage = imageID )
		where taskStateID in (0,1) order by taskCreateTime, taskName";	
$res = mysql_query( $sql, $conn ) or die( mysql_error() );
if ( mysql_num_rows( $res ) > 0 )
{

	echo ( "<center><table class=\"taskTable\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=100%>" );
	$cnt = 0;
	echo ( "<tr class=\"header\"><td>Force</td><td>"._("Task Name")."</td><td>"._("Hostname")."</td><td>"._("MAC")."</td><td>"._("Start Time")."</td><td>"._("Type")."</td><td>"._("State")."</td><td>"._("Kill")."</td></tr>" );
	while( $ar = mysql_fetch_array( $res ) )
	{
		$bgcolor = "alt1";
		if ( $cnt++ % 2 == 0 ) $bgcolor = "alt2";
		
		if ( $ar[iState] > 0 )
			$bgcolor = "#B8E2B6";

		$state = state2text($ar["taskStateID"]);
		if ( $ar["taskStateID"] == 0 && hasCheckedIn( $conn, $ar["taskID"] ) )
			$state = "In Line";			

		$hname = $ar["hostName"];
		
		if ( $ar["taskForce"] == "1" )
			$hname = "* " . $hname;
			
		$blAllowForce = false;	
		if ( strtolower($ar["taskType"]) == "d" || strtolower($ar["taskType"]) == "u" )
			$blAllowForce = true;
		echo ( "<tr class=\"$bgcolor\"><td>" );
		if ( $blAllowForce )
			echo ( "<a href=\"?node=" . $_GET["node"] . "&sub=" . $_GET["sub"] . "&forcetask=" . $ar["taskID"] . "&mac=" . $ar["hostMAC"] ."\"><img src=\"images/force.png\" border=0 /></a>" );
			
		echo ( "</td><td>" . trimString(  $ar["taskName"], 10 ) . "</td><td>" . $hname . "</td><td>" . $ar["hostMAC"] . "</td><td>" . $ar["taskCreateTime"] . "</td><td>" . getImageAction( $ar["taskType"] ) . "</td><td>" . $state . "</td><td><a href=\"?node=$_GET[node]&sub=$_GET[sub]&rmtask=$ar[taskID]&mac=$ar[hostMAC]\"><span class=\"icon icon-kill\" title=\"Kill task\"></span></a></td></tr>" );
		
		if ( strtolower($ar["taskType"]) == "d" || strtolower($ar["taskType"]) == "u" )
		{
			$percent = trim($ar["taskPercentText"]);
			if ( is_numeric( $percent ) || $percent == null )
			{

				$scaled = round(( $percent * 150 ) / 100, 2);
				
				$bpm = trim($ar["taskBPM"]);
				$elapsed = trim( $ar["taskTimeElapsed"] );
				$remaining = trim( $ar["taskTimeRemaining"] );
				$copied = trim( $ar["taskDataCopied"] );
				$total = trim( $ar["taskDataTotal"] );
				
				
				
				$datastring = "";
				$percentstring = "";
				$elapsedstring = "";
				$speedstring = ""; 
				
				if ( $percent != null )
				{
					$datastring = "($copied/$total)";
					$percentstring = "" . $percent . "%";
					$elapsedstring = "($elapsed/$remaining)";
					$speedstring = "<strong>"._("Speed").":</strong> $bpm /min.";
				}
				else
					$speedstring = _("Waiting for host to check in...");
				
				echo ( "<tr class=\"$bgcolor\"><td colspan=\"8\"><div class=\"taskpb\"><img width=\"" . $scaled . "px\" height=\"15px\" src=\"./images/pb.png\" /></div>" . $percentstring . " " . $speedstring . " " . $elapsedstring . " " . $datastring ."</td></tr>" );			
			}
		}
	}
	echo ( "</table></center>" );
} 
else
{
	echo ( "<center>"._("No Active Tasks found")."</center>" );
}