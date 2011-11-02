<?php

if (IS_INCLUDED !== true) die(_('Unable to load system configuration information.'));

if (isset($_POST['ulang']))
{
	$_SESSION['locale'] = $_POST['ulang'];
	
	putenv('LC_ALL='.$_SESSION['locale']);
	setlocale(LC_ALL, $_SESSION['locale']);
	bindtextdomain('messages', '../../management/languages');
	textdomain('messages');
}

if ($_POST['uname'] != null && $_POST['upass'] != null && $conn != null)
{
	$username = mysql_real_escape_string(trim($_POST['uname']));
	$password = mysql_real_escape_string(trim($_POST['upass']));
	
	$uMan = new UserManager();
	
	if ($uMan->isValidPassword($password, $password) && $uMan->isValidUsername($username))
	{
		 $sql = "select * from users where uName = '$username' and uPass = '" . md5($password) . "'";
		 $res = mysql_query( $sql, $conn ) or die( mysql_error() );
		 if ( mysql_num_rows( $res ) == 1 )
		 {
		 	while( $ar = mysql_fetch_array( $res ) )
			{
				$currentUser = new User($ar['uID'], $ar['uName'], $_SERVER['REMOTE_ADDR'], time(), $ar['uType']);
			}
		 }
		 else
		 {
		 	msgBox(_('Invalid Login.'));
		 }
	}
	else
	{
		msgBox(_('Either the username or password contains invalid characters'));
	}
}