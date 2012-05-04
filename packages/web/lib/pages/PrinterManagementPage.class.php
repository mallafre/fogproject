<?php

// Blackout - 12:38 PM 25/09/2011
class PrinterManagementPage extends FOGPage
{
	// Base variables
	var $name = 'Printer Management';
	var $node = 'printer';
	var $id = 'id';
	
	// Menu Items
	var $menu = array(
		
	);
	var $subMenu = array(
		
	);
	
	// __construct
	public function __construct($name = '')
	{
		// Call parent constructor
		parent::__construct($name);
		
		// Header row
		$this->headerData = array(
			_('Printer Name'),
			_('Model'),
			_('Port'),
			_('File'),
			_('IP'),
			_('Edit')
		);
		
		// Row templates
		$this->templates = array(
			sprintf('<a href="?node=%s&sub=edit&%s=${id}" title="Edit">${name}</a>', $this->node, $this->id),
			'${model}',
			'${port}',
			'${file}',
			'${ip}',
			sprintf('<a href="?node=%s&sub=edit&%s=${id}"><span class="icon icon-edit"></span></a>', $this->node, $this->id)
		);
		
		// Row attributes
		$this->attributes = array(
			array(),
			array(),
			array(),
			array(),
			array(),
			array('class' => 'c', 'width' => '55'),
		);
	}
	
	// Pages
	public function index()
	{
		// Set title
		$this->title = _('All Users');
		
		// Find data
		$Printers = $this->FOGCore->getClass('PrinterManager')->find();
		
		// Row data
		foreach ($Printers AS $Printer)
		{
			$this->data[] = array(
				'id'		=> $Printer->get('id'),
				'name'		=> $Printer->get('name'),
				'model'		=> $Printer->get('model'),
				'port'		=> $Printer->get('port'),
				'file'		=> $Printer->get('file'),
				'ip'		=> $Printer->get('ip')
			);
		}
		
		// Hook
		$this->HookManager->processEvent('PRINTER_DATA', array('headerData' => &$this->headerData, 'data' => &$this->data, 'templates' => &$this->templates, 'attributes' => &$this->attributes));
		
		// Output
		$this->render();
	}
	
	public function search()
	{
		// Set title
		$this->title = _('Search');
		
		// Set search form
		$this->searchFormURL = sprintf('%s?node=%s&sub=search', $_SERVER['PHP_SELF'], $this->node);
		
		// Hook
		$this->HookManager->processEvent('PRINTER_SEARCH');

		// Output
		$this->render();
	}
	
	public function search_post()
	{
		// Variables
		$keyword = preg_replace('#%+#', '%', '%' . preg_replace('#[[:space:]]#', '%', $this->REQUEST['crit']) . '%');
		$where = array(
			'id'		=> $keyword,
			'name'		=> $keyword,
			'model'		=> $keyword,
			'port'		=> $keyword,
			'file'		=> $keyword,
			'ip'		=> $keyword
		);
	
		// Find data -> Push data
		foreach ($this->FOGCore->getClass('PrinterManager')->find($where, 'OR') AS $Printer)
		{
			$this->data[] = array(
				'id'		=> $Printer->get('id'),
				'name'		=> $Printer->get('name'),
				'model'		=> $Printer->get('model'),
				'port'		=> $Printer->get('port'),
				'file'		=> $Printer->get('file'),
				'ip'		=> $Printer->get('ip')
			);
		}
		
		// Hook
		$this->HookManager->processEvent('PRINTER_DATA', array('headerData' => &$this->headerData, 'data' => &$this->data, 'templates' => &$this->templates, 'attributes' => &$this->attributes));

		// Output
		$this->render();
	}
	
