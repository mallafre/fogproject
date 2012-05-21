<?php

// Blackout - 11:15 AM 1/10/2011
class Host extends FOGController
{
	// Table
	public $databaseTable = 'hosts';
	
	// Name -> Database field name
	public $databaseFields = array(
		'id'		=> 'hostID',
		'name'		=> 'hostName',
		'description'	=> 'hostDesc',
		'ip'		=> 'hostIP',
		'imageID'	=> 'hostImage',
		'building'	=> 'hostBuilding',
		'createdTime'	=> 'hostCreateDate',
		'createdBy'	=> 'hostCreateBy',
		'mac'		=> 'hostMAC',
		'useAD'		=> 'hostUseAD',
		'ADDomain'	=> 'hostADDomain',
		'ADOU'		=> 'hostADOU',
		'ADUser'	=> 'hostADUser',
		'ADPass'	=> 'hostADPass',
		'printerLevel'	=> 'hostPrinterLevel',
		'kernel'	=> 'hostKernel',
		'kernelArgs'	=> 'hostKernelArgs',
		'kernelDevice'	=> 'hostDevice',
		// Move to Image Object
		'osID'		=> 'hostOS'
	);
	
	// Allow setting / getting of these additional fields
	public $additionalFields = array(
		'additionalMACs',
		'groups',
		'primayGroup',
		'primayGroupID',
		'optimalStorageNode',
		'printers',
		'snapins',
		'modules'
	);
	
	// Required database fields
	public $databaseFieldsRequired = array(
		'id',
		'name',
		'mac'
	);
	
	// Database field to Class relationships
	public $databaseFieldClassRelationships = array(
		'osID'		=> 'OS',
		'imageID'	=> 'Image'
	);
	
	// Custom functons
	public function isHostnameSafe()
	{
		return (strlen($this->get('name')) > 0 && strlen($this->get('name')) <= 15 && preg_replace('#[0-9a-zA-Z_\-]#', '', $this->get('name')) == '');
	}
	
	public function getImage()
	{
		return new Image($this->get('imageID'));
	}
	
	public function getOS()
	{
		return $this->getImage()->getOS();
	}
	
	public function getMACAddress()
	{
		return $this->get('mac');
	}
	
	public function getActiveTask()
	{
		// Find Active Task - there should only ever be one, but sort by latest just in case
		$Task = current($this->FOGCore->getClass('TaskManager')->find(array('stateID' => array(1, 2, 3), 'hostID' => $this->get('id')), 'AND', 'id', 'DESC'));
		
		// Failed to find an Active Task
		if (!$Task)
		{
			throw new Exception(sprintf('%s: %s (%s)', _('No Active Task found for Host'), $this->get('name'), $this->get('mac')));
		}
		
		return $Task;
	}
	
	private function loadPrinters()
	{
		if (!$this->isLoaded('printers'))
		{
			if ($this->get('id'))
			{
				$this->DB->query("SELECT printers.* FROM printerAssoc inner join printers on ( printerAssoc.paPrinterID = printers.pID ) WHERE printerAssoc.paHostID = '%s' ORDER BY printers.pAlias", $this->get('id'));
				
				while ($printer = $this->DB->fetch()->get())
				{
					$printers[] = new Printer($printer);
				}
			}
			
			$this->set('printers', (array)$printers);
		}
	}
	
	private function loadSnapins()
	{
		if (!$this->isLoaded('snapins'))
		{
			if ($this->get('id'))
			{
				$this->DB->query("SELECT snapins.* FROM snapinAssoc INNER JOIN snapins ON ( snapinAssoc.saSnapinID = snapins.sID ) WHERE snapinAssoc.saHostID = '%s' ORDER BY snapins.sName", $this->get('id'));
				
				while ($snapin = $this->DB->fetch()->get())
				{
					$snapins[] = new Snapin($snapin);
				}
			}
			
			$this->set('snapins', (array)$snapins);
		}
	}
	
	// Overrides
	public function get($key = '')
	{
		if ($this->key($key) == 'printers')
		{
			// Printers
			$this->loadPrinters();
		}
		if ($this->key($key) == 'snapins')
		{
			// Snapins
			$this->loadSnapins();
		}
		else if ($this->key($key) == 'optimalStorageNode' && !$this->isLoaded('optimalStorageNode'))
		{
			// Get Optimal Storage Node once - we must store this as we dont want different Storage Node's coming back after each call
			$this->set($key, $this->getImage()->getStorageGroup()->getOptimalStorageNode());
		}
		
		return parent::get($key);
	}
	
