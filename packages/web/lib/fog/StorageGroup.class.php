<?php

// Blackout - 8:44 PM 23/09/2011
class StorageGroup extends FOGController
{
	// Debug & Info
	public $debug = true;
	public $info = false;
	
	// Table
	public $databaseTable = 'nfsGroups';
	
	// Name -> Database field name
	public $databaseFields = array(
		'id'		=> 'ngID',
		'name'		=> 'ngName',
		'description'	=> 'ngDesc'
	);
	
	// Allow setting / getting of these additional fields
	public $additionalFields = array(
	);
	
	// Custom functions: Storage Group
	function getStorageNodes()
	{
		return (array)$this->FOGCore->getClass('StorageNodeManager')->find(array('isEnabled' => '1', 'storageGroupID' => $this->get('id')));
	}
	
	function getTotalSupportedClients()
	{
		foreach ($this->getStorageNodes() AS $StorageNode)
		{
			$clients += $StorageNode->get('maxClients');
		}
		
		return ($clients ? $clients : 0);
	}
	
	function getMasterStorageNode()
	{
		// Return master
		foreach ($this->getStorageNodes() AS $StorageNode)
		{
			if ($StorageNode->get('isMaster'))
			{
				return $StorageNode;
			}
		}
		
		// Failed to find Master - return first Storage Node if there is one, otherwise false
		return (count($this->getStorageNodes()) ? current($this->getStorageNodes()) : false);
	}
	
	function getOptimalStorageNode()
	{
		$StorageNodes = $this->getStorageNodes();
		
		// Change this to count client connections -> Return based on that (instead of random)
		return (count($StorageNodes) ? $StorageNodes[rand(0, count($StorageNodes)-1)] : false);
	}
	
	public function getUsedSlotCount()
	{
		return $this->FOGCore->getClass('TaskManager')->count(array(	'stateID'	=> 3,
										'typeID'	=> array(1, 8, 15, 2, 16),	// Upload + Download Tasks - TODO: DB lookup on TaskTypes -> Build Array
										'NFSGroupID'	=> $this->get('id')
									)
								);
	}
}
