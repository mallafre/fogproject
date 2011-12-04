<?php
/*
 *  FOG - Free, Open-Source Ghost is a computer imaging solution.
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
<h2><?php print _("All Current Users"); ?></h2>
<?php
	
$uMan = $FOGCore->getClass('UserManager');
$users = $uMan->getAllUsers();

if ( count( $users ) > 0 )
{

	echo ( "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"100%\">" );
	$cnt = 0;
	echo ( "<tr class=\"header\"><td>&nbsp;<b>"._("Username")."</b></td><td width=\"40\" align=\"c\"><b>"._("Edit")."</b></td></tr>" );
	for( $i = 0; $i < count( $users ); $i++ )
	{
		$user = $users[$i];
		if ( $user != null )
		{
			$bgcolor = "alt1";
			if ( $cnt++ % 2 == 0 ) 
				$bgcolor = "alt2";
			echo ( "<tr class=\"$bgcolor\"><td><a href=\"?node=$_GET[node]&sub=edit&userid=" . $user->get('id') . "\">" . $user->get('name') . "</a></td><td align=\"c\"><a href=\"?node=$_GET[node]&sub=edit&userid=" . $user->get('id') . "\"><img src=\"images/edit.png\" class=\"link\" /></a></td></tr>" );
		}
	}
	echo ( "</table>" );
} 
else
{
	echo ( _("No users found") );
}