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

/*
 *   Updated by elishughes on Sept. 23, 2008
 *          + Added network printer support
 */

if ( IS_INCLUDED !== true ) die( _("Unable to load system configuration information.") );

if ( $_POST["add"] != null )
{
	$model = mysql_real_escape_string( $_POST["model"] );
	$alias = mysql_real_escape_string( $_POST["alias"] ); 
	$port = mysql_real_escape_string( $_POST["port"] );
	$inf = mysql_real_escape_string( $_POST["inf"] );
	$ip = mysql_real_escape_string( $_POST["ip"] );
	function add_printer( $sql_statement, $sql_conn)
	{
		if ( mysql_query( $sql_statement, $sql_conn ) )
		{
			msgBox( _("Printer Added, you may now add another.") );
		}
		else
		{
			msgBox( _("Failed to create printer!") );
		}
	}

	if ( $alias != null )
	{
		if ( $_POST['printertype'] != "Network" )
		{
			if ( $port != null )
			{
				if ( $_POST['printertype'] != "iPrint" )
				{
					if ( $model != null && $inf != null )
					{
						$sql = "INSERT INTO
						printers( pPort, pDefFile, pModel, pAlias, pIP )
						values( '$port', '$inf', '$model', '$alias', '$ip' )";
						add_printer($sql, $conn);
					}
					else
					{
						msgBox( _("A required field is null, unable to create printer!") );
					}
				}
				else
				{
					$sql = "INSERT INTO
					printers( pPort, pAlias)
					values ( '$port', '$alias' )";
					add_printer( $sql, $conn);
				}
			}
			else
			{
				msgBox( _("You must specify a model, unable to create printer!") );
			}
		}
		else
		{
			$sql = "INSERT INTO
			printers( pAlias )
			values( '$alias' )";
			add_printer( $sql, $conn);
		}
	}			
	else
	{
		msgBox( _("You must specify a port, unable to create printer!") );
	}
}
?>
<h2><?php print _("Add new printer definition"); ?></h2>
<?php
echo ( "<form id=\"printerform\" action=\"?node=$_GET[node]&sub=$_GET[sub]\" method=\"POST\" > " );
echo ( "<select name=\"printertype\"> " );
foreach (array("Local" => _("Local Printer"), "iPrint" => _("iPrint Printer"), "Network" => _("Network Printer")) as $key => $value)
{
    $selected = ($key == $_POST['printertype']) ? "selected=\"selected\"" : "";
    echo ( "<option $selected value=\"$key\">$value</option>" );
}
echo ( "</select>" );
echo ( "<input type=\"submit\" value=\""._("Change type")."\">" );
echo ( "</form><br />" );

echo ( "<form method=\"POST\" action=\"?node=$_GET[node]&sub=$_GET[sub]\">" );
echo ( "<center><table cellpadding=0 cellspacing=0 border=0 width=90%>" );
echo ( "<tr><td>"._("Printer Alias").":</td><td><input type=\"text\" name=\"alias\" value=\"\" /></td></tr>" );
if ( $_POST['printertype'] == "Network" )
{
    echo ( "<tr><td>"._("e.g. \\\\printserver\\printername")."</td></tr>" );
}
// if it's iprint or local display more options
if ( $_POST['printertype'] != "Network" )
{
    echo ( "<tr><td>"._("Printer Port").":</td><td><input type=\"text\" name=\"port\" value=\"\" /></td></tr>" );
	// if it's local display even more options
    if ( $_POST['printertype'] != "iPrint" )
    {
        echo ( "<tr><td>"._("Printer Model").":</td><td><input type=\"text\" name=\"model\" value=\"\" /></td></tr>" );
        echo ( "<tr><td>"._("Print INF File").":</td><td><input type=\"text\" name=\"inf\" value=\"\" /></td></tr>" );	
        echo ( "<tr><td>"._("Print IP (optional)").":</td><td><input type=\"text\" name=\"ip\" value=\"\" /></td></tr>" );
    }
}
echo ( "<tr><td colspan=2><center><br />" );
$printertype = $_POST['printertype'];
echo ( "<input type=\"hidden\" name=\"printertype\" value=\"$printertype\" />" );
echo ( "<input type=\"hidden\" name=\"add\" value=\"1\" /><input type=\"submit\" value=\""._("Add Printer")."\" /></center></td></tr>" );
echo ( "</table></center>" );
echo ( "</form>" );