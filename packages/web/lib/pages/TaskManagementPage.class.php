<?php

// Blackout - 10:50 AM 13/12/2011
class TaskManagementPage extends FOGPage
{
	// Base variables
	var $name = 'Task Management';
	var $node = 'tasks';
	var $id = 'id';
	
	// Menu Items
	var $menu = array(
		
	);
	var $subMenu = array(
		
	);
	
	// Pages
	public function index()
	{
		$this->active();
	}
	
	// Active Tasks
	public function active()
	{
		// Set title
		$this->title = _('Active Tasks');
		
		// Header row
		$this->headerData = array(
			_('Hostname<br><small>MAC</small>'),
			_('Start Time'),
			_('Status'),
			_('Actions'),
		);
		
		// Row templates
		$this->templates = array(
			'${details_taskname}<p><a href="?node=host&sub=edit&id=${host_id}" title="' . _('Edit Host') . '">${host_name}</a></p><small>${host_mac}</small>',
			'<small>${task_time}</small>',
			'<span class="icon icon-${icon_state}" title="${task_state}"></span> <span class="icon icon-${icon_type}" title="${task_type}"></span>',
			'${details_taskforce} <a href="?node=tasks&sub=cancel-task&id=${task_id}"><span class="icon icon-kill" title="' . _('Cancel Task') . '"></span></a>',
		);
		
		// Row attributes
		$this->attributes = array(
			array(),
			array('width' => 110, 'class' => 'c'),
			array('width' => 50, 'class' => 'c'),
			array('width' => 50, 'class' => 'r')
		);
		
		// Tasks
		foreach ($this->FOGCore->getClass('TaskManager')->getActiveTasks() AS $Task)
		{
			$this->data[] = array(
				'task_id'	=> $Task->get('id'),
				'task_name'	=> $Task->get('name'),
				'task_time'	=> $Task->getCreateTime()->toFormatted(),
				//'task_state'	=> ($Task->get('stateID') == Task::STATE_QUEUED && $this->FOGCore->getClass('TaskManager')->hasActiveTaskCheckedIn($Task->get('id')) ? 'In Line' : $Task->getTaskStateText()),
				'task_state'	=> $Task->getTaskStateText(),
				'task_forced'	=> ($Task->get('isForced') ? '1' : '0'),
				'task_type'	=> $Task->getTaskTypeText(),
				
				// TODO: Move this to template logic
				'details_taskname'	=> ($Task->get('name')	? sprintf('<div class="task-name">%s</div>', $Task->get('name')) : ''),
				'details_taskforce'	=> ($Task->get('isForced') ? sprintf('<span class="icon icon-forced" title="%s"></span>', _('Task forced to start')) : ($Task->get('typeID') == 1 || $Task->get('typeID') == 2 ? sprintf('<a href="?node=tasks&sub=force-task&id=%s"><span class="icon icon-force" title="%s"></span></a>', $Task->get('id'), _('Force task to start')) : '&nbsp;')),
				
				'host_id'	=> $Task->get('hostID'),
				'host_name'	=> $Task->getHost()->get('name'),
				'host_mac'	=> $Task->getHost()->get('mac')->__toString(),
				
				'icon_state'	=> strtolower(str_replace(' ', '', $Task->getTaskStateText())),
				'icon_type'	=> strtolower(preg_replace(array('#[[:space:]]+#', '#[^\w-]#', '#\d+#', '#-{2,}#'), array('-', '', '', '-'), $Task->getTaskTypeText())),
			);
		}
		
		// Hook
		$this->HookManager->processEvent('TASK_DATA', array('headerData' => &$this->headerData, 'data' => &$this->data, 'templates' => &$this->templates, 'attributes' => &$this->attributes));
		
		// Output
		$this->render();
	}
	
