<?php

if ( IS_INCLUDED !== true ) die( _("Unable to load system configuration information.") );

if ( $currentUser != null && $currentUser->isLoggedIn() )
{
	?>
	<h2><?php print _("FOG Help Resources"); ?></h2>
	<a href="http://freeghost.no-ip.org/wiki/index.php/FOGUserGuide" target="_blank"><?php print _("FOG Wiki Documentation"); ?></a>
	<?php
}