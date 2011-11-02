<?php
/*
 *  FOG  is a computer imaging solution.
 *  Copyright (C) 2010 SyperiorSoft Inc (Chuck Syperski & Jian Zhang)
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
 */

// Require FOG Base
require_once('../commons/config.php');
require_once(BASEPATH . '/commons/init.php');

// Error checking
// TODO: Fix
/*
if (!$_SESSION['AllowAJAXTasks'])
{
	die('FOG Session Invalid');
}
*/

$mac = new MACAddress($_GET["wakeonlan"]);
if ( $mac != null && $mac->isValid( ) )
{
	$wol = new WakeOnLan($mac->getMACWithColon());
	$wol->send();
}