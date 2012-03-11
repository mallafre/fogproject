<?php

// Blackout - 9:52 AM 23/02/2012
class PluginManagementPage extends FOGPage
{
	// Base variables
	var $name = 'Plugin Management';
	var $node = 'plugin';
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
		$this->title = _($this->name);
	}
}

// Register page with FOGPageManager
$FOGPageManager->register(new PluginManagementPage());