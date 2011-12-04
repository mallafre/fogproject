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
require_once(BASEPATH . '/commons/init.database.php');

// Allow AJAX check
if (!$_SESSION['AllowAJAXTasks'])
{
	die('FOG Session Invalid');
}

if ( $_GET["prefix"] != null && strlen($_GET["prefix"]) >= 8 )
{
	if ( $FOGCore->getMACLookupCount() > 0 )
	{
		$mac = new MACAddress( $_GET["prefix"] );
		if ( $mac != null )
		{
			$mac = $FOGCore->getMACManufacturer($mac->getMACPrefix());
			echo ($mac == 'n/a' ? _('Unknown') : $mac);
		}
	}
	else
		echo "<a href='?node=about&sub=maclist'>"._("Load MAC Vendors")."</a>";
}
else
{
	echo _('Unknown');
}