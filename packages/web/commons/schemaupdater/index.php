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

// Require FOG Base
require_once('../config.php');
require_once(BASEPATH . '/commons/init.php');
require_once(BASEPATH . '/commons/init.database.php');

// If you have an existing database change this value to false
// It will still require that none of the tables exist in the schema
define( "FOG_CREATE_DATABASE", true );

$installPath = array();
if ( FOG_CREATE_DATABASE )
	$installPath[0] = array( 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12 );
else
	$installPath[0] = array( 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12 );
$installPath[1] = array( 13, 14 );
$installPath[2] = array( 15, 16, 17, 18, 19, 20 );
$installPath[3] = array( 21, 22, 23 );
$installPath[4] = array( 24, 25, 26, 27, 28 );
$installPath[5] = array( 29, 30 );
$installPath[6] = array( 31, 32, 33, 34, 35, 36, 37 );
$installPath[7] = array( 38, 39, 40, 41, 42 );
$installPath[8] = array( 43, 44, 45, 46, 47, 48, 49, 50, 51, 52, 53, 54, 55, 56, 57, 58, 59, 60, 61, 62, 63, 64, 65, 66, 67, 68, 69, 70, 71, 72, 73, 74, 75, 76, 77, 78, 79, 80, 81, 82, 83, 84, 85, 86, 87, 88, 89, 90, 91, 92, 93, 94, 95, 96, 97, 98, 99, 100, 101, 102, 103, 104, 105, 106 );
$installPath[9] = array( 107, 108, 109, 110, 111, 112 );
$installPath[10] = array( 113, 114, 115 );
$installPath[11] = array( 116, 117, 118, 119, 120, 121 );
$installPath[12] = array( 122, 123, 124, 125, 126 );
$installPath[13] = array( 127, 128, 129 );
$installPath[14] = array( 130, 131, 132, 133, 134 );
$installPath[15] = array( 135, 136, 137, 138, 139, 140, 141, 142, 143, 144, 145, 146, 147, 148, 149, 150, 151, 152, 153, 154 );
$installPath[16] = array( 155, 156, 157, 158 );
$installPath[17] = array( 159, 160 );
$installPath[18] = array( 161, 162, 163, 164, 165, 166 );
$installPath[19] = array( 167, 168, 169, 170, 171 );
$installPath[20] = array( 172, 173, 174, 175, 176, 177, 178, 179  );
$installPath[21] = array( 180, 181, 182 );
$installPath[22] = array( 183, 184, 185 );
$installPath[23] = array( 186, 187, 188, 189 );
$installPath[24] = array( 190, 191, 192, 193, 194 );
$installPath[25] = array( 195, 196, 197, 198 );
$installPath[26] = array( 199, 200, 201, 202, 203, 204, 205, 206, 207, 208, 209, 210, 211, 212, 213 );
$installPath[27] = array( 214, 215, 216 );

$dbschema[0] = "CREATE DATABASE " . MYSQL_DATABASE ;

$dbschema[1] = "CREATE TABLE  `" . MYSQL_DATABASE . "`.`groupMembers` (
  `gmID` int(11) NOT NULL auto_increment,
  `gmHostID` int(11) NOT NULL,
  `gmGroupID` int(11) NOT NULL,
  PRIMARY KEY  (`gmID`),
  KEY `new_index` (`gmHostID`),
  KEY `new_index1` (`gmGroupID`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC";

$dbschema[2] = "CREATE TABLE  `" . MYSQL_DATABASE . "`.`groups` (
  `groupID` int(11) NOT NULL auto_increment,
  `groupName` varchar(50) NOT NULL,
  `groupDesc` longtext NOT NULL,
  `groupDateTime` datetime NOT NULL,
  `groupCreateBy` varchar(50) NOT NULL,
  `groupBuilding` int(11) NOT NULL,
  PRIMARY KEY  (`groupID`),
  KEY `new_index` (`groupName`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1";

$dbschema[3] = "CREATE TABLE  `" . MYSQL_DATABASE . "`.`history` (
  `hID` int(11) NOT NULL auto_increment,
  `hText` longtext NOT NULL,
  `hUser` varchar(200) NOT NULL,
  `hTime` datetime NOT NULL,
  `hIP` varchar(50) NOT NULL,
  PRIMARY KEY  (`hID`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1";

$dbschema[4] = "CREATE TABLE  `" . MYSQL_DATABASE . "`.`hosts` (
  `hostID` int(11) NOT NULL auto_increment,
  `hostName` varchar(16) NOT NULL,
  `hostDesc` longtext NOT NULL,
  `hostIP` varchar(25) NOT NULL,
  `hostImage` int(11) NOT NULL,
  `hostBuilding` int(11) NOT NULL,
  `hostCreateDate` datetime NOT NULL,
  `hostCreateBy` varchar(50) NOT NULL,
  `hostMAC` varchar(20) NOT NULL,
  `hostOS` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`hostID`),
  KEY `new_index` (`hostName`),
  KEY `new_index1` (`hostIP`),
  KEY `new_index2` (`hostMAC`),
  KEY `new_index3` (`hostOS`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1";

$dbschema[5] = "CREATE TABLE  `" . MYSQL_DATABASE . "`.`images` (
  `imageID` int(11) NOT NULL auto_increment,
  `imageName` varchar(40) NOT NULL,
  `imageDesc` longtext NOT NULL,
  `imagePath` longtext NOT NULL,
  `imageDateTime` datetime NOT NULL,
  `imageCreateBy` varchar(50) NOT NULL,
  `imageBuilding` int(11) NOT NULL,
  `imageSize` varchar(200) NOT NULL,
  PRIMARY KEY  (`imageID`),
  KEY `new_index` (`imageName`),
  KEY `new_index1` (`imageBuilding`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1";

$dbschema[6] = "CREATE TABLE  `" . MYSQL_DATABASE . "`.`schemaVersion` (
  `vID` int(11) NOT NULL auto_increment,
  `vValue` int(11) NOT NULL,
  PRIMARY KEY  (`vID`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC";

$dbschema[7] = "CREATE TABLE  `" . MYSQL_DATABASE . "`.`supportedOS` (
  `osID` int(10) unsigned NOT NULL auto_increment,
  `osName` varchar(150) NOT NULL,
  `osValue` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`osID`),
  KEY `new_index` (`osValue`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1";

$dbschema[8] = "CREATE TABLE  `" . MYSQL_DATABASE . "`.`tasks` (
  `taskID` int(11) NOT NULL auto_increment,
  `taskName` varchar(250) NOT NULL,
  `taskCreateTime` datetime NOT NULL,
  `taskCheckIn` datetime NOT NULL,
  `taskHostID` int(11) NOT NULL,
  `taskState` int(11) NOT NULL,
  `taskCreateBy` varchar(200) NOT NULL,
  `taskForce` varchar(1) NOT NULL,
  `taskScheduledStartTime` datetime NOT NULL,
  `taskType` varchar(1) NOT NULL,
  `taskPCT` int(10) unsigned zerofill NOT NULL,
  PRIMARY KEY  (`taskID`),
  KEY `new_index` (`taskHostID`),
  KEY `new_index1` (`taskCheckIn`),
  KEY `new_index2` (`taskState`),
  KEY `new_index3` (`taskForce`),
  KEY `new_index4` (`taskType`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1";

$dbschema[9] = "CREATE TABLE  `" . MYSQL_DATABASE . "`.`users` (
  `uId` int(11) NOT NULL auto_increment,
  `uName` varchar(40) NOT NULL,
  `uPass` varchar(50) NOT NULL,
  `uCreateDate` datetime NOT NULL,
  `uCreateBy` varchar(40) NOT NULL,
  PRIMARY KEY  (`uId`),
  KEY `new_index` (`uName`),
  KEY `new_index1` (`uPass`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1";

$dbschema[10] = "INSERT INTO `" . MYSQL_DATABASE . "`.`users` VALUES  ('','fog', MD5('password'),'0000-00-00 00:00:00','')";

$dbschema[11] = "INSERT INTO `" . MYSQL_DATABASE . "`.`supportedOS` VALUES  ('','"._("Windows XP")."', '1')";

$dbschema[12] = "INSERT INTO `" . MYSQL_DATABASE . "`.`schemaVersion` VALUES  ('','1')";

// Schema version 2

$dbschema[13] = "INSERT INTO `" . MYSQL_DATABASE . "`.`supportedOS` VALUES  ('','"._("Windows Vista")."', '2')";

$dbschema[14] = "UPDATE `" . MYSQL_DATABASE . "`.`schemaVersion` set vValue = '2'";

// Schema Version 3

$dbschema[15] = "ALTER TABLE `" . MYSQL_DATABASE . "`.`hosts`
		 ADD COLUMN `hostUseAD` char  NOT NULL AFTER `hostOS`,
		 ADD COLUMN `hostADDomain` VARCHAR(250)  NOT NULL AFTER `hostUseAD`,
		 ADD COLUMN `hostADOU` longtext  NOT NULL AFTER `hostADDomain`,
		 ADD COLUMN `hostADUser` VARCHAR(250)  NOT NULL AFTER `hostADOU`,
		 ADD COLUMN `hostADPass` VARCHAR(250)  NOT NULL AFTER `hostADUser`,
		 ADD COLUMN `hostAnon1` VARCHAR(250)  NOT NULL AFTER `hostADPass`,
		 ADD COLUMN `hostAnon2` VARCHAR(250)  NOT NULL AFTER `hostAnon1`,
		 ADD COLUMN `hostAnon3` VARCHAR(250)  NOT NULL AFTER `hostAnon2`,
		 ADD COLUMN `hostAnon4` VARCHAR(250)  NOT NULL AFTER `hostAnon3`,
		 ADD INDEX `new_index4`(`hostUseAD`)";

$dbschema[16] = "CREATE TABLE  `" . MYSQL_DATABASE . "`.`snapinAssoc` (
		  `saID` int(11) NOT NULL auto_increment,
		  `saHostID` int(11) NOT NULL,
		  `saSnapinID` int(11) NOT NULL,
		  PRIMARY KEY  (`saID`),
		  KEY `new_index` (`saHostID`),
		  KEY `new_index1` (`saSnapinID`)
		) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1";

$dbschema[17] = "CREATE TABLE  `" . MYSQL_DATABASE . "`.`snapinJobs` (
		  `sjID` int(11) NOT NULL auto_increment,
		  `sjHostID` int(11) NOT NULL,
		  `sjCreateTime` datetime NOT NULL,
		  PRIMARY KEY  (`sjID`),
		  KEY `new_index` (`sjHostID`)
		) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1";

$dbschema[18] = "CREATE TABLE  `" . MYSQL_DATABASE . "`.`snapinTasks` (
		  `stID` int(11) NOT NULL auto_increment,
		  `stJobID` int(11) NOT NULL,
		  `stState` int(11) NOT NULL,
		  `stCheckinDate` datetime NOT NULL,
		  `stCompleteDate` datetime NOT NULL,
		  `stSnapinID` int(11) NOT NULL,
		  PRIMARY KEY  (`stID`),
		  KEY `new_index` (`stJobID`),
		  KEY `new_index1` (`stState`),
		  KEY `new_index2` (`stSnapinID`)
		) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1";

$dbschema[19] = "CREATE TABLE  `" . MYSQL_DATABASE . "`.`snapins` (
		  `sID` int(11) NOT NULL auto_increment,
		  `sName` varchar(200) NOT NULL,
		  `sDesc` longtext NOT NULL,
		  `sFilePath` longtext NOT NULL,
		  `sArgs` longtext NOT NULL,
		  `sCreateDate` datetime NOT NULL,
		  `sCreator` varchar(200) NOT NULL,
		  `sReboot` varchar(1) NOT NULL,
		  `sAnon1` varchar(45) NOT NULL,
		  `sAnon2` varchar(45) NOT NULL,
		  `sAnon3` varchar(45) NOT NULL,
		  PRIMARY KEY  (`sID`),
		  KEY `new_index` (`sName`)
		) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1";

$dbschema[20] = "UPDATE `" . MYSQL_DATABASE . "`.`schemaVersion` set vValue = '3'";

$dbschema[21] = "CREATE TABLE  `" . MYSQL_DATABASE . "`.`multicastSessions` (
		  `msID` int(11) NOT NULL auto_increment,
		  `msName` varchar(250) NOT NULL,
		  `msBasePort` int(11) NOT NULL,
		  `msLogPath` longtext NOT NULL,
		  `msImage` longtext NOT NULL,
		  `msClients` int(11) NOT NULL,
		  `msInterface` varchar(250) NOT NULL,
		  `msStartDateTime` datetime NOT NULL,
		  `msPercent` int(11) NOT NULL,
		  `msState` int(11) NOT NULL,
		  `msCompleteDateTime` datetime NOT NULL,
		  `msAnon1` varchar(250) NOT NULL,
		  `msAnon2` varchar(250) NOT NULL,
		  `msAnon3` varchar(250) NOT NULL,
		  `msAnon4` varchar(250) NOT NULL,
		  `msAnon5` varchar(250) NOT NULL,
		  PRIMARY KEY  (`msID`)
		) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1";

$dbschema[22] = "CREATE TABLE  `" . MYSQL_DATABASE . "`.`multicastSessionsAssoc` (
		  `msaID` int(11) NOT NULL auto_increment,
		  `msID` int(11) NOT NULL,
		  `tID` int(11) NOT NULL,
		  PRIMARY KEY  (`msaID`),
		  KEY `new_index` (`msID`),
		  KEY `new_index1` (`tID`)
		) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1";

$dbschema[23] = "UPDATE `" . MYSQL_DATABASE . "`.`schemaVersion` set vValue = '4'";

$dbschema[24] = "ALTER TABLE `" . MYSQL_DATABASE . "`.`images`
		 ADD COLUMN `imageDD` VARCHAR(1)  NOT NULL AFTER `imageSize`,
		 ADD INDEX `new_index2`(`imageDD`)";

$dbschema[25] = "UPDATE `" . MYSQL_DATABASE . "`.`supportedOS` set osName = 'Windows 2000/XP' where osValue = '1'";

$dbschema[26] = "INSERT INTO `" . MYSQL_DATABASE . "`.`supportedOS` VALUES  ('','Other', '99')";

$dbschema[27] = "ALTER TABLE `" . MYSQL_DATABASE . "`.`multicastSessions` CHANGE COLUMN `msAnon1` `msIsDD` VARCHAR(1)  CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL";

$dbschema[28] = "UPDATE `" . MYSQL_DATABASE . "`.`schemaVersion` set vValue = '5'";

$dbschema[29] = "CREATE TABLE `" . MYSQL_DATABASE . "`.`virus` (
		  `vID` integer  NOT NULL AUTO_INCREMENT,
		  `vName` varchar(250)  NOT NULL,
		  `vHostMAC` varchar(50)  NOT NULL,
		  `vOrigFile` longtext  NOT NULL,
		  `vDateTime` datetime  NOT NULL,
		  `vMode` varchar(5)  NOT NULL,
		  `vAnon2` varchar(50)  NOT NULL,
		  PRIMARY KEY (`vID`),
		  INDEX `new_index`(`vHostMAC`),
		  INDEX `new_index2`(`vDateTime`)
		)
		ENGINE = MyISAM";
