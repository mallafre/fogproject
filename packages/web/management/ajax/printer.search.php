<?php
/*
 *  FOG is a computer imaging solution.
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

// Main
// Prep query
$crit = addslashes(preg_replace(array('#[[:space:]]#', '#\*#'), array('%', '%'), urldecode($crit)));
	
// Query
$sql = "SELECT * FROM printers WHERE pModel LIKE '%$crit%' OR pAlias LIKE '%$crit%' OR pIP LIKE '%$crit%' OR pPort LIKE '%$crit%' ORDER BY pAlias";
$res = mysql_query( $sql, $conn ) or die( mysql_error() );
while( $ar = mysql_fetch_array( $res ) )
{
	$data[] = array('id' => $ar['pID'], 'model' => $ar['pModel'], 'alias' => $ar['pAlias'], 'port' => $ar['pPort'], 'inf' => $ar['pDefFile'], 'ip' => $ar['pIP']);
}

$templates = array(
	'%model%',
	'%alias%',
	'%port%',
	'%inf%',
	'%ip%',
	'<a href="?node=print&sub=edit&id=%id%"><span class="icon icon-edit" title="Edit: %alias%"></span></a>'
);

$attributes = array(
	array(),
	array(),
	array('class' => 'c'),
	array('class' => 'c')
);

// Hook
$HookManager->processEvent('PrinterData', array('data' => &$data, 'templates' => &$templates, 'attributes' => &$attributes));
	
// Output
$OutputManager = new OutputManager('printer', $data, $templates, $attributes);

// Output
if ($FOGCore->isAJAXRequest())
{
	// AJAX request - JSON output
	print json_encode(array('data' => $data, 'templates' => $templates, 'attributes' => $attributes));
}
else
{
	// Regular request / include - HTML output
	if (count($data))
	{
		foreach ($data AS $rowData)
		{
			printf('<tr id="group-%s" class="%s">%s</tr>%s', $rowData['id'], (++$i % 2 ? 'alt1' : 'alt2'), $OutputManager->processRow($rowData, $templates, $attributes), "\n");
		}
	
		msgBox(sprintf('%s printers found', count($data)));
	}
	else
	{
		printf('<tr><td colspan="%s" class="no-active-tasks">%s</td></tr>', count($templates), ($data['error'] ? (is_array($data['error']) ? '<p>' . implode('</p><p>', $data['error']) . '</p>' : $data['error']) : _('No printers found')));
	}
}