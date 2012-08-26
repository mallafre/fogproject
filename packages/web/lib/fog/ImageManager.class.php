<?php

// Blackout - 11:47 AM 2/10/2011
class ImageManager extends FOGManagerController
{
	// Table
	public $databaseTable = 'images';

	// Search query
	public $searchQuery = 'SELECT * FROM images WHERE imageName LIKE "%${keyword}%"';
	
	// Custom function
	public function buildSelectBox($matchID = '', $elementName = '', $orderBy = 'id')
	{
		// Change default sort order to by 'id'
		return parent::buildSelectBox($matchID, $elementName, $orderBy);
	}
}