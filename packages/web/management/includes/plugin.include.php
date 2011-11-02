<?php
/*
 *  FOG is a computer imaging solution.
 *  Copyright (C) 2008  Chuck Syperski & Jian Zhang
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

if ( IS_INCLUDED !== true ) die( _("Unable to load system configuration information.") );

if ( $currentUser != null && $currentUser->isLoggedIn() )
{
	$pm = new PluginManager();

	if ( $_GET["run"] != null )
	{
		$runner = $pm->getRunInclude( $_GET["run"] );
		if ( $runner != null )
		{
			if ( file_exists( $runner ) )
				require_once( $runner );
			else
				msgBox( _("Find not found").": (" . $runner . ")" );
		}
		else
			msgBox( _("Error finding entry point for modules")." (" . $runner . ")" );
	}
	else
	{
		if ( $_GET[sub] == "installed" )
		{
			require_once( "./includes/plugin.active.include.php" );
		}
		else if ( $_GET[sub] == "activate" )
		{
			require_once( "./includes/plugin.inactive.include.php" );
		}		
		else
		{
			require_once( "./includes/plugin.active.include.php" );
		}
	}
}
?>
