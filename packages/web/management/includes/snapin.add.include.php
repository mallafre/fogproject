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

if ( $_POST["add"] != null )
{
		if ( ! snapinExists( $conn, $_POST["name"] ) )
		{
			if ( $_FILES["snapin"] != null  )
			{
				$uploadfile = $GLOBALS['FOGCore']->getSetting( "FOG_SNAPINDIR" ) . basename($_FILES['snapin']['name']);
				if ( file_exists( $GLOBALS['FOGCore']->getSetting( "FOG_SNAPINDIR" ) ) )
				{
					if ( is_writable( $GLOBALS['FOGCore']->getSetting( "FOG_SNAPINDIR" ) ) )
					{
						if ( ! file_exists( $uploadfile ) )
						{
							if (move_uploaded_file($_FILES['snapin']['tmp_name'], $uploadfile))					
							{
								$name = mysql_real_escape_string( $_POST["name"] );
								$description = mysql_real_escape_string( $_POST["description"] );
								$args = mysql_real_escape_string( $_POST["args"] );
								$file = mysql_real_escape_string(  $uploadfile );
								$rw = mysql_real_escape_string( $_POST["rw"] );
								$rwa = mysql_real_escape_string( $_POST["rwa"] );
								$blReboot = "0";
								if ( $_POST["reboot"] == "on" )
								{
									$blReboot = "1";
								}
								
								$user = mysql_real_escape_string( $currentUser->get('name') );
								$sql = "insert into snapins(sName, sDesc, sFilePath, sArgs, sCreateDate, sCreator, sReboot, sRunWith, sRunWithArgs) values('$name', '$description', '$file', '$args', NOW(), '$user', '$blReboot', '$rw', '$rwa' )";
								if ( mysql_query( $sql, $conn ) )
								{
									msgBox( _("Snapin Added, you may now add another.") );
									lg( _("Snapin Added")." :: $name" );
								}
								else
								{
									msgBox( _("Failed to add snapin.") );
									lg( _("Failed to add snapin")." :: $name " . mysql_error()  );
								}
							}
							else
							{
								msgBox( _("Failed to add snapin, file upload failed.") );
								lg( _("Failed to add snapin, file upload failed.")  );							
							}
						}
						else
						{
							msgBox( _("Failed to add snapin, file already exists.") );
							lg( _("Failed to add snapin, file already exists.")  );				
						}
					}
					else
					{
						msgBox( _("Failed to add snapin, snapin directory exists, but isn't writable.") );
						lg( _("Failed to add snapin, snapin directory exists, but isn't writable.")  );					
					}
				}
				else
				{
					msgBox( _("Failed to add snapin, unable to locate snapin directory.") );
					lg( _("Failed to add snapin, unable to locate snapin directory.")  );				
				}
			}
			else
			{
				msgBox( _("Failed to add snapin, no file was uploaded.") );
				lg( _("Failed to add snapin, no file was uploaded.") );			
			}
		}
}

?>
<h2><?php print _("Add new Snapin definition"); ?></h2>
<form method="POST" action="?node=<?php print $_GET['node']; ?>&sub=<?php print $_GET['sub']; ?>" enctype="multipart/form-data">
<center><table cellpadding="0" cellspacing="0" border="0" width="90%">
	<tr><td><?php print _("Snapin Name"); ?>:</td><td><input class="smaller" type="text" name="name" value="" /></td></tr>
	<tr><td><?php print _("Snapin Description"); ?>:</td><td><textarea class="smaller" name="description" rows="5" cols="65"></textarea></td></tr>
	<tr><td><?php print _("Snapin Run With"); ?>:</td><td><input class="smaller" type="text" name="rw" value="" /></td></tr>	
	<tr><td><?php print _("Snapin Run With Argument"); ?>:</td><td><input class="smaller" type="text" name="rwa" /></td></tr>	
	<tr><td><?php print _("Snapin File"); ?>:</td><td><input class="smaller" type="file" name="snapin" value="" /> <span class="lightColor"> <?php print _("Max Size"); ?>: <?php print ini_get("post_max_size"); ?></span></td></tr>
	<tr><td><?php print _("Snapin Arguments"); ?>:</td><td><input class="smaller" type="text" name="args" value="" /></td></tr>	
	<tr><td><?php print _("Reboot after install"); ?>:</td><td><input type="checkbox" name="reboot" /></td></tr>		
	<tr><td colspan=2><center><input type="hidden" name="add" value="1" /><input class="smaller" type="submit" value="<?php print _("Add"); ?>" /></center></td></tr>				
</table></center>
</form>