<?php

if ( IS_INCLUDED !== true ) die( _("Unable to load system configuration information.") );
if ( $currentUser != null && $currentUser->isLoggedIn() )
{
	echo ( "<div id=\"menuBar\">" );
		echo ( "<a href=\"?node=\"><img class=\"link\" src=\"./images/home.png\" /></a>" );
		echo ( "<a href=\"?node=host\"><img class=\"link\" src=\"./images/host.png\" /></a>" );
		echo ( "<a href=\"?node=tasks\"><img class=\"link\" src=\"./images/star.png\" /></a>" );		
		echo ( "<a href=\"?node=logout\"><img class=\"link\" src=\"./images/logout.png\"  /></a>" );												
	echo ( "</div>" );
}
?>
