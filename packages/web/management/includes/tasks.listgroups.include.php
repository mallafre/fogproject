<?php
	
if (IS_INCLUDED !== true) die(_('Unable to load system configuration information.'));

if ($currentUser != null && $currentUser->isLoggedIn())
{
	$_SESSION['allow_ajax_task'] = true;
	
	$templates = array(
		_('Name'),
		_('Members'),
		_('Deploy'),
		_('Multicast'),
		_('Advanced')
	);

	$attributes = array(
		array(),
		array('width' => 55, 'class' => 'c'),
		array('width' => 55, 'class' => 'c'),
		array('width' => 55, 'class' => 'c'),
		array('width' => 55, 'class' => 'c'),
		array('width' => 55, 'class' => 'c')
	);
	
	// Hook
	$HookManager->processEvent('TasksListGroupTableHeader', array('data' => &$data, 'templates' => &$templates, 'attributes' => &$attributes));
	
	// Output
	$OutputManager = new OutputManager('task', $data, $templates, $attributes);
	
	?>
	<h2><?php echo(_('All Current Groups')); ?></h2>
		
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
					'%name%',
					'%memberCount%',
					'%deployLink%',
					'%multicastLink%',
					'%advancedLink%'
				);

				$attributes = array(
					array(),
					array('class' => 'c'),
					array('class' => 'c'),
					array('class' => 'c'),
					array('class' => 'c')
				);
				
				$data = array();

				$groups = $FOGCore->getClass('GroupManager')->find();
				if (count($groups))
				{
					foreach ($groups as $group)
					{
						$deployLink = '<a href="?node=tasks&type=group&direction=down&noconfirm=' . $group->getID() . '"><span class="icon icon-download" title="Deploy"></span></a>';
						$multicastLink = '<a href="?node=tasks&type=group&direction=downmc&noconfirm=' . $group->getID() . '"><span class="icon icon-multicast" title="Deploy Multicast"></span></a>';
						$advancedLink = '<a href="?node=tasks&sub=advanced&groupid=' . $group->getID() . '"><span class="icon icon-advanced" title="Advanced Deployment"></span></a>';
						
						$data[] = array(
							'id'		=> $group->getID(),
							'name'		=> $group->getName(),
							'memberCount'	=> $group->getHostCount(),
							'deployLink'	=> $deployLink,
							'advancedLink'	=> $advancedLink,
							'multicastLink'	=> $multicastLink
						);
					}
					
					// Hook
					$HookManager->processEvent('TasksListGroupData', array('data' => &$data, 'templates' => &$templates, 'attributes' => &$attributes));
					
					foreach ($data AS $rowData)
					{
						printf('<tr id="group-%s" class="%s">%s</tr>%s', $rowData['id'], (++$i % 2 ? 'alt1' : 'alt2'), $OutputManager->processRow($rowData, $templates, $attributes), "\n");
					}
				}
				else
				{
					msgBox('No Groups found');
					echo ( _("No Groups found") );
				}
				
				?>			
			</tbody>
		</table>
	</form>
	<?php
	
	// Hook
	$HookManager->processEvent('TasksListGroupAfterTable');
}