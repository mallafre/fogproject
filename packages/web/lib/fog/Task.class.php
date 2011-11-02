<?php

// Blackout - 10:59 AM 30/09/2011
class Task extends FOGController
{
	// Table
	protected $databaseTable = 'tasks';
	
	// Name -> Database field name
	protected $databaseFields = array(
		'id'			=> 'taskID',
		'name'			=> 'taskName',
		'checkInTime'		=> 'taskCheckIn',
		'hostID'		=> 'taskHostID',
		'state'			=> 'taskState',
		'createdTime'		=> 'taskCreateTime',
		'createdBy'		=> 'taskCreateBy',
		'isForced'		=> 'taskForce',
		'scheduledStartTime'	=> 'taskScheduledStartTime',
		'type'			=> 'taskType',
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
	protected $databaseFieldsRequired = array(
		'id',
		'type',
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
		$Host = new Host($this->get('hostID'));
		
		return $Host->getImage();
	}
	
	// Overrides
	public function isValid()
	{
		$Host = $this->getHost();
		$mac = $Host->get('mac');
		
		return ($Host->isValid() && $mac->isValid());
	}
	
	// Legacy
	const UPLOAD = 'u';
	const DOWNLOAD = 'd';
	const WIPE = 'w';
	const DEBUG = 'x';
	const MEMTEST = 'm';
	const TESTDISK = 't';
	const PHOTOREC = 'r';
	const MULTICAST = 'c';
	const VIRUS_SCAN = 'v';
	const INVENTORY = 'i';
	const PASSWORD_RESET = 'j';
	const ALL_SNAPINS = 's';
	const SINGLE_SNAPIN = 'l';
	const WAKEUP = 'o';

	const STATE_QUEUED = 0;
	const STATE_INPROGRESS = 1;
	const STATE_COMPLETE = 2;
	
	// Legacy: From ImageMember.class.php
	public function  getNFSRoot() 	{ return $this->getStorageNode()->get('path'); }
	public function  getNFSServer()	{ return $this->getStorageNode()->get('ip'); }
	public function  getImageID()		{ return $this->getHost()->getImage()->get('id'); }
	public function  getHostName()	{ return $this->getHost()->get('name'); }
	public function  getIPAddress()	{ return $this->getHost()->get('ip'); }
	public function  getImagePath()	{ return $this->getHost()->getImage()->get('path'); }
	public function  getMAC()		{ return $this->getHost()->get('mac'); }
	public function  getOSID()		{ return $this->getHost()->getOS()->get('id'); }
	public function  getImageType()	{ return $this->getImage()->get('type'); }
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
	public function getState()				{ return $this->get('state');	}
	public function setState($state)			{ return $this->set('state', $state);	}
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
	public function setTaskType($taskType)		{ return $this->set('type', $taskType);	}
	public function getCreateTime()			{ return new Date(strtotime($this->get('createdTime')));	}
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
		if ($this->get('type'))
		{
			return strtolower($this->get('type'));
		}
		
		return $this->get('type');
	}
	
	public function getStateText()
	{
		switch ($this->get('state'))
		{
			case Task::STATE_QUEUED:
				return 'Queued';
			case Task::STATE_INPROGRESS:
				return 'In progress';
			case Task::STATE_COMPLETE:
				return 'Complete';
			default:
				return 'unknown';
		}
	}
	
	public function getTaskTypeString()
	{
		switch (strtolower($this->get('type')))
		{
			case Task::UPLOAD;
				return 'Upload';
			case Task::DOWNLOAD;
				return 'Download';
			case Task::WIPE;
				return 'Wipe';
			case Task::DEBUG;
				return 'Debug';
			case Task::MEMTEST;
				return 'Memtest';
			case Task::TESTDISK;
				return 'Testdisk';
			case Task::PHOTOREC;
				return 'PhotoRec';
			case Task::MULTICAST;
				return 'Multicast';
			case Task::VIRUS_SCAN;
				return 'Virus Scan';
			case Task::INVENTORY;
				return 'Inventory';
			case Task::PASSWORD_RESET;
				return 'Pass Reset';
			case Task::ALL_SNAPINS;
				return 'All Snapins';
			case Task::WAKEUP;
				return 'Wake up';
			case Task::SINGLE_SNAPIN;
				return 'Single Snapin';
			default:
				return 'n/a';
		}
	}
}