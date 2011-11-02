
<?php
	
if (IS_INCLUDED !== true) die(_("Unable to load system configuration information."));

if ($currentUser != null && $currentUser->isLoggedIn())
{
	$_SESSION['AllowAJAXTasks'] = true;
	
	$templates = array(
		_('Image Name'),
		_('Operating System'),
		_('Storage Group'),
		_('Edit')
	);
	
	$attributes = array(
		array(),
		array('width' => 140, 'class' => 'c'),
		array('width' => 140, 'class' => 'c'),
		array('width' => 40, 'class' => 'c')
	);
	
	// Hook
	$HookManager->processEvent('ImageTableHeader', array('templates' => &$templates, 'attributes' => &$attributes));
	
	// Output
	$OutputManager = new OutputManager('image', $data, $templates, $attributes);
	
	?>
	<h2><?php print _('Search Images'); ?></h2>
	
	<input id="image-search" type="text" value="<?php echo(_('Search')); ?>" class="search-input" />
	
	<table width="100%" cellpadding="0" cellspacing="0" border="0" id="search-content">
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
	$HookManager->processEvent('ImageAfterTable');
}