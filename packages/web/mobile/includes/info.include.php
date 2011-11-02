<?php
	
if ( IS_INCLUDED !== true ) die( _("Unable to load system configuration information.") );

if ( $currentUser != null && $currentUser->isLoggedIn() )
{
	echo ( "<p id=\"contentTitle\">" );
			echo ( _("Welcome to FOG Mobile") );		
	echo ( "</p>" );	
	
	echo ( "<p class=\"padded\">" );
		echo _("Welcome to FOG - Mobile Edition!  This light weight interface for FOG allows for access via mobile, low power devices.");
	echo ( "</p>" );
}
?>
