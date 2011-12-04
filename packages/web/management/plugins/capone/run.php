<?php
/*
 *  FOG is a computer imaging solution.
 *  Copyright (C) 2008  Chuck Syperski & Jian Zhang
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
// Blackout - disable error reporting on capone addon
//error_reporting( E_ALL );
$plugin = unserialize( $_SESSION["fogactiveplugin"] );
if  ( $plugin == null )
	die( "Unable to determine plugin details" );


?>
<h2>Plugin: <?php print $plugin->getName(); ?></h2>
<p>Plugin Description: <?php print $plugin->getDesc(); ?></p>
<?php

if ( $plugin->isInstalled() )
{
	if ( $_POST["basics"] == "1" )
	{
		mysql_query( "UPDATE globalSettings set settingValue = '" . mysql_real_escape_string( $_POST["dmifield"] ) . "' WHERE settingKey = 'FOG_PLUGIN_CAPONE_DMI'", $conn  ) or die( mysql_error() );
	}
	
	if ( $_POST["addass"] == "1" )
	{
		if ( is_numeric( $_POST["image"] ) && is_numeric( $_POST["os"] )&& $_POST["key"] != null && $_POST["os"] !== null && $_POST["image"] !== null )
		{
			$img = mysql_real_escape_string( $_POST["image"] );
			$os = mysql_real_escape_string( $_POST["os"] );
			$key = mysql_real_escape_string( $_POST["key"] );
			if ( $img >= 0 && $os >= 0 )
			{
				mysql_query( "INSERT INTO capone( cImageID, cOSID, cKEY ) values( '$img', '$os', '$key' )", $conn ) or die( mysql_error() );
			}
		}	
	}

	if ( $_GET["kill"] !== null )
	{
		$k = mysql_real_escape_string( $_GET["kill"] );
		mysql_query( "DELETE FROM capone WHERE cID = '" . $k . "'", $conn ) or die( mysql_error() );
	}

	echo ( "<p class=\"titleBottomLeft\">Settings</p>" );
	echo ( "<form action=\"?node=" . $_GET["node"] . "&run=" . $_GET["run"] . "\" method=\"post\">" );
		echo ( "<input type=\"hidden\" name=\"basics\" value=\"1\" />" );
		echo ( "<table width=\"300\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\">" );
		echo ( "<tr><td>DMI Field: </td><td>" );
			echo ( "<select name=\"dmifield\" size=\"1\">" );
				$dmiFields = array(
							"bios-vendor", 
							"bios-version", 
							"bios-release-date", 
							"system-manufacturer", 
							"system-product-name", 
							"system-version", 
							"system-serial-number", 
							"system-uuid", 
							"baseboard-manufacturer", 
							"baseboard-product-name", 
							"baseboard-version", 
							"baseboard-serial-number", 
							"baseboard-asset-tag", 
							"chassis-manufacturer",
							"chassis-type",
							"chassis-version",
							"chassis-serial-number",
							"chassis-asset-tag",
							"processor-family", 
							"processor-manufacturer",
							"processor-version",
							"processor-frequency"
						);
				echo ( "<option value=\"\" label=\"Select One\" >Select One</option>");
				$cur = $GLOBALS['FOGCore']->getSetting( "FOG_PLUGIN_CAPONE_DMI" );
				for( $i = 0; $i < count( $dmiFields ); $i++ )
					echo ( "<option value=\"" . $dmiFields[$i] . "\" label=\"" . $dmiFields[$i] . "\" " . (( $cur == $dmiFields[$i] ) ? "selected=\"selected\"" : "" ) . ">" . $dmiFields[$i] . "</option>");
					
			echo ( "</select>" );
		echo ( "<tr><td colspan=\"2\"><center><input style=\"margin-top: 7px;\" type=\"submit\" value=\"Update Settings\" /></center></td></tr>" );			
		echo ( "</table>" );	
	echo ( "</form>" );
	
	echo ( "<p class=\"titleBottomLeft\">Add Image to DMI Associations</p>" );
	echo ( "<form action=\"?node=" . $_GET["node"] . "&run=" . $_GET["run"] . "\" method=\"post\">" );
		echo ( "<input type=\"hidden\" name=\"addass\" value=\"1\" />" );
		echo ( "<table width=\"300\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\">" );
		echo ( "<tr><td>Image Definition: </td><td>" . $FOGCore->getClass('ImageManager')->buildSelectBox() . "</td></tr>" );
		echo ( "<tr><td>Operating System: </td><td>" . $FOGCore->getClass('OSManager')->buildSelectBox() . "</td></tr>" );	
		echo ( "<tr><td>DMI Result: </td><td><input type=\"text\" name=\"key\" /></td></tr>" );	
		echo ( "<tr><td colspan=\"2\"><center><input type=\"submit\" style=\"margin-top: 7px;\" value=\"Add Association\" /></center></td></tr>" );			
		echo ( "</table>" );	
	echo ( "</form>" );
	
	echo ( "<p class=\"titleBottomLeft\">Current Image to DMI Associations</p>" );
	echo ( "<table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\">" );
	$sql = "SELECT 
			* 
		FROM 
			capone
			INNER JOIN images on (cImageID = imageID )
			INNER JOIN supportedOS on ( cOSID = osValue )
		ORDER BY 
			imageName";
	$res = mysql_query( $sql, $conn ) or die( mysql_error() );
	echo ( "<tr bgcolor=\"#BDBDBD\"><td><b>&nbsp;Image Name</b></td><td><b>Operating System</b></td><td><b>Key</b></td><td><b>Remove</b></td></tr>" );			
	if ( mysql_num_rows( $res ) > 0 )
	{
		while( $ar = mysql_fetch_array( $res ) )
		{
			echo ( "<tr><td>&nbsp;&nbsp;" . $ar["imageName"] . "</td><td>" . $ar["osName"] . "</td><td>" . $ar["cKey"] . "</td><td><a href=\"?node=" . $_GET["node"] . "&run=" . $_GET["run"] . "&kill=" . $ar["cID"] . "\"><span class=\"icon icon-kill\" title=\"Kill task\"></span></a></td></tr>" );			
		}
	}
	else
	{
		echo ( "<tr><td colspan=\"4\">No results found.</td></tr>" );			
	}
	echo ( "</table>" );	
	
	$sql = "SELECT
			*
		FROM 
			nfsGroups
			INNER JOIN ( SELECT * FROM nfsGroupMembers WHERE ngmIsEnabled = '1' and ngmIsMasterNode = '1' ) nfsGroupMembers on ( nfsGroups.ngID = nfsGroupMembers.ngmGroupID )
		LIMIT
			1";
	
	$store = "";
	$res = mysql_query( $sql, $conn ) or die( mysql_error() );
	while( $ar = mysql_fetch_array( $res ) )
	{
		$store = $ar["ngmHostname"] .  ":" . $ar["ngmRootPath"];
	}
	
	echo ( "<p class=\"titleBottomLeft\">PXE Configuration</p>" );
	echo ( "<p class=\"code\">" );
	echo ( "LABEL capone
kernel " . $GLOBALS['FOGCore']->getSetting( "FOG_TFTP_PXE_KERNEL" ) . "
append initrd=" . $GLOBALS['FOGCore']->getSetting( "FOG_PXE_BOOT_IMAGE" ) . " root=/dev/ram0 rw ramdisk_size=" . $GLOBALS['FOGCore']->getSetting( "FOG_KERNEL_RAMDISK_SIZE" ) . " ip=dhcp dns=" . $GLOBALS['FOGCore']->getSetting( "FOG_PXE_IMAGE_DNSADDRESS" ) . " mode=capone ftp=" . $GLOBALS['FOGCore']->getSetting( "FOG_TFTP_HOST" ) . " storage=" . $store . " web=" . $GLOBALS['FOGCore']->getSetting( "FOG_WEB_HOST" ) . $GLOBALS['FOGCore']->getSetting( "FOG_WEB_ROOT" ) . " shutdown=on loglevel=4   " );
	echo ( "</p>" );		
}
else
{
	echo ( "<p class=\"titleBottomLeft\">Plugin Installation</p>" );
	if ( $_POST["install"] == "1" )
	{
		$sql = "CREATE TABLE `" . DATABASE_NAME . "`.`capone` (
			  `cID` integer  NOT NULL AUTO_INCREMENT,
			  `cImageID` integer  NOT NULL,
			  `cOSID` integer  NOT NULL,
			  `cKey` varchar(250)  NOT NULL,
			  PRIMARY KEY (`cID`),
			  INDEX `new_index`(`cImageID`),
			  INDEX `new_index1`(`cOSID`),
			  INDEX `new_index2`(`cKey`)
			)
			ENGINE = MyISAM";
		if ( mysql_query( $sql, $conn ) )
		{
			$sql = "INSERT INTO `" . DATABASE_NAME . "`.globalSettings(settingKey, settingDesc, settingValue, settingCategory)
						values('FOG_PLUGIN_CAPONE_DMI', 'This setting is used for the capone module to set the DMI field used.', '', 'Plugin: " . mysql_real_escape_string($plugin->getName()) . "')";
			mysql_query( $sql, $conn ) or die ( mysql_error() );

			$sql = "INSERT INTO `" . DATABASE_NAME . "`.globalSettings(settingKey, settingDesc, settingValue, settingCategory)
						values('FOG_PLUGIN_CAPONE_REGEX', 'This setting is used for the capone module to set the reg ex used.', '', 'Plugin: " . mysql_real_escape_string($plugin->getName()) . "')";
			mysql_query( $sql, $conn ) or die ( mysql_error() );

			$sql = "UPDATE plugins set pInstalled = '1', pVersion = '1' WHERE pName = '" . mysql_real_escape_string($plugin->getName()) . "'";
			if ( mysql_query( $sql, $conn ) )
			{
				echo ( "<p>Plugin Installed!</p>" );
				echo ( "<p><a href=\"?node=" . $_GET["node"] . "&run=" . $_GET["run"] . "\">Run Plugin</a></p>" );
			}
			else
			{
				echo ( "<p>Plugin Installation Failed!</p>" );
			}
		}	
		else
		{
			echo ( "<p>Failed to install schema!</p>" );
		}			
	}
	else
	{
		echo ( "<p>This plugin is currently not installed, would you like to install it now?</p>" );
		echo ( "<div><form method=\"post\" action=\"?node=" . $_GET["node"] . "&run=" . $_GET["run"] . "\"><input type=\"hidden\" name=\"install\" value=\"1\" /><input type=\"submit\" value=\"Install Plugin\" /></form></div>" );
	}			
}