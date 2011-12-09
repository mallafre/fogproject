<?php
/*
 *  FOG - Free, Open-Source Ghost is a computer imaging solution.
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

if ( IS_INCLUDED !== true ) die( _("Unable to load system configuration information.") );

$uploadErrors = "";
$numSuccess = 0;
$numFailed = 0;
$numAlreadyExist = 0;
$totalRows = 0;

if ($_FILES["file"] != null  )
{
	if ( $_FILES["file"]["error"] > 0 )
	{
		msgBox( _("Error").": " . $_FILES["file"]["error"] );
	}
	else
	{
		if ( file_exists($_FILES["file"]["tmp_name"]) )
		{
			$handle = fopen($_FILES["file"]["tmp_name"], "r");
			while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) 
			{
				$totalRows++;
				if ( count( $data ) < 7 && count( $data ) >= 2 )
				{
					try
					{
						// Error checking
						if ($FOGCore->getClass('HostManager')->doesHostExistWithMac(new MACAddress($data[0])))
						{
							throw new Exception('A Host with this MAC Address already exists');
						}
					
						$Host = new Host(array(
							'name'		=> $data[1],
							'description'	=> $data[3] . ' ' . _('Uploaded by batch import on'),
							'ip'		=> $data[2],
							'imageID'	=> $data[5],
							'createdTime'	=> time(),
							'createdBy'	=> $currentUser->get('name'),
							'mac'		=> $data[0],
							// Move to Image Object
							'osID'		=> $data[4]
						));
						
						if ($Host->save())
						{
							$numSuccess++;
						}
						else
						{
							$numFailed++;
						}
							
					}
					catch (Exception $e )
					{
						$numFailed++;
						$uploadErrors .= _("Row").": " . $totalRows . "- ".$e->getMessage()."<br />";
					}					
				}
				else
				{
					$numFailed++;
					$uploadErrors .= _("Row").": " . $totalRows . "- "._("Invalid number of cells.")."<br />";
				}
			}
			fclose($handle);	
		}
	}
	?>
	<h2><?php print _("Upload Results"); ?></h2>
	<?php
	echo ( "<center><table cellpadding=0 cellspacing=0 border=0 width=90%>" );
		echo ( "<tr><td>"._("Total Rows")."</font></td><td>$totalRows</td></tr>" );
		echo ( "<tr><td>"._("Successful Hosts")."</td><td>$numSuccess</td></tr>" );				
		echo ( "<tr><td>"._("Existing Hosts")."</td><td>$numAlreadyExist</td></tr>" );		
		echo ( "<tr><td>"._("Failed Hosts")."</td><td>$numFailed</td></tr>" );				
		echo ( "<tr><td>"._("Errors")."</td><td>$uploadErrors</td></tr>" );						
	echo ( "</table></center>" );
}
else
{
	?>
	<h2><?php print _("Upload Host List"); ?></h2>
	<?php
	echo ( "<form enctype=\"multipart/form-data\" method=\"POST\" action=\"?node=$_GET[node]&sub=$_GET[sub]\">" );
	echo ( "<center><table cellpadding=0 cellspacing=0 border=0 width=90%>" );
		echo ( "<tr><td>"._("CSV File").":</font></td><td><input class=\"smaller\" type=\"file\" name=\"file\" value=\"\" /></td></tr>" );
		echo ( "<tr><td colspan=2><font><center><input class=\"smaller\" type=\"submit\" value=\""._("Upload CSV")."\" /></center></font></td></tr>" );				
	echo ( "</table></center>" );
	echo ( "</form>" );
	echo ( "<p class=\"titleBottom\">" );
		echo (_("This page allows you to upload a CSV file of hosts into FOG to ease migration.  Right click ")."<a href=\"./other/hostimport.csv\">"._("here")."</a> "._("and select <strong>Save target as...</strong> or <strong>Save link as...</strong>  to download a template file.  The only fields that are required are hostname and MAC address.  Do <strong>NOT</strong> include a header row, and make sure you resave the file as a CSV file and not XLS!"));
	echo ( "</p>" );
}
