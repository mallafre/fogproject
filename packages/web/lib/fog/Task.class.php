<?php

// Blackout - 10:59 AM 30/09/2011
class Task extends FOGController
{
	// Table
	public $databaseTable = 'tasks';
	
	// Name -> Database field name
	public $databaseFields = array(
		'id'			=> 'taskID',
		'name'			=> 'taskName',
		'checkInTime'		=> 'taskCheckIn',
		'hostID'		=> 'taskHostID',
		'stateID'		=> 'taskStateID',
		'createdTime'		=> 'taskCreateTime',
		'createdBy'		=> 'taskCreateBy',
		'isForced'		=> 'taskForce',
		'scheduledStartTime'	=> 'taskScheduledStartTime',
		'typeID'		=> 'taskTypeID',
		'pct'			=> 'taskPCT',
		'bpm'			=> 'taskBPM',
		'timeElapsed'		=> 'taskTimeElapsed',
		'timeRemaining'		=> 'taskTimeRemaining',
		'dataCopied'		=> 'taskDataCopied',
		'percent'		=> 'taskPercentText',
		'dataTotal'		=> 'taskDataTotal',
		'NFSGroupID'		=> 'taskNFSGroupID',
		'NFSMemberID'		=> 'taskNFSMemberID',
		'NFSFailures'		=> 'taskNFSFailures',
		'NFSLastMemberID'	=> 'taskLastMemberID'
	);
	
	// Required database fields
	public $databaseFieldsRequired = array(
		'id',
		'typeID',
		'hostID',
		'NFSGroupID',
		'NFSMemberID'
	);
	
	// Custom Functions
	public function getHost()
	{
		return new Host($this->get('hostID'));
	}
	
	public function getStorageGroup()
	{
		return new StorageGroup($this->get('NFSGroupID'));
	}
	
	public function getStorageNode()
	{
		return new StorageNode($this->get('NFSMemberID'));
	}
	
	public function getImage()
	{
		return $this->getHost()->getImage();
	}
	
	public function getInFrontOfHostCount()
	{
		$count = $this->DB->query("SELECT
						COUNT(*) AS count
					FROM
						tasks
					WHERE
						taskStateID IN (1, 2) AND
						taskTypeID IN (%s) AND
						taskNFSGroupID = '%s' AND
						taskID < '%s' AND
						(UNIX_TIMESTAMP() - UNIX_TIMESTAMP(taskCreateTime)) < '%s'",
						array(
							'1', 	// Download only - TODO: DB lookup on TaskTypes -> Build Array
							$this->get('NFSGroupID'),
							$this->get('id'),
							$this->FOGCore->getSetting('FOG_CHECKIN_TIMEOUT')
						  )
					)->fetch()->get('count');
		return ($count ? $count : 0);

	}
	
	public function removePXEFile()
	{
		// Remove PXE file
		if ($this->getHost()->isValid())
		{
			// FTP: Connect
			$this->FOGFTP	->set('host', 		$this->FOGCore->getSetting('FOG_TFTP_HOST'))
					->set('username',	$this->FOGCore->getSetting('FOG_TFTP_FTP_USERNAME'))
					->set('password',	$this->FOGCore->getSetting('FOG_TFTP_FTP_PASSWORD'))
					->connect()
			// FTP: Delete
					->delete(rtrim($this->FOGCore->getSetting('FOG_TFTP_PXE_CONFIG_DIR'), '/') . '/' . $this->getHost()->getMACAddress()->getMACPXEPrefix())
			// FTP: Close
					->close();
		}
		
		return $this;
	}
	
	public function cancel()
	{
		// Set State to User Cancelled
		$this->set('stateID', '5')->save();
		
		// Remove PXE File -> Return
		return $this->removePXEFile();
	}
	
	// Overrides
	public function set($key, $value)
	{
		// Check in time: Convert Unix time to MySQL datetime
		if ($this->key($key) == 'checkInTime' && is_numeric($value) && strlen($value) == 10)
		{
			$value = date('Y-m-d H:i:s', $value);
		}
		
		// Return
		return parent::set($key, $value);
	}
	
