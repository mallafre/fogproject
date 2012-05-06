<?php

// Blackout - 10:00 AM 13/12/2011
class ServiceConfigurationPage extends FOGPage
{
	// Base variables
	var $name = 'Service Configuration';
	var $node = 'service';
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
			_('Username'),
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
		?>
		<div id="tab-container">
			<!-- Home -->
			<div id="home">
				<h2><?php print _('FOG Service Configuration Information'); ?></h2>
				<p>This section of the FOG management portal allows you to configure how the FOG service functions on client computers.  The settings in this section tend to be global settings that effect all hosts.  If you are looking to configure settings for a service module that is specific to a host, please see the Servicesection.  To get started editing global settings, please select an item from the left hand menu.</p>
			</div>
			
			<!-- Auto Log Out -->
			<div id="auto-logout">
				<h2><?php print _('Auto Log Out'); ?></h2>
				<form method="POST" action="<?php print $this->formAction; ?>&tab=auto-logout">
				
					<p><?php echo(_("The Auto Log Out service module will log a user out of a workstation after x minutes of inactivity.")); ?></p>
					<h2><?php echo(_("Service Status")); ?></h2>	
					<table cellpadding=0 cellspacing=0 border=0 width=100%>
					<tr><td width="270"><?php print _("Auto Log Out Enabled?"); ?></td><td><input type="checkbox" name="en"<?php print ($this->FOGCore->getSetting("FOG_SERVICE_AUTOLOGOFF_ENABLED") ? ' checked="checked"' : ''); ?> /></td><td><span class="icon icon-help hand" title="<?php print _("This setting will globally enable or disable the auto log out service module.  If you disable the module, it will be disabled for all clients, regardless of the Servicespecific setting."); ?>"></span></td></tr>
					<tr><td>&nbsp;</td><td><input type="submit" value="<?php print _("Update"); ?>" /></td></tr>			
					</table>
					<h2><?php print _("Default Setting"); ?></h2>
					<p><?php print _("Default log out time (in minutes)"); ?>: <input type="text" name="tme" value="<?php print $this->FOGCore->getSetting("FOG_SERVICE_AUTOLOGOFF_MIN"); ?>" /></p>
					<p><input type="submit" value="<?php print _("Update Defaults"); ?>" /></p>
				
				</form>
			</div>
			
			<!-- Client Updater -->
			<div id="client-updater">
				<h2><?php print _('Client Updater'); ?></h2>
				<form method="POST" action="<?php print $this->formAction; ?>&tab=client-updater">
				
				<p><?php echo(_("Client updater will keeps your FOG client up to date.")); ?></p>
				<h2><?php echo(_("Service Status")); ?></h2>
				<table cellpadding=0 cellspacing=0 border=0 width=100%>
				<tr><td width="270"><?php print _("Client Update Enabled?"); ?></td><td><input type="checkbox" name="en"<?php print ($this->FOGCore->getSetting("FOG_SERVICE_CLIENTUPDATER_ENABLED") ? ' checked="checked"' : ''); ?> /></td><td><span class="icon icon-help hand" title="<?php print _("This setting will globally enable or disable the client updater service module.  If you disable the module, it will be disabled for all clients, regardless of the Servicespecific setting."); ?>"></span></td></tr>
				<tr><td>&nbsp;</td><td><input type="submit" value="<?php print _("Update"); ?>" /></td></tr>			
				</table>
				
				</form>
			</div>
			
			<!-- Directory Cleaner -->
			<div id="directory-cleaner">
				<h2><?php print _('Directory Cleaner'); ?></h2>
				<form method="POST" action="<?php print $this->formAction; ?>&tab=directory-cleaner">
				
					<p><?php echo(_("The Directory Cleaner module will clean out all files and directories within a parent directory during log off.  This module can be useful if you have an application that uses cache that you don't want to persist between users.")); ?></p>
					<h2><?php echo(_("Service Status")); ?></h2>	
					
					
						<table cellpadding=0 cellspacing=0 border=0 width=100%>
							<tr><td width="270"><?php print _("Directory Cleaner Enabled?"); ?></td><td><input type="checkbox" name="en"<?php print ($this->FOGCore->getSetting("FOG_SERVICE_DIRECTORYCLEANER_ENABLED") ? ' checked="checked"' : ''); ?> /></td><td><span class="icon icon-help hand" title="<?php print _("This setting will globally enable or disable the directory cleaner service module.  If you disable the module, it will be disabled for all clients, regardless of the Servicespecific setting."); ?>"></span></td></tr>
							<tr><td>&nbsp;</td><td><input type="submit" value="<?php print _("Update"); ?>" /></td></tr>			
						</table>
						
