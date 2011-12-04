<?php

// Blackout - 9:02 PM 27/09/2011
class DatabaseManager
{
	public $type, $host, $user, $pass, $db;
	private $valid = false;

	function __construct($type, $host, $user, $pass, $db) 
	{
		try
		{
			if (!$type)
			{
				throw new Exception('Type not set');
			}
			if (!$host)
			{
				throw new Exception('Host not set');
			}
			if (!$user)
			{
				throw new Exception('User not set');
			}
			if (!$pass)
			{
				throw new Exception('Pass not set');
			}
			if (!$db)
			{
				throw new Exception('Database not set');
			}
			
			$this->type = $type;
			$this->host = $host;
			$this->user = $user;
			$this->pass = $pass;
			$this->db = $db;
			
			$this->valid = true;
		}
		catch (Exception $e)
		{
			$this->valid = false;
		
			$GLOBALS['FOGCore']->error('Failed: %s->%s(): Error: %s', array(get_class($this), __FUNCTION__, $e->getMessage()));
		}
		
		return false;
	}
	
	function connect()
	{
		try
		{
			if (!$this->valid)
			{
				throw new Exception('Class not constructed correctly');
			}
		
			switch($this->type)
			{
				case 'mysql':
					$this->db = new MySQL($this->host, $this->user, $this->pass, $this->db);
					
					return $this->db;	
					
					break;
				case 'mssql':
					break;
				case 'oracle':
					$db = new OracleOLD();
					$db->setCredentials( $this->user, $this->pass );
					$db->setHost( $this->host );
					$db->setSchema( $this->db );
					if ( $db->connect() )
					{
					
						$this->db = $db;
						return $db;				
					}
					break;								
				default:
					throw new Exception('Unknown database type');
			}
		}
		catch (Exception $e)
		{
			$GLOBALS['FOGCore']->error('Failed: %s->%s(): Error: %s', array(get_class($this), __FUNCTION__, $e->getMessage()));
		}
	}
 
	function getVersion()
	{
		try
		{
			if (!$this->db)
			{
				throw new Exception('Database not connected');
			}
			
			return $this->db->query('SELECT vValue FROM schemaVersion LIMIT 1')->fetch()->get('vValue');
		}
		catch (Exception $e)
		{
			$GLOBALS['FOGCore']->error('Failed: %s->%s(): Error: %s', array(get_class($this), __FUNCTION__, $e->getMessage()));
		}
		
		return false;
	}
}