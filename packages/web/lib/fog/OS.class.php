<?php

// Blackout - 8:17 AM 25/09/2011
class OS extends FOGController
{
	// Table
	protected $databaseTable = 'os';
	
	// Name -> Database field name
	protected $databaseFields = array(
		'id'		=> 'osID',
		'name'		=> 'osName',
		'description'	=> 'osDescription'
	);
}