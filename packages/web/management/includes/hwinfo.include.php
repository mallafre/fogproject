<?php

if ( IS_INCLUDED !== true ) die( _("Unable to load system configuration information.") );

if ( $currentUser != null && $currentUser->isLoggedIn() )
{
?>
	<center>
	<table width="98%" cellpadding=0 cellspacing=0 border=0>
	<tr>
	<td>
		<div class="sub">
			<h2><?php print _("Server Information"); ?></h2>
<?php
			if ($id != null && $StorageNode = mysql_fetch_array(mysql_query("SELECT ngmHostname FROM nfsGroupMembers WHERE ngmID = '$id'", $conn)))
			{
				echo ( "<center><table cellpadding=0 cellspacing=0 border=0 width=90%>" );
				if ($ret = Fetch('http://' . $StorageNode['ngmHostname'] . '/fog/status/hw.php'))
				{
					$arRet = explode( "\n", $ret );
					$section = 0; //general
					
					$arGeneral = array();
					$arFS = array();
					$arNIC = array();
					foreach( $arRet as $line ) 
					{
						$line = trim( $line );
						if ( $line == "@@start" )
						{

						}
						else if ( $line == "@@general" )
						{
							$section = 0;
						}
						else if ( $line == "@@fs" ) 
						{
							$section = 1;
						}
						else if ( $line == "@@nic" ) 
						{
							$section = 2;
						}	
						else if ( $line == "@@end" ) 
						{
							$section = 3;
						}											
						else
						{
							if ( $section == 0 )
							{
								$arGeneral[] = $line;												
							}
							else if ( $section == 1 )
							{
								$arFS[] = $line;
							}
							else if ( $section == 2 )
							{
								$arNIC[] = $line;
							}
						}
						
					}

					if ( count( $arGeneral ) == 11 )
					{
						echo ( "<tr><td colspan=2 class=\"hwHeader\"><b>"._("General Information")."</b></td></tr>" );
						echo ( "<tr class=\"hwColor\"><td>"._("Storage Node")."</td><td>" . $_SESSION["hwname"] . "</td></tr>" );
						echo ( "<tr class=\"hwWhite\"><td>"._("IP")."</td><td>" . $_SESSION["hwinfo"] . "</td></tr>" );
						echo ( "<tr class=\"hwColor\"><td>"._("Kernel")."</td><td>" . $arGeneral[0] . "</td></tr>" );
						echo ( "<tr class=\"hwWhite\"><td>"._("Hostname")."</td><td>" . $arGeneral[1] . "</td></tr>" );
						echo ( "<tr class=\"hwColor\"><td>"._("Uptime")."</td><td>" . $arGeneral[2] . "</td></tr>" );
						echo ( "<tr class=\"hwWhite\"><td>"._("CPU Type")."</td><td>" . $arGeneral[3] . "</td></tr>" );
						echo ( "<tr class=\"hwColor\"><td>"._("CPU Count")."</td><td>" . $arGeneral[4] . "</td></tr>" );
						echo ( "<tr class=\"hwWhite\"><td>"._("CPU Model")."</td><td>" . $arGeneral[5] . "</td></tr>" );
						echo ( "<tr class=\"hwColor\"><td>"._("CPU Speed")."</td><td>" . $arGeneral[6] . "</td></tr>" );
						echo ( "<tr class=\"hwWhite\"><td>"._("CPU cache")."</td><td>" . $arGeneral[7] . "</td></tr>" );
						echo ( "<tr class=\"hwColor\"><td>"._("Total Memory")."</td><td>" . $arGeneral[8] . " MB</td></tr>" );
						echo ( "<tr class=\"hwWhite\"><td>"._("Used Memory")."</td><td>" . $arGeneral[9] . " MB</td></tr>" );
						echo ( "<tr class=\"hwColor\"><td>"._("Free Memory")."</td><td>" . $arGeneral[10] . " MB</td></tr>" );
						echo ( "<tr><td colspan=2 class=\"hwHeader\"><b>"._("File System Information")."</b></td></tr>" );
						echo ( "<tr class=\"hwColor\"><td>"._("Total Disk Space")."</td><td>" . $arFS[0] . "</td></tr>" );
						echo ( "<tr class=\"hwWhite\"><td>"._("Used Disk Space")."</td><td>" . $arFS[1] . "</td></tr>" );						
						echo ( "<tr><td colspan=2 class=\"hwHeader\"><b>"._("Network Information")."</b></td></tr>" );

						for( $i = 0; $i < count( $arNIC ); $i++ )
						{
							$arNicParts = explode( "$$", $arNIC[$i] );

							if ( count( $arNicParts ) == 5 ) 
							{
								echo ( "<tr class=\"hwColor\"><td>" . $arNicParts[0] . " "._("RX")."</td><td>" . round((($arNicParts[1] / 1024 ) /1024 ) /1024,2) . " GB</td></tr>" );
								echo ( "<tr class=\"hwWhite\"><td>" . $arNicParts[0] . " "._("TX")."</td><td>" . round((($arNicParts[2]/ 1024 ) /1024 ) /1024,2) . " GB</td></tr>" );
								echo ( "<tr class=\"hwColor\"><td>" . $arNicParts[0] . " "._("Errors")."</td><td>" . $arNicParts[3] . "</td></tr>" );
								echo ( "<tr class=\"hwWhite\"><td>" . $arNicParts[0] . " "._("Dropped")."</td><td>" . $arNicParts[4] . "</td></tr>" );
							}
						}
					}
				}
				else
				{
					echo ( "<p>"._("Unable to pull server information!")."</p>" );
				}
				echo ( "</table></center>" );
			}
			else
			{
				echo "<p>"._("Invalid Server Information!")."</p>";
			}
?>
		</div>
	</td></tr>
	</table>
<?php
}