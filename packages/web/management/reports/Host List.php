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
<h2><?php print _("Host Listing Export")."  <a href=\"export.php?type=csv\" target=\"_blank\"><img class=\"noBorder\" src=\"images/csv.png\" /></a>"; ?></h2>
<?php

$report = new ReportMaker();
$report->addCSVCell(_("Host ID"));
$report->addCSVCell(_("Host Name"));		
$report->addCSVCell(_("Host Description"));		
$report->addCSVCell(_("Host IP"));				
$report->addCSVCell(_("Image ID"));
$report->addCSVCell(_("Host Create Date"));
$report->addCSVCell(_("Host MAC"));
$report->addCSVCell(_("Host OS ID"));
$report->addCSVCell(_("Host OS Name"));
$report->addCSVCell(_("AD"));
$report->addCSVCell(_("AD Domain"));
$report->addCSVCell(_("AD OU"));
$report->addCSVCell(_("AD user"));
$report->addCSVCell(_("AD Pass"));
$report->addCSVCell(_("Printer Level"));
$report->addCSVCell(_("Kernel Args"));
$report->addCSVCell(_("Kernel"));
$report->addCSVCell(_("HD Device"));
$report->addCSVCell(_("Image Name"));		
$report->addCSVCell(_("Image Desc"));
$report->endCSVLine();												
	
$sql = "SELECT 
		* 
	FROM 
		hosts  
		left outer join images on ( hostImage = imageID )
		left outer join supportedOS on ( hostOS = osID )";
$res = mysql_query( $sql, $conn ) or die( mysql_error() );

while ( $ar = mysql_fetch_array( $res ) )
{																																				
	$report->addCSVCell($ar["hostID"]);
	$report->addCSVCell($ar["hostName"]);
	$report->addCSVCell($ar["hostDesc"]);		
	$report->addCSVCell($ar["hostIP"]);			
	$report->addCSVCell($ar["imageID"]);
	$report->addCSVCell($ar["hostCreateDate"]);
	$report->addCSVCell($ar["hostMAC"]);
	$report->addCSVCell($ar["hostOS"]);
	$report->addCSVCell($ar["osName"]);
	$report->addCSVCell($ar["hostUseAD"]);
	$report->addCSVCell($ar["hostADDomain"]);
	$report->addCSVCell($ar["hostADOU"]);
	$report->addCSVCell($ar["hostADUser"]);
	$report->addCSVCell($ar["hostADPass"]);
	$report->addCSVCell($ar["hostPrinterLevel"]);
	$report->addCSVCell($ar["hostKernelArgs"]);
	$report->addCSVCell($ar["hostKernel"]);
	$report->addCSVCell($ar["hostDevice"]);
	$report->addCSVCell($ar["imageName"]);
	$report->addCSVCell($ar["imageDesc"]);	
	$report->endCSVLine();
}
echo ( "<p>"._("Reporting Complete!")."</p>" );

$_SESSION["foglastreport"] = serialize( $report );