	public function set($key, $value)
	{
		// MAC Address
		if ($this->key($key) == 'mac' && !($value instanceof MACAddress))
		{
			$value = new MACAddress($value);
		}
		
		// Additional MAC Addresses
		else if ($this->key($key) == 'additionalMACs')
		{
			foreach ((array)$value AS $MAC)
			{
				$newValue[] = ($MAC instanceof MACAddress ? $MAC : new MACAddress($MAC));
			}
			
			$value = (array)$newValue;
		}
		
		// Printers
		else if ($this->key($key) == 'printers')
		{
			$this->loadPrinters();
		
			foreach ((array)$value AS $printer)
			{
				$newValue[] = ($printer instanceof Printer ? $printer : new Printer($printer));
			}
			
			$value = (array)$newValue;
		}
		
		// Snapins
		else if ($this->key($key) == 'snapins')
		{
			$this->loadSnapins();
		
			foreach ((array)$value AS $snapin)
			{
				$newValue[] = ($snapin instanceof Snapin ? $snapin : new Snapin($snapin));
			}
			
			$value = (array)$newValue;
		}
		
		// Set
		return parent::set($key, $value);
	}
	
	public function add($key, $value)
	{
		// Additional MAC Addresses
		if ($this->key($key) == 'additionalMACs' && !($value instanceof MACAddress))
		{
			$value = new MACAddress($value);
		}
		
		// Printers
		else if ($this->key($key) == 'printers' && !($value instanceof Printer))
		{
			$this->loadPrinters();
			
			$value = new Printer($value);
		}
		
		// Snapins
		else if ($this->key($key) == 'snapins' && !($value instanceof Snapin))
		{
			$this->loadSnapins();
			
			$value = new Snapin($value);
		}
		
		// Add
		return parent::add($key, $value);
	}
	
	public function remove($key, $object)
	{
		// Printers
		if ($this->key($key) == 'printers')
		{
			$this->loadPrinters();
		}
		
		// Snapins
		else if ($this->key($key) == 'snapins')
		{
			$this->loadSnapins();
		}
		
		// Remove
		return parent::remove($key, $object);
	}
	
	public function save()
	{
		// Save
		parent::save();

		// Additional MAC Addresses
		// Remove existing Additional MAC Addresses
		$this->DB->query("DELETE FROM `hostMAC` WHERE `hmHostID`='%s'", array($this->get('id')));
		
		// Add new Additional MAC Addresses
		foreach ((array)$this->get('additionalMACs') AS $MAC)
		{
			if (($MAC instanceof MACAddress) && $MAC->isValid())
			{
				$this->DB->query("INSERT INTO `hostMAC` (`hmHostID`, `hmMAC`) VALUES('%s', '%s')", array($this->get('id'), $MAC));
			}
		}
		
		// Printers
		if ($this->isLoaded('printers'))
		{
			// Remove old rows
			$this->DB->query("DELETE FROM `printerAssoc` WHERE `paHostID` = '%s'", array($this->get('id')));
			
			// Create assoc
			foreach ($this->getPrinters() AS $i => $Printer)
			{
				if (($Printer instanceof Printer) && $Printer->isValid())
				{
					$this->DB->query("INSERT INTO `printerAssoc` (paHostID, paPrinterID, paIsDefault) VALUES ('%s', '%s', '%s')", array($this->get('id'), $Printer->get('id'), ($i === 0 ? '1' : '0')));
				}
			}
		}
		
		// Snapins
		if ($this->isLoaded('snapins'))
		{
			// Remove old rows
			$this->DB->query("DELETE FROM `snapinAssoc` WHERE `saHostID` = '%s'", array($this->get('id')));
			
			// Create assoc
			foreach ($this->getSnapins() AS $i => $Snapin)
			{
				if (($Snapin instanceof Snapin) && $Snapin->isValid())
				{
					$this->DB->query("INSERT INTO `snapinAssoc` (saHostID, saSnapinID) VALUES ('%s', '%s')", array($this->get('id'), $Snapin->get('id')));
				}
			}
		}
		
		// Return
		return $this;
	}
	
