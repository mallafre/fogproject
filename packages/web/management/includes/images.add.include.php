<?php

// Blackout - 5:14 PM 24/09/2011
if (IS_INCLUDED !== true) die(_('Unable to load system configuration information.'));

// Add
if ($_POST['add'])
{
	try
	{
		// Error checking
		if (empty($_POST['name']))
		{
			throw new Exception('An image name is required!');
		}
		if ($FOGCore->getClass('ImageManager')->exists($_POST['name']))
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
		if (empty($_POST['imagetype']) && $_POST['imagetype'] != '0')
		{
			throw new Exception('An image type is required!');
		}
	
		// Define new Image object with data provided
		$Image = new Image(array(
			'name'		=> $_POST['name'],
			'description'	=> $_POST['description'],
			'path'		=> $_POST['file'],
			'createdBy'	=> $currentUser->get('name'),
			'createdTime'	=> time(),
			'storageGroupID'=> $_POST['storagegroup'],
			'osID'		=> $_POST['os'],
			'imageTypeID'	=> ($_POST['imagetype'] ? $_POST['imagetype'] : 0)
		));
		
		// Save to database
		if ($Image->save())
		{
			// Log History event
			$FOGCore->logHistory(sprintf('Image added: ID: %s, Name: %s', $Image->get('id'), $Image->get('name')));
		
			// Set session message
			$FOGCore->setMessage('Image added!');
		
			// Redirect to new entry
			$FOGCore->redirect("$_SERVER[PHP_SELF]?node=$node&sub=edit&imageid=" . $Image->get('id'));
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
		$FOGCore->logHistory(sprintf('Image add failed: Name: %s, Error: %s', $_POST['name'], $e->getMessage()));
	
		// Set session message
		$FOGCore->setMessage($e->getMessage());
	}
}

?>
<h2><?php print _("Add new image definition"); ?></h2>
<form method="POST" action="<?php print "?node=$node&sub=$sub"; ?>">
<input type="hidden" name="add" value="1" />
<table cellpadding="0" cellspacing="0" border="0" width="100%">
	<tr><td><?php print _("Image Name"); ?>:</td><td><input type="text" name="name" id="iName" onblur="duplicateImageName();" value="<?php print $_POST['name']; ?>" /></td></tr>
	<tr><td><?php print _("Image Description"); ?>:</td><td><textarea name="description" rows="5" cols="65"><?php print $_POST['description']; ?></textarea></td></tr>
	<tr><td><?php print _("Storage Group"); ?>:</td><td><?php print $FOGCore->getClass('StorageGroupManager')->buildSelectBox($_POST['storagegroup']); ?></td></tr>
	<tr><td><?php print _("Operating System"); ?>:</td><td><?php print $FOGCore->getClass('OSManager')->buildSelectBox($_POST['os']); ?></td></tr>
	<tr><td><?php print _("Image Path"); ?>:</td><td>/images/<input type="text" name="file" id="iFile" value="<?php print $_POST['file']; ?>" /></td></tr>
	<tr><td><?php print _("Image Type"); ?>:</td><td><?php print $FOGCore->getClass('ImageTypeManager')->buildSelectBox($_POST['imagetype']); ?> <span class="icon icon-help" title="TODO!"></span></td></tr>				
	<tr><td colspan=2><center><input type="submit" value="<?php print _("Add"); ?>" /></center></td></tr>				
</table>
</form>