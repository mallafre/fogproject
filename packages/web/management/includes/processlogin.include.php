<?php

if ( IS_INCLUDED !== true ) die( _("Unable to load system configuration information.") );

$userMan = $core->getUserManager();

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
			$_SESSION['FOG_USER_OBJECT'] = serialize($currentUser);
			$_SESSION['FOG_USER'] = $currentUser->get('name');
			
			// Check if we were going to a particular page before the login page was presented - if we were, rebuild URL
			unset($_POST['upass'], $_POST['uname'], $_POST['ulang']);
			foreach ($_POST AS $key => $value)
			{
				$redirect[] = $key . '=' . $value;
			}
			
			// Redirect after successful login - this will stop the "resend data" prompt if you refresh after logging in
			$FOGCore->redirect($_SERVER['PHP_SELF'] . ($redirect ? '?' . implode('&', $redirect) : ''));
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