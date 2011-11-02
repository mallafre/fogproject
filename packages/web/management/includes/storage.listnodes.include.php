<?php

if (IS_INCLUDED !== true) die(_('Unable to load system configuration information.'));

print '<h2>' . _('All Current Storage Nodes') . '</h2>';

$sql = "SELECT 
		* 
	FROM 
		nfsGroupMembers 
	LEFT OUTER JOIN
		nfsGroups on ( nfsGroupMembers.ngmGroupID = nfsGroups.ngID ) 
	$orderby";
	
$allStorageNodes = mysql_query($sql, $conn) or die(mysql_error());
if (mysql_num_rows($allStorageNodes) > 0)
{
	$templates = array(
		_('Node Name'),
		_('Description'),
		_('Group'),
		_('Is Master'),
		_('Max Clients'),
		_('Is Enabled'),
		_('Edit')
	);
	
	$attributes = array(
		array(),
		array(),
		array(),
		array(),
		array(),
		array(),
		array('width' => 40, 'class' => 'c')
	);
	
	// Hook
	$HookManager->processEvent('StorageNodeTableHeader', array('templates' => &$templates, 'attributes' => &$attributes));
	
	// Output
	$OutputManager = new OutputManager('storagenode', $data, $templates, $attributes);
	
	?>
	<table width="100%" cellpadding="0" cellspacing="0" border="0">
		<thead>
			<tr class="header">
				<?php
				
				// Hook
				print $OutputManager->processHeaderRow($templates, $attributes);
				
				?>
			</tr>
		</thead>
		<tbody>
			<?php
			
			$templates = array(
				'<a href="?node=' . $_GET["node"] . '&sub=editnode&storagenodeid=%id%" title="Edit">%name%</a>',
				'%description%',
				'%group%',
				'%master%',
				'%maxclients%',
				'%enabled%',
				'<a href="?node=' . $_GET['node'] . '&sub=editnode&storagenodeid=%id%"><span class="icon icon-edit" title="Edit: %name%"></span></a>'
			);

			$attributes = array(
				array(),
				array(),
				array(),
				array(),
				array(),
				array(),
				array('class' => 'c')
			);
			
			while ($storageNode = mysql_fetch_array($allStorageNodes, MYSQL_ASSOC))
			{
				$data[] = array('id' => $storageNode["ngmID"], 'name' => $storageNode["ngmMemberName"], 'description' => $storageNode["ngmMemberDesc"], 'group' => $storageNode["ngName"], 'master' => $storageNode["ngmIsMasterNode"], 'maxclients' => $storageNode["ngmMaxClients"], 'enabled' => $storageNode["ngmIsEnabled"]);
			}
			
			// Hook
			$HookManager->processEvent('StorageNodeData', array('data' => &$data, 'templates' => &$templates, 'attributes' => &$attributes));
			
			// Output row
			foreach ($data AS $rowData)
			{
				printf('<tr class="%s">%s</tr>%s', (++$i % 2 ? 'alt1' : 'alt2'), $OutputManager->processRow($rowData, $templates, $attributes), "\n");
			}
			
			?>
		</tbody>
	</table>
	<?php
	
	// Hook
	$HookManager->processEvent('StorageNodeAfterTable');
} 
else
{
	echo ( _("No Storage Groups Nodes Found!") );
}