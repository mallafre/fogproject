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
 
class SnapinTask
{
	private $id, $jobId, $hostId, $state, $returnCode;
	private $dateCheckin, $dateComplete, $dateCreated;
	private $returnDetails;
	private $snapin;

	function __construct( $id, $jobid, $hostid, $state, $dateCheckin, $dateComplete, $dateJobCreated, $snapin, $returnCode, $returnDetails  ) 
	{
		$this->id = $id;
		$this->jobId = $jobid;
		$this->hostId = $hostid;
		$this->state = $state;
		$this->snapin = $snapin;
		$this->returnCode = $returnCode;
		$this->dateCheckin = $dateCheckin;
		$this->dateComplete = $dateComplete;
		$this->returnDetails = $returnDetails;
		$this->dateCreated = $dateJobCreated;
	}

	public function getId()
	{
		return $this->id;
	}

	public function getJobId()
	{
		return $this->jobId;
	}

	public function getHostId()
	{
		return $this->hostId;
	}

	public function getState()
	{
		return $this->state;
	}

	public function setSnapin( $snapin )
	{
		$this->snapin = $snapin;
	}

	public function getSnapin()
	{
		return $this->snapin;
	}

	public function getReturnCode()
	{
		return $this->returnCode;
	}

	public function getCheckInDate()
	{
		return $this->dateCheckin;
	}

	public function getCreationDate()
	{
		return $this->dateCreated;
	}

	public function getCompletionDate()
	{
		return $this->dateComplete;
	}

	public function getReturnDetails()
	{
		return $this->returnDetails;
	}								
}
?>
