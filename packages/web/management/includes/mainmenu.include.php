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
			'Snap-in Management'	=> 'snapin',
			'Printer Management'	=> 'printer',
			'Service Configuration'	=> 'service',
			'Task Management'	=> 'tasks',
			'Reports'		=> 'report',
			'FOG Configuration'	=> 'about'
	);
	
	// Plugin system enabled?
	if ($GLOBALS['FOGCore']->getSetting('FOG_PLUGINSYS_ENABLED'))
	{
		$mainMenuItems['Plugin Management'] = 'plugin';
	}
	
	// Finally - Logout
	$mainMenuItems['Logout'] = 'logout';
	
	// Hook
	$HookManager->processEvent('MAIN_MENU_DATA', array('data' => &$mainMenuItems));
	
	// Build the menu
	print "<ul>\n";
	foreach ($mainMenuItems AS $title => $link) {
		print "\t\t\t\t<li><a href=\"$_SERVER[PHP_SELF]?node=$link\" title=\"$title\"><img src=\"images/icon-$link.png\" /></a></li>\n";
	}
	print "\t\t\t</ul>\n";
}