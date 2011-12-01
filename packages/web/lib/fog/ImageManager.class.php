<?php

// Blackout - 11:47 AM 2/10/2011
class ImageManager extends FOGManagerController
{
	// Table
	public $databaseTable = 'images';

	// Search query
	public $searchQuery = 'SELECT * FROM images WHERE imageName LIKE "%${keyword}%"';
	
	// Custom function
}