$dbschema[30] = "UPDATE `" . MYSQL_DATABASE . "`.`schemaVersion` set vValue = '6'";

$dbschema[31] = "CREATE TABLE `" . MYSQL_DATABASE . "`.`userTracking` (
		  `utID` integer  NOT NULL AUTO_INCREMENT,
		  `utHostID` integer  NOT NULL,
		  `utUserName` varchar(50)  NOT NULL,
		  `utAction` varchar(2)  NOT NULL,
		  `utDateTime` datetime  NOT NULL,
		  `utDesc` varchar(250)  NOT NULL,
		  `utDate` date  NOT NULL,
		  `utAnon3` varchar(2)  NOT NULL,
		  PRIMARY KEY (`utID`),
		  INDEX `new_index`(`utHostID`),
		  INDEX `new_index1`(`utUserName`),
		  INDEX `new_index2`(`utAction`),
		  INDEX `new_index3`(`utDateTime`)
		)
		ENGINE = MyISAM";

$dbschema[32] = "ALTER TABLE `" . MYSQL_DATABASE . "`.`hosts` CHANGE COLUMN `hostAnon1` `hostPrinterLevel` VARCHAR(2)  CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL";

$dbschema[33] = "CREATE TABLE `" . MYSQL_DATABASE . "`.`printers` (
		  `pID` integer  NOT NULL AUTO_INCREMENT,
		  `pPort` longtext  NOT NULL,
		  `pDefFile` longtext  NOT NULL,
		  `pModel` varchar(250)  NOT NULL,
		  `pAlias` varchar(250)  NOT NULL,
		  `pConfig` varchar(10)  NOT NULL,
		  `pIP` varchar(20)  NOT NULL,
		  `pAnon2` varchar(10)  NOT NULL,
		  `pAnon3` varchar(10)  NOT NULL,
		  `pAnon4` varchar(10)  NOT NULL,
		  `pAnon5` varchar(10)  NOT NULL,
		  PRIMARY KEY (`pID`),
		  INDEX `new_index1`(`pModel`),
		  INDEX `new_index2`(`pAlias`)
		)
		ENGINE = MyISAM";


$dbschema[34] = "CREATE TABLE `" . MYSQL_DATABASE . "`.`printerAssoc` (
		  `paID` integer  NOT NULL AUTO_INCREMENT,
		  `paHostID` integer  NOT NULL,
		  `paPrinterID` integer  NOT NULL,
		  `paIsDefault` varchar(2)  NOT NULL,
		  `paAnon1` varchar(2)  NOT NULL,
		  `paAnon2` varchar(2)  NOT NULL,
		  `paAnon3` varchar(2)  NOT NULL,
		  `paAnon4` varchar(2)  NOT NULL,
		  `paAnon5` varchar(2)  NOT NULL,
		  PRIMARY KEY (`paID`),
		  INDEX `new_index1`(`paHostID`),
		  INDEX `new_index2`(`paPrinterID`)
		)
		ENGINE = MyISAM";

$dbschema[35] = "CREATE TABLE  `" . MYSQL_DATABASE . "`.`inventory` (
		  `iID` int(11) NOT NULL auto_increment,
		  `iHostID` int(11) NOT NULL,
		  `iPrimaryUser` varchar(50) NOT NULL,
		  `iOtherTag` varchar(50) NOT NULL,
		  `iOtherTag1` varchar(50) NOT NULL,
		  `iCreateDate` datetime NOT NULL,
		  `iSysman` varchar(250) NOT NULL,
		  `iSysproduct` varchar(250) NOT NULL,
		  `iSysversion` varchar(250) NOT NULL,
		  `iSysserial` varchar(250) NOT NULL,
		  `iSystype` varchar(250) NOT NULL,
		  `iBiosversion` varchar(250) NOT NULL,
		  `iBiosvendor` varchar(250) NOT NULL,
		  `iBiosdate` varchar(250) NOT NULL,
		  `iMbman` varchar(250) NOT NULL,
		  `iMbproductname` varchar(250) NOT NULL,
		  `iMbversion` varchar(250) NOT NULL,
		  `iMbserial` varchar(250) NOT NULL,
		  `iMbasset` varchar(250) NOT NULL,
		  `iCpuman` varchar(250) NOT NULL,
		  `iCpuversion` varchar(250) NOT NULL,
		  `iCpucurrent` varchar(250) NOT NULL,
		  `iCpumax` varchar(250) NOT NULL,
		  `iMem` varchar(250) NOT NULL,
		  `iHdmodel` varchar(250) NOT NULL,
		  `iHdfirmware` varchar(250) NOT NULL,
		  `iHdserial` varchar(250) NOT NULL,
		  `iCaseman` varchar(250) NOT NULL,
		  `iCasever` varchar(250) NOT NULL,
		  `iCaseserial` varchar(250) NOT NULL,
		  `iCaseasset` varchar(250) NOT NULL,
		  PRIMARY KEY  (`iID`)
		) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1";

$dbschema[36] = "CREATE TABLE `" . MYSQL_DATABASE . "`.`clientUpdates` (
		  `cuID` integer  NOT NULL AUTO_INCREMENT,
		  `cuName` varchar(200)  NOT NULL,
		  `cuMD5` varchar(100)  NOT NULL,
		  `cuType` varchar(3)  NOT NULL,
		  `cuFile` LONGBLOB  NOT NULL,
		  PRIMARY KEY (`cuID`),
		  INDEX `new_index`(`cuName`),
		  INDEX `new_index1`(`cuType`)
		)
		ENGINE = MyISAM";

$dbschema[37] = "UPDATE `" . MYSQL_DATABASE . "`.`schemaVersion` set vValue = '7'";

$dbschema[38] = "INSERT INTO " . MYSQL_DATABASE . ".supportedOS(osName, osValue) values( '"._("Windows 98")."', '3' )";

$dbschema[39] = "INSERT INTO " . MYSQL_DATABASE . ".supportedOS(osName, osValue) values( '"._("Windows (other)")."', '4' )";

