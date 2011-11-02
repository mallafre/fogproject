<?php
/*
 *  FOG is a computer imaging solution.
 *  Copyright (C) 2007  Chuck Syperski & Jian Zhang
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

// Require FOG Base
require_once('../commons/config.php');
require_once(BASEPATH . '/commons/init.php');

if (IS_INCLUDED !== true ) die( _("Unable to load system configuration information."));

// Allow AJAX check
if (!$_SESSION['AllowAJAXTasks'])
{
	die('FOG Session Invalid');
}

// Remove Active Task
if ($_GET['rmtask'])
{
	// Hook
	$HookManager->processEvent('TasksActiveRemove', array('id' => &$_GET['rmtask']));
	
	if ( ! ftpDelete( $GLOBALS['FOGCore']->getSetting( "FOG_TFTP_PXE_CONFIG_DIR" ) . "01-" . str_replace ( ":", "-", strtolower($_GET[mac]) ) ) )
	{
		msgBox( _("Unable to delete PXE file") );
	}
	
	$sql = "DELETE FROM tasks WHERE taskID = '" . mysql_real_escape_string( $_GET['rmtask'] ) . "' LIMIT 1";
	if ( mysql_query( $sql, $conn ) )
	{
		// Hook
		$HookManager->processEvent('TasksActiveRemoveSuccess', array('id' => &$_GET['rmtask']));
	
		msgBox( _("Task removed, but if the task was in progress or the computer already booted to the Linux Image, you will need to reboot it!") );
		lg( _("Task deleted")." :: $_GET[rmtask]" );
	}
	else
	{
		// Hook
		$HookManager->processEvent('TasksActiveRemoveFail', array('id' => &$_GET['rmtask']));
		
		criticalError( mysql_error(), _("FOG :: Database error!") );
	}
}

// Force Task
if ($_GET["forcetask"])
{
	// Hook
	$HookManager->processEvent('TasksActiveForce', array('id' => &$_GET['forcetask']));
	
	$sql = "update tasks set taskForce = '1' where taskID = '" . mysql_real_escape_string( $_GET["forcetask"] ) . "'";
	if ( mysql_query( $sql, $conn ) )
	{
		// Hook
		$HookManager->processEvent('TasksActiveForceSuccess', array('id' => &$_GET['forcetask']));
	
		msgBox( _("Task updated to force!") );
		lg( _("Task set to Force")." :: $_GET[forcetask]" );	
	}
	else
	{
		// Hook
		$HookManager->processEvent('TasksActiveForceFail', array('id' => &$_GET['forcetask']));
		
		criticalError( mysql_error(), _("FOG :: Database error!") );
	}
}

$templates = array(
	_('Hostname') . '<br /><small>' . _('MAC'),
	_('Start Time'),
	_('State'),
	_('Type'),
	_('Force'),
	_('Kill')
);

$attributes = array(
	array(),
	array('width' => 120, 'class' => 'c'),
	array('width' => 40, 'class' => 'c'),
	array('width' => 40, 'class' => 'c'),
	array('width' => 40, 'class' => 'c'),
	array('width' => 40, 'class' => 'c')
);

// Hook
$HookManager->processEvent('TasksActiveTableHeader', array('templates' => &$templates, 'attributes' => &$attributes));
	
// Output
$OutputManager = new OutputManager('task', $data, $templates, $attributes);

?>
<h2><?php echo(_("All Active Tasks")); ?></h2>

<table width="100%" border="0" cellpadding="0" cellspacing="0" id="search-content">
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
		
		require('ajax/tasks.active.php');
		
		?>
	</tbody>
</table>
<?php

// Hook
$HookManager->processEvent('TasksActiveAfterTable');