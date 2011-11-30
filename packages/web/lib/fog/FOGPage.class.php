<?php

// Blackout - 12:38 PM 25/09/2011
abstract class FOGPage
{
	// Name
	public $name = '';
	
	// Node Variable
	public $node = '';
	
	// ID Variable
	public $id = '';
	
	// Menu Items
	// TODO: Finish
	public $menu = array(
		
	);
	
	// Sub Menu Items - when ID Variable is set
	// TODO: Finish
	public $subMenu = array(
		
	);
	
	// Page title
	public $title = '';
	
	// Render engine
	public $headerData = array();
	public $data = array();
	public $templates = array();
	public $attributes = array();
	public $searchFormURL = '';	// If set, allows a search page using FOGAjaxSearch JQuery function
	private $wrapper = 'td';
	private $result;
	
	// Post
	protected $post = false;
	protected $request;
	protected $formAction;
	protected $sub;
	protected $tab;
	
	// FOG Class Variables
	protected $db;
	protected $FOG;
	protected $HookManager;
	protected $currentUser;
	
	// __construct
	public function __construct($name = '')
	{
		// Setup classes
		$this->db = $GLOBALS['db'];
		$this->FOGCore = $GLOBALS['FOGCore'];
		$this->HookManager = $GLOBALS['HookManager'];
		$this->currentUser = $GLOBALS['currentUser'];
		
		// Set name if passed
		if ($name)
		{
			$this->name = $name;
		}
		
		// True if $this->id is set in $GLOBALS context
		foreach (array('node', 'sub', 'tab', 'confirm') AS $x)
		{
			$this->request[$x] = (isset($_REQUEST[$x]) && !empty($_REQUEST[$x]) ? $_REQUEST[$x] : false);
		}
		$this->request['id'] = $this->request[$this->id] = (isset($_REQUEST[$this->id]) && !empty($_REQUEST[$this->id]) ? $_REQUEST[$this->id] : false);
		
		$this->post = ($_SERVER['REQUEST_METHOD'] == 'POST' ? true : false);
		$this->formAction = sprintf('%s?node=%s&sub=%s%s', $_SERVER['PHP_SELF'], $this->request['node'], $this->request['sub'], ($this->request['id'] ? sprintf('&%s=%s', $this->id, $this->request['id']) : ''));
		
		// DEBUG
		//printf('node: %s, sub: %s, id: %s, id value: %s, post: %s', $this->node, $this->sub, $this->id, $this->request['id'], ($this->post === false ? 'false' : $this->post));
	}
	
	// Default index page
	public function index($args)
	{
		printf('Index page of: %s%s', get_class($this), (count($args) ? ', Arguments = ' . implode(', ', array_map(create_function('$key, $value', 'return $key." : ".$value;'), array_keys($args), array_values($args))) : ''));
	}
	
	function set($key, $value)
	{
		$this->$key = $value;
		
		return $this;
	}
	
	function get($key)
	{
		return $this->$key;
	}
	
	function __toString()
	{
		$this->process();
	}
	
	public function render()
	{
		print $this->process();
	}
	
