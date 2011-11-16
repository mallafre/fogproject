/****************************************************
 * FOG Main JS
 *	Author:		Blackout
 *	Created:	10:51 AM 21/03/2011
 *	Revision:	$Revision$
 *	Last Update:	$LastChangedDate$
 ***/

// JQuery autoloader
$(function()
{
	// Process FOG JS Variables
	$('.fog-variable').fogVariable();
	
	// Process FOG Message Boxes
	$('.fog-message-box').fogMessageBox();
	
	// Host Ping
	$('.ping').fogPing();
	
	// Nav Menu: Add hover label
	$('#menu li a').each(function() {
		// Variables
		var $this = $(this);
		var $img = $this.find('img');
		
		// Add our label
		$this.prepend('<span class="nav-label">' + $this.attr('title') + '</span>');
		
		// Label variable
		var $label = $this.parent().find('span');
		
		// Unset 'title' so the browser does not display its own lame popup
		$this.attr('title', '')
		
		// Add show/hide hover
		$this.hover(function() {
			// Recalculate left to center labels
			var center = ($label.width() - $this.width()) / 2;
			var left = $this.offset().left - (center > 0 ? center : -center/2);
			
			// Set 'left'
			$label.css({ 'left': left + 'px', 'top': $this.offset().top + 55 + 'px' }).show();
		}, function() {
			$label.hide();
		});
	});
	
	// Tooltips
	HookTooltips();

	// Host Search
	$('#host-search').fogAjaxSearch({
		'URL':		'ajax/host.search.php',
		'Template':	function(data, i)
		{
			return '<tr id="host-' + data['id'] + '"><td><input type="checkbox" name="HID' + data['id'] + '" checked="checked" /></td><td><span class="icon ping"></span></td><td><a href="?node=host&sub=edit&id=' + data['id'] + '" title="Edit">' + data['hostname'] + '</a></td><td>' + data['mac'] + '</td><td>' + (data['ip'] ? data['ip'] : '&nbsp;') + '</td><td class="c"><a href="?node=host&sub=edit&id=' + data['id'] + '"><span class="icon icon-edit" title="Edit: ' + data['hostname'] + '"></span></a></td></tr>';
		}
	});
	
	// Group Search
	$('#group-search').fogAjaxSearch({
		'URL':		'ajax/group.search.php',
		'Template':	function(data, i)
		{
			return '<tr><td><a href="?node=group&sub=edit&groupid=' + data['id'] + '" title="Edit">' + data['name'] + '</a></td><td>' + data['description'] + '</td><td class="c">' + data['count'] + '</td><td class="c"><a href="?node=group&sub=edit&groupid=' + data['id'] + '"><span class="icon icon-edit" title="Edit: ' + data['name'] + '"></span></a></td></tr>';
		}
	});
	
	// Task Search
	$('#task-search').fogAjaxSearch({
		'URL':		'ajax/tasks.search.php',
		'Template':	function(data, i)
		{
			// Alter output based on type
			if (data['type'] == 'host')
			{
				return '<tr id="host-' + data['id'] + '"><td>' + data['name'] + '</td><td colspan="2">' + data['mac'] + '</td><td class="c">' + (data['running'] ? '<a href="?node=tasks&sub=active"><span class="icon icon-taskrunning" title="Task is running"></span></a>' : '<a href="?node=tasks&type=host&direction=down&noconfirm=' + data['id'] + '"><span class="icon icon-download" title="Deploy"></span></a>') + '</td><td class="c">' + (data['running'] ? '<a href="?node=tasks&sub=active"><span class="icon icon-taskrunning" title="Task is running"></span></a>' : '<a href="?node=tasks&type=host&direction=up&noconfirm=' + data['id'] + '"><span class="icon icon-upload" title="Upload"></span></a>') + '</td><td class="c">' + (data['running'] ? '<a href="?node=tasks&sub=active"><span class="icon icon-taskrunning" title="Task is running"></span></a>' : '<a href="?node=tasks&sub=advanced&hostid=' + data['id'] + '"><span class="icon icon-advanced" title="Advanced Deployment"></span></a>' ) + '</td></tr>';
			}
			else if (data['type'] == 'group')
			{
				return '<tr id="group-' + data['id'] + '"><td colspan="2">' + data['name'] + '</td><td class="c">' + data['count'] + '</td><td class="c"><a href="?node=tasks&type=group&direction=down&noconfirm=' + data['id'] + '"><span class="icon icon-download" title="Deploy"></span></a></td><td class="c"><a href="?node=tasks&type=group&direction=downmc&noconfirm=' + data['id'] + '"><span class="icon icon-multicast" title="Multicast Deploy"></span></a></td><td class="c"><a href="?node=tasks&sub=advanced&groupid=' + data['id'] + '"><span class="icon icon-advanced" title="Advanced Deploy"></span></a></td></tr>';
			}
		},
		'CallbackSearchSuccess':	function(Container)
		{
			// Insert Header above Hosts set
			$('<tr class="header"><td>Host Name</td><td>MAC</td><td>&nbsp;</td><td class="c">Deploy</td><td class="c">Upload</td><td class="c">Advanced</td></tr>').insertBefore($('tr[id^="host-"]:eq(0)'));
			
			// Hide / Show group header row
			var GroupRows = $('tr[id^="group-"]');
			if (GroupRows.size() == 0)
			{
				$('.header:eq(0)').hide();
			}
			else
			{
				$('.header:eq(0)').show();
			}
		}
	});
	
	// LEGACY - Task Confirm Date/time picker
	$('#singlescheddatetime').dynDateTime({
		'showsTime':	true,
		'ifFormat':	'%Y/%m/%d %H:%M',
		'daFormat':	'%l;%M %p, %e %m,  %Y',
		'align':	'TL',
		'electric':	false,
		'timeFormat':	24,
		'singleClick':	false,
		'displayArea':	'.siblings(".dtcDisplayArea")',
		'button':	'.next()'
	});

	// Snapin uploader for existing snapins
	$('#snapin-upload').click(function() {
		$('#uploader').html('<input type="file" name="snap" />');
	});

	// Image Search
	$('#image-search').fogAjaxSearch({
		'URL':		'ajax/image.search.php',
		'Template':	function(data, i)
		{
			return '<tr><td><a href="?node=images&sub=edit&imageid=' + data['id'] + '" title="Edit">' + data['name'] + '</a></td><td>' + data['description'].substr(0, 35) + '</td><td class="c">' + data['storagegroup'] + '</td><td class="c"><a href="?node=images&sub=edit&imageid=' + data['id'] + '"><span class="icon icon-edit" title="Edit: ' + data['name'] + '"></span></a></td></tr>';
		}
	});
	
	// Snapin Search
	$('#snapin-search').fogAjaxSearch({
		'URL':		'ajax/snapin.search.php',
		'Template':	function(data, i)
		{
			return '<tr><td><a href="?node=snap&sub=edit&snapinid=' + data['id'] + '" title="Edit">' + data['name'] + '</a></td><td>' + data['description'].substr(0, 45) + '</td><td class="c"><a href="?node=snap&sub=edit&snapinid=' + data['id'] + '"><span class="icon icon-edit" title="Edit: ' + data['name'] + '"></span></a></td></tr>';
		}
	});
	
	// Printer Search
	$('#printer-search').fogAjaxSearch({
		'URL':		'ajax/printer.search.php',
		'Template':	function(data, i)
		{
			return '<tr><td>' + data['model'] + '</td><td>' + data['alias'] + '</td><td>' + data['port'] + '</td><td>' + data['inf'] + '</td><td>' + data['ip'] + '</td><td class="c"><a href="?node=print&sub=edit&id=' + data['id'] + '"><span class="icon icon-edit" title="Edit: ' + data['alias'] + '"></span></a></td></tr>';
		}
	});
	
	// Host Management - Select all checkbox
	$('.header input[type="checkbox"][name="no"]').click(function()
	{
		var $this = $(this);
		if ($this.is(':checked'))
		{
			$('input[type="checkbox"][name^="HID"]').attr('checked', true);
			//checkAll(document.hosts.elements);
		}
		else
		{
			$('input[type="checkbox"][name^="HID"]').attr('checked', false);
			//uncheckAll(document.hosts.elements);
		}
	});
	
	// User Search
	$('#user-search').fogAjaxSearch({
		'URL':		'ajax/user.search.php',
		'Template':	function(data, i)
		{
			return '<tr><td><a href="?node=group&sub=edit&groupid=' + data['id'] + '" title="Edit">' + data['name'] + '</a></td><td>' + data['description'] + '</td><td class="c">' + data['count'] + '</td><td class="c"><a href="?node=group&sub=edit&groupid=' + data['id'] + '"><span class="icon icon-edit" title="Edit: ' + data['name'] + '"></span></a></td></tr>';
		}
	});
});

