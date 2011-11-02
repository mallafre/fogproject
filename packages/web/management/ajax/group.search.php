<?php
// Blackout - 1:50 PM 25/09/2011

// Require FOG Base - the relative path to config.php changes in AJAX files as these files are included and accessed directly
require_once((defined('BASEPATH') ? BASEPATH . '/commons/config.php' : '../../commons/config.php'));
require_once(BASEPATH . '/commons/init.php');
require_once(BASEPATH . '/commons/init.database.php');

try
{
	// Error checking
	if (!$_SESSION['AllowAJAXTasks'])
	{
		throw new Exception('FOG Session Invalid');
	}
	if (!$crit)
	{
		throw new Exception('No Query');
	}
	
	// Variables
	$data = array();

	// Start ImageManager -> Search for Images
	$GroupManager = new GroupManager();
	$searchResults = $GroupManager->search($crit);
	
	if ($searchResults === false)
	{
		throw new Exception('Search failed');
	}
	
	// Build page data based on Search Results
	foreach ($searchResults AS $Group)
	{
		$data[] = array(
			'id'		=> $Group->get('id'),
			'name'		=> $Group->get('name'),
			'description'	=> $Group->get('description'),
			'count'		=> $Group->getHostCount()
		);
	}

	// Templates for data
	$templates = array(
		'<a href="?node=group&sub=edit&groupid=%id%" title="Edit">%name%</a>',
		'%description%',
		'%count%',
		'<a href="?node=group&sub=edit&groupid=%id%"><span class="icon icon-edit" title="Edit: %name%"></span></a>'
	);

	// Attributes for template data
	$attributes = array(
		array(),
		array(),
		array(),
		array('class' => 'c')
	);

	// Hook
	$HookManager->processEvent('GroupData', array('data' => &$data, 'templates' => &$templates, 'attributes' => &$attributes));

	// Output
	print new OutputManager('group', (array)$data, (array)$templates, (array)$attributes);
}
catch (Exception $e)
{
	printf('Error: %s', $e->getMessage());
}