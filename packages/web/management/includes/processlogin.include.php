<?php

if ( IS_INCLUDED !== true ) die( _("Unable to load system configuration information.") );

$userMan = $FOGCore->getClass('UserManager');

if( isset($_POST["ulang"]) && strlen(trim($_POST["ulang"])) > 0 )
{
	$_SESSION['locale'] = $_POST["ulang"];
	
	putenv("LC_ALL=".$_SESSION['locale']);
	setlocale(LC_ALL, $_SESSION['locale']);
	bindtextdomain("messages", "languages");
	textdomain("messages");
}

if ( $userMan != null && isset($_POST["uname"]) && isset($_POST["upass"]) && $conn != null  )
{
	$username = trim($_POST["uname"]);
	$password = trim($_POST["upass"]);
	
	// Hook
	$HookManager->processEvent('Login', array('username' => &$username, 'password' => &$password));
	
	if ( $userMan->isValidPassword($password, $password) && $userMan->isValidUsername( $username ) )
	{
		 $tmpUser = $userMan->attemptLogin($username, $password);
		 if ( $tmpUser != null && $tmpUser->isValid() && $tmpUser->get('type') == User::TYPE_ADMIN)
		 {
			$currentUser = $tmpUser;
			$currentUser->set('authTime', time());
			$currentUser->set('authIP', $_SERVER["REMOTE_ADDR"]);
			
			// Hook
			$HookManager->processEvent('LoginSuccess', array('user' => &$currentUser, 'username' => &$username, 'password' => &$password));
			
			// Set session
			$_SESSION['FOG_USER'] = serialize($currentUser);
			$_SESSION['FOG_USERNAME'] = $currentUser->get('name');
			
			// Check if we were going to a particular page before the login page was presented - if we were, rebuild URL
			$redirect = array_merge($_GET, $_POST); // $_REQUEST contains $_COOKIES which we dont want
			
			unset($redirect['upass'], $redirect['uname'], $redirect['ulang']);
			
			if (in_array($redirect['node'], array('login', 'logout')))
			{
				unset($redirect['node']);
			}
			
			foreach ($redirect AS $key => $value)
			{
				$redirectData[] = $key . '=' . $value;
			}
			
			// Redirect after successful login - this will stop the "resend data" prompt if you refresh after logging in
			$FOGCore->redirect($_SERVER['PHP_SELF'] . ($redirectData ? '?' . implode('&', (array)$redirectData) : ''));
		 }
		 else
		 {
			// Hook
			$HookManager->processEvent('LoginFail', array('username' => &$username, 'password' => &$password));
		 	
		 	// Msssage
		 	$FOGCore->setMessage(_('Invalid Login'));
		 }
	}
	else
	{
		// Msssage
		$FOGCore->setMessage(_('Either the username or password contains invalid characters'));
	}
}