$dbschema[40] = "INSERT INTO " . MYSQL_DATABASE . ".supportedOS(osName, osValue) values( '"._("Linux")."', '50' )";

$dbschema[41] = "ALTER TABLE `" . MYSQL_DATABASE . "`.`multicastSessions` MODIFY COLUMN `msIsDD` integer  NOT NULL";

$dbschema[42] = "UPDATE `" . MYSQL_DATABASE . "`.`schemaVersion` set vValue = '8'";

$dbschema[43] = "CREATE TABLE `" . MYSQL_DATABASE . "`.`globalSettings` (
		  `settingID` INTEGER  NOT NULL AUTO_INCREMENT,
		  `settingKey` VARCHAR(254)  NOT NULL,
		  `settingDesc` longtext  NOT NULL,
		  `settingValue` varchar(254)  NOT NULL,
		  `settingCategory` varchar(254)  NOT NULL,
		  PRIMARY KEY (`settingID`),
		  INDEX `new_index`(`settingKey`)
		)
		ENGINE = MyISAM;";

$dbschema[44] = "INSERT INTO `" . MYSQL_DATABASE . "`.globalSettings(settingKey, settingDesc, settingValue, settingCategory)
                             values('FOG_TFTP_HOST', '"._("Hostname or IP address of the TFTP Server.")."', '" . TFTP_HOST . "', 'TFTP Server')";

$dbschema[45] = "INSERT INTO `" . MYSQL_DATABASE . "`.globalSettings(settingKey, settingDesc, settingValue, settingCategory)
                             values('FOG_TFTP_FTP_USERNAME', '"._("Username used to access the tftp server via ftp.")."', '" . TFTP_FTP_USERNAME . "', 'TFTP Server')";

$dbschema[46] = "INSERT INTO `" . MYSQL_DATABASE . "`.globalSettings(settingKey, settingDesc, settingValue, settingCategory)
                             values('FOG_TFTP_FTP_PASSWORD', '"._("Password used to access the tftp server via ftp.")."', '" . TFTP_FTP_PASSWORD . "', 'TFTP Server')";

$dbschema[47] = "INSERT INTO `" . MYSQL_DATABASE . "`.globalSettings(settingKey, settingDesc, settingValue, settingCategory)
                             values('FOG_TFTP_PXE_CONFIG_DIR', '"._("Location of pxe boot files on the PXE server.")."', '" . TFTP_PXE_CONFIG_DIR . "', 'TFTP Server')";

$dbschema[48] = "INSERT INTO `" . MYSQL_DATABASE . "`.globalSettings(settingKey, settingDesc, settingValue, settingCategory)
                             values('FOG_TFTP_PXE_KERNEL_DIR', '"._("Location of kernel files on the PXE server.")."', '" . TFTP_PXE_KERNEL_DIR . "', 'TFTP Server')";

$dbschema[49] = "INSERT INTO `" . MYSQL_DATABASE . "`.globalSettings(settingKey, settingDesc, settingValue, settingCategory)
                             values('FOG_TFTP_PXE_KERNEL', '"._("Location of kernel file on the PXE server, this should point to the kernel itself.")."', '" . PXE_KERNEL . "', 'TFTP Server')";

$dbschema[50] = "INSERT INTO `" . MYSQL_DATABASE . "`.globalSettings(settingKey, settingDesc, settingValue, settingCategory)
                             values('FOG_KERNEL_RAMDISK_SIZE', '"._("This setting defines the amount of physical memory (in KB) you want to use for the boot image.  This setting needs to be larger than the boot image and smaller that the total physical memory on the client.")."', '" . PXE_KERNEL_RAMDISK . "', 'TFTP Server')";

$dbschema[51] = "INSERT INTO `" . MYSQL_DATABASE . "`.globalSettings(settingKey, settingDesc, settingValue, settingCategory)
                             values('FOG_USE_SLOPPY_NAME_LOOKUPS', '"._("The settings was added to workaround a partial implementation of DHCP in the boot image.  The boot image is unable to obtain a DNS server address from the DHCP server, so what this setting will do is resolve any hostnames to IP address on the FOG server before writing the config files.")."', '" . USE_SLOPPY_NAME_LOOKUPS . "', 'General Settings')";

$dbschema[52] = "INSERT INTO `" . MYSQL_DATABASE . "`.globalSettings(settingKey, settingDesc, settingValue, settingCategory)
                             values('FOG_MEMTEST_KERNEL', '"._("The settings defines where the memtest boot image/kernel is located.")."', '" . MEMTEST_KERNEL . "', 'General Settings')";

$dbschema[53] = "INSERT INTO `" . MYSQL_DATABASE . "`.globalSettings(settingKey, settingDesc, settingValue, settingCategory)
                             values('FOG_PXE_BOOT_IMAGE', '"._("The settings defines where the fog boot file system image is located.")."', '" . PXE_IMAGE . "', 'TFTP Server')";

$dbschema[54] = "INSERT INTO `" . MYSQL_DATABASE . "`.globalSettings(settingKey, settingDesc, settingValue, settingCategory)
                             values('FOG_PXE_IMAGE_DNSADDRESS', '"._("Since the fog boot image has an incomplete dhcp implementation, you can specify a dns address to be used with the boot image.  If you are going to use this settings, you should turn <b>FOG_USE_SLOPPY_NAME_LOOKUPS</b> off.")."', '" . PXE_IMAGE_DNSADDRESS . "', 'TFTP Server')";

$dbschema[55] = "INSERT INTO `" . MYSQL_DATABASE . "`.globalSettings(settingKey, settingDesc, settingValue, settingCategory)
                             values('FOG_NFS_HOST', '"._("This setting defines the hostname or ip address of the NFS server used with FOG.")."', '" . STORAGE_HOST . "', 'NFS Server')";

$dbschema[56] = "INSERT INTO `" . MYSQL_DATABASE . "`.globalSettings(settingKey, settingDesc, settingValue, settingCategory)
                             values('FOG_NFS_FTP_USERNAME', '"._("This setting defines the username used to access files on the nfs server used with FOG.")."', '" . STORAGE_FTP_USERNAME . "', 'NFS Server')";

$dbschema[57] = "INSERT INTO `" . MYSQL_DATABASE . "`.globalSettings(settingKey, settingDesc, settingValue, settingCategory)
                             values('FOG_NFS_FTP_PASSWORD', '"._("This setting defines the password used to access flies on the nfs server used with FOG.")."', '" . STORAGE_FTP_PASSWORD . "', 'NFS Server')";

$dbschema[58] = "INSERT INTO `" . MYSQL_DATABASE . "`.globalSettings(settingKey, settingDesc, settingValue, settingCategory)
                             values('FOG_NFS_DATADIR', '"._("This setting defines the directory on the NFS server where images are stored.  ")."', '" . STORAGE_DATADIR . "', 'NFS Server')";

$dbschema[59] = "INSERT INTO `" . MYSQL_DATABASE . "`.globalSettings(settingKey, settingDesc, settingValue, settingCategory)
                             values('FOG_NFS_DATADIR_UPLOAD', '"._("This setting defines the directory on the NFS server where images are uploaded too.")."', '" . STORAGE_DATADIR_UPLOAD . "', 'NFS Server')";

$dbschema[60] = "INSERT INTO `" . MYSQL_DATABASE . "`.globalSettings(settingKey, settingDesc, settingValue, settingCategory)
                             values('FOG_NFS_BANDWIDTHPATH', '"._("This setting defines the web page used to acquire the bandwidth used by the nfs server.")."', '" . STORAGE_BANDWIDTHPATH . "', 'NFS Server')";

$dbschema[61] = "INSERT INTO `" . MYSQL_DATABASE . "`.globalSettings(settingKey, settingDesc, settingValue, settingCategory)
                             values('FOG_UPLOADRESIZEPCT', '"._("This setting defines the amount of padding applied to a partition before attempting resize the ntfs volume and upload it.")."', '" . UPLOADRESIZEPCT . "', 'General Settings')";

$dbschema[62] = "INSERT INTO `" . MYSQL_DATABASE . "`.globalSettings(settingKey, settingDesc, settingValue, settingCategory)
                             values('FOG_WEB_HOST', '"._("This setting defines the hostname or ip address of the web server used with fog.")."', '" . WEB_HOST . "', 'Web Server')";

$dbschema[63] = "INSERT INTO `" . MYSQL_DATABASE . "`.globalSettings(settingKey, settingDesc, settingValue, settingCategory)
                             values('FOG_WEB_ROOT', '"._("This setting defines the path to the fog webserver\'s root directory.")."', '" . WEB_ROOT . "', 'Web Server')";

$dbschema[64] = "INSERT INTO `" . MYSQL_DATABASE . "`.globalSettings(settingKey, settingDesc, settingValue, settingCategory)
                             values('FOG_WOL_HOST', '"._("This setting defines the ip address of hostname for the server hosting the Wake-on-lan service.")."', '" . WOL_HOST . "', 'General Settings')";

$dbschema[65] = "INSERT INTO `" . MYSQL_DATABASE . "`.globalSettings(settingKey, settingDesc, settingValue, settingCategory)
                             values('FOG_WOL_PATH', '"._("This setting defines the path to the files performing the WOL tasks.")."', '" . WOL_PATH . "', 'General Settings')";

$dbschema[66] = "INSERT INTO `" . MYSQL_DATABASE . "`.globalSettings(settingKey, settingDesc, settingValue, settingCategory)
                             values('FOG_WOL_INTERFACE', '"._("This setting defines the network interface used in the WOL process.")."', '" . WOL_INTERFACE . "', 'General Settings')";

$dbschema[67] = "INSERT INTO `" . MYSQL_DATABASE . "`.globalSettings(settingKey, settingDesc, settingValue, settingCategory)
                             values('FOG_SNAPINDIR', '"._("This setting defines the location of the snapin files.  These files must be hosted on the web server.")."', '" . SNAPINDIR . "', 'Web Server')";

$dbschema[68] = "INSERT INTO `" . MYSQL_DATABASE . "`.globalSettings(settingKey, settingDesc, settingValue, settingCategory)
                             values('FOG_QUEUESIZE', '"._("This setting defines how many unicast tasks to allow to be active at one time.")."', '" . QUEUESIZE . "', 'General Settings')";

$dbschema[69] = "INSERT INTO `" . MYSQL_DATABASE . "`.globalSettings(settingKey, settingDesc, settingValue, settingCategory)
                             values('FOG_CHECKIN_TIMEOUT', '"._("This setting defines the amount of time between client checks to determine if they are active clients.")."', '" . CHECKIN_TIMEOUT . "', 'General Settings')";

