/****************************************************
 * FOG Group Management - Edit - JavaScript
 *	Author:		Blackout
 *	Created:	10:26 AM 1/01/2012
 *	Revision:	$Revision$
 *	Last Update:	$LastChangedDate$
 ***/

$(function()
{
	// Bind to AD Settings checkbox
	$('#adEnabled').change(function() {
		
		if ( $(this).attr('checked') )
		{	
			if ( $('#adDomain').val() == '' && $('#adOU').val() == '' && $('#adUsername').val() == '' &&  $('#adPassword').val() == '' )
			{
				$.ajax({
					'type':		'GET',
					'url':		'ajax/host.adsettings.php',
					'cache':	false,
					'dataType':	'json',
					'success':	function(data)
					{	
						$('#adDomain').val(data['domain']);
						$('#adOU').val(data['ou']);
						$('#adUsername').val(data['user']);
						$('#adPassword').val(data['pass']);
					}
				});
			}

		}
	});
	
	// Host Tasks - show advanced tasks on click
	$('.advanced-tasks-link').click(function(event)
	{
		$(this).parents('tr').fadeOut('fast', function()
		{
			$('#advanced-tasks').slideDown('slow');
		});
		
		event.preventDefault();
	});
});