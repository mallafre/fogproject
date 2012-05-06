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
		'storageNodes'
	);
	
	// Custom functions: Storage Group
	function __construct($data)
	{
		// Load row data and prepare class
		parent::__construct($data);
		
		// Update StorageNodes in StorageGroup
		$this->updateStorageNodes();
	}
	
	function updateStorageNodes()
	{
		// Variables
		$this->data['storageNodes'] = array();
		
		// Loop all Storage Nodes -> Push into data array
		foreach ((array)$this->FOGCore->getClass('StorageNodeManager')->find(array('isEnabled' => '1', 'storageGroupID' => $this->get('id'))) AS $StorageNode)
		{
			$this->add('storageNodes', $StorageNode);
		}
		
		//var_dump($this->FOGCore->getClass('StorageNodeManager')->find(array('isEnabled' => '1', 'storageGroupID' => $this->get('id'))));exit;
	}
	
	function getStorageNodes($index = null)
	{
		if ($index != null)
		{
			return $this->data['storageNodes'][$index];
		}
		else
		{
			return (array)$this->data['storageNodes'];
		}
	}
	
	function getTotalSupportedClients()
	{
		$clients = 0;
		foreach( $this->getStorageNodes() AS $node )
		{
			$clients += $node->get('maxClients');
		}
		return $clients;
	}
	
	function getMasterStorageNode()
	{
		// Return master
		foreach ($this->get('storageNodes') AS $StorageNode)
		{
			if ($StorageNode->get('isMaster'))
			{
				return $StorageNode;
			}
		}
		
		// Failed to find Master - return first Storage Node if there is one, otherwise false
		return (count($this->get('storageNodes')) ? current($this->get('storageNodes')) : false);
	}
	
	function getOptimalStorageNode()
	{
		$StorageNodes = $this->getStorageNodes();
		
		// Change this to count client connections -> Return based on that (instead of random)
		return (count($StorageNodes) ? $StorageNodes[rand(0, count($StorageNodes)-1)] : false);
	}
	
	function addMember($addStorageNode)
	{
		foreach ($this->getStorageNodes() AS $storageNode)
		{
			if ($storageNode->get('id') == $addStorageNode->get('id'))
			{
				return $this;
			}
		}
		
		$this->data['storageNodes'][] = $addStorageNode;
		
		return $this;
	}
	
	public function getUsedSlotCount()
	{
		return $this->FOGCore->getClass('TaskManager')->count(array(	'stateID'	=> 3,
										'typeID'	=> array(1, 8, 15, 2, 16),	// Upload + Download Tasks - TODO: DB lookup on TaskTypes -> Build Array
										'NFSGroupID'	=> $this->get('id')
									)
								);
	}
	
	// Legacy functions - remove once updated in other areas
	function getID() { return $this->get('id'); }
	function getName() { return $this->get('name'); }
	function getDescription() { return $this->get('description'); }
	function getMembers() { return $this->get('storageNodes'); }
}
