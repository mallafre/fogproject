<?php
	
if ( IS_INCLUDED !== true ) die( _("Unable to load system configuration information.") );

if ( $currentUser != null && $currentUser->isLoggedIn() )
{
	?>
	<h2><?php echo(_("FOG Service Configuration Information")); ?></h2>
	<p class="l padded"><?php echo(_("This section of the FOG management portal allows you to configure how the FOG service functions on client computers.  The settings in this section tend to be global settings that effect all hosts.  If you are looking to configure settings for a service module that is specific to a host, please see the host section.  To get started editing global settings, please select an item from the left hand menu.")); ?></p>
	<?php
}
?>
