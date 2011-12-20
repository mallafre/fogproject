<?php

// Blackout - 10:15 AM 1/10/2011
class MACAddress
{
	private $MAC;
	
	public $debug = false;
	public $info = false;

	public function __construct($MAC)
	{
		return $this->setMAC($MAC);
	}
	
	public function setMAC($MAC)
	{
		try
		{
			$MAC = trim($MAC);
		
			if ($MAC instanceof MACAddress)
			{
				$MAC = $MAC->__toString();
			}
			elseif (strlen($MAC) == 12)
			{
				for ($i = 0; $i < 12; $i = $i + 2)
				{
					$newMAC[] = $MAC{$i} . $MAC{$i + 1};
				}
				
				$MAC = implode(':', $newMAC);
			}
			elseif (strlen($MAC) == 17)
			{
				$MAC = str_replace('-', ':', $MAC);
			}
			else
			{
				throw new Exception('');
			}
			
			$this->MAC = $MAC;
		}
		catch (Exception $e)
		{
			if ($this->debug)
			{
				$GLOBALS['FOGCore']->debug('Invalid MAC Address: MAC: %s', $MAC);
			}
		}
		
		return $this;
	}
	
	public function getMACWithColon() 
	{ 
		return str_replace('-', ':', strtolower($this->MAC));
	}
	
	public function getMACWithDash() 
	{ 
		return str_replace(':', '-', strtolower($this->MAC));
	}
	
	public function getMACImageReady()
	{
		return '01-' . strtolower($this->getMACWithDash());
	}
	
	public function getMACPrefix()
	{
		return substr($this->getMACWithDash(), 0, 8);
	}

	public function __toString()
	{
		return $this->getMACWithColon();
	}
	
	public function isValid()
	{
		return ($this->MAC != '' ? preg_match('#^([0-9a-fA-F][0-9a-fA-F][:-]){5}([0-9a-fA-F][0-9a-fA-F])$#', $this) : false);
	}
	
	public function startsWith($txt)
	{
		return (preg_match('#^' . str_replace('-', ':', $txt) . '#i', $this->__toString()) ? true : false);
	}
	
	public function endsWith($txt)
	{
		return (preg_match('#' . str_replace('-', ':', $txt) . '$#i', $this->__toString()) ? true : false);
	}
	
	public function contains($txt)
	{
		return (preg_match('#' . str_replace('-', ':', $txt) . '#i', $this->__toString()) ? true : false);
	}
	
	public function getHost()
	{
		// Create new Host Object with MAC Address specified
		$Host = new Host(array('mac' => $this->__toString()));
		
		// Load data based off 'mac' field data 
		$Host->load('mac');
		
		// Return
		return $Host;
	}
	
	public function getTask()
	{
	}
}