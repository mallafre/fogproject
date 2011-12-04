<?php

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

if ( ! isValidMACAddress( $mac ) )
{
	die( _("Invalid MAC address format!") );
}

if ( $mac != null  )
{
	
	$hostid = getHostID( $conn, $mac );	
	$jobid = getTaskIDByMac( $conn, $mac);

	$ftp = ftp_connect( $GLOBALS['FOGCore']->getSetting( "FOG_TFTP_HOST") ); 
	$ftp_loginres = ftp_login($ftp, $GLOBALS['FOGCore']->getSetting( "FOG_TFTP_FTP_USERNAME"), $GLOBALS['FOGCore']->getSetting( "FOG_TFTP_FTP_PASSWORD")); 				
	if ((!$ftp) || (!$ftp_loginres )) 
	{
  		echo _("FTP connection has failed!");
 		exit;
 	}			

 	$mac = str_replace( ":", "-", $mac );
	if ( ftp_delete ( $ftp, $GLOBALS['FOGCore']->getSetting( "FOG_TFTP_PXE_CONFIG_DIR") . "01-". $mac ) )
	{
		if ( $jobid !== null )
		{			
			if ( checkOut( $conn, $jobid ) )
			{			
				echo "##";
			}
			else
			{
				echo _("Error: Checkout Failed.");
			}	
		}
		else
		{
			echo _("Unable to locate job in database, please ensure that mac address is correct.");
		}							
	}
	else
	{
		echo _("Error: Unable to remove TFTP file");
	}
	ftp_close($ftp); 
}
else
	echo _("Invalid MAC or FTP Address");
?>
