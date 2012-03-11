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
			settingKey = 'FOG_SERVICE_DIRECTORYCLEANER_ENABLED'";
	if ( ! mysql_query( $sql, $conn ) )
	{
		criticalError( mysql_error(), _("FOG :: Database error!") );
	}
}
else if ( $_GET["adddir"] == "1" )
{
	$dir = mysql_real_escape_string( $_POST["dir"] );
	if ( ! dircleanDirExists( $conn, $dir ) && trim($dir) != null )
	{
		$sql = "INSERT INTO dirCleaner ( dcPath ) values( '$dir' )";
		if ( ! mysql_query( $sql, $conn ) )
			criticalError( mysql_error(), _("FOG :: Database error!") );
	}
	else
		msgBox( _("Directory Entry Already Exists.") );
}
else if ( $_GET["delid"] !== null && is_numeric( $_GET["delid"] ) )
{
	$delid = mysql_real_escape_string( $_GET["delid"] );
	$sql = "DELETE FROM dirCleaner WHERE dcID = '$delid'";
	if ( ! mysql_query( $sql, $conn ) )
		criticalError( mysql_error(), _("FOG :: Database error!") );
}

?>
	<h2><?php echo(_("Configure Directory Cleaner Service Module")); ?></h2>
	
	<p class="l padded">
		<?php echo(_("The Directory Cleaner module will clean out all files and directories within a parent directory during log off.  This module can be useful if you have an application that uses cache that you don't want to persist between users.")); ?>  
	</p>
	<p class="titleBottomLeft"><?php echo(_("Service Status")); ?></p>	
<?php		
	echo ( "<form method=\"post\" action=\"?node=$_GET[node]&sub=$_GET[sub]&updatestatus=1\">" );
		echo ( "<center><table cellpadding=0 cellspacing=0 border=0 width=90%>" );
			$enabled = $GLOBALS['FOGCore']->getSetting( "FOG_SERVICE_DIRECTORYCLEANER_ENABLED" );
			$checked = "";
			if ( $enabled == "1" )
			{
				$checked = " checked=\"checked\" ";
			}
			echo ( "<tr><td width=\"270\">&nbsp;"._("Directory Cleaner Enabled?")."</td><td>&nbsp;<input type=\"checkbox\" name=\"en\" $checked /></td><td><span class=\"icon icon-help hand\" title=\"" . _("This setting will globally enable or disable the directory cleaner service module.  If you disable the module, it will be disabled for all clients, regardless of the host specific setting.") . "\"></span></td></tr>" );
			echo ( "<tr><td colspan='3'><center><input type=\"submit\" value=\""._("Update")."\" /></center></td></tr>" );			
		echo ( "</table></center>" );
	echo ( "</form>" );
	echo ( "<p class=\"titleBottomLeft\">"._("Add Directory")."</p>" );

		echo ( "<form method=\"post\" action=\"?node=$_GET[node]&sub=$_GET[sub]&adddir=1\">" );
			echo ( "<p>"._("Directory Path").": <input type=\"text\" name=\"dir\" /></p>" );
			echo ( "<p><input type=\"submit\" value=\""._("Add Directory")."\" /></p>" );
		echo ( "</form>" );
		
	echo ( "<p class=\"titleBottomLeft\">"._("Directories Cleaned")."</p>" );
	
	echo ( "<table cellpadding=0 cellspacing=0 border=0 width=100%>" );
		echo ( "<tr class=\"header\"><td>&nbsp;<b>"._("Path")."</b></td><td><b>"._("Remove")."</b></td></tr>" );
		$sql = "SELECT * FROM dirCleaner ORDER BY dcID";
		$res = mysql_query( $sql, $conn ) or criticalError( mysql_error(), _("FOG :: Database error!") );
		if ( mysql_num_rows( $res ) > 0 )
		{
			$cnt = 0;
			while( $ar = mysql_fetch_array( $res ) )
			{
				$bg = "";
				if ( $cnt++ % 2 == 0 )  $bg = "#E7E7E7";
				echo ( "<tr bgcolor=\"$bg\"><td>&nbsp;" . $ar["dcPath"] . "</td><td><a href=\"?node=$_GET[node]&sub=$_GET[sub]&delid=" . $ar["dcID"] . "\"><img src=\"images/deleteSmall.png\" class=\"link\" /></a></td></tr>" );	
			}
		}
		else
		{
			echo ( "<tr><td colspan=\"2\">&nbsp;"._("No Entries Found.")."</td></tr>" );
		}
	echo ( "</table>" );		




?>
