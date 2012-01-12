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

// Blackout - 11:05 AM 8/01/2012
class Date extends FOGBase
{
	private $time;

	// Overrides
	function __construct( $longUnixTime ) 
	{
		// FOGBase Constructor
		parent::__construct();
		
		// Set time
		$this->time = $longUnixTime;
	}
		
	function __toString()
	{
		return (string)$this->toFormatted();
	}
	
	// Custom
	function toTimestamp()
	{
		return $this->time;
	}
	
	function toFormatted()
	{
		return (string)$this->FOGCore->formatTime($this->time);
	}

	// LEGACY
	public function getLong()
	{
		return $this->time;
	}
}