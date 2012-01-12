<?php

// Blackout - 9:02 PM 27/09/2011
class DatabaseManager
{
	public $type, $host, $user, $pass, $database;
	public $DB;
	private $valid = false;

	function __construct($type, $host, $user, $pass, $database) 
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
			if (!$database)
			{
				throw new Exception('Database not set');
			}
			
			$this->type = $type;
			$this->host = $host;
			$this->user = $user;
			$this->pass = $pass;
			$this->database = $database;
			
			$this->valid = true;
		}
		catch (Exception $e)
		{
			$this->valid = false;
		
			FOGCore::error('Failed: %s->%s(): Error: %s', array(get_class($this), __FUNCTION__, $e->getMessage()));
		}
		
		return false;
	}
	
	function connect()
	{
		try
		{
			// Error checking
			if (!$this->valid)
			{
				throw new Exception('Class not constructed correctly');
			}
			
			// Determine database host type
			switch($this->type)
			{
				case 'mysql':
					$this->DB = new MySQL($this->host, $this->user, $this->pass, $this->database);
					
					break;
				case 'mssql':
					break;
				case 'oracle':
					$db = new OracleOLD();
					$db->setCredentials( $this->user, $this->pass );
					$db->setHost( $this->host );
					$db->setSchema( $this->DB );
					if ( $db->connect() )
					{
					
						$this->DB = $db;			
					}
					break;								
				default:
					throw new Exception(sprintf('Unknown database type. Check that DATABASE_TYPE is being set in "%s/commons/config.php"', rtrim($_SERVER['DOCUMENT_ROOT'], '/') . dirname($_SERVER['PHP_SELF'])));
			}
			
			// Database Schema version check
			if ($this->getVersion() < FOG_SCHEMA && !preg_match('#schemaupdater#i', $_SERVER['PHP_SELF']))
			{
				FOGCore::redirect('../commons/schemaupdater/index.php?redir=1');
			}
		}
		catch (Exception $e)
		{
			FOGCore::error('Failed: %s->%s(): Error: %s', array(get_class($this), __FUNCTION__, $e->getMessage()));
		}
		
		return $this;
	}
	
	public function close()
	{
		if ($this->DB)
		{
			$this->DB->close();
		}
		
		return $this;
	}
 
	function getVersion()
	{
		try
		{
			// Error checking
			if (!$this->DB)
			{
				throw new Exception('Database not connected');
			}
			
			// Get version
			$version = $this->DB->query('SELECT vValue FROM schemaVersion LIMIT 1')->fetch()->get('vValue');
			
			// Return version OR 0 (for new install) if query failed
			return ($version ? $version : 0);
		}
		catch (Exception $e)
		{
			FOGCore::error('Failed: %s->%s(): Error: %s', array(get_class($this), __FUNCTION__, $e->getMessage()));
		}
		
		return false;
	}
}