	public function add()
	{
		// Set title
		$this->title = _('New Printer');
		
		// Hook
		$this->HookManager->processEvent('PRINTER_ADD');
		
		// TODO: Put table rows into variables -> Add hooking
		?>
		<form id="printerform" action="?node=$_GET[node]&sub=$_GET[sub]" method="POST" > 
		<select name="printertype">
		<?php
		foreach (array("Local" => _("Local Printer"), "iPrint" => _("iPrint Printer"), "Network" => _("Network Printer")) as $key => $value)
		{
			printf('<option value="%s"%s>%s</option>', $key, ($key == $_POST['printertype'] ? 'selected="selected"' : ''), $value);
		}
		?>
		</select>
		<input type="submit" value="<?php print _("Change type"); ?>">
		</form><br />

		<form method="POST" action="?node=$_GET[node]&sub=$_GET[sub]">
		<center><table cellpadding=0 cellspacing=0 border=0 width=100%>
		<tr><td><?php print _("Printer Alias"); ?></td><td><input type="text" name="alias" value="" /></td></tr>
		<?php
		if ( $_POST['printertype'] == "Network" )
		{
			printf('<tr><td>%s</td></tr>', _("e.g. \\\\printserver\\printername"));
		}
		
		if ( $_POST['printertype'] != "Network" )
		{
			printf('<tr><td>%s</td><td><input type="text" name="port" value="" /></td></tr>', _("Printer Port"));
			if ( $_POST['printertype'] != "iPrint" )
			{
				?>
				<tr><td><?php print _("Printer Model"); ?></td><td><input type="text" name="model" value="" /></td></tr>
				<tr><td><?php print _("Print INF File"); ?></td><td><input type="text" name="inf" value="" /></td></tr>	
				<tr><td><?php print _("Print IP (optional)"); ?></td><td><input type="text" name="ip" value="" /></td></tr>
				<?php
			}
		}
		?>
		<tr><td colspan=2><center>
		<input type="hidden" name="printertype" value="<?php print $_POST['printertype']; ?>" />
		<input type="hidden" name="add" value="1" /><input type="submit" value="<?php print _("Add Printer"); ?>" /></center></td></tr>
		</table></center>
		</form>
		<?php
	}
	
	public function add_post()
	{
		// Hook
		$this->HookManager->processEvent('PRINTER_ADD_POST');
		
		// POST
		try
		{
			/*
			$model = mysql_real_escape_string( $_POST["model"] );
			$alias = mysql_real_escape_string( $_POST["alias"] ); 
			$port = mysql_real_escape_string( $_POST["port"] );
			$inf = mysql_real_escape_string( $_POST["inf"] );
			$ip = mysql_real_escape_string( $_POST["ip"] );
			function add_printer( $sql_statement, $sql_conn)
			{
				if ( mysql_query( $sql_statement, $sql_conn ) )
				{
					msgBox( _("Printer Added, you may now add another.") );
				}
				else
				{
					msgBox( _("Failed to create printer!") );
				}
			}

			if ( $alias != null )
			{
				if ( $_POST['printertype'] != "Network" )
				{
					if ( $port != null )
					{
						if ( $_POST['printertype'] != "iPrint" )
						{
							if ( $model != null && $inf != null )
							{
								$sql = "INSERT INTO
								printers( pPort, pDefFile, pModel, pAlias, pIP )
								values( '$port', '$inf', '$model', '$alias', '$ip' )";
								add_printer($sql, $conn);
							}
							else
							{
								msgBox( _("A required field is null, unable to create printer!") );
							}
						}
						else
						{
							$sql = "INSERT INTO
							printers( pPort, pAlias)
							values ( '$port', '$alias' )";
							add_printer( $sql, $conn);
						}
					}
					else
					{
						msgBox( _("You must specify a model, unable to create printer!") );
					}
				}
				else
				{
					$sql = "INSERT INTO
					printers( pAlias )
					values( '$alias' )";
					add_printer( $sql, $conn);
				}
			}			
			else
			{
				msgBox( _("You must specify a port, unable to create printer!") );
			}
			*/
			// PrinterManager
			$PrinterManager = $this->FOGCore->getClass('PrinterManager');
			
			// Error checking
			if ($PrinterManager->exists($_POST['name']))
			{
				throw new Exception(_('Username already exists'));
			}
			
			// Create new Object
			$Printer = new Printer(array(
				'name'		=> $_POST['name'],
				'type'		=> ($_POST['isGuest'] == 'on' ? '1' : '0'),
				'password'	=> $_POST['password'],
				'createdBy'	=> $_SESSION['FOG_USERNAME']
			));
			
			// Save
			if ($Printer->save())
			{
				// Hook
				$this->HookManager->processEvent('PRINTER_ADD_SUCCESS', array('Printer' => &$Printer));
				
				// Log History event
				$this->FOGCore->logHistory(sprintf('%s: ID: %s, Name: %s', _('User created'), $Printer->get('id'), $Printer->get('name')));
				
				// Set session message
				$this->FOGCore->setMessage(_('User created'));
				
				// Redirect to new entry
				$this->FOGCore->redirect(sprintf('?node=%s&sub=edit&%s=%s', $this->request['node'], $this->id, $Printer->get('id')));
			}
			else
			{
				// Database save failed
				throw new Exception('Database update failed');
			}
		}
		catch (Exception $e)
		{
			// Hook
			$this->HookManager->processEvent('PRINTER_ADD_FAIL', array('Printer' => &$Printer));
			
			// Log History event
			$this->FOGCore->logHistory(sprintf('%s add failed: Name: %s, Error: %s', _('User'), $_POST['name'], $e->getMessage()));
			
			// Set session message
			$this->FOGCore->setMessage($e->getMessage());
			
			// Redirect to new entry
			$this->FOGCore->redirect($this->formAction);
		}
	}
	
