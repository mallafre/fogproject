<?php
/*
 *  FOG - Free, Open-Source Ghost is a computer imaging solution.
 *  Copyright (C) 2007  Chuck Syperski & Jian Zhang
 *
 *   This program is free software: you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation, either version 3 of the License, or
 *   (at your option) any later version.
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

// Require FOG Base - the relative path to config.php changes in AJAX files as these files are included and accessed directly
require_once((defined('BASEPATH') ? BASEPATH . '/commons/config.php' : '../../commons/config.php'));
require_once(BASEPATH . '/commons/init.php');
require_once(BASEPATH . '/commons/init.database.php');

// Allow AJAX check
if (!$_SESSION['AllowAJAXTasks'])
{
	die('FOG Session Invalid');
}

if ( $_SESSION["allow_ajax_kdl"] && $_SESSION["dest-kernel-file"] != null && $_SESSION["tmp-kernel-file"] != null && $_SESSION["dl-kernel-file"] != null )
{
	if ( $_POST["msg"] == "dl" )
	{
		// download kernel from sf
		$blUseProxy = false;
		$proxy = "";
		if ( trim( $FOGCore->getSetting( "FOG_PROXY_IP" ) ) != null )
		{
			$blUseProxy = true;
			$proxy = $FOGCore->getSetting( "FOG_PROXY_IP" ).":".$FOGCore->getSetting( "FOG_PROXY_PORT" );
		}
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_TIMEOUT, '700');
		if ( $blUseProxy )
			curl_setopt($ch, CURLOPT_PROXY, $proxy);
		curl_setopt($ch, CURLOPT_URL, $_SESSION["dl-kernel-file"] );
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		$fp = fopen($_SESSION["tmp-kernel-file"], 'wb');
		
		if ( $fp )
		{
			curl_setopt($ch, CURLOPT_FILE, $fp);
			curl_exec ($ch);
			curl_close ($ch);
			fclose($fp);	
			
			if ( file_exists( $_SESSION["tmp-kernel-file"] ) )
			{
				if (filesize( $_SESSION["tmp-kernel-file"]) > 1048576 )
					echo "##OK##";
				else
					echo "Error: Download failed: filesize = " . filesize( $_SESSION["tmp-kernel-file"]);
			}
			else
				echo "Error: Failed to download kernel!";
		}
		else
			echo "Error: Failed to open temp file.";
	}
	else if ( $_POST["msg"] == "tftp" )
	{
		$ftp = ftp_connect($FOGCore->getSetting( "FOG_TFTP_HOST" ) ); 
		$ftp_loginres = ftp_login($ftp, $FOGCore->getSetting( "FOG_TFTP_FTP_USERNAME" ), $FOGCore->getSetting( "FOG_TFTP_FTP_PASSWORD" )); 			
		if ($ftp && $ftp_loginres ) 
		{				
			$backuppath = $FOGCore->getSetting( "FOG_TFTP_PXE_KERNEL_DIR" ) . "backup/";	
			$warning = "";	
			@ftp_mkdir( $ftp, $backuppath );

					
			$bzImageOrig = $FOGCore->getSetting( "FOG_TFTP_PXE_KERNEL_DIR" ) . $_SESSION["dest-kernel-file"];
			$bzImage = $FOGCore->getSetting( "FOG_TFTP_PXE_KERNEL_DIR" ) . $_SESSION["dest-kernel-file"];
			$backupfile = $backuppath . $_SESSION["dest-kernel-file"] . date("Ymd") . "_" . date("His");
			@ftp_rename( $ftp, $bzImageOrig, $backupfile );
			
			if ( ftp_put( $ftp, $bzImage, $_SESSION["tmp-kernel-file"], FTP_BINARY ) )
			{	
				@unlink($_SESSION["tmp-kernel-file"]);				
				echo "##OK##";
			}
			else
			{
				echo ( _("Error:  Failed to install new kernel!") );
			}
		}
		else
			echo _("Error:  Unable to connect to tftp server.");	
	}
}
else
{
	echo "<b><center>"._("This page can only be viewed via the FOG Management portal")."</center></b>";
}