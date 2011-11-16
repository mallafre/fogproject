<?php

// Blackout - 9:08 AM 4/10/2011
class FOGPageManager
{
	private $nodes = array();
	private $nodeVariable = 'node';
	private $subVariable = 'sub';
	
	private $FOGCore;
	
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
			$this->info('Adding FOGPage: %s, Node: %s', array(get_class($class), $class->node));
		
			$this->nodes[$class->node] = $class;
		}
		catch (Exception $e)
		{
			$this->error('Failed to add Page: Node: %s, Class: %s, Error: %s', array($node, $class, $e->getMessage()));
		}
		
		return $this;
	}
	
	// Call FOGPage->method based on $node and $sub
	public function render()
	{
		try
		{
			// Variables
			$node = $GLOBALS[$this->nodeVariable];
			$sub = $GLOBALS[$this->subVariable];
			$class = $this->nodes[$node];
		
			// Error checking
			if (!array_key_exists($node, $this->nodes))
			{
				throw new Exception('No FOGPage Class found for this node');
			}
			if (!method_exists($class, $sub))
			{
				if (!empty($sub))
				{
					$this->error('Class: %s, Method: %s, Error: Method not found in class, defaulting to index()', array(get_class($class), $sub));
				}
				
				$sub = 'index';
			}
			
			// FOG - Default view
			if ($sub == 'index' && $this->FOGCore->getSetting('FOG_VIEW_DEFAULT_SCREEN') != 'LIST')
			{
				$sub = 'search';
			}
			
			// Arguments
			$args = (!empty($GLOBALS[$class->id]) ? array('id' => $GLOBALS[$class->id]) : array());
		
			// Render result to variable - we do this so we can send HTTP Headers in a class method
			// TODO: Create a better solution
			ob_start();
			call_user_method_array($sub, $class, array($args));
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
		$this->FOGCore->error('%s: %s', array(get_class($this), (count($data) ? vsprintf($txt, $data) : $txt)));
	}
	
	// Info
	protected function info($txt, $data = array())
	{
		$this->FOGCore->info('%s: %s', array(get_class($this), (count($data) ? vsprintf($txt, $data) : $txt)));
	}
}