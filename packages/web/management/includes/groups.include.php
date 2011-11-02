<?php
	
if ( IS_INCLUDED !== true ) die( _("Unable to load system configuration information.") );

if ( $currentUser != null && $currentUser->isLoggedIn() )
{

	if ( $_GET['sub'] == "add" )
	{
		require_once( "./includes/images.add.include.php" );
	}
	else if ( $_GET['sub'] == "list" )
	{
		require_once( "./includes/groups.list.include.php" );
	}
	else if ( $_GET['sub'] == "edit" )
	{
		require_once( "./includes/groups.edit.include.php" );
	}				
	else if ( $_GET['sub'] == "search" )
	{
		require_once( "./includes/groups.search.include.php" );
	}			
	else if ( $_GET['sub'] == "printers" )
	{
		require_once( "./includes/groups.printers.include.php" );
	}			
	else
	{
		if ( $GLOBALS['FOGCore']->getSetting( "FOG_VIEW_DEFAULT_SCREEN" ) == "LIST" )
			require_once( "./includes/groups.list.include.php" );
		else		
			require_once( "./includes/groups.search.include.php" );
	}
}
?>
