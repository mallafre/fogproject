<?php
	
if (IS_INCLUDED !== true) die(_('Unable to load system configuration information.'));

if ($currentUser != null && $currentUser->isLoggedIn())
{
	$_SESSION['allow_ajax_task'] = true;
	
	$templates = array(
		_('Host Name'),
		_('MAC'),
		_('Deploy'),
		_('Upload'),
		_('Advanced')
	);

	$attributes = array(
		array(),
		array('width' => 170),
		array('width' => 55, 'class' => 'c'),
		array('width' => 55, 'class' => 'c'),
		array('width' => 55, 'class' => 'c'),
		array('width' => 55, 'class' => 'c')
	);
	
	// Hook
	$HookManager->processEvent('TasksListHostTableHeader', array('data' => &$data, 'templates' => &$templates, 'attributes' => &$attributes));
	
	// Output
	$OutputManager = new OutputManager('task', $data, $templates, $attributes);
	
	?>
	<h2><?php echo(_('All Current Hosts')); ?></h2>
		
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
					'%hostname%',
					'%mac%',
					'%deployLink%',
					'%uploadLink%',
					'%advancedLink%'
				);

				$attributes = array(
					array(),
					array('width' => 170),
					array('width' => 55, 'class' => 'c'),
					array('width' => 55, 'class' => 'c'),
					array('width' => 55, 'class' => 'c'),
					array('width' => 55, 'class' => 'c')
				);
				
				$hostMan = $FOGCore->getClass('HostManager');
				$taskMan = $FOGCore->getClass('TaskManager');
				
				$data = array();
				$hosts = $hostMan->getAllHosts();
				if (count($hosts))
				{
					foreach ($hosts as $host)
					{
						$imgUp = "<a href=\"?node=tasks&type=host&direction=up&noconfirm=" . $host->get('id') ."\"><span class=\"icon icon-upload\" title=\"Upload\"></span></a>";
						$imgDown = "<a href=\"?node=tasks&type=host&direction=down&noconfirm=" . $host->get('id') ."\"><span class=\"icon icon-download\" title=\"Deploy\"></span></a>";
						$imgAdvanced = "<a href=\"?node=tasks&sub=advanced&hostid=" . $host->get('id') ."\"><span class=\"icon icon-advanced\" title=\"Advanced Deployment\"></span></a>";
						if ($taskMan->getCountOfActiveTasksForHost($host) > 0)
						{
							$imgAdvanced = $imgUp = $imgDown = "<a href=\"?node=tasks&sub=active\"><span class=\"icon icon-taskrunning\" title=\"Task running\"></span></a>";				
						}
						
						$data[] = array(
							'id'		=> $host->get('id'),
							'hostname'	=> $host->get('name'),
							'mac'		=> $host->get('mac'),
							'uploadLink'	=> $imgUp,
							'deployLink'	=> $imgDown,
							'advancedLink'	=> $imgAdvanced							
						);
					}
					
					// Hook
					$HookManager->processEvent('TasksListHostData', array('data' => &$data, 'templates' => &$templates, 'attributes' => &$attributes));
					
					foreach ($data AS $rowData)
					{
						printf('<tr id="host-%s" class="%s">%s</tr>%s', $rowData['id'], (++$i % 2 ? 'alt1' : 'alt2'), $OutputManager->processRow($rowData, $templates, $attributes), "\n");
					}
				}
				else
				{
					printf('<tr><td colspan="%s" class="no-active-tasks">%s</td></tr>', count($templates), _("No hosts found"));
				}
				
				?>			
			</tbody>
		</table>
	</form>
	<?php
	
	// Hook
	$HookManager->processEvent('TasksListHostAfterTable');
}