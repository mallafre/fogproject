<?php

// Blackout - 10:40 AM 1/12/2011
class StorageManagementPage extends FOGPage
{
	// Base variables
	var $name = 'Storage Management';
	var $node = 'storage';
	var $id = 'id';
	
	// Menu Items
	var $menu = array(
		
	);
	var $subMenu = array(
		
	);
	
	// Common functions - call Storage Node functions if the default sub's are used
	public function search()
	{
		$this->index();
	}
	
	public function edit()
	{
		$this->edit_storage_node();
	}
	
	public function edit_post()
	{
		$this->edit_storage_node_post();
	}
	
	public function delete()
	{
		$this->delete_storage_node();
	}
	
	public function delete_post()
	{
		$this->delete_storage_node_post();
	}
	
	// Pages
	public function index()
	{
		// Set title
		$this->title = _('All Storage Nodes');
		
		// Find data
		$StorageNodes = $this->FOGCore->getClass('StorageNodeManager')->find();
		
		// Row data
		foreach ($StorageNodes AS $StorageNode)
		{
			$this->data[] = array_merge(
				(array)$StorageNode->get(),
				array(	'isMasterText'		=> ($StorageNode->get('isMaster') ? 'Yes' : 'No'),
					'isEnabledText'		=> ($StorageNode->get('isEnabled') ? 'Yes' : 'No'),
					'isGraphEnabledText'	=> ($StorageNode->get('isGraphEnabled') ? 'Yes' : 'No')
				)
			);
		}
		
		// Header row
		$this->headerData = array(
			_('Storage Node'),
			_('Enabled'),
			_('Graph Enabled'),
			_('Master Node'),
			''
		);
		
		// Row templates
		$this->templates = array(
			sprintf('<a href="?node=%s&sub=edit&%s=${id}" title="%s">${name}</a>', $this->node, $this->id, _('Edit')),
			sprintf('${isEnabledText}', $this->node, $this->id),
			sprintf('${isGraphEnabledText}', $this->node, $this->id),
			sprintf('${isMasterText}', $this->node, $this->id),
			sprintf('<a href="?node=%s&sub=edit&%s=${id}" title="%s"><span class="icon icon-edit"></span></a> <a href="?node=%s&sub=delete&%s=${id}" title="%s"><span class="icon icon-delete"></span></a>', $this->node, $this->id, _('Edit'), $this->node, $this->id, _('Delete'))
		);
		
		// Row attributes
		$this->attributes = array(
			array(),
			array('class' => 'c', 'width' => '90'),
			array('class' => 'c', 'width' => '90'),
			array('class' => 'c', 'width' => '90'),
			array('class' => 'c', 'width' => '50'),
		);
		
		// Hook
		$this->HookManager->processEvent('STORAGE_NODE_DATA', array('headerData' => &$this->headerData, 'data' => &$this->data, 'templates' => &$this->templates, 'attributes' => &$this->attributes));
		
		// Output
		$this->render();
	}
	
