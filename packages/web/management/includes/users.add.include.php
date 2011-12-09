<?php
/*
 *  FOG - Free, Open-Source Ghost is a computer imaging solution.
 *  Copyright (C) 2010  Chuck Syperski & Jian Zhang
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

if ( $_POST["add"] != null )
{
	if ( ! $FOGCore->getClass('UserManager')->exists( $_POST["name"] ) )
	{
		$name = $_POST["name"];
		$password1 = $_POST["p1"];
		$password2 = $_POST["p2"];
		
		if ( $uMan->isValidPassword($password1, $password2) )
		{
			try
			{
				$User = new User(array(
					'name'		=> $_POST["name"],
					'type'		=> ($_POST["isGuest"] == "on" ? '1' : '0'),
					'password'	=> $password1,
					'createdBy'	=> $currentUser->get('name'),
					'createdTime'	=> time()
				));
				
				if ($User->save())
				{
					msgBox(_("User created") . ": $name");
					lg(_("User Added") . ": $name");
				}
				else
				{
					msgBox( _("Failed to add user.") );
					lg( _("Failed to add user")." :: $name "  );
				}
			}
			catch( Exception $e )
			{
				msgBox( _("Error adding user").": <br />" . $e->getMessage() );			
			}				
		}
		else
		{
			msgBox( _("Invalid Password!") );
		}
	}
	else
	{
		msgBox( "$_POST[name] "._("already exists") );
	}
}

?>
<h2><?php print _("Add new user account"); ?></h2>
<form method="POST" action="?node=<?php print $_GET['node']; ?>&sub=<?php print $_GET['sub']; ?>">
<center><table cellpadding="0" cellspacing="0" border="0" width="90%">
	<tr><td><?php print _("User Name"); ?>:</td><td><input class="smaller" type="text" name="name" value="" autocomplete="off" /></td></tr>
	<tr><td><?php print _("User Password"); ?>:</td><td><input type="password" name="p1" value="" autocomplete="off" /></td></tr>
	<tr><td><?php print _("User Password (confirm)"); ?>:</td><td><input type="password" name="p2" value="" autocomplete="off" /></td></tr>
	<tr><td><?php print _("Mobile/Quick Image Access Only?"); ?></td><td><input type="checkbox" name="isGuest" autocomplete="off" /></td></tr>
	<tr><td colspan=2><center><input type="hidden" name="add" value="1" /><input class="smaller" type="submit" value="<?php print _("Create User"); ?>" /></center></td></tr>				
</table></center>
</form>