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

define( "WIPE_FAST", 1 );
define( "WIPE_NORMAL", 2 );
define( "WIPE_FULL", 3 );

define( "FOG_AV_SCANONLY", 1 );
define( "FOG_AV_SCANQUARANTINE", 2 );

function getFirstGroupNameByHostID( $conn, $hostid )
/*
 * getGroupNameByHostname returns the group name
 * written to be able to add group data to a host's quick info
 *
 * Written by Sam Wilson 09/07/09
 * Modified by csyperski to use host id instead of name
 */
{
	if( $conn != null && is_numeric( $hostid ) )
	{

	 	$sql = "select gmGroupID from groupMembers where gmHostID = '". $hostid ."' order by gmID limit 1";
	 	
	 	$res = mysql_query($sql, $conn) or criticalError( mysql_error(), _("FOG:: Database Error!"));
	 	if ( $ar = mysql_fetch_array( $res ) )
	 	{
	 		$groupId =  $ar["gmGroupID"];
	 		$sql = "select groupName from groups where groupID = '". mysql_real_escape_string($groupId) ."'";

		 	$res1 = mysql_query($sql, $conn) or criticalError( mysql_error(), _("FOG:: Database Error!"));
		 	
		 	if ( $ar1 = mysql_fetch_array( $res1 ) )
		 	{
		 		return $ar1["groupName"];
		 	}
	 	}
   	}
   	return null;
}

function createCronScheduledPackage( $conn, $blGroup, $groupHostID, $taskType, $m, $h, $dom, $mon, $dow, $blShutdown, $blPushSnapins, &$reason, $arg2=null  )
{
	if( $conn != null && $groupHostID != null && is_numeric( $groupHostID ) && $taskType != null && $m != null && $h != null && $dom != null && $mon != null && $dow != null )
	{
		// check is task already exists
		$sql = "SELECT 
				COUNT(*) as cnt
			FROM 
				scheduledTasks 
			WHERE 
				stActive = '1' and 
				stIsGroup = '" . ($blGroup ? "1" : "0") . "' and 
				stTaskTypeID = '" . mysql_real_escape_string( $taskTypeID ) . "' and 
				stType = 'C' and 
				stGroupHostID = '$groupHostID' and 
				stMinute = '$m' and 
				stHour = '$h' and 
				stDOM = '$dom' and 
				stMonth = '$mon' and 
				stDOW = '$dow'";
		$res = mysql_query( $sql, $conn ) or die( mysql_error( $conn ) );
		$cnt = 0;
		while( $ar = mysql_fetch_array( $res ) )
		{
			$cnt = $ar["cnt"];
		}	
				
		if ( $cnt == 0 )
		{	
			$sql = "INSERT INTO
					scheduledTasks 
						(stName, stDesc, stType, stTaskTypeID, stMinute, stHour, stDOM, stMonth, stDOW, stIsGroup, stGroupHostID, stShutDown, stOther1, stOther2, stActive)
					VALUES
						( 'Scheduled Task', '', 'C', '" . mysql_real_escape_string( $taskTypeID ) . "', '" . mysql_real_escape_string( $m ) . "', '" . mysql_real_escape_string( $h ) . "', '" . mysql_real_escape_string( $dom ) . "', '" . mysql_real_escape_string( $mon ) . "', '" . mysql_real_escape_string( $dow ) . "', '" . ($blGroup ? "1" : "0") . "', '$groupHostID', '" . ($blShutdown ? "1" : "0" ) . "', '" . ( $blPushSnapins ? "1" : "0" ) . "', '" . mysql_real_escape_string( $arg2 ) . "','1' ) ";
			if ( mysql_query( $sql, $conn ) )
				return true;
			else
				$reason = mysql_error( $conn );		
		}
		else
		{
			$reason = _("Task already exists.");
		}		
	}
	else
		$reason = ("General Error");
	return false;
}


function createSingleRunScheduledPackage( $conn, $blGroup, $groupHostID, $taskType, $lngTime, $blShutdown, $blPushSnapins, &$reason, $arg2=null )
{
	if( $conn != null && $groupHostID != null && is_numeric( $groupHostID ) && $taskType != null && $lngTime != null && is_numeric( $lngTime ) )
	{
		// check is task already exists
		$sql = "SELECT 
				COUNT(*) as cnt
			FROM 
				scheduledTasks 
			WHERE 
				stActive = '1' and 
				stIsGroup = '" . ($blGroup ? "1" : "0") . "' and 
				stTaskTypeID = '" . mysql_real_escape_string( $taskTypeID ) . "' and 
				stType = 'S' and 
				stGroupHostID = '$groupHostID' and 
				stDateTime = '$lngTime'";
		$res = mysql_query( $sql, $conn ) or die( mysql_error( $conn ) );
		$cnt = 0;
		while( $ar = mysql_fetch_array( $res ) )
		{
			$cnt = $ar["cnt"];
		}	
				
		if ( $cnt == 0 )
		{
			$sql = "INSERT INTO
					scheduledTasks 
						(stName, stDesc, stType, stTaskTypeID, stIsGroup, stGroupHostID, stDateTime, stShutDown, stOther1, stOther2, stActive)
					VALUES
						( 'Scheduled Task', '', 'S', '" . mysql_real_escape_string( $taskTypeID ) . "', '" . ($blGroup ? "1" : "0") . "', '$groupHostID', '$lngTime', '" . ($blShutdown ? "1" : "0" ) . "', '" . ( $blPushSnapins ? "1" : "0" ) . "', '" . mysql_real_escape_string( $arg2 ) . "','1' ) ";
			if ( mysql_query( $sql, $conn ) )
				return true;
			else
				$reason = mysql_error( $conn );
		}
		else
		{
			$reason = _("Task already exists.");
		}
	}
	return false;
}

function sysLinuxEncrypt( $conn, $string )
{
	if ( $conn != null && $string !== null )
	{
		$dir = $GLOBALS['FOGCore']->getSetting( "FOG_UTIL_DIR" );
		if ( $dir != null )
		{
			$bin = $dir . "/md5pass";
			$output = null;
			$intRet = null;
			exec( $bin . " " . $string, $output, $intRet );
			if ( $intRet == "0" )
			{
				return $output[0];
			}
		}
	}
	return null;
}

function generatePXEMenu( $conn, $type, $masterpw, $memtestpw, $reginputpw, $regpw, $quickpw, $sysinfo, $debugpw, $timeout, $blHide, $adv, &$reason )
{
	global $currentUser;
	if ( $conn != null  )
	{
		$masterpw = trim( $masterpw );

		if ( ! is_numeric($timeout) || $timeout <= 0 )
		{
			$reason = _("Invalid Timeout Value.");
			return false;
		}

		if (  $type == "1" || $type == "2"  )
		{
			$strMenu = "";
			if ( $type == "1" )
			{
				if ( $masterpw != null ) 
				{	
					$masterEnc = sysLinuxEncrypt( $conn, $masterpw );	
					if ( $masterEnc != null )
					{	
						$encMemTest = "";
						$encRegInput = "";
						$encReg = "";
						$encDebug = "";
						$encQuick = "";
						$encSysinfo = "";
					
						if ( $memtestpw != null )
						{
							$encMemTest = "MENU PASSWD " . sysLinuxEncrypt( $conn, $memtestpw );
						}
						
						if ( $reginputpw != null )
						{
							$encRegInput = "MENU PASSWD " . sysLinuxEncrypt( $conn, $reginputpw );
						}			
						
						if ( $regpw != null )
						{
							$encReg = "MENU PASSWD " . sysLinuxEncrypt( $conn, $regpw );
						}									

						if ( $debugpw != null )
						{
							$encDebug = "MENU PASSWD " . sysLinuxEncrypt( $conn, $debugpw );
						}	
						
						if ( $quickpw != null )
						{
							$encQuick = "MENU PASSWD " . sysLinuxEncrypt( $conn, $quickpw );
						}
						
						if ( $sysinfo != null )
							$encSysinfo = "MENU PASSWD " . sysLinuxEncrypt( $conn, $sysinfo );
					
						$strMenu = "DEFAULT vesamenu.c32
MENU TITLE "._("FOG Computer Cloning Solution")."
MENU BACKGROUND fog/bg.png
MENU MASTER PASSWD " . $masterEnc . "
" . (( $blHide ) ? "MENU HIDDEN" : "") . "
" . (( $blHide ) ? "MENU AUTOBOOT " : "") . "

menu color title	1;36;44    #ffffffff #00000000 std

LABEL fog.local
	localboot 0
	MENU DEFAULT
	MENU LABEL "._("Boot from hard disk")."
	TEXT HELP
	"._("Boot from the local hard drive.  
	If you are unsure, select this option.")."
	ENDTEXT

LABEL fog.memtest
	$encMemTest
	kernel " . $GLOBALS['FOGCore']->getSetting( "FOG_MEMTEST_KERNEL" ) . "
	MENU LABEL "._("Run Memtest86+")."
	TEXT HELP
	"._("Run Memtest86+ on the client computer.")."
	ENDTEXT

LABEL fog.reg
	$encRegInput
	kernel " . $GLOBALS['FOGCore']->getSetting( "FOG_TFTP_PXE_KERNEL" ) . "
	append initrd=" . $GLOBALS['FOGCore']->getSetting( "FOG_PXE_BOOT_IMAGE" ) . " root=/dev/ram0 rw ramdisk_size=" . $GLOBALS['FOGCore']->getSetting( "FOG_KERNEL_RAMDISK_SIZE" ) . " ip=dhcp dns=" . $GLOBALS['FOGCore']->getSetting( "FOG_PXE_IMAGE_DNSADDRESS" ) . " mode=autoreg keymap=" . $GLOBALS['FOGCore']->getSetting( "FOG_KEYMAP" ) . " web=" . $GLOBALS['FOGCore']->getSetting( "FOG_WEB_HOST" ) . $GLOBALS['FOGCore']->getSetting( "FOG_WEB_ROOT" ) . " loglevel=4 consoleblank=0
	MENU LABEL "._("Quick Host Registration and Inventory")."
	TEXT HELP
	"._("Automatically register the client computer,
	and perform a hardware inventory.")."
	ENDTEXT

LABEL fog.reginput
	$encReg
	kernel " . $GLOBALS['FOGCore']->getSetting( "FOG_TFTP_PXE_KERNEL" ) . "
	append initrd=" . $GLOBALS['FOGCore']->getSetting( "FOG_PXE_BOOT_IMAGE" ) . " root=/dev/ram0 rw ramdisk_size=" . $GLOBALS['FOGCore']->getSetting( "FOG_KERNEL_RAMDISK_SIZE" ) . " ip=dhcp dns=" . $GLOBALS['FOGCore']->getSetting( "FOG_PXE_IMAGE_DNSADDRESS" ) . " mode=manreg keymap=" . $GLOBALS['FOGCore']->getSetting( "FOG_KEYMAP" ) . " web=" . $GLOBALS['FOGCore']->getSetting( "FOG_WEB_HOST" ) . $GLOBALS['FOGCore']->getSetting( "FOG_WEB_ROOT" ) . " loglevel=4 consoleblank=0
	MENU LABEL "._("Perform Full Host Registration and Inventory")."
	TEXT HELP
	"._("Perform a full host registration on the client
	computer, perform a hardware inventory, and 
	optionally image the host.")."
	ENDTEXT

LABEL fog.quickimage
	$encQuick
	kernel " . $GLOBALS['FOGCore']->getSetting( "FOG_TFTP_PXE_KERNEL" ) . "
	append initrd=" . $GLOBALS['FOGCore']->getSetting( "FOG_PXE_BOOT_IMAGE" ) . "  root=/dev/ram0 rw ramdisk_size=" . $GLOBALS['FOGCore']->getSetting( "FOG_KERNEL_RAMDISK_SIZE" ) . " ip=dhcp dns=" . $GLOBALS['FOGCore']->getSetting( "FOG_PXE_IMAGE_DNSADDRESS" ) . " mode=quickimage keymap=" . $GLOBALS['FOGCore']->getSetting( "FOG_KEYMAP" ) . " web=" . $GLOBALS['FOGCore']->getSetting( "FOG_WEB_HOST" ) . $GLOBALS['FOGCore']->getSetting( "FOG_WEB_ROOT" ) . " loglevel=4 consoleblank=0
	MENU LABEL "._("Quick Image")."
	TEXT HELP
	"._("This mode will allow you to image this host quickly with
	it's default assigned image.")."
	ENDTEXT

LABEL fog.sysinfo
	$encSysinfo
	kernel " . $GLOBALS['FOGCore']->getSetting( "FOG_TFTP_PXE_KERNEL" ) . "
	append initrd=" . $GLOBALS['FOGCore']->getSetting( "FOG_PXE_BOOT_IMAGE" ) . "  root=/dev/ram0 rw ramdisk_size=" . $GLOBALS['FOGCore']->getSetting( "FOG_KERNEL_RAMDISK_SIZE" ) . " ip=dhcp dns=" . $GLOBALS['FOGCore']->getSetting( "FOG_PXE_IMAGE_DNSADDRESS" ) . " mode=sysinfo keymap=" . $GLOBALS['FOGCore']->getSetting( "FOG_KEYMAP" ) . " loglevel=4 consoleblank=0
	MENU LABEL "._("Client System Information")."
	TEXT HELP
	"._("View basic client information such as MAC address 
	and FOG compatibility.")."
	ENDTEXT

LABEL fog.debug
	$encDebug
	kernel " . $GLOBALS['FOGCore']->getSetting( "FOG_TFTP_PXE_KERNEL" ) . "
	append initrd=" . $GLOBALS['FOGCore']->getSetting( "FOG_PXE_BOOT_IMAGE" ) . "  root=/dev/ram0 rw ramdisk_size=" . $GLOBALS['FOGCore']->getSetting( "FOG_KERNEL_RAMDISK_SIZE" ) . " ip=dhcp dns=" . $GLOBALS['FOGCore']->getSetting( "FOG_PXE_IMAGE_DNSADDRESS" ) . " mode=onlydebug keymap=" . $GLOBALS['FOGCore']->getSetting( "FOG_KEYMAP" ) . " consoleblank=0
	MENU LABEL "._("Debug Mode")."
	TEXT HELP
	"._("Debug mode will load the boot image and load a prompt so
	you can run any commands you wish.")."
	ENDTEXT

PROMPT 0
TIMEOUT " . $timeout . "0
$adv";

						
						
					}
					else
					{
						$reason = _("Encrypted Master Password is null.");
						return false;
					}
				}
				else
				{
					$reason = _("Master Password is null.");
					return false;
				}
			}
			else
			{
				$strMenu = "DISPLAY boot.txt
DEFAULT fog.local

LABEL fog.local
	localboot 0

LABEL fog.memtest
	kernel " . $GLOBALS['FOGCore']->getSetting( "FOG_MEMTEST_KERNEL" ) . "

LABEL fog.reg
	kernel  " . $GLOBALS['FOGCore']->getSetting( "FOG_TFTP_PXE_KERNEL" ) . "
	append initrd=" . $GLOBALS['FOGCore']->getSetting( "FOG_PXE_BOOT_IMAGE" ) . "  root=/dev/ram0 rw ramdisk_size=" . $GLOBALS['FOGCore']->getSetting( "FOG_KERNEL_RAMDISK_SIZE" ) . " ip=dhcp dns=" . $GLOBALS['FOGCore']->getSetting( "FOG_PXE_IMAGE_DNSADDRESS" ) . " mode=autoreg keymap=" . $GLOBALS['FOGCore']->getSetting( "FOG_KEYMAP" ) . " web=" . $GLOBALS['FOGCore']->getSetting( "FOG_WEB_HOST" ) . $GLOBALS['FOGCore']->getSetting( "FOG_WEB_ROOT" ) . " loglevel=4 consoleblank=0

LABEL fog.reginput
	kernel  " . $GLOBALS['FOGCore']->getSetting( "FOG_TFTP_PXE_KERNEL" ) . "
	append initrd=" . $GLOBALS['FOGCore']->getSetting( "FOG_PXE_BOOT_IMAGE" ) . "  root=/dev/ram0 rw ramdisk_size=" . $GLOBALS['FOGCore']->getSetting( "FOG_KERNEL_RAMDISK_SIZE" ) . " ip=dhcp dns=" . $GLOBALS['FOGCore']->getSetting( "FOG_PXE_IMAGE_DNSADDRESS" ) . " mode=manreg keymap=" . $GLOBALS['FOGCore']->getSetting( "FOG_KEYMAP" ) . " web=" . $GLOBALS['FOGCore']->getSetting( "FOG_WEB_HOST" ) . $GLOBALS['FOGCore']->getSetting( "FOG_WEB_ROOT" ) . " loglevel=4 consoleblank=0

PROMPT 1
TIMEOUT " . $timeout . "0
$adv";
			}

			$tmp = createPXEFile( $strMenu );
			if( $tmp !== null )
			{

				
				$ftp = ftp_connect($GLOBALS['FOGCore']->getSetting( "FOG_TFTP_HOST" )); 
				$ftp_loginres = ftp_login($ftp, $GLOBALS['FOGCore']->getSetting( "FOG_TFTP_FTP_USERNAME" ), $GLOBALS['FOGCore']->getSetting( "FOG_TFTP_FTP_PASSWORD" )); 			
				if ($ftp && $ftp_loginres ) 
				{
					if ( ftp_put( $ftp, $GLOBALS['FOGCore']->getSetting( "FOG_TFTP_PXE_CONFIG_DIR" ) . "default", $tmp, FTP_ASCII ) )
					{		
						@ftp_close($ftp); 					
						@unlink( $tmp );
						
						$GLOBALS['FOGCore']->setSetting( "FOG_PXE_MENU_TIMEOUT", $timeout );
						$GLOBALS['FOGCore']->setSetting( "FOG_PXE_ADVANCED", $adv );
						if ( $type == "1" )
							$GLOBALS['FOGCore']->setSetting( "FOG_PXE_MENU_HIDDEN", ($blHide) ? "1" : "0" );
						
						return true;			
					}  
					else
					{
						$reason = _("Unable to upload file."); 											
					}
				}	
				else
				{
					$reason = _("Unable to connect to tftp server."); 				
				}
				@ftp_close($ftp); 					
				@unlink( $tmp );		

			}
			else
				$reason = _("Failed to open tmp file.");
	
		} 
		else
			$reason = _("Invalid PXE Menu Type.");
	}
	else
	{
		$reason = _("Database connection was null");
	}
	return false;	
}

