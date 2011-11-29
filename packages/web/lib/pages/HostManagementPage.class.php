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
			'<input type="checkbox" name="HID${id}" checked="checked" />',
			'<span class="icon ping"></span>',
			'<a href="?node=host&sub=edit&id=${id}" title="Edit">${name}</a>',
			'${mac}',
			'${ip}',
			'<a href="?node=host&sub=edit&id=${id}"><span class="icon icon-edit" title="Edit: ${hostname}"></span></a>'
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
		$Hosts = $this->FOGCore->getClass('HostManager')->find();
	
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
					$this->FOGCore->logHistory(sprintf('%s: ID: %s, Name: %s', _('Host added'), $Host->get('id'), $Host->get('name')));
				
					// Set session message
					$this->FOGCore->setMessage(_('Host added'));
				
					// Redirect to new entry
					$this->FOGCore->redirect(sprintf('?node=%s&sub=edit&%s=%s', $this->request['node'], $this->id, $Host->get('id')));
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
			<h2><?php print _("Add new host definition"); ?></h2>
			<form method="POST" action="<?php print $this->formAction; ?>">
				<input type="hidden" name="add" value="1" />
				<table cellpadding="0" cellspacing="0" border="0" width="100%">
					<tr><td width="35%"><?php print _("Host Name"); ?>:*</td><td><input type="text" name="host" value="<?php print $_POST['host']; ?>" /></td></tr>
					<tr><td><?php print _("Host IP"); ?>:</td><td><input type="text" name="ip" value="<?php print $_POST['ip']; ?>" /></td></tr>
					<tr><td><?php print _("Primary MAC"); ?>:*</td><td><input type="text" id="mac" name="mac" value="<?php print $_POST['mac']; ?>" /> &nbsp; <span id="priMaker"></span> </td></tr>
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
					<tr><td width="35%"><?php print _("Join Domain after image task"); ?>:</td><td><input id="adEnabled" type="checkbox" name="domain" value="on"<?php print ($_POST['domain'] == 'on' ? ' selected="selected"' : ''); ?> /></td></tr>
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

	public function edit()
	{
		$Host = new Host($this->request['id']);
		
		// Hook
		$this->HookManager->processEvent('HOST_EDIT', array('Host' => &$Host));
		
		// POST ?
		if ($this->post)
		{
			try
			{
				// Error checking
				if (empty($_POST['id']))
				{
					throw new Exception('Host ID is required');
				}
				
				// Variables
				$mac = new MACAddress($_POST['mac']);
				
				if (!$mac->isValid())
				{
					throw new Exception('MAC Address is not valid');
				}
			
				// Define new Image object with data provided
				$Host	->set('name',		$_POST['host'])
					->set('description',	$_POST['description'])
					->set('ip',		$_POST['ip'])
					->set('mac',		$mac)
					->set('osID',		$_POST['os'])
					->set('imageID',	$_POST['image'])
					->set('kernel',		$_POST['kern'])
					->set('kernelArgs',	$_POST['args'])
					->set('kernelDevice',	$_POST['dev']);
				
				// Add Additional MAC Addresses
				$Host->set('additionalMACs', (array)$_POST['additionalMACs']);
			
				// Save to database
				if ($Host->save())
				{
					// Hook
					$this->HookManager->processEvent('HOST_EDIT_SUCCESS', array('host' => &$Host));
					
					// Log History event
					$this->FOGCore->logHistory(sprintf('Host updated: ID: %s, Name: %s', $Host->get('id'), $Host->get('name')));
				
					// Set session message
					$this->FOGCore->setMessage('Host updated!');
				
					// Redirect to new entry
					$this->FOGCore->redirect(sprintf('?node=%s&sub=edit&%s=%s', $this->request['node'], $this->id, $Host->get('id')));
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
				$this->HookManager->processEvent('HOST_EDIT_FAIL', array('Host' => &$Host));
				
				// Log History event
				$this->FOGCore->logHistory(sprintf('Host update failed: Name: %s, Error: %s', $_POST['name'], $e->getMessage()));
			
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
			<h2><?php print _("Edit host definition"); ?></h2>
			<form method="POST" action="<?php print $this->formAction; ?>">
				<input type="hidden" name="id" value="<?php print $this->request['id']; ?>" />
				<table cellpadding="0" cellspacing="0" border="0" width="100%">
					<tr><td width="35%"><?php print _("Host Name"); ?>:*</td><td><input type="text" name="host" value="<?php print $Host->get('name'); ?>" /></td></tr>
					<tr><td><?php print _("Host IP"); ?>:</td><td><input type="text" name="ip" value="<?php print $Host->get('ip'); ?>" /></td></tr>
					<tr><td><?php print _("Primary MAC"); ?>:*</td><td><input type="text" id="mac" name="mac" value="<?php print $Host->get('mac'); ?>" /> &nbsp; <span id="priMaker"></span> </td></tr>
					<tr><td><?php print _("Host Description"); ?>:</td><td><textarea name="description" rows="5" cols="40"><?php print $Host->get('description'); ?></textarea></td></tr>
					<tr><td><?php print _("Host Image"); ?>:</td><td><?php print $this->FOGCore->getClass('ImageManager')->buildSelectBox($Host->get('imageID')); ?></td></tr>
					<tr><td><?php print _("Host OS"); ?>:</td><td><?php print $this->FOGCore->getClass('OSManager')->buildSelectBox($Host->get('osID')); ?></td></tr>
					<tr><td><?php print _("Host Kernel"); ?>:</td><td><input type="text" name="kern" value="<?php print $Host->get('kern'); ?>" /></td></tr>		
					<tr><td><?php print _("Host Kernel Arguments"); ?>:</td><td><input type="text" name="args" value="<?php print $Host->get('args'); ?>" /></td></tr>	
					<tr><td><?php print _("Host Primary Disk"); ?>:</td><td><input type="text" name="dev" value="<?php print $Host->get('dev'); ?>" /></td></tr>
					<tr><td>&nbsp;</td><td><input type="submit" value="<?php print _("Update"); ?>" /></td></tr>
				</table>
			</form>
			<?php
		}
	}

	public function delete()
	{
		$Host = new Host($this->request['id']);
		
		// Hook
		$this->HookManager->processEvent('HOST_ADD', array('Host' => &$Host));
		
		// POST ?
		if ($this->request['confirm'])
		{
			try
			{
				// Error checking
				if (!$Host->destroy())
				{
					throw new Exception(_('Failed to destroy Host'));
				}
				
				// Hook
				$this->HookManager->processEvent('HOST_DELETE_SUCCESS', array('Host' => &$Host));
				
				// Log History event
				$this->FOGCore->logHistory(sprintf('%s: ID: %s, Name: %s', _('Host deleted'), $Host->get('id'), $Host->get('name')));
				
				// Set session message
				$this->FOGCore->setMessage(sprintf('%s: %s', _('Host deleted'), $Host->get('name')));
				
				// Redirect
				$this->FOGCore->redirect(sprintf('?node=%s', $this->request['node']));
			}
			catch (Exception $e)
			{
				// Hook
				$this->HookManager->processEvent('HOST_DELETE_FAIL', array('Host' => &$Host));
				
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
			<p class="C"><?php printf('%s <b>%s</b>?', _('Click on the icon below to delete this host from the FOG database.'), $Host->get('name')); ?></p>
			<p class="C"><a href="<?php print $this->formAction . '&confirm=1'; ?>"><span class="icon icon-kill"></span></a></p>
			<?php
		}
	}
}

// Register page with FOGPageManager
$FOGPageManager->add(new HostManagementPage());