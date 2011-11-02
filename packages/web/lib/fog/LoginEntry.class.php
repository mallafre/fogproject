<?php
/*
 *  FOG is a computer imaging solution.
 *  Copyright (C) 2010  SyperiorSoft Inc. (Chuck Syperski & Jian Zhang)
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

class LoginEntry
{
	const ACTION_LOGIN = 1;
	const ACTION_START = 99;
	const ACTION_LOGOUT = 0;

	private $hostId, $id;
	private $username, $action, $description;
	private $objDate;

	public function __construct($id, $hostid, $user, $action, $desc, $date)
	{
		$this->hostId = $hostid;
		$this->id = $id;
		$this->username = $user;
		$this->action = $action;
		$this->description = $desc;
		$this->objDate = $date;
	}

	public function isComplete()
	{
		return ( $this->getHostId() != null && $this->getHostId() >= 0 &&
			 $this->action != null && $this->objDate != null );
	}

	public function getId()
	{
		return $this->id;
	}

	public function getHostId()
	{
		return $this->hostId;
	}

	public function getUsername()
	{
		return $this->username;
	}

	public function getAction()
	{
		return $this->action;
	}

	public function getDescription()
	{
		return $this->description;
	}	

	public function getDate()
	{
		return $this->objDate;
	}
}


?>
