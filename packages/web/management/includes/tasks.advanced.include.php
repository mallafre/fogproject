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
	
if ( IS_INCLUDED !== true ) die( _("Unable to load system configuration information.") );

?>
<h2><?php print _('Advanced Options'); ?></h2>
<?php

$hostid = mysql_real_escape_string( $_GET["hostid"] );
$groupid = mysql_real_escape_string( $_GET["groupid"] );
$blIsHost = false;
if ( $hostid !== "" )
{
	$blIsHost = true;
	$imageMembers = getImageMemberFromHostID( $conn, $hostid );	
}
else if( $groupid !== "" )
{
	$imageMembers = getImageMembersByGroupID( $conn, $groupid );
}
else
{
	echo "Error, no host or group found!";
	exit;
}

if ( $blIsHost )
{
	$Host = $imageMembers->getHost();
	?>
	<table cellpadding="0" cellspacing="0" border="0" width=90%>
	<tr>
		<td><?php print _('Hostname'); ?></td>
		<td><?php print $Host->get('name'); ?></td>
	</tr>
	<tr>
		<td><?php print _('IP Address'); ?></td>
		<td><?php print ($Host->get('ip') ? $Host->get('ip') : '&nbsp;'); ?></td>
	</tr>
	<tr>
		<td><?php print _('MAC Address'); ?></td>
		<td><?php print $Host->get('mac'); ?></td>
	</tr>
	</table>
	<h3><?php print _('Advanced Actions'); ?></h3>
	<table cellpadding="0" cellspacing="0" border="0" width=100%>
	<tr>
		<td class="c"><a href="?node=tasks&type=host&direction=debug&noconfirm=<?php print $Host->get('id'); ?>"><img src="./images/debug.png" /><p><?php print _('Debug'); ?></p></a></td>
		<td><p><?php print _('Debug mode will load the boot image and load a prompt so you can run any commands you wish.  When you are done, you must remember to remove the PXE file, by clicking on "Active Tasks" and clicking on the "Kill Task" button.'); ?></p></td>
	</tr>	
	<tr>
		<td class="c"><a href="?node=tasks&type=host&direction=down&debug=true&noconfirm=<?php print $Host->get('id'); ?>"><img src="./images/senddebug.png" /><p><?php print _('Deploy-Debug'); ?></p></a></td>
		<td><p><?php print _('Deploy-Debug mode allows FOG to setup the environment to allow you send a specific image to a computer, but instead of sending the image, FOG will leave you at a prompt right before sending.  If you actually wish to send the image all you need to do is type "fog" and hit enter.'); ?></p></td>
	</tr>		
	<tr>
		<td class="c"><a href="?node=tasks&type=host&direction=up&debug=true&noconfirm=<?php print $Host->get('id'); ?>"><img src="./images/restoredebug.png" /><p><?php print _('Upload-Debug'); ?></p></a></td>
		<td><p><?php print _('Upload-Debug mode allows FOG to setup the environment to allow you Upload a specific image to a computer, but instead of Upload the image, FOG will leave you at a prompt right before restoring.  If you actually wish to Upload the image all you need to do is type "fog" and hit enter.'); ?></p></td>
	</tr>			
	<tr>
		<td class="c"><a href="?node=tasks&type=host&direction=downnosnap&noconfirm=<?php print $Host->get('id'); ?>"><img src="./images/sendnosnapin.png" /><p><?php print _('Deploy without Snapins'); ?></p></a></td>
		<td><p><?php print _('Deploy without snapins allows FOG to image the workstation, but after the task is complete any snapins linked to the host or group will NOT be sent.'); ?></p></td>
	</tr>		
	<tr>
		<td class="c"><a href="?node=tasks&type=host&direction=allsnaps&noconfirm=<?php print $Host->get('id'); ?>"><img src="./images/snap.png" /><p><?php print _('Deploy Snapins'); ?></p></a></td>
		<td><p><?php print _('This option allows you to send all the snapins to host without imaging the computer.  (Requires FOG Service to be installed on client)'); ?></p></td>
	</tr>		
	<tr>
		<td class="c"><a href="?node=tasks&type=host&direction=onesnap&noconfirm=<?php print $Host->get('id'); ?>"><img src="./images/snap.png" /><p><?php print _('Deploy Single Snapin'); ?></p></a></td>
		<td><p><?php print _('This option allows you to send a single snapin to a host.  (Requires FOG Service to be installed on client)'); ?></p></td>
	</tr>			
	<tr>
		<td class="c"><a href="?node=tasks&type=host&direction=memtest&noconfirm=<?php print $Host->get('id'); ?>"><img src="./images/memtest.png" /><p><?php print _('Memtest86+'); ?></p></a></td>
		<td><p><?php print _('Memtest86+ loads Memtest86+ on the client computer and will have it continue to run until stopped.  When you are done, you must remember to remove the PXE file, by clicking on "Active Tasks" and clicking on the "Kill Task" button.'); ?></p></td>
	</tr>		
	<tr>
		<td class="c"><a href="?node=tasks&type=host&direction=wol&noconfirm=<?php print $Host->get('id'); ?>"><img src="./images/wake.png" /><p><?php print _('Wake Up'); ?></p></a></td>
		<td><p><?php print _('Wake Up will attempt to send the Wake-On-LAN packet to the computer to turn the computer on.  In switched environments, you typically need to configure your hardware to allow for this (iphelper).'); ?></p></td>
	</tr>			
	<tr>
		<td class="c"><a href="?node=tasks&type=host&direction=wipe&mode=fast&noconfirm=<?php print $Host->get('id'); ?>"><img src="./images/veryfastwipe.png" /><p><?php print _('Fast Wipe'); ?></p></a></td>
		<td><p><?php print _("Fast Wipe will boot the client computer and perform a quick and lazy disk wipe.  This method writes zero's to the start of the hard disk, destroying the MBR, but NOT overwritting everything on the disk."); ?></p></td>
	</tr>					
	<tr>
		<td class="c"><a href="?node=tasks&type=host&direction=wipe&mode=normal&noconfirm=<?php print $Host->get('id'); ?>"><img src="./images/quickwipe.png" /><p><?php print _('Normal Wipe'); ?></p></a></td>
		<td><p><?php print _("Normal Wipe will boot the client computer and perform a simple disk wipe.  This method writes one pass of zero's to the hard disk."); ?></p></td>
	</tr>				
	<tr>
		<td class="c"><a href="?node=tasks&type=host&direction=wipe&mode=full&noconfirm=<?php print $Host->get('id'); ?>"><img src="./images/fullwipe.png" /><p><?php print _('Full Wipe'); ?></p></a></td>
		<td><p><?php print _('Full Wipe will boot the client computer and perform a full disk wipe.  This method writes a few passes of random data to the hard disk.'); ?></p></td>
	</tr>					
	<tr>
		<td class="c"><a href="?node=tasks&type=host&direction=surfacetest&noconfirm=<?php print $Host->get('id'); ?>"><img src="./images/surfacetest.png" /><p><?php print _('Disk Surface Test'); ?></p></a></td>
		<td><p><?php print _("Disk Surface Test checks the hard drive's surface sector by sector for any errors and reports back if errors are present."); ?></p></td>
	</tr>							
	<tr>
		<td class="c"><a href="?node=tasks&type=host&direction=testdisk&noconfirm=<?php print $Host->get('id'); ?>"><img src="./images/testdisk.png" /><p><?php print _('Test Disk'); ?></p></a></td>
		<td><p><?php print _('Test Disk loads the testdisk utility that can be used to check a hard disk and recover lost partitions.'); ?></p></td>
	</tr>					
	<tr>
		<td class="c"><a href="?node=tasks&type=host&direction=photorec&noconfirm=<?php print $Host->get('id'); ?>"><img src="./images/recover.png" /><p><?php print _('Recover'); ?></p></a></td>
		<td><p><?php print _('Recover loads the photorec utility that can be used to recover lost files from a hard drisk.  When recovering files, make sure you save them to your NFS volume (ie: /images).'); ?></p></td>
	</tr>								
	<tr>
		<td class="c"><a href="?node=tasks&type=host&direction=clamav&noconfirm=<?php print $Host->get('id'); ?>"><img src="./images/clam.png" /><p><?php print _('Anti-Virus'); ?></p></a></td>
		<td><p><?php print _('Anti-Virus loads Clam AV on the client boot image, updates the scanner and then scans the Windows partition.'); ?></p></td>
	</tr>									
	<tr>
		<td class="c"><a href="?node=tasks&type=host&direction=inventory&noconfirm=<?php print $Host->get('id'); ?>"><img src="./images/inventory.png" /><p><?php print _('Hardware Inventory'); ?></p></a></td>
		<td><p><?php print _('The hardware inventory task will boot the client computer and pull basic hardware informtation from it and report it back to the FOG server.'); ?></p></td>
	</tr>									
	<tr>
		<td class="c"><a href="?node=tasks&type=host&direction=winpassreset&noconfirm=<?php print $Host->get('id'); ?>"><img src="./images/winpass.png" /><p><?php print _('Password Reset'); ?></p></a></td>
		<td><p><?php print _('Password reset will blank out a Windows user password that may have been lost or forgotten.'); ?></p></td>
	</tr>	
	</table>
	<?php
}
else
{
	?>
	<table width="98%" cellspacing="0" cellpadding="0" border="0">
	<?php
	for ($i = 0; $i < count($imageMembers); $i++)
	{
		printf("<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>", $imageMembers[$i]->getHostName(), $imageMembers[$i]->get('mac'), $imageMembers[$i]->getIPAddress(), $imageMembers[$i]->getImage(), $imageMembers[$i]->getImagePath());
	}
	?>
	</table>
	<h3><?php print _('Advanced Actions'); ?></h3>
	<table width="98%" cellspacing="0" cellpadding=0 border=0>
	<tr>
		<td class="c"><a href="?node=tasks&type=group&direction=debug&noconfirm=<?php print $groupid; ?>"><img src="./images/debug.png" /><p>Debug</p></a></td>
		<td><p><?php print _('Debug mode will load the boot image and load a prompt so you can run any commands you wish.  When you are done, you must remember to remove the PXE file, by clicking on "Active Tasks" and clicking on the "Kill Task" button.'); ?></p></td>
	</tr>
	<tr>
		<td class="c"><a href="?node=tasks&type=group&direction=down&debug=true&noconfirm=<?php print $groupid; ?>"><img src="./images/senddebug.png" /><p><?php print _('Deploy-Debug'); ?></p></a></td>
		<td><p><?php print _('Deploy-Debug mode allows FOG to setup the environment to allow you send a specific image to a computer, but instead of sending the image, FOG will leave you at a prompt right before sending.  If you actually wish to send the image all you need to do is type "fog" and hit enter.'); ?></p></td>
	</tr>		
	<tr>
		<td class="c"><a href="?node=tasks&type=group&direction=downnosnap&noconfirm=<?php print $groupid; ?>"><img src="./images/sendnosnapin.png" /><p><?php print _('Deploy without Snapins'); ?></p></a></td>
		<td><p><?php print _('Deploy without snapins allows FOG to image the workstation, but after the task is complete any snapins linked to the host or group will NOT be sent.'); ?></p></td>
	</tr>		
	<tr>
		<td class="c"><a href="?node=tasks&type=group&direction=allsnaps&noconfirm=<?php print $groupid; ?>"><img src="./images/snap.png" /><p><?php print _('Deploy Snapins'); ?></p></a></td>
		<td><p><?php print _('This option allows you to send all the snapins to host without imaging the computer.  (Requires FOG Service to be installed on client)'); ?></p></td>
	</tr>		
	<tr>
		<td class="c"><a href="?node=tasks&type=group&direction=memtest&noconfirm=<?php print $groupid; ?>"><img src="./images/memtest.png" /><p><?php print _('Memtest86+'); ?></p></a></td>
		<td><p><?php print _('Memtest86+ loads Memtest86+ on the client computer and will have it continue to run until stopped.  When you are done, you must remember to remove the PXE file, by clicking on "Active Tasks" and clicking on the "Kill Task" button.'); ?></p></td>
	</tr>		
	<tr>
		<td class="c"><a href="?node=tasks&type=group&direction=wol&noconfirm=<?php print $groupid; ?>"><img src="./images/wake.png" /><p><?php print _('Wake Up'); ?></p></a></td>
		<td><p><?php print _('Wake Up will attempt to send the Wake-On-LAN packet to the computer to turn the computer on.  In switched environments, you typically need to configure your hardware to allow for this (iphelper).'); ?></p></td>
	</tr>			
	<tr>
		<td class="c"><a href="?node=tasks&type=group&direction=wipe&mode=fast&noconfirm=<?php print $groupid; ?>"><img src="./images/veryfastwipe.png" /><p><?php print _('Fast Wipe'); ?></p></a></td>
		<td><p><?php print _("Fast Wipe will boot the client computer and perform a quick and lazy disk wipe.  This method writes zero's to the start of the hard disk, destroying the MBR, but NOT overwritting everything on the disk."); ?></p></td>
	</tr>					
	<tr>
		<td class="c"><a href="?node=tasks&type=group&direction=wipe&mode=normal&noconfirm=<?php print $groupid; ?>"><img src="./images/quickwipe.png" /><p><?php print _('Normal Wipe'); ?></p></a></td>
		<td><p><?php print _("Normal Wipe will boot the client computer and perform a simple disk wipe.  This method writes one pass of zero's to the hard disk."); ?></p></td>
	</tr>				
	<tr>
		<td class="c"><a href="?node=tasks&type=group&direction=wipe&mode=full&noconfirm=<?php print $groupid; ?>"><img src="./images/fullwipe.png" /><p><?php print _('Full Wipe'); ?></p></a></td>
		<td><p><?php print _('Full Wipe will boot the client computer and perform a full disk wipe.  This method writes a few passes of random data to the hard disk.'); ?></p></td>
	</tr>					
	<tr>
		<td class="c"><a href="?node=tasks&type=group&direction=surfacetest&noconfirm=<?php print $groupid; ?>"><img src="./images/surfacetest.png" /><p><?php print _('Disk Surface Test'); ?></p></a></td>
		<td><p><?php print _("Disk Surface Test checks the hard drive's surface sector by sector for any errors and reports back if errors are present."); ?></p></td>
	</tr>								
	<tr>
		<td class="c"><a href="?node=tasks&type=group&direction=testdisk&noconfirm=<?php print $groupid; ?>"><img src="./images/testdisk.png" /><p><?php print _('Test Disk'); ?></p></a></td>
		<td><p><?php print _('Test Disk loads the testdisk utility that can be used to check a hard disk and recover lost partitions.'); ?></p></td>
	</tr>						
	<tr>
		<td class="c"><a href="?node=tasks&type=group&direction=photorec&noconfirm=<?php print $groupid; ?>"><img src="./images/recover.png" /><p><?php print _('Recover'); ?></p></a></td>
		<td><p><?php print _('Recover loads the photorec utility that can be used to recover lost files from a hard drisk.  When recovering files, make sure you save them to your NFS volume (ie: /images).'); ?></p></td>
	</tr>						
	<tr>
		<td class="c"><a href="?node=tasks&type=group&direction=clamav&noconfirm=<?php print $groupid; ?>"><img src="./images/clam.png" /><p><?php print _('Anti-Virus'); ?></p></a></td>
		<td><p><?php print _('Anti-Virus loads Clam AV on the client boot image, updates the scanner and then scans the Windows partition.'); ?></p></td>
	</tr>							
	<tr>
		<td class="c"><a href="?node=tasks&type=group&direction=inventory&noconfirm=<?php print $groupid; ?>"><img src="./images/inventory.png" /><p><?php print _('Hardware Inventory'); ?></p></a></td>
		<td><p><?php print _('The hardware inventory task will boot the client computer and pull basic hardware informtation from it and report it back to the FOG server.'); ?></p></td>
	</tr>	
	<tr>
		<td class="c"><a href="?node=tasks&type=group&direction=winpassreset&noconfirm=<?php print $groupid; ?>"><img src="./images/winpass.png" /><p><?php print _('Password Reset'); ?></p></a></td>
		<td><p><?php print _('Password reset will blank out a Windows user password that may have been lost or forgotten.'); ?></p></td>
	</tr>
	</table>
	<?php
}