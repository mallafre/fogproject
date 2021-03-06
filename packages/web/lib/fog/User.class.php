<?php

// Blackout - 6:01 PM 28/09/2011
class User extends FOGController
{
	// Variables
	public $inactivitySessionTimeout = 1;			// In hours
	public $regenerateSessionTimeout = 0.5;		// In hours

	// Table
	public $databaseTable = 'users';
	
	// Name -> Database field name
	public $databaseFields = array(
		'id'		=> 'uId',
		'name'		=> 'uName',
		'password'	=> 'uPass',
		'createdTime'	=> 'uCreateDate',
		'createdBy'	=> 'uCreateBy',
		'type'		=> 'uType'
	);
	
	// Allow setting / getting of these additional fields
	public $additionalFields = array(
		'authIP',
		'authTime',
		'salt'
	);
	
	// Overrides
	public function __construct($data)
	{
		// FOGController constructor
		parent::__construct($data);
		
		// Add password salt
		if (!$this->get('salt'))
		{
			$this->set('salt', uniqid());
		}
	}
	
	public function set($key, $value)
	{
		if ($this->key($key) == 'password' && strlen($value) != 32)
		{
			// TODO: Convert to this better password hashing
			//$value = md5(md5($value) . $this->get('salt'));
			$value = md5($value);
		}
		
		// Set
		return parent::set($key, $value);
	}
	
	public function isLoggedIn()
	{
		// Has IP Address has changed
		if (!$_SERVER['REMOTE_ADDR'] || $this->get('authIP') != $_SERVER['REMOTE_ADDR'])
		{
			// Logged out
			return false;
		}
		
		// Has session expired due to inactivity
		if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > ($this->inactivitySessionTimeout * 60 * 60)))
		{
			// Logout
			$this->logout();
			
			// Set Message -> Redirect to invoke login page
			$this->FOGCore->setMessage(_('Session timeout'));//->redirect();
			
			// Logged out
			return false;
		}
		
		// Update last activity
		$_SESSION['LAST_ACTIVITY'] = time();
		
		// Regenerate session ID every 30minutes to aviod session fixation - https://www.owasp.org/index.php/Session_fixation
		if (!isset($_SESSION['CREATED']))
		{
			// Set session time created
			$_SESSION['CREATED'] = time();
		}
		else if (!headers_sent() && time() - $_SESSION['CREATED'] > ($this->regenerateSessionTimeout * 60 * 60))
		{
			// Regenerate session ID
			session_regenerate_id(true);
			
			$_SESSION['CREATED'] = time();
		}
		
		// Logged in
		return true;
	}
	
	public function logout()
	{
		// Destroy session
		@session_destroy();
		@session_unset();
		
		$_SESSION = array();
	}
	
	// Legacy
	const TYPE_ADMIN = 0;
	const TYPE_MOBILE = 1;
}