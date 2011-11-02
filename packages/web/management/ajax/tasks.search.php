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
$hostMan = $core->getHostManager();
$taskMan = $core->getTaskManager();
$data = array();

// Main
// Prep query
$crit = addslashes(preg_replace(array('#[[:space:]]#', '#\*#'), array('%', '%'), urldecode($crit)));

// Find groups
$sql = "select * from groups where groupName like '%$crit%' ORDER BY groupName asc";
$res = mysql_query( $sql, $conn ) or die( mysql_error() );
while( $ar = mysql_fetch_array( $res ) )
{
	$members = getImageMembersByGroupID($conn, $ar['groupID']);
	$data[] = array(
		'type'		=> 'group',
		'id'		=> $ar['groupID'],
		'name'		=> $ar['groupName'],
		'description'	=> $ar['groupDesc'],
		'count'		=> ($members != null ? count($members) : 0)
	);
}

// Find hosts
try
{
	$arHosts = $hostMan->search($crit, HostManager::SORT_HOST_ASC);
	$cnt = count($arHosts);
	if ($cnt > 0)
	{
		for ($i = 0; $i < $cnt; $i++)
		{
			$host = $arHosts[$i];
			if ($host != null)
			{
				$mac = $host->get('mac');
				$data[] = array(	
					'type'		=> 'host',
					'id'		=> $host->getID(),
					'name'		=> $host->getHostname(),
					'ip'		=> $host->getIPAddress(),
					'mac'		=> $mac->getMACWithColon(),
					'running'	=> ($taskMan->getCountOfActiveTasksForHost( $host ) > 0 ? 1 : 0)
				);
			}
		}
	}
}
catch( Exception $e )
{
	$data['error'] = $e->getMessage();
}

$templates = array(
	'<div class="ping"></div>',
	'%name%',
	'%mac%',
	'%ip%',
	'<a href="?node=host&sub=edit&id=%id%"><span class="icon icon-edit" title="Edit: %name%"></span></a>'
);

$attributes = array(
	array(),
	array(),
	array(),
	array(),
	array('class' => 'c')
);

// Hook
$HookManager->processEvent('TaskData', array('data' => &$data, 'templates' => &$templates, 'attributes' => &$attributes));
	
// Output
$OutputManager = new OutputManager('task', $data, $templates, $attributes);

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
			printf('<tr id="host-%s" class="%s">%s</tr>%s', $rowData['id'], (++$i % 2 ? 'alt1' : 'alt2'), $OutputManager->processRow($rowData, $templates, $attributes), "\n");
		}
	
		msgBox(sprintf('%s tasks found', count($data)));
	}
	else
	{
		printf('<tr><td colspan="%s" class="no-active-tasks">%s</td></tr>', count($templates), ($data['error'] ? (is_array($data['error']) ? '<p>' . implode('</p><p>', $data['error']) . '</p>' : $data['error']) : _('No items found')));
	}
}