function HookTooltips()
{
	// TODO: Clean up - use LIVE - tipsy fails on IE with LIVE
	setTimeout(function()
	{
		$('.tipsy').remove();
		$('a[title]', Content).tipsy({ 'gravity': 'e' });
		$('.remove-mac[title], .add-mac[title], .icon-help[title]', Content).tipsy({ 'gravity': 'w' });
		$('.task-name[title], .icon[title]', Content).tipsy({ 'gravity': 's' });
		$('img[title]', Content).tipsy();
	}, 20);
}


function popUpWindow( url )
{
	newwindow=window.open(url,'name','height=400,width=330,toolbar=no,menubar=no,scrollbars=yes,resizable=yes,location=no,directories=no,status=no');
	if (window.focus) 
		newwindow.focus();
}

function changeClass(id, cssclass)
{
	$('#' + id).removeClass().addClass(cssclass);
}

function StopAllPings()
{
	var len = PingActive.length;
	
	// Do we have active ping checks running?
	if (len > 0)
	{
		// Abort first ping check, remove from array
		PingActive[0].abort();
		PingActive.splice(0, 1)
		
		// If we still have ping checks running, schedule another run of this function
		// This passes control back to the browser briefly, avoiding browser lock ups
		if ((len-1) > 0)
		{
			setTimeout(function()
			{
				StopAllPings();
			}, 25);
		}
	}
}

