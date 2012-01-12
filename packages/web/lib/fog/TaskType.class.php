<?php

// Blackout - 12:23 PM 8/01/2012
class TaskType extends FOGController
{
	// Table
	public $databaseTable = 'taskTypes';
	
	// Name -> Database field name
	public $databaseFields = array(
		'id'			=> 'ttID',
		'name'			=> 'ttName',
		'description'		=> 'ttDescription',
		'icon'			=> 'ttIcon',
		'kernelTemplate'	=> 'ttKernelTemplate',
		'type'			=> 'ttType',		// fog or user
		'isAdvanced'		=> 'ttIsAdvanced',
		'access'		=> 'ttIsAccess'		// both, host or group
	);
}