$dbschema[70] = "INSERT INTO `" . MYSQL_DATABASE . "`.globalSettings(settingKey, settingDesc, settingValue, settingCategory)
                             values('FOG_USER_MINPASSLENGTH', '"._("This setting defines the minimum number of characters in a user\'s password.")."', '" . USER_MINPASSLENGTH . "', 'User Management')";

$dbschema[71] = "INSERT INTO `" . MYSQL_DATABASE . "`.globalSettings(settingKey, settingDesc, settingValue, settingCategory)
                             values('FOG_USER_VALIDPASSCHARS', '"._("This setting defines the valid characters used in a password.")."', '" . USER_VALIDPASSCHARS . "', 'User Management')";

$dbschema[72] = "INSERT INTO `" . MYSQL_DATABASE . "`.globalSettings(settingKey, settingDesc, settingValue, settingCategory)
                             values('FOG_NFS_ETH_MONITOR', '"._("This setting defines which interface is monitored for traffic summaries.")."', '" . NFS_ETH_MONITOR . "', 'NFS Server')";

$dbschema[73] = "INSERT INTO `" . MYSQL_DATABASE . "`.globalSettings(settingKey, settingDesc, settingValue, settingCategory)
                             values('FOG_UDPCAST_INTERFACE', '"._("This setting defines the interface used in multicast communications.")."', '" . UDPCAST_INTERFACE . "', 'Multicast Settings')";

$dbschema[74] = "INSERT INTO `" . MYSQL_DATABASE . "`.globalSettings(settingKey, settingDesc, settingValue, settingCategory)
                             values('FOG_UDPCAST_STARTINGPORT', '"._("This setting defines the starting port number used in multicast communications.  This starting port number must be an even number.")."', '" . UDPCAST_STARTINGPORT . "', 'Multicast Settings')";

$dbschema[75] = "INSERT INTO `" . MYSQL_DATABASE . "`.globalSettings(settingKey, settingDesc, settingValue, settingCategory)
                             values('FOG_MULTICAST_MAX_SESSIONS', '"._("This setting defines the maximum number of multicast sessions that can be running at one time.")."', '" . FOG_MULTICAST_MAX_SESSIONS . "', 'Multicast Settings')";

$dbschema[76] = "INSERT INTO `" . MYSQL_DATABASE . "`.globalSettings(settingKey, settingDesc, settingValue, settingCategory)
                             values('FOG_JPGRAPH_VERSION', '"._("This setting defines ")."', '" . FOG_JPGRAPH_VERSION . "', 'Web Server')";

$dbschema[77] = "INSERT INTO `" . MYSQL_DATABASE . "`.globalSettings(settingKey, settingDesc, settingValue, settingCategory)
                             values('FOG_REPORT_DIR', '"._("This setting defines the location on the web server of the FOG reports.")."', '" . FOG_REPORT_DIR . "', 'Web Server')";

$dbschema[78] = "INSERT INTO `" . MYSQL_DATABASE . "`.globalSettings(settingKey, settingDesc, settingValue, settingCategory)
                             values('FOG_THEME', '"._("This setting defines what css style sheet and theme to use for FOG.")."', '" . FOG_THEME . "', 'Web Server')";

$dbschema[79] = "INSERT INTO `" . MYSQL_DATABASE . "`.globalSettings(settingKey, settingDesc, settingValue, settingCategory)
                             values('FOG_UPLOADIGNOREPAGEHIBER', '"._("This setting defines if you would like to remove hibernate and swap files before uploading a Windows image.  ")."', '" . FOG_UPLOADIGNOREPAGEHIBER . "', 'General Settings')";

$dbschema[80] = "INSERT INTO `" . MYSQL_DATABASE . "`.globalSettings(settingKey, settingDesc, settingValue, settingCategory)
                             values('FOG_SERVICE_DIRECTORYCLEANER_ENABLED', '"._("This setting defines if the Windows Service module directory cleaner should be enabled on client computers. This service is clean out the contents of a directory on when a user logs out of the workstation. (Valid values: 0 or 1).")."', '1', 'FOG Service - Directory Cleaner')";

$dbschema[81] = "CREATE TABLE `" . MYSQL_DATABASE . "`.`moduleStatusByHost` (
			  `msID` integer  NOT NULL AUTO_INCREMENT,
			  `msHostID` integer  NOT NULL,
			  `msModuleID` varchar(50)  NOT NULL,
			  `msState` varchar(1)  NOT NULL,
			  PRIMARY KEY (`msID`),
			  INDEX `new_index`(`msHostID`),
			  INDEX `new_index2`(`msModuleID`)
			)
			ENGINE = MyISAM;";
$dbschema[82] = "CREATE TABLE `" . MYSQL_DATABASE . "`.`dirCleaner` (
			  `dcID` integer  NOT NULL AUTO_INCREMENT,
			  `dcPath` longtext  NOT NULL,
			  PRIMARY KEY (`dcID`)
			)
			ENGINE = MyISAM;";

$dbschema[83] = "INSERT INTO `" . MYSQL_DATABASE . "`.globalSettings(settingKey, settingDesc, settingValue, settingCategory)
                             values('FOG_USE_ANIMATION_EFFECTS', '"._("This setting defines if the FOG management portal uses animation effects on it.  Valid values are 0 or 1")."', '1', 'General Settings')";

$dbschema[84] = "INSERT INTO `" . MYSQL_DATABASE . "`.globalSettings(settingKey, settingDesc, settingValue, settingCategory)
                             values('FOG_SERVICE_USERCLEANUP_ENABLED', '"._("This setting defines if user cleanup should be enabled.  The User Cleanup module will remove all local windows users from the workstation on log off accept for users that are whitelisted.  (Valid values are 0 or 1)")."', '0', 'FOG Service - User Cleanup')";

$dbschema[85] = "CREATE TABLE `" . MYSQL_DATABASE . "`.`userCleanup` (
			  `ucID` integer  NOT NULL AUTO_INCREMENT,
			  `ucName` varchar(254)  NOT NULL,
			  PRIMARY KEY (`ucID`)
			)
			ENGINE = MyISAM";

$dbschema[86] = "INSERT INTO `" . MYSQL_DATABASE . "`.userCleanup( ucName ) values( 'administrator' )";

$dbschema[87] = "INSERT INTO `" . MYSQL_DATABASE . "`.userCleanup( ucName ) values( 'admin' )";

$dbschema[88] = "INSERT INTO `" . MYSQL_DATABASE . "`.userCleanup( ucName ) values( 'guest' )";

$dbschema[89] = "INSERT INTO `" . MYSQL_DATABASE . "`.userCleanup( ucName ) values( 'HelpAssistant' )";

$dbschema[90] = "INSERT INTO `" . MYSQL_DATABASE . "`.globalSettings(settingKey, settingDesc, settingValue, settingCategory)
                             values('FOG_SERVICE_GREENFOG_ENABLED', '"._("This setting defines if the green fog module should be enabled.  The green fog module will shutdown or restart a computer at a set time.  (Valid values are 0 or 1)")."', '1', 'FOG Service - Green Fog')";

$dbschema[91] = "INSERT INTO `" . MYSQL_DATABASE . "`.globalSettings(settingKey, settingDesc, settingValue, settingCategory)
                             values('FOG_SERVICE_AUTOLOGOFF_ENABLED', '"._("This setting defines if the auto log off module should be enabled.  This module will log off any active user after X minutes of inactivity.  (Valid values are 0 or 1)")."', '1', 'FOG Service - Auto Log Off')";

$dbschema[92] = "INSERT INTO `" . MYSQL_DATABASE . "`.userCleanup( ucName ) values( 'ASPNET' )";

$dbschema[93] = "INSERT INTO `" . MYSQL_DATABASE . "`.userCleanup( ucName ) values( 'SUPPORT_' )";

$dbschema[94] = "INSERT INTO `" . MYSQL_DATABASE . "`.globalSettings(settingKey, settingDesc, settingValue, settingCategory)
                             values('FOG_SERVICE_DISPLAYMANAGER_ENABLED', '"._("This setting defines if the fog display manager should be active.  The fog display manager will reset the clients screen resolution to a fixed size on log off and on computer start up.  (Valid values are 0 or 1)")."', '0', 'FOG Service - Display Manager')";

$dbschema[95] = "INSERT INTO `" . MYSQL_DATABASE . "`.globalSettings(settingKey, settingDesc, settingValue, settingCategory)
                             values('FOG_SERVICE_DISPLAYMANAGER_X', '"._("This setting defines the default width in pixels to reset the computer display to with the fog display manager service.")."', '1024', 'FOG Service - Display Manager')";

$dbschema[96] = "INSERT INTO `" . MYSQL_DATABASE . "`.globalSettings(settingKey, settingDesc, settingValue, settingCategory)
                             values('FOG_SERVICE_DISPLAYMANAGER_Y', '"._("This setting defines the default height in pixels to reset the computer display to with the fog display manager service.")."', '768', 'FOG Service - Display Manager')";

$dbschema[97] = "INSERT INTO `" . MYSQL_DATABASE . "`.globalSettings(settingKey, settingDesc, settingValue, settingCategory)
                             values('FOG_SERVICE_DISPLAYMANAGER_R', '"._("This setting defines the default refresh rate to reset the computer display to with the fog display manager service.")."', '60', 'FOG Service - Display Manager')";

$dbschema[98] = "CREATE TABLE `" . MYSQL_DATABASE . "`.`hostScreenSettings` (
			  `hssID` integer  NOT NULL AUTO_INCREMENT,
			  `hssHostID` integer  NOT NULL,
			  `hssWidth` integer  NOT NULL,
			  `hssHeight` integer  NOT NULL,
			  `hssRefresh` integer  NOT NULL,
			  `hssOrientation` integer  NOT NULL,
			  `hssOther1` integer  NOT NULL,
			  `hssOther2` integer  NOT NULL,
			  PRIMARY KEY (`hssID`),
			  INDEX `new_index`(`hssHostID`)
			)
			ENGINE = MyISAM";

$dbschema[99] = "INSERT INTO `" . MYSQL_DATABASE . "`.globalSettings(settingKey, settingDesc, settingValue, settingCategory)
                             values('FOG_SERVICE_AUTOLOGOFF_MIN', '"._("This setting defines the number of minutes to wait before logging a user off of a PC. (Value of 0 will disable this module.)")."', '0', 'FOG Service - Auto Log Off')";

$dbschema[100] = "CREATE TABLE `" . MYSQL_DATABASE . "`.`hostAutoLogOut` (
			  `haloID` integer  NOT NULL AUTO_INCREMENT,
			  `haloHostID` integer  NOT NULL,
			  `haloTime` varchar(10) NOT NULL,
			  PRIMARY KEY (`haloID`),
			  INDEX `new_index`(`haloHostID`)
			)
			ENGINE = MyISAM";