	public function load($field = 'id')
	{
		// Load
		parent::load($field);

		// Load 'additionalMACs'
		$this->DB->query("SELECT * FROM `hostMAC` WHERE `hmHostID`='%s'", array($this->get('id')));
		while ($MAC = $this->DB->fetch()->get('hmMAC'))
		{
			$this->add('additionalMACs', $MAC);
		}
		
		// Return
		return $this;
	}
	
	public function isValid()
	{
		return (($this->get('id') != '' || $this->get('name') != '') && $this->getMACAddress() != '' ? true : false);
	}
	
	// Custom functions
	public function getActiveTaskCount()
	{
		return $this->FOGCore->getClass('TaskManager')->count(array('stateID' => array(1, 2, 3), 'hostID' => $this->get('id')));
	}
	
	public function isValidToImage()
	{
		$Image = $this->getImage();
		$OS = $this->getOS();
		$StorageGroup = $Image->getStorageGroup();
		$StorageNode = $StorageGroup->getStorageNode();
		
		return ($Image->isValid() && $OS->isValid() && $StorageGroup->isValid() && $StorageNode->isValid() ? true : false);
		
		// TODO: Use this version when class caching has been finialized
		//return ($this->getImage()->isValid() && $this->getImage()->getOS()->isValid() && $this->getImage()->getStorageGroup()->isValid() && $this->getImage()->getStorageGroup()->getStorageNode*(->isValid() ? true : false);
	}
	
	public function getOptimalStorageNode()
	{
		return $this->get('optimalStorageNode');
	}

