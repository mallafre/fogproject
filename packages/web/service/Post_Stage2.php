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


//
// Blackout - 5:26 PM 5/05/2012
//
// Post_Stage2.php
// Triggered:	After image upload
// Actions:	Moves uploaded image to final location via FTP
//




require('../commons/config.php');
require(BASEPATH . '/commons/init.php');
require(BASEPATH . '/commons/init.database.php');

$conn = mysql_connect( DATABASE_HOST, DATABASE_USERNAME, DATABASE_PASSWORD);
if ( $conn )
{
	if ( ! mysql_select_db( DATABASE_NAME, $conn ) ) die( _("Unable to select database") );
}
else
{
	die( _("Unable to connect to Database") );
}

$mac = strtolower($_GET["mac"]);
//$size = $_GET["size"];
$imgid = $_GET["imgid"];
$type = $_GET["imgtype"];

if ( ! isValidMACAddress( $mac ) )
{
	die( _("Invalid MAC address format!") );
}

if ( ! is_numeric( $imgid ) )
{
	die( _("Image ID must be numeric") );
}

if ( $mac != null  )
{
	// Get master node of storage group.
	$mac = str_replace( "-", ":", $mac );
	$jobid = getTaskIDByMac( $conn, $mac );
	if ( $jobid != null )
	{
		$nfsGroupID = getNFSGroupIDByTaskID( $conn, $jobid );
		if ( $nfsGroupID )
		{	
			// get details about the storage node
			$sql = "SELECT 
					* 
				FROM 
					nfsGroupMembers
				WHERE
					ngmIsMasterNode = '1' and
					ngmIsEnabled = '1' and 
					ngmGroupID = '$nfsGroupID'";
				
			$res = mysql_query( $sql, $conn ) or die( mysql_error() );
			if ( mysql_num_rows( $res ) == 1 )
			{
				while( $ar = mysql_fetch_array( $res ) )
				{
					$nodeip = trim(mysql_real_escape_string($ar["ngmHostname"]));
					$noderoot = '/' . trim(mysql_real_escape_string($ar["ngmRootPath"]), '/');
					$nodeuser = trim(mysql_real_escape_string($ar["ngmUser"]));
					$nodepass = trim(mysql_real_escape_string($ar["ngmPass"]));
				
					$ftp = ftp_connect( $FOGCore->getSetting( "FOG_TFTP_HOST")); 
					$ftp_loginres = ftp_login($ftp, $FOGCore->getSetting( "FOG_TFTP_FTP_USERNAME"), $FOGCore->getSetting( "FOG_TFTP_FTP_PASSWORD") ); 			
					if ((!$ftp) || (!$ftp_loginres )) 
					{
				  		echo _("FTP connection to TFTP Server has failed!");
				 		exit;
				 	}			
				 	$mac = str_replace( ":", "-", $mac );
					@ftp_delete ( $ftp, $FOGCore->getSetting( "FOG_TFTP_PXE_CONFIG_DIR") . "01-". $mac );
					@ftp_close($ftp); 
	
					$ftp = ftp_connect( $nodeip ); 
					$ftp_loginres = ftp_login($ftp, $nodeuser, $nodepass); 			
					if ((!$ftp) || (!$ftp_loginres )) 
					{
				  		echo _("FTP connection to Storage Server has failed!");
				 		exit;
				 	}	
					$mac = str_replace( "-", ":", $mac );
					
					$uploaddir = $noderoot . '/' . 'dev';
					
					$mac = str_replace( ":", "", $mac );
					
					if ( $type == "mpa" || $type == "mps" )
						$src = $uploaddir . '/' . $mac;
					else
						$src = $uploaddir . '/' . $mac . '.000';
		
					$srcdd = $uploaddir . '/' . $mac;
					$dest = $noderoot . '/' . $_GET["to"];
	
					// if the destination is a directory, we must delete the old
					// data first or rename will fail!
					if ( $type == "mpa" || $type == "mps" )
					{		
						$arFiles = ftp_nlist($ftp, $dest);
						for( $i = 0; $i < count( $arFiles ); $i++ )
						{
							if ( $arFiles[$i] != "." && $arFiles[$i] != ".." )
								@ftp_delete( $ftp, $arFiles[$i] );
						}
		
						@ftp_rmdir( $ftp, $dest );
					}
	
					if (@ftp_rename ( $ftp, $src, $dest ) || @ftp_rename ( $ftp, $srcdd, $dest ))
					{
						if ( checkOut( $conn, $jobid ) )
						{
							echo "##";
						}
						else
							echo ( _("Error: Checkout failed!") );
					}
					else
					{
						$errorDetails = error_get_last();
						printf('%s %s %s %s%s', _('Unable to move'), $src, _('to'), $dest, "\n");
						printf('%s: %s, %s: %s%s', _('FTP Host'), $FOGCore->getSetting("FOG_TFTP_HOST"), _('Error'), preg_replace(array('#\:(.*)\.$#', '#\[.*\]#', '#[[:space:]][[:space:]]#', '#\(\)#'), array('\\1', '', ' ', '():'), $errorDetails['message']), "\n\n");
					}
	
					@ftp_close($ftp); 
				}
			}
			else
				echo _("Invalid number of device nodes returned!");
		}
		else
			echo _("Unable to find a valid storage node, based on the job id.");
	}
	else
		echo _("Unable to find a valid task ID based on the clients mac address of").": " . $mac;
}
else
	echo _("Invalid MAC or FTP Address");
?>
