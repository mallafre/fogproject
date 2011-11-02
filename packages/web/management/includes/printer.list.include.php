<?php
		
if (IS_INCLUDED !== true) die(_("Unable to load system configuration information."));

if ($currentUser != null && $currentUser->isLoggedIn())
{
	$_SESSION['AllowAJAXTasks'] = true;
	
	$templates = array(
		_('Model'),
		_('Alias'),
		_('Port'),
		_('INF'),
		_('IP'),
		_('Edit')
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
	$HookManager->processEvent('PrinterTableHeader', array('templates' => &$templates, 'attributes' => &$attributes));
	
	// Output
	$OutputManager = new OutputManager('printer', $data, $templates, $attributes);
	
	?>
	<h2><?php print _('All Current Printers'); ?></h2>
	
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
			
			$crit = '%';
			require('ajax/printer.search.php');
			
			?>
		</tbody>
	</table>
	<?php
	
	// Hook
	$HookManager->processEvent('PrinterAfterTable');
}