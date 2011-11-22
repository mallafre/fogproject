<?php

// Blackout - 3:46 PM 25/09/2011
if (IS_INCLUDED !== true) die(_('Unable to load system configuration information.'));

if ($_POST['add'])
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
			// Log History event
			$FOGCore->logHistory(sprintf('Host added: ID: %s, Name: %s', $Host->get('id'), $Host->get('name')));
		
			// Set session message
			$FOGCore->setMessage('Host added!');
		
			// Redirect to new entry
			$FOGCore->redirect("$_SERVER[PHP_SELF]?node=$node&sub=edit&hostid=" . $Host->get('id'));
		}
		else
		{
			// Database save failed
			throw new Exception('Database update failed');
		}
	}
	catch (Exception $e)
	{
		// Log History event
		$FOGCore->logHistory(sprintf('Host add failed: Name: %s, Error: %s', $_POST['name'], $e->getMessage()));
	
		// Set session message
		$FOGCore->setMessage($e->getMessage());
	}
}


?>
<h2><?php print _("Add new host definition"); ?></h2>
<form method="POST" action="<?php print "?node=$node&sub=$sub"; ?>">
	<input type="hidden" name="add" value="1" />
	<table cellpadding="0" cellspacing="0" border="0" width="100%">
		<tr><td width="35%"><?php print _("Host Name"); ?>:*</td><td><input type="text" name="host" value="<?php print $_POST['host']; ?>" /></td></tr>
		<tr><td><?php print _("Host IP"); ?>:</td><td><input type="text" name="ip" value="<?php print $_POST['ip']; ?>" /></td></tr>
		<tr><td><?php print _("Primary MAC"); ?>:*</td><td><input type="text" id='mac' name="mac" value="<?php print $_POST['mac']; ?>" /> &nbsp; <span id='priMaker'></span> </td></tr>
		<tr><td><?php print _("Host Description"); ?>:</td><td><textarea name="description" rows="5" cols="40"><?php print $_POST['description']; ?></textarea></td></tr>
		<tr><td><?php print _("Host Image"); ?>:</td><td><?php print $FOGCore->getClass('ImageManager')->buildSelectBox($_POST['image']);  ?></td></tr>
		<tr><td><?php print _("Host OS"); ?>:</td><td><?php print $FOGCore->getClass('OSManager')->buildSelectBox($_POST['os']); ?></td></tr>
		<tr><td><?php print _("Host Kernel"); ?>:</td><td><input type="text" name="kern" value="<?php print $_POST['kern']; ?>" /></td></tr>		
		<tr><td><?php print _("Host Kernel Arguments"); ?>:</td><td><input type="text" name="args" value="<?php print $_POST['args']; ?>" /></td></tr>	
		<tr><td><?php print _("Host Primary Disk"); ?>:</td><td><input type="text" name="dev" value="<?php print $_POST['dev']; ?>" /></td></tr>		
	</table>

	<br />
	<h2><?php print _("Active Directory"); ?></h2>		
	<table cellpadding="0" cellspacing="0" border="0" width="100%">
		<tr><td width="35%"><?php print _("Join Domain after image task"); ?>:</td><td><input id='adEnabled' type="checkbox" name="domain" value="on"<?php print ($_POST['domain'] == 'on' ? ' selected="selected"' : ''); ?> /></td></tr>
		<tr><td><?php print _("Domain name"); ?>:</td><td><input id="adDomain" type="text" name="domainname" value="<?php print $_POST['domainname']; ?>" /></td></tr>				
		<tr><td><?php print _("Organizational Unit"); ?>:</td><td><input id="adOU" type="text" name="ou" value="<?php print $_POST['ou']; ?>" /> <?php print _("(Blank for default)"); ?></td></tr>				
		<tr><td><?php print _("Domain Username"); ?>:</td><td><input id="adUsername" type="text" name="domainuser" value="<?php print $_POST['domainuser']; ?>" /></td></tr>						
		<tr><td><?php print _("Domain Password"); ?>:</td><td><input id="adPassword" type="text" name="domainpassword" value="<?php print $_POST['domainpassword']; ?>" /> <?php print _("(Must be encrypted)"); ?></td></tr>											
		<tr><td>&nbsp;</td><td><input type="submit" value="<?php print _("Add"); ?>" /></td></tr>				
	</table>	
</form>