function getImageName( $conn, $hostID )
{
	if ( $conn != null && is_numeric( $hostID ) )
	{
		$sql = "SELECT 
				imageName
			FROM 
				( SELECT * FROM hosts WHERE hostID = '$hostID') hosts
				INNER JOIN images on ( hosts.hostImage = images.imageID )";
		$res = mysql_query( $sql, $conn ) or die( mysql_error() );
		while( $ar = mysql_fetch_array( $res ) )
		{
			return $ar["imageName"];
		}
	}
	return null;
}

// Log imaging start and end times
// type is either "s" for start or "e" for end
function logImageTask( $conn, $type, $hostid, $imageName=null)
{
	if ( $conn != null && is_numeric($hostid) )
	{
		if ( $type == "s" )
		{
			$sql = "INSERT INTO imagingLog(ilHostID, ilStartTime, ilImageName) values('$hostid', NOW(), '" . mysql_real_escape_string( $imageName ) . "')";
			mysql_query( $sql, $conn );
		}
		else if ( $type == "e" )
		{
			$sql = "SELECT MAX(ilID) as ilID FROM imagingLog WHERE ilHostID = '$hostid'";
			$res = mysql_query( $sql, $conn );
			
			if ( $ar = mysql_fetch_array( $res ) )
			{
				$sql = "UPDATE imagingLog set ilFinishTime = NOW() where ilID = '" . $ar["ilID"] . "'";
				mysql_query( $sql, $conn );
			}
		}
	}
}

function setHostModuleStatus( $conn, $state, $hostid, $moduleid )
{
	$state = mysql_real_escape_string( $state );
	$hostid = mysql_real_escape_string( $hostid );
	$moduleid = mysql_real_escape_string( $moduleid );

	if ( $conn != null )
	{
		if ( is_numeric( $state ) && ( $state == "1" || $state == "0" ) )
		{
			if ( is_numeric( $hostid ) )
			{
				$sql = "SELECT 
						count(*) as cnt 
					FROM 
						moduleStatusByHost 
					WHERE 
						msHostID = '$hostid' and 
						msModuleID = '$moduleid'";
						
				$res = mysql_query( $sql, $conn ) or criticalError( mysql_error(), _("FOG :: Database error!") );
				$blExists = false;
				if ( $ar = mysql_fetch_array( $res ) )
				{
					if ( $ar["cnt"] > 0 )
						$blExists = true;
				}
				
				$sql = "";
				if ( $blExists )
				{
					$sql = "UPDATE moduleStatusByHost set msState = '$state' WHERE msHostID = '$hostid' and msModuleID = '$moduleid'";
				}
				else
				{
					$sql = "INSERT INTO moduleStatusByHost(msHostID, msModuleID, msState) values('$hostid', '$moduleid', '$state')";
				}
				
				if ( ! mysql_query( $sql, $conn ) )
					criticalError( mysql_error(), _("FOG :: Database error!") );
				else
					return true;
												
			}
		}
	}
	return false;
}

function userCleanupUserExists( $conn, $name, $id=-1 )
{
	if ( $conn != null && $name != null )
	{
		$sql = "select count(*) as cnt from userCleanup where ucName = '" . $name . "' and ucID <> $id";
		$res = mysql_query( $sql, $conn ) or criticalError( mysql_error(), _("FOG :: Database error!") );
		if ( $ar = mysql_fetch_array( $res ) )
		{
			if ( $ar["cnt"] == 0 )
				return false;
		}	
	}
}

function dircleanDirExists( $conn, $name, $id=-1 )
{
	if ( $conn != null && $name != null )
	{
		$sql = "select count(*) as cnt from dirCleaner where dcPath = '" . $name . "' and dcID <> $id";
		$res = mysql_query( $sql, $conn ) or criticalError( mysql_error(), _("FOG :: Database error!") );
		if ( $ar = mysql_fetch_array( $res ) )
		{
			if ( $ar["cnt"] == 0 )
				return false;
		}	
	}
}

function getSettingCats( $conn )
{
	$arCats = array();
	if ( $conn != null )
	{
		$sql = "SELECT settingCategory FROM globalSettings GROUP BY settingCategory ORDER BY settingCategory";
		$res = mysql_query( $sql, $conn ) or criticalError( mysql_error(), _("FOG :: Database error!") );
		while( $ar = mysql_fetch_array( $res ) )
		{
			$arCats[] = $ar["settingCategory"];
		}
	}
	return $arCats;
}



// returns the # of tasks killed
function removeAllTasksForHostMac( $conn, $mac )
{
	if ( $conn != null )
	{
		$macColon = str_replace ( "-", ":", strtolower($mac) );
		
		$sql = "SELECT 
				hostID
			FROM
				hosts
			WHERE
				hostMAC = '$macColon'";
				
		$res = mysql_query( $sql, $conn );
	
		while( $ar = mysql_fetch_array( $res ) )
		{
			return removeAllTasksForHostID( $conn, $ar["hostID"] );
		}
		return $num;
	}
	return 0;
}

function removeAllTasksForHostID( $conn, $id )
{
	if ( $conn != null && is_numeric( $id ) )
	{
		$sql = "SELECT 
				taskID, hostMAC
			FROM
				tasks
				inner join (SELECT * FROM hosts where hostID = '$id' ) hosts on (hosts.hostID = tasks.taskHostID)";
		
		$res = mysql_query( $sql, $conn );
		if ( $res )
		{
			$num = 0;
			while( $ar = mysql_fetch_array( $res ) )
			{
				$macDash = str_replace ( ":", "-", strtolower($ar["hostMAC"]) );
		
				@ftpDelete( $GLOBALS['FOGCore']->getSetting( "FOG_TFTP_PXE_CONFIG_DIR" ) . "01-" . $macDash );			
			
				$sql = "delete from tasks where taskID = '" . mysql_real_escape_string( $ar["taskID"] ) . "' limit 1";
				if ( mysql_query( $sql, $conn ) )
				{
					$num++;
				}
			}
			return $num;
		}
	}
	return 0;
}

function userTrackerActionToString( $code )
{
	switch( $code )
	{
		case "0":
			return _("Logout");
			break;
		case "1":
			return _("Login");
			break;
		case "99":
			return _("Service Start");
			break;
		default:
			return "N/A";
			break;
	}
}

function criticalError( $description, $title="FOG :: Critical Error!")
{
	echo "<div class=\"errorBox\">";
		echo "<h2>";
			echo $title;
		echo "</h2>";
		echo "<b>"._("Description").":</b> " . $description;
	echo "</div>";
	exit;
}

function isSafeHostName( $hostname )
{

	return (preg_match( "#^[0-9a-zA-Z_\-]*$#", $hostname ) && strlen($hostname) > 0 && strlen( $hostname ) <= 15);
} 
 
function isValidIPAddress( $ip )
{
	$ar = explode( ".", $ip );
	
	if (count($ar) != 4 ) return false;
	
	for($i=0;$i<count($ar);$i++)
	{
		if ( $ar[$i] === null || ! is_numeric( $ar[$i] ) || $ar[$i] < 0 || $ar[$i] > 255 ) return false;
	}
	return true;
}
 
function isValidMACAddress( $mac )
{
	return preg_match( "#^([0-9a-fA-F][0-9a-fA-F]:){5}([0-9a-fA-F][0-9a-fA-F])$#", $mac );
}
 
function doAllMembersHaveSameImage( $members )
{
	if ( $members !== null )
	{
		$firstImageDef = null;
		for( $i = 0; $i < count( $members ); $i++ )
		{
			$currentImageDef = $members[$i]->getImageID();
			if( $i == 0 )
				$firstImageDef = $currentImageDef;
				
			if ( $currentImageDef === null || $currentImageDef != $firstImageDef || $currentImageDef < 0 || ! is_numeric( $currentImageDef ) )
				return false;
		}	
		
		if ( $firstImageDef !== null ) return true;
	}
	return false;
}

function addPrinter( $conn, $hostId, $printerId )
{
	if ( $conn != null )
	{
		$host = mysql_real_escape_string( $hostId );
		$printer = mysql_real_escape_string( $printerId );	
		
		$sql = "select count(*) as cnt from printerAssoc where paPrinterID = '$printer' and paHostID = '$host'";
		$res = mysql_query( $sql, $conn ) or criticalError( mysql_error(), _("FOG :: Database error!") );
		if ( $ar = mysql_fetch_array( $res ) )
		{
			if ( $ar["cnt"] == 0 )
			{
				$sql = "select count(*) as cnt from printerAssoc where paHostID = '$host' and paIsDefault = '1'";
				$res = mysql_query( $sql, $conn ) or criticalError( mysql_error(), _("FOG :: Database error!") );
				if ( $ar = mysql_fetch_array( $res ) )
				{
					$default = "0";
					if ( $ar["cnt"] == 0 )
					{
						$default = "1";
					}
					$sql = "INSERT INTO 
							printerAssoc( paHostID, paPrinterID, paIsDefault )
							values( '$host', '$printer', '$default' )";

					if ( mysql_query( $sql, $conn ) )
					{
						return true;
					}
				}
			}

		}			
	}
	return false;	
}

function deletePrinter( $conn, $printerAssocId )
{
	if ( $conn != null )
	{
		$printer = mysql_real_escape_string( $printerAssocId );
		$sql = "delete from printerAssoc where paID = '$printer'";	
		if ( mysql_query( $sql, $conn ) )
			return true;
	}
	return false;
}

function deletePrinterByHost( $conn, $printerId, $hostId )
{
	if ( $conn != null )
	{
		$printer = mysql_real_escape_string( $printerId );
		$host = mysql_real_escape_string( $hostId );
		$sql = "delete from printerAssoc where paPrinterID = '$printer' and paHostID = '$host'";	
		if ( mysql_query( $sql, $conn ) )
			return true;
	}
	return false;
}

function setDefaultPrinter( $conn, $printerAssocId )
{
	if ( $conn != null )
	{
		$printer = mysql_real_escape_string( $printerAssocId );	
		$sql = "select paHostID from printerAssoc where paID = '$printer'";
		$res = mysql_query( $sql, $conn ) or criticalError( mysql_error(), _("FOG :: Database error!") );;
		$hostid = null;
		while( $ar = mysql_fetch_array( $res ) )
		{
			$hostid = $ar["paHostID"];
			if ( $hostid !== null && is_numeric($hostid) )
			{
				$sql = "update printerAssoc set paIsDefault = '0' where paHostID = '$hostid'";
				if ( mysql_query( $sql, $conn ) )
				{
					$sql = "update printerAssoc set paIsDefault = '1' where paID = '$printer'";
					return mysql_query( $sql, $conn );
				}
			}
		}
	}
	return false;
}

function isHostAssociatedWithSnapin( $conn, $hostid, $snapinid )
{
	if ( $conn != null && $hostid !== null && $snapinid !== null && is_numeric( $hostid ) && is_numeric( $snapinid ) )
	{
		$hostid = mysql_real_escape_string( $hostid );
		$snapinid = mysql_real_escape_string( $snapinid );
		$sql = "select count(*) as cnt from snapinAssoc where saHostID = '" . $hostid . "' and saSnapinID = '" . $snapinid . "'"; 
		$res = mysql_query($sql, $conn) or criticalError( mysql_error(), _("FOG :: Database error!") );
		while( $ar = mysql_fetch_array( $res ) )
		{
			return ($ar["cnt"] > 0);
		
		}
		return false;
	}
	return true;
}

function addSnapinToHost( $conn, $hostid, $snapinid, &$reason )
{
	if ( $conn != null && $hostid !== null && $snapinid !== null && is_numeric( $hostid ) && is_numeric( $snapinid ) )
	{
		if ( ! isHostAssociatedWithSnapin( $conn, $hostid, $snapinid ) )
		{
			$hostid = mysql_real_escape_string( $hostid );
			$snapinid = mysql_real_escape_string( $snapinid );		
			$sql = "insert into snapinAssoc(saHostID, saSnapinID) values('$hostid','$snapinid')";
			if( mysql_query( $sql, $conn ) )
			{
				$reason = _("Snapin added to host.");
				return true;
			}
			else
				$reason = _("Database error").": " . mysql_error() ;
		}
		else
			$reason = _("Snapin is already linked with this host.");
	}
	else
		$reason = _("Either the database connection, snapid ID, or host ID was null.");
		
	return false;
}

function deleteSnapinFromHost( $conn, $hostid, $snapinid, &$reason )
{
	if ( $conn != null && $hostid !== null && $snapinid !== null && is_numeric( $hostid ) && is_numeric( $snapinid ) )
	{
		if ( isHostAssociatedWithSnapin( $conn, $hostid, $snapinid ) )
		{
			$hostid = mysql_real_escape_string( $hostid );
			$snapinid = mysql_real_escape_string( $snapinid );		
			$sql = "delete from snapinAssoc where saHostID = '$hostid' and saSnapinID = '$snapinid'";
			if( mysql_query( $sql, $conn ) )
			{
				$reason = _("Snapin removed from host.");
				return true;
			}
			else
				$reason = _("Database error").": " . mysql_error() ;		
		}
		else
			$reason = _("Snapin is not linked with this host.");
	}
	else
		$reason = _("Either the database connection, snapid ID, or host ID was null.");
	
	return false;
}

