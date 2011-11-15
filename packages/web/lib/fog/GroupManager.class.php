<?php

// Blackout - 3:10 PM 25/09/2011
class GroupManager extends FOGManagerController
{
	// Table
	protected $databaseTable = 'groups';
	
	// Search query
	protected $searchQuery = 'SELECT * FROM groups WHERE groupName LIKE "%${keyword}%"';

	// Legacy - remove when all updated
	public function getGroupByName($name)
	{
		$Group = new Group(array('name' => $name));
		$Group->load('name');
		
		return $Group;
	}
	
	public function createGroup($name, $user)
	{
		if ($this->db != null && ! $this->doesGroupExist( $name ) && $name != null && strlen( $name ) > 0 && $user != null)
		{
			$sql = "INSERT 
						into 
					groups(groupName, groupCreateBy, groupDateTime) 
					values( '" . $this->db->sanitize($name) . "', '" . $this->db->sanitize($user->get('name')) . "', NOW() )";
			if ( $this->db->query($sql)->affected_rows() == 1 )
				return $this->db->getInsertID();
						
		}
		return -1;
	}
	
	public function addHostToGroup($groupid, $hostid)
	{
		if ( $this->db != null && $groupid >= 0 && $hostid >= 0  && is_numeric( $groupid ) && is_numeric($hostid))
		{
			$sql = "INSERT 
						into 
					groupMembers(gmHostID, gmGroupID) 
					values( '" . $hostid . "', '" . $groupid . "' )";
			return ( $this->db->query($sql)->affected_rows() == 1 );			
		}
		return false;
	}
	
	public function doesGroupExist( $name, $excludeid=-1)
	{
		if ( $this->db != null )
		{
			$sql = "SELECT 
						COUNT(*) AS c 
					FROM 
						groups
					WHERE 
						groupName = '" . $this->db->sanitize( $name ) . "' and
						groupID <> '" . $this->db->sanitize( $excludeid ) . "'";
			if ( $this->db->query($sql) )
			{
				while( $ar = $this->db->fetch()->get() )
					return $ar["c"] > 0;
			}
		}
		throw new Exception( _("Database Error!"));
	}
	
	public function getGroupsWithMember($hostid)
	{
		$this->db->query("SELECT `gmGroupID` FROM `groupMembers` WHERE `gmHostID` = '%s'", array($hostid));
		
		while ($groupID = $this->db->fetch()->get('gmGroupID'))
		{
			//$groups[] = new Group($groupID);
			//$groups[] = $groupID;
		}
		
		//print_r($groups);exit;
		
		return (array)$groups;
		
		/*
		return ($count ? $count : 0);
		
		if ( $this->db != null && $hostid !== null && is_numeric( $hostid ) && $hostid >= 0 )
		{
			$sql = " = '" . $hostid . "'";
			
			$arGroupIDs = array();
			if ( $this->db->query($sql) )
			{
				while( $ar = $this->db->fetch()->get() )
				{
					$arGroupIDs[] = $ar["gmGroupID"];
				}
				
				$arGroups = array();
				if ( $arGroupIDs != null )
				{
					for( $i = 0; $i < count( $arGroupIDs); $i++ )
					{
						$tmpGroup = $arGroupIDs[$i];
						if ( $tmpGroup != null )
						{
							$arGroups[] = $tmpGroup;
						}
					}
				}
				return (array)$arGroups;
			}
		}
		return null;
		*/
	}
	
	public function searchOLD( $crit, $sortingOpts )
	{
		$arResults = array();
		if ( $this->db != null && $crit != null )
		{
			
		}
		return $arResults;
	}
	
	// function either returns true or throws an exception
	public function updateGroup( $group )
	{
		throw new Exception( _("Not implemented") );
		if ( $this->db != null && $host != null )
		{
			if ( ( self::UPDATE_GENERAL & $flags ) == 1 )
			{
				if ( ! $this->updateGeneral( $host ) )
					return false;
			}
		
			return true;
		}
		return false;
	}
	
	// groupExists() moved from functions.include.php - Blackout - 11:15 AM 23/09/2011
	function groupExists( $conn, $groupName, $id=-1 )
	{
		if ( $conn != null && $groupName != null )
		{
			$sql = "select count(*) as cnt from groups where groupName = '" . mysql_real_escape_string( $groupName ) . "' and groupID <> $id";
			$res = mysql_query( $sql, $conn ) or criticalError( mysql_error(), _("FOG :: Database error!") );
			if ( $ar = mysql_fetch_array( $res ) )
			{
				if ( $ar["cnt"] == 0 )
					return false;
			}
		}
		return true;
	}
}