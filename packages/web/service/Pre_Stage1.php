<?php

//
// Blackout - 5:26 PM 5/05/2012
//
// Pre_Stage1.php
// Triggered:	On checkin - all tasks
// Actions:	Checks queue
//		Determines if Host is allowed to start imaging
//		Echos '##' when host is allowed to image
//


// Require FOG Base files
require('../commons/config.php');
require(BASEPATH . '/commons/init.php');
require(BASEPATH . '/commons/init.database.php');

try
{
	// Error checking
	// NOTE: Most of these validity checks should never fail as checks are made during Task creation - better safe than sorry!
	
	// MAC Address
	$MACAddress = new MACAddress($_REQUEST['mac']);
	if (!$MACAddress->isValid())
	{
		throw new Exception( _('Invalid MAC address') );
	}
	
	// Host for MAC Address
	$Host = $MACAddress->getHost();
	if (!$Host->isValid())
	{
		throw new Exception( _('Invalid Host') );
	}
	
	// Task for Host
	$Task = $Host->getActiveTask();
	if (!$Task->isValid())
	{
		throw new Exception( sprintf('%s: %s (%s)', _('No Active Task found for Host'), $Host->get('name'), $MACAddress) );
	}
	
	// Check-in Host
	if ($Task->get('stateID') == 1)
	{
		$Task->set('stateID', '2')->set('checkInTime', time())->save();
	}
	
	// Storage Group
	$StorageGroup = $Task->getStorageGroup();
	if (!$StorageGroup->isValid())
	{
		throw new Exception( _('Invalid StorageGroup') );
	}
	
	// Storage Node
	$StorageNodes = $StorageGroup->getStorageNodes();
	if (!$StorageNodes)
	{
		throw new Exception( _('Could not find a Storage Node. Is there one enabled within this Storage Group?') );
	}
	
	// Forced to start
	if ($Task->get('isForced'))
	{
		if (!$Task->set('stateID', '3' )->save())
		{
			throw new Exception(_('Forced Task: Failed to update Task'));
		}
		
		// Forced - Success!
		die('##@GO');
	}
	
	// Queue checks
	$totalSlots = $StorageGroup->getTotalSupportedClients();
	$usedSlots = $StorageGroup->getUsedSlotCount();
	$inFrontOfMe = $Task->getInFrontOfHostCount();
	$groupOpenSlots = $totalSlots - $usedSlots;

	// Fail if all Slots are used
	if ($usedSlots >= $totalSlots)
	{
		throw new Exception(sprintf('%s, %s %s', _('Waiting for a slot'), $inFrontOfMe, _('PCs are in front of me.')));
	}
	
	// At this point we know there are open slots, but are we next in line for that slot (or has the next is line timed out?)
	if ($groupOpenSlots <= $inFrontOfMe)
	{
		throw new Exception(sprintf('%s %s %s', _('There are open slots, but I am waiting for'), $inFrontOfMe, _('PCs in front of me.')));
	}

	// Determine the best Storage Node to use - based off amount of clients connected
	$messageArray = array();
	$clientsOnBestStorageNode = 9999;
	foreach ($StorageNodes as $StorageNode)
	{
		$nodeUsedSlots = $StorageNode->getUsedSlotCount();
		if ($nodeUsedSlots < $StorageNode->get('maxClients') && $nodeUsedSlots < $clientsOnBestStorageNode)
		{	
			if ($StorageNode->getNodeFailure($Host) === null)
			{
				$bestStorageNode = $StorageNode;
				$clientsOnBestStorageNode = $nodeUsedSlots;
			}
			else
			{
				$messageArray[] = sprintf("%s '%s' (%s) %s", _('Storage Node'), $StorageNode->get('name'), $StorageNode->get('ip'), _('is open, but has recently failed for this Host'));
			}
		}
	}

	// Failed to find a Storage Node - this should only occur if all Storage Nodes in this Storage Group have failed
	if (!isset($bestStorageNode) || !$bestStorageNode->isValid())
	{
		// Print failed node messages if we are unable to find a valid node
		if (count($messageArray))
		{
			print implode(PHP_EOL, $messageArray) . PHP_EOL;
		}
		
		throw new Exception(_("Unable to find a suitable Storage Node for transfer!"));
	}
	
	// All tests passed! Almost there!
	// Update Task State ID -> Update Storage Node ID -> Save
	if (!$Task->set('stateID', '3' )->set('NFSMemberID', $bestStorageNode->get('id'))->save())
	{
			throw new Exception(_('Failed to update Task'));
	}
	
	// Success!
	print '##@GO';
}
catch (Exception $e)
{
	// Failure
	print $e->getMessage();
}