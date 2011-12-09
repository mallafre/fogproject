<?php

// Blackout - 8:14 PM 25/09/2011
if (IS_INCLUDED !== true) die(_('Unable to load system configuration information.'));

if ($currentUser != null && $currentUser->isLoggedIn())
{
	$hostMan = $FOGCore->getClass('HostManager');
	
	if (!empty($id))
	{
		$Host = new Host($id);
	}
	
	// Update Host
	if ($_POST['updategen'] == '1')
	{
		try
		{
			// Error checking
			if (empty($id))
			{
				throw new Exception('Host ID is required');
			}
			
			// Variables
			$mac = new MACAddress($_POST['mac']);
			
			if (!$mac->isValid())
			{
				throw new Exception('MAC Address is not valid');
			}
			
			// Hook
			$HookManager->processEvent('HostEditUpdate', array('host' => &$Host));
		
			// Define new Image object with data provided
			$Host	->set('name',		$_POST['host'])
				->set('description',	$_POST['description'])
				->set('ip',		$_POST['ip'])
				->set('mac',		$mac)
				//->set('osID',		$_POST['os'])
				->set('imageID',	$_POST['image'])
				->set('kernel',		$_POST['kern'])
				->set('kernelArgs',	$_POST['args'])
				->set('kernelDevice',	$_POST['dev']);
			
			// Add Additional MAC Addresses
			$Host->set('additionalMACs', (array)$_POST['additionalMACs']);
		
			// Save to database
			if ($Host->save())
			{
				// Hook
				$HookManager->processEvent('HostEditUpdateSuccess', array('host' => &$Host));
				
				// Log History event
				$FOGCore->logHistory(sprintf('Host updated: ID: %s, Name: %s', $Host->get('id'), $Host->get('name')));
			
				// Set session message
				$FOGCore->setMessage('Host updated!');
			
				// Redirect to new entry
				$FOGCore->redirect("$_SERVER[PHP_SELF]?node=$node&sub=edit&id=" . $Host->get('id'));
			}
			else
			{
				// Hook
				$HookManager->processEvent('HostEditUpdateFail', array('host' => &$Host));
				
				// Database save failed
				throw new Exception('Database update failed');
			}
		}
		catch (Exception $e)
		{
			// Hook
			$HookManager->processEvent('HostEditUpdateFail', array('host' => &$Host));
			
			// Log History event
			$FOGCore->logHistory(sprintf('Host update failed: Name: %s, Error: %s', $_POST['name'], $e->getMessage()));
		
			// Set session message
			$FOGCore->setMessage($e->getMessage());
		}
	}
	
	if ( isset($_GET["confirmMac"]) )
	{
		// Hook
		$HookManager->processEvent('HostEditConfirmMACUpdate', array('host' => &$Host));
		
		foreach ($hostMan->getPendingMacAddressesForHost($Host) AS $pMac)
		{
			if ( $pMac != null && $pMac->getMACWithColon() == base64_decode( $_GET["confirmMac"] )  )
			{
				$Host->add('additionalMACs', $pMac);

				if ( ! ( $hostMan->deletePendingMacAddressForHost( $Host, $pMac ) && $hostMan->updateHost( $Host, HostManager::UPDATE_GENERAL )) )
				{
					// Hook
					$HookManager->processEvent('HostEditConfirmMACUpdateFail', array('host' => &$Host, 'mac' => &$pMac));
					
					msgBox( _("Failed to update host!") . ($hostMan->lastError ? '<br />' . $hostMan->lastError : ''));
				}
				else
				{
					// Hook
					$HookManager->processEvent('HostEditConfirmMACUpdateSuccess', array('host' => &$Host, 'mac' => &$pMac));
				}
			}
		}
	}

	// Update Active Directory
	if ( $_POST["updatead"] == "1" )
	{
		try
		{
			// Error checking
			if (empty($id))
			{
				throw new Exception('Host ID is required');
			}
			
			// Hook
			$HookManager->processEvent('HostEditADUpdate', array('host' => &$Host));
		
			// Define new Image object with data provided
			$Host	->set('useAD',		($_POST["domain"] == "on" ? '1' : '0'))
				->set('ADDomain',	$_POST['domainname'])
				->set('ADOU',		$_POST['ou'])
				->set('ADUser',		$_POST['domainuser'])
				->set('ADPass',		$_POST['domainpassword']);
		
			// Save to database
			if ($Host->save())
			{
				// Hook
				$HookManager->processEvent('HostEditADUpdateSuccess', array('host' => &$Host));
				
				// Log History event
				$FOGCore->logHistory(sprintf('Host updated: ID: %s, Name: %s', $Host->get('id'), $Host->get('name')));
			
				// Set session message
				$FOGCore->setMessage('Host AD Details updated!');
			
				// Redirect to new entry
				$FOGCore->redirect("$_SERVER[PHP_SELF]?node=$node&sub=edit&id=" . $Host->get('id'));
			}
			else
			{
				// Hook
				$HookManager->processEvent('HostEditADUpdateFail', array('host' => &$Host));
				
				// Database save failed
				throw new Exception('Database update failed');
			}
		}
		catch (Exception $e)
		{
			// Hook
			$HookManager->processEvent('HostEditUpdateFail', array('host' => &$Host));
			
			// Log History event
			$FOGCore->logHistory(sprintf('Host AD Details update failed: Name: %s, Error: %s', $_POST['name'], $e->getMessage()));
		
			// Set session message
			$FOGCore->setMessage($e->getMessage());
		}
	}

	if ( $_POST["snap"] !== null && is_numeric( $_POST["snap"] ) && $_POST["snap"] >= 0 )
	{
		// Hook
		$HookManager->processEvent('HostEditAddSnapinUpdate');
		
		$snap = mysql_real_escape_string( $_POST["snap"] );
		$ret = "";
		if ( ! addSnapinToHost( $conn, $id, $snap, $ret ) )
		{
			// Hook
			$HookManager->processEvent('HostEditAddSnapinUpdateFail');
			
			msgBox($ret);
		}
		else
		{
			// Hook
			$HookManager->processEvent('HostEditAddSnapinUpdateSuccess');
		}
	}

	if ( $_GET["delsnaplinkid"] !== null && is_numeric( $_GET["delsnaplinkid"] ) )
	{
		// Hook
		$HookManager->processEvent('HostEditRemoveSnapinUpdate');
		
		$snap = mysql_real_escape_string( $_GET["delsnaplinkid"] );
		$ret = "";
		if ( ! deleteSnapinFromHost( $conn, $id, $snap, $ret ) )
		{
			// Hook
			$HookManager->processEvent('HostEditRemoveSnapinUpdateFail');
		
			msgBox($ret);
		}
		else
		{
			// Hook
			$HookManager->processEvent('HostEditRemoveSnapinUpdateSuccess');
		}
	}

	if ( $_GET["delvid"] !== null && is_numeric( $_GET["delvid"] ) )
	{		
		$vid = mysql_real_escape_string( $_GET["delvid"] );
		clearAVRecord( $conn, $vid );
	}

	if ( $_GET["delvid"] == "all"  )
	{
		$member = getImageMemberFromHostID( $conn, $id );
		if ( $member != null )
		{
			clearAVRecordsForHost( $conn, $member->getMACColon() );
		}
	}

	if ( $_GET["updatemodulestatus"] == "1" )
	{
		
		//$clientupdaterchecked = " checked=\"checked\" ";
		//$hostregisterchecked = " checked=\"checked\" ";
		//$printermanagerchecked = " checked=\"checked\" ";					
		//$taskrebootchecked = " checked=\"checked\" ";
		//$usertrackerchecked = " checked=\"checked\" ";
	
	
		$dircleanupstate = "0";
		$usercleanupstate = "0";
		$displaymanagerstate = "0";
		$alostate = "0";
		$gfstate = "0";
		$snapinstate = "0";		
		$hncstate = "0";
		$custate = "0";
		$hrstate = "0";
		$pmstate = "0";
		$trstate = "0";
		$utstate = "0";
		
		if ( $_POST["dircleanen"] == "on" ) $dircleanupstate = "1";
		if ( $_POST["usercleanen"] == "on" ) $usercleanupstate = "1";
		if ( $_POST["displaymanager"] == "on" ) $displaymanagerstate = "1";
		if ( $_POST["alo"] == "on" ) $alostate = "1";
		if ( $_POST["gf"] == "on" ) $gfstate = "1";
		if ( $_POST["snapin"] == "on" ) $snapinstate = "1";
		if ( $_POST["hostnamechanger"] == "on" ) $hncstate = "1";
		if ( $_POST["clientupdater"] == "on" ) $custate = "1";
		if ( $_POST["hostregister"] == "on" ) $hrstate = "1";
		if ( $_POST["printermanager"] == "on" ) $pmstate = "1";
		if ( $_POST["taskreboot"] == "on" ) $trstate = "1";
		if ( $_POST["usertracker"] == "on" ) $utstate = "1";

		setHostModuleStatus( $conn, $dircleanupstate, $id, 'dircleanup' );
		setHostModuleStatus( $conn, $usercleanupstate, $id, 'usercleanup' );
		setHostModuleStatus( $conn, $displaymanagerstate, $id, 'displaymanager' );
		setHostModuleStatus( $conn, $alostate, $id, 'autologout' );
		setHostModuleStatus( $conn, $gfstate, $id, 'greenfog' );
		setHostModuleStatus( $conn, $snapinstate, $id, 'snapin' );
		setHostModuleStatus( $conn, $hncstate, $id, 'hostnamechanger' );
		setHostModuleStatus( $conn, $custate, $id, 'clientupdater' );
		setHostModuleStatus( $conn, $hrstate, $id, 'hostregister' );
		setHostModuleStatus( $conn, $pmstate, $id, 'printermanager' );
		setHostModuleStatus( $conn, $trstate, $id, 'taskreboot' );
		setHostModuleStatus( $conn, $utstate, $id, 'usertracker' );

		// update screen settings
		$x = mysql_real_escape_string( $_POST["x"] );
		$y = mysql_real_escape_string( $_POST["y"] );
		$r = mysql_real_escape_string( $_POST["r"] );
		if ( $x == "" && $y == "" && $z == "" )
		{
			$sql = "DELETE FROM hostScreenSettings WHERE hssHostID = '$id'";
			$res = mysql_query( $sql, $conn ) or criticalError( mysql_error(), _("FOG :: Database error!") );
		}
		else
		{
			$sql = "SELECT
					COUNT(*) as cnt
				FROM
					hostScreenSettings
				WHERE
					hssHostID = '$id'";
			$res = mysql_query( $sql, $conn ) or criticalError( mysql_error(), _("FOG :: Database error!") );
			$blFound = false;
			while( $ar = mysql_fetch_array( $res ) )
			{
				if ( $ar["cnt"] > 0 ) $blFound = true;
			}

			if ( $blFound )
			{
				$sql = "UPDATE
						hostScreenSettings
						set
							hssWidth = '$x',
							hssHeight = '$y',
							hssRefresh = '$r'
						WHERE
							hssHostID = '$id'";
			}
			else
			{
				$sql = "INSERT INTO hostScreenSettings(hssHostID, hssWidth, hssHeight, hssRefresh) values('$id', '$x', '$y', '$r')";
			}
			if ( ! mysql_query( $sql, $conn ) )
				criticalError( mysql_error(), _("FOG :: Database error!") );
		}
		// Update auto log off times.
		$tme = mysql_real_escape_string( $_POST["tme"] );
		$sql = "SELECT
				COUNT(*) as cnt
			FROM
				hostAutoLogOut
			WHERE
				haloHostID = '$id'";
		$res = mysql_query( $sql, $conn ) or criticalError( mysql_error(), _("FOG :: Database error!") );
		$blFound = false;
		while( $ar = mysql_fetch_array( $res ) )
		{
			if ( $ar["cnt"] > 0 ) $blFound = true;
		}

		if ( $blFound )
		{
			$sql = "UPDATE
					hostAutoLogOut
					set
						haloTime = '$tme'
					WHERE
						haloHostID = '$id'";
		}
		else
		{
			$sql = "INSERT INTO hostAutoLogOut(haloHostID, haloTime) values('$id', '$tme')";
		}
		if ( ! mysql_query( $sql, $conn ) )
			criticalError( mysql_error(), _("FOG :: Database error!") );

	}

	
	if ( is_numeric( $id ) && $Host->isValid() )
	{
		if ( $_GET["tab"] == "gen" || $_GET["tab"] == ""  )
		{
			?>
			<h2><?php print _("General"); ?></h2>
			<form method="POST" action="<?php print "?node=$node&sub=$sub&id=$id"; ?>">
			<table cellpadding="0" cellspacing="0" border="0" width="100%">
				<tr id="host-<?php print $Host->getHostname(); ?>"><td class="alt1"><?php print _("Host Name"); ?>:</td><td><input class="smaller" type="text" name="host" maxlength="15" value="<?php print $Host->getHostname(); ?>" /> <span class="host-ping"></span></td></tr>
				<tr><td><?php print _("Host IP"); ?>:</td><td><input class="smaller" type="text" name="ip" value="<?php print $Host->getIPAddress(); ?>" /></td></tr>
			<?php
				$strMac = "";
				if ( $Host->get('mac') )
					$strMac = $Host->get('mac')->getMACWithColon();
				echo ( "<tr><td>"._("Primary MAC").":</td><td><input class=\"smaller macAddy\" type=\"text\" id='mac' name=\"mac\" class=\"mac\" value=\"" . $strMac . "\" /> <span class=\"icon icon-add add-mac hand\" title=\"Add MAC\"></span> <span class=\"mac-manufactor\"></span></div></td></tr>" );
				$style="style='display: none;'";
				if ( count($Host->get('additionalMACs')) )
				{
					$style="";
				}
				$addMacs = $Host->get('additionalMACs');
				$baseMacId = 0;
				echo ( "<tr id='additionalMACsRow'" . (!count($addMacs) ? ' style="display: none"' : '') . "><td>"._("Additional MACs").":</td><td id='additionalMACsCell'>" );
				if ( $addMacs != null )
				{
					for( $i = 0; $i < count( $addMacs);$i++ )
					{
						$m = $addMacs[$i];
						if( $m != null )
						{
							echo ( "<div><input class='additionalMAC' type='text' name='additionalMACs[]' value='" . $m . "' /> <span class=\"icon icon-remove remove-mac hand\" title=\"Remove MAC\"></span> <span class=\"mac-manufactor\"></span></div>" );
							$baseMacId = $i+1;
						}
							
						
					}
				}
				echo ( "</td></tr>" );
				
				// Pending MACs addresses
				// Try as this might fail - do not why just yet
				try
				{
					$pendingMacs = $hostMan->getPendingMacAddressesForHost( $host );
					if ( $pendingMacs != null && count($pendingMacs) > 0 )
					{
						for( $i = 0; $i < count( $pendingMacs ); $i++ )
						{
							$pMac = $pendingMacs[$i];
							if ( $pMac != null )
							{
								echo ( "<tr><td>". ( $i==0 ? (_("Pending MAC Addresses").":") : "") . "</td>" );
								echo ( "<td class=\"pending-mac\"><span class=\"mac\">" . (String)$pMac . "</span>&nbsp;<a href=\"?node=$node&sub=$sub&id=$id&tab=$tab&confirmMac=" . base64_encode((String)$pMac) . "\"><span class=\"icon icon-tick\"></span></a> <span class=\"mac-manufactor\"></span></td></tr>" );
							}
						}
					}
				}
				catch (Exception $e)
				{
					print '<tr><td colspan="2">' . _('Error retrieving Pending MAC addresses. Have you upgraded your DB schema to the latest version?') . '</td>';
				}
				
				echo ( "<tr><td>"._("Host Description").":</td><td><textarea name=\"description\" rows=\"5\" cols=\"40\">" . $Host->getDescription() . "</textarea></td></tr>" );
				echo ( "<tr><td>"._("Host Image").":</td><td>" );

				$Image = $Host->getImage();
				if ($Image->isValid())
				{
					$hostImgId = $Image->get('id');
				}

				// TODO use image manager for this not a raw query
				$sql = "select * from images order by imageName";
				$res = mysql_query( $sql, $conn ) or die( mysql_error() );
				echo ( "<select name=\"image\" size=\"1\">" );
				echo ( "<option value=\"\"></option>" );
				while( $ar1 = mysql_fetch_array( $res ) )
				{
					$selected = "";
					if ( $hostImgId == $ar1["imageID"] )
						$selected = "selected=\"selected\"";
					echo ( "<option value=\"" . $ar1["imageID"] . "\" $selected>" . $ar1["imageName"] . " (" . $ar1["imageID"] . ")</option>" );
				}
				echo ( "</select>" );

				//echo ( "<tr><td>"._("Host OS").":</td><td>" );
				//	echo ( $FOGCore->getClass('OSManager')->buildSelectBox($Host->getImage()->getOS()->get('id')) );
				echo ( "</td></tr>" );
				echo ( "<tr><td>"._("Host Kernel").":</td><td><input class=\"smaller\" type=\"text\" name=\"kern\" value=\"" . $Host->get('kernel') . "\" /></td></tr>" );
				echo ( "<tr><td>"._("Host Kernel Arguments").":</td><td><input class=\"smaller\" type=\"text\" name=\"args\" value=\"\" /></td></tr>" );
				echo ( "<tr><td>"._("Host Primary Disk").":</td><td><input class=\"smaller\" type=\"text\" name=\"dev\" value=\"" . $Host->get('kernelDevice') . "\" /></td></tr>" );		
				echo ( "<tr><td colspan=2><font class=\"smaller\"><center><input type=\"hidden\" name=\"updategen\" value=\"1\" /><input class=\"smaller\" type=\"submit\" value=\""._("Update")."\" /></center></font></td></tr>" );
			echo ( "</table>" );
			echo ( "</form>" );
		}

		if ( $_GET["tab"] == "ad" )
		{
			?>
			<h2><?php print _("Active Directory"); ?></h2>
			<?php
			echo ( "<form method=\"POST\" action=\"?node=$node&sub=$sub&tab=$tab&id=$id\">" );
			echo ( "<table cellpadding=0 cellspacing=0 border=0 width=\"100%\">" );
				$usedomain = "";
				if ( $Host->get('useAD') == '1' )
					$usedomain = " checked=\"checked\" ";
				echo ( "<tr><td>"._("Join Domain after image task").":</td><td><input id='adEnabled' type=\"checkbox\" name=\"domain\" $usedomain /></td></tr>" );
				echo ( "<tr><td>"._("Domain name").":</td><td><input id=\"adDomain\" class=\"smaller\" type=\"text\" name=\"domainname\" value=\"" . $Host->get('ADDomain') . "\" /></td></tr>" );
				echo ( "<tr><td>"._("Organizational Unit").":<br> <span class=\"lightColor\">"._("(Blank for default)")."</span></td><td><input size=\"50\" id=\"adOU\" class=\"smaller\" type=\"text\" name=\"ou\" value=\"" . $Host->get('ADOU') . "\" /></td></tr>" );
				echo ( "<tr><td>"._("Domain Username").":</td><td><input id=\"adUsername\" class=\"smaller\" type=\"text\" name=\"domainuser\" value=\"" . $Host->get('ADUser') . "\" /></td></tr>" );
				echo ( "<tr><td>"._("Domain Password").":</td><td><input id=\"adPassword\" class=\"smaller\" type=\"text\" name=\"domainpassword\" value=\"" . $Host->get('ADPass') . "\" /> <span class=\"lightColor\">"._("(Must be encrypted)")."</span></td></tr>" );
				echo ( "<tr><td colspan=2><center><input type=\"hidden\" name=\"updatead\" value=\"1\" /><input type=\"submit\" value=\""._("Update")."\" /></center></td></tr>" );
			echo ( "</table>" );

			echo ( "</form>" );
		}

		if ( $_GET["tab"] == "tasks" )
		{
			?>
			<h2><?php print _("Basic Imaging Tasks"); ?></h2>
			<table cellpadding="0" cellspacing="0" border="0" width="100%">
				<tr>
					<td class="c" width="50"><a href="?node=tasks&type=host&direction=down&noconfirm=<?php echo $id; ?>"><img src="./images/senddebug.png" /><p><?php echo(_("Deploy")); ?></p></a></td>
					<td><p><?php echo(_("Deploy action will send an image saved on the FOG server to the client computer with all included snapins.")); ?></p></td></tr>
				<tr>
					<td class="c" width="50"><a href="?node=tasks&type=host&direction=up&noconfirm=<?php echo $id; ?>"><img src="./images/restoredebug.png" /><p><?php echo(_("Upload")); ?></p></a></td>
					<td><p><?php echo(_("Upload will pull an image from a client computer that will be saved on the server.")); ?></p></td></tr>
				<tr>
					<td class="c" width="50"><a href="?node=tasks&sub=advanced&hostid=<?php echo $id; ?>"><img src="./images/host-advanced.png" /><p><?php echo(_("Advanced")); ?></p></a></td>
					<td><p><?php echo(_("View advanced tasks for this host.")); ?></p></td>
				</tr>
			</table>
			<?php
		}

		if ( $_GET["tab"] == "service" )
		{
			?>
			<h2><?php print _("Service Configuration"); ?></h2>
			<?php

			$sql = "SELECT * FROM moduleStatusByHost WHERE msHostID = '$id'";
			$res = mysql_query( $sql, $conn ) or criticalError( mysql_error(), _("FOG :: Database error!") );
			$checked = " checked=\"checked\" ";
			$ucchecked = " checked=\"checked\" ";
			$dmchecked = " checked=\"checked\" ";
			$alochecked = " checked=\"checked\" ";
			$gfchecked = " checked=\"checked\" ";
			$snapinchecked = " checked=\"checked\" ";
			$hostnamechangerchecked = " checked=\"checked\" ";
			$clientupdaterchecked = " checked=\"checked\" ";
			$hostregisterchecked = " checked=\"checked\" ";
			$printermanagerchecked = " checked=\"checked\" ";					
			$taskrebootchecked = " checked=\"checked\" ";
			$usertrackerchecked = " checked=\"checked\" ";	
												
			while( $ar = mysql_fetch_array( $res ) )
			{
				if ( $ar["msModuleID"] == "dircleanup" && $ar["msState"] == "0" )
					$checked="";

				if ( $ar["msModuleID"] == "usercleanup" && $ar["msState"] == "0" )
					$ucchecked="";

				if ( $ar["msModuleID"] == "displaymanager" && $ar["msState"] == "0" )
					$dmchecked="";

				if ( $ar["msModuleID"] == "autologout" && $ar["msState"] == "0" )
					$alochecked="";

				if ( $ar["msModuleID"] == "greenfog" && $ar["msState"] == "0" )
					$gfchecked="";
					
				if ( $ar["msModuleID"] == "snapin" && $ar["msState"] == "0" )
					$snapinchecked="";							
					
				if ( $ar["msModuleID"] == "hostnamechanger" && $ar["msState"] == "0" )
					$hostnamechangerchecked="";					
					
				if ( $ar["msModuleID"] == "clientupdater" && $ar["msState"] == "0" )
					$clientupdaterchecked="";										
					
				if ( $ar["msModuleID"] == "hostregister" && $ar["msState"] == "0" )
					$hostregisterchecked="";
					
				if ( $ar["msModuleID"] == "printermanager" && $ar["msState"] == "0" )
					$printermanagerchecked="";
					
				if ( $ar["msModuleID"] == "taskreboot" && $ar["msState"] == "0" )
					$taskrebootchecked="";							

				if ( $ar["msModuleID"] == "usertracker" && $ar["msState"] == "0" )
					$usertrackerchecked="";
			}

			echo ( "<form method=\"post\" action=\"?node=$node&sub=$sub&id=$id&tab=$tab&updatemodulestatus=1\">" );
				echo ( "<center><table cellpadding=0 cellspacing=0 border=0 width=\"100%\">" );
					echo ( "<tr><td width=\"270\">&nbsp;"._("Hostname Changer Enabled?")."</td><td>&nbsp;<input type=\"checkbox\" name=\"hostnamechanger\" $hostnamechangerchecked /></td><td><span class=\"icon icon-help hand\" title=\"" . _("This setting will enable or disable the hostname changer module on this specific host.  If the module is globally disabled, this setting is ignored.") . "\"></span></td></tr>" );						
					echo ( "<tr><td width=\"270\">&nbsp;"._("Directory Cleaner Enabled?")."</td><td>&nbsp;<input type=\"checkbox\" name=\"dircleanen\" $checked /></td><td><span class=\"icon icon-help hand\" title=\"" . _("This setting will enable or disable the directory cleaner service module on this specific host.  If the module is globally disabled, this setting is ignored.") . "\"></span></td></tr>" );
					echo ( "<tr><td width=\"270\">&nbsp;"._("User Cleanup Enabled?")."</td><td>&nbsp;<input type=\"checkbox\" name=\"usercleanen\" $ucchecked /></td><td><span class=\"icon icon-help hand\" title=\"" . _("This setting will enable or disable the user cleaner service module on this specific host.  If the module is globally disabled, this setting is ignored.  The user clean up service will remove all stale users on the local machine, accept for user accounts that are whitelisted.  This is typically used when dynamic local users is implemented on the workstation.") . "\"></span></td></tr>" );
					echo ( "<tr><td width=\"270\">&nbsp;"._("Display Manager Enabled?")."</td><td>&nbsp;<input type=\"checkbox\" name=\"displaymanager\" $dmchecked /></td><td><span class=\"icon icon-help hand\" title=\"" . _("This setting will enable or disable the display manager service module on this specific host.  If the module is globally disabled, this setting is ignored. ") . "\"></span></td></tr>" );
					echo ( "<tr><td width=\"270\">&nbsp;"._("Auto Log Out Enabled?")."</td><td>&nbsp;<input type=\"checkbox\" name=\"alo\" $alochecked /></td><td><span class=\"icon icon-help hand\" title=\"" . _("This setting will enable or disable the auto log out service module on this specific host.  If the module is globally disabled, this setting is ignored. ") . "\"></span></td></tr>" );
					echo ( "<tr><td width=\"270\">&nbsp;"._("Green FOG Enabled?")."</td><td>&nbsp;<input type=\"checkbox\" name=\"gf\" $gfchecked /></td><td><span class=\"icon icon-help hand\" title=\"" . _("This setting will enable or disable the green fog service module on this specific host.  If the module is globally disabled, this setting is ignored. ") . "\"></span></td></tr>" );							
					echo ( "<tr><td width=\"270\">&nbsp;"._("Snapin Enabled?")."</td><td>&nbsp;<input type=\"checkbox\" name=\"snapin\" $snapinchecked /></td><td><span class=\"icon icon-help hand\" title=\"" . _("This setting will enable or disable the snapin service module on this specific host.  If the module is globally disabled, this setting is ignored. ") . "\"></span></td></tr>" );														
					echo ( "<tr><td width=\"270\">&nbsp;"._("Client Updater Enabled?")."</td><td>&nbsp;<input type=\"checkbox\" name=\"clientupdater\" $clientupdaterchecked /></td><td><span class=\"icon icon-help hand\" title=\"" . _("This setting will enable or disable the client updater service module on this specific host.  If the module is globally disabled, this setting is ignored. ") . "\"></span></td></tr>" );														
					echo ( "<tr><td width=\"270\">&nbsp;"._("Host Registration Enabled?")."</td><td>&nbsp;<input type=\"checkbox\" name=\"hostregister\" $hostregisterchecked /></td><td><span class=\"icon icon-help hand\" title=\"" . _("This setting will enable or disable the host register service module on this specific host.  If the module is globally disabled, this setting is ignored. ") . "\"></span></td></tr>" );														
					echo ( "<tr><td width=\"270\">&nbsp;"._("Printer Manager Enabled?")."</td><td>&nbsp;<input type=\"checkbox\" name=\"printermanager\" $printermanagerchecked /></td><td><span class=\"icon icon-help hand\" title=\"" . _("This setting will enable or disable the printer manager service module on this specific host.  If the module is globally disabled, this setting is ignored. ") . "\"></span></td></tr>" );														
					echo ( "<tr><td width=\"270\">&nbsp;"._("Task Reboot Enabled?")."</td><td>&nbsp;<input type=\"checkbox\" name=\"taskreboot\" $taskrebootchecked /></td><td><span class=\"icon icon-help hand\" title=\"" . _("This setting will enable or disable the task reboot service module on this specific host.  If the module is globally disabled, this setting is ignored. ") . "\"></span></td></tr>" );														
					echo ( "<tr><td width=\"270\">&nbsp;"._("User Tracker Enabled?")."</td><td>&nbsp;<input type=\"checkbox\" name=\"usertracker\" $usertrackerchecked /></td><td><span class=\"icon icon-help hand\" title=\"" . _("This setting will enable or disable the user tracker service module on this specific host.  If the module is globally disabled, this setting is ignored. ") . "\"></span></td></tr>" );														
					echo ( "<tr><td colspan='3'><center><input type=\"submit\" value=\""._("Update")."\" /></center></td></tr>" );
				echo ( "</table></center>" );
			echo ( "<p class=\"titleBottomLeft\">"._("Host Screen Resolution")."</p>" );
				echo ( "<center><table cellpadding=0 cellspacing=0 border=0 width=\"100%\">" );
					$x = "";
					$y = "";
					$r = "";

					$sql = "SELECT
							*
						FROM
							hostScreenSettings
						WHERE
							hssHostID = '$id'";
					$res = mysql_query( $sql, $conn ) or criticalError( mysql_error(), "FOG :: Database error!" );
					while( $ar = mysql_fetch_array( $res ) )
					{
						$x = $ar["hssWidth"];
						$y = $ar["hssHeight"];
						$r = $ar["hssRefresh"];
					}

					echo ( "<tr><td width=\"270\">&nbsp;"._("Screen Width (in pixels)")."</td><td>&nbsp;<input type=\"text\" name=\"x\" value=\"$x\"/></td><td><span class=\"icon icon-help hand\" title=\"" . _("This setting defines the screen horizontal resolution to be used with this host.  Leaving this field blank will force this host to use the global default setting") . "\"></span></td></tr>" );
					echo ( "<tr><td width=\"270\">&nbsp;"._("Screen Height (in pixels)")."</td><td>&nbsp;<input type=\"text\" name=\"y\" value=\"$y\"/></td><td><span class=\"icon icon-help hand\" title=\"" . _("This setting defines the screen vertial resolution to be used with this host.  Leaving this field blank will force this host to use the global default setting") . "\"></span></td></tr>" );
					echo ( "<tr><td width=\"270\">&nbsp;"._("Screen Refresh Rate")."</td><td>&nbsp;<input type=\"text\" name=\"r\" value=\"$r\" /></td><td><span class=\"icon icon-help hand\" title=\"" . _("This setting defines the screen refresh rate to be used with this host.  Leaving this field blank will force this host to use the global default setting") . "\"></span></td></tr>" );
					echo ( "<tr><td colspan='3'><center><input type=\"submit\" value=\""._("Update")."\" /></center></td></tr>" );
				echo ( "</table></center>" );
			echo ( "<p class=\"titleBottomLeft\">"._("Auto Log Out Settings")."</p>" );
				echo ( "<center><table cellpadding=0 cellspacing=0 border=0 width=\"100%\">" );
					$tme = "";

					$sql = "SELECT
							*
						FROM
							hostAutoLogOut
						WHERE
							haloHostID = '$id'";
					$res = mysql_query( $sql, $conn ) or criticalError( mysql_error(), "FOG :: Database error!" );
					while( $ar = mysql_fetch_array( $res ) )
					{
						$tme = $ar["haloTime"];
					}

					echo ( "<tr><td width=\"270\">&nbsp;"._("Auto Log Out Time (in minutes)")."</td><td>&nbsp;<input type=\"text\" name=\"tme\" value=\"$tme\"/></td><td><span class=\"icon icon-help hand\" title=\"" . _("This setting defines the time to auto log out this host.") . "\"></span></td></tr>" );
					echo ( "<tr><td colspan='3'><center><input type=\"submit\" value=\""._("Update")."\" /></center></td></tr>" );
				echo ( "</table></center>" );
			echo ( "</form>" );
		}

		if ( $_GET["tab"] == "snapins"  )
		{
			?>
			<h2><?php print _("Snapins"); ?></h2>
			<?php
			echo ( "<table cellpadding=0 cellspacing=0 border=0 width=\"100%\">" );
					echo ( "<tr class=\"header\"><td><font class=\"smaller\">&nbsp;<b>"._("Snapin Name")."</b></font></td><td><font class=\"smaller\"><b>"._("Remove")."</b></font></td></tr>" );
					$sql = "SELECT
							*
						FROM
							snapinAssoc
							inner join snapins on ( snapinAssoc.saSnapinID = snapins.sID )
						WHERE
							snapinAssoc.saHostID = '$id'
						ORDER BY
							snapins.sName";
					$resSnap = mysql_query( $sql, $conn ) or die( mysql_error() );
					if ( mysql_num_rows( $resSnap ) > 0 )
					{
						$i = 0;
						while ( $arSp = mysql_fetch_array( $resSnap ) )
						{
							$bgcolor = "alt1";
							if ( $i++ % 2 == 0 ) $bgcolor = "alt2";
							echo ( "<tr class=\"$bgcolor\"><td>" . $arSp["sName"] . "</td><td><a href=\"?node=$node&sub=$sub&id=" . $id . "&delsnaplinkid=" . $arSp["sID"] . "&tab=$tab\"><img src=\"images/deleteSmall.png\" class=\"link\" /></a></td></tr>" );
						}
					}
					else
					{
						echo ( "<tr><td colspan=\"2\" class=\"c\">"._("No snapins linked to this host.")."</td></tr>" );
					}
			echo ( "</table>" );

			echo ( "<div class=\"hostgroup\">" );
				echo ( "<form method=\"POST\" action=\"?node=$node&sub=$sub&id=$id&tab=$tab\">" );
				echo("<p>"._("Add new snapin package.")."</p>");
				echo $FOGCore->getClass('SnapinManager')->buildSelectBox();
				echo( "<p><input type=\"submit\" value=\""._("Add Snapin")."\" /></p>" );
				echo ( "</form>" );
			echo ( "</div>" );
		}

		if ( $_GET["tab"] == "virus"  )
		{
			?>
			<h2><?php print _("Virus History")." (<a href=\"?node=$node&sub=$sub&id=" . $id . "&delvid=all&tab=$tab\">"._("clear all history")."</a>)"; ?></h2>
			<?php
			echo ( "<table cellpadding=0 cellspacing=0 border=0 width=100%>" );
					echo ( "<tr class=\"header\"><td>&nbsp;<b>"._("Virus Name")."</b></td><td><b>"._("File")."</b></td><td><b>"._("Mode")."</b></td><td><b>"._("Date")."</b></td><td><b>"._("Clear")."</b></td></tr>" );
					$sql = "SELECT
							*
						FROM
							virus
						WHERE
							vHostMAC = '" . mysql_real_escape_string(  $Host->get('mac') ) . "'
						ORDER BY
							vDateTime, vName";
					$resSnap = mysql_query( $sql, $conn ) or die( mysql_error() );
					if ( mysql_num_rows( $resSnap ) > 0 )
					{
						$i = 0;
						while ( $arSp = mysql_fetch_array( $resSnap ) )
						{
							$bgcolor = "alt1";
							if ( $i++ % 2 == 0 ) $bgcolor = "alt2";
							echo ( "<tr class=\"$bgcolor\"><td>&nbsp;<a href=\"http://www.google.com/search?q=" .  $arSp["vName"] . "\" target=\"_blank\">" . $arSp["vName"] . "</a></td><td>" . $arSp["vOrigFile"] . "</td><td>" . avModeToString( $arSp["vMode"] ) . "</td><td>" . $arSp["vDateTime"] . "</td><td><a href=\"?node=$node&sub=$sub&id=" . $id . "&delvid=" . $arSp["vID"] . "\"><img src=\"images/deleteSmall.png\" class=\"link\" /></a></td></tr>" );
						}
					}
					else
					{
						echo ( "<tr><td colspan=\"5\" class=\"c\">"._("No Virus Information Reported for this host.")."</td></tr>" );
					}
			echo ( "</table>" );
		}

		if ( $_GET["tab"] == "delete"  )
		{
			?>
			<h2><?php print _("Delete Host"); ?></h2>
			<?php

			echo ( "<p>"._("Click on the icon below to delete this host from the FOG database.")."</p>" );
			echo ( "<p ><a href=\"?node=" . $_GET["node"] . "&rmhostid=" . $id . "\"><img class=\"link\" src=\"images/delete.png\"></a></p>" );
		}
	}
	else
	{
		echo ( "<center><font class=\"smaller\">"._("Invalid host ID Number.")."</font></center>" );
	}
}