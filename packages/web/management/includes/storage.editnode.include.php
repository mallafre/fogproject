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

if ( $_GET["rmsnid"] != null && is_numeric( $_GET["rmsnid"] ) )
{
	$rmid = mysql_real_escape_string( $_GET["rmsnid"] );
	if ( $_GET["confirm"] != "1" )
	{
		$sql = "select * from nfsGroupMembers where ngmID = '$rmid'";
		$res = mysql_query( $sql, $conn ) or die( mysql_error() );
		if ( $ar = mysql_fetch_array( $res ) )
		{
			?>
			<h2><?php print _("Confirm Storage Node Removal"); ?></h2>
			<?php
			echo ( "<center><table cellpadding=0 cellspacing=0 border=0 width=90%>" );
				echo ( "<tr><td>"._("Storage Node Name").":</td><td>" . $ar["ngmMemberName"] . "</td></tr>" );
				echo ( "<tr><td>"._("Storage Node Description").":</td><td>" . $ar["ngmMemberDescription"] . "</td></tr>" );
				echo ( "<tr><td colspan=2><center><br /><form method=\"POST\" action=\"?node=" . $_GET["node"] . "&sub=" . $_GET["sub"] . "&rmsnid=" . $_GET["rmsnid"] . "&confirm=1\"><input type=\"submit\" value=\""._("Delete Storage Node Definition")."\" /></form></center></td></tr>" );				
			echo ( "</table></center>" );		
		}
	}
	else
	{
		$output = "";
		?>
		<h2><?php print _("Storage Node Removal Results"); ?></h2>
		<?php
		$sql = "DELETE FROM nfsGroupMembers WHERE ngmID = '" . $rmid . "'";
		if (mysql_query($sql, $conn))
		{
			$output .= _("Storage Node definition has been removed");
			lg(_("Storage Group deleted") . ": $rmid");				
		}
		else
		{
			$output .= mysql_error();
		}
			
		echo $output;
	}
}
else
{
	$blShow = true;

	?>
	<h2><?php print _("Edit Storage Node Definition"); ?></h2>
	<?php
	
	if ( $_POST["update"] == "1" && is_numeric( $_POST["storagenodeid"] ) )
	{
		if ( ! doesStorageNodeExist( $conn, $_POST["name"], $_POST["storagenodeid"] ) )
		{
			$ngmid = mysql_real_escape_string( $_POST["storagenodeid"] );
			$name = mysql_real_escape_string( $_POST["name"] );
			$description = mysql_real_escape_string( $_POST["description"] );
			$ip = mysql_real_escape_string( $_POST["ip"] );
			$maxclients = mysql_real_escape_string( $_POST["clients"] );
			$ismaster = "0";
			if ( $_POST["ismaster"] == "on" )
				$ismaster = "1";
			$storagegroup = mysql_real_escape_string( $_POST["storagegroup"] );
			$imageloc = mysql_real_escape_string( $_POST["imageloc"] );
			if ( ! endsWith( $imageloc, "/" ) && $imageloc != null )
				$imageloc .= "/";			
			$isenabled = "0";
			if ( $_POST["isenabled"] == "on" )
				$isenabled = "1";	
			$muser = mysql_real_escape_string( $_POST["username"] );
			$mpass = mysql_real_escape_string( $_POST["password"] );
			
			if ( is_numeric( $ngmid ) )
			{
				// detect a change in master node status
				$sql = "SELECT
						ngmIsMasterNode
					FROM 
						nfsGroupMembers
					WHERE 
						ngmID = '$ngmid'";
				$res = mysql_query( $sql, $conn ) or die( mysql_error() );
				$blCurIsMast;
				while( $ar = mysql_fetch_array( $res ) )
				{
					$blCurIsMast = ($ar["ngmIsMasterNode"] == "1");
				}
				
				$blOkToUpdate = false;
				if ( $ismaster == "0" )
					$blOkToUpdate = true;
				else
				{
					if ( $blCurIsMast && ( $ismaster == "1" ) ) 
						$blOkToUpdate = true;
					else if ( ! $blCurIsMast && ( $ismaster == "0" ) )
						$blOkToUpdate = true;
				}
				
				if( $_POST["confirm"] == "1"  ) $blOkToUpdate = true;
				
				if ( $blOkToUpdate )
				{
					$sql = "UPDATE 
							nfsGroupMembers 
						SET 
							ngmMemberName = '$name', 
							ngmMemberDescription = '$description', 
							ngmIsMasterNode = '$ismaster', 
							ngmGroupID = '$storagegroup', 
							ngmRootPath = '$imageloc', 
							ngmIsEnabled = '$isenabled', 
							ngmHostname = '$ip', 
							ngmMaxClients = '$maxclients', 
							ngmUser = '$muser',
							ngmPass = '$mpass'
						WHERE 
							ngmID = '$ngmid'";

					if ( mysql_query( $sql, $conn ) )
					{
						if ( $ismaster == "1" && $storagegroup != null  )
						{				
							// only one master per group, remove previous master.
							$sql = "UPDATE nfsGroupMembers SET ngmIsMasterNode = '0' WHERE ngmGroupID = '$storagegroup' and ngmID <> '$ngmid'";
							if ( ! mysql_query( $sql, $conn ) )
								die( mysql_error() );					
						}
						msgBox(_('Storage Node Updated') . ": $name");
						lg(_('Storage Node Updated') . ": $name");
					}
					else
					{
						msgBox(_('Failed to update Storage Node'));
						lg(_('Failed to update Storage Node') . ": $name, Error: " . mysql_error());
					}
				}
				else
				{
					$blShow = false;
					echo ("<div class=\"warn\">");
						echo _("You have chosen to set this node as the master node in this storage group.")."  <b>"._("Caution").": </b> "._("This is a very dangerous action, and should only be done if you known what you are doing.  Settings this node as master could potentially wipe out all images on all other nodes in this storage group.")."<p><strong>"._("Are you sure you wish to do this?")."</strong></p>";
						echo ( "<form action=\"?node=" . $_GET["node"] . "&sub=" . $_GET["sub"] . "&storagenodeid=" . $_GET["storagenodeid"] . "\" method=\"post\">" );
						echo ( "<input type=\"hidden\" name=\"update\" value=\"1\" />" );
						echo ( "<input type=\"hidden\" name=\"name\" value=\"" . $_POST["name"] . "\" />" );
						echo ( "<input type=\"hidden\" name=\"description\" value=\"" . $_POST["description"] . "\" />" );						
						echo ( "<input type=\"hidden\" name=\"ip\" value=\"" . $_POST["ip"] . "\" />" );						
						echo ( "<input type=\"hidden\" name=\"clients\" value=\"" . $_POST["clients"] . "\" />" );						
						echo ( "<input type=\"hidden\" name=\"ismaster\" value=\"" . $_POST["ismaster"] . "\" />" );						
						echo ( "<input type=\"hidden\" name=\"storagegroup\" value=\"" . $_POST["storagegroup"] . "\" />" );						
						echo ( "<input type=\"hidden\" name=\"imageloc\" value=\"" . $_POST["imageloc"] . "\" />" );						
						echo ( "<input type=\"hidden\" name=\"isenabled\" value=\"" . $_POST["isenabled"] . "\" />" );
						echo ( "<input type=\"hidden\" name=\"username\" value=\"" . $_POST["username"] . "\" />" );						
						echo ( "<input type=\"hidden\" name=\"password\" value=\"" . $_POST["password"] . "\" />" );												
						echo ( "<input type=\"hidden\" name=\"storagenodeid\" value=\"" . $_POST["storagenodeid"] . "\" />" );
						echo ( "<input type=\"hidden\" name=\"confirm\" value=\"1\" />" );												
						echo ( "<input type=\"submit\" value=\""._("Yes, make the node master.")."\" />&nbsp;&nbsp;&nbsp;&nbsp;<input type=\"button\" onclick=\"javascript:history.back(-1)\" value=\""._("No, don't add node as master.")."\" /></form>" );
					echo ("</div>" );				
				}
			}
			else
				msgBox( _("Failed to update storage node.") );
		}	
	}	
	
	if ( $blShow )
	{
		$sql = "select * from nfsGroupMembers where ngmID = '" . mysql_real_escape_string( $_GET["storagenodeid"] ) . "'";
		$res = mysql_query( $sql, $conn ) or die( mysql_error() );
		if ( $ar = mysql_fetch_array( $res ) )
		{
			echo ( "<center>" );
			if ( $_GET["tab"] == "gen" || $_GET["tab"] == "" )
			{
				echo ( "<form method=\"POST\" action=\"?node=" . $_GET["node"] . "&sub=" . $_GET["sub"] . "&storagenodeid=" . $_GET["storagenodeid"] . "\">" );
				echo ( "<table cellpadding=0 cellspacing=0 border=0 width=90%>" );
					echo ( "<tr><td>"._("Storage Node Name").":</td><td><input type=\"text\" name=\"name\" value=\"" . $ar["ngmMemberName"] . "\" /></td></tr>" );
					echo ( "<tr><td>"._("Storage Node Description").":</td><td><textarea name=\"description\" rows=\"5\" cols=\"65\">" . $ar["ngmMemberDescription"] . "</textarea></td></tr>" );
					$checked = "";
					if ( $ar["ngmIsMasterNode"] == "1" )
					{
						$checked="checked=\"checked\"";
					}
					echo ( "<tr><td>"._("IP Address").":</td><td><input type=\"text\" name=\"ip\" value=\"" . $ar["ngmHostname"] . "\" /></td></tr>" );				
					echo ( "<tr><td>"._("Max Clients").":</td><td><input type=\"text\" name=\"clients\" value=\"" . $ar["ngmMaxClients"] . "\" /></td></tr>" );				
					echo ( "<tr><td>"._("Is Master Node").":</td><td><input type=\"checkbox\" name=\"ismaster\" $checked />&nbsp;&nbsp;<span class=\"icon icon-help hand\" title=\"" . _("Use extreme caution with this setting!  This setting, if used incorrectly could potentially wipe out all of your images stored on all current storage nodes.  The 'Is Master Node' setting defines which node is the distributor of the images.  If you add a blank node, meaning a node that has no images on it, and set it to master, it will distribute its store, which is empty, to all hosts in the group.") . "\"></span></td></tr>" );	
				
					echo ( "<tr><td>"._("Storage Group").":</td><td>" . getNFSGroupDropDown( $conn, "storagegroup", $ar["ngmGroupID"] ) . "</td></tr>" );
				
					echo ( "<tr><td>"._("Image Location").":</td><td><input type=\"text\" name=\"imageloc\" value=\"" . $ar["ngmRootPath"] . "\" /></td></tr>" );							
					$echecked = "";
					if ( $ar["ngmIsEnabled"] == "1" )
					{
						$echecked="checked=\"checked\"";
					}				
				
					echo ( "<tr><td>"._("Is Enabled").":</td><td><input type=\"checkbox\" name=\"isenabled\" $echecked /></td></tr>" );					
					echo ( "<tr><td>"._("Management Username").":</td><td><input type=\"text\" name=\"username\" value=\"" . $ar["ngmUser"] . "\" /></td></tr>" );				
					echo ( "<tr><td>"._("Management Password").":</td><td><input type=\"text\" name=\"password\" value=\"" . $ar["ngmPass"] . "\" /></td></tr>" );								
					echo ( "<tr><td colspan=2><font><center><br /><input type=\"hidden\" name=\"update\" value=\"1\" /><input type=\"hidden\" name=\"storagenodeid\" value=\"" . $_GET["storagenodeid"] . "\" /><input type=\"submit\" value=\""._("Update")."\" /></center></font></td></tr>" );				
				echo ( "</table>" );
				echo ( "</form>" );
			}
			else if ( $_GET["tab"] == "delete" )
			{
				echo ( "<p>"._("Are you sure you would like to remove this Storage Node?")."</p>" );
				echo ( "<p><a href=\"?node=" . $_GET["node"] . "&sub=" . $_GET["sub"] . "&rmsnid=" . $_GET["storagenodeid"] . "&storagenodeid=" . $_GET["storagenodeid"] . "\"><img class=\"link\" src=\"images/delete.png\"></a></p>" );
			}
			echo ( "</center>" );
		}
	}
}