$dbschema[101] = "INSERT INTO `" . MYSQL_DATABASE . "`.globalSettings(settingKey, settingDesc, settingValue, settingCategory)
                             values('FOG_SERVICE_AUTOLOGOFF_BGIMAGE', '"._("This setting defines the location of the background image used in the auto log off module.  The image should be 300px x 300px.  This image can be located locally (such as c:\\\\images\\\\myimage.jpg) or on a web server (such as http://freeghost.sf.net/images/image.jpg)")."', 'c:\\\\program files\\\\fog\\\\images\\\\alo-bg.jpg', 'FOG Service - Auto Log Off')";

$dbschema[102] = "INSERT INTO `" . MYSQL_DATABASE . "`.globalSettings(settingKey, settingDesc, settingValue, settingCategory)
                             values('FOG_KEYMAP', '"._("This setting defines the keymap used on the client boot image.")."', '', 'General Settings')";

$dbschema[103] = "CREATE TABLE `" . MYSQL_DATABASE . "`.`greenFog` (
			  `gfID` integer  NOT NULL AUTO_INCREMENT,
			  `gfHostID` integer  NOT NULL,
			  `gfHour` integer  NOT NULL,
			  `gfMin` integer  NOT NULL,
			  `gfAction` varchar(2)  NOT NULL,
			  `gfDays` varchar(25)  NOT NULL,
			  PRIMARY KEY (`gfID`),
			  INDEX `new_index`(`gfHostID`)
			)
			ENGINE = MyISAM";

$dbschema[104] = "INSERT INTO `" . MYSQL_DATABASE . "`.globalSettings(settingKey, settingDesc, settingValue, settingCategory)
                             values('FOG_SERVICE_HOSTNAMECHANGER_ENABLED', '"._("This setting defines if the fog hostname changer should be globally active.  (Valid values are 0 or 1)")."', '1', 'FOG Service - Hostname Changer')";

$dbschema[105] = "CREATE TABLE `" . MYSQL_DATABASE . "`.`aloLog` (
			  `alID` integer  NOT NULL AUTO_INCREMENT,
			  `alUserName` varchar(254)  NOT NULL,
			  `alHostID` integer  NOT NULL,
			  `alDateTime` datetime  NOT NULL,
			  `alAnon1` varchar(254)  NOT NULL,
			  `alAnon2` varchar(254)  NOT NULL,
			  `alAnon3` varchar(254)  NOT NULL,
			  PRIMARY KEY (`alID`),
			  INDEX `new_index`(`alUserName`),
			  INDEX `new_index2`(`alHostID`),
			  INDEX `new_index3`(`alDateTime`)
			)
			ENGINE = MyISAM";

$dbschema[106] = "UPDATE `" . MYSQL_DATABASE . "`.`schemaVersion` set vValue = '9'";

$dbschema[107] = "CREATE TABLE `" . MYSQL_DATABASE . "`.`imagingLog` (
			  `ilID` integer  NOT NULL AUTO_INCREMENT,
			  `ilHostID` integer  NOT NULL,
			  `ilStartTime` datetime  NOT NULL,
			  `ilFinishTime` datetime  NOT NULL,
			  `ilImageName` varchar(64)  NOT NULL,
			  PRIMARY KEY (`ilID`),
			  INDEX `new_index`(`ilHostID`)
			)
			ENGINE = MyISAM";

$dbschema[108] = "INSERT INTO `" . MYSQL_DATABASE . "`.globalSettings(settingKey, settingDesc, settingValue, settingCategory)
                             values('FOG_SERVICE_SNAPIN_ENABLED', '"._("This setting defines if the fog snapin installer should be globally active.  (Valid values are 0 or 1)")."', '1', 'FOG Service - Snapins')";

$dbschema[109] = "ALTER TABLE `" . MYSQL_DATABASE . "`.`snapins` CHANGE COLUMN `sAnon1` `sRunWith` VARCHAR(245) NOT NULL";

$dbschema[110] = "ALTER TABLE `" . MYSQL_DATABASE . "`.`snapinTasks` ADD COLUMN `stReturnCode` integer  NOT NULL AFTER `stSnapinID`,
			 ADD COLUMN `stReturnDetails` varchar(250)  NOT NULL AFTER `stReturnCode`";

$dbschema[111] = "ALTER TABLE `" . MYSQL_DATABASE . "`.`snapins` CHANGE COLUMN `sAnon2` `sRunWithArgs` VARCHAR(200)  CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL";

$dbschema[112] = "UPDATE `" . MYSQL_DATABASE . "`.`schemaVersion` set vValue = '10'";

$dbschema[113] = "ALTER TABLE `" . MYSQL_DATABASE . "`.`hosts` CHANGE COLUMN `hostAnon2` `hostKernelArgs` VARCHAR(250)  CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL";

$dbschema[114] = "INSERT INTO `" . MYSQL_DATABASE . "`.globalSettings(settingKey, settingDesc, settingValue, settingCategory)
                             values('FOG_KERNEL_ARGS', '"._("This setting allows you to add additional kernel arguments to the client boot image.  This setting is global for all hosts.")."', '', 'General Settings')";

$dbschema[115] = "UPDATE `" . MYSQL_DATABASE . "`.`schemaVersion` set vValue = '11'";

$dbschema[116] = "INSERT INTO `" . MYSQL_DATABASE . "`.globalSettings(settingKey, settingDesc, settingValue, settingCategory)
                             values('FOG_SERVICE_CLIENTUPDATER_ENABLED', '"._("This setting defines if the fog client updater should be globally active.  (Valid values are 0 or 1)")."', '1', 'FOG Service - Client Updater')";

$dbschema[117] = "INSERT INTO `" . MYSQL_DATABASE . "`.globalSettings(settingKey, settingDesc, settingValue, settingCategory)
                             values('FOG_SERVICE_HOSTREGISTER_ENABLED', '"._("This setting defines if the fog host register should be globally active.  (Valid values are 0 or 1)")."', '1', 'FOG Service - Host Register')";

$dbschema[118] = "INSERT INTO `" . MYSQL_DATABASE . "`.globalSettings(settingKey, settingDesc, settingValue, settingCategory)
                             values('FOG_SERVICE_PRINTERMANAGER_ENABLED', '"._("This setting defines if the fog printer maanger should be globally active.  (Valid values are 0 or 1)")."', '1', 'FOG Service - Printer Manager')";

$dbschema[119] = "INSERT INTO `" . MYSQL_DATABASE . "`.globalSettings(settingKey, settingDesc, settingValue, settingCategory)
                             values('FOG_SERVICE_TASKREBOOT_ENABLED', '"._("This setting defines if the fog task reboot should be globally active.  (Valid values are 0 or 1)")."', '1', 'FOG Service - Task Reboot')";

$dbschema[120] = "INSERT INTO `" . MYSQL_DATABASE . "`.globalSettings(settingKey, settingDesc, settingValue, settingCategory)
                             values('FOG_SERVICE_USERTRACKER_ENABLED', '"._("This setting defines if the fog user tracker should be globally active.  (Valid values are 0 or 1)")."', '1', 'FOG Service - User Tracker')";

$dbschema[121] = "UPDATE `" . MYSQL_DATABASE . "`.`schemaVersion` set vValue = '12'";

$dbschema[122] = "INSERT INTO `" . MYSQL_DATABASE . "`.globalSettings(settingKey, settingDesc, settingValue, settingCategory)
                             values('FOG_AD_DEFAULT_DOMAINNAME', '"._("This setting defines the default value to populate the host\'s Active Directory domain name value.")."', '', 'Active Directory Defaults')";

$dbschema[123] = "INSERT INTO `" . MYSQL_DATABASE . "`.globalSettings(settingKey, settingDesc, settingValue, settingCategory)
                             values('FOG_AD_DEFAULT_OU', '"._("This setting defines the default value to populate the host\'s Active Directory OU value.")."', '', 'Active Directory Defaults')";

$dbschema[124] = "INSERT INTO `" . MYSQL_DATABASE . "`.globalSettings(settingKey, settingDesc, settingValue, settingCategory)
                             values('FOG_AD_DEFAULT_USER', '"._("This setting defines the default value to populate the host\'s Active Directory user name value.'").", '', 'Active Directory Defaults')";

$dbschema[125] = "INSERT INTO `" . MYSQL_DATABASE . "`.globalSettings(settingKey, settingDesc, settingValue, settingCategory)
                             values('FOG_AD_DEFAULT_PASSWORD', '"._("This setting defines the default value to populate the host\'s Active Directory password value.  This settings must be encrypted.")."', '', 'Active Directory Defaults')";

$dbschema[126] = "UPDATE `" . MYSQL_DATABASE . "`.`schemaVersion` set vValue = '13'";

$dbschema[127] = "INSERT INTO `" . MYSQL_DATABASE . "`.globalSettings(settingKey, settingDesc, settingValue, settingCategory)
                             values('FOG_UTIL_DIR', '"._("This setting defines the location of the fog utility directory.")."', '/opt/fog/utils', 'FOG Utils')";

$dbschema[128] = "ALTER TABLE `" . MYSQL_DATABASE . "`.`users` ADD COLUMN `uType` varchar(2)  NOT NULL AFTER `uCreateBy`";

$dbschema[129] = "UPDATE `" . MYSQL_DATABASE . "`.`schemaVersion` set vValue = '14'";

$dbschema[130] = "INSERT INTO `" . MYSQL_DATABASE . "`.globalSettings(settingKey, settingDesc, settingValue, settingCategory)
                             values('FOG_PLUGINSYS_ENABLED', '"._("This setting defines if the fog plugin system should be enabled.")."', '0', 'Plugin System')";

$dbschema[131] = "INSERT INTO `" . MYSQL_DATABASE . "`.globalSettings(settingKey, settingDesc, settingValue, settingCategory)
                             values('FOG_PLUGINSYS_DIR', '"._("This setting defines the base location of fog plugins.")."', './plugins', 'Plugin System')";

$dbschema[132] = "CREATE TABLE `" . MYSQL_DATABASE . "`.`plugins` (
			  `pID` INTEGER  NOT NULL AUTO_INCREMENT,
			  `pName` VARCHAR(100)  NOT NULL,
			  `pState` CHAR  NOT NULL,
			  `pInstalled` CHAR  NOT NULL,
			  `pVersion` VARCHAR(100)  NOT NULL,
			  `pAnon1` VARCHAR(100)  NOT NULL,
			  `pAnon2` VARCHAR(100)  NOT NULL,
			  `pAnon3` VARCHAR(100)  NOT NULL,
			  `pAnon4` VARCHAR(100)  NOT NULL,
			  `pAnon5` VARCHAR(100)  NOT NULL,
			  PRIMARY KEY (`pID`),
			  INDEX `new_index`(`pName`),
			  INDEX `new_index1`(`pState`),
			  INDEX `new_index2`(`pInstalled`),
			  INDEX `new_index3`(`pVersion`)
			)
			ENGINE = MyISAM";

