<?php
/*
 *  FOG is a computer imaging solution.
 *  Copyright (C) 2007  Chuck Syperski & Jian Zhang
 *
 *   This program is free software: you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation, either version 3 of the License, or
 *   any later version.
 *
 *   This program is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 *
 */

// Require FOG Base - the relative path to config.php changes in AJAX files as these files are included and accessed directly
require_once((defined('BASEPATH') ? BASEPATH . '/commons/config.php' : '../../commons/config.php'));
require_once(BASEPATH . '/commons/init.php');

// Allow AJAX check
if (!$_SESSION['AllowAJAXTasks'])
{
	die('FOG Session Invalid');
}

// Blackout - just incase the JS element cannot be found
if ($_REQUEST['ping'] == 'undefined')
{
	die('Undefined host to ping');
}

if (!isset($_SESSION['FOGPingActive']))
{
	// FOG Session Invalid
	die('99');
}
else if (!$_SESSION['FOGPingActive'])
{
	// Ping disabled via FOG Configuration
	die('97');
}

if (isset($_GET["ping"]))
{
	try
	{
		$ip = gethostbyname($_GET["ping"]);
		
		if ($ip != $_GET["ping"])
		{
			$ping = new Ping($ip);
			if ($ping->execute())
			{
				// Ping Success!
				echo "1";
			}
			else
			{
				// Ping failed
				echo "0";
			}
		}
		else
		{
			echo "Unable to resolve hostname: $ip";
		}
	}
	catch (Exception $e)
	{
		echo $e->getMessage();
	}
}
else
{
	// No host passed to ping
	echo "98";
}