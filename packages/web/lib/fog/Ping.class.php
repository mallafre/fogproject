<?php
/*
 *  FOG  is a computer imaging solution.
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


/**
 *  This is the poor man's ping class.  Because we run in Linux we can use
 *  TCP ports below 1024, so we did a little UDP trick to check is a host is 
 *  alive.  From our tests it seems pretty stable.  We didn't want to have to 
 *  use the system ping command because the overhead of execute().
 */

class Ping
{
	private $host;
	private $timeout;
	private $internalSleep;

	public function __construct( $host, $timeout=2, $sleep=false, $type='udp' )
	{
		$this->host = $host;
		$this->timeout = $timeout;
		$this->internalSleep = $sleep;
		$this->type = ($type != 'icmp' ? 'udp' : 'icmp');
	}
	
	public function execute()
	{
		if ( $this->timeout > 0 && $this->host != null )
		{
			if ($this->internalSleep) usleep($this->internalSleep);
			
			return ($this->type == 'icmp' ? $this->icmpPing() : $this->udpPing());
		}
		return false;
	}
	
	function icmpPing() {
		/* ICMP ping packet with a pre-calculated checksum */
		$package = "\x08\x00\x7d\x4b\x00\x00\x00\x00PingHost";
		$socket  = socket_create(AF_INET, SOCK_RAW, 1);
		socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, array('sec' => $this->timeout, 'usec' => 0));
		socket_connect($socket, $this->host, null);
		
		print $socket;

		$ts = microtime(true);
		socket_send($socket, $package, strLen($package), 0);
		if (socket_read($socket, 255))
		{
			$result = microtime(true) - $ts;
		}
		else
		{
			$result = false;
		}
		socket_close($socket);

		return $result;
	}
	
	// Blackout - 9:08 AM 4/10/2011
	function udpPing()
	{
		try
		{
			$h = fsockopen('udp://'.$this->host, 7, $errNo, $errStr, $this->timeout);

			if (!$h)
			{
				throw new Exception( "Ping Error: " . $errStr . " (" . $errNo . ")" );
			}
			
			stream_set_timeout($h, $this->timeout);
			$start = microtime(true);
			$write = fwrite($h,"echo-fog\n");
			
			if (!$write)
			{
				throw new Exception( "Ping Error: Unable to write to socket!" );
			}
			
			fread($h,1024);
			$blReturn = ( (microtime(true) - $start) <= $this->timeout );
			fclose($h);
			
			return $blReturn;
		}
		catch (Exception $e)
		{
			print $e->getMessage();
		}
			
		return false;
	}
}