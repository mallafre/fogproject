<?php

// Blackout - 8:44 PM 23/09/2011
class StorageGroup extends FOGController
{
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
		
		// Query
		$this->db->query("SELECT * FROM nfsGroupMembers WHERE ngmGroupID='%s' AND ngmIsEnabled='1'", array($this->get('id')));
		
		// Loop all Storage Nodes -> Push into data array
		while ($storageNode = $this->db->fetch()->get())
		{
			$this->add('storageNodes', new StorageNode($storageNode));
		}
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
	
	function getMasterStorageNode()
	{
		foreach ($this->getStorageNodes() AS $storageNode)
		{
			if ($storageNode->isMaster())
			{
				return $storageNode;
			}
		}		
		
		return ($storageNode ? $storageNode : false);
	}
	
	function getRandomStorageNode()
	{
		$StorageNodes = $this->getStorageNodes();
		
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
	
	// Legacy functions - remove once updated in other areas
	function getID() { return $this->get('id'); }
	function getName() { return $this->get('name'); }
	function getDescription() { return $this->get('description'); }
	function getMembers() { return $this->get('storageNodes'); }
}