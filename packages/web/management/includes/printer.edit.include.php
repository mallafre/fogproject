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

// Sanitize valid input variables
foreach (array('model','alias','port','inf','ip','update') AS $x) if ($_REQUEST[$x]) $$x = addslashes($_REQUEST[$x]); unset($x);

if ( IS_INCLUDED !== true ) die( _("Unable to load system configuration information.") );
if ( $_GET["id"] != null && is_numeric( $_GET["id"] ) )
{
	$id = mysql_real_escape_string( $_GET["id"] );

	if ($update != null)
	{
		if ($alias != null)
		{
			$sql = "UPDATE printers SET pPort = '$port', pDefFile = '$inf', pModel ='$model', pAlias ='$alias', pIP = '$ip' WHERE pID = '$id'";

			if (mysql_query($sql, $conn))
			{
				msgBox(_('Update Successful'));
			}
			else
			{
				msgBox(_('Failed to create printer!'));
			}
		}			
		else
		{
			msgBox(_('A required field is null, unable to update printer!'));
		}
	}
	
	$sql = "select * from printers where pID = '$id'";
	$res = mysql_query($sql, $conn) or die(mysql_error());
	if ($ar = mysql_fetch_array($res))
	{
		$model =  $ar["pModel"];
		$alias =  $ar["pAlias"]; 
		$port =  $ar["pPort"];
		$inf = $ar["pDefFile"];
		$ip =  $ar["pIP"];
		
		?>
		<h2><?php print _("Update printer definition"); ?></h2>
		<?php
		echo ( "<form method=\"POST\" action=\"?node=$_GET[node]&sub=$_GET[sub]&id=$_GET[id]\">" );
		echo ( "<center><table cellpadding=0 cellspacing=0 border=0 width=90%>" );
			echo ( "<tr><td>"._("Printer Model").":</td><td><input type=\"text\" name=\"model\" value=\"$model\" /></td></tr>" );
			echo ( "<tr><td>"._("Printer Alias").":</td><td><input type=\"text\" name=\"alias\" value=\"$alias\" /></td></tr>" );
			echo ( "<tr><td>"._("Printer Port").":</td><td><input type=\"text\" name=\"port\" value=\"$port\" /></td></tr>" );
			echo ( "<tr><td>"._("Print INF File").":</td><td><input type=\"text\" name=\"inf\" value=\"$inf\" /></td></tr>" );	
			echo ( "<tr><td>"._("Print IP (optional)").":</td><td><input type=\"text\" name=\"ip\" value=\"$ip\" /></td></tr>" );	
			echo ( "<tr><td colspan=2><center><input type=\"hidden\" name=\"update\" value=\"1\" /><input type=\"submit\" value=\""._("Update Printer")."\" /></center></td></tr>" );
		echo ( "</table></center>" );
		echo ( "</form>" );
	}
}