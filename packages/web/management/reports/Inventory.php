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

?>
<h2><?php print _("Full Inventory Export")."  <a href=\"export.php?type=csv\" target=\"_blank\"><img class=\"noBorder\" src=\"images/csv.png\" /></a>"; ?></h2>
<?php

$report = new ReportMaker();
$report->addCSVCell(_("Host Name"));
$report->addCSVCell(_("Host IP"));
$report->addCSVCell(_("Host MAC"));		
$report->addCSVCell(_("Host Description"));		
$report->addCSVCell(_("Image ID"));
$report->addCSVCell(_("Image Name"));
$report->addCSVCell(_("Image Desc"));
$report->addCSVCell(_("OS Name"));
$report->addCSVCell(_("Inventory ID"));
$report->addCSVCell(_("Primary User"));
$report->addCSVCell(_("Other Tag 1"));
$report->addCSVCell(_("Other Tag 2"));		
$report->addCSVCell(_("Inventory create date"));
$report->addCSVCell(_("System Man"));
$report->addCSVCell(_("System Product"));
$report->addCSVCell(_("System Version"));
$report->addCSVCell(_("System Serial"));
$report->addCSVCell(_("System Type"));
$report->addCSVCell(_("BIOS Version"));
$report->addCSVCell(_("BIOS Vendor"));
$report->addCSVCell(_("BIOS Date"));
$report->addCSVCell(_("MB Man"));
$report->addCSVCell(_("MB name"));
$report->addCSVCell(_("MB Ver"));
$report->addCSVCell(_("MB Serial"));
$report->addCSVCell(_("MB Asset"));
$report->addCSVCell(_("CPU Man"));
$report->addCSVCell(_("CPU Version"));
$report->addCSVCell(_("CPU Speed"));
$report->addCSVCell(_("CPU Max Speed"));
$report->addCSVCell(_("Memory"));
$report->addCSVCell(_("HD Model"));
$report->addCSVCell(_("HD Firmware"));
$report->addCSVCell(_("HD Serial"));
$report->addCSVCell(_("Chassis Man"));
$report->addCSVCell(_("Chassis Version"));
$report->addCSVCell(_("Chassis Serial"));
$report->addCSVCell(_("Chassis Asset"));
$report->endCSVLine();												
	
$sql = "SELECT 
		* 
	FROM 
		hosts  
		inner join inventory on ( hosts.hostID = inventory.iHostID )
		left outer join images on ( hostImage = imageID )
		left outer join supportedOS on ( hostOS = osID )";
$res = mysql_query( $sql, $conn ) or die( mysql_error() );

while ( $ar = mysql_fetch_array( $res ) )
{																																					
	$report->addCSVCell($ar["hostName"]);
	$report->addCSVCell($ar["hostIP"]);
	$report->addCSVCell($ar["hostMAC"]);		
	$report->addCSVCell($ar["hostDesc"]);		
	$report->addCSVCell($ar["imageID"]);
	$report->addCSVCell($ar["imageName"]);
	$report->addCSVCell($ar["imageDesc"]);					
	$report->addCSVCell($ar["osName"]);
	$report->addCSVCell($ar["iID"]);
	$report->addCSVCell($ar["iPrimaryUser"]);
	$report->addCSVCell($ar["iOtherTag"]);
	$report->addCSVCell($ar["iOtherTag1"]);		
	$report->addCSVCell($ar["iCreateDate"]);
	$report->addCSVCell($ar["iSysman"]);
	$report->addCSVCell($ar["iSysproduct"]);
	$report->addCSVCell($ar["iSysversion"]);
	$report->addCSVCell($ar["iSysserial"]);
	$report->addCSVCell($ar["iSystype"]);
	$report->addCSVCell($ar["iBiosversion"]);
	$report->addCSVCell($ar["iBiosvendor"]);
	$report->addCSVCell($ar["iBiosdate"]);
	$report->addCSVCell($ar["iMbman"]);
	$report->addCSVCell($ar["iMbproductname"]);
	$report->addCSVCell($ar["iMbversion"]);
	$report->addCSVCell($ar["iMbserial"]);
	$report->addCSVCell($ar["iMbasset"]);
	$report->addCSVCell($ar["iCpuman"]);
	$report->addCSVCell($ar["iCpuversion"]);
	$report->addCSVCell($ar["iCpucurrent"]);
	$report->addCSVCell($ar["iCpumax"]);
	$report->addCSVCell($ar["iMem"]);
	$report->addCSVCell($ar["iHdmodel"]);
	$report->addCSVCell($ar["iHdfirmware"]);
	$report->addCSVCell($ar["iHdserial"]);
	$report->addCSVCell($ar["iCaseman"]);
	$report->addCSVCell($ar["iCasever"]);
	$report->addCSVCell($ar["iCaseserial"]);
	$report->addCSVCell($ar["iCaseasset"]);
	$report->endCSVLine();						
}
echo ( "<p>"._("Reporting Complete!")."</p>" );

$_SESSION["foglastreport"] = serialize( $report );
