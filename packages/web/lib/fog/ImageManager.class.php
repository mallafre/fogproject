<?php

// Blackout - 11:47 AM 2/10/2011
class ImageManager extends FOGManagerController
{
	// Table
	protected $databaseTable = 'images';

	// Search query
	protected $searchQuery = 'SELECT * FROM images WHERE imageName LIKE "%${keyword}%"';
	
	
	
	
	
	// Legacy - remove when all updated
	// Blackout - 1:13 PM 23/09/2011
	function getAllImages()
	{
		$query = "SELECT * FROM images ORDER BY imageName";
		$allImages = mysql_query($query) or die(mysql_error());
		
		while ($image = mysql_fetch_array($allImages, MYSQL_ASSOC))
		{
			$data[] = new Image($image);
		}
		
		return (array)$data;
	}
	
	function imageDefExists( $conn, $name, $id=-1 )
	{
		if ( $conn != null && $name != null )
		{
			$sql = "select count(*) as cnt from images where imageName = '" . mysql_real_escape_string( $name ) . "' and imageID <> $id";
			$res = mysql_query( $sql, $conn ) or criticalError( mysql_error(), _("FOG :: Database error!") );
			if ( $ar = mysql_fetch_array( $res ) )
			{
				if ( $ar["cnt"] == 0 )
					return false;
			}
		}
		return true;
	}
}