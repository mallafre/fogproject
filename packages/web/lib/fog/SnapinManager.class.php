<?php

// Blackout - 3:10 PM 25/09/2011
class SnapinManager extends FOGManagerController
{
	// Search query
	public $searchQuery = 'SELECT * FROM snapins WHERE sName LIKE "%${keyword}%" OR sFilePath LIKE "%${keyword}%"';
}