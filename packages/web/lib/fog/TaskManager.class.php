<?php

// Blackout - 11:31 AM 2/10/2011
class TaskManager extends FOGManagerController
{
	// Table
	public $databaseTable = 'tasks';
	
	// Search query
	public $searchQuery = 'SELECT * FROM tasks WHERE taskName LIKE "%${keyword}%"';

	// Legacy - remove when all updated
        function hasActiveTaskCheckedIn($taskid)
        {
		$Task = new Task($taskid);
		
		return ((strtotime($Task->get('checkInTime')) - strtotime($Task->get('createdTime'))) > 2);
	}

        public function getActiveTasks()
        {
		return (array)$this->find(array('stateID' => array(1, 2)));
	}
	
	public function getCountInFrontOfHost($storageGroup, $task)
	{
		$count = $this->DB->query("SELECT
						COUNT(*) AS count
					FROM
						tasks
					WHERE
						taskStateID = '1' AND
						taskTypeID in ('U', 'D') AND
						taskNFSGroupID = '%s' AND
						taskID < '%s' AND
						(UNIX_TIMESTAMP() - UNIX_TIMESTAMP(taskCheckIn)) < '%s'", 
						array(
							$this->DB->sanitize($storageGroup instanceof StorageGroup ? $storageGroup->get('id') : $storageGroup ),
							$this->DB->sanitize($task instanceof Task ? $task->get('id') : $task ),
							$GLOBALS['FOGCore']->getSetting( "FOG_CHECKIN_TIMEOUT" )
						  )
					)->fetch()->get('count');
		return ($count ? $count : 0);

	}
	
	public function getCountQueuedTasksByStorageGroup($storageGroup)
	{
		return $this->count(array(	
						'stateID'	=> 2,
						'typeID'	=> array('U', 'D'),
						'NFSGroupID'	=> $this->DB->sanitize($storageGroup instanceof StorageGroup ? $storageGroup->get('id') : $storageGroup )
					 )
				   );
	
	}
	
	public function getCountQueuedTasksByStorageNode($storageNode)
	{
		return $this->count(array(	
						'stateID'	=> 2,
						'typeID'	=> array('U', 'D'),
						'NFSGroupID'	=> $this->DB->sanitize($storageNode instanceof StorageNode ? $storageNode->get('id') : $storageNode )
					 )
				   );	
	}
	
	// LEGACY
	// NOTE: Dont use this, use $Host->getActiveTaskCount() instead
	public function getCountOfActiveTasksForHost($host)
	{
		return $this->count(array(	'stateID'	=> array(1, 2),
						'hostID'	=> ($host instanceof Host ? $host->get('id') : $host)
					)
				);
	}
	
	// NOTE: Dont use this, use $Host->getActiveTaskCount() instead
	function getCountOfActiveTasksWithMAC($mac)
	{
		$count = $this->DB->query("SELECT
						COUNT(*) AS count
					FROM
						tasks
					INNER JOIN
						hosts ON ( tasks.taskHostID = hostID )
					WHERE
						hostMAC = '%s' AND
						tasks.taskStateID IN (1,2)", array($mac))->fetch()->get('count');
		
		return ($count ? $count : 0);
	}
}
