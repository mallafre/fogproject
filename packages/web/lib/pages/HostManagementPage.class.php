<?php

// Blackout - 2:02 PM 7/10/2011
class HostManagementPage extends FOGPage
{
	// Base variables
	var $name = 'Host Management';
	var $node = 'host';
	var $id = 'id';
	
	// Menu Items
	var $menu = array(
		
	);
	var $subMenu = array(
		
	);
	
	// Pages
	public function index()
	{
		
	}
	
	public function search()
	{
		
	}
	
	public function add()
	{
		
	}
	
	public function edit()
	{
		
	}
	
	public function delete()
	{
		
	}
}

// Register page with FOGPageManager
$FOGPageManager->add(new HostManagementPage());