function cancelSnapinsForHost( $conn, $hostid, $snapID = -1 )
{
	if ( $conn != null && $hostid !== null && is_numeric( $hostid )  )
	{
		$hostid = mysql_real_escape_string( $hostid );
		$snapID = mysql_real_escape_string( $snapID );
		$where = "";
		if ( $snapID != -1 )
		{
			$where = " and stSnapinID = '$snapID' ";
		}
		
		$sql = "SELECT 
				stID 
			FROM 
				snapinTasks
				inner join snapinJobs on ( snapinTasks.stJobID = snapinJobs.sjID )
			WHERE
				sjHostID = '$hostid' and
				stState in ( '0', '1' )
				$where";
				
		$res = mysql_query( $sql, $conn ) or criticalError( mysql_error(), _("FOG :: Database error!") );
		while( $ar = mysql_fetch_array( $res ) )
		{
			$sql = "update snapinTasks set stState = '-1' where stID = '" . $ar["stID"] . "'";
			if (!mysql_query( $sql, $conn ) )
				die( mysql_error() );
		}
		return true;
	}
	return false;
}

function deploySnapinsForHost( $conn, $hostid, $snapID = -1 )
{
	if ( $conn != null && $hostid !== null && is_numeric( $hostid )  )
	{
		$hostid = mysql_real_escape_string( $hostid );
		$snap = mysql_real_escape_string( $snapID );
		$where = "";
		if ( $snapID != -1 )
		{
			$where = " and sID = '$snap' ";
		}
		$sql = "SELECT 
				count(*) as cnt 
			FROM 
				snapinAssoc 
				inner join snapins on ( snapinAssoc.saSnapinID = snapins.sID )
			WHERE
				snapinAssoc.saHostID = '$hostid' 
				$where";
		
		$res = mysql_query( $sql, $conn ) or criticalError( mysql_error(), _("FOG :: Database error!") );
		if ( $ar = mysql_fetch_array($res) )
		{
			if($ar["cnt"] > 0 )
			{
				// create job record
				// todo: make transactional 
				$sql = "insert into snapinJobs(sjHostID, sjCreateTime) values( '$hostid', NOW())";
				if ( mysql_query( $sql, $conn ) )
				{
					$insertedID = mysql_insert_id( $conn );
					if ( $insertedID !== false )
					{
						// create job items
						$suc = 0;
						$sql = "SELECT 
								sID 
							FROM 
								snapinAssoc 
								inner join snapins on ( snapinAssoc.saSnapinID = snapins.sID )
							WHERE
								snapinAssoc.saHostID = '$hostid'
							ORDER BY
								snapins.sName";	

						$resS = mysql_query( $sql, $conn ) or criticalError( mysql_error(), _("FOG :: Database error!") );
						while( $arS = mysql_fetch_array( $resS ) )
						{

							if ( $snap == -1 || $arS["sID"] == $snap )
							{
								$sql = "insert into 
										snapinTasks(stJobID, stState, stSnapinID) 
										values('$insertedID', '0', '" . $arS["sID"] . "')";
								
								if ( mysql_query( $sql, $conn ) )
									$suc++;
							}
						}
						return $suc;
					}
				}
			}
			else 
				return 0;
		}
							
	}
	return -1;
}

function getImageAction( $char )
{
	$char = strtolower( $char );
	if ( $char == "u" )
		return _("Upload");
	else if ( $char == "d" )
		return _("Download");
	else if ( $char == "w" )
		return _("Wipe");		
	else if ( $char == "x" )
		return _("Debug");			
	else if ( $char == "m" )
		return _("Memtest");			
	else if ( $char == "t" )
		return _("Testdisk");
	else if ( $char == "r" )
		return _("PhotoRec");		
	else if ( $char == "c" )
		return _("Multicast");					
	else if ( $char == "v" )
		return _("Virus Scan");			
	else if ( $char == "i" )
		return _("Inventory");		
	else if ( $char == "j" )
		return _("Pass Reset");
	else if ( $char == "s" )
		return _("All Snapins");							
	else if ( $char == "l" )
		return _("Single Snapin");		
	else if ( $char == "o" )
		return _("Wake up");			
	else
		return _("N/A");
}

// new method to handle deletimg from master
function ftpDeleteImage($conn, $imageid)
{
	if ( $conn != null && $imageid !== null && is_numeric( $imageid ) )
	{
		$sql = "SELECT 
				*
			FROM
				( SELECT * FROM images WHERE imageID = '$imageid' ) images
				INNER JOIN nfsGroups on ( images.imageNFSGroupID = nfsGroups.ngID )
				INNER JOIN nfsGroupMembers on ( nfsGroupMembers.ngmGroupID = nfsGroups.ngID )
			WHERE
				nfsGroupMembers.ngmIsMasterNode = '1' and
				nfsGroupMembers.ngmIsEnabled = '1'";
		$res = mysql_query( $sql, $conn ) or die( mysql_error() );
		if ( mysql_num_rows( $res ) == 1 )
		{

			while( $ar = mysql_fetch_array( $res ) )
			{

				$path = $ar["ngmRootPath"] . $ar["imagePath"];
				$user = $ar["ngmUser"];
				$pass = $ar["ngmPass"];
				$server = $ar["ngmHostname"];
				
				if ( $path != null && $user != null && $pass != null && $server != null )
				{

					$ftp = ftp_connect($server); 
					$ftp_loginres = ftp_login($ftp, $user, $pass); 			
					if ($ftp && $ftp_loginres ) 
					{
						return ftp_delete( $ftp, $path ); 
					}
					@ftp_close($ftp); 				
				}
				
			}
		}
	}
	return false;
}

function ftpDeleteImageDir($conn, $imageid)
{
	if ( $conn != null && $imageid !== null && is_numeric( $imageid ) )
	{
		$sql = "SELECT 
				*
			FROM
				( SELECT * FROM images WHERE imageID = '$imageid' ) images
				INNER JOIN nfsGroups on ( images.imageNFSGroupID = nfsGroups.ngID )
				INNER JOIN nfsGroupMembers on ( nfsGroupMembers.ngmGroupID = nfsGroups.ngID )
			WHERE
				nfsGroupMembers.ngmIsMasterNode = '1' and
				nfsGroupMembers.ngmIsEnabled = '1'";
		$res = mysql_query( $sql, $conn ) or die( mysql_error() );
		if ( mysql_num_rows( $res ) == 1 )
		{
			while( $ar = mysql_fetch_array( $res ) )
			{
				$path = $ar["ngmRootPath"] . $ar["imagePath"];
				$user = $ar["ngmUser"];
				$pass = $ar["ngmPass"];
				$server = $ar["ngmHostname"];
				
				if ( $path != null && $user != null && $pass != null && $server != null )
				{
					$ftp = ftp_connect($server); 
					$ftp_loginres = ftp_login($ftp, $user, $pass); 	
					if ($ftp && $ftp_loginres ) 
					{
						$arFiles = ftp_nlist($ftp, $path);
						for( $i = 0; $i < count( $arFiles ); $i++ )
						{		
							if ( $arFiles[$i] != "." && $arFiles[$i] != ".." )
								@ftp_delete( $ftp, $arFiles[$i] );		
						}
						return ftp_rmdir( $ftp, $path );
					}
					@ftp_close($ftp); 			
				}
				
			}
		}
	}
	return false;
}


function ftpDelete( $remotefile )
{
	global $conn;
	
	$ftp = ftp_connect($GLOBALS['FOGCore']->getSetting( "FOG_TFTP_HOST" )); 
	$ftp_loginres = ftp_login($ftp, $GLOBALS['FOGCore']->getSetting( "FOG_TFTP_FTP_USERNAME" ), $GLOBALS['FOGCore']->getSetting( "FOG_TFTP_FTP_PASSWORD" )); 			
	if ($ftp && $ftp_loginres ) 
	{
		return ftp_delete( $ftp, $remotefile ); 
	}
	@ftp_close($ftp); 
	return false;	
}

function ftpDeleteDir( $dir )
{
	global $conn; 
	
	$ftp = ftp_connect($GLOBALS['FOGCore']->getSetting( "FOG_TFTP_HOST" )); 
	$ftp_loginres = ftp_login($ftp, $GLOBALS['FOGCore']->getSetting( "FOG_TFTP_FTP_USERNAME" ), $GLOBALS['FOGCore']->getSetting( "FOG_TFTP_FTP_PASSWORD" )); 	
	if ($ftp && $ftp_loginres ) 
	{
		$arFiles = ftp_nlist($ftp, $dir);
		for( $i = 0; $i < count( $arFiles ); $i++ )
		{		
			if ( $arFiles[$i] != "." && $arFiles[$i] != ".." )
				@ftp_delete( $ftp, $arFiles[$i] );		
		}
		return ftp_rmdir( $ftp, $dir );
	}
	@ftp_close($ftp); 
	return false;	
}

function hasCheckedIn( $conn, $jobid )
{
	if ( $conn && $jobid )
	{
		$sql = "select (UNIX_TIMESTAMP(taskCheckIn) - UNIX_TIMESTAMP(taskCreateTime) ) as diff from tasks where taskID = '" . mysql_real_escape_string( $jobid ) . "'";
		$res = mysql_query( $sql, $conn ) or criticalError( mysql_error(), _("FOG :: Database error!") );
		if ( $ar = mysql_fetch_array( $res ) )
		{
			if ( $ar["diff"] > 2 ) return true;
		}
	}
	return false;
}

function state2text( $intState )
{
	if ( $intState == 0 )
		return _("Queued");
	else if ( $intState == 1 )
		return _("In progress");
	else if ( $intState == 2 )
		return _("complete");
	else
		return _("unknown");
}

function getNumberOfTasks($conn, $intState )
{
	if ( $conn != null )
	{
		$sql = "select 
				count(*) as cnt 
			from 
				(select * from tasks where taskStateID = '" . mysql_real_escape_string( $intState ) . "' and taskTypeID in ('" . Task::TYPE_UPLOAD . "', '" . Task::TYPE_DOWNLOAD . "') ) tasks 
				inner join hosts on ( tasks.taskHostID = hosts.hostID )";
		$res = mysql_query( $sql, $conn ) or criticalError( mysql_error(), _("FOG :: Database error!") );
		if ( $ar = mysql_fetch_array( $res ) )
		{
			return $ar["cnt"];
		}		
	}
	return 0;
}


function snapinExists( $conn, $name, $id=-1 )
{
	if ( $conn != null && $name != null )
	{
		$sql = "select count(*) as cnt from snapins where sName = '" . mysql_real_escape_string( $name ) . "' and sID <> $id";
		$res = mysql_query( $sql, $conn ) or criticalError( mysql_error(), _("FOG :: Database error!") );
		if ( $ar = mysql_fetch_array( $res ) )
		{
			if ( $ar["cnt"] == 0 )
				return false;
		}	
	}
}

function msgBox($msg)
{
	// Hook
	$GLOBALS['HookManager']->processEvent('MessageBox', array('data' => &$msg));
	
	// Insert this element into our DOM for JavaScript to find and act on
	printf('<div class="fog-message-box">%s</div>%s', $msg, "\n");
}

function lg( $string )
{
	global $conn, $currentUser;
	$uname = "";
	if ( $currentUser != null )
		$uname = mysql_real_escape_string( $currentUser->get('name') );
		
	$sql = "insert into history( hText, hUser, hTime, hIP ) values( '" . mysql_real_escape_string( $string ) . "', '" . $uname . "', NOW(), '" . $_SERVER[REMOTE_ADDR] . "')";
	@mysql_query( $sql, $conn );
}

function hostsExists( $conn, $mac, $id=-1 )
{
	if ( $conn != null && $mac != null )
	{
		$sql = "select count(*) as cnt from hosts where hostMAC = '" . mysql_real_escape_string( $mac ) . "' and hostID <> $id";
		$res = mysql_query( $sql, $conn ) or criticalError( mysql_error(), _("FOG :: Database error!") );
		if ( $ar = mysql_fetch_array( $res ) )
		{
			if ( $ar["cnt"] == 0 )
				return false;
		}
	}
	return true;
}

function trimString( $string, $len )
{
	if ( strlen($string) > $len )
	{
		return substr( trim($string), 0, $len ) . "...";
	}
	
	return $string;
}

function endsWith( $str, $sub ) 
{
   return ( substr( $str, strlen( $str ) - strlen( $sub ) ) === $sub );
}

function getImageMembersByGroupID( $conn, $groupID )
{
	$arM = array();
	if ( $conn != null && $groupID != null )
	{
		$sql = "select 
				* 
			from groups
			inner join groupMembers on ( groups.groupID = groupMembers.gmGroupID )
			where groupID = $groupID";
		$res = mysql_query( $sql, $conn ) or criticalError( mysql_error(), _("FOG :: Database error!") );
		$arHosts = array();
		while( $ar = mysql_fetch_array( $res ) )
		{
			$arHosts[] = $ar["gmHostID"];
		}
		
		for( $i = 0; $i < count( $arHosts ); $i++ )
		{
			$tmpMember = getImageMemberFromHostID( $conn, $arHosts[$i] );
			if( $tmpMember != null )
				$arM[] = $tmpMember;
		}
	}
	return $arM;
}

function getGroupNameByID( $conn, $id )
{
	if ( $conn != null && $id != null && is_numeric( $id ) )
	{
		$id = mysql_real_escape_string( $id );
		$sql = "select * from groups where groupID = '$id'";

		$res = mysql_query( $sql, $conn ) or criticalError( mysql_error(), _("FOG :: Database error!") );
		if ( mysql_num_rows( $res ) == 1 )
		{
			if( $ar = mysql_fetch_array( $res ) )
			{
				return $ar["groupName"];
			}
		}	
	}
	return null;
}

function getImageMemberFromHostID( $conn, $hostid )
{
	try
	{
		$Host = new Host($hostid);
		
		$Image = $Host->getImage();
		if (!$Image->get('id'))
		{
			throw new Exception('No Image defined for this host');
		}
		
		$StorageGroup = $Image->getStorageGroup();
		if (!$StorageGroup->get('id'))
		{
			throw new Exception('No StorageGroup defined for this host');
		}
		
		
		$Task = new Task(array(
			'hostID'	=> $hostid,
			'NFSGroupID'	=> $Host->getImage()->getStorageGroup()->get('id'),
			'NFSMemberID'	=> $Host->getImage()->getStorageGroup()->getOptimalStorageNode()->get('id')
		));
		
		
		/*
		// Fails badly when no image or storage node exists
		$Host = new Host($hostid);
		$Task = new Task(array(
			'hostID'	=> $hostid,
			'NFSGroupID'	=> $Host->getImage()->getStorageGroup()->get('id'),
			'NFSMemberID'	=> $Host->getImage()->getStorageGroup()->getOptimalStorageNode()->get('id')
		));
		*/
		
		return $Task;
	}
	catch (Exception $e)
	{
		FOGCore::error(sprintf('%s(): Error: %s', __FUNCTION__, $e->getMessage()));
		exit;
	}
	
	/*
				$sql = "SELECT 
						* 
					FROM 
						(SELECT * FROM nfsGroups WHERE ngID = '$gid' ) nfsGroups 
						INNER JOIN nfsGroupMembers on ( nfsGroupMembers.ngmGroupID = nfsGroups.ngID )
					WHERE
						nfsGroupMembers.ngmIsEnabled = '1' and 
						trim(ngmRootPath) <> '' and
						trim(ngmHostname) <> '' and
						ngmMaxClients > 0 ";
	*/
}

function wakeUp( $mac )
{
	global $conn;
	
	if ( $mac != null )
	{	
		$ch = curl_init();	
		curl_setopt($ch, CURLOPT_URL, "http://" . $GLOBALS['FOGCore']->getSetting( "FOG_WOL_HOST" ) . $GLOBALS['FOGCore']->getSetting( "FOG_WOL_PATH" ) . "?wakeonlan=$mac");
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_exec($ch);
		curl_close($ch);	
	}
}

function createPXEFile( $contents )
{
	$tmp = tempnam(sys_get_temp_dir(), 'FPX');
	$hndl = fopen( $tmp, "w" );	
	if( $hndl )
	{	
		if ( ! fwrite( $hndl, $contents ) )
		{
			FOGCore::error('Failed to write PXE file to tmp file: %s', $tmp);
		}
		
		fclose( $hndl );
		
		return $tmp;
	}
	return null;
}