	// Should be called: createDeployTask
	function createImagePackage($taskTypeID, $taskName = '', $shutdown = false, $debug = false, $deploySnapins = true, $isGroupTask = false)
	{
		try
		{
			// Error checking
			if ($this->getActiveTaskCount())
			{
				throw new Exception(_('Host is already a member of a active task'));
			}
			if (!$this->isValid())
			{
				throw new Exception(_('Host is not valid'));
			}
			
			// TaskType: Variables
			$TaskType = new TaskType($taskTypeID);
			$isUpload = $TaskType->isUpload();
			
			// TaskType: Error checking
			if (!$TaskType->isValid())
			{
				throw new Exception(_('Task Type is not valid'));
			}
			
			// Image: Variables
			$Image = $this->getImage();
			
			// Image: Error checking
			if (!$Image->isValid())
			{
				throw new Exception(_('Image is not valid'));
			}
			if (!$Image->getStorageGroup()->isValid())
			{
				throw new Exception(_('The Image\'s associated Storage Group is not valid'));
			}
			
			// Storage Node: Variables
			// NOTE: Master storage node node for Uploads or, Optimal storage node for Deploy
			$StorageNode = ($isUpload ? $Image->getStorageGroup()->getMasterStorageNode() : $this->getOptimalStorageNode());
			
			// Storage Node: Error Checking
			if (!$StorageNode || !($StorageNode instanceof StorageNode))
			{
				throw new Exception( _('Could not find a Storage Node. Is there one enabled within this Storage Group?') );
			}
			if (!$StorageNode->isValid())
			{
				throw new Exception(_('The Storage Group\'s associated Storage Node is not valid'));
			}
			
			// Variables
			$mac = $this->getMACAddress()->getMACWithColon();
			$localPXEFile = $this->FOGCore->makeTempFilePath();
			$remotePXEFile = rtrim($this->FOGCore->getSetting('FOG_TFTP_PXE_CONFIG_DIR'), '/') . '/' . $this->getMACAddress()->getMACPXEPrefix();

			// Kernel Arguments: Define possible kernel arguments
			// NOTE: slightly more manageable but needs more love
			$kernelArgsArray = array(
				// FOG base
				'initrd=' . $this->FOGCore->getSetting('FOG_PXE_BOOT_IMAGE'),
				'root=/dev/ram0',
				'rw',
				'ramdisk_size=' . $this->FOGCore->getSetting('FOG_KERNEL_RAMDISK_SIZE'),
				'ip=dhcp',
				'dns=' . $this->FOGCore->getSetting('FOG_PXE_IMAGE_DNSADDRESS'),
				'mac=' . $mac,
				'ftp=' . $this->FOGCore->resolveHostname($this->FOGCore->getSetting('FOG_TFTP_HOST')),
				'storage=' . sprintf('%s:/%s/%s', trim($StorageNode->get('ip')), trim($StorageNode->get('path'), '/'), ($isUpload ? 'dev/' : '')),
				'storageip=' . $StorageNode->get('ip'),
				'web=' . $this->FOGCore->resolveHostname($this->FOGCore->getSetting('FOG_WEB_HOST')) . '/' . ltrim($this->FOGCore->getSetting('FOG_WEB_ROOT'), '/'),
				'osid=' . $Image->get('osID'),
				'loglevel=4',
				'consoleblank=0',
				'irqpoll',
				'chkdsk=' . ($this->FOGCore->getSetting('FOG_DISABLE_CHKDSK') == '1' ? '0' : '1'),
				'img=' . $Image->get('path'),
				'imgType=' . $Image->getImageType()->get('type'),
				'imgid=' . $Image->get('id'),
				
				// Dynamic - if 'active' is true, then 'value' is used
				array(	'value'		=> 'shutdown=1',
					'active'	=> $shutdown
				),
				array(	'value'		=> 'keymap=' . $this->FOGCore->getSetting('FOG_KEYMAP'),
					'active'	=> $this->FOGCore->getSetting('FOG_KEYMAP')
				),
				array(	'value'		=> 'fdrive=' . $this->get('kernelDevice'),
					'active'	=> $this->get('kernelDevice')
				),
				array(	'value'		=> 'hostname=' . $this->get('name'),
					'active'	=> $this->FOGCore->getSetting('FOG_CHANGE_HOSTNAME_EARLY')
				),
				
				// Upload
				// TODO: Move to database when FOG Variable templating is implemented
				array(	'value'		=> 'pct=' . (is_numeric($GLOBALS['FOGCore']->getSetting('FOG_UPLOADRESIZEPCT')) && $GLOBALS['FOGCore']->getSetting('FOG_UPLOADRESIZEPCT') >= 5 && $GLOBALS['FOGCore']->getSetting('FOG_UPLOADRESIZEPCT') < 100 ? $this->FOGCore->getSetting('FOG_UPLOADRESIZEPCT') : '5'),
					'active'	=> $isUpload
				),
				array(	'value'		=> 'ignorepg=' . ($GLOBALS['FOGCore']->getSetting( "FOG_UPLOADIGNOREPAGEHIBER" ) ? '1' : '0'),
					'active'	=> $isUpload
				),
				
				// Multicast
				array(	'value'		=> 'port=666',
					'active'	=> $TaskType->isMulticast()
				),
				
				// Task Type
				$TaskType->get('kernelArgs'),
				
				// Global
				$this->FOGCore->getSetting('FOG_KERNEL_ARGS'),
				
				// Host
				$this->get('kernelArgs'),
				
				// OLD DEPLOY
				//append initrd=" . $GLOBALS['FOGCore']->getSetting( "FOG_PXE_BOOT_IMAGE" ) . "  root=/dev/ram0 rw ramdisk_size=" . $GLOBALS['FOGCore']->getSetting( "FOG_KERNEL_RAMDISK_SIZE" ) . " ip=dhcp dns=" . $GLOBALS['FOGCore']->getSetting( "FOG_PXE_IMAGE_DNSADDRESS" ) . " type=down img=" . $Image->get('path') . " mac=" . $member->getMACColon() . " ftp=" . sloppyNameLookup($GLOBALS['FOGCore']->getSetting( "FOG_TFTP_HOST" )) . " storage=" . $member->getNFSServer() . ":" . $member->getNFSRoot() . " web=" . sloppyNameLookup($GLOBALS['FOGCore']->getSetting( "FOG_WEB_HOST")) . $GLOBALS['FOGCore']->getSetting( "FOG_WEB_ROOT" ) . " osid=" . $member->getOSID() . " $mode $imgType $keymapapp shutdown=$shutdown loglevel=4 consoleblank=0 " . $GLOBALS['FOGCore']->getSetting( "FOG_KERNEL_ARGS" ) . " " . $member->getKernelArgs() . " " . $otherargs; 
				// OLD UPLOAD
				//append initrd=" . $GLOBALS['FOGCore']->getSetting( "FOG_PXE_BOOT_IMAGE" ) . "  root=/dev/ram0 rw ramdisk_size=" . $GLOBALS['FOGCore']->getSetting( "FOG_KERNEL_RAMDISK_SIZE" ) . " ip=dhcp dns=" . $GLOBALS['FOGCore']->getSetting( "FOG_PXE_IMAGE_DNSADDRESS" ) . " type=up img=$image imgid=$imageid mac=" . $member->getMACColon() . " storage=" . $nfsip . ":" . $nfsroot . " web=" . sloppyNameLookup($GLOBALS['FOGCore']->getSetting( "FOG_WEB_HOST")) . $GLOBALS['FOGCore']->getSetting( "FOG_WEB_ROOT" ) . " ignorepg=$ignorepg osid=" . $member->getOSID() . " $mode $pct $imgType $keymapapp shutdown=$shutdown loglevel=4 consoleblank=0 " . $GLOBALS['FOGCore']->getSetting( "FOG_KERNEL_ARGS" ) . " " . $member->getKernelArgs() . " " . $otherargs; 
			);
			
			// Kernel Arguments: Build kernelArgs array based on 'active' element
			foreach ($kernelArgsArray AS $arg)
			{
				if (!is_array($arg) && !empty($arg) || (is_array($arg) && $arg['active'] && !empty($arg)))
				{
					$kernelArgs[] = (is_array($arg) ? $arg['value'] : $arg);
				}
			}
			
			// Kernel Arguments: Remove duplicates
			$kernelArgs = array_unique($kernelArgs);
			
			// Kernel Arguements: Error checking
			if (!count($kernelArgs))
			{
				throw new Exception('No Kernel Arguments! This should not happen!');
			}
			
			// PXE: Build PXE File contents
			$output[] = "# " . _("Created by FOG Imaging System");
			$output[] = "DEFAULT fog";
			$output[] = "LABEL fog";
			$output[] = "KERNEL " . ($TaskType->get('kernel') ? $TaskType->get('kernel') : ($this->get('kernel') ? $this->get('kernel') : $this->FOGCore->getSetting('FOG_TFTP_PXE_KERNEL')));
			$output[] = "APPEND " . implode(' ', (array)$kernelArgs);
			
			// DEBUG
			//$this->fatalError(implode("<br/>", $output));
			
			// PXE: Save PXE File to tmp file
			if (!@file_put_contents($localPXEFile, implode("\n", $output)))
			{
				$error = error_get_last();
				throw new Exception(sprintf(_('Failed to write TMP PXE File. File: %s, Error: %s'), $localPXEFile, $error['message']));
			}
			
			// TODO: Multiple TFTP servers
			/*
			$FOGTFTPServers = (preg_match('#,#', $this->FOGCore->getSetting('FOG_TFTP_HOST')) ? explode(',', $this->FOGCore->getSetting('FOG_TFTP_HOST')) : array($this->FOGCore->getSetting('FOG_TFTP_HOST')));
			foreach ($FOGTFTPServers AS $TFTPServer)
			{
				$this->FOGFTP	->set('host', 		$TFTPServer)
						->set('username',	$this->FOGCore->getSetting('FOG_TFTP_FTP_USERNAME'))
						->set('password',	$this->FOGCore->getSetting('FOG_TFTP_FTP_PASSWORD'))
						->connect()
						->put($remotePXEFile, $localPXEFile);
			}
			*/
			
			// FTP: Connect -> Upload new PXE file
			$this->FOGFTP	->set('host', 		$this->FOGCore->getSetting('FOG_TFTP_HOST'))
					->set('username',	$this->FOGCore->getSetting('FOG_TFTP_FTP_USERNAME'))
					->set('password',	$this->FOGCore->getSetting('FOG_TFTP_FTP_PASSWORD'))
					->connect()
					->put($remotePXEFile, $localPXEFile);
			
			
			// PXE: Remove local PXE file
			@unlink($localPXEFile);
			
			// Task: Create Task Object
			$Task = new Task(array(
				'name'		=> $taskName,
				'createdBy'	=> $this->FOGUser->get('name'),
				'hostID'	=> $this->get('id'),
				'isForced'	=> '0',
				'stateID'	=> '1',
				'typeID'	=> $taskTypeID, 
				'NFSGroupID' 	=> $Image->getStorageGroup()->get('id'), 
				'NFSMemberID'	=> $Image->getStorageGroup()->getOptimalStorageNode()->get('id')
			));
			
			// Task: Save to database
			if (!$Task->save())
			{
				// Task save failed!
				try
				{
					// FTP: Delete PXE file -> Disconnect
					$this->FOGFTP->delete($remotePXEFile);
				} catch (Exception $e) {}
				
				$this->FOGFTP->close(($isGroupTask ? false : true));
				
			
				// Throw error
				throw new Exception(_('Task creation failed'));
			}
			
			// Success
			// FTP: Disconnect
			$this->FOGFTP->close(($isGroupTask ? false : true));
		
			// Snapins
			// LEGACY
			// TODO: Convert
			if (!$isUpload && $deploySnapins)
			{
				// Remove any exists snapin tasks
				cancelSnapinsForHost($conn, $this->get('id'));
				
				// now do a clean snapin deploy
				deploySnapinsForHost($conn, $this->get('id'));
			}
			
			// Wake Host
			$this->wakeOnLAN();
			
			// Log History event
			$this->FOGCore->logHistory(sprintf('Task Created: Task ID: %s, Task Name: %s, Host ID: %s, Host Name: %s, Host MAC: %s, Image ID: %s, Image Name: %s', $Task->get('id'), $Task->get('name'), $this->get('id'), $this->get('name'), $this->getMACAddress(), $this->getImage()->get('id'), $this->getImage()->get('name')));
			
			return $Task;
		}
		catch (Exception $e)
		{
			// Failure
			throw new Exception($e->getMessage());
		}
	}
	
