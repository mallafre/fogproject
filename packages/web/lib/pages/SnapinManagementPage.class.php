<?php

// Blackout - 2:06 PM 9/12/2011
class SnapinManagementPage extends FOGPage
{
	// Base variables
	var $name = 'Snapin Management';
	var $node = 'snapin';
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
			_('Imagename'),
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
		$this->title = _("All Snap-in's");
		
		// Find data
		$Snapins = $this->FOGCore->getClass('SnapinManager')->find();
		
		// Row data
		foreach ($Snapins AS $Snapin)
		{
			$this->data[] = array(
				'id'	=> $Snapin->get('id'),
				'name'	=> $Snapin->get('name')
			);
		}
		
		// Hook
		$this->HookManager->processEvent('SNAPIN_DATA', array('headerData' => &$this->headerData, 'data' => &$this->data, 'templates' => &$this->templates, 'attributes' => &$this->attributes));
		
		// Output
		$this->render();
	}
	
	
	public function search()
	{
		// Set title
		$this->title = _('Search');
		
		// Set search form
		$this->searchFormURL = 'ajax/snapin.search.php';
		
		// Hook
		$this->HookManager->processEvent('SNAPIN_SEARCH');

		// Output
		$this->render();
	}
	
	// STORAGE NODE
	public function add()
	{
		// Set title
		$this->title = _('Add New Snapin');
		
		// Hook
		$this->HookManager->processEvent('SNAPIN_ADD');
		
		// TODO: Put table rows into variables -> Add hooking
		?>
		<form method="POST" action="?node=<?php print $_GET['node']; ?>&sub=<?php print $_GET['sub']; ?>" enctype="multipart/form-data">
		<center><table cellpadding="0" cellspacing="0" border="0" width="100%">
			<tr><td><?php print _("Snapin Name"); ?></td><td><input type="text" name="name" value="" /></td></tr>
			<tr><td><?php print _("Snapin Description"); ?></td><td><textarea name="description" rows="5" cols="65"></textarea></td></tr>
			<tr><td><?php print _("Snapin Run With"); ?></td><td><input type="text" name="rw" value="" /></td></tr>	
			<tr><td><?php print _("Snapin Run With Argument"); ?></td><td><input type="text" name="rwa" /></td></tr>	
			<tr><td><?php print _("Snapin File"); ?></td><td><input type="file" name="snapin" value="" /> <span class="lightColor"> <?php print _("Max Size"); ?>: <?php print ini_get("post_max_size"); ?></span></td></tr>
			<tr><td><?php print _("Snapin Arguments"); ?></td><td><input type="text" name="args" value="" /></td></tr>	
			<tr><td><?php print _("Reboot after install"); ?></td><td><input type="checkbox" name="reboot" /></td></tr>		
			<tr><td colspan=2><center><br /><input type="hidden" name="add" value="1" /><input type="submit" value="<?php print _("Add"); ?>" /></center></td></tr>				
		</table></center>
		</form>
		<?php
	}
	
	public function add_post()
	{
		// Hook
		$this->HookManager->processEvent('SNAPIN_ADD_POST');
		
		// POST
		try
		{
			/*
			if ( ! snapinExists( $conn, $_POST["name"] ) )
			{
				if ( $_FILES["snapin"] != null  )
				{
					$uploadfile = $GLOBALS['FOGCore']->getSetting( "FOG_SNAPINDIR" ) . basename($_FILES['snapin']['name']);
					if ( file_exists( $GLOBALS['FOGCore']->getSetting( "FOG_SNAPINDIR" ) ) )
					{
						if ( is_writable( $GLOBALS['FOGCore']->getSetting( "FOG_SNAPINDIR" ) ) )
						{
							if ( ! file_exists( $uploadfile ) )
							{
								if (move_uploaded_file($_FILES['snapin']['tmp_name'], $uploadfile))					
								{
									$name = mysql_real_escape_string( $_POST["name"] );
									$description = mysql_real_escape_string( $_POST["description"] );
									$args = mysql_real_escape_string( $_POST["args"] );
									$file = mysql_real_escape_string(  $uploadfile );
									$rw = mysql_real_escape_string( $_POST["rw"] );
									$rwa = mysql_real_escape_string( $_POST["rwa"] );
									$blReboot = "0";
									if ( $_POST["reboot"] == "on" )
									{
										$blReboot = "1";
									}
									
									$user = mysql_real_escape_string( $currentUser->get('name') );
									$sql = "insert into snapins(sName, sDesc, sFilePath, sArgs, sCreateDate, sCreator, sReboot, sRunWith, sRunWithArgs) values('$name', '$description', '$file', '$args', NOW(), '$user', '$blReboot', '$rw', '$rwa' )";
									if ( mysql_query( $sql, $conn ) )
									{
										msgBox( _("Snapin Added, you may now add another.") );
										lg( _("Snapin Added"); ?> :: $name
									}
									else
									{
										msgBox( _("Failed to add snapin.") );
										lg( _("Failed to add snapin"); ?> :: $name " . mysql_error()  );
									}
								}
								else
								{
									msgBox( _("Failed to add snapin, file upload failed.") );
									lg( _("Failed to add snapin, file upload failed.")  );							
								}
							}
							else
							{
								msgBox( _("Failed to add snapin, file already exists.") );
								lg( _("Failed to add snapin, file already exists.")  );				
							}
						}
						else
						{
							msgBox( _("Failed to add snapin, snapin directory exists, but isn't writable.") );
							lg( _("Failed to add snapin, snapin directory exists, but isn't writable.")  );					
						}
					}
					else
					{
						msgBox( _("Failed to add snapin, unable to locate snapin directory.") );
						lg( _("Failed to add snapin, unable to locate snapin directory.")  );				
					}
				}
				else
				{
					msgBox( _("Failed to add snapin, no file was uploaded.") );
					lg( _("Failed to add snapin, no file was uploaded.") );			
				}
			}
			*/
		
		
		
			// UserManager
			$UserManager = $this->FOGCore->getClass('UserManager');
			
			// Error checking
			if (count($UserManager->find(array('name' => $_POST['name']))))
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
				'createdBy'	=> $this->FOGCore->get('name')
			));
			
			// Save
			if ($User->save())
			{
				// Hook
				$this->HookManager->processEvent('SNAPIN_ADD_SUCCESS', array('StorageNode' => &$StorageNode));
				
				// Log History event
				$this->FOGCore->logHistory(sprintf('%s: ID: %s, Name: %s', _('User created'), $User->get('id'), $Snapin->get('name')));
				
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
			$this->HookManager->processEvent('SNAPIN_ADD_FAIL', array('StorageNode' => &$StorageNode));
			
			// Log History event
			$this->FOGCore->logHistory(sprintf('%s add failed: Name: %s, Error: %s', _('Storage'), $_POST['name'], $e->getMessage()));
			
			// Set session message
			$this->FOGCore->setMessage($e->getMessage());
			
			// Redirect to new entry
			$this->FOGCore->redirect($this->formAction);
		}
	}
	
	public function edit()
	{
		// Find
		$Snapin = new Snapin($this->request['id']);
		
		// Title
		$this->title = sprintf('%s: %s', _('Edit'), $Snapin->get('name'));
		
		// Hook
		$this->HookManager->processEvent('SNAPIN_ADD', array('StorageNode' => &$StorageNode));
		
		// TODO: Put table rows into variables -> Add hooking
		?>
		<form enctype="multipart/form-data" method="POST" action="?node=$_GET[node]&sub=$_GET[sub]&snapinid=$_GET[snapinid]">
			<input type="hidden" name="update" value="1" />
			<input type="hidden" name="snapinid" value="<?php print $ar["sID"]; ?>" />
			<table cellpadding=0 cellspacing=0 border=0 width=100%>
				<tr><td><?php print _("Snapin Name"); ?></td><td><input type="text" name="name" value="<?php print $Snapin->get('name'); ?>" /></td></tr>
				<tr><td><?php print _("Snapin Description"); ?></td><td><textarea name="description" rows="5" cols="65"><?php print $Snapin->get('description'); ?></textarea></td></tr>
				<tr><td><?php print _("Snapin Run With"); ?></td><td><input type="text" name="rw" value="<?php print $Snapin->get('runWith'); ?>" /></td></tr>
				<tr><td><?php print _("Snapin Run With Arguments"); ?></td><td><input type="text" name="rwa" value="<?php print htmlentities(stripslashes($Snapin->get('runWithArgs'))); ?>" /></td></tr>
				<tr><td><?php print _("Snapin File"); ?></td><td><span id='uploader'><?php print $Snapin->get('file'); ?> <a href="#" id='snapin-upload'><img class="noBorder" src="./images/upload.png" /></a></span></td></tr>
				<tr><td><?php print _("Snapin Arguments"); ?></td><td><input type="text" name="args" value="<?php print $Snapin->get('args'); ?>" /></td></tr>
				<tr><td><?php print _("Reboot after install"); ?></td><td><input type="checkbox" name="reboot"<?php print ($Snapin->get('reboot') ? ' checked="checked"' : ''); ?> /></td></tr>
				<tr><td>&nbsp;</td><td><input type="submit" value="<?php print _("Update"); ?>" /></td></tr>
			</table>
		</form>
		<?php
	}
	
	public function edit_post()
	{
		// Find
		$Snapin = new Snapin($this->request['id']);
		
		// Hook
		$this->HookManager->processEvent('SNAPIN_EDIT_POST', array('StorageNode' => &$StorageNode));
		
		// POST
		try
		{
			/*
			if ( ! snapinExists( $conn, $_POST["name"], $_POST["snapinid"] ) )
			{
			
				$snap = mysql_real_escape_string( $_POST["snapinid"] );
				$name = mysql_real_escape_string( $_POST["name"] );
				$description = mysql_real_escape_string( $_POST["description"] );
				$args = mysql_real_escape_string( $_POST["args"] );
				$rw = mysql_real_escape_string( $_POST["rw"] );
				$rwa = mysql_real_escape_string( $_POST["rwa"] );
				$blReboot = "0";
				if ( $_POST["reboot"] == "on" )
				{
					$blReboot = "1";
				}
				
				$sql = "update snapins set sRunWithArgs = '$rwa', sRunWith = '$rw', sName = '$name', sDesc = '$description', sArgs = '$args', sReboot = '$blReboot' where sID = '$snap'";
				if ( mysql_query( $sql, $conn ) )
				{
					if ( $_FILES["snap"] != null && count( $_FILES["snap"]) > 0 )
					{
						$uploadfile = $GLOBALS['FOGCore']->getSetting( "FOG_SNAPINDIR" ) . basename($_FILES['snap']['name']);
						if ( file_exists( $GLOBALS['FOGCore']->getSetting( "FOG_SNAPINDIR" ) ) )
						{	
							$sql = "SELECT sFilePath from snapins where sID = '" . mysql_real_escape_string( $_GET["snapinid"] ) . "'";
							$res = mysql_query( $sql, $conn ) or die( mysql_error() );
							while( $ar = mysql_fetch_array( $res ) )
							{
								@unlink( $ar["sFilePath"] );
								if (move_uploaded_file($_FILES['snap']['tmp_name'], $uploadfile))					
								{		
									$sql = "UPDATE snapins set sFilePath = '" . mysql_real_escape_string( $uploadfile ) . "' where sID = '" . mysql_real_escape_string( $_GET["snapinid"] ) . "'";
									if ( mysql_query( $sql, $conn ) )
									{
										msgBox( _("Snapin Updated!") );
										lg( _("snapin updated.")  );					
									}
									else
									{
										msgBox( _("Database Error"); ?>: " . mysql_error() );
										lg( _("Database Error (during snapin update)"); ?>: " . mysql_error()  );									
									}
								}
								else
								{
									msgBox( _("Failed to update snapin, file upload failed.") );
									lg( _("Failed to update snapin, file upload failed.")  );							
								}
							}		
						}
						else
						{
							msgBox( _("Failed to update snapin, unable to locate snapin directory.") );
							lg( _("Failed to update snapin, unable to locate snapin directory.")  );				
						}
					}

					lg( _("Snapin updated"); ?> :: $name
				}
				else
				{
					msgBox( _("Failed to update Snapin.") );
					lg( _("Failed to update Snapin"); ?> :: $name " . mysql_error()  );
				}
			}
			*/
			
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
				$this->HookManager->processEvent('SNAPIN_UPDATE_SUCCESS', array('StorageNode' => &$StorageNode));
				
				// Log History event
				$this->FOGCore->logHistory(sprintf('%s: ID: %s, Name: %s', _('User updated'), $User->get('id'), $Snapin->get('name')));
				
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
			$this->HookManager->processEvent('SNAPIN_UPDATE_FAIL', array('StorageNode' => &$StorageNode));
			
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
		$Snapin = new Snapin($this->request['id']);
		
		// Title
		$this->title = sprintf('%s: %s', _('Remove'), $Snapin->get('name'));
		
		// Hook
		$this->HookManager->processEvent('SNAPIN_DELETE', array('StorageNode' => &$StorageNode));
		
		// TODO: Put table rows into variables -> Add hooking
		?>
		<center><table cellpadding=0 cellspacing=0 border=0 width=100%>
		<tr><td><?php print _("Snapin Name"); ?></td><td><?php print $Snapin->get('name'); ?></td></tr>
		<tr><td><?php print _("Snapin Description"); ?></td><td><?php print $Snapin->get('description'); ?></td></tr>
		<tr><td><?php print _("Snapin File"); ?></td><td><?php print $Snapin->get('file'); ?></td></tr>
		<tr><td><?php print _("Snapin Arguments"); ?></td><td><?php print $Snapin->get('args'); ?></td></tr>
		<tr><td><?php print _("Reboot after install"); ?></td><td><?php print ($Snapin->get('reboot') ? 'Yes' : 'No'); ?></td></tr>
		<tr><td colspan=2><center><br /><form method="POST" action="?node=$_GET[node]&sub=$_GET[sub]&rmsnapinid=$_GET[rmsnapinid]&confirm=1&killfile=1"><input type="submit" value="<?php print _("Delete snapin definition, and snapin file."); ?>" /></form></center></td></tr>
		</table></center>
		<?php
	}
	
	public function delete_post()
	{
		// Find
		$Snapin = new Snapin($this->request['id']);
		
		// Hook
		$this->HookManager->processEvent('SNAPIN_DELETE_POST', array('StorageNode' => &$StorageNode));
		
		// POST
		try
		{
			/*
			$output = "";
			?>
			<h2><?php print _("Snapin Removal Results"); ?></h2>
			<?php
			if ( $_GET["killfile"] == "1" )
			{
				$sql = "select sFilePath from snapins where sID = '" . $rmid . "'";
				$res = mysql_query( $sql, $conn ) or die( mysql_error() );
				$file = null;
				while( $ar = mysql_fetch_array( $res ) )
				{
					$file = $ar["sFilePath"];
				}
				
				if ( file_exists( $file ) )
				{
					if ( unlink( $file ) )
					{
						$output .= _("snapin file has been deleted."); ?><br />";
					}
					else
					{	
						$output .= _("Failed to delete snapin file."); ?><br />";
					}
				}
				else
					$output .= _("Failed to locate snapin file."); ?><br />";
			}
			$sql = "delete from snapins where sID = '" . $rmid . "'";
			if ( mysql_query( $sql, $conn ) )
			{
				$output .= _("Snapin definition has been removed."); ?><br />";
				lg( _("Snapin deleted"); ?> :: $_GET[delid]
			}
			else
				$output .= mysql_error();
				
			echo $output;
			*/
		
			// Error checking
			if (!$User->destroy())
			{
				throw new Exception(_('Failed to destroy User'));
			}
			
			// Hook
			$this->HookManager->processEvent('SNAPIN_DELETE_SUCCESS', array('StorageNode' => &$StorageNode));
			
			// Log History event
			$this->FOGCore->logHistory(sprintf('%s: ID: %s, Name: %s', _('User deleted'), $User->get('id'), $Snapin->get('name')));
			
			// Set session message
			$this->FOGCore->setMessage(sprintf('%s: %s', _('User deleted'), $Snapin->get('name')));
			
			// Redirect
			$this->FOGCore->redirect(sprintf('?node=%s', $this->request['node']));
		}
		catch (Exception $e)
		{
			// Hook
			$this->HookManager->processEvent('SNAPIN_DELETE_FAIL', array('StorageNode' => &$StorageNode));
			
			// Log History event
			$this->FOGCore->logHistory(sprintf('%s %s: ID: %s, Name: %s', _('User'), _('deleted'), $User->get('id'), $Snapin->get('name')));
			
			// Set session message
			$this->FOGCore->setMessage($e->getMessage());
			
			// Redirect
			$this->FOGCore->redirect($this->formAction);
		}
	}
}

// Register page with FOGPageManager
$FOGPageManager->register(new SnapinManagementPage());