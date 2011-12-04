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

// Variables
$data = array();

$allTasks = $FOGCore->getClass('TaskManager')->getActiveTasks();
foreach ($allTasks as $task)
{
	// Reset
	$taskData = array();
	
	// Determine state
	$state = ($task->getState() == Task::STATE_QUEUED && $FOGCore->getClass('TaskManager')->hasActiveTaskCheckedIn($task->get('id')) ? 'In Line' : $task->getStateText());
	
	// Push static variables into local array
	$Host = $task->getHost();
	$taskData = array(
		'id'		=> $task->get('id'),
		'state'		=> $state,
		'hostname'	=> $Host->getHostname(),
		'force'		=> $task->get('isForced') ? '1' : '0',
		'type'		=> $task->getTaskType(),
		'typeName'	=> $task->getTaskTypeString(),
		'mac'		=> (string)$Host->get('mac'),
		'createTime'	=> $task->getCreateTime()->getLong()
	);

	if ($task->getName() != null)
	{
		$taskData['name'] = trim($task->getName());
	}
	
	if ($task->hasTransferData())
	{
		$taskData['percentText'] = trim($task->getTaskPercentText());
		$taskData['BPM'] = trim($task->getTransferRate());
		$taskData['timeElapsed'] = trim($task->getTimeElapsed());
		$taskData['timeRemaining'] = trim($task->getTimeRemaining());
		$taskData['dataCopied'] = trim($task->getDataCopied());
		$taskData['dataTotal'] = trim($task->getTaskDataTotal());
	}

	// Format variables
	$time = $taskData['createTime'];
	if ($time)
	{
		// Today
		if (date('d-m-Y', $time) == date('d-m-Y'))
		{
			//$taskData['createTime'] = 'Today, ' . date('g:i a', $time);
			$taskData['createTime'] = date('g:ia', $time);
		}
		// Yesterday
		elseif (date('d-m-Y', $time) == date("d-m-Y", strtotime("-1 day")))
		{
			$taskData['createTime'] = 'Yesterday, ' . date('g:i a', $time);
		}
		// Short date
		elseif (date('m-Y', $time) == date('m-Y'))
		{
			$taskData['createTime'] = date('dS, g:ia', $time);
		}
		// Long date
		else
		{
			$taskData['createTime'] = date('m-d-Y g:ia', $time);
		}
	}
	
	if ($taskData['BPM'])
	{
		// Convert from speed unit/min -> speed MiB/sec
		$taskData['BPM'] = (preg_match('#/min#', $taskData['BPM']) ? $taskData['BPM'] : $taskData['BPM'] . '/min');
		
		// Partimage outputs from src/shared/common.cpp: bytes, KiB, MiB, GiB, TiB
		foreach (array('MiB' => 0, 'GiB' => 1024, 'TiB' => 1024*1024) AS $unit => $multiplier)
		{
			if (preg_match('#' . $unit . '#', $taskData['BPM']))
			{
				// Remove unit/min -> recalculate to MiB/sec
				if ($taskData['BPM'] = preg_replace('#(.*) ' . $unit . '/min#U', '\\1', $taskData['BPM']))
				{
					if ($multiplier) $taskData['BPM'] = $taskData['BPM'] * $multiplier;
					
					$taskData['BPM'] = number_format($taskData['BPM'] / 60, 2) . ' MiB/sec';
				}
			}
		}
	}
	
	if ($taskData['percentText'])
	{
		$taskData['percentData'] = sprintf('<tr id="progress-%s"><td colspan="7" class="task-progress-td min"><div class="task-progress-fill min" style="width: %spx"></div><div class="task-progress min"><ul><li>%s / %s</li><li>%s</li><li>%s of %s (%s)</li></ul></div></td></tr>',
			$taskData['id'],
			(600 * ($taskData['percentText'] / 100)),
			$taskData['timeElapsed'],
			$taskData['timeRemaining'],
			$taskData['percentText'] . '%',
			$taskData['dataCopied'],
			$taskData['dataTotal'],
			$taskData['BPM']
		);
	}
	
	// Remove colons in time
	if ($taskData['timeElapsed']) $taskData['timeElapsed'] = str_replace(':', ' ', $taskData['timeElapsed']);
	if ($taskData['timeRemaining']) $taskData['timeRemaining'] = str_replace(':', ' ', $taskData['timeRemaining']);
	
	// Variables needed for templates
	$taskData['stateIconName'] = strtolower(str_replace(' ', '', $taskData['state']));
	$taskData['typeIconName'] = strtolower(str_replace(' ', '', $taskData['typeName']));
	$taskData['taskDetails'] = ($taskData['name'] ? '<div class="task-name" title="Task: ' . $taskData['name'] . '">' . $taskData['name'] . '</div>' : '');
	$taskData['forceDetails'] = ($taskData['force'] == '1' ? '<span class="icon icon-forced" title="' . _('Task forced to start') . '"></span>' : (strtolower($taskData['type']) == 'u' || strtolower($taskData['type']) == 'd' ? '<a href="?node=tasks&sub=active&forcetask=%id%&mac=%mac%"><span class="icon icon-force" title="' . _('Force task to start') . '"></span></a>' : '&nbsp;'));
	
	// Push into our final data array
	$data[] = $taskData;
}

$templates = array(
	'%taskDetails%<p>%hostname%</p><small>%mac%</small>',
	'<small>%createTime%</small>',
	'<span class="icon icon-%stateIconName%" title="%state%"></span>',
	'<span class="icon icon-%typeIconName%" title="%typeName%"></span>',
	'%forceDetails%',
	'<a href="?node=tasks&sub=active&rmtask=%id%&mac=%mac%"><span class="icon icon-kill" title="' . _('Cancel Task') . '"></span></a>'
);

$attributes = array(
	array(),
	array('class' => 'c'),
	array('class' => 'c'),
	array('class' => 'c'),
	array('class' => 'c'),
	array('class' => 'c')
);

// Hook
$HookManager->processEvent('TasksActiveData', array('data' => &$data, 'templates' => &$templates, 'attributes' => &$attributes));

//var_dump($data);
	
// Output
print new OutputManager('task', $data, $templates, $attributes);