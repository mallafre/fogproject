<?php
	
if (IS_INCLUDED !== true) die(_('Unable to load system configuration information.'));

if ($currentUser != null && $currentUser->isLoggedIn())
{
	$_SESSION['allow_ajax_task'] = true;
	
	$templates = array(
		_('Group Name'),
		'&nbsp;',
		_('Members'),
		_('Deploy'),
		_('Multicast'),
		_('Advanced')
	);

	$attributes = array(
		array(),
		array('width' => 115),
		array('width' => 55, 'class' => 'c'),
		array('width' => 55, 'class' => 'c'),
		array('width' => 55, 'class' => 'c'),
		array('width' => 55, 'class' => 'c')
	);
	
	// Hook
	$HookManager->processEvent('TasksSearchTableHeader', array('templates' => &$templates, 'attributes' => &$attributes));
	
	// Output
	$OutputManager = new OutputManager('task', $data, $templates, $attributes);
	
	?>
	<h2><?php echo(_('Task Search')); ?></h2>
	
	<input id="task-search" type="text" value="<?php echo(_('Search')); ?>" class="search-input" />
		
	<form method="POST" action="<?php print $_SERVER['PHP_SELF'] . "?node=$node"; ?>">	
		<table width="100%" cellpadding="0" cellspacing="0" id="search-content" border="0">
			<thead>
				<tr class="header">
				<?php
				
				// Hook
				print $OutputManager->processHeaderRow($templates, $attributes);
				
				?>
				</tr>
			</thead>
			<tbody>
			
			</tbody>
		</table>
	</form>
	<?php
}