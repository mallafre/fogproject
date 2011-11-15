<?php

if (IS_INCLUDED !== true) die(_('Unable to load system configuration information.'));

if ($currentUser != null && $currentUser->isLoggedIn())
{
	$_SESSION['AllowAJAXTasks'] = true;
	
	$templates = array(	
		'<input type="checkbox" name="no" checked="checked" />',
		'',
		_('Host Name'),
		_('MAC'),
		_('IP Address'),
		_('Edit')
	);
	
	$attributes = array(	
		array('width' => 22),
		array('width' => 20),
		array(),
		array('width' => 120),
		array('width' => 120),
		array('width' => 40, 'class' => 'c')
	);
	
	// Hook
	$HookManager->processEvent('HostTableHeader', array('templates' => &$templates, 'attributes' => &$attributes));
	
	// Output
	$OutputManager = new OutputManager('host', $data, $templates, $attributes);
	?>
	<h2><?php echo(_('Host Search')); ?></h2>
	
	<input id="host-search" type="text" value="<?php echo(_('Search')); ?>" class="search-input" />
		
	<form method="POST" name="hosts" action="?node=host">	
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

		<div id="action-box">
			<input type="hidden" name="frmSub" value="1" />
			<p>
			<label for="newgroup"><?php print _('Create new group'); ?></label>
			<input type="text" name="newgroup" id="newgroup" autocomplete="off" />
			</p>
			<?php
			// Group lookup
			try
			{
				?>				
				<p class="c">OR</p>
				<label for="grp"><?php print _('Add to group'); ?></label>
				<select name="grp" id="grp"><option value="">- <?php print _('Select a group'); ?> -</option>
				<?php
				
				foreach ($FOGCore->getClass('GroupManager')->find() AS $Group)
				{
					printf('<option value="%s">%s</option>', $Group->get('id'), $Group->get('name'));
				}
				?>
				</select>
				<?php
			}
			catch( Exception $e )
			{
				criticalError( $e->getMessage(), _("FOG :: Group Lookup Error!") );
			}
			?>
			<p class="c"><input type="submit" value="<?php print _("Process Group Changes"); ?>" /></p>
		</div>
	</form>
	<?php
	
	// Hook
	$HookManager->processEvent('HostAfterTable');
}