	// STORAGE NODE
	public function add_storage_node()
	{
		// Set title
		$this->title = _('Add New Storage Node');
		
		// Hook
		$this->HookManager->processEvent('STORAGE_NODE_ADD');
		
		// TODO: Put table rows into variables -> Add hooking
		?>
		<form method="POST" action="<?php print $this->formAction; ?>">
			<table cellpadding="0" cellspacing="0" border="0" width="100%">
				<tr><td width="25%"><?php print _("Storage Node Name"); ?></td><td><input type="text" name="name" /> *</td></tr>
				<tr><td><?php print _("Storage Node Description"); ?></td><td><textarea name="description" rows="5" cols="65"></textarea></td></tr>
				<tr><td><?php print _("IP Address"); ?></td><td><input type="text" name="ip" /> *</td></tr>				
				<tr><td><?php print _("Max Clients"); ?></td><td><input type="text" name="maxClients" value="10" /> *</td></tr>				
				<tr><td><?php print _("Is Master Node"); ?></td><td><input type="checkbox" name="isMaster" value="1" />&nbsp;&nbsp;<span class="icon icon-help hand" title="<?php print _("Use extreme caution with this setting!  This setting, if used incorrectly could potentially wipe out all of your images stored on all current storage nodes.  The 'Is Master Node' setting defines which node is the distributor of the images.  If you add a blank node, meaning a node that has no images on it, and set it to master, it will distribute its store, which is empty, to all hosts in the group"); ?>"></span></td></tr>	
				<tr><td><?php print _("Storage Group"); ?></td><td><?php print $this->FOGCore->getClass('StorageGroupManager')->buildSelectBox(1, 'storageGroupID'); ?></td></tr>
				<tr><td><?php print _("Image Path"); ?></td><td><input type="text" name="path" value="/images/" /></td></tr>
				<tr><td><?php print _("Interface"); ?></td><td><input type="text" name="interface" value="eth0" /></td></tr>
				<tr><td><?php print _("Is Enabled"); ?></td><td><input type="checkbox" name="isEnabled" value="1" checked="checked" /></td></tr>
				<tr><td><?php print _("Is Graph Enabled"); ?></td><td><input type="checkbox" name="isGraphEnabled" checked="checked" /> <small>(On Dashboard)</small></td></tr>
				<tr><td><?php print _("Management Username"); ?></td><td><input type="text" name="user" /> *</td></tr>				
				<tr><td><?php print _("Management Password"); ?></td><td><input type="text" name="pass" /> *</td></tr>								
				<tr><td>&nbsp;</td><td><input type="hidden" name="add" value="1" /><input type="submit" value="<?php print _("Update"); ?>" /></td></tr>				
			</table>
		</form>
		<?php
	}
	
	public function add_storage_node_post()
	{
		// Hook
		$this->HookManager->processEvent('STORAGE_NODE_ADD_POST');
		
		// POST
		try
		{
			// Error checking
			if (empty($this->REQUEST['name']))
			{
				throw new Exception( _('Storage Node Name is required') );
			}
			if ($this->FOGCore->getClass('StorageNodeManager')->exists($this->REQUEST['name']))
			{
				throw new Exception( _('Storage Node already exists') );
			}
			if (empty($this->REQUEST['ip']))
			{
				throw new Exception( _('Storage Node IP Address is required') );
			}
			if (empty($this->REQUEST['maxClients']))
			{
				throw new Exception( _('Storage Node Max Clients is required') );
			}
			if (empty($this->REQUEST['interface']))
			{
				throw new Exception( _('Storage Node Interface is required') );
			}
			if (empty($this->REQUEST['user']))
			{
				throw new Exception( _('Storage Node Management Username is required') );
			}
			if (empty($this->REQUEST['pass']))
			{
				throw new Exception( _('Storage Node Management Password is required') );
			}
			
			// Create new Object
			$StorageNode = new StorageNode(array(
				'name'			=> $this->REQUEST['name'],
				'description'		=> $this->REQUEST['description'],
				'ip'			=> $this->REQUEST['ip'],
				'maxClients'		=> $this->REQUEST['maxClients'],
				'isMaster'		=> ($this->REQUEST['isMaster'] ? '1' : '0'),
				'storageGroupID'	=> $this->REQUEST['storageGroupID'],
				'path'			=> $this->REQUEST['path'],
				'interface'		=> $this->REQUEST['interface'],
				'isGraphEnabled'	=> ($this->REQUEST['isGraphEnabled'] ? '1' : '0'),
				'isEnabled'		=> ($this->REQUEST['isEnabled'] ? '1' : '0'),
				'user'			=> $this->REQUEST['user'],
				'pass'			=> $this->REQUEST['pass']
			));
			
			// Save
			if ($StorageNode->save())
			{
				if ($StorageNode->get('isMaster'))
				{
					// Unset other Master Nodes in this Storage Group
					foreach ((array)$this->FOGCore->getClass('StorageNodeManager')->find(array('isMaster' => '1', 'storageGroupID' => $StorageNode->get('storageGroupID'))) AS $StorageNodeMaster)
					{
						if ($StorageNode->get('id') != $StorageNodeMaster->get('id'))
						{
							$StorageNodeMaster->set('isMaster', '0')->save();
						}
					}
				}
			
				// Hook
				$this->HookManager->processEvent('STORAGE_NODE_ADD_SUCCESS', array('StorageNode' => &$StorageNode));
				
				// Log History event
				$this->FOGCore->logHistory(sprintf('%s: ID: %s, Name: %s', _('Storage Node created'), $StorageNode->get('id'), $StorageNode->get('name')));
				
				// Set session message
				$this->FOGCore->setMessage(_('Storage Node created'));
				
				// Redirect to new entry
				$this->FOGCore->redirect(sprintf('?node=%s', $this->request['node'], $this->id, $StorageNode->get('id')));
			}
			else
			{
				// Database save failed
				throw new Exception('Database update failed');
			}
		}
		catch (Exception $e)
		{
			// Hook
			$this->HookManager->processEvent('STORAGE_NODE_ADD_FAIL', array('StorageNode' => &$StorageNode));
			
			// Log History event
			$this->FOGCore->logHistory(sprintf('%s add failed: Name: %s, Error: %s', _('Storage Node'), $this->REQUEST['name'], $e->getMessage()));
			
			// Set session message
			$this->FOGCore->setMessage($e->getMessage());
			
			// Redirect to new entry
			$this->FOGCore->redirect($this->formAction);
		}
	}
	