					<h2><?php print _("Add Directory"); ?></h2>


							<p><?php print _("Directory Path"); ?>: <input type="text" name="dir" /></p>
							<p><input type="submit" value="<?php print _("Add Directory"); ?>" /></p>
							
						
					<h2><?php print _("Directories Cleaned"); ?></h2>
					
					<table cellpadding=0 cellspacing=0 border=0 width=100%>
						<tr class="header"><td><b><?php print _("Path"); ?></b></td><td><b><?php print _("Remove"); ?></b></td></tr>
						<?php
						
						$this->DB->query('SELECT * FROM dirCleaner ORDER BY dcID');
						
						if ($this->DB->num_rows())
						{
							while ($row = $this->DB->fetch()->get())
							{
								printf('<tr%s><td>%s</td><td><a href="%s"><span class="icon icon-kill"></span></a></td></tr>',
									(++$i % 2 ? ' class="alt"' : ''),
									$row["dcPath"],
									"?node=$_GET[node]&sub=$_GET[sub]&delid=$row[dcID]"
								);
							}
						}
						else
						{
							?>
							<tr><td colspan="2"><?php print _("No Entries Found."); ?></td></tr>
							<?php
						}
						?>
					</table>
	
				</form>
			</div>
			
			<!-- Display Manager -->
			<div id="display-manager">
				<h2><?php print _('Display Manager'); ?></h2>
				<form method="POST" action="<?php print $this->formAction; ?>&tab=display-manager">

				
					<p><?php echo(_("The Display Manager service module will reset a computers display to a fixed setting such as 1024 x 768 on user log in.")); ?></p>
					<h2><?php echo(_("Service Status")); ?></h2>	


					<table cellpadding=0 cellspacing=0 border=0 width=100%>
					<tr><td width="270"><?php print _("Display Manager Enabled?"); ?></td><td><input type="checkbox" name="en"<?php print ($this->FOGCore->getSetting("FOG_SERVICE_DISPLAYMANAGER_ENABLED") ? ' checked="checked"' : ''); ?> /></td><td><span class="icon icon-help hand" title="<?php print _("This setting will globally enable or disable the display manager service module.  If you disable the module, it will be disabled for all clients, regardless of the Servicespecific setting."); ?>"></span></td></tr>
					<tr><td>&nbsp;</td><td><input type="submit" value="<?php print _("Update"); ?>" /></td></tr>			
					</table>

					<h2><?php print _("Default Setting"); ?></h2>
					<p><?php print _("Default width"); ?>: <input type="text" name="width" value="<?php print $this->FOGCore->getSetting("FOG_SERVICE_DISPLAYMANAGER_X"); ?>" /></p>
					<p><?php print _("Default height"); ?>: <input type="text" name="height" value="<?php print $this->FOGCore->getSetting("FOG_SERVICE_DISPLAYMANAGER_Y"); ?>" /></p>			
					<p><?php print _("Default Refresh Rate"); ?>: <input type="text" name="refresh" value="<?php print $this->FOGCore->getSetting("FOG_SERVICE_DISPLAYMANAGER_R"); ?>" /></p>						
					<p><input type="submit" value="<?php print _("Update Defaults"); ?>" /></p>


				</form>
			</div>
			
			<!-- Green FOG -->
			<div id="green-fog">
				<h2><?php print _('Green FOG'); ?></h2>
				<form method="POST" action="<?php print $this->formAction; ?>&tab=green-fog">


					<p><?php echo(_("Green FOG is a service module that will shutdown / restart the client computers at a set time.")); ?></p>
					<h2><?php echo(_("Service Status")); ?></h2>	