$dbschema[133] = "ALTER TABLE `" . MYSQL_DATABASE . "`.`hosts` CHANGE COLUMN `hostAnon3` `hostKernel` VARCHAR(250)  CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
			 CHANGE COLUMN `hostAnon4` `hostDevice` VARCHAR(250)  CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL";

$dbschema[134] = "UPDATE `" . MYSQL_DATABASE . "`.`schemaVersion` set vValue = '15'";

$dbschema[135] = "ALTER TABLE `" . MYSQL_DATABASE . "`.`tasks` ADD COLUMN `taskBPM` varchar(250)  NOT NULL AFTER `taskPCT`,
			 ADD COLUMN `taskTimeElapsed` varchar(250)  NOT NULL AFTER `taskBPM`,
			 ADD COLUMN `taskTimeRemaining` varchar(250)  NOT NULL AFTER `taskTimeElapsed`,
			 ADD COLUMN `taskDataCopied` varchar(250)  NOT NULL AFTER `taskTimeRemaining`,
			 ADD COLUMN `taskPercentText` varchar(250)  NOT NULL AFTER `taskDataCopied`,
			 ADD COLUMN `taskDataTotal` VARCHAR(250)  NOT NULL AFTER `taskPercentText`";

$dbschema[136] = "CREATE TABLE `" . MYSQL_DATABASE . "`.`nfsGroups` (
			  `ngID` integer  NOT NULL AUTO_INCREMENT,
			  `ngName` varchar(250)  NOT NULL,
			  `ngDesc` longtext  NOT NULL,
			  PRIMARY KEY (`ngID`)
			)
			ENGINE = MyISAM";

$dbschema[137] = "CREATE TABLE `" . MYSQL_DATABASE . "`.`nfsGroupMembers` (
		  `ngmID` integer  NOT NULL AUTO_INCREMENT,
		  `ngmMemberName` varchar(250)  NOT NULL,
		  `ngmMemberDescription` longtext  NOT NULL,
		  `ngmIsMasterNode` char  NOT NULL,
		  `ngmGroupID` integer  NOT NULL,
		  `ngmRootPath` longtext  NOT NULL,
		  `ngmIsEnabled` char  NOT NULL,
		  `ngmHostname` varchar(250)  NOT NULL,
		  `ngmMaxClients` integer  NOT NULL,
		  `ngmUser` varchar(250)  NOT NULL,
		  `ngmPass` varchar(250)  NOT NULL,
		  `ngmKey` varchar(250)  NOT NULL,
		  PRIMARY KEY (`ngmID`),
		  INDEX `new_index`(`ngmMemberName`),
		  INDEX `new_index2`(`ngmIsMasterNode`),
		  INDEX `new_index3`(`ngmGroupID`),
		  INDEX `new_index4`(`ngmIsEnabled`)
		)
		ENGINE = MyISAM";

$dbschema[138] = "ALTER TABLE `" . MYSQL_DATABASE . "`.`images` ADD COLUMN `imageNFSGroupID` integer  NOT NULL AFTER `imageDD`,
	 	ADD INDEX `new_index3`(`imageNFSGroupID`)";
 
$dbschema[139] = "ALTER TABLE `" . MYSQL_DATABASE . "`.`tasks` ADD COLUMN `taskNFSGroupID` integer  NOT NULL AFTER `taskDataTotal`,
		 ADD COLUMN `taskNFSMemberID` integer  NOT NULL AFTER `taskNFSGroupID`,
		 ADD COLUMN `taskNFSFailures` char  NOT NULL AFTER `taskNFSMemberID`,
		 ADD COLUMN `taskLastMemberID` integer  NOT NULL AFTER `taskNFSFailures`,
		 ADD INDEX `new_index5`(`taskNFSGroupID`),
		 ADD INDEX `new_index6`(`taskNFSMemberID`),
		 ADD INDEX `new_index7`(`taskNFSFailures`),
		 ADD INDEX `new_index8`(`taskLastMemberID`)";
 
$dbschema[140] = "CREATE TABLE `" . MYSQL_DATABASE . "`.`nfsFailures` (
		  `nfID` integer  NOT NULL AUTO_INCREMENT,
		  `nfNodeID` integer  NOT NULL,
		  `nfTaskID` integer  NOT NULL,
		  `nfHostID` integer  NOT NULL,
		  `nfGroupID` integer  NOT NULL,
		  `nfDateTime` integer  NOT NULL,
		  PRIMARY KEY (`nfID`),
		  INDEX `new_index`(`nfNodeID`),
		  INDEX `new_index1`(`nfTaskID`),
		  INDEX `new_index2`(`nfHostID`),
		  INDEX `new_index3`(`nfGroupID`)
		)
		ENGINE = MyISAM";

$dbschema[141] = "ALTER TABLE `" . MYSQL_DATABASE . "`.`nfsFailures` MODIFY COLUMN `nfDateTime` datetime  NOT NULL,
		 ADD INDEX `new_index4`(`nfDateTime`)";
 
$dbschema[142] ="ALTER TABLE `" . MYSQL_DATABASE . "`.`multicastSessions` CHANGE COLUMN `msAnon2` `msNFSGroupID` integer  NOT NULL,
		 ADD INDEX `new_index`(`msNFSGroupID`)";

$dbschema[143] = "INSERT INTO `" . MYSQL_DATABASE . "`.nfsGroups (ngName, ngDesc) values ('default', '"._("Auto generated fog nfs group")."' );";

$dbschema[144] =  "INSERT INTO 
			`" . MYSQL_DATABASE . "`.nfsGroupMembers
			(ngmMemberName, ngmMemberDescription, ngmIsMasterNode, ngmGroupID, ngmRootPath, ngmIsEnabled, ngmHostname, ngmMaxClients, ngmUser, ngmPass ) 
			VALUES
			('DefaultMember', '"._("Auto generated fog nfs group member")."', '1', '1', '/images/', '1', '" . STORAGE_HOST . "', '10', '" . STORAGE_FTP_USERNAME . "', '" . STORAGE_FTP_PASSWORD . "' )";

$dbschema[145] = "UPDATE `" . MYSQL_DATABASE . "`.images set imageNFSGroupID = '1'";

$dbschema[146] ="DELETE FROM `" . MYSQL_DATABASE . "`.`globalSettings` WHERE settingKey = 'FOG_NFS_HOST'";

$dbschema[147] ="DELETE FROM `" . MYSQL_DATABASE . "`.`globalSettings` WHERE settingKey = 'FOG_NFS_FTP_USERNAME'";

$dbschema[148] ="DELETE FROM `" . MYSQL_DATABASE . "`.`globalSettings` WHERE settingKey = 'FOG_NFS_FTP_PASSWORD'";

$dbschema[149] ="DELETE FROM `" . MYSQL_DATABASE . "`.`globalSettings` WHERE settingKey = 'FOG_NFS_DATADIR'";

$dbschema[150] ="DELETE FROM `" . MYSQL_DATABASE . "`.`globalSettings` WHERE settingKey = 'FOG_NFS_DATADIR_UPLOAD'";

$fogstoragenodeuser = "fogstorage";
$fogstoragenodepass = "fs" . rand( 1000, 10000 );

$dbschema[151] = "INSERT INTO `" . MYSQL_DATABASE . "`.globalSettings(settingKey, settingDesc, settingValue, settingCategory)
                             values('FOG_STORAGENODE_MYSQLUSER', '"._("This setting defines the username the storage nodes should use to connect to the fog server.")."', '$fogstoragenodeuser', 'FOG Storage Nodes')";

$dbschema[152] = "INSERT INTO `" . MYSQL_DATABASE . "`.globalSettings(settingKey, settingDesc, settingValue, settingCategory)
                             values('FOG_STORAGENODE_MYSQLPASS', '"._("This setting defines the password the storage nodes should use to connect to the fog server.")."', '$fogstoragenodepass', 'FOG Storage Nodes')";

$dbschema[153] = "GRANT ALL ON `" . MYSQL_DATABASE . "`.* TO '$fogstoragenodeuser'@'%' IDENTIFIED BY '$fogstoragenodepass'";

$dbschema[154] = "UPDATE `" . MYSQL_DATABASE . "`.`schemaVersion` set vValue = '16'";

$dbschema[155] = "INSERT INTO `" . MYSQL_DATABASE . "`.globalSettings(settingKey, settingDesc, settingValue, settingCategory)
                             values('FOG_SSH_USERNAME', '"._("This setting defines the username used for the ssh client.")."', 'root', 'SSH Client')";
                             
$dbschema[156] = "INSERT INTO `" . MYSQL_DATABASE . "`.globalSettings(settingKey, settingDesc, settingValue, settingCategory)
                             values('FOG_SSH_PORT', '"._("This setting defines the port to use for the ssh client.")."', '22', 'SSH Client')";

$dbschema[157] = "INSERT INTO `" . MYSQL_DATABASE . "`.globalSettings(settingKey, settingDesc, settingValue, settingCategory)
                             values('FOG_VIEW_DEFAULT_SCREEN', '"._("This setting defines which page is displayed in each section, valid settings includes <b>LIST</b> and <b>SEARCH</b>.")."', 'SEARCH', 'FOG View Settings')";

$dbschema[158] = "UPDATE `" . MYSQL_DATABASE . "`.`schemaVersion` set vValue = '17'";

$dbschema[159] = "INSERT INTO " . MYSQL_DATABASE . ".supportedOS(osName, osValue) values( '"._("Windows 7")."', '5' )";

$dbschema[160] = "UPDATE `" . MYSQL_DATABASE . "`.`schemaVersion` set vValue = '18'";

$dbschema[161] = "INSERT INTO `" . MYSQL_DATABASE . "`.globalSettings(settingKey, settingDesc, settingValue, settingCategory)
                             values('FOG_PXE_MENU_TIMEOUT', '"._("This setting defines the default value for the pxe menu timeout.")."', '3', 'FOG PXE Settings')";

$dbschema[162] = "INSERT INTO `" . MYSQL_DATABASE . "`.globalSettings(settingKey, settingDesc, settingValue, settingCategory)
                             values('FOG_PROXY_IP', '"._("This setting defines the proxy ip address to use.")."', '', 'General Settings')";

