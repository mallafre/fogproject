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
 *
 *
 */
 
class WakeOnLan
{
	private $strMac;

	public function __construct( $mac )
	{
		$this->strMac = $mac;
	}
	
	public function send()
	{
		if ( $this->strMac != null )
		{
			$arByte = explode(':', $this->strMac);
			
			$strAddr = null;

			for ($i=0; $i<count( $arByte); $i++) 
				$strAddr .= chr(hexdec($arByte[$i]));
		
			$strRaw = null;
			for ($i=0; $i<6; $i++) 
				$strRaw .= chr(255);
				
			for ($i=0; $i<16; $i++) 
				$strRaw .= $strAddr;
				
			$soc = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
			if ( $soc !== FALSE )
			{
				if(socket_set_option($soc, SOL_SOCKET, SO_BROADCAST, TRUE)) 
				{
					if( socket_sendto($soc, $strRaw, strlen($strRaw), 0, "255.255.255.255", 9) ) 
					{
						socket_close($soc);
						return true;
					}
					else 
						return false;				
				}
				else
					new Exception( "Failed to set option!");	
			}
			else
			{
				$errCd = socket_last_error();
				$errMsg = socket_strerror($errCd);
				throw new Exception( "Socket Error: $errCd :: $errMsg" );
			}
		}
		return false;
	}
} 
 
?>
