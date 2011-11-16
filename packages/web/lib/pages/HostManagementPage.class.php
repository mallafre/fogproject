<?php

// Blackout - 12:36 PM 16/11/2011
class HostManagementPage extends FOGPage
{
	// Base variables
	var $name = 'Host Management';
	var $node = 'host';
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
			'<input type="checkbox" name="no" checked="checked" />',
			'',
			_('Host Name'),
			_('MAC'),
			_('IP Address'),
			_('Edit')
		);
		
		// Row templates
		$this->templates = array(
			'<input type="checkbox" name="HID%id%" checked="checked" />',
			'<span class="icon ping"></span>',
			'<a href="?node=host&sub=edit&id=%id%" title="Edit">%name%</a>',
			'%mac%',
			'%ip%',
			'<a href="?node=host&sub=edit&id=%id%"><span class="icon icon-edit" title="Edit: %hostname%"></span></a>'
		);
		
		// Row attributes
		$this->attributes = array(
			array('width' => 22),
			array('width' => 20),
			array(),
			array('width' => 120),
			array('width' => 120),
			array('width' => 40, 'class' => 'c')
		);
	}
	
	// Pages
	public function index()
	{
		// Set title
		$this->title = _('All Hosts');
		
		// Find data
		$Hosts = $this->FOG->getClass('HostManager')->find();
	
		// Error checking
		if (!count($Hosts))
		{
			throw new Exception('No hosts found');
		}
		
		// Row data
		foreach ($Hosts AS $Host)
		{
			$this->data[] = array(
				'id'	=> $Host->get('id'),
				'name'	=> $Host->get('name'),
				'mac'	=> $Host->get('mac')->__toString(),
				'ip'	=> $Host->get('ip')
			);
		}
		
		// Hook
		$this->HookManager->processEvent('HOST_DATA', array('headerData' => &$this->headerData, 'data' => &$this->data, 'templates' => &$this->templates, 'attributes' => &$this->attributes));
		
		// Output
		$this->render();
	}
	
	public function search()
	{
		// Set title
		$this->title = _('Host Search');
		
		// Set search form
		$this->searchFormURL = 'ajax/host.search.php';

		// Output
		$this->render();
	}
	
	public function add()
	{
		// Hook
		$this->HookManager->processEvent('HOST_ADD', array('Host' => &$Host));
		
		// POST ?
		if ($this->post)
		{
			try
			{
				// Error checking
				if (empty($_POST['host']))
				{
					throw new Exception('Hostname is required');
				}
				if (empty($_POST['mac']))
				{
					throw new Exception('MAC Address is required');
				}
			
				// Define new Image object with data provided
				$Host = new Host(array(
					'name'		=> $_POST['host'],
					'description'	=> $_POST['description'],
					'ip'		=> $_POST['ip'],
					'mac'		=> new MACAddress($_POST['mac']),
					'osID'		=> $_POST['os'],
					'imageID'	=> $_POST['image'],
					'kernel'	=> $_POST['kern'],
					'kernelArgs'	=> $_POST['args'],
					'kernelDevice'	=> $_POST['dev'],
					'useAD'		=> ($_POST["domain"] == "on" ? '1' : '0'),
					'ADDomain'	=> $_POST['domainname'],
					'ADOU'		=> $_POST['ou'],
					'ADUser'	=> $_POST['domainuser'],
					'ADPass'	=> $_POST['domainpassword']
				));
				
				// Save to database
				if ($Host->save())
				{
					// Hook
					$this->HookManager->processEvent('HOST_ADD_SUCCESS', array('Host' => &$Host));
					
					// Log History event
					$this->FOG->logHistory(sprintf('%s: ID: %s, Name: %s', _('Host added'), $Host->get('id'), $Host->get('name')));
				
					// Set session message
					$this->FOG->setMessage(_('Host added'));
				
					// Redirect to new entry
					$this->FOG->redirect(sprintf('?node=%s&sub=edit&%s=%s', $this->request['node'], $this->id, $Host->get('id')));
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
				$this->HookManager->processEvent('HOST_ADD_FAIL', array('Host' => &$Host));
				
				// Set session message
				$this->FOG->setMessage($e->getMessage());
				
				// Redirect to new entry
				$this->FOG->redirect($this->formAction);
			}
		}
		else
		{
			// TODO: Put table rows into variables -> Add hooking
			?>
			<h2><?php print _("Add new host definition"); ?></h2>
			<form method="POST" action="<?php print $this->formAction; ?>">
				<input type="hidden" name="add" value="1" />
				<table cellpadding="0" cellspacing="0" border="0" width="100%">
					<tr><td width="35%"><?php print _("Host Name"); ?>:*</td><td><input type="text" name="host" value="<?php print $_POST['host']; ?>" /></td></tr>
					<tr><td><?php print _("Host IP"); ?>:</td><td><input type="text" name="ip" value="<?php print $_POST['ip']; ?>" /></td></tr>
					<tr><td><?php print _("Primary MAC"); ?>:*</td><td><input type="text" id='mac' name="mac" value="<?php print $_POST['mac']; ?>" /> &nbsp; <span id='priMaker'></span> </td></tr>
					<tr><td><?php print _("Host Description"); ?>:</td><td><textarea name="description" rows="5" cols="40"><?php print $_POST['description']; ?></textarea></td></tr>
					<tr><td><?php print _("Host Image"); ?>:</td><td><?php print getImageDropDown( $conn, 'image', $_POST['image'] );  ?></td></tr>
					<tr><td><?php print _("Host OS"); ?>:</td><td><?php print getOSDropDown( $conn, 'os', $_POST['os'] ); ?></td></tr>
					<tr><td><?php print _("Host Kernel"); ?>:</td><td><input type="text" name="kern" value="<?php print $_POST['kern']; ?>" /></td></tr>		
					<tr><td><?php print _("Host Kernel Arguments"); ?>:</td><td><input type="text" name="args" value="<?php print $_POST['args']; ?>" /></td></tr>	
					<tr><td><?php print _("Host Primary Disk"); ?>:</td><td><input type="text" name="dev" value="<?php print $_POST['dev']; ?>" /></td></tr>		
				</table>

				<br />
				<h2><?php print _("Active Directory"); ?></h2>		
				<table cellpadding="0" cellspacing="0" border="0" width="100%">
					<tr><td width="35%"><?php print _("Join Domain after image task"); ?>:</td><td><input id='adEnabled' type="checkbox" name="domain" value="on"<?php print ($_POST['domain'] == 'on' ? ' selected="selected"' : ''); ?> /></td></tr>
					<tr><td><?php print _("Domain name"); ?>:</td><td><input id="adDomain" type="text" name="domainname" value="<?php print $_POST['domainname']; ?>" /></td></tr>				
					<tr><td><?php print _("Organizational Unit"); ?>:</td><td><input id="adOU" type="text" name="ou" value="<?php print $_POST['ou']; ?>" /> <?php print _("(Blank for default)"); ?></td></tr>				
					<tr><td><?php print _("Domain Username"); ?>:</td><td><input id="adUsername" type="text" name="domainuser" value="<?php print $_POST['domainuser']; ?>" /></td></tr>						
					<tr><td><?php print _("Domain Password"); ?>:</td><td><input id="adPassword" type="text" name="domainpassword" value="<?php print $_POST['domainpassword']; ?>" /> <?php print _("(Must be encrypted)"); ?></td></tr>											
					<tr><td>&nbsp;</td><td><input type="submit" value="<?php print _("Add"); ?>" /></td></tr>				
				</table>	
			</form>
			<?php
		}
	}
	
	
	// TODO: FROM USER
	/*
	public function edit()
	{
		$User = new Host($this->request['id']);
		
		// Hook
		$this->HookManager->processEvent('USER_ADD', array('Host' => &$Host));
		
		// POST ?
		if ($this->post)
		{
			try
			{
				// UserManager
				$HostManager = $this->FOG->getClass('HostManager');
				
				// Error checking
				if ($UserCheck = $HostManager->find(array('uName' => $_POST['name'])) && is_array($UserCheck) && $UserCheck = end($UserCheck) && $UserCheck->get('id') != $User->get('id'))
				{
					throw new Exception(_('Username already exists'));
				}
				if ($_POST['password'] && $_POST['password_confirm'])
				{
					if (!$HostManager->isPasswordValid($_POST['password'], $_POST['password_confirm']))
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
					$this->HookManager->processEvent('HOST_UPDATE_SUCCESS', array('Host' => &$Host));
					
					// Log History event
					$this->FOG->logHistory(sprintf('%s: ID: %s, Name: %s', _('User updated'), $User->get('id'), $User->get('name')));
					
					// Set session message
					$this->FOG->setMessage(_('User updated'));
					
					// Redirect to new entry
					$this->FOG->redirect(sprintf('?node=%s&sub=edit&%s=%s', $this->request['node'], $this->id, $User->get('id')));
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
				$this->HookManager->processEvent('HOST_UPDATE_FAIL', array('Host' => &$Host));
				
				// Set session message
				$this->FOG->setMessage($e->getMessage());
				
				// Redirect to new entry
				$this->FOG->redirect($this->formAction);
			}
		}
		else
		{
			// TODO: Put table rows into variables -> Add hooking
			?>
			<form method="POST" action="<?php print $this->formAction; ?>">
				<input type="hidden" name="update" value="<?php print $User->get('id'); ?>" />
				<table cellpadding="0" cellspacing="0" border="0" width="100%">
					<tr><td><?php print _("User Name"); ?>:</td><td><input type="text" name="name" value="<?php print $User->get('name'); ?>" /></td></tr>
					<tr><td><?php print _("New Password"); ?>:</td><td><input type="password" name="password" value="" /></td></tr>
					<tr><td><?php print _("New Password (confirm)"); ?>:</td><td><input type="password" name="password_confirm" value="" /></td></tr>
					<tr><td><?php print _("Mobile/Quick Image Access Only?"); ?></td><td><input type="checkbox" name="isGuest"<?php print ($User->get('type') == User::TYPE_MOBILE ? ' checked="checked"' : ''); ?>></td></tr>
					<tr><td>&nbsp;</td><td><input type="submit" value="<?php print _('Update'); ?>" /></td></tr>
				</table>
			</form>
			<?php
		}
	}
	
	public function delete()
	{
		$User = new Host($this->request['id']);
		
		// Hook
		$this->HookManager->processEvent('USER_ADD', array('Host' => &$Host));
		
		// POST ?
		if ($this->request['confirm'])
		{
			try
			{
				// Error checking
				if (!$User->destroy())
				{
					throw new Exception(_('Failed to destroy User'));
				}
				
				// Hook
				$this->HookManager->processEvent('HOST_DELETE_SUCCESS', array('Host' => &$Host));
				
				// Log History event
				$this->FOG->logHistory(sprintf('%s: ID: %s, Name: %s', _('User deleted'), $User->get('id'), $User->get('name')));
				
				// Set session message
				$this->FOG->setMessage(sprintf('%s: %s', _('User deleted'), $User->get('name')));
				
				// Redirect
				$this->FOG->redirect(sprintf('?node=%s', $this->request['node']));
			}
			catch (Exception $e)
			{
				// Hook
				$this->HookManager->processEvent('HOST_DELETE_FAIL', array('Host' => &$Host));
				
				// Set session message
				$this->FOG->setMessage($e->getMessage());
				
				// Redirect
				$this->FOG->redirect($this->formAction);
			}
		}
		else
		{
			// TODO: Put table rows into variables -> Add hooking
			?>
			<p class="C"><?php printf('%s <b>%s</b>?', _('Are you sure you wish to remove the user'), $User->get('name')); ?></p>
			<p class="C"><a href="<?php print $this->formAction . '&confirm=1'; ?>"><span class="icon icon-kill"></span></a></p>
			<?php
		}
	}
	*/
}

// Register page with FOGPageManager
$FOGPageManager->add(new HostManagementPage());