/*
 *  Until we move to a busy box based client image
 *  that can handle dns lookups, this is the poor mans
 *  name resolution.
 */

function sloppyNameLookup( $host )
{
	global $conn;
	
	if ( $GLOBALS['FOGCore']->getSetting( "FOG_USE_SLOPPY_NAME_LOOKUPS" ) )
		return gethostbyname( $host );
	
	return $host;
}

function createInventoryPackage( $conn, $member, &$reason, $shutdown="", $kernel="", $otherargs="" )
{
	global $currentUser;
	if ( $conn != null && $member != null )
	{
		if (  $member->getMACDash() != null  )
		{

			$mac = strtolower( $member->getMACImageReady() );
			
			$keymapapp = "";
			$keymap = $GLOBALS['FOGCore']->getSetting( "FOG_KEYMAP" );
			if ( $keymap != null && $keymap != "" )
				$keymapapp = "keymap=$keymap";			
						
			$strKern = $GLOBALS['FOGCore']->getSetting( "FOG_TFTP_PXE_KERNEL" );
			if ( $kernel != "" )
				$strKern = $kernel;		
			$output = "# "._("Created by FOG Imaging System")."\n\n
						  DEFAULT send\n
						  LABEL send\n
						  kernel " . $strKern . "\n
						  append initrd=" . $GLOBALS['FOGCore']->getSetting( "FOG_PXE_BOOT_IMAGE" ) . "  root=/dev/ram0 rw ramdisk_size=" . $GLOBALS['FOGCore']->getSetting( "FOG_KERNEL_RAMDISK_SIZE" ) . " ip=dhcp dns=" . $GLOBALS['FOGCore']->getSetting( "FOG_PXE_IMAGE_DNSADDRESS" ) . " mac_deployed=" . $member->getMACColon() . " web=" . sloppyNameLookup($GLOBALS['FOGCore']->getSetting( "FOG_WEB_HOST")) . $GLOBALS['FOGCore']->getSetting( "FOG_WEB_ROOT" ) . " mode=autoreg deployed=1 $keymapapp shutdown=$shutdown loglevel=4 consoleblank=0 " . $GLOBALS['FOGCore']->getSetting( "FOG_KERNEL_ARGS" ) . " " . $member->getKernelArgs() . " " . $otherargs;
			$tmp = createPXEFile( $output );
			if( $tmp !== null )
			{
				$num = $GLOBALS['FOGCore']->getClass('TaskManager')->getCountOfActiveTasksWithMAC($member->getMACColon());
					
				if ( $num == 0 )
				{	
				
					$ftp = ftp_connect($GLOBALS['FOGCore']->getSetting( "FOG_TFTP_HOST" )); 
					$ftp_loginres = ftp_login($ftp, $GLOBALS['FOGCore']->getSetting( "FOG_TFTP_FTP_USERNAME" ), $GLOBALS['FOGCore']->getSetting( "FOG_TFTP_FTP_PASSWORD" )); 			
					if ($ftp && $ftp_loginres ) 
					{
						if ( ftp_put( $ftp, $GLOBALS['FOGCore']->getSetting( "FOG_TFTP_PXE_CONFIG_DIR" ) . $mac, $tmp, FTP_ASCII ) )
						{		
							$sql = "insert into 
									tasks(taskName, taskCreateTime, taskCheckIn, taskHostID, taskStateID, taskCreateBy, taskForce, taskTypeID ) 
									values('" . mysql_real_escape_string($member->getHostName() . " Inventory") . "', NOW(), NOW(), '" . $member->getID() . "', '0', '" . mysql_real_escape_string( $currentUser->get('name') ) . "', '0', 'I' )";
							if ( mysql_query( $sql, $conn ) )
							{
								wakeUp( $member->getMACColon() );																			
								lg( _("Inventory package created for host")." ". $member->getHostName() . " [" . $member->getMACDash() . "]" );										
								@ftp_close($ftp);
								@unlink( $tmp );
								return true;								
							}
							else
							{
								ftp_delete( $ftp, $GLOBALS['FOGCore']->getSetting( "FOG_TFTP_PXE_CONFIG_DIR" ) . $mac ); 									
								$reason = mysql_error();								
							}							
						}  
 						else
							$reason = _("Unable to upload file."); 											
 					}	
 					else
						$reason = _("Unable to connect to tftp server."); 				
					
					@ftp_close($ftp); 					
					@unlink( $tmp );		
				}
				else
					$reason = _("This host is already a member of a task!");	
			}
			else
				$reason = _("Failed to open tmp file.");
			
		} 
		else
			$reason = _("MAC is null.");
	}
	else
	{
		$reason = _("Either member of database connection was null");
	}
	return false;	
}

function createDiskSufaceTestPackage( $conn, $member, &$reason, $shutdown="", $kernel="", $otherargs="" )
{
	global $currentUser;
	if ( $conn != null && $member != null )
	{
		if (  $member->getMACDash() != null  )
		{

			$mac = strtolower( $member->getMACImageReady() );
			
			$keymapapp = "";
			$keymap = $GLOBALS['FOGCore']->getSetting( "FOG_KEYMAP" );
			if ( $keymap != null && $keymap != "" )
				$keymapapp = "keymap=$keymap";			
				
			$strKern = $GLOBALS['FOGCore']->getSetting( "FOG_TFTP_PXE_KERNEL" );
			if ( $kernel != "" )
				$strKern = $kernel;					
			$output = "# "._("Created by FOG Imaging System")."\n\n
						  DEFAULT send\n
						  LABEL send\n
						  kernel " . $strKern . "\n
						  append initrd=" . $GLOBALS['FOGCore']->getSetting( "FOG_PXE_BOOT_IMAGE" ) . "  root=/dev/ram0 rw ramdisk_size=" . $GLOBALS['FOGCore']->getSetting( "FOG_KERNEL_RAMDISK_SIZE" ) . " ip=dhcp dns=" . $GLOBALS['FOGCore']->getSetting( "FOG_PXE_IMAGE_DNSADDRESS" ) . " mac=" . $member->getMACColon() . " web=" . sloppyNameLookup($GLOBALS['FOGCore']->getSetting( "FOG_WEB_HOST")) . $GLOBALS['FOGCore']->getSetting( "FOG_WEB_ROOT" ) . " mode=badblocks $keymapapp shutdown=$shutdown loglevel=4 consoleblank=0 " . $GLOBALS['FOGCore']->getSetting( "FOG_KERNEL_ARGS" ) . " " . $member->getKernelArgs() . " " . $otherargs;
			$tmp = createPXEFile( $output );
			if( $tmp !== null )
			{
				$num = $GLOBALS['FOGCore']->getClass('TaskManager')->getCountOfActiveTasksWithMAC($member->getMACColon());
					
				if ( $num == 0 )
				{	
				
					$ftp = ftp_connect($GLOBALS['FOGCore']->getSetting( "FOG_TFTP_HOST" )); 
					$ftp_loginres = ftp_login($ftp, $GLOBALS['FOGCore']->getSetting( "FOG_TFTP_FTP_USERNAME" ), $GLOBALS['FOGCore']->getSetting( "FOG_TFTP_FTP_PASSWORD" )); 			
					if ($ftp && $ftp_loginres ) 
					{
						if ( ftp_put( $ftp, $GLOBALS['FOGCore']->getSetting( "FOG_TFTP_PXE_CONFIG_DIR" ) . $mac, $tmp, FTP_ASCII ) )
						{		
							$sql = "insert into 
									tasks(taskName, taskCreateTime, taskCheckIn, taskHostID, taskStateID, taskCreateBy, taskForce, taskTypeID ) 
									values('" . mysql_real_escape_string($member->getHostName() . " Testdisk") . "', NOW(), NOW(), '" . $member->getID() . "', '0', '" . mysql_real_escape_string( $currentUser->get('name') ) . "', '0', 'T' )";
							if ( mysql_query( $sql, $conn ) )
							{
								wakeUp( $member->getMACColon() );																			
								lg( _("Testdisk package created for host")." " . $member->getHostName() . " [" . $member->getMACDash() . "]" );										
								@ftp_close($ftp);
								@unlink( $tmp );
								return true;								
							}
							else
							{
								ftp_delete( $ftp, $GLOBALS['FOGCore']->getSetting( "FOG_TFTP_PXE_CONFIG_DIR" ) . $mac ); 									
								$reason = mysql_error();								
							}							
						}  
 						else
							$reason = _("Unable to upload file."); 											
 					}	
 					else
						$reason = _("Unable to connect to tftp server."); 				
					
					@ftp_close($ftp); 					
					@unlink( $tmp );		
				}	
			}
			else
				$reason = _("Failed to open tmp file.");
			
		} 
		else
			$reason = _("MAC is null.");
	}
	else
	{
		$reason = _("Either member of database connection was null");
	}
	return false;	
}


function createPassResetPackage( $conn, $member, &$reason, $shutdown="", $kernel="", $otherargs="" )
{
	global $currentUser;
	if ( $conn != null && $member != null )
	{
		if (  $member->getMACDash() != null  )
		{

			$mac = strtolower( $member->getMACImageReady() );
			
			$keymapapp = "";
			$keymap = $GLOBALS['FOGCore']->getSetting( "FOG_KEYMAP" );
			if ( $keymap != null && $keymap != "" )
				$keymapapp = "keymap=$keymap";			
						
			$strKern = $GLOBALS['FOGCore']->getSetting( "FOG_TFTP_PXE_KERNEL" );
			if ( $kernel != "" )
				$strKern = $kernel;						
			$output = "# "._("Created by FOG Imaging System")."\n\n
						  DEFAULT send\n
						  LABEL send\n
						  kernel " . $strKern . "\n
						  append initrd=" . $GLOBALS['FOGCore']->getSetting( "FOG_PXE_BOOT_IMAGE" ) . "  storage=" . sloppyNameLookup($GLOBALS['FOGCore']->getSetting( "FOG_NFS_HOST" )) . ":" . $GLOBALS['FOGCore']->getSetting( "FOG_NFS_DATADIR_UPLOAD") . " root=/dev/ram0 rw ramdisk_size=" . $GLOBALS['FOGCore']->getSetting( "FOG_KERNEL_RAMDISK_SIZE" ) . " ip=dhcp dns=" . $GLOBALS['FOGCore']->getSetting( "FOG_PXE_IMAGE_DNSADDRESS" ) . " mac=" . $member->getMACColon() . " web=" . sloppyNameLookup($GLOBALS['FOGCore']->getSetting( "FOG_WEB_HOST")) . $GLOBALS['FOGCore']->getSetting( "FOG_WEB_ROOT" ) . " mode=winpassreset $keymapapp shutdown=$shutdown loglevel=4 consoleblank=0 " . $GLOBALS['FOGCore']->getSetting( "FOG_KERNEL_ARGS" ) . " " . $member->getKernelArgs() . " " . $otherargs;
			$tmp = createPXEFile( $output );
			if( $tmp !== null )
			{
				$num = $GLOBALS['FOGCore']->getClass('TaskManager')->getCountOfActiveTasksWithMAC($member->getMACColon());
					
				if ( $num == 0 )
				{	
				
					$ftp = ftp_connect($GLOBALS['FOGCore']->getSetting( "FOG_TFTP_HOST" )); 
					$ftp_loginres = ftp_login($ftp, $GLOBALS['FOGCore']->getSetting( "FOG_TFTP_FTP_USERNAME" ), $GLOBALS['FOGCore']->getSetting( "FOG_TFTP_FTP_PASSWORD" )); 			
					if ($ftp && $ftp_loginres ) 
					{
						if ( ftp_put( $ftp, $GLOBALS['FOGCore']->getSetting( "FOG_TFTP_PXE_CONFIG_DIR" ) . $mac, $tmp, FTP_ASCII ) )
						{		
							$sql = "insert into 
									tasks(taskName, taskCreateTime, taskCheckIn, taskHostID, taskStateID, taskCreateBy, taskForce, taskTypeID ) 
									values('" . mysql_real_escape_string($member->getHostName() . " pass reset") . "', NOW(), NOW(), '" . $member->getID() . "', '0', '" . mysql_real_escape_string( $currentUser->get('name') ) . "', '0', 'J' )";
							if ( mysql_query( $sql, $conn ) )
							{
								wakeUp( $member->getMACColon() );																			
								lg( _("Password reset package created for host")." " . $member->getHostName() . " [" . $member->getMACDash() . "]" );
								@ftp_close($ftp); 					
								@unlink( $tmp );								
								return true;								
							}
							else
							{
								ftp_delete( $ftp, $GLOBALS['FOGCore']->getSetting( "FOG_TFTP_PXE_CONFIG_DIR" ) . $mac ); 									
								$reason = mysql_error();								
							}							
						}  
 						else
							$reason = _("Unable to upload file."); 											
 					}	
 					else
						$reason = _("Unable to connect to tftp server."); 				
					
					@ftp_close($ftp); 					
					@unlink( $tmp );					
									
				}	
			}
			else
				$reason = _("Failed to open tmp file.");
			
		} 
		else
			$reason = _("MAC is null.");
	}
	else
	{
		$reason = _("Either member of database connection was null");
	}
	return false;	
}

// create photorec package
function createPhotoRecPackage( $conn, $member, &$reason, $shutdown="", $kernel="", $otherargs="" )
{
	global $currentUser;
	if ( $conn != null && $member != null )
	{
		if (  $member->getMACDash() != null  )
		{

			$mac = strtolower( $member->getMACImageReady() );
			
			$keymapapp = "";
			$keymap = $GLOBALS['FOGCore']->getSetting( "FOG_KEYMAP" );
			if ( $keymap != null && $keymap != "" )
				$keymapapp = "keymap=$keymap";			
						
			$strKern = $GLOBALS['FOGCore']->getSetting( "FOG_TFTP_PXE_KERNEL" );
			if ( $kernel != "" )
				$strKern = $kernel;						
			$output = "# "._("Created by FOG Imaging System")."\n\n
						  DEFAULT send\n
						  LABEL send\n
						  kernel " . $strKern . "\n
						  append initrd=" . $GLOBALS['FOGCore']->getSetting( "FOG_PXE_BOOT_IMAGE" ) . "  storage=" . sloppyNameLookup($GLOBALS['FOGCore']->getSetting( "FOG_NFS_HOST" )) . ":" . $GLOBALS['FOGCore']->getSetting( "FOG_NFS_DATADIR_UPLOAD") . " root=/dev/ram0 rw ramdisk_size=" . $GLOBALS['FOGCore']->getSetting( "FOG_KERNEL_RAMDISK_SIZE" ) . " ip=dhcp dns=" . $GLOBALS['FOGCore']->getSetting( "FOG_PXE_IMAGE_DNSADDRESS" ) . " mac=" . $member->getMACColon() . " web=" . sloppyNameLookup($GLOBALS['FOGCore']->getSetting( "FOG_WEB_HOST")) . $GLOBALS['FOGCore']->getSetting( "FOG_WEB_ROOT" ) . " mode=photorec $keymapapp shutdown=$shutdown loglevel=4 consoleblank=0 " . $GLOBALS['FOGCore']->getSetting( "FOG_KERNEL_ARGS" ) . " " . $member->getKernelArgs() . " " . $otherargs;
			$tmp = createPXEFile( $output );
			if( $tmp !== null )
			{
				$num = $GLOBALS['FOGCore']->getClass('TaskManager')->getCountOfActiveTasksWithMAC($member->getMACColon());
					
				if ( $num == 0 )
				{	
				
					$ftp = ftp_connect($GLOBALS['FOGCore']->getSetting( "FOG_TFTP_HOST" )); 
					$ftp_loginres = ftp_login($ftp, $GLOBALS['FOGCore']->getSetting( "FOG_TFTP_FTP_USERNAME" ), $GLOBALS['FOGCore']->getSetting( "FOG_TFTP_FTP_PASSWORD" )); 			
					if ($ftp && $ftp_loginres ) 
					{
						if ( ftp_put( $ftp, $GLOBALS['FOGCore']->getSetting( "FOG_TFTP_PXE_CONFIG_DIR" ) . $mac, $tmp, FTP_ASCII ) )
						{		
							$sql = "insert into 
									tasks(taskName, taskCreateTime, taskCheckIn, taskHostID, taskStateID, taskCreateBy, taskForce, taskTypeID ) 
									values('" . mysql_real_escape_string($member->getHostName() . " photorec") . "', NOW(), NOW(), '" . $member->getID() . "', '0', '" . mysql_real_escape_string( $currentUser->get('name') ) . "', '0', 'R' )";
							if ( mysql_query( $sql, $conn ) )
							{
								wakeUp( $member->getMACColon() );																			
								lg( _("Testdisk package created for host")." " . $member->getHostName() . " [" . $member->getMACDash() . "]" );
								@ftp_close($ftp); 					
								@unlink( $tmp );								
								return true;								
							}
							else
							{
								ftp_delete( $ftp, $GLOBALS['FOGCore']->getSetting( "FOG_TFTP_PXE_CONFIG_DIR" ) . $mac ); 									
								$reason = mysql_error();								
							}							
						}  
 						else
							$reason = _("Unable to upload file."); 											
 					}	
 					else
						$reason = _("Unable to connect to tftp server."); 				
					
					@ftp_close($ftp); 					
					@unlink( $tmp );					
									
				}	
			}
			else
				$reason = _("Failed to open tmp file.");
			
		} 
		else
			$reason = _("MAC is null.");
	}
	else
	{
		$reason = _("Either member of database connection was null");
	}
	return false;	
}


