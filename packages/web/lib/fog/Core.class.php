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
 
class Core
{
	public $db;

	function __construct()
	{
		$this->db = $GLOBALS['db'];
	}
	
	public function getHostManager()
	{
		return new HostManager();
	}
	
	public function getUserManager()
	{
		return new UserManager();
	}
	
	public function getGroupManager()
	{
		return new GroupManager();
	}
	
	public function getImageManager()
	{
		return new ImageManager();
	}
	
	public function getTaskManager()
	{
		return new TaskManager();
	}

	public function getClientServiceManager()
	{
		return new ClientServiceManager();
	}
	
	function getMACManufacturer( $macprefix )
	{
		if ( $this->db && strlen( $macprefix ) == 8 )
		{
			$sql = "SELECT
					ouiMan
				FROM 
					oui
				WHERE
					ouiMACPrefix = '" . $this->db->sanitize( $macprefix ) . "'";
			if ( $this->db->query($sql) )
			{
				while( $ar = $this->db->fetch()->get() )
				{	
					return $ar["ouiMan"];
				}
			}
		}
		return _("n/a");
	}
	
	function addUpdateMACLookupTable( $macprefix, $strMan )
	{
		if ( $this->db && strlen( $macprefix ) == 8 && $strMan != null && strlen( $strMan ) > 0 )
		{
			if ( $this->doesMACLookCodeExist( $macprefix ) )
			{
				// update
				$sql = "UPDATE
						oui
					SET
						ouiMan = '" . $this->db->sanitize( $strMan ) . "'
					WHERE
						ouiMACPrefix = '" . $this->db->sanitize( $macprefix ) . "'";
				$this->db->query($sql)->affected_rows();
				return true;
			}
			else
			{
				// insert
				$sql = "INSERT INTO
						oui
							(ouiMACPrefix, ouiMan)
						VALUES
							('" . $this->db->sanitize( $macprefix ) . "', '" . $this->db->sanitize( $strMan ) . "')";
				return $this->db->query($sql)->affected_rows() == 1;
			}
		}
		return false;
	}
	
	private function doesMACLookCodeExist( $macprefix )
	{
		if ( $this->db != null  )
		{
			if ( strlen( $macprefix ) == 8 )
			{
				$sql = "SELECT
						count(*) as cnt 
					FROM 
						oui
					WHERE
						ouiMACPrefix = '" . $this->db->sanitize( $macprefix ) . "'";
				if ( $this->db->query($sql) )
				{
					while( $ar = $this->db->fetch()->get() )
					{	
						return $ar["cnt"] > 0;
					}
				}
				else
					throw new Exception( _("Unable to lookup mac prefix!") );
			}
			else
				throw new Exception( _("Invalid mac prefix")." " . $macprefix );	
		}
		else
			throw new Exception( _("Unable to lookup mac prefix!") );
	}
	
	function clearMACLookupTable()
	{
		if ( $this->db != null )
		{
			$sql = "DELETE 
				FROM 
					oui
				WHERE
					1=1";
			return ( $this->db->query($sql)->affected_rows() );

		}
		return false;
	}
	
	function getMACLookupCount()
	{
		if ( $this->db != null )
		{
			$sql = "SELECT 
					COUNT(*) AS cnt
				FROM 
					oui";
			if ( $this->db->query($sql) )
			{
				while( $ar = $this->db->fetch()->get() )
				{
					return $ar["cnt"];
				}
			}
		}
		return -1;
	}
}