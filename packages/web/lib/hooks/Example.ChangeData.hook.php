<?php
/****************************************************
 * FOG Hook: Example Change Hostname
 *	Author:		Blackout
 *	Created:	8:57 AM 31/08/2011
 *	Revision:	$Revision$
 *	Last Update:	$LastChangedDate$
 ***/

// Example class
class TestHookChangeHostname extends Hook
{
	var $name = 'ChangeHostname';
	var $description = 'Appends "Chicken-" to all hostnames ';
	var $author = 'Blackout';
	
	var $active = false;
	
	function HostData($arguments)
	{
		foreach ($arguments['data'] AS $i => $data)
		{
			// DEBUG
			//$this->log(sprintf('Renaming Host: i: %s Data: %s', $i, print_r($data, 1)));
			
			// Rename host
			$arguments['data'][$i]['hostname'] = 'Chicken-' . $data['hostname'];
		}
	}
}

// Example: Test by changing all hostnames in Host Management
$HookManager->register('HostData', array(new TestHookChangeHostname(), 'HostData'));