					<table cellpadding=0 cellspacing=0 border=0 width=100%>
					<tr><td width="270"><?php print _("Green FOG Enabled?"); ?></td><td><input type="checkbox" name="en"<?php print ($this->FOGCore->getSetting("FOG_SERVICE_GREENFOG_ENABLED") ? ' checked="checked"' : ''); ?> /></td><td><span class="icon icon-help hand" title="<?php print _("This setting will globally enable or disable the Green FOG service module.  If you disable the module, it will be disabled for all clients, regardless of the Servicespecific setting."); ?>"></span></td></tr>
					<tr><td>&nbsp;</td><td><input type="submit" value="<?php print _("Update"); ?>" /></td></tr>			
					</table>

					<h2><?php print _("Shutdown/Reboot Schedule"); ?></h2>


					<p><?php print _("Add Event (24 Hour Format)"); ?>: <input class="short" type="text" name="h" maxlength="2" value="<?php print _("HH"); ?>" onFocus="this.value=''" /> : <input class="short" type="text" name="m" maxlength="2" value="<?php print _("MM"); ?>" onFocus="this.value=''" /> <select name="style" size="1"><option value="" label="Select One"><?php print _("Select One"); ?></option><option value="s" label="Shut Down"><?php print _("Shut Down"); ?></option><option value="r" label="Reboot"><?php print _("Reboot"); ?></option></select></p>
					<p><input type="submit" value="<?php print _("Add Event"); ?>" /></p>


					<table cellpadding=0 cellspacing=0 border=0 width=100%>
						<tr class="header"><td><b><?php print _("Time"); ?></b></td><td><b><?php print _("Action"); ?></b></td><td><b><?php print _("Remove"); ?></b></td></tr>
						<?php
					
						$this->DB->query('SELECT * FROM greenFog order by gfHour, gfMin');
						
						if ($this->DB->num_rows())
						{
							while ($row = $this->DB->fetch()->get())
							{
								printf('<tr%s><td>%s:%s</td><td>%s</td><td><a href="%s"><span class="icon icon-kill"></span></a></td></tr>',
									(++$i % 2 ? ' class="alt"' : ''),
									$row["gfHour"],
									$row["gfMin"],
									($ar["gfAction"] == 'r' ? 'Reboot' : ($ar["gfAction"] == 's' ? 'Shutdown' : 'N/A')),
									"?node=$_GET[node]&sub=$_GET[sub]&delid=$row[gfID]"
								);
							}
						}
						else
						{
							?>
							<tr><td colspan="3"><?php print _("No Entries Found."); ?></td></tr>
							<?php
						}
						?>
					</table>
 

				</form>
			</div>
			
			<!-- Hostname Changer -->
			<div id="hostname-changer">
				<h2><?php print _('Hostname Changer'); ?></h2>
				<form method="POST" action="<?php print $this->formAction; ?>&tab=hostname-changer">

					<h2><?php echo(_("Configure Hostname Changer Service Module")); ?></h2>
					<p><?php echo(_("The hostname changer is a service module that rename the client's hostname after imaging.  This service also handles Microsoft Active Directory integration.")); ?></p>
					<h2><?php echo(_("Service Status")); ?></h2>
					<table cellpadding=0 cellspacing=0 border=0 width=100%>
						<tr><td width="270"><?php print _("Hostname Changer Enabled?"); ?></td><td><input type="checkbox" name="en"<?php print ($this->FOGCore->getSetting("FOG_SERVICE_HOSTNAMECHANGER_ENABLED") ? ' checked="checked"' : ''); ?> /></td><td><span class="icon icon-help hand" title="<?php print _("This setting will globally enable or disable the hostname changer service module.  If you disable the module, it will be disabled for all clients, regardless of the Servicespecific setting."); ?>"></span></td></tr>
						<tr><td>&nbsp;</td><td><input type="submit" value="<?php print _("Update"); ?>" /></td></tr>			
					</table>

				</form>
			</div>
			
			<!-- ServiceRegistration -->
			<div id="host-registration">
				<h2><?php print _('ServiceRegistration'); ?></h2>
				<form method="POST" action="<?php print $this->formAction; ?>&tab=host-registration">


					<h2><?php echo(_("Configure Serviceregistration Service Module")); ?></h2>
					<p><?php echo(_("Serviceregistration is a service module that will register unknown fog clients with the fog server.")); ?></p>
					<h2><?php echo(_("Service Status")); ?></h2>
					
