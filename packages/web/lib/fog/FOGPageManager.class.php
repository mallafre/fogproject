<?php

// Blackout - 9:08 AM 4/10/2011
class FOGPageManager
{
	private $nodes = array();
	private $nodeVariable = 'node';
	private $subVariable = 'sub';
	
	private $FOGCore;
	
	private $debug = true;
	private $info = false;
	
	private $pageTitle;
	
	public function __construct()
	{
		$this->FOGCore = $GLOBALS['FOGCore'];
	}
	
	// Load FOGPage classes
	public function load()
	{
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
		
		return $this;
	}

	// Add FOGPage
	public function add($class)
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
			$this->error('Failed to add Page: Node: %s, Class: %s, Error: %s', array($node, $class, $e->getMessage()));
		}
		
		return $this;
	}
	
	public function getFOGPageClass()
	{
		return $this->nodes[$GLOBALS[$this->nodeVariable]];
	}
	
	public function getFOGPageTitle()
	{
		return $this->getFOGPageClass()->title;
	}
	
	public function isFOGPageTitleDisplayEnabled()
	{
		return ($this->getFOGPageClass()->titleDisplay == true && !empty($this->getFOGPageClass()->title));
	}
	
	// Call FOGPage->method based on $node and $sub
	public function render()
	{
		try
		{
			// Variables
			$node = $GLOBALS[$this->nodeVariable];
			$sub = $method = $GLOBALS[$this->subVariable];
			$class = $this->getFOGPageClass();
		
			// Error checking
			if (!array_key_exists($node, $this->nodes))
			{
				throw new Exception('No FOGPage Class found for this node');
			}
			
			// Figure out which method to call - default to index() if method is not found
			if (empty($sub) || !method_exists($class, $sub))
			{
				if (!empty($sub) && $sub != 'list')
				{
					$this->error('Class: %s, Method: %s, Error: Method not found in class, defaulting to index()', array(get_class($class), $sub));
				}
				
				$method = 'index';
			}
			
			// FOG - Default view override
			if ($sub != 'list' && $method == 'index' && $this->FOGCore->getSetting('FOG_VIEW_DEFAULT_SCREEN') != 'LIST')
			{
				$method = 'search';
			}
			
			// POST - Change method to 'method_post', if the method exists
			if ($_SERVER['REQUEST_METHOD'] == 'POST' && method_exists($class, $method . '_post'))
			{
				$method = $method . '_post';
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
			$this->error('Failed to Render Page: Node: %s, Error: %s', array($node, $e->getMessage()));
		}
		
		return false;
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
		if ($this->info)
		{
			$this->FOGCore->info('%s: %s', array(get_class($this), (count($data) ? vsprintf($txt, $data) : $txt)));
		}
	}
}