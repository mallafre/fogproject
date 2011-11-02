<?php

if ( $currentUser != null && $currentUser->isLoggedIn() )
{
	if ( $_GET["confirm"] != null || $_GET["noconfirm"] != null )
	{
		require_once( "./includes/tasks.confirm.include.php" );
	}
	else if ( $_GET["sub"] == "search" )
	{
		require_once( "./includes/tasks.search.include.php" );
	}	
	else if ( $_GET["sub"] == "listgroups" )
	{
		require_once( "./includes/tasks.listgroups.include.php" );
	}	
	else if ( $_GET["sub"] == "listhosts" )
	{
		require_once( "./includes/tasks.listhosts.include.php" );
	}
	else if ( $_GET["sub"] == "active" )
	{
		if ( $GLOBALS['FOGCore']->getSetting( "FOG_USE_LEGACY_TASKLIST" )  == "1" )
			require_once( "./includes/tasks.active.legacy.include.php" );
		else
			require_once( "./includes/tasks.active.include.php" );
	}						
	else if ( $_GET["sub"] == "activemc" )
	{
		require_once( "./includes/tasks.activemc.include.php" );
	}		
	else if ( $_GET["sub"] == "advanced" )
	{
		require_once( "./includes/tasks.advanced.include.php" );
	}
	else if ( $_GET["sub"] == "activesnapins" )
	{
		require_once( "./includes/tasks.activesnapins.include.php" );
	}
	else if ( $_GET["sub"] == "sched" )
	{
		require_once( "./includes/tasks.sched.include.php" );
	}	
	else
	{
		if ( $GLOBALS['FOGCore']->getSetting( "FOG_VIEW_DEFAULT_SCREEN" ) == "LIST" )
			require_once( "./includes/tasks.listhosts.include.php" );
		else
			require_once( "./includes/tasks.search.include.php" );
	}
}
?>
