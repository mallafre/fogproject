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
<h2><?php print _("FOG Log Viewer"); ?></h2>
<?php

echo ( "<p>" );
	echo ( "<form method=\"post\" action=\"?node=" . $_GET["node"] . "&sub=" . $_GET["sub"] . "\">" );
	echo ( "<p>"._("File: ") );
		echo ( "<select name=\"logtype\"> " );
		foreach (array("Multicast", "Scheduler", "Replicator") as $value)
		{
		    $selected = ($value == $_POST['logtype']) ? "selected=\"selected\"" : "";
		    echo ( "<option $selected value=\"$value\">$value</option>" );
		}
		echo ( "</select>" );
		echo ( "&nbsp;&nbsp;"._("Number of lines: ") );
		echo ( "<select name=\"n\"> " );
		foreach (array("20", "50", "100", "200", "400", "500", "1000") as $value)
		{
		    $selected = ($value == $_POST['n']) ? "selected=\"selected\"" : "";
		    echo ( "<option $selected value=\"$value\">$value</option>" );
		}
		echo ( "</select>" );
		echo ( "&nbsp;&nbsp;<input type=\"submit\" value=\""._("Refresh")."\" />" );			
	echo ( "</p>" );
	echo ( "</form>" );
	echo ( "<div class=\"sub l\"><pre>" );

	
		$n = 20;
		if ( $_POST["n"] != null && is_numeric($_POST["n"]) )
			$n = $_POST["n"];
	
		$t = trim($_POST["logtype"]);
		$logfile = $GLOBALS['FOGCore']->getSetting( "FOG_UTIL_BASE" ) . "/log/multicast.log";
		if ( $t == "Multicast" )
			$logfile = $GLOBALS['FOGCore']->getSetting( "FOG_UTIL_BASE" ) . "/log/multicast.log";
		else if ( $t == "Scheduler" )
			$logfile = $GLOBALS['FOGCore']->getSetting( "FOG_UTIL_BASE" ) . "/log/fogscheduler.log";
		else if ( $t == "Replicator" )
			$logfile = $GLOBALS['FOGCore']->getSetting( "FOG_UTIL_BASE" ) . "/log/fogreplicator.log";				
	
		system("tail -n $n \"$logfile\"");
	echo ( "</pre></div>" );
echo ( "</p>" );