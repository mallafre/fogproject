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
if ( $host == null || $host->get('id') <= 0 ) 
	die( _("Unable to locate host in database, please ensure that mac address is correct.") );

	
// Clean old task status
$taskManager = new TaskManager();
$tasks = $taskManager->find(array('stateID' => 1, 'hostID' => $host->get('id')));
foreach ($tasks as $task)
{
	$task->set('stateID', '2')->set('checkInTime', time())->save();
}


$task = current($taskManager->find(array('stateID' => array(2, 3), 'hostID' => $host->get('id'))));
if ( $task === false )
{
	echo _("No job was found for MAC Address").": $mac";
	exit;
}

$storageGroup = $task->getStorageGroup();
if ( $storageGroup === null || ! $storageGroup->isValid() )
{
	echo _("No storage group is invalid!");
	exit;
}

// Short circuit
if ( $task->get('isForced') )
{
	if ( $task->set('stateID', '3' )->save() )
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
$usedSlots = $storageGroup->getUsedSlotCount();
//$inFrontOfMe = $taskManager->getCountInFrontOfHost($storageGroup, $task);
$inFrontOfMe = $task->getInFrontOfHostCount();

if ( $usedSlots >= $totalSlots )
{
	echo _("Waiting for a slot").", " . $inFrontOfMe . " "._("PCs are in front of me.");						
	exit;
}


// At this point we know there are open slots, but are we next in line for that slot (or has the next is line timed out?)
//echo $inFrontOfMe;
$groupOpenSlots = $totalSlots - $usedSlots;
if ( $groupOpenSlots <= $inFrontOfMe )
{
	echo _("There are open slots, but I am waiting for")." " . $inFrontOfMe . " "._("CPUs in front of me.");							
	exit;
}

$nodes = $storageGroup->getStorageNodes();

if ( count( $nodes ) <= 0 )
{
	echo _("No Storage servers are in this cluster!");
	exit;
}
							

/*
 * This section determines the best storage node within a group to use
 */							
$bestNode = null;																
$clientsOnBestNode = 999999999;
$msgs = "";
foreach ($nodes as $node)
{
	$nodeUsedSlots = $node->getUsedSlotCount();
	if ( $nodeUsedSlots < $node->get('maxclients') && $nodeUsedSlots < $clientsOnBestNode  )
	{	
		if ( $node->getNodeFailure($host) === null )
		{
			$bestNode = $node;
			$clientsOnBestNode = $nodeUsedSlots;
		}
		else
			$msgs .= _("Storage Node").": " . $node->get('name') ." " ._("is open, but has recently failed").".\n";
	}
}

if ( $bestNode != null )
{
	if ( $task->set('stateID', '3' )->set('NFSMemberID', $bestNode->get('id'))->save() )
	{
		echo "##@GO";
	//	@logImageTask( $conn, "s", $hostid, mysql_real_escape_string( getImageName( $conn, $hostid ) ) );
	}
	else
		echo _("Error attempting to start imaging process");										
}
else
{
	echo _("Unable to determine best node for transfer!")."\n\n" . $strNotes ;
}	
?>
