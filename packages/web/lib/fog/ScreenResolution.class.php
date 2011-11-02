<?php
/*
 *  FOG is a computer imaging solution.
 *  Copyright (C) 2010  Chuck Syperski & Jian Zhang
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
 
class ScreenResolution
{
	private $height, $width, $refresh;

	function __construct( $x, $y, $r )
	{
		$this->height = $y;
		$this->width = $x;
		$this->refresh = $r;
	}

	public function isValid()
	{
		return ( $this->height != null && is_numeric( $this->height ) && $this->height > 0 && 
		     $this->width != null && is_numeric( $this->width ) && $this->width > 0 &&
		     $this->refresh != null && is_numeric( $this->refresh ) && $this->refresh > 0 );
	}

	public function getHeight()
	{
		return $this->height;
	}

	public function getWidth()
	{
		return $this->width;
	}

	public function getRefresh()
	{
		return $this->refresh;
	}
}
?>
