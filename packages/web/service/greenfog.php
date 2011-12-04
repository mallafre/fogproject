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

// Require FOG Base
require_once('../commons/config.php');
require_once(BASEPATH . '/commons/init.php');
require_once(BASEPATH . '/commons/init.database.php');

$serviceManager = $FOGCore->getClass('ClientServiceManager');

$gfs = $serviceManager->getGreenFOGActions();

if ( $gfs != null )
{
	for( $i = 0; $i < count( $gfs ); $i++ )
	{
		$gf = $gfs[$i];
		if ( $gf != null )
			echo base64_encode( $gf->getHour() . "@" . $gf->getMinute() . "@" . $gf->getAction() ) . "\n";
	}
}