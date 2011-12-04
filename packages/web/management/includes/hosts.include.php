<?php

if (IS_INCLUDED !== true) die(_('Unable to load system configuration information.'));

$hostMan = $FOGCore->getClass('HostManager');
$groupMan = $FOGCore->getClass('GroupManager');

if ($currentUser != null && $currentUser->isLoggedIn())
{
	if ( $_GET['rmhostid'] != null && is_numeric( $_GET['rmhostid'] ) )
	{	
		// TODO: Move to OO
		removeAllTasksForHostID( $conn, $rmid );	
		try
		{
			if ($hostMan->deleteHost($_GET['rmhostid']))
			{
				msgBox(_('Host removed!'));
				lg(_('Removed host id #') . $rmid);
			}
			else
			{
				msgBox(_('Failed to remove host!'));
				lg(_('Failed to remove host') );
			}
		}
		catch (Exception $e)
		{
			msgBox(_('Failed to remove host!') . " " . $e->getMessage());
			lg(_('Failed to remove host') . ', Error: ' . $e->getMessage() );
		}
	}

	if ($_POST['frmSub'] == '1')
	{
		// Blackout - 11:04 AM 15/11/2011
		// TODO: Finish
		/*
		try
		{
			// Error checking
			if ($_POST['grp'] == '-1')
			{
				throw new Exception('Group is required');
			}
			if ($_POST['newgroup'] == null)
			{
				throw new Exception('New Group is required');
			}
		
			// Define new object with data provided
			$Group = new Group();
			$Group->set('name', $_POST['newgroup']);
			
			// Save to database
			if ($Group->save())
			{
				// Log History event
				$FOGCore->logHistory(sprintf('Group added: ID: %s, Name: %s', $Group->get('id'), $Group->get('name')));
			
				// Set session message
				$FOGCore->setMessage('Group added!');
			
				// Redirect to new entry
				$FOGCore->redirect("$_SERVER[PHP_SELF]?node=$node&sub=edit&groupid=" . $Group->get('id'));
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
			$FOGCore->logHistory(sprintf('Group add failed: Name: %s, Error: %s', $_POST['name'], $e->getMessage()));
		
			// Set session message
			$FOGCore->setMessage($e->getMessage());
		}
		*/
	
		if ($_POST['grp'] != '-1' || $_POST['newgroup'] != null)
		{
			$blGo = false;
			$grp = '';
			try
			{
				if ($_POST['newgroup'] != null)
				{
					if ($groupMan->createGroup($_POST['newgroup'], $currentUser) >= 0)
					{
						$blGo = true;
						$grp = $_POST['newgroup'];
					}
					else
					{
						msgBox(_('Unable to create new group, does it exist already?'));
					}
				}
				else
				{
					$blGo = true;
					$grp = $_POST['grp'];
				}
			
				$group = $groupMan->getGroupByName($grp);  
				if ($blGo && $group != null && $group->getID() != -1)
				{
					$selectedItems = array();
					foreach ($_POST as $key => $value)
					{
				   		if ( substr( trim($key), 0, 3 ) == "HID" && $value == "on" )
				   			$selectedItems[] = substr(trim($key),3 );
				   	}
			
					if ($selectedItems != null)
					{
						$error = false;
						foreach ($selectedItems as $item)
						{
							if (!$groupMan->addHostToGroup($group->getID(), $item) )
							{
								msgBox(_('Error updating') . " $grp $grpID");
								$error = true;
								break;
							}
						}
					
						if ( ! $error )
							msgBox("$grp " . _('was updated/created!'));
					}
				}
			}
			catch (Exception $e)
			{
				msgBox(_('Failed to modify groups!') . " " . $e->getMessage());
				lg(_('Failed to modify groups') . ', Error: ' . $e->getMessage() );
			}
		}
		else
		{
			msgBox(_('Please select or create a new group!'));
		}
	}
	
	if ($sub == 'add')
	{
		require_once('./includes/hosts.add.include.php');
	}
	else if ($sub == 'newsearch')
	{
		require_once('./includes/hosts.search.include.php');
	}
	else if ($sub == 'list')
	{
		require_once('./includes/hosts.list.include.php');
	}
	else if ($sub == 'edit')
	{
		require_once('./includes/hosts.edit.include.php');
	}
	else if ($sub == 'upload')
	{
		require_once('./includes/hosts.upload.include.php');
	}
	else if ($sub == 'loginhist')
	{
		require_once('./includes/hosts.login.include.php');
	}
	else if ($sub == 'printers')
	{
		require_once('./includes/hosts.printers.include.php');
	}
	else if ($sub == 'inv')
	{
		require_once('./includes/hosts.inventory.include.php');
	}
	else
	{
		if ($FOGCore->getSetting('FOG_VIEW_DEFAULT_SCREEN' ) == 'LIST')
		{
			require_once('./includes/hosts.list.include.php');
		}
		else
		{
			require_once('./includes/hosts.search.include.php');
		}
	}
}