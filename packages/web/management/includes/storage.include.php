<?php
	
if ( IS_INCLUDED !== true ) die( _("Unable to load system configuration information.") );

if ( $currentUser != null && $currentUser->isLoggedIn() )
{
	if ( $_GET["sub"] == "addgroup" )
	{
		require_once( "./includes/storage.addgroup.include.php" );
	}
	else if ( $_GET["sub"] == "addnode" )
	{
		require_once( "./includes/storage.addnode.include.php" );
	}
	else if ( $_GET["sub"] == "groups" )
	{
		require_once( "./includes/storage.listgroups.include.php" );
	}
	else if ( $_GET["sub"] == "nodes" )
	{
		require_once( "./includes/storage.listnodes.include.php" );
	}				
	else if ( $_GET["sub"] == "edit" )
	{
		require_once( "./includes/storage.editgroup.include.php" );
	}	
	else if ( $_GET["sub"] == "editnode" )
	{
		require_once( "./includes/storage.editnode.include.php" );
	}								
	else
	{
		require_once( "./includes/storage.listnodes.include.php" );
	}
}
?>