					<table cellpadding=0 cellspacing=0 border=0 width=100%>
						<tr><td width="270"><?php print _("ServiceRegister Enabled?"); ?></td><td><input type="checkbox" name="en"<?php print ($this->FOGCore->getSetting("FOG_SERVICE_HOSTREGISTER_ENABLED") ? ' checked="checked"' : ''); ?> /></td><td><span class="icon icon-help hand" title="<?php print _("This setting will globally enable or disable the Serviceregister service module.  If you disable the module, it will be disabled for all clients, regardless of the Servicespecific setting."); ?>"></span></td></tr>
						<tr><td>&nbsp;</td><td><input type="submit" value="<?php print _("Update"); ?>" /></td></tr>			
					</table>

				</form>
			</div>
			
			<!-- Printer Manager -->
			<div id="printer-manager">
				<h2><?php print _('Printer Manager'); ?></h2>
				<form method="POST" action="<?php print $this->formAction; ?>&tab=printer-manager">


				<p><? echo(_("Printer Manager is a service module that will install, remove, and set the default printer on clients.")); ?></p>
				<h2><? echo(_("Service Status")); ?></h2>
				<table cellpadding=0 cellspacing=0 border=0 width=100%>
					<tr><td width="270"><?php print _("Printer Manager Enabled?"); ?></td><td><input type="checkbox" name="en"<?php print ($this->FOGCore->getSetting("FOG_SERVICE_PRINTERMANAGER_ENABLED") ? ' checked="checked"' : ''); ?> /></td><td><span class="icon icon-help hand" title="<?php print _("This setting will globally enable or disable the printer manager service module.  If you disable the module, it will be disabled for all clients, regardless of the Servicespecific setting."); ?>"></span></td></tr>
					<tr><td>&nbsp;</td><td><input type="submit" value="<?php print _("Update"); ?>" /></td></tr>			
				</table>


				</form>
			</div>
			
			<!-- Snapin Client -->
			<div id="snapin-client">
				<h2><?php print _('Snapin Client'); ?></h2>
				<form method="POST" action="<?php print $this->formAction; ?>&tab=snapin-client">


					<p><?php echo(_("The Snapin Service module is used to install snapin files to the client computers.")); ?></p>
					<h2><?php echo(_("Service Status")); ?></h2>
					
					<table cellpadding=0 cellspacing=0 border=0 width=100%>
						<tr><td width="270"><?php print _("Snapin Client Enabled?"); ?></td><td><input type="checkbox" name="en"<?php print ($this->FOGCore->getSetting("FOG_SERVICE_SNAPIN_ENABLED") ? ' checked="checked"' : ''); ?> /></td><td><span class="icon icon-help hand" title="<?php print _("This setting will globally enable or disable the snapin service module.  If you disable the module, it will be disabled for all clients, regardless of the Servicespecific setting."); ?>"></span></td></tr>
						<tr><td>&nbsp;</td><td><input type="submit" value="<?php print _("Update"); ?>" /></td></tr>			
					</table>
		
			
				</form>
			</div>
			
			<!-- Task Reboot -->
			<div id="task-reboot">
				<h2><?php print _('Task Reboot'); ?></h2>
				<form method="POST" action="<?php print $this->formAction; ?>&tab=task-reboot">


					<p><?php echo(_("The task reboot service will periodically query the fog service to determine is a the client has a task associated with it.  If it does and no user is logged in, the Servicewill restart.")); ?></p>
					<h2><?php echo(_("Service Status")); ?></h2>
					
					<table cellpadding=0 cellspacing=0 border=0 width=100%>
						<tr><td width="270"><?php print _("Task Reboot Enabled?"); ?></td><td><input type="checkbox" name="en"<?php print ($this->FOGCore->getSetting("FOG_SERVICE_TASKREBOOT_ENABLED") ? ' checked="checked"' : ''); ?> /></td><td><span class="icon icon-help hand" title="<?php print _("This setting will globally enable or disable the task reboot service module.  If you disable the module, it will be disabled for all clients, regardless of the Servicespecific setting."); ?>"></span></td></tr>
						<tr><td>&nbsp;</td><td><input type="submit" value="<?php print _("Update"); ?>" /></td></tr>			
					</table>
		
	
	
				</form>
			</div>
			
