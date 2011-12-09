<?php
/*
 *  FOG - is a computer imaging solution.
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

if ( $_POST["add"] != null )
{
		if ( ! doesStorageGroupExist( $conn, $_POST["name"] ) )
		{
			$name = mysql_real_escape_string( $_POST["name"] );
			$description = mysql_real_escape_string( $_POST["description"] );

			$sql = "INSERT INTO nfsGroups( ngName, ngDesc ) values('$name', '$description')";
			if ( mysql_query( $sql, $conn ) )
			{
				msgBox( _("Storage Group created.")."<br />"._("You may now add another.") );
				lg( _("Image Added")." :: $name" );
			}
			else
			{
				msgBox( _("Failed to add storage group.") );
				lg( _("Failed to add storage group")." :: $name " . mysql_error()  );
			}
		}
}

?>
<h2><?php print _("Add New Storage Group"); ?></h2>
<form method="POST" action="?node=<?php print $_GET['node']; ?>&sub=<?php print $_GET['sub']; ?>">
<center><table cellpadding=0 cellspacing=0 border=0 width=90%>
	<tr><td><?php print _("Storage Group Name"); ?>:</td><td><input class="smaller" type="text" name="name" value="" /></td></tr>
	<tr><td><?php print _("Storage Group Description"); ?>:</td><td><textarea class="smaller" name="description" rows="5" cols="65"></textarea></td></tr>
	<tr><td colspan=2><center><input type="hidden" name="add" value="1" /><input class="smaller" type="submit" value="<?php print _("Add"); ?>" /></center></td></tr>				
</table></center>
</form>