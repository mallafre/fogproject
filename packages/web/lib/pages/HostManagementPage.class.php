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
		$this->title = _('Search');
		
		// Set search form
		$this->searchFormURL = 'ajax/host.search.php';
		
		// Hook
		$this->HookManager->processEvent('HOST_SEARCH');

		// Output
		$this->render();
	}
	
	public function add()
	{
		// Set title
		$this->title = _('New Host');
		
		// Hook
		$this->HookManager->processEvent('HOST_ADD');
		
		// TODO: Put table rows into variables -> Add hooking
		// TODO: Add tabs with other options
		?>
		<h2><?php print _("Add new host definition"); ?></h2>
		<form method="POST" action="<?php print $this->formAction; ?>">
			<input type="hidden" name="add" value="1" />
			<table cellpadding="0" cellspacing="0" border="0" width="100%">
				<tr><td width="35%"><?php print _("Host Name"); ?>:*</td><td><input type="text" name="host" value="<?php print $_POST['host']; ?>" maxlength="15" class="hostname-input" /></td></tr>
				<tr><td><?php print _("Host IP"); ?>:</td><td><input type="text" name="ip" value="<?php print $_POST['ip']; ?>" /></td></tr>
				<tr><td><?php print _("Primary MAC"); ?>:*</td><td><input type="text" id="mac" name="mac" value="<?php print $_POST['mac']; ?>" /> &nbsp; <span id="priMaker"></span> </td></tr>
				<tr><td><?php print _("Host Description"); ?>:</td><td><textarea name="description" rows="5" cols="40"><?php print $_POST['description']; ?></textarea></td></tr>
				<tr><td><?php print _("Host Image"); ?>:</td><td><?php print $this->FOGCore->getClass('ImageManager')->buildSelectBox($_POST['image']);  ?></td></tr>
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
	
	public function add_post()
	{
		// Hook
		$this->HookManager->processEvent('HOST_ADD_POST');
		
		// POST ?
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
			
			// Log History event
			$this->FOGCore->logHistory(sprintf('%s add failed: Name: %s, Error: %s', _('Host'), $_POST['name'], $e->getMessage()));
			
			// Set session message
			$this->FOGCore->setMessage($e->getMessage());
			
			// Redirect to new entry
			$this->FOGCore->redirect($this->formAction);
		}
	}

	public function edit()
	{
		// Find
		$Host = new Host($this->request['id']);
		
		// Title - set title for page title in window
		$this->title = sprintf('%s: %s', _('Edit'), $Host->get('name'));
		// But disable displaying in content
		$this->titleDisplay = false;
		
		// Hook
		$this->HookManager->processEvent('HOST_EDIT', array('Host' => &$Host));
		
		// TODO: Put table rows into variables -> Add hooking
		// TODO: Add ping lookup + additional macs from original HTML (its awful and messy, needs a rewrite)
		// TODO: Rewrite HTML & PHP
		?>
		<!--<form method="POST" action="<?php print $this->formAction; ?>">
			<input type="hidden" name="id" value="<?php print $this->request['id']; ?>" />-->
			<div id="tab-container">
				<!-- General -->
				<div id="host-general">
					<form method="POST" action="<?php print $this->formAction; ?>&tab=host-general">
						<input type="hidden" name="id" value="<?php print $this->request['id']; ?>" />
						<h2><?php print _("Edit host definition"); ?></h2>
						<table cellpadding="0" cellspacing="0" border="0" width="100%">
							<tr><td width="35%"><?php print _("Host Name"); ?>:*</td><td><input type="text" name="host" value="<?php print $Host->get('name'); ?>" maxlength="15" class="hostname-input" /></td></tr>
							<tr><td><?php print _("Host IP"); ?>:</td><td><input type="text" name="ip" value="<?php print $Host->get('ip'); ?>" /></td></tr>
							<tr><td><?php print _("Primary MAC"); ?>:*</td><td><input type="text" id="mac" name="mac" value="<?php print $Host->get('mac'); ?>" /> &nbsp; <span id="priMaker"></span> </td></tr>
							<tr><td><?php print _("Host Description"); ?>:</td><td><textarea name="description" rows="5" cols="40"><?php print $Host->get('description'); ?></textarea></td></tr>
							<tr><td><?php print _("Host Image"); ?>:</td><td><?php print $this->FOGCore->getClass('ImageManager')->buildSelectBox($Host->get('imageID')); ?></td></tr>
							<tr><td><?php print _("Host Kernel"); ?>:</td><td><input type="text" name="kern" value="<?php print $Host->get('kern'); ?>" /></td></tr>
							<tr><td><?php print _("Host Kernel Arguments"); ?>:</td><td><input type="text" name="args" value="<?php print $Host->get('args'); ?>" /></td></tr>
							<tr><td><?php print _("Host Primary Disk"); ?>:</td><td><input type="text" name="dev" value="<?php print $Host->get('dev'); ?>" /></td></tr>
							<tr><td>&nbsp;</td><td><input type="submit" value="<?php print _("Update"); ?>" /></td></tr>
						</table>
					</form>
				</div>
				
				<!-- Basic Tasks -->
				<div id="host-tasks" class="organic-tabs-hidden">
					<form method="POST" action="<?php print $this->formAction; ?>&tab=host-tasks">
						<input type="hidden" name="id" value="<?php print $this->request['id']; ?>" />
						<h2><?php print _("Basic Imaging Tasks"); ?></h2>
						<table cellpadding="0" cellspacing="0" border="0" width="100%">
							<tr>
								<td class="c" width="50"><a href="?node=tasks&type=host&direction=down&noconfirm=<?php echo $id; ?>"><img src="./images/senddebug.png" /><p><?php echo(_("Deploy")); ?></p></a></td>
								<td><p><?php echo(_("Deploy action will send an image saved on the FOG server to the client computer with all included snapins.")); ?></p></td></tr>
							<tr>
								<td class="c" width="50"><a href="?node=tasks&type=host&direction=up&noconfirm=<?php echo $id; ?>"><img src="./images/restoredebug.png" /><p><?php echo(_("Upload")); ?></p></a></td>
								<td><p><?php echo(_("Upload will pull an image from a client computer that will be saved on the server.")); ?></p></td></tr>
							<tr>
								<td class="c" width="50"><a href="?node=tasks&sub=advanced&hostid=<?php echo $id; ?>"><img src="./images/host-advanced.png" /><p><?php echo(_("Advanced")); ?></p></a></td>
								<td><p><?php echo(_("View advanced tasks for this host.")); ?></p></td>
							</tr>
						</table>
					</form>
				</div>
				
				<!-- Active Directory -->
				<div id="host-active-directory" class="organic-tabs-hidden">
					<form method="POST" action="<?php print $this->formAction; ?>&tab=host-active-directory">
						<input type="hidden" name="id" value="<?php print $this->request['id']; ?>" />
						<h2><?php print _("Active Directory"); ?></h2>
						<table cellpadding=0 cellspacing=0 border=0 width="100%">
							<tr><td><?php print _("Join Domain after image task"); ?>:</td><td><input id='adEnabled' type="checkbox" name="domain"<?php print ($Host->get('useAD') == '1' ? ' checked="checked"' : ''); ?> /></td></tr>
							<tr><td><?php print _("Domain name"); ?>:</td><td><input id="adDomain" class="smaller" type="text" name="domainname" value="<?php print $Host->get('ADDomain'); ?>" /></td></tr>
							<tr><td><?php print _("Organizational Unit"); ?>:<br> <span class="lightColor"><?php print _("(Blank for default)"); ?></span></td><td><input size="50" id="adOU" class="smaller" type="text" name="ou" value="<?php print $Host->get('ADOU'); ?>" /></td></tr>
							<tr><td><?php print _("Domain Username"); ?>:</td><td><input id="adUsername" class="smaller" type="text" name="domainuser" value="<?php print $Host->get('ADUser'); ?>" /></td></tr>
							<tr><td><?php print _("Domain Password"); ?>:</td><td><input id="adPassword" class="smaller" type="text" name="domainpassword" value="<?php print $Host->get('ADPass'); ?>" /> <span class="lightColor"><?php print _("(Must be encrypted)"); ?></span></td></tr>
							<tr><td colspan=2><center><br /><input type="hidden" name="updatead" value="1" /><input type="submit" value="<?php print _("Update"); ?>" /></td></tr>
						</table>
					</form>
				</div>
				
				<!-- Printers -->
				<div id="host-printers" class="organic-tabs-hidden">
					<form method="POST" action="<?php print $this->formAction; ?>&tab=host-printers">
						<input type="hidden" name="id" value="<?php print $this->request['id']; ?>" />
						<h2><?php print _("Host Printer Configuration"); ?></h2>
						<p><?php print _("Select Management Level for this Host"); ?>:</p>
						<p class="l">
						
						<input type="radio" name="level" value="0"<?php print ($Host->get('printerLevel') === '0' || $Host->get('printerLevel') === '' ? ' checked="checked"' : ''); ?> /><?php print _("No Printer Management"); ?><br/>
						<input type="radio" name="level" value="1"<?php print ($Host->get('printerLevel') === '0' ? ' checked="checked"' : ''); ?> /><?php print _("Add Only"); ?><br/>
						<input type="radio" name="level" value="2"<?php print ($Host->get('printerLevel') === '0' ? ' checked="checked"' : ''); ?> /><?php print _("Add and Remove"); ?><br/>
						</p>
						
						<table cellpadding=0 cellspacing=0 border=0 width=100%>
								<tr class="header"><td>&nbsp;<b><?php print _("Default"); ?></b></td><td>&nbsp;<b><?php print _("Printer Alias"); ?></b></td><td>&nbsp;<b><?php print _("Printer Model"); ?></b></td><td><b><?php print _("Remove"); ?></b></td></tr>
						<?php
						// TODO: Complete
						/*
								$sql = "SELECT 
										* 
									FROM 
										printerAssoc
										inner join printers on ( printerAssoc.paPrinterID = printers.pID )
									WHERE
										printerAssoc.paHostID = '$id'
									ORDER BY
										printers.pAlias";
								$res = mysql_query( $sql ) or die( mysql_error() );
								if ( mysql_num_rows( $res ) > 0 )
								{
									$i = 0;
									while ( $ar = mysql_fetch_array( $res ) )
									{
										$bgcolor = "alt1";
										if ( $i++ % 2 == 0 ) $bgcolor = "alt2";
										
										$default = "<a href="?node=$_GET[node]&sub=$_GET[sub]&id=$_GET[id]&default=$ar[paID]"><img src=\<?php print /images/no.png" class="noBorder" /></a>";
										if ( $ar["paIsDefault"] == "1" )
											$default = "<img src=\<?php print /images/yes.png" class="noBorder" />";
										
										<tr class="$bgcolor"><td>&nbsp;<?php print $default; ?></td><td>&nbsp;<?php print trimString( $ar["pAlias"], 30 ); ?></td><td>&nbsp;<?php print trimString( $ar["pModel"], 30 ); ?></td><td><a href="?node=$_GET[node]&sub=$_GET[sub]&id=<?php print $id; ?>&dellinkid=<?php print $ar["paID"]; ?>"><img src="images/deleteSmall.png" class="link" /></a></td></tr>
									}
								}
								else
								{
									<tr><td colspan="4" class="c"><?php print _("No printers linked to this host; ?>); ?></td></tr>
								}
						*/
						?>
						</table>			
						
						<br /><br />
						<h2>Add new printer</h2>
						<?php
						print $this->FOGCore->getClass('PrinterManager')->buildSelectBox('', "prnt")
						?>
						
						<input type="submit" value="<?php print _("Update"); ?>" />
					</form>
				</div>
				
				<!-- Snapins -->
				<div id="host-snapins" class="organic-tabs-hidden">
					<form method="POST" action="<?php print $this->formAction; ?>&tab=host-snapins">
						<input type="hidden" name="id" value="<?php print $this->request['id']; ?>" />
						<h2><?php print _("Snapins"); ?></h2>
						<table cellpadding=0 cellspacing=0 border=0 width="100%">
								<tr class="header"><td><font class="smaller">&nbsp;<b><?php print _("Snapin Name"); ?></b></font></td><td><font class="smaller"><b><?php print _("Remove"); ?></b></font></td></tr>
								<?php
								/*
								$sql = "SELECT
										*
									FROM
										snapinAssoc
										inner join snapins on ( snapinAssoc.saSnapinID = snapins.sID )
									WHERE
										snapinAssoc.saHostID = '$id'
									ORDER BY
										snapins.sName";
								$resSnap = mysql_query( $sql ) or die( mysql_error() );
								if ( mysql_num_rows( $resSnap ) > 0 )
								{
									$i = 0;
									while ( $arSp = mysql_fetch_array( $resSnap ) )
									{
										$bgcolor = "alt1";
										if ( $i++ % 2 == 0 ) $bgcolor = "alt2";
										<tr class="$bgcolor"><td>" . $arSp["sName"] . "</td><td><a href="?node=$node&sub=$sub&id=" . $id . "&delsnaplinkid=" . $arSp["sID"] . "&tab=$tab"><img src="images/deleteSmall.png" class="link" /></a></td></tr>
									}
								}
								else
								{
									<tr><td colspan="2" class="c"><?php print _("No snapins linked to this host"); ?></td></tr>
								}
								*/
								?>
						</table>
						
						<br /><br />
						<h2><?php print _("Add new snapin package"); ?></h2>
						<?php print $this->FOGCore->getClass('SnapinManager')->buildSelectBox(); ?>
						<p><input type="submit" value="<?php print _("Add Snapin"); ?>" /></p>
					</form>
				</div>
				
				<!-- Service Configuration -->
				<div id="host-service" class="organic-tabs-hidden">
					<form method="POST" action="<?php print $this->formAction; ?>&tab=host-service">
						<input type="hidden" name="id" value="<?php print $this->request['id']; ?>" />
						<h2><?php print _("Service Configuration"); ?></h2>
						<?php
						
						/*
						$sql = "SELECT * FROM moduleStatusByHost WHERE msHostID = '$id'";
						$res = mysql_query( $sql ) or criticalError( mysql_error(), _("FOG :: Database error!") );
						$checked = " checked="checked" ";
						$ucchecked = " checked="checked" ";
						$dmchecked = " checked="checked" ";
						$alochecked = " checked="checked" ";
						$gfchecked = " checked="checked" ";
						$snapinchecked = " checked="checked" ";
						$hostnamechangerchecked = " checked="checked" ";
						$clientupdaterchecked = " checked="checked" ";
						$hostregisterchecked = " checked="checked" ";
						$printermanagerchecked = " checked="checked" ";					
						$taskrebootchecked = " checked="checked" ";
						$usertrackerchecked = " checked="checked" ";	
															
						while( $ar = mysql_fetch_array( $res ) )
						{
							if ( $ar["msModuleID"] == "dircleanup" && $ar["msState"] == "0" )
								$checked="";

							if ( $ar["msModuleID"] == "usercleanup" && $ar["msState"] == "0" )
								$ucchecked="";

							if ( $ar["msModuleID"] == "displaymanager" && $ar["msState"] == "0" )
								$dmchecked="";

							if ( $ar["msModuleID"] == "autologout" && $ar["msState"] == "0" )
								$alochecked="";

							if ( $ar["msModuleID"] == "greenfog" && $ar["msState"] == "0" )
								$gfchecked="";
								
							if ( $ar["msModuleID"] == "snapin" && $ar["msState"] == "0" )
								$snapinchecked="";							
								
							if ( $ar["msModuleID"] == "hostnamechanger" && $ar["msState"] == "0" )
								$hostnamechangerchecked="";					
								
							if ( $ar["msModuleID"] == "clientupdater" && $ar["msState"] == "0" )
								$clientupdaterchecked="";										
								
							if ( $ar["msModuleID"] == "hostregister" && $ar["msState"] == "0" )
								$hostregisterchecked="";
								
							if ( $ar["msModuleID"] == "printermanager" && $ar["msState"] == "0" )
								$printermanagerchecked="";
								
							if ( $ar["msModuleID"] == "taskreboot" && $ar["msState"] == "0" )
								$taskrebootchecked="";							

							if ( $ar["msModuleID"] == "usertracker" && $ar["msState"] == "0" )
								$usertrackerchecked="";
						}
						*/
						
						?>
						<fieldset>
						<legend><?php print _("General"); ?></legend>
							<table cellpadding=0 cellspacing=0 border=0 width="100%">
								<tr><td width="270"><?php print _("Hostname Changer Enabled?"); ?></td><td><input type="checkbox" name="hostnamechanger" $hostnamechangerchecked /></td><td><span class="icon icon-help hand" title="<?php print _("This setting will enable or disable the hostname changer module on this specific host.  If the module is globally disabled, this setting is ignored."); ?>"></span></td></tr>						
								<tr><td width="270"><?php print _("Directory Cleaner Enabled?"); ?></td><td><input type="checkbox" name="dircleanen" $checked /></td><td><span class="icon icon-help hand" title="<?php print _("This setting will enable or disable the directory cleaner service module on this specific host.  If the module is globally disabled, this setting is ignored."); ?>"></span></td></tr>
								<tr><td width="270"><?php print _("User Cleanup Enabled?"); ?></td><td><input type="checkbox" name="usercleanen" $ucchecked /></td><td><span class="icon icon-help hand" title="<?php print _("This setting will enable or disable the user cleaner service module on this specific host.  If the module is globally disabled, this setting is ignored.  The user clean up service will remove all stale users on the local machine, accept for user accounts that are whitelisted.  This is typically used when dynamic local users is implemented on the workstation."); ?>"></span></td></tr>
								<tr><td width="270"><?php print _("Display Manager Enabled?"); ?></td><td><input type="checkbox" name="displaymanager" $dmchecked /></td><td><span class="icon icon-help hand" title="<?php print _("This setting will enable or disable the display manager service module on this specific host.  If the module is globally disabled, this setting is ignored. "); ?>"></span></td></tr>
								<tr><td width="270"><?php print _("Auto Log Out Enabled?"); ?></td><td><input type="checkbox" name="alo" $alochecked /></td><td><span class="icon icon-help hand" title="<?php print _("This setting will enable or disable the auto log out service module on this specific host.  If the module is globally disabled, this setting is ignored. "); ?>"></span></td></tr>
								<tr><td width="270"><?php print _("Green FOG Enabled?"); ?></td><td><input type="checkbox" name="gf" $gfchecked /></td><td><span class="icon icon-help hand" title="<?php print _("This setting will enable or disable the green fog service module on this specific host.  If the module is globally disabled, this setting is ignored. "); ?>"></span></td></tr>							
								<tr><td width="270"><?php print _("Snapin Enabled?"); ?></td><td><input type="checkbox" name="snapin" $snapinchecked /></td><td><span class="icon icon-help hand" title="<?php print _("This setting will enable or disable the snapin service module on this specific host.  If the module is globally disabled, this setting is ignored. "); ?>"></span></td></tr>														
								<tr><td width="270"><?php print _("Client Updater Enabled?"); ?></td><td><input type="checkbox" name="clientupdater" $clientupdaterchecked /></td><td><span class="icon icon-help hand" title="<?php print _("This setting will enable or disable the client updater service module on this specific host.  If the module is globally disabled, this setting is ignored. "); ?>"></span></td></tr>														
								<tr><td width="270"><?php print _("Host Registration Enabled?"); ?></td><td><input type="checkbox" name="hostregister" $hostregisterchecked /></td><td><span class="icon icon-help hand" title="<?php print _("This setting will enable or disable the host register service module on this specific host.  If the module is globally disabled, this setting is ignored. "); ?>"></span></td></tr>														
								<tr><td width="270"><?php print _("Printer Manager Enabled?"); ?></td><td><input type="checkbox" name="printermanager" $printermanagerchecked /></td><td><span class="icon icon-help hand" title="<?php print _("This setting will enable or disable the printer manager service module on this specific host.  If the module is globally disabled, this setting is ignored. "); ?>"></span></td></tr>														
								<tr><td width="270"><?php print _("Task Reboot Enabled?"); ?></td><td><input type="checkbox" name="taskreboot" $taskrebootchecked /></td><td><span class="icon icon-help hand" title="<?php print _("This setting will enable or disable the task reboot service module on this specific host.  If the module is globally disabled, this setting is ignored. "); ?>"></span></td></tr>														
								<tr><td width="270"><?php print _("User Tracker Enabled?"); ?></td><td><input type="checkbox" name="usertracker" $usertrackerchecked /></td><td><span class="icon icon-help hand" title="<?php print _("This setting will enable or disable the user tracker service module on this specific host.  If the module is globally disabled, this setting is ignored. "); ?>"></span></td></tr>														
								<tr><td>&nbsp;</td><td><input type="submit" value="<?php print _("Update"); ?>" /></td></tr>
							</table>
						</fieldset>
						<fieldset>
						<legend><?php print _("Host Screen Resolution"); ?></legend>
							<table cellpadding=0 cellspacing=0 border=0 width="100%">
							<?php
							/*
							$x = "";
							$y = "";
							$r = "";

							$sql = "SELECT
									*
								FROM
									hostScreenSettings
								WHERE
									hssHostID = '$id'";
							$res = mysql_query( $sql ) or criticalError( mysql_error(), "FOG :: Database error!
							while( $ar = mysql_fetch_array( $res ) )
							{
								$x = $ar["hssWidth"];
								$y = $ar["hssHeight"];
								$r = $ar["hssRefresh"];
							}
							*/
							?>

							<tr><td width="270"><?php print _("Screen Width (in pixels)"); ?></td><td><input type="text" name="x" value="<?php print $x; ?>"/></td><td><span class="icon icon-help hand" title="<?php print _("This setting defines the screen horizontal resolution to be used with this host.  Leaving this field blank will force this host to use the global default setting"); ?>"></span></td></tr>
							<tr><td width="270"><?php print _("Screen Height (in pixels)"); ?></td><td><input type="text" name="y" value="<?php print $y; ?>"/></td><td><span class="icon icon-help hand" title="<?php print _("This setting defines the screen vertial resolution to be used with this host.  Leaving this field blank will force this host to use the global default setting"); ?>"></span></td></tr>
							<tr><td width="270"><?php print _("Screen Refresh Rate"); ?></td><td><input type="text" name="r" value="<?php print $r; ?>" /></td><td><span class="icon icon-help hand" title="<?php print _("This setting defines the screen refresh rate to be used with this host.  Leaving this field blank will force this host to use the global default setting"); ?>"></span></td></tr>
							<tr><td>&nbsp;</td><td><input type="submit" value="<?php print _("Update"); ?>" /></td></tr>
						</table>
						</fieldset>
						
						<fieldset>
						<legend><?php print _("Auto Log Out Settings"); ?></legend>
							<table cellpadding=0 cellspacing=0 border=0 width="100%">
							<?php
							/*
							$tme = "";

							$sql = "SELECT
									*
								FROM
									hostAutoLogOut
								WHERE
									haloHostID = '$id'";
							$res = mysql_query( $sql ) or criticalError( mysql_error(), "FOG :: Database error!
							while( $ar = mysql_fetch_array( $res ) )
							{
								$tme = $ar["haloTime"];
							}
							*/
							?>
							<tr><td width="270"><?php print _("Auto Log Out Time (in minutes)"); ?></td><td><input type="text" name="tme" value="<?php print $tme; ?>"/></td><td><span class="icon icon-help hand" title="<?php print _("This setting defines the time to auto log out this host."); ?>"></span></td></tr>
							<tr><td>&nbsp;</td><td><input type="submit" value="<?php print _("Update"); ?>" /></td></tr>
						</table>
						</fieldset>
					</form>
				</div>
				
				<!-- Inventory -->
				<div id="host-hardware-inventory" class="organic-tabs-hidden">
					<form method="POST" action="<?php print $this->formAction; ?>&tab=host-hardware-inventory">
						<input type="hidden" name="id" value="<?php print $this->request['id']; ?>" />
						<h2><?php print _("Host Hardware Inventory"); ?></h2>
						
						<table cellpadding=0 cellspacing=0 border=0 width=100%>
						<?php
								$sql = "SELECT 
										* 
									FROM 
										inventory
									WHERE
										iHostID = '" . $Host->get('id') . "'";
						$res = mysql_query( $sql ) or die( mysql_error() );
						if ( mysql_num_rows( $res ) > 0 )
						{
							while ( $ar = mysql_fetch_array( $res ) )
							{
								// Get unique core and manufactor names
								foreach (array('iCpuman','iCpuversion') AS $x) $ar[$x] = implode(' ', array_unique(explode(' ', $ar[$x])));
								
								// TODO: UGLY!!!!!
								?>
								<tr><td>&nbsp;</td><td style='width: 200px'>&nbsp;<?php print _("Primary User"); ?></td><td>&nbsp;<input type="text" value="<?php print $ar["iPrimaryUser"]; ?>" name="pu" /></td></tr>
								<tr><td>&nbsp;</td><td>&nbsp;<?php print _("Other Tag #1"); ?></td><td>&nbsp;<input type="text" value="<?php print $ar["iOtherTag"]; ?>" name="other1" /></td></tr>
								<tr><td>&nbsp;</td><td>&nbsp;<?php print _("Other Tag #2"); ?></td><td>&nbsp;<input type="text" value="<?php print $ar["iOtherTag1"]; ?>" name="other2" /></td></tr>
								<tr><td>&nbsp;</td><td>&nbsp;<?php print _("System Manufacturer"); ?></td><td>&nbsp;<?php print $ar["iSysman"]; ?></td></tr>
								<tr><td>&nbsp;</td><td>&nbsp;<?php print _("System Product"); ?></td><td>&nbsp;<?php print $ar["iSysproduct"]; ?></td></tr>
								<tr><td>&nbsp;</td><td>&nbsp;<?php print _("System Version"); ?></td><td>&nbsp;<?php print $ar["iSysversion"]; ?></td></tr>
								<tr><td>&nbsp;</td><td>&nbsp;<?php print _("System Serial Number"); ?></td><td>&nbsp;<?php print $ar["iSysserial"]; ?></td></tr>
								<tr><td>&nbsp;</td><td>&nbsp;<?php print _("System Type"); ?></td><td>&nbsp;<?php print $ar["iSystype"]; ?></td></tr>
								<tr><td>&nbsp;</td><td>&nbsp;<?php print _("BIOS Vendor"); ?></td><td>&nbsp;<?php print $ar["iBiosvendor"]; ?></td></tr>
								<tr><td>&nbsp;</td><td>&nbsp;<?php print _("BIOS Version"); ?></td><td>&nbsp;<?php print $ar["iBiosversion"]; ?></td></tr>
								<tr><td>&nbsp;</td><td>&nbsp;<?php print _("BIOS Date"); ?></td><td>&nbsp;<?php print $ar["iBiosdate"]; ?></td></tr>
								<tr><td>&nbsp;</td><td>&nbsp;<?php print _("Motherboard Manufacturer"); ?></td><td>&nbsp;<?php print $ar["iMbman"]; ?></td></tr>
								<tr><td>&nbsp;</td><td>&nbsp;<?php print _("Motherboard Product Name"); ?></td><td>&nbsp;<?php print $ar["iMbproductname"]; ?></td></tr>
								<tr><td>&nbsp;</td><td>&nbsp;<?php print _("Motherboard Version"); ?></td><td>&nbsp;<?php print $ar["iMbversion"]; ?></td></tr>
								<tr><td>&nbsp;</td><td>&nbsp;<?php print _("Motherboard Serial Number"); ?></td><td>&nbsp;<?php print $ar["iMbserial"]; ?></td></tr>
								<tr><td>&nbsp;</td><td>&nbsp;<?php print _("Motherboard Asset Tag"); ?></td><td>&nbsp;<?php print $ar["iMbasset"]; ?></td></tr>
								<tr><td>&nbsp;</td><td>&nbsp;<?php print _("CPU Manufacturer"); ?></td><td>&nbsp;<?php print $ar["iCpuman"]; ?></td></tr>
								<tr><td>&nbsp;</td><td>&nbsp;<?php print _("CPU Version"); ?></td><td>&nbsp;<?php print $ar["iCpuversion"]; ?></td></tr>																		
								<tr><td>&nbsp;</td><td>&nbsp;<?php print _("CPU Normal Speed"); ?></td><td>&nbsp;<?php print $ar["iCpucurrent"]; ?></td></tr>
								<tr><td>&nbsp;</td><td>&nbsp;<?php print _("CPU Max Speed"); ?></td><td>&nbsp;<?php print $ar["iCpumax"]; ?></td></tr>
								<tr><td>&nbsp;</td><td>&nbsp;<?php print _("Memory"); ?></td><td>&nbsp;<?php print $ar["iMem"]; ?></td></tr>
								<tr><td>&nbsp;</td><td>&nbsp;<?php print _("Hard Disk Model"); ?></td><td>&nbsp;<?php print $ar["iHdmodel"]; ?></td></tr>
								<tr><td>&nbsp;</td><td>&nbsp;<?php print _("Hard Disk Firmware"); ?></td><td>&nbsp;<?php print $ar["iHdfirmware"]; ?></td></tr>
								<tr><td>&nbsp;</td><td>&nbsp;<?php print _("Hard Disk Serial Number"); ?></td><td>&nbsp;<?php print $ar["iHdserial"]; ?></td></tr>
								<tr><td>&nbsp;</td><td>&nbsp;<?php print _("Chassis Manufacturer"); ?></td><td>&nbsp;<?php print $ar["iCaseman"]; ?></td></tr>
								<tr><td>&nbsp;</td><td>&nbsp;<?php print _("Chassis Version"); ?></td><td>&nbsp;<?php print $ar["iCasever"]; ?></td></tr>
								<tr><td>&nbsp;</td><td>&nbsp;<?php print _("Chassis Serial"); ?></td><td>&nbsp;<?php print $ar["iCaseserial"]; ?></td></tr>
								<tr><td>&nbsp;</td><td>&nbsp;<?php print _("Chassis Asset"); ?></td><td>&nbsp;<?php print $ar["iCaseasset"]; ?></td></tr>
								<tr><td>&nbsp;</td><td colspan='2'><center><input type="hidden" name="update" value="1" /><input type="submit" value="<?php print _("Update"); ?>" /></td></tr>
								<?php
							}
						}
						else
						{
							?><tr><td colspan="3" class="c"><?php print _("No Inventory found for this host"); ?></td></tr><?php
						}
						?>
						</table>
					</form>
				</div>
				
				<!-- Virus -->
				<div id="host-virus-history" class="organic-tabs-hidden">
					<form method="POST" action="<?php print $this->formAction; ?>&tab=host-virus-history">
						<input type="hidden" name="id" value="<?php print $this->request['id']; ?>" />
						<h2><?php print _("Virus History"); ?> (<a href="<?php print "?node=$GLOBALS[node]&sub=$GLOBALS[sub]&id=$GLOBALS[id]&delvid=all&tab=$GLOBALS[tab]"; ?>"><?php print _("clear all history"); ?></a>)</h2>
						<table cellpadding=0 cellspacing=0 border=0 width=100%>
							<tr class="header"><td>&nbsp;<b><?php print _("Virus Name"); ?></b></td><td><b><?php print _("File"); ?></b></td><td><b><?php print _("Mode"); ?></b></td><td><b><?php print _("Date"); ?></b></td><td><b><?php print _("Clear"); ?></b></td></tr>
							<?php
							$sql = "SELECT
									*
								FROM
									virus
								WHERE
									vHostMAC = '" . $Host->get('mac') . "'
								ORDER BY
									vDateTime, vName";
							$resSnap = mysql_query( $sql ) or die( mysql_error() );
							if ( mysql_num_rows( $resSnap ) > 0 )
							{
								$i = 0;
								while ( $arSp = mysql_fetch_array( $resSnap ) )
								{
									?>
									<tr<?php print ( $i++ % 2 == 0 ? ' class="alt"' : ''); ?>><td>&nbsp;<a href="http://www.google.com/search?q=<?php print $arSp["vName"]; ?>" target="_blank"><?php print $arSp["vName"]; ?></a></td><td><?php print $arSp["vOrigFile"]; ?></td><td><?php print avModeToString( $arSp["vMode"] ); ?></td><td><?php print $arSp["vDateTime"]; ?></td><td><a href="?node=$node&sub=$sub&id=<?php print $id; ?>&delvid=<?php print $arSp["vID"]; ?>"><img src="images/deleteSmall.png" class="link" /></a></td></tr>
									<?php
								}
							}
							else
							{
								?>
								<tr><td colspan="5" class="c"><?php print _("No Virus Information Reported for this host."); ?></td></tr>
								<?php
							}
							?>
						</table>
					</form>
				</div>
				
				<!-- Login History -->
				<div id="host-login-history" class="organic-tabs-hidden">
					<form method="POST" action="<?php print $this->formAction; ?>&tab=host-login-history">
						<input type="hidden" name="id" value="<?php print $this->request['id']; ?>" />
						<h2><?php print _("Host Login History"); ?></h2>
						<?php
						
						$dte = mysql_real_escape_string($_POST["dte"]);
						
						?>
						<p>View History for 
						<?php
							
							$sql = "SELECT 
									utDate as dte 
								FROM 
									userTracking 
								WHERE 
									utHostID = '" . $Host->get('id') . "' 
								GROUP BY 
									utDate 
								ORDER BY 
									utDate desc";
							$res = mysql_query( $sql ) or die( mysql_error() );
						?>
							<form id="dte" method="post" action="?node=$_GET[node]&sub=$_GET[sub]&id=$_GET[id]">
							<select name="dte" size="1">
						<?php
								$blFirst = true;			
								while( $ar = mysql_fetch_array( $res ) )
								{
									if ( $blFirst )
									{
										if ( $dte == null )
											$dte = $ar["dte"];
									}
									
									$sel = "";
									if ( $dte == $ar["dte"] )
										$sel = ' selected="selected" ';
									
									?>
									<option value="<?php print $ar["dte"]; ?>" $sel><?php print $ar["dte"]; ?></option>
									<?php
								}
						?>
							</select> <a href="#" onclick="document.getElementById('dte').submit();"><img src="images/go.png" class="noBorder" /></a>
							</form>
						</p>
						<?php
						$sql = "SELECT 
								* 
							FROM 
								( SELECT *, TIME(utDateTime) as tme FROM userTracking WHERE utHostID = '" . $Host->get('id') . "' and utDate = DATE('" . $dte . "') ) userTracking
							ORDER BY
								utDateTime";
						$res = mysql_query( $sql ) or die( mysql_error() );
						
						?>
						<table cellpadding=0 cellspacing=0 border=0 width=100%>
						<tr class="header"><td><b>&nbsp;<?php print _("Action"); ?></b></td><td><b>&nbsp;<?php print _("Username"); ?></b></font></td><td><b>&nbsp;<?php print _("Time"); ?></b></td><td><b>&nbsp;<?php print _("Description"); ?></b></td></tr>		
						
						<?php
						$cnt = 0;
						$arAllUsers = array();
						while( $ar = mysql_fetch_array( $res ) )
						{
							if ( ! in_array( $ar["utUserName"], $arAllUsers ) )
								$arAllUsers[] = $ar["utUserName"];

							?><tr<?php print ( $cnt++ % 2 == 0 ? ' class="alt"' : ''); ?>><td>&nbsp;<?php print userTrackerActionToString( $ar["utAction"] ); ?></td><td>&nbsp;<?php print $ar["utUserName"]; ?></td><td>&nbsp;<?php print $ar["tme"]; ?></td><td>&nbsp;<?php print trimString( $ar["utDesc"], 60 ); ?></td></tr><?php
						}
						?>
						
						</table>
						
						<?php
						
						$_SESSION["fog_logins"] = array();

						for( $i = 0; $i < count( $arAllUsers ); $i++ )
						{
							$sql = "SELECT 
									utDateTime, utAction
								FROM 
									( SELECT *, TIME(utDateTime) as tme FROM userTracking WHERE utUserName = '<?php print mysql_real_escape_string( $arAllUsers[$i] ); ?>' and utHostID = '<?php print $id; ?>' and utDate = DATE('<?php print $dte; ?>') ) userTracking
								ORDER BY
									utDateTime";	
							$res = mysql_query( $sql ) or die( mysql_error() );
							$tmpUserLogin = null;
							while( $ar = mysql_fetch_array( $res ) )
							{			
								if ( $ar["utAction"] == "1" || $ar["utAction"] == "99" )
								{
									$tmpUserLogin = new UserLoginEntry( $arAllUsers[$i] );					
									$tmpUserLogin->setLogInTime( $ar["utDateTime"] );
									$tmpUserLogin->setClean( ($ar["utAction"] == "1") );
								}
								else if ( $ar["utAction"] == "0" )
								{
									if ( $tmpUserLogin != null )
										$tmpUserLogin->setLogOutTime( $ar["utDateTime"] );


									$_SESSION["fog_logins"][] = serialize( $tmpUserLogin );
									$tmpUserLogin = null;
								}
							}				
						}
						
						if ( count( $_SESSION["fog_logins"] ) > 0 )
						{
							?><p><img src="/phpimages/hostloginhistory.phpgraph.php" /></p><?php
						}
						?>
					</form>
				</div>
			</div>
		<!-- </form> -->
		<?php
	}
	
	public function edit_post()
	{
		// Find
		$Host = new Host($this->request['id']);
		
		// Hook
		$this->HookManager->processEvent('HOST_EDIT_POST', array('Host' => &$Host));
		
		// POST
		try
		{
			// Error checking
			if (empty($_POST['id']))
			{
				throw new Exception('Host ID is required');
			}
			
			// Tabs
			if ($this->request['tab'] == 'host-general')
			{
				// Error checking
				if (empty($_POST['mac']))
				{
					throw new Exception('MAC Address is required');
				}
				
				// Variables
				$mac = new MACAddress($_POST['mac']);
				
				// Error checking
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
			}
			elseif ($this->request['tab'] == 'host-active-directory')
			{
				$Host	->set('useAD',		($_POST["domain"] == "on" ? '1' : '0'))
					->set('ADDomain',	$_POST['domainname'])
					->set('ADOU',		$_POST['ou'])
					->set('ADUser',		$_POST['domainuser'])
					->set('ADPass',		$_POST['domainpassword']);
			}
			elseif ($this->request['tab'] == 'host-printers')
			{
				/*
				if ( $_POST["update"] == "1" )
				{
					if ( $_POST["level"] !== null )
					{
						$level = mysql_real_escape_string( $_POST["level"] );
						$sql = "update hosts set hostPrinterLevel = '$level' where hostID = '$id'";
						if ( mysql_query( $sql, $conn ) )
						{
							if ( $_POST["prnt"] !== null && is_numeric( $_POST["prnt"] ) && $_POST["prnt"]  >=  0 )
							{
								$printer = mysql_real_escape_string( $_POST["prnt"] );
								
								if ( ! addPrinter( $conn, $id, $printer ) )
									msgBox( _("Failed to add printer") );
														
							}		
						}
						else
						{
							msgBox( mysql_error() );
						}
					}
				}
				
				if ( $_GET["default"] !== null )
				{
					setDefaultPrinter( $conn, $_GET["default"] );
				}
				
				if ( $_GET["dellinkid"] !== null )
				{
					deletePrinter( $conn, $_GET["dellinkid"] );
				}
				*/
			}
			elseif ($this->request['tab'] == 'host-snapins')
			{
				// ADD
				/*
				// Hook
				$HookManager->processEvent('HostEditAddSnapinUpdate');
				
				$snap = mysql_real_escape_string( $_POST["snap"] );
				$ret = "";
				if ( ! addSnapinToHost( $conn, $id, $snap, $ret ) )
				{
					// Hook
					$HookManager->processEvent('HostEditAddSnapinUpdateFail');
					
					msgBox($ret);
				}
				else
				{
					// Hook
					$HookManager->processEvent('HostEditAddSnapinUpdateSuccess');
				}
				*/
				
				
				// DELETE
				/*
				// Hook
				$HookManager->processEvent('HostEditRemoveSnapinUpdate');
				
				$snap = mysql_real_escape_string( $_GET["delsnaplinkid"] );
				$ret = "";
				if ( ! deleteSnapinFromHost( $conn, $id, $snap, $ret ) )
				{
					// Hook
					$HookManager->processEvent('HostEditRemoveSnapinUpdateFail');
				
					msgBox($ret);
				}
				else
				{
					// Hook
					$HookManager->processEvent('HostEditRemoveSnapinUpdateSuccess');
				}
				*/
			}
			elseif ($this->request['tab'] == 'host-service')
			{
				/*
				if ( $_GET["updatemodulestatus"] == "1" )
				{
					
					//$clientupdaterchecked = " checked=\"checked\" ";
					//$hostregisterchecked = " checked=\"checked\" ";
					//$printermanagerchecked = " checked=\"checked\" ";					
					//$taskrebootchecked = " checked=\"checked\" ";
					//$usertrackerchecked = " checked=\"checked\" ";
				
				
					$dircleanupstate = "0";
					$usercleanupstate = "0";
					$displaymanagerstate = "0";
					$alostate = "0";
					$gfstate = "0";
					$snapinstate = "0";		
					$hncstate = "0";
					$custate = "0";
					$hrstate = "0";
					$pmstate = "0";
					$trstate = "0";
					$utstate = "0";
					
					if ( $_POST["dircleanen"] == "on" ) $dircleanupstate = "1";
					if ( $_POST["usercleanen"] == "on" ) $usercleanupstate = "1";
					if ( $_POST["displaymanager"] == "on" ) $displaymanagerstate = "1";
					if ( $_POST["alo"] == "on" ) $alostate = "1";
					if ( $_POST["gf"] == "on" ) $gfstate = "1";
					if ( $_POST["snapin"] == "on" ) $snapinstate = "1";
					if ( $_POST["hostnamechanger"] == "on" ) $hncstate = "1";
					if ( $_POST["clientupdater"] == "on" ) $custate = "1";
					if ( $_POST["hostregister"] == "on" ) $hrstate = "1";
					if ( $_POST["printermanager"] == "on" ) $pmstate = "1";
					if ( $_POST["taskreboot"] == "on" ) $trstate = "1";
					if ( $_POST["usertracker"] == "on" ) $utstate = "1";

					setHostModuleStatus( $conn, $dircleanupstate, $id, 'dircleanup' );
					setHostModuleStatus( $conn, $usercleanupstate, $id, 'usercleanup' );
					setHostModuleStatus( $conn, $displaymanagerstate, $id, 'displaymanager' );
					setHostModuleStatus( $conn, $alostate, $id, 'autologout' );
					setHostModuleStatus( $conn, $gfstate, $id, 'greenfog' );
					setHostModuleStatus( $conn, $snapinstate, $id, 'snapin' );
					setHostModuleStatus( $conn, $hncstate, $id, 'hostnamechanger' );
					setHostModuleStatus( $conn, $custate, $id, 'clientupdater' );
					setHostModuleStatus( $conn, $hrstate, $id, 'hostregister' );
					setHostModuleStatus( $conn, $pmstate, $id, 'printermanager' );
					setHostModuleStatus( $conn, $trstate, $id, 'taskreboot' );
					setHostModuleStatus( $conn, $utstate, $id, 'usertracker' );

					// update screen settings
					$x = mysql_real_escape_string( $_POST["x"] );
					$y = mysql_real_escape_string( $_POST["y"] );
					$r = mysql_real_escape_string( $_POST["r"] );
					if ( $x == "" && $y == "" && $z == "" )
					{
						$sql = "DELETE FROM hostScreenSettings WHERE hssHostID = '$id'";
						$res = mysql_query( $sql, $conn ) or criticalError( mysql_error(), _("FOG :: Database error!") );
					}
					else
					{
						$sql = "SELECT
								COUNT(*) as cnt
							FROM
								hostScreenSettings
							WHERE
								hssHostID = '$id'";
						$res = mysql_query( $sql, $conn ) or criticalError( mysql_error(), _("FOG :: Database error!") );
						$blFound = false;
						while( $ar = mysql_fetch_array( $res ) )
						{
							if ( $ar["cnt"] > 0 ) $blFound = true;
						}

						if ( $blFound )
						{
							$sql = "UPDATE
									hostScreenSettings
									set
										hssWidth = '$x',
										hssHeight = '$y',
										hssRefresh = '$r'
									WHERE
										hssHostID = '$id'";
						}
						else
						{
							$sql = "INSERT INTO hostScreenSettings(hssHostID, hssWidth, hssHeight, hssRefresh) values('$id', '$x', '$y', '$r')";
						}
						if ( ! mysql_query( $sql, $conn ) )
							criticalError( mysql_error(), _("FOG :: Database error!") );
					}
					// Update auto log off times.
					$tme = mysql_real_escape_string( $_POST["tme"] );
					$sql = "SELECT
							COUNT(*) as cnt
						FROM
							hostAutoLogOut
						WHERE
							haloHostID = '$id'";
					$res = mysql_query( $sql, $conn ) or criticalError( mysql_error(), _("FOG :: Database error!") );
					$blFound = false;
					while( $ar = mysql_fetch_array( $res ) )
					{
						if ( $ar["cnt"] > 0 ) $blFound = true;
					}

					if ( $blFound )
					{
						$sql = "UPDATE
								hostAutoLogOut
								set
									haloTime = '$tme'
								WHERE
									haloHostID = '$id'";
					}
					else
					{
						$sql = "INSERT INTO hostAutoLogOut(haloHostID, haloTime) values('$id', '$tme')";
					}
					if ( ! mysql_query( $sql, $conn ) )
						criticalError( mysql_error(), _("FOG :: Database error!") );

				}
				*/
			}
			elseif ($this->request['tab'] == 'host-hardware-inventory')
			{
				/*
				if ( $_POST["update"] == "1" )
				{

					$prim = mysql_real_escape_string( $_POST["pu"] );
					$other1 = mysql_real_escape_string( $_POST["other1"] );
					$other2 = mysql_real_escape_string( $_POST["other2"] );
					$sql = "update inventory set iPrimaryUser = '$prim', iOtherTag = '$other1', iOtherTag1 ='$other2' where iHostID = '$id'";
					if ( !mysql_query( $sql, $conn ) )
					{
						msgBox( mysql_error() );
					}
				}
				*/
			}
			elseif ($this->request['tab'] == 'host-virus-history')
			{
				/*
				if ( $_GET["delvid"] !== null && is_numeric( $_GET["delvid"] ) )
				{		
					$vid = mysql_real_escape_string( $_GET["delvid"] );
					clearAVRecord( $conn, $vid );
				}

				if ( $_GET["delvid"] == "all"  )
				{
					$member = getImageMemberFromHostID( $conn, $id );
					if ( $member != null )
					{
						clearAVRecordsForHost( $conn, $member->getMACColon() );
					}
				}
				*/
			}
		
			// Save to database
			if ($Host->save())
			{
				// Hook
				$this->HookManager->processEvent('HOST_EDIT_SUCCESS', array('host' => &$Host));
				
				// Log History event
				$this->FOGCore->logHistory(sprintf('Host updated: ID: %s, Name: %s, Tab: %s', $Host->get('id'), $Host->get('name'), $this->request['tab']));
			
				// Set session message
				$this->FOGCore->setMessage('Host updated!');
			
				// Redirect to new entry
				$this->FOGCore->redirect(sprintf('?node=%s&sub=edit&%s=%s#%s', $this->request['node'], $this->id, $Host->get('id'), $this->request['tab']));
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
			$this->FOGCore->logHistory(sprintf('%s update failed: Name: %s, Tab: %s, Error: %s', _('Host'), $_POST['name'], $this->request['tab'], $e->getMessage()));
		
			// Set session message
			$this->FOGCore->setMessage($e->getMessage());
			
			// Redirect
			$this->FOGCore->redirect(sprintf('?node=%s&sub=edit&%s=%s#%s', $this->request['node'], $this->id, $Host->get('id'), $this->request['tab']));
		}
	}

	public function delete()
	{	
		// Find
		$Host = new Host($this->request['id']);
		
		// Title
		$this->title = sprintf('%s: %s', _('Remove'), $Host->get('name'));
		
		// Hook
		$this->HookManager->processEvent('HOST_ADD', array('Host' => &$Host));
	
		// TODO: Put table rows into variables -> Add hooking
		?>
		<p class="C"><?php printf('%s <b>%s</b>?', _('Click on the icon below to delete this host from the FOG database.'), $Host->get('name')); ?></p>
		<p class="C"><a href="<?php print $this->formAction . '&confirm=1'; ?>"><span class="icon icon-kill"></span></a></p>
		<?php
	}
	
	public function delete_post()
	{
		// Find
		$Host = new Host($this->request['id']);
		
		// Hook
		$this->HookManager->processEvent('HOST_ADD_POST', array('Host' => &$Host));
		
		// POST
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
			
			// Log History event
			$this->FOGCore->logHistory(sprintf('%s %s: ID: %s, Name: %s', _('Host'), _('deleted'), $Host->get('id'), $Host->get('name')));
			
			// Set session message
			$this->FOGCore->setMessage($e->getMessage());
			
			// Redirect
			$this->FOGCore->redirect($this->formAction);
		}
	}
	
	public function import()
	{
		// Title
		$this->title = _('Import Host List');
		
		?>
		<form enctype="multipart/form-data" method="POST" action="<?php print $this->formAction; ?>">
		<table cellpadding=0 cellspacing=0 border=0 width=90%>
			<tr><td><?php print _("CSV File"); ?>:</font></td><td><input class="smaller" type="file" name="file" value="" /></td></tr>
			<tr><td colspan=2><font><center><br /><input class="smaller" type="submit" value="<?php print _("Upload CSV"); ?>" /></center></font></td></tr>				
		</table>
		</form>
		<p><?php print _('This page allows you to upload a CSV file of hosts into FOG to ease migration.  Right click <a href="./other/hostimport.csv">here</a> and select <strong>Save target as...</strong> or <strong>Save link as...</strong>  to download a template file.  The only fields that are required are hostname and MAC address.  Do <strong>NOT</strong> include a header row, and make sure you resave the file as a CSV file and not XLS!'); ?></p>
		<?php
	}
	
	public function import_post()
	{
		// TODO: Rewrite this... it works for now
		try
		{
			// Error checking
			if ($_FILES["file"]["error"] > 0)
			{
				throw new Exception(sprintf('%s: %s', _('Error'), (is_array($_FILES["file"]["error"]) ? implode(', ', $_FILES["file"]["error"]) : $_FILES["file"]["error"])));
			}
			if (!file_exists($_FILES["file"]["tmp_name"]))
			{
				throw new Exception('Could not find tmp filename');
			}
			
			$numSuccess = $numFailed = $numAlreadyExist = 0;
			
			$handle = fopen($_FILES["file"]["tmp_name"], "r");
			while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) 
			{
				// Ignore header data if left in CSV
				if (preg_match('#ie#', $data[0]))
				{
					continue;
				}
				
				$totalRows++;
				if ( count( $data ) < 6 && count( $data ) >= 2 )
				{
					try
					{
						// Error checking
						if ($this->FOGCore->getClass('HostManager')->doesHostExistWithMac(new MACAddress($data[0])))
						{
							throw new Exception('A Host with this MAC Address already exists');
						}
					
						$Host = new Host(array(
							'name'		=> $data[1],
							'description'	=> $data[3] . ' ' . _('Uploaded by batch import on'),
							'ip'		=> $data[2],
							'imageID'	=> $data[5],
							'createdTime'	=> time(),
							'createdBy'	=> $this->FOGUser->get('name'),
							'mac'		=> $data[0]
						));
						
						if ($Host->save())
						{
							$numSuccess++;
						}
						else
						{
							$numFailed++;
						}
							
					}
					catch (Exception $e )
					{
						$numFailed++;
						$uploadErrors .= sprintf('%s #%s: %s<br />', _('Row'), $totalRows, $e->getMessage());
					}					
				}
				else
				{
					$numFailed++;
					$uploadErrors .= sprintf('%s #%s: %s<br />', _('Row'), $totalRows, _('Invalid number of cells'));
				}
			}
			fclose($handle);
		}
		catch (Exception $e)
		{
			$error = $e->getMessage();
		}
		
		// Title
		$this->title = 'Import Host Results';
		
		// Output
		?>
		<table cellpadding=0 cellspacing=0 border=0 width=100%>
			<tr><td width="25%"><?php print _("Total Rows"); ?></font></td><td><?php print $totalRows; ?></td></tr>
			<tr><td><?php print _("Successful Hosts"); ?></td><td><?php print $numSuccess; ?></td></tr>
			<tr><td><?php print _("Failed Hosts"); ?></td><td><?php print $numFailed; ?></td></tr>				
			<tr><td><?php print _("Errors"); ?></td><td><?php print $uploadErrors; ?></td></tr>						
		</table>
		<?php
	}
	
	public function export()
	{
		// Title
		$this->title = _('TODO!');
	}
	
	public function export_post()
	{
	
	}
}

// Register page with FOGPageManager
$FOGPageManager->add(new HostManagementPage());