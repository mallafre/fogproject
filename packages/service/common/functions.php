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

//function endsWith( $str, $sub ) 
//{
//   return ( substr( $str, strlen( $str ) - strlen( $sub ) ) === $sub );
//}

function out( $string, $device, $blLog=false, $blNewLine=true ) 
{
	if ( $blNewLine ) $strOut = $string . "\n";
		
	if (!$hdl = fopen( $device, 'w')) 
	{
		return;
	}

	if (fwrite($hdl, $strOut) === FALSE) 
	{
		return;
	}
	fclose($hdl);		
}

function getDateTime()
{
	return date("m-d-y g:i:s a");
}

function wlog( $string, $path )
{
	if ( filesize( $path ) > LOGMAXSIZE )
		unlink( $path );
		
	if (!$hdl = fopen( $path, 'a')) 
	{
		out( " " );
		out( " * Error: Unable to open file: $path" );
		out( "" );
		return;
	}

	if (fwrite($hdl, "[" . getDateTime() . "] " . $string . "\n") === FALSE) 
	{
		out( " " );
		out( " * Error: Unable to write to file: $path" );
		out( "" );
		return;
	}
	fclose($hdl);	
}

function getBanner()
{
	
	$str = "        ___           ___           ___      \n";
	$str .= "       /\  \         /\  \         /\  \     \n";
	$str .= "      /::\  \       /::\  \       /::\  \    \n";
	$str .= "     /:/\:\  \     /:/\:\  \     /:/\:\  \   \n";
	$str .= "    /::\-\:\  \   /:/  \:\  \   /:/  \:\  \  \n";
	$str .= "   /:/\:\ \:\__\ /:/__/ \:\__\ /:/__/_\:\__\ \n";
	$str .= "   \/__\:\ \/__/ \:\  \ /:/  / \:\  /\ \/__/ \n";
	$str .= "        \:\__\    \:\  /:/  /   \:\ \:\__\   \n";
	$str .= "         \/__/     \:\/:/  /     \:\/:/  /   \n";
	$str .= "                    \::/  /       \::/  /    \n";
	$str .= "                     \/__/         \/__/     \n";
	$str .= "\n";
	$str .= "  ###########################################\n";
	$str .= "  #     Free Computer Imaging Solution      #\n";
	$str .= "  #                                         #\n";
	$str .= "  #     Created by:                         #\n";
	$str .= "  #         Chuck Syperski                  #\n";	
	$str .= "  #         Jian Zhang                      #\n";
	$str .= "  #                                         #\n";		
	$str .= "  #     GNU GPL Version 3                   #\n";		
	$str .= "  ###########################################\n";
	$str .= "\n";
	return $str;
	
}

function isMCTaskNew( $arKnown, $id )
{
	for( $i = 0; $i < count( $arKnown ); $i++ )
	{
		if ( $arKnown[$i] != null )
		{
			if ( $arKnown[$i]->getID() == $id ) return false;
		}
	}
	return true;
}

function getMCExistingTask( $arKnown, $id )
{
	for( $i = 0; $i < count( $arKnown ); $i++ )
	{
		if ( $arKnown[$i] != null )
		{
			if ( $arKnown[$i]->getID() == $id ) return $arKnown[$i];
		}
	}
	return null;
}

function removeFromKnownList( $arKnown, $id )
{
	$arNew = array();
	for( $i = 0; $i < count( $arKnown ); $i++ )
	{
		if ( $arKnown[$i] != null )
		{
			if ( $arKnown[$i]->getID() != $id ) $arNew[] = $arKnown[$i];
		}
	}
	return $arNew;
}

function getMCTasksNotInDB( $arKnown, $arAll )
{
	// arKnown are known tasks to the service
	// arAll are all the tasks known to the database
	
	// returns an array of tasks that should be purged from the known list
	$arRet = array();
	for( $i = 0; $i < count( $arKnown ); $i++ )
	{
		if ( $arKnown[$i] != null )
		{
			if ( $arKnown[$i]->getID() !== null )
			{
				$kID = $arKnown[$i]->getID();
				$blFound = false;
				for( $z = 0; $z < count( $arAll ); $z++ )
				{
					if ( $arAll[$z] != null )
					{
						if ( $arAll[$z]->getID() !== null )
						{
							if ( $kID == $arAll[$z]->getID() )
							{	
								$blFound = true;
								break;
							}
						}
					}
				}
				
				if ( ! $blFound )
					$arRet[] = $arKnown[$i];
			}
		}
	}
	return $arRet;
}