	function createSingleRunScheduledPackage($taskTypeID, $taskName = '', $scheduledDeployTime, $enableShutdown = false, $enableSnapins = false, $isGroupTask = false, $arg2 = null)
	{
		try
		{
			// Varaibles
			$findWhere = array(
				'isActive' 	=> '1',
				'isGroupTask' 	=> $isGroupTask,
				'taskType' 	=> $taskTypeID,
				'type' 		=> 'S',		// S = Single Schedule Deployment, C = Cron-style Schedule Deployment
				'hostID' 	=> $this->get('id'),
				'scheduleTime'	=> $scheduledDeployTime
			);

			// Error checking
			if ($scheduledDeployTime < time())
			{
				throw new Exception(sprintf(_('Scheduled date is in the past. Date: %s'), date('Y/d/m H:i', $scheduledDeployTime)));
			}
			if ($this->FOGCore->getClass('ScheduledTaskManager')->count($findWhere))
			{
				throw new Exception(_('A task already exists for this Host at this scheduled date & time'));
			}
			
			// TaskType: Variables
			$TaskType = new TaskType($taskTypeID);
			$isUpload = $TaskType->isUpload();
			
			// TaskType: Error checking
			if (!$TaskType->isValid())
			{
				throw new Exception(_('Task Type is not valid'));
			}
			
			// Task: Merge $findWhere array with other Task data -> Create ScheduledTask Object
			$Task = new ScheduledTask(array_merge($findWhere, array(
				'name'		=> 'Scheduled Task',
				'shutdown'	=> ($enableShutdown ? '1' : '0'),
				'other1'	=> ($isUpload && $enableSnapins ? '1' : '0'),
				'other2'	=> $arg2
			)));
			
			// Save
			if (!$Task->save())
			{
				// Throw error
				throw new Exception(_('Task creation failed'));
			}
			
			// Log History event
			$this->FOGCore->logHistory(sprintf('Scheduled Task Created: Task ID: %s, Task Name: %s, Host ID: %s, Host Name: %s, Host MAC: %s, Image ID: %s, Image Name: %s', $Task->get('id'), $Task->get('name'), $this->get('id'), $this->get('name'), $this->getMACAddress(), $this->getImage()->get('id'), $this->getImage()->get('name')));
			
			// Return
			return $Task;
		}
		catch (Exception $e)
		{
			// Failure
			throw new Exception($e->getMessage());
		}
	}
	
