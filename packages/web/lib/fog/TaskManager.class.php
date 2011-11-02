<?php

// Blackout - 11:31 AM 2/10/2011
class TaskManager extends FOGManagerController
{
	// Table
	protected $databaseTable = 'tasks';
	
	// Search query
	protected $searchQuery = 'SELECT * FROM tasks WHERE taskName LIKE "%${keyword}%"';

	// Legacy - remove when all updated
        function hasActiveTaskCheckedIn($taskid)
        {
		$Task = new Task($taskid);
		
		return ((strtotime($Task->get('checkInTime')) - strtotime($Task->get('createdTime'))) > 2);
	}

        public function getActiveTasks()
        {
		return $this->find(array('taskState' => array('0', '1')));
	}
	
	// Move this to $Host->getTasks()
	public function getCountOfActiveTasksForHost($host)
	{
		return count(
			$this->find(
				array(
					'taskState'	=> array('0', '1'),
					'taskHostID'	=> ($host instanceof Host ? $host->get('id') : $host)
				)
			)
		);
		
		/*
			$sql = "SELECT count(*) as cnt FROM tasks WHERE taskHostID = '" . $this->db->sanitize( $host->getID() ) . "' and tasks.taskState in (0,1)";	
		*/
	}
	
	function getCountOfActiveTasksWithMAC($mac)
	{
		$count = $this->db->query("SELECT
						COUNT(*) AS count
					FROM
						tasks
					INNER JOIN
						hosts ON ( tasks.taskHostID = hostID )
					WHERE
						hostMAC = '%s' AND
						tasks.taskState IN (0,1)", array($mac))->fetch()->get('count');
		
		return ($count ? $count : 0);
	}
}