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

// Require FOG Base
require_once('../commons/config.php');
require_once(BASEPATH . '/commons/init.php');
require_once(BASEPATH . '/commons/init.database.php');

/*
 *  Possible return codes
 *  "#!db" => Database error
 *  "#!im" => Invalid MAC Format
 *  "#!ih" => Invalid Host format
 *  "#!ma" => Mac address already exists.
 *  "#!er" => Other error.
 *  "#!ok" => registration successful.
 *
 */

if ( isset( $_POST["mac"] ) )
{
	$ifconfig = base64_decode( $_POST["mac"] );

	if ( $ifconfig != null )
	{
		$arIfconfig = explode( "HWaddr", $ifconfig  );
		if ( count( $arIfconfig ) == 2 )
		{
			$conn = @mysql_connect( DATABASE_HOST, DATABASE_USERNAME, DATABASE_PASSWORD);
			$mac =  mysql_real_escape_string( strtolower( trim($arIfconfig[1]) ) );
			if ( strlen( trim($mac) ) == 17 )
			{
				if ( isValidMACAddress( $mac ) )
				{			
					$macsimple = str_replace( ":", "", $mac );
					
					
					if ( $conn )
					{	
						if ( ! @mysql_select_db( DATABASE_NAME, $conn ) ) die( mysql_error() );
						
						if ( isSafeHostName( $macsimple ) )
						{
							$sql = "select count(*) as cnt from hosts where hostMAC = '" . $mac . "'";
							$res = mysql_query( $sql, $conn ) or die( mysql_error() );
							while( $ar = mysql_fetch_array( $res ) )
							{
								if ( $ar["cnt"] == 0 )
								{	
									if ( $_POST["advanced"] == "1" )
									{
										$host=mysql_real_escape_string(trim(base64_decode( $_POST["host"] )));
										$ip=mysql_real_escape_string(trim(base64_decode( $_POST["ip"] )));
										$imageid=mysql_real_escape_string(trim(base64_decode( $_POST["imageid"] )));
										$osid=mysql_real_escape_string(trim(base64_decode( $_POST["osid"] )));
										$primaryuser=mysql_real_escape_string(trim(base64_decode( $_POST["primaryuser"] )));
										$other1=mysql_real_escape_string(trim(base64_decode( $_POST["other1"] )));
										$other2=mysql_real_escape_string(trim(base64_decode( $_POST["other2"] )));
										$doimage=mysql_real_escape_string(trim( $_POST["doimage"] ) );
										
										$strDoAD = "0";
										$strADDomain = "";
										$strADOU = "";
										$strADUser = "";
										$strADPass = "";
										
										if ( $_POST["doad"] == "1" )
										{
											$strDoAD="1";
											$strADDomain = mysql_real_escape_string($FOGCore->getSetting( "FOG_AD_DEFAULT_DOMAINNAME"));
											$strADOU = mysql_real_escape_string($FOGCore->getSetting( "FOG_AD_DEFAULT_OU"));
											$strADUser = mysql_real_escape_string($FOGCore->getSetting( "FOG_AD_DEFAULT_USER"));
											$strADPass = mysql_real_escape_string($FOGCore->getSetting( "FOG_AD_DEFAULT_PASSWORD"));
										}
										
										$realhost = $macsimple;
										$realimageid = "";
										$realosid = "";
										if ( $host != null && strlen($host) > 0 && isSafeHostName($host ) )
										{
											$realhost = $host;
										}
										
										if ( $imageid != null && is_numeric( $imageid ) )
											$realimageid = $imageid;
											
										if ( $osid != null && is_numeric( $osid ) )
											$realosid = $osid;										

										$desc = mysql_real_escape_string(_("Created by FOG Reg on")." " . date("F j, Y, g:i a") );	
																																				
										$sql = "insert into hosts(hostName, hostDesc, hostCreateDate, hostCreateBy, hostMAC, hostIP, hostImage, hostOS, hostUseAD, hostADDomain, hostADOU, hostADUser, hostADPass) 
							                    		values('" . $realhost . "', '" . $desc . "', NOW(), 'FOGREG', '" . $mac . "', '$ip', '$realimageid', '$realosid', '$strDoAD', '$strADDomain', '$strADOU', '$strADUser', '$strADPass')";	
							                    										
										if ( mysql_query( $sql, $conn ) )
										{
											$sql = "select hostID from hosts where hostMAC = '" . $mac . "'";
											$res = mysql_query( $sql, $conn ) or die( mysql_error() );
											if ( mysql_num_rows( $res ) == 1 )
											{
												while( $ar = mysql_fetch_array( $res ) )
												{
													if ( is_numeric( $ar["hostID"] ) && $ar["hostID"] !== null )
													{
														$hid = mysql_real_escape_string( $ar["hostID"] );
														$sql = "select count(*) as cnt from inventory where iHostID = '" . $hid . "'";
														$res = mysql_query( $sql, $conn ) or die( mysql_error() );
														if ( $ar = mysql_fetch_array( $res ) )
														{
															if ( $ar["cnt"] == 1 )
															{
																$sql = "UPDATE 
																		inventory 
																	SET
																		iPrimaryUser = '$primaryuser',
																		iOtherTag = '$other1',
																		iOtherTag1 = '$other2'																		
																	WHERE 
																		iHostID = '$hid'";
																if ( mysql_query( $sql, $conn ))
																{
																	echo _("Done");
																}
																else
																	echo _("Failed (2)");
															}
															else
															{
																$sql = "INSERT INTO 
																		inventory  (iHostID, iPrimaryUser, iOtherTag, iOtherTag1, iCreateDate )
																		values ('$hid', '$primaryuser', '$other1', '$other2', NOW() )";
																if ( mysql_query( $sql, $conn ))
																{
																	if ( $doimage == "1" )
																	{
																		$imageMember = getImageMemberFromHostID( $conn, $hid );
																		if ( $imageMember != null )
																		{
																			$other .= (" chkdsk=" . ($FOGCore->getSetting("FOG_DISABLE_CHKDSK") == "1" ? '0' : '1'));
																			if ( $FOGCore->getSetting("FOG_CHANGE_HOSTNAME_EARLY") == "1" )
																				$other .= (" hostname=" .  ( $imageMember->getHostName()));
																		
																			$tmp;
																			if( createImagePackage($conn, $imageMember, "AutoRegTask", $tmp, false, true, "", "", $other ) )
																			{
																				echo _("Done, with imaging!");
																			}
																			else
																			{
																				echo _("Done, but without imaging").": $tmp";
																			}
																			
																		}
																		else
																			echo _("Done, but without imaging!");
																	}
																	else
																		echo _("Done");	
																}
																else
																	echo _("Failed (3)");															
															}
															
														}
													}
												}
											}
											else
												echo _("FAILED (1)");

										}
										else
											echo _("FAILED (0)");	

									}
									else
									{	
										//basic registration
																
										$desc = mysql_real_escape_string(_("Created by FOG Reg on")." " . date("F j, Y, g:i a") );																											
										
										$blDoAuto = $FOGCore->getSetting( "FOG_QUICKREG_AUTOPOP") == "1";
										if ( $blDoAuto )
										{
											$imageid = mysql_real_escape_string($FOGCore->getSetting( "FOG_QUICKREG_IMG_ID"));
 											$osid = mysql_real_escape_string($FOGCore->getSetting( "FOG_QUICKREG_OS_ID"));
 										
											$autoregSysName = mysql_real_escape_string($FOGCore->getSetting( "FOG_QUICKREG_SYS_NAME"));
 											$autoregSysNumber = mysql_real_escape_string($FOGCore->getSetting( "FOG_QUICKREG_SYS_NUMBER"));

 											
 											$paddingLen = substr_count(  $autoregSysName, "*" );
 											$paddingString = null;
 											$realhost  = null;
 											if ( $paddingLen > 0 )
 											{
	 											$paddingString = str_repeat  (  "*",  $paddingLen );

	 											$paddedInsert = str_pad($autoregSysNumber, $paddingLen, "0", STR_PAD_LEFT);
 												$realhost = str_replace($paddingString,$paddedInsert,$autoregSysName);
 											}
 											else
 												$realhost = $autoregSysName;
 											
 											$sql = "insert into 
 													hosts(hostName, hostDesc, hostCreateDate, hostCreateBy, hostMAC, hostImage, hostOS) 
		 							                    		values('" . $realhost . "', '" . $desc . "', NOW(), 'FOGREG', '" . $mac . "', '$imageid', '$osid')";
 							                    		if ( mysql_query( $sql, $conn ) )
 							                    		{
												$imageMember = getImageMemberFromHostID( $conn, mysql_insert_id($conn) );
												$FOGCore->setSetting("FOG_QUICKREG_SYS_NUMBER",($autoregSysNumber+1));
												if ( $imageMember != null )
												{
													$tmp;
													if( createImagePackage($conn, $imageMember, "", $tmp, false ) )
													{
														echo _("Done, with imaging!");
													}
													else
													{
														echo _("Done, but without imaging").": $tmp";
													}
												}
												else
													echo _("Done, but without imaging!");
 							                    		}
 							                    		else
												echo _("FAILED (0)");

										}
										else
										{
											$sql = "insert into hosts(hostName, hostDesc, hostCreateDate, hostCreateBy, hostMAC) 
									            		values('" . $macsimple . "', '" . $desc . "', NOW(), 'FOGREG', '" . $mac . "')";									
											if ( mysql_query( $sql, $conn ) )
												echo _("Done");
											else
												echo _("FAILED");						                    		
										}
									}
								}
								else
									echo _("Exists");
							}								
						}
						else
							echo " "._("Unsafe Hostname").": $macsimple";
					}
					else
						echo " "._("Unable to connect to database, host not imported!");
				}
				else
					echo ( " "._("Invalid MAC Address format!") );
			}
			else
				echo " "._("Invalid MAC address (3)");
		}
		else
			echo " "._("Invalid MAC address (2)");
	}
	else
		echo " "._("Invalid MAC address (1)");
}
else
	echo " "._("Invalid MAC address (0)");
?>
