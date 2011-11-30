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
			<form method="POST" action="<?php print $this->formAction; ?>">
				<input type="hidden" name="id" value="<?php print $this->request['id']; ?>" />
				<div id="tab-container">
					<!-- General -->
					<div id="host-general">
						<h2><?php print _("Edit host definition"); ?></h2>
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
					</div>
					
					<!-- Basic Tasks -->
					<div id="host-tasks" class="organic-tabs-hidden">
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
					</div>
					
					<!-- Active Directory -->
					<div id="host-active-directory" class="organic-tabs-hidden">
						<h2><?php print _("Active Directory"); ?></h2>
						<table cellpadding=0 cellspacing=0 border=0 width="100%">
							<tr><td><?php print _("Join Domain after image task"); ?>:</td><td><input id='adEnabled' type="checkbox" name="domain"<?php print ($Host->get('useAD') == '1' ? ' checked="checked"' : ''); ?> /></td></tr>
							<tr><td><?php print _("Domain name"); ?>:</td><td><input id="adDomain" class="smaller" type="text" name="domainname" value="<?php print $Host->get('ADDomain'); ?>" /></td></tr>
							<tr><td><?php print _("Organizational Unit"); ?>:<br> <span class="lightColor"><?php print _("(Blank for default)"); ?></span></td><td><input size="50" id="adOU" class="smaller" type="text" name="ou" value="<?php print $Host->get('ADOU'); ?>" /></td></tr>
							<tr><td><?php print _("Domain Username"); ?>:</td><td><input id="adUsername" class="smaller" type="text" name="domainuser" value="<?php print $Host->get('ADUser'); ?>" /></td></tr>
							<tr><td><?php print _("Domain Password"); ?>:</td><td><input id="adPassword" class="smaller" type="text" name="domainpassword" value="<?php print $Host->get('ADPass'); ?>" /> <span class="lightColor"><?php print _("(Must be encrypted)"); ?></span></td></tr>
							<tr><td colspan=2><center><br /><input type="hidden" name="updatead" value="1" /><input type="submit" value="<?php print _("Update"); ?>" /></center></td></tr>
						</table>
					</div>
					
					<!-- Printers -->
					<div id="host-printers" class="organic-tabs-hidden">
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
						print getPrinterDropDown( $GLOBALS['conn'], "prnt" );
						?>
						
						<input type="submit" value="<?php print _("Update"); ?>" />
					</div>
					
					<!-- Snapins -->
					<div id="host-snapins" class="organic-tabs-hidden">
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
						<?php print getSnapinDropDown($GLOBALS['conn']); ?>
						<p><input type="submit" value="<?php print _("Add Snapin"); ?>" /></p>
					</div>
					
					<!-- Service Configuration -->
					<div id="host-service" class="organic-tabs-hidden">
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

						<center><table cellpadding=0 cellspacing=0 border=0 width="100%">
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
							<tr><td colspan='3'><center><br /><input type="submit" value="<?php print _("Update"); ?>" /></center></td></tr>
						</table></center>
						<p class="titleBottomLeft"><?php print _("Host Screen Resolution"); ?></p>
							<center><table cellpadding=0 cellspacing=0 border=0 width="100%">
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

							<tr><td width="270"><?php print _("Screen Width (in pixels)"); ?></td><td><input type="text" name="x" value="$x"/></td><td><span class="icon icon-help hand" title="<?php print _("This setting defines the screen horizontal resolution to be used with this host.  Leaving this field blank will force this host to use the global default setting"); ?>"></span></td></tr>
							<tr><td width="270"><?php print _("Screen Height (in pixels)"); ?></td><td><input type="text" name="y" value="$y"/></td><td><span class="icon icon-help hand" title="<?php print _("This setting defines the screen vertial resolution to be used with this host.  Leaving this field blank will force this host to use the global default setting"); ?>"></span></td></tr>
							<tr><td width="270"><?php print _("Screen Refresh Rate"); ?></td><td><input type="text" name="r" value="$r" /></td><td><span class="icon icon-help hand" title="<?php print _("This setting defines the screen refresh rate to be used with this host.  Leaving this field blank will force this host to use the global default setting"); ?>"></span></td></tr>
							<tr><td colspan='3'><center><br /><input type="submit" value="<?php print _("Update"); ?>" /></center></td></tr>
						</table></center>
						<p class="titleBottomLeft"><?php print _("Auto Log Out Settings"); ?></p>
							<center><table cellpadding=0 cellspacing=0 border=0 width="100%">
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

							<tr><td width="270"><?php print _("Auto Log Out Time (in minutes)"); ?></td><td><input type="text" name="tme" value="$tme"/></td><td><span class="icon icon-help hand" title="<?php print _("This setting defines the time to auto log out this host."); ?>"></span></td></tr>
							<tr><td colspan='3'><center><br /><input type="submit" value="<?php print _("Update"); ?>" /></center></td></tr>
						</table></center>
					</div>
					
					<!-- Inventory -->
					<div id="host-hardware-inventory" class="organic-tabs-hidden">
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
								<tr><td>&nbsp;</td><td colspan='2'><center><input type="hidden" name="update" value="1" /><input type="submit" value="<?php print _("Update"); ?>" /></center></td></tr>
								<?php
							}
						}
						else
						{
							?><tr><td colspan="3" class="c"><?php print _("No Inventory found for this host"); ?></td></tr><?php
						}
						?>
						</table>
					</div>
					
					<!-- Virus -->
					<div id="host-virus-history" class="organic-tabs-hidden">
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
					</div>
					
					<!-- Login History -->
					<div id="host-login-history" class="organic-tabs-hidden">
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
					</div>
				</div>
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