	public function edit_storage_node()
	{
		// Find
		$StorageNode = new StorageNode($this->request['id']);
		
		// Title
		$this->title = sprintf('%s: %s', _('Edit'), $StorageNode->get('name'));
		
		// Hook
		$this->HookManager->processEvent('STORAGE_NODE_EDIT', array('StorageNode' => &$StorageNode));
		
		// TODO: Put table rows into variables -> Add hooking
		?>
		<form method="POST" action="<?php print $this->formAction; ?>">
			<table cellpadding="0" cellspacing="0" border="0" width="100%">
				<tr><td width="25%"><?php print _("Storage Node Name"); ?></td><td><input type="text" name="name" value="<?php print $StorageNode->get('name'); ?>" /> *</td></tr>
				<tr><td><?php print _("Storage Node Description"); ?></td><td><textarea name="description" rows="5" cols="65"><?php print $StorageNode->get('description'); ?></textarea></td></tr>
				<tr><td><?php print _("IP Address"); ?></td><td><input type="text" name="ip" value="<?php print $StorageNode->get('ip'); ?>" /> *</td></tr>				
				<tr><td><?php print _("Max Clients"); ?></td><td><input type="text" name="maxClients" value="<?php print $StorageNode->get('maxClients'); ?>" /> *</td></tr>				
				<tr><td><?php print _("Is Master Node"); ?></td><td><input type="checkbox" name="isMaster" value="1"<?php print ($StorageNode->get('isMaster') == 1 ? ' checked="chcked"' : ''); ?> />&nbsp;&nbsp;<span class="icon icon-help hand" title="<?php print _("Use extreme caution with this setting!  This setting, if used incorrectly could potentially wipe out all of your images stored on all current storage nodes.  The 'Is Master Node' setting defines which node is the distributor of the images.  If you add a blank node, meaning a node that has no images on it, and set it to master, it will distribute its store, which is empty, to all hosts in the group"); ?>"></span></td></tr>	
				<tr><td><?php print _("Storage Group"); ?></td><td><?php print $this->FOGCore->getClass('StorageGroupManager')->buildSelectBox($StorageNode->get('storageGroupID'), 'storageGroupID'); ?></td></tr>
				<tr><td><?php print _("Image Path"); ?></td><td><input type="text" name="path" value="<?php print $StorageNode->get('path'); ?>" /></td></tr>
				<tr><td><?php print _("Interface"); ?></td><td><input type="text" name="interface" value="<?php print $StorageNode->get('interface'); ?>" /></td></tr>
				<tr><td><?php print _("Is Enabled"); ?></td><td><input type="checkbox" name="isEnabled" value="1"<?php print ($StorageNode->get('isEnabled') == 1 ? ' checked="chcked"' : ''); ?> /></td></tr>
				<tr><td><?php print _("Is Graph Enabled"); ?></td><td><input type="checkbox" name="isGraphEnabled" value="1"<?php print ($StorageNode->get('isGraphEnabled') == 1 ? ' checked="chcked"' : ''); ?> /> <small>(On Dashboard)</small></td></tr>
				<tr><td><?php print _("Management Username"); ?></td><td><input type="text" name="user" value="<?php print $StorageNode->get('user'); ?>" /> *</td></tr>				
				<tr><td><?php print _("Management Password"); ?></td><td><input type="text" name="pass" value="<?php print $StorageNode->get('pass'); ?>" /> *</td></tr>								
				<tr><td>&nbsp;</td><td><input type="hidden" name="add" value="1" /><input type="submit" value="<?php print _("Update"); ?>" /></td></tr>				
			</table>
		</form>
		<?php
	}
	
