<?php
/*
 *  FOG is a computer imaging solution.
 *  Copyright (C) 2009  Chuck Syperski & Jian Zhang
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
 
class Date
{
	private $lngTS;

	function __construct( $longUnixTime ) 
	{
		$this->lngTS = $longUnixTime;
	}

	public function getLong()
	{
		return $this->lngTS;
	}
	
	function toString( $format="M j, y" )
	{
		return date($format, $this->lngTS );
	}
	
	function __toString()
	{
		return $this->toString();
	}
}
 
?>
