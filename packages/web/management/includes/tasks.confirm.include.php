<?php
/*
 *  FOG  is a computer imaging solution.
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

if ( $currentUser != null && $currentUser->isLoggedIn() )
{
	?>
	<h2><?php print _("Confirm Task"); ?></h2>
	<?php
	if ( $_GET["noconfirm"] != null )
	{
		$noconfirm = trim( mysql_real_escape_string($_GET[noconfirm]) );
		if ( $_GET["direction"] == "up" )
		{
			$imageMembers = getImageMemberFromHostID( $conn, $noconfirm );
			if ( $imageMembers != null )
			{
				echo ( "<form method=\"get\" action=\"?\">" );
					echo ( "<input type=\"hidden\" name=\"node\" value=\"" . $_GET["node"] . "\" />" );
					echo ( "<input type=\"hidden\" name=\"sub\" value=\"" . $_GET["sub"] . "\" />" );				
					echo ( "<input type=\"hidden\" name=\"debug\" value=\"" . $_GET["debug"] . "\" />" );									
					echo ( "<input type=\"hidden\" name=\"confirm\" value=\"" . $noconfirm . "\" />" );
					echo ( "<input type=\"hidden\" name=\"type\" value=\"" . $_GET["type"] . "\" />" );
					echo ( "<input type=\"hidden\" name=\"direction\" value=\"" . $_GET["direction"] . "\" />" );
					echo ( "<div class=\"confirm-message\">" );
						echo ( "Are you sure you wish to upload this machine?" );
							echo ( "<div class=\"advanced-settings\">" );
								echo ( "<h2>"._("Advanced Settings")."</h2>" );
								echo ( "<input type=\"checkbox\" name=\"shutdown\"> "._("Shutdown after task completion?") );
								if ( $_GET["debug"] != "true" )
								{
									echo ( "<br />" );
									echo ( "<input type=\"checkbox\" name=\"singlesched\" id=\"singlesched\"> "._("Schedule Single Task Execution?") );
									echo ( "<p class=\"scheduleTask\">" );
										echo ( "<input type=\"text\" name=\"singlescheddate\" id=\"singlescheddatetime\" onFocus=\"document.getElementById('singlesched').checked='checked';\" />" );
									echo ( "</p>" );
									echo ( "<input type=\"checkbox\" name=\"cronsched\" id=\"cronsched\"> "._("Schedule Cron Style Task Execution?") );
									echo ( "<p class=\"scheduleTask\">" );
										echo ( "<input type=\"text\" name=\"cronMin\" id=\"cronMin\" size=\"5\" value=\"min\" onFocus=\"clearIf( this, 'min' );document.getElementById('cronsched').checked='checked';\" /> <input type=\"text\" name=\"cronHour\" id=\"cronHour\" size=\"5\" value=\"hour\" onFocus=\"clearIf( this, 'hour' );document.getElementById('cronsched').checked='checked';\" /> <input type=\"text\" name=\"cronDOM\" id=\"cronDOM\" size=\"5\" value=\"dom\" onFocus=\"clearIf( this, 'dom' );document.getElementById('cronsched').checked='checked';\" /> <input type=\"text\" name=\"cronMonth\" id=\"cronMonth\" size=\"5\" value=\"month\" onFocus=\"clearIf( this, 'month' );document.getElementById('cronsched').checked='checked';\" /> <input type=\"text\" name=\"cronDOW\" id=\"cronDOW\" size=\"5\" value=\"dow\" onFocus=\"clearIf( this, 'dow' );document.getElementById('cronsched').checked='checked';\" />" );
									echo ( "</p>" );
								}
							echo ( "</div>" );
					echo ( "</div>" );
					echo ( "<table width=\"100%\" cellspacing=\"0\" cellpadding=0 border=0>" );
					echo ( "<tr><td><font>" . $imageMembers->getHostName() . "</font></td><td><font>" . $imageMembers->get('mac') . "</font></td><td><font>" . $imageMembers->getIPAddress() . "</font></td><td><font>" . $imageMembers->getImage() . "</font></td></tr>" );
					echo ( "<tr><td colspan=10><center><br /><input class=\"smaller\" type=\"submit\" value=\""._("Upload Image")."\"  /></center></td></tr>" );
					echo ( "</table>" );
				echo ( "</form>" );
			}
			else
			{
				msgBox( _("Error:  Is an image associated with the computer?") );
			}
		}
		else if ( $_GET["direction"] == "down" )
		{
			$imageMembers = null;
			if ( $_GET["type"] == "host" )
			{
				$imageMembers = array( getImageMemberFromHostID( $conn, $noconfirm ) );
			}
			else if ( $_GET["type"] == "group" )
			{
				$imageMembers = getImageMembersByGroupID( $conn, $noconfirm );
			}

			if ( count( $imageMembers ) > 0 )
			{
				echo ( "<form method=\"get\" action=\"?\">" );
					echo ( "<input type=\"hidden\" name=\"node\" value=\"" . $_GET["node"] . "\" />" );
					echo ( "<input type=\"hidden\" name=\"sub\" value=\"" . $_GET["sub"] . "\" />" );
					echo ( "<input type=\"hidden\" name=\"debug\" value=\"" . $_GET["debug"] . "\" />" );
					echo ( "<input type=\"hidden\" name=\"confirm\" value=\"" . $noconfirm . "\" />" );
					echo ( "<input type=\"hidden\" name=\"type\" value=\"" . $_GET["type"] . "\" />" );
					echo ( "<input type=\"hidden\" name=\"direction\" value=\"" . $_GET["direction"] . "\" />" );
					
					
					
					echo ( "<div class=\"confirm-message\">" );
						echo ( _("Are you sure you wish to deploy these machines?") );
							echo ( "<div class=\"advanced-settings\">" );
								echo ( "<h2>"._("Advanced Settings")."</h2>" );
								echo ( "<input type=\"checkbox\" name=\"shutdown\"> "._("Shutdown after task completion?") );
								if ( $_GET["debug"] != "true" )
								{
									echo ( "<br />" );
									echo ( "<input type=\"checkbox\" name=\"singlesched\" id=\"singlesched\"> "._("Schedule Single Task Execution?") );
									echo ( "<p class=\"scheduleTask\">" );
										echo ( "<input type=\"text\" name=\"singlescheddate\" id=\"singlescheddatetime\" onFocus=\"document.getElementById('singlesched').checked='checked';\" />" );
									echo ( "</p>" );
									echo ( "<input type=\"checkbox\" name=\"cronsched\" id=\"cronsched\"> "._("Schedule Cron Style Task Execution?") );
									echo ( "<p class=\"scheduleTask\">" );
										echo ( "<input type=\"text\" name=\"cronMin\" id=\"cronMin\" size=\"5\" value=\"min\" onFocus=\"clearIf( this, 'min' );document.getElementById('cronsched').checked='checked';\" /> <input type=\"text\" name=\"cronHour\" id=\"cronHour\" size=\"5\" value=\"hour\" onFocus=\"clearIf( this, 'hour' );document.getElementById('cronsched').checked='checked';\" /> <input type=\"text\" name=\"cronDOM\" id=\"cronDOM\" size=\"5\" value=\"dom\" onFocus=\"clearIf( this, 'dom' );document.getElementById('cronsched').checked='checked';\" /> <input type=\"text\" name=\"cronMonth\" id=\"cronMonth\" size=\"5\" value=\"month\" onFocus=\"clearIf( this, 'month' );document.getElementById('cronsched').checked='checked';\" /> <input type=\"text\" name=\"cronDOW\" id=\"cronDOW\" size=\"5\" value=\"dow\" onFocus=\"clearIf( this, 'dow' );document.getElementById('cronsched').checked='checked';\" />" );
									echo ( "</p>" );
								}
							echo ( "</div>" );
					echo ( "</div>" );
					echo ( "<table width=\"98%\" cellspacing=\"0\" cellpadding=0 border=0>" );
					for( $i = 0; $i < count( $imageMembers ); $i++ )
					{
						echo ( "<tr><td><font class=\"smaller\">" . $imageMembers[$i]->getHostName() . "</font></td><td><font class=\"smaller\">" . $imageMembers[$i]->getMACColon() . "</font></td><td><font class=\"smaller\">" . $imageMembers[$i]->getIPAddress() . "</font></td><td><font class=\"smaller\">" . $imageMembers[$i]->getImage() . "</font></td></tr>" );
					}
					echo ( "<tr><td colspan=10><center><br /><input class=\"smaller\" type=\"submit\" value=\""._("Image All Computers")."\" /></center></td></tr>" );
					echo ( "</table>" );
				echo ( "</form>" );

			}
			else
			{
				echo ( _("No Host are members of this group") );
			}
		}
		else if ( $_GET["direction"] == "downmc" )
		{
			$imageMembers = null;
			if ( $_GET["type"] == "host" )
			{
				$imageMembers = array( getImageMemberFromHostID( $conn, $noconfirm ) );
			}
			else if ( $_GET["type"] == "group" )
			{
				$imageMembers = getImageMembersByGroupID( $conn, $noconfirm );
			}

			if ( count( $imageMembers ) > 0 )
			{
				if ( doAllMembersHaveSameImage( $imageMembers ) )
				{
					echo ( "<form method=\"get\" action=\"?\">" );
					echo ( "<input type=\"hidden\" name=\"node\" value=\"" . $_GET["node"] . "\" />" );
					echo ( "<input type=\"hidden\" name=\"sub\" value=\"" . $_GET["sub"] . "\" />" );
					echo ( "<input type=\"hidden\" name=\"debug\" value=\"" . $_GET["debug"] . "\" />" );
					echo ( "<input type=\"hidden\" name=\"confirm\" value=\"" . $noconfirm . "\" />" );										
					echo ( "<input type=\"hidden\" name=\"type\" value=\"" . $_GET["type"] . "\" />" );
					echo ( "<input type=\"hidden\" name=\"direction\" value=\"" . $_GET["direction"] . "\" />" );
						echo ( "<div class=\"confirm-message\">" );
							echo ( _("Are you sure you wish to image these machines using multicast?") );
							echo ( "<div class=\"advanced-settings\">" );
								echo ( "<h2>"._("Advanced Settings")."</h2>" );
								echo ( "<input type=\"checkbox\" name=\"shutdown\"> "._("Shutdown after task completion?") );
								echo ( "<br />" );
									echo ( "<input type=\"checkbox\" name=\"singlesched\" id=\"singlesched\"> "._("Schedule Single Task Execution?") );
									echo ( "<p class=\"scheduleTask\">" );
										echo ( "<input type=\"text\" name=\"singlescheddate\" id=\"singlescheddatetime\" onFocus=\"document.getElementById('singlesched').checked='checked';\" />" );
									echo ( "</p>" );
									echo ( "<input type=\"checkbox\" name=\"cronsched\" id=\"cronsched\"> "._("Schedule Cron Style Task Execution?") );
									echo ( "<p class=\"scheduleTask\">" );
										echo ( "<input type=\"text\" name=\"cronMin\" id=\"cronMin\" size=\"5\" value=\"min\" onFocus=\"clearIf( this, 'min' );document.getElementById('cronsched').checked='checked';\" /> <input type=\"text\" name=\"cronHour\" id=\"cronHour\" size=\"5\" value=\"hour\" onFocus=\"clearIf( this, 'hour' );document.getElementById('cronsched').checked='checked';\" /> <input type=\"text\" name=\"cronDOM\" id=\"cronDOM\" size=\"5\" value=\"dom\" onFocus=\"clearIf( this, 'dom' );document.getElementById('cronsched').checked='checked';\" /> <input type=\"text\" name=\"cronMonth\" id=\"cronMonth\" size=\"5\" value=\"month\" onFocus=\"clearIf( this, 'month' );document.getElementById('cronsched').checked='checked';\" /> <input type=\"text\" name=\"cronDOW\" id=\"cronDOW\" size=\"5\" value=\"dow\" onFocus=\"clearIf( this, 'dow' );document.getElementById('cronsched').checked='checked';\" />" );
									echo ( "</p>" );
							echo ( "</div>" );							
						echo ( "</div>" );
						echo ( "<table width=\"98%\" cellspacing=\"0\" cellpadding=0 border=0>" );
						for( $i = 0; $i < count( $imageMembers ); $i++ )
						{
							echo ( "<tr><td><font class=\"smaller\">" . $imageMembers[$i]->getHostName() . "</font></td><td><font class=\"smaller\">" . $imageMembers[$i]->get('mac') . "</font></td><td><font class=\"smaller\">" . $imageMembers[$i]->getIPAddress() . "</font></td><td><font class=\"smaller\">" . $imageMembers[$i]->getImage() . "</font></td></tr>" );
						}
						echo ( "<tr><td colspan=10><font class=\"smaller\"><center><br /><input class=\"smaller\" type=\"submit\" value=\""._("Image All Computers using multicast")."\"  /></center></font></td></tr>" );
						echo ( "</table>" );
					echo ( "</form>" );
				}
				else
				{
					echo ( "<p class=\"task-start-failed\">"._("Unable to multicast to this group of computers because they all do not have the same image definition!")."</p>" );
				}
			}
			else
			{
				echo ( _("No Host are members of this group") );
			}
		}
		else if ( $_GET["direction"] == "wol" )
		{
			$imageMembers = null;
			if ( $_GET["type"] == "host" )
			{
				$imageMembers = array( getImageMemberFromHostID( $conn, $noconfirm ) );
			}
			else if ( $_GET["type"] == "group" )
			{
				$imageMembers = getImageMembersByGroupID( $conn, $noconfirm );
			}

			echo ( "<form method=\"get\" action=\"?\">" );
				echo ( "<input type=\"hidden\" name=\"node\" value=\"" . $_GET["node"] . "\" />" );
				echo ( "<input type=\"hidden\" name=\"confirm\" value=\"" . $noconfirm . "\" />" );
				echo ( "<input type=\"hidden\" name=\"type\" value=\"" . $_GET["type"] . "\" />" );
				echo ( "<input type=\"hidden\" name=\"direction\" value=\"" . $_GET["direction"] . "\" />" );				
				
				echo ( "<div class=\"confirm-message\">" );
					echo ( _("Are you sure you wish to wake up these machines?") );
						echo ( "<div class=\"advanced-settings\">" );
							echo ( "<h2>"._("Advanced Settings")."</h2>" );
							echo ( "<input type=\"checkbox\" name=\"singlesched\" id=\"singlesched\"> "._("Schedule Single Task Execution?") );
							echo ( "<p class=\"scheduleTask\">" );
								echo ( "<input type=\"text\" name=\"singlescheddate\" id=\"singlescheddatetime\" onFocus=\"document.getElementById('singlesched').checked='checked';\" />" );
							echo ( "</p>" );
							echo ( "<input type=\"checkbox\" name=\"cronsched\" id=\"cronsched\"> "._("Schedule Cron Style Task Execution?") );
							echo ( "<p class=\"scheduleTask\">" );
								echo ( "<input type=\"text\" name=\"cronMin\" id=\"cronMin\" size=\"5\" value=\"min\" onFocus=\"clearIf( this, 'min' );document.getElementById('cronsched').checked='checked';\" /> <input type=\"text\" name=\"cronHour\" id=\"cronHour\" size=\"5\" value=\"hour\" onFocus=\"clearIf( this, 'hour' );document.getElementById('cronsched').checked='checked';\" /> <input type=\"text\" name=\"cronDOM\" id=\"cronDOM\" size=\"5\" value=\"dom\" onFocus=\"clearIf( this, 'dom' );document.getElementById('cronsched').checked='checked';\" /> <input type=\"text\" name=\"cronMonth\" id=\"cronMonth\" size=\"5\" value=\"month\" onFocus=\"clearIf( this, 'month' );document.getElementById('cronsched').checked='checked';\" /> <input type=\"text\" name=\"cronDOW\" id=\"cronDOW\" size=\"5\" value=\"dow\" onFocus=\"clearIf( this, 'dow' );document.getElementById('cronsched').checked='checked';\" />" );
							echo ( "</p>" );						
						echo ( "</div>" );					
				echo ( "</div>" );
				echo ( "<table width=\"98%\" cellspacing=\"0\" cellpadding=0 border=0>" );
				for( $i = 0; $i < count( $imageMembers ); $i++ )
				{
					echo ( "<tr><td>&nbsp;" . $imageMembers[$i]->getHostName() . "</td><td><font class=\"smaller\">" . $imageMembers[$i]->get('mac') . "</font></td><td><font class=\"smaller\">" . $imageMembers[$i]->getIPAddress() . "</font></td><td><font class=\"smaller\">" . $imageMembers[$i]->getImage() . "</font></td></tr>" );
				}
				echo ( "<tr><td colspan=10><center><br /><input class=\"smaller\" type=\"submit\" value=\"Wake up computers\"  /></center></td></tr>" );
				echo ( "</table>" );
			echo ( "</form>" );
		}
		else if ( $_GET["direction"] == "wipe" )
		{
			$imageMembers = null;
			if ( $_GET["type"] == "host" )
			{
				$imageMembers = array( getImageMemberFromHostID( $conn, $noconfirm ) );
			}
			else if ( $_GET["type"] == "group" )
			{
				$imageMembers = getImageMembersByGroupID( $conn, $noconfirm );
			}

			echo ( "<form method=\"get\" action=\"?\">" );
				
				echo ( "<input type=\"hidden\" name=\"node\" value=\"" . $_GET["node"] . "\" />" );
				echo ( "<input type=\"hidden\" name=\"confirm\" value=\"" . $noconfirm . "\" />" );				
				echo ( "<input type=\"hidden\" name=\"type\" value=\"" . $_GET["type"] . "\" />" );
				echo ( "<input type=\"hidden\" name=\"direction\" value=\"" . $_GET["direction"] . "\" />" );
				echo ( "<input type=\"hidden\" name=\"mode\" value=\"" . $_GET["mode"] . "\" />" );
				
				echo ( "<div class=\"confirm-message\">" );
					echo ( _("Are you sure you wish to wipe these machines? By wiping these machines you will be destorying all data present on the hard disk.") );
						echo ( "<div class=\"advanced-settings\">" );
							echo ( "<h2>"._("Advanced Settings")."</h2>" );
							echo ( "<input type=\"checkbox\" name=\"shutdown\"> "._("Shutdown after task completion?") );
							echo ( "<br /><input type=\"checkbox\" name=\"singlesched\" id=\"singlesched\"> "._("Schedule Single Task Execution?") );
							echo ( "<p class=\"scheduleTask\">" );
								echo ( "<input type=\"text\" name=\"singlescheddate\" id=\"singlescheddatetime\" onFocus=\"document.getElementById('singlesched').checked='checked';\" />" );
							echo ( "</p>" );
							echo ( "<input type=\"checkbox\" name=\"cronsched\" id=\"cronsched\"> Schedule Cron Style Task Execution?" );
							echo ( "<p class=\"scheduleTask\">" );
								echo ( "<input type=\"text\" name=\"cronMin\" id=\"cronMin\" size=\"5\" value=\"min\" onFocus=\"clearIf( this, 'min' );document.getElementById('cronsched').checked='checked';\" /> <input type=\"text\" name=\"cronHour\" id=\"cronHour\" size=\"5\" value=\"hour\" onFocus=\"clearIf( this, 'hour' );document.getElementById('cronsched').checked='checked';\" /> <input type=\"text\" name=\"cronDOM\" id=\"cronDOM\" size=\"5\" value=\"dom\" onFocus=\"clearIf( this, 'dom' );document.getElementById('cronsched').checked='checked';\" /> <input type=\"text\" name=\"cronMonth\" id=\"cronMonth\" size=\"5\" value=\"month\" onFocus=\"clearIf( this, 'month' );document.getElementById('cronsched').checked='checked';\" /> <input type=\"text\" name=\"cronDOW\" id=\"cronDOW\" size=\"5\" value=\"dow\" onFocus=\"clearIf( this, 'dow' );document.getElementById('cronsched').checked='checked';\" />" );
							echo ( "</p>" );
						echo ( "</div>" );					
				echo ( "</div>" );
				echo ( "<table width=\"98%\" cellspacing=\"0\" cellpadding=0 border=0>" );
				for( $i = 0; $i < count( $imageMembers ); $i++ )
				{
					echo ( "<tr><td>&nbsp;" . $imageMembers[$i]->getHostName() . "</td><td><font class=\"smaller\">" . $imageMembers[$i]->get('mac') . "</font></td><td><font class=\"smaller\">" . $imageMembers[$i]->getIPAddress() . "</font></td><td><font class=\"smaller\">" . $imageMembers[$i]->getImage() . "</font></td></tr>" );
				}
				echo ( "<tr><td colspan=10><center><br /><input class=\"smaller\" type=\"submit\" value=\""._("Wipe computer(s)")."\"  /></center></td></tr>" );
				echo ( "</table>" );
			echo ( "</form>" );
		}
		else if ( $_GET["direction"] == "clamav" )
		{
			$imageMembers = null;
			if ( $_GET["type"] == "host" )
			{
				$imageMembers = array( getImageMemberFromHostID( $conn, $noconfirm ) );
			}
			else if ( $_GET["type"] == "group" )
			{
				$imageMembers = getImageMembersByGroupID( $conn, $noconfirm );
			}

			echo ( "<form method=\"get\" action=\"?\">" );				
				echo ( "<input type=\"hidden\" name=\"node\" value=\"" . $_GET["node"] . "\" />" );
				echo ( "<input type=\"hidden\" name=\"confirm\" value=\"" . $noconfirm . "\" />" );								
				echo ( "<input type=\"hidden\" name=\"type\" value=\"" . $_GET["type"] . "\" />" );
				echo ( "<input type=\"hidden\" name=\"direction\" value=\"" . $_GET["direction"] . "\" />" );
				echo ( "<input type=\"hidden\" name=\"mode\" value=\"" . $_GET["mode"] . "\" />" );

				
				echo ( "<div class=\"confirm-message\">" );
					echo ( "Are you sure you wish to run ClamAV on these machines?" );
						echo ( "<div class=\"advanced-settings\">" );
							echo ( "<h2>"._("Advanced Settings")."</h2>" );
							echo ( "<input type=\"checkbox\" name=\"shutdown\"> "._("Shutdown after task completion?")."<br />" );
							echo ( "<input type=\"checkbox\" name=\"q\" value=\"1\"> Quarantine?" );
							echo ( "<br /><input type=\"checkbox\" name=\"singlesched\" id=\"singlesched\"> "._("Schedule Single Task Execution?") );
							echo ( "<p class=\"scheduleTask\">" );
								echo ( "<input type=\"text\" name=\"singlescheddate\" id=\"singlescheddatetime\" onFocus=\"document.getElementById('singlesched').checked='checked';\" />" );
							echo ( "</p>" );
							echo ( "<input type=\"checkbox\" name=\"cronsched\" id=\"cronsched\"> Schedule Cron Style Task Execution?" );
							echo ( "<p class=\"scheduleTask\">" );
								echo ( "<input type=\"text\" name=\"cronMin\" id=\"cronMin\" size=\"5\" value=\"min\" onFocus=\"clearIf( this, 'min' );document.getElementById('cronsched').checked='checked';\" /> <input type=\"text\" name=\"cronHour\" id=\"cronHour\" size=\"5\" value=\"hour\" onFocus=\"clearIf( this, 'hour' );document.getElementById('cronsched').checked='checked';\" /> <input type=\"text\" name=\"cronDOM\" id=\"cronDOM\" size=\"5\" value=\"dom\" onFocus=\"clearIf( this, 'dom' );document.getElementById('cronsched').checked='checked';\" /> <input type=\"text\" name=\"cronMonth\" id=\"cronMonth\" size=\"5\" value=\"month\" onFocus=\"clearIf( this, 'month' );document.getElementById('cronsched').checked='checked';\" /> <input type=\"text\" name=\"cronDOW\" id=\"cronDOW\" size=\"5\" value=\"dow\" onFocus=\"clearIf( this, 'dow' );document.getElementById('cronsched').checked='checked';\" />" );
							echo ( "</p>" );							
						echo ( "</div>" );					
				echo ( "</div>" );
				echo ( "<table width=\"98%\" cellspacing=\"0\" cellpadding=0 border=0>" );
				for( $i = 0; $i < count( $imageMembers ); $i++ )
				{
					echo ( "<tr><td>&nbsp;" . $imageMembers[$i]->getHostName() . "</td><td><font class=\"smaller\">" . $imageMembers[$i]->get('mac') . "</font></td><td><font class=\"smaller\">" . $imageMembers[$i]->getIPAddress() . "</font></td><td><font class=\"smaller\">" . $imageMembers[$i]->getImage() . "</font></td></tr>" );
				}
				
				echo ( "<tr><td colspan=10><center><br /><input class=\"smaller\" type=\"submit\" value=\"Scan Host\"  /></center></td></tr>" );
				echo ( "</table>" );
			echo ( "</form>" );
		}
		else if ( $_GET["direction"] == "debug" )
		{
			// general debugging environment
			$imageMembers = null;
			if ( $_GET["type"] == "host" )
			{
				$imageMembers = array( getImageMemberFromHostID( $conn, $noconfirm ) );
			}
			else if ( $_GET["type"] == "group" )
			{
				$imageMembers = getImageMembersByGroupID( $conn, $noconfirm );
			}

			echo ( "<form method=\"get\" action=\"?\">" );
				echo ( "<input type=\"hidden\" name=\"node\" value=\"" . $_GET["node"] . "\" />" );
				echo ( "<input type=\"hidden\" name=\"confirm\" value=\"" . $noconfirm . "\" />" );												
				echo ( "<input type=\"hidden\" name=\"type\" value=\"" . $_GET["type"] . "\" />" );
				echo ( "<input type=\"hidden\" name=\"direction\" value=\"" . $_GET["direction"] . "\" />" );				
				echo ( "<input type=\"hidden\" name=\"mode\" value=\"" . $_GET["mode"] . "\" />" );
				
				echo ( "<div class=\"confirm-message\">" );
					echo ( _("Are you sure you wish to debug these machines?") );
				echo ( "</div>" );
				echo ( "<table width=\"98%\" cellspacing=\"0\" cellpadding=0 border=0>" );
				for( $i = 0; $i < count( $imageMembers ); $i++ )
				{
					echo ( "<tr><td>&nbsp;" . $imageMembers[$i]->getHostName() . "</td><td><font class=\"smaller\">" . $imageMembers[$i]->get('mac') . "</font></td><td><font class=\"smaller\">" . $imageMembers[$i]->getIPAddress() . "</font></td><td><font class=\"smaller\">" . $imageMembers[$i]->getImage() . "</font></td></tr>" );
				}
				echo ( "<tr><td colspan=10><center><br /><input class=\"smaller\" type=\"submit\" value=\""._("Debug computer(s)")."\"  /></center></td></tr>" );
				echo ( "</table>" );
			echo ( "</form>" );
		}
		else if ( $_GET["direction"] == "memtest" )
		{
			// memtest86+
			$imageMembers = null;
			if ( $_GET["type"] == "host" )
			{
				$imageMembers = array( getImageMemberFromHostID( $conn, $noconfirm ) );
			}
			else if ( $_GET["type"] == "group" )
			{
				$imageMembers = getImageMembersByGroupID( $conn, $noconfirm );
			}

			echo ( "<form method=\"get\" action=\"?\">" );
				echo ( "<input type=\"hidden\" name=\"node\" value=\"" . $_GET["node"] . "\" />" );
				echo ( "<input type=\"hidden\" name=\"confirm\" value=\"" . $noconfirm . "\" />" );												
				echo ( "<input type=\"hidden\" name=\"type\" value=\"" . $_GET["type"] . "\" />" );
				echo ( "<input type=\"hidden\" name=\"direction\" value=\"" . $_GET["direction"] . "\" />" );				
				echo ( "<input type=\"hidden\" name=\"mode\" value=\"" . $_GET["mode"] . "\" />" );				
				echo ( "<div class=\"confirm-message\">" );
					echo ( _("Are you sure you wish to run memtest86+ on these machines?") );
				echo ( "</div>" );
				echo ( "<table width=\"98%\" cellspacing=\"0\" cellpadding=0 border=0>" );
				for( $i = 0; $i < count( $imageMembers ); $i++ )
				{
					echo ( "<tr><td>&nbsp;" . $imageMembers[$i]->getHostName() . "</td><td><font class=\"smaller\">" . $imageMembers[$i]->get('mac') . "</font></td><td><font class=\"smaller\">" . $imageMembers[$i]->getIPAddress() . "</font></td><td><font class=\"smaller\">" . $imageMembers[$i]->getImage() . "</font></td></tr>" );
				}
				echo ( "<tr><td colspan=10><center><br /><input class=\"smaller\" type=\"submit\" value=\""._("Run memtest86+")."\"  /></center></td></tr>" );
				echo ( "</table>" );
			echo ( "</form>" );
		}
		else if ( $_GET["direction"] == "testdisk" )
		{
			// testdisk
			$imageMembers = null;
			if ( $_GET["type"] == "host" )
			{
				$imageMembers = array( getImageMemberFromHostID( $conn, $noconfirm ) );
			}
			else if ( $_GET["type"] == "group" )
			{
				$imageMembers = getImageMembersByGroupID( $conn, $noconfirm );
			}

			echo ( "<form method=\"get\" action=\"?\">" );
				echo ( "<input type=\"hidden\" name=\"node\" value=\"" . $_GET["node"] . "\" />" );
				echo ( "<input type=\"hidden\" name=\"confirm\" value=\"" . $noconfirm . "\" />" );												
				echo ( "<input type=\"hidden\" name=\"type\" value=\"" . $_GET["type"] . "\" />" );
				echo ( "<input type=\"hidden\" name=\"direction\" value=\"" . $_GET["direction"] . "\" />" );							
				echo ( "<input type=\"hidden\" name=\"mode\" value=\"" . $_GET["mode"] . "\" />" );
				
				echo ( "<div class=\"confirm-message\">" );
					echo ( _("Are you sure you wish to run testdisk on these machines?") );
						echo ( "<div class=\"advanced-settings\">" );
							echo ( "<h2>"._("Advanced Settings")."</h2>" );
							echo ( "<input type=\"checkbox\" name=\"shutdown\"> Shutdown after task completion?" );
						echo ( "</div>" );					
				echo ( "</div>" );
				echo ( "<table width=\"98%\" cellspacing=\"0\" cellpadding=0 border=0>" );
				for( $i = 0; $i < count( $imageMembers ); $i++ )
				{
					echo ( "<tr><td>&nbsp;" . $imageMembers[$i]->getHostName() . "</td><td><font class=\"smaller\">" . $imageMembers[$i]->get('mac') . "</font></td><td><font class=\"smaller\">" . $imageMembers[$i]->getIPAddress() . "</font></td><td><font class=\"smaller\">" . $imageMembers[$i]->getImage() . "</font></td></tr>" );
				}
				echo ( "<tr><td colspan=10><center><br /><input class=\"smaller\" type=\"submit\" value=\""._("Run testdisk")."\"  /></center></td></tr>" );
				echo ( "</table>" );
			echo ( "</form>" );
		}
		else if ( $_GET["direction"] == "photorec" )
		{
			// photorec
			$imageMembers = null;
			if ( $_GET["type"] == "host" )
			{
				$imageMembers = array( getImageMemberFromHostID( $conn, $noconfirm ) );
			}
			else if ( $_GET["type"] == "group" )
			{
				$imageMembers = getImageMembersByGroupID( $conn, $noconfirm );
			}

			echo ( "<form method=\"get\" action=\"?\">" );
				echo ( "<input type=\"hidden\" name=\"node\" value=\"" . $_GET["node"] . "\" />" );
				echo ( "<input type=\"hidden\" name=\"confirm\" value=\"" . $noconfirm . "\" />" );												
				echo ( "<input type=\"hidden\" name=\"type\" value=\"" . $_GET["type"] . "\" />" );
				echo ( "<input type=\"hidden\" name=\"direction\" value=\"" . $_GET["direction"] . "\" />" );							
				echo ( "<input type=\"hidden\" name=\"mode\" value=\"" . $_GET["mode"] . "\" />" );
												
				echo ( "<div class=\"confirm-message\">" );
					echo ( _("Are you sure you wish to run file recovery on these machines?") );
						echo ( "<div class=\"advanced-settings\">" );
			echo ( "<h2>"._("Advanced Settings")."</h2>" );
							echo ( "<input type=\"checkbox\" name=\"shutdown\"> "._("Shutdown after task completion?") );
						echo ( "</div>" );					
				echo ( "</div>" );
				echo ( "<table width=\"98%\" cellspacing=\"0\" cellpadding=0 border=0>" );
				for( $i = 0; $i < count( $imageMembers ); $i++ )
				{
					echo ( "<tr><td>&nbsp;" . $imageMembers[$i]->getHostName() . "</td><td><font class=\"smaller\">" . $imageMembers[$i]->get('mac') . "</font></td><td><font class=\"smaller\">" . $imageMembers[$i]->getIPAddress() . "</font></td><td><font class=\"smaller\">" . $imageMembers[$i]->getImage() . "</font></td></tr>" );
				}
				echo ( "<tr><td colspan=10><center><br /><input class=\"smaller\" type=\"submit\" value=\""._("Run File Recovery")."\" /></center></td></tr>" );
				echo ( "</table>" );
			echo ( "</form>" );
		}
		else if ( $_GET["direction"] == "winpassreset" )
		{
			// windows password reset
			$imageMembers = null;
			if ( $_GET["type"] == "host" )
			{
				$imageMembers = array( getImageMemberFromHostID( $conn, $noconfirm ) );
			}
			else if ( $_GET["type"] == "group" )
			{
				$imageMembers = getImageMembersByGroupID( $conn, $noconfirm );
			}

			echo ( "<form method=\"get\" action=\"?\">" );
				echo ( "<input type=\"hidden\" name=\"node\" value=\"" . $_GET["node"] . "\" />" );
				echo ( "<input type=\"hidden\" name=\"confirm\" value=\"" . $noconfirm . "\" />" );												
				echo ( "<input type=\"hidden\" name=\"type\" value=\"" . $_GET["type"] . "\" />" );
				echo ( "<input type=\"hidden\" name=\"direction\" value=\"" . $_GET["direction"] . "\" />" );							
				echo ( "<input type=\"hidden\" name=\"mode\" value=\"" . $_GET["mode"] . "\" />" );
												
				echo ( "<div class=\"confirm-message\">" );
					echo ( _("Are you sure you wish to reset the password on these machines?") );
						echo ( "<div class=\"advanced-settings\">" );
							echo ( "<h2>"._("Advanced Settings")."</h2>" );
							echo ( "<input type=\"checkbox\" name=\"shutdown\" /> "._("Shutdown after task completion?") );
							echo ( "<p>"._("Which account would you like to reset the password for?")." <br /><input type=\"text\" name=\"account\" value=\"Administrator\" /></p>" );
						echo ( "</div>" );					
				echo ( "</div>" );
				echo ( "<table width=\"98%\" cellspacing=\"0\" cellpadding=0 border=0>" );
				for( $i = 0; $i < count( $imageMembers ); $i++ )
				{
					echo ( "<tr><td>&nbsp;" . $imageMembers[$i]->getHostName() . "</td><td><font class=\"smaller\">" . $imageMembers[$i]->get('mac') . "</font></td><td><font class=\"smaller\">" . $imageMembers[$i]->getIPAddress() . "</font></td><td><font class=\"smaller\">" . $imageMembers[$i]->getImage() . "</font></td></tr>" );
				}
				echo ( "<tr><td colspan=10><center><br /><input class=\"smaller\" type=\"submit\" value=\""._("Reset Password")."\" /></center></td></tr>" );
				echo ( "</table>" );
			echo ( "</form>" );
		}		
		else if ( $_GET["direction"] == "surfacetest" )
		{
			// badblocks
			$imageMembers = null;
			if ( $_GET["type"] == "host" )
			{
				$imageMembers = array( getImageMemberFromHostID( $conn, $noconfirm ) );
			}
			else if ( $_GET["type"] == "group" )
			{
				$imageMembers = getImageMembersByGroupID( $conn, $noconfirm );
			}

			echo ( "<form method=\"get\" action=\"?\">" );
				echo ( "<input type=\"hidden\" name=\"node\" value=\"" . $_GET["node"] . "\" />" );
				echo ( "<input type=\"hidden\" name=\"confirm\" value=\"" . $noconfirm . "\" />" );												
				echo ( "<input type=\"hidden\" name=\"type\" value=\"" . $_GET["type"] . "\" />" );
				echo ( "<input type=\"hidden\" name=\"direction\" value=\"" . $_GET["direction"] . "\" />" );							
				echo ( "<input type=\"hidden\" name=\"mode\" value=\"" . $_GET["mode"] . "\" />" );
												
				echo ( "<div class=\"confirm-message\">" );
					echo ( _("Are you sure you wish to run Disk Surface Test on these machines?") );
						echo ( "<div class=\"advanced-settings\">" );
							echo ( "<h2>"._("Advanced Settings")."</h2>" );
							echo ( "<input type=\"checkbox\" name=\"shutdown\"> "._("Shutdown after task completion?") );
							echo ( "<br /><input type=\"checkbox\" name=\"singlesched\" id=\"singlesched\"> "._("Schedule Single Task Execution?") );
							echo ( "<p class=\"scheduleTask\">" );
								echo ( "<input type=\"text\" name=\"singlescheddate\" id=\"singlescheddatetime\" onFocus=\"document.getElementById('singlesched').checked='checked';\" />" );
							echo ( "</p>" );
							echo ( "<input type=\"checkbox\" name=\"cronsched\" id=\"cronsched\"> "._("Schedule Cron Style Task Execution?") );
							echo ( "<p class=\"scheduleTask\">" );
								echo ( "<input type=\"text\" name=\"cronMin\" id=\"cronMin\" size=\"5\" value=\"min\" onFocus=\"clearIf( this, 'min' );document.getElementById('cronsched').checked='checked';\" /> <input type=\"text\" name=\"cronHour\" id=\"cronHour\" size=\"5\" value=\"hour\" onFocus=\"clearIf( this, 'hour' );document.getElementById('cronsched').checked='checked';\" /> <input type=\"text\" name=\"cronDOM\" id=\"cronDOM\" size=\"5\" value=\"dom\" onFocus=\"clearIf( this, 'dom' );document.getElementById('cronsched').checked='checked';\" /> <input type=\"text\" name=\"cronMonth\" id=\"cronMonth\" size=\"5\" value=\"month\" onFocus=\"clearIf( this, 'month' );document.getElementById('cronsched').checked='checked';\" /> <input type=\"text\" name=\"cronDOW\" id=\"cronDOW\" size=\"5\" value=\"dow\" onFocus=\"clearIf( this, 'dow' );document.getElementById('cronsched').checked='checked';\" />" );
							echo ( "</p>" );
						echo ( "</div>" );					
				echo ( "</div>" );
				echo ( "<table width=\"98%\" cellspacing=\"0\" cellpadding=0 border=0>" );
				for( $i = 0; $i < count( $imageMembers ); $i++ )
				{
					echo ( "<tr><td>&nbsp;" . $imageMembers[$i]->getHostName() . "</td><td><font class=\"smaller\">" . $imageMembers[$i]->get('mac') . "</font></td><td><font class=\"smaller\">" . $imageMembers[$i]->getIPAddress() . "</font></td><td><font class=\"smaller\">" . $imageMembers[$i]->getImage() . "</font></td></tr>" );
				}
				echo ( "<tr><td colspan=10><center><br /><input class=\"smaller\" type=\"submit\" value=\""._("Run Surface Test")."\"  /></center></td></tr>" );
				echo ( "</table>" );
			echo ( "</form>" );
		}
		else if ( $_GET["direction"] == "allsnaps" )
		{
			$imageMembers = null;
			if ( $_GET["type"] == "host" )
			{
				$imageMembers = array( getImageMemberFromHostID( $conn, $noconfirm ) );
			}
			else if ( $_GET["type"] == "group" )
			{
				$imageMembers = getImageMembersByGroupID( $conn, $noconfirm );
			}

			echo ( "<form method=\"get\" action=\"?\">" );		
				echo ( "<input type=\"hidden\" name=\"node\" value=\"" . $_GET["node"] . "\" />" );
				echo ( "<input type=\"hidden\" name=\"confirm\" value=\"" . $noconfirm . "\" />" );												
				echo ( "<input type=\"hidden\" name=\"type\" value=\"" . $_GET["type"] . "\" />" );
				echo ( "<input type=\"hidden\" name=\"direction\" value=\"" . $_GET["direction"] . "\" />" );				
				echo ( "<input type=\"hidden\" name=\"mode\" value=\"" . $_GET["mode"] . "\" />" );
				
				echo ( "<div class=\"confirm-message\">" );
					echo ( _("Are you sure you wish to deploy all linked snapins these machines?") );
					echo ( "<div class=\"advanced-settings\">" );
						echo ( "<h2>"._("Advanced Settings")."</h2>" );

						echo ( "<input type=\"checkbox\" name=\"singlesched\" id=\"singlesched\"> "._("Schedule Single Task Execution?") );
						echo ( "<p class=\"scheduleTask\">" );
							echo ( "<input type=\"text\" name=\"singlescheddate\" id=\"singlescheddatetime\" onFocus=\"document.getElementById('singlesched').checked='checked';\" />" );
						echo ( "</p>" );
						echo ( "<input type=\"checkbox\" name=\"cronsched\" id=\"cronsched\"> "._("Schedule Cron Style Task Execution?") );
						echo ( "<p class=\"scheduleTask\">" );
							echo ( "<input type=\"text\" name=\"cronMin\" id=\"cronMin\" size=\"5\" value=\"min\" onFocus=\"clearIf( this, 'min' );document.getElementById('cronsched').checked='checked';\" /> <input type=\"text\" name=\"cronHour\" id=\"cronHour\" size=\"5\" value=\"hour\" onFocus=\"clearIf( this, 'hour' );document.getElementById('cronsched').checked='checked';\" /> <input type=\"text\" name=\"cronDOM\" id=\"cronDOM\" size=\"5\" value=\"dom\" onFocus=\"clearIf( this, 'dom' );document.getElementById('cronsched').checked='checked';\" /> <input type=\"text\" name=\"cronMonth\" id=\"cronMonth\" size=\"5\" value=\"month\" onFocus=\"clearIf( this, 'month' );document.getElementById('cronsched').checked='checked';\" /> <input type=\"text\" name=\"cronDOW\" id=\"cronDOW\" size=\"5\" value=\"dow\" onFocus=\"clearIf( this, 'dow' );document.getElementById('cronsched').checked='checked';\" />" );
						echo ( "</p>" );
					echo ( "</div>" );			
				echo ( "</div>" );
				echo ( "<table width=\"98%\" cellspacing=\"0\" cellpadding=0 border=0>" );
				for( $i = 0; $i < count( $imageMembers ); $i++ )
				{
					echo ( "<tr><td>&nbsp;" . $imageMembers[$i]->getHostName() . "</td><td><font class=\"smaller\">" . $imageMembers[$i]->get('mac') . "</font></td><td><font class=\"smaller\">" . $imageMembers[$i]->getIPAddress() . "</font></td><td><font class=\"smaller\">" . $imageMembers[$i]->getImage() . "</font></td></tr>" );
				}
				echo ( "<tr><td colspan=10><center><br /><input class=\"smaller\" type=\"submit\" value=\""._("Deploy Snapins")."\"  /></center></td></tr>" );
				echo ( "</table>" );
			echo ( "</form>" );
		}
		else if ( $_GET["direction"] == "downnosnap" )
		{
			$imageMembers = null;
			if ( $_GET["type"] == "host" )
			{
				$imageMembers = array( getImageMemberFromHostID( $conn, $noconfirm ) );
			}
			else if ( $_GET["type"] == "group" )
			{
				$imageMembers = getImageMembersByGroupID( $conn, $noconfirm );
			}

			if ( count( $imageMembers ) > 0 )
			{
				echo ( "<form method=\"get\" action=\"?\">" );
					echo ( "<input type=\"hidden\" name=\"node\" value=\"" . $_GET["node"] . "\" />" );
					echo ( "<input type=\"hidden\" name=\"sub\" value=\"" . $_GET["sub"] . "\" />" );
					echo ( "<input type=\"hidden\" name=\"debug\" value=\"" . $_GET["debug"] . "\" />" );
					echo ( "<input type=\"hidden\" name=\"confirm\" value=\"" . $noconfirm . "\" />" );
					echo ( "<input type=\"hidden\" name=\"type\" value=\"" . $_GET["type"] . "\" />" );
					echo ( "<input type=\"hidden\" name=\"direction\" value=\"" . $_GET["direction"] . "\" />" );									
					
					echo ( "<div class=\"confirm-message\">" );
						echo ( _("Are you sure you wish to image these machines (without snapins)?") );
							echo ( "<div class=\"advanced-settings\">" );
								echo ( "<h2>"._("Advanced Settings")."</h2>" );
								echo ( "<input type=\"checkbox\" name=\"shutdown\"> "._("Shutdown after task completion?") );
								echo ( "<br />" );
								echo ( "<input type=\"checkbox\" name=\"singlesched\" id=\"singlesched\"> "._("Schedule Single Task Execution?") );
								echo ( "<p class=\"scheduleTask\">" );
									echo ( "<input type=\"text\" name=\"singlescheddate\" id=\"singlescheddatetime\" onFocus=\"document.getElementById('singlesched').checked='checked';\" />" );
								echo ( "</p>" );
								echo ( "<input type=\"checkbox\" name=\"cronsched\" id=\"cronsched\"> "._("Schedule Cron Style Task Execution?") );
								echo ( "<p class=\"scheduleTask\">" );
									echo ( "<input type=\"text\" name=\"cronMin\" id=\"cronMin\" size=\"5\" value=\"min\" onFocus=\"clearIf( this, 'min' );document.getElementById('cronsched').checked='checked';\" /> <input type=\"text\" name=\"cronHour\" id=\"cronHour\" size=\"5\" value=\"hour\" onFocus=\"clearIf( this, 'hour' );document.getElementById('cronsched').checked='checked';\" /> <input type=\"text\" name=\"cronDOM\" id=\"cronDOM\" size=\"5\" value=\"dom\" onFocus=\"clearIf( this, 'dom' );document.getElementById('cronsched').checked='checked';\" /> <input type=\"text\" name=\"cronMonth\" id=\"cronMonth\" size=\"5\" value=\"month\" onFocus=\"clearIf( this, 'month' );document.getElementById('cronsched').checked='checked';\" /> <input type=\"text\" name=\"cronDOW\" id=\"cronDOW\" size=\"5\" value=\"dow\" onFocus=\"clearIf( this, 'dow' );document.getElementById('cronsched').checked='checked';\" />" );
								echo ( "</p>" );
							echo ( "</div>" );						
					echo ( "</div>" );
					echo ( "<table width=\"98%\" cellspacing=\"0\" cellpadding=0 border=0>" );
					for( $i = 0; $i < count( $imageMembers ); $i++ )
					{
						echo ( "<tr><td><font class=\"smaller\">" . $imageMembers[$i]->getHostName() . "</font></td><td><font class=\"smaller\">" . $imageMembers[$i]->get('mac') . "</font></td><td><font class=\"smaller\">" . $imageMembers[$i]->getIPAddress() . "</font></td><td><font class=\"smaller\">" . $imageMembers[$i]->getImage() . "</font></td></tr>" );
					}
					echo ( "<tr><td colspan=10><center><br /><input class=\"smaller\" type=\"submit\" value=\""._("Image All Computers")."\"  /></center></td></tr>" );
					echo ( "</table>" );
				echo ( "</form>" );

			}
			else
			{
				echo ( _("No Host are members of this group") );
			}
		}
		else if ( $_GET["direction"] == "onesnap" )
		{
			$imageMembers = null;
			if ( $_GET["type"] == "host" )
			{
				$imageMembers = array( getImageMemberFromHostID( $conn, $noconfirm ) );
			}
			else if ( $_GET["type"] == "group" )
			{
				$imageMembers = getImageMembersByGroupID( $conn, $noconfirm );
			}

			if ( count( $imageMembers ) > 0 )
			{
				echo ( "<form method=\"get\" action=\"index.php\" />" );

				echo ( "<input type=\"hidden\" name=\"node\" value=\"" . $_GET["node"] . "\" />" );
				echo ( "<input type=\"hidden\" name=\"sub\" value=\"" . $_GET["sub"] . "\" />" );
				echo ( "<input type=\"hidden\" name=\"confirm\" value=\"" . $noconfirm . "\" />" );
				echo ( "<input type=\"hidden\" name=\"type\" value=\"" . $_GET["type"] . "\" />" );
				echo ( "<input type=\"hidden\" name=\"direction\" value=\"" . $_GET["direction"] . "\" />" );
				echo ( "<div class=\"confirm-message\">" );
					echo ( "<p class=\"confirm-message\">"._("Which snapin would you like to deployed to the machines listed below?")."<br /><br />" . $FOGCore->getClass('SnapinManager')->buildSelectBox() . "<br /><br /></p>" );
					echo ( "<div class=\"advanced-settings\">" );
						echo ( "<h2>"._("Advanced Settings")."</h2>" );
						echo ( "<input type=\"checkbox\" name=\"singlesched\" id=\"singlesched\"> "._("Schedule Single Task Execution?") );
						echo ( "<p class=\"scheduleTask\">" );
							echo ( "<input type=\"text\" name=\"singlescheddate\" id=\"singlescheddatetime\" onFocus=\"document.getElementById('singlesched').checked='checked';\" />" );
						echo ( "</p>" );
						echo ( "<input type=\"checkbox\" name=\"cronsched\" id=\"cronsched\"> "._("Schedule Cron Style Task Execution?") );
						echo ( "<p class=\"scheduleTask\">" );
							echo ( "<input type=\"text\" name=\"cronMin\" id=\"cronMin\" size=\"5\" value=\"min\" onFocus=\"clearIf( this, 'min' );document.getElementById('cronsched').checked='checked';\" /> <input type=\"text\" name=\"cronHour\" id=\"cronHour\" size=\"5\" value=\"hour\" onFocus=\"clearIf( this, 'hour' );document.getElementById('cronsched').checked='checked';\" /> <input type=\"text\" name=\"cronDOM\" id=\"cronDOM\" size=\"5\" value=\"dom\" onFocus=\"clearIf( this, 'dom' );document.getElementById('cronsched').checked='checked';\" /> <input type=\"text\" name=\"cronMonth\" id=\"cronMonth\" size=\"5\" value=\"month\" onFocus=\"clearIf( this, 'month' );document.getElementById('cronsched').checked='checked';\" /> <input type=\"text\" name=\"cronDOW\" id=\"cronDOW\" size=\"5\" value=\"dow\" onFocus=\"clearIf( this, 'dow' );document.getElementById('cronsched').checked='checked';\" />" );
						echo ( "</p>" );
					echo ( "</div>" );
				echo ( "</div>" );
				
				echo ( "<table width=\"98%\" cellspacing=\"0\" cellpadding=0 border=0>" );
				for( $i = 0; $i < count( $imageMembers ); $i++ )
				{
					echo ( "<tr><td><font class=\"smaller\">" . $imageMembers[$i]->getHostName() . "</font></td><td><font class=\"smaller\">" . $imageMembers[$i]->get('mac') . "</font></td><td><font class=\"smaller\">" . $imageMembers[$i]->getIPAddress() . "</font></td><td><font class=\"smaller\">" . $imageMembers[$i]->getImage() . "</font></td></tr>" );
				}
				echo ( "<tr><td colspan=10><center><br /><input class=\"smaller\" type=\"submit\" value=\""._("Deploy Snapin")."\" /></center></td></tr>" );
				echo ( "</table>" );
				echo ( "</form>" );

			}
			else
			{
				echo ( _("No Host are members of this group") );
			}
		}
		else if ( $_GET["direction"] == "inventory" )
		{
			$imageMembers = null;
			if ( $_GET["type"] == "host" )
			{
				$imageMembers = array( getImageMemberFromHostID( $conn, $noconfirm ) );
			}
			else if ( $_GET["type"] == "group" )
			{
				$imageMembers = getImageMembersByGroupID( $conn, $noconfirm );
			}

			echo ( "<form method=\"get\" action=\"?\">" );
				echo ( "<input type=\"hidden\" name=\"node\" value=\"" . $_GET["node"] . "\" />" );
				echo ( "<input type=\"hidden\" name=\"confirm\" value=\"" . $noconfirm . "\" />" );												
				echo ( "<input type=\"hidden\" name=\"type\" value=\"" . $_GET["type"] . "\" />" );
				echo ( "<input type=\"hidden\" name=\"direction\" value=\"" . $_GET["direction"] . "\" />" );								

				echo ( "<div class=\"confirm-message\">" );
					echo ( _("Are you sure you wish to update/take an inventory of these machines?") );
						echo ( "<div class=\"advanced-settings\">" );
							echo ( "<h2>"._("Advanced Settings")."</h2>" );
							echo ( "<input type=\"checkbox\" name=\"shutdown\"> "._("Shutdown after task completion?") );
							echo ( "<br /><input type=\"checkbox\" name=\"singlesched\" id=\"singlesched\"> "._("Schedule Single Task Execution?") );
							echo ( "<p class=\"scheduleTask\">" );
								echo ( "<input type=\"text\" name=\"singlescheddate\" id=\"singlescheddatetime\" onFocus=\"document.getElementById('singlesched').checked='checked';\" />" );
							echo ( "</p>" );
							echo ( "<input type=\"checkbox\" name=\"cronsched\" id=\"cronsched\"> "._("Schedule Cron Style Task Execution?") );
							echo ( "<p class=\"scheduleTask\">" );
								echo ( "<input type=\"text\" name=\"cronMin\" id=\"cronMin\" size=\"5\" value=\"min\" onFocus=\"clearIf( this, 'min' );document.getElementById('cronsched').checked='checked';\" /> <input type=\"text\" name=\"cronHour\" id=\"cronHour\" size=\"5\" value=\"hour\" onFocus=\"clearIf( this, 'hour' );document.getElementById('cronsched').checked='checked';\" /> <input type=\"text\" name=\"cronDOM\" id=\"cronDOM\" size=\"5\" value=\"dom\" onFocus=\"clearIf( this, 'dom' );document.getElementById('cronsched').checked='checked';\" /> <input type=\"text\" name=\"cronMonth\" id=\"cronMonth\" size=\"5\" value=\"month\" onFocus=\"clearIf( this, 'month' );document.getElementById('cronsched').checked='checked';\" /> <input type=\"text\" name=\"cronDOW\" id=\"cronDOW\" size=\"5\" value=\"dow\" onFocus=\"clearIf( this, 'dow' );document.getElementById('cronsched').checked='checked';\" />" );
							echo ( "</p>" );
						echo ( "</div>" );					
				echo ( "</div>" );
				echo ( "<table width=\"98%\" cellspacing=\"0\" cellpadding=0 border=0>" );
				for( $i = 0; $i < count( $imageMembers ); $i++ )
				{
					echo ( "<tr><td>&nbsp;" . $imageMembers[$i]->getHostName() . "</td><td><font class=\"smaller\">" . $imageMembers[$i]->get('mac') . "</font></td><td><font class=\"smaller\">" . $imageMembers[$i]->getIPAddress() . "</font></td><td><font class=\"smaller\">" . $imageMembers[$i]->getImage() . "</font></td></tr>" );
				}
				echo ( "<tr><td colspan=10><center><br /><input class=\"smaller\" type=\"submit\" value=\""._("Run Inventory")."\"  /></center></td></tr>" );
				echo ( "</table>" );
			echo ( "</form>" );
		}


	}
	else if ( $_GET["confirm"] != null )
	{
		$confirm = trim( mysql_real_escape_string($_GET["confirm"]) );
		$shutdown = trim( mysql_real_escape_string( $_GET["shutdown"] ) );
		if ( $_GET["direction"] == "up" )
		{
			$imageMembers = getImageMemberFromHostID( $conn, $confirm );
			if ( $imageMembers != null )
			{
				$reason = "";
				$other = "fdrive=" . $imageMembers->getDevice();
				$other .= (" chkdsk=" . ($FOGCore->getSetting("FOG_DISABLE_CHKDSK") == "1" ? '0' : '1'));
				
				$blSchedSingle = false;
				$blSchedCron = false;
			
				if ( $_GET["singlesched"] == "on" ) $blSchedSingle = true;
				if ( $_GET["cronsched"] == "on" ) $blSchedCron = true;
				
				if ( $blSchedSingle )
				{
					if ( ! $blSchedCron )
					{
						$tmp = "";
						$lngTime = strtotime( $_GET["singlescheddate"] );
						if(createSingleRunScheduledPackage( $conn, false, $confirm, "U", $lngTime, $shutdown == "on", true, &$tmp )  )
							$suc++;
						else
						{
							$output .= $tmp . "<br />";
							$fail++;
						}
					
						if ( $suc == 1 )
							echo ( "<div class=\"task-start-ok\"><p>"._("The task has been scheduled for ") . date( "r",  $lngTime ) . "!</p></div>" );
						else
							echo "<div class=\"task-start-failed\"><p>"._("Failed to schedule task.")."</p><p>$output</p></div>";
					}
					else
						echo ( "<div class=\"task-start-failed\"><p>"._("You can not select a cron style schedule and a single schedule at the same time.")."</p></div>" );
				}
				else if ( $blSchedCron )
				{
					if ( ! $blSchedSingle )
					{
						 $tmp = "";
					
						 $m = mysql_real_escape_string( $_GET["cronMin"] );
						 $h = mysql_real_escape_string( $_GET["cronHour"] );
						 $dom = mysql_real_escape_string( $_GET["cronDOM"] ); 
						 $mon = mysql_real_escape_string( $_GET["cronMonth"] );
						 $dow = mysql_real_escape_string( $_GET["cronDOW"] );
						 if ( createCronScheduledPackage( $conn, false, $confirm, "U", $m, $h, $dom, $mon, $dow, $shutdown == "on", true, &$tmp ) )
						 {	
						 	$suc++;
						 }
						 else
						 {
						 	$output .= $tmp."<br />";
						 	$fail++;
						 }
						 
						if ( $suc == 1 )
						{
							echo ( "<div class=\"task-start-ok\"><p>"._("The cron task has been scheduled!")."</p></div>" );
						}
						else
						{
							echo "<div class=\"task-start-failed\"><p>"._("Failed to schedule task.")."</p><p>$output</p></div>";
						}
					}
					else
					{
						echo ( "<div class=\"task-start-failed\"><p>"._("You can not select a cron style schedule and a single schedule at the same time.")."</p></div>" );
					}
				}
				else
				{				
					if ( createUploadImagePackage( $conn, $imageMembers, $reason, ($_GET["debug"] == "true" ), $shutdown, $imageMembers->getKernel(), $other ) )
					{
						echo ( "<div class=\"task-start-ok\"><p>"._("Task Started!")."</p></div>" );
					}
					else
					{
						echo ( "<div class=\"task-start-failed\"><p>"._("Unable to start task")."</p><p>$reason</p></div>" );
					}
				}
			}
			else
			{
				msgBox( _("Error:  Is an image associated with the computer?") );
			}
		}
		else if ( $_GET["direction"] == "down" )
		{
			$imageMembers = null;
			$taskName = "";
			$blIsGroup = false;
			if ( $_GET["type"] == "host" )
			{
				$imageMembers = array( getImageMemberFromHostID( $conn, $confirm ) );
			}
			else if ( $_GET["type"] == "group" )
			{
				$blIsGroup = true;
				$imageMembers = getImageMembersByGroupID( $conn, $confirm );
				$taskName = getGroupNameByID( $conn, $confirm );
			}

			$output = "";
			$suc = 0;
			$fail = 0;

			$blSchedSingle = false;
			$blSchedCron = false;
			
			if ( $_GET["singlesched"] == "on" ) $blSchedSingle = true;
			if ( $_GET["cronsched"] == "on" ) $blSchedCron = true;

			if ( $blSchedSingle )
			{
				if ( ! $blSchedCron )
				{
					$tmp = "";
					$lngTime = strtotime( $_GET["singlescheddate"] );
					if(createSingleRunScheduledPackage( $conn, $blIsGroup, $confirm, "D", $lngTime, $shutdown == "on", true, &$tmp )  )
						$suc++;
					else
					{
						$output .= $tmp . "<br />";
						$fail++;
					}
					
					if ( $suc == 1 )
						echo ( "<div class=\"task-start-ok\"><p>"._("The task has been scheduled for ") . date( "r",  $lngTime ) . "!</p></div>" );
					else
						echo "<div class=\"task-start-failed\"><p>"._("Failed to schedule task.")."</p><p>$output</p></div>";
				}
				else
					echo ( "<div class=\"task-start-failed\"><p>"._("You can not select a cron style schedule and a single schedule at the same time.")."</p></div>" );
			}
			else if ( $blSchedCron )
			{
				if ( ! $blSchedSingle )
				{
					 $tmp = "";
					
					 $m = mysql_real_escape_string( $_GET["cronMin"] );
					 $h = mysql_real_escape_string( $_GET["cronHour"] );
					 $dom = mysql_real_escape_string( $_GET["cronDOM"] ); 
					 $mon = mysql_real_escape_string( $_GET["cronMonth"] );
					 $dow = mysql_real_escape_string( $_GET["cronDOW"] );
					 if ( createCronScheduledPackage( $conn, $blIsGroup, $confirm, "D", $m, $h, $dom, $mon, $dow, $shutdown == "on", true, &$tmp ) )
					 {	
					 	$suc++;
					 }
					 else
					 {
					 	$output .= $tmp."<br />";
					 	$fail++;
					 }
					 
					if ( $suc == 1 )
					{
						echo ( "<div class=\"task-start-ok\"><p>"._("The cron task has been scheduled!")."</p></div>" );
					}
					else
					{
						echo "<div class=\"task-start-failed\"><p>"._("Failed to schedule task.")."</p><p>$output</p></div>";
					}
				}
				else
				{
					echo ( "<div class=\"task-start-failed\"><p>"._("You can not select a cron style schedule and a single schedule at the same time.")."</p></div>" );
				}
			}
			else
			{	
				for( $i = 0; $i < count( $imageMembers ); $i++ )
				{
					$tmp = "";
					if ( $imageMembers[$i] != null )
					{
						$other = "fdrive=" . $imageMembers[$i]->getDevice();
						$other .= (" chkdsk=" . ($FOGCore->getSetting("FOG_DISABLE_CHKDSK") == "1" ? '0' : '1'));
						if ( $FOGCore->getSetting("FOG_CHANGE_HOSTNAME_EARLY") == "1" )
							$other .= (" hostname=" .  ( $imageMembers[$i]->getHostName()));
						if( ! createImagePackage($conn, $imageMembers[$i], $taskName, $tmp, ($_GET["debug"] == "true" ), true, $shutdown, $imageMembers[$i]->getKernel(), $other ) )
						{
							$output .= "[" . $imageMembers[$i]->getHostName() . "] " . $tmp . "<br />";
							$fail++;
						}
						else
						{
							$suc++;
						}
					}
				}

				if ( $fail == 0 )
				{
					echo "<div class=\"task-start-ok\"><p>"._("All $suc machines were queued without error.")."</p></div>";
				}
				else if ( $suc == 0 )
				{
					echo ( "<div class=\"task-start-failed\"><p>"._("None of the machines were able to be queued!")."</p><p>$output</p></div>" );
				}
				else
				{
					echo "<div class=\"taskStartWarn\"><p>"._("$suc machines were queued, $fail Failed!.")."</p><p>$output</p></div>";
				}
			}
		}
		else if ( $_GET["direction"] == "downmc" )
		{
			$imageMembers = null;
			$taskName = "";
			$blIsGroup = false;
			if ( $_GET["type"] == "host" )
			{
				$imageMembers = array( getImageMemberFromHostID( $conn, $confirm ) );
			}
			else if ( $_GET["type"] == "group" )
			{
				$blIsGroup = true;
				$imageMembers = getImageMembersByGroupID( $conn, $confirm );
				$taskName = getGroupNameByID( $conn, $confirm );
			}

			$output = "";
			$suc = 0;
			$fail = 0;

			$blSchedSingle = ( $_GET["singlesched"] == "on" );
			$blSchedCron = ( $_GET["cronsched"] == "on" );

			if ( $blSchedSingle )
			{
				if ( ! $blSchedCron )
				{
					$tmp = "";
					$lngTime = strtotime( $_GET["singlescheddate"] );
					if(createSingleRunScheduledPackage( $conn, $blIsGroup, $confirm, "C", $lngTime, $shutdown == "on", true, &$tmp )  )
						$suc++;
					else
					{
						$output .= $tmp . "<br />";
						$fail++;
					}
					
					if ( $suc == 1 )
						echo ( "<div class=\"task-start-ok\"><p>"._("The task has been scheduled for ") . date( "r",  $lngTime ) . "!</p></div>" );
					else
						echo "<div class=\"task-start-failed\"><p>"._("Failed to schedule task.")."</p><p>$output</p></div>";
				}
				else
					echo ( "<div class=\"task-start-failed\"><p>"._("You can not select a cron style schedule and a single schedule at the same time.")."</p></div>" );
			}
			else if ( $blSchedCron )
			{
				if ( ! $blSchedSingle )
				{
					 $tmp = "";
					
					 $m = mysql_real_escape_string( $_GET["cronMin"] );
					 $h = mysql_real_escape_string( $_GET["cronHour"] );
					 $dom = mysql_real_escape_string( $_GET["cronDOM"] ); 
					 $mon = mysql_real_escape_string( $_GET["cronMonth"] );
					 $dow = mysql_real_escape_string( $_GET["cronDOW"] );
					 if ( createCronScheduledPackage( $conn, $blIsGroup, $confirm, "C", $m, $h, $dom, $mon, $dow, $shutdown == "on", true, &$tmp ) )
					 {	
					 	$suc++;
					 }
					 else
					 {
					 	$output .= $tmp."<br />";
					 	$fail++;
					 }
					 
					if ( $suc == 1 )
					{
						echo ( "<div class=\"task-start-ok\"><p>"._("The cron task has been scheduled!")."</p></div>" );
					}
					else
					{
						echo "<div class=\"task-start-failed\"><p>"._("Failed to schedule task.")."</p><p>$output</p></div>";
					}
				}
				else
				{
					echo ( "<div class=\"task-start-failed\"><p>"._("You can not select a cron style schedule and a single schedule at the same time.")."</p></div>" );
				}
			}
			else
			{
				if ( $imageMembers !== null && count( $imageMembers ) > 0 && doAllMembersHaveSameImage( $imageMembers ) )
				{
					$port = getMulticastPort( $conn );

					if ( $port !== -1 )
					{
						// create the multicast job
						$mcId = createMulticastJob( $conn, $taskName, $port, $imageMembers[0]->getImage(), null, $imageMembers[0]->getImageType(), $imageMembers[0]->getStorageGroup() );
						if ( is_numeric( $mcId ) && $mcId >=0 )
						{
							for( $i = 0; $i < count( $imageMembers ); $i++ )
							{
								$tmp = "";
								if ( $imageMembers[$i] != null )
								{
							
									$other = "fdrive=" . $imageMembers[$i]->getDevice();
									$other .= (" chkdsk=" . ($FOGCore->getSetting("FOG_DISABLE_CHKDSK") == "1" ? '0' : '1'));
									if ( $FOGCore->getSetting("FOG_CHANGE_HOSTNAME_EARLY") == "1" )
										$other .= (" hostname=" .  ( $imageMembers[$i]->getHostName()));
									$taskid = createImagePackageMulticast($conn, $imageMembers[$i], $taskName, $port, $tmp, ($_GET["debug"] == "true" ), true, $shutdown, $imageMembers[$i]->getKernel(), $other );

									if( $taskid == -1 )
									{
										$output .= "[" . $imageMembers[$i]->getHostName() . "] " . $tmp . "<br />";
										$fail++;
									}
									else
									{
										if ( linkTaskToMultitaskJob( $conn, $taskid, $mcId ) )
											$suc++;
										// link it to the multitask job

									}
								}
							}

							if ( $fail == 0 && $suc != 0)
							{
								if ( activateMulticastJob( $conn, $mcId ) )
									echo "<div class=\"task-start-ok\"><p>"._("All $suc machines were queued without error.")."</p></div>";
								else
									echo "<div class=\"task-start-failed\"><p>"._("Failed to activate multicast session!")."</p></div>";
							}
							else if ( $suc == 0 )
							{
								echo ( "<div class=\"task-start-failed\"><p>"._("None of the machines were able to be queued!")."</p><p>$output</p></div>" );
								deleteMulticastJob( $conn, $mcId );
							}
							else
							{
								echo "<div class=\"taskStartWarn\"><p>"._("$suc machines were queued, $fail Failed!.")."</p><p>$output</p></div>";
							}
						}
					}
					else
					{
						echo "<div class=\"task-start-failed\"><p>"._("Unable to determine a valid multicast port number.")."</p></div>";
					}
				}
				else
				{
					echo "<div class=\"task-start-failed\"><p>"._("Unable to create multicast package.")."</p></div>";
				}
			}

		}
		else if ( $_GET["direction"] == "wol" )
		{

			$imageMembers = null;
			$blIsGroup = false;
			if ( $_GET["type"] == "host" )
			{
				$imageMembers = array( getImageMemberFromHostID( $conn, $confirm ) );
			}
			else if ( $_GET["type"] == "group" )
			{
				$imageMembers = getImageMembersByGroupID( $conn, $confirm );
				$blIsGroup = true;
			}

			$blSchedSingle = ( $_GET["singlesched"] == "on" );
			$blSchedCron = ( $_GET["cronsched"] == "on" );

			if ( $blSchedSingle )
			{
				if ( ! $blSchedCron )
				{
					$tmp = "";
					$lngTime = strtotime( $_GET["singlescheddate"] );
					if(createSingleRunScheduledPackage( $conn, $blIsGroup, $confirm, "O", $lngTime, false, false, &$tmp )  )
						$suc++;
					else
					{
						$output .= $tmp . "<br />";
						$fail++;
					}
					
					if ( $suc == 1 )
						echo ( "<div class=\"task-start-ok\"><p>"._("The task has been scheduled for ") . date( "r",  $lngTime ) . "!</p></div>" );
					else
						echo "<div class=\"task-start-failed\"><p>"._("Failed to schedule task.")."</p><p>$output</p></div>";
				}
				else
					echo ( "<div class=\"task-start-failed\"><p>"._("You can not select a cron style schedule and a single schedule at the same time.")."</p></div>" );
			}
			else if ( $blSchedCron )
			{
				if ( ! $blSchedSingle )
				{
					 $tmp = "";
					
					 $m = mysql_real_escape_string( $_GET["cronMin"] );
					 $h = mysql_real_escape_string( $_GET["cronHour"] );
					 $dom = mysql_real_escape_string( $_GET["cronDOM"] ); 
					 $mon = mysql_real_escape_string( $_GET["cronMonth"] );
					 $dow = mysql_real_escape_string( $_GET["cronDOW"] );
					 if ( createCronScheduledPackage( $conn, $blIsGroup, $confirm, "O", $m, $h, $dom, $mon, $dow, false, false, &$tmp ) )
					 {	
					 	$suc++;
					 }
					 else
					 {
					 	$output .= $tmp."<br />";
					 	$fail++;
					 }
					 
					if ( $suc == 1 )
					{
						echo ( "<div class=\"task-start-ok\"><p>"._("The cron task has been scheduled!")."</p></div>" );
					}
					else
					{
						echo "<div class=\"task-start-failed\"><p>"._("Failed to schedule task.")."</p><p>$output</p></div>";
					}
				}
				else
				{
					echo ( "<div class=\"task-start-failed\"><p>"._("You can not select a cron style schedule and a single schedule at the same time.")."</p></div>" );
				}
			}
			else
			{
				for( $i = 0; $i < count( $imageMembers ); $i++ )
				{
					wakeUp( $imageMembers[$i]->getMACColon() );
				}
				echo ( "<div class=\"task-start-ok\"><p>"._("Wake up packet sent to ") . count( $imageMembers ) . _(" computer(s).")."</p></div>" );
			}
		}
		else if ( $_GET["direction"] == "wipe" )
		{
			$imageMembers = null;
			$blIsGroup = false;
			if ( $_GET["type"] == "host" )
			{
				$imageMembers = array( getImageMemberFromHostID( $conn, $confirm ) );
			}
			else if ( $_GET["type"] == "group" )
			{
				$blIsGroup = true;
				$imageMembers = getImageMembersByGroupID( $conn, $confirm );
			}

			$output = "";
			$suc = 0;
			$fail = 0;
			
			$blSchedSingle = ( $_GET["singlesched"] == "on" );
			$blSchedCron = ( $_GET["cronsched"] == "on" );
			
			$mode = WIPE_NORMAL;
			if ( $_GET["mode"] === "fast" )
				$mode = WIPE_FAST;
			else if ( $_GET["mode"] === "full" )
				$mode = WIPE_FULL;
			
			if ( $blSchedSingle )
			{
				if ( ! $blSchedCron )
				{
					$tmp = "";
					$lngTime = strtotime( $_GET["singlescheddate"] );
					if(createSingleRunScheduledPackage( $conn, $blIsGroup, $confirm, "W", $lngTime, $shutdown == "on", false, &$tmp, $mode )  )
						$suc++;
					else
					{
						$output .= $tmp . "<br />";
						$fail++;
					}
					
					if ( $suc == 1 )
						echo ( "<div class=\"task-start-ok\"><p>"._("The task has been scheduled for ") . date( "r",  $lngTime ) . "!</p></div>" );
					else
						echo "<div class=\"task-start-failed\"><p>"._("Failed to schedule task.")."</p><p>$output</p></div>";
				}
				else
					echo ( "<div class=\"task-start-failed\"><p>"._("You can not select a cron style schedule and a single schedule at the same time.")."</p></div>" );
			}
			else if ( $blSchedCron )
			{
				if ( ! $blSchedSingle )
				{
					 $tmp = "";
					
					 $m = mysql_real_escape_string( $_GET["cronMin"] );
					 $h = mysql_real_escape_string( $_GET["cronHour"] );
					 $dom = mysql_real_escape_string( $_GET["cronDOM"] ); 
					 $mon = mysql_real_escape_string( $_GET["cronMonth"] );
					 $dow = mysql_real_escape_string( $_GET["cronDOW"] );
					 if ( createCronScheduledPackage( $conn, $blIsGroup, $confirm, "W", $m, $h, $dom, $mon, $dow, $shutdown == "on", false, &$tmp, $mode ) )
					 {	
					 	$suc++;
					 }
					 else
					 {
					 	$output .= $tmp."<br />";
					 	$fail++;
					 }
					 
					if ( $suc == 1 )
					{
						echo ( "<div class=\"task-start-ok\"><p>"._("The cron task has been scheduled!")."</p></div>" );
					}
					else
					{
						echo "<div class=\"task-start-failed\"><p>"._("Failed to schedule task.")."</p><p>$output</p></div>";
					}
				}
				else
				{
					echo ( "<div class=\"task-start-failed\"><p>"._("You can not select a cron style schedule and a single schedule at the same time.")."</p></div>" );
				}
			}
			else
			{			
				for( $i = 0; $i < count( $imageMembers ); $i++ )
				{

					$tmp = "";
					if ( $imageMembers[$i] != null )
					{
						
						
						$other = "fdrive=" . $imageMembers[$i]->getDevice();						
						if(! createWipePackage( $conn, $imageMembers[$i], $tmp, $mode, $shutdown, $imageMembers[$i]->getKernel(), $other ) )
						{
							$output .= "[" . $imageMembers[$i]->getHostName() . "] " . $tmp . "<br />";
							$fail++;
						}
						else
						{
							$suc++;
						}
					}
				}

				if ( $fail == 0 )
				{
					echo "<div class=\"task-start-ok\"><p>"._("All $suc machines were queued without error.")."</p></div>";
				}
				else if ( $suc == 0 )
				{
					echo ( "<div class=\"task-start-failed\"><p>"._("None of the machines were able to be queued!")."</p><p>$output</p></div>" );
				}
				else
				{
					echo "<div class=\"taskStartWarn\"><p>"._("$suc machines were queued, $fail Failed!.")."</p><p>$output</p></div>";
				}
			}

		}
		else if ( $_GET["direction"] == "clamav" )
		{
			$imageMembers = null;
			$blIsGroup = false;
			if ( $_GET["type"] == "host" )
			{
				$imageMembers = array( getImageMemberFromHostID( $conn, $confirm ) );
			}
			else if ( $_GET["type"] == "group" )
			{
				$blIsGroup = true;
				$imageMembers = getImageMembersByGroupID( $conn, $confirm );
			}
	
			$mode = FOG_AV_SCANONLY;
			if ( $_GET["q"] == "1" )
				$mode = FOG_AV_SCANQUARANTINE;

			$output = "";
			$suc = 0;
			$fail = 0;
			
			$blSchedSingle = ( $_GET["singlesched"] == "on" );
			$blSchedCron = ( $_GET["cronsched"] == "on" );
			
			if ( $blSchedSingle )
			{
				if ( ! $blSchedCron )
				{
					$tmp = "";
					$lngTime = strtotime( $_GET["singlescheddate"] );
					if(createSingleRunScheduledPackage( $conn, $blIsGroup, $confirm, "V", $lngTime, $shutdown == "on", false, &$tmp, $mode )  )
						$suc++;
					else
					{
						$output .= $tmp . "<br />";
						$fail++;
					}
					
					if ( $suc == 1 )
						echo ( "<div class=\"task-start-ok\"><p>"._("The task has been scheduled for ") . date( "r",  $lngTime ) . "!</p></div>" );
					else
						echo "<div class=\"task-start-failed\"><p>"._("Failed to schedule task.")."</p><p>$output</p></div>";
				}
				else
					echo ( "<div class=\"task-start-failed\"><p>"._("You can not select a cron style schedule and a single schedule at the same time.")."</p></div>" );
			}
			else if ( $blSchedCron )
			{
				if ( ! $blSchedSingle )
				{
					 $tmp = "";
					
					 $m = mysql_real_escape_string( $_GET["cronMin"] );
					 $h = mysql_real_escape_string( $_GET["cronHour"] );
					 $dom = mysql_real_escape_string( $_GET["cronDOM"] ); 
					 $mon = mysql_real_escape_string( $_GET["cronMonth"] );
					 $dow = mysql_real_escape_string( $_GET["cronDOW"] );
					 if ( createCronScheduledPackage( $conn, $blIsGroup, $confirm, "V", $m, $h, $dom, $mon, $dow, $shutdown == "on", false, &$tmp, $mode ) )
					 {	
					 	$suc++;
					 }
					 else
					 {
					 	$output .= $tmp."<br />";
					 	$fail++;
					 }
					 
					if ( $suc == 1 )
					{
						echo ( "<div class=\"task-start-ok\"><p>"._("The cron task has been scheduled!")."</p></div>" );
					}
					else
					{
						echo "<div class=\"task-start-failed\"><p>"._("Failed to schedule task.")."</p><p>$output</p></div>";
					}
				}
				else
				{
					echo ( "<div class=\"task-start-failed\"><p>"._("You can not select a cron style schedule and a single schedule at the same time.")."</p></div>" );
				}
			}
			else
			{
				for( $i = 0; $i < count( $imageMembers ); $i++ )
				{

					$tmp = "";
					if ( $imageMembers[$i] != null )
					{
						$other = "fdrive=" . $imageMembers[$i]->getDevice();						
						if(! createAVPackage( $conn, $imageMembers[$i], $tmp, $mode, $shutdown, $imageMembers[$i]->getKernel(), $other ) )
						{
							$output .= "[" . $imageMembers[$i]->getHostName() . "] " . $tmp . "<br />";
							$fail++;
						}
						else
						{
							$suc++;
						}
					}
				}

				if ( $fail == 0 )
				{
					echo "<div class=\"task-start-ok\"><p>"._("All $suc machines were queued without error.")."</p></div>";
				}
				else if ( $suc == 0 )
				{
					echo ( "<div class=\"task-start-failed\"><p>"._("None of the machines were able to be queued!")."</p><p>$output</p></div>" );
				}
				else
				{
					echo "<div class=\"taskStartWarn\"><p>"._("$suc machines were queued, $fail Failed!.")."</p><p>$output</p></div>";
				}
			}

		}
		else if ( $_GET["direction"] == "debug" )
		{

			$imageMembers = null;
			if ( $_GET["type"] == "host" )
			{
				$imageMembers = array( getImageMemberFromHostID( $conn, $confirm ) );
			}
			else if ( $_GET["type"] == "group" )
			{
				$imageMembers = getImageMembersByGroupID( $conn, $confirm );
			}

			$output = "";
			$suc = 0;
			$fail = 0;
			for( $i = 0; $i < count( $imageMembers ); $i++ )
			{
				$tmp = "";
				if ( $imageMembers[$i] != null )
				{
					$other = "fdrive=" . $imageMembers[$i]->getDevice();
					if(! createDebugPackage($conn, $imageMembers[$i], $tmp, $imageMembers[$i]->getKernel(), $other))
					{
						$output .= "[" . $imageMembers[$i]->getHostName() . "] " . $tmp . "<br />";
						$fail++;
					}
					else
					{
						$suc++;
					}
				}
			}

			if ( $fail == 0 )
			{
				echo "<div class=\"task-start-ok\"><p>"._("All $suc machines were prepared for debug mode without error.")."</p></div>";
			}
			else if ( $suc == 0 )
			{
				echo ( "<div class=\"task-start-failed\"><p>"._("None of the machines were prepared for debug mode!")."</p><p>$output</p></div>" );
			}
			else
			{
				echo "<div class=\"taskStartWarn\"><p>"._("$suc machines were prepared for debug mode, $fail Failed!.")."</p><p>$output</p></div>";
			}
		}
		else if ( $_GET["direction"] == "memtest" )
		{

			$imageMembers = null;
			if ( $_GET["type"] == "host" )
			{
				$imageMembers = array( getImageMemberFromHostID( $conn, $confirm ) );
			}
			else if ( $_GET["type"] == "group" )
			{
				$imageMembers = getImageMembersByGroupID( $conn, $confirm );
			}

			$output = "";
			$suc = 0;
			$fail = 0;
			for( $i = 0; $i < count( $imageMembers ); $i++ )
			{
				$tmp = "";
				if ( $imageMembers[$i] != null )
				{
					if(! createMemtestPackage($conn, $imageMembers[$i], $tmp))
					{
						$output .= "[" . $imageMembers[$i]->getHostName() . "] " . $tmp . "<br />";
						$fail++;
					}
					else
					{
						$suc++;
					}
				}
			}

			if ( $fail == 0 )
			{
				echo "<div class=\"task-start-ok\"><p>"._("All $suc machines were prepared for memtest mode without error.")."</p></div>";
			}
			else if ( $suc == 0 )
			{
				echo ( "<div class=\"task-start-failed\"><p>"._("None of the machines were prepared for memtest mode!")."</p><p>$output</p></div>" );
			}
			else
			{
				echo "<div class=\"taskStartWarn\"><p>"._("$suc machines were prepared for memtest mode, $fail Failed!.")."</p><p>$output</p></div>";
			}
		}
		else if ( $_GET["direction"] == "testdisk" )
		{

			$imageMembers = null;
			if ( $_GET["type"] == "host" )
			{
				$imageMembers = array( getImageMemberFromHostID( $conn, $confirm ) );
			}
			else if ( $_GET["type"] == "group" )
			{
				$imageMembers = getImageMembersByGroupID( $conn, $confirm );
			}

			$output = "";
			$suc = 0;
			$fail = 0;
			for( $i = 0; $i < count( $imageMembers ); $i++ )
			{
				$tmp = "";
				if ( $imageMembers[$i] != null )
				{
					$other = "fdrive=" . $imageMembers[$i]->getDevice();
					if(! createTestDiskPackage($conn, $imageMembers[$i], $tmp, $shutdown, $imageMembers[$i]->getKernel(), $other))
					{
						$output .= "[" . $imageMembers[$i]->getHostName() . "] " . $tmp . "<br />";
						$fail++;
					}
					else
					{
						$suc++;
					}
				}
			}

			if ( $fail == 0 )
			{
				echo "<div class=\"task-start-ok\"><p>"._("All $suc machines were prepared for testdisk without error.")."</p></div>";
			}
			else if ( $suc == 0 )
			{
				echo ( "<div class=\"task-start-failed\"><p>"._("None of the machines were prepared for testdisk!")."</p><p>$output</p></div>" );
			}
			else
			{
				echo "<div class=\"taskStartWarn\"><p>"._("$suc machines were prepared for testdisk, $fail Failed!")."</p><p>$output</p></div>";
			}
		}
		else if ( $_GET["direction"] == "photorec" )
		{

			$imageMembers = null;
			if ( $_GET["type"] == "host" )
			{
				$imageMembers = array( getImageMemberFromHostID( $conn, $confirm ) );
			}
			else if ( $_GET["type"] == "group" )
			{
				$imageMembers = getImageMembersByGroupID( $conn, $confirm );
			}

			$output = "";
			$suc = 0;
			$fail = 0;
			for( $i = 0; $i < count( $imageMembers ); $i++ )
			{
				$tmp = "";
				if ( $imageMembers[$i] != null )
				{
					$other = "fdrive=" . $imageMembers[$i]->getDevice();
					if(! createPhotoRecPackage($conn, $imageMembers[$i], $tmp, $shutdown, $imageMembers[$i]->getKernel(), $other))
					{
						$output .= "[" . $imageMembers[$i]->getHostName() . "] " . $tmp . "<br />";
						$fail++;
					}
					else
					{
						$suc++;
					}
				}
			}

			if ( $fail == 0 )
			{
				echo "<div class=\"task-start-ok\"><p>"._("All $suc machines were prepared for file recovery without error.")."</p></div>";
			}
			else if ( $suc == 0 )
			{
				echo ( "<div class=\"task-start-failed\"><p>"._("None of the machines were prepared for file recovery!")."</p><p>$output</p></div>" );
			}
			else
			{
				echo "<div class=\"taskStartWarn\"><p>"._("$suc machines were prepared for file recovery, $fail Failed!")."</p><p>$output</p></div>";
			}
		}
		else if ( $_GET["direction"] == "winpassreset" )
		{

			$imageMembers = null;
			if ( $_GET["type"] == "host" )
			{
				$imageMembers = array( getImageMemberFromHostID( $conn, $confirm ) );
			}
			else if ( $_GET["type"] == "group" )
			{
				$imageMembers = getImageMembersByGroupID( $conn, $confirm );
			}

			$output = "";
			$suc = 0;
			$fail = 0;

			for( $i = 0; $i < count( $imageMembers ); $i++ )
			{
				$tmp = "";
				if ( $imageMembers[$i] != null )
				{
					$acct = trim($_GET["account"]);
					if ( $acct != null )
					{
						$other = "fdrive=" . $imageMembers[$i]->getDevice();
						if(! createPassResetPackage($conn, $imageMembers[$i], $tmp, $shutdown, $imageMembers[$i]->getKernel(), " winuser=" .  $acct  . " " . $other))
						{
							$output .= "[" . $imageMembers[$i]->getHostName() . "] " . $tmp . "<br />";
							$fail++;
						}
						else
						{
							$suc++;
						}
					}
					else
					{
						$output .= "[" . $imageMembers[$i]->getHostName() . "] "._("Invalid account to reset.")."<br />";
						$fail++;					
					}
				}
			}


			if ( $fail == 0 )
			{
				echo "<div class=\"task-start-ok\"><p>"._("All $suc machines were prepared for password reset without error.")."</p></div>";
			}
			else if ( $suc == 0 )
			{
				echo ( "<div class=\"task-start-failed\"><p>"._("None of the machines were prepared for password reset!")."</p><p>$output</p></div>" );
			}
			else
			{
				echo "<div class=\"taskStartWarn\"><p>"._("$suc machines were prepared for password reset, $fail Failed!")."</p><p>$output</p></div>";
			}
		}
		else if ( $_GET["direction"] == "surfacetest" )
		{

			$imageMembers = null;
			$blIsGroup = false;
			if ( $_GET["type"] == "host" )
			{
				$imageMembers = array( getImageMemberFromHostID( $conn, $confirm ) );
			}
			else if ( $_GET["type"] == "group" )
			{
				$blIsGroup = true;
				$imageMembers = getImageMembersByGroupID( $conn, $confirm );
			}

			$output = "";
			$suc = 0;
			$fail = 0;
			
			$blSchedSingle = ( $_GET["singlesched"] == "on" );
			$blSchedCron = ( $_GET["cronsched"] == "on" );
			
			if ( $blSchedSingle )
			{
				if ( ! $blSchedCron )
				{
					$tmp = "";
					$lngTime = strtotime( $_GET["singlescheddate"] );
					if(createSingleRunScheduledPackage( $conn, $blIsGroup, $confirm, "T", $lngTime, $shutdown == "on", false, &$tmp )  )
						$suc++;
					else
					{
						$output .= $tmp . "<br />";
						$fail++;
					}
					
					if ( $suc == 1 )
						echo ( "<div class=\"task-start-ok\"><p>"._("The task has been scheduled for ") . date( "r",  $lngTime ) . "!</p></div>" );
					else
						echo "<div class=\"task-start-failed\"><p>"._("Failed to schedule task.")."</p><p>$output</p></div>";
				}
				else
					echo ( "<div class=\"task-start-failed\"><p>"._("You can not select a cron style schedule and a single schedule at the same time.")."</p></div>" );
			}
			else if ( $blSchedCron )
			{
				if ( ! $blSchedSingle )
				{
					 $tmp = "";
					
					 $m = mysql_real_escape_string( $_GET["cronMin"] );
					 $h = mysql_real_escape_string( $_GET["cronHour"] );
					 $dom = mysql_real_escape_string( $_GET["cronDOM"] ); 
					 $mon = mysql_real_escape_string( $_GET["cronMonth"] );
					 $dow = mysql_real_escape_string( $_GET["cronDOW"] );
					 if ( createCronScheduledPackage( $conn, $blIsGroup, $confirm, "T", $m, $h, $dom, $mon, $dow, $shutdown == "on", false, &$tmp ) )
					 {	
					 	$suc++;
					 }
					 else
					 {
					 	$output .= $tmp."<br />";
					 	$fail++;
					 }
					 
					if ( $suc == 1 )
					{
						echo ( "<div class=\"task-start-ok\"><p>"._("The cron task has been scheduled!")."</p></div>" );
					}
					else
					{
						echo "<div class=\"task-start-failed\"><p>"._("Failed to schedule task.")."</p><p>$output</p></div>";
					}
				}
				else
				{
					echo ( "<div class=\"task-start-failed\"><p>"._("You can not select a cron style schedule and a single schedule at the same time.")."</p></div>" );
				}
			}
			else
			{
				for( $i = 0; $i < count( $imageMembers ); $i++ )
				{
					$tmp = "";
					if ( $imageMembers[$i] != null )
					{
						$other = "fdrive=" . $imageMembers[$i]->getDevice();
						if(! createDiskSufaceTestPackage($conn, $imageMembers[$i], $tmp, $shutdown, $imageMembers[$i]->getKernel(), $other))
						{
							$output .= "[" . $imageMembers[$i]->getHostName() . "] " . $tmp . "<br />";
							$fail++;
						}
						else
						{
							$suc++;
						}
					}
				}

				if ( $fail == 0 )
				{
					echo "<div class=\"task-start-ok\"><p>"._("All $suc machines were prepared for surface test without error.")."</p></div>";
				}
				else if ( $suc == 0 )
				{
					echo ( "<div class=\"task-start-failed\"><p>"._("None of the machines were prepared for surface test!")."</p><p>$output</p></div>" );
				}
				else
				{
					echo "<div class=\"taskStartWarn\"><p>"._("$suc machines were prepared for surface test, $fail Failed!")."</p><p>$output</p></div>";
				}
			}
		}
		else if ( $_GET["direction"] == "allsnaps" )
		{

			$imageMembers = null;
			$taskName = "";
			$blIsGroup = false;
			if ( $_GET["type"] == "host" )
			{
				$imageMembers = array( getImageMemberFromHostID( $conn, $confirm ) );
				$taskName = "Deploy Snapins: " . $imageMembers[0]->getHostName();
			}
			else if ( $_GET["type"] == "group" )
			{
				$blIsGroup = true;
				$imageMembers = getImageMembersByGroupID( $conn, $confirm );
				$taskName = "Deploy Snapins: " . getGroupNameByID( $conn, $confirm );
			}

			$output = "";
			$suc = 0;
			$fail = 0;
			
			$blSchedSingle = false;
			$blSchedCron = false;
			
			if ( $_GET["singlesched"] == "on" ) $blSchedSingle = true;
			if ( $_GET["cronsched"] == "on" ) $blSchedCron = true;
			
			if ( $blSchedSingle )
			{
				if ( ! $blSchedCron )
				{
					$tmp = "";
					$lngTime = strtotime( $_GET["singlescheddate"] );
					if(createSingleRunScheduledPackage( $conn, $blIsGroup, $confirm, "S", $lngTime, false, false, &$tmp )  )
						$suc++;
					else
					{
						$output .= $tmp . "<br />";
						$fail++;
					}
					
					if ( $suc == 1 )
						echo ( "<div class=\"task-start-ok\"><p>"._("The task has been scheduled for ") . date( "r",  $lngTime ) . "!</p></div>" );
					else
						echo "<div class=\"task-start-failed\"><p>"._("Failed to schedule task.")."</p><p>$output</p></div>";
				}
				else
					echo ( "<div class=\"task-start-failed\"><p>"._("You can not select a cron style schedule and a single schedule at the same time.")."</p></div>" );
			}
			else if ( $blSchedCron )
			{
				if ( ! $blSchedSingle )
				{
					 $tmp = "";
					
					 $m = mysql_real_escape_string( $_GET["cronMin"] );
					 $h = mysql_real_escape_string( $_GET["cronHour"] );
					 $dom = mysql_real_escape_string( $_GET["cronDOM"] ); 
					 $mon = mysql_real_escape_string( $_GET["cronMonth"] );
					 $dow = mysql_real_escape_string( $_GET["cronDOW"] );
					 if ( createCronScheduledPackage( $conn, $blIsGroup, $confirm, "S", $m, $h, $dom, $mon, $dow, $shutdown == "on", true, &$tmp ) )
					 {	
					 	$suc++;
					 }
					 else
					 {
					 	$output .= $tmp."<br />";
					 	$fail++;
					 }
					 
					if ( $suc == 1 )
					{
						echo ( "<div class=\"task-start-ok\"><p>"._("The cron task has been scheduled!")."</p></div>" );
					}
					else
					{
						echo "<div class=\"task-start-failed\"><p>"._("Failed to schedule task.")."</p><p>$output</p></div>";
					}
				}
				else
				{
					echo ( "<div class=\"task-start-failed\"><p>"._("You can not select a cron style schedule and a single schedule at the same time.")."</p></div>" );
				}
			}
			else
			{			
				for( $i = 0; $i < count( $imageMembers ); $i++ )
				{
					$tmp = "";
					if ( $imageMembers[$i] != null )
					{
						cancelSnapinsForHost( $conn, $imageMembers[$i]->getID() );
						$cnt = deploySnapinsForHost( $conn, $imageMembers[$i]->getID() );
						if( $cnt === -1 )
						{
							$output .= "[" . $imageMembers[$i]->getHostName() . "] "._("Failed")."<br />";
							$fail++;
						}
						else
						{
							$suc++;
						}
					}
				}

				if ( $fail == 0 )
				{
					echo "<div class=\"task-start-ok\"><p>"._("All $suc machines were queued to receive snapins.")."</p></div>";
				}
				else if ( $suc == 0 )
				{
					echo ( "<div class=\"task-start-failed\"><p>"._("None of the machines were queued to receive snapins!")."</p><p>$output</p></div>" );
				}
				else
				{
					echo "<div class=\"taskStartWarn\"><p>"._("$suc machines were queued to receive snapins, $fail Failed!")."</p><p>$output</p></div>";
				}
			}
		}
		else if ( $_GET["direction"] == "downnosnap" )
		{
			$imageMembers = null;
			$taskName = "";
			if ( $_GET["type"] == "host" )
			{
				$imageMembers = array( getImageMemberFromHostID( $conn, $confirm ) );
			}
			else if ( $_GET["type"] == "group" )
			{
				$blIsGroup = true;
				$imageMembers = getImageMembersByGroupID( $conn, $confirm );
				$taskName = getGroupNameByID( $conn, $confirm );
			}

			$output = "";
			$suc = 0;
			$fail = 0;

			$blSchedSingle = false;
			$blSchedCron = false;
			
			if ( $_GET["singlesched"] == "on" ) $blSchedSingle = true;
			if ( $_GET["cronsched"] == "on" ) $blSchedCron = true;

			if ( $blSchedSingle )
			{
				if ( ! $blSchedCron )
				{
					$tmp = "";
					$lngTime = strtotime( $_GET["singlescheddate"] );
					if(createSingleRunScheduledPackage( $conn, $blIsGroup, $confirm, "D", $lngTime, $shutdown == "on", false, &$tmp )  )
						$suc++;
					else
					{
						$output .= $tmp . "<br />";
						$fail++;
					}
					
					if ( $suc == 1 )
						echo ( "<div class=\"task-start-ok\"><p>"._("The task has been scheduled for ") . date( "r",  $lngTime ) . "!</p></div>" );
					else
						echo "<div class=\"task-start-failed\"><p>"._("Failed to schedule task.")."</p><p>$output</p></div>";
				}
				else
					echo ( "<div class=\"task-start-failed\"><p>"._("You can not select a cron style schedule and a single schedule at the same time.")."</p></div>" );
			}
			else if ( $blSchedCron )
			{
				if ( ! $blSchedSingle )
				{
					 $tmp = "";
					
					 $m = mysql_real_escape_string( $_GET["cronMin"] );
					 $h = mysql_real_escape_string( $_GET["cronHour"] );
					 $dom = mysql_real_escape_string( $_GET["cronDOM"] ); 
					 $mon = mysql_real_escape_string( $_GET["cronMonth"] );
					 $dow = mysql_real_escape_string( $_GET["cronDOW"] );
					 if ( createCronScheduledPackage( $conn, $blIsGroup, $confirm, "D", $m, $h, $dom, $mon, $dow, $shutdown == "on", false, &$tmp ) )
					 {	
					 	$suc++;
					 }
					 else
					 {
					 	$output .= $tmp."<br />";
					 	$fail++;
					 }
					 
					if ( $suc == 1 )
					{
						echo ( "<div class=\"task-start-ok\"><p>"._("The cron task has been scheduled!")."</p></div>" );
					}
					else
					{
						echo "<div class=\"task-start-failed\"><p>"._("Failed to schedule task.")."</p><p>$output</p></div>";
					}
				}
				else
				{
					echo ( "<div class=\"task-start-failed\"><p>"._("You can not select a cron style schedule and a single schedule at the same time.")."</p></div>" );
				}
			}
			else
			{
				for( $i = 0; $i < count( $imageMembers ); $i++ )
				{
					$tmp = "";
					if ( $imageMembers[$i] != null )
					{
						$other = "fdrive=" . $imageMembers[$i]->getDevice();
						$other .= (" chkdsk=" . ($FOGCore->getSetting("FOG_DISABLE_CHKDSK") == "1" ? '0' : '1'));
						if ( $FOGCore->getSetting("FOG_CHANGE_HOSTNAME_EARLY") == "1" )
							$other .= (" hostname=" .  ( $imageMembers[$i]->getHostName()));
						if(! createImagePackage($conn, $imageMembers[$i], $taskName, $tmp, ($_GET["debug"] == "true" ), false, $shutdown, $imageMembers[$i]->getKernel(), $other ) )
						{
							$output .= "[" . $imageMembers[$i]->getHostName() . "] " . $tmp . "<br />";
							$fail++;
						}
						else
						{
							$suc++;
						}
					}
				}

				if ( $fail == 0 )
				{
					echo "<div class=\"task-start-ok\"><p>"._("All $suc machines were queued without error.")."</p></div>";
				}
				else if ( $suc == 0 )
				{
					echo ( "<div class=\"task-start-failed\"><p>"._("None of the machines were able to be queued!")."</p><p>$output</p></div>" );
				}
				else
				{
					echo "<div class=\"taskStartWarn\"><p>"._("$suc machines were queued, $fail Failed!.")."</p><p>$output</p></div>";
				}
			}
		}
		else if ( $_GET["direction"] == "onesnap" )
		{
			if ( $_GET["snap"] != "-1" && is_numeric( $_GET["snap"] ) )
			{
				$snapin = mysql_real_escape_string( $_GET["snap"] );

				$imageMembers = null;
				$taskName = "";
				$blIsGroup = false;
				if ( $_GET["type"] == "host" )
				{
					$imageMembers = array( getImageMemberFromHostID( $conn, $confirm ) );
				}
				else if ( $_GET["type"] == "group" )
				{
					$blIsGroup = true;
					$imageMembers = getImageMembersByGroupID( $conn, $confirm );
					$taskName = getGroupNameByID( $conn, $confirm );
				}

				$output = "";
				$suc = 0;
				$fail = 0;

				$blSchedSingle = ( $_GET["singlesched"] == "on" );
				$blSchedCron = ( $_GET["cronsched"] == "on" );

				if ( $blSchedSingle )
				{
					if ( ! $blSchedCron )
					{
						$tmp = "";
						$lngTime = strtotime( $_GET["singlescheddate"] );
						if(createSingleRunScheduledPackage( $conn, $blIsGroup, $confirm, "L", $lngTime, false, false, &$tmp, $snapin )  )
							$suc++;
						else
						{
							$output .= $tmp . "<br />";
							$fail++;
						}
					
						if ( $suc == 1 )
							echo ( "<div class=\"task-start-ok\"><p>"._("The task has been scheduled for ") . date( "r",  $lngTime ) . "!</p></div>" );
						else
							echo "<div class=\"task-start-failed\"><p>"._("Failed to schedule task.")."</p><p>$output</p></div>";
					}
					else
						echo ( "<div class=\"task-start-failed\"><p>"._("You can not select a cron style schedule and a single schedule at the same time.")."</p></div>" );
				}
				else if ( $blSchedCron )
				{
					if ( ! $blSchedSingle )
					{
						 $tmp = "";
					
						 $m = mysql_real_escape_string( $_GET["cronMin"] );
						 $h = mysql_real_escape_string( $_GET["cronHour"] );
						 $dom = mysql_real_escape_string( $_GET["cronDOM"] ); 
						 $mon = mysql_real_escape_string( $_GET["cronMonth"] );
						 $dow = mysql_real_escape_string( $_GET["cronDOW"] );
						 if ( createCronScheduledPackage( $conn, $blIsGroup, $confirm, "L", $m, $h, $dom, $mon, $dow, false, false, &$tmp, $snapin ) )
						 {	
						 	$suc++;
						 }
						 else
						 {
						 	$output .= $tmp."<br />";
						 	$fail++;
						 }
						 
						if ( $suc == 1 )
						{
							echo ( "<div class=\"task-start-ok\"><p>"._("The cron task has been scheduled!")."</p></div>" );
						}
						else
						{
							echo "<div class=\"task-start-failed\"><p>"._("Failed to schedule task.")."</p><p>$output</p></div>";
						}
					}
					else
					{
						echo ( "<div class=\"task-start-failed\"><p>"._("You can not select a cron style schedule and a single schedule at the same time.")."</p></div>" );
					}
				}
				else
				{
					for( $i = 0; $i < count( $imageMembers ); $i++ )
					{
						if ( $imageMembers[$i] != null )
						{
							if( deploySnapinsForHost( $conn, $imageMembers[$i]->getID(), $snapin ) == 1 )
							{
								$suc++;
							}
							else
							{

								$output .= "[" . $imageMembers[$i]->getHostName() . "] "._("Deploy failed, make sure snapin is linked with host.")."<br />";
								$fail++;
							}
						}
					}

					if ( $fail == 0 )
					{
						echo "<div class=\"task-start-ok\"><p>"._("All $suc snapins were queued without error.")."</p></div>";
					}
					else if ( $suc == 0 )
					{
						echo ( "<div class=\"task-start-failed\"><p>"._("None of the snapins were able to be queued!")."</p><p>$output</p></div>" );
					}
					else
					{
						echo "<div class=\"taskStartWarn\"><p>"._("$suc snapins were queued, $fail Failed!.")."</p><p>$output</p></div>";
					}
				}
			}
		}
		else if ( $_GET["direction"] == "inventory" )
		{

			$imageMembers = null;
			$blIsGroup = false;
			if ( $_GET["type"] == "host" )
			{
				$imageMembers = array( getImageMemberFromHostID( $conn, $confirm ) );
			}
			else if ( $_GET["type"] == "group" )
			{
				$blIsGroup = true;
				$imageMembers = getImageMembersByGroupID( $conn, $confirm );
			}

			$output = "";
			$suc = 0;
			$fail = 0;
			
			$blSchedSingle = ( $_GET["singlesched"] == "on" );
			$blSchedCron = ( $_GET["cronsched"] == "on" );
			
			if ( $blSchedSingle )
			{
				if ( ! $blSchedCron )
				{
					$tmp = "";
					$lngTime = strtotime( $_GET["singlescheddate"] );
					if(createSingleRunScheduledPackage( $conn, $blIsGroup, $confirm, "I", $lngTime, $shutdown == "on", false, &$tmp )  )
						$suc++;
					else
					{
						$output .= $tmp . "<br />";
						$fail++;
					}
					
					if ( $suc == 1 )
						echo ( "<div class=\"task-start-ok\"><p>"._("The task has been scheduled for ") . date( "r",  $lngTime ) . "!</p></div>" );
					else
						echo "<div class=\"task-start-failed\"><p>"._("Failed to schedule task.")."</p><p>$output</p></div>";
				}
				else
					echo ( "<div class=\"task-start-failed\"><p>"._("You can not select a cron style schedule and a single schedule at the same time.")."</p></div>" );
			}
			else if ( $blSchedCron )
			{
				if ( ! $blSchedSingle )
				{
					 $tmp = "";
					
					 $m = mysql_real_escape_string( $_GET["cronMin"] );
					 $h = mysql_real_escape_string( $_GET["cronHour"] );
					 $dom = mysql_real_escape_string( $_GET["cronDOM"] ); 
					 $mon = mysql_real_escape_string( $_GET["cronMonth"] );
					 $dow = mysql_real_escape_string( $_GET["cronDOW"] );
					 if ( createCronScheduledPackage( $conn, $blIsGroup, $confirm, "I", $m, $h, $dom, $mon, $dow, $shutdown == "on", false, &$tmp ) )
					 {	
					 	$suc++;
					 }
					 else
					 {
					 	$output .= $tmp."<br />";
					 	$fail++;
					 }
					 
					if ( $suc == 1 )
					{
						echo ( "<div class=\"task-start-ok\"><p>"._("The cron task has been scheduled!")."</p></div>" );
					}
					else
					{
						echo "<div class=\"task-start-failed\"><p>"._("Failed to schedule task.")."</p><p>$output</p></div>";
					}
				}
				else
				{
					echo ( "<div class=\"task-start-failed\"><p>"._("You can not select a cron style schedule and a single schedule at the same time.")."</p></div>" );
				}
			}
			else
			{			
				for( $i = 0; $i < count( $imageMembers ); $i++ )
				{
					$tmp = "";
					if ( $imageMembers[$i] != null )
					{
						$other = "fdrive=" . $imageMembers[$i]->getDevice();
						if(! createInventoryPackage($conn, $imageMembers[$i], $tmp, $shutdown, $imageMembers[$i]->getKernel(), $other))
						{
							$output .= "[" . $imageMembers[$i]->getHostName() . "] " . $tmp . "<br />";
							$fail++;
						}
						else
						{
							$suc++;
						}
					}
				}

				if ( $fail == 0 )
				{
					echo "<div class=\"task-start-ok\"><p>"._("All $suc machines were prepared for inventory without error.")."</p></div>";
				}
				else if ( $suc == 0 )
				{
					echo ( "<div class=\"task-start-failed\"><p>"._("None of the machines were prepared for inventory!")."</p><p>$output</p></div>" );
				}
				else
				{
					echo "<div class=\"taskStartWarn\"><p>"._("$suc machines were prepared for inventory, $fail Failed!.")."</p><p>$output</p></div>";
				}
			}
		}

	}
}
