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
	<h2><?php print _("FOG Unicast Imaging Log")." <a href=\"export.php?type=csv\" target=\"_blank\"><img class=\"noBorder\" src=\"images/csv.png\" /></a> <a href=\"export.php?type=pdf\" target=\"_blank\"><img class=\"noBorder\" src=\"images/pdf.png\" /></a>"; ?></h2>
	<?php

	$report = new ReportMaker();
	
	$dte1 = mysql_real_escape_string( $_POST["date1"] );
	$dte2 = mysql_real_escape_string( $_POST["date2"] );
	
	$sql = "SELECT 
			*,
			DATE(ilStartTime) as startdate,
			DATE(ilFinishTime) as finishdate,
			TIME(ilStartTime) as starttime,
			TIME(ilFinishTime) as finishtime,
			TIMEDIFF(ilFinishTime,ilStartTime) as timediff
		FROM 
			( SELECT * FROM imagingLog WHERE DATE(ilStartTime) >= DATE('" . $dte1 . "') and DATE(ilFinishTime) <= DATE('" . $dte2 . "') ) imagingLog
			LEFT OUTER JOIN hosts on ( ilHostID = hosts.hostID )
			LEFT OUTER JOIN images on ( hostImage = imageID )
		ORDER BY
			ilStartTime";	

	$report->appendHTML("<table cellpadding=0 cellspacing=0 border=0 width=100%>");
	$report->appendHTML( "<tr bgcolor=\"#BDBDBD\"><td><b>&nbsp;"._("Host")."</b></td><td><b>&nbsp;"._("Start Date")."</b></font></td><td><b>&nbsp;"._("Start Time")."</b></font></td><td><b>&nbsp;"._("End Date")."</b></td><td><b>&nbsp;"._("End Time")."</b></td><td><b>&nbsp;"._("Duration")."</b></td><td><b>&nbsp;"._("Image Name")."</b></td></tr>");		
		
	$report->addCSVCell( _("Host ID") );
	$report->addCSVCell( _("Host Name") );
	$report->addCSVCell( _("Host MAC") );
	$report->addCSVCell( _("Host Desc") );
	$report->addCSVCell( _("Image Name") );
	$report->addCSVCell( _("Image Path") );
	$report->addCSVCell( _("Start Date") );
	$report->addCSVCell( _("Start Time") );
	$report->addCSVCell( _("End Date") );
	$report->addCSVCell( _("End Time") );					
	$report->addCSVCell( _("Duration") );
	$report->endCSVLine();												
		

	$res = mysql_query( $sql, $conn ) or die( mysql_error() );
	if ( mysql_num_rows( $res ) > 0 )
	{
		$cnt = 0;
		while( $ar = mysql_fetch_array( $res ) )
		{					
			$bg = "";
			if ( $cnt++ % 2 == 0 ) $bg = "#E7E7E7";
			$report->appendHTML("<tr bgcolor=\"$bg\"><td>&nbsp;" . $ar["hostName"] . "</td><td>&nbsp;" . $ar["startdate"] . "</td><td>&nbsp;" . $ar["starttime"] . "</td><td>&nbsp;" . $ar["finishdate"] . "</td><td>&nbsp;" . $ar["finishtime"] . "</td><td>&nbsp;" .  $ar["timediff"] . "</td><td>&nbsp;" .  $ar["ilImageName"] . "</td></tr>"  );		
			
			$report->addCSVCell( $ar["hostID"] );
			$report->addCSVCell( $ar["hostName"] );
			$report->addCSVCell( $ar["hostMAC"] );
			$report->addCSVCell( $ar["hostDesc"] );
			$report->addCSVCell($ar["ilImageName"]);
			$report->addCSVCell($ar["imagePath"]);
			$report->addCSVCell($ar["startdate"]);
			$report->addCSVCell( $ar["starttime"] );
			$report->addCSVCell($ar["finishdate"]);
			$report->addCSVCell($ar["finishtime"]);					
			$report->addCSVCell($ar["timediff"]);

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
	<h2><?php print _("FOG Unicast Imaging Log - Select Date Range"); ?></h2>
	<?php
	$sql = "SELECT 
				DATE(ilStartTime) as dte 
			FROM 
				imagingLog 
			GROUP BY 
				DATE(ilStartTime) 
			ORDER BY 
				DATE(ilStartTime) desc";
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
			$sql = "SELECT 
						DATE(ilFinishTime) as dte 
					FROM 
						imagingLog 
					WHERE
						DATE(ilFinishTime) <> '0000-00-00'
					GROUP BY 
						DATE(ilFinishTime) 
					ORDER BY 
						DATE(ilFinishTime) desc";
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