function createTestDiskPackage( $conn, $member, &$reason, $shutdown="", $kernel="", $otherargs="" )
{
	global $currentUser;
	if ( $conn != null && $member != null )
	{
		if (  $member->getMACDash() != null  )
		{

			$mac = strtolower( $member->getMACImageReady() );
			
			$keymapapp = "";
			$keymap = $GLOBALS['FOGCore']->getSetting( "FOG_KEYMAP" );
			if ( $keymap != null && $keymap != "" )
				$keymapapp = "keymap=$keymap";			
						
			$strKern = $GLOBALS['FOGCore']->getSetting( "FOG_TFTP_PXE_KERNEL" );
			if ( $kernel != "" )
				$strKern = $kernel;						
			$output = "# "._("Created by FOG Imaging System")."\n\n
						  DEFAULT send\n
						  LABEL send\n
						  kernel " . $strKern . "\n
						  append initrd=" . $GLOBALS['FOGCore']->getSetting( "FOG_PXE_BOOT_IMAGE" ) . "  root=/dev/ram0 rw ramdisk_size=" . $GLOBALS['FOGCore']->getSetting( "FOG_KERNEL_RAMDISK_SIZE" ) . " ip=dhcp dns=" . $GLOBALS['FOGCore']->getSetting( "FOG_PXE_IMAGE_DNSADDRESS" ) . " mac=" . $member->getMACColon() . " web=" . sloppyNameLookup($GLOBALS['FOGCore']->getSetting( "FOG_WEB_HOST")) . $GLOBALS['FOGCore']->getSetting( "FOG_WEB_ROOT" ) . " mode=checkdisk $keymapapp shutdown=$shutdown loglevel=4 consoleblank=0 " . $GLOBALS['FOGCore']->getSetting( "FOG_KERNEL_ARGS" ) . " " . $member->getKernelArgs() . " " . $otherargs;
			$tmp = createPXEFile( $output );
			if( $tmp !== null )
			{
				$num = $GLOBALS['FOGCore']->getClass('TaskManager')->getCountOfActiveTasksWithMAC($member->getMACColon());
					
				if ( $num == 0 )
				{	
				
					$ftp = ftp_connect($GLOBALS['FOGCore']->getSetting( "FOG_TFTP_HOST" )); 
					$ftp_loginres = ftp_login($ftp, $GLOBALS['FOGCore']->getSetting( "FOG_TFTP_FTP_USERNAME" ), $GLOBALS['FOGCore']->getSetting( "FOG_TFTP_FTP_PASSWORD" )); 			
					if ($ftp && $ftp_loginres ) 
					{
						if ( ftp_put( $ftp, $GLOBALS['FOGCore']->getSetting( "FOG_TFTP_PXE_CONFIG_DIR" ) . $mac, $tmp, FTP_ASCII ) )
						{		
							$sql = "insert into 
									tasks(taskName, taskCreateTime, taskCheckIn, taskHostID, taskStateID, taskCreateBy, taskForce, taskTypeID ) 
									values('" . mysql_real_escape_string($member->getHostName() . " Testdisk") . "', NOW(), NOW(), '" . $member->getID() . "', '0', '" . mysql_real_escape_string( $currentUser->get('name') ) . "', '0', 'T' )";
							if ( mysql_query( $sql, $conn ) )
							{
								wakeUp( $member->getMACColon() );																			
								lg( _("Testdisk package created for host")." " . $member->getHostName() . " [" . $member->getMACDash() . "]" );
								@ftp_close($ftp); 					
								@unlink( $tmp );								
								return true;								
							}
							else
							{
								ftp_delete( $ftp, $GLOBALS['FOGCore']->getSetting( "FOG_TFTP_PXE_CONFIG_DIR" ) . $mac ); 									
								$reason = mysql_error();								
							}							
						}  
 						else
							$reason = _("Unable to upload file."); 											
 					}	
 					else
						$reason = _("Unable to connect to tftp server."); 				
					
					@ftp_close($ftp); 					
					@unlink( $tmp );					
									
				}	
			}
			else
				$reason = _("Failed to open tmp file.");
			
		} 
		else
			$reason = _("MAC is null.");
	}
	else
	{
		$reason = _("Either member of database connection was null");
	}
	return false;	
}

function createWipePackage( $conn, $member, &$reason, $mode=WIPE_NORMAL, $shutdown="", $kernel="", $otherargs="" )
{
	global $currentUser;
	if ( $conn != null && $member != null )
	{
		if ( $member->getMACDash() != null  )
		{

			$mac = strtolower( $member->getMACImageReady() );
						
			$wipemode="wipemode=full";
			if ( $mode ==  WIPE_FAST )
				$wipemode="wipemode=fast";
			else if ( $mode ==  WIPE_NORMAL )
				$wipemode="wipemode=normal";
			else if ( $mode ==  WIPE_FULL )	
				$wipemode="wipemode=full";		
				
			$keymapapp = "";
			$keymap = $GLOBALS['FOGCore']->getSetting( "FOG_KEYMAP" );
			if ( $keymap != null && $keymap != "" )
				$keymapapp = "keymap=$keymap";				
				
			$strKern = $GLOBALS['FOGCore']->getSetting( "FOG_TFTP_PXE_KERNEL" );
			if ( $kernel != "" )
				$strKern = $kernel;				
			$output = "# "._("Created by FOG Imaging System")."\n\n
						  DEFAULT send\n
						  LABEL send\n
						  kernel " . $strKern . "\n
						  append initrd=" . $GLOBALS['FOGCore']->getSetting( "FOG_PXE_BOOT_IMAGE" ) . "  root=/dev/ram0 rw ramdisk_size=" . $GLOBALS['FOGCore']->getSetting( "FOG_KERNEL_RAMDISK_SIZE" ) . " ip=dhcp dns=" . $GLOBALS['FOGCore']->getSetting( "FOG_PXE_IMAGE_DNSADDRESS" ) . " mac=" . $member->getMACColon() . " web=" . sloppyNameLookup( $GLOBALS['FOGCore']->getSetting( "FOG_WEB_HOST") ) . $GLOBALS['FOGCore']->getSetting( "FOG_WEB_ROOT" ) . " osid=" . $member->getOSID() . " $wipemode mode=wipe $keymapapp shutdown=$shutdown loglevel=4 consoleblank=0 " . $GLOBALS['FOGCore']->getSetting( "FOG_KERNEL_ARGS" ) . " " . $member->getKernelArgs() . " " . $otherargs;
			$tmp = createPXEFile( $output );
			if( $tmp !== null )
			{
				$num = $GLOBALS['FOGCore']->getClass('TaskManager')->getCountOfActiveTasksWithMAC($member->getMACColon());
					
				if ( $num == 0 )
				{	
				
					$ftp = ftp_connect($GLOBALS['FOGCore']->getSetting( "FOG_TFTP_HOST" )); 
					$ftp_loginres = ftp_login($ftp, $GLOBALS['FOGCore']->getSetting( "FOG_TFTP_FTP_USERNAME" ), $GLOBALS['FOGCore']->getSetting( "FOG_TFTP_FTP_PASSWORD" )); 			
					if ($ftp && $ftp_loginres ) 
					{
						if ( ftp_put( $ftp, $GLOBALS['FOGCore']->getSetting( "FOG_TFTP_PXE_CONFIG_DIR" ) . $mac, $tmp, FTP_ASCII ) )
						{		
							$sql = "insert into 
									tasks(taskName, taskCreateTime, taskCheckIn, taskHostID, taskStateID, taskCreateBy, taskForce, taskTypeID ) 
									values('" . mysql_real_escape_string($member->getHostName() . " Wipe") . "', NOW(), NOW(), '" . $member->getID() . "', '0', '" . mysql_real_escape_string( $currentUser->get('name') ) . "', '0', 'W' )";
							if ( mysql_query( $sql, $conn ) )
							{
								wakeUp( $member->getMACColon() );																			
								lg( "Wipe package created for host " . $member->getHostName() . " [" . $member->getMACDash() . "]" );										
								@ftp_close($ftp); 					
								@unlink( $tmp );								
								return true;								
							}
							else
							{
								ftp_delete( $ftp, $GLOBALS['FOGCore']->getSetting( "FOG_TFTP_PXE_CONFIG_DIR" ) . $mac ); 									
								$reason = mysql_error();								
							}							
						}  
 						else
							$reason = _("Unable to upload file."); 											
 					}	
 					else
						$reason = _("Unable to connect to tftp server."); 				
					
					@ftp_close($ftp); 					
					@unlink( $tmp ); 					
									
				}	
			}
			else
				$reason = _("Failed to open tmp file.");
			
		} 
		else
			$reason = _("MAC is null.");
	}
	else
	{
		$reason = _("Either member of database connection was null");
	}
	return false;	
}

function avModeToString( $avMode )
{
	if ( $avMode == "q" )
		return _("Quarantine");
	else if ( $avMode == "s" )
		return _("Report");	
}

function clearAVRecord( $conn, $avID )
{
	if ( $conn != null && $avID != null && is_numeric( $avID ) )
	{
		$vid = mysql_real_escape_string( $avID );
		$sql = "delete from virus where vID = '$vid'";
		return mysql_query( $sql, $conn );
	}
	return false;
}

function clearAVRecordsForHost( $conn, $mac )
{
	if ( $conn != null && $mac != null  )
	{
		$mac = mysql_real_escape_string( $mac );
		$sql = "delete from virus where vHostMAC = '$mac'";
		return mysql_query( $sql, $conn );
	}
	return false;
}

function clearAllAVRecords( $conn )
{
	if ( $conn != null  )
	{
		$sql = "delete from virus";
		return mysql_query( $sql, $conn );
	}
	return false;
}

function createAVPackage( $conn, $member, &$reason, $mode=FOG_AV_SCANONLY, $shutdown="", $kernel="", $otherargs="" )
{
	global $currentUser;
	if ( $conn != null && $member != null )
	{
		if ( $member->getMACDash() != null  )
		{

			$mac = strtolower( $member->getMACImageReady() );
						
			$scanmode="avmode=s";
			if ( $mode ==  FOG_AV_SCANQUARANTINE )
				$scanmode="avmode=q";
		
			$keymapapp = "";
			$keymap = $GLOBALS['FOGCore']->getSetting( "FOG_KEYMAP" );
			if ( $keymap != null && $keymap != "" )
				$keymapapp = "keymap=$keymap";		
				
			$strKern = $GLOBALS['FOGCore']->getSetting( "FOG_TFTP_PXE_KERNEL" );
			if ( $kernel != "" )
				$strKern = $kernel;				
			$output = "# "._("Created by FOG Imaging System")."\n\n
						  DEFAULT send\n
						  LABEL send\n
						  kernel " . $strKern . "\n
						  append initrd=" . $GLOBALS['FOGCore']->getSetting( "FOG_PXE_BOOT_IMAGE" ) . "  root=/dev/ram0 rw ramdisk_size=" . $GLOBALS['FOGCore']->getSetting( "FOG_KERNEL_RAMDISK_SIZE" ) . " ip=dhcp dns=" . $GLOBALS['FOGCore']->getSetting( "FOG_PXE_IMAGE_DNSADDRESS" ) . " mac=" . $member->getMACColon() . " web=" . sloppyNameLookup($GLOBALS['FOGCore']->getSetting( "FOG_WEB_HOST")) . $GLOBALS['FOGCore']->getSetting( "FOG_WEB_ROOT" ) . " osid=" . $member->getOSID() . " $scanmode mode=clamav $keymapapp shutdown=$shutdown loglevel=4 consoleblank=0 " . $GLOBALS['FOGCore']->getSetting( "FOG_KERNEL_ARGS" ) . " " . $member->getKernelArgs() . " " . $otherargs;
			$tmp = createPXEFile( $output );
			if( $tmp !== null )
			{
				$num = $GLOBALS['FOGCore']->getClass('TaskManager')->getCountOfActiveTasksWithMAC($member->getMACColon());
					
				if ( $num == 0 )
				{	
				
					$ftp = ftp_connect($GLOBALS['FOGCore']->getSetting( "FOG_TFTP_HOST" )); 
					$ftp_loginres = ftp_login($ftp, $GLOBALS['FOGCore']->getSetting( "FOG_TFTP_FTP_USERNAME" ), $GLOBALS['FOGCore']->getSetting( "FOG_TFTP_FTP_PASSWORD" )); 			
					if ($ftp && $ftp_loginres ) 
					{
						if ( ftp_put( $ftp, $GLOBALS['FOGCore']->getSetting( "FOG_TFTP_PXE_CONFIG_DIR" ) . $mac, $tmp, FTP_ASCII ) )
						{		
							$sql = "insert into 
									tasks(taskName, taskCreateTime, taskCheckIn, taskHostID, taskStateID, taskCreateBy, taskForce, taskTypeID ) 
									values('" . mysql_real_escape_string($member->getHostName() . " ClamScan") . "', NOW(), NOW(), '" . $member->getID() . "', '0', '" . mysql_real_escape_string( $currentUser->get('name') ) . "', '0', 'v' )";
							if ( mysql_query( $sql, $conn ) )
							{
								wakeUp( $member->getMACColon() );																			
								lg( _("ClamAV package created for host")." " . $member->getHostName() . " [" . $member->getMACDash() . "]" );										
								@ftp_close($ftp); 					
								@unlink( $tmp );								
								return true;								
							}
							else
							{
								ftp_delete( $ftp, $GLOBALS['FOGCore']->getSetting( "FOG_TFTP_PXE_CONFIG_DIR" ) . $mac ); 									
								$reason = mysql_error();								
							}							
						}  
 						else
							$reason = _("Unable to upload file."); 											
 					}	
 					else
						$reason = _("Unable to connect to tftp server."); 				
					
					@ftp_close($ftp); 					
					@unlink( $tmp ); 					
									
				}	
				else
					$reason = _("Host is already a member of an active task!");
			}
			else
				$reason = _("Failed to open tmp file.");
			
		} 
		else
			$reason = _("MAC is null.");
	}
	else
	{
		$reason = _("Either member of database connection was null");
	}
	return false;	
}


