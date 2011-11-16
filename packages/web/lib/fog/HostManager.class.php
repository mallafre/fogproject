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
 
class HostManager extends FOGManagerController
{
	// Table
	protected $databaseTable = 'hosts';
	
	// Search query
	protected $searchQuery = 'SELECT 
					hosts.* 
				FROM
					hosts 
					left outer join 
						(SELECT * FROM hostMAC WHERE hmMAC like "%${keyword}%") hostMAC
							on ( hmHostID = hostID ) 
					left outer join 
						inventory
							on ( iHostId = hostID ) 
				WHERE 
					hostName like "%${keyword}%" or 
					hostDesc like "%${keyword}%" or 
					hostIP like "%${keyword}%" or 
					hostMAC like "%${keyword}%" or 
					hmMAC like "%${keyword}%" or
					iSysSerial like "%${keyword}%" or 
					iPrimaryUser like "%${keyword}%" or
					iOtherTag like "%${keyword}%" or
					iOtherTag1 like "%${keyword}%" or
					iSysman like "%${keyword}%" or
					iSysproduct like "%${keyword}%" 
				GROUP BY 	
					hostID DESC';
	
	const SORT_HOST_ASC = 1;
	const SORT_HOST_DESC = 2;
	const SORT_IP_ASC = 3;
	const SORT_IP_DESC = 4;
	const SORT_MAC_ASC = 5;
	const SORT_MAC_DESC = 6;	


	const UPDATE_GENERAL = 1;						// includes everything on the General page including additional MAC addresses
	const UPDATE_AD = 2;
	const UPDATE_PRINTERS = 4;

	const UPDATE_ALL = 7;

	protected $db;

	public static function parseMacList( $stringlist )
	{
		if ( $stringlist != null && strlen( $stringlist ) > 0 )
		{
			$arParts = explode("|",$stringlist );
			$arMacs = array();
			for( $i = 0; $i < count( $arParts ); $i++ )
			{
				$part = trim($arParts[$i]);
				if ( $part != null && strlen( $part ) > 0 )
				{
					$tmpMac = new MACAddress( $part );
					if ( $tmpMac->isValid()  )
						$arMacs[] = $tmpMac;
				} 
			}
			return $arMacs;
		}
		return null;
	}
	
	public function getAllHosts($sortingOpts=self::SORT_HOST_ASC)
	{
		$HostManager = new HostManager();
		
		return $HostManager->search();
	}

	public function addMACToPendingForHost( $host, $mac )
	{
		if ( $this->db != null && $host != null && $mac != null && $mac->isValid() )
		{
			// make sure it doesn't exist in the pending table
			$macs = $this->getPendingMacAddressesForHost( $host );
			if ( $macs != null )
			{
				for( $i = 0; $i < count( $macs ); $i++ )
				{
					$cMac = $macs[$i];
					if ( $cMac != null )
					{
						if ( $cMac->getMACWithColon() == $mac->getMACWithColon() )
							return false;
					}
				}
			}
		
			$sql = "INSERT INTO
					pendingMACS (pmAddress, pmHostID)
				VALUES
					('" . $this->db->sanitize((String)$mac) . "', '" . $this->db->sanitize($host->getID()) . "')";
			return $this->db->query($sql)->affected_rows() == 1;
		}
		return false;
	}

	public function deletePendingMacAddressForHost( $host, $mac )
	{
		if ( $this->db != null && $host != null && $mac != null && $mac->isValid() )
		{
			$sql = "DELETE
				FROM 
					pendingMACS 
				WHERE
					pmHostID = '" . $this->db->sanitize($host->getID()) . "' AND
					pmAddress = '" . $this->db->sanitize($mac->getMACWithColon()) . "'";

			return $this->db->query($sql)->affected_rows() > 0;
		
		}
		return false;
	}

