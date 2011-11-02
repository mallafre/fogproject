<?php

if ( IS_INCLUDED !== true ) die( _("Unable to load system configuration information.") );

if ( $currentUser != null && $currentUser->isLoggedIn() )
{
		if ( $_GET[sub] == "ver" )
		{
			require_once( "./includes/about.version.include.php" );
		}
		else if ( $_GET[sub] == "lic" )
		{
			require_once( "./includes/about.lic.include.php" );
		}
		else if ( $_GET[sub] == "kernel" )
		{
			require_once( "./includes/about.kernel.include.php" );
		}	
		else if ( $_GET[sub] == "virus" )
		{
			require_once( "./includes/about.virus.include.php" );
		}
		else if ( $_GET[sub] == "clientup" )
		{
			require_once( "./includes/about.clientupdater.include.php" );
		}	
		else if ( $_GET["sub"] == "settings" )
		{
			require_once( "./includes/about.fogsettings.include.php" );
		}	
		else if ( $_GET["sub"] == "pxemenu" )
		{
			require_once( "./includes/about.pxemenu.include.php" );
		}			
		else if ( $_GET["sub"] == "shell" )
		{
			require_once( "./includes/about.ssh.include.php" );
		}
		else if ( $_GET["sub"] == "log" )
			require_once( "./includes/about.log.include.php" );
		else if ( $_GET["sub"] == "maclist" )
			require_once( "./includes/about.maclist.include.php" );			
		else if ( $_GET["sub"] == "follow" )
			require_once( "./includes/about.follow.include.php" );				
		else
		{
			require_once( "./includes/about.version.include.php" );
		}
}
?>
