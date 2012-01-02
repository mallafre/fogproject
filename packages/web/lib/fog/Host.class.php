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
		'printers',
		'optimalStorageNode'
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
		return new OS($this->get('osID'));
	}
	
	public function getPrinters()
	{
		return $this->get('printers');
	}
	
	public function getMACAddress()
	{
		return $this->get('mac');
	}
	
	// Overrides
	public function get($key = '')
	{
		if ($this->key($key) == 'printers' && !$this->isLoaded('printers'))
		{
			// Printers
			if ($this->get('id'))
			{
				$this->DB->query("SELECT * FROM  printerAssoc inner join printers on ( printerAssoc.paPrinterID = printers.pID ) WHERE printerAssoc.paHostID = '%s' ORDER BY printers.pAlias", $this->get('id'));
				
				while ($printer = $this->DB->fetch()->get())
				{
					$printers[] = new Printer($printer);
				}
			}
			
			$this->set('printers', (array)$printers);
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
		if ($this->key($key) == 'additionalMACs')
		{
			foreach ((array)$value AS $MAC)
			{
				$newValue[] = ($MAC instanceof MACAddress ? $MAC : new MACAddress($MAC));
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
		
		
		
		// Add
		return parent::add($key, $value);
	}
	
	public function save()
	{
		// Save
		parent::save();

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
		
		// Return
		return $this;
	}
	
	public function load()
	{
		// Save
		parent::load();

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
		return $this->FOGCore->getClass('TaskManager')->count(array('state' => array(0, 1), 'hostID' => $this->get('id')));
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
	function createImagePackage($taskType, $taskName = '', $shutdown = false, $debug = false, $deploySnapins = true, $isGroupTask = false)
	{
		try
		{
			// Variables
			$taskType = strtoupper($taskType);	// U = Upload, D = Download
			$isUpload = ($taskType == 'U');
		
			// Error checking
			if ($this->getActiveTaskCount())
			{
				throw new Exception('Host is already a member of a active task');
			}
			if (!$this->isValid())
			{
				throw new Exception('Host is not valid');
			}
			
			// Image: Variables
			$Image = $this->getImage();
			
			// Image: Error checking
			if (!$Image->isValid())
			{
				throw new Exception('Image is not valid');
			}
			if (!$Image->getStorageGroup()->isValid())
			{
				throw new Exception('Storage Group is not valid');
			}
			
			// Storage Node: Variables
			// NOTE: Master storage node node for Uploads or, Optimal storage node for Deploy
			$StorageNode = ($isUpload ? $Image->getStorageGroup()->getMasterStorageNode() : $this->getOptimalStorageNode());
			
			// Storage Node: Error Checking
			if (!$StorageNode->isValid())
			{
				throw new Exception('Storage Node is not valid');
			}
			
			// Variables
			$mac = $this->getMACAddress()->getMACWithColon();
			$localPXEFile = $this->FOGCore->makeTempFilePath();
			$remotePXEFile = rtrim($this->FOGCore->getSetting('FOG_TFTP_PXE_CONFIG_DIR'), '/') . '/' . $this->getMACAddress()->getMACPXEPrefix();
			
			// Kernel Arguments: Define possible kernel arguments
			// NOTE: slightly more manageable but needs more love
			$kernelArgsArray = array(
				// FOG
				'initrd=' . $this->FOGCore->getSetting('FOG_PXE_BOOT_IMAGE'),
				'root=/dev/ram0',
				'rw',
				'ramdisk_size=' . $this->FOGCore->getSetting('FOG_KERNEL_RAMDISK_SIZE'),
				'ip=dhcp',
				'dns=' . $this->FOGCore->getSetting('FOG_PXE_IMAGE_DNSADDRESS'),
				'mac=' . $mac,
				'ftp=' . $this->FOGCore->resolveHostname($this->FOGCore->getSetting('FOG_TFTP_HOST')),
				'storage=' . $StorageNode->get('ip') . ':' . $StorageNode->get('path'),
				'web=' . $this->FOGCore->resolveHostname($this->FOGCore->getSetting('FOG_WEB_HOST')) . '/' . ltrim($this->FOGCore->getSetting('FOG_WEB_ROOT'), '/'),
				'osid=' . $this->get('osID'),
				'loglevel=4',
				'consoleblank=0',
				'chkdsk=' . ($this->FOGCore->getSetting('FOG_DISABLE_CHKDSK') == '1' ? '0' : '1'),
				'img=' . $Image->get('path'),
				'imgType=' . $Image->getImageType()->get('type'),
				
			
				// Dynamic kernel args - if 'active' is false, arg wont be used
				array(	'value'		=> 'shutdown=' . $shutdown,
					'active'	=> $shutdown
				),
				array(	'value'		=> 'mode=debug',
					'active'	=> $debug
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
				
				// Type
				'type=' . ($isUpload ? 'up' : 'down'),
				
				// Upload
				array(	'value'		=> 'pct=' . (is_numeric($GLOBALS['FOGCore']->getSetting('FOG_UPLOADRESIZEPCT')) && $GLOBALS['FOGCore']->getSetting('FOG_UPLOADRESIZEPCT') >= 5 && $GLOBALS['FOGCore']->getSetting('FOG_UPLOADRESIZEPCT') < 100 ? $this->FOGCore->getSetting('FOG_UPLOADRESIZEPCT') : '5'),
					'active'	=> $isUpload
				),
				array(	'value'		=> 'ignorepg=' . ($GLOBALS['FOGCore']->getSetting( "FOG_UPLOADIGNOREPAGEHIBER" ) ? '1' : '0'),
					'active'	=> $isUpload
				),
				array(	'value'		=> 'imgid=' . $Image->get('id'),
					'active'	=> $isUpload
				),
				
				
				
				// OLD DEPLOY
				//append initrd=" . $GLOBALS['FOGCore']->getSetting( "FOG_PXE_BOOT_IMAGE" ) . "  root=/dev/ram0 rw ramdisk_size=" . $GLOBALS['FOGCore']->getSetting( "FOG_KERNEL_RAMDISK_SIZE" ) . " ip=dhcp dns=" . $GLOBALS['FOGCore']->getSetting( "FOG_PXE_IMAGE_DNSADDRESS" ) . " type=down img=" . $Image->get('path') . " mac=" . $member->getMACColon() . " ftp=" . sloppyNameLookup($GLOBALS['FOGCore']->getSetting( "FOG_TFTP_HOST" )) . " storage=" . $member->getNFSServer() . ":" . $member->getNFSRoot() . " web=" . sloppyNameLookup($GLOBALS['FOGCore']->getSetting( "FOG_WEB_HOST")) . $GLOBALS['FOGCore']->getSetting( "FOG_WEB_ROOT" ) . " osid=" . $member->getOSID() . " $mode $imgType $keymapapp shutdown=$shutdown loglevel=4 consoleblank=0 " . $GLOBALS['FOGCore']->getSetting( "FOG_KERNEL_ARGS" ) . " " . $member->getKernelArgs() . " " . $otherargs; 
				// OLD UPLOAD
				//append initrd=" . $GLOBALS['FOGCore']->getSetting( "FOG_PXE_BOOT_IMAGE" ) . "  root=/dev/ram0 rw ramdisk_size=" . $GLOBALS['FOGCore']->getSetting( "FOG_KERNEL_RAMDISK_SIZE" ) . " ip=dhcp dns=" . $GLOBALS['FOGCore']->getSetting( "FOG_PXE_IMAGE_DNSADDRESS" ) . " type=up img=$image imgid=$imageid mac=" . $member->getMACColon() . " storage=" . $nfsip . ":" . $nfsroot . " web=" . sloppyNameLookup($GLOBALS['FOGCore']->getSetting( "FOG_WEB_HOST")) . $GLOBALS['FOGCore']->getSetting( "FOG_WEB_ROOT" ) . " ignorepg=$ignorepg osid=" . $member->getOSID() . " $mode $pct $imgType $keymapapp shutdown=$shutdown loglevel=4 consoleblank=0 " . $GLOBALS['FOGCore']->getSetting( "FOG_KERNEL_ARGS" ) . " " . $member->getKernelArgs() . " " . $otherargs; 
				
				// User
				$this->FOGCore->getSetting('FOG_KERNEL_ARGS'),
				$this->get('kernelArgs'),
			);
			
			// 
			
			// Kernel Arguments: Build kernelArgs array based on 'active' element
			foreach ($kernelArgsArray AS $arg)
			{
				if (!is_array($arg) && !empty($arg) || (is_array($arg) && $arg['active'] && $arg = $arg['value'] && !empty($arg)))
				{
					$kernelArgs[] = $arg;
				}
			}
			
			// Kernel Arguements: Error checking
			if (!count($kernelArgs))
			{
				throw new Exception('No Kernel Arguments! This should not happen!');
			}
			
			// PXE: Build PXE File contents
			$output[] = "# " . _("Created by FOG Imaging System");
			$output[] = "DEFAULT fog";
			$output[] = "LABEL fog";
			$output[] = "KERNEL " . ($this->get('kernel') ? $this->get('kernel') : $this->FOGCore->getSetting('FOG_TFTP_PXE_KERNEL'));
			$output[] = "APPEND " . implode(' ', (array)$kernelArgs);
			
			// PXE: Save PXE File to tmp file
			if (!@file_put_contents($localPXEFile, implode("\n", $output)))
			{
				$error = error_get_last();
				throw new Exception(sprintf('Failed to write TMP PXE File. File: %s, Error: %s', $localPXEFile, $error['message']));
			}
			
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
				'isForced'	=> 0,
				'state'		=> 0,
				'type'		=> $taskType
			));
			
			// Task: Save to database
			if (!$Task->save())
			{
				// Task save failed!
				// FTP: Delete PXE file -> Disconnect
				$this->FOGFTP->delete($remotePXEFile)->close(($isGroupTask ? false : true));
				
			
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
			//$this->debug('Failed to %s. Error: %s', array(__FUNCTION__, $e->getMessage()));
			//throw new Exception(sprintf('Failed to %s. Error: %s', __FUNCTION__, $e->getMessage()));
			throw new Exception($e->getMessage());
		}
	}
	
	function createSingleRunScheduledPackage($taskType, $taskName = '', $scheduledDeployTime, $enableShutdown = false, $enableSnapins = false, $isGroupTask = false, $arg2 = null)
	{
		try
		{
			// Varaibles
			$taskType = strtoupper($taskType);
			$isUpload = ($taskType == 'U');
			
			$findWhere = array(
				'isActive' 	=> '1',
				'isGroupTask' 	=> $isGroupTask,
				'taskType' 	=> $taskType,
				'type' 		=> 'S',		// S = Single Schedule Deployment, C = Cron-style Schedule Deployment
				'hostID' 	=> $this->get('id'),
				'scheduleTime'	=> $scheduledDeployTime
			);

			// Error checking
			if ($scheduledDeployTime < time())
			{
				throw new Exception(sprintf('Scheduled date is in the past. Date: %s', date('Y/d/m H:i', $scheduledDeployTime)));
			}
			if ($this->FOGCore->getClass('ScheduledTaskManager')->count($findWhere))
			{
				throw new Exception('A task already exists for this Host at this scheduled date & time');
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
	
	function createCronScheduledPackage($taskType, $taskName = '', $minute = 1, $hour = 23, $dayOfMonth = '*', $month = '*', $dayOfWeek = '*', $enableShutdown = false, $enableSnapins = true, $isGroupTask = false, $arg2 = null)
	{
		try
		{
			// Varaibles
			$taskType = strtoupper($taskType);
			$isUpload = ($taskType == 'U');
			
			// Error checking
			if ($minute != '*' && ($minute < 0 || $minute > 59))
			{
				throw new Exception('Minute value is not valid');
			}
			if ($hour != '*' && ($hour < 0 || $hour > 23))
			{
				throw new Exception('Hour value is not valid');
			}
			if ($dayOfMonth != '*' && ($dayOfMonth < 0 || $dayOfMonth > 31))
			{
				throw new Exception('Day of Month value is not valid');
			}
			if ($month != '*' && ($month < 0 || $month > 12))
			{
				throw new Exception('Month value is not valid');
			}
			if ($dayOfWeek != '*' && ($dayOfWeek < 0 || $dayOfWeek > 6))
			{
				throw new Exception('Day of Week value is not valid');
			}
			
			// Variables
			$findWhere = array(
				'isActive' 	=> '1',
				'isGroupTask' 	=> $isGroupTask,
				'taskType' 	=> $taskType,
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
				throw new Exception('A task already exists for this Host at this cron schedule');
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