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
		'printers'
	);
	
	// Required database fields
	public $databaseFieldsRequired = array(
		'id',
		'name',
		'mac'
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
	
	// Overrides
	public function get($key)
	{
		// Printers
		if ($this->key($key) == 'printers' && !$this->isLoaded('printers'))
		{
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
		return (($this->get('id') != '' || $this->get('name') != '') && $this->get('mac') != '' ? true : false);
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
	function setID( $id )				{ return $this->set('id', $id); }
	function getID()				{ return $this->get('id'); }
	function getHostName()				{ return $this->get('name'); }
	function setHostname( $hn )			{ return $this->set('name', $hn); }
	function setDescription( $desc )		{ return $this->set('description', $desc); }
	function getDescription( )			{ return $this->get('description'); }
	function setOS( $os )				{ return $this->set('osID', $os); }
	
	// Replace with Task Class when completed
	public function startTask($conn, $tasktype, $blShutdown, $args1=null, $args2=null, $args3=null, $args4=null, $args5=null, &$reason)
	{
		$reason = "";
		if ( $conn != null  )
		{
			switch( strtoupper($tasktype) )
			{
				/*
				 *    Unicast Send
				 */
				
				
				case strtoupper(FOGCore::TASK_UNICAST_SEND):
					if ( $this->getImage() != null  )
					{
						if ( $this->getImage()->getPath() != null && strlen(trim($this->getImage()->getPath())) > 0 )
						{
							$storageGroup = $this->getImage()->getStorageGroup();
							if ( $storageGroup != null )
							{
								if ( $storageGroup->getMembers() != null && count( $storageGroup->getMembers() ) > 0  )
								{
									$masterNode = $storageGroup->getMasterStorageNode();
									if ( $masterNode != null )
									{
										$mac = $this->get('mac');							
										if ( $mac != null )
										{
											if ( $mac->isValid( ) )
											{
												$imgType = "imgType=n";
												if ( $this->getImage()->getImageType() == Image::IMAGE_TYPE_DD )
													$imgType = "imgType=dd";
												else if ( $this->getImage()->getImageType() == Image::IMAGE_TYPE_MULTIPARTITION_SINGLE_DISK )
													$imgType = "imgType=mps";
												else if ( $this->getImage()->getImageType() == Image::IMAGE_TYPE_MULTIPARTITION_MULTIDISK )
													$imgType = "imgType=mpa";												
												else
												{
													if ( $this->get('osID') == Host::OS_OTHER )
													{
														$reason = "Invalid OS type, unable to determine MBR.";
														return false;
													}

													if ( strlen( trim($this->get('osID')) ) == 0 || $this->get('osID') == Host::OS_UNKNOWN )
													{
														$reason = "Invalid OS type, you must specify an OS Type to image.";
														return false;
													}

													if ( trim($this->get('osID')) != Host::OS_WIN2000XP && trim($this->get('osID')) != Host::OS_WINVISTA && trim($this->get('osID')) != Host::OS_WIN7 )
													{
														$reason = "Unsupported OS detected in host!";
														return false;
													}				
												}
											
												$keymapapp = "";
												$keymap = $this->FOGCore->getSetting("FOG_KEYMAP");
												if ( $keymap != null && $keymap != "" )
													$keymapapp = "keymap=$keymap";																							

												$strKern = $this->FOGCore->getSetting("FOG_TFTP_PXE_KERNEL" );
												if ( $this->get('kernel') != "" && $this->get('kernel') != null )
													$strKern = $this->get('kernel');		
													
												$output = "# Created by FOG Imaging System\n\n
															  DEFAULT fog\n
															  LABEL fog\n
															  kernel " . $strKern . "\n
															  append initrd=" . $this->FOGCore->getSetting("FOG_PXE_BOOT_IMAGE" ) . "  root=/dev/ram0 rw ramdisk_size=" . $this->FOGCore->getSetting("FOG_KERNEL_RAMDISK_SIZE" ) . " ip=dhcp dns=" . $this->FOGCore->getSetting("FOG_PXE_IMAGE_DNSADDRESS" ) . " type=down img=" . $this->getImage()->getPath() . " mac=" . $mac->getMACWithColon() . " ftp=" . sloppyNameLookup($this->FOGCore->getSetting("FOG_TFTP_HOST" )) . " storage=" . $masterNode->getHostIP() . ":" . $masterNode->getRoot() . " web=" . sloppyNameLookup($this->FOGCore->getSetting("FOG_WEB_HOST")) . $this->FOGCore->getSetting("FOG_WEB_ROOT" ) . " osid=" . $this->get('osID') . " $imgType $keymapapp shutdown=" . ( $blShutdown ? "on" : " " ) . " loglevel=4 "  . $this->FOGCore->getSetting("FOG_KERNEL_ARGS" ) . " " . $this->get('kernelArgs');

												$tmp = createPXEFile( $output );
												if( $tmp !== null )
												{
													// make sure there is no active task for this mac address
													$num = $this->FOGCore->getClass('TaskManager')->getCountOfActiveTasksWithMAC($mac->getMACWithColon());
				
													if ( $num == 0 )
													{
														// attempt to ftp file
										
														$ftp = ftp_connect($this->FOGCore->getSetting("FOG_TFTP_HOST" )); 
														$ftp_loginres = ftp_login($ftp, $this->FOGCore->getSetting("FOG_TFTP_FTP_USERNAME" ), $this->FOGCore->getSetting("FOG_TFTP_FTP_PASSWORD" )); 			
														if ($ftp && $ftp_loginres ) 
														{
															if ( ftp_put( $ftp, $this->FOGCore->getSetting("FOG_TFTP_PXE_CONFIG_DIR" ) . $mac->getMACImageReady(), $tmp, FTP_ASCII ) )
															{			
																$uname = "FOGScheduler";
											
																$sql = "insert into 
																		tasks(taskName, taskCreateTime, taskCheckIn, taskHostID, taskState, taskCreateBy, taskForce, taskType, taskNFSGroupID, taskNFSMemberID ) 
																		values('" . mysql_real_escape_string("Sched: " . $this->get('name')) . "', NOW(), NOW(), '" . $this->get('id') . "', '0', '" . $uname . "', '0', 'D', '" . $storageGroup->get('id') . "', '" . $masterNode->get('id') . "' )";
								
																if ( mysql_query( $sql, $conn ) )
																{
																
																	if ( trim($args1) == "1" )
																	{
																		// Remove any exists snapin tasks
																		cancelSnapinsForHost( $conn, $this->get('id') );
									
																		// now do a clean snapin deploy
																		deploySnapinsForHost( $conn, $this->get('id') );
																	}
								
																	// lets try to wake the computer up!
																	wakeUp( $mac->getMACWithColon() );																			
																	@ftp_close($ftp); 					
																	@unlink( $tmp );								
																	return true;
																}
																else
																{
																	ftp_delete( $ftp, $this->FOGCore->getSetting("FOG_TFTP_PXE_CONFIG_DIR" ) . $mac->getMACImageReady() ); 									
																	$reason = mysql_error($conn);
																}
															}  
															else
																$reason = "Unable to upload file."; 											
														}	
														else
															$reason = "Unable to connect to tftp server."; 	
						
														@ftp_close($ftp); 					
														@unlink( $tmp ); 							
													}
													else
														$reason = "Host is already a member of a active task.";
												}
												else
													$reason = "Failed to open tmp file.";	
											}
											else
												$reason = "Primary MAC is invalid: " . $mac->getMACWithColon();
										}
										else
											$reason = "No primary MAC address found.";
									}
									else
										$reason = "Unable to locate master node in storage group.";
								}
								else
									$reason = "Storage group has no members.";
							}
							else 
								$reason = "Storage Group is null.";
						}
						else
							$reason = "Image path is null.";
					}
					else
						$reason = "Image is null.";				
					break;
				case strtoupper(FOGCore::TASK_UNICAST_UPLOAD):
					/*
				 	*    Unicast Upload
				 	*/
				 	if ( $this->getImage() != null  )
					{
						if ( $this->getImage()->getPath() != null && strlen(trim($this->getImage()->getPath())) > 0 )
						{
							$storageGroup = $this->getImage()->getStorageGroup();
							if ( $storageGroup != null )
							{
								if ( $storageGroup->getMembers() != null && count( $storageGroup->getMembers() ) > 0  )
								{
									$masterNode = $storageGroup->getMasterStorageNode();
									if ( $masterNode != null )
									{
										$mac = $this->get('mac');							
										if ( $mac != null )
										{
											if ( $mac->isValid( ) )
											{
												$imgType = "imgType=n";
												if ( $this->getImage()->getImageType() == Image::IMAGE_TYPE_DD )
													$imgType = "imgType=dd";
												else if ( $this->getImage()->getImageType() == Image::IMAGE_TYPE_MULTIPARTITION_SINGLE_DISK )
													$imgType = "imgType=mps";
												else if ( $this->getImage()->getImageType() == Image::IMAGE_TYPE_MULTIPARTITION_MULTIDISK )
													$imgType = "imgType=mpa";												
												else
												{
													if ( $this->get('osID') == Host::OS_OTHER )
													{
														$reason = "Invalid OS type, unable to determine MBR.";
														return false;
													}

													if ( strlen( trim($this->get('osID')) ) == 0 || $this->get('osID') == Host::OS_UNKNOWN )
													{
														$reason = "Invalid OS type, you must specify an OS Type to image.";
														return false;
													}

													if ( trim($this->get('osID')) != Host::OS_WIN2000XP && trim($this->get('osID')) != Host::OS_WINVISTA && trim($this->get('osID')) != Host::OS_WIN7 )
													{
														$reason = "Unsupported OS detected in host!";
														return false;
													}				
												}
												
												$nfsroot = $masterNode->getRoot();
												if ( $nfsroot != null )
												{
													if ( endsWith( $nfsroot, "/" )  )
														$nfsroot .= "dev/";
													else 
														$nfsroot .= "/dev/";
														
													$pct = "pct=5";
													if ( is_numeric($this->FOGCore->getSetting("FOG_UPLOADRESIZEPCT") ) && $this->FOGCore->getSetting("FOG_UPLOADRESIZEPCT") >= 5 && $this->FOGCore->getSetting("FOG_UPLOADRESIZEPCT") < 100 )
														$pct = "pct=" . $this->FOGCore->getSetting("FOG_UPLOADRESIZEPCT");
														
													$ignorepg = "0";
			
													if ( $this->FOGCore->getSetting("FOG_UPLOADIGNOREPAGEHIBER" ) )
														$ignorepg = "1";		
														
													$keymapapp = "";
													$keymap = $this->FOGCore->getSetting("FOG_KEYMAP" );
													if ( $keymap != null && $keymap != "" )
														$keymapapp = "keymap=$keymap";	
														
													$strKern = $this->FOGCore->getSetting("FOG_TFTP_PXE_KERNEL" );
													if ( $this->get('kernel') != "" && $this->get('kernel') != null )
														$strKern = $this->get('kernel');	
														
													$output = "# Created by FOG Imaging System\n\n
																  DEFAULT send\n
																  LABEL send\n
																  kernel " . $strKern . "\n
																  append initrd=" . $this->FOGCore->getSetting("FOG_PXE_BOOT_IMAGE" ) . "  root=/dev/ram0 rw ramdisk_size=" . $this->FOGCore->getSetting("FOG_KERNEL_RAMDISK_SIZE" ) . " ip=dhcp dns=" . $this->FOGCore->getSetting("FOG_PXE_IMAGE_DNSADDRESS" ) . " type=up img=" .  $this->getImage()->getPath()  . " imgid=" . $this->getImage()->get('id') . " mac=" . $mac->getMACWithColon() . " storage=" . $masterNode->getHostIP() . ":" . $nfsroot . " web=" . sloppyNameLookup($this->FOGCore->getSetting("FOG_WEB_HOST")) . $this->FOGCore->getSetting("FOG_WEB_ROOT" ) . " ignorepg=$ignorepg osid=" . $this->get('osID') . " $pct $imgType $keymapapp shutdown=" . ( $blShutdown ? "on" : " " ) . " loglevel=4 "  . $this->FOGCore->getSetting("FOG_KERNEL_ARGS" ) . " " . $this->get('kernelArgs');																									
			 										$tmp = createPXEFile( $output );
													if( $tmp !== null )
													{ 
														$num = $this->FOGCore->getClass('TaskManager')->getCountOfActiveTasksWithMAC($mac->getMACWithColon());
														if ( $num == 0 )
														{
															$ftp = ftp_connect($this->FOGCore->getSetting("FOG_TFTP_HOST" )); 
															$ftp_loginres = ftp_login($ftp, $this->FOGCore->getSetting("FOG_TFTP_FTP_USERNAME" ), $this->FOGCore->getSetting("FOG_TFTP_FTP_PASSWORD" )); 			
															if ($ftp && $ftp_loginres ) 
															{
																if ( ftp_put( $ftp, $this->FOGCore->getSetting("FOG_TFTP_PXE_CONFIG_DIR" ) . $mac->getMACImageReady(), $tmp, FTP_ASCII ) )
																{			
																	$uname = "FOGScheduler";
																	$sql = "INSERT INTO 
																			tasks(taskName, taskCreateTime, taskCheckIn, taskHostID, taskState, taskCreateBy, taskForce, taskType, taskNFSGroupID, taskNFSMemberID ) 
																			VALUES('" . mysql_real_escape_string("Sched: " . $this->get('name')) . "', NOW(), NOW(), '" . $this->get('id') . "', '0', '" . mysql_real_escape_string( $uname ) . "', '0', 'U', '" . $storageGroup->get('id') . "', '" . $masterNode->get('id') . "' )";																																
						
																	if ( mysql_query( $sql, $conn ) )
																	{																
																		// lets try to wake the computer up!
																		wakeUp( $mac->getMACWithColon() );																			
																		@ftp_close($ftp); 					
																		@unlink( $tmp );								
																		return true;
																	}
																	else
																	{
																		ftp_delete( $ftp, $this->FOGCore->getSetting("FOG_TFTP_PXE_CONFIG_DIR" ) . $mac->getMACImageReady() ); 									
																		$reason = mysql_error($conn);
																																			
																	}
																}
																else
																	$reason = "Unable to upload file.";
															}
															else
																$reason = "Unable to connect to tftp server."; 	
						
															@ftp_close($ftp); 					
															@unlink( $tmp );															
														}
														else
															$reason = "Host is already a member of a active task.";
													}	
													else
														$reason = "Failed to open tmp file.";																  
												}
												else
													$reason = "Invalid NFS Root: " . $nfsroot;
											}
											else
												$reason = "Primary MAC is invalid: " . $mac->getMACWithColon();
										}
										else
											$reason = "No primary MAC address found.";											
									}
									else
										$reason = "Unable to locate master node in storage group.";									
								}
								else
									$reason = "Storage group has no members.";
							}
							else 
								$reason = "Storage Group is null.";
						}
						else
							$reason = "Image path is null.";
					}
					else
						$reason = "Image is null.";
					break;
				case strtoupper(FOGCore::TASK_WIPE):
					if ( $this->getImage() != null  )
					{
						if ( $this->getImage()->getPath() != null && strlen(trim($this->getImage()->getPath())) > 0 )
						{
							$storageGroup = $this->getImage()->getStorageGroup();
							if ( $storageGroup != null )
							{
								if ( $storageGroup->getMembers() != null && count( $storageGroup->getMembers() ) > 0  )
								{
									$masterNode = $storageGroup->getMasterStorageNode();
									if ( $masterNode != null )
									{
										$mac = $this->get('mac');							
										if ( $mac != null )
										{
											if ( $mac->isValid( ) )
											{
												if ( is_numeric($args2) )
												{
													$wipemode="wipemode=full";
													if ( trim($args2) ==  WIPE_FAST )
														$wipemode="wipemode=fast";
													else if ( trim($args2) ==  WIPE_NORMAL )
														$wipemode="wipemode=normal";
													else if ( trim($args2) ==  WIPE_FULL )	
														$wipemode="wipemode=full";
												
													$keymapapp = "";
													$keymap = $this->FOGCore->getSetting("FOG_KEYMAP" );
													if ( $keymap != null && $keymap != "" )
														$keymapapp = "keymap=$keymap";	
														
													$strKern = $this->FOGCore->getSetting("FOG_TFTP_PXE_KERNEL" );
													if ( $this->get('kernel') != "" && $this->get('kernel') != null )
														$strKern = $this->get('kernel');													
													
													$output = "# Created by FOG Imaging System\n\n
															  DEFAULT send\n
															  LABEL send\n
															  kernel " . $strKern . "\n
															  append initrd=" . $this->FOGCore->getSetting("FOG_PXE_BOOT_IMAGE" ) . "  root=/dev/ram0 rw ramdisk_size=" . $this->FOGCore->getSetting("FOG_KERNEL_RAMDISK_SIZE" ) . " ip=dhcp dns=" . $this->FOGCore->getSetting("FOG_PXE_IMAGE_DNSADDRESS" ) . " mac=" . $mac->getMACWithColon() . " web=" . sloppyNameLookup( $this->FOGCore->getSetting("FOG_WEB_HOST") ) . $this->FOGCore->getSetting("FOG_WEB_ROOT" ) . " osid=" . $this->get('osID') . " $wipemode mode=wipe $keymapapp shutdown=" . ( $blShutdown ? "on" : " " ) . " loglevel=4 " . $this->FOGCore->getSetting("FOG_KERNEL_ARGS" ) . " " . $this->get('kernelArgs') ;												
												
													//cancelSnapinsForHost( $conn, $this->get('id') );
													//deploySnapinsForHost( $conn, $this->get('id'), trim($args2) );
												
													$tmp = createPXEFile( $output );
													if( $tmp !== null )
													{ 
														$num = $this->FOGCore->getClass('TaskManager')->getCountOfActiveTasksWithMAC($mac->getMACWithColon());
														if ( $num == 0 )
														{
															$ftp = ftp_connect($this->FOGCore->getSetting("FOG_TFTP_HOST" )); 
															$ftp_loginres = ftp_login($ftp, $this->FOGCore->getSetting("FOG_TFTP_FTP_USERNAME" ), $this->FOGCore->getSetting("FOG_TFTP_FTP_PASSWORD" )); 			
															if ($ftp && $ftp_loginres ) 
															{
																if ( ftp_put( $ftp, $this->FOGCore->getSetting("FOG_TFTP_PXE_CONFIG_DIR" ) . $mac->getMACImageReady(), $tmp, FTP_ASCII ) )
																{			
																	$uname = "FOGScheduler";
																	$sql = "INSERT INTO 
																			tasks(taskName, taskCreateTime, taskCheckIn, taskHostID, taskState, taskCreateBy, taskForce, taskType ) 
																			VALUES('" . mysql_real_escape_string("Sched: " . $this->get('name')) . "', NOW(), NOW(), '" . $this->get('id') . "', '0', '" . mysql_real_escape_string( $uname ) . "', '0', 'W')";																																
						
																	if ( mysql_query( $sql, $conn ) )
																	{																
																		// lets try to wake the computer up!
																		wakeUp( $mac->getMACWithColon() );																			
																		@ftp_close($ftp); 					
																		@unlink( $tmp );								
																		return true;
																	}
																	else
																	{
																		ftp_delete( $ftp, $this->FOGCore->getSetting("FOG_TFTP_PXE_CONFIG_DIR" ) . $mac->getMACImageReady() ); 									
																		$reason = mysql_error($conn);
																																			
																	}
																}
																else
																	$reason = "Unable to upload file.";
															}
															else
																$reason = "Unable to connect to tftp server."; 	
						
															@ftp_close($ftp); 					
															@unlink( $tmp );															
														}
														else
															$reason = "Host is already a member of a active task.";
													}	
													else
														$reason = "Failed to open tmp file.";												
												
													wakeUp( $mac->getMACWithColon() );
													return true;
												}
												else
													$reason = "Invalid snapin ID";
											}		
											else
												$reason = "Primary MAC is invalid: " . $mac->getMACWithColon();
										}
										else
											$reason = "No primary MAC address found.";											
									}
									else
										$reason = "Unable to locate master node in storage group.";									
								}
								else
									$reason = "Storage group has no members.";
							}
							else 
								$reason = "Storage Group is null.";
						}
						else
							$reason = "Image path is null.";
					}
					else
						$reason = "Image is null.";							
					break;
				case strtoupper(FOGCore::TASK_DEBUG):
					break;	
				case strtoupper(FOGCore::TASK_MEMTEST):
					break;	
				case strtoupper(FOGCore::TASK_TESTDISK):
					if ( $this->getImage() != null  )
					{
						if ( $this->getImage()->getPath() != null && strlen(trim($this->getImage()->getPath())) > 0 )
						{
							$storageGroup = $this->getImage()->getStorageGroup();
							if ( $storageGroup != null )
							{
								if ( $storageGroup->getMembers() != null && count( $storageGroup->getMembers() ) > 0  )
								{
									$masterNode = $storageGroup->getMasterStorageNode();
									if ( $masterNode != null )
									{
										$mac = $this->get('mac');							
										if ( $mac != null )
										{
											if ( $mac->isValid( ) )
											{
												$keymapapp = "";
												$keymap = $this->FOGCore->getSetting("FOG_KEYMAP" );
												if ( $keymap != null && $keymap != "" )
													$keymapapp = "keymap=$keymap";	
													
												$strKern = $this->FOGCore->getSetting("FOG_TFTP_PXE_KERNEL" );
												if ( $this->get('kernel') != "" && $this->get('kernel') != null )
													$strKern = $this->get('kernel');													
												
												$output = "# Created by FOG Imaging System\n\n
														  DEFAULT send\n
														  LABEL send\n
														  kernel " . $strKern . "\n
														  append initrd=" . $this->FOGCore->getSetting("FOG_PXE_BOOT_IMAGE" ) . "  root=/dev/ram0 rw ramdisk_size=" . $this->FOGCore->getSetting("FOG_KERNEL_RAMDISK_SIZE" ) . " ip=dhcp dns=" . $this->FOGCore->getSetting("FOG_PXE_IMAGE_DNSADDRESS" ) . " mac=" . $mac->getMACWithColon() . " web=" . sloppyNameLookup($this->FOGCore->getSetting("FOG_WEB_HOST")) . $this->FOGCore->getSetting("FOG_WEB_ROOT" ) . " mode=badblocks $keymapapp shutdown=" . ( $blShutdown ? "on" : " " ) . " loglevel=4 " . $this->FOGCore->getSetting("FOG_KERNEL_ARGS" ) . " " . $this->get('kernelArgs');

												$tmp = createPXEFile( $output );
												if( $tmp !== null )
												{ 
													$num = $this->FOGCore->getClass('TaskManager')->getCountOfActiveTasksWithMAC($mac->getMACWithColon());
													if ( $num == 0 )
													{
														$ftp = ftp_connect($this->FOGCore->getSetting("FOG_TFTP_HOST" )); 
														$ftp_loginres = ftp_login($ftp, $this->FOGCore->getSetting("FOG_TFTP_FTP_USERNAME" ), $this->FOGCore->getSetting("FOG_TFTP_FTP_PASSWORD" )); 			
														if ($ftp && $ftp_loginres ) 
														{
															if ( ftp_put( $ftp, $this->FOGCore->getSetting("FOG_TFTP_PXE_CONFIG_DIR" ) . $mac->getMACImageReady(), $tmp, FTP_ASCII ) )
															{			
																$uname = "FOGScheduler";
																$sql = "INSERT INTO 
																		tasks(taskName, taskCreateTime, taskCheckIn, taskHostID, taskState, taskCreateBy, taskForce, taskType ) 
																		VALUES('" . mysql_real_escape_string("Sched: " . $this->get('name')) . "', NOW(), NOW(), '" . $this->get('id') . "', '0', '" . mysql_real_escape_string( $uname ) . "', '0', 'T')";																																
					
																if ( mysql_query( $sql, $conn ) )
																{																
																	// lets try to wake the computer up!
																	wakeUp( $mac->getMACWithColon() );																			
																	@ftp_close($ftp); 					
																	@unlink( $tmp );								
																	return true;
																}
																else
																{
																	ftp_delete( $ftp, $this->FOGCore->getSetting("FOG_TFTP_PXE_CONFIG_DIR" ) . $mac->getMACImageReady() ); 									
																	$reason = mysql_error($conn);
																																		
																}
															}
															else
																$reason = "Unable to upload file.";
														}
														else
															$reason = "Unable to connect to tftp server."; 	
					
														@ftp_close($ftp); 					
														@unlink( $tmp );															
													}
													else
														$reason = "Host is already a member of a active task.";
												}	
												else
													$reason = "Failed to open tmp file.";												
											
												wakeUp( $mac->getMACWithColon() );
												return true;
												
											}		
											else
												$reason = "Primary MAC is invalid: " . $mac->getMACWithColon();
										}
										else
											$reason = "No primary MAC address found.";											
									}
									else
										$reason = "Unable to locate master node in storage group.";									
								}
								else
									$reason = "Storage group has no members.";
							}
							else 
								$reason = "Storage Group is null.";
						}
						else
							$reason = "Image path is null.";
					}
					else
						$reason = "Image is null.";				
					break;
				case strtoupper(FOGCore::TASK_PHOTOREC):
					break;
				case strtoupper(FOGCore::TASK_MULTICAST):
					if ( $this->getImage() != null  )
					{
						if ( $this->getImage()->getPath() != null && strlen(trim($this->getImage()->getPath())) > 0 )
						{
							$storageGroup = $this->getImage()->getStorageGroup();
							if ( $storageGroup != null )
							{
								if ( $storageGroup->getMembers() != null && count( $storageGroup->getMembers() ) > 0  )
								{
									$masterNode = $storageGroup->getMasterStorageNode();
									if ( $masterNode != null )
									{
										$mac = $this->get('mac');							
										if ( $mac != null )
										{
											if ( $mac->isValid( ) )
											{
												$port = trim($args1);
												$mcID = trim($args2);
												if ( is_numeric( $port ) && is_numeric( $mcID ) && $port > 0 && $mcID >= 0 )
												{
													$nfsroot = $masterNode->getRoot();
													if ( $nfsroot != null )
													{
														if ( ! endsWith( $nfsroot, "/" )  )
															$nfsroot .= "/";
															
														$imgType = "imgType=n";
														if ( $this->getImage()->getImageType() == Image::IMAGE_TYPE_DD )
															$imgType = "imgType=dd";
														else if ( $this->getImage()->getImageType() == Image::IMAGE_TYPE_MULTIPARTITION_SINGLE_DISK )
															$imgType = "imgType=mps";
														else if ( $this->getImage()->getImageType() == Image::IMAGE_TYPE_MULTIPARTITION_MULTIDISK )
															$imgType = "imgType=mpa";												
														else
														{
															if ( $this->get('osID') == Host::OS_OTHER )
															{
																$reason = "Invalid OS type, unable to determine MBR.";
																return false;
															}

															if ( strlen( trim($this->get('osID')) ) == 0 || $this->get('osID') == Host::OS_UNKNOWN )
															{
																$reason = "Invalid OS type, you must specify an OS Type to image.";
																return false;
															}

															if ( trim($this->get('osID')) != Host::OS_WIN2000XP && trim($this->get('osID')) != Host::OS_WINVISTA && trim($this->get('osID')) != Host::OS_WIN7 )
															{
																$reason = "Unsupported OS detected in host!";
																return false;
															}				
														}
													
														$keymapapp = "";
														$keymap = $this->FOGCore->getSetting("FOG_KEYMAP" );
														if ( $keymap != null && $keymap != "" )
															$keymapapp = "keymap=$keymap";	
														
														$strKern = $this->FOGCore->getSetting("FOG_TFTP_PXE_KERNEL" );
														if ( $this->get('kernel') != "" && $this->get('kernel') != null )
															$strKern = $this->get('kernel');	
														
													
														
														$output = "# Created by FOG Imaging System\n\n
																	  DEFAULT send\n
																	  LABEL send\n
																	  kernel " . $strKern . "\n
																	  append initrd=" . $this->FOGCore->getSetting("FOG_PXE_BOOT_IMAGE" ) . " root=/dev/ram0 rw ramdisk_size=" . $this->FOGCore->getSetting("FOG_KERNEL_RAMDISK_SIZE" ) . " ip=dhcp dns=" . $this->FOGCore->getSetting("FOG_PXE_IMAGE_DNSADDRESS" ) . " type=down img=" .  $this->getImage()->getPath()  . " mc=yes port=" . $port . " storageip=" . $masterNode->getHostIP() . " storage=" . $masterNode->getHostIP() . ":" . $nfsroot . " mac=" . $mac->getMACWithColon() . " ftp=" . sloppyNameLookup($this->FOGCore->getSetting("FOG_TFTP_HOST" )) . " web=" . sloppyNameLookup($this->FOGCore->getSetting("FOG_WEB_HOST")) . $this->FOGCore->getSetting("FOG_WEB_ROOT" ) . " osid=" . $this->get('osID') . " $mode $imgType $keymapapp shutdown=" . ( $blShutdown ? "on" : " " ) . " loglevel=4 " . $this->FOGCore->getSetting("FOG_KERNEL_ARGS" ) . " " . $this->get('kernelArgs');
																						
				 										$tmp = createPXEFile( $output );
														if( $tmp !== null )
														{ 
															// make sure there is no active task for this mac address
															$num = $this->FOGCore->getClass('TaskManager')->getCountOfActiveTasksWithMAC($mac->getMACWithColon());
				
															if ( $num == 0 )
															{
																// attempt to ftp file
										
																$ftp = ftp_connect($this->FOGCore->getSetting("FOG_TFTP_HOST" )); 
																$ftp_loginres = ftp_login($ftp, $this->FOGCore->getSetting("FOG_TFTP_FTP_USERNAME" ), $this->FOGCore->getSetting("FOG_TFTP_FTP_PASSWORD" )); 			
																if ($ftp && $ftp_loginres ) 
																{
																	if ( ftp_put( $ftp, $this->FOGCore->getSetting("FOG_TFTP_PXE_CONFIG_DIR" ) . $mac->getMACImageReady(), $tmp, FTP_ASCII ) )
																	{			
																		$uname = "FOGScheduler";
											
																		$sql = "insert into 
																				tasks(taskName, taskCreateTime, taskCheckIn, taskHostID, taskState, taskCreateBy, taskForce, taskType, taskNFSGroupID, taskNFSMemberID ) 
																				values('" . mysql_real_escape_string("Sched: " . $this->get('name')) . "', NOW(), NOW(), '" . $this->get('id') . "', '0', '" . $uname . "', '0', 'D', '" . $storageGroup->get('id') . "', '" . $masterNode->get('id') . "' )";
								
																		if ( mysql_query( $sql, $conn ) )
																		{
																			$insertId = mysql_insert_id( $conn );
																			if ( $insertId !== null && $insertId >= 0 )
																			{
																				if ( linkTaskToMultitaskJob( $conn, $insertId, $mcID ) )
																				{

																					// Remove any exists snapin tasks
																					cancelSnapinsForHost( $conn, $this->get('id') );
								
																					// now do a clean snapin deploy
																					deploySnapinsForHost( $conn, $this->get('id') );

																					// lets try to wake the computer up!
																					wakeUp( $mac->getMACWithColon() );																			
																					@ftp_close($ftp); 					
																					@unlink( $tmp );
																					$reason = "OK";								
																					return true;
																				}
																				else
																					$reason = "Unable to link host task to multicast job!";
																			}
																			else
																				$reason = "Unable to obtain auto ID";
																		}
																		else
																		{
																			ftp_delete( $ftp, $this->FOGCore->getSetting("FOG_TFTP_PXE_CONFIG_DIR" ) . $mac->getMACImageReady() ); 									
																			$reason = mysql_error($conn);
																		}
																	}  
																	else
																		$reason = "Unable to upload file."; 											
																}	
																else
																	$reason = "Unable to connect to tftp server."; 	
						
																@ftp_close($ftp); 					
																@unlink( $tmp ); 							
															}
															else
																$reason = "Host is already a member of a active task.";
														}
														else
															$reason = "Failed to open tmp file.";														
													}
													else
														$reason = "Unable to determine NFS root";
												}
												else
													$reason = "Invalid port or multicast ID number";
											}		
											else
												$reason = "Primary MAC is invalid: " . $mac->getMACWithColon();																	
										}
										else
											$reason = "No primary MAC address found.";
									}
									else
										$reason = "Unable to locate master node in storage group.";
								}
								else
									$reason = "Storage group has no members.";
							}
							else 
								$reason = "Storage Group is null.";
						}
						else
							$reason = "Image path is null.";						
					}
					else
						$reason = "Image is null.";					
					break;
				case strtoupper(FOGCore::TASK_VIRUSSCAN):
					if ( $this->getImage() != null  )
					{
						if ( $this->getImage()->getPath() != null && strlen(trim($this->getImage()->getPath())) > 0 )
						{
							$storageGroup = $this->getImage()->getStorageGroup();
							if ( $storageGroup != null )
							{
								if ( $storageGroup->getMembers() != null && count( $storageGroup->getMembers() ) > 0  )
								{
									$masterNode = $storageGroup->getMasterStorageNode();
									if ( $masterNode != null )
									{
										$mac = $this->get('mac');							
										if ( $mac != null )
										{
											if ( $mac->isValid( ) )
											{
												$scanmode="avmode=s";
												if ( trim($args2) == FOG_AV_SCANQUARANTINE )
													$scanmode="avmode=q";
											
												$keymapapp = "";
												$keymap = $this->FOGCore->getSetting("FOG_KEYMAP" );
												if ( $keymap != null && $keymap != "" )
													$keymapapp = "keymap=$keymap";	
													
												$strKern = $this->FOGCore->getSetting("FOG_TFTP_PXE_KERNEL" );
												if ( $this->get('kernel') != "" && $this->get('kernel') != null )
													$strKern = $this->get('kernel');													
												
												$output = "# Created by FOG Imaging System\n\n
														  DEFAULT send\n
														  LABEL send\n
														  kernel " . $strKern . "\n
														  append initrd=" . $this->FOGCore->getSetting("FOG_PXE_BOOT_IMAGE" ) . "  root=/dev/ram0 rw ramdisk_size=" . $this->FOGCore->getSetting("FOG_KERNEL_RAMDISK_SIZE" ) . " ip=dhcp dns=" . $this->FOGCore->getSetting("FOG_PXE_IMAGE_DNSADDRESS" ) . " mac=" . $mac->getMACWithColon() . " web=" . sloppyNameLookup($this->FOGCore->getSetting("FOG_WEB_HOST")) . $this->FOGCore->getSetting("FOG_WEB_ROOT" ) . " osid=" . $this->get('osID') . " $scanmode mode=clamav $keymapapp shutdown=" . ( $blShutdown ? "on" : " " ) . " loglevel=4 " . $this->FOGCore->getSetting("FOG_KERNEL_ARGS" ) . " " . $this->get('kernelArgs');												
											
												$tmp = createPXEFile( $output );
												if( $tmp !== null )
												{ 
													$num = $this->FOGCore->getClass('TaskManager')->getCountOfActiveTasksWithMAC($mac->getMACWithColon());
													if ( $num == 0 )
													{
														$ftp = ftp_connect($this->FOGCore->getSetting("FOG_TFTP_HOST" )); 
														$ftp_loginres = ftp_login($ftp, $this->FOGCore->getSetting("FOG_TFTP_FTP_USERNAME" ), $this->FOGCore->getSetting("FOG_TFTP_FTP_PASSWORD" )); 			
														if ($ftp && $ftp_loginres ) 
														{
															if ( ftp_put( $ftp, $this->FOGCore->getSetting("FOG_TFTP_PXE_CONFIG_DIR" ) . $mac->getMACImageReady(), $tmp, FTP_ASCII ) )
															{			
																$uname = "FOGScheduler";
																$sql = "INSERT INTO 
																		tasks(taskName, taskCreateTime, taskCheckIn, taskHostID, taskState, taskCreateBy, taskForce, taskType ) 
																		VALUES('" . mysql_real_escape_string("Sched: " . $this->get('name')) . "', NOW(), NOW(), '" . $this->get('id') . "', '0', '" . mysql_real_escape_string( $uname ) . "', '0', 'V')";																																
					
																if ( mysql_query( $sql, $conn ) )
																{																
																	// lets try to wake the computer up!
																	wakeUp( $mac->getMACWithColon() );																			
																	@ftp_close($ftp); 					
																	@unlink( $tmp );								
																	return true;
																}
																else
																{
																	ftp_delete( $ftp, $this->FOGCore->getSetting("FOG_TFTP_PXE_CONFIG_DIR" ) . $mac->getMACImageReady() ); 									
																	$reason = mysql_error($conn);
																																		
																}
															}
															else
																$reason = "Unable to upload file.";
														}
														else
															$reason = "Unable to connect to tftp server."; 	
					
														@ftp_close($ftp); 					
														@unlink( $tmp );															
													}
													else
														$reason = "Host is already a member of a active task.";
												}	
												else
													$reason = "Failed to open tmp file.";												
											
												wakeUp( $mac->getMACWithColon() );
												return true;
												
											}		
											else
												$reason = "Primary MAC is invalid: " . $mac->getMACWithColon();
										}
										else
											$reason = "No primary MAC address found.";											
									}
									else
										$reason = "Unable to locate master node in storage group.";									
								}
								else
									$reason = "Storage group has no members.";
							}
							else 
								$reason = "Storage Group is null.";
						}
						else
							$reason = "Image path is null.";
					}
					else
						$reason = "Image is null.";				
					break;	
				case strtoupper(FOGCore::TASK_INVENTORY):
					if ( $this->getImage() != null  )
					{
						if ( $this->getImage()->getPath() != null && strlen(trim($this->getImage()->getPath())) > 0 )
						{
							$storageGroup = $this->getImage()->getStorageGroup();
							if ( $storageGroup != null )
							{
								if ( $storageGroup->getMembers() != null && count( $storageGroup->getMembers() ) > 0  )
								{
									$masterNode = $storageGroup->getMasterStorageNode();
									if ( $masterNode != null )
									{
										$mac = $this->get('mac');							
										if ( $mac != null )
										{
											if ( $mac->isValid( ) )
											{
												$keymapapp = "";
												$keymap = $this->FOGCore->getSetting("FOG_KEYMAP" );
												if ( $keymap != null && $keymap != "" )
													$keymapapp = "keymap=$keymap";	
													
												$strKern = $this->FOGCore->getSetting("FOG_TFTP_PXE_KERNEL" );
												if ( $this->get('kernel') != "" && $this->get('kernel') != null )
													$strKern = $this->get('kernel');													
												
												$output = "# Created by FOG Imaging System\n\n
														  DEFAULT send\n
														  LABEL send\n
														  kernel " . $strKern . "\n
														  append initrd=" . $this->FOGCore->getSetting("FOG_PXE_BOOT_IMAGE" ) . "  root=/dev/ram0 rw ramdisk_size=" . $this->FOGCore->getSetting("FOG_KERNEL_RAMDISK_SIZE" ) . " ip=dhcp dns=" . $this->FOGCore->getSetting("FOG_PXE_IMAGE_DNSADDRESS" ) . " mac_deployed=" . $mac->getMACWithColon() . " web=" . sloppyNameLookup($this->FOGCore->getSetting("FOG_WEB_HOST")) . $this->FOGCore->getSetting("FOG_WEB_ROOT" ) . " mode=autoreg deployed=1 $keymapapp shutdown=" . ( $blShutdown ? "on" : " " ) . " loglevel=4 " . $this->FOGCore->getSetting("FOG_KERNEL_ARGS" ) . " " . $this->get('kernelArgs');											
											
												$tmp = createPXEFile( $output );
												if( $tmp !== null )
												{ 
													$num = $this->FOGCore->getClass('TaskManager')->getCountOfActiveTasksWithMAC($mac->getMACWithColon());
													if ( $num == 0 )
													{
														$ftp = ftp_connect($this->FOGCore->getSetting("FOG_TFTP_HOST" )); 
														$ftp_loginres = ftp_login($ftp, $this->FOGCore->getSetting("FOG_TFTP_FTP_USERNAME" ), $this->FOGCore->getSetting("FOG_TFTP_FTP_PASSWORD" )); 			
														if ($ftp && $ftp_loginres ) 
														{
															if ( ftp_put( $ftp, $this->FOGCore->getSetting("FOG_TFTP_PXE_CONFIG_DIR" ) . $mac->getMACImageReady(), $tmp, FTP_ASCII ) )
															{			
																$uname = "FOGScheduler";
																$sql = "INSERT INTO 
																		tasks(taskName, taskCreateTime, taskCheckIn, taskHostID, taskState, taskCreateBy, taskForce, taskType ) 
																		VALUES('" . mysql_real_escape_string("Sched: " . $this->get('name')) . "', NOW(), NOW(), '" . $this->get('id') . "', '0', '" . mysql_real_escape_string( $uname ) . "', '0', 'I')";																																
					
																if ( mysql_query( $sql, $conn ) )
																{																
																	// lets try to wake the computer up!
																	wakeUp( $mac->getMACWithColon() );																			
																	@ftp_close($ftp); 					
																	@unlink( $tmp );								
																	return true;
																}
																else
																{
																	ftp_delete( $ftp, $this->FOGCore->getSetting("FOG_TFTP_PXE_CONFIG_DIR" ) . $mac->getMACImageReady() ); 									
																	$reason = mysql_error($conn);
																																		
																}
															}
															else
																$reason = "Unable to upload file.";
														}
														else
															$reason = "Unable to connect to tftp server."; 	
					
														@ftp_close($ftp); 					
														@unlink( $tmp );															
													}
													else
														$reason = "Host is already a member of a active task.";
												}	
												else
													$reason = "Failed to open tmp file.";												
											
												wakeUp( $mac->getMACWithColon() );
												return true;
												
											}		
											else
												$reason = "Primary MAC is invalid: " . $mac->getMACWithColon();
										}
										else
											$reason = "No primary MAC address found.";											
									}
									else
										$reason = "Unable to locate master node in storage group.";									
								}
								else
									$reason = "Storage group has no members.";
							}
							else 
								$reason = "Storage Group is null.";
						}
						else
							$reason = "Image path is null.";
					}
					else
						$reason = "Image is null.";					
					break;
				case strtoupper(FOGCore::TASK_PASSWORD_RESET):
					break;
				case strtoupper(FOGCore::TASK_ALL_SNAPINS):
					if ( $this->getImage() != null  )
					{
						if ( $this->getImage()->getPath() != null && strlen(trim($this->getImage()->getPath())) > 0 )
						{
							$storageGroup = $this->getImage()->getStorageGroup();
							if ( $storageGroup != null )
							{
								if ( $storageGroup->getMembers() != null && count( $storageGroup->getMembers() ) > 0  )
								{
									$masterNode = $storageGroup->getMasterStorageNode();
									if ( $masterNode != null )
									{
										$mac = $this->get('mac');							
										if ( $mac != null )
										{
											if ( $mac->isValid( ) )
											{
												cancelSnapinsForHost( $conn, $this->get('id') );
												deploySnapinsForHost( $conn, $this->get('id') );
												
												wakeUp( $mac->getMACWithColon() );
												return true;
											}		
											else
												$reason = "Primary MAC is invalid: " . $mac->getMACWithColon();
										}
										else
											$reason = "No primary MAC address found.";											
									}
									else
										$reason = "Unable to locate master node in storage group.";									
								}
								else
									$reason = "Storage group has no members.";
							}
							else 
								$reason = "Storage Group is null.";
						}
						else
							$reason = "Image path is null.";
					}
					else
						$reason = "Image is null.";											
					break;
				case strtoupper(FOGCore::TASK_SINGLE_SNAPIN):
					if ( $this->getImage() != null  )
					{
						if ( $this->getImage()->getPath() != null && strlen(trim($this->getImage()->getPath())) > 0 )
						{
							$storageGroup = $this->getImage()->getStorageGroup();
							if ( $storageGroup != null )
							{
								if ( $storageGroup->getMembers() != null && count( $storageGroup->getMembers() ) > 0  )
								{
									$masterNode = $storageGroup->getMasterStorageNode();
									if ( $masterNode != null )
									{
										$mac = $this->get('mac');							
										if ( $mac != null )
										{
											if ( $mac->isValid( ) )
											{
												if ( is_numeric($args2) )
												{
													//cancelSnapinsForHost( $conn, $this->get('id') );
													deploySnapinsForHost( $conn, $this->get('id'), trim($args2) );
												
													wakeUp( $mac->getMACWithColon() );
													return true;
												}
												else
													$reason = "Invalid snapin ID";
											}		
											else
												$reason = "Primary MAC is invalid: " . $mac->getMACWithColon();
										}
										else
											$reason = "No primary MAC address found.";											
									}
									else
										$reason = "Unable to locate master node in storage group.";									
								}
								else
									$reason = "Storage group has no members.";
							}
							else 
								$reason = "Storage Group is null.";
						}
						else
							$reason = "Image path is null.";
					}
					else
						$reason = "Image is null.";				
				
				
					break;
				case strtoupper(FOGCore::TASK_WAKE_ON_LAN):
					$mac = $this->get('mac');							
					if ( $mac != null )
					{
						if ( $mac->isValid( ) )
						{
							wakeUp( $mac->getMACWithColon() );
							return true;
						}
						else
							$reason = "MAC Address is not valid.";
					}
					else
						$reason = "MAC address is null";
					break;				
				default:
					$reason = "Unknown task type: " . $tasktype;
			}
		}
		else
			$reason = "Database connection was null.";
		return false;
	}
}