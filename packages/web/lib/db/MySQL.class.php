<?php

// Blackout - 8:52 PM Thursday, April 26, 2007
// Last Update: 10:27 AM 27/09/2011

class MySQL
{
	private $host, $user, $pass, $db, $startTime, $result, $queryResult, $link, $query;
	
	// Cannot use constants as you cannot access constants from $this->db::ROW_ASSOC
	public $ROW_ASSOC = 1;	// MYSQL_ASSOC
	public $ROW_NUM = 2;	// MYSQL_NUM
	public $ROW_BOTH = 3;	// MYSQL_BOTH
	
	public $debug = false;
	
	function __construct($host, $user, $pass, $db = '')
	{
		try
		{
			if (!function_exists('mysql_connect'))
			{
				throw new Exception(sprintf('%s PHP extension not loaded', __CLASS__));
			}
			
			$this->host = $host;
			$this->user = $user;
			$this->pass = $pass;
			$this->db = $db;
			
			if (!$this->connect())
			{
				throw new Exception('Failed to connect');
			}
			
			$this->startTime = $this->now();
		}
		catch (Exception $e)
		{
			$this->debug(sprintf('Could not %s(): %s', __FUNCTION__, $e->getMessage()));
		}
	}
	
	function __destruct()
	{
		try
		{
			if (!$this->link)
			{
				return;
			}
			
			if ($this->link && !mysql_close($this->link))
			{
				throw new Exception('Could not disconnect');
			}
		}
		catch (Exception $e)
		{
			$this->debug(sprintf('Could not %s(): %s', __FUNCTION__, $e->getMessage()));
		}
	}
	
	public function close()
	{
		$this->__destruct();
	}
	
	public function connect()
	{
		try
		{
			if ($this->link)
			{
				$this->close();
			}
			
			if (!$this->link = @mysql_connect($this->host, $this->user, $this->pass))
			{
				throw new Exception(sprintf('Host: %s, Username: %s, Password: %s, Database: %s', $this->host, $this->user, $this->pass, $this->db));
			}
			
			if ($this->db)
			{
				$this->select_db($this->db);
			}
		}
		catch (Exception $e)
		{
			$this->debug(sprintf('Could not %s(): %s', __FUNCTION__, $e->getMessage()));
		}
		
		return $this;
	}
	
	public function query($sql, $data = array())
	{
		try
		{
			// printf
			if (!is_array($data))
			{
				//throw new Exception('printf data passed, but not an array!');
				
				$data = array($data);
			}
			if (count($data))
			{
				$sql = vsprintf($sql, $data);
			}
			
			// Query
			$this->query = $sql;
			$this->queryResult = mysql_query($this->query, $this->link) or $this->debug($this->error(), $this->query);
			
			// INFO
			$this->info($this->query);
		}
		catch (Exception $e)
		{
			$this->debug(sprintf('Could not %s(): %s', __FUNCTION__, $e->getMessage()));
		}
		
		return $this;
	}
	
	public function fetch($type = MYSQL_ASSOC)
	{
		try
		{
			if (!$this->queryResult)
			{
				throw new Exception('No query result present. Use query() first');
			}
			
			if ($this->queryResult === false)
			{
				// queryResult is false - error in query?
				$this->result = false;
			}
			elseif ($this->queryResult === true)
			{
				// queryResult is true - query was successful, but did not return any rows - i.e. delete, update, etc
				$this->result = true;
			}
			else
			{
				// queryResult is good
				$this->result = mysql_fetch_array($this->queryResult, $type);
			}
			
			//return $this->result;
		}
		catch (Exception $e)
		{
			$this->debug(sprintf('Could not %s(): %s', __FUNCTION__, $e->getMessage()));
		}
		
		//return false;
		return $this;
	}
	
	public function result()
	{
		return $this->result;
	}
	
	public function queryResult()
	{
		return $this->queryResult;
	}
	
	public function get($field = '')
	{
		try
		{
			if ($this->result === false)
			{
				// result finished
				return false;
			}
			if ($field && !array_key_exists($field, $this->result))
			{
				throw new Exception(sprintf('No field found in results: Field: %s', $field));
			}
			
			return ($field ? $this->result[$field] : $this->result);
		}
		catch (Exception $e)
		{
			$this->debug(sprintf('Could not %s(): %s', __FUNCTION__, $e->getMessage()));
		}
		
		return false;
	}
	
	public function select_db($db)
	{
		try
		{
			if (!mysql_select_db($db, $this->link))
			{
				throw new Exception("$db");
			}
			
			$this->db = $db;
		}
		catch (Exception $e)
		{
			$this->debug(sprintf('Could not %s(): %s', __FUNCTION__, $e->getMessage()));
		}
		
		return $this;
	}

	public function error()
	{
		return mysql_error();
	}
	
	public function insert_id()
	{
		$id = mysql_insert_id($this->link);
		
		return ($id ? $id : 0);
	}
	
	public function affected_rows()
	{
		$count = mysql_affected_rows($this->link);
		
		return ($count ? $count : 0);
	}
	
	public function num_rows()
	{
		try
		{
			if (!$this->queryResult)
			{
				throw new Exception('No query result present. Use query() first');
			}
			
			return mysql_num_rows($this->queryResult);
		}
		catch (Exception $e)
		{
			$this->debug(sprintf('Could not %s(): %s', __FUNCTION__, $e->getMessage()));
		}
		
		return 0;
	}

	public function age()
	{
		return ($this->now() - $this->startTime);
	}
	
	private function now()
	{
		return microtime(true);
	}
	
	public function escape($data)
	{
		return $this->sanitize($data);
	}
	
	public function sanitize($data)
	{
		if (!is_array($data))
		{
			return $this->clean($data);
		}
		
		foreach ($data AS $key => $val)
		{
			if (is_array($val))
			{
				$data[$this->clean($key)] = $this->escape($val);
			}
			else
			{
				$data[$this->clean($key)] = $this->clean($val);
			}
		}
		
		return $data;
	}
	
	private function clean($data)
	{
		return (get_magic_quotes_gpc() ? mysql_real_escape_string(stripslashes($data)) : mysql_real_escape_string($data));;
	}
	
	// For legacy $conn connections
	public function getLink()
	{
		return $this->link;
	}
	
	// Error
	private function debug($txt, $data = array())
	{
		if ($this->debug)
		{
			$GLOBALS['FOGCore']->error('%s: %s', array(get_class($this), (count($data) ? vsprintf($txt, $data) : $txt)));
		}
	}
	
	// Info
	private function info($txt, $data = array())
	{
		if ($this->debug)
		{
			$GLOBALS['FOGCore']->info('%s: %s', array(get_class($this), (count($data) ? vsprintf($txt, $data) : $txt)));
		}
	}
}