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

//@ini_set( "max_execution_time", 120 );
 
if ( IS_INCLUDED !== true ) die( _("Unable to load system configuration information.") );

if ( $_POST["date1"] != null && $_POST["date2"] != null )
{
	?>
	<h2><?php print _("FOG Snapin Log")." <a href=\"export.php?type=csv\" target=\"_blank\"><img class=\"noBorder\" src=\"images/csv.png\" /></a> <a href=\"export.php?type=pdf\" target=\"_blank\"><img class=\"noBorder\" src=\"images/pdf.png\" /></a>"; ?></h2>
	<?php
	$report = new ReportMaker();
	
	$dte1 = mysql_real_escape_string( $_POST["date1"] );
	$dte2 = mysql_real_escape_string( $_POST["date2"] );
	
	$sql = "SELECT 
			DATE( stCheckinDate ) as TaskCheckinDate,
			TIME( stCheckinDate ) as TaskCheckinTime, 
			stState,
			stReturnCode, 
			stReturnDetails,
			sID, 
			sName, 
			sDesc, 
			sFilePath,
			sArgs, 
			sReboot, 
			sRunWith,
			sRunWithArgs,
			DATE(sjCreateTime) as JobCreateDate,
			TIME(sjCreateTime) as JobCreateTime,
			hostID,
			hostName, 
			hostDesc,
			hostIP, 
			hostMAC
		FROM
			snapinTasks
			LEFT OUTER JOIN snapins ON (stSnapinID = snapins.sID)
			LEFT OUTER JOIN snapinJobs ON ( stJobID = snapinJobs.sjID )
			LEFT OUTER JOIN hosts ON ( snapinJobs.sjHostID = hosts.hostID )
		ORDER BY
			stCheckinDate";	

	$report->appendHTML("<table cellpadding=0 cellspacing=0 border=0 width=100%>");
	$report->appendHTML( "<tr bgcolor=\"#BDBDBD\"><td><b>&nbsp;"._("Snapin Name")."</b></td><td><b>&nbsp;"._("State")."</b></td><td><b>&nbsp;"._("Return Code")."</b></td><td><b>&nbsp;"._("Return Desc.")."</b></font></td><td><b>&nbsp;"._("Checkin Date")."</b></td><td><b>&nbsp;"._("Checkin Time")."</b></td><td><b>&nbsp;"._("Creation Date")."</b></td><td><b>&nbsp;"._("Creation Time")."</b></td><td><b>&nbsp;"._("Host Name")."</b></td><td><b>&nbsp;"._("MAC")."</b></td></tr>");		
		
	$report->addCSVCell( _("Snapin Name") );
	$report->addCSVCell( _("Snapin Description") );
	$report->addCSVCell( _("Snapin ID") );
	$report->addCSVCell( _("Snapin File Path") );			
	$report->addCSVCell( _("Snapin Args") );			
	$report->addCSVCell( _("Snapin Run With") );			
	$report->addCSVCell( _("Snapin Run With Args") );			
	$report->addCSVCell( _("Snapin State") );
	$report->addCSVCell( _("Snapin Return Code") );
	$report->addCSVCell( _("Snapin Return Description") );
	$report->addCSVCell( _("Checkin Date") );
	$report->addCSVCell( _("Checkin Time") );
	$report->addCSVCell( _("Creation Date") );
	$report->addCSVCell( _("Creation Time") );
	$report->addCSVCell( _("Host Name") );
	$report->addCSVCell( _("Host MAC") );					
	$report->addCSVCell( _("Host IP") );
	$report->addCSVCell( _("Host Description") );
	$report->endCSVLine();												
		

	$res = mysql_query( $sql, $conn ) or die( mysql_error() );
	if ( mysql_num_rows( $res ) > 0 )
	{
		$cnt = 0;
		while( $ar = mysql_fetch_array( $res ) )
		{					
			$bg = "";
			if ( $cnt++ % 2 == 0 ) $bg = "#E7E7E7";
			$report->appendHTML("<tr bgcolor=\"$bg\"><td>&nbsp;" . trimString( $ar["sName"], 20 ) . "</td><td>&nbsp;" . $ar["stState"] . "</td><td>&nbsp;" . $ar["stReturnCode"] . "</td><td>&nbsp;" . trimString($ar["stReturnDetails"], 40 ) . "</td><td>&nbsp;" . $ar["TaskCheckinDate"] . "</td><td>&nbsp;" .  $ar["TaskCheckinTime"] . "</td><td>&nbsp;" .  $ar["JobCreateDate"] . "</td><td>&nbsp;" .  $ar["JobCreateTime"] . "</td><td>&nbsp;" .  $ar["hostName"] . "</td><td>&nbsp;" .  $ar["hostMAC"] . "</td></tr>"  );		
			
			$report->addCSVCell( $ar["sName"] );
			$report->addCSVCell( $ar["sDesc"] );
			$report->addCSVCell( $ar["sID"] );
			$report->addCSVCell( $ar["sFilePath"] );			
			$report->addCSVCell( $ar["sArgs"] );			
			$report->addCSVCell( $ar["sRunWith"] );			
			$report->addCSVCell( $ar["sRunWithArgs"] );			
			$report->addCSVCell( $ar["stState"] );
			$report->addCSVCell( $ar["stReturnCode"] );
			$report->addCSVCell( $ar["stReturnDetails"] );
			$report->addCSVCell( $ar["TaskCheckinDate"] );
			$report->addCSVCell( $ar["TaskCheckinTime"] );
			$report->addCSVCell( $ar["JobCreateDate"] );
			$report->addCSVCell( $ar["JobCreateTime"] );
			$report->addCSVCell( $ar["hostName"] );
			$report->addCSVCell( $ar["hostMAC"] );					
			$report->addCSVCell( $ar["hostIP"] );
			$report->addCSVCell( $ar["hostDesc"] );

			$report->endCSVLine();						
		}
	}
	else
	{
		$report->appendHTML("<tr><td colspan=\"5\" class=\"c\">"._("No Entries Found.")."</td></tr>" );
		$report->addCSVCell(_("No Entries Found."));
		$report->endCSVLine();						
	}
	
	$report->appendHTML( "</table>" );
	$report->outputReport(ReportMaker::FOG_REPORT_HTML);
	$_SESSION["foglastreport"] = serialize( $report );
}
else
{
	?>
	<h2><?php print _("FOG Snapin Log - Select Date Range"); ?></h2>
	<?php
	$sql = "SELECT 
				DATE(stCheckinDate) as dte 
			FROM 
				snapinTasks 
			GROUP BY 
				DATE(stCheckinDate) 
			ORDER BY 
				DATE(stCheckinDate) desc";
	$res = mysql_query( $sql, $conn ) or die( mysql_error());	
	echo ( "<form method=\"POST\" name=\"hosts\" action=\"?node=$_GET[node]&sub=$_GET[sub]&f=$_GET[f]\">" );						
		echo ( "<p>"._("Select Start Date")."<p>" );
		echo ( "<p>" );
		echo ( "<select name=\"date1\" size=\"1\">" );	
			while( $ar = mysql_fetch_array( $res ) )
			{	
				echo ( "<option value=\"" . $ar["dte"] . "\" $sel>" . $ar["dte"] . "</option>" );
			}
		echo ( "</select>" );		
		echo ( "</p>" );
		
		echo ( "<p>"._("Select End Date")."<p>" );
		echo ( "<p>" );
		echo ( "<select name=\"date2\" size=\"1\">" );	
			$res = mysql_query( $sql, $conn ) or die( mysql_error());
			while( $ar = mysql_fetch_array( $res ) )
			{	
				echo ( "<option value=\"" . $ar["dte"] . "\" $sel>" . $ar["dte"] . "</option>" );
			}
		echo ( "</select>" );		
		echo ( "</p>" );
		
		echo ( "<input type=\"submit\" value=\""._("Search for Entries")."\" />" );
	echo ( "</form>" );
}