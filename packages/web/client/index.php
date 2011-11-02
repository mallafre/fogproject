<?php
/*
 *   FOG is a computer imaging solution.
 *   Copyright (C) 2007  Chuck Syperski & Jian Zhang
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
 ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<?php require_once( "../commons/config.php" ); ?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<meta http-equiv="x-ua-compatible" content="IE=8">
	<link href="http://fonts.googleapis.com/css?family=Ubuntu" rel="stylesheet" type="text/css">
	<link href="../management/default.css" rel="stylesheet" type="text/css" />
	<link rel="stylesheet" type="text/css" href="../management/css/global.css" />
	<title>
	<?php 
		echo(_("FOG")." ".FOG_VERSION." "._("client applications")); 
	?>
	</title>
</head>
<body>
	<div id="wrapper">
		<!-- Header -->
		<div id="header">
			<div id="logo">
				<h1><a href="#"><img src="../management/images/fog.png" /><sup><?php echo FOG_VERSION ?></sup></a></h1>
				<h2>Open Source Computer Cloning Solution</h2>
			</div>
		</div>
		<!-- Content -->
		<div id="content" class="dashboard">
			<h1><?php print $pageTitle; ?></h1>
			<div id="content-inner">
				<div class="dashbaord">
					<p class="infoTitle">Client Service</p>
					<p class="noSpace">
						<?php echo(_("Download the FOG client service.  This service allows for advanced management of the PC, including hostname changing, etc.")); ?>
						<br /><br />
						<p class="noSpace"><a href="FogService.zip"><?php echo(_("FOG Client Service")); ?></a></p>
					</p>
				</div>
				<div class="dashbaord">
					<p class="infoTitle">FOG Prep</p>
					<p class="noSpace">
						<?php echo(_("Download FOG Prep which must be run on computers running Windows 7 immediately prior to image upload.")); ?>
						<br /><br />
						<p class="noSpace"><a href="FogPrep.zip"><?php echo(_("FOG Prep")); ?></a></p>
					</p>
				</div>	
			</div>
		</div>
	</div>
	<!-- Footer -->
	<div id="footer">FOG: Chuck Syperski & Jian Zhan, FOG WEB UI: Peter Gilchrist</div>
</body>
</html>
