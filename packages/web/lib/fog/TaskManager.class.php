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
	
	// I know, I know this needs to be cleaned up : )
	public function getQueuedTasksByStorageGroup($storageGroupId)
	{
		if ( $this->DB != null )
		{
			$sql = "SELECT 
					taskID
				FROM
					tasks
				WHERE 
					taskStateID = '2' and
					taskTypeID in ( 'U', 'D' ) and
					taskNFSGroupID = '" . $this->DB->sanitize($storageGroupId) . "'";
					
			$tasks = array();	
			if ( $this->DB->query($sql) )
			{
				while( $ar = $this->DB->fetch()->get() )
				{
					$tasks[] = new Task($ar["taskID"]);
				}	
			}
			
			$tasks;
			return $arHosts;
								
		}
		return null;
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
