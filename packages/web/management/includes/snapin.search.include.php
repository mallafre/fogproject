<?php
	
if ( IS_INCLUDED !== true ) die( _("Unable to load system configuration information.") );

if ( $currentUser != null && $currentUser->isLoggedIn() )
{
	$_SESSION['AllowAJAXTasks'] = true;
	
	$templates = array(
		_('Snapin Name'),
		_('Description'),
		_('Edit')
	);
	
	$attributes = array(	
		array(),
		array('width' => 280),
		array('width' => 40, 'class' => 'c')
	);
	
	// Hook
	$HookManager->processEvent('SnapinTableHeader', array('templates' => &$templates, 'attributes' => &$attributes));
	
	// Output
	$OutputManager = new OutputManager('snapin', $data, $templates, $attributes);
	
	?>
	<h2><?php echo(_('Snapin Search')); ?></h2>
	
	<input id="snapin-search" type="text" value="<?php echo(_('Search')); ?>" class="search-input" />
	
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
	<?php
	
	// Hook
	$HookManager->processEvent('SnapinAfterTable');
}