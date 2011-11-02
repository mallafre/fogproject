<?php
		
if ( IS_INCLUDED !== true ) die( _("Unable to load system configuration information.") );

if ( $currentUser != null && $currentUser->isLoggedIn() )
{
	if ( $_GET["sub"] == "dircleaner" )
	{
		require_once( "./includes/service.dircleaner.include.php" );
	}	
	else if ( $_GET["sub"] == "usercleanup" )
	{
		require_once( "./includes/service.usercleanup.include.php" );
	}	
	else if ( $_GET["sub"] == "displaymanager" )
	{
		require_once( "./includes/service.displaymanager.include.php" );
	}	
	else if ( $_GET["sub"] == "alo" )
	{
		require_once( "./includes/service.alo.include.php" );
	}														
	else if ( $_GET["sub"] == "greenfog" )
	{
		require_once( "./includes/service.greenfog.include.php" );
	}			
	else if ( $_GET["sub"] == "snapin" )
	{
		require_once( "./includes/service.snapin.include.php" );
	}		
	else if ( $_GET["sub"] == "hostnamechanger" )
	{
		require_once( "./includes/service.hostnamechanger.include.php" );
	}				
	else if ( $_GET["sub"] == "clientupdater" )
	{
		require_once( "./includes/service.clientupdater.include.php" );
	}				
	else if ( $_GET["sub"] == "hostregister" )
	{
		require_once( "./includes/service.hostregister.include.php" );
	}					
	else if ( $_GET["sub"] == "printermanager" )
	{
		require_once( "./includes/service.printermanager.include.php" );
	}							
	else if ( $_GET["sub"] == "taskreboot" )
	{
		require_once( "./includes/service.taskreboot.include.php" );
	}				
	else if ( $_GET["sub"] == "usertracker" )
	{
		require_once( "./includes/service.usertracker.include.php" );
	}			
	else if ( $_GET["sub"] == "cs" )
	{
		?>
		<h2><?php print _("Module Configuration"); ?></h2>
		<p><?php print _("Configuration for this module is coming soon."); ?></p>
		<?php
	}		
	else
	{
		require_once( "./includes/service.about.include.php" );
	}
}