function createUploadImagePackage( $conn, $member, &$reason, $debug=false, $shutdown="", $kernel="", $otherargs="" )
{
	global $currentUser;
	if ( $conn != null && $member != null )
	{
		if (  $member->getImage() != null && $member->getMACDash() != null  )
		{

			$mac = strtolower( $member->getMACImageReady() );

			$image = $member->getImage();
			$imageid = $member->getImageID();
			$building = $member->getBuilding();

			$mode = "";
			if ($debug)
				$mode = "mode=debug";		
				
			// since this is an upload, we need to get the master server for the NFS Group
			if( $member->getStorageGroup() !== null  && is_numeric( $member->getStorageGroup()  ) )
			{
				
				$sql = "SELECT 
						* 
					FROM 
						nfsGroups 
						INNER JOIN nfsGroupMembers on ( nfsGroups.ngID = nfsGroupMembers.ngmGroupID )
					WHERE 
						ngID = '"  . $member->getStorageGroup() . "' and
						ngmIsEnabled = '1' and 
						ngmIsMasterNode = '1'";
				
				$res = mysql_query( $sql, $conn ) or die( mysql_error() );
				if ( mysql_num_rows( $res ) == 1 )
				{
				
					while( $ar = mysql_fetch_array( $res ) )
					{
						$nfsip = trim(mysql_real_escape_string($ar["ngmHostname"]));
						$nfsroot = trim(mysql_real_escape_string($ar["ngmRootPath"]));
						$groupid = trim(mysql_real_escape_string( $ar["ngID"] ));
						$nodeid  = trim(mysql_real_escape_string( $ar["ngmID"] ));
				
						
						if ( $nfsip != null && $nfsroot != null && is_numeric( $groupid ) && is_numeric( $nodeid ) )
						{
							if ( endsWith( $nfsroot, "/" )  )
								$nfsroot .= "dev/";
							else 
								$nfsroot .= "/dev/";					
						
							$imgType = "imgType=n";
							if ( $member->getImageType() == Image::IMAGE_TYPE_DD )
								$imgType = "imgType=dd";
							else if ( $member->getImageType() == Image::IMAGE_TYPE_MULTIPARTITION_SINGLE_DISK )
								$imgType = "imgType=mps";
							else if ( $member->getImageType() == Image::IMAGE_TYPE_MULTIPARTITION_MULTIDISK )
								$imgType = "imgType=mpa";			
				
							$pct = "pct=5"; // default percentage
			
							if ( is_numeric($GLOBALS['FOGCore']->getSetting( "FOG_UPLOADRESIZEPCT") ) && $GLOBALS['FOGCore']->getSetting( "FOG_UPLOADRESIZEPCT") >= 5 && $GLOBALS['FOGCore']->getSetting( "FOG_UPLOADRESIZEPCT") < 100 )
								$pct = "pct=" . $GLOBALS['FOGCore']->getSetting( "FOG_UPLOADRESIZEPCT");
			
							$ignorepg = "0";
			
							if ( $GLOBALS['FOGCore']->getSetting( "FOG_UPLOADIGNOREPAGEHIBER" ) )
								$ignorepg = "1";
			
							$keymapapp = "";
							$keymap = $GLOBALS['FOGCore']->getSetting( "FOG_KEYMAP" );
							if ( $keymap != null && $keymap != "" )
								$keymapapp = "keymap=$keymap";			

							$strKern = $GLOBALS['FOGCore']->getSetting( "FOG_TFTP_PXE_KERNEL" );
							if ( $kernel != "" )
								$strKern = $kernel;			
							$output = "# "._("Created by FOG Imaging System")."\n\n
										  DEFAULT send\n
										  LABEL send\n
										  kernel " . $strKern . "\n
										  append initrd=" . $GLOBALS['FOGCore']->getSetting( "FOG_PXE_BOOT_IMAGE" ) . "  root=/dev/ram0 rw ramdisk_size=" . $GLOBALS['FOGCore']->getSetting( "FOG_KERNEL_RAMDISK_SIZE" ) . " ip=dhcp dns=" . $GLOBALS['FOGCore']->getSetting( "FOG_PXE_IMAGE_DNSADDRESS" ) . " type=up img=$image imgid=$imageid mac=" . $member->getMACColon() . " storage=" . $nfsip . ":" . $nfsroot . " web=" . sloppyNameLookup($GLOBALS['FOGCore']->getSetting( "FOG_WEB_HOST")) . $GLOBALS['FOGCore']->getSetting( "FOG_WEB_ROOT" ) . " ignorepg=$ignorepg osid=" . $member->getOSID() . " $mode $pct $imgType $keymapapp shutdown=$shutdown loglevel=4 consoleblank=0 " . $GLOBALS['FOGCore']->getSetting( "FOG_KERNEL_ARGS" ) . " " . $member->getKernelArgs() . " " . $otherargs;
							$tmp = createPXEFile( $output );
							if( $tmp !== null )
							{
								$num = $GLOBALS['FOGCore']->getClass('TaskManager')->getCountOfActiveTasksWithMAC($member->getMACColon());
					
								if ( $num == 0 )
								{	
				
									$ftp = ftp_connect($GLOBALS['FOGCore']->getSetting( "FOG_TFTP_HOST" )); 
									$ftp_loginres = ftp_login($ftp, $GLOBALS['FOGCore']->getSetting( "FOG_TFTP_FTP_USERNAME" ), $GLOBALS['FOGCore']->getSetting( "FOG_TFTP_FTP_PASSWORD" )); 			
									if ($ftp && $ftp_loginres ) 
									{
										if ( ftp_put( $ftp, $GLOBALS['FOGCore']->getSetting( "FOG_TFTP_PXE_CONFIG_DIR" ) . $mac, $tmp, FTP_ASCII ) )
										{		
											$sql = "insert into 
													tasks(taskName, taskCreateTime, taskCheckIn, taskHostID, taskStateID, taskCreateBy, taskForce, taskType, taskNFSGroupID, taskNFSMemberID ) 
													values('" . mysql_real_escape_string($taskName) . "', NOW(), NOW(), '" . $member->getID() . "', '0', '" . mysql_real_escape_string( $currentUser->get('name') ) . "', '0', 'U', '$groupid', '$nodeid' )";
											if ( mysql_query( $sql, $conn ) )
											{
												wakeUp( $member->getMACColon() );																			
												lg( _("Image upload package created for host")." " . $member->getHostName() . " [" . $member->getMACDash() . "]" );
												@ftp_close($ftp); 					
												@unlink( $tmp );								
												return true;								
											}
											else
											{
												ftp_delete( $ftp, $GLOBALS['FOGCore']->getSetting( "FOG_TFTP_PXE_CONFIG_DIR" ) . $mac ); 									
												$reason = mysql_error();								
											}							
										}  
				 						else
											$reason = _("Unable to upload file."); 											
				 					}	
				 					else
										$reason = _("Unable to connect to tftp server."); 				
					
									@ftp_close($ftp); 					
									@unlink( $tmp );					
									
								}	
							}
							else
								$reason = _("Failed to open tmp file.");
						}
						else
							$reason = _("Unable to determine valid settings for NFS Server.");
					}
				}
				else
					$reason = _("Unable to located master node from storage group.");
			}
			else
				$reason = _("Unable to determine the Group ID number.");
			
		} 
		else
		{
			if( $member->getImage() == null )
				$reason = _("Image assocation is null, please define an image for this host.");
			
			if ( $member->getMACDash() == null )
				$reason = _("MAC Address is null");		
		}
	}
	else
	{
		$reason = _("Either member of database connection was null");
	}
	return false;
}

function createMemTestPackage($conn, $member, &$reason)
{
	// Load memtest86+
	global $currentUser;
	if ( $conn != null && $member != null )
	{
		if ( $member->getMACDash() != null )
		{
			$mac = strtolower( $member->getMACImageReady() );

			$output = "# "._("Created by FOG Imaging System")."\n\n
						  DEFAULT fog\n
						  LABEL fog\n
						  kernel " . $GLOBALS['FOGCore']->getSetting( "FOG_MEMTEST_KERNEL" ) . "\n";
						  	  
			$tmp = createPXEFile( $output );

			if( $tmp !== null )
			{
				// make sure there is no active task for this mac address
				$num = $GLOBALS['FOGCore']->getClass('TaskManager')->getCountOfActiveTasksWithMAC($member->getMACColon());
				
				if ( $num == 0 )
				{
					// attempt to ftp file
										
					$ftp = ftp_connect($GLOBALS['FOGCore']->getSetting( "FOG_TFTP_HOST" )); 
					$ftp_loginres = ftp_login($ftp, $GLOBALS['FOGCore']->getSetting( "FOG_TFTP_FTP_USERNAME" ), $GLOBALS['FOGCore']->getSetting( "FOG_TFTP_FTP_PASSWORD" ) ); 			
					if ($ftp && $ftp_loginres ) 
					{
						if ( ftp_put( $ftp, $GLOBALS['FOGCore']->getSetting( "FOG_TFTP_PXE_CONFIG_DIR" ) . $mac, $tmp, FTP_ASCII ) )
						{						
							$sql = "insert into 
									tasks(taskName, taskCreateTime, taskCheckIn, taskHostID, taskStateID, taskCreateBy, taskForce, taskTypeID ) 
									values('" . mysql_real_escape_string('MEMTEST') . "', NOW(), NOW(), '" . $member->getID() . "', '0', '" . mysql_real_escape_string( $currentUser->get('name') ) . "', '0', 'M' )";
							if ( mysql_query( $sql, $conn ) )
							{
								// lets try to wake the computer up!
								wakeUp( $member->getMACColon() );																			
								lg( _("memtest package created for host")." " . $member->getHostName() . " [" . $member->getMACDash() . "]" );
								@ftp_close($ftp); 					
								@unlink( $tmp );								
								return true;
							}
							else
							{
								ftp_delete( $ftp, $GLOBALS['FOGCore']->getSetting( "FOG_TFTP_PXE_CONFIG_DIR" ) . $mac ); 									
								$reason = mysql_error();
							}
						}  
						else
							$reason = _("Unable to upload file."); 											
					}	
					else
						$reason = _("Unable to connect to tftp server."); 	
						
					@ftp_close($ftp); 					
					@unlink( $tmp );							
				}
				else
					$reason = _("Host is already a member of a active task.");
			}
			else
				$reason = _("Failed to open tmp file.");
			
		} 
		else
		{
			if ( $member->getMACDash() == null )
				$reason = _("MAC Address is null");
		}
	}
	else
	{
		$reason = _("Either member of database connection was null");
	}
	return false;
}


function createDebugPackage($conn, $member, &$reason, $kernel="", $otherargs="")
{
	// Just load image
	global $currentUser;
	if ( $conn != null && $member != null )
	{
		if ( $member->getMACDash() != null )
		{
			$mac = strtolower( $member->getMACImageReady() );

			$keymapapp = "";
			$keymap = $GLOBALS['FOGCore']->getSetting( "FOG_KEYMAP" );
			if ( $keymap != null && $keymap != "" )
				$keymapapp = "keymap=$keymap";
			
			$strKern = $GLOBALS['FOGCore']->getSetting( "FOG_TFTP_PXE_KERNEL" );
			if ( $kernel != "" )
				$strKern = $kernel;			
			$output = "# "._("Created by FOG Imaging System")."\n\n
						  DEFAULT fog\n
						  LABEL fog\n
						  kernel " . $strKern . "\n
						  append initrd=" . $GLOBALS['FOGCore']->getSetting( "FOG_PXE_BOOT_IMAGE" ) . "  root=/dev/ram0 rw ramdisk_size=" . $GLOBALS['FOGCore']->getSetting( "FOG_KERNEL_RAMDISK_SIZE" ) . " ip=dhcp dns=" . $GLOBALS['FOGCore']->getSetting( "FOG_PXE_IMAGE_DNSADDRESS" ) . " mode=onlydebug consoleblank=0 $keymapapp " . $GLOBALS['FOGCore']->getSetting( "FOG_KERNEL_ARGS" ) . " " . $member->getKernelArgs() . " " . $otherargs;
						  
			$tmp = createPXEFile( $output );

			if( $tmp !== null )
			{
				// make sure there is no active task for this mac address
				$num = $GLOBALS['FOGCore']->getClass('TaskManager')->getCountOfActiveTasksWithMAC($member->getMACColon());
				
				if ( $num == 0 )
				{
					// attempt to ftp file
										
					$ftp = ftp_connect($GLOBALS['FOGCore']->getSetting( "FOG_TFTP_HOST" )); 
					$ftp_loginres = ftp_login($ftp, $GLOBALS['FOGCore']->getSetting( "FOG_TFTP_FTP_USERNAME" ), $GLOBALS['FOGCore']->getSetting( "FOG_TFTP_FTP_PASSWORD" )); 			
					if ($ftp && $ftp_loginres ) 
					{
						if ( ftp_put( $ftp, $GLOBALS['FOGCore']->getSetting( "FOG_TFTP_PXE_CONFIG_DIR" ) . $mac, $tmp, FTP_ASCII ) )
						{						
							$sql = "insert into 
									tasks(taskName, taskCreateTime, taskCheckIn, taskHostID, taskStateID, taskCreateBy, taskForce, taskTypeID ) 
									values('" . mysql_real_escape_string('DEBUG') . "', NOW(), NOW(), '" . $member->getID() . "', '0', '" . mysql_real_escape_string( $currentUser->get('name') ) . "', '0', 'X' )";
							if ( mysql_query( $sql, $conn ) )
							{
								// lets try to wake the computer up!
								wakeUp( $member->getMACColon() );																			
								lg( _("debug package created for host")." " . $member->getHostName() . " [" . $member->getMACDash() . "]" );
								@ftp_close($ftp); 					
								@unlink( $tmp );								
								return true;
							}
							else
							{
								ftp_delete( $ftp, $GLOBALS['FOGCore']->getSetting( "FOG_TFTP_PXE_CONFIG_DIR" ) . $mac ); 									
								$reason = mysql_error();
							}
						}  
						else
							$reason = _("Unable to upload file."); 											
					}	
					else
						$reason = _("Unable to connect to tftp server."); 	
						
					@ftp_close($ftp); 					
					@unlink( $tmp );							
				}
				else
					$reason = _("Host is already a member of a active task.");
			}
			else
				$reason = _("Failed to open tmp file.");
			
		} 
		else
		{
			if ( $member->getMACDash() == null )
				$reason = _("MAC Address is null");
		}
	}
	else
	{
		$reason = _("Either member of database connection was null");
	}
	return false;
}

function getMulticastPort( $conn )
{
	$endingPort = $GLOBALS['FOGCore']->getSetting( "FOG_UDPCAST_STARTINGPORT" ) + ($GLOBALS['FOGCore']->getSetting( "FOG_MULTICAST_MAX_SESSIONS" ) * 2);
	if ( $conn !== null && $GLOBALS['FOGCore']->getSetting( "FOG_UDPCAST_STARTINGPORT" ) !== null && isValidPortNumber( $GLOBALS['FOGCore']->getSetting( "FOG_UDPCAST_STARTINGPORT" ) ) && isValidPortNumber( $endingPort ) )
	{
		$sql = "select msBasePort from multicastSessions order by msID desc limit 1";
		$res = mysql_query( $sql, $conn ) or criticalError( mysql_error(), _("FOG :: Database error!") );
		
		$recPort = $GLOBALS['FOGCore']->getSetting( "FOG_UDPCAST_STARTINGPORT" );
		
		if ( $ar = mysql_fetch_array( $res ) )
		{
			$potPort = ($ar["msBasePort"]  + 2);
			if ( $potPort >= $GLOBALS['FOGCore']->getSetting( "FOG_UDPCAST_STARTINGPORT" ) && ($potPort + 1) < $endingPort )
			{
				$recPort = $potPort;
			}
		}
		
		if ( ( ( $recPort % 2 ) == 0 ) && $recPort >= $GLOBALS['FOGCore']->getSetting( "FOG_UDPCAST_STARTINGPORT" ) && $recPort + 1 < $endingPort )
		{
			return $recPort;
		}
	}
	return -1;
}

