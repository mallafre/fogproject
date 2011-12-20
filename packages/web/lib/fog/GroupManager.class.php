<?php

// Blackout - 3:10 PM 25/09/2011
class GroupManager extends FOGManagerController
{
	// Table
	public $databaseTable = 'groups';
	
	// Search query
	public $searchQuery = 'SELECT * FROM groups WHERE groupName LIKE "%${keyword}%"';

	// Custom methods
	public function getGroupByName($name)
	{
		$Group = new Group(array('name' => $name));
		$Group->load('name');
		
		return $Group;
	}
	
	
	
	
	
	// Legacy - remove when all updated
	public function createGroup($name, $user)
	{
		if ($this->DB != null && ! $this->doesGroupExist( $name ) && $name != null && strlen( $name ) > 0 && $user != null)
		{
			$sql = "INSERT 
						into 
					groups(groupName, groupCreateBy, groupDateTime) 
					values( '" . $this->DB->sanitize($name) . "', '" . $this->DB->sanitize($user->get('name')) . "', NOW() )";
			if ( $this->DB->query($sql)->affected_rows() == 1 )
				return $this->DB->getInsertID();
						
		}
		return -1;
	}
	
	public function addHostToGroup($groupid, $hostid)
	{
		if ( $this->DB != null && $groupid >= 0 && $hostid >= 0  && is_numeric( $groupid ) && is_numeric($hostid))
		{
			$sql = "INSERT 
						into 
					groupMembers(gmHostID, gmGroupID) 
					values( '" . $hostid . "', '" . $groupid . "' )";
			return ( $this->DB->query($sql)->affected_rows() == 1 );			
		}
		return false;
	}
	
	public function doesGroupExist( $name, $excludeid=-1)
	{
		if ( $this->DB != null )
		{
			$sql = "SELECT 
						COUNT(*) AS c 
					FROM 
						groups
					WHERE 
						groupName = '" . $this->DB->sanitize( $name ) . "' and
						groupID <> '" . $this->DB->sanitize( $excludeid ) . "'";
			if ( $this->DB->query($sql) )
			{
				while( $ar = $this->DB->fetch()->get() )
					return $ar["c"] > 0;
			}
		}
		throw new Exception( _("Database Error!"));
	}
	
	public function getGroupsWithMember($hostid)
	{
		$this->DB->query("SELECT `gmGroupID` FROM `groupMembers` WHERE `gmHostID` = '%s'", array($hostid));
		
		while ($groupID = $this->DB->fetch()->get('gmGroupID'))
		{
			//$groups[] = new Group($groupID);
			//$groups[] = $groupID;
		}
		
		//print_r($groups);exit;
		
		return (array)$groups;
		
		/*
		return ($count ? $count : 0);
		
		if ( $this->DB != null && $hostid !== null && is_numeric( $hostid ) && $hostid >= 0 )
		{
			$sql = " = '" . $hostid . "'";
			
			$arGroupIDs = array();
			if ( $this->DB->query($sql) )
			{
				while( $ar = $this->DB->fetch()->get() )
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
	
	// function either returns true or throws an exception
	public function updateGroup( $group )
	{
		throw new Exception( _("Not implemented") );
		if ( $this->DB != null && $host != null )
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
}