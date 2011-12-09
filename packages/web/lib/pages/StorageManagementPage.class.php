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
			$this->data[] = array(
				'id'	=> $StorageNode->get('id'),
				'name'	=> $StorageNode->get('name')
			);
		}
		
		// Header row
		$this->headerData = array(
			_('Username'),
			_('Edit')
		);
		
		// Row templates
		$this->templates = array(
			'<a href="?node=storage&sub=edit-storage-node&id=${id}">${name}</a>',
			'<a href="?node=storage&sub=edit-storage-node&id=${id}"><span class="icon icon-edit"></span></a>'
		);
		
		// Row attributes
		$this->attributes = array(
			array(),
			array('class' => 'c', 'width' => '55'),
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
		<form method="POST" action="?node=<?php print $_GET["node"]; ?>&sub=<?php print $_GET["sub"]; ?>&storagenodeid=<?php print $_GET["storagenodeid"]; ?>">
			<table cellpadding=0 cellspacing=0 border=0 width=100%>
				<tr><td width="25%"><?php print _("Storage Node Name"); ?></td><td><input type="text" name="name" value="<?php print $ar["ngmMemberName"]; ?>" /></td></tr>
				<tr><td><?php print _("Storage Node Description"); ?></td><td><textarea name="description" rows="5" cols="65"><?php print $ar["ngmMemberDescription"]; ?></textarea></td></tr>
				<tr><td><?php print _("IP Address"); ?></td><td><input type="text" name="ip" value="<?php print $ar["ngmHostname"]; ?>" /></td></tr>				
				<tr><td><?php print _("Max Clients"); ?></td><td><input type="text" name="clients" value="<?php print $ar["ngmMaxClients"]; ?>" /></td></tr>				
				<tr><td><?php print _("Is Master Node"); ?></td><td><input type="checkbox" name="ismaster" $checked />&nbsp;&nbsp;<span class="icon icon-help hand" title="<?php print _("Use extreme caution with this setting!  This setting, if used incorrectly could potentially wipe out all of your images stored on all current storage nodes.  The 'Is Master Node' setting defines which node is the distributor of the images.  If you add a blank node, meaning a node that has no images on it, and set it to master, it will distribute its store, which is empty, to all hosts in the group"); ?>"></span></td></tr>	
				<tr><td><?php print _("Storage Group"); ?></td><td><?php print $this->FOGCore->getClass('StorageGroupManager')->buildSelectBox(); ?></td></tr>
				<tr><td><?php print _("Image Location"); ?></td><td><input type="text" name="imageloc" value="<?php print $ar["ngmRootPath"]; ?>" /></td></tr>														
				<tr><td><?php print _("Is Enabled"); ?></td><td><input type="checkbox" name="isenabled" checked="checked" /></td></tr>					
				<tr><td><?php print _("Management Username"); ?></td><td><input type="text" name="username" value="<?php print $ar["ngmUser"]; ?>" /></td></tr>				
				<tr><td><?php print _("Management Password"); ?></td><td><input type="text" name="password" value="<?php print $ar["ngmPass"]; ?>" /></td></tr>								
				<tr><td>&nbsp;</td><td><input type="hidden" name="add" value="1" /><input type="submit" value="<?php print _("Add"); ?>" /></td></tr>				
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
			/*
			if ( ! doesStorageNodeExist( $conn, $_POST["name"] ) )
			{
				$name = mysql_real_escape_string( $_POST["name"] );
				$description = mysql_real_escape_string( $_POST["description"] );
				$ip = mysql_real_escape_string( $_POST["ip"] );
				$maxclients = mysql_real_escape_string( $_POST["clients"] );
				$ismaster = "0";
				if ( $_POST["ismaster"] == "on" )
					$ismaster = "1";
				$storagegroup = mysql_real_escape_string( $_POST["storagegroup"] );
				$imageloc = mysql_real_escape_string( $_POST["imageloc"] );
				if ( ! endsWith( $imageloc, "/" ) && $imageloc != null )
					$imageloc .= "/";
				$isenabled = "0";
				if ( $_POST["isenabled"] == "on" )
					$isenabled = "1";	
				$muser = mysql_real_escape_string( $_POST["username"] );
				$mpass = mysql_real_escape_string( $_POST["password"] );
		
				if ( ($ismaster == "1" && $_POST["confirm"] == "1") || $ismaster == "0" )
				{
					$sql = "INSERT INTO 
							nfsGroupMembers (ngmMemberName, ngmMemberDescription, ngmIsMasterNode, ngmGroupID, ngmRootPath, ngmIsEnabled, ngmHostname, ngmMaxClients, ngmUser, ngmPass)
						VALUES
							('$name', '$description', '$ismaster', '$storagegroup', '$imageloc', '$isenabled', '$ip', '$maxclients', '$muser', '$mpass' )";
					if ( mysql_query( $sql, $conn ) )
					{
						if ( $ismaster == "1" && $storagegroup != null  )
						{
							// only one master per group, remove previous master.
							$lastid = mysql_insert_id($conn);
							$sql = "UPDATE nfsGroupMembers SET ngmIsMasterNode = '0' WHERE ngmGroupID = '$storagegroup' and ngmID <> '$lastid'";
							if ( ! mysql_query( $sql, $conn ) )
								die( mysql_error() );
				
						}
						msgBox( _("Storage node created."); ?><br /><?php print _("You may now add another.") );
						lg( _("node Added"); ?> :: $name
					}
					else
					{
						msgBox( _("Failed to update Storage Node.") );
						lg( _("Failed to update Storage Node"); ?> :: $name " . mysql_error()  );
					}
				}
				else if ( $ismaster == "1" )
				{
					$blShow = false;
					echo ("<div class=\"warn\">");
						echo _("You have chosen to set this node as the master node in this storage group.  "); ?><b><?php print _("Caution"); ?>: </b> <?php print _("This is a very dangerous action, and should only be done if you known what you are doing.  Settings this node as master could potentially wipe out all images on all other nodes in this storage group."); ?><p><strong><?php print _("Are you sure you wish to do this?"); ?></strong></p>";
						<form action=\"?node=" . $_GET["node"]; ?>&sub=" . $_GET["sub"]; ?>&storagenodeid=" . $_GET["storagenodeid"]; ?>\" method=\"post\">
						<input type=\"hidden\" name=\"add\" value=\"1\" />
						<input type=\"hidden\" name=\"name\" value=\"" . $_POST["name"]; ?>\" />
						<input type=\"hidden\" name=\"description\" value=\"" . $_POST["description"]; ?>\" />						
						<input type=\"hidden\" name=\"ip\" value=\"" . $_POST["ip"]; ?>\" />						
						<input type=\"hidden\" name=\"clients\" value=\"" . $_POST["clients"]; ?>\" />						
						<input type=\"hidden\" name=\"ismaster\" value=\"" . $_POST["ismaster"]; ?>\" />						
						<input type=\"hidden\" name=\"storagegroup\" value=\"" . $_POST["storagegroup"]; ?>\" />						
						<input type=\"hidden\" name=\"imageloc\" value=\"" . $_POST["imageloc"]; ?>\" />						
						<input type=\"hidden\" name=\"isenabled\" value=\"" . $_POST["isenabled"]; ?>\" />
						<input type=\"hidden\" name=\"username\" value=\"" . $_POST["username"]; ?>\" />						
						<input type=\"hidden\" name=\"password\" value=\"" . $_POST["password"]; ?>\" />												
						<input type=\"hidden\" name=\"confirm\" value=\"1\" />												
						<input type=\"submit\" value=\"<?php print _("Yes, make the node master."); ?>\" />&nbsp;&nbsp;&nbsp;&nbsp;<input type=\"button\" onclick=\"javascript:history.back(-1)\" value=\"<?php print _("No, don't add node as master."); ?>\" /></form>
					echo ("</div>
				}
			}
			*/
		
		
		
			// UserManager
			$UserManager = $this->FOGCore->getClass('UserManager');
			
			// Error checking
			if (count($UserManager->find(array('uName' => $_POST['name']))))
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
				$this->HookManager->processEvent('STORAGE_NODE_ADD_SUCCESS', array('StorageNode' => &$StorageNode));
				
				// Log History event
				$this->FOGCore->logHistory(sprintf('%s: ID: %s, Name: %s', _('User created'), $User->get('id'), $StorageGroup->get('name')));
				
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
			$this->HookManager->processEvent('STORAGE_NODE_ADD_FAIL', array('StorageNode' => &$StorageNode));
			
			// Log History event
			$this->FOGCore->logHistory(sprintf('%s add failed: Name: %s, Error: %s', _('Storage'), $_POST['name'], $e->getMessage()));
			
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
		$this->HookManager->processEvent('STORAGE_NODE_ADD', array('StorageNode' => &$StorageNode));
		
		// TODO: Put table rows into variables -> Add hooking
		?>
		<form method="POST" action="?node=<?php print $_GET["node"] . "&sub=" . $_GET["sub"] . "&storagenodeid=" . $_GET["storagenodeid"]; ?> ?>">
			<input type="hidden" name="storagenodeid" value="<?php print $_GET["storagenodeid"]; ?>" />
			<input type="hidden" name="update" value="1" />
			<table cellpadding=0 cellspacing=0 border=0 width=100%>
				<tr><td><?php print _("Storage Node Name"); ?></td><td><input type="text" name="name" value="<?php print $ar["ngmMemberName"]; ?>" /></td></tr>
				<tr><td><?php print _("Storage Node Description"); ?></td><td><textarea name="description" rows="5" cols="65"><?php print $ar["ngmMemberDescription"]; ?></textarea></td></tr>
				<tr><td><?php print _("IP Address"); ?></td><td><input type="text" name="ip" value="<?php print $ar["ngmHostname"]; ?>" /></td></tr>				
				<tr><td><?php print _("Max Clients"); ?></td><td><input type="text" name="clients" value="<?php print $ar["ngmMaxClients"]; ?>" /></td></tr>				
				<tr><td><?php print _("Is Master Node"); ?></td><td><input type="checkbox" name="ismaster"<?php print ($StorageNode->get('isMaster') ? ' checked="checked"' : ''); ?> />&nbsp;&nbsp;<span class="icon icon-help hand" title="" . _("Use extreme caution with this setting!  This setting, if used incorrectly could potentially wipe out all of your images stored on all current storage nodes.  The 'Is Master Node' setting defines which node is the distributor of the images.  If you add a blank node, meaning a node that has no images on it, and set it to master, it will distribute its store, which is empty, to all hosts in the group.") . ""></span></td></tr>
				<tr><td><?php print _("Storage Group"); ?></td><td><?php print $this->FOGCore->getClass('StorageGroupManager')->buildSelectBox($StorageNode->get('storageGroupID')); ?></td></tr>
				<tr><td><?php print _("Image Location"); ?></td><td><input type="text" name="imageloc" value="<?php print $ar["ngmRootPath"]; ?>" /></td></tr>
				<tr><td><?php print _("Is Enabled"); ?></td><td><input type="checkbox" name="isenabled"<?php print ($StorageNode->get('isEnabled') ? ' checked="checked"' : ''); ?> /></td></tr>					
				<tr><td><?php print _("Management Username"); ?></td><td><input type="text" name="username" value="<?php print $ar["ngmUser"]; ?>" /></td></tr>				
				<tr><td><?php print _("Management Password"); ?></td><td><input type="text" name="password" value="<?php print $ar["ngmPass"]; ?>" /></td></tr>
				<tr><td><?php print _("Graph Bandwidth on Dashboard?"); ?></td><td><input type="radio" name="graphBandwidth" id="graphBandwidthYes" value="1" /><label for="graphBandwidthYes">Yes</label> <input type="radio" name="graphBandwidth" id="graphBandwidthNo" value="0" /><label for="graphBandwidthNo">No</label></td></tr>
				<tr><td>&nbsp;</td><td><input type="submit" value="<?php print _("Update"); ?>" /></td></tr>				
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
			/*
			if ( ! doesStorageNodeExist( $conn, $_POST["name"], $_POST["storagenodeid"] ) )
			{
				$ngmid = mysql_real_escape_string( $_POST["storagenodeid"] );
				$name = mysql_real_escape_string( $_POST["name"] );
				$description = mysql_real_escape_string( $_POST["description"] );
				$ip = mysql_real_escape_string( $_POST["ip"] );
				$maxclients = mysql_real_escape_string( $_POST["clients"] );
				$ismaster = "0";
				if ( $_POST["ismaster"] == "on" )
					$ismaster = "1";
				$storagegroup = mysql_real_escape_string( $_POST["storagegroup"] );
				$imageloc = mysql_real_escape_string( $_POST["imageloc"] );
				if ( ! endsWith( $imageloc, "/" ) && $imageloc != null )
					$imageloc .= "/";			
				$isenabled = "0";
				if ( $_POST["isenabled"] == "on" )
					$isenabled = "1";	
				$muser = mysql_real_escape_string( $_POST["username"] );
				$mpass = mysql_real_escape_string( $_POST["password"] );
				
				if ( is_numeric( $ngmid ) )
				{
					// detect a change in master node status
					$sql = "SELECT
							ngmIsMasterNode
						FROM 
							nfsGroupMembers
						WHERE 
							ngmID = '$ngmid'";
					$res = mysql_query( $sql, $conn ) or die( mysql_error() );
					$blCurIsMast;
					while( $ar = mysql_fetch_array( $res ) )
					{
						$blCurIsMast = ($ar["ngmIsMasterNode"] == "1");
					}
					
					$blOkToUpdate = false;
					if ( $ismaster == "0" )
						$blOkToUpdate = true;
					else
					{
						if ( $blCurIsMast && ( $ismaster == "1" ) ) 
							$blOkToUpdate = true;
						else if ( ! $blCurIsMast && ( $ismaster == "0" ) )
							$blOkToUpdate = true;
					}
					
					if( $_POST["confirm"] == "1"  ) $blOkToUpdate = true;
					
					if ( $blOkToUpdate )
					{
						$sql = "UPDATE 
								nfsGroupMembers 
							SET 
								ngmMemberName = '$name', 
								ngmMemberDescription = '$description', 
								ngmIsMasterNode = '$ismaster', 
								ngmGroupID = '$storagegroup', 
								ngmRootPath = '$imageloc', 
								ngmIsEnabled = '$isenabled', 
								ngmHostname = '$ip', 
								ngmMaxClients = '$maxclients', 
								ngmUser = '$muser',
								ngmPass = '$mpass'
							WHERE 
								ngmID = '$ngmid'";

						if ( mysql_query( $sql, $conn ) )
						{
							if ( $ismaster == "1" && $storagegroup != null  )
							{				
								// only one master per group, remove previous master.
								$sql = "UPDATE nfsGroupMembers SET ngmIsMasterNode = '0' WHERE ngmGroupID = '$storagegroup' and ngmID <> '$ngmid'";
								if ( ! mysql_query( $sql, $conn ) )
									die( mysql_error() );					
							}
							msgBox(_('Storage Node Updated') . ": $name");
							lg(_('Storage Node Updated') . ": $name");
						}
						else
						{
							msgBox(_('Failed to update Storage Node'));
							lg(_('Failed to update Storage Node') . ": $name, Error: " . mysql_error());
						}
					}
					else
					{
						$blShow = false;
						echo ("<div class=\"warn\">");
							echo _("You have chosen to set this node as the master node in this storage group."); ?>  <b><?php print _("Caution"); ?>: </b> <?php print _("This is a very dangerous action, and should only be done if you known what you are doing.  Settings this node as master could potentially wipe out all images on all other nodes in this storage group."); ?><p><strong><?php print _("Are you sure you wish to do this?"); ?></strong></p>";
							<form action=\"?node=" . $_GET["node"]; ?>&sub=" . $_GET["sub"]; ?>&storagenodeid=" . $_GET["storagenodeid"]; ?>\" method=\"post\">
							<input type=\"hidden\" name=\"update\" value=\"1\" />
							<input type=\"hidden\" name=\"name\" value=\"" . $_POST["name"]; ?>\" />
							<input type=\"hidden\" name=\"description\" value=\"" . $_POST["description"]; ?>\" />						
							<input type=\"hidden\" name=\"ip\" value=\"" . $_POST["ip"]; ?>\" />						
							<input type=\"hidden\" name=\"clients\" value=\"" . $_POST["clients"]; ?>\" />						
							<input type=\"hidden\" name=\"ismaster\" value=\"" . $_POST["ismaster"]; ?>\" />						
							<input type=\"hidden\" name=\"storagegroup\" value=\"" . $_POST["storagegroup"]; ?>\" />						
							<input type=\"hidden\" name=\"imageloc\" value=\"" . $_POST["imageloc"]; ?>\" />						
							<input type=\"hidden\" name=\"isenabled\" value=\"" . $_POST["isenabled"]; ?>\" />
							<input type=\"hidden\" name=\"username\" value=\"" . $_POST["username"]; ?>\" />						
							<input type=\"hidden\" name=\"password\" value=\"" . $_POST["password"]; ?>\" />												
							<input type=\"hidden\" name=\"storagenodeid\" value=\"" . $_POST["storagenodeid"]; ?>\" />
							<input type=\"hidden\" name=\"confirm\" value=\"1\" />												
							<input type=\"submit\" value=\"<?php print _("Yes, make the node master."); ?>\" />&nbsp;&nbsp;&nbsp;&nbsp;<input type=\"button\" onclick=\"javascript:history.back(-1)\" value=\"<?php print _("No, don't add node as master."); ?>\" /></form>
						echo ("</div>				
					}
				}
				else
					msgBox( _("Failed to update storage node.") );
			}
			*/
			
			// UserManager
			$UserManager = $this->FOGCore->getClass('UserManager');
			
			// Error checking
			if ($UserCheck = $UserManager->find(array('uName' => $_POST['name'])) && is_array($UserCheck) && $UserCheck = end($UserCheck) && $UserCheck->get('id') != $User->get('id'))
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
				$this->HookManager->processEvent('STORAGE_NODE_UPDATE_SUCCESS', array('StorageNode' => &$StorageNode));
				
				// Log History event
				$this->FOGCore->logHistory(sprintf('%s: ID: %s, Name: %s', _('User updated'), $User->get('id'), $StorageGroup->get('name')));
				
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
			$this->HookManager->processEvent('STORAGE_NODE_UPDATE_FAIL', array('StorageNode' => &$StorageNode));
			
			// Log History event
			$this->FOGCore->logHistory(sprintf('%s update failed: Name: %s, Error: %s', _('User'), $_POST['name'], $e->getMessage()));
			
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
		$this->title = sprintf('%s: %s', _('Remove'), $StorageGroup->get('name'));
		
		// Hook
		$this->HookManager->processEvent('STORAGE_NODE_DELETE', array('StorageNode' => &$StorageNode));
		
		// TODO: Put table rows into variables -> Add hooking
		?>
		<table cellpadding=0 cellspacing=0 border=0 width=100%>
			<tr><td><?php print _("Storage Node Name"); ?></td><td><?php print $ar["ngmMemberName"]; ?></td></tr>
			<tr><td><?php print _("Storage Node Description"); ?></td><td><?php print $ar["ngmMemberDescription"]; ?></td></tr>
			<tr><td>&nbsp;</td><td><form method=\"POST\" action=\"?node=" . $_GET["node"]; ?>&sub=" . $_GET["sub"]; ?>&rmsnid=" . $_GET["rmsnid"]; ?>&confirm=1\"><input type=\"submit\" value=\"<?php print _("Delete Storage Node Definition"); ?>\" /></form></center></td></tr>				
		</table>
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
			/*
			$output = "";
			?>
			<h2><?php print _("Storage Node Removal Results"); ?></h2>
			<?php
			$sql = "DELETE FROM nfsGroupMembers WHERE ngmID = '" . $rmid . "'";
			if (mysql_query($sql, $conn))
			{
				$output .= _("Storage Node definition has been removed");
				lg(_("Storage Group deleted") . ": $rmid");				
			}
			else
			{
				$output .= mysql_error();
			}
				
			echo $output;
			*/
		
			// Error checking
			if (!$User->destroy())
			{
				throw new Exception(_('Failed to destroy User'));
			}
			
			// Hook
			$this->HookManager->processEvent('STORAGE_NODE_DELETE_SUCCESS', array('StorageNode' => &$StorageNode));
			
			// Log History event
			$this->FOGCore->logHistory(sprintf('%s: ID: %s, Name: %s', _('User deleted'), $User->get('id'), $StorageGroup->get('name')));
			
			// Set session message
			$this->FOGCore->setMessage(sprintf('%s: %s', _('User deleted'), $StorageGroup->get('name')));
			
			// Redirect
			$this->FOGCore->redirect(sprintf('?node=%s', $this->request['node']));
		}
		catch (Exception $e)
		{
			// Hook
			$this->HookManager->processEvent('STORAGE_NODE_DELETE_FAIL', array('StorageNode' => &$StorageNode));
			
			// Log History event
			$this->FOGCore->logHistory(sprintf('%s %s: ID: %s, Name: %s', _('User'), _('deleted'), $User->get('id'), $StorageGroup->get('name')));
			
			// Set session message
			$this->FOGCore->setMessage($e->getMessage());
			
			// Redirect
			$this->FOGCore->redirect($this->formAction);
		}
	}
	
	// STORAGE GROUP
	public function storage_groups()
	{
		// Set title
		$this->title = _('All Storage Nodes');
		
		// Find data
		$StorageGroups = $this->FOGCore->getClass('StorageGroupManager')->find();
		
		// Row data
		foreach ($StorageGroups AS $StorageGroup)
		{
			$this->data[] = array(
				'id'	=> $StorageGroup->get('id'),
				'name'	=> $StorageGroup->get('name')
			);
		}
		
		// Header row
		$this->headerData = array(
			_('Username'),
			_('Edit')
		);
		
		// Row templates
		$this->templates = array(
			'<a href="?node=storage&sub=edit-storage-group&id=${id}">${name}</a>',
			'<a href="?node=storage&sub=edit-storage-group&id=${id}"><span class="icon icon-edit"></span></a>'
		);
		
		// Row attributes
		$this->attributes = array(
			array(),
			array('class' => 'c', 'width' => '55'),
		);
		
		// Hook
		$this->HookManager->processEvent('STORAGE_NODE_DATA', array('headerData' => &$this->headerData, 'data' => &$this->data, 'templates' => &$this->templates, 'attributes' => &$this->attributes));
		
		// Output
		$this->render();
	}
	
	public function add_storage_group()
	{
		// Set title
		$this->title = _('Add New Storage Group');
		
		// Hook
		$this->HookManager->processEvent('STORAGE_NODE_ADD');
		
		// TODO: Put table rows into variables -> Add hooking
		?>
		<form method="POST" action="?node=<?php print $_GET['node']; ?>&sub=<?php print $_GET['sub']; ?>">
			<table cellpadding=0 cellspacing=0 border=0 width=100%>
				<tr><td><?php print _("Storage Group Name"); ?></td><td><input type="text" name="name" value="" /></td></tr>
				<tr><td><?php print _("Storage Group Description"); ?></td><td><textarea name="description" rows="5" cols="65"></textarea></td></tr>
				<tr><td>&nbsp;</td><td><input type="hidden" name="add" value="1" /><input type="submit" value="<?php print _("Add"); ?>" /></center></td></tr>				
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
			/*
			if ( ! doesStorageGroupExist( $conn, $_POST["name"] ) )
			{
				$name = mysql_real_escape_string( $_POST["name"] );
				$description = mysql_real_escape_string( $_POST["description"] );

				$sql = "INSERT INTO nfsGroups( ngName, ngDesc ) values('$name', '$description')";
				if ( mysql_query( $sql, $conn ) )
				{
					msgBox( _("Storage Group created."); ?><br /><?php print _("You may now add another.") );
					lg( _("Image Added"); ?> :: $name
				}
				else
				{
					msgBox( _("Failed to add storage group.") );
					lg( _("Failed to add storage group"); ?> :: $name " . mysql_error()  );
				}
			}

			*/
		
		
		
			// UserManager
			$UserManager = $this->FOGCore->getClass('UserManager');
			
			// Error checking
			if (count($UserManager->find(array('uName' => $_POST['name']))))
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
				$this->HookManager->processEvent('STORAGE_GROUP_ADD_SUCCESS', array('StorageGroup' => &$StorageGroup));
				
				// Log History event
				$this->FOGCore->logHistory(sprintf('%s: ID: %s, Name: %s', _('User created'), $User->get('id'), $StorageGroup->get('name')));
				
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
			$this->HookManager->processEvent('STORAGE_GROUP_ADD_FAIL', array('StorageGroup' => &$StorageGroup));
			
			// Log History event
			$this->FOGCore->logHistory(sprintf('%s add failed: Name: %s, Error: %s', _('Storage'), $_POST['name'], $e->getMessage()));
			
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
		$this->HookManager->processEvent('STORAGE_GROUP_ADD', array('StorageGroup' => &$StorageGroup));
		
		// TODO: Put table rows into variables -> Add hooking
		?>
		<form method="POST" action="?node="<?php print $_GET["node"] . "&sub=" . $_GET["sub"] . "&storagegroupid=" . $_GET["storagegroupid"]; ?>">
			<input type="hidden" name="update" value="1" />
			<input type="hidden" name="storagegroupid" value="<?php print $_GET["storagegroupid"]; ?>" />
			<table cellpadding=0 cellspacing=0 border=0 width=100%>
				<tr><td><?php print _("Storage Group Name"); ?></td><td><input type="text" name="name" value="<?php print $StorageGroup->get('name'); ?>" /></td></tr>
				<tr><td><?php print _("Storage Group Description"); ?></td><td><textarea name="description" rows="5" cols="65"><?php print $StorageGroup->get('description'); ?></textarea></td></tr>
				<tr><td>&nbsp;</td><td><input type="submit" value="<?php print _("Update"); ?>" /></td></tr>				
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
			/*
			if ( ! doesStorageGroupExist( $conn, $_POST["name"], $_POST["storagegroupid"] ) )
			{
				$ngid = mysql_real_escape_string( $_POST["storagegroupid"] );
				$name = mysql_real_escape_string( $_POST["name"] );
				$description = mysql_real_escape_string( $_POST["description"] );
				$sql = "UPDATE nfsGroups set ngName = '$name', ngDesc = '$description' WHERE ngID = '$ngid'";
				if ( mysql_query( $sql, $conn ) )
				{
					msgBox(_('Storage Group Updated') . ": $name");
					lg(_('Storage Group Updated') . ": $name");
				}
				else
				{
					msgBox(_('Failed to Storage Group'));
					lg(_('Failed to update Storage Group') . ": $name, Error: " . mysql_error());
				}
			}	
			*/
			// UserManager
			$UserManager = $this->FOGCore->getClass('UserManager');
			
			// Error checking
			if ($UserCheck = $UserManager->find(array('uName' => $_POST['name'])) && is_array($UserCheck) && $UserCheck = end($UserCheck) && $UserCheck->get('id') != $User->get('id'))
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
				$this->HookManager->processEvent('STORAGE_GROUP_UPDATE_SUCCESS', array('StorageGroup' => &$StorageGroup));
				
				// Log History event
				$this->FOGCore->logHistory(sprintf('%s: ID: %s, Name: %s', _('User updated'), $User->get('id'), $StorageGroup->get('name')));
				
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
			$this->HookManager->processEvent('STORAGE_GROUP_UPDATE_FAIL', array('StorageGroup' => &$StorageGroup));
			
			// Log History event
			$this->FOGCore->logHistory(sprintf('%s update failed: Name: %s, Error: %s', _('User'), $_POST['name'], $e->getMessage()));
			
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
		<table cellpadding=0 cellspacing=0 border=0 width=100%>
			<tr><td><?php print _("Storage Group Name"); ?></td><td><?php print $StorageGroup->get('name'); ?></td></tr>
			<tr><td><?php print _("Storage Group Description"); ?></td><td><?php print $StorageGroup->get('description'); ?></font></td></tr>
			<tr><td>&nbsp;</td><td><form method="POST" action="?node=" . $_GET["node"] . "&sub=" . $_GET["sub"] . "&rmsgid=" . $_GET["rmsgid"] . "&confirm=1"><input type="submit" value="<?php print _("Delete Storage Definition"); ?>" /></form></center></td></tr>				
		</table>
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
			/*
			$output = "";
			?>
			<h2><?php print _("Storage Group Removal Results"); ?></h2>
			<?php
			$sql = "DELETE FROM nfsGroups WHERE ngID = '" . $rmid . "'";
			if ( mysql_query( $sql, $conn ) )
			{
				$output .= _("Storage Group definition has been removed."); ?><br />";
				lg( _("Storage Group deleted"); ?> :: $rmid				
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
			$this->HookManager->processEvent('STORAGE_GROUP_DELETE_SUCCESS', array('StorageGroup' => &$StorageGroup));
			
			// Log History event
			$this->FOGCore->logHistory(sprintf('%s: ID: %s, Name: %s', _('User deleted'), $User->get('id'), $StorageGroup->get('name')));
			
			// Set session message
			$this->FOGCore->setMessage(sprintf('%s: %s', _('User deleted'), $StorageGroup->get('name')));
			
			// Redirect
			$this->FOGCore->redirect(sprintf('?node=%s', $this->request['node']));
		}
		catch (Exception $e)
		{
			// Hook
			$this->HookManager->processEvent('STORAGE_GROUP_DELETE_FAIL', array('StorageGroup' => &$StorageGroup));
			
			// Log History event
			$this->FOGCore->logHistory(sprintf('%s %s: ID: %s, Name: %s', _('User'), _('deleted'), $User->get('id'), $StorageGroup->get('name')));
			
			// Set session message
			$this->FOGCore->setMessage($e->getMessage());
			
			// Redirect
			$this->FOGCore->redirect($this->formAction);
		}
	}
}

// Register page with FOGPageManager
$FOGPageManager->add(new StorageManagementPage());