function getContentHD(url)
{
	// TODO: Replace this with generic search JS
	var element = $('#remainingfreespace');
	
	$.ajax({
		'url':		url,
		'method':	'GET',
		'beforeSend':	function()
		{
			// TODO: Replace with loading spinner
			element.html('<center><b>Performing Search...</b></center>');
		},
		'success':	function(data)
		{
			element.html('');
			
			// TODO: OLD CODE - rewrite
			var strRes = data;
			if ( strRes != null )
			{
				var arRes = strRes.split("@");
				if ( arRes.length == 2 )
				{
					var totalspace = Math.round( (Number(arRes[0]) + Number(arRes[1]) ) * 100  ) / 100;
					var pct = Math.round( (arRes[1] / totalspace) * 100 );
					var pctText = Math.round( (arRes[1] / totalspace) * 10000 ) / 100;
					
					$('#dashSpaceGraph').html("<img src=\"./images/openslots.jpg\" height=25 width=\"" + pct + "%\" />");
					$('#dashPCTText').html(pctText + "% Used <br />Used: " + arRes[1] + " GB  Free: " + arRes[0] + " GB  Total: " + totalspace + " GB");
				}				
			}
		},
		'error':	function(e)
		{
			if (url.match(/localhost|127\.0\.0\.1/))
			{
				element.html(e + "<p>(Try using the server's IP address or hostname instead of localhost.)</p>");
			}
			else
			{
				element.html('Failed to update!');
			}
			
			setTimeout(function()
			{
				element.fadeOut('fast');
			}, 1000);
		}
	});	
}