function getIPAddress()
{
	$arR = null;
	$retVal = null;
	$output = array();
	//thanks: schmalenegger
	exec( "/sbin/ifconfig | grep '[0-9]\{1,3\}\.[0-9]\{1,3\}\.[0-9]\{1,3\}\.[0-9]\{1,3\}' | cut -d':' -f 2 | cut -d' ' -f1", $arR, $retVal );
	// original below
	//exec( "/sbin/ifconfig | grep \"inet addr:\" | cut -d':' -f 2 | cut -d' ' -f1", $arR, $retVal );
	for( $i = 0; $i < count( $arR ); $i++ )
	{
		$arR[$i] = trim( $arR[$i] );
		if ( $arR[$i] != "127.0.0.1" )
		{
			if (($bIp = ip2long($arR[$i])) !== false)
			{
				$output[] = $arR[$i];
			} 		
		}
	}
	return $output;
}

function getGroupID( $conn )
{
	if ( $conn != null )
	{
		$arIPs = getIPAddress();
		if ( count( $arIPs ) > 0 )
		{
			for( $i = 0; $i < count( $arIPs ); $i++ )
			{
				$ip = $arIPs[$i];
				$sql = "SELECT * FROM nfsGroupMembers WHERE ngmHostname = '$ip' and ngmIsEnabled = '1' and ngmIsMasterNode = '1'";
				$res = mysql_query( $sql, $conn ) or die( mysql_error() );
				if (mysql_num_rows( $res ) == 1)
				{
					while( $ar = mysql_fetch_array( $res ) )
					{
						return $ar["ngmGroupID"];
					}
				}
			}
		}
	}
	return -1;
}

function getMyNodeID( $conn )
{
	if ( $conn != null )
	{
		$arIPs = getIPAddress();
		if ( count( $arIPs ) > 0 )
		{
			for( $i = 0; $i < count( $arIPs ); $i++ )
			{
				$ip = $arIPs[$i];
				$sql = "SELECT * FROM nfsGroupMembers WHERE ngmHostname = '$ip' and ngmIsEnabled = '1' and ngmIsMasterNode = '1'";
				$res = mysql_query( $sql, $conn ) or die( mysql_error() );
				if (mysql_num_rows( $res ) == 1)
				{
					while( $ar = mysql_fetch_array( $res ) )
					{
						return $ar["ngmID"];
					}
				}
			}
		}
	}
	return -1;
}

function getGroupMembersIDs( $conn, $group, $notNode )
{
	$arRet = array();
	if ( $conn != null && is_numeric( $group ) && is_numeric( $notNode ) )
	{	
		$sql = "SELECT ngmID from nfsGroupMembers WHERE ngmGroupID = '$group' and ngmIsEnabled = '1' and ngmID <> '$notNode'";
		$res = mysql_query( $sql, $conn ) or die( mysql_error() );
		while( $ar = mysql_fetch_array( $res ) )
		{
			$arRet[] = $ar["ngmID"];
		}
	}
	return $arRet;
}

function getAllImageFilesInGroup( $conn, $group )
{
	$arRet = array();
	if ( $conn != null && is_numeric( $group ) )
	{
		$sql = "SELECT * FROM images WHERE imageNFSGroupID = '$group'";
		$res = mysql_query( $sql, $conn ) or die( mysql_error() );
		while( $ar = mysql_fetch_array( $res ) )
		{
			$arRet[] = $ar["imagePath"];
		}
	}
	return $arRet;
	
}

function amITheGroupMananger( $conn )
{
	if ( $conn != null )
	{
		$arIPs = getIPAddress();
		if ( count( $arIPs ) > 0 )
		{
			for( $i = 0; $i < count( $arIPs ); $i++ )
			{
				$ip = $arIPs[$i];
				$sql = "SELECT * FROM nfsGroupMembers WHERE ngmHostname = '$ip' and ngmIsEnabled = '1' and ngmIsMasterNode = '1'";
				$res = mysql_query( $sql, $conn ) or die( mysql_error() );
				if (mysql_num_rows( $res ) == 1) return true;
			}
		}
	}
	return false;
}

function getMyImageStoreRoot( $conn, $nodeid )
{
	if ( $conn != null && is_numeric( $nodeid ) )
	{
		$sql = "SELECT * FROM nfsGroupMembers WHERE ngmID = '$nodeid'";
		$res = mysql_query( $sql, $conn ) or die( mysql_error() );
		while( $ar = mysql_fetch_array( $res ) )
		{
			return $ar["ngmRootPath"];
		}
	}
	return null;
}

?>
