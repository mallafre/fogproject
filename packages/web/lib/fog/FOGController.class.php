<?php

// Blackout - 1:28 PM 23/09/2011
abstract class FOGController
{
	// Table
	protected $databaseTable = '';
	
	// Name -> Database field name
	protected $databaseFields = array();
	
	// Do not update these database fields
	protected $databaseFieldsToIgnore = array(
		'createdBy',
		'createdTime'
	);
	
	// Allow setting / getting of these additional fields
	protected $additionalFields = array();
	
	// Required database fields
	protected $databaseFieldsRequired = array(
		'id',
		'name'
	);
	
	// Store data array
	protected $data = array();
	
	// Auto save class data on __destruct
	protected $autoSave = false;
	
	// DEBUG mode - print all Errors & SQL queries
	protected $debug = true;	
	
	// FOG Database Class
	protected $db;
	
	// FOG Core Class
	protected $core;
	
	// Construct
	public function __construct($data)
	{
		try
		{
			// Error checking
			if (!count($this->databaseFields))
			{
				throw new Exception('No database fields defined for this class!');
			}
			
			// Flip database fields and common name - used multiple times
			$this->databaseFieldsFlipped = array_flip($this->databaseFields);
			
			// Database
			$this->db = $GLOBALS['db'];
			$this->core = $GLOBALS['core'];
			
			// Created By
			if (array_key_exists('createdBy', $this->databaseFields) && !empty($_SESSION['FOG_USER']))
			{
				$this->set('createdBy', $_SESSION['FOG_USER']);
			}
			
			// Add incoming data
			if (is_array($data))
			{
				// Iterate data -> Set data
				foreach ($data AS $key => $value)
				{
					$this->set($key, $value);
				}
			}
			// If incoming data is an INT -> Set as ID -> Load from database
			elseif (is_numeric($data))
			{
				if ($data <= 0)
				{
					throw new Exception(sprintf('ID less than or equal to 0: Data: %s', $data));
					//return false;
				}
				
				$this->set('id', $data)->load();
			}
			// Unknown data format
			else
			{
				throw new Exception('No data array or ID passed!');
			}
		}
		catch (Exception $e)
		{
			$this->error('Create Class Failed: Class: %s, Error: %s', array(get_class($this), $e->getMessage()));
		}
		
		//var_dump($this);
		//exit;
		
		return $this;
	}
	
	// Destruct
	public function __destruct()
	{
		// Auto save
		if ($this->autoSave)
		{
			$this->save();
		}
	}
	
	// Set
	public function set($key, $value)
	{
		try
		{
			if (!array_key_exists($key, $this->databaseFields) && !in_array($key, $this->additionalFields) && !array_key_exists($key, $this->databaseFieldsFlipped))
			{
				throw new Exception('Invalid data being set');
			}
			
			if (array_key_exists($key, $this->databaseFieldsFlipped))
			{
				$key = $this->databaseFieldsFlipped[$key];
			}
			
			$this->data[$key] = $value;
		}
		catch (Exception $e)
		{
			$this->error('Set Failed: Class: %s, Key: %s, Value: %s, Error: %s', array(get_class($this), $key, $value, $e->getMessage()));
		}
		
		return $this;
	}
	
	// Get
	public function get($key = '')
	{
		return (!empty($key) && isset($this->data[$key]) ? $this->data[$key] : (empty($key) ? $this->data : ''));
	}
	
	// Add
	public function add($key, $value)
	{
		try
		{
			if (!array_key_exists($key, $this->databaseFields) && !in_array($key, $this->additionalFields) && !array_key_exists($key, $this->databaseFieldsFlipped))
			{
				throw new Exception('Invalid data being set');
			}
			
			if (array_key_exists($key, $this->databaseFieldsFlipped))
			{
				$key = $this->databaseFieldsFlipped[$key];
			}
			
			$this->data[$key][] = $value;
		}
		catch (Exception $e)
		{
			$this->error('Add Failed: Class: %s, Key: %s, Value: %s, Error: %s', array(get_class($this), $key, $value, $e->getMessage()));
		}
		
		return $this;
	}
	
	// Remove
	public function remove($key, $object)
	{
		try
		{
			if (!array_key_exists($key, $this->databaseFields) && !in_array($key, $this->additionalFields) && !array_key_exists($key, $this->databaseFieldsFlipped))
			{
				throw new Exception('Invalid data being set');
			}
			
			if (array_key_exists($key, $this->databaseFieldsFlipped))
			{
				$key = $this->databaseFieldsFlipped[$key];
			}
			
			foreach ((array)$this->data[$key] AS $i => $data)
			{
				if ($data->get('id') != $object->get('id'))
				{
					$newDataArray[] = $data;
				}
			}
			
			$this->data[$key] = (array)$newDataArray;
		}
		catch (Exception $e)
		{
			$this->error('Remove Failed: Class: %s, Key: %s, Object: %s, Error: %s', array(get_class($this), $key, $object, $e->getMessage()));
		}
		
		return $this;
	}
	