	public function edit_storage_node_post()
	{
		// Find
		$StorageNode = new StorageNode($this->request['id']);

		// Hook
		$this->HookManager->processEvent('STORAGE_NODE_EDIT_POST', array('StorageNode' => &$StorageNode));
		
		// POST
		try
		{
			// Error checking
			if (empty($this->REQUEST['name']))
			{
				throw new Exception( _('Storage Node Name is required') );
			}
			if ($this->FOGCore->getClass('StorageNodeManager')->exists($this->REQUEST['name'], $StorageNode->get('id')))
			{
				throw new Exception( _('Storage Node already exists') );
			}
			if (empty($this->REQUEST['ip']))
			{
				throw new Exception( _('Storage Node IP Address is required') );
			}
			if (empty($this->REQUEST['maxClients']))
			{
				throw new Exception( _('Storage Node Max Clients is required') );
			}
			if (empty($this->REQUEST['interface']))
			{
				throw new Exception( _('Storage Node Interface is required') );
			}
			if (empty($this->REQUEST['user']))
			{
				throw new Exception( _('Storage Node Management Username is required') );
			}
			if (empty($this->REQUEST['pass']))
			{
				throw new Exception( _('Storage Node Management Password is required') );
			}
			
			// Update Object
			$StorageNode	->set('name',		$this->REQUEST['name'])
					->set('description',	$this->REQUEST['description'])
					->set('ip',		$this->REQUEST['ip'])
					->set('maxClients',	$this->REQUEST['maxClients'])
					->set('isMaster',	($this->REQUEST['isMaster'] ? '1' : '0'))
					->set('storageGroupID',	$this->REQUEST['storageGroupID'])
					->set('path',		$this->REQUEST['path'])
					->set('interface',	$this->REQUEST['interface'])
					->set('isGraphEnabled',	($this->REQUEST['isGraphEnabled'] ? '1' : '0'))
					->set('isEnabled',	($this->REQUEST['isEnabled'] ? '1' : '0'))
					->set('user',		$this->REQUEST['user'])
					->set('pass',		$this->REQUEST['pass']);
			
			// Save
			if ($StorageNode->save())
			{
				if ($StorageNode->get('isMaster'))
				{
					// Unset other Master Nodes in this Storage Group
					foreach ((array)$this->FOGCore->getClass('StorageNodeManager')->find(array('isMaster' => '1', 'storageGroupID' => $StorageNode->get('storageGroupID'))) AS $StorageNodeMaster)
					{
						if ($StorageNode->get('id') != $StorageNodeMaster->get('id'))
						{
							$StorageNodeMaster->set('isMaster', '0')->save();
						}
					}
				}
			
				// Hook
				$this->HookManager->processEvent('STORAGE_NODE_EDIT_SUCCESS', array('StorageNode' => &$StorageNode));
				
				// Log History event
				$this->FOGCore->logHistory(sprintf('%s: ID: %s, Name: %s', _('Storage Node updated'), $StorageNode->get('id'), $StorageNode->get('name')));
				
				// Set session message
				$this->FOGCore->setMessage(_('Storage Node updated'));
				
				// Redirect to new entry
				$this->FOGCore->redirect(sprintf('?node=%s', $this->request['node'], $this->id, $StorageNode->get('id')));
			}
			else
			{
				// Database save failed
				throw new Exception('Database update failed');
			}
		}
		catch (Exception $e)
		{
			// Hook
			$this->HookManager->processEvent('STORAGE_NODE_EDIT_FAIL', array('StorageNode' => &$StorageNode));
			
			// Log History event
			$this->FOGCore->logHistory(sprintf('%s add failed: Name: %s, Error: %s', _('Storage Node'), $this->REQUEST['name'], $e->getMessage()));
			
			// Set session message
			$this->FOGCore->setMessage($e->getMessage());
			
			// Redirect to new entry
			$this->FOGCore->redirect($this->formAction);
		}
	}
	
