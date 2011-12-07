<?php
/****************************************************
 * FOG Initialization
 *	Author:		Blackout
 *	Created:	3:15 PM 1/05/2011
 *	Revision:	$Revision$
 *	Last Update:	$LastChangedDate$
 ***/

// Init
@error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
@header('Cache-Control: no-cache');
session_cache_limiter('no-cache');
session_start();
@set_magic_quotes_runtime(0);

// Sanitize valid input variables
foreach (array('groupid','node','id','imageid','sub','snapinid','userid','storagegroupid','storagenodeid','crit','sort', 'userid', 'confirm', 'tab') AS $x)
{
	$$x = (isset($_REQUEST[$x]) ? addslashes($_REQUEST[$x]) : '');
}
unset($x);

// Auto Loader
if (!function_exists('__autoload'))
{
	function __autoload($className) 
	{
		try
		{
			$paths = array(BASEPATH . '/lib/fog', BASEPATH . '/lib/db', BASEPATH . '/lib/pages');
		       
			foreach ($paths as $path)
			{
				$fileName = $className . '.class.php';
				$filePath = rtrim($path, '/') . '/' . $fileName;

				if (file_exists($filePath))
				{
					
					if (!include($filePath))
					{
						throw new Exception(sprintf('Failed to include: %s', $filePath));
					}
					
					return true;
				}
			}
			
			throw new Exception(sprintf('Could not find file: File: %s, Paths: %s', $fileName, implode(', ', $paths)));
		}
		catch (Exception $e)
		{
			die(sprintf('Failed to load Class file: Class: %s, Error: %s', $className, $e->getMessage()));
		}
	}
}

// Legacy Functions
require_once(BASEPATH . '/commons/functions.include.php');

// Core
$FOGCore = new FOGCore();

// Hook Manager - Init & Load Hooks
$HookManager = new HookManager();
$HookManager->load();

// Locale
if ($_SESSION['locale'])
{
	putenv('LC_ALL='.$_SESSION['locale']);
	setlocale(LC_ALL, $_SESSION['locale']);
}

// Languages
bindtextdomain('messages', 'languages');
textdomain('messages');