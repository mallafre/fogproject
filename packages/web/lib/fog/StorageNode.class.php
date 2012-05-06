<?php

// Blackout - 9:09 PM 23/09/2011
class StorageNode extends FOGController
{
	// Table
	public $databaseTable = 'nfsGroupMembers';
	
	// Name -> Database field name
	public $databaseFields = array(
		'id'		=> 'ngmID',
		'name'		=> 'ngmMemberName',
		'description'	=> 'ngmMemberDescription',
		'isMaster'	=> 'ngmIsMasterNode',
		'storageGroupID'=> 'ngmGroupID',
		'isEnabled'	=> 'ngmIsEnabled',
		'isGraphEnabled'=> 'ngmGraphEnabled',
		'path'		=> 'ngmRootPath',
		'ip'		=> 'ngmHostname',
		'maxClients'	=> 'ngmMaxClients',
		'user'		=> 'ngmUser',
		'pass'		=> 'ngmPass',
		'key'		=> 'ngmKey',
		'interface'	=> 'ngmInterface'
	);
	
	// Required database fields
	public $databaseFieldsRequired = array(
		'ip',
		'path'
	);
	
	// Overrides
	public function get($key = '')
	{
		// Path: Always remove trailing slash on NFS path
		if ($this->key($key) == 'path')
		{
			return rtrim(parent::get($key), '/');
		}
		
		// FOGController get()
		return parent::get($key);
	}
	
	// Custom functions
	function isMaster()
	{
		return $this->get('isMaster');
	}
	
	function isEnabled()
	{
		return $this->get('isEnabled');
	}
	
	function getStorageGroup()
	{
		return new StorageGroup($this->get('storageGroupID'));
	}
	
	function getNodeFailure($Host)
	{
		$NodeFailures = $this->FOGCore->getClass('NodeFailureManager')->find(array(	'storageNodeID'	=> $this->get('id'), 
												'hostID'	=> $this->DB->sanitize($Host instanceof Host ? $Host->get('id') : $Host)
											)
										);

		return (count($NodeFailures) ? $NodeFailures[0] : null);
	}
	
	public function getUsedSlotCount()
	{
		return $this->FOGCore->getClass('TaskManager')->count(array(	'stateID'	=> 3,
										'typeID'	=> array(1, 8, 15, 2, 16),	// Upload + Download Tasks - TODO: DB lookup on TaskTypes -> Build Array
										'NFSMemberID'	=> $this->get('id')
									)
								);
	}

	// Legacy functions - remove once updated in other areas
	function getID() { return $this->get('id'); }
	function getName() { return $this->get('name'); }
	function getDescription() { return $this->get('description'); }
	function getRoot() { return $this->get('path'); }
	function getHostIP() { return $this->get('ip'); }
	function getMaxClients() { return $this->get('maxClients'); }	
	function getUser() { return $this->get('user'); }
	function getPass() { return $this->get('pass'); }	
}
