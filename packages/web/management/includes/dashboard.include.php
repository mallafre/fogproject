<?php

if ( IS_INCLUDED !== true ) die( _("Unable to load system configuration information.") );

if ( $currentUser != null && $currentUser->isLoggedIn() )
{
	$SystemUptime = SystemUptime();
	
	?><ul id="dashboard-boxes">
			<li>
				<h4><?php print _('System Overview'); ?></h4>
				<table width="100%">
					<tr>
						<td width="90"><?php print _('Username'); ?></td>
						<td><?php print $currentUser->get('name'); ?></td>
					</tr>
					<tr>
						<td><?php print _('Web Server'); ?></td>
						<td><?php print $GLOBALS['FOGCore']->getSetting( 'FOG_WEB_HOST' ); ?></td>
					</tr>
					<tr>
						<td><?php print _('TFTP Server'); ?></td>
						<td><?php print $GLOBALS['FOGCore']->getSetting( 'FOG_TFTP_HOST' ); ?></td>
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
						$StorageNodes = mysql_query("SELECT * FROM nfsGroupMembers WHERE ngmIsEnabled = '1' ORDER BY ngmIsMasterNode DESC", $conn) or die(mysql_error());
						while ($Node = mysql_fetch_array($StorageNodes))
						{								
							printf('<option value="%s"%s>%s</option>', $Node['ngmID'], ($Node['ngmIsMasterNode'] ? ' selected' : ''), $Node['ngmMemberName']);
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
		$res = mysql_query("SELECT COUNT(*) AS c, DATE(NOW() - INTERVAL $i DAY) AS d FROM tasks WHERE DATE(taskCreateTime) = DATE(NOW()) - INTERVAL $i DAY", $conn) or die(mysql_error());
		if ($ar = mysql_fetch_array($res))
		{
			// NOTE: Must multiply timestamp by 1000 for unix timestamp in MILLISECONDS
			$Graph30dayData[] = '["' . strtotime($ar['d'])*1000 . '", ' . $ar['c'] . ']';
		}
	}
	
	$ActivityActive = getNumberOfTasks($conn, 1);
	$ActivityQueued = getNumberOfTasks($conn, 0);
	$ActivitySlots = getGlobalQueueSize($conn) - $ActivityActive;
	?>	
		<!-- Variables -->
		<div class="fog-variable" id="ActivityActive"><?php print $ActivityActive; ?></div>
		<div class="fog-variable" id="ActivityQueued"><?php print $ActivityQueued; ?></div>
		<div class="fog-variable" id="ActivitySlots"><?php print ($ActivitySlots < 0 ? 0 : $ActivitySlots); ?></div>	
		<div class="fog-variable" id="Graph30dayData">[<?php echo implode(', ', (array)$Graph30dayData); ?>]</div>
	<?php
}