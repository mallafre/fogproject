<?php

// Blackout - 1:51 PM 1/12/2011
class ImageTypeManager extends FOGManagerController
{
	// Overrides
	public function buildSelectBox($matchID = '', $elementName = '')
	{
		// Legacy support for old imageID's - imageID's ranged from 0-3, this has been changed to 1-4
		if ($matchID === '0')
		{
			$matchID = '1';
		}
		
		// Build select box
		return parent::buildSelectBox($matchID, $elementName);
	}
}