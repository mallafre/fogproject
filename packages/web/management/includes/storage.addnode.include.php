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


$blShow = true;

?>
<h2><?php print _("Add New Storage Node Definition"); ?></h2>
<?php

echo ( "<center>" );
	if ( $_POST["add"] == "1"  )
	{
		if ( ! doesStorageNodeExist( $conn, $_POST["name"] ) )
		{
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
	
			if ( ($ismaster == "1" && $_POST["confirm"] == "1") || $ismaster == "0" )
			{
				$sql = "INSERT INTO 
						nfsGroupMembers (ngmMemberName, ngmMemberDescription, ngmIsMasterNode, ngmGroupID, ngmRootPath, ngmIsEnabled, ngmHostname, ngmMaxClients, ngmUser, ngmPass)
					VALUES
						('$name', '$description', '$ismaster', '$storagegroup', '$imageloc', '$isenabled', '$ip', '$maxclients', '$muser', '$mpass' )";
				if ( mysql_query( $sql, $conn ) )
				{
					if ( $ismaster == "1" && $storagegroup != null  )
					{
						// only one master per group, remove previous master.
						$lastid = mysql_insert_id($conn);
						$sql = "UPDATE nfsGroupMembers SET ngmIsMasterNode = '0' WHERE ngmGroupID = '$storagegroup' and ngmID <> '$lastid'";
						if ( ! mysql_query( $sql, $conn ) )
							die( mysql_error() );
			
					}
					msgBox( _("Storage node created.")."<br />"._("You may now add another.") );
					lg( _("node Added")." :: $name" );
				}
				else
				{
					msgBox( _("Failed to update Storage Node.") );
					lg( _("Failed to update Storage Node")." :: $name " . mysql_error()  );
				}
			}
			else if ( $ismaster == "1" )
			{
				$blShow = false;
				echo ("<div class=\"warn\">");
					echo _("You have chosen to set this node as the master node in this storage group.  ")."<b>"._("Caution").": </b> "._("This is a very dangerous action, and should only be done if you known what you are doing.  Settings this node as master could potentially wipe out all images on all other nodes in this storage group.")."<p><strong>"._("Are you sure you wish to do this?")."</strong></p>";
					echo ( "<form action=\"?node=" . $_GET["node"] . "&sub=" . $_GET["sub"] . "&storagenodeid=" . $_GET["storagenodeid"] . "\" method=\"post\">" );
					echo ( "<input type=\"hidden\" name=\"add\" value=\"1\" />" );
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
					echo ( "<input type=\"hidden\" name=\"confirm\" value=\"1\" />" );												
					echo ( "<input type=\"submit\" value=\""._("Yes, make the node master.")."\" />&nbsp;&nbsp;&nbsp;&nbsp;<input type=\"button\" onclick=\"javascript:history.back(-1)\" value=\""._("No, don't add node as master.")."\" /></form>" );
				echo ("</div>" );
			}
		}	
	}	

	if ( $blShow )
	{
		echo ( "<form method=\"POST\" action=\"?node=" . $_GET["node"] . "&sub=" . $_GET["sub"] . "&storagenodeid=" . $_GET["storagenodeid"] . "\">" );
		echo ( "<table cellpadding=0 cellspacing=0 border=0 width=90%>" );
			echo ( "<tr><td>"._("Storage Node Name").":</td><td><input class=\"smaller\" type=\"text\" name=\"name\" value=\"" . $ar["ngmMemberName"] . "\" /></td></tr>" );
			echo ( "<tr><td>"._("Storage Node Description").":</td><td><textarea class=\"smaller\" name=\"description\" rows=\"5\" cols=\"65\">" . $ar["ngmMemberDescription"] . "</textarea></td></tr>" );
			echo ( "<tr><td>"._("IP Address").":</td><td><input class=\"smaller\" type=\"text\" name=\"ip\" value=\"" . $ar["ngmHostname"] . "\" /></td></tr>" );				
			echo ( "<tr><td>"._("Max Clients").":</td><td><input class=\"smaller\" type=\"text\" name=\"clients\" value=\"" . $ar["ngmMaxClients"] . "\" /></td></tr>" );				
			echo ( "<tr><td>"._("Is Master Node").":</td><td><input type=\"checkbox\" name=\"ismaster\" $checked />&nbsp;&nbsp;<span class=\"icon icon-help hand\" title=\"" . _("Use extreme caution with this setting!  This setting, if used incorrectly could potentially wipe out all of your images stored on all current storage nodes.  The 'Is Master Node' setting defines which node is the distributor of the images.  If you add a blank node, meaning a node that has no images on it, and set it to master, it will distribute its store, which is empty, to all hosts in the group.") . "\"></span></td></tr>" );	
			echo ( "<tr><td>"._("Storage Group").":</td><td>" . $FOGCore->getClass('StorageGroupManager')->buildSelectBox() . "</td></tr>" );
			echo ( "<tr><td>"._("Image Location").":</td><td><input class=\"smaller\" type=\"text\" name=\"imageloc\" value=\"" . $ar["ngmRootPath"] . "\" /></td></tr>" );														
			echo ( "<tr><td>"._("Is Enabled").":</td><td><input type=\"checkbox\" name=\"isenabled\" checked=\"checked\" /></td></tr>" );					
			echo ( "<tr><td>"._("Management Username").":</td><td><input class=\"smaller\" type=\"text\" name=\"username\" value=\"" . $ar["ngmUser"] . "\" /></td></tr>" );				
			echo ( "<tr><td>"._("Management Password").":</td><td><input class=\"smaller\" type=\"text\" name=\"password\" value=\"" . $ar["ngmPass"] . "\" /></td></tr>" );								
			echo ( "<tr><td colspan=2><font><center><input type=\"hidden\" name=\"add\" value=\"1\" /><input class=\"smaller\" type=\"submit\" value=\""._("Add")."\" /></center></font></td></tr>" );				
		echo ( "</table>" );
		echo ( "</form>" );
	}
echo ( "</center>" );