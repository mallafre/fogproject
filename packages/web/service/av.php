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
$string = $_GET["string"];
$mode = trim($_GET["mode"]);

if ( $mode != "q" && $mode != "s" )
	die( _("Invalid operational mode") );

if ( ! isValidMACAddress( $mac ) )
{
	die( _("Invalid MAC address format!") );
}

if ( $mac != null && $string != null )
{
	$str = base64_decode( $string );
	$arStr = explode( ":", $str );
	
	if ( count( $arStr ) == 2 )
	{
		$file = trim($arStr[0]);
		if ( $file !== null && strlen( $file ) > 0 )
		{
			$vInfo = trim($arStr[1]);
			$arVInfo = explode( " ", $vInfo );
			if ( count( $arVInfo ) == 2 )
			{
				$vName = trim($arVInfo[0]);
				if ( $vName !== null )
				{
					$sql = "insert into virus(vName, vHostMAC, vOrigFile, vDateTime, vMode) values('" . mysql_real_escape_string( $vName ) . "', '" . mysql_real_escape_string( $mac ) . "', '" . mysql_real_escape_string( $file ) . "', NOW(), '" . mysql_real_escape_string( $mode ) . "' )";
					if ( mysql_query( $sql, $conn ) )
						echo( _("Accepted") );
					else
						die( _("Failed").": " . mysql_error() );
				}
			}
			else
			{
				count( $arVInfo );
				print_r( $arVInfo );
				die( _("Failed: Unable to determine virus information") );
			}
		}
		else
			die( _("Failed: No file path") );
	}
	else
		die( _("Failed: Invalid piece count").": " . count( $arStr ) );
}
else
	echo(_("Invalid MAC Address"));
?>