	public function getAllHostsWithPendingMacs()
	{
		if ( $this->db != null )
		{
			$sql = "SELECT 
					pmHostID
				FROM 
					pendingMACS";
					
			$arHostsIds = array();	
			
			if ( $this->db->query($sql) )
			{
				while( $ar = $this->db->fetch()->get() )
				{
					$arHostsIds[] = $ar["pmHostID"];
				}	
			}
			
			$arHosts = array();
			for( $i = 0; $i < count($arHostsIds); $i++ )
			{
				$h = new Host( $arHostsIds[$i] );
				if ( $h != null )
				$arHosts[] = $h;
			}
			return $arHosts;
								
		}
		return null;
	}

	public function getPendingMacAddressesForHost( $host )
	{
		if ( $this->db != null && $host != null  )
		{
			$sql = "SELECT
					pmAddress 
				FROM 
					pendingMACS 
				WHERE
					pmHostID = '" . $this->db->sanitize($host->getID()) . "'
				GROUP BY
					pmAddress";
			if ( $this->db->query($sql) )
			{
				$arMacs = array();
				while( $ar = $this->db->fetch()->get() )
				{
					 $mac = new MACAddress($ar["pmAddress"]);
					 if ( $mac->isValid() )
						 $arMacs[] = $mac;
				}	
				return $arMacs;	
			}					
		}
		return null;
	}

	public function getHostByMacAddress($mac, $primaryOnly = false)
	{
		if ( $this->db != null && $mac != null && $mac->isValid() )
		{
			$sql = "SELECT
					* 
				FROM 
					hosts 
				WHERE
					hostMAC = '" . $this->db->sanitize($mac->getMACWithColon()) . "'";
			
			
			if ( $this->db->query($sql) )
			{
				while( $ar = $this->db->fetch()->get() )
				{
					return new Host($ar);
				}
			}

			if ( ! $primaryOnly )
			{
				$sql = "SELECT
						hmHostID 
					FROM 
						hostMAC 
					WHERE
						hmMAC = '" . $this->db->sanitize($mac->getMACWithColon()) . "'";
					
				if ( $this->db->query($sql) )
				{
					while( $ar = $this->db->fetch()->get() )
					{
						return new Host($ar);
					}		
				}
			}					
		}
		return null;
	}
	
	public function doesHostExistWithMac( $mac, $ignoringHostId=-1 )
	{
		$host = $this->getHostByMacAddress( $mac );
		if ( $host == null )
			return false;
		else
		{	
			if ( $ignoringHostId == -1 )
				return true;
			else
				return  $host->getID() != $ignoringHostId;
		} 
	}
	
	public function getHostByMacAddresses( $arMac )
	{
		if ( $arMac != null )
		{
			if ( is_array( $arMac ) )
			{
				$hostReturn = null;
				for( $i = 0; $i < count( $arMac ); $i++ )
				{
					if ( $arMac[$i] !== null && $arMac[$i]->isValid() )
					{
						$tmpHost = $this->getHostByMacAddress( $arMac[$i] );
						if ( $tmpHost != null )
						{
							if ( $hostReturn == null )
							{
								$hostReturn = $tmpHost;
							}
							else
							{
								if ( $hostReturn->getID() != $tmpHost->getID() )
									throw new Exception( _("Error multiple hosts returned for list of mac addresses!") );
							}
						}
					}
				}
				return $hostReturn;
			}
			else
			{
				return $this->getHostByMacAddress( $arMac );
			}
		}
		return null;
	}
	
	private function getSortingOptions( $sortingOpts )
	{
		$orderby = "";
		switch( $sortingOpts )
		{
			case self::SORT_HOST_ASC:
				$orderby = "ORDER BY hostName asc";
				break;
			case self::SORT_HOST_DESC:
				$orderby = "ORDER BY hostName desc";
				break;
			case self::SORT_IP_ASC:
				$orderby = "ORDER BY hostIP asc";
				break;
			case self::SORT_IP_DESC:
				$orderby = "ORDER BY hostIP desc";
				break;
			case self::SORT_MAC_ASC:
				$orderby = "ORDER BY hostMAC asc";
				break;
			case self::SORT_MAC_DESC:
				$orderby = "ORDER BY hostMAC desc";
				break;																									
		}
		return $orderby;
	}
	