function isValidPortNumber( $port )
{
	if ( $port <= 65535 && $port > 0 && is_numeric($port) )
		return true;
		
	return false;
}


function deleteMulticastJob( $conn, $mcid )
{
	// first pulls all the associations, delete the jobs in task table, delete associations, then deletes mc task.
	if ( $conn != null && is_numeric( $mcid ) && $mcid !== null )
	{
		$mcid = mysql_real_escape_string( $mcid );
		$sql = "select tID from multicastSessionsAssoc where msID = '$mcid'";
		$res = mysql_query( $sql, $conn ) or criticalError( mysql_error(), _("FOG :: Database error!") );
		while( $ar = mysql_fetch_array( $res ) )
		{
			$sql = "select taskHostID from tasks where taskID = '" . mysql_real_escape_string( $ar["tID"] ) . "'";
			$res_hostid = mysql_query( $sql, $conn ) or criticalError( mysql_error(), _("FOG :: Database error!") );
			if ( $ar_hid = mysql_fetch_array( $res_hostid ) )
			{
				$im = getImageMemberFromHostID( $conn, $ar_hid["taskHostID"] );
				if ( $im != null )
				{
					if ( ! ftpDelete( $GLOBALS['FOGCore']->getSetting( "FOG_TFTP_PXE_CONFIG_DIR" ) . $im->getMACImageReady() ) )
					{
						msgBox( _("Unable to delete PXE file") );
					}				
				}
			}
					
			$sql = "delete from tasks where taskID = '" . mysql_real_escape_string( $ar["tID"] ) . "'";
			mysql_query( $sql, $conn ) or criticalError( mysql_error(), _("FOG :: Database error!") );			
		}
		
		// now remove all the associations
		$sql = "delete from multicastSessionsAssoc where msID = '$mcid'";
		mysql_query( $sql, $conn ) or criticalError( mysql_error(), _("FOG :: Database error!") );
		
		// now remove the multicast task
		$sql = "update multicastSessions set msState = '2' where msID = '" . $mcid . "'";
		mysql_query( $sql, $conn ) or criticalError( mysql_error(), _("FOG :: Database error!") );	
		return true;	
	}
	return false;
}

// returns the parent job id
function createMulticastJob( $conn, $name, $port, $path, $eth, $imagetype, $nfsGroupID )
{
	if ( $conn != null && isValidPortNumber($port) && $path !== null  )
	{
		$name = mysql_real_escape_string( $name );
		$port = mysql_real_escape_string( $port );
		$path = mysql_real_escape_string( $path );
		$eth  = mysql_real_escape_string( $eth );
		$dd = mysql_real_escape_string($imagetype); 
		$nfsGroupID = mysql_real_escape_string( $nfsGroupID );

		$sql = "insert 
				into multicastSessions
				(msName, msBasePort, msImage, msInterface, msStartDateTime, msPercent, msState, msIsDD, msNFSGroupID ) 
				values
				('$name', '$port', '$path', '$eth', NOW(), '0', '-1', '$dd', '$nfsGroupID')";
		mysql_query( $sql, $conn ) or criticalError( mysql_error(), _("FOG :: Database error!") );
		$id = mysql_insert_id( $conn );
		if ( $id !== null )
			return $id;
	}
	return -1;
}

function activateMulticastJob( $conn, $mcid )
{
	if ( $conn != null && is_numeric( $mcid ) )
	{
		$sql = "UPDATE
				multicastSessions
			SET
				msState = '0'
			WHERE
				msID = '$mcid'";
		if ( mysql_query( $sql, $conn ) )
			return true;
	}
	return false;
}

function linkTaskToMultitaskJob( $conn, $taskid, $mcid )
{
	if ( $conn != null && $taskid !== null && $mcid !== null && is_numeric( $taskid) && is_numeric( $mcid ) )
	{
		$taskid = mysql_real_escape_string( $taskid );
		$mcid = mysql_real_escape_string( $mcid );
		
		$sql = "insert into multicastSessionsAssoc(msID, tID) values('$mcid', '$taskid')";
		
		mysql_query( $sql, $conn ) or criticalError( mysql_error(), _("FOG :: Database error!") );
		return true;		
	}
	return false;
}

// this function return the insert id so the multicast session can be linked with the single tasks
function createImagePackageMulticast($conn, $member, $taskName, $port, &$reason, $debug=false, $deploySnapins=true, $shutdown="", $kernel="", $otherargs="" )
{
	global $currentUser;
	
	if ( $conn != null && $member != null  )
	{
		if ( $port !== null && is_numeric( $port ) )
		{
			if ( $member->getImage() != null && $member->getMACDash() != null )
			{
				if ( $member->getStorageGroup() != null && is_numeric( $member->getStorageGroup() ) )
				{
				
					$blStorageOk = false;
					$snIP = null;
					$snRoot = null;
					$sql = "SELECT * FROM nfsGroupMembers WHERE ngmGroupID = '" . $member->getStorageGroup() . "' and ngmIsMasterNode = '1' and ngmIsEnabled = '1'";
					$res = mysql_query( $sql, $conn ) or die( mysql_error() );
					if ( mysql_num_rows( $res ) == 1 )
					{
						while( $ar = mysql_fetch_array( $res ) )
						{
							$snIP = $ar["ngmHostname"];
							$snRoot = $ar["ngmRootPath"];
							$blStorageOk = true;
						}
					}
				
					if ( $blStorageOk )
					{
						$mac = strtolower( $member->getMACImageReady() );

						$image = $member->getImage();
						$mode = "";
						if ($debug)
							$mode = "mode=debug";
				
						$imgType = "imgType=n";
						if ( $member->getImageType() == Image::IMAGE_TYPE_DD )
							$imgType = "imgType=dd";
						else if ( $member->getImageType() == Image::IMAGE_TYPE_MULTIPARTITION_SINGLE_DISK )
							$imgType = "imgType=mps";
						else if ( $member->getImageType() == Image::IMAGE_TYPE_MULTIPARTITION_MULTIDISK )
							$imgType = "imgType=mpa";						
						else
						{
							if ( $member->getOSID() == "99" )
							{
								$reason = _("Invalid OS type, unable to determine MBR.");
								return -1;
							}
					
							if ( strlen( trim($member->getOSID()) ) == 0 )
							{
								$reason = _("Invalid OS type, you must specify an OS Type to image.");
								return -1;
							}
					
							if ( trim($member->getOSID()) != "1" && trim($member->getOSID()) != "2" && trim($member->getOSID()) != "5")
							{
								$reason = _("Unsupported OS detected in host!");
								return -1;
							}
						}									
				
						$keymapapp = "";
						$keymap = $GLOBALS['FOGCore']->getSetting( "FOG_KEYMAP" );
						if ( $keymap != null && $keymap != "" )
							$keymapapp = "keymap=$keymap";				

						$strKern = $GLOBALS['FOGCore']->getSetting( "FOG_TFTP_PXE_KERNEL" );
						if ( $kernel != "" )
							$strKern = $kernel;				
						$output = "# "._("Created by FOG Imaging System")."\n\n
									  DEFAULT fog\n
									  LABEL fog\n
									  kernel " . $strKern . "\n
									  append initrd=" . $GLOBALS['FOGCore']->getSetting( "FOG_PXE_BOOT_IMAGE" ) . " root=/dev/ram0 rw ramdisk_size=" . $GLOBALS['FOGCore']->getSetting( "FOG_KERNEL_RAMDISK_SIZE" ) . " ip=dhcp dns=" . $GLOBALS['FOGCore']->getSetting( "FOG_PXE_IMAGE_DNSADDRESS" ) . " type=down img=$image mc=yes port=" . $port . " storageip=" . $snIP . " storage=" . $snIP . ":" . $snRoot . " mac=" . $member->getMACColon() . " ftp=" . sloppyNameLookup($GLOBALS['FOGCore']->getSetting( "FOG_TFTP_HOST" )) . " web=" . sloppyNameLookup($GLOBALS['FOGCore']->getSetting( "FOG_WEB_HOST")) . $GLOBALS['FOGCore']->getSetting( "FOG_WEB_ROOT" ) . " osid=" . $member->getOSID() . " $mode $imgType $keymapapp shutdown=$shutdown loglevel=4 consoleblank=0 " . $GLOBALS['FOGCore']->getSetting( "FOG_KERNEL_ARGS" ) . " " . $member->getKernelArgs() . " " . $otherargs;
									  
						$tmp = createPXEFile( $output );

						if( $tmp !== null )
						{
							// make sure there is no active task for this mac address
							$num = $GLOBALS['FOGCore']->getClass('TaskManager')->getCountOfActiveTasksWithMAC($member->getMACColon());
					
							if ( $num == 0 )
							{
								// attempt to ftp file
											
								$ftp = ftp_connect($GLOBALS['FOGCore']->getSetting( "FOG_TFTP_HOST" )); 
								$ftp_loginres = ftp_login($ftp, $GLOBALS['FOGCore']->getSetting( "FOG_TFTP_FTP_USERNAME" ), $GLOBALS['FOGCore']->getSetting( "FOG_TFTP_FTP_PASSWORD" )); 			
								if ($ftp && $ftp_loginres ) 
								{
									if ( ftp_put( $ftp, $GLOBALS['FOGCore']->getSetting( "FOG_TFTP_PXE_CONFIG_DIR" ) . $mac, $tmp, FTP_ASCII ) )
									{						
										$sql = "insert into 
												tasks(taskName, taskCreateTime, taskCheckIn, taskHostID, taskStateID, taskCreateBy, taskForce, taskTypeID ) 
												values('" . mysql_real_escape_string($taskName) . "', NOW(), NOW(), '" . $member->getID() . "', '0', '" . mysql_real_escape_string( $currentUser->get('name') ) . "', '0', 'C' )";
										if ( mysql_query( $sql, $conn ) )
										{
											$insertId = mysql_insert_id( $conn );
											if ( $insertId !== null && $insertId >= 0 )
											{
												if ( $deploySnapins )
												{
													// Remove any exists snapin tasks
													cancelSnapinsForHost( $conn, $member->getID() );
											
													// now do a clean snapin deploy
													deploySnapinsForHost( $conn, $member->getID() );
												}
										
												// lets try to wake the computer up!
												wakeUp( $member->getMACColon() );																			
												lg( _("Image push multicast package created for host")." " . $member->getHostName() . " [" . $member->getMACDash() . "]" );
												@ftp_close($ftp); 					
												@unlink( $tmp );								
												return $insertId;
											}
										}
										else
										{
											ftp_delete( $ftp, $GLOBALS['FOGCore']->getSetting( "FOG_TFTP_PXE_CONFIG_DIR" ) . $mac ); 									
											$reason = mysql_error();
										}
									}  
									else
										$reason = _("Unable to upload file."); 											
								}	
								else
									$reason = _("Unable to connect to tftp server."); 	
							
								@ftp_close($ftp); 					
								@unlink( $tmp ); 							
							}
							else
								$reason = _("Host is already a member of a active task.");
						}
						else
							$reason = _("Failed to open tmp file.");
					}
					else
						$reason = _("Unable to locate master storage node.");
				}
				else
					$reason = _("Invalid NFS Group ID");
			}
			else
			{
				if( $member->getImage() == null )
					$reason = _("Image assocation is null, please define an image for this host.");
				
				if ( $member->getMACDash() == null )
					$reason = _("MAC Address is null");			
			}
			
		} 
		else
		{
			$reason = _("Invalid port number".", $port");
		}
	}
	else
	{
		$reason = _("Either member or database connection was null");
	}
	return -1;
}

function createImagePackage($conn, $member, $taskName, &$reason, $debug=false, $deploySnapins=true, $shutdown="", $kernel="", $otherargs="" )
{
	global $currentUser, $db;
	
	try
	{
		// Variables
		$taskCount = $GLOBALS['FOGCore']->getClass('TaskManager')->getCountOfActiveTasksWithMAC($member->getMACColon());
	
		// Error checking
		if ($taskCount)
		{
			throw new Exception('Host is already a member of a active task');
		}
		if (!$member->isValid())
		{
			throw new Exception('Task is invalid');
		}
		if ($member->getNFSRoot() == null || $member->getNFSServer() == null || $member->getStorageNode() === null || $member->getStorageGroup() === null)
		{	
			throw new Exception(_('Unable to determine a valid storage server root path or server IP'));
		}
		
		// Variables
		$mac = $member->getMACImageReady();
		$Host = $member->getHost();
		$Image = $member->getImage();
		
		// Debug mode
		$mode = ($debug ? 'mode=debug' : '');
		
		// Image type
		$imgType = 'imgType=n';
		if ( $member->getImageType() == Image::IMAGE_TYPE_DD )
		{
			$imgType = 'imgType=dd';
		}
		else if ( $member->getImageType() == Image::IMAGE_TYPE_MULTIPARTITION_SINGLE_DISK )
		{
			$imgType = 'imgType=mps';
		}
		else if ( $member->getImageType() == Image::IMAGE_TYPE_MULTIPARTITION_MULTIDISK )
		{
			$imgType = 'imgType=mpa';
		}
		else
		{
			if ( $member->getOSID() == '99' )
			{
				throw new Exception(_('Invalid OS type, unable to determine MBR.'));
			}
			
			if ( strlen( trim($member->getOSID()) ) == 0 )
			{
				throw new Exception(_('Invalid OS type, you must specify an OS Type to image.'));
			}
			
			if ( trim($member->getOSID()) != '1' && trim($member->getOSID()) != '2' && trim($member->getOSID()) != '5' )
			{
				throw new Exception(_('Unsupported OS detected in Host!'));
			}
		}
		
		// Keymap
		$keymap = $GLOBALS['FOGCore']->getSetting("FOG_KEYMAP");
		$keymapapp = ($keymap ? 'keymap=' . $keymap : '');
		
		// Kernel
		$strKern = ($kernel != '' ? $kernel : $GLOBALS['FOGCore']->getSetting("FOG_TFTP_PXE_KERNEL"));
		
		// PXE File
		$output = "# "._("Created by FOG Imaging System")."\n\n
					  DEFAULT fog\n
					  LABEL fog\n
					  kernel " . $strKern . "\n
					  append initrd=" . $GLOBALS['FOGCore']->getSetting( "FOG_PXE_BOOT_IMAGE" ) . "  root=/dev/ram0 rw ramdisk_size=" . $GLOBALS['FOGCore']->getSetting( "FOG_KERNEL_RAMDISK_SIZE" ) . " ip=dhcp dns=" . $GLOBALS['FOGCore']->getSetting( "FOG_PXE_IMAGE_DNSADDRESS" ) . " type=down img=" . $Image->get('path') . " mac=" . $member->getMACColon() . " ftp=" . sloppyNameLookup($GLOBALS['FOGCore']->getSetting( "FOG_TFTP_HOST" )) . " storage=" . $member->getNFSServer() . ":" . $member->getNFSRoot() . " web=" . sloppyNameLookup($GLOBALS['FOGCore']->getSetting( "FOG_WEB_HOST")) . $GLOBALS['FOGCore']->getSetting( "FOG_WEB_ROOT" ) . " osid=" . $member->getOSID() . " $mode $imgType $keymapapp shutdown=$shutdown loglevel=4 consoleblank=0 " . $GLOBALS['FOGCore']->getSetting( "FOG_KERNEL_ARGS" ) . " " . $member->getKernelArgs() . " " . $otherargs;
		
		$tmp = createPXEFile( $output );
		
		// Error checking
		if ($tmp === null)
		{
			throw new Exception('Failed to create PXE File');
		}
		
		// PXE File: FTP
		$ftp = ftp_connect($GLOBALS['FOGCore']->getSetting( "FOG_TFTP_HOST" )); 
		$ftp_loginres = ftp_login($ftp, $GLOBALS['FOGCore']->getSetting( "FOG_TFTP_FTP_USERNAME" ), $GLOBALS['FOGCore']->getSetting( "FOG_TFTP_FTP_PASSWORD" ));
		
		// PXE File: FTP connect failed
		if (!$ftp || !$ftp_loginres)
		{
			throw new Exception(_("Unable to connect to tftp server"));
		}
		
		// PXE File: FTP upload failed
		if (!ftp_put( $ftp, $GLOBALS['FOGCore']->getSetting( "FOG_TFTP_PXE_CONFIG_DIR" ) . $mac, $tmp, FTP_ASCII ))
		{
			throw new Exception(_("Unable to upload file"));
		}
		
		// Set Task data -> Save
		$member	->set('name',		$taskName)
			->set('createdBy',	($currentUser ? $currentUser->get('name') : 'unknown'))
			->set('hostID',		$member->getHost()->get('id'))
			->set('isForced',	'0')
			->set('state',		'0')
			->set('type',		'D');

		// Save to database
		if ($member->save())
		{
			if ($deploySnapins)
			{
				// Remove any exists snapin tasks
				cancelSnapinsForHost($conn, $member->getID());
				
				// now do a clean snapin deploy
				deploySnapinsForHost($conn, $member->getID());
			}
			
			// Wake Host
			wakeUp($member->getMACColon());	
			
			// Log History event
			$GLOBALS['FOGCore']->logHistory(sprintf('Image Deploy: Task ID: %s, Task Name: %s, Host ID: %s, Host Name: %s, Host MAC: %s, Image ID: %s, Image Name: %s', $member->get('id'), $member->get('name'), $Host->get('id'), $Host->get('name'), $Host->get('mac'), $Image->get('id'), $Image->get('id')));
		}
		else
		{
			// Delete PXE file
			ftp_delete( $ftp, $GLOBALS['FOGCore']->getSetting( "FOG_TFTP_PXE_CONFIG_DIR" ) . $mac );
		
			// Database save failed
			throw new Exception('Database update failed');
		}
		
		@ftp_close($ftp);
		@unlink($tmp);
		
		return true;
	}
	catch (Exception $e)
	{
		FOGCore::debug('Could not %s(): %s', array(__FUNCTION__, $e->getMessage()));
	}
	
	return false;
}

