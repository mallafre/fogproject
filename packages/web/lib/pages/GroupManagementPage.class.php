<?php

// Blackout - 9:51 AM 1/12/2011
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
		$this->HookManager->processEvent('GROUP_ADD');
		
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
	
	public function add_post()
	{
		// Hook
		$this->HookManager->processEvent('GROUP_ADD_POST');
		
		// POST
		try
		{
			// Error checking
			if (empty($_POST['name']))
			{
				throw new Exception('Group Name is required');
			}
			if ($this->FOGCore->getClass('GroupManager')->exists($_POST['name']))
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

	public function edit()
	{
		// Find
		$Group = new Group($this->request['id']);
		
		// Hook
		$this->HookManager->processEvent('GROUP_EDIT', array('Group' => &$Group));
		
		// TODO: Put table rows into variables -> Add hooking
		// TODO: Add ping lookup + additional macs from original HTML (its awful and messy, needs a rewrite)
		// TODO: Add tabs with other options
		?>
		<!--<form method="POST" action="<?php print $this->formAction; ?>">-->
			<input type="hidden" name="<?php print $this->id; ?>" value="<?php print $this->request['id']; ?>" />
			<div id="tab-container">
				<!-- General -->
				<div id="group-general">
					<h2><?php print _('Modify Group') . ': ' . $Group->get('name'); ?></h2>
					<table cellpadding=0 cellspacing=0 border=0 width=100%>
						<tr><td><?php print _("Group Name"); ?>:</td><td><input type="text" name="name" value="<?php print $Group->get('name'); ?>" /></td></tr>
						<tr><td><?php print _("Group Description"); ?>:</td><td><textarea name="description" rows="5" cols="40"><?php print $Group->get('description'); ?></textarea></td></tr>
						<tr><td><?php print _("Group Kernel"); ?>:</td><td><input type="text" name="kern" value="<?php print $Group->get('kernel'); ?>" /></td></tr>	
						<tr><td><?php print _("Group Kernel Arguments"); ?>:</td><td><input type="text" name="args" value="<?php print $Group->get('kernelArgs'); ?>" /></td></tr>	
						<tr><td><?php print _("Group Primary Disk"); ?>:</td><td><input type="text" name="dev" value="<?php print $Group->get('primaryDisk'); ?>" /></td></tr>	
						<tr><td>&nbsp;</td><td><input type="submit" value="<?php print _("Update"); ?>" /></td></tr>
					</table>
				</div>
				
				<!-- Basic Tasks -->
				<div id="group-tasks">
					<h2><?php print _("Basic Imaging Tasks"); ?></h2>
					<table cellpadding="0" cellspacing="0" border="0" width="100%">
					<tr>
					<td class="c" width="50"><a href="?node=tasks&type=group&direction=down&noconfirm=<?php echo $groupid; ?>"><img src="./images/senddebug.png" /><p><?php echo(_("Deploy")); ?></p></a></td>
					<td><p><?php echo(_("Deploy action will send an image saved on the FOG server to the client computer with all included snapins.")); ?></p></td>
					</tr>
					<tr>
					<td class="c" width="50"><a href="?node=tasks&sub=advanced&groupid=<?php echo $groupid; ?>"><img src="./images/host-advanced.png" /><p><?php echo(_("Advanced")); ?></p></a></td>
					<td><p><?php echo(_("View advanced tasks for this group.")); ?></p></td>
					</tr>
					</table>
				</div>
				
				<!-- Membership -->
				<div id="group-membership">
					<h2><?php print _("Modify Membership for ") . $Group->get('name'); ?></h2>
					<center><table cellpadding=0 cellspacing=0 border=0 width=100%>
					<?php
					if ( $_GET["delhostid"] != null && is_numeric( $_GET["delhostid"] ) )
					{
						$sql = "delete from groupMembers where gmGroupID = '" . mysql_real_escape_string( $groupid ) . "' and gmHostID = '" . mysql_real_escape_string( $_GET["delhostid"] ) . "'";
						if ( !mysql_query( $sql, $GLOBALS['conn'] ) )
							msgBox( _("Failed to remove host from group!") );

					}


					$members = getImageMembersByGroupID( $GLOBALS['conn'], $Group->get('id') );
					if ( $members != null )
					{
						for( $i = 0; $i < count( $members ); $i++ )
						{
							if ( $members[$i] != null )
							{
								$bgcolor = "alt1";
								if ( $i % 2 == 0 ) $bgcolor = "alt2";
								
								?>
								<tr class="$bgcolor"><td>&nbsp;" . $members[$i]->getHostName() . "</td><td>&nbsp;" . $members[$i]->getIPaddress() . "</td><td>&nbsp;" . $members[$i]->get('mac') . "</td><td><a href="?node=$node&sub=$sub&groupid=" . $groupid . "&tab=$tab&delhostid=" . $members[$i]->getID() . ""><img src="images/deleteSmall.png" class="link" /></a></td></tr>
								<?php
							}
						}
					}
					?>
					</table></center>
				</div>
				
				<!-- Image Association -->
				<div id="group-image">
					<h2><?php print _('Image Association for') . ': ' . $Group->get('name'); ?></h2>
					<form method="POST" action="?node=$node&sub=$sub&groupid=$groupid&tab=$tab">
					<?php
					
					printf('<select name="image"><option value="">%s</option>', _('Do Nothing'));
					
					foreach ($this->FOGCore->getClass('ImageManager')->find() AS $image)
					{
						printf('<option value="%s">%s</option>', $image->get('id'), $image->get('name'));
					}
					?>
					</select>
					<p><input type="submit" value="<?php print _("Update Images"); ?>" /></p>
					</form>
				</div>
				
				<!-- OS Association -->
				<div id="group-os">
					<h2><?php print _("Operating System Association for") . ': ' . $Group->get('name'); ?></h2>
					<form method="POST" action="?node=$node&sub=$sub&groupid=$groupid&tab=$tab">
					echo ( $FOGCore->getClass('OSManager')->buildSelectBox($Host->get('osID'), "grpos") );
					<p><input type="submit" value="<?php print _("Update Operating System"); ?>" /></p>
					</form>
				</div>
				
				<!-- Add Snap-ins -->
				<div id="group-snap-add">
					<h2><?php print _("Add Snapin to all hosts in ") . $Group->get('name'); ?></h2>
					<form method="POST" action="?node=" . $node . "&sub=" . $sub . "&groupid=" . $groupid . "&tab=$tab">
					<?php
					print $this->FOGCore->getClass('SnapinManager')->buildSelectBox();
					?>
					<p><input type="hidden" name="gsnapinadd" value="1" /><input type="submit" value="<?php print _("Add Snapin"); ?>" /></p>
					</form>
				</div>
				
				<!-- Remove Snap-ins -->
				<div id="group-snap-delete">
					<h2><?php print _("Remove Snapin to all hosts in ") . $Group->get('name'); ?></h2>
					<form method="POST" action="?node=" . $node . "&sub=" . $sub . "&groupid=" . $groupid . "&tab=$tab">
					<?php
					print $this->FOGCore->getClass('SnapinManager')->buildSelectBox();
					?>
					<p><input type="hidden" name="gsnapindel" value="1" /><input type="submit" value="<?php print _("Remove Snapin"); ?>" /></p>
					</form>
				</div>
				
				<!-- Service Settings -->
				<div id="group-service">
					<h2><?php print _("Service Configuration"); ?></h2>
					<form method="post" action="?node=$node&sub=$sub&groupid=$groupid&tab=$tab&updatemodulestatus=1">
						<center><table cellpadding=0 cellspacing=0 border=0 width=90%>
							<tr>
								<td width="270">&nbsp;<?php print _("Set Hostname Changer status on all hosts to"); ?>:</td>
								<td>&nbsp;<select name="hostnamechanger" size="1">
								  <option value="nc" label="Not Configured"><?php print _("Not Configured"); ?></option>
								  <option value="on" label="Enabled"><?php print _("Enabled"); ?></option>
								  <option value="" label="Disabled"><?php print _("Disabled"); ?></option>
								  </select>
								</td>
								<td><span class="icon icon-help hand" title="<?php print _("This setting will enable or disable the hostname changer service module on this specific host.  If the module is globally disabled, this setting is ignored."); ?>"></span>
								</td>
								</tr>
							<tr>
								  <td width="270">&nbsp;<?php print _("Set Directory Cleaner status on all hosts to"); ?>:</td>
								  <td>&nbsp;<select name="dircleanen" size="1">
									<option value="nc" label="Not Configured"><?php print _("Not Configured"); ?></option>
									<option value="on" label="Enabled"><?php print _("Enabled"); ?></option>
									<option value="" label="Disabled"><?php print _("Disabled"); ?></option>
								  </select></td>
								  <td><span class="icon icon-help hand" title="<?php print _("This setting will enable or disable the directory cleaner service module on this specific host.  If the module is globally disabled, this setting is ignored."); ?>"></span></td></tr>
							<tr>
								<td width="270">&nbsp;<?php print _("Set User Cleanup status on all hosts to"); ?>:</td>
								<td>&nbsp;<select name="usercleanen" size="1">
									<option value="nc" label="Not Configured"><?php print _("Not Configured"); ?></option>
									<option value="on" label="Enabled"><?php print _("Enabled"); ?></option>
									<option value="" label="Disabled"><?php print _("Disabled"); ?></option>
									</select></td>
								<td><span class="icon icon-help hand" title="<?php print _("This setting will enable or disable the user cleanup service module on this specific host.  If the module is globally disabled, this setting is ignored."); ?>"></span></td></tr>
					<tr><td width="270">&nbsp;<?php print _("Set Display Manager status on all hosts to"); ?>:</td><td>&nbsp;<select name="displaymanager" size="1"><option value="nc" label="Not Configured"><?php print _("Not Configured"); ?></option><option value="on" label="Enabled"><?php print _("Enabled"); ?></option><option value="" label="Disabled"><?php print _("Disabled"); ?></option></select></td><td><span class="icon icon-help hand" title="<?php print _("This setting will enable or disable the display manager service module on this specific host.  If the module is globally disabled, this setting is ignored."); ?>"></span></td></tr>
							<tr><td width="270">&nbsp;<?php print _("Set Auto Log Out on all hosts to"); ?>:</td><td>&nbsp;<select name="alo" size="1"><option value="nc" label="Not Configured"><?php print _("Not Configured"); ?></option><option value="on" label="Enabled"><?php print _("Enabled"); ?></option><option value="" label="Disabled"><?php print _("Disabled"); ?></option></select></td><td><span class="icon icon-help hand" title="<?php print _("This setting will enable or disable the auto log out service module on this specific host.  If the module is globally disabled, this setting is ignored."); ?>"></span></td></tr>
							<tr><td width="270">&nbsp;<?php print _("Set Green FOG on all hosts to"); ?>:</td><td>&nbsp;<select name="gf" size="1"><option value="nc" label="Not Configured"><?php print _("Not Configured"); ?></option><option value="on" label="Enabled"><?php print _("Enabled"); ?></option><option value="" label="Disabled"><?php print _("Disabled"); ?></option></select></td><td><span class="icon icon-help hand" title="<?php print _("This setting will enable or disable the green fog service module on this specific host.  If the module is globally disabled, this setting is ignored."); ?>"></span></td></tr>
							<tr><td width="270">&nbsp;<?php print _("Set Snapin Client on all hosts to"); ?>:</td><td>&nbsp;<select name="snapin" size="1"><option value="nc" label="Not Configured"><?php print _("Not Configured"); ?></option><option value="on" label="Enabled"><?php print _("Enabled"); ?></option><option value="" label="Disabled"><?php print _("Disabled"); ?></option></select></td><td><span class="icon icon-help hand" title="<?php print _("This setting will enable or disable the snapin service module on this specific host.  If the module is globally disabled, this setting is ignored."); ?>"></span></td></tr>						
							<tr><td width="270">&nbsp;<?php print _("Set Client Updater on all hosts to"); ?>:</td><td>&nbsp;<select name="clientupdater" size="1"><option value="nc" label="Not Configured"><?php print _("Not Configured"); ?></option><option value="on" label="Enabled"><?php print _("Enabled"); ?></option><option value="" label="Disabled"><?php print _("Disabled"); ?></option></select></td><td><span class="icon icon-help hand" title="<?php print _("This setting will enable or disable the client updater service module on this specific host.  If the module is globally disabled, this setting is ignored."); ?>"></span></td></tr>												
							<tr><td width="270">&nbsp;<?php print _("Set Host Register on all hosts to"); ?>:</td><td>&nbsp;<select name="hostregister" size="1"><option value="nc" label="Not Configured"><?php print _("Not Configured"); ?></option><option value="on" label="Enabled"><?php print _("Enabled"); ?></option><option value="" label="Disabled"><?php print _("Disabled"); ?></option></select></td><td><span class="icon icon-help hand" title="<?php print _("This setting will enable or disable the client updater service module on this specific host.  If the module is globally disabled, this setting is ignored."); ?>"></span></td></tr>												
							<tr><td width="270">&nbsp;<?php print _("Set Printer Manager on all hosts to"); ?>:</td><td>&nbsp;<select name="printermanager" size="1"><option value="nc" label="Not Configured"><?php print _("Not Configured"); ?></option><option value="on" label="Enabled"><?php print _("Enabled"); ?></option><option value="" label="Disabled"><?php print _("Disabled"); ?></option></select></td><td><span class="icon icon-help hand" title="<?php print _("This setting will enable or disable the printer manager service module on this specific host.  If the module is globally disabled, this setting is ignored."); ?>"></span></td></tr>												
							<tr><td width="270">&nbsp;<?php print _("Set Task Reboot on all hosts to"); ?>:</td><td>&nbsp;<select name="taskreboot" size="1"><option value="nc" label="Not Configured"><?php print _("Not Configured"); ?></option><option value="on" label="Enabled"><?php print _("Enabled"); ?></option><option value="" label="Disabled"><?php print _("Disabled"); ?></option></select></td><td><span class="icon icon-help hand" title="<?php print _("This setting will enable or disable the task reboot service module on this specific host.  If the module is globally disabled, this setting is ignored."); ?>"></span></td></tr>												
							<tr><td width="270">&nbsp;<?php print _("Set User Tracker on all hosts to"); ?>:</td><td>&nbsp;<select name="usertracker" size="1"><option value="nc" label="Not Configured"><?php print _("Not Configured"); ?></option><option value="on" label="Enabled"><?php print _("Enabled"); ?></option><option value="" label="Disabled"><?php print _("Disabled"); ?></option></select></td><td><span class="icon icon-help hand" title="<?php print _("This setting will enable or disable the user tracker service module on this specific host.  If the module is globally disabled, this setting is ignored."); ?>"></span></td></tr>								
							<tr><td colspan='3'><center><br /><input type="submit" value="<?php print _("Update"); ?>" /></center></td></tr>
						</table></center>

						<p class="titleBottomLeft"><?php print _("Group Screen Resolution"); ?></p>
							<center><table cellpadding=0 cellspacing=0 border=0 width=90%>
								<tr><td width="270">&nbsp;<?php print _("Screen Width (in pixels)"); ?></td><td>&nbsp;<input type="text" name="x" value="$x"/></td><td><span class="icon icon-help hand" title="<?php print _("This setting defines the screen horizontal resolution to be used with this host.  Leaving this field blank will force this host to use the global default setting"); ?>"></span></td></tr>
								<tr><td width="270">&nbsp;<?php print _("Screen Height (in pixels)"); ?></td><td>&nbsp;<input type="text" name="y" value="$y"/></td><td><span class="icon icon-help hand" title="<?php print _("This setting defines the screen vertial resolution to be used with this host.  Leaving this field blank will force this host to use the global default setting"); ?>"></span></td></tr>
								<tr><td width="270">&nbsp;<?php print _("Screen Refresh Rate"); ?></td><td>&nbsp;<input type="text" name="r" value="$r" /></td><td><span class="icon icon-help hand" title="<?php print _("This setting defines the screen refresh rate to be used with this host.  Leaving this field blank will force this host to use the global default setting"); ?>"></span></td></tr>
								<tr><td colspan='3'><center><br /><input type="submit" value="<?php print _("Update"); ?>" /></center></td></tr>
							</table></center>

						<p class="titleBottomLeft"><?php print _("Auto Log Out Settings"); ?></p>
							<center><table cellpadding=0 cellspacing=0 border=0 width=90%>
								<tr><td width="270">&nbsp;<?php print _("Auto Log Out Time (in minutes)"); ?></td><td>&nbsp;<input type="text" name="tme" value="$tme"/></td><td><span class="icon icon-help hand" title="<?php print _("This setting defines the time to auto log out this host."); ?>"></span></td></tr>
								<tr><td colspan='3'><center><br /><input type="submit" value="<?php print _("Update"); ?>" /></center></td></tr>
							</table></center>
					</form>
				</div>
				
				<!-- Active Directory -->
				<div id="group-active-directory">
					<h2><?php print _("Modify AD information for ") . $Group->get('name'); ?></h2>
					<form method="POST" action="?node=" . $node . "&sub=" . $sub . "&groupid=" . $groupid . "&tab=$tab">
					<table cellpadding=0 cellspacing=0 border=0 width=90%>
						<tr><td><?php print _("Join Domain after image task"); ?>:</td><td><input id='adEnabled' type="checkbox" name="domain" /></td></tr>
						<tr><td><?php print _("Domain name"); ?>:</td><td><input id="adDomain" type="text" name="domainname" /></td></tr>
						<tr><td><?php print _("Organizational Unit"); ?>:</td><td><input  id="adOU" type="text" name="ou" /> <span class="lightColor"><?php print _("(Blank for default)"); ?></span></td></tr>
						<tr><td><?php print _("Domain Username"); ?>:</td><td><input id="adUsername" type="text" name="domainuser" /></td></tr>
						<tr><td><?php print _("Domain Password"); ?>:</td><td><input id="adPassword" type="text" name="domainpassword" /> <span class="lightColor"><?php print _("(Must be encrypted)"); ?></span></td></tr>
						<tr><td colspan=2><center><br /><input type="hidden" name="updatead" value="1" /><input type="submit" value="<?php print _("Update"); ?>" /></center></td></tr>
					</table>
					</form>
				</div>
				
				<!-- Printers -->
				<div id="group-printers">
					<form method="POST" action="?node=$_GET[node]&sub=$_GET[sub]&groupid=$_GET[groupid]">
					<h2><?php print _("Select Management Level for all Hosts in this group"); ?>:</h2>
					<p class="l">
							
					<input type="radio" name="level" value="0" /><?php print _("No Printer Management"); ?><br/>
					<input type="radio" name="level" value="1" /><?php print _("Add Only"); ?><br/>
					<input type="radio" name="level" value="2" /><?php print _("Add and Remove"); ?><br/>
					</p>
								
					<div class="hostgroup">
						<h2><?php print _("Add new printer to all hosts in this group."); ?></h2>
						<?php
						print $this->FOGCore->getClass('PrinterManager')->buildSelectBox('', "prntadd");
						?>
						<br /><br />
					</div>
					
					<div class="hostgroup">
						<h2><?php print _("Remove printer from all hosts in this group."); ?></h2>
						<?php
						print $this->FOGCore->getClass('PrinterManager')->buildSelectBox('', "prntdel");
						?>
						<br /><br />
					</div>
					
					
					<input type="hidden" name="update" value="1" /><input type="submit" value="<?php print _("Update"); ?>" />
					</form>
				</div>
			</div>
		<!-- </form> -->
		<?php
	}
	
	public function edit_post()
	{
		// Find
		$Group = new Group($this->request['id']);
		
		// Hook
		$this->HookManager->processEvent('GROUP_ADD_POST', array('Group' => &$Group));
		
		// POST
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

	public function delete()
	{
		// Find
		$Group = new Group($this->request['id']);
		
		// Hook
		$this->HookManager->processEvent('GROUP_ADD', array('Group' => &$Group));
		
		// TODO: Put table rows into variables -> Add hooking
		?>
		<p class="C"><?php printf('%s <b>%s</b>?', _('Click on the icon below to delete this group from the FOG database.'), $Group->get('name')); ?></p>
		<p class="C"><a href="<?php print $this->formAction . '&confirm=1'; ?>"><span class="icon icon-kill"></span></a></p>
		<?php
	}
	
	public function delete_post()
	{
		// Find
		$Group = new Group($this->request['id']);
		
		// Hook
		$this->HookManager->processEvent('GROUP_DELETE_POST', array('Group' => &$Group));
		
		// POST
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
}

// Register page with FOGPageManager
$FOGPageManager->add(new GroupManagementPage());