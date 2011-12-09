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

$uMan = $FOGCore->getClass('UserManager');

if ($userid != null && is_numeric($userid))
{
	$user = new User($userid);

	?>
	<h2><?php print _("Edit User Information"); ?></h2>
	<?php
	
	if ($tab == "delete")
	{
		if ($confirm != "1")
		{
			?>
			<h2><?php print _("Confirm User Removal"); ?></h2>
			<form method="POST" action="<?php print "?node=$node&sub=$sub&tab=$tab&userid=$userid&confirm=1"; ?>">
			<center><table cellpadding=0 cellspacing=0 border=0 width=90%>
				<tr><td><font class="smaller"><?php print _("User Name"); ?>:</font></td><td><font class="smaller"><?php print $user->get('name'); ?></font></td></tr>
				<tr><td colspan=2><font class="smaller"><center><input class="smaller" type="submit" value="<?php print _("Yes, delete this user"); ?>" /></center></font></td></tr>				
			</table></center>
			</form>
			<?php
		}
		else
		{
			if ($uMan->deleteUser($user))
			{
				?>
				<h2><?php print _("User Removal Complete"); ?></h2>
				<?php	
				echo ( _("User has been deleted") );
				lg( _("user deleted")." :: " . $user->get('name') . " (" . $user->get('id') . ")" );				
			}
			else
				echo ( _("Failed to delete user.") );
		}	
	}
	else
	{
		if ( $_POST["update"] != null && is_numeric( $_POST["update"] ) )
		{
			$uId = $_POST["update"];
			$name = $_POST["name"];
			if ( ! $FOGCore->getClass('UserManager')->exists( $name, $uId ) )
			{
				$user->set('name', $name);
				$blGo = true;
				if ( $_POST["p1"] != null )
				{
					if ( $uMan->isValidPassword( $_POST["p1"], $_POST["p2"] )  )
						$user->set('password', $_POST["p1"]);
					else
						$blGo = false;
				}
				
				$user->set('type', ($_POST["isGuest"] == "on") ? (User::TYPE_MOBILE) : (User::TYPE_ADMIN));
				
				if ( $blGo )
				{	
					if ( $uMan->updateUser( $user ) )
					{
						msgbox( _("Username and password have been updated!") );
						lg( _("user updated")." :: $uId" );				
					}
					else
						msgBox( _("Failed to update user.") );
				}
				else
					 msgBox(_("Invalid Password!") );
			}
			else
			{
				msgBox( _("Another user exists with this username.") );
			}
		}
		
		

		echo ( "<center>" );
		if ( $tab == "gen" || $tab == "" )
		{
			$checked = "";
			if ( $user->get('type') == User::TYPE_MOBILE )
				$checked = " checked=\"checked\" ";
	
			echo ( "<form method=\"POST\" action=\"?node=$node&sub=$sub&userid=$userid\">" );
			echo ( "<table cellpadding=0 cellspacing=0 border=0 width=90%>" );
				echo ( "<tr><td>"._("User Name").":</td><td><input class=\"smaller\" type=\"text\" name=\"name\" value=\"" . $user->get('name') . "\" /></td></tr>" );
				echo ( "<tr><td>".("New Password").":</td><td><input type=\"password\" name=\"p1\" value=\"\" /></td></tr>" );
				echo ( "<tr><td>"._("New Password (confirm)").":</td><td><input type=\"password\" name=\"p2\" value=\"\" /></td></tr>" );
				echo ( "<tr><td>"._("Mobile/Quick Image Access Only?")."</td><td><input type=\"checkbox\" name=\"isGuest\" $checked></td></tr>" );
				echo ( "<tr><td colspan=2><font class=\"smaller\"><center><input type=\"hidden\" name=\"update\" value=\"" . $user->get('id') . "\" /><input class=\"smaller\" type=\"submit\" value=\""._("Update")."\" /></center></font></td></tr>" );				
			echo ( "</table>" );
			echo ( "</form>" );
		}
		else if ( $tab == "delete" )
		{
			echo ( "<p>"._("Are you sure you wish to remove this user?")."</p>" );
			echo ( "<p><a href=\"?node=" . $node . "&sub=" . $sub . "&userid=" . $user->get('id') . "\"><img class=\"link\" src=\"images/delete.png\"></a></p>" );
		}
		echo ( "</center>" );
	}
}