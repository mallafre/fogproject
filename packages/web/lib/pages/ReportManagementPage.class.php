<?php

// Blackout - 1:51 PM 13/12/2011
class ReportManagementPage extends FOGPage
{
	// Base variables
	var $name = 'Report Management';
	var $node = 'report';
	var $id = 'id';
	
	// Menu Items
	var $menu = array(
		
	);
	var $subMenu = array(
		
	);
	
	// Pages
	public function index()
	{
		// Set title
		$this->title = _('Reports');
	}
	
	public function file()
	{
		$path = rtrim($this->FOGCore->getSetting('FOG_REPORT_DIR'), '/') . '/' . basename(base64_decode($this->REQUEST['f']));
		
		if (!file_exists($path))
		{
			$this->fatalError('Report file does not exist! Path: %s', array($path));
		}
		
		require_once($path);
	}
}

// Register page with FOGPageManager
$FOGPageManager->register(new ReportManagementPage());