	function createCronScheduledPackage($taskTypeID, $taskName = '', $minute = 1, $hour = 23, $dayOfMonth = '*', $month = '*', $dayOfWeek = '*', $enableShutdown = false, $enableSnapins = true, $isGroupTask = false, $arg2 = null)
	{
		try
		{
			// Error checking
			if ($minute != '*' && ($minute < 0 || $minute > 59))
			{
				throw new Exception(_('Minute value is not valid'));
			}
			if ($hour != '*' && ($hour < 0 || $hour > 23))
			{
				throw new Exception(_('Hour value is not valid'));
			}
			if ($dayOfMonth != '*' && ($dayOfMonth < 0 || $dayOfMonth > 31))
			{
				throw new Exception(_('Day of Month value is not valid'));
			}
			if ($month != '*' && ($month < 0 || $month > 12))
			{
				throw new Exception(_('Month value is not valid'));
			}
			if ($dayOfWeek != '*' && ($dayOfWeek < 0 || $dayOfWeek > 6))
			{
				throw new Exception(_('Day of Week value is not valid'));
			}
			
			// Variables
			$findWhere = array(
				'isActive' 	=> '1',
				'isGroupTask' 	=> $isGroupTask,
				'taskType' 	=> $taskTypeID,
				'type' 		=> 'C',		// S = Single Schedule Deployment, C = Cron-style Schedule Deployment
				'hostID' 	=> $this->get('id'),
				'minute' 	=> $minute,
				'hour' 		=> $hour,
				'dayOfMonth' 	=> $dayOfMonth,
				'month' 	=> $month,
				'dayOfWeek' 	=> $dayOfWeek
			);
			
			// Error checking: Active Scheduled Task
			if ($this->FOGCore->getClass('ScheduledTaskManager')->count($findWhere))
			{
				throw new Exception(_('A task already exists for this Host at this cron schedule'));
			}
			
			// TaskType: Variables
			$TaskType = new TaskType($taskTypeID);
			$isUpload = $TaskType->isUpload();
			
			// TaskType: Error checking
			if (!$TaskType->isValid())
			{
				throw new Exception(_('Task Type is not valid'));
			}
			
			// Task: Merge $findWhere array with other Task data -> Create ScheduledTask Object
			$Task = new ScheduledTask(array_merge($findWhere, array(
				'name'		=> 'Scheduled Task',
				'shutdown'	=> ($enableShutdown ? '1' : '0'),
				'other1'	=> ($isUpload && $enableSnapins ? '1' : '0'),
				'other2'	=> $arg2
			)));
			
			// Task: Save
			if (!$Task->save())
			{
				// Throw error
				throw new Exception(_('Task creation failed'));
			}
			
			// Log History event
			$this->FOGCore->logHistory(sprintf('Cron Task Created: Task ID: %s, Task Name: %s, Host ID: %s, Host Name: %s, Host MAC: %s, Image ID: %s, Image Name: %s', $Task->get('id'), $Task->get('name'), $this->get('id'), $this->get('name'), $this->getMACAddress(), $this->getImage()->get('id'), $this->getImage()->get('name')));
			
			// Return
			return $Task;
		}
		catch (Exception $e)
		{
			// Failure
			throw new Exception($e->getMessage());
		}
	}

