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
			//_('Description'),
			_('Members'),
			'',
		);
		
		// Row templates
		$this->templates = array(
			sprintf('<a href="?node=group&sub=edit&%s=${id}" title="${description}">${name}</a>', $this->id),
			//'${description}',
			'${count}',
			sprintf('<a href="?node=group&sub=deploy&%s=${id}"><span class="icon icon-download" title="Deploy"></span></a> <a href="?node=group&sub=multicast&%s=${id}"><span class="icon icon-multicast" title="Multi-cast Deploy"></span></a> <a href="?node=group&sub=edit&%s=${id}"><span class="icon icon-edit" title="Edit"></span></a>', $this->id, $this->id, $this->id),
		);
		
		// Row attributes
		$this->attributes = array(
			array(),
			//array('width' => 150),
			array('width' => 40, 'class' => 'c'),
			array('width' => 80, 'class' => 'c')
		);
	}
	
	// Pages
	public function index()
	{
		// Set title
		$this->title = _('All Groups');
		
		// Find data
		$Groups = $this->FOGCore->getClass('GroupManager')->find();
		
		// Row data
		foreach ($Groups AS $Group)
		{
			$this->data[] = array(
				'id'		=> $Group->get('id'),
				'name'		=> $Group->get('name'),
				'description'	=> $Group->get('description'),
				'count'		=> $Group->getHostCount()
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
		$this->title = _('Search');
		
		// Set search form
		$this->searchFormURL = sprintf('%s?node=%s&sub=search', $_SERVER['PHP_SELF'], $this->node);
		
		// Hook
		$this->HookManager->processEvent('GROUP_SEARCH');

		// Output
		$this->render();
	}
	
	public function search_post()
	{
		// Variables
		$keyword = preg_replace('#%+#', '%', '%' . preg_replace('#[[:space:]]#', '%', $this->REQUEST['crit']) . '%');
		
		// Find data -> Push data
		foreach ($this->FOGCore->getClass('GroupManager')->find(array('name' => $keyword)) AS $Group)
		{
			$this->data[] = array(
				'id'		=> $Group->get('id'),
				'name'		=> $Group->get('name'),
				'description'	=> $Group->get('description'),
				'count'		=> $Group->getHostCount()
			);
		}
		
		// Hook
		$this->HookManager->processEvent('GROUP_DATA', array('headerData' => &$this->headerData, 'data' => &$this->data, 'templates' => &$this->templates, 'attributes' => &$this->attributes));

		// Output
		$this->render();
	}
	
	public function add()
	{
		// Set title
		$this->title = _('New Group');
		
		// Hook
		$this->HookManager->processEvent('GROUP_ADD');
		
		// TODO: Put table rows into variables -> Add hooking
		// TODO: Add tabs with other options
		?>
		<form method="POST" action="<?php print $this->formAction; ?>">
			<table cellpadding=0 cellspacing=0 border=0 width=100%>
				<tr><td><?php print _("Group Name"); ?></td><td><input type="text" name="name" value="<?php print $_POST['name']; ?>" /></td></tr>
				<tr><td><?php print _("Group Description"); ?></td><td><textarea name="description" rows="5" cols="40"><?php print $_POST['description']; ?></textarea></td></tr>
				<tr><td><?php print _("Group Kernel"); ?></td><td><input type="text" name="kern" value="<?php print $_POST['kernel']; ?>" /></td></tr>	
				<tr><td><?php print _("Group Kernel Arguments"); ?></td><td><input type="text" name="args" value="<?php print $_POST['kernelArgs']; ?>" /></td></tr>	
				<tr><td><?php print _("Group Primary Disk"); ?></td><td><input type="text" name="dev" value="<?php print $_POST['kernelDevice']; ?>" /></td></tr>	
				<tr><td></td><td><input type="submit" value="<?php print _("Add"); ?>" /></td></tr>
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
				'kernelDevice'	=> $_POST['dev']
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
			
			// Log History event
			$this->FOGCore->logHistory(sprintf('%s add failed: Name: %s, Error: %s', _('Group'), $_POST['name'], $e->getMessage()));
			
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
		
		// Title - set title for page title in window
		$this->title = sprintf('%s: %s', _('Edit'), $Group->get('name'));
		// But disable displaying in content
		$this->titleDisplay = false;
		
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
						<tr><td><?php print _("Group Name"); ?></td><td><input type="text" name="name" value="<?php print $Group->get('name'); ?>" /></td></tr>
						<tr><td><?php print _("Group Description"); ?></td><td><textarea name="description" rows="5" cols="40"><?php print $Group->get('description'); ?></textarea></td></tr>
						<tr><td><?php print _("Group Kernel"); ?></td><td><input type="text" name="kern" value="<?php print $Group->get('kernel'); ?>" /></td></tr>	
						<tr><td><?php print _("Group Kernel Arguments"); ?></td><td><input type="text" name="args" value="<?php print $Group->get('kernelArgs'); ?>" /></td></tr>	
						<tr><td><?php print _("Group Primary Disk"); ?></td><td><input type="text" name="dev" value="<?php print $Group->get('kernelDevice'); ?>" /></td></tr>	
						<tr><td></td><td><input type="submit" value="<?php print _("Update"); ?>" /></td></tr>
					</table>
				</div>
				
				<!-- Basic Tasks -->
				<div id="group-tasks">
					<h2><?php print _("Basic Imaging Tasks"); ?></h2>
					<table cellpadding="0" cellspacing="0" border="0" width="100%">
						<tr>
							<td class="task-action"><a href="?node=group&sub=deploy&<?php print $this->id . '=' . $this->request['id']; ?>"><img src="./images/senddebug.png" /><p><?php echo(_("Deploy")); ?></p></a></td>
							<td><p><?php echo(_("Deploy action will send an image saved on the FOG server to the client computer with all included snapins.")); ?></p></td>
						</tr>
						<tr>
							<td class="task-action"><a href="?node=group&sub=multicast&<?php print $this->id . '=' . $this->request['id']; ?>"><img src="./images/senddebug.png" /><p><?php echo(_("Deploy Multicast")); ?></p></a></td>
							<td><p><?php echo(_("Deploy action will send an image saved on the FOG server to the client computer with all included snapins.")); ?></p></td>
						</tr>
						<tr>
							<td class="task-action"><a href="?node=group&sub=edit&<?php print $this->id . '=' . $this->request['id']; ?>#group-tasks" class="advanced-tasks-link"><img src="./images/host-advanced.png" /><p><?php echo(_("Advanced")); ?></p></a></td>
							<td><p><?php echo(_("View advanced tasks for this group.")); ?></p></td>
						</tr>
					</table>
					
					<div id="advanced-tasks" class="hidden">
						<h2><?php print _('Advanced Actions'); ?></h2>
						<table cellspacing="0" cellpadding="0" border="0" width="100%">
							<tr>
								<td class="task-action"><a href="?node=group&sub=deploy&mode=debug&<?php print $this->id . '=' . $this->REQUEST['id']; ?>"><img src="./images/debug.png" /><p>Debug</p></a></td>
								<td><p><?php print _('Debug mode will load the boot image and load a prompt so you can run any commands you wish.  When you are done, you must remember to remove the PXE file, by clicking on "Active Tasks" and clicking on the "Kill Task" button.'); ?></p></td>
							</tr>
							<tr>
								<td class="task-action"><a href="?node=group&sub=deploy&debug=true&<?php print $this->id . '=' . $this->REQUEST['id']; ?>"><img src="./images/senddebug.png" /><p><?php print _('Deploy-Debug'); ?></p></a></td>
								<td><p><?php print _('Deploy-Debug mode allows FOG to setup the environment to allow you send a specific image to a computer, but instead of sending the image, FOG will leave you at a prompt right before sending.  If you actually wish to send the image all you need to do is type "fog" and hit enter.'); ?></p></td>
							</tr>		
							<tr>
								<td class="task-action"><a href="?node=group&sub=deploy&snapins=false&<?php print $this->id . '=' . $this->REQUEST['id']; ?>"><img src="./images/sendnosnapin.png" /><p><?php print _('Deploy without Snapins'); ?></p></a></td>
								<td><p><?php print _('Deploy without snapins allows FOG to image the workstation, but after the task is complete any snapins linked to the host or group will NOT be sent.'); ?></p></td>
							</tr>		
							<tr>
								<td class="task-action"><a href="?node=group&sub=snapins&<?php print $this->id . '=' . $this->REQUEST['id']; ?>"><img src="./images/snap.png" /><p><?php print _('Deploy Snapins'); ?></p></a></td>
								<td><p><?php print _('This option allows you to send all the snapins to host without imaging the computer.  (Requires FOG Service to be installed on client)'); ?></p></td>
							</tr>		
							<tr>
								<td class="task-action"><a href="?node=group&sub=memtest&<?php print $this->id . '=' . $this->REQUEST['id']; ?>"><img src="./images/memtest.png" /><p><?php print _('Memtest86+'); ?></p></a></td>
								<td><p><?php print _('Memtest86+ loads Memtest86+ on the client computer and will have it continue to run until stopped.  When you are done, you must remember to remove the PXE file, by clicking on "Active Tasks" and clicking on the "Kill Task" button.'); ?></p></td>
							</tr>		
							<tr>
								<td class="task-action"><a href="?node=group&sub=wakeup&<?php print $this->id . '=' . $this->REQUEST['id']; ?>"><img src="./images/wake.png" /><p><?php print _('Wake Up'); ?></p></a></td>
								<td><p><?php print _('Wake Up will attempt to send the Wake-On-LAN packet to the computer to turn the computer on.  In switched environments, you typically need to configure your hardware to allow for this (iphelper).'); ?></p></td>
							</tr>			
							<tr>
								<td class="task-action"><a href="?node=group&sub=wipe-fast&<?php print $this->id . '=' . $this->REQUEST['id']; ?>"><img src="./images/veryfastwipe.png" /><p><?php print _('Fast Wipe'); ?></p></a></td>
								<td><p><?php print _("Fast Wipe will boot the client computer and perform a quick and lazy disk wipe.  This method writes zero's to the start of the hard disk, destroying the MBR, but NOT overwritting everything on the disk."); ?></p></td>
							</tr>					
							<tr>
								<td class="task-action"><a href="?node=group&sub=wipe-normal&<?php print $this->id . '=' . $this->REQUEST['id']; ?>"><img src="./images/quickwipe.png" /><p><?php print _('Normal Wipe'); ?></p></a></td>
								<td><p><?php print _("Normal Wipe will boot the client computer and perform a simple disk wipe.  This method writes one pass of zero's to the hard disk."); ?></p></td>
							</tr>				
							<tr>
								<td class="task-action"><a href="?node=group&sub=wipe-full&<?php print $this->id . '=' . $this->REQUEST['id']; ?>"><img src="./images/fullwipe.png" /><p><?php print _('Full Wipe'); ?></p></a></td>
								<td><p><?php print _('Full Wipe will boot the client computer and perform a full disk wipe.  This method writes a few passes of random data to the hard disk.'); ?></p></td>
							</tr>					
							<tr>
								<td class="task-action"><a href="?node=group&sub=surface-test&<?php print $this->id . '=' . $this->REQUEST['id']; ?>"><img src="./images/surfacetest.png" /><p><?php print _('Disk Surface Test'); ?></p></a></td>
								<td><p><?php print _("Disk Surface Test checks the hard drive's surface sector by sector for any errors and reports back if errors are present."); ?></p></td>
							</tr>								
							<tr>
								<td class="task-action"><a href="?node=group&sub=test-disk&<?php print $this->id . '=' . $this->REQUEST['id']; ?>"><img src="./images/testdisk.png" /><p><?php print _('Test Disk'); ?></p></a></td>
								<td><p><?php print _('Test Disk loads the testdisk utility that can be used to check a hard disk and recover lost partitions.'); ?></p></td>
							</tr>						
							<tr>
								<td class="task-action"><a href="?node=group&sub=recover&<?php print $this->id . '=' . $this->REQUEST['id']; ?>"><img src="./images/recover.png" /><p><?php print _('Recover'); ?></p></a></td>
								<td><p><?php print _('Recover loads the photorec utility that can be used to recover lost files from a hard drisk.  When recovering files, make sure you save them to your NFS volume (ie: /images).'); ?></p></td>
							</tr>						
							<tr>
								<td class="task-action"><a href="?node=group&sub=antivirus&<?php print $this->id . '=' . $this->REQUEST['id']; ?>"><img src="./images/clam.png" /><p><?php print _('Anti-Virus'); ?></p></a></td>
								<td><p><?php print _('Anti-Virus loads Clam AV on the client boot image, updates the scanner and then scans the Windows partition.'); ?></p></td>
							</tr>							
							<tr>
								<td class="task-action"><a href="?node=group&sub=inventory&<?php print $this->id . '=' . $this->REQUEST['id']; ?>"><img src="./images/inventory.png" /><p><?php print _('Hardware Inventory'); ?></p></a></td>
								<td><p><?php print _('The hardware inventory task will boot the client computer and pull basic hardware informtation from it and report it back to the FOG server.'); ?></p></td>
							</tr>	
							<tr>
								<td class="task-action"><a href="?node=group&sub=windows-password-reset&<?php print $this->id . '=' . $this->REQUEST['id']; ?>"><img src="./images/winpass.png" /><p><?php print _('Password Reset'); ?></p></a></td>
								<td><p><?php print _('Password reset will blank out a Windows user password that may have been lost or forgotten.'); ?></p></td>
							</tr>
						</table>
					</div>
				</div>
								
				<!-- Membership -->
				<div id="group-membership">
					<h2><?php print _("Modify Membership for ") . $Group->get('name'); ?></h2>
					<table cellpadding=0 cellspacing=0 border=0 width=100%>
					<?php
					
					foreach ($Group->get('hosts') AS $Host)
					{
						printf('<tr class="%s"><td>%s</td><td>%s</td><td>%s</td><td><a href="%s"><img src="images/deleteSmall.png" class="link" /></a></td></tr>',
							(++$i % 2 ? 'alt': ''),
							$Host->get('name'),
							$Host->get('ip'),
							$Host->get('mac'),
							$this->formAction . '&id=' . $Host->get('id')
						);
					}
					?>
					</table>
				</div>
				
				<!-- Image Association -->
				<div id="group-image">
					<h2><?php print _('Image Association for') . ': ' . $Group->get('name'); ?></h2>
					<form method="POST" action="?node=$node&sub=$sub&groupid=$groupid&tab=$tab">
					<?php
					
					print $this->FOGCore->getClass('ImageManager')->buildSelectBox();
					
					?>
					</select>
					<p><input type="submit" value="<?php print _("Update Images"); ?>" /></p>
					</form>
				</div>
				
				<!-- OS Association -->
				<div id="group-os">
					<h2><?php print _("Operating System Association for") . ': ' . $Group->get('name'); ?></h2>
					<form method="POST" action="?node=$node&sub=$sub&groupid=$groupid&tab=$tab">
					<?php
					print $this->FOGCore->getClass('OSManager')->buildSelectBox('', "grpos");
					?>
					<p><input type="submit" value="<?php print _("Update Operating System"); ?>" /></p>
					</form>
				</div>
				
				<!-- Add Snap-ins -->
				<div id="group-snap-add">
					<h2><?php print _("Add Snapin to all hosts in ") . $Group->get('name'); ?></h2>
					<form method="POST" action="<?php print $this->formAction; ?>">
					<?php
					print $this->FOGCore->getClass('SnapinManager')->buildSelectBox();
					?>
					<p><input type="hidden" name="gsnapinadd" value="1" /><input type="submit" value="<?php print _("Add Snapin"); ?>" /></p>
					</form>
				</div>
				
				<!-- Remove Snap-ins -->
				<div id="group-snap-delete">
					<h2><?php print _("Remove Snapin to all hosts in ") . $Group->get('name'); ?></h2>
					<form method="POST" action="<?php print $this->formAction; ?>">
					<?php
					print $this->FOGCore->getClass('SnapinManager')->buildSelectBox();
					?>
					<p><input type="hidden" name="gsnapindel" value="1" /><input type="submit" value="<?php print _("Remove Snapin"); ?>" /></p>
					</form>
				</div>
				
				<!-- Service Settings -->
				<div id="group-service">
					<h2><?php print _("Service Configuration"); ?></h2>
					<form method="post" action="<?php print $this->formAction; ?>">
						<fieldset>
							<legend>General</legend>
							<table cellpadding=0 cellspacing=0 border=0 width=100%>
								<tr>
									<td width="270"><?php print _("Set Hostname Changer status on all hosts to"); ?></td>
									<td><select name="hostnamechanger" size="1">
									  <option value="nc" label="Not Configured"><?php print _("Not Configured"); ?></option>
									  <option value="on" label="Enabled"><?php print _("Enabled"); ?></option>
									  <option value="" label="Disabled"><?php print _("Disabled"); ?></option>
									  </select>
									</td>
									<td><span class="icon icon-help hand" title="<?php print _("This setting will enable or disable the hostname changer service module on this specific host.  If the module is globally disabled, this setting is ignored."); ?>"></span>
									</td>
									</tr>
								<tr>
									  <td width="270"><?php print _("Set Directory Cleaner status on all hosts to"); ?></td>
									  <td><select name="dircleanen" size="1">
										<option value="nc" label="Not Configured"><?php print _("Not Configured"); ?></option>
										<option value="on" label="Enabled"><?php print _("Enabled"); ?></option>
										<option value="" label="Disabled"><?php print _("Disabled"); ?></option>
									  </select></td>
									  <td><span class="icon icon-help hand" title="<?php print _("This setting will enable or disable the directory cleaner service module on this specific host.  If the module is globally disabled, this setting is ignored."); ?>"></span></td></tr>
								<tr>
									<td width="270"><?php print _("Set User Cleanup status on all hosts to"); ?></td>
									<td><select name="usercleanen" size="1">
										<option value="nc" label="Not Configured"><?php print _("Not Configured"); ?></option>
										<option value="on" label="Enabled"><?php print _("Enabled"); ?></option>
										<option value="" label="Disabled"><?php print _("Disabled"); ?></option>
										</select></td>
									<td><span class="icon icon-help hand" title="<?php print _("This setting will enable or disable the user cleanup service module on this specific host.  If the module is globally disabled, this setting is ignored."); ?>"></span></td></tr>
						<tr><td width="270"><?php print _("Set Display Manager status on all hosts to"); ?></td><td><select name="displaymanager" size="1"><option value="nc" label="Not Configured"><?php print _("Not Configured"); ?></option><option value="on" label="Enabled"><?php print _("Enabled"); ?></option><option value="" label="Disabled"><?php print _("Disabled"); ?></option></select></td><td><span class="icon icon-help hand" title="<?php print _("This setting will enable or disable the display manager service module on this specific host.  If the module is globally disabled, this setting is ignored."); ?>"></span></td></tr>
								<tr><td width="270"><?php print _("Set Auto Log Out on all hosts to"); ?></td><td><select name="alo" size="1"><option value="nc" label="Not Configured"><?php print _("Not Configured"); ?></option><option value="on" label="Enabled"><?php print _("Enabled"); ?></option><option value="" label="Disabled"><?php print _("Disabled"); ?></option></select></td><td><span class="icon icon-help hand" title="<?php print _("This setting will enable or disable the auto log out service module on this specific host.  If the module is globally disabled, this setting is ignored."); ?>"></span></td></tr>
								<tr><td width="270"><?php print _("Set Green FOG on all hosts to"); ?></td><td><select name="gf" size="1"><option value="nc" label="Not Configured"><?php print _("Not Configured"); ?></option><option value="on" label="Enabled"><?php print _("Enabled"); ?></option><option value="" label="Disabled"><?php print _("Disabled"); ?></option></select></td><td><span class="icon icon-help hand" title="<?php print _("This setting will enable or disable the green fog service module on this specific host.  If the module is globally disabled, this setting is ignored."); ?>"></span></td></tr>
								<tr><td width="270"><?php print _("Set Snapin Client on all hosts to"); ?></td><td><select name="snapin" size="1"><option value="nc" label="Not Configured"><?php print _("Not Configured"); ?></option><option value="on" label="Enabled"><?php print _("Enabled"); ?></option><option value="" label="Disabled"><?php print _("Disabled"); ?></option></select></td><td><span class="icon icon-help hand" title="<?php print _("This setting will enable or disable the snapin service module on this specific host.  If the module is globally disabled, this setting is ignored."); ?>"></span></td></tr>						
								<tr><td width="270"><?php print _("Set Client Updater on all hosts to"); ?></td><td><select name="clientupdater" size="1"><option value="nc" label="Not Configured"><?php print _("Not Configured"); ?></option><option value="on" label="Enabled"><?php print _("Enabled"); ?></option><option value="" label="Disabled"><?php print _("Disabled"); ?></option></select></td><td><span class="icon icon-help hand" title="<?php print _("This setting will enable or disable the client updater service module on this specific host.  If the module is globally disabled, this setting is ignored."); ?>"></span></td></tr>												
								<tr><td width="270"><?php print _("Set Host Register on all hosts to"); ?></td><td><select name="hostregister" size="1"><option value="nc" label="Not Configured"><?php print _("Not Configured"); ?></option><option value="on" label="Enabled"><?php print _("Enabled"); ?></option><option value="" label="Disabled"><?php print _("Disabled"); ?></option></select></td><td><span class="icon icon-help hand" title="<?php print _("This setting will enable or disable the client updater service module on this specific host.  If the module is globally disabled, this setting is ignored."); ?>"></span></td></tr>												
								<tr><td width="270"><?php print _("Set Printer Manager on all hosts to"); ?></td><td><select name="printermanager" size="1"><option value="nc" label="Not Configured"><?php print _("Not Configured"); ?></option><option value="on" label="Enabled"><?php print _("Enabled"); ?></option><option value="" label="Disabled"><?php print _("Disabled"); ?></option></select></td><td><span class="icon icon-help hand" title="<?php print _("This setting will enable or disable the printer manager service module on this specific host.  If the module is globally disabled, this setting is ignored."); ?>"></span></td></tr>												
								<tr><td width="270"><?php print _("Set Task Reboot on all hosts to"); ?></td><td><select name="taskreboot" size="1"><option value="nc" label="Not Configured"><?php print _("Not Configured"); ?></option><option value="on" label="Enabled"><?php print _("Enabled"); ?></option><option value="" label="Disabled"><?php print _("Disabled"); ?></option></select></td><td><span class="icon icon-help hand" title="<?php print _("This setting will enable or disable the task reboot service module on this specific host.  If the module is globally disabled, this setting is ignored."); ?>"></span></td></tr>												
								<tr><td width="270"><?php print _("Set User Tracker on all hosts to"); ?></td><td><select name="usertracker" size="1"><option value="nc" label="Not Configured"><?php print _("Not Configured"); ?></option><option value="on" label="Enabled"><?php print _("Enabled"); ?></option><option value="" label="Disabled"><?php print _("Disabled"); ?></option></select></td><td><span class="icon icon-help hand" title="<?php print _("This setting will enable or disable the user tracker service module on this specific host.  If the module is globally disabled, this setting is ignored."); ?>"></span></td></tr>								
								<tr><td>&nbsp;</td><td><input type="submit" value="<?php print _("Update"); ?>" /></td></tr>
							</table>
						</fieldset>
					

						<fieldset>
							<legend><?php print _("Group Screen Resolution"); ?></legend>
							<table cellpadding=0 cellspacing=0 border=0 width=100%>
								<tr><td width="270"><?php print _("Screen Width (in pixels)"); ?></td><td><input type="text" name="x" value="<?php print $x; ?>"/></td><td><span class="icon icon-help hand" title="<?php print _("This setting defines the screen horizontal resolution to be used with this host.  Leaving this field blank will force this host to use the global default setting"); ?>"></span></td></tr>
								<tr><td width="270"><?php print _("Screen Height (in pixels)"); ?></td><td><input type="text" name="y" value="<?php print $y; ?>"/></td><td><span class="icon icon-help hand" title="<?php print _("This setting defines the screen vertial resolution to be used with this host.  Leaving this field blank will force this host to use the global default setting"); ?>"></span></td></tr>
								<tr><td width="270"><?php print _("Screen Refresh Rate"); ?></td><td><input type="text" name="r" value="<?php print $r; ?>" /></td><td><span class="icon icon-help hand" title="<?php print _("This setting defines the screen refresh rate to be used with this host.  Leaving this field blank will force this host to use the global default setting"); ?>"></span></td></tr>
								<tr><td>&nbsp;</td><td><input type="submit" value="<?php print _("Update"); ?>" /></td></tr>
							</table>
						</fieldset>
						
						<fieldset>
							<legend><?php print _("Auto Log Out Settings"); ?></legend>
							<table cellpadding=0 cellspacing=0 border=0 width=100%>
								<tr><td width="270"><?php print _("Auto Log Out Time (in minutes)"); ?></td><td><input type="text" name="tme" value="<?php print $tme; ?>"/></td><td><span class="icon icon-help hand" title="<?php print _("This setting defines the time to auto log out this host."); ?>"></span></td></tr>
								<tr><td>&nbsp;</td><td><input type="submit" value="<?php print _("Update"); ?>" /></td></tr>
							</table>
						</fieldset>
					</form>
				</div>
				
				<!-- Active Directory -->
				<div id="group-active-directory">
					<h2><?php print _("Modify AD information for ") . $Group->get('name'); ?></h2>
					<form method="POST" action="<?php print $this->formAction; ?>">
					<table cellpadding=0 cellspacing=0 border=0 width=100%>
						<tr><td><?php print _("Join Domain after image task"); ?></td><td><input id='adEnabled' type="checkbox" name="domain" /></td></tr>
						<tr><td><?php print _("Domain name"); ?></td><td><input id="adDomain" type="text" name="domainname" /></td></tr>
						<tr><td><?php print _("Organizational Unit"); ?></td><td><input  id="adOU" type="text" name="ou" /> <span class="lightColor"><?php print _("(Blank for default)"); ?></span></td></tr>
						<tr><td><?php print _("Domain Username"); ?></td><td><input id="adUsername" type="text" name="domainuser" /></td></tr>
						<tr><td><?php print _("Domain Password"); ?></td><td><input id="adPassword" type="text" name="domainpassword" /> <span class="lightColor"><?php print _("(Must be encrypted)"); ?></span></td></tr>
						<tr><td>&nbsp;</td><td><input type="hidden" name="updatead" value="1" /><input type="submit" value="<?php print _("Update"); ?>" /></td></tr>
					</table>
					</form>
				</div>
				
				<!-- Printers -->
				<div id="group-printers">
					<form method="POST" action="<?php print $this->formAction; ?>">
					<h2><?php print _("Select Management Level for all Hosts in this group"); ?></h2>
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
		$this->HookManager->processEvent('GROUP_EDIT_POST', array('Group' => &$Group));
		
		// POST
		try
		{
			/*
			// Membership
						if ( $_GET["delhostid"] != null && is_numeric( $_GET["delhostid"] ) )
						{
							$sql = "delete from groupMembers where gmGroupID = '" . mysql_real_escape_string( $groupid ) . "' and gmHostID = '" . mysql_real_escape_string( $_GET["delhostid"] ) . "'";
							if ( !mysql_query( $sql, $GLOBALS['conn'] ) )
								msgBox( _("Failed to remove host from group!") );

						}
			*/
		
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
				->set('kernelDevice',	$_POST['dev']);
		
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
			$this->FOGCore->logHistory(sprintf('%s update failed: Name: %s, Error: %s', _('Group'), $_POST['name'], $e->getMessage()));
		
			// Set session message
			$this->FOGCore->setMessage($e->getMessage());
			
			// Redirect
			$this->FOGCore->redirect(sprintf('?node=%s&sub=edit&%s=%s#%s', $this->request['node'], $this->id, $Group->get('id'), $this->request['tab']));
		}
	}

	public function delete()
	{
		// Find
		$Group = new Group($this->request['id']);
		
		// Title
		$this->title = sprintf('%s: %s', _('Remove'), $Group->get('name'));
		
		// Hook
		$this->HookManager->processEvent('GROUP_DELETE', array('Group' => &$Group));
		
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
	
	public function deploy()
	{
		// Find
		$Group = new Group($this->REQUEST['id']);
		
		// Title
		$this->title = "Deploy Image to Group of Hosts";
		
		// Deploy
		?>
		<p class="c"><b><?php print _("Are you sure you wish to deploy these machines?"); ?></b></p>
		<form method="POST" action="<?php print $this->formAction; ?>" id="deploy-container">
			<div class="confirm-message">
				<div class="advanced-settings">
					<h2><?php print _("Advanced Settings"); ?></h2>
					<p><input type="checkbox" name="shutdown" id="shutdown" value="1" autocomplete="off"> <label for="shutdown"><?php print _("Schedule <u>Shutdown</u> after task completion"); ?></label></p>
					<?php
					if ($_GET['debug'] != 'true')
					{
						?>
						<p><input type="radio" name="scheduleType" id="scheduleInstant" value="instant" autocomplete="off" checked="checked" /> <label for="scheduleInstant"><?php print _("Schedule <u>Instant Deployment</u>"); ?></label></p>
						<p><input type="radio" name="scheduleType" id="scheduleSingle" value="single" autocomplete="off" /> <label for="scheduleSingle"><?php print _("Schedule <u>Delayed Deployment</u>"); ?></label></p>
						<p class="hidden" id="singleOptions"><input type="text" name="scheduleSingleTime" id="scheduleSingleTime" autocomplete="off" /></p>
						<p><input type="radio" name="scheduleType" id="scheduleCron" value="cron" autocomplete="off"> <label for="scheduleCron"><?php print _("Schedule <u>Cron-style Deployment</u>"); ?></label></p>
						<p class="hidden" id="cronOptions">
							<input type="text" name="scheduleCronMin" id="scheduleCronMin" placeholder="min" autocomplete="off" />
							<input type="text" name="scheduleCronHour" id="scheduleCronHour" placeholder="hour" autocomplete="off" />
							<input type="text" name="scheduleCronDOM" id="scheduleCronDOM" placeholder="dom" autocomplete="off" />
							<input type="text" name="scheduleCronMonth" id="scheduleCronMonth" placeholder="month" autocomplete="off" />
							<input type="text" name="scheduleCronDOW" id="scheduleCronDOW" placeholder="dow" autocomplete="off" />
						</p>
						<?php
					}
					?>
				</div>
			</div>
			
			<h2><?php print _('Hosts in Task'); ?></h2>
			<table width="100%" cellspacing="0" cellpadding="0" border="0">
				<tbody>
					<?php
					foreach ((array)$Group->get('hosts') AS $Host)
					{
						?>
						<tr>
							<td><?php print $Host->get('name'); ?></td>
							<td><?php print $Host->get('mac') . ($Host->get('ip') ? sprintf('(%s)', $Host->get('ip')) : ''); ?></td>
							<td><?php print $Host->getImage()->get('name'); ?></td>
						</tr>
						<?php
					}
					?>
				</tbody>
			</table>
			
			<p class="c"><input type="submit" value="<?php print _("Deploy to Group") . ' ' . $Group->get('name'); ?>" /></p>
		</form>
		<?php
	}
	
	public function deploy_post()
	{
		// Find
		$Group = new Group($this->REQUEST['id']);
		
		// Title
		$this->title = "Deploy Image to Group of Hosts";
		
		// Variables
		$enableShutdown = ($this->REQUEST['shutdown'] == 1 ? true : false);
		$enableSnapins = ($this->REQUEST['deploySnapins'] == 'true' ? true : false);
		$enableDebug = ($this->REQUEST['debug'] == 'true' ? true : false);
		$scheduledDeployTime = strtotime($this->REQUEST['scheduleSingleTime']);
		
		$taskName = $Group->get('name');
		
		// Deploy
		try
		{
			foreach ($Group->get('hosts') AS $Host)
			{
				try
				{
					// NOTE: These functions will throw an exception if they fail
					if ($this->REQUEST['scheduleType'] == 'single')
					{
						// Scheduled Deployment
						$Host->createSingleRunScheduledPackage('D', $taskName, $scheduledDeployTime, $enableShutdown, $enableSnapins, true);
					}
					else if ($this->REQUEST['scheduleType'] == 'cron')
					{
						// Cron Deployment
						$Host->createCronScheduledPackage('D', $taskName, $this->REQUEST['scheduleCronMin'], $this->REQUEST['scheduleCronHour'], $this->REQUEST['scheduleCronDOM'], $this->REQUEST['scheduleCronMonth'], $this->REQUEST['scheduleCronDOW'], $enableShutdown, $enableSnapins, true);
					}
					else
					{
						// Instant Deployment
						$Host->createImagePackage('D', $taskName, $enableShutdown, $enableDebug, $enableSnapins);
					}
					
					$success[] = sprintf('<li>%s &ndash; %s</li>', $Host->get('name'), $Host->getImage()->get('name'));
				}
				catch (Exception $e)
				{
					$error[] = sprintf('%s: %s', $Host->get('name'), $e->getMessage());
				}	
			}
			
			// Failure
			if (count($error))
			{
				throw new Exception('<ul><li>' . implode('</li><li>', $error) . '</li></ul>');
			}
		}
		catch (Exception $e)
		{
			// Failure
			printf('<div class="task-start-failed"><p>%s</p><p>%s</p></div>', _('Failed to create deployment tasks for the following Hosts'), $e->getMessage());
		}
				
		// Success
		if (count($success))
		{
			printf('<div class="task-start-ok"><p>%s</p><p>%s%s%s</p></div>',
				sprintf(_('Successfully created tasks for deployment to the following Hosts'), $Host->getImage()->get('name')),
				($this->REQUEST['scheduleType'] == 'cron'	? _('Cron Schedule:') . ' ' . implode(' ', array($this->REQUEST['scheduleCronMin'], $this->REQUEST['scheduleCronHour'], $this->REQUEST['scheduleCronDOM'], $this->REQUEST['scheduleCronMonth'], $this->REQUEST['scheduleCronDOW'])) : ''),
				($this->REQUEST['scheduleType'] == 'single'	? _('Scheduled to start at:') . ' ' . $this->REQUEST['scheduleSingleTime'] : ''),
				(count($success) ? '<ul>' . implode('', $success) . '</ul>' : '')
			);
		}
		
		// Manually disconnect FTP - the connection is kept open for Group tasks for performance
		$this->FOGFTP->close();
	}
	
	
}

// Register page with FOGPageManager
$FOGPageManager->register(new GroupManagementPage());