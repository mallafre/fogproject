<?php

// Blackout - 9:51 AM 23/02/2012
class OtherInformationManagementPage extends FOGPage
{
	// Base variables
	var $name = 'Other Information';
	var $node = 'about';
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
$FOGPageManager->register(new OtherInformationManagementPage());