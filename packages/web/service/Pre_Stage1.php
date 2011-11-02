<?php

require('../commons/config.php');
require(BASEPATH . '/commons/init.php');
require(BASEPATH . '/commons/init.database.php');

$conn = mysql_connect( MYSQL_HOST, MYSQL_USERNAME, MYSQL_PASSWORD);
if ( $conn )
{
	if ( ! mysql_select_db( MYSQL_DATABASE, $conn ) ) die( _("Unable to select database")  );
}
else
	die( _("Unable to connect to Database") );

$mac = strtolower($_GET["mac"]);
if ( ! isValidMACAddress( $mac ) ) die( _("Invalid MAC address format!") );

if ( $mac != null  )
{
	$hostid = getHostID( $conn, $mac );
	
	if ( $hostid == null ) die( _("Unable to locate host in database, please ensure that mac address is correct.") );
	
	cleanIncompleteTasks( $conn, $hostid );	
	if ( queuedTaskExists( $conn, $mac ) )
	{
		$jobid = getTaskIDByMac( $conn, $mac );
		if ( $jobid != null )
		{	
			// Find out which NFS Group the JOB requires
			$nfsGroupID = getNFSGroupIDByTaskID( $conn, $jobid );
			if ( $nfsGroupID )
			{
				if ( checkIn( $conn, $jobid ) )
				{
					if ( isForced( $conn, $jobid ) )
					{
						if ( doImage( $conn, $jobid ) )
						{
							echo "##@GO";
							@logImageTask( $conn, "s", $hostid, mysql_real_escape_string( getImageName( $conn, $hostid ) ) );
						}
						else
							echo _("Error attempting to start imaging process");				
						exit;			
					}
			
					// check if there are any open spots in the clustered queue
					$clusterMaxClients = getTotalClusteredQueueSize( $conn, $nfsGroupID );
					$groupNumRunning = getNumberInQueueByNFSGroup($conn, 1, $nfsGroupID);
					
					if ( $groupNumRunning < $clusterMaxClients )
					{
						// there is an open spot somewhere
						// now we need to see if it is
						// intended for us
						
						// get the number of machines that are in line
						// in front of me for the whole cluster
						$groupInFrontOfMe = getNumberInFrontOfMe( $conn, $jobid, $nfsGroupID );
						
						$groupOpenSlots = $clusterMaxClients - $groupNumRunning;
						if ( $groupOpenSlots > $groupInFrontOfMe )
						{
							$clusterNodes = getAllNodeInNFSGroup( $conn, $nfsGroupID );
							$arBlamedNodes = getAllBlamedNodes( $conn, $jobid, $hostid );
							//print_r ( $arBlamedNodes );
							//die ("!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!");
							$strNotes = "";

							if ( count( $clusterNodes ) > 0 )
							{
								$bestNode = -1;
								$clientsOnBestNode = 999999999;
								for( $i = 0; $i < count( $clusterNodes ); $i++ )
								{
									$nodeActiveTasks = getNumberInQueueByNFSServer( $conn, 1, $clusterNodes[$i] );
									$nodeMaxClients = getNodeQueueSize( $conn, $clusterNodes[$i] );

									if ( $nodeActiveTasks < $nodeMaxClients )
									{	

										if ( $nodeActiveTasks < $clientsOnBestNode )
										{
											if ( ! in_array( $clusterNodes[$i], $arBlamedNodes ) )
											{
												// new best
												$bestNode = $clusterNodes[$i];
												$clientsOnBestNode = $nodeActiveTasks;
											}
											else
											{
												$strNotes .= _("Storage Node").": " . getNFSNodeNameById( $conn, $clusterNodes[$i] ) ." " ._("is open, but has recently failed").".\n";
											}
										}										
									}									
								}

								if ( $bestNode != -1 )
								{								
									if ( doImage( $conn, $jobid, true, $bestNode ) ) 
									{
										echo "##@" . getNewStorageStringForImage( $conn, $bestNode ); 
										@logImageTask( $conn, "s", $hostid, mysql_real_escape_string( getImageName( $conn, $hostid ) ) );
									}
									else
										echo _("Error attempting to start imaging process");								
								}
								else
									echo _("Unable to determine best node for transfer!")."\n\n" . $strNotes ;
							}
							else
								echo _("No Storage servers are in this cluster!");
						}	
						else
							echo _("There are open slots, but I am waiting for")." " . $groupInFrontOfMe . " "._("CPUs in front of me.");							
					}
					else
						echo _("Waiting for a slot").", " . getNumberInFrontOfMe( $conn, $jobid, $nfsGroupID ) . " "._("PCs are in front of me.");
				}
				else
					echo _("Error: Checkin Failed.");

			}
		}
		else
			echo _("Unable to locate a valid job ID number.");
	}
	else
		echo _("No job was found for MAC Address").": $mac";
}
else
	echo _("Invalid MAC Address");
?>