	public function edit()
	{
		// Find
		$Printer = new Printer($this->request['id']);
		
		// Title
		$this->title = sprintf('%s: %s', _('Edit'), $Printer->get('name'));
		
		// Hook
		$this->HookManager->processEvent('PRINTER_ADD', array('Printer' => &$Printer));
		
		// TODO: Put table rows into variables -> Add hooking
		?>
		<form method="POST" action="<?php print $this->formAction; ?>">
		<center><table cellpadding=0 cellspacing=0 border=0 width=100%>
			<tr><td><?php print _("Printer Model"); ?></td><td><input type="text" name="model" value="<?php print $Printer->get('model'); ?>" /></td></tr>
			<tr><td><?php print _("Printer Alias"); ?></td><td><input type="text" name="alias" value="<?php print $Printer->get('name'); ?>" /></td></tr>
			<tr><td><?php print _("Printer Port"); ?></td><td><input type="text" name="port" value="<?php print $Printer->get('port'); ?>" /></td></tr>
			<tr><td><?php print _("Print INF File"); ?></td><td><input type="text" name="inf" value="<?php print $Printer->get('file'); ?>" /></td></tr>	
			<tr><td><?php print _("Print IP (optional)"); ?></td><td><input type="text" name="ip" value="<?php print $Printer->get('ip'); ?>" /></td></tr>	
			<tr><td colspan=2><center><input type="hidden" name="update" value="1" /><input type="submit" value="<?php print _("Update Printer"); ?>" /></center></td></tr>
		</table></center>
		</form>
		<?php
	}
	