			<!-- User Cleanup -->
			<div id="user-cleanup">
				<h2><?php print _('User Cleanup'); ?></h2>
				<form method="POST" action="<?php print $this->formAction; ?>&tab=user-cleanup">


					<p><?php echo(_('The User Cleanup module will clean out "stale" user account left over from services such as dynamic local user.')); ?></p>
					<h2><?php echo(_("Service Status")); ?></h2>	
					
						<table cellpadding=0 cellspacing=0 border=0 width=100%>
							<tr><td width="270"><?php print _("User Cleanup Enabled?"); ?></td><td><input type="checkbox" name="en"<?php print ($this->FOGCore->getSetting("FOG_SERVICE_USERCLEANUP_ENABLED") ? ' checked="checked"' : ''); ?> /></td><td><span class="icon icon-help hand" title="<?php print _("This setting will globally enable or disable the user cleanup module."); ?>"></span></td></tr>
							<tr><td>&nbsp;</td><td><input type="submit" value="<?php print _("Update"); ?>" /></td></tr>			
						</table>
						
					<h2><?php print _("Add Protected User"); ?></h2>


							<p><?php print _("Username"); ?>: <input type="text" name="usr" /></p>
							<p><input type="submit" value="<?php print _("Add User"); ?>" /></p>
							
						
					<h2><?php print _("Current Protected User Accounts"); ?></h2>
					
					<table cellpadding=0 cellspacing=0 border=0 width=100%>
						<tr class="header"><td><b>User</b></td><td><b><?php print _("Remove"); ?></b></td></tr>
						<?php
						
						$this->DB->query('SELECT * FROM userCleanup ORDER BY ucID');
						
						if ($this->DB->num_rows())
						{
							while ($row = $this->DB->fetch()->get())
							{
								printf('<tr%s><td>%s</td><td><a href="%s"><span class="icon icon-kill"></span></a></td></tr>',
									(++$i % 2 ? ' class="alt"' : ''),
									$row["ucName"],
									"?node=$_GET[node]&sub=$_GET[sub]&delid=$row[ucID]"
								);
							}
						}
						else
						{
							?>
							<tr><td colspan="2"><?php print _("No Entries Found."); ?></td></tr>
							<?php
						}
						
						?>
					</table>


				</form>
			</div>
			
			<!-- User Tracker -->
			<div id="user-tracker">
				<h2><?php print _('User Tracker'); ?></h2>
				<form method="POST" action="<?php print $this->formAction; ?>&tab=user-tracker">


					<p><?php echo(_("The user tracker module will watch for local login/logoff and log them to the fog database.")); ?></p>
					<h2><?php echo(_("Service Status")); ?></h2>
					
