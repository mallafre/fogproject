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
		'maxclients'	=> 'ngmMaxClients',
		'user'		=> 'ngmUser',
		'pass'		=> 'ngmPass',
		'key'		=> 'ngmKey',
		// TODO: Add interface
		'interface'	=> 'ngmInterface'
	);
	
	// Allow setting / getting of these additional fields
	public $additionalFields = array(
		
	);
	
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

	// Legacy functions - remove once updated in other areas
	function getID() { return $this->get('id'); }
	function getName() { return $this->get('name'); }
	function getDescription() { return $this->get('description'); }
	function getRoot() { return $this->get('path'); }
	function getHostIP() { return $this->get('ip'); }
	function getMaxClients() { return $this->get('maxclients'); }	
	function getUser() { return $this->get('user'); }
	function getPass() { return $this->get('pass'); }	
}