/*
 *
 *    Below are functions that are used in the service scripts
 *
 *
 */

function cleanIncompleteTasks( $conn, $hostid )
{
	if ( $conn != null && $hostid != null )
	{
		$sql = "update tasks set taskStateID = '0' where taskHostID = '" . mysql_real_escape_string($hostid) . "' and taskStateID = '1'";	
		mysql_query( $sql, $conn ) or criticalError( mysql_error(), _("FOG :: Database error!") );
	}
}

function queuedTaskExists( $conn, $mac )
{
	if ( $conn != null && $mac != null )
	{
		if ( getTaskIDByMac( $conn, $mac ) != null ) return true;	
	}
	return false;
}

function getTaskIDByMac( $conn, $mac, $state=0 )
{
	if ( $conn != null && $mac != null )
	{
		$sql = "select 
				* 
				from hosts 
				inner join tasks on ( hosts.hostID = tasks.taskHostID ) where hostMAC = '" . mysql_real_escape_string($mac) . "' and taskStateID = '$state'";

		$res = mysql_query( $sql, $conn ) or criticalError( mysql_error(), _("FOG :: Database error!") );
		while( $ar = mysql_fetch_array( $res ) )
		{
			return $ar["taskID"];
		}		
	}
	return null;
}

/* Depracted as of version 0.24 */
function getNumberInQueue( $conn, $state )
{
	if ( $conn != null && $state != null )
	{
		$sql = "select count(*) as cnt from tasks where taskStateID = '" . mysql_real_escape_string($state) . "' and taskTypeID in ('" . Task::TYPE_UPLOAD . "', '" . Task::TYPE_DOWNLOAD . "')";
		$res = mysql_query( $sql, $conn ) or criticalError( mysql_error(), _("FOG :: Database error!") );
		
		if ( $ar = mysql_fetch_array( $res ) )
		{
			return $ar["cnt"];
		}
	}
	return null;
}

/* 
 * Replaces function getNumber InQueue 
 * Handles Queue by NFS Server
 *
 */
function getNumberInQueueByNFSServer( $conn, $state, $nodeid )
{
	if ( $conn != null && $state != null && $nodeid != null )
	{
		$sql = "SELECT 
				COUNT(*) as cnt
			FROM
				tasks
			WHERE 
				taskStateID = '" . mysql_real_escape_string( $state ) . "' and
				taskTypeID in ( 'U', 'D' ) and
				taskNFSMemberID = '" . mysql_real_escape_string( $nodeid ) . "'";
		$res = mysql_query( $sql, $conn ) or criticalError( mysql_error(), _("FOG :: Database error!") );
		
		if ( $ar = mysql_fetch_array( $res ) )
		{
			return $ar["cnt"];
		}				
	}
	return null;
}


// global queue stuff for
// the dashboard
function getGlobalQueueSize( $conn )
{
	if ( $conn != null )
	{
		$sql = "SELECT 
				SUM(ngmMaxClients) as cnt 
			FROM 
				nfsGroupMembers
			WHERE 
				ngmIsEnabled = '1'";
		$res = mysql_query( $sql, $conn ) or die( mysql_error() );
		while( $ar = mysql_fetch_array( $res ) )
		{
			return $ar["cnt"];
		}
	}
	return null;
}


/* 
 * Replaces function getNumber InQueue 
 * Handles Queue by NFS Server Group
 *
 */
function getNumberInQueueByNFSGroup( $conn, $state, $groupid )
{
	if ( $conn != null && $state != null && $groupid != null )
	{
		$sql = "SELECT 
				COUNT(*) as cnt
			FROM
				tasks
			WHERE 
				taskStateID = '" . mysql_real_escape_string( $state ) . "' and
				taskTypeID in ( 'U', 'D' ) and
				taskNFSGroupID = '" . mysql_real_escape_string( $groupid ) . "'";
		$res = mysql_query( $sql, $conn ) or criticalError( mysql_error(), _("FOG :: Database error!") );
		
		if ( $ar = mysql_fetch_array( $res ) )
		{
			return $ar["cnt"];
		}				
	}
	return null;
}

function getNFSGroupIDByTaskID( $conn, $taskID )
{
	if ( $conn != null && $taskID != null )
	{
		$sql = "SELECT
				taskNFSGroupID 
			FROM 
				tasks
			WHERE 
				taskID = '" . mysql_real_escape_string( $taskID ) . "'";

		$res = mysql_query( $sql, $conn ) or criticalError( mysql_error(), _("FOG :: Database error!") );
		
		if ( $ar = mysql_fetch_array( $res ) )
		{
			return $ar["taskNFSGroupID"];
		}				
	}
	return null;
}

function getAllBlamedNodes( $conn, $taskid, $hostid )
{
	$arRet = array();

	if ( $conn != null && is_numeric( $taskid ) && is_numeric( $hostid ) )
	{
		$sql = "SELECT 
				nfNodeID 
			FROM 
				nfsFailures 
			WHERE 
				nfTaskID = '$taskid' and
				nfHostID = '$hostid' and 
				TIMESTAMP(nfDateTime) BETWEEN TIMESTAMP(DATE_ADD(NOW(), INTERVAL -5 MINUTE)) and TIMESTAMP(NOW())";
		
		$res = mysql_query( $sql, $conn ) or die( mysql_error() );
		while( $ar = mysql_fetch_array( $res ) )
		{
			$node = $ar["nfNodeID"];
		
			if ( ! in_array  ( $node, $arRet ) )
				$arRet[] = $node;
		}
	}
	return $arRet;
}

// Returns the node id for all nodes in a given group (id)
function getAllNodeInNFSGroup( $conn, $groupid )
{
	$arRet = array();
	if ( $conn != null && $groupid != null )
	{
		$sql = "SELECT 
				ngmID 
			FROM 
				nfsGroupMembers
			WHERE
				ngmGroupID = '" . $groupid . "' and
				ngmIsEnabled = '1'";
		$res = mysql_query( $sql, $conn ) or criticalError( mysql_error(), _("FOG :: Database error!") );
		while ( $ar = mysql_fetch_array( $res ) )
		{
			$arRet[] = $ar["ngmID"];
		}				
	}
	return $arRet;	
}

function getNodeQueueSize( $conn, $nodeid )
{
	if ( $conn != null && $nodeid != null )
	{
		$sql = "SELECT 
				ngmMaxClients 
			FROM 
				nfsGroupMembers
			WHERE
				ngmID = '" . $nodeid . "' and
				ngmIsEnabled = '1'";
		$res = mysql_query( $sql, $conn ) or criticalError( mysql_error(), _("FOG :: Database error!") );
		while ( $ar = mysql_fetch_array( $res ) )
		{
			return $ar["ngmMaxClients"];
		}				
	}
	return null;
}

function getNFSNodeNameById( $conn, $nodeid )
{
	if ( $conn != null && $nodeid != null )
	{
		$sql = "SELECT 
				ngmMemberName 
			FROM 
				nfsGroupMembers
			WHERE
				ngmID = '" . $nodeid . "' and
				ngmIsEnabled = '1'";
		$res = mysql_query( $sql, $conn ) or criticalError( mysql_error(), _("FOG :: Database error!") );
		while ( $ar = mysql_fetch_array( $res ) )
		{
			return $ar["ngmMemberName"];
		}				
	}
	return null;
}

function checkIn( $conn, $jobid )
{
	if ( $conn != null && $jobid != null )
	{
		$sql = "update tasks set taskCheckIn = NOW() where taskID = '" . mysql_real_escape_string( $jobid ) . "'";
		if ( mysql_query( $sql, $conn ) )
			return true;
	}
	return false;
}

function isForced( $conn, $jobid )
{
	if ( $conn != null && $jobid != null )
	{
		$sql = "select count(*) as c from tasks where taskID = '" . mysql_real_escape_string( $jobid ) . "' and taskForce = 1";
		$res = mysql_query( $sql, $conn ) or criticalError( mysql_error(), _("FOG :: Database error!") );
		if ( $ar = mysql_fetch_array( $res ) )
		{
			if ( $ar["c"] == "1" ) return true;
		} 
	}
	return false;
}

function getNewStorageStringForImage( $conn, $nodeid )
{
	if ( $conn != null && $nodeid != null )
	{
		$sql = "SELECT 
				ngmHostName, 
				ngmRootPath, 
				ngmMemberName
			FROM
				nfsGroupMembers 
			WHERE
				ngmID = '" . mysql_real_escape_string( $nodeid ) . "' and 
				ngmIsEnabled = '1'";
		$res = mysql_query( $sql, $conn ) or criticalError( mysql_error(), _("FOG :: Database error!") );
		if ( $ar = mysql_fetch_array( $res ) )
		{
			return  sloppyNameLookup( $ar["ngmHostName"] ) . "@" . sloppyNameLookup($ar["ngmHostName"]) . ":" . $ar["ngmRootPath"] . "@" . base64_encode( $ar["ngmMemberName"] );
		} 
	}
	return false;	
}

function doesStorageGroupExist( $conn, $name, $id=-1 )
{
	if ( $conn != null && $name != null && is_numeric( $id ) )
	{
		$sql = "SELECT COUNT(*) as cnt FROM nfsGroups WHERE ngName = '" . mysql_real_escape_string( $name ) . "' and ngID <> $id";
		$res = mysql_query( $sql, $conn ) or die( mysql_error() );
		while( $ar = mysql_fetch_array( $res ) )
		{
			return ( $ar["cnt"] > 0 );
		}
	}
	// play it safe
	return true;
}

function doesStorageNodeExist( $conn, $name, $id=-1 )
{
	if ( $conn != null && $name != null && is_numeric( $id ) )
	{
		$sql = "SELECT COUNT(*) as cnt FROM nfsGroupMembers WHERE ngmMemberName = '" . mysql_real_escape_string( $name ) . "' and ngmID <> $id";
		$res = mysql_query( $sql, $conn ) or die( mysql_error() );
		while( $ar = mysql_fetch_array( $res ) )
		{
			return ( $ar["cnt"] > 0 );
		}
	}
	// play it safe
	return true;
}

function getStorageRootByGroupID( $conn, $groupid )
{
	if ( $conn != null && $groupid != null && is_numeric( $groupid ) )
	{
		$sql = "SELECT  
				ngmRootPath
			FROM 
				(SELECT * FROM nfsGroups WHERE ngID = '$groupid') nfsGroups
				INNER JOIN nfsGroupMembers on ( nfsGroups.ngID = nfsGroupMembers.ngmGroupID )
			WHERE 
				ngmIsMasterNode = '1' and
				ngmIsEnabled = '1'";

		$res = mysql_query( $sql, $conn ) or die( mysql_error() );
		if ( mysql_num_rows( $res ) == 1 )
		{
			while( $ar = mysql_fetch_array( $res ) )
				return $ar["ngmRootPath"];
		}
	}
	return null;
}

function doImage( $conn, $jobid, $blUpdateNFS=false, $nodeid=null )
{
	if ( $conn != null && $jobid != null )
	{
		$set = "";
		if ( $blUpdateNFS )
		{
			$set = "taskNFSMemberID = '" . mysql_real_escape_string( $nodeid ) . "', ";
		}
		$sql = "update tasks set $set taskStateID = '1' where taskID = '" . mysql_real_escape_string($jobid) . "'";
		if ( mysql_query( $sql, $conn ) )
			return true;
		else
		{
			die( mysql_error() );
		}
	}
	return false;
}

function getTotalClusteredQueueSize( $conn, $groupid )
{
	if ( $conn != null && $groupid != null )
	{
		$sql = "SELECT
				SUM(ngmMaxClients) as max
			FROM
				nfsGroupMembers
			WHERE
				ngmGroupID = '" . mysql_real_escape_string( $groupid ) . "' and 
				ngmIsEnabled = '1'";
		
		$res = mysql_query( $sql, $conn ) or die( mysql_error() );

		while( $ar = mysql_fetch_array( $res ) )
		{
			return $ar["max"];
		}
	}
	return null;
}


function getNumberInFrontOfMe( $conn, $jobid, $groupid=-1 )
{
	if ( $conn != null && $jobid != null )
	{
		$where = "";
		if ( $groupid != -1 )
		{ 
			$where = " taskNFSGroupID = '" . mysql_real_escape_string( $groupid ) . "' and ";
		}
		$sql = "select count(*) as c from tasks where $where taskStateID = '0' and taskID < " . mysql_real_escape_string($jobid) . " and (UNIX_TIMESTAMP() - UNIX_TIMESTAMP(taskCheckIn)) < " . $GLOBALS['FOGCore']->getSetting( "FOG_CHECKIN_TIMEOUT" );
		$res = mysql_query( $sql, $conn ) or criticalError( mysql_error(), _("FOG :: Database error!") );
		if ( $ar = mysql_fetch_array( $res ) )
			return $ar["c"];
	}
	return null;
}

function getHostID( $conn, $mac )
{
	if ( $conn != null && $mac != null )
	{
		$sql = "select * from hosts where hostMAC = '" . mysql_real_escape_string($mac) . "'";
		$res = mysql_query( $sql, $conn ) or criticalError( mysql_error(), _("FOG :: Database error!") );
		while( $ar = mysql_fetch_array( $res ) )
		{
			return $ar["hostID"];
		}		
	}
	return null;
}

function checkOut( $conn, $jobid )
{
	if ( $conn != null && $jobid != null )
	{
		$sql = "update tasks set taskStateID = '2' where taskID = '" . mysql_real_escape_string($jobid). "'";
		if ( mysql_query( $sql, $conn ) )
			return true;
	}
	return false;
}

// Blackout - 2:40 PM 25/05/2011
function SystemUptime()
{
	$data = trim(shell_exec('uptime'));
	
	$load = end(explode(' load average: ', $data));
	
	$uptime = explode(',', end(explode(' up ', $data)));
	$uptime = (count($uptime) > 1 ? $uptime[0] . ', ' . $uptime[1] : 'uptime not found');
	
	return array('uptime' => $uptime, 'load' => $load);
}