	// Save
	public function save()
	{
		try
		{
			// Error checking
			if (!$this->isTableDefined())
			{
				throw new Exception('No Table defined for this class');
			}
			
			// Variables
			$fieldData = array();
			$fieldsToUpdate = $this->databaseFields;
			$fieldToName = array_flip($this->databaseFields);
			
			// Remove unwanted fields for update query
			foreach ($this->databaseFields AS $name => $fieldName)
			{
				if (in_array($name, $this->databaseFieldsToIgnore))
				{
					unset($fieldsToUpdate[$name]);
				}
			}
			
			// Build insert key and value arrays
			foreach ($this->databaseFields AS $name => $fieldName)
			{
				if ($this->get($name) != '')
				{
					$insertKeys[] = $this->db->sanitize($fieldName);
					$insertValues[] = $this->db->sanitize($this->get($name));
				}
			}
			
			// Build update field array using filtered data
			foreach ($fieldsToUpdate AS $name => $fieldName)
			{
				if ($this->get($name) != '')
				{
					$updateData[] = sprintf("`%s`='%s'", $this->db->sanitize($fieldName), $this->db->sanitize($this->get($name)));
				}
			}
			
			// Insert & Update query all-in-one
			$query = sprintf("INSERT INTO `%s` (`%s`) VALUES ('%s') ON DUPLICATE KEY UPDATE %s",
				$this->db->sanitize($this->databaseTable),
				implode("`, `", $insertKeys),
				implode("', '", $insertValues),
				implode(', ', $updateData)
			);

			if (!$this->db->query($query))
			{
				// Query failed
				throw new Exception($this->db->error());
			}
			
			// Database query was successful - set ID if ID was not set
			if (!$this->get('id'))
			{
				$this->set('id', $this->db->insert_id());
			}
			
			// Success
			return true;
		}
		catch (Exception $e)
		{
			$this->error('Database Save Failed: Class: %s, ID: %s, Error: %s', array(get_class($this), $this->get('id'), $e->getMessage()));
		}
	
		// Fail
		return false;
	}
	
	// Load
	public function load($field = 'id')
	{
		try
		{
			// Error checking
			if (!$this->isTableDefined())
			{
				throw new Exception('No Table defined for this class');
			}
			if (!$this->get($field))
			{
				throw new Exception(sprintf('Operation field not set: %s', strtoupper($field)));
			}
			
			// Variables
			$fieldToName = array_flip($this->databaseFields);
			
			// Build query
			if (is_array($this->get($field)))
			{
				// Multiple values
				foreach ($this->get($field) AS $fieldValue)
				{
					$fieldData[] = sprintf("`%s`='%s'", $this->db->sanitize($this->databaseFields[$field]), $this->db->sanitize($fieldValue));
				}
				
				$query = sprintf("SELECT * FROM `%s` WHERE %s",
					$this->db->sanitize($this->databaseTable),
					implode(' OR ', $fieldData)
				);
			}
			else
			{
				// Single value
				$query = sprintf("SELECT * FROM `%s` WHERE `%s`='%s'",
					$this->db->sanitize($this->databaseTable),
					$this->db->sanitize($this->databaseFields[$field]),
					$this->db->sanitize($this->get($field))
				);
			}
			
			// Did we find a row in the database?
			if (!$queryData = $this->db->query($query)->fetch()->get())
			{
				throw new Exception(($this->db->error() ? $this->db->error() : 'Row not found'));
			}
			
			// Loop returned rows -> Set new data
			foreach ($queryData AS $key => $value)
			{
				$this->set($fieldToName[$key], (string)$value);
			}
			
			// Success
			return true;
		}
		catch (Exception $e)
		{
			$this->error('Database Load Failed: Class: %s, ID: %s, Error: %s', array(get_class($this), $this->get('id'), $e->getMessage()));
		}
	
		// Fail
		return false;
	}
	
	// Destroy
	public function destroy($field = 'id')
	{
		try
		{
			// Error checking
			if (!$this->isTableDefined())
			{
				throw new Exception('No Table defined for this class');
			}
			if (!$this->get($field))
			{
				throw new Exception(sprintf('Operation field not set: %s', strtoupper($field)));
			}
			
			// Variables
			$fieldToName = array_flip($this->databaseFields);
			
			// Query row data
			$query = sprintf("DELETE FROM `%s` WHERE `%s`='%s'",
				$this->db->sanitize($this->databaseTable),
				$this->db->sanitize($this->databaseFields[$field]),
				$this->db->sanitize($this->get($field))
			);
			
			// Did we find a row in the database?
			if (!$queryData = $this->db->query($query)->fetch()->get())
			{
				throw new Exception('Failed to delete');
			}
			
			// Success
			return true;
		}
		catch (Exception $e)
		{
			$this->error('Database Destroy Failed: Class: %s, ID: %s, Error: %s', array(get_class($this), $this->get('id'), $e->getMessage()));
		}
	
		// Fail
		return false;
	}
	
	// Key
	public function key($key)
	{
		if (array_key_exists($key, $this->databaseFieldsFlipped))
		{
			return $this->databaseFieldsFlipped[$key];
		}
		
		return $key;
	}
	
	// Error
	protected function error($txt, $data = array())
	{
		if ($this->debug)
		{
			$GLOBALS['FOGCore']->error('%s: %s', array(get_class($this), (count($data) ? vsprintf($txt, $data) : $txt)));
		}
	}
	// Info
	protected function info($txt, $data = array())
	{
		if ($this->debug)
		{
			$GLOBALS['FOGCore']->info('%s: %s', array(get_class($this), (count($data) ? vsprintf($txt, $data) : $txt)));
		}
	}
	
	// isValid
	public function isValid()
	{
		try
		{
			foreach ($this->databaseFieldsRequired AS $field)
			{
				if (!$this->get($field))
				{
					throw new Exception(_('Required database field is empty'));
				}
			}
			
			if ($this->get('id') || $this->get('name'))
			{
				return true;
			}
		}
		catch (Exception $e)
		{
			$this->error('isValid Failed: Class: %s, Error: %s', array(get_class($this), $e->getMessage()));
		}
		
		return false;
	}
	
	// isTableDefined 
	private function isTableDefined()
	{
		return (!empty($this->databaseTable) ? true : false);
	}
	
	// Name is returned if class is printed
	public function __toString()
	{
		return ($this->get('name') ? $this->get('name') : sprintf('%s #%s', get_class($this), $this->get('id')));
	}
}