	public function wakeOnLAN()
	{
		// HTTP request to WOL script
		$this->FOGCore->wakeOnLAN($this->getMACAddress());
	}
	
	// Printer Management
	public function getPrinters()
	{
		return $this->get('printers');
	}
	
	public function addPrinter($addArray)
	{
		// Add
		foreach ((array)$addArray AS $item)
		{
			$this->add('printers', $item);
		}
		
		// Return
		return $this;
	}
	
	public function removePrinter($removeArray)
	{
		// Iterate array (or other as array)
		foreach ((array)$removeArray AS $remove)
		{
			// Create object if needed -> Remove object from data
			$this->remove('printers', ($remove instanceof Printer ? $remove : new Printer((int)$remove)));
		}
		
		// Return
		return $this;
	}
	
	// Snapin Management
	public function getSnapins()
	{
		return $this->get('snapins');
	}
	
	public function addSnapin($addArray)
	{
		// Add
		foreach ((array)$addArray AS $item)
		{
			$this->add('snapins', $item);
		}
		
		// Return
		return $this;
	}
	
	public function removeSnapin($removeArray)
	{
		// Iterate array (or other as array)
		foreach ((array)$removeArray AS $remove)
		{
			// Create object if needed -> Remove object from data
			$this->remove('snapins', ($remove instanceof Snapin ? $remove : new Snapin((int)$remove)));
		}
		
		// Return
		return $this;
	}
	
	// Modules
	public function getModules($Module = '')
	{
		return $this->get('modules');
	}
	
