<?php

// Blackout - 5:06 PM 27/12/2011
class FOGFTP extends FOGGetSet
{
	// Debug & Info
	public $debug = false;
	public $info = false;
	
	// Data
	public $data = array(
		'host'		=> '',
		'username'	=> '',
		'password'	=> '',
		'port'		=> 21,
		'timeout'	=> 10
	);
	
	// Links
	private $link;
	private $loginLink;
	private $lastConnectionHash;
	
	public $passiveMode = true;
	
	public function connect()
	{
		// Return if - already connected && last connection is the same || details unset
		$connectionHash = md5(serialize($this->data));
		if (($this->link && $this->lastConnectionHash == $connectionHash) || !$this->get('host') || !$this->get('username') || !$this->get('password') || !$this->get('port'))
		{
			return $this;
		}
		
		// Connect
		$this->link = @ftp_connect($this->get('host'), $this->get('port'), $this->get('timeout'));
		if (!$this->link)
		{
			$error = error_get_last();
			throw new Exception(sprintf('%s: Failed to connect. Host: %s, Error: %s', get_class($this), $this->get('host'), $error['message']));
		}
		
		// Login
		if (!$this->loginLink = @ftp_login($this->link, $this->get('username'), $this->get('password')))
		{
			$error = error_get_last();
			throw new Exception(sprintf('%s: Login failed. Host: %s, Username: %s, Password: %s, Error: %s', get_class($this), $this->get('host'), $this->get('username'), $this->get('password'), $error['message']));
		}
		
		if ($this->passiveMode)
		{
			ftp_pasv($this->link, true);
		}
		
		// Store connection hash
		$this->lastConnectionHash = $connectionHash;
		
		// Return
		return $this;
	}
	
	public function close($if = true)
	{
		// Only if connected
		if ($this->link && $if)
		{
			// Disconnect
			@ftp_close($this->link);
			
			// unset connection variable
			unset($this->link);
		}
		
		// Return
		return $this;
	}
	
	public function put($remotePath, $localPath, $mode = FTP_ASCII)
	{
		// Put file
		if (!@ftp_put($this->link, $remotePath, $localPath, $mode))
		{
			$error = error_get_last();
			throw new Exception(sprintf('%s: Failed to %s file. Remote Path: %s, Local Path: %s, Error: %s', get_class($this), __FUNCTION__, $remotePath, $localPath, $error['message']));
		}
		
		// Return
		return $this;
	}
	
	public function delete($path)
	{
		// Put file
		if (!@ftp_delete($this->link, $path))
		{
			$error = error_get_last();
			throw new Exception(sprintf('%s: Failed to %s file. Path: %s, Error: %s', get_class($this), __FUNCTION__, $path, $error['message']));
		}
		
		// Return
		return $this;
	}
}