	// Active Tasks - Force Task Start
	public function force_task()
	{
		// Find
		$Task = new Task($this->REQUEST['id']);
		
		// Hook
		$this->HookManager->processEvent('TASK_FORCE', array('Task' => &$Task));
		
		// Force
		try
		{
			$result['success'] = $Task->set('isForced', '1')->save();
		}
		catch (Exception $e)
		{
			$result['error'] = $e->getMessage();
		}
		
		// Output
		if ($this->FOGCore->isAJAXRequest())
		{
			print json_encode($result);
		}
		else
		{
			if ($result['error'])
			{
				$this->fatalError($result['error']);
			}
			else
			{
				$this->FOGCore->redirect(sprintf('?node=%s', $this->node));
			}
		}
	}
	
	// Active Tasks - Cancel Task
	public function cancel_task()
	{
		// Find
		$Task = new Task($this->REQUEST['id']);
		
		// Hook
		$this->HookManager->processEvent('TASK_CANCEL', array('Task' => &$Task));
		
		// Force
		try
		{
			// Cencel task - will throw Exception on error
			$Task->cancel();
		
			// Success
			$result['success'] = true;
		}
		catch (Exception $e)
		{
			// Failure
			$result['error'] = $e->getMessage();
		}
		
		// Output
		if ($this->FOGCore->isAJAXRequest())
		{
			print json_encode($result);
		}
		else
		{
			if ($result['error'])
			{
				$this->fatalError($result['error']);
			}
			else
			{
				$this->FOGCore->redirect(sprintf('?node=%s', $this->node));
			}
		}
	}
	
	public function active_multicast()
	{
		// Set title
		$this->title = _('Active Multi-cast Tasks');
		
		// Header row
		$this->headerData = array(
			_('Task Name'),
			_('Hosts'),
			_('Start Time'),
			_('State'),
			_('Status'),
			_('Kill')
		);
		
		// Row templates
		$this->templates = array(
			'%name%',
			'%count%',
			'%start_date%',
			'%state%',
			'%percent%',
			'<a href="?node=tasks&sub=remove-multicast-task&id=${id}"><span class="icon icon-kill" title="Kill Task"></span></a>'
		);
		
		// Row attributes
		$this->attributes = array(
			array(),
			array('class' => 'c'),
			array('class' => 'c'),
			array('class' => 'c'),
			array('class' => 'c'),
			array('width' => 40, 'class' => 'c')
		);
		
		// Multicast data
		// TODO: Move to class
		$this->DB->query("SELECT 
					count(hosts.hostID) as cnt, 
					multicastSessions.msName,
					multicastSessions.msStartDateTime,
					multicastSessions.msState,
					multicastSessions.msPercent,
					multicastSessions.msID
				FROM 
					(select * from multicastSessions where msState in (0,1)) multicastSessions  
					inner join multicastSessionsAssoc on ( multicastSessionsAssoc.msID = multicastSessions.msID )
					inner join ( select * from tasks where taskStateID in (1, 2, 3) ) tasks on ( multicastSessionsAssoc.tID = tasks.taskID )
					inner join hosts on (taskHostID = hostID)
				GROUP BY
					multicastSessions.msID");
		
		// Row data
		while ($task = $this->DB->fetch()->get())
		{
			$this->data[] = array(
				'id'		=> $task['msID'],
				'name'		=> $task['msName'],
				'count'		=> $task['cnt'],
				'start_date'	=> $task['msStartDateTime'],
				'state'		=> ($task['taskState'] == 0 && hasCheckedIn($conn, $task['taskID']) ? 'In Line' : state2text($task['msState'])),
				'percent'	=> $task['msPercent']
			);
		}
		
		// Hook
		$this->HookManager->processEvent('MULTICAST_TASK_DATA', array('headerData' => &$this->headerData, 'data' => &$this->data, 'templates' => &$this->templates, 'attributes' => &$this->attributes));
		
		// Output
		$this->render();
		
	}
	
	public function active_snapins()
	{
		
	}
	
	public function scheduled()
	{
		
	}
}

// Register page with FOGPageManager
$FOGPageManager->register(new TaskManagementPage());