	public function delete_storage_node()
	{
		// Find
		$StorageNode = new StorageNode($this->request['id']);
		
		// Title
		$this->title = sprintf('%s: %s', _('Remove'), $StorageNode->get('name'));
		
		// Hook
		$this->HookManager->processEvent('STORAGE_NODE_DELETE', array('StorageNode' => &$StorageNode));
		
		// TODO: Put table rows into variables -> Add hooking
		?>
		<p class="c"><?php printf('%s <b>%s</b>?', _('Please confirm you want to delete'), $StorageNode->get('name')); ?></p>
		<form method="post" action="<?php print $this->formAction; ?>" class="c">
			<input type="submit" value="<?php print $this->title; ?>" />
		</form>
		<?php
	}
	
	public function delete_storage_node_post()
	{
		// Find
		$StorageNode = new StorageNode($this->request['id']);
		
		// Hook
		$this->HookManager->processEvent('STORAGE_NODE_DELETE_POST', array('StorageNode' => &$StorageNode));
		
		// POST
		try
		{
			// Destroy
			if (!$StorageNode->destroy())
			{
				throw new Exception(_('Failed to destroy Storage Node'));
			}
			
			// Hook
			$this->HookManager->processEvent('STORAGE_NODE_DELETE_SUCCESS', array('StorageNode' => &$StorageNode));
			
			// Log History event
			$this->FOGCore->logHistory(sprintf('%s: ID: %s, Name: %s', _('Storage Node deleted'), $StorageNode->get('id'), $StorageNode->get('name')));
			
			// Set session message
			$this->FOGCore->setMessage(sprintf('%s: %s', _('Storage Node deleted'), $StorageNode->get('name')));
			
			// Redirect
			$this->FOGCore->redirect(sprintf('?node=%s', $this->request['node']));
		}
		catch (Exception $e)
		{
			// Hook
			$this->HookManager->processEvent('STORAGE_NODE_DELETE_FAIL', array('StorageNode' => &$StorageNode));
			
			// Log History event
			$this->FOGCore->logHistory(sprintf('%s %s: ID: %s, Name: %s', _('Storage Node'), _('deleted'), $StorageNode->get('id'), $StorageNode->get('name')));
			
			// Set session message
			$this->FOGCore->setMessage($e->getMessage());
			
			// Redirect
			$this->FOGCore->redirect($this->formAction);
		}
	}
	
	// STORAGE GROUP
	public function storage_group()
	{
		// Set title
		$this->title = _('All Storage Groups');
		
		// Find data
		$StorageGroups = $this->FOGCore->getClass('StorageGroupManager')->find();
		
		// Row data
		foreach ($StorageGroups AS $StorageGroup)
		{
			$this->data[] = $StorageGroup->get();
		}
		
		// Header row
		$this->headerData = array(
			_('Storage Group'),
			''
		);
		
		// Row templates
		$this->templates = array(
			sprintf('<a href="?node=%s&sub=edit-storage-group&%s=${id}" title="%s">${name}</a>', $this->node, $this->id, _('Edit')),
			sprintf('<a href="?node=%s&sub=edit-storage-group&%s=${id}" title="%s"><span class="icon icon-edit"></span></a> <a href="?node=%s&sub=delete-storage-group&%s=${id}" title="%s"><span class="icon icon-delete"></span></a>', $this->node, $this->id, _('Edit'), $this->node, $this->id, _('Delete'))
		);
		
		// Row attributes
		$this->attributes = array(
			array(),
			array('class' => 'c', 'width' => '50'),
		);
		
		// Hook
		$this->HookManager->processEvent('STORAGE_GROUP_DATA', array('headerData' => &$this->headerData, 'data' => &$this->data, 'templates' => &$this->templates, 'attributes' => &$this->attributes));
		
		// Output
		$this->render();
	}
	