	public function process()
	{
		try
		{
			// Error checking
			if (!count($this->templates))
			{
				throw new Exception('Requires templates to process');
			}
			
			// Variables
			$result = '';
			
			// Is AJAX Request?
			if ($this->FOGCore->isAJAXRequest())
			{
				// JSON output
				$result = json_encode(array(
					'data'		=> $this->data,
					'templates'	=> $this->templates,
					'attributes'	=> $this->attributes
				));
			}
			else
			{
				// HTML output
				if ($this->searchFormURL)
				{
					$result = sprintf('<input id="%s-search" type="text" value="%s" class="search-input" />', (substr($this->node, -1) == 's' ? substr($this->node, 0, -1) : $this->node), _('Search'));
				}
			
				// Table -> Header Row
				$result .= sprintf('%s<table width="%s" cellpadding="0" cellspacing="0" border="0"%s>%s<thead>%s<tr class="header">%s</tr>%s<thead>%s<tbody>%s',
					"\n\n",
					'100%',
					($this->searchFormURL ? ' id="search-content"' : ''),
					"\n\t",
					"\n\t\t",
					$this->buildHeaderRow(),
					"\n\t",
					"\n\t",
					"\n\t\t",
					"\n"
				);
			
				// Rows
				if (count($this->data))
				{
					// Data found
					foreach ($this->data AS $rowData)
					{
						$result .= sprintf('<tr id="%s-%s" class="%s">%s</tr>%s',
							$this->name,
							$rowData['id'],
							(++$i % 2 ? 'alt1' : 'alt2'),
							$this->buildRow($rowData),
							"\n"
						);
					}
					
					// Set message
					if (!$this->searchFormURL)
					{
						$this->FOGCore->setMessage(sprintf('%s %s%s found', count($this->data), ucwords($this->node), (count($this->data) == 1 ? '' : (substr($this->node, -1) == 's' ? '' : 's'))));
					}
				}
				else
				{
					// No data found
					$result .= sprintf('<tr><td colspan="%s" class="no-active-tasks">%s</td></tr>',
						count($this->templates),
						($this->data['error'] ? (is_array($this->data['error']) ? '<p>' . implode('</p><p>', $this->data['error']) . '</p>' : $this->data['error']) : _('No results found'))
					);
				}
				
				// Table close
				$result .= sprintf('%s</tbody>%s</table>%s', "\n\t", "\n", "\n\n");
			}
		
			// Return output
			return $result;
		}
		catch (Exception $e)
		{
			return $e->getMessage();
		}
	}
	
	public function buildHeaderRow()
	{
		// Loop data
		foreach ($this->headerData AS $i => $content)
		{
			// Create attributes data
			foreach ((array)$this->attributes[$i] as $attributeName => $attributeValue)
			{
				// Format into HTML attributes -> Push into attributes array
				$attributes[] = sprintf('%s="%s"', $attributeName, $attributeValue);
			}

			// Push into results array
			$result[] = sprintf('<%s%s>%s</%s>',	$this->wrapper,
								(count($attributes) ? ' ' . implode(' ', $attributes) : ''),
								$content,
								$this->wrapper);
			
			// Reset
			unset($attributes);
		}
		
		// Return result
		return "\n\t\t\t" . implode("\n\t\t\t", $result) . "\n\t\t";
	}
	
	public function buildRow($data)
	{
		// Loop template data
		foreach ($this->templates AS $i => $template)
		{
			// Create attributes data
			foreach ((array)$this->attributes[$i] as $attributeName => $attributeValue)
			{
				// Format into HTML attributes -> Push into attributes array
				$attributes[] = sprintf('%s="%s"', $attributeName, $attributeValue);
			}
			
			// Create find and replace arrays for data
			foreach ($data AS $dataName => $dataValue)
			{
				// Legacy - remove when converted
				$dataFind[] = '#%' . $dataName . '%#';
				$dataReplace[] = $dataValue;
				
				// New
				$dataFind[] = '#\$\{' . $dataName . '\}#';
				$dataReplace[] = $dataValue;
			}
			foreach (array('node', 'sub', 'tab') AS $extraData)
			{
				// Legacy - remove when converted
				$dataFind[] = '#%' . $extraData . '%#';
				$dataReplace[] = $GLOBALS[$extraData];
				
				// New
				$dataFind[] = '#\$\{' . $extraData . '\}#';
				$dataReplace[] = $GLOBALS[$extraData];
			}
			
			// Remove any other data keys not found
			// Legacy
			$dataFind[] = '#%\w+%#';
			$dataReplace[] = '';
			
			// New
			$dataFind[] = '#\$\{\w+\}#';
			$dataReplace[] = '';
			
			// Replace variables in template with data -> wrap in $this->wrapper -> push into $result
			$result[] = sprintf('<%s%s>%s</%s>',	$this->wrapper,
								(count($attributes) ? ' ' . implode(' ', $attributes) : ''),
								preg_replace($dataFind, $dataReplace, $template),
								$this->wrapper);
			
			// Reset
			unset($dataFind, $dataReplace);
		}
		
		// Return result
		return "\n\t\t\t" . implode("\n\t\t\t", $result) . "\n\t\t";
	}
}