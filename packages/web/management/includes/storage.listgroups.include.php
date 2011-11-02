<?php

if (IS_INCLUDED !== true) die(_('Unable to load system configuration information.'));

print '<h2>' . _('All Current Storage Groups') . '</h2>';

$sql = "select * from nfsGroups $orderby";
$allStorageGroups = mysql_query($sql, $conn) or die(mysql_error());
if (mysql_num_rows($allStorageGroups) > 0)
{
	$templates = array(
		_('Group Name'),
		_('Description'),
		_('Edit')
	);
	
	$attributes = array(
		array(),
		array(),
		array('width' => 40, 'class' => 'c')
	);
	
	// Hook
	$HookManager->processEvent('StorageGroupTableHeader', array('templates' => &$templates, 'attributes' => &$attributes));
	
	// Output
	$OutputManager = new OutputManager('storagegroup', $data, $templates, $attributes);
	
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
				'<a href="?node=' . $_GET["node"] . '&sub=edit&storagegroupid=%id%" title="Edit">%name%</a>',
				'%description%',
				'<a href="?node=' . $_GET["node"] . '&sub=edit&storagegroupid=%id%"><span class="icon icon-edit" title="Edit: %name%"></span></a>'
			);

			$attributes = array(
				array(),
				array(),
				array('class' => 'c')
			);
			
			while ($storageGroup = mysql_fetch_array($allStorageGroups, MYSQL_ASSOC))
			{
				$data[] = array('id' => $storageGroup["ngID"], 'name' => $storageGroup["ngName"], 'description' => $storageGroup["ngDesc"]);
			}
			
			// Hook
			$HookManager->processEvent('StorageGroupData', array('data' => &$data, 'templates' => &$templates, 'attributes' => &$attributes));
			
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
	$HookManager->processEvent('StorageGroupAfterTable');
} 
else
{
	echo ( _("No Storage Groups Found!") );
}