	public function isServiceModuleEnabledForHost( $host, $modKey )
	{
		if ( $this->db != null && $host != null && $modKey != null )
		{
			$sql = "SELECT 
					msState 
				FROM
					moduleStatusByHost
				WHERE
					msHostID = '" . $this->db->sanitize( $host->getID() ) . "' and
					msModuleID = '" . $this->db->sanitize( $modKey ) . "'";
			if ( $this->db->query($sql) )
			{
				while( $ar = $this->db->fetch()->get() )
				{
					if ( $ar["msState"] == "0" ) return false;
				}
				return true;
			}					
		}
		throw new Exception( _("Error looking up service status for host!") );
	}
	
	
	public function deleteHost( $id )
	{
		if ( $this->db == null )
			throw new Exception( _("Database connection is null.") );
		
		if ( $id == null )
			throw new Exception( _("Host ID is null.") );
			
		if ( ! is_numeric( $id ) || $id < 0 )
			throw new Exception( _("Invalid Host ID.") );
			
		// clean up potential orphans
		// Clean up printers
		$this->db->query( "DELETE FROM printerAssoc WHERE paHostID = '" . $this->db->sanitize($id) . "'" );		
		// clean up inventory
		$this->db->query( "DELETE FROM inventory WHERE iHostID = '" . $this->db->sanitize($id) . "'" );		
		// clean up pending mac addresses
		$this->db->query( "DELETE FROM pendingMACS WHERE pmHostID = '" . $this->db->sanitize($id) . "'" );		
		// clean assoc macs
		$this->db->query( "DELETE FROM hostMAC WHERE hmHostID = '" . $this->db->sanitize($id) . "'" );
		// clean up associated snapins
		$this->db->query( "DELETE FROM snapinAssoc WHERE saHostID = '" . $this->db->sanitize($id) . "'" );
		
		
		// finally remove the host object
		return $this->db->query( "DELETE FROM hosts WHERE hostID = '" . $this->db->sanitize($id) . "'" ) == 1;
	}
	
	// Adds a new host to the database
	public function addHost( $host, $user=null )
	{
		if ( $this->db == null )
			throw new Exception( _("Database connection is null.") );
		
		if ( $host == null )
			throw new Exception( _("Host is null.") );
			
		if ( $host->get('mac') || ! $host->get('mac')->isValid() )
			throw new Exception( _("MAC address is invalid.") );			
			
		if ( $this->doesHostExistWithMac( $host->get('mac') ) )
			throw new Exception( _("A host with this MAC address already exists.") );			
			
		if ( $host->isHostnameSafe() )
		{
			$sql = "insert into hosts(hostMAC, hostIP, hostName, hostDesc, hostCreateDate, hostImage, hostCreateBy, hostOS, hostUseAD, hostADDomain, hostADOU, hostADUser, hostADPass, hostKernelArgs, hostKernel, hostDevice) 
					  values('" . $this->db->sanitize($host->get('mac')->getMACWithColon() ) . "',
					  	 '" . $this->db->sanitize($host->getIPAddress() ) . "', 
					  	 '" . $this->db->sanitize($host->getHostname() ) . "', 
					  	 '" . $this->db->sanitize($host->getDescription() ) . "', 
					  	 NOW(), 
					  	 '" . $this->db->sanitize($host->getImage() != null ? $host->getImage()->getID() : '' ) . "', 
					  	 '" . $this->db->sanitize($user != null ? $user->get('name') : '' ) . "', 
					  	 '" . $this->db->sanitize($host->getOS()) . "', 
					  	 '" . $this->db->sanitize($host->usesAD() ? '1' : '0') . "', 
					  	 '" . $this->db->sanitize($host->getDomain()) . "', 
					  	 '" . $this->db->sanitize($host->getOU()) . "', 
					  	 '" . $this->db->sanitize($host->getUser()) . "', 
					  	 '" . $this->db->sanitize($host->getPassword()) . "', 
					  	 '" . $this->db->sanitize($host->getKernelArgs()) . "', 
					  	 '" . $this->db->sanitize($host->getKernel()) . "', 
					  	 '" . $this->db->sanitize($host->getDiskDevice()) . "' )";
			return $this->db->query($sql)->affected_rows() == 1;
		}
		else
			throw new Exception( _("Invalid hostname.") );			
		return	false;
	}	

