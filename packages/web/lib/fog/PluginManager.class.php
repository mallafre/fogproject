<?php
/*
 *  FOG is a computer imaging solution.
 *  Copyright (C) 2008  Chuck Syperski & Jian Zhang
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
 
class PluginManager extends FOGManagerController
{
	private $dbConn;
	private $strLocation;

	public function __construct()
	{
		parent::__construct();
	
		$this->dbConn = $GLOBALS['conn'];
		$this->strLocation = $GLOBALS['FOGCore']->getSetting("FOG_PLUGINSYS_DIR");
	}
	
	public function getRunInclude( $hash )
	{
		if ( $this->dbConn != null && $hash != null )
		{
			$plugs = $this->getAllPlugins();
			for( $i = 0; $i < count( $plugs );$i++ )
			{
				if ( $plugs[$i] != null )
				{
					if ( md5(trim($plugs[$i]->getName())) == trim($hash) )
					{
						$_SESSION["fogactiveplugin"] = serialize( $plugs[$i] );
						return $plugs[$i]->getPath() . "/" . $plugs[$i]->getEntryPoint();	
					}		
				}
			}
		}
		return null;
	}
	
	public function activatePlugin( $plugincode )
	{
		if ( $this->dbConn != null && $plugincode != null )
		{
			$plugs = $this->getAllPlugins();
			for( $i = 0; $i < count( $plugs ); $i++ )
			{
				if ( $plugs[$i] != null )
				{
					if ( md5(trim($plugs[$i]->getName())) == trim($plugincode) )
					{
						$sql = "SELECT 
								*
							FROM 
								plugins
							WHERE 
								pName = '" . mysql_real_escape_string(trim($plugs[$i]->getName())) . "'";
						$res = mysql_query( $sql, $this->dbConn ) or die ( mysql_error() );
						if ( mysql_num_rows( $res ) > 0 )
						{
							$blActive = false;
							while( $ar = mysql_fetch_array( $res ) )
							{
								if ( $ar["pState"] == "1" )
								{
									$blActive = true;
								}
							}
							
							if ( ! $blActive )
							{
								$sql = "UPDATE plugins set pState = '1', pInstalled = '0' WHERE pName = '" . mysql_real_escape_string(trim($plugs[$i]->getName())) . "'";
								return mysql_query( $sql, $this->dbConn );
							}
						}
						else
						{
							$sql = "INSERT INTO plugins(pName, pState, pInstalled) values('" . mysql_real_escape_string(trim($plugs[$i]->getName())) . "', '1', '0')";
							return mysql_query( $sql, $this->dbConn );
						}
					}
				}
			}
		}
		return false;
	}
	
	public function getAllPlugins()
	{
		$arPlugins = array();
		if ( $this->strLocation != null )
		{
			
			if ( file_exists( $this->strLocation ) && is_dir( $this->strLocation . "/" ) )
			{
				$cfgfile = "plugin.config.php";
				

				if ($hndl = opendir($this->strLocation) ) 
				{
					while (($file = readdir($hndl)) !== false) 
					{
						if ( is_dir( $this->strLocation . "/" . $file ) && $file != "." && $file != ".." )
						{
							$cfg = $this->strLocation . "/" . $file . "/" . $cfgfile;
							if ( file_exists( $cfg ) )
							{
								$fog_plugin = array();
								include( $cfg );
								$p = new Plugin($this->strLocation . "/" . $file, $fog_plugin["name"], $fog_plugin["description"], $fog_plugin["entrypoint"], $fog_plugin["menuicon"], $fog_plugin["menuicon_hover"] );					
								$sql = "SELECT 
										* 
									FROM 
										plugins
									WHERE
										pName = '" . mysql_real_escape_string(trim($fog_plugin["name"])) . "'";
								$res = mysql_query( $sql, $this->dbConn ) or die( mysql_error() );
								$blActive = "0";
								$blInstalled = "0";
								$intVersion = "";
								
								while( $ar = mysql_fetch_array( $res ) )
								{
									$blActive = $ar["pState"];
									$blInstalled = $ar["pInstalled"];
									$intVersion = $ar["pVersion"];
								}
								
								$p->setIsInstalled( $blInstalled == "1" );
								$p->setIsActive( $blActive == "1" );
								$p->setVersion( $intVersion );							
								$arPlugins[] = $p;
								
								$fog_plugin = array();
							}
			            		}
			        	}
			        	closedir($hndl);
			        	
			        	
			    	}
			}
		}
		return $arPlugins;
	}
}