<?php

if (IS_INCLUDED !== true) die(_('Unable to load system configuration information.'));

if ($currentUser != null && $currentUser->isLoggedIn())
{
	// FOGSubMenu namespace
	$FOGSubMenu = new FOGSubMenu();
	
	// About Page
	if ($node == 'about')
	{
		$FOGSubMenu->addItems('about', array(	_('Version Info')	=> 'ver',
							_('License')		=> 'lic',
							_('Kernel Updates')	=> 'kernel',
							_('PXE Boot Menu')	=> 'pxemenu',
							_('Client Updater')	=> 'clientup',
							_('MAC Address List')	=> 'maclist',
							_('FOG Settings')	=> 'settings',
							_('Server Shell')	=> 'shell',
							_('Log Viewer')		=> 'log',
							_('FOG Sourceforge Page')	=> 'http://www.sf.net/projects/freeghost',
							_('FOG Home Page')	=> 'http://freeghost.sf.net/',
					));
	}

	// Group Management
	if ($node == "group")
	{
		$FOGSubMenu->addItems('group', array(	_('New Search')		=> 'search',
							_('List All Groups')	=> 'list',
					));
		if ($groupid)
		{
			// Group Management: Edit
			$Group = new Group($groupid);
			
			if (!preg_match('#indexold#', $_SERVER['PHP_SELF']))
			{
				$FOGSubMenu->addItems('group', array(	_('General')		=> "$_SERVER[PHP_SELF]?node=$node&sub=edit&groupid=$groupid#group-general",
									_('Basic Tasks')	=> "$_SERVER[PHP_SELF]?node=$node&sub=edit&groupid=$groupid#group-tasks",
									_('Membership')		=> "$_SERVER[PHP_SELF]?node=$node&sub=edit&groupid=$groupid#group-membership",
									_('Image Association')	=> "$_SERVER[PHP_SELF]?node=$node&sub=edit&groupid=$groupid#group-image",
									_('OS Association')	=> "$_SERVER[PHP_SELF]?node=$node&sub=edit&groupid=$groupid#group-os",
									_('Add Snap-ins')	=> "$_SERVER[PHP_SELF]?node=$node&sub=edit&groupid=$groupid#group-snap-add",
									_('Remove Snap-ins')	=> "$_SERVER[PHP_SELF]?node=$node&sub=edit&groupid=$groupid#group-snap-delete",
									_('Service Settings')	=> "$_SERVER[PHP_SELF]?node=$node&sub=edit&groupid=$groupid#group-service",
									_('Active Directory')	=> "$_SERVER[PHP_SELF]?node=$node&sub=edit&groupid=$groupid#group-active-directory",
									_('Printers')		=> "$_SERVER[PHP_SELF]?node=$node&sub=edit&groupid=$groupid#group-printers",
									_('Delete')		=> "delete",
							), 'groupid', 'Group Menu');
			}
			else
			{
				$FOGSubMenu->addItems('group', array(	_('General')		=> "gen",
									_('Basic Tasks')	=> "tasks",
									_('Membership')		=> "member",
									_('Image Association')	=> "image",
									_('OS Association')	=> "os",
									_('Add Snap-ins')	=> "snapadd",
									_('Remove Snap-ins')	=> "snapdel",
									_('Service Settings')	=> "service",
									_('Active Directory')	=> "ad",
									_('Printers')		=> "$_SERVER[PHP_SELF]?node=$node&sub=printers&groupid=$groupid",
									_('Delete')		=> "del",
							), 'groupid', 'Group Menu');
			}
			
			// Group Management: Notes
			$FOGSubMenu->addNotes('group', array(	_('Group')		=> $Group->get('name'),
								_('Members')		=> count($Group->get('hosts')),
						), 'groupid');
		}
	}
	
	// Host Management
	if ($node == "host")
	{
		if (!preg_match('#indexold#', $_SERVER['PHP_SELF']))
		{
			$FOGSubMenu->addItems('host', array(	_('New Search')		=> 'search',
								_('List All Hosts')	=> 'list',
								_('Add New Host')	=> 'add',
								_('Export Hosts')	=> 'export',
								_('Import Hosts')	=> 'import',
						));
		}
		else
		{
			$FOGSubMenu->addItems('host', array(	_('New Search')		=> 'search',
								_('List All Hosts')	=> 'list',
								_('Add New Host')	=> 'add',
								_('Export Hosts')	=> 'export',
								_('Import Hosts')	=> 'upload',
						));
		}
			
		if ($id)
		{
			$Host = new Host($id);
			$hostname = ($Host->isValid() ? $Host->get('name') : '-');
		
			// Host Management: Edit
			if (!preg_match('#indexold#', $_SERVER['PHP_SELF']))
			{
				// NEW menu items - tab based, single page
				$FOGSubMenu->addItems('host', array(	_('General')		=> "$_SERVER[PHP_SELF]?node=$node&sub=edit&id=$id#host-general",
									_('Basic Tasks')	=> "$_SERVER[PHP_SELF]?node=$node&sub=edit&id=$id#host-tasks",
									_('Active Directory')	=> "$_SERVER[PHP_SELF]?node=$node&sub=edit&id=$id#host-active-directory",
									_('Printers')		=> "$_SERVER[PHP_SELF]?node=$node&sub=edit&id=$id#host-printers",
									_('Snap-ins')		=> "$_SERVER[PHP_SELF]?node=$node&sub=edit&id=$id#host-snapins",
									_('Service Settings')	=> "$_SERVER[PHP_SELF]?node=$node&sub=edit&id=$id#host-service",
									_('Hardware')		=> "$_SERVER[PHP_SELF]?node=$node&sub=edit&id=$id#host-hardware-inventory",
									_('Virus History')	=> "$_SERVER[PHP_SELF]?node=$node&sub=edit&id=$id#host-virus-history",
									_('Login History')	=> "$_SERVER[PHP_SELF]?node=$node&sub=edit&id=$id#host-login-history",
									_('Delete')		=> "$_SERVER[PHP_SELF]?node=$node&sub=delete&id=$id",
							), 'id', $hostname);
			}
			else
			{
				// LEGACY menu items - old includes style
				$FOGSubMenu->addItems('host', array(	_('General')		=> "gen",
									_('Basic Tasks')	=> "tasks",
									_('Active Directory')	=> "ad",
									_('Printers')		=> "$_SERVER[PHP_SELF]?node=$node&sub=printers&id=$id",
									_('Snap-ins')		=> "snapins",
									_('Service Settings')	=> "service",
									_('Hardware')		=> "$_SERVER[PHP_SELF]?node=$node&sub=inv&id=$id",
									_('Virus History')	=> "virus",
									_('Login History')	=> "$_SERVER[PHP_SELF]?node=$node&sub=loginhist&id=$id",
									_('Delete')		=> "$_SERVER[PHP_SELF]?node=$node&sub=delete&id=$id",
							), 'id', $hostname);
			}

			// Host Management: Notes
			$FOGSubMenu->addNotes('host', array(	_('Host')	=> stripslashes($hostname),
								_('MAC')	=> stripslashes(($Host ? $Host->get('mac') : '')),
								_('Image')	=> stripslashes($Host->getImage()->get('name')),
								_('O/S')	=> stripslashes(($Host ? $Host->getOS()->get('name') : '')),
						), 'id');

			// Primary Group
			$group = $FOGCore->getClass('GroupManager')->getGroupsWithMember($id);
			//var_dump($group);exit;
			if ($group[0])
			{
				$FOGSubMenu->addNotes('host', array(_("Primary Group")	=> $group[0]->get('name')), 'id');
			}
		}
	}
	
	// Image Management
	if ($node == "images")
	{
		$FOGSubMenu->addItems('images', array(	_('New Search')		=> 'search',
							_('List All Images')	=> 'list',
							_('New Image')		=> 'add',
					));

		if ($imageid)
		{
			// Image Management: Edit
			if (!preg_match('#indexold#', $_SERVER['PHP_SELF']))
			{
				$FOGSubMenu->addItems('images', array(	_('General')		=> "$_SERVER[PHP_SELF]?node=$node&sub=list&imageid=$imageid",
									_('Delete')		=> "$_SERVER[PHP_SELF]?node=$node&sub=delete&imageid=$imageid",
							), 'imageid', 'Image Menu');
			}
			else
			{
				$FOGSubMenu->addItems('images', array(	_('General')		=> "list",
									_('Delete')		=> "delete",
							), 'imageid', 'Image Menu');
			}
						
			// Image Management: Notes
			$FOGSubMenu->addNotes('images',  create_function('', '	$allImages = mysql_query("select * from images where imageID = \'' . $imageid . '\'");
										while ($image = mysql_fetch_array($allImages))
										{
											$x[("Image Name")] = $image["imageName"];
										}
										return $x;')
						, 'imageid');
		}
	}
	
	
	// Printer Management
	if ($node == "print")
	{
		$FOGSubMenu->addItems('print', array(	_('New Search')		=> 'search',
							_('List All Printers')	=> 'list',
							_('Add New Printer')	=> 'add',
					));
		
		if ($id)
		{
			// Printer Management
			$FOGSubMenu->addItems('print', array(	_('General')		=> "$_SERVER[PHP_SELF]?node=$node&sub=$sub&id=$id",
								_('Delete')		=> "$_SERVER[PHP_SELF]?node=$node&sub=delete&id=$id",
						), 'id', 'Printer Menu');

			// Printer Note
			$res = mysql_query( "select * from printers where pID = '$id'", $conn ) or die( mysql_error() );
			if ( $ar = mysql_fetch_array( $res ) )
			{
				$FOGSubMenu->addNotes('print', array('Model' => stripslashes($ar["pModel"]), 'Alias' => stripslashes($ar["pAlias"])));
			}
		}

	}
	
	// Reports Management
	if ($node == "report")
	{
		$FOGSubMenu->addItems('report', array(	_('Home')	=> 'home'));
		
		// Dynamically read php files and push into side menu
		$dh = opendir( $GLOBALS['FOGCore']->getSetting( "FOG_REPORT_DIR" ) );
		$included_in_fog = array("Equipment Loan.php" => _("Equipment Loan"), "Host List.php" => _("Host List"), "Imaging Log.php" => _("Imaging Log"), "Inventory.php" => _("Inventory"), "Snapin Log.php" => _("Snapin Log"), "User Login Hist.php" => _("User Login Hist"), "Virus History.php" => _("Virus History"));
		if ( $dh != null )
		{
			while ( ! (($f = readdir( $dh )) === FALSE) )
			{
				if ( is_file( $GLOBALS['FOGCore']->getSetting( "FOG_REPORT_DIR" ) . $f ) )
				{	
					if ( endswith( $f, ".php" ) )
					{
						$FOGSubMenu->addItems('report', array(	($included_in_fog[$f] ? $included_in_fog[$f] : substr( $f, 0, strlen( $f ) -4 )) => "$_SERVER[PHP_SELF]?node=$node&sub=file&f=" . base64_encode($f)));
					}
				}
			}
		}
		
		$FOGSubMenu->addItems('report', array(	_('Upload a Report')	=> 'upload'));
	}
	
	// Service Management
	if ($node == "service")
	{
		$FOGSubMenu->addItems('service', array(	_('Auto Log Out')	=> 'alo',
							_('Client Updater')	=> 'clientupdater',
							_('Directory Cleaner')	=> 'dircleaner',
							_('Display Manager')	=> 'displaymanager',
							_('Green FOG')		=> 'greenfog',
							_('Hostname Changer')	=> 'hostnamechanger',
							_('Host Registration')	=> 'hostregister',
							_('Printer Manager')	=> 'printermanager',
							_('Snapin Client')	=> 'snapin',
							_('Task Reboot')	=> 'taskreboot',
							_('User Cleanup')	=> 'usercleanup',
							_('User Tracker')	=> 'usertracker',
					));
	}
	
	// Snapin Management
	if ($node == "snap")
	{
		$FOGSubMenu->addItems('snap', array(	_('New Search')		=> 'search',
							_('List All Snap-ins')	=> 'list',
							_('New Snapin')		=> 'add',
					));
		
		if ($snapinid)
		{
			// Snapin Management: Per Snapin
			$FOGSubMenu->addItems('snap', array(	_('General')		=> "$_SERVER[PHP_SELF]?node=$node&sub=edit&snapinid=$snapinid&tab=gen",
								_('Delete')		=> "$_SERVER[PHP_SELF]?node=$node&sub=edit&snapinid=$snapinid&tab=delete",
						), 'snapinid', 'Snapin Menu');

			// Snapin Management: Notes
			$res = mysql_query( "select * from snapins where sID = '$snapinid'", $conn ) or die( mysql_error() );
			if ( $ar = mysql_fetch_array( $res ) )
			{
				$FOGSubMenu->addNotes('snap', array('Name' => stripslashes($ar["sName"])));
			}
		}
	}
	
	// Storage Management
	if ($node == "storage")
	{
		if (!preg_match('#indexold#', $_SERVER['PHP_SELF']))
		{
			$FOGSubMenu->addItems('storage', array(	_('All Storage Nodes')		=> "$_SERVER[PHP_SELF]?node=$node",
								_('Add Storage Nodes')		=> "$_SERVER[PHP_SELF]?node=$node&sub=add-storage-node",
								_('All Storage Groups')		=> "$_SERVER[PHP_SELF]?node=$node&sub=all-storage-groups",
								_('Add Storage Group')		=> "$_SERVER[PHP_SELF]?node=$node&sub=add-storage-group",
						));
		}
		else
		{
			$FOGSubMenu->addItems('storage', array(	_('All Storage Groups')		=> 'groups',
								_('Add Storage Group')		=> 'addgroup',
								_('All Storage Nodes')		=> 'nodes',
								_('Add Storage Nodes')		=> 'addnode',
						));
		}
			
		if ($storagegroupid)
		{
			$FOGSubMenu->addItems('storage', array(	_('General')			=> 'gen',
								_('Delete')			=> 'delete'
						), 'storagegroupid', _('Storage Group Menu'));
			
			$res = mysql_query( "select * from nfsGroups where ngID = '$storagegroupid'", $conn ) or die( mysql_error() );
			if ( $ar = mysql_fetch_array( $res ) )
			{
				$FOGSubMenu->addNotes('storage', array(_("Group Name") => stripslashes($ar["ngName"])));
			}
		}
		
		if ($storagenodeid)
		{
			$FOGSubMenu->addItems('storage', array(	_('General')			=> "$_SERVER[PHP_SELF]?node=$node&sub=editnode&storagenodeid=$storagenodeid&tab=gen",
								_('Delete')			=> "$_SERVER[PHP_SELF]?node=$node&sub=editnode&storagenodeid=$storagenodeid&tab=delete"
						), 'storagenodeid', _("Storage Node Menu"));

			$res = mysql_query( "select * from nfsGroupMembers where ngmID = '$storagenodeid'", $conn ) or die( mysql_error() );
			if ( $ar = mysql_fetch_array( $res ) )
			{
				$FOGSubMenu->addNotes('storage', array(_("Node Name") => stripslashes($ar["ngmMemberName"])));
			}
		}
	}
	
	// Task Management
	if ($node == "tasks")
	{
		$FOGSubMenu->addItems('tasks', array(	_('New Search')			=> 'search',
							_('List All Groups')		=> 'listgroups',
							_('List All Hosts')		=> 'listhosts',
							_('Active Tasks')		=> 'active',
							_('Scheduled Tasks')		=> 'sched',
							_('Active Multicast Tasks')	=> 'activemc',
							_('Active Snap-ins')		=> 'activesnapins',
					));
	}
	
	// User Management
	if ($node == "users")
	{
		$FOGSubMenu->addItems('users', array(	_('New Search')			=> 'search',
							_('List All Users')		=> 'list',
							_('New User')			=> 'add',
					));
		
		if ($id)
		{
			// User Management: Per User
			$FOGSubMenu->addItems('users', array(	_('General')			=> "$_SERVER[PHP_SELF]?node=$node&sub=edit&id=$id",
								_('Delete')			=> "$_SERVER[PHP_SELF]?node=$node&sub=delete&id=$id",
						), 'id', 'User Menu');
			
			// User Management: Notes
			$userMan = $FOGCore->getClass('UserManager');
			$user = new User($id);
			
			if ( $user != null )
			{
				$FOGSubMenu->addNotes('users', array(_('Username') => $user->get('name')));
			}
		}
	}
	
	// Plugins
	if ($node == "plugin")
	{
		$FOGSubMenu->addItems('plugin', array(	_('Home')		=> 'home',
							_('Installed Plugin')	=> 'installed',
							_('Activate Plugin')	=> 'activate',
					));
	}
	
	// HWInfo - linked to from Dashboard
	if ($node == "hwinfo")
	{
		$FOGSubMenu->addItems('hwinfo', array(	_('Home')		=> 'home'));
	}
	
	if ($node == "help")
	{
		$FOGSubMenu->addItems('help', array(	_('Home')		=> 'home'));
	}
	
	// Hook
	$HookManager->processEvent('SubMenuData', array('FOGSubMenu' => &$FOGSubMenu));
	
	// DEBUG
	//print "<pre>";
	//print_r(htmlspecialchars($FOGSubMenu->get($node)));
	
	print $FOGSubMenu->get($node);
}