	public function getModuleStatus($Module = '')
	{
		// TODO: Complete me!
		return $this->get('modules');
	}
	
	public function addModule($addArray)
	{
		// Add
		foreach ((array)$addArray AS $item)
		{
			$this->add('modules', $item);
		}
		
		// Return
		return $this;
	}
	
	public function removeModule($removeArray)
	{
		// Iterate array (or other as array)
		foreach ((array)$removeArray AS $remove)
		{
			// Create object if needed -> Remove object from data
			$this->remove('modules', ($remove instanceof Module ? $remove : new Module((int)$remove)));
		}
		
		// Return
		return $this;
	}
	
	public function destroy($field = 'id')
	{
		// Complete active tasks
		foreach ((array)$this->FOGCore->getClass('TaskManager')->find(array('hostID' => $this->get('id'))) AS $Task)
		{
			$Task	->set('stateID', '5')
				->save();
		}
		
		// Remove Group associations
		$this->FOGCore->getClass('GroupAssociationManager')->destroy(array('hostID' => $this->get('id')));
		
		// Remove Module associations
		//$this->FOGCore->getClass('GroupAssociationManager')->destroy(array('hostID' => $this->get('id')));
		
		// Remove Snapin associations
		//$this->FOGCore->getClass('GroupAssociationManager')->destroy(array('hostID' => $this->get('id')));
		
		// Remove Printer associations
		//$this->FOGCore->getClass('GroupAssociationManager')->destroy(array('hostID' => $this->get('id')));
		
		// Return
		return parent::destroy($field);
	}
	
	
	// Legacy
	const PRINTER_MANAGEMENT_UNKNOWN = -1;
	const PRINTER_MANAGEMENT_NO_MANAGEMENT = 0;
	const PRINTER_MANAGEMENT_ADD = 1;	
	const PRINTER_MANAGEMENT_ADDREMOVE = 2;	

	const OS_UNKNOWN = -1;
	const OS_WIN2000XP = 1;
	const OS_WINVISTA = 2;
	const OS_WIN98 = 3;
	const OS_WIN7 = 5;
	const OS_WINOTHER = 4;
	const OS_LINUX = 50;
	const OS_OTHER = 99;
	
	function setPrinterManagementLevel( $level ) 	{ return $this->set('printerLevel', $level); }
	function setADUsage( $bl ) 			{ return $this->set('useAD', $bl); }
	function useAD() 				{ return $this->get('useAD'); }
	function getADDomain() 			{ return $this->get('ADDomain'); }
	function getADOU() 				{ return $this->get('ADOU'); }
	function getADUser() 				{ return $this->get('ADUser'); }	
	function getADPass() 				{ return $this->get('ADPass'); }
	function setKernel( $kernel ) 			{ return $this->set('kernel', $kernel); }	
	function getKernel() 				{ return $this->get('kernel'); }
	function setKernelArgs( $args ) 		{ return $this->set('kernelArgs', $args); }
	function getKernelArgs() 			{ return $this->get('kernelArgs'); }
	function setImage( $objimg ) 			{ return $this->set('imageID', $objimg->get('id')); }
	function getOSID() 				{ return $this->get('osID'); }
	function getPrinterManagementLevel(  ) 	{ return $this->get('printerLevel'); }
	function setIPAddress( $ip )			{ return $this->set('ip', $ip); }
	function getIPAddress( )			{ return $this->get('ip'); }
	function usesAD()				{ return $this->get('useAD'); }
	function getDomain()				{ return $this->get('ADDomain'); }
	function getOU()				{ return $this->get('ADOU'); }
	function getUser()				{ return $this->get('ADUser'); }
	function getPassword()				{ return $this->get('ADPass'); }
	function setDiskDevice( $hd )			{ return $this->set('kernelDevice', $hd); }
	function getDiskDevice(  )			{ return $this->get('kernelDevice'); }
	function getDevice(  )				{ return $this->get('kernelDevice'); }
	function setID( $id )				{ return $this->set('id', $id); }
	function getID()				{ return $this->get('id'); }
	function getHostName()				{ return $this->get('name'); }
	function setHostname( $hn )			{ return $this->set('name', $hn); }
	function setDescription( $desc )		{ return $this->set('description', $desc); }
	function getDescription( )			{ return $this->get('description'); }
	function setOS( $os )				{ return $this->set('osID', $os); }
}
