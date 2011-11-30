<?php

// Blackout - 2:44 PM 29/11/2011
class GroupManagementPage extends FOGPage
{
	// Base variables
	var $name = 'Group Management';
	var $node = 'group';
	var $id = 'groupid';
	
	// Menu Items
	var $menu = array(
		
	);
	var $subMenu = array(
		
	);
	
	// __construct
	public function __construct($name = '')
	{
		// Call parent constructor
		parent::__construct($name);
		
		// Header row
		$this->headerData = array(
			_('Name'),
			_('Description'),
			_('Members'),
			_('Edit')
		);
		
		// Row templates
		$this->templates = array(
			'<a href="?node=group&sub=edit&groupid=${id}" title="Edit">${name}</a>',
			'${description}',
			'${count}',
			'<a href="?node=group&sub=edit&groupid=${id}"><span class="icon icon-edit" title="Edit: ${name}"></span></a>'
		);
		
		// Row attributes
		$this->attributes = array(
			array(),
			array('width' => 230),
			array('width' => 40, 'class' => 'c'),
			array('width' => 40, 'class' => 'c')
		);
	}
	
	// Pages
	public function index()
	{
		// Set title
		$this->title = _('All Groups');
		
		// Find data
		$Groups = $this->FOGCore->getClass('GroupManager')->find();
	
		// Error checking
		if (!count($Groups))
		{
			throw new Exception('No groups found');
		}
		
		// Row data
		foreach ($Groups AS $Group)
		{
			$this->data[] = array(
				'id'		=> $Group->get('id'),
				'name'		=> $Group->get('name'),
				'description'	=> $Group->get('description'),
				'members'	=> $Group->getHostCount()
			);
		}
		
		// Hook
		$this->HookManager->processEvent('GROUP_DATA', array('headerData' => &$this->headerData, 'data' => &$this->data, 'templates' => &$this->templates, 'attributes' => &$this->attributes));
		
		// Output
		$this->render();
	}
	
	public function search()
	{
		// Set title
		$this->title = _('Group Search');
		
		// Set search form
		$this->searchFormURL = 'ajax/group.search.php';

		// Output
		$this->render();
	}
	
