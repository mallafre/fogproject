<?php

// Blackout - 11:25 AM 26/09/2011
class ClientServiceManager
{
	protected $db;
	
	function __construct()
	{
		$this->db = $GLOBALS['db'];
	}

	public function getGreenFOGActions()
	{
		if ( $this->db != null )
		{
			$sql = "SELECT 
					gfHour, 
					gfMin, 
					gfAction 
				FROM 
					greenFog";


			$arEntries = array();
			if ( $this->db->query($sql) )
			{
				while( $ar = $this->db->fetch()->get() )
				{
					$arEntries[] = new GreenFOG($ar["gfHour"], $ar["gfMin"], $ar["gfAction"]);
				}
			}	
			return $arEntries;				
		}
		return null;
	}

	public function completeSnapinTask( $snapintask, $exitCode, $exitDesc )
	{
		if ( $this->db != null && $snapintask != null && $snapintask->getId() >= 0 )
		{
			$exitcode = $this->db->sanitize( $exitCode );
			$exitdesc = base64_decode($this->db->sanitize( $exitDesc ) );
			$sql = "UPDATE 
					snapinTasks 
				SET 
					stState = '2', 
					stReturnCode = '" . $exitcode . "', 
					stReturnDetails = '" . $exitdesc . "' 
				WHERE 
					stID = '" . $snapintask->getId() . "'";

			return $this->db->query($sql)->affected_rows() == 1;
		}
		return false;
	}

	public function checkInSnapinTask( $snapin )
	{
		if ( $this->db != null && $snapin != null && $snapin->getId() >= 0 )
		{
			$sql = "UPDATE 
					snapinTasks 
				SET 
					stState = '1', 
					stCheckinDate = NOW() 
				WHERE 
					stID = '" . $snapin->getId() . "'";
			return $this->db->query($sql)->affected_rows() == 1;
		}
		return false;
	}

	public function getSnapinTaskById( $id )
	{
		if ( $this->db != null && $id >= 0 )
		{
			$sql = "SELECT 
			 		snapins.*,
			 		snapinJobs.*,
			 		snapinTasks.*,
			 		UNIX_TIMESTAMP(snapins.sCreateDate) as snapincreatedate,
			 		UNIX_TIMESTAMP(snapinTasks.stCheckinDate) as cin,
			 		UNIX_TIMESTAMP(snapinTasks.stCompleteDate) as comp,
			 		UNIX_TIMESTAMP(snapinJobs.sjCreateTime) as sj
			 	FROM 
					snapinTasks
					inner join snapinJobs on ( snapinTasks.stJobID = snapinJobs.sjID )
					inner join snapins on ( snapins.sID = snapinTasks.stSnapinID )
				WHERE
					stID = '" . $this->db->sanitize($id) . "'";
			$entries = null;
			if ( $this->db->query($sql) )
			{
				while( $ar = $this->db->fetch()->get() )
				{	
					$snapin = new Snapin($ar);
					$entries = new SnapinTask( $ar["stID"], $ar["stJobID"], $ar["sjHostID"], $ar["stState"], new Date($ar["cin"]), new Date($ar["comp"]), new Date($ar["sj"]), $snapin, $ar["stReturnCode"], $ar["stReturnDetails"]  );
				}
			}
			return $entries;		
		}
		return null;
	}

	public function addLoginEntry( $loginEntry )
	{
		if ( $this->db != null && $loginEntry != null && $loginEntry->isComplete() )
		{
			$sql = "INSERT INTO 
					userTracking(utHostID, utUserName, utAction, utDateTime, utDesc, utDate)
					values( '" . $this->db->sanitize($loginEntry->getHostId()) . "', '" . $this->db->sanitize($loginEntry->getUsername()) . "', '" . $this->db->sanitize( $loginEntry->getAction() )  . "', FROM_UNIXTIME(" . $loginEntry->getDate()->getLong() . "),  '" . $loginEntry->getDescription() . "', DATE('" . $loginEntry->getDate()->toString( "Y-m-d" ) . "') )";	

			return $this->db->query($sql)->affected_rows() == 1;	
		}
		return false;
	}

	public function getAllPrintersForHost( $host )
	{
		if ( $this->db != null && $host != null && $host->getID() >= 0 )
		{
			$sql = "SELECT 
					* 
			 	FROM 
					printerAssoc
					inner join printers on ( printerAssoc.paPrinterID = printers.pID )
				WHERE
					paHostID = '" . $this->db->sanitize( $host->getID() ) . "'";

			$arPrinters = array();
			if ( $this->db->query($sql) )
			{
				while( $ar = $this->db->fetch()->get() )
				{
					$printer = new Printer($ar);
					$printer->set('default', ($ar["paIsDefault"] == "1" ? true : false));
					$arPrinters[] = $printer;
				}
			}
			return $arPrinters;
		}
		return null;
	}

	public function getAutoLogOutTimeForHost( $host )
	{
		if ( $this->db != null && $host != null && $host->getID() >= 0 )
		{
			$sql = "SELECT
					haloTime 
				FROM
					hostAutoLogOut
				WHERE
					haloHostID = '" . $this->db->sanitize( $host->getID() ) . "'";
			if ( $this->db->query($sql) )
			{
				while( $ar = $this->db->fetch()->get() )
				{					
					return $ar["haloTime"];
				}
			}
		}
		return -1;
	}

	public function getAllActiveSnapinsForHost( $host )
	{
		if ( $this->db != null && $host != null && $host->getID() >= 0 )
		{
			$sql = "SELECT 
			 		stID
			 	FROM 
					snapinTasks
					inner join snapinJobs on ( snapinTasks.stJobID = snapinJobs.sjID )
					inner join hosts on ( snapinJobs.sjHostID = hosts.hostID )
					inner join snapins on ( snapins.sID = snapinTasks.stSnapinID )
				WHERE
					stState in ( '0', '1' ) and
					sjHostID = '" . $host->getID() . "'";
			$arEntries = array();
			if ( $this->db->query($sql) )
			{
				while( $ar = $this->db->fetch()->get() )
				{	
					$arEntries[] = $ar["stID"];
				}
			}

			$arRet = array();
			if ( $arEntries != null )
			{
				for( $i = 0; $i < count( $arEntries ); $i++ )
				{
					if ( $arEntries[$i] != null )
					{
						$tmp = $this->getSnapinTaskById( $arEntries[$i] );					
						if ( $tmp != null )
							$arRet[] = $tmp;
					}
				}
			}
			
			return $arRet;
			
		}
		return null;
	}

	public function getScreenResolutionSettingsForHost( $host )
	{
		if ( $this->db != null && $host != null && $host->getID() >= 0 )
		{
			$sql = "SELECT
					* 
				FROM
					hostScreenSettings
				WHERE
					hssHostID = '" . $host->getID() . "'";
			if ( $this->db->query($sql) )
			{
				while( $ar = $this->db->fetch()->get() )
				{
					return new ScreenResolution($ar["hssWidth"], $ar["hssHeight"], $ar["hssRefresh"]);
				}
			}
		}
		return null;
	}

}
?>