	public function edit_post()
	{
		// Find
		$Printer = new Printer($this->request['id']);
		
		// Hook
		$this->HookManager->processEvent('PRINTER_EDIT_POST', array('Printer' => &$Printer));
		
		// POST
		try
		{
			/*
			if ($alias != null)
			{
				$sql = "UPDATE printers SET pPort = '$port', pDefFile = '$inf', pModel ='$model', pAlias ='$alias', pIP = '$ip' WHERE pID = '$id'";

				if (mysql_query($sql, $conn))
				{
					msgBox(_('Update Successful'));
				}
				else
				{
					msgBox(_('Failed to create printer!'));
				}
			}			
			else
			{
				msgBox(_('A required field is null, unable to update printer!'));
			}
			*/
			// PrinterManager
			$PrinterManager = $this->FOGCore->getClass('PrinterManager');
			
			// Error checking
			if ($PrinterManager->exists($_POST['name'], $Printer->get('id')))
			{
				throw new Exception(_('Username already exists'));
			}
			if ($_POST['password'] && $_POST['password_confirm'])
			{
				if (!$PrinterManager->isPasswordValid($_POST['password'], $_POST['password_confirm']))
				{
					throw new Exception(_('Password is invalid'));
				}
			}
			
			// Update User Object
			$Printer	->set('name',		$_POST['name'])
					->set('type',		($_POST['isGuest'] == 'on' ? '1' : '0'));
			
			// Set new password if password was passed
			if ($_POST['password'] && $_POST['password_confirm'])
			{
				$Printer->set('password',	$_POST['password']);
			}
			
			// Save
			if ($Printer->save())
			{
				// Hook
				$this->HookManager->processEvent('PRINTER_UPDATE_SUCCESS', array('Printer' => &$Printer));
				
				// Log History event
				$this->FOGCore->logHistory(sprintf('%s: ID: %s, Name: %s', _('User updated'), $Printer->get('id'), $Printer->get('name')));
				
				// Set session message
				$this->FOGCore->setMessage(_('User updated'));
				
				// Redirect to new entry
				$this->FOGCore->redirect(sprintf('?node=%s&sub=edit&%s=%s', $this->request['node'], $this->id, $Printer->get('id')));
			}
			else
			{
				// Database save failed
				throw new Exception('Database update failed');
			}
		}
		catch (Exception $e)
		{
			// Hook
			$this->HookManager->processEvent('PRINTER_UPDATE_FAIL', array('Printer' => &$Printer));
			
			// Log History event
			$this->FOGCore->logHistory(sprintf('%s update failed: Name: %s, Error: %s', _('User'), $_POST['name'], $e->getMessage()));
			
			// Set session message
			$this->FOGCore->setMessage($e->getMessage());
			
			// Redirect to new entry
			$this->FOGCore->redirect($this->formAction);
		}
	}
	
	public function delete()
	{
		// Find
		$Printer = new Printer($this->request['id']);
		
		// Title
		$this->title = sprintf('%s: %s', _('Remove'), $Printer->get('name'));
		
		// Hook
		$this->HookManager->processEvent('PRINTER_DELETE', array('Printer' => &$Printer));
		
		// TODO: Put table rows into variables -> Add hooking
		?>
		<p class="c"><?php print _("Click on the icon below to delete this printer from the FOG database."); ?></p>
		<form method="post" action="<?php print $this->formAction; ?>" class="c">
		<input type="submit" value="<?php print $this->title; ?>" />
		</form>
		<?php
	}
	
	public function delete_post()
	{
		// Find
		$Printer = new Printer($this->request['id']);
		
		// Hook
		$this->HookManager->processEvent('PRINTER_DELETE_POST', array('Printer' => &$Printer));
		
		// POST
		try
		{
			/*
			if ( $id !== null  )
			{
				$sql = "DELETE FROM 
						printers
					WHERE
						pID = '$id'";

				if ( mysql_query( $sql, $conn ) )
				{
					echo ( _("Printer has been deleted!") );
				}
				else
				{
					echo( _("Failed to delete printer!") );
				}
			}			
			else
				echo( _("A required field is null, unable to delete printer!") );
			*/
			
			// Error checking
			if (!$Printer->destroy())
			{
				throw new Exception(_('Failed to destroy User'));
			}
			
			// Hook
			$this->HookManager->processEvent('PRINTER_DELETE_SUCCESS', array('Printer' => &$Printer));
			
			// Log History event
			$this->FOGCore->logHistory(sprintf('%s: ID: %s, Name: %s', _('User deleted'), $Printer->get('id'), $Printer->get('name')));
			
			// Set session message
			$this->FOGCore->setMessage(sprintf('%s: %s', _('User deleted'), $Printer->get('name')));
			
			// Redirect
			$this->FOGCore->redirect(sprintf('?node=%s', $this->request['node']));
		}
		catch (Exception $e)
		{
			// Hook
			$this->HookManager->processEvent('PRINTER_DELETE_FAIL', array('Printer' => &$Printer));
			
			// Log History event
			$this->FOGCore->logHistory(sprintf('%s %s: ID: %s, Name: %s', _('User'), _('deleted'), $Printer->get('id'), $Printer->get('name')));
			
			// Set session message
			$this->FOGCore->setMessage($e->getMessage());
			
			// Redirect
			$this->FOGCore->redirect($this->formAction);
		}
	}
}

// Register page with FOGPageManager
$FOGPageManager->register(new PrinterManagementPage());