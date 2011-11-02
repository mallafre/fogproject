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

require_once('../../commons/config.php');
require_once(BASEPATH . '/commons/init.php');
require_once(BASEPATH . '/commons/init.database.php');

if ( IS_INCLUDED !== true ) die( _("Unable to load system configuration information.") );

$currentUser = null;

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<link media="only screen and (max-device-width: 320px)" rel="stylesheet" type="text/css" href="css/main.css" />
	<link rel="stylesheet" type="text/css" href="css/main.css" />
	<meta name="viewport" content="width=320" />
	<meta name="viewport" content="initial-scale=1.0" />
	<title>
	<?php echo(_("FOG :: Mobile Manager :: Version")." ".FOG_VERSION); ?>
	</title>
</head>
<body>

	<div id="mainContainer">
		<div id="header">
			
		</div>
		<div class="mainContent">
		<?php
			if ( $_SESSION["fogmob_user"] != null )
				$currentUser = unserialize($_SESSION["fogmob_user"]);
		
			require_once( "./includes/processlogin.include.php" );
			if ( $_GET["node"] == "logout" || $currentUser == null || ! $currentUser->isLoggedIn() )
			{
				if ( $_GET["node"] == "logout" )
				{
					$_SESSION["fogmob_user"] = null;
					$currentUser  = null;
					session_destroy();
				}
				
				require_once( "./includes/loginform.include.php" );
			}
			else
			{
				require_once( "./includes/mainmenu.include.php" );				
			
				echo "<div id=\"mainContent\">";
					if( $_GET["node"] == "tasks" )
						require_once( "./includes/tasks.include.php" );
					else if ( $_GET["node"] == "host" )
						require_once( "./includes/hosts.include.php" );
					else
						require_once( "./includes/info.include.php" );
				echo "</div>";
			}
			
			if ( $currentUser != null )
				$_SESSION["fogmob_user"] = serialize( $currentUser );
		?>
		</div>
	</div>
</body>
</html>

