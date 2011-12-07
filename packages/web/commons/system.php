<?php

/****************************************************
 * FOG Dashboard JS
 *	Author:		Blackout
 *	Created:	5:44 PM 4/12/2011
 *	Revision:	$Revision$
 *	Last Update:	$LastChangedDate$
 ***/

// These variables are for the FOG system and do NOT need to be modified by the end user

define('IS_INCLUDED', true);
define('FOG_VERSION', '0.33B');
define('FOG_SCHEMA', 28);
define('BASEPATH', DetermineBasePath());

// LEGACY - Database
if (defined('MYSQL_HOST') && !defined('DATABASE_HOST'))
{
	define('DATABASE_TYPE', (define('DB_TYPE') ? DB_TYPE : 'mysql'));
	define('DATABASE_HOST', MYSQL_HOST);
	define('DATABASE_USERNAME', MYSQL_USERNAME);
	define('DATABASE_PASSWORD', MYSQL_PASSWORD);
	define('DATABASE_NAME', MYSQL_DATABASE);
}
if (defined('DB_HOST') && !defined('DATABASE_HOST'))
{
	define('DATABASE_TYPE', (define('DB_TYPE') ? DB_TYPE : 'mysql'));
	define('DATABASE_HOST', DB_HOST);
	define('DATABASE_USERNAME', DB_USERNAME);
	define('DATABASE_PASSWORD', DB_PASSWORD);
	define('DATABASE_NAME', DB_DATABASE);
}

function DetermineBasePath()
{
	// Find the name of the first directory in the files path
	$FirstDirectory = rtrim(next(explode('/', dirname($_SERVER['PHP_SELF']))));
	
	if (preg_match('#fog#i', $FirstDirectory))
	{
		// If the first directory contains the word 'fog', we assume the fog installation is under a sub directory (default installation)
		return rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/' . $FirstDirectory;
	}
	else
	{
		// else we assume fog is not under a sub directory (virtual host)
		return rtrim($_SERVER['DOCUMENT_ROOT'], '/');
	}
}

/*
if (!preg_match('#' . WEB_ROOT . '#', dirname($_SERVER['PHP_SELF'])))
{
	die("WEB_ROOT constant set incorrectly in commons/config.php\n\nWEBROOT:\t\t" . WEB_ROOT . "\nScript Filename:\t" . $_SERVER['SCRIPT_FILENAME'] . "\nSuggested Value:\t" . BASEPATH);
}
*/