<?php
/****************************************************
 * FOG Hook: HostVNCLink
 *	Author:		Blackout
 *	Created:	9:26 AM 3/09/2011
 *	Revision:	$Revision$
 *	Last Update:	$LastChangedDate$
 ***/

// HostVNCLink - custom hook class
class HostVNCLink extends Hook
{
	// Class variables
	var $name = 'HostVNCLink';
	var $description = 'Adds a "VNC" link to the Host Lists';
	var $author = 'Blackout';
	
	var $active = false;
	
	// Custom variable
	var $port = 5800;
	
	function HostData($arguments)
	{
		// Add column template into 'templates' array
		$arguments['templates'][] = sprintf('<a href="http://%s:%d" target="_blank">VNC</a>', '%hostname%', $this->port);
		// Add these HTML attributes to that column
		$arguments['attributes'][] = array('class' => 'c');
	}
	
	function HostTableHeader($arguments)
	{
		// Add new Header column with the content 'VNC'
		$arguments['templates'][] = 'VNC';
		// Add these HTML attributes to that column
		$arguments['attributes'][] = array('width' => '40', 'class' => 'c');
	}
}

// Init
$HostVNCLink = new HostVNCLink();

// Register hooks with HookManager on desired events
$HookManager->register('HostData', array($HostVNCLink, 'HostData'));
$HookManager->register('HostTableHeader', array($HostVNCLink, 'HostTableHeader'));