					<table cellpadding=0 cellspacing=0 border=0 width=100%>
						<tr><td width="270"><?php print _("User Tracker Enabled?"); ?></td><td><input type="checkbox" name="en"<?php print ($this->FOGCore->getSetting("FOG_SERVICE_USERTRACKER_ENABLED") ? ' checked="checked"' : ''); ?> /></td><td><span class="icon icon-help hand" title="<?php print _("This setting will globally enable or disable the user tracker service module.  If you disable the module, it will be disabled for all clients, regardless of the Servicespecific setting."); ?>"></span></td></tr>
						<tr><td>&nbsp;</td><td><input type="submit" value="<?php print _("Update"); ?>" /></td></tr>			
					</table>
		
		
				</form>
			</div>
		</div>
		<?php
	}
	
	public function index_post()
	{
		// Hook
		$this->HookManager->processEvent('SERVICE_EDIT_POST', array('Host' => &$Service));
		
		// POST
		try
		{
			// Tabs
			if ($this->request['tab'] == 'auto-logout')
			{
			/*
if ( $_GET["updatestatus"] == "1" )
{
	$value = "0";
	if ( $_POST["en"] == "on" )
		$value = "1";
	$sql = "UPDATE 
			globalSettings
		SET
			settingValue = '$value'
		WHERE
			settingKey = 'FOG_SERVICE_AUTOLOGOFF_ENABLED'";
	if ( ! mysql_query( $sql, $conn ) )
	{
		criticalError( mysql_error(), _("FOG :: Database error!") );
	}
}
else if ( $_GET["updatedefaults"] == "1" )
{
	
	$tme = mysql_real_escape_string($_POST["tme"]);
	
	if ( is_numeric( $tme ) )
	{
		$sql = "UPDATE 
				globalSettings
			SET
				settingValue = '$tme'
			WHERE
				settingKey = 'FOG_SERVICE_AUTOLOGOFF_MIN'";	
		if ( ! mysql_query( $sql, $conn ) ) criticalError( mysql_error(), _("FOG :: Database error!") );

	}
}
			*/
			}
			elseif ($this->request['tab'] == 'client-updater')
			{
			/*
if ( $_GET["updatestatus"] == "1" )
{
	$value = "0";
	if ( $_POST["en"] == "on" )
		$value = "1";
	$sql = "UPDATE 
			globalSettings
		SET
			settingValue = '$value'
		WHERE
			settingKey = 'FOG_SERVICE_CLIENTUPDATER_ENABLED'";
	if ( ! mysql_query( $sql, $conn ) )
	{
		criticalError( mysql_error(), _("FOG :: Database error!") );
	}
}
			*/
			}
			elseif ($this->request['tab'] == 'directory-cleaner')
			{
			/*
if ( $_GET["updatestatus"] == "1" )
{
	$value = "0";
	if ( $_POST["en"] == "on" )
		$value = "1";
	$sql = "UPDATE 
			globalSettings
		SET
			settingValue = '$value'
		WHERE
			settingKey = 'FOG_SERVICE_DIRECTORYCLEANER_ENABLED'";
	if ( ! mysql_query( $sql, $conn ) )
	{
		criticalError( mysql_error(), _("FOG :: Database error!") );
	}
}
else if ( $_GET["adddir"] == "1" )
{
	$dir = mysql_real_escape_string( $_POST["dir"] );
	if ( ! dircleanDirExists( $conn, $dir ) && trim($dir) != null )
	{
		$sql = "INSERT INTO dirCleaner ( dcPath ) values( '$dir' )";
		if ( ! mysql_query( $sql, $conn ) )
			criticalError( mysql_error(), _("FOG :: Database error!") );
	}
	else
		msgBox( _("Directory Entry Already Exists.") );
}
else if ( $_GET["delid"] !== null && is_numeric( $_GET["delid"] ) )
{
	$delid = mysql_real_escape_string( $_GET["delid"] );
	$sql = "DELETE FROM dirCleaner WHERE dcID = '$delid'";
	if ( ! mysql_query( $sql, $conn ) )
		criticalError( mysql_error(), _("FOG :: Database error!") );
}
			*/
			}
			elseif ($this->request['tab'] == 'display-manager')
			{
			/*
if ( $_GET["updatestatus"] == "1" )
{
	$value = "0";
	if ( $_POST["en"] == "on" )
		$value = "1";
	$sql = "UPDATE 
			globalSettings
		SET
			settingValue = '$value'
		WHERE
			settingKey = 'FOG_SERVICE_DISPLAYMANAGER_ENABLED'";
	if ( ! mysql_query( $sql, $conn ) )
	{
		criticalError( mysql_error(), _("FOG :: Database error!") );
	}
}
else if ( $_GET["updatedefaults"] == "1" )
{
	
	$x = mysql_real_escape_string($_POST["width"]);
	$y = mysql_real_escape_string($_POST["height"]);
	$r = mysql_real_escape_string($_POST["refresh"]);
	

	
	if ( is_numeric( $x ) && is_numeric( $y ) && is_numeric( $r ) )
	{
		$sql = "UPDATE 
				globalSettings
			SET
				settingValue = '$x'
			WHERE
				settingKey = 'FOG_SERVICE_DISPLAYMANAGER_X'";	
		if ( ! mysql_query( $sql, $conn ) ) criticalError( mysql_error(), _("FOG :: Database error!") );

		$sql = "UPDATE 
				globalSettings
			SET
				settingValue = '$y'
			WHERE
				settingKey = 'FOG_SERVICE_DISPLAYMANAGER_Y'";	
		if ( ! mysql_query( $sql, $conn ) ) criticalError( mysql_error(), _("FOG :: Database error!") );
		
		$sql = "UPDATE 
				globalSettings
			SET
				settingValue = '$r'
			WHERE
				settingKey = 'FOG_SERVICE_DISPLAYMANAGER_R'";	
		if ( ! mysql_query( $sql, $conn ) ) criticalError( mysql_error(), _("FOG :: Database error!") );		

	}
} 
			*/
			}
			elseif ($this->request['tab'] == 'green-fog')
			{
			/*
if ( $_GET["updatestatus"] == "1" )
{
	$value = "0";
	if ( $_POST["en"] == "on" )
		$value = "1";
	$sql = "UPDATE 
			globalSettings
		SET
			settingValue = '$value'
		WHERE
			settingKey = 'FOG_SERVICE_GREENFOG_ENABLED'";
	if ( ! mysql_query( $sql, $conn ) )
	{
		criticalError( mysql_error(), _("FOG :: Database error!") );
	}
}
else if ( $_GET["addevent"] == "1" )
{
	
	$h = mysql_real_escape_string($_POST["h"]);
	$m = mysql_real_escape_string($_POST["m"]);
	$t = mysql_real_escape_string($_POST["style"]);
	
	if ( is_numeric( $h ) && is_numeric( $m ) && $h >= 0 && $h <= 23 && $m >= 0 && $m <= 59 && ( $t == "r" || $t == "s" ) )
	{
		$sql = "INSERT INTO 
				greenFog(gfHour, gfMin, gfAction) values('$h', '$m', '$t')";	
		if ( ! mysql_query( $sql, $conn ) ) criticalError( mysql_error(), _("FOG :: Database error!") );

	}
	else
		msgBox( "Failed to add event!
}
else if ( $_GET["delid"] != null && is_numeric( $_GET["delid"] ) )
{
	$sql = "DELETE FROM greenFog WHERE gfID = '" . mysql_real_escape_string( $_GET["delid"] ) . "'";
	if ( ! mysql_query( $sql, $conn ) )
		criticalError( mysql_error(), _("FOG :: Database error!") );
}
			*/
			}
			elseif ($this->request['tab'] == 'hostname-changer')
			{
			/*
if ( $_GET["updatestatus"] == "1" )
{
	$value = "0";
	if ( $_POST["en"] == "on" )
		$value = "1";
	$sql = "UPDATE 
			globalSettings
		SET
			settingValue = '$value'
		WHERE
			settingKey = 'FOG_SERVICE_HOSTNAMECHANGER_ENABLED'";
	if ( ! mysql_query( $sql, $conn ) )
	{
		criticalError( mysql_error(), _("FOG :: Database error!") );
	}
}
			*/
			}
			elseif ($this->request['tab'] == 'host-registration')
			{
			/*
if ( $_GET["updatestatus"] == "1" )
{
	$value = "0";
	if ( $_POST["en"] == "on" )
		$value = "1";
	$sql = "UPDATE 
			globalSettings
		SET
			settingValue = '$value'
		WHERE
			settingKey = 'FOG_SERVICE_HOSTREGISTER_ENABLED'";
	if ( ! mysql_query( $sql, $conn ) )
	{
		criticalError( mysql_error(), _("FOG :: Database error!") );
	}
}
			*/
			}
			elseif ($this->request['tab'] == 'printer-manager')
			{
			/*
if ( $_GET["updatestatus"] == "1" )
{
	$value = "0";
	if ( $_POST["en"] == "on" )
		$value = "1";
	$sql = "UPDATE 
			globalSettings
		SET
			settingValue = '$value'
		WHERE
			settingKey = 'FOG_SERVICE_PRINTERMANAGER_ENABLED'";
	if ( ! mysql_query( $sql, $conn ) )
	{
		criticalError( mysql_error(), _("FOG :: Database error!") );
	}
}
			*/
			}
			elseif ($this->request['tab'] == 'snapin-client')
			{
			/*
if ( $_GET["updatestatus"] == "1" )
{
	$value = "0";
	if ( $_POST["en"] == "on" )
		$value = "1";
	$sql = "UPDATE 
			globalSettings
		SET
			settingValue = '$value'
		WHERE
			settingKey = 'FOG_SERVICE_SNAPIN_ENABLED'";
	if ( ! mysql_query( $sql, $conn ) )
	{
		criticalError( mysql_error(), _("FOG :: Database error!") );
	}
} 
			*/
			}
			elseif ($this->request['tab'] == 'task-reboot')
			{
			/*
if ( $_GET["updatestatus"] == "1" )
{
	$value = "0";
	if ( $_POST["en"] == "on" )
		$value = "1";
	$sql = "UPDATE 
			globalSettings
		SET
			settingValue = '$value'
		WHERE
			settingKey = 'FOG_SERVICE_TASKREBOOT_ENABLED'";
	if ( ! mysql_query( $sql, $conn ) )
	{
		criticalError( mysql_error(), _("FOG :: Database error!") );
	}
}
			*/
			}
			elseif ($this->request['tab'] == 'user-cleanup')
			{
			/*
if ( $_GET["updatestatus"] == "1" )
{
	$value = "0";
	if ( $_POST["en"] == "on" )
		$value = "1";
	$sql = "UPDATE 
			globalSettings
		SET
			settingValue = '$value'
		WHERE
			settingKey = 'FOG_SERVICE_USERCLEANUP_ENABLED'";
	if ( ! mysql_query( $sql, $conn ) )
	{
		criticalError( mysql_error(), _("FOG :: Database error!") );
	}
}
else if ( $_GET["adduser"] == "1" )
{
	$usr = mysql_real_escape_string( $_POST["usr"] );
	if ( ! userCleanupUserExists( $conn, $usr ) && trim($usr) != null )
	{
		$sql = "INSERT INTO userCleanup ( ucName ) values( '$usr' )";
		if ( ! mysql_query( $sql, $conn ) )
			criticalError( mysql_error(), _("FOG :: Database error!") );
	}
	else
		msgBox( _("User Entry Already Exists.") );
}
else if ( $_GET["delid"] !== null && is_numeric( $_GET["delid"] ) )
{
	$delid = mysql_real_escape_string( $_GET["delid"] );
	$sql = "DELETE FROM userCleanup WHERE ucID = '$delid'";
	if ( ! mysql_query( $sql, $conn ) )
		criticalError( mysql_error(), _("FOG :: Database error!") );
}
			*/
			}
			elseif ($this->request['tab'] == 'user-tracker')
			{
			/*
if ( $_GET["updatestatus"] == "1" )
{
	$value = "0";
	if ( $_POST["en"] == "on" )
		$value = "1";
	$sql = "UPDATE 
			globalSettings
		SET
			settingValue = '$value'
		WHERE
			settingKey = 'FOG_SERVICE_USERTRACKER_ENABLED'";
	if ( ! mysql_query( $sql, $conn ) )
	{
		criticalError( mysql_error(), _("FOG :: Database error!") );
	}
}
			*/
			}
		
			// Save to database
			if ($Service->save())
			{
				// Hook
				$this->HookManager->processEvent('SERVICE_EDIT_SUCCESS', array('host' => &$Service));
				
				// Log History event
				$this->FOGCore->logHistory(sprintf('Serviceupdated: ID: %s, Name: %s, Tab: %s', $Service->get('id'), $Service->get('name'), $this->request['tab']));
			
				// Set session message
				$this->FOGCore->setMessage('Serviceupdated!');
			
				// Redirect to new entry
				$this->FOGCore->redirect(sprintf('?node=%s&sub=edit&%s=%s#%s', $this->request['node'], $this->id, $Service->get('id'), $this->request['tab']));
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
			$this->HookManager->processEvent('SERVICE_EDIT_FAIL', array('Host' => &$Service));
			
			// Log History event
			$this->FOGCore->logHistory(sprintf('%s update failed: Name: %s, Tab: %s, Error: %s', _('Host'), $_POST['name'], $this->request['tab'], $e->getMessage()));
		
			// Set session message
			$this->FOGCore->setMessage($e->getMessage());
			
			// Redirect
			$this->FOGCore->redirect(sprintf('?node=%s&sub=edit&%s=%s#%s', $this->request['node'], $this->id, $Service->get('id'), $this->request['tab']));
		}
	}
	
	public function search()
	{
		$this->index();
	
		/*
		// Title
		$this->title = _('Search');
		
		// Set search form
		$this->searchFormURL = 'ajax/service.search.php';
		
		// Hook
		$this->HookManager->processEvent('SERVICE_SEARCH');

		// Output
		$this->render();
		*/
	}
}

// Register page with FOGPageManager
$FOGPageManager->register(new ServiceConfigurationPage());