$dbschema[163] = "INSERT INTO `" . MYSQL_DATABASE . "`.globalSettings(settingKey, settingDesc, settingValue, settingCategory)
                             values('FOG_PROXY_PORT', '"._("This setting defines the proxy port address to use.")."', '', 'General Settings')";

$dbschema[164] = "CREATE TABLE `" . MYSQL_DATABASE . "`.`scheduledTasks` (
			  `stID` integer  NOT NULL AUTO_INCREMENT,
			  `stName` varchar(240)  NOT NULL,
			  `stDesc` longtext  NOT NULL,
			  `stType` varchar(24)  NOT NULL,
			  `stTaskType` varchar(24)  NOT NULL,
			  `stMinute` varchar(240)  NOT NULL,
			  `stHour` varchar(240)  NOT NULL,
			  `stDOM` varchar(240)  NOT NULL,
			  `stMonth` varchar(240)  NOT NULL,
			  `stDOW` varchar(240)  NOT NULL,
			  `stIsGroup` varchar(2)  NOT NULL,
			  `stGroupHostID` integer  NOT NULL,
			  `stShutDown` varchar(2)  NOT NULL,
			  `stOther1` varchar(240)  NOT NULL,
			  `stOther2` varchar(240)  NOT NULL,
			  `stOther3` varchar(240)  NOT NULL,
			  `stOther4` varchar(240)  NOT NULL,
			  `stOther5` varchar(240)  NOT NULL,
			  `stDateTime` BIGINT UNSIGNED NOT NULL DEFAULT 0,
			  `stActive` varchar(2)  NOT NULL DEFAULT 1,
			  PRIMARY KEY (`stID`)
			)
			ENGINE = MyISAM;";

$dbschema[165] = "INSERT INTO `" . MYSQL_DATABASE . "`.globalSettings(settingKey, settingDesc, settingValue, settingCategory)
                             values('FOG_UTIL_BASE', '"._("This setting defines the location of util base, which is typically /opt/fog/")."', '/opt/fog/', 'FOG Utils')";

$dbschema[166] = "UPDATE `" . MYSQL_DATABASE . "`.`schemaVersion` set vValue = '19'";

$dbschema[167] = "INSERT INTO `" . MYSQL_DATABASE . "`.globalSettings(settingKey, settingDesc, settingValue, settingCategory)
                             values('FOG_PXE_MENU_HIDDEN', '"._("This setting defines if you would like the FOG pxe menu hidden or displayed")."', '0', 'FOG PXE Settings')";

$dbschema[168] = "ALTER TABLE `" . MYSQL_DATABASE . "`.`globalSettings` MODIFY COLUMN `settingValue` LONGTEXT  CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL";

$dbschema[169] = "INSERT INTO `" . MYSQL_DATABASE . "`.globalSettings(settingKey, settingDesc, settingValue, settingCategory)
                             values('FOG_PXE_ADVANCED', '"._("This setting defines if you would like to append any settings to the end of your PXE default file.")."', '', 'FOG PXE Settings')";

$dbschema[170] = "INSERT INTO `" . MYSQL_DATABASE . "`.globalSettings(settingKey, settingDesc, settingValue, settingCategory)
                             values('FOG_USE_LEGACY_TASKLIST', '"._("This setting defines if you would like to use the legacy active tasks window.  Note:  The legacy screen will no longer be updated.")."', '0', 'General Settings')";

$dbschema[171] = "UPDATE `" . MYSQL_DATABASE . "`.`schemaVersion` set vValue = '20'";

$dbschema[172] = "CREATE TABLE `" . MYSQL_DATABASE . "`.`hostMAC` (
				  `hmID` integer  NOT NULL AUTO_INCREMENT,
				  `hmHostID` integer  NOT NULL,
				  `hmMAC` varchar(18)  NOT NULL,
				  `hmDesc` longtext  NOT NULL,
				  PRIMARY KEY (`hmID`),
				  INDEX `idxHostID`(`hmHostID`),
				  INDEX `idxMac`(`hmMAC`)
				)
				ENGINE = MyISAM";

$dbschema[173] = "CREATE TABLE `" . MYSQL_DATABASE . "`.`oui` (
				  `ouiID` int(11) NOT NULL AUTO_INCREMENT,
				  `ouiMACPrefix` varchar(8) NOT NULL,
				  `ouiMan` varchar(254) NOT NULL,
				  PRIMARY KEY (`ouiID`),
				  KEY `idxMac` (`ouiMACPrefix`)
				) ENGINE=MyISAM";

$dbschema[174] = "INSERT INTO `" . MYSQL_DATABASE . "`.`globalSettings` (`settingKey`, `settingDesc`, `settingValue`, `settingCategory`) VALUES
			('FOG_QUICKREG_AUTOPOP', 'Enable FOG Quick Registration auto population feature (0 = disabled, 1=enabled).  If this feature is enabled, FOG will auto populate the host settings and automatically image the computer without any user intervention.', '0', 'FOG Quick Registration'),
			('FOG_QUICKREG_IMG_ID', 'FOG Quick Registration Image ID.', '-1', 'FOG Quick Registration'),
			('FOG_QUICKREG_OS_ID', 'FOG Quick Registration OS ID.', '-1', 'FOG Quick Registration'),
			('FOG_QUICKREG_SYS_NAME', 'FOG Quick Registration system name template.  Use * for the autonumber feature.', 'PC-*', 'FOG Quick Registration'),
			('FOG_QUICKREG_SYS_NUMBER', 'FOG Quick Registration system name auto number.', '1', 'FOG Quick Registration'),
			('FOG_DEFAULT_LOCALE', 'Default language code to use for FOG.', 'en_US.UTF-8', 'General Settings'),
			('FOG_HOST_LOCKUP', 'Should FOG attempt to see if a host is active and display it as part of the UI?', '1', 'General Settings'),
			('FOG_UUID', 'This is a unique ID that is used to identify your installation.  In most cases you do not want to change this value.', '" . uniqid("", true) . "', 'General Settings')";

$dbschema[175] = "CREATE TABLE `" . MYSQL_DATABASE . "`.`pendingMACS` (
				  `pmID` INTEGER  NOT NULL AUTO_INCREMENT,
				  `pmAddress` varchar(18)  NOT NULL,
				  `pmHostID` INTEGER  NOT NULL,
				  PRIMARY KEY (`pmID`),
				  INDEX `idx_mc`(`pmAddress`),
				  INDEX `idx_host`(`pmHostID`)
				)
				ENGINE = MyISAM;";

$dbschema[176] = "INSERT INTO `" . MYSQL_DATABASE . "`.`globalSettings` (`settingKey`, `settingDesc`, `settingValue`, `settingCategory`) VALUES
			('FOG_QUICKREG_MAX_PENDING_MACS', 'This setting defines how many mac addresses will be stored in the pending mac address table for each host.', '4', 'FOG Service - Host Register'), 
			('FOG_QUICKREG_PENDING_MAC_FILTER', 'This is a list of MAC address fragments that is used to filter out pending mac address requests.  For example, if you don\'t want to see pending mac address requests for VMWare NICs then you could filter by 00:05:69.  This filter is comma seperated, and is used like a *starts with* filter.', '', 'FOG Service - Host Register')";

$dbschema[177] = "UPDATE `" . MYSQL_DATABASE . "`.`globalSettings` SET settingValue = '3.0.7' WHERE settingKey = 'FOG_JPGRAPH_VERSION'";

$dbschema[178] = "INSERT INTO `" . MYSQL_DATABASE . "`.`globalSettings` (`settingKey`, `settingDesc`, `settingValue`, `settingCategory`) VALUES
			('FOG_ADVANCED_STATISTICS', 'Enable the collection and display of advanced statistics.  This information WILL be sent to a remote server!  This information is used by the FOG team to see how FOG is being used.  The information that will be sent includes the server\'s UUID value, the number of hosts present in FOG, and number of images on your FOG server and well as total image space used. (0 = disabled, 1 = enabled).', '0', 'General Settings')";

$dbschema[179] = "UPDATE `" . MYSQL_DATABASE . "`.`schemaVersion` set vValue = '21'";

$dbschema[180] = "ALTER TABLE `" . MYSQL_DATABASE . "`.`inventory` ADD INDEX ( `iHostID` )";

$dbschema[181] = "UPDATE `" . MYSQL_DATABASE . "`.`globalSettings` set settingKey = 'FOG_HOST_LOOKUP' WHERE settingKey = 'FOG_HOST_LOCKUP'";

$dbschema[182] = "UPDATE `" . MYSQL_DATABASE . "`.`schemaVersion` set vValue = '22'";

$dbschema[183] = "INSERT INTO `" . MYSQL_DATABASE . "`.`globalSettings` (`settingKey`, `settingDesc`, `settingValue`, `settingCategory`) VALUES
			('FOG_DISABLE_CHKDSK', 'This is an experimental feature that will can be used to not set the dirty flag on a NTFS partition after resizing it.  It is recommended to you run chkdsk. (0 = runs chkdsk, 1 = disables chkdsk).', '1', 'General Settings')";

$dbschema[184] = "INSERT INTO `" . MYSQL_DATABASE . "`.`globalSettings` (`settingKey`, `settingDesc`, `settingValue`, `settingCategory`) VALUES
			('FOG_CHANGE_HOSTNAME_EARLY', 'This is an experimental feature that will can be used to change the computers hostname right after imaging the box, without the need for the FOG service.  (1 = enabled, 0 = disabled).', '1', 'General Settings')";

$dbschema[185] = "UPDATE `" . MYSQL_DATABASE . "`.`schemaVersion` set vValue = '23'";

// 24 - Blackout
// Add Kernel, KernelArgs and PrimaryDisk to Group
$dbschema[186] = "ALTER TABLE `" . MYSQL_DATABASE . "`.`groups` ADD `groupKernel` VARCHAR( 255 ) NOT NULL";
$dbschema[187] = "ALTER TABLE `" . MYSQL_DATABASE . "`.`groups` ADD `groupKernelArgs` VARCHAR( 255 ) NOT NULL";
$dbschema[188] = "ALTER TABLE `" . MYSQL_DATABASE . "`.`groups` ADD `groupPrimaryDisk` VARCHAR( 255 ) NOT NULL";
$dbschema[189] = "UPDATE `" . MYSQL_DATABASE . "`.`schemaVersion` set vValue = '24'";

