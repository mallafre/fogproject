<?php
/****************************************************
 * FOG Database Initialization
 *	Author:		Blackout
 *	Created:	5:49 PM 27/09/2011
 *	Revision:	$Revision: 711 $
 *	Last Update:	$LastChangedDate: 2011-06-23 11:09:04 +1000 (Thu, 23 Jun 2011) $
 ***/

// Init
require_once(BASEPATH . '/commons/init.php');

// Database
// Use this when reflection class arg call works
//$db = $FOGCore->getClass('DatabaseManager', DB_TYPE, DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME)->connect();

$DatabaseManager = new DatabaseManager(DB_TYPE, DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
$db = $DatabaseManager->connect();

//print_r($db);exit;

// Legacy - Clean up when DB classes have been normalized
$conn = $db->getLink();
if ($FOGCore)
{
	$FOGCore->db = $conn;
	$core->db = $db;
}
if ($conn)
{
	if ($DatabaseManager->getVersion() != FOG_SCHEMA)
	{
		if ($_GET['redir'] != '1')
		{
			$FOGCore->redirect('../commons/schemaupdater/index.php?redir=1');
			exit;
		}
	}
}
else
{
	die(_('Unable to connect to Database'));
}