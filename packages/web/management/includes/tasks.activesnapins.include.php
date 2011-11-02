<?php

if (IS_INCLUDED !== true) die(_('Unable to load system configuration information.'));

if ( $_GET['rmsnapinid'] != null && is_numeric($_GET['rmsnapinid']) && $_GET['hostid'] != null && is_numeric($_GET['hostid']) )
{
	// Hook
	$HookManager->processEvent('TasksActiveSnapinsRemove', array('id' => &$_GET['rmsnapinid'], 'hostid' => &$_GET['hostid']));
	
	if (cancelSnapinsForHost($conn, $_GET['hostid'], $_GET['rmsnapinid']))
	{
		// Hook
		$HookManager->processEvent('TasksActiveSnapinsRemoveSuccess', array('id' => &$_GET['rmsnapinid'], 'hostid' => &$_GET['hostid']));
	
		msgBox(_('Snapin Task Removed!'));
		lg(_('Snapin Task Removed') . ': ' . $_GET['rmtasksnap']);
	}
	else
	{
		// Hook
		$HookManager->processEvent('TasksActiveSnapinsRemoveFail', array('id' => &$_GET['rmsnapinid'], 'hostid' => &$_GET['hostid']));
		
		msgBox(_('Failed to remove snapin task'));
	}
}

$templates = array(
	_('Host Name'),
	_('Snapin'),
	_('Start Time'),
	_('State'),
	_('Kill')
);

$attributes = array(
	array(),
	array('class' => 'c'),
	array('class' => 'c'),
	array('class' => 'c'),
	array('width' => 40, 'class' => 'c')
);

// Hook
$HookManager->processEvent('TasksActiveSnapinsTableHeader', array('templates' => &$templates, 'attributes' => &$attributes));
	
// Output
$OutputManager = new OutputManager('task', $data, $templates, $attributes);

?>
<h2><?php echo(_('All Active Snapins')); ?></h2>

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
			'%hostname%',
			'%name%',
			'%startDate%',
			'%state%',
			'<a href="?node=tasks&sub=activesnapins&rmsnapinid=%id%&hostid=%hostID%"><span class="icon icon-kill" title="Kill Task"></span></a>'
		);
		
		$data = array();
				
		$sql = "SELECT 
				* 
			FROM 
				snapinTasks
				inner join snapinJobs on ( snapinTasks.stJobID = snapinJobs.sjID )
				inner join hosts on ( snapinJobs.sjHostID = hosts.hostID )
				inner join snapins on ( snapins.sID = snapinTasks.stSnapinID )
			WHERE
				stState in ( '0', '1' )";
				
		$allActiveSnapins = mysql_query($sql, $conn) or die(mysql_error());
		while ($task = mysql_fetch_array($allActiveSnapins))
		{
			$data[] = array(
				'id'		=> $task['sID'],
				'name'		=> $task['sName'],
				'hostID'	=> $task['hostID'],
				'hostname'	=> $task['hostName'],
				'startDate'	=> $task['sjCreateTime'],
				'state'		=> ($task['stState'] == 0 ? 'Queued' : ($task['stState'] == 1 ? 'In Progress' : 'N/A'))
			);
		}
		
		// Hook
		$HookManager->processEvent('TasksActiveSnapinsData', array('data' => &$data, 'templates' => &$templates, 'attributes' => &$attributes));
		
		// Output
		print new OutputManager('task', $data, $templates, $attributes); 
		
		?>
	</tbody>
</table>
<?php

// Hook
$HookManager->processEvent('TasksActiveSnapinsAfterTable');