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
class ScheduledTask
{
	const TASK_TYPE_SINGLE 	= "S";
	const TASK_TYPE_CRON 	= "C";

	private $id;
	private $host;
	private $group;
	private $timer;
	private $taskType;
	private $shutdown;
	private $otherArgs1;
	private $otherArgs2;
	private $otherArgs3;
	private $otherArgs4;
	private $otherArgs5;

	function __construct(  $objhost=null, $objgroup=null, $objtimer=null, $chTaskType=null, $id=null )
	{
		if ( $objhost != null )
		{
			$this->setHost($objhost);
		}
		else if ( $objgroup != null )
		{
			$this->setGroup( $objgroup );
		}
		$this->taskType = $chTaskType;
		$this->timer=$objtimer;
		$this->shutdown = false;
		$this->id=$id;
	}
	
	function getID() { return $this->id; }
	
	
	function getHost() { return $this->host; }
	function setHost($objhost) 
	{ 
		$this->group = null;
		$this->host = $objhost; 
	}
	
	function getGroup() { return $this->group; }
	function setGroup( $objgroup )
	{
		$this->host = null;
		$this->group = $objgroup;
	}
	
	
	function isGroupBased() { return $this->group != null; }
	
	function getTimer() { return $this->timer; }
	function setTimer( $objtimer ) { $this->timer = $objtimer; }
	
	function getTaskType()	{ return $this->taskType; }
	function setTaskType($t) { $this->taskType = $t; }
	
	function setShutdownAfterTask( $blShutdown ) { $this->shutdown = $blShutdown; }
	function getShutdownAfterTask(  ) { return $this->shutdown; }
	
	function setOther1( $string ) { $this->otherArgs1 = $string; }
	function getOther1( ) { return $this->otherArgs1; }
	
	function setOther2( $string ) { $this->otherArgs2 = $string; }
	function getOther2( ) { return $this->otherArgs2; }
	
	function setOther3( $string ) { $this->otherArgs3 = $string; }
	function getOther3( ) { return $this->otherArgs3; }	
	
	function setOther4( $string ) { $this->otherArgs4 = $string; }
	function getOther4( ) { return $this->otherArgs4; }
	
	function setOther5( $string ) { $this->otherArgs5 = $string; }
	function getOther5( ) { return $this->otherArgs5; }		
}

?>