	public function destroy($field = 'id')
	{
		// Remvoe PXE File
		$this->removePXEFile();

		// TODO 
		// Snapins: Cancel snapin tasks
		
		
		// FOGController destroy
		return parent::destroy($field);
	}

	// Task State ID Constants - required for now
	const STATE_QUEUED = 1;
	const STATE_INPROGRESS = 2;
	const STATE_COMPLETE = 3;
	
	// Task Type ID Constants - required for now
	const TYPE_DOWNLOAD = 1;		// Old: d
	const TYPE_UPLOAD = 2;			// Old: u
	const TYPE_DEBUG = 3;			// Old: x
	const TYPE_WIPE = 4;			// Old: w
	const TYPE_MEMTEST = 5;			// Old: m
	const TYPE_TESTDISK = 6;		// Old: t
	const TYPE_PHOTOREC = 7;		// Old: r
	const TYPE_MULTICAST = 8;		// Old: c
	const TYPE_VIRUS_SCAN = 9;		// Old: v
	const TYPE_INVENTORY = 10;		// Old: i
	const TYPE_PASSWORD_RESET = 11;		// Old: j
	const TYPE_ALL_SNAPINS = 12;		// Old: s
	const TYPE_SINGLE_SNAPIN = 13;		// Old: l
	const TYPE_WAKEUP = 14;			// Old: o
	
	// Legacy: From ImageMember.class.php
	public function  getNFSRoot() 	{ return $this->getStorageNode()->get('path'); }
	public function  getNFSServer()	{ return $this->getStorageNode()->get('ip'); }
	public function  getImageID()		{ return $this->getHost()->getImage()->get('id'); }
	public function  getHostName()	{ return $this->getHost()->get('name'); }
	public function  getIPAddress()	{ return $this->getHost()->get('ip'); }
	public function  getImagePath()	{ return $this->getHost()->getImage()->get('path'); }
	public function  getMAC()		{ return $this->getHost()->get('mac'); }
	public function  getOSID()		{ return $this->getHost()->getOS()->get('id'); }
	public function  getImageTypeID()	{ return $this->getImage()->get('imageTypeID'); }
	public function  getKernel()		{ return $this->getHost()->get('kernel'); }
	public function  getDevice()		{ return $this->getHost()->get('kernelDevice'); }
	public function  getMACColon()	{ return $this->getHost()->get('mac'); }
	public function  getMACDash() 	{ return $this->getHost()->get('mac')->getMACWithDash(); }
	public function  getMACImageReady()	{ return '01-' . $this->getMACDash(); }
	public function  getBuilding()	{ return $this->getHost()->get('building'); }
	public function  getIsForced()	{ return $this->get('isForced'); }
	public function  getKernelArgs()	{ return $this->getHost()->get('kernelArgs'); }