	public function add_storage_group()
	{
		// Set title
		$this->title = _('Add New Storage Group');
		
		// Hook
		$this->HookManager->processEvent('STORAGE_GROUP_ADD');
		
		// TODO: Put table rows into variables -> Add hooking
		?>
		<form method="POST" action="<?php print $this->formAction; ?>">
			<table cellpadding=0 cellspacing=0 border=0 width=100%>
				<tr><td width="25%"><?php print _("Storage Group Name"); ?></td><td><input type="text" name="name" value="" /></td></tr>
				<tr><td><?php print _("Storage Group Description"); ?></td><td><textarea name="description" rows="5" cols="65"></textarea></td></tr>
				<tr><td>&nbsp;</td><td><input type="submit" value="<?php print _("Add"); ?>" /></center></td></tr>				
			</table>
		</form>
		<?php
	}
	
	public function add_storage_group_post()
	{
		// Hook
		$this->HookManager->processEvent('STORAGE_GROUP_ADD_POST');
		
		// POST
		try
		{
			// Error checking
			if (empty($this->REQUEST['name']))
			{
				throw new Exception( _('Storage Group Name is required') );
			}
			if ($this->FOGCore->getClass('StorageGroupManager')->exists($this->REQUEST['name']))
			{
				throw new Exception( _('Storage Group already exists') );
			}
			
			// Create new Object
			$StorageGroup = new StorageGroup(array(
				'name'		=> $this->REQUEST['name'],
				'description'	=> $this->REQUEST['description']
			));
			
			// Save
			if ($StorageGroup->save())
			{
				// Hook
				$this->HookManager->processEvent('STORAGE_GROUP_ADD_POST_SUCCESS', array('StorageGroup' => &$StorageGroup));
				
				// Log History event
				$this->FOGCore->logHistory(sprintf('%s: ID: %s, Name: %s', _('Storage Group created'), $StorageGroup->get('id'), $StorageGroup->get('name')));
				
				// Set session message
				$this->FOGCore->setMessage(_('Storage Group created'));
				
				// Redirect to new entry
				$this->FOGCore->redirect(sprintf('?node=%s&sub=edit-storage-group&%s=%s', $this->request['node'], $this->id, $StorageGroup->get('id')));
			}
			else
			{
				// Database save failed
				throw new Exception('Database update failed');
			}
		}
		catch (Exception $e)
		{
			// Hook
			$this->HookManager->processEvent('STORAGE_GROUP_ADD_POST_FAIL', array('StorageGroup' => &$StorageGroup));
			
			// Log History event
			$this->FOGCore->logHistory(sprintf('%s add failed: Name: %s, Error: %s', _('Storage'), $this->REQUEST['name'], $e->getMessage()));
			
			// Set session message
			$this->FOGCore->setMessage($e->getMessage());
			
			// Redirect to new entry
			$this->FOGCore->redirect($this->formAction);
		}
	}
	
	public function edit_storage_group()
	{
		// Find
		$StorageGroup = new StorageGroup($this->request['id']);
		
		// Title
		$this->title = sprintf('%s: %s', _('Edit'), $StorageGroup->get('name'));
		
		// Hook
		$this->HookManager->processEvent('STORAGE_GROUP_EDIT', array('StorageGroup' => &$StorageGroup));
		
		// TODO: Put table rows into variables -> Add hooking
		?>
		<form method="POST" action="<?php print $this->formAction; ?>">
			<table cellpadding=0 cellspacing=0 border=0 width=100%>
				<tr><td width="25%"><?php print _("Storage Group Name"); ?></td><td><input type="text" name="name" value="<?php print $StorageGroup->get('name'); ?>" /></td></tr>
				<tr><td><?php print _("Storage Group Description"); ?></td><td><textarea name="description" rows="5" cols="65"><?php print $StorageGroup->get('description'); ?></textarea></td></tr>
				<tr><td>&nbsp;</td><td><input type="submit" value="<?php print _("Update"); ?>" /></center></td></tr>				
			</table>
		</form>
		<?php
	}
	
