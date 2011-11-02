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

if ( $_GET["rmsgid"] != null && is_numeric( $_GET["rmsgid"] ) )
{
	$rmid = mysql_real_escape_string( $_GET["rmsgid"] );
	if ( $_GET["confirm"] != "1" )
	{
		$sql = "select * from nfsGroups where ngID = '$rmid'";
		$res = mysql_query( $sql, $conn ) or die( mysql_error() );
		if ( $ar = mysql_fetch_array( $res ) )
		{
			?>
			<h2><?php print _("Confirm Storage Group Removal"); ?></h2>
			<?php
			echo ( "<center><table cellpadding=0 cellspacing=0 border=0 width=90%>" );
				echo ( "<tr><td>"._("Storage Group Name").":</td><td>" . $ar["ngName"] . "</td></tr>" );
				echo ( "<tr><td>"._("Storage Group Description").":</td><td>" . $ar["ngDesc"] . "</font></td></tr>" );
				echo ( "<tr><td colspan=2><center><br /><form method=\"POST\" action=\"?node=" . $_GET["node"] . "&sub=" . $_GET["sub"] . "&rmsgid=" . $_GET["rmsgid"] . "&confirm=1\"><input class=\"smaller\" type=\"submit\" value=\""._("Delete Storage Definition")."\" /></form></center></td></tr>" );				
			echo ( "</table></center>" );		
		}
	}
	else
	{
		$output = "";
		?>
		<h2><?php print _("Storage Group Removal Results"); ?></h2>
		<?php
		$sql = "DELETE FROM nfsGroups WHERE ngID = '" . $rmid . "'";
		if ( mysql_query( $sql, $conn ) )
		{
			$output .= _("Storage Group definition has been removed.")."<br />";
			lg( _("Storage Group deleted")." :: $rmid" );				
		}
		else
			$output .= mysql_error();
			
		echo $output;
	}
	echo ( "</div>" );	
}
else
{
	if ( $_POST["update"] == "1" && is_numeric( $_POST["storagegroupid"] ) )
	{
		if ( ! doesStorageGroupExist( $conn, $_POST["name"], $_POST["storagegroupid"] ) )
		{
			$ngid = mysql_real_escape_string( $_POST["storagegroupid"] );
			$name = mysql_real_escape_string( $_POST["name"] );
			$description = mysql_real_escape_string( $_POST["description"] );
			$sql = "UPDATE nfsGroups set ngName = '$name', ngDesc = '$description' WHERE ngID = '$ngid'";
			if ( mysql_query( $sql, $conn ) )
			{
				msgBox(_('Storage Group Updated') . ": $name");
				lg(_('Storage Group Updated') . ": $name");
			}
			else
			{
				msgBox(_('Failed to Storage Group'));
				lg(_('Failed to update Storage Group') . ": $name, Error: " . mysql_error());
			}
		}	
	}
	
	?>
	<h2><?php print _("Edit Storage Group Definition"); ?></h2>
	<?php
	$sql = "select * from nfsGroups where ngID = '" . mysql_real_escape_string( $_GET["storagegroupid"] ) . "'";
	$res = mysql_query( $sql, $conn ) or die( mysql_error() );
	if ( $ar = mysql_fetch_array( $res ) )
	{
		echo ( "<center>" );
		if ( $_GET["tab"] == "gen" || $_GET["tab"] == "" )
		{
			echo ( "<form method=\"POST\" action=\"?node=" . $_GET["node"] . "&sub=" . $_GET["sub"] . "&storagegroupid=" . $_GET["storagegroupid"] . "\">" );
			echo ( "<table cellpadding=0 cellspacing=0 border=0 width=90%>" );
				echo ( "<tr><td>"._("Storage Group Name").":</td><td><input class=\"smaller\" type=\"text\" name=\"name\" value=\"" . $ar["ngName"] . "\" /></td></tr>" );
				echo ( "<tr><td>"._("Storage Group Description").":</td><td><textarea class=\"smaller\" name=\"description\" rows=\"5\" cols=\"65\">" . $ar["ngDesc"] . "</textarea></td></tr>" );
				echo ( "<tr><td colspan=2><font><center><br /><input type=\"hidden\" name=\"update\" value=\"1\" /><input type=\"hidden\" name=\"storagegroupid\" value=\"" . $_GET["storagegroupid"] . "\" /><input class=\"smaller\" type=\"submit\" value=\""._("Update")."\" /></center></font></td></tr>" );				
			echo ( "</table>" );
			echo ( "</form>" );
		}
		else if ( $_GET["tab"] == "delete" )
		{
			echo ( "<p>"._("Are you sure you would like to remove this Storage Group?")."</p>" );
			echo ( "<p><a href=\"?node=" . $_GET["node"] . "&sub=" . $_GET["sub"] . "&rmsgid=" . $_GET["storagegroupid"] . "&storagegroupid=" . $_GET["storagegroupid"] . "\"><img class=\"link\" src=\"images/delete.png\"></a></p>" );
		}
		echo ( "</center>" );
	}
}