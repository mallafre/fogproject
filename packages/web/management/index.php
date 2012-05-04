<?php
/*
 *  FOG is a computer imaging solution.
 *  Copyright (C) 2007  Chuck Syperski & Jian Zhang
 *
 *   This program is free software: you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation, either version 3 of the License, or
 *   any later version.
 *
 *   This program is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 *
 */

// Require FOG Base
require_once('../commons/config.php');
require_once(BASEPATH . '/commons/init.php');
require_once(BASEPATH . '/commons/init.database.php');

// Config load check
if (IS_INCLUDED !== true) die(_('Unable to load system configuration information'));

// User session data
$currentUser = (!empty($_SESSION['FOG_USER']) ? unserialize($_SESSION['FOG_USER']) : null);

// Process Login
require_once('./includes/processlogin.include.php');

// Login form + logout
if ($node == 'logout' || $currentUser == null || !method_exists($currentUser, 'isLoggedIn') || !$currentUser->isLoggedIn())
{
	// Hook
	$HookManager->processEvent('LOGOUT', array('user' => &$currentUser));
	
	// Logout
	if (method_exists($currentUser, 'logout'))
	{
		$currentUser->logout();
	}
	
	// Unset session variables
	unset($currentUser, $_SESSION['FOG_USER'], $_SESSION['FOG_USERNAME'], $_SESSION['AllowAJAXTasks']);
	
	// Show login form
	require_once('./includes/loginform.include.php');
}

// Ping Active
$_SESSION['FOGPingActive'] = ($FOGCore->getSetting('FOG_HOST_LOOKUP') == '1' ? true : false);

// Allow AJAX Tasks
$_SESSION['AllowAJAXTasks'] = true;

// Are we on the Homeapge?
$isHomepage = (!$_REQUEST['node'] || in_array($_REQUEST['node'], array('home', 'dashboard')) ? true : false);

// Render content - must be done before anything is outputted so classes can change HTTP headers
$FOGPageManager = new FOGPageManager();
// Load Page Classes -> Render content based on incoming node variables
$content = $FOGPageManager->render();
// Section title
$sectionTitle = $FOGPageManager->getFOGPageName();
// Page Title - should be set after page has been rendered
$pageTitle = $FOGPageManager->getFOGPageTitle();

if ($FOGCore->isAJAXRequest())
{
	print $content;
	exit;
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="X-UA-Compatible" content="IE=Edge"/>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	
	<title><?php print ($pageTitle ? $pageTitle . ' &gt; ' : '') . $sectionTitle; ?> &gt; FOG &gt; Open Source Computer Cloning Solution</title>
	
	<link rel="stylesheet" type="text/css" href="css/calendar/calendar-win2k-1.css"  />
	<link rel="stylesheet" type="text/css" href="css/jquery.organicTabs.css" />
	<link rel="stylesheet" type="text/css" href="css/fog.css" />
	
	<link rel="shortcut icon" href="../favicon.ico" type="image/x-icon" />
<?php

// Hook
$HookManager->processEvent('CSS');

?>
</head>
<body>

<!-- FOG Message Boxes -->
<div id="loader-wrapper"><div id="loader"><div id="progress"></div></div></div>

<!-- Main -->
<div id="wrapper">
	<!-- Header -->
	<div id="header">
		<div id="logo">
			<h1><a href="<?php print $_SERVER['PHP_SELF']; ?>"><img src="images/fog-logo.png" title="Home" /><sup><?php echo FOG_VERSION; ?></sup></a></h1>
			<h2>Open Source Computer Cloning Solution</h2>
		</div>
		<div id="menu">
			<?php
			require_once('./includes/mainmenu.include.php');
			?>
		</div>
	</div>
	<!-- Content -->
	<div id="content"<?php print ($isHomepage ? ' class="dashboard"' : ''); ?>>
		<h1><?php print $sectionTitle; ?></h1>
		<div id="content-inner">
			<?php
			
			if ($FOGPageManager->isFOGPageTitleEnabled())
			{
				printf('<h2>%s</h2>', $FOGPageManager->getFOGPageTitle());
			}
			print $content;

			?>
		</div>
	</div>
<?php
if (!$isHomepage) {
?>
	<!-- Menu -->
	<div id="sidebar">
		<?php
		require_once('./includes/submenu.include.php');
		?>
	</div>
<?php
}
?>
</div>

<!-- Footer: Be nice, give us some credit -->
<div id="footer">FOG Project: Chuck Syperski, Jian Zhang &amp; Peter Gilchrist</div>

<?php

// Session Messages
$FOGCore->getMessages();

?>
<div class="fog-variable" id="FOGPingActive"><?php print ($FOGCore->getSetting('FOG_HOST_LOOKUP') == '1' ? '1' : '0'); ?></div>

<!-- JavaScript -->
<script type="text/javascript" src="js/jquery.js"></script>
<script type="text/javascript" src="js/calendar/jquery.dynDateTime.js"></script>
<script type="text/javascript" src="js/calendar/calendar-en.js"></script>
<script type="text/javascript" src="js/jquery.tipsy.js"></script>
<script type="text/javascript" src="js/jquery.progressbar.js"></script>
<script type="text/javascript" src="js/jquery.tmpl.js"></script>
<script type="text/javascript" src="js/jquery.organicTabs.js"></script>
<script type="text/javascript" src="js/jquery.placeholder.js"></script>
<script type="text/javascript" src="js/jquery.disableSelection.js"></script>
<script type="text/javascript" src="js/fog.js"></script>
<script type="text/javascript" src="js/fog.main.js"></script>
<?php

// Auto find javascript based on $node and/or $sub
foreach (array("js/fog.{$node}.js", "js/fog.{$node}.{$sub}.js") AS $jsFilepath)
{
	if (file_exists($jsFilepath))
	{
		printf('<script type="text/javascript" src="%s"></script>%s', $jsFilepath, "\n");
	}
}


// TODO: Move to array
if ($isHomepage)
{
	?>
	<script type="text/javascript" src="js/jquery.flot.js"></script>
	<script type="text/javascript" src="js/jquery.flot.pie.js"></script>
	<script type="text/javascript" src="js/fog.dashboard.js"></script>
	<?php
	
	// Include 'excanvas' for HTML5 <canvas> support in IE 6/7/8/9...
	// I hate IE soooo much, only Microsoft wouldnt fix their own broken software
	if (preg_match('#MSIE [6|7|8|9]#', $_SERVER['HTTP_USER_AGENT']))
	{
		?>
		<script type="text/javascript" src="js/excanvas.js"></script>
		<?php
	}
}

// Hook
$HookManager->processEvent('JAVASCRIPT');

?>

</body>
</html>