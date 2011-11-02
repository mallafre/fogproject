<?php

if ( IS_INCLUDED !== true ) die( _("Unable to load system configuration information.") );

if ( $currentUser != null && $currentUser->isLoggedIn() )
{
	echo ( "<p id=\"contentTitle\">" );
			echo ( _("Quick Image Menu") );		
	echo ( "</p>" );	
	
	echo ( "<div class=\"center\">" );
		if ( $_GET["id"] !== null && is_numeric( $_GET["id"] ) )
		{
			$id = $_GET["id"]; 
			
			$imageMembers = getImageMemberFromHostID( $conn, $id );
			if ( $imageMembers != null )
			{	
				$tmp = "";
				if( ! createImagePackage($conn, $imageMembers, _("Mobile").": " . $imageMembers->getHostName(), $tmp ) )
				{
					echo _("Task Failed").": $tmp";
				}
				else
					echo _("Task Started!");
			}
			else
			{
				echo( _("Error:  Is an image associated with the computer?") );
			}			
		}
		else if ( $_POST["searchzip"] != null )
		{
			$srch = mysql_real_escape_string( trim($_POST["searchzip"]) );
			$sql = "SELECT
					* 
				FROM 
					hosts 
					LEFT OUTER JOIN inventory on ( hostID = iHostID )
				WHERE 
					hostName like '%$srch%' or 
					hostID like '%$srch%' or
					hostMAC like '%$srch%' or 
					iOtherTag like '%$srch%' or 
					iOtherTag1 like '%$srch%' or 
					iCaseasset like '%$srch%' or 
					iCaseserial like '%$srch%' or 
					iSysserial like '%$srch%' or 
					iMbasset like '%$srch%' or
					iPrimaryUser like '%$srch%'";
			$res = mysql_query( $sql, $conn ) or die( mysql_error() );
			
			echo ( "<table width=\"100%\" cellpadding=0 cellspacing=0>" );
			echo ( "<tr bgcolor=\"#e8e8e8\"><td><b>&nbsp;"._("ID")."</b></td><td><b>&nbsp;"._("Name")."</b></td><td><b>&nbsp;"._("Tag 1")."</b></td><td><b>&nbsp;"._("Tag 2")."</b></td><td><b>&nbsp;"._("Image")."</b></td></tr>" );
			
			if ( mysql_num_rows( $res ) > 0 )
			{
				$i = 0;
				while( $ar = mysql_fetch_array( $res ) )
				{
					( $i++ % 2 == 0 ) ? ( $bg = "#fbfbfb" ) : ($bg = "" );
					echo ( "<tr bgcolor=\"$bg\"><td>&nbsp;" . $ar["hostID"] . "</td><td>&nbsp;" . $ar["hostName"] . "</td><td>&nbsp;" . $ar["iOtherTag"] . "</td><td>&nbsp;" . $ar["iOtherTag1"] . "</td><td><a href=\"?node=" . $_GET["node"] . "&id=" . $ar["hostID"] . "\"><img class=\"task\" src=\"./images/send.png\" /></a></td></tr>" );
				}
			}
			else
			{
				echo ( "<tr bgcolor=\"#e8e8e8\"><td colspan=5>"._("No Results Found")."</td></tr>" );			
			}
			echo ( "</table>" );								
		}
		else
		{
			echo ( _("Search for Host") );
			echo ( "<form method=\"post\" action=\"?node=" . $_GET["node"] . "\" >" );
				// Called searchzip to force iPhone to go to number pad.
				echo ( "<input type=\"text\" name=\"searchzip\" /><br />" );
				echo ( "<input type=\"submit\" value=\""._("Image Host")."\" />" );
			echo ( "</form>" );
		}
	echo ( "</div>" );	
}
?>


