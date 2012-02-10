<?php

require('../commons/config.php');
require(BASEPATH . '/commons/init.php');
require(BASEPATH . '/commons/init.database.php');

$mac = new MACAddress($_REQUEST['mac']);
if ( ! $mac->isValid( ) ) 
	die( _("Invalid MAC address format!") );
	
if ( $mac == null  )
	die( _("Invalid MAC Address"));
	
$host = $mac->getHost();

if ( $host == null ) 
	die( _("Unable to locate host in database, please ensure that mac address is correct.") );
	
	
// Clean old task status
$taskManager = new TaskManager();
$tasks = $taskManager->find(array('stateID' => array(2), 'hostID' => $host->get('id')));
foreach ($tasks as $task)
{
	$task->set('stateID', '1' )->save();
}


$task = new Task( array('hostID' => $host->get('id') ) );
$task->load('hostID');
if ( $task === false )
{
	echo _("No job was found for MAC Address").": $mac";
	exit;
}

$storageGroup = $task->getStorageGroup();
if ( $storageGroup === null )
{
	echo _("No storage group was associated with this task!");
	exit;
}
	
// Check the host in	
if ( $task->set('checkInTime', time())->save() === false )
{
	echo _("Error: Checkin Failed.");
	exit;
}

// Short circuit
if ( $task->get('isForced') )
{
	if ( $task->set('stateID', '2' )->save() )
		echo "##@GO";
	else
		echo _("Error attempting to start imaging process");				
	
	// log the start of the task
	//@logImageTask( $conn, "s", $hostid, mysql_real_escape_string( getImageName( $conn, $hostid ) ) );
	exit;
}			

// check if there are any open spots in the group's queue

$totalSlots = $storageGroup->getTotalSupportedClients();
$taskManager = new TaskManager();
$usedSlots = count($taskManager->getQueuedTasksByStorageGroup($storageGroup->get('id')));

if ( $usedSlots < $totalSlots )
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
				
				


			



?>
