/****************************************************
 * * FOG Host Management - Edit - JavaScript
 *	Author:		Blackout
 *	Created:	9:34 AM 1/01/2012
 *	Revision:	$Revision$
 *	Last Update:	$LastChangedDate$
 ***/

$(function()
{
	// Host Tasks - show advanced tasks on click
	$('.advanced-tasks-link').click(function(event)
	{
		$(this).parents('tr').fadeOut('fast', function()
		{
			$('#advanced-tasks').slideDown('slow');
		});
		
		event.preventDefault();
	});
	
	$('.toggle-checkbox').click(function()
	{
		var $this = $(this);
		var checked = $this.attr('checked');
		
		$this.parents('table').find('tbody').find('input[type="checkbox"]').attr('checked', (checked ? 'checked' : ''));
	});
	
	$('#action-box').submit(function()
	{
		var checked = $('input.toggle-host:checked');
		var hostIDArray = new Array();
		
		for (var i = 0, len = checked.size(); i < len; i++)
		{
			hostIDArray[hostIDArray.length] = checked.eq(i).attr('value');
		}
		
		$('#hostIDArray', this).val( hostIDArray.join(',') );
	});
});