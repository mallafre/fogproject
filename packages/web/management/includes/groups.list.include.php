<?php

if (IS_INCLUDED !== true ) die( _("Unable to load system configuration information."));

if ($currentUser != null && $currentUser->isLoggedIn())
{
	$_SESSION['AllowAJAXTasks'] = true;
	
	$templates = array(
		_('Name'),
		_('Description'),
		_('Members'),
		_('Edit')
	);
	
	$attributes = array(	
		array(),
		array('width' => 230),
		array('width' => 40, 'class' => 'c'),
		array('width' => 40, 'class' => 'c')
	);
	
	// Hook
	$HookManager->processEvent('GroupTableHeader', array('templates' => &$templates, 'attributes' => &$attributes));
	
	// Output
	$OutputManager = new OutputManager('group', $data, $templates, $attributes);
	
	?>
	<h2><?php print _("Group Search"); ?></h2>
	
	<input id="group-search" type="text" value="<?php echo(_('Search')); ?>" class="search-input" />
		
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
			<?php
			
			$crit = '%';
			require('./ajax/group.search.php');
			
			?>
		</tbody>
	</table>
	<?php
	
	// Hook
	$HookManager->processEvent('GroupAfterTable');
}