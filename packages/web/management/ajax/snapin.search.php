<?php

// Blackout - 8:07 AM 29/09/2011

// Require FOG Base - the relative path to config.php changes in AJAX files as these files are included and accessed directly
require_once((defined('BASEPATH') ? BASEPATH . '/commons/config.php' : '../../commons/config.php'));
require_once(BASEPATH . '/commons/init.php');
require_once(BASEPATH . '/commons/init.database.php');

// Allow AJAX check
if (!$_SESSION['AllowAJAXTasks'])
{
	die('FOG Session Invalid');
}

// No search query - exit
if (!$crit)
{
	die('No Query');
}

// Variables
$data = array();

// Find Snapins
foreach ($FOGCore->getClass('SnapinManager')->search($crit) AS $Snapin)
{
	$data[] = array(
		'id'		=> $Snapin->get('id'),
		'name'		=> $Snapin->get('name'),
		'description'	=> $Snapin->get('description'),
		'path'		=> $Snapin->get('file')
	);
}

$templates = array(
	'<a href="?node=snap&sub=edit&snapinid=%id%" title="Edit">%name%</a>',
	'%description%',
	'<a href="?node=snap&sub=edit&snapinid=%id%"><span class="icon icon-edit" title="Edit: %name%"></span></a>'
);

$attributes = array(array(), array(), array('class' => 'c'));

// Hook
$HookManager->processEvent('SnapinData', array('data' => &$data, 'templates' => &$templates, 'attributes' => &$attributes));

// Output
$OutputManager = new OutputManager('snapin', $data, $templates, $attributes);
print $OutputManager;