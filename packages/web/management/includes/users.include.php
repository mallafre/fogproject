<?php

if ( IS_INCLUDED !== true ) die( _("Unable to load system configuration information.") );

if ( $currentUser != null && $currentUser->isLoggedIn() )
{
	if ( $_GET[sub] == "add" )
	{
		require_once( "./includes/users.add.include.php" );
	}
	else if ( $_GET[sub] == "list" )
	{
		require_once( "./includes/users.list.include.php" );
	}		
	else if ( $_GET[sub] == "edit" )
	{
		require_once( "./includes/users.edit.include.php" );
	}				
	else
	{
		require_once( "./includes/users.list.include.php" );
	}
}
?>
