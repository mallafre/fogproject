<?php

// Blackout - 5:54 PM 23/09/2011
class Group extends FOGController
{
	// Table
	public $databaseTable = 'groups';
	
	// Name -> Database field name
	public $databaseFields = array(
		'id'		=> 'groupID',
		'name'		=> 'groupName',
		'description'	=> 'groupDesc',
		'createdBy'	=> 'groupCreateBy',
		'createdTime'	=> 'groupDateTime',
		'building'	=> 'groupBuilding',
		'kernel'	=> 'groupKernel',
		'kernelArgs'	=> 'groupKernelArgs',
		'kernelDevice'	=> 'groupPrimaryDisk'
	);
	
	// Allow setting / getting of these additional fields
	public $additionalFields = array(
		'hosts'
	);
	
	// Custom Variables
	private $hostsLoaded = false;
	
	// Legacy - remove when fully converted
	private $id, $name, $description, $createTime, $createdBy, $building, $hosts, $kernel, $kernelArgs, $primaryDisk;
	public $lastError;
	
	// Overrides
	public function save()
	{
		// Save row data
		parent::save();
		
		// Which fields to update in the Host object
		$updateInHost = array('kernel', 'kernelArgs', 'kernelDevice');
		
		// Iterate update array -> Update fields -> Save Host
		foreach ((array)$this->get('hosts') AS $Host)
		{
			foreach ($updateInHost AS $field)
			{
				$Host->set($field, $this->get($field));
			}
			
			// Save Host
			$Host->save();
		}
		
		// Return		
		return $this;
	}
	
	public function get($key = '')
	{
		if ($this->key($key) == 'hosts' && !$this->hostsLoaded)
		{
			$this->updateHosts();
			
			$hostsLoaded = true;
		}
		
		// Get
		return parent::get($key);
	}
	
	// Host related functions
	public function getHostCount()
	{
		return (is_array($this->getHosts()) ? count($this->getHosts()) : 0);
	}
	
	public function getHosts()
	{
		return $this->get('hosts');
	}
	
	function removeHost($removeHost)
	{
		foreach ((array)$this->get('hosts') AS $host)
		{
			if ($host->get('id') != $removeHost->get('id'))
			{
				$newHostArray[] = $host;
			}
		}
		
		$this->set('hosts', (array)$newHostArray);
		
		return $this;
	}
	
	function updateHosts()
	{
		// Reset hosts
		$this->set('hosts', array());
		
		// Find all group members
		$this->DB->query("SELECT hosts.* FROM groups INNER JOIN groupMembers ON ( groups.groupID = groupMembers.gmGroupID ) LEFT JOIN hosts ON (groupMembers.gmHostID = hosts.hostID) WHERE groupID='%s' ORDER BY groupName", array($this->get('id')));
		
		// Iterate group member data -> Create new Host object using data
		while ($host = $this->DB->fetch()->get())
		{
			$this->add('hosts', new Host($host));
		}
		
		// Return
		return $this;
	}
	
	// LEGACY
	function getID()
	{
		return $this->get('id');
	}
	
	function setID($id)
	{
		return $this->set('id', $id);
	}
	
	function getName()
	{
		return $this->get('name');
	}
	
	function setName($name)
	{
		return $this->set('name', $name);
	}
	
	function getDescription()
	{
		return $this->get('description');
	}
	
	function setDescription($description)
	{
		return $this->set('description', $description);
	}
	
	function doMembersHaveUniformImages()
	{
		$members = $this->getHosts();	
		if ( $members != null && count( $members ) > 0 )
		{
			$imgid = -99999999;
			for ( $i = 0; $i < count( $members ); $i++ )
			{
				$member = $members[$i];
				if ( $member != null )
				{
					$image = $member->getImage();
					if ( $image != null )
					{
						if ( $i == 0 )
						{
							$imgid = $image->get('id');
							if ( $imgid < 0 ) return false;
							if ( ! is_numeric($imgid ) ) return false;
						}
						else 
						{
							if ( $imgid != $image->get('id') )
								return false;
						}
					}
					else
						return false;
				}
			}
			return true;
		}
		return false;
	}
	
	function doMembersHaveUniformOS()
	{
		$members = $this->getHosts();
		if ( $members != null && count( $members ) > 0 )
		{
			$osid = -99999999;
			for ( $i = 0; $i < count( $members ); $i++ )
			{
				$member = $members[$i];
				if ( $member != null )
				{
					if ( $i == 0 )
						$osid = $member->getOSID();
					else 
					{
						if ( $osid != $member->getOSID() )
							return false;
					}
				}
			}
			return true;
		}
		return false;
	}
}