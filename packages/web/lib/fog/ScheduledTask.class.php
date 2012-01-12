<?php

// Blackout - 9:56 AM 28/12/2011
class ScheduledTask extends FOGController
{
	// Table
	public $databaseTable = 'scheduledTasks';
	
	// Name -> Database field name
	public $databaseFields = array(
		'id'		=> 'stID',
		'name'		=> 'stName',
		'description'	=> 'stDesc',
		'type'		=> 'stType',
		'stTaskTypeID'	=> 'stTaskTypeID',
		'minute'	=> 'stMinute',
		'hour'		=> 'stHour',
		'dayOfMonth'	=> 'stDOM',
		'month'		=> 'stMonth',
		'dayOfWeek'	=> 'stDOW',
		'isGroupTask'	=> 'stIsGroup',
		'hostID'	=> 'stGroupHostID',
		'shutdown'	=> 'stShutDown',
		'other1'	=> 'stOther1',
		'other2'	=> 'stOther2',
		'other3'	=> 'stOther3',
		'other4'	=> 'stOther4',
		'other5'	=> 'stOther5',
		'scheduleTime'	=> 'stDateTime',
		'isActive'	=> 'stActive'
	);
	
	// Allow setting / getting of these additional fields
	public $additionalFields = array(
	);
	
	// Database field to Class relationships
	public $databaseFieldClassRelationships = array(
	);
	
	// Custom Functions
	public function getHost()
	{
		return new Host($this->get('hostID'));
	}
	
	public function getImage()
	{
		return $this->getHost()->getImage();
	}
	
	// LEGACY
	const TASK_TYPE_SINGLE 	= "S";
	const TASK_TYPE_CRON 	= "C";	
}