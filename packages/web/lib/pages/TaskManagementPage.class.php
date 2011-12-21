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
	
	// __construct
	public function __construct($name = '')
	{
		// Call parent constructor
		parent::__construct($name);
		
		// Header row
		$this->headerData = array(
			_('Name'),
			_('Edit')
		);
		
		// Row templates
		$this->templates = array(
			sprintf('<a href="?node=%s&sub=edit&%s=${id}">${name}</a>', $this->node, $this->id),
			sprintf('<a href="?node=%s&sub=edit&%s=${id}"><span class="icon icon-edit"></span></a>', $this->node, $this->id)
		);
		
		// Row attributes
		$this->attributes = array(
			array(),
			array('class' => 'c', 'width' => '55'),
		);
	}
	
	// Pages
	public function index()
	{
		// Set title
		$this->title = _('All Users');
		
		// Find data
		$Users = $this->FOGCore->getClass('TaskManager')->find();
		
		// Row data
		foreach ($Users AS $User)
		{
			$this->data[] = array(
				'id'	=> $User->get('id'),
				'name'	=> $User->get('name')
			);
		}
		
		// Hook
		$this->HookManager->processEvent('TASK_DATA', array('headerData' => &$this->headerData, 'data' => &$this->data, 'templates' => &$this->templates, 'attributes' => &$this->attributes));
		
		// Output
		$this->render();
	}
	
	public function search()
	{
		// Set title
		$this->title = _('Search');
		
		// Set search form
		$this->searchFormURL = 'ajax/task.search.php';
		
		// Hook
		$this->HookManager->processEvent('TASK_SEARCH');

		// Output
		$this->render();
	}
}

// Register page with FOGPageManager
$FOGPageManager->register(new TaskManagementPage());