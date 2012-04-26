<?php

// Blackout - 12:38 PM 25/09/2011
class UserManagementPage extends FOGPage
{
	// Base variables
	var $name = 'User Management';
	var $node = 'users';
	var $id = 'id';
	
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
			_('Username'),
			_('Edit')
		);
		
		// Row templates
		$this->templates = array(
			sprintf('<a href="?node=%s&sub=edit&%s=${id}">${name}</a>', $this->node, $this->id),
			sprintf('<a href="?node=%s&sub=edit&%s=${id}"><span class="icon icon-edit"></span></a>', $this->node, $this->id)
		);
		
		// Row attributes
		$this->attributes = array(
			array(),
			array('class' => 'c', 'width' => '55'),
		);
	}
	
	// Pages
	public function index()
	{
		// Set title
		$this->title = _('All Users');
		
		// Find data
		$Users = $this->FOGCore->getClass('UserManager')->find();
		
		// Row data
		foreach ($Users AS $User)
		{
			$this->data[] = array(
				'id'	=> $User->get('id'),
				'name'	=> $User->get('name')
			);
		}
		
		// Hook
		$this->HookManager->processEvent('USER_DATA', array('headerData' => &$this->headerData, 'data' => &$this->data, 'templates' => &$this->templates, 'attributes' => &$this->attributes));
		
		// Output
		$this->render();
	}
	
	public function search()
	{
		// Set title
		$this->title = _('Search');
		
		// Set search form
		//$this->searchFormURL = 'ajax/users.search.php';
		$this->searchFormURL = sprintf('%s?node=%s&sub=search', $_SERVER['PHP_SELF'], $this->node);
		
		// Hook
		$this->HookManager->processEvent('USER_SEARCH');

		// Output
		$this->render();
	}
	
	public function search_post()
	{
		// Variables
		$keyword = preg_replace('#%+#', '%', '%' . preg_replace('#[[:space:]]#', '%', $this->REQUEST['crit']) . '%');
		$findWhere = array(
			'name'		=> $keyword
		);
	
		// Find data -> Push data
		foreach ($this->FOGCore->getClass('UserManager')->find($findWhere, 'OR') AS $User)
		{
			$this->data[] = array(
				'id'	=> $User->get('id'),
				'name'	=> $User->get('name')
			);
		}
		
		// Hook
		$this->HookManager->processEvent('USER_DATA', array('headerData' => &$this->headerData, 'data' => &$this->data, 'templates' => &$this->templates, 'attributes' => &$this->attributes));

		// Output
		$this->render();
	}
	
	public function add()
	{
		// Set title
		$this->title = _('New User');
		
		// Hook
		$this->HookManager->processEvent('USER_ADD');
		
		// TODO: Put table rows into variables -> Add hooking
		?>
		<h2><?php print _("Add new user account"); ?></h2>
		<form method="POST" action="<?php print $this->formAction; ?>">
			<input type="hidden" name="add" value="1" />
			<table cellpadding="0" cellspacing="0" border="0" width="100%">
				<tr><td><?php print _("User Name"); ?></td><td><input type="text" name="name" value="" autocomplete="off" /></td></tr>
				<tr><td><?php print _("User Password"); ?></td><td><input type="password" name="password" value="" autocomplete="off" /></td></tr>
				<tr><td><?php print _("User Password (confirm)"); ?></td><td><input type="password" name="password_confirm" value="" autocomplete="off" /></td></tr>
				<tr><td><?php print _("Mobile/Quick Image Access Only?"); ?></td><td><input type="checkbox" name="isGuest" autocomplete="off" /></td></tr>
				<tr><td>&nbsp;</td><td><input type="submit" value="<?php print _('Create User'); ?>" /></td></tr>
			</table>
		</form>
		<?php
	}
	
	public function add_post()
	{
		// Hook
		$this->HookManager->processEvent('USER_ADD_POST');
		
		// POST
		try
		{
			// UserManager
			$UserManager = $this->FOGCore->getClass('UserManager');
			
			// Error checking
			if ($UserManager->exists($_POST['name']))
			{
				throw new Exception(_('Username already exists'));
			}
			if (!$UserManager->isPasswordValid($_POST['password'], $_POST['password_confirm']))
			{
				throw new Exception(_('Password is invalid'));
			}
			
			// Create new Object
			$User = new User(array(
				'name'		=> $_POST['name'],
				'type'		=> ($_POST['isGuest'] == 'on' ? '1' : '0'),
				'password'	=> $_POST['password'],
				'createdBy'	=> $_SESSION['FOG_USERNAME']
			));
			
			// Save
			if ($User->save())
			{
				// Hook
				$this->HookManager->processEvent('USER_ADD_SUCCESS', array('User' => &$User));
				
				// Log History event
				$this->FOGCore->logHistory(sprintf('%s: ID: %s, Name: %s', _('User created'), $User->get('id'), $User->get('name')));
				
				// Set session message
				$this->FOGCore->setMessage(_('User created'));
				
				// Redirect to new entry
				$this->FOGCore->redirect(sprintf('?node=%s&sub=edit&%s=%s', $this->request['node'], $this->id, $User->get('id')));
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
			$this->HookManager->processEvent('USER_ADD_FAIL', array('User' => &$User));
			
			// Log History event
			$this->FOGCore->logHistory(sprintf('%s add failed: Name: %s, Error: %s', _('User'), $_POST['name'], $e->getMessage()));
			
			// Set session message
			$this->FOGCore->setMessage($e->getMessage());
			
			// Redirect to new entry
			$this->FOGCore->redirect($this->formAction);
		}
	}
	
	public function edit()
	{
		// Find
		$User = new User($this->request['id']);
		
		// Title
		$this->title = sprintf('%s: %s', _('Edit'), $User->get('name'));
		
		// Hook
		$this->HookManager->processEvent('USER_EDIT', array('User' => &$User));
		
		// TODO: Put table rows into variables -> Add hooking
		?>
		<form method="POST" action="<?php print $this->formAction; ?>">
			<input type="hidden" name="update" value="<?php print $User->get('id'); ?>" />
			<table cellpadding="0" cellspacing="0" border="0" width="100%">
				<tr><td><?php print _("User Name"); ?></td><td><input type="text" name="name" value="<?php print $User->get('name'); ?>" /></td></tr>
				<tr><td><?php print _("New Password"); ?></td><td><input type="password" name="password" value="" /></td></tr>
				<tr><td><?php print _("New Password (confirm)"); ?></td><td><input type="password" name="password_confirm" value="" /></td></tr>
				<tr><td><?php print _("Mobile/Quick Image Access Only?"); ?></td><td><input type="checkbox" name="isGuest"<?php print ($User->get('type') == User::TYPE_MOBILE ? ' checked="checked"' : ''); ?>></td></tr>
				<tr><td>&nbsp;</td><td><input type="submit" value="<?php print _('Update'); ?>" /></td></tr>
			</table>
		</form>
		<?php
	}
	
	public function edit_post()
	{
		// Find
		$User = new User($this->request['id']);
		
		// Hook
		$this->HookManager->processEvent('USER_EDIT_POST', array('User' => &$User));
		
		// POST
		try
		{
			// UserManager
			$UserManager = $this->FOGCore->getClass('UserManager');
			
			// Error checking
			if ($UserManager->exists($_POST['name'], $User->get('id')))
			{
				throw new Exception(_('Username already exists'));
			}
			if ($_POST['password'] && $_POST['password_confirm'])
			{
				if (!$UserManager->isPasswordValid($_POST['password'], $_POST['password_confirm']))
				{
					throw new Exception(_('Password is invalid'));
				}
			}
			
			// Update User Object
			$User	->set('name',		$_POST['name'])
				->set('type',		($_POST['isGuest'] == 'on' ? '1' : '0'));
			
			// Set new password if password was passed
			if ($_POST['password'] && $_POST['password_confirm'])
			{
				$User->set('password',	$_POST['password']);
			}
			
			// Save
			if ($User->save())
			{
				// Hook
				$this->HookManager->processEvent('USER_UPDATE_SUCCESS', array('User' => &$User));
				
				// Log History event
				$this->FOGCore->logHistory(sprintf('%s: ID: %s, Name: %s', _('User updated'), $User->get('id'), $User->get('name')));
				
				// Set session message
				$this->FOGCore->setMessage(_('User updated'));
				
				// Redirect to new entry
				$this->FOGCore->redirect(sprintf('?node=%s&sub=edit&%s=%s', $this->request['node'], $this->id, $User->get('id')));
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
			$this->HookManager->processEvent('USER_UPDATE_FAIL', array('User' => &$User));
			
			// Log History event
			$this->FOGCore->logHistory(sprintf('%s update failed: Name: %s, Error: %s', _('User'), $_POST['name'], $e->getMessage()));
			
			// Set session message
			$this->FOGCore->setMessage($e->getMessage());
			
			// Redirect to new entry
			$this->FOGCore->redirect($this->formAction);
		}
	}
	
	public function delete()
	{
		// Find
		$User = new User($this->request['id']);
		
		// Title
		$this->title = sprintf('%s: %s', _('Remove'), $User->get('name'));
		
		// Hook
		$this->HookManager->processEvent('USER_DELETE', array('User' => &$User));
		
		// TODO: Put table rows into variables -> Add hooking
		?>
		<p class="c"><?php printf('%s <b>%s</b>?', _('Are you sure you wish to remove the user'), $User->get('name')); ?></p>
		<form method="post" action="<?php print $this->formAction; ?>" class="c">
		<input type="submit" value="<?php print $this->title; ?>" />
		</form>
		<?php
	}
	
	public function delete_post()
	{
		// Find
		$User = new User($this->request['id']);
		
		// Hook
		$this->HookManager->processEvent('USER_DELETE_POST', array('User' => &$User));
		
		// POST
		try
		{
			// Error checking
			if (!$User->destroy())
			{
				throw new Exception(_('Failed to destroy User'));
			}
			
			// Hook
			$this->HookManager->processEvent('USER_DELETE_SUCCESS', array('User' => &$User));
			
			// Log History event
			$this->FOGCore->logHistory(sprintf('%s: ID: %s, Name: %s', _('User deleted'), $User->get('id'), $User->get('name')));
			
			// Set session message
			$this->FOGCore->setMessage(sprintf('%s: %s', _('User deleted'), $User->get('name')));
			
			// Redirect
			$this->FOGCore->redirect(sprintf('?node=%s', $this->request['node']));
		}
		catch (Exception $e)
		{
			// Hook
			$this->HookManager->processEvent('USER_DELETE_FAIL', array('User' => &$User));
			
			// Log History event
			$this->FOGCore->logHistory(sprintf('%s %s: ID: %s, Name: %s', _('User'), _('deleted'), $User->get('id'), $User->get('name')));
			
			// Set session message
			$this->FOGCore->setMessage($e->getMessage());
			
			// Redirect
			$this->FOGCore->redirect($this->formAction);
		}
	}
}

// Register page with FOGPageManager
$FOGPageManager->register(new UserManagementPage());