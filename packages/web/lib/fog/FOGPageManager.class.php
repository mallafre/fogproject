<?php

// Blackout - 9:08 AM 4/10/2011
class FOGPageManager extends FOGBase
{
	// Debug & Info
	public $debug = true;
	public $info = false;
	
	private $pageTitle;
	private $nodes = array();
	
	private $classVariable = 'node';
	private $methodVariable = 'sub';
	
	private $classValue;
	private $methodValue;
	
	// Construct
	public function __construct()
	{
		// FOGBase Constructor
		parent::__construct();
	
		// Save class & method values into class - used many times through out
		$this->classValue = ($GLOBALS[$this->classVariable] ? preg_replace('#[^\w]#', '_', urldecode($GLOBALS[$this->classVariable])) : 'home');
		$this->methodValue = preg_replace('#[^\w]#', '_', urldecode($GLOBALS[$this->methodVariable]));	// No default value as we want to detect an empty string for 'list' or 'search' default page
	}
	
	// Util functions - easy access to class & child class data
	public function getFOGPageClass()
	{
		return $this->nodes[$this->classValue];
	}
	
	public function getFOGPageName()
	{
		return $this->getFOGPageClass()->name;
	}
	
	public function getFOGPageTitle()
	{
		return $this->getFOGPageClass()->title;
	}
	
	public function isFOGPageTitleEnabled()
	{
		return ($this->getFOGPageClass()->titleEnabled == true && !empty($this->getFOGPageClass()->title));
	}

	// Register FOGPage
	public function register($class)
	{
		try
		{
			if (!$class)
			{
				throw new Exception('Invalid Class');
			}
			if (!($class instanceof FOGPage))
			{
				throw new Exception('Class is not extended from FOGPage!');
			}
			
			// INFO
			//$this->info('Adding FOGPage: %s, Node: %s, Methods: %s', array(get_class($class), $class->node, implode('(), ', (array)get_class_methods($class->node)) . '()'));
			$this->info('Adding FOGPage: %s, Node: %s', array(get_class($class), $class->node));
		
			$this->nodes[$class->node] = $class;
		}
		catch (Exception $e)
		{
			$this->debug('Failed to add Page: Node: %s, Class: %s, Error: %s', array($this->classValue, $class, $e->getMessage()));
		}
		
		return $this;
	}
	
	// Call FOGPage->method based on $this->classValue and $this->methodValue
	public function render()
	{
		$this->loadPageClasses();
		
		try
		{
			// Variables
			$class = $this->getFOGPageClass();	// Class that will be used
			$method = $this->methodValue;		// Method that will be called in the above class. This value changes while $this->methodValue remains constant.
		
			// Error checking
			if (!array_key_exists($this->classValue, $this->nodes))
			{
				throw new Exception(sprintf('No FOGPage Class found for this node. You should try the old "includes" style management code <a href="%s">found here</a>', preg_replace("#index\.php#", 'indexold.php', $_SERVER['PHP_SELF']) . "?$_SERVER[QUERY_STRING]"));
			}
			
			// Figure out which method to call - default to index() if method is not found
			if (empty($method) || !method_exists($class, $method))
			{
				if (!empty($method) && $method != 'list')
				{
					$this->debug('Class: %s, Method: %s, Error: Method not found in class, defaulting to index()', array(get_class($class), $method));
				}
				
				$method = 'index';
			}
			
			// FOG - Default view override
			if ($this->methodValue != 'list' && $method == 'index' && $this->FOGCore->getSetting('FOG_VIEW_DEFAULT_SCREEN') != 'LIST' && method_exists($class, 'search'))
			{
				$method = 'search';
			}
			
			// POST - Append '_post' to method name if request method is POST and the method exists
			if ($this->FOGCore->isPOSTRequest() && method_exists($class, $method . '_post'))
			{
				$method = $method . '_post';
			}
			
			// AJAX - Append '_ajax' to method name if request is ajax and the method exists
			if ($this->FOGCore->isAJAXRequest() && method_exists($class, $method . '_ajax'))
			{
				$method = $method . '_ajax';
			}
			
			// Arguments
			$args = (!empty($GLOBALS[$class->id]) ? array('id' => $GLOBALS[$class->id]) : array());
		
			// Render result to variable - we do this so we can send HTTP Headers in a class method
			// TODO: Create a better solution
			ob_start();
			call_user_method_array($method, $class, array($args));
			$result = ob_get_contents();
			ob_end_clean();
			
			// Return result
			return $result;
		}
		catch (Exception $e)
		{
			$this->debug('Failed to Render Page: Node: %s, Error: %s', array($this->classValue, $e->getMessage()));
		}
		
		return false;
	}
	
	// Load FOGPage classes
	private function loadPageClasses()
	{
		if ($this->isLoaded('PageClasses'))
		{
			return;
		}
	
		// This variable is required as each class file uses it
		global $FOGPageManager;
	
		$path = BASEPATH . '/lib/pages';
		
		$iterator = new DirectoryIterator($path);
		foreach ($iterator as $fileInfo)
		{
			if ($fileInfo->isFile() && substr($fileInfo->getFilename(), -10) == '.class.php')
			{
				require_once($path . '/' . $fileInfo->getFilename());
			}
		}
	}
}