	// Legacy: From Task.class.php
	public function getId()				{ return $this->get('id');	}
	public function setId($id)				{ return $this->set('id', $id);	}
	public function getHostId()				{ return $this->get('hostID');	}
	public function setHostId($hostId)			{ return $this->set('hostID', $id);	}
	public function getState()				{ return $this->get('stateID');	}
	public function setState($state)			{ return $this->set('stateID', $state);	}
	public function getNfsGroupId()			{ return $this->get('NFSGroupID');	}
	public function setNfsGroupId($nfsGroupId)		{ return $this->set('NFSGroupID', $nfsGroupId);	}
	public function getNfsMemberId()			{ return $this->get('NFSMemberID');	}
	public function setNfsMemberId($nfsMemberId)		{ return $this->set('NFSMemberID', $nfsMemberId);	}
	public function getNfsFailures()			{ return $this->get('NFSFailures');	}
	public function setNfsFailures($nfsFailures)		{ return $this->set('NFSFailures', $nfsFailures);	}
	public function getNfsLastMemberId()			{ return $this->get('NFSLastMemberID');	}
	public function setNfsLastMemberId($nfsLastMemberId)	{ return $this->set('NFSLastMemberID', $nfsLastMemberId);	}
	public function getName()				{ return $this->get('name');	}
	public function setName($name)			{ return $this->set('name', $name);	}
	public function setTaskType($taskType)		{ return $this->set('typeID', $taskType);	}
	public function getCreateTime()			{ return new Date($this->get('createdTime'));	}
	public function setCreateTime($createTime)		{ return $this->set('createdTime', $createTime);	}
	public function getCheckinTime()			{ return $this->get('checkInTime');	}
	public function setCheckinTime($checkinTime)		{ return $this->set('checkInTime', $checkinTime);	}
	public function getScheduledStartTime()		{ return $this->get('scheduledStartTime');	}
	public function setScheduledStartTime($time)		{ return $this->set('scheduledStartTime', $time);	}
	public function getCreator()				{ return $this->get('createdBy');	}
	public function setCreator($creator)			{ return $this->set('createdBy', $creator);	}
	public function isForced()				{ return $this->get('isForced');	}
	public function setForced($forced)			{ return $this->set('isForced', $forced);	}
	public function getPercent()				{ return $this->get('percent');	}
	public function setPercent($percent)			{ return $this->set('percent', $percent);	}
	public function getTransferRate()			{ return $this->get('bpm');	}
	public function setTransferRate($transferRate)	{ return $this->set('bpm', $transferRate);	}
	public function getTimeElapsed()			{ return $this->get('timeElapsed');	}
	public function setTimeElapsed($timeElapsed)		{ return $this->set('timeElapsed', $timeElapsed);	}
	public function getTimeRemaining()			{ return $this->get('timeRemaining');	}
	public function setTimeRemaining($timeRemaining) 	{ return $this->set('timeRemaining', $timeRemaining);	}
	public function getDataCopied()			{ return $this->get('dataCopied');	}
	public function setDataCopied($dataCopied)		{ return $this->set('dataCopied', $dataCopied);	}
	public function getTaskPercentText()			{ return $this->get('percent');	}
	public function setTaskPercentText($taskPercentText)	{ return $this->set('percent', $taskPercentText);	}
	public function getTaskDataTotal()			{ return $this->get('dataTotal');	}
	public function setTaskDataTotal($taskDataTotal) 	{ return $this->set('dataTotal', $taskDataTotal); }
	
	
	public function setHost($Host)
	{
		if ($Host instanceof Host)
		{
			$this->set('hostID', $Host->get('id'));
		}
		else
		{
			$this->set('hostID', $Host);
		}

		return $this;
	}
	
	public function hasTransferData()
	{
		return $this->getPercent() != '' && strlen( trim($this->getPercent() ) ) > 0 &&
			$this->getTransferRate() != '' && strlen( trim($this->getTransferRate() ) ) > 0 &&
			$this->getTimeElapsed() != '' && strlen( trim($this->getTimeElapsed() ) ) > 0 &&
			$this->getTimeRemaining() != '' && strlen( trim($this->getTimeRemaining() ) ) > 0 &&
			$this->getDataCopied() != '' && strlen( trim($this->getDataCopied() ) ) > 0 &&
			$this->getTaskPercentText() != '' && strlen( trim($this->getTaskPercentText() ) ) > 0 &&
			$this->getTaskDataTotal() != '' && strlen( trim($this->getTaskDataTotal() ) ) > 0;
	}
	
	public function getTaskType()
	{
		return new TaskType($this->get('typeID'));
	}
	
	public function getTaskTypeText()
	{
		return (string)($this->getTaskType()->get('name') ? $this->getTaskType()->get('name') : _('Unknown'));
	}
	
	public function getTaskState()
	{
		return new TaskState($this->get('stateID'));
	}
	
	public function getTaskStateText()
	{
		return (string)($this->getTaskState()->get('name') ? $this->getTaskState()->get('name') : _('Unknown'));
	}
}
