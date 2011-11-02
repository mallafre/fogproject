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

?>
<h2><?php print _("FOG PXE Boot Menu Configuration"); ?></h2>
<?php

echo ( "<p class=\"hostgroup\">"._("This section allows you to customize the PXE Boot Menu Screen as well as password protect certain boot options.")."</p>" );

if ( $_POST["menutype"] !== null )
{
	$reason;
	if ( generatePXEMenu( $conn, $_POST["menutype"], $_POST["masterpassword"], $_POST["memtestpassword"], $_POST["reginputpassword"], $_POST["regpassword"], $_POST["quickimage"], $_POST["sysinfo"], $_POST["debugpassword"], $_POST["timeout"], $_POST["hidemenu"] == "on", $_POST['adv'], $reason ) )
	{
		msgBox( _("PXE Menu has been updated!") );
	}
	else
	{
		msgBox( _("PXE Menu updated failed!")."<br />" . $reason);
	}
}


echo ( "<form method=\"post\" action=\"?node=" . $_GET["node"] . "&sub=" . $_GET["sub"] . "\">" );
	echo ( "<p class=\"titleBottomLeft\">Boot Menu Builder</p>" );
	echo ( "<table width=\"80%\" cellpadding=\"0\" cellspacing=\"0\">" );
		echo ( "<tr>" );
			echo ( "<td>"._("Boot Menu Type: ")."</td>" );
			   echo ( "<td><select name=\"menutype\" size=\"1\" onchange=\"disableTextModePXEMenu(this);\"><option value=\"1\" label=\"Graphical\" selected=\"selected\" >"._("Graphical")."</option><option value=\"2\" label=\"Text\" >"._("Text")."</option></select></td>" );				
		echo ( "</tr>" );
		echo ( "<tr>" );
			echo ( "<td>"._("Hide Menu: ")."</td>" );
			echo ( "<td><input type=\"checkbox\" id=\"hidemenu\" name=\"hidemenu\" " . ( ( $GLOBALS['FOGCore']->getSetting( "FOG_PXE_MENU_HIDDEN" ) == "1" ) ? "checked=\"checked\"" : "" ) . " id=\"timeout\" /></td>" );				
		echo ( "</tr>" );
		echo ( "<tr>" );
			echo ( "<td>"._("Menu Timeout (in seconds): *")."</td>" );
			echo ( "<td><input type=\"text\" name=\"timeout\" value=\"" . $GLOBALS['FOGCore']->getSetting( "FOG_PXE_MENU_TIMEOUT" ) . "\" id=\"timeout\" /></td>" );				
		echo ( "</tr>" );			
		echo ( "<tr>" );
			echo ( "<td>"._("Master password: *")."</td>" );
			echo ( "<td><input type=\"password\" name=\"masterpassword\" value=\"\" id=\"masterpassword\" /></td>" );				
		echo ( "</tr>" );				
		echo ( "<tr>" );
			echo ( "<td>"._("Memory Test password (blank for none): ")."</td>" );
			echo ( "<td><input type=\"password\" name=\"memtestpassword\" value=\"\" id=\"memtestpassword\" /></td>" );				
		echo ( "</tr>" );			
		echo ( "<tr>" );
			echo ( "<td>"._("fog.reginput password (blank for none): ")."</td>" );
			echo ( "<td><input type=\"password\" name=\"reginputpassword\" value=\"\" id=\"reginputpassword\" /></td>" );				
		echo ( "</tr>" );			
		echo ( "<tr>" );
			echo ( "<td>"._("fog.reg password (blank for none): ")."</td>" );
			echo ( "<td><input type=\"password\" name=\"regpassword\" value=\"\" id=\"regpassword\" /></td>" );				
		echo ( "</tr>" );			
		echo ( "<tr>" );
			echo ( "<td>"._("fog.quickimage password (blank for none): ")."</td>" );
			echo ( "<td><input type=\"password\" name=\"quickimage\" value=\"\" id=\"quickimage\" /></td>" );				
		echo ( "</tr>" );			
		echo ( "<tr>" );
			echo ( "<td>"._("fog.sysinfo password (blank for none): ")."</td>" );
			echo ( "<td><input type=\"password\" name=\"sysinfo\" value=\"\" id=\"sysinfo\" /></td>" );				
		echo ( "</tr>" );			
		echo ( "<tr>" );
			echo ( "<td>"._("debug password (blank for none): ")."</td>" );
			echo ( "<td><input type=\"password\" name=\"debugpassword\" value=\"\" id=\"debugpassword\" /></td>" );				
		echo ( "</tr>" );						
		echo ( "<tr>" );
			echo ( "<td><a href=\"#\" onclick=\"$('#advancedTextArea').toggle(); return false;\" id=\"pxeAdvancedLink\"> "._("Advanced Configuration Options")."</a></td>" );
			echo ( "<td></td>" );				
		echo ( "</tr>" );
		echo ( "<tr>" );	
			echo ( "<td colspan=\"2\"><div id=\"advancedTextArea\" class=\"hidden\"><div class=\"lighterText tabbed\">"._("Add any custom text you would like included added as part of your <i>default</i> file.")." </div><textarea rows=\"5\" cols=\"64\" name=\"adv\">" . $GLOBALS['FOGCore']->getSetting( "FOG_PXE_ADVANCED" ) . "</textarea></div></td>" );			
		echo ( "</tr>" );
	echo ( "</table>" );
	
	echo ( "<p><input type=\"submit\" value=\""._("Save PXE Menu")."\" /></p>" );
echo ( "</form>" );
