<?php

// Blackout - 5:55 PM 20/12/2011
abstract class FOGBase
{
	// Debug & Info
	public $debug = false;
	public $info = false;
	
	// Class variables
	public $FOGCore;
	public $DB;
	public $HookManager;
	public $FOGUser;
	
	// LEGACY
	public $db;
	public $conn;
	
	// isLoaded counter
	protected $isLoaded = array();

	// Construct
	public function __construct()
	{
		// Class setup
		$this->FOGCore = $GLOBALS['FOGCore'];
		$this->DB = $this->FOGCore->DB;
		$this->HookManager = $GLOBALS['HookManager'];
		$this->FOGUser = $GLOBALS['currentUser'];
		
		// LEGACY
		$this->db = $this->FOGCore->DB;
		$this->conn = $GLOBALS['conn'];
		
		//printf('Creating Class: %s', get_class($this));
	}
	
	// Error - results in FOG halting with an error message
	public function error($txt, $data = array())
	{
		//if (!$this->isAJAXRequest() && !preg_match('#/service/#', $_SERVER['PHP_SELF']))
		if (!preg_match('#/service/#', $_SERVER['PHP_SELF']))
		{
			printf('<div class="debug-error">FOG ERROR: %s: %s</div>%s', get_clasS($this), (count($data) ? vsprintf($txt, $data) : $txt), "\n");
			exit;
		}
		
		// TODO: Log to Database
	}
	
	// Debug - message is shown if debug is enabled for that class
	public function debug($txt, $data = array())
	{
		if ((!isset($this) || (isset($this->debug) && $this->debug === true)) && !FOGCore::isAJAXRequest() && !preg_match('#/service/#', $_SERVER['PHP_SELF']))
		{
			printf('<div class="debug-error">FOG DEBUG: %s: %s</div>%s', get_clasS($this), (count($data) ? vsprintf($txt, $data) : $txt), "\n");
		}
		
		// TODO: Log to Database
	}
	
	// Info - message is shown if info is enabled for that class
	public function info($txt, $data = array())
	{
		//printf('Class: %s, Info: %s', get_class($this), ($this->info === true ? 'true' : 'false'));
		
		// !isset gets used when a call is made without a class context. i.e. FOGCore::info('foo bah');
		if ((!isset($this) || (isset($this->info) && $this->info === true)) && !FOGCore::isAJAXRequest() && !preg_match('#/service/#', $_SERVER['PHP_SELF']))
		{
			printf('<div class="debug-info">FOG INFO: %s: %s</div>%s', get_clasS($this), (count($data) ? vsprintf($txt, $data) : $txt), "\n");
		}
		
		// TODO: Log to Database
	}
	
	// toString
	public function __toString()
	{
		return get_class($this);
	}
	
	// isLoaded
	public function isLoaded($key)
	{
		$result = (isset($this->isLoaded[$key]) ? $this->isLoaded[$key] : 0);
		$this->isLoaded[$key]++;
		
		//printf('key: %s, value: %s', $key, $result);
		
		return ($result ? $result : false);
	}
}