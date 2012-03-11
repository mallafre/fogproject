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
			settingKey = 'FOG_SERVICE_DISPLAYMANAGER_ENABLED'";
	if ( ! mysql_query( $sql, $conn ) )
	{
		criticalError( mysql_error(), _("FOG :: Database error!") );
	}
}
else if ( $_GET["updatedefaults"] == "1" )
{
	
	$x = mysql_real_escape_string($_POST["width"]);
	$y = mysql_real_escape_string($_POST["height"]);
	$r = mysql_real_escape_string($_POST["refresh"]);
	

	
	if ( is_numeric( $x ) && is_numeric( $y ) && is_numeric( $r ) )
	{
		$sql = "UPDATE 
				globalSettings
			SET
				settingValue = '$x'
			WHERE
				settingKey = 'FOG_SERVICE_DISPLAYMANAGER_X'";	
		if ( ! mysql_query( $sql, $conn ) ) criticalError( mysql_error(), _("FOG :: Database error!") );

		$sql = "UPDATE 
				globalSettings
			SET
				settingValue = '$y'
			WHERE
				settingKey = 'FOG_SERVICE_DISPLAYMANAGER_Y'";	
		if ( ! mysql_query( $sql, $conn ) ) criticalError( mysql_error(), _("FOG :: Database error!") );
		
		$sql = "UPDATE 
				globalSettings
			SET
				settingValue = '$r'
			WHERE
				settingKey = 'FOG_SERVICE_DISPLAYMANAGER_R'";	
		if ( ! mysql_query( $sql, $conn ) ) criticalError( mysql_error(), _("FOG :: Database error!") );		

	}
}


?>
	<h2></h2>
	<h2><?php echo(_("Configure Display Manager Service Module")); ?></h2>
	
	<p class="l padded">
		<?php echo(_("The Display Manager service module will reset a computers display to a fixed setting such as 1024 x 768 on user log in.")); ?>
	</p>
	<p class="titleBottomLeft"><?php echo(_("Service Status")); ?></p>	
<?php		
	echo ( "<form method=\"post\" action=\"?node=$_GET[node]&sub=$_GET[sub]&updatestatus=1\">" );
		echo ( "<center><table cellpadding=0 cellspacing=0 border=0 width=90%>" );
			$enabled = $GLOBALS['FOGCore']->getSetting( "FOG_SERVICE_DISPLAYMANAGER_ENABLED" );
			$checked = "";
			if ( $enabled == "1" )
			{
				$checked = " checked=\"checked\" ";
			}
			echo ( "<tr><td width=\"270\">&nbsp;"._("Display Manager Enabled?")."</td><td>&nbsp;<input type=\"checkbox\" name=\"en\" $checked /></td><td><span class=\"icon icon-help hand\" title=\"" . _("This setting will globally enable or disable the display manager service module.  If you disable the module, it will be disabled for all clients, regardless of the host specific setting.") . "\"></span></td></tr>" );
			echo ( "<tr><td colspan='3'><center><input type=\"submit\" value=\""._("Update")."\" /></center></td></tr>" );			
		echo ( "</table></center>" );
	echo ( "</form>" );
	echo ( "<p class=\"titleBottomLeft\">"._("Default Setting")."</p>" );

		echo ( "<form method=\"post\" action=\"?node=$_GET[node]&sub=$_GET[sub]&updatedefaults=1\">" );
			echo ( "<p>"._("Default width").": <input type=\"text\" name=\"width\" value=\"" . $GLOBALS['FOGCore']->getSetting( "FOG_SERVICE_DISPLAYMANAGER_X") . "\" /></p>" );
			echo ( "<p>"._("Default height").": <input type=\"text\" name=\"height\" value=\"" . $GLOBALS['FOGCore']->getSetting( "FOG_SERVICE_DISPLAYMANAGER_Y") . "\" /></p>" );			
			echo ( "<p>"._("Default Refresh Rate").": <input type=\"text\" name=\"refresh\" value=\"" . $GLOBALS['FOGCore']->getSetting( "FOG_SERVICE_DISPLAYMANAGER_R") . "\" /></p>" );						
			echo ( "<p><input type=\"submit\" value=\""._("Update Defaults")."\" /></p>" );
		echo ( "</form>" );



?>
