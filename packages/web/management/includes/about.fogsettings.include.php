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

if ( $_POST["update"] == "1" ) 
{
	$sql = "SELECT
			settingID
		FROM
			globalSettings
		ORDER BY
			settingID";
	$res = mysql_query( $sql, $conn ) or criticalError( mysql_error(), _("FOG :: Database error!") );
	while( $ar = mysql_fetch_array($res) )
	{
		$key = $ar["settingID"];
		$value = mysql_real_escape_string($_POST[$key]);
		$sql = "UPDATE globalSettings SET settingValue = '$value' WHERE settingID = '$key'";
		if ( ! mysql_query( $sql, $conn ) )
		{
			criticalError( mysql_error(), _("FOG :: Database error!") );
		}
	}
}

?>
<h2><?php print _("FOG System Settings"); ?></h2>
<p class="hostgroup"><?php print _("This section allows you to customize or alter the way in which FOG operates.  Please be very careful changing any of the following settings, as they can cause issues that are difficult to troubleshoot."); ?></p>
<form method="post" action="?node=<?php print $_GET["node"]; ?>&sub=<?php print $_GET["sub"]; ?>">
	<input type="hidden" value="1" name="update" />
<?php

$cats = getSettingCats($conn);
for ($i = 0; $i < count($cats); $i++)
{
	echo ( "<h3>" . $cats[$i] . "</h3>" );
	echo ( "<table width=\"80%\" cellpadding=\"0\" cellspacing=\"0\">" );
	
		$sql = "SELECT * FROM globalSettings WHERE settingCategory = '" . mysql_real_escape_string( $cats[$i] ) . "' ORDER BY settingID";
		$res = mysql_query( $sql, $conn ) or die( mysql_error() );
		if ( mysql_num_rows( $res ) > 0 )
		{
			while( $ar = mysql_fetch_array( $res ) )
			{		
				echo ( "<tr><td width=\"270\">&nbsp;" . $ar["settingKey"] . "</td><td>&nbsp;" );

				if (count(explode( chr(10),  $ar["settingValue"]) ) <= 1 )
					echo ( "<input type=\"text\" name=\"" . $ar["settingID"] . "\" value=\"" . $ar["settingValue"] . "\" />" );
				else
					echo ( "<textarea rows=\"3\" cols=\"25\" name=\"" . $ar["settingID"] . "\">" . $ar["settingValue"] . "</textarea>" );
				echo ( "</td><td><span class=\"icon icon-help hand\" title=\"" . $ar["settingDesc"] . "\"></span></td></tr>" );
			}
		}

	echo ( "</table>" );
	
	echo ( "<p><input type=\"submit\" value=\""._("Save Changes")."\" /></p>" );
}

?>
</form>