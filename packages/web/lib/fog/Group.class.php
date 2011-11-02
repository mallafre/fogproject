<?php

// Blackout - 5:54 PM 23/09/2011
class Group extends FOGController
{
	// Table
	protected $databaseTable = 'groups';
	
	// Name -> Database field name
	protected $databaseFields = array(
		'id'		=> 'groupID',
		'name'		=> 'groupName',
		'description'	=> 'groupDesc',
		'createdBy'	=> 'groupCreateBy',
		'createdTime'	=> 'groupDateTime',
		'building'	=> 'groupBuilding',
		'kernel'	=> 'groupKernel',
		'kernelArgs'	=> 'groupKernelArgs',
		'primaryDisk'	=> 'groupPrimaryDisk'
	);
	
	// Allow setting / getting of these additional fields
	protected $additionalFields = array(
		'hosts'
	);
	
	// Legacy - remove when fully converted
	private $id, $name, $description, $createTime, $createdBy, $building, $hosts, $kernel, $kernelArgs, $primaryDisk;
	public $lastError;
	
	// Overrides
	public function __construct($data)
	{
		// Construct
		parent::__construct($data);
	}
	
	public function save()
	{
		// Save row data
		parent::save();
		
		// Update Hosts in Group
		// TODO: Enable saving of Host data via Group when Host has been rewritten
		/*
		foreach ((array)$this->get('hosts') AS $hostMember)
		{
			$hostMember	->set('kernel',		$this->get('kernel'))
					->set('kernelArgs',	$this->get('kernelArgs'))
					->set('primaryDisk',	$this->get('primaryDisk'))
					->save();
		}
		*/
		
		return $this;
	}
	
	public function get($key)
	{
		if ($this->key($key) == 'hosts')
		{
			$this->updateHosts();
		}
		
		// Get
		return $this->get($key);
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
		
		// Query group members
		//var_dump($this->db);exit;
		
		$this->db->query("SELECT * FROM groups INNER JOIN groupMembers ON ( groups.groupID = groupMembers.gmGroupID ) WHERE groupID='%s'", array($this->get('id')));
		while ($host = $this->db->fetch()->get())
		{
			$this->add('hosts', new Host($host['gmHostID']));
		}
		
		return $this;
	}
	
	// Remove these
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
	
	// Rewrite this
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
	
	public function startTask($conn, $tasktype, $blShutdown, $args1=null, $args2=null, $args3=null, $args4=null, $args5=null, &$reason)
	{
		$reason = '';
		
		try
		{
			if ($conn == null)
			{
				throw new Exception('Database connection was null');
			}
			
			switch (strtoupper($tasktype))
			{
				case strtoupper(FOGCore::TASK_MULTICAST):
					if ($this->getHostCount() == 0)
					{
						throw new Exception('No hosts present in group');
					}
					
					if (!$this->doMembersHaveUniformOS())
					{
						throw new Exception('Not all hosts have the same operating system association');
					}
					
					if (!$this->doMembersHaveUniformImages())
					{
						throw new Exception('Not all hosts have the same Image');
					}
					
					$t = $this->getHosts();
					$templateHost = $t[0];
					
					if ($templateHost == null)
					{
						throw new Exception('Template host is null');
					}
					
					$templateImage = $templateHost->getImage();
					
					if ($templateImage == null)
					{
						throw new Exception('Template image is null');
					}
					
					$templateSG = $templateImage->getStorageGroup();
					
					if ($templateSG == null)
					{
						throw new Exception('Template storage group is null');
					}
					
					// get port base
					$port = getMulticastPort( $conn );
					
					if ($port === -1)
					{
						throw new Exception('Unable to determine port base for multicast');
					}
					
					$mcId = createMulticastJob( $conn, "Scheduled Task: " . $this->getName(), $port, $templateImage->getPath(), null, $templateImage->getImageType(), $templateSG->getID() );
					
					if (!is_numeric($mcId) || $mcId != 0)
					{
						throw new Exception('Unable to create a multicast job entry');
					}
					
					$hosts = $this->getHosts();
					$suc = 0;
					$fail = 0;
					$hostoutput = "";
					for( $i = 0; $i < count( $hosts ); $i++ )
					{
						$host = $hosts[$i];
						if ( $host != null )
						{
							// arg1 = port
							// arg2 = job id
							$ireason;
							if ( $host->startTask($conn, $tasktype, $blShutdown, $port, $mcId, null, null, null, &$ireason) )
							{
								$suc++;
								$hostoutput .= $host->getHostName() . ": " . $ireason . "\n";
							}
							else
							{
								$fail++;
								$hostoutput .= $host->getHostName() . ": " . $ireason . "\n";
							}
						}
					}
					
					if ($suc <= 0)
					{
						throw new Exception($hostoutput . "\nNo hosts were added to multicast task!");
					}
					
					if ( activateMulticastJob( $conn, $mcId ) )
					{
						$this->lastError = $hostoutput . "\n" . "=============================================" . "\nResult: " . $suc . " of " . ($suc + $fail) . " clients were added to the task.";
						
						return true;
					}
					else
					{
						throw new Exception('Failed to active task!');
					}
					
					
					break;
				default:
				
					throw new Exception('Unsupported task at group level');
					break;
			}
		}
		catch (Exception $e)
		{
			$this->lastError = $e->getMessage();
		}
		
		return false;		
	}
	// END Rewrite this
}