	public function edit_storage_group_post()
	{
		// Find
		$StorageGroup = new StorageGroup($this->request['id']);

		// Hook
		$this->HookManager->processEvent('STORAGE_GROUP_EDIT_POST', array('StorageGroup' => &$StorageGroup));
		
		// POST
		try
		{
			// Error checking
			if (empty($this->REQUEST['name']))
			{
				throw new Exception( _('Storage Group Name is required') );
			}
			if ($this->FOGCore->getClass('StorageGroupManager')->exists($this->REQUEST['name'], $StorageGroup->get('id')))
			{
				throw new Exception( _('Storage Group already exists') );
			}
			
			// Update Object
			$StorageGroup	->set('name',		$this->REQUEST['name'])
					->set('description',	$this->REQUEST['description']);
			
			// Save
			if ($StorageGroup->save())
			{
				// Hook
				$this->HookManager->processEvent('STORAGE_GROUP_EDIT_POST_SUCCESS', array('StorageGroup' => &$StorageGroup));
				
				// Log History event
				$this->FOGCore->logHistory(sprintf('%s: ID: %s, Name: %s', _('Storage Group updated'), $StorageGroup->get('id'), $StorageGroup->get('name')));
				
				// Set session message
				$this->FOGCore->setMessage(_('Storage Group updated'));
				
				// Redirect to new entry
				$this->FOGCore->redirect(sprintf('?node=%s&sub=storage-group', $this->request['node'], $this->id, $StorageGroup->get('id')));
			}
			else
			{
				// Database save failed
				throw new Exception('Database update failed');
			}
		}
		catch (Exception $e)
		{
			// Hook
			$this->HookManager->processEvent('STORAGE_GROUP_EDIT_FAIL', array('StorageGroup' => &$StorageGroup));
			
			// Log History event
			$this->FOGCore->logHistory(sprintf('%s add failed: Name: %s, Error: %s', _('Storage Group'), $this->REQUEST['name'], $e->getMessage()));
			
			// Set session message
			$this->FOGCore->setMessage($e->getMessage());
			
			// Redirect to new entry
			$this->FOGCore->redirect($this->formAction);
		}
	}
	
	public function delete_storage_group()
	{
		// Find
		$StorageGroup = new StorageGroup($this->request['id']);
		
		// Title
		$this->title = sprintf('%s: %s', _('Remove'), $StorageGroup->get('name'));
		
		// Hook
		$this->HookManager->processEvent('STORAGE_GROUP_DELETE', array('StorageGroup' => &$StorageGroup));
		
		// TODO: Put table rows into variables -> Add hooking
		?>
		<p class="c"><?php printf('%s <b>%s</b>?', _('Please confirm you want to delete'), $StorageGroup->get('name')); ?></p>
		<form method="post" action="<?php print $this->formAction; ?>" class="c">
			<input type="submit" value="<?php print $this->title; ?>" />
		</form>
		<?php
	}
	
	public function delete_storage_group_post()
	{
		// Find
		$StorageGroup = new StorageGroup($this->request['id']);
		
		// Hook
		$this->HookManager->processEvent('STORAGE_GROUP_DELETE_POST', array('StorageGroup' => &$StorageGroup));
		
		// POST
		try
		{
			// Error checking
			if ($this->FOGCore->getClass('StorageGroupManager')->count() == 1)
			{
				throw new Exception(_('You must have atleast one Storage Group'));
			}
			
			// Destroy
			if (!$StorageGroup->destroy())
			{
				throw new Exception(_('Failed to destroy User'));
			}
			
			// Hook
			$this->HookManager->processEvent('STORAGE_GROUP_DELETE_POST_SUCCESS', array('StorageGroup' => &$StorageGroup));
			
			// Log History event
			$this->FOGCore->logHistory(sprintf('%s: ID: %s, Name: %s', _('Storage Group deleted'), $StorageGroup->get('id'), $StorageGroup->get('name')));
			
			// Set session message
			$this->FOGCore->setMessage(sprintf('%s: %s', _('Storage Group deleted'), $StorageGroup->get('name')));
			
			// Redirect
			$this->FOGCore->redirect(sprintf('?node=%s&sub=storage-group', $this->request['node']));
		}
		catch (Exception $e)
		{
			// Hook
			$this->HookManager->processEvent('STORAGE_GROUP_DELETE_POST_FAIL', array('StorageGroup' => &$StorageGroup));
			
			// Log History event
			$this->FOGCore->logHistory(sprintf('%s %s: ID: %s, Name: %s', _('Storage Group'), _('deleted'), $StorageGroup->get('id'), $StorageGroup->get('name')));
			
			// Set session message
			$this->FOGCore->setMessage($e->getMessage());
			
			// Redirect
			$this->FOGCore->redirect($this->formAction);
		}
	}
}

// Register page with FOGPageManager
$FOGPageManager->register(new StorageManagementPage());