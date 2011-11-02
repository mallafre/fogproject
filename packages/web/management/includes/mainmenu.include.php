<?php

if (IS_INCLUDED !== true) die(_('Unable to load system configuration information.'));

if ($currentUser != null && $currentUser->isLoggedIn())
{
	// Standard menu items
	$mainMenuItems = array(
			'Home'	                => 'home',
			'User Mananagement'	=> 'users',
			'Host Management'	=> 'host',
			'Group Management'	=> 'group',
			'Image Management'	=> 'images',
			'Storage Management'	=> 'storage',
			'Snap-in Management'	=> 'snap',
			'Printer Management'	=> 'print',
			'Fog Configuration'	=> 'service',
			'Task Management'	=> 'tasks',
			'Reports'		=> 'report',
			'Other Information'	=> 'about'
	);
	
	// Plugin system enabled?
	if ($GLOBALS['FOGCore']->getSetting( "FOG_PLUGINSYS_ENABLED"))
	{
		$mainMenuItems['Plugin Management'] = 'plugin';
	}
	
	// Finally - Logout
	$mainMenuItems['Logout'] = 'logout';
	
	// Hook
	$HookManager->processEvent('MainMenuData', array('data' => &$mainMenuItems));
	
	// Build the menu
	print "<ul>\n";
	foreach ($mainMenuItems AS $title => $link) {
		print "\t\t\t\t<li><a href=\"$_SERVER[PHP_SELF]?node=$link\" title=\"$title\"><img src=\"images/icon-$link.png\" /></a></li>\n";
	}
	print "\t\t\t</ul>\n";
}