	public function add()
	{
		// Hook
		$this->HookManager->processEvent('GROUP_ADD', array('Group' => &$Group));
		
		// POST ?
		if ($this->post)
		{
			try
			{
				// Error checking
				if (empty($_POST['name']))
				{
					throw new Exception('Group Name is required');
				}
				if ($this->FOGCore->getClass('GroupManager')->groupNameExists($_POST['name']))
				{
					throw new Exception('Group Name already exists');
				}
			
				// Define new Image object with data provided
				$Group = new Group(array(
					'name'		=> $_POST['name'],
					'description'	=> $_POST['description'],
					'kernel'	=> $_POST['kern'],
					'kernelArgs'	=> $_POST['args'],
					'primaryDisk'	=> $_POST['dev']
				));
				
				// Save to database
				if ($Group->save())
				{
					// Hook
					$this->HookManager->processEvent('GROUP_ADD_SUCCESS', array('Group' => &$Group));
					
					// Log History event
					$this->FOGCore->logHistory(sprintf('%s: ID: %s, Name: %s', _('Group added'), $Group->get('id'), $Group->get('name')));
				
					// Set session message
					$this->FOGCore->setMessage(_('Group added'));
				
					// Redirect to new entry
					$this->FOGCore->redirect(sprintf('?node=%s&sub=edit&%s=%s', $this->request['node'], $this->id, $Group->get('id')));
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
				$this->HookManager->processEvent('GROUP_ADD_FAIL', array('Group' => &$Group));
				
				// Set session message
				$this->FOGCore->setMessage($e->getMessage());
				
				// Redirect to new entry
				$this->FOGCore->redirect($this->formAction);
			}
		}
		else
		{
			// TODO: Put table rows into variables -> Add hooking
			// TODO: Add tabs with other options
			?>
			<h2><?php print _('Add new Group'); ?></h2>
			<form method="POST" action="<?php print $this->formAction; ?>">
				<table cellpadding=0 cellspacing=0 border=0 width=100%>
					<tr><td><?php print _("Group Name"); ?>:</td><td><input type="text" name="name" value="<?php print $_POST['name']; ?>" /></td></tr>
					<tr><td><?php print _("Group Description"); ?>:</td><td><textarea name="description" rows="5" cols="40"><?php print $_POST['description']; ?></textarea></td></tr>
					<tr><td><?php print _("Group Kernel"); ?>:</td><td><input type="text" name="kern" value="<?php print $_POST['kernel']; ?>" /></td></tr>	
					<tr><td><?php print _("Group Kernel Arguments"); ?>:</td><td><input type="text" name="args" value="<?php print $_POST['kernelArgs']; ?>" /></td></tr>	
					<tr><td><?php print _("Group Primary Disk"); ?>:</td><td><input type="text" name="dev" value="<?php print $_POST['primaryDisk']; ?>" /></td></tr>	
					<tr><td>&nbsp;</td><td><input type="submit" value="<?php print _("Add"); ?>" /></td></tr>
				</table>
			</form>
			<?php
		}
	}

	public function edit()
	{
		$Group = new Group($this->request['id']);
		
		// Hook
		$this->HookManager->processEvent('GROUP_EDIT', array('Group' => &$Group));
		
		// POST ?
		if ($this->post)
		{
			try
			{
				// Error checking
				if (empty($_POST[$this->id]))
				{
					throw new Exception('Group ID is required');
				}
				if (empty($_POST['name']))
				{
					throw new Exception('Group Name is required');
				}
			
				// Define new Image object with data provided
				$Group	->set('name',		$_POST['name'])
					->set('description',	$_POST['description'])
					->set('kernel',		$_POST['kern'])
					->set('kernelArgs',	$_POST['args'])
					->set('primaryDisk',	$_POST['dev']);
			
				// Save to database
				if ($Group->save())
				{
					// Hook
					$this->HookManager->processEvent('GROUP_EDIT_SUCCESS', array('host' => &$Group));
					
					// Log History event
					$this->FOGCore->logHistory(sprintf('Group updated: ID: %s, Name: %s', $Group->get('id'), $Group->get('name')));
				
					// Set session message
					$this->FOGCore->setMessage('Group updated!');
				
					// Redirect to new entry
					$this->FOGCore->redirect(sprintf('?node=%s&sub=edit&%s=%s', $this->request['node'], $this->id, $Group->get('id')));
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
				$this->HookManager->processEvent('GROUP_EDIT_FAIL', array('Group' => &$Group));
				
				// Log History event
				$this->FOGCore->logHistory(sprintf('Group update failed: Name: %s, Error: %s', $_POST['name'], $e->getMessage()));
			
				// Set session message
				$this->FOGCore->setMessage($e->getMessage());
			}
		}
		else
		{
			// TODO: Put table rows into variables -> Add hooking
			// TODO: Add ping lookup + additional macs from original HTML (its awful and messy, needs a rewrite)
			// TODO: Add tabs with other options
			?>
			<h2><?php print _('Modify Group') . ': ' . $Group->get('name'); ?></h2>
			<form method="POST" action="<?php print $this->formAction; ?>">
				<input type="hidden" name="<?php print $this->id; ?>" value="<?php print $this->request['id']; ?>" />
				<table cellpadding=0 cellspacing=0 border=0 width=100%>
					<tr><td><?php print _("Group Name"); ?>:</td><td><input type="text" name="name" value="<?php print $Group->get('name'); ?>" /></td></tr>
					<tr><td><?php print _("Group Description"); ?>:</td><td><textarea name="description" rows="5" cols="40"><?php print $Group->get('description'); ?></textarea></td></tr>
					<tr><td><?php print _("Group Kernel"); ?>:</td><td><input type="text" name="kern" value="<?php print $Group->get('kernel'); ?>" /></td></tr>	
					<tr><td><?php print _("Group Kernel Arguments"); ?>:</td><td><input type="text" name="args" value="<?php print $Group->get('kernelArgs'); ?>" /></td></tr>	
					<tr><td><?php print _("Group Primary Disk"); ?>:</td><td><input type="text" name="dev" value="<?php print $Group->get('primaryDisk'); ?>" /></td></tr>	
					<tr><td>&nbsp;</td><td><input type="submit" value="<?php print _("Update"); ?>" /></td></tr>
				</table>
			</form>
			<?php
		}
	}

	public function delete()
	{
		$Group = new Group($this->request['id']);
		
		// Hook
		$this->HookManager->processEvent('GROUP_ADD', array('Group' => &$Group));
		
		// POST ?
		if ($this->request['confirm'])
		{
			try
			{
				// Error checking
				if (!$Group->destroy())
				{
					throw new Exception(_('Failed to destroy Host'));
				}
				
				// Hook
				$this->HookManager->processEvent('GROUP_DELETE_SUCCESS', array('Group' => &$Group));
				
				// Log History event
				$this->FOGCore->logHistory(sprintf('%s: ID: %s, Name: %s', _('Group deleted'), $Group->get('id'), $Group->get('name')));
				
				// Set session message
				$this->FOGCore->setMessage(sprintf('%s: %s', _('Group deleted'), $Group->get('name')));
				
				// Redirect
				$this->FOGCore->redirect(sprintf('?node=%s', $this->request['node']));
			}
			catch (Exception $e)
			{
				// Hook
				$this->HookManager->processEvent('GROUP_DELETE_FAIL', array('Group' => &$Group));
				
				// Set session message
				$this->FOGCore->setMessage($e->getMessage());
				
				// Redirect
				$this->FOGCore->redirect($this->formAction);
			}
		}
		else
		{			
			// TODO: Put table rows into variables -> Add hooking
			?>
			<p class="C"><?php printf('%s <b>%s</b>?', _('Click on the icon below to delete this group from the FOG database.'), $Group->get('name')); ?></p>
			<p class="C"><a href="<?php print $this->formAction . '&confirm=1'; ?>"><span class="icon icon-kill"></span></a></p>
			<?php
		}
	}
}

// Register page with FOGPageManager
$FOGPageManager->add(new GroupManagementPage());