<?php

// Blackout - 8:36 AM 23/02/2012
class DashboardPage extends FOGPage
{
	// Base variables
	var $name = 'Dashboard';
	var $node = 'home';
	var $id = 'id';
	
	// Pages
	public function index()
	{
		$SystemUptime = $this->FOGCore->SystemUptime();
	
		?>
		<ul id="dashboard-boxes">
			<li>
				<h4><?php print _('System Overview'); ?></h4>
				<table width="100%">
					<tr>
						<td width="90"><?php print _('Username'); ?></td>
						<td><?php print $GLOBALS['currentUser']->get('name'); ?></td>
					</tr>
					<tr>
						<td><?php print _('Web Server'); ?></td>
						<td><?php print $this->FOGCore->getSetting( 'FOG_WEB_HOST' ); ?></td>
					</tr>
					<tr>
						<td><?php print _('TFTP Server'); ?></td>
						<td><?php print $this->FOGCore->getSetting( 'FOG_TFTP_HOST' ); ?></td>
					</tr>
					<tr>
						<td><?php print _('Load Average'); ?></td>
						<td><?php print $SystemUptime['load']; ?></td>
					</tr>
					<tr>
						<td><?php print _('Uptime'); ?></td>
						<td><?php print $SystemUptime['uptime']; ?></td>
					</tr>
				</table>
			</li>
			<li>
				<h4><?php print _('System Activity'); ?></h4>
				<div class="graph pie-graph" id="graph-activity"></div>
			</li>
			<li>
				<h4><?php print _('Disk Information'); ?></h4>
				<div id="diskusage-selector">
					<select>
						<?php
						
						foreach ((array)$this->FOGCore->getClass('StorageNodeManager')->find(array('isEnabled' => 1)) AS $StorageNode)
						{								
							printf('<option value="%s"%s>%s</option>', $StorageNode->get('id'), ($StorageNode->get('isMaster') ? ' selected' : ''), $StorageNode->get('name'));
						}
						
						?>
					</select>
				</div>
				<a href="?node=hwinfo"><div class="graph pie-graph" id="graph-diskusage"></div></a>
			</li>
		</ul>
		
		<h3>Imaging over the last 30days</h3>
		<div id="graph-30day" class="graph"></div>
		<h3 id="graph-bandwidth-title">Bandwidth - <span>Transmit</span><!-- (<span>2 Minutes</span>)--></h3>
		<div id="graph-bandwidth-filters">
			<div>
				<a href="#" id="graph-bandwidth-filters-transmit" class="l active"><?php print _('Transmit'); ?></a>
				<a href="#" id="graph-bandwidth-filters-receive" class="l"><?php print _('Receive'); ?></a>
			</div>
			<div class="spacer"></div>
			<!-- 
			<div>
				<a href="#" rel="3600" class="r"><?php print _('1 Hour'); ?></a>
				<a href="#" rel="1800" class="r"><?php print _('30 Minutes'); ?></a>
				<a href="#" rel="600" class="r"><?php print _('10 Minutes'); ?></a>
				<a href="#" rel="120" class="r active"><?php print _('2 Minutes'); ?></a>
			</div>
			-->
		</div>
		<div id="graph-bandwidth" class="graph"></div>
		<?php
		
		// Build 30 day data for graph
		// TODO: Rewrite this.... wayyyy too many queries
		for( $i = 30; $i >= 0; $i-- )
		{
			$this->DB->query("SELECT COUNT(*) AS c, DATE(NOW() - INTERVAL $i DAY) AS d FROM tasks WHERE DATE(taskCreateTime) = DATE(NOW()) - INTERVAL $i DAY");
			if ($data = $this->DB->fetch()->get())
			{
				// NOTE: Must multiply timestamp by 1000 for unix timestamp in MILLISECONDS
				$Graph30dayData[] = '["' . strtotime($data['d'])*1000 . '", ' . $data['c'] . ']';
			}
		}
		
		$ActivityQueued = getNumberOfTasks($this->DB->getLink(), 1);
		$ActivityActive = (int)getNumberOfTasks($this->DB->getLink(), 2) + (int)getNumberOfTasks($this->DB->getLink(), 3);
		$ActivitySlots = getGlobalQueueSize($this->DB->getLink()) - $ActivityActive;
		?>	
		<!-- Variables -->
		<div class="fog-variable" id="ActivityActive"><?php print $ActivityActive; ?></div>
		<div class="fog-variable" id="ActivityQueued"><?php print $ActivityQueued; ?></div>
		<div class="fog-variable" id="ActivitySlots"><?php print ($ActivitySlots < 0 ? 0 : $ActivitySlots); ?></div>	
		<div class="fog-variable" id="Graph30dayData">[<?php echo implode(', ', (array)$Graph30dayData); ?>]</div>
		<?php
	}
	
	public function bandwidth()
	{
		// Loop each storage node -> grab stats
		foreach ((array)$this->FOGCore->getClass('StorageNodeManager')->find(array('isEnabled' => 1, 'isGraphEnabled' => 1)) AS $StorageNode)
		{
			// TODO: Need to move interface to per storage group server
			$URL = sprintf('http://%s/%s?dev=%s', rtrim($StorageNode->get('ip'), '/'), ltrim($this->FOGCore->getSetting("FOG_NFS_BANDWIDTHPATH"), '/'), $StorageNode->get('interface'));
			
			// Fetch bandwidth stats from remote server
			if ($fetchedData = $this->FOGCore->fetchURL($URL))
			{
				// Legacy client
				if (preg_match('/(.*)##(.*)/U', $fetchedData, $match))
				{
					$data[$StorageNode->get('name')] = array('rx' => $match[1], 'tx' => $match[2]);
				}
				else
				{
					$data[$StorageNode->get('name')] = json_decode($fetchedData, true);
				}
			}
		}

		print json_encode((array)$data);
	}
}

// Register page with FOGPageManager
$FOGPageManager->register(new DashboardPage());