<?php
// Blackout - 10:43 AM 25/09/2011

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
	$ImageManager = new ImageManager();
	$searchResults = $ImageManager->search($crit);

	// Build page data based on Search Results
	foreach ($searchResults AS $image)
	{
		$data[] = array(
			'id'		=> $image->get('id'),
			'name'		=> $image->get('name'),
			'description'	=> ($image->get('description') ? current(explode("\n", $image->get('description'))) : '&nbsp;'),
			'storagegroup'	=> $image->getStorageGroup()->get('name'),
			'os'		=> $image->getOS()->get('name')
		);
	}

	// Templates for data
	$templates = array(
		'<a href="?node=images&sub=edit&imageid=%id%" title="Edit">%name%</a>',
		'%os%',
		'%storagegroup%',
		'<a href="?node=images&sub=edit&imageid=%id%"><span class="icon icon-edit" title="Edit: %name%"></span></a>'
	);

	// Attributes for template data
	$attributes = array(
		array(),
		array('class' => 'c'),
		array('class' => 'c'),
		array('class' => 'c')
	);

	// Hook
	$HookManager->processEvent('ImageData', array('data' => &$data, 'templates' => &$templates, 'attributes' => &$attributes));

	// Output
	print new OutputManager('image', $data, $templates, $attributes);
}
catch (Exception $e)
{
	printf('Error: %s', $e->getMessage());
}