<?php
/*
 *  FOG is a computer imaging solution.
 *  Copyright (C) 2008  Chuck Syperski & Jian Zhang
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


if ( $_POST["user"] != null  )
{
	?>
	<h2><?php print _("FOG Equipment Loan Form")."  <a href=\"export.php?type=pdf\" target=\"_blank\"><img class=\"noBorder\" src=\"images/pdf.png\" /></a>"; ?></h2>
	<?php
	$report = new ReportMaker();
	
	$id = mysql_real_escape_string( $_POST["user"] );
	
	$sql = "SELECT 
			*
		FROM 
			( SELECT * FROM inventory WHERE iID = '$id' ) inventory
			LEFT OUTER JOIN hosts on ( iHostID = hosts.hostID )";	

	$res = mysql_query( $sql, $conn ) or die( mysql_error() );
	if ( mysql_num_rows( $res ) > 0 )
	{
		$cnt = 0;
		while( $ar = mysql_fetch_array( $res ) )
		{					
			// Title
			$report->appendHTML( "<!-- "._("FOOTER CENTER")." \"" . '$PAGE' . " "._("of")." " . '$PAGES' . " - "._("Printed").": " . date("D M j G:i:s T Y") . "\" -->" );
			$report->appendHTML( "<center><h2>"._("[YOUR ORGANIZATION HERE]")."</h2></center>" );	
			$report->appendHTML( "<center><h3>"._("[sub-unit here]")."</h3></center>" );
			$report->appendHTML( "<center><h2><u>"._("PC Check-Out Agreement")."</u></h2></center>" );	

			
			// Start Personal Information
			$report->appendHTML( "<h4><u>"._("Personal Information")."</u></h4>" );
			$report->appendHTML( "<h4><b>"._("Name").": </b><u>" . $ar["iPrimaryUser"] . "</u></h4>" );
			$report->appendHTML( "<h4><b>"._("Location").": </b><u>" . _("Your Location Here") . "</u></h4>" );
			$report->appendHTML( "<h4><b>"._("Home Address").": </b>__________________________________________________________________</h4>" );
			$report->appendHTML( "<h4><b>"._("City / State / Zip").": </b>__________________________________________________________________</h4>" );					
			$report->appendHTML( "<h4><b>"._("Extension").":</b>_________________ &nbsp;&nbsp;&nbsp;<b>"._("Home Phone").":</b> (__________)_____________________________</h4>" );					
			
			// Computer Information
			$report->appendHTML( "<h4><u>"._("Computer Information")."</u></h4>" );
			$report->appendHTML( "<h4><b>"._("Serial Number / Service Tag").": </b><u>" . $ar["iSysserial"] . " / " . $ar["iCastasset"] . "_____________________</u></h4>" );
			$report->appendHTML( "<h4><b>"._("Barcode Numbers").": </b><u>" . $ar["iOtherTag"] . "   " . $ar["iOtherTag1"] . "</u>________________________</h4>" );
			$report->appendHTML( "<h4><b>"._("Date of Checkout").": </b>____________________________________________</h4>" );
			$report->appendHTML( "<h4><b>"._("Notes / Miscellaneous / Included Items").": </b></h4>" );
			$report->appendHTML( "<h4><b>_____________________________________________________________________________________________</b></h4>" );
			$report->appendHTML( "<h4><b>_____________________________________________________________________________________________</b></h4>" );
			$report->appendHTML( "<h4><b>_____________________________________________________________________________________________</b></h4>" );
			$report->appendHTML( "<hr />" );
			$report->appendHTML( "<h4><b>"._("Releasing Staff Initials").": </b>_____________________     "._("(To be released only by XXXXXXXXX)")."</h4>" );
			$report->appendHTML( "<h4>"._("I have read, understood, and agree to all the Terms and Condidtions on the following pages of this document.")."</h4>" );
			$report->appendHTML( "<br />" );
			$report->appendHTML( "<h4><b>"._("Signed").": </b>X _____________________________  "._("Date").": _________/_________/20_______</h4>" );
			$report->appendHTML( _("<!-- "._("NEW PAGE")." -->") );
			$report->appendHTML( "<!-- "._("FOOTER CENTER")." \"" . '$PAGE' . " "._("of")." " . '$PAGES' . " - "._("Printed").": " . date("D M j G:i:s T Y") . "\" -->" );
			$report->appendHTML( "<center><h3>"._("Terms and Conditions")."</h3></center>" );
			$report->appendHTML( "<hr />" );
			$report->appendHTML( "<h4>"._("Your terms and conditions here")."</h4>" );
			$report->appendHTML( "<h4><b>"._("Signed").": </b>"._("X")." _____________________________  "._("Date").": _________/_________/20_______</h4>" );
		}
	}


	echo ( "<p>"._("Your report is ready!")."</p>" );
	$_SESSION["foglastreport"] = serialize( $report );
}
else
{
	?>
	<h2><?php print _("FOG Equipment Loan Form"); ?></h2>
	<?php
	$sql = "SELECT 
			*
		FROM 
			inventory 
		ORDER BY 
			iPrimaryUser desc";
	$res = mysql_query( $sql, $conn ) or die( mysql_error());	
	echo ( "<form method=\"POST\" action=\"?node=$_GET[node]&sub=$_GET[sub]&f=$_GET[f]\">" );						
		echo ( "<p>"._("Select User")."<p>" );
		echo ( "<p>" );
		echo ( "<select name=\"user\" size=\"1\">" );	
			while( $ar = mysql_fetch_array( $res ) )
			{	
				echo ( "<option value=\"" . $ar["iID"] . "\" >" . $ar["iPrimaryUser"] . "</option>" );
			}
		echo ( "</select>" );		
		echo ( "</p>" );
		
		echo ( "<input type=\"submit\" value=\""._("Create Report")."\" />" );
	echo ( "</form>" );
}