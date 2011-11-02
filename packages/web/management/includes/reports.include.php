<?php
	
if ( IS_INCLUDED !== true ) die( _("Unable to load system configuration information.") );

if ( $currentUser != null && $currentUser->isLoggedIn() )
{
	if ( $_GET["sub"] == "file" )
	{			
		if ( $_GET["f"] != null )
		{
			$file = base64_decode($_GET["f"]);
			if ( endswith( $file, ".php" ) )
			{
				require_once( $GLOBALS['FOGCore']->getSetting( "FOG_REPORT_DIR" ) . $file );				
			}
		}
	}
	else if ( $_GET[sub] == "upload" )
	{
		require_once( "./includes/reports.upload.include.php" );
	}					
	else
	{
		require_once( "./includes/reports.about.include.php" );	
	}
}
?>
