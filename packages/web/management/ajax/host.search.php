<?php
/*
 *  FOG - Free, Open-Source Ghost is a computer imaging solution.
 *  Copyright (C) 2007  Chuck Syperski & Jian Zhang
 *
 *   This program is free software: you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation, either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   This program is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 *
 */

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

// Search -> Build data array
foreach ($FOGCore->getClass('HostManager')->search($crit) AS $Host)
{
	// Minimum fields
	$x = array(	
			'id' 		=> 	$Host->get('id'),
			'hostname'	=>	$Host->get('name'),
			'mac'		=>	$Host->get('mac')->__toString()
			);
	
	// Optional fields - dont send fields that have no data - this decreases ajax overhead
	if ($Host->get('ip'))
	{
		$x['ip'] = $Host->get('ip');
	}

	$data[] = $x;
}

$templates = array(
	'<input type="checkbox" name="HID%id%" checked="checked" />',
	'<span class="icon ping"></span>',
	'<a href="?node=host&sub=edit&id=%id%" title="Edit">%hostname%</a>',
	'%mac%',
	'%ip%',
	'<a href="?node=host&sub=edit&id=%id%"><span class="icon icon-edit" title="Edit: %hostname%"></span></a>'
);

$attributes = array(
	array(),
	array(),
	array(),
	array(),
	array(),
	array('class' => 'c')
);

// Hook
$HookManager->processEvent('HostData', array('data' => &$data, 'templates' => &$templates, 'attributes' => &$attributes));
	
// Output
$OutputManager = new OutputManager('host', $data, $templates, $attributes);

print $OutputManager;