function setADDefaults(dn, ou, user, pass)
{
	var objDN = document.getElementById( 'dn' );
	var objOU = document.getElementById( 'ou' );
	var objUN = document.getElementById( 'un' );
	var objPS = document.getElementById( 'ps' );


	if ( objDN != null && objOU != null && objUN != null && objPS != null)
	{

		if ( objDN.value == '' && objOU.value == '' && objUN.value == '' && objPS.value == '' )
		{
			objDN.value = dn;
			objOU.value = ou;
			objUN.value = user;
			objPS.value = pass;
		}
	}			
}

function parseMAC( mac, element )
{
	if ( mac != null && element != null )
	{
		if ( mac.length == 12 )
		{
			var strNew = "";
			for( var i = 0; i < mac.length; i++ )
			{
				var c = mac.charAt(i);
				if ( i % 2 == 0 && i != 0 )
				{
					if ( c != ":" )
						strNew += ":" + c;	
					else 
						strNew += c;		
				}
				else
					strNew += c;
			}
			element.value = strNew;
		}
		else if ( mac.length == 17 )
		{
			element.value = mac.replace(/-/g,":");	
		}
	}
}

function disableTextModePXEMenu(ele)
{
	if ( ele != null )
	{
		if( ele[ele.selectedIndex].value == "1" )
		{
			document.getElementById( 'masterpassword' ).disabled = false;
			document.getElementById( 'masterpassword' ).value= '';			
			
			document.getElementById( 'memtestpassword' ).disabled = false;
			document.getElementById( 'memtestpassword' ).value= '';
			
			document.getElementById( 'reginputpassword' ).disabled = false;
			document.getElementById( 'reginputpassword' ).value= '';
			
			document.getElementById( 'regpassword' ).disabled = false;
			document.getElementById( 'regpassword' ).value= '';
			
			document.getElementById( 'debugpassword' ).disabled = false;
			document.getElementById( 'debugpassword' ).value= '';	
			
			document.getElementById( 'quickimage' ).disabled = false;
			document.getElementById( 'quickimage' ).value= '';	

			document.getElementById( 'sysinfo' ).disabled = false;
			document.getElementById( 'sysinfo' ).value= '';	
			
			document.getElementById( 'hidemenu' ).disabled = false;
			
		
		}
		else
		{
			document.getElementById( 'masterpassword' ).disabled = true;
			document.getElementById( 'masterpassword' ).value= '';			
			
			document.getElementById( 'memtestpassword' ).disabled = true;
			document.getElementById( 'memtestpassword' ).value= '';
			
			document.getElementById( 'reginputpassword' ).disabled = true;
			document.getElementById( 'reginputpassword' ).value= '';
			
			document.getElementById( 'regpassword' ).disabled = true;
			document.getElementById( 'regpassword' ).value= '';
			
			document.getElementById( 'debugpassword' ).disabled = true;
			document.getElementById( 'debugpassword' ).value= '';	
			
			document.getElementById( 'quickimage' ).disabled = true;
			document.getElementById( 'quickimage' ).value= '';	
			
			document.getElementById( 'sysinfo' ).disabled = true;
			document.getElementById( 'sysinfo' ).value= '';	
			
			document.getElementById( 'hidemenu' ).disabled = true;														
		}
	}
}

function duplicateImageName()
{
	if ( document.getElementById('iName') != null && document.getElementById('iFile') )
	{
		if ( document.getElementById('iFile').value == null || document.getElementById('iFile').value.length == 0 )
		{
			var str = document.getElementById('iName').value;
			var strOut = "";
			for( var i = 0; i < str.length; i++ )
			{
				var c = str[i];
				var code = c.charCodeAt(0);
				if ( ( code >= "a".charCodeAt(0) && code <= "z".charCodeAt(0) ) || ( code >= "A".charCodeAt(0) && code <= "Z".charCodeAt(0) ) || ( code >= "0".charCodeAt(0) && code <= "9".charCodeAt(0) ) )
					strOut += c;
			}
			document.getElementById('iFile').value=strOut;
		}
	}
	else
		alert( 'test');
}

function clearIf( ele, value )
{
	if ( ele != null && value != null )
	{
		
		if ( ele.value == value )
			ele.value = '';
	}
}
