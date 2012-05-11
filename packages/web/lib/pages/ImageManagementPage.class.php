<?php

// Blackout - 10:41 AM 1/12/2011
class ImageManagementPage extends FOGPage
{
	// Base variables
	var $name = 'Image Management';
	var $node = 'images';
	var $id = 'imageid';
	
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
			_('Image Name'),
			_('Storage Group'),
			_('O/S'),
			''
		);
		
		// Row templates
		$this->templates = array(
			sprintf('<a href="?node=%s&sub=edit&%s=${id}" title="%s">${name}</a>', $this->node, $this->id, _('Edit')),
			sprintf('${storageGroup}'),
			sprintf('${os}'),
			sprintf('<a href="?node=%s&sub=edit&%s=${id}" title="%s"><span class="icon icon-edit"></span></a> <a href="?node=%s&sub=delete&%s=${id}" title="%s"><span class="icon icon-delete"></span></a>', $this->node, $this->id, _('Edit'), $this->node, $this->id, _('Delete'))
		);
		
		// Row attributes
		$this->attributes = array(
			array(),
			array('width' => '100'),
			array('width' => '100'),
			array('class' => 'c', 'width' => '50'),
		);
	}
	
	// Pages
	public function index()
	{
		// Set title
		$this->title = _('All Images');
		
		// Find data
		$Images = $this->FOGCore->getClass('ImageManager')->find();
		
		// Row data
		foreach ($Images AS $Image)
		{
			$this->data[] = array(
				'id'		=> $Image->get('id'),
				'name'		=> $Image->get('name'),
				'description'	=> $Image->get('description'),
				'storageGroup'	=> $Image->getStorageGroup()->get('name'),
				'storageGroupID'=> $Image->get('storageGroupID'),
				'osID'		=> $Image->get('osID'),
				'os'		=> $Image->getOS()->get('name')
			);
		}
		
		// Hook
		$this->HookManager->processEvent('IMAGE_DATA', array('headerData' => &$this->headerData, 'data' => &$this->data, 'templates' => &$this->templates, 'attributes' => &$this->attributes));
		
		// Output
		$this->render();
	}
	
	public function search()
	{
		// Set title
		$this->title = _('Search');
		
		// Set search form
		$this->searchFormURL = sprintf('%s?node=%s&sub=search', $_SERVER['PHP_SELF'], $this->node);
		
		// Hook
		$this->HookManager->processEvent('IMAGE_SEARCH');

		// Output
		$this->render();
	}
	
	public function search_post()
	{
		// Variables
		$keyword = preg_replace('#%+#', '%', '%' . preg_replace('#[[:space:]]#', '%', $this->REQUEST['crit']) . '%');
		
		// Find data -> Push data
		foreach ($this->FOGCore->getClass('ImageManager')->find(array('name' => $keyword)) AS $Image)
		{
			$this->data[] = array(
				'id'		=> $Image->get('id'),
				'name'		=> $Image->get('name'),
				'description'	=> $Image->get('description'),
				'storageGroup'	=> $Image->getStorageGroup()->get('name'),
				'storageGroupID'=> $Image->get('storageGroupID'),
				'osID'		=> $Image->get('osID'),
				'os'		=> $Image->getOS()->get('name')
			);
		}
		
		// Hook
		$this->HookManager->processEvent('IMAGE_DATA', array('headerData' => &$this->headerData, 'data' => &$this->data, 'templates' => &$this->templates, 'attributes' => &$this->attributes));

		// Output
		$this->render();
	}
	
	public function add()
	{
		// Set title
		$this->title = _('New Image');
		
		// Hook
		$this->HookManager->processEvent('IMAGE_ADD');
		
		// TODO: Put table rows into variables -> Add hooking
		?>
		<h2><?php print _("Add new image definition"); ?></h2>
		<form method="POST" action="<?php print $this->formAction; ?>">
		<input type="hidden" name="add" value="1" />
		<table cellpadding="0" cellspacing="0" border="0" width="100%">
			<tr><td><?php print _("Image Name"); ?></td><td><input type="text" name="name" id="iName" onblur="duplicateImageName();" value="<?php print $_POST['name']; ?>" /></td></tr>
			<tr><td><?php print _("Image Description"); ?></td><td><textarea name="description" rows="5" cols="65"><?php print $_POST['description']; ?></textarea></td></tr>
			<tr><td><?php print _("Storage Group"); ?></td><td><?php print $this->FOGCore->getClass('StorageGroupManager')->buildSelectBox(1); ?></td></tr>
			<tr><td><?php print _("Operating System"); ?></td><td><?php print $this->FOGCore->getClass('OSManager')->buildSelectBox($_POST['os']); ?></td></tr>
			<tr><td><?php print _("Image Path"); ?></td><td>/images/ <input type="text" name="file" id="iFile" value="<?php print $_POST['file']; ?>" /></td></tr>
			<tr><td><?php print _("Image Type"); ?></td><td><?php print $this->FOGCore->getClass('ImageTypeManager')->buildSelectBox(); ?> <span class="icon icon-help" title="TODO!"></span></td></tr>				
			<tr><td colspan=2><center><input type="submit" value="<?php print _("Add"); ?>" /></center></td></tr>				
		</table>
		</form>
		<?php
	}
	
	public function add_post()
	{
		// Hook
		$this->HookManager->processEvent('IMAGE_ADD_POST');
		
		// POST
		try
		{
			// Error checking
			if (empty($_POST['name']))
			{
				throw new Exception('An image name is required!');
			}
			if ($this->FOGCore->getClass('ImageManager')->exists($_POST['name']))
			{
				throw new Exception('An image already exists with this name!');
			}
			if (empty($_POST['file']))
			{
				throw new Exception('An image file name is required!');
			}
			if (empty($_POST['storagegroup']))
			{
				throw new Exception('A Storage Group is required!');
			}
			if (empty($_POST['os']))
			{
				throw new Exception('An Operating System is required!');
			}
			if (empty($_POST['imagetype']) && $_POST['imagetype'] != '0')
			{
				throw new Exception('An image type is required!');
			}
			
			// Create new Object
			$Image = new Image(array(
				'name'		=> $_POST['name'],
				'description'	=> $_POST['description'],
				'storageGroupID'=> $_POST['storagegroup'],
				'osID'		=> $_POST['os'],
				'path'		=> $_POST['file'],
				'imageTypeID'	=> $_POST['imagetype']
			));
			
			// Save
			if ($Image->save())
			{
				// Hook
				$this->HookManager->processEvent('IMAGE_ADD_SUCCESS', array('Image' => &$Image));
				
				// Log History event
				$this->FOGCore->logHistory(sprintf('%s: ID: %s, Name: %s', _('Image created'), $Image->get('id'), $Image->get('name')));
				
				// Set session message
				$this->FOGCore->setMessage(_('Image created'));
				
				// Redirect to new entry
				$this->FOGCore->redirect(sprintf('?node=%s&sub=edit&%s=%s', $this->request['node'], $this->id, $Image->get('id')));
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
			$this->HookManager->processEvent('IMAGE_ADD_FAIL', array('Image' => &$Image));
			
			// Log History event
			$this->FOGCore->logHistory(sprintf('%s add failed: Name: %s, Error: %s', _('Image'), $_POST['name'], $e->getMessage()));
			
			// Set session message
			$this->FOGCore->setMessage($e->getMessage());
			
			// Redirect to new entry
			$this->FOGCore->redirect($this->formAction);
		}
	}
	
	public function edit()
	{
		// Find
		$Image = new Image($this->request['id']);
		
		// Title - set title for page title in window
		$this->title = sprintf('%s: %s', _('Edit'), $Image->get('name'));
		// But disable displaying in content
		$this->titleEnabled = false;
		
		// Hook
		$this->HookManager->processEvent('IMAGE_ADD', array('Image' => &$Image));
		
		// TODO: Put table rows into variables -> Add hooking
		?>
		<h2><?php print _("Add new image definition"); ?></h2>
		<form method="POST" action="<?php print $this->formAction; ?>">
		<input type="hidden" name="add" value="1" />
		<table cellpadding="0" cellspacing="0" border="0" width="100%">
			<tr><td><?php print _("Image Name"); ?></td><td><input type="text" name="name" id="iName" onblur="duplicateImageName();" value="<?php print $Image->get('name'); ?>" /></td></tr>
			<tr><td><?php print _("Image Description"); ?></td><td><textarea name="description" rows="5" cols="65"><?php print $Image->get('description'); ?></textarea></td></tr>
			<tr><td><?php print _("Storage Group"); ?></td><td><?php print $this->FOGCore->getClass('StorageGroupManager')->buildSelectBox($Image->get('storageGroupID')); ?></td></tr>
			<tr><td><?php print _("Operating System"); ?></td><td><?php print $this->FOGCore->getClass('OSManager')->buildSelectBox($Image->get('osID')); ?></td></tr>
			<tr><td><?php print _("Image Path"); ?></td><td>/images/ <input type="text" name="file" id="iFile" value="<?php print $Image->get('path'); ?>" /></td></tr>
			<tr><td><?php print _("Image Type"); ?></td><td><?php print $this->FOGCore->getClass('ImageTypeManager')->buildSelectBox($Image->get('imageTypeID')); ?></td></tr>				
			<tr><td colspan=2><center><input type="submit" value="<?php print _("Update"); ?>" /></center></td></tr>				
		</table>
		</form>
		<?php
	}
	
	public function edit_post()
	{
		// Find
		$Image = new Image($this->request['id']);
		
		// Hook
		$this->HookManager->processEvent('IMAGE_EDIT_POST', array('Image' => &$Image));
		
		// POST
		try
		{
			// Error checking
			if (empty($_POST['name']))
			{
				throw new Exception('An image name is required!');
			}
			if ($this->FOGCore->getClass('ImageManager')->exists($_POST['name'], $Image->get('id')))
			{
				throw new Exception('An image already exists with this name!');
			}
			if (empty($_POST['file']))
			{
				throw new Exception('An image file name is required!');
			}
			if (empty($_POST['storagegroup']))
			{
				throw new Exception('A Storage Group is required!');
			}
			if (empty($_POST['os']))
			{
				throw new Exception('An Operating System is required!');
			}
			if (empty($_POST['imagetype']) && $_POST['imagetype'] != '0')
			{
				throw new Exception('An image type is required!');
			}
			
			// Update Object
			$Image	->set('name',		$_POST['name'])
				->set('description',	$_POST['description'])
				->set('storageGroupID',	$_POST['storagegroup'])
				->set('osID',		$_POST['os'])
				->set('path',		$_POST['file'])
				->set('imageTypeID',	$_POST['imagetype']);
			
			// Save
			if ($Image->save())
			{
				// Hook
				$this->HookManager->processEvent('IMAGE_UPDATE_SUCCESS', array('Image' => &$Image));
				
				// Log History event
				$this->FOGCore->logHistory(sprintf('%s: ID: %s, Name: %s', _('Image updated'), $Image->get('id'), $Image->get('name')));
				
				// Set session message
				$this->FOGCore->setMessage(_('Image updated'));
				
				// Redirect to new entry
				$this->FOGCore->redirect(sprintf('?node=%s&sub=edit&%s=%s', $this->request['node'], $this->id, $Image->get('id')));
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
			$this->HookManager->processEvent('IMAGE_UPDATE_FAIL', array('Image' => &$Image));
			
			// Log History event
			$this->FOGCore->logHistory(sprintf('%s update failed: Name: %s, Error: %s', _('Image'), $_POST['name'], $e->getMessage()));
			
			// Set session message
			$this->FOGCore->setMessage($e->getMessage());
			
			// Redirect
			$this->FOGCore->redirect($this->formAction);
		}
	}
	
	public function delete()
	{
		// Find
		$Image = new Image($this->request['id']);
		
		// Title
		$this->title = sprintf('%s: %s', _('Remove'), $Image->get('name'));
		
		// Hook
		$this->HookManager->processEvent('IMAGE_DELETE', array('Image' => &$Image));
		
		// TODO: Put table rows into variables -> Add hooking
		?>
		<p class="c"><?php printf('%s <b>%s</b>?', _('Please confirm you want to delete'), $Image->get('name')); ?></p>
		<form method="post" action="<?php print $this->formAction; ?>" class="c">
			<input type="submit" value="<?php print $this->title; ?>" />
		</form>
		<?php
	}
	
	public function delete_post()
	{
		// Find
		$Image = new Image($this->request['id']);
		
		// Hook
		$this->HookManager->processEvent('IMAGE_DELETE_POST', array('Image' => &$Image));
		
		// POST
		try
		{
			// Error checking
			if (!$Image->destroy())
			{
				throw new Exception(_('Failed to destroy Object'));
			}
			
			// Hook
			$this->HookManager->processEvent('IMAGE_DELETE_SUCCESS', array('Image' => &$Image));
			
			// Log History event
			$this->FOGCore->logHistory(sprintf('%s: ID: %s, Name: %s', _('Image deleted'), $Image->get('id'), $Image->get('name')));
			
			// Set session message
			$this->FOGCore->setMessage(sprintf('%s: %s', _('Image deleted'), $Image->get('name')));
			
			// Redirect
			$this->FOGCore->redirect(sprintf('?node=%s', $this->request['node']));
		}
		catch (Exception $e)
		{
			// Hook
			$this->HookManager->processEvent('IMAGE_DELETE_FAIL', array('Image' => &$Image));
			
			// Log History event
			$this->FOGCore->logHistory(sprintf('%s %s: ID: %s, Name: %s', _('Image'), _('deleted'), $Image->get('id'), $Image->get('name')));
			
			// Set session message
			$this->FOGCore->setMessage($e->getMessage());
			
			// Redirect
			$this->FOGCore->redirect($this->formAction);
		}
	}
}

// Register page with FOGPageManager
$FOGPageManager->register(new ImageManagementPage());