	// function either returns true or throws an exception
	public function updateHost( $host, $flags )
	{
		if ( $this->db != null && $host != null )
		{
			if ( ( self::UPDATE_GENERAL & $flags ) == 1 )
			{
				if ($this->updateGeneral($host) === true)
				{
					return true;
				}
				else
				{
					return false;
				}
			}
		
			return true;
		}
		
		return false;
	}
	
	private function updateGeneral( $host )
	{
		try
		{
			if ( $this->db == null )
			{
				throw new Exception(_("No Database Connection"));
			}
			
			if (  $host == null ) 
			{
				throw new Exception(_("Invalid Host"));
			}
			if ( $host->get('mac') )
			{
				throw new Exception( _("MAC address object is null.") );
			}
				
			if ( ! $host->get('mac')->isValid() )
			{
				throw new Exception( _("MAC address is invalid.") );
			}
				
			if ( $host->getID() < 0 )
			{
				throw new Exception( _("Host ID is invalid.") );
			}
				
			if ( $host->getHostname() == null || strlen($host->getHostname()) == 0 )
			{
				throw new Exception( _("Hostname is invalid.") );
			}
				
			if ( $host->getOS() == null || $host->getOS() == -1 )
			{
				throw new Exception( _("Operating System ID is invalid.") );
			}
			
			if ( $this->doesHostExistWithMac( $host->get('mac'), $host->getID() ) )
			{
				throw new Exception( _("Another host exists with that MAC address.") );
			}
			
			$imageID = -1;
			if ( $host->getImage() != null )
				$imageID = $host->getImage()->getID();
			$sql = "UPDATE 
					hosts 
				SET 
					hostKernel = '" . $this->db->sanitize( $host->getKernel() ) . "', 
					hostDevice = '" . $this->db->sanitize( $host->getDiskDevice() ) . "', 
					hostKernelArgs = '" . $this->db->sanitize( $host->getKernelArgs() ) . "', 
					hostMAC = '" . $this->db->sanitize( $host->get('mac')->getMACWithColon() ) . "', 
					hostIP = '" . $this->db->sanitize( $host->getIPAddress() ) . "', 
					hostOS = '" . $this->db->sanitize( $host->getOS() ) . "', 
					hostName = '" . $this->db->sanitize( $host->getHostname() ) . "', 
					hostDesc = '" . $this->db->sanitize( $host->getDescription() ) . "', 
					hostImage = '" . $this->db->sanitize( $imageID ) . "' 
				WHERE 
					hostID = '" . $this->db->sanitize( $host->getID() ) . "'";
			
			$this->db->query($sql)->affected_rows();
			
			// update the additional mac addresses
			$sql = "DELETE FROM hostMAC where hmHostID = '" . $this->db->sanitize( $host->getID() ) . "'";

			$this->db->query($sql)->affected_rows();
			
			$addMacs = $host->get('additionalMACs');
			if ( $addMacs != null )
			{
				$exception = null;
				for( $i = 0; $i < count( $addMacs );$i++ )
				{
					$curMac = $addMacs[$i];
					if ( $curMac != null && $curMac->isValid() )
					{
						if ( ! $this->doesHostExistWithMac( $curMac ) )
						{
							$sql = "INSERT INTO hostMAC (hmHostID, hmMAC) VALUES('" . $this->db->sanitize( $host->getID() ) . "','" . $this->db->sanitize( $curMac->getMACWithColon() ) . "')";
							if ( $this->db->query($sql)->affected_rows() != 1 )
								$exception = new Exception( "Error adding additional MAC address: " . $curMac->getMACWithColon() );
						}
						else
							$exception = new Exception( _("MAC address").": " . $curMac->getMACWithColon() . " "._("is already registered with FOG.") );
					}
				}
				
				if ( $exception != null )
					throw $exception; 
			}
			
			return true;
		}
		catch (Exception $e)
		{
			$this->lastError = $e->getMessage();
			
			return false;
		}
	}
}