<?php
/*
 *  FOG is a computer imaging solution.
 *  Copyright (C) 2007  Chuck Syperski & Jian Zhang
 *
 *   This program is free software: you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation, either version 3 of the License, or
 *   any later version.
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

if ( $_GET["updatestatus"] == "1" )
{
	$value = "0";
	if ( $_POST["en"] == "on" )
		$value = "1";
	$sql = "UPDATE 
			globalSettings
		SET
			settingValue = '$value'
		WHERE
			settingKey = 'FOG_SERVICE_USERCLEANUP_ENABLED'";
	if ( ! mysql_query( $sql, $conn ) )
	{
		criticalError( mysql_error(), _("FOG :: Database error!") );
	}
}
else if ( $_GET["adduser"] == "1" )
{
	$usr = mysql_real_escape_string( $_POST["usr"] );
	if ( ! userCleanupUserExists( $conn, $usr ) && trim($usr) != null )
	{
		$sql = "INSERT INTO userCleanup ( ucName ) values( '$usr' )";
		if ( ! mysql_query( $sql, $conn ) )
			criticalError( mysql_error(), _("FOG :: Database error!") );
	}
	else
		msgBox( _("User Entry Already Exists.") );
}
else if ( $_GET["delid"] !== null && is_numeric( $_GET["delid"] ) )
{
	$delid = mysql_real_escape_string( $_GET["delid"] );
	$sql = "DELETE FROM userCleanup WHERE ucID = '$delid'";
	if ( ! mysql_query( $sql, $conn ) )
		criticalError( mysql_error(), _("FOG :: Database error!") );
}

?>
	<h2><?php echo(_("Configure User Cleanup Service Module")); ?></h2>
	
	<p class="l padded">
		<?php echo(_("The User Cleanup module will clean out \"stale\" user account left over from services such as dynamic local user.")); ?>   
	</p>
	<p class="titleBottomLeft"><?php echo(_("Service Status")); ?></p>	
<?php		
	echo ( "<form method=\"post\" action=\"?node=$_GET[node]&sub=$_GET[sub]&updatestatus=1\">" );
		echo ( "<center><table cellpadding=0 cellspacing=0 border=0 width=90%>" );
			$enabled = $GLOBALS['FOGCore']->getSetting( "FOG_SERVICE_USERCLEANUP_ENABLED" );
			$checked = "";
			if ( $enabled == "1" )
			{
				$checked = " checked=\"checked\" ";
			}
			echo ( "<tr><td width=\"270\">&nbsp;"._("User Cleanup Enabled?")."</td><td>&nbsp;<input type=\"checkbox\" name=\"en\" $checked /></td><td><span class=\"icon icon-help hand\" title=\"" . _("This setting will globally enable or disable the user cleanup module.") . "\"></span></td></tr>" );
			echo ( "<tr><td colspan='3'><center><input type=\"submit\" value=\""._("Update")."\" /></center></td></tr>" );			
		echo ( "</table></center>" );
	echo ( "</form>" );
	echo ( "<p class=\"titleBottomLeft\">"._("Add Protected User")."</p>" );

		echo ( "<form method=\"post\" action=\"?node=$_GET[node]&sub=$_GET[sub]&adduser=1\">" );
			echo ( "<p>"._("Username").": <input type=\"text\" name=\"usr\" /></p>" );
			echo ( "<p><input type=\"submit\" value=\""._("Add User")."\" /></p>" );
		echo ( "</form>" );
		
	echo ( "<p class=\"titleBottomLeft\">"._("Current Protected User Accounts")."</p>" );
	
	echo ( "<table cellpadding=0 cellspacing=0 border=0 width=100%>" );
		echo ( "<tr class=\"header\"><td>&nbsp;<b>User</b></td><td><b>"._("Remove")."</b></td></tr>" );
		$sql = "SELECT * FROM userCleanup ORDER BY ucID";
		$res = mysql_query( $sql, $conn ) or criticalError( mysql_error(), _("FOG :: Database error!") );
		if ( mysql_num_rows( $res ) > 0 )
		{
			$cnt = 0;
			while( $ar = mysql_fetch_array( $res ) )
			{
				$bg = "";
				if ( $cnt++ % 2 == 0 )  $bg = "#E7E7E7";
				echo ( "<tr bgcolor=\"$bg\"><td>&nbsp;" . $ar["ucName"] . "</td><td><a href=\"?node=$_GET[node]&sub=$_GET[sub]&delid=" . $ar["ucID"] . "\"><img src=\"images/deleteSmall.png\" class=\"link\" /></a></td></tr>" );	
			}
		}
		else
		{
			echo ( "<tr><td colspan=\"2\">&nbsp;"._("No Entries Found.")."</td></tr>" );
		}
	echo ( "</table>" );
?>