<?php

// Require FOG Base - the relative path to config.php changes in AJAX files as these files are included and accessed directly
require_once((defined('BASEPATH') ? BASEPATH . '/commons/config.php' : '../../commons/config.php'));
require_once(BASEPATH . '/commons/init.php');

// Blackout - 10:26 AM 25/05/2011
$data = array();
$fetchDataInfo = array(	'sites' 	=> 'http://www.fogproject.org/globalusers/',
			'version'	=> 'http://freeghost.sourceforge.net/version/version.php');

foreach ($fetchDataInfo AS $key => $url)
{
	if ($fetchedData = $FOGCore->fetchURL($url))
	{
		$data[$key] = $fetchedData;
	}
	else
	{
		$data['error-' . $key] = _('Error contacting server');
	}
}

print json_encode($data);