// 25 - Blackout - 8:39 AM 25/09/2011
// Add 'os' Table & Data
// Add 'imageOSID' field to 'images' table
$dbschema[190] = "CREATE TABLE IF NOT EXISTS `" . MYSQL_DATABASE . "`.`os` (
  `osID` mediumint(9) NOT NULL AUTO_INCREMENT,
  `osName` varchar(30) NOT NULL,
  `osDescription` text NOT NULL,
  PRIMARY KEY (`osID`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 ;";
$dbschema[191] = "INSERT INTO `" . MYSQL_DATABASE . "`.`os` (`osID`, `osName`, `osDescription`) VALUES (1, 'Windows 2000/XP', ''), (3, 'Windows 98', ''), (2, 'Windows Vista', ''), (4, 'Windows Other', ''), (5, 'Windows 7', ''), (50, 'Linux', ''), (99, 'Other', '');";
$dbschema[192] = "ALTER TABLE `" . MYSQL_DATABASE . "`.`images` ADD `imageOSID` MEDIUMINT NOT NULL ";
$dbschema[193] = "ALTER TABLE `" . MYSQL_DATABASE . "`.`hosts` ADD UNIQUE (`hostMAC`)";
$dbschema[194] = "UPDATE `" . MYSQL_DATABASE . "`.`schemaVersion` set vValue = '25'";

// 26 - Blackout - 10:05 AM 30/09/2011
// Change `images`.`imageSize` from VARCHAR(200) -> MEDIUMINT(9)
$dbschema[195] = "ALTER TABLE `" . MYSQL_DATABASE . "`.`images` CHANGE `imageSize` `imageSize` MEDIUMINT NOT NULL";
// Add 'ngmInterface' to Storage Node table
$dbschema[196] = "ALTER TABLE `" . MYSQL_DATABASE . "`.`nfsGroupMembers` ADD `ngmInterface` VARCHAR( 10 ) NOT NULL DEFAULT 'eth0'";
$dbschema[197] = "ALTER TABLE `" . MYSQL_DATABASE . "`.`nfsGroupMembers` ADD `ngmGraphEnabled` ENUM( '0', '1' ) NOT NULL DEFAULT '0'";
$dbschema[198] = "UPDATE `" . MYSQL_DATABASE . "`.`schemaVersion` set vValue = '26'";

// 27 - Blackout - 2:16 PM 3/10/2011
// Convert DATETIME 'createdTime' fields to TIMESTAMP with DEFAULT of CURRENT_TIMESTAMP
$dbschema[199] = "ALTER TABLE `" . MYSQL_DATABASE . "`.`tasks` CHANGE `taskCreateTime` `taskCreateTime` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP";
$dbschema[200] = "ALTER TABLE `" . MYSQL_DATABASE . "`.`groups` CHANGE `groupDateTime` `groupDateTime` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP";
$dbschema[201] = "ALTER TABLE `" . MYSQL_DATABASE . "`.`hosts` CHANGE `hostCreateDate` `hostCreateDate` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP";
$dbschema[202] = "ALTER TABLE `" . MYSQL_DATABASE . "`.`history` CHANGE `hTime` `hTime` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP";
$dbschema[203] = "ALTER TABLE `" . MYSQL_DATABASE . "`.`aloLog` CHANGE `alDateTime` `alDateTime` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP";
$dbschema[204] = "ALTER TABLE `" . MYSQL_DATABASE . "`.`images` CHANGE `imageDateTime` `imageDateTime` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP";
$dbschema[205] = "ALTER TABLE `" . MYSQL_DATABASE . "`.`inventory` CHANGE `iCreateDate` `iCreateDate` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP";
$dbschema[206] = "ALTER TABLE `" . MYSQL_DATABASE . "`.`nfsFailures` CHANGE `nfDateTime` `nfDateTime` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP";
$dbschema[207] = "ALTER TABLE `" . MYSQL_DATABASE . "`.`snapinJobs` CHANGE `sjCreateTime` `sjCreateTime` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP";
$dbschema[208] = "ALTER TABLE `" . MYSQL_DATABASE . "`.`snapins` CHANGE `sCreateDate` `sCreateDate` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP";
$dbschema[209] = "ALTER TABLE `" . MYSQL_DATABASE . "`.`snapinTasks` CHANGE `stCheckinDate` `stCheckinDate` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP";
$dbschema[210] = "ALTER TABLE `" . MYSQL_DATABASE . "`.`users` CHANGE `uCreateDate` `uCreateDate` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP";
$dbschema[211] = "ALTER TABLE `" . MYSQL_DATABASE . "`.`userTracking` CHANGE `utDateTime` `utDateTime` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP";
$dbschema[212] = "ALTER TABLE `" . MYSQL_DATABASE . "`.`virus` CHANGE `vDateTime` `vDateTime` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP";
$dbschema[213] = "UPDATE `" . MYSQL_DATABASE . "`.`schemaVersion` set vValue = '27'";

// 28 - Blackout - 1:48 PM 1/12/2011
// Add 'imageTypes' table and data
$dbschema[214] = "CREATE TABLE IF NOT EXISTS `" . MYSQL_DATABASE . "`.`imageTypes` (
  `imageTypeID` mediumint(9) NOT NULL auto_increment,
  `imageTypeName` varchar(100) NOT NULL,
  PRIMARY KEY  (`imageTypeID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;";
$dbschema[215] = "INSERT INTO `" . MYSQL_DATABASE . "`.`imageTypes` (`imageTypeID`, `imageTypeName`) VALUES
(1, 'Single Partition (NTFS Only, Resizable)'),
(2, 'Multiple Partition Image - Single Disk (Not Resizable)'),
(3, 'Multiple Partition Image - All Disks  (Not Resizable)'),
(4, 'Raw Image (Sector By Sector, DD, Slow)');";
$dbschema[216] = "UPDATE `" . MYSQL_DATABASE . "`.`schemaVersion` set vValue = '28'";




// Blackout - 1:52 PM 1/12/2011
// TODO: Search 'hosts' -> compare with 'images.osID' -> update 'osID' in 'hosts'
// TODO: Search 'images' -> Get image type -> +1 to image type value
/*
	const IMAGE_TYPE_SINGLE_PARTITION_NTFS = 0;
	const IMAGE_TYPE_DD = 1;
	const IMAGE_TYPE_MULTIPARTITION_SINGLE_DISK = 2;
	const IMAGE_TYPE_MULTIPARTITION_MULTIDISK = 3;
*/





?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title><?php echo(_("FOG")." ".FOG_VERSION." "._("Database Schema Installer / Updater")); ?></title>
	
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<meta http-equiv="x-ua-compatible" content="IE=8">
	
	<link rel="stylesheet" type="text/css" href="../../management/css/fog.css" />
</head>
<body>
	<div id="wrapper">
		<!-- Header -->
		<div id="header">
			<div id="logo">
				<h1><a href="#"><img src="../../management/images/fog.png" /><sup><?php echo FOG_VERSION; ?></sup></a></h1>
				<h2>Open Source Computer Cloning Solution</h2>
			</div>
		</div>
		<!-- Content -->
		<div id="content" class="dashboard">
			<h1>Database Schema Installer / Updater</h1>
			<div id="content-inner">
			<?php
				// Blackout - 4:24 PM 23/09/2011
				// We now assign a Schema count to $FOG_SCHEMA instead of using the config variable FOG_SCHEMA
				// This means the amount of FOG Schema updates is automactically determined & users dont have to manually update their config file
				// BUT!! The check that forwards the user to this file uses the config file value
				
				$FOG_SCHEMA = count($installPath);
				
				if ( $_POST["confirm"] == "yes" )
				{
					//$conn = mysql_connect( MYSQL_HOST, MYSQL_USERNAME, MYSQL_PASSWORD);
					if ($DatabaseManager && $db)
					{
						$currentSchema = $DatabaseManager->getVersion();
						if ( $FOG_SCHEMA != $currentSchema )
						{
							while( $currentSchema != $FOG_SCHEMA )
							{
								$queryArray = $installPath[$currentSchema];
								for( $i = 0; $i < count( $queryArray ); $i++ )
								{
									$sql = $dbschema[$queryArray[$i]];
									if ( ! $db->query($sql)->queryResult() )
									{
											echo ( "<p class=\"installConfirm\">Database SQL: " . $sql . "<br>\n" ._("Database error").": (ID# ".	 $currentSchema . "-" . $i . ")</p><p>"._("Database Error").": <br /><pre class=\"shellcommand\">"  . mysql_error() . "</pre></p>" );
											exit;
									}
									//$conn = @mysql_connect( MYSQL_HOST, MYSQL_USERNAME, MYSQL_PASSWORD);
								}
								$currentSchema++;
							}

							if ( $FOG_SCHEMA == $DatabaseManager->getVersion() )
							{
								echo "<p class=\"installConfirm\">"._("Update/Install Successful!")."</p>";
								echo ( "<p>"._("Click")." <a href=\"../../management\">"._("here")."</a> "._("to login.")."</p>" );
							}
							else
								echo(  "<p class=\"installConfirm\">"._("Update/Install Failed!")."</p>" );
						}
						else
						{
							echo ( "<p class=\"installConfirm\">"._("Update not required, your database schema is up to date!")."</p>" );
							echo ( "<p>"._("Click")." <a href=\"../../management\">"._("here")."</a> "._("to login.")."</p>" );
						}
					}
					else
					{
						echo( "<p class=\"installConfirm\">"._("Unable to connect to Database")."</p><p>"._("Database Error").":<br /><pre class=\"shellcommand\">" . mysql_error() . "</pre></p><p>"._("Make sure your database username and password are correct.")."</p>" );
					}
				}
				else
				{
					echo ( "<form method=\"POST\" action=\"index.php?redir=1\">\n" );
						echo ( "<p>"._("Your FOG database schema is not up to date, either because you have updated FOG or this is a new FOG installation.  If this is a upgrade, we highly recommend that you backup your FOG database before updating the schema (this will allow you to return the previous installed version).")."</p>\n" );

						echo ( "<p>"._("If you would like to backup your FOG database you can do so my using MySql Administrator or by running the following command in a terminal window (Applications -> System Tools -> Terminal), this will save sqldump in your home directory.")."</p>\n" );

						echo ( "<div id=\"sidenotes\">cd ~;mysqldump --allow-keywords -x -v fog > fogbackup.sql</div>" );

						echo ( "<p></p>" );

						echo ( "<p class=\"installConfirm\">"._("Are you sure you wish to install/update the FOG database?")."</p>\n" );
						echo ( "<br /><input type=\"hidden\" name=\"confirm\" value=\"yes\" /><input type=\"submit\" value=\""._("Install/Upgrade Now")."\" />\n" );
					echo ( "</form>\n" );
				}
			?>	
			</div>
		</div>
	</div>
	<!-- Footer -->
	<div id="footer">FOG: Chuck Syperski &amp; Jian Zhan, FOG WEB UI: Peter Gilchrist</div>

</body>
</html>