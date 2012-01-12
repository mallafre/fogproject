<?php

if (IS_INCLUDED !== true) die(_('Unable to load system configuration information.'));

if ($_GET['rmtaskmc'] != null && is_numeric($_GET['rmtaskmc']))
{	
	deleteMulticastJob($conn, mysql_real_escape_string($_GET['rmtaskmc']));
}

$templates = array(
	_('Task Name'),
	_('Hosts'),
	_('Start Time'),
	_('State'),
	_('Status'),
	_('Kill')
);

$attributes = array(
	array(),
	array('class' => 'c'),
	array('class' => 'c'),
	array('class' => 'c'),
	array('class' => 'c'),
	array('width' => 40, 'class' => 'c')
);

// Hook
$HookManager->processEvent('TasksActiveMulticastTableHeader', array('templates' => &$templates, 'attributes' => &$attributes));
	
// Output
$OutputManager = new OutputManager('task', $data, $templates, $attributes);

?>
<h2><?php echo(_("All Active Multicast Task")); ?></h2>

<table width="100%" border="0" cellpadding="0" cellspacing="0">
	<thead>
		<tr class="header">
			<?php
			
			// Hook
			print $OutputManager->processHeaderRow($templates, $attributes);
			
			?>
		</tr>
	</thead>
	<tbody>
		<?php

		$templates = array(
			'%name%',
			'%count%',
			'%startDate%',
			'%state%',
			'%percent%',
			'<a href="?node=tasks&sub=activemc&rmtaskmc=%id%"><span class="icon icon-kill" title="Kill Task"></span></a>'
		);
		
		$data = array();
		
		$sql = "SELECT 
				count(hosts.hostID) as cnt, 
				multicastSessions.msName,
				multicastSessions.msStartDateTime,
				multicastSessions.msState,
				multicastSessions.msPercent,
				multicastSessions.msID
			FROM 
				(select * from multicastSessions where msState in (0,1)) multicastSessions  
				inner join multicastSessionsAssoc on ( multicastSessionsAssoc.msID = multicastSessions.msID )
				inner join ( select * from tasks where taskStateID in (1, 2) ) tasks on ( multicastSessionsAssoc.tID = tasks.taskID )
				inner join hosts on (taskHostID = hostID)
			GROUP BY
				multicastSessions.msID";	

		$allMulticastTasks = mysql_query($sql, $conn) or criticalError(mysql_error(), _('FOG :: Database error!'));
		while ($task = mysql_fetch_array($allMulticastTasks))
		{
			$data[] = array('id' => $task['msID'], 'name' => $task['msName'], 'count' =>$task['cnt'], 'startDate' => $task['msStartDateTime'], 'state' => ($task['taskState'] == 0 && hasCheckedIn($conn, $task['taskID']) ? 'In Line' : state2text($task['msState'])), 'percent' => $task['msPercent']);
		}
		
		// Hook
		$HookManager->processEvent('TasksActiveMulticastData', array('data' => &$data, 'templates' => &$templates, 'attributes' => &$attributes));
		
		// Output
		if (count($data))
		{
			foreach ($data AS $rowData)
			{
				printf('<tr id="task-%s" class="%s">%s</tr>%s', $rowData['id'], (++$i % 2 ? 'alt1' : 'alt2'), $OutputManager->processRow($rowData, $templates, $attributes), "\n");
			}
		} 
		else
		{
			printf('<tr><td colspan="%s" class="no-active-tasks">%s</td></tr>', count($templates), _("No Active Tasks found"));
		}
		
		?>
	</tbody>
</table>
<?php

// Hook
$HookManager->processEvent('TasksActiveMulticastAfterTable');