<?php
/****************************************************
 * FOG Database Initialization
 *	Author:		Blackout
 *	Created:	5:49 PM 27/09/2011
 *	Revision:	$Revision$
 *	Last Update:	$LastChangedDate$
 ***/

// Init
require_once(BASEPATH . '/commons/init.php');

// Database
// Use this when reflection class arg call works
//$db = $FOGCore->getClass('DatabaseManager', DATABASE_TYPE, DATABASE_HOST, DATABASE_USERNAME, DATABASE_PASSWORD, DATABASE_NAME)->connect();

$DatabaseManager = new DatabaseManager(DATABASE_TYPE, DATABASE_HOST, DATABASE_USERNAME, DATABASE_PASSWORD, DATABASE_NAME);
$db = $DatabaseManager->connect();

// Legacy - Clean up when DB classes have been normalized
$conn = $db->getLink();
if ($FOGCore)
{
	$FOGCore->db = $conn;
}
if ($conn)
{
	// Database Schema version check
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