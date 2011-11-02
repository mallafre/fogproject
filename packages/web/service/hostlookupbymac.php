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
 
require('../commons/config.php');
require(BASEPATH . '/commons/init.php');
require(BASEPATH . '/commons/init.database.php');

$mac = strtolower($_POST['mac']);

if ( isset($mac) )
{
	if ( isValidMACAddress( $mac ) )
	{
		if ( $_POST["username"] != null && $_POST["password"] != null )
		{
			$u = mysql_real_escape_string(base64_decode(trim($_POST["username"])));
			$p = mysql_real_escape_string(base64_decode(trim($_POST["password"])));
		
		
		
			 $sql = "select * from users where uName = '$u' and uPass = MD5('$p')";
			 $res = mysql_query( $sql, $conn ) or die( mysql_error() );
			 if ( mysql_num_rows( $res ) == 1 )
			 {
				$blFound = false;
				while( $ar = mysql_fetch_array( $res ) )
				{
					$blFound = true;
				}
			
				$hid = getHostID( $conn, $mac );
				if ( $hid != null )
				{
					$imageMember = getImageMemberFromHostID( $conn, $hid );
					if ( $imageMember != null )
					{
						$tmp;
						if( createImagePackage($conn, $imageMember, "", $tmp, false ) )
						{
							echo "#!ok";
						}
						else
						{
							echo "    $tmp";
						}
			
					}
					else
						echo "#ih";				
				}
				else
					echo "#ih";
			 }
			 else
			 {
				echo ( "#!il" );
			 }				
		}
		else
		{
			$sql = "select * from hosts left outer join images on ( hostImage = imageID ) where hostMAC = '" .  $mac . "'";
			$res = mysql_query( $sql, $conn ) or die( "#!db" );
			if( $ar = mysql_fetch_array( $res ) )
			{
				if ( isSafeHostName( $ar["hostName"] ) )
				{
					echo "  "._("Host").": " . $ar["hostName"] . "\\n";					
					echo "  "._("Image").": " . $ar["imageName"] . "\\n";	
				}	
				else
					echo "#!ih";
				exit;
			}
			echo "#!nf";
		}
	}
	else
	{
		echo "#!im";
	}
}
else
	echo "#!im";




