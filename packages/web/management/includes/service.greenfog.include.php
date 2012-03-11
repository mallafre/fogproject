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
			settingKey = 'FOG_SERVICE_GREENFOG_ENABLED'";
	if ( ! mysql_query( $sql, $conn ) )
	{
		criticalError( mysql_error(), _("FOG :: Database error!") );
	}
}
else if ( $_GET["addevent"] == "1" )
{
	
	$h = mysql_real_escape_string($_POST["h"]);
	$m = mysql_real_escape_string($_POST["m"]);
	$t = mysql_real_escape_string($_POST["style"]);
	
	if ( is_numeric( $h ) && is_numeric( $m ) && $h >= 0 && $h <= 23 && $m >= 0 && $m <= 59 && ( $t == "r" || $t == "s" ) )
	{
		$sql = "INSERT INTO 
				greenFog(gfHour, gfMin, gfAction) values('$h', '$m', '$t')";	
		if ( ! mysql_query( $sql, $conn ) ) criticalError( mysql_error(), _("FOG :: Database error!") );

	}
	else
		msgBox( "Failed to add event!" );
}
else if ( $_GET["delid"] != null && is_numeric( $_GET["delid"] ) )
{
	$sql = "DELETE FROM greenFog WHERE gfID = '" . mysql_real_escape_string( $_GET["delid"] ) . "'";
	if ( ! mysql_query( $sql, $conn ) )
		criticalError( mysql_error(), _("FOG :: Database error!") );
}


?>
	<h2><?php echo(_("Configure Green FOG Service Module")); ?></h2>
	
	<p class="l padded">
		<?php echo(_("Green FOG is a service module that will shutdown / restart the client computers at a set time.")); ?>
	</p>
	<p class="titleBottomLeft"><?php echo(_("Service Status")); ?></p>	
<?php		
	echo ( "<form method=\"post\" action=\"?node=$_GET[node]&sub=$_GET[sub]&updatestatus=1\">" );
		echo ( "<center><table cellpadding=0 cellspacing=0 border=0 width=90%>" );
			$enabled = $GLOBALS['FOGCore']->getSetting( "FOG_SERVICE_GREENFOG_ENABLED" );
			$checked = "";
			if ( $enabled == "1" )
			{
				$checked = " checked=\"checked\" ";
			}
			echo ( "<tr><td width=\"270\">&nbsp;"._("Green FOG Enabled?")."</td><td>&nbsp;<input type=\"checkbox\" name=\"en\" $checked /></td><td><span class=\"icon icon-help hand\" title=\"" . _("This setting will globally enable or disable the Green FOG service module.  If you disable the module, it will be disabled for all clients, regardless of the host specific setting.") . "\"></span></td></tr>" );
			echo ( "<tr><td colspan='3'><center><input type=\"submit\" value=\""._("Update")."\" /></center></td></tr>" );			
		echo ( "</table></center>" );
	echo ( "</form>" );
	echo ( "<p class=\"titleBottomLeft\">"._("Shutdown/Reboot Schedule")."</p>" );

		echo ( "<form method=\"post\" action=\"?node=$_GET[node]&sub=$_GET[sub]&addevent=1\">" );
			echo ( "<p>"._("Add Event (24 Hour Format)").": <input class=\"short\" type=\"text\" name=\"h\" maxlength=\"2\" value=\""._("HH")."\" onFocus=\"this.value=''\" /> : <input class=\"short\" type=\"text\" name=\"m\" maxlength=\"2\" value=\""._("MM")."\" onFocus=\"this.value=''\" /> &nbsp;&nbsp;&nbsp;&nbsp;<select name=\"style\" size=\"1\"><option value=\"\" label=\"Select One\">"._("Select One")."</option><option value=\"s\" label=\"Shut Down\">"._("Shut Down")."</option><option value=\"r\" label=\"Reboot\">"._("Reboot")."</option></select></p>" );
			echo ( "<p><input type=\"submit\" value=\""._("Add Event")."\" /></p>" );
		echo ( "</form>" );
		
		echo ( "<table cellpadding=0 cellspacing=0 border=0 width=100%>" );
			echo ( "<tr class=\"header\"><td>&nbsp;<b>"._("Time")."</b></td><td>&nbsp;<b>"._("Action")."</b></td><td><b>"._("Remove")."</b></td></tr>" );
			$sql = "SELECT * FROM greenFog order by gfHour, gfMin";
			$res = mysql_query( $sql, $conn ) or die( mysql_error() );
			if ( mysql_num_rows( $res ) > 0 )
			{
				$cnt = 0;
				while( $ar = mysql_fetch_array( $res ) )
				{
					$bg = "";
					if ( $cnt++ % 2 == 0 )  $bg = "#E7E7E7";
					$type = "N/A";
					if ( $ar["gfAction"] == "r" )
						$type = "Reboot";
					else if ( $ar["gfAction"] == "s" )
						$type = "Shutdown";
						
					echo ( "<tr bgcolor=\"$bg\"><td>&nbsp;" . $ar["gfHour"] . ":" . $ar["gfMin"] . "</td><td>$type</td><td><a href=\"?node=$_GET[node]&sub=$_GET[sub]&delid=" . $ar["gfID"] . "\"><img src=\"images/deleteSmall.png\" class=\"link\" /></a></td></tr>" );	
				}			
			}
			else
			{
				echo ( "<tr><td colspan=\"3\">&nbsp;"._("No Entries Found.")."</td></tr>" );
			}
		echo ( "</table>" );

?>