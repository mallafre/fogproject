<?php
	
if (IS_INCLUDED !== true) die(_('Unable to load system configuration information.'));


if ($currentUser != null && $currentUser->isLoggedIn())
{
	// Remove scheduled task
	if ($_GET["rmid"])
	{
		// Hook
		$HookManager->processEvent('TasksScheduledRemove', array('id' => &$_GET['rmid']));
		
		if ($FOGCore->stopScheduledTask(new ScheduledTask(null, null, null, null, $_GET['rmid'])))
		{
			// Hook
			$HookManager->processEvent('TasksScheduledRemoveSuccess', array('id' => &$_GET['rmid']));
			
			msgBox( _("Scheduled Task removed!") );
			lg( _("Scheduled Task deleted")." :: $_GET[rmid]" );
		}
		else
		{
			// Hook
			$HookManager->processEvent('TasksScheduledRemoveFail', array('id' => &$_GET['rmid']));
			
			criticalError( mysql_error(), _("FOG :: Database error!") );
		}
	}

	$_SESSION['allow_ajax_task'] = true;
	
	$templates = array(
		_('Run time'),
		_('Task Type'),
		_('Is Group'),
		_('Group/Host Name'),
		_('Kill')
	);

	$attributes = array(
		array(),
		array(),
		array(),
		array(),
		array(),
		array('width' => 40, 'class' => 'c')
	);
	
	// Hook
	$HookManager->processEvent('TasksScheduledTableHeader', array('data' => &$data, 'templates' => &$templates, 'attributes' => &$attributes));
	
	// Output
	$OutputManager = new OutputManager('task', $data, $templates, $attributes);
	
	?>
	<h2><?php echo(_('All Scheduled Tasks')); ?></h2>
		
	<form method="POST" action="<?php print $_SERVER['PHP_SELF'] . "?node=$node"; ?>">	
		<table width="100%" cellpadding="0" cellspacing="0" border="0">
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
					'%time%',
					'%type%',
					'%isGroupBased%',
					'%name%',
					'<a href="?node=tasks&sub=sched&rmid=%id%"><span class="icon icon-kill" title="Kill task"></span></a>'
				);

				$attributes = array(
					array(),
					array(),
					array(),
					array(),
					array(),
					array('class' => 'c')
				);
				
				foreach ($FOGCore->getScheduledTasksByStorageGroupID("%", true) AS $task)
				{
					$timer = $task->getTimer();
					$taskType = getImageAction($task->getTaskType());
					$hostGroupName = ($task->isGroupBased() ? $task->getGroup()->getName() : $task->getHost()->getHostName());
					
					$data[] = array(
						'id'		=> $task->getID(),
						'name'		=> $hostGroupName,
						'time'		=> $timer->toString(),
						'type'		=> $taskType,
						'isGroupBased'	=> ($task->isGroupBased() ? "Yes" : "No")
					);
				}
				
				// Hook
				$HookManager->processEvent('TasksScheduledData', array('data' => &$data, 'templates' => &$templates, 'attributes' => &$attributes));
				
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
					printf('<tr><td colspan="%s" class="no-active-tasks">%s</td></tr>', count($templates), _("No scheduled Tasks found"));
				}
				
				?>			
			</tbody>
		</table>
	</form>
	<?php
	
	// Hook
	$HookManager->processEvent('TasksScheduledAfterTable');
}