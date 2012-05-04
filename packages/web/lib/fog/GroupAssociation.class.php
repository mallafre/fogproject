<?php

// Blackout - 12:06 PM 26/04/2012
class GroupAssociation extends FOGController
{
	// Table
	public $databaseTable = 'groupmembers';
	
	// Name -> Database field name
	public $databaseFields = array(
		'id'		=> 'gmID',
		'hostID'	=> 'gmHostID',
		'groupID'	=> 'gmGroupID'
	);
	
	// Custom
	public function getHost()
	{
		return new Host($this->get('hostID'));
	}
	
	public function getGroup()
	{
		return new Group($this->get('groupID'));
	}
}