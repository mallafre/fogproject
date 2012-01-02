<?php

// Blackout - 7:28 AM 25/12/2011
class FOGCron extends FOGGetSet
{
	protected $data = array(
		'minute'	=> 0,		// Minute (0 - 59)
		'hour'		=> 23,		// Hour (0 - 23)
		'dayOfMonth'	=> '*',		// Day of Month (1 - 31)
		'month'		=> '*',		// Month (1 - 12)
		'dayOfWeek'	=> '*'		// Day of Week (0 - 6) - sun,mon,tue,wed,thu,fri,sat
	);
}