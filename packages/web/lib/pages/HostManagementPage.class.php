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
			'<input type="checkbox" name="toggle-checkbox" class="toggle-checkbox" checked="checked" />',
			'',
			_('Host Name'),
			_('MAC'),
			_('IP Address'),
			''
		);
		
		// Row templates
		$this->templates = array(
			'<input type="checkbox" name="host[]" value="${id}" class="toggle-host" checked="checked" />',
			'<span class="icon ping"></span>',
			'<a href="?node=host&sub=edit&id=${id}" title="Edit">${name}</a>',
			'${mac}',
			'${ip}',
			'<a href="?node=host&sub=deploy&sub=deploy&type=1&id=${id}"><span class="icon icon-download" title="Deploy"></span></a> <a href="?node=host&sub=deploy&type=8&id=${id}"><span class="icon icon-multicast" title="Multi-cast Deploy"></span></a> <a href="?node=host&sub=edit&id=${id}"><span class="icon icon-edit" title="Edit"></span></a>'
		);
		
		// Row attributes
		$this->attributes = array(
			array('width' => 22),
			array('width' => 20),
			array(),
			array('width' => 110),
			array('width' => 110),
			array('width' => 80, 'class' => 'c')
		);
	}
	
	// Pages
	public function index()
	{
		// Set title
		$this->title = _('All Hosts');
		
		// Find data -> Push data
		foreach ($this->FOGCore->getClass('HostManager')->find() AS $Host)
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
		//$this->searchFormURL = 'ajax/host.search.php';
		//$this->searchFormURL = $this->formAction;
		$this->searchFormURL = sprintf('%s?node=%s&sub=search', $_SERVER['PHP_SELF'], $this->node);

		// Output
		$this->render();
	}
	
	public function search_post()
	{
		// Variables
		$keyword = preg_replace('#%+#', '%', '%' . preg_replace('#[[:space:]]#', '%', $this->REQUEST['crit']) . '%');
		$findWhere = array(
			'name'		=> $keyword,
			'mac'		=> $keyword,
			'ip'		=> $keyword,
			'description'	=> $keyword
		);
	
		// Find data -> Push data
		foreach ($this->FOGCore->getClass('HostManager')->find($findWhere, 'OR') AS $Host)
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
				<tr><td width="35%"><?php print _("Host Name"); ?></td><td><input type="text" name="host" value="<?php print $_POST['host']; ?>" maxlength="15" class="hostname-input" /> *</td></tr>
				<tr><td><?php print _("Host IP"); ?></td><td><input type="text" name="ip" value="<?php print $_POST['ip']; ?>" /></td></tr>
				<tr><td><?php print _("Primary MAC"); ?></td><td><input type="text" id="mac" name="mac" value="<?php print $_POST['mac']; ?>" /> * &nbsp; <span id="priMaker"></span> </td></tr>
				<tr><td><?php print _("Host Description"); ?></td><td><textarea name="description" rows="5" cols="40"><?php print $_POST['description']; ?></textarea></td></tr>
				<tr><td><?php print _("Host Image"); ?></td><td><?php print $this->FOGCore->getClass('ImageManager')->buildSelectBox($_POST['image']);  ?></td></tr>
				<tr><td><?php print _("Host Kernel"); ?></td><td><input type="text" name="kern" value="<?php print $_POST['kern']; ?>" /></td></tr>		
				<tr><td><?php print _("Host Kernel Arguments"); ?></td><td><input type="text" name="args" value="<?php print $_POST['args']; ?>" /></td></tr>	
				<tr><td><?php print _("Host Primary Disk"); ?></td><td><input type="text" name="dev" value="<?php print $_POST['dev']; ?>" /></td></tr>		
			</table>

			<br />
			<h2><?php print _("Active Directory"); ?></h2>		
			<table cellpadding="0" cellspacing="0" border="0" width="100%">
				<tr><td width="35%"><?php print _("Join Domain after image task"); ?></td><td><input id="adEnabled" type="checkbox" name="domain" value="on"<?php print ($_POST['domain'] == 'on' ? ' selected="selected"' : ''); ?> /></td></tr>
				<tr><td><?php print _("Domain name"); ?></td><td><input id="adDomain" type="text" name="domainname" value="<?php print $_POST['domainname']; ?>" /></td></tr>				
				<tr><td><?php print _("Organizational Unit"); ?></td><td><input id="adOU" type="text" name="ou" value="<?php print $_POST['ou']; ?>" /> <?php print _("(Blank for default)"); ?></td></tr>				
				<tr><td><?php print _("Domain Username"); ?></td><td><input id="adUsername" type="text" name="domainuser" value="<?php print $_POST['domainuser']; ?>" /></td></tr>						
				<tr><td><?php print _("Domain Password"); ?></td><td><input id="adPassword" type="text" name="domainpassword" value="<?php print $_POST['domainpassword']; ?>" /> <?php print _("(Must be encrypted)"); ?></td></tr>											
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
				$this->FOGCore->redirect(sprintf('?node=%s&sub=edit&%s=%s', $this->REQUEST['node'], $this->id, $Host->get('id')));
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
		$Host = new Host($this->REQUEST['id']);
		
		// Title - set title for page title in window
		$this->title = sprintf('%s: %s', _('Edit'), $Host->get('name'));
		// But disable displaying in content
		$this->titleEnabled = false;
		
		// Hook
		$this->HookManager->processEvent('HOST_EDIT', array('Host' => &$Host));
		
		// TODO: Put table rows into variables -> Add hooking
		// TODO: Add ping lookup + additional macs from original HTML (its awful and messy, needs a rewrite)
		// TODO: Rewrite HTML & PHP
		?>
		<!--<form method="POST" action="<?php print $this->formAction; ?>">
			<input type="hidden" name="id" value="<?php print $this->REQUEST['id']; ?>" />-->
			<div id="tab-container">
				<!-- General -->
				<div id="host-general">
					<form method="POST" action="<?php print $this->formAction; ?>&tab=host-general">
						<input type="hidden" name="id" value="<?php print $this->REQUEST['id']; ?>" />
						<h2><?php print _("Edit host definition"); ?></h2>
						<table cellpadding="0" cellspacing="0" border="0" width="100%">
							<tr><td width="35%"><?php print _("Host Name"); ?></td><td><input type="text" name="host" value="<?php print $Host->get('name'); ?>" maxlength="15" class="hostname-input" /> *</td></tr>
							<tr><td><?php print _("Host IP"); ?></td><td><input type="text" name="ip" value="<?php print $Host->get('ip'); ?>" /></td></tr>
							<tr><td><?php print _("Primary MAC"); ?></td><td><input type="text" id="mac" name="mac" value="<?php print $Host->get('mac'); ?>" /> * &nbsp; <span id="priMaker"></span> </td></tr>
							<tr><td><?php print _("Host Description"); ?></td><td><textarea name="description" rows="5" cols="40"><?php print $Host->get('description'); ?></textarea></td></tr>
							<tr><td><?php print _("Host Image"); ?></td><td><?php print $this->FOGCore->getClass('ImageManager')->buildSelectBox($Host->get('imageID')); ?></td></tr>
							<tr><td><?php print _("Host Kernel"); ?></td><td><input type="text" name="kern" value="<?php print $Host->get('kernel'); ?>" /></td></tr>
							<tr><td><?php print _("Host Kernel Arguments"); ?></td><td><input type="text" name="args" value="<?php print $Host->get('kernelArgs'); ?>" /></td></tr>
							<tr><td><?php print _("Host Primary Disk"); ?></td><td><input type="text" name="dev" value="<?php print $Host->get('kernelDevice'); ?>" /></td></tr>
							<tr><td>&nbsp;</td><td><input type="submit" value="<?php print _("Update"); ?>" /></td></tr>
						</table>
					</form>
				</div>
				
				<!-- Basic Tasks -->
				<div id="host-tasks" class="organic-tabs-hidden">
					<h2><?php print _('Host Tasks'); ?></h2>
					<table cellpadding="0" cellspacing="0" border="0" width="100%">
						<?php
						
						// Find TaskTypes
						$TaskTypes = $this->FOGCore->getClass('TaskTypeManager')->find(array('access' => array('both', 'host'), 'isAdvanced' => '0'), 'AND', 'id');
						
						// Iterate -> Print
						foreach ((array)$TaskTypes AS $TaskType)
						{
							printf('<tr>
									<td class="task-action"><a href="?node=%s&sub=deploy&type=%s&%s=%s"><img src="./images/%s" /><p>%s</p></a></td>
									<td><p>%s</p></td>
								</tr>',
									$this->node,
									$TaskType->get('id'),
									$this->id,
									$Host->get('id'),
									$TaskType->get('icon'),
									_($TaskType->get('name')),
									_($TaskType->get('description'))
							);
						}
						
						?>
						<tr>
							<td class="task-action"><a href="<?php print $this->formAction; ?>#host-tasks" class="advanced-tasks-link"><img src="./images/host-advanced.png" /><p><?php echo(_("Advanced")); ?></p></a></td>
							<td><p><?php print _("View advanced tasks for this host."); ?></p></td>
						</tr>
					</table>
					
					<div id="advanced-tasks" class="hidden">
						<h2><?php print _('Advanced Actions'); ?></h2>
						<table cellpadding="0" cellspacing="0" border="0" width="100%">
							<?php
							
							// Find TaskTypes
							$TaskTypes = $this->FOGCore->getClass('TaskTypeManager')->find(array('access' => array('both', 'host'), 'isAdvanced' => '1'), 'AND', 'id');
							
							// Iterate -> Print
							foreach ((array)$TaskTypes AS $TaskType)
							{
								printf('<tr>
										<td class="task-action"><a href="?node=%s&sub=deploy&type=%s&%s=%s"><img src="./images/%s" /><p>%s</p></a></td>
										<td><p>%s</p></td>
									</tr>',
										$this->node,
										$TaskType->get('id'),
										$this->id,
										$Host->get('id'),
										$TaskType->get('icon'),
										_($TaskType->get('name')),
										_($TaskType->get('description'))
								);
							}
							
							?>
						</table>
					</div>
				</div>
				
				<!-- Active Directory -->
				<div id="host-active-directory" class="organic-tabs-hidden">
					<form method="POST" action="<?php print $this->formAction; ?>&tab=host-active-directory">
						<input type="hidden" name="id" value="<?php print $this->REQUEST['id']; ?>" />
						<h2><?php print _("Active Directory"); ?></h2>
						<table cellpadding=0 cellspacing=0 border=0 width="100%">
							<tr><td><?php print _("Join Domain after image task"); ?></td><td><input id='adEnabled' type="checkbox" name="domain"<?php print ($Host->get('useAD') == '1' ? ' checked="checked"' : ''); ?> /></td></tr>
							<tr><td><?php print _("Domain name"); ?></td><td><input id="adDomain" class="smaller" type="text" name="domainname" value="<?php print $Host->get('ADDomain'); ?>" /></td></tr>
							<tr><td><?php print _("Organizational Unit"); ?><br> <span class="lightColor"><?php print _("(Blank for default)"); ?></span></td><td><input size="50" style="width: 350px" id="adOU" class="smaller" type="text" name="ou" value="<?php print $Host->get('ADOU'); ?>" /></td></tr>
							<tr><td><?php print _("Domain Username"); ?></td><td><input id="adUsername" class="smaller" type="text" name="domainuser" value="<?php print $Host->get('ADUser'); ?>" /></td></tr>
							<tr><td><?php print _("Domain Password"); ?><br /><?php print _("(Must be encrypted)"); ?></td><td><input id="adPassword" class="smaller" type="text" name="domainpassword" style="width: 250px" value="<?php print $Host->get('ADPass'); ?>" /></td></tr>
							<tr><td colspan=2><center><br /><input type="hidden" name="updatead" value="1" /><input type="submit" value="<?php print _("Update"); ?>" /></td></tr>
						</table>
					</form>
				</div>
				
				<!-- Printers -->
				<div id="host-printers" class="organic-tabs-hidden">
					<form method="POST" action="<?php print $this->formAction; ?>&tab=host-printers">
						<input type="hidden" name="id" value="<?php print $this->REQUEST['id']; ?>" />
						<h2><?php print _("Host Printer Configuration"); ?></h2>
						<p><?php print _("Select Management Level for this Host"); ?></p>
						<p>
							<input type="radio" name="level" value="0"<?php print ($Host->get('printerLevel') === '0' || $Host->get('printerLevel') === '' ? ' checked="checked"' : ''); ?> /><?php print _("No Printer Management"); ?><br/>
							<input type="radio" name="level" value="1"<?php print ($Host->get('printerLevel') === '1' ? ' checked="checked"' : ''); ?> /><?php print _("Add Only"); ?><br/>
							<input type="radio" name="level" value="2"<?php print ($Host->get('printerLevel') === '2' ? ' checked="checked"' : ''); ?> /><?php print _("Add and Remove"); ?><br/>
						</p>
						
						<table cellpadding=0 cellspacing=0 border=0 width=100%>
							<thead>
								<tr class="header">
									<td><?php print _("Default"); ?></td>
									<td><?php print _("Printer Alias"); ?></td>
									<td><?php print _("Printer Model"); ?></td>
									<td><?php print _("Remove"); ?></td>
								</tr>
							</thead>
							<tbody>
								<?php
								
								foreach ($Host->getPrinters() AS $Printer)
								{
									printf('<tr>
										<td>%s</td>
										<td>%s</td>
										<td>%s</td>
										<td><input type="checkbox" name="printerRemove[]" value="%s" /></td>
									</tr>',
										($Printer->get('default') ? _('Yes') : ''),
										$Printer->get('name'),
										$Printer->get('model'),
										$Printer->get('id')
									);
								}
								
								?>
							</tbody>
						</table>
						
						<br /><br />
						<h2>Add new printer</h2>
						<?php
						print $this->FOGCore->getClass('PrinterManager')->buildSelectBox('', "prnt")
						?>
						<br />
						<input type="submit" value="<?php print _("Update"); ?>" />
					</form>
				</div>
				
				<!-- Snapins -->
				<div id="host-snapins" class="organic-tabs-hidden">
					<form method="POST" action="<?php print $this->formAction; ?>&tab=host-snapins">
						<input type="hidden" name="id" value="<?php print $this->REQUEST['id']; ?>" />
						<h2><?php print _("Snapins"); ?></h2>
						
						<table cellpadding=0 cellspacing=0 border=0 width=100%>
							<thead>
								<tr class="header">
									<td><?php print _("Snapin Name"); ?></td>
									<td><?php print _("Remove"); ?></td>
								</tr>
							</thead>
							<tbody>
								<?php
								
								foreach ($Host->getSnapins() AS $Snapin)
								{
									printf('<tr>
										<td>%s</td>
										<td><input type="checkbox" name="snapinRemove[]" value="%s" /></td>
									</tr>',
										$Snapin->get('name'),
										$Snapin->get('id')
									);
								}
								
								?>
							</tbody>
						</table>
						
						<br /><br />
						<h2><?php print _("Add new snapin package"); ?></h2>
						<?php print $this->FOGCore->getClass('SnapinManager')->buildSelectBox(); ?>
						<p><input type="submit" value="<?php print _("Update Snapins"); ?>" /></p>
					</form>
				</div>
				
				<!-- Service Configuration -->
				<div id="host-service" class="organic-tabs-hidden">
					<form method="POST" action="<?php print $this->formAction; ?>&tab=host-service">
						<input type="hidden" name="id" value="<?php print $this->REQUEST['id']; ?>" />
						<h2><?php print _("Service Configuration"); ?></h2>
						<fieldset>
							<legend><?php print _("General"); ?></legend>
							<table cellpadding=0 cellspacing=0 border=0 width="100%">
								<?php
								
								foreach ($this->FOGCore->getClass('ModuleManager')->find() AS $Module)
								{
									printf('<tr><td width="270">%s</td><td><input type="checkbox" name="%s" value="1"%s /></td><td><span class="icon icon-help hand" title="%s"></span></td></tr>',
										$Module->get('name') . ' ' . _('Enabled?'),
										$Module->get('shortName'),
										($Host->getModuleStatus($Module) ? ' checked="checked"' : ''),
										str_replace('"', '\"', $Module->get('description'))
									);
										
								}
								?>
																						
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
						<input type="hidden" name="id" value="<?php print $this->REQUEST['id']; ?>" />
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
						<input type="hidden" name="id" value="<?php print $this->REQUEST['id']; ?>" />
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
						<input type="hidden" name="id" value="<?php print $this->REQUEST['id']; ?>" />
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
						<tr class="header"><td><b>&nbsp;<?php print _("Action"); ?></b></td><td><b>&nbsp;<?php print _("Username"); ?></b></td><td><b>&nbsp;<?php print _("Time"); ?></b></td><td><b>&nbsp;<?php print _("Description"); ?></b></td></tr>		
						
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
		$Host = new Host($this->REQUEST['id']);
		
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
			switch ($this->REQUEST['tab'])
			{
				// General
				case 'host-general';
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
					$Host	->set('additionalMACs', (array)$_POST['additionalMACs']);
					
					break;
				
				// Active Directory
				case 'host-active-directory';
				
					$Host	->set('useAD',		($_POST["domain"] == "on" ? '1' : '0'))
						->set('ADDomain',	$_POST['domainname'])
						->set('ADOU',		$_POST['ou'])
						->set('ADUser',		$_POST['domainuser'])
						->set('ADPass',		$_POST['domainpassword']);
					
					break;
				
				// Printers
				case 'host-printers';
				
					// Set printer level for Host
					$Host->set('printerLevel', (int)$this->REQUEST['level']);
				
					// Add
					if (!empty($this->REQUEST['prnt']))
					{
						$Host->addPrinter($this->REQUEST['prnt']);
					}
					
					// Remove
					if (!empty($this->REQUEST['printerRemove']))
					{
						$Host->removePrinter($this->REQUEST['printerRemove']);
					}
					
					// TODO: Set default printer
					/*
					if ( $_GET["default"] !== null )
					{
						setDefaultPrinter( $GLOBALS['conn'], $_GET["default"] );
					}
					*/
					
					break;
				
				// Snapins
				case 'host-snapins';
					
					// Add
					if (!empty($this->REQUEST['snapin']))
					{
						$Host->addSnapin($this->REQUEST['snapin']);
					}
					
					// Remove
					if (!empty($this->REQUEST['snapinRemove']))
					{
						$Host->removeSnapin($this->REQUEST['snapinRemove']);
					}
					
					break;
					
				// Service
				case 'host-service';
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

						setHostModuleStatus( $GLOBALS['conn'], $dircleanupstate, $id, 'dircleanup' );
						setHostModuleStatus( $GLOBALS['conn'], $usercleanupstate, $id, 'usercleanup' );
						setHostModuleStatus( $GLOBALS['conn'], $displaymanagerstate, $id, 'displaymanager' );
						setHostModuleStatus( $GLOBALS['conn'], $alostate, $id, 'autologout' );
						setHostModuleStatus( $GLOBALS['conn'], $gfstate, $id, 'greenfog' );
						setHostModuleStatus( $GLOBALS['conn'], $snapinstate, $id, 'snapin' );
						setHostModuleStatus( $GLOBALS['conn'], $hncstate, $id, 'hostnamechanger' );
						setHostModuleStatus( $GLOBALS['conn'], $custate, $id, 'clientupdater' );
						setHostModuleStatus( $GLOBALS['conn'], $hrstate, $id, 'hostregister' );
						setHostModuleStatus( $GLOBALS['conn'], $pmstate, $id, 'printermanager' );
						setHostModuleStatus( $GLOBALS['conn'], $trstate, $id, 'taskreboot' );
						setHostModuleStatus( $GLOBALS['conn'], $utstate, $id, 'usertracker' );

						// update screen settings
						$x = mysql_real_escape_string( $_POST["x"] );
						$y = mysql_real_escape_string( $_POST["y"] );
						$r = mysql_real_escape_string( $_POST["r"] );
						if ( $x == "" && $y == "" && $z == "" )
						{
							$sql = "DELETE FROM hostScreenSettings WHERE hssHostID = '$id'";
							$res = mysql_query( $sql, $GLOBALS['conn'] ) or criticalError( mysql_error(), _("FOG :: Database error!") );
						}
						else
						{
							$sql = "SELECT
									COUNT(*) as cnt
								FROM
									hostScreenSettings
								WHERE
									hssHostID = '$id'";
							$res = mysql_query( $sql, $GLOBALS['conn'] ) or criticalError( mysql_error(), _("FOG :: Database error!") );
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
							if ( ! mysql_query( $sql, $GLOBALS['conn'] ) )
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
						$res = mysql_query( $sql, $GLOBALS['conn'] ) or criticalError( mysql_error(), _("FOG :: Database error!") );
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
						if ( ! mysql_query( $sql, $GLOBALS['conn'] ) )
							criticalError( mysql_error(), _("FOG :: Database error!") );

					}
					*/
					
					break;
				
				// Hardware Inventory
				case 'host-hardware-inventory';
					/*
					if ( $_POST["update"] == "1" )
					{

						$prim = mysql_real_escape_string( $_POST["pu"] );
						$other1 = mysql_real_escape_string( $_POST["other1"] );
						$other2 = mysql_real_escape_string( $_POST["other2"] );
						$sql = "update inventory set iPrimaryUser = '$prim', iOtherTag = '$other1', iOtherTag1 ='$other2' where iHostID = '$id'";
						if ( !mysql_query( $sql, $GLOBALS['conn'] ) )
						{
							msgBox( mysql_error() );
						}
					}
					*/
					
					break;
				
				// Virus History
				case 'host-virus-history';
					/*
					if ( $_GET["delvid"] !== null && is_numeric( $_GET["delvid"] ) )
					{		
						$vid = mysql_real_escape_string( $_GET["delvid"] );
						clearAVRecord( $GLOBALS['conn'], $vid );
					}

					if ( $_GET["delvid"] == "all"  )
					{
						$member = getImageMemberFromHostID( $GLOBALS['conn'], $id );
						if ( $member != null )
						{
							clearAVRecordsForHost( $GLOBALS['conn'], $member->getMACColon() );
						}
					}
					*/
					
					break;
			}
		
			// Save to database
			if ($Host->save())
			{
				// Hook
				$this->HookManager->processEvent('HOST_EDIT_SUCCESS', array('host' => &$Host));
				
				// Log History event
				$this->FOGCore->logHistory(sprintf('Host updated: ID: %s, Name: %s, Tab: %s', $Host->get('id'), $Host->get('name'), $this->REQUEST['tab']));
			
				// Set session message
				$this->FOGCore->setMessage('Host updated!');
			
				// Redirect to new entry
				$this->FOGCore->redirect(sprintf('?node=%s&sub=edit&%s=%s#%s', $this->REQUEST['node'], $this->id, $Host->get('id'), $this->REQUEST['tab']));
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
			$this->FOGCore->logHistory(sprintf('%s update failed: Name: %s, Tab: %s, Error: %s', _('Host'), $_POST['name'], $this->REQUEST['tab'], $e->getMessage()));
		
			// Set session message
			$this->FOGCore->setMessage($e->getMessage());
			
			// Redirect
			$this->FOGCore->redirect(sprintf('?node=%s&sub=edit&%s=%s#%s', $this->REQUEST['node'], $this->id, $Host->get('id'), $this->REQUEST['tab']));
		}
	}

	public function delete()
	{	
		// Find
		$Host = new Host($this->REQUEST['id']);
		
		// Title
		$this->title = sprintf('%s: %s', _('Remove'), $Host->get('name'));
		
		// Hook
		$this->HookManager->processEvent('HOST_ADD', array('Host' => &$Host));
	
		// TODO: Put table rows into variables -> Add hooking
		?>
		<p class="c"><?php printf('%s <b>%s</b>?', _('Click on the icon below to delete this host from the FOG database.'), $Host->get('name')); ?></p>
		<form method="post" action="<?php print $this->formAction; ?>" class="c">
		<input type="submit" value="<?php print $this->title; ?>" />
		</form>
		<?php
	}
	
	public function delete_post()
	{
		// Find
		$Host = new Host($this->REQUEST['id']);
		
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
			$this->FOGCore->redirect(sprintf('?node=%s', $this->REQUEST['node']));
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
			<tr><td><?php print _("CSV File"); ?></td><td><input class="smaller" type="file" name="file" value="" /></td></tr>
			<tr><td colspan=2><font><center><br /><input class="smaller" type="submit" value="<?php print _("Upload CSV"); ?>" /></center></td></tr>				
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
			<tr><td width="25%"><?php print _("Total Rows"); ?></td><td><?php print $totalRows; ?></td></tr>
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
	
	public function deploy()
	{
		// Find
		$Host = new Host($this->REQUEST['id']);
		$TaskType = new TaskType(($this->REQUEST['type'] ? $this->REQUEST['type'] : '1'));
		
		// Title
		$this->title = sprintf('%s: %s', _('Deploy Task'), $TaskType->get('name'));
		
		// Deploy
		?>
		<p class="c"><b><?php print _("Are you sure you wish to deploy these machines?"); ?></b></p>
		<form method="POST" action="<?php print $this->formAction; ?>" id="deploy-container">
			<div class="confirm-message">
				<div class="advanced-settings">
					<h2><?php print _("Advanced Settings"); ?></h2>
					<p><input type="checkbox" name="shutdown" id="shutdown" value="1" autocomplete="off"> <label for="shutdown"><?php print _("Schedule <u>Shutdown</u> after task completion"); ?></label></p>
					<?php
					if (!$TaskType->isDebug())
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
					<tr>
						<td><?php print $Host->get('name'); ?></td>
						<td><?php print $Host->get('mac') . ($Host->get('ip') ? sprintf('(%s)', $Host->get('ip')) : ''); ?></td>
						<td><?php print $Host->getImage()->get('name'); ?></td>
					</tr>
				</tbody>
			</table>
			
			<p class="c"><input type="submit" value="<?php print _('Deploy to') . ' ' . $Host->get('name'); ?>" /></p>
		</form>
		<?php
	}
	
	public function deploy_post()
	{
		// Find
		$Host = new Host($this->REQUEST['id']);
		
		// Title
		$this->title = "Deploy Image to Host";
		
		// Variables
		$enableShutdown = ($this->REQUEST['shutdown'] == 1 ? true : false);
		$enableSnapins = ($this->REQUEST['deploySnapins'] == 'true' ? true : false);
		$enableDebug = ($this->REQUEST['debug'] == 'true' ? true : false);
		$scheduledDeployTime = strtotime($this->REQUEST['scheduleSingleTime']);
		$taskTypeID = $this->REQUEST['type'];
		
		$taskName = '';
		
		// Deploy
		try
		{
			if ($this->REQUEST['scheduleType'] == 'single')
			{
				// Scheduled Deployment
				// NOTE: Function will throw an exception if it fails
				$Host->createSingleRunScheduledPackage($taskTypeID, $taskName, $scheduledDeployTime, $enableShutdown, $enableSnapins);
				
				// Success
				printf('<div class="task-start-ok"><p>%s</p><p>%s</p></div>', sprintf(_('Successfully created task for deployment of <u>%s</u> to <u>%s</u>'), $Host->getImage()->get('name'), $Host->get('name')), _('Scheduled to start at:') . ' ' . $this->REQUEST['scheduleSingleTime']);
			
			}
			else if ($this->REQUEST['scheduleType'] == 'cron')
			{
				// Cron Deployment
				// NOTE: Function will throw an exception if it fails
				$Host->createCronScheduledPackage($taskTypeID, $taskName, $this->REQUEST['scheduleCronMin'], $this->REQUEST['scheduleCronHour'], $this->REQUEST['scheduleCronDOM'], $this->REQUEST['scheduleCronMonth'], $this->REQUEST['scheduleCronDOW'], $enableShutdown, $enableSnapins);
				
				// Success
				printf('<div class="task-start-ok"><p>%s</p><p>%s</p></div>', sprintf(_('Successfully created task for deployment of <u>%s</u> to <u>%s</u>'), $Host->getImage()->get('name'), $Host->get('name')), _('Cron Schedule:') . ' ' . implode(' ', array($this->REQUEST['scheduleCronMin'], $this->REQUEST['scheduleCronHour'], $this->REQUEST['scheduleCronDOM'], $this->REQUEST['scheduleCronMonth'], $this->REQUEST['scheduleCronDOW'])));
			}
			else
			{
				// Instant Deployment
				// NOTE: Function will throw an exception if it fails
				$Host->createImagePackage($taskTypeID, $taskName, $enableShutdown, $enableDebug, $enableSnapins);
				
				// Success
				printf('<div class="task-start-ok"><p>%s</p></div>', sprintf(_('Successfully created task for deployment of <u>%s</u> to <u>%s</u>'), $Host->getImage()->get('name'), $Host->get('name')));
			}
		}
		catch (Exception $e)
		{
			// Failure
			printf('<div class="task-start-failed"><p>%s</p><p>%s</p></div>', _('Failed to create deploy task'), $e->getMessage());
		}
	}
	
	// Overrides
	public function render()
	{
		// Render
		parent::render();
		
		// Add action-box
		if (($this->sub == '' || in_array($this->sub, array('list', 'search'))) && !$this->FOGCore->isAJAXRequest() && !$this->FOGCore->isPOSTRequest())
		{	
			?>
			<form method="POST" action="<?php print sprintf('%s?node=%s&sub=save_group', $_SERVER['PHP_SELF'], $this->node); ?>" id="action-box">
				<input type="hidden" name="hostIDArray" id="hostIDArray" value="" autocomplete="off" />
				<p><label for="group_new"><?php print _('Create new group'); ?></label><input type="text" name="group_new" id="group_new" autocomplete="off" /></p>
				<p class="c">OR</p>
				<p><label for="group"><?php print _('Add to group'); ?></label> <?php print $this->FOGCore->getClass('GroupManager')->buildSelectBox(); ?></p>
				<p class="c"><input type="submit" value="<?php print _("Process Group Changes"); ?>" /></p>
			</form>
			<?php
		}
	}
	
	public function save_group()
	{
		try
		{
			// Error checking
			if (empty($this->REQUEST['hostIDArray']))
			{
				throw new Exception( _('No Hosts were selected') );
			}
		
			if (empty($this->REQUEST['group_new']) && empty($this->REQUEST['group']))
			{
				throw new Exception( _('No Group selected and no new Group name entered') );
			}
			
			// Determine which method to use
			// New group
			if (!empty($this->REQUEST['group_new']))
			{
				if (!$Group = current($this->FOGCore->getClass('GroupManager')->find(array('name' => $this->REQUEST['group_new']))))
				{
					$Group = new Group(array('name' => $this->REQUEST['group_new']));
					
					if (!$Group->save())
					{
						throw new Exception( _('Failed to create new Group') );
					}
				}
			}
			else
			// Existing group
			{
				if (!$Group = current($this->FOGCore->getClass('GroupManager')->find(array('id' => $this->REQUEST['group']))))
				{
					throw new Exception( _('Invalid Group ID') );
				}
			}
			
			// Valid
			if (!$Group->isValid())
			{
				throw new Exception( _('Group is Invalid') );
			}
			
			// Main
			foreach ((array)explode(',', $this->REQUEST['hostIDArray']) AS $hostID)
			{
				//$Group->add('hosts', $hostID);
				$GroupAssociation = new GroupAssociation(array('hostID' => $hostID, 'groupID' => $Group->get('id')));
				$GroupAssociation->save();
			}
			
			// Success
			printf('<div class="task-start-ok"><p>%s</p></div>', sprintf(_('Successfully associated Hosts with Group <u>%s</u>'), $Group->get('name')));
		}
		catch (Exception $e)
		{
			// Failure
			printf('<div class="task-start-failed"><p>%s</p><p>%s</p></div>', _('Failed to Associate Hosts with Group'), $e->getMessage());
		}
	}
}

// Register page with FOGPageManager
$FOGPageManager->register(new HostManagementPage());