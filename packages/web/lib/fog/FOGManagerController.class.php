<?php

// Blackout - 10:02 AM 25/09/2011
abstract class FOGManagerController
{
	// Table
	protected $databaseTable = '';
	
	// Search query
	protected $searchQuery = '';
	
	// DEBUG mode - print all Errors & SQL queries
	protected $debug = true;	
	
	// FOG Database Class
	protected $db;
	
	// FOG Core Class
	protected $FOGCore;
	
	// Child class name
	protected $childClass;
	
	// Construct
	public function __construct()
	{
		// Legacy
		$this->db = $GLOBALS['db'];
		$this->FOGCore = $GLOBALS['FOGCore'];
	
		// Set child classes name
		$this->childClass = preg_replace('#Manager$#', '', get_class($this));
	}
	
	// Search
	public function search($keyword = '%')
	{
		try
		{
			// Error checking
			if (empty($this->searchQuery))
			{
				throw new Exception('No query defined');
			}
			if (empty($keyword))
			{
				throw new Exception('No keyword passed');
			}
			
			// Build query
			$keyword = preg_replace(array('#\*#', '#[[:space:]]#'), array('%', '%'), $keyword);
			$query = preg_replace(array('#\$\{keyword\}#'), array($keyword), $this->searchQuery);
			
			// Execute query -> Build new object -> Push into data array
			$allSearchResults = $this->db->query($query);
			while ($searchResult = $this->db->fetch()->get())
			{
				$data[] = new $this->childClass($searchResult);
			}
			
			// Return
			return (array)$data;
		}
		catch (Exception $e)
		{
			$this->error('Search failed! Class: %s, Error: %s', array(get_class($this), $e->getMessage()));
		}
		
		return false;
	}

	public function find($where = array(), $whereOperator = 'AND')
	{
		try
		{
			// Error checking
			if (empty($this->databaseTable))
			{
				throw new Exception('No database table defined');
			}
			
			if (count($where))
			{
				foreach ($where AS $field => $value)
				{
					if (is_array($value))
					{
						$whereArray[] = sprintf("`%s` IN ('%s')", $field, implode("', '", $value));
					}
					else
					{
						$whereArray[] = sprintf("`%s`='%s'", $field, $value);
					}
				}
			}

			// Select all
			$this->db->query("SELECT * FROM `%s`%s", array($this->databaseTable, (count($whereArray) ? ' WHERE ' . implode(' ' . $whereOperator . ' ', $whereArray) : '')));
			while ($row = $this->db->fetch()->get())
			{
				//$data[] = $row;
				$data[] = new $this->childClass($row);
			}
			
			// Return
			return (array)$data;
		}
		catch (Exception $e)
		{
			$this->error('Find all failed! Class: %s, Error: %s', array(get_class($this), $e->getMessage()));
		}
		
		return false;
	}
	
	// Blackout - 11:28 AM 22/11/2011
	function buildSelectBox($matchID = '', $elementName = '')
	{
		if (empty($elementName))
		{
			$elementName = strtolower($this->childClass);
		}
	
		foreach ($this->find() AS $Object)
		{
			$listArray[] = sprintf('<option value="%s"%s>[%s] %s</option>', $Object->get('id'), ($matchID == $Object->get('id') ? ' selected="selected"' : ''), $Object->get('id'), $Object->get('name'));
		}
		
		return (isset($listArray) ? sprintf('<select name="%s"><option value="">- %s -</option>%s</select>', $elementName, _('Please select an option'), implode("\n", $listArray)) : false);
	}
	
	// Error
	protected function error($txt, $data = array())
	{
		if ($this->debug)
		{
			$this->FOGCore->error('%s: %s', array(get_class($this), (count($data) ? vsprintf($txt, $data) : $txt)));
		}
	}
	// Info
	protected function info($txt, $data = array())
	{
		if ($this->debug)
		{
			$this->FOGCore->info('%s: %s', array(get_class($this), (count($data) ? vsprintf($txt, $data) : $txt)));
		}
	}
}