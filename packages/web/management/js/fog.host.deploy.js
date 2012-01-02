/****************************************************
 * FOG Host Management - Deploy - JavaScript
 *	Author:		Blackout
 *	Created:	9:18 AM 27/12/2011
 *	Revision:	$Revision$
 *	Last Update:	$LastChangedDate$
 ***/

$(function()
{
	// Bind radio buttons for 'Single' and 'Cron' scheduled task
	$('input[name="scheduleType"]').click(function()
	{
		var $this = $(this);
		var $content = $this.parents('p').parent().find('p').eq($this.parent().index());
		
		if ($this.is(':checked'))
		{
			$content.slideDown('fast').siblings('.hidden').slideUp('fast');
		}
		else
		{
			$content.slideDown('fast');
			$('.calendar').remove();
			$('.error').removeClass('error');
		}
	});
	
	// Basic validation on deployment page
	$('form#deploy-container').submit(function()
	{
		var result = true;
		var scheduleType = $('input[name="scheduleType"]:checked', $(this)).val();
		var inputsToValidate = $('#' + scheduleType + 'Options > input').removeClass('error');
	
		if (scheduleType == 'cron')
		{
			inputsToValidate.each(function()
			{
				var $this = $(this);
				
				// Basic checks
				if ($this.val() != '*' && ($this.val() == '' || parseInt($this.val(), 10) != $this.val() || $this.val() > 31 || $this.val() < 1))
				{
					result = false;
					
					$this.addClass('error');
				}
			});
		}
		else if (scheduleType == 'single')
		{
			// Format check
			if (!inputsToValidate.val().match(/\d{4}\/\d{2}\/\d{2} \d{2}:\d{2}/))
			{
				result = false;
					
				inputsToValidate.addClass('error').click();
			}
		}
		
		return result;
	});
	
	// Fiddle with calendar to make it auto open and close
	// TODO: Find a better, modern calendar
	$('#scheduleSingle').click(function()
	{
		if ($(this).is(':checked'))
		{
			$('#scheduleSingleTime').parent().slideDown('fast', function()
			{
				var dayClickRemoveCalendar = function()
				{
					$('.daysrow .day').click(function()
					{
						$('.calendar').remove();
					});
				}
				
				$(this)	.children(0)
					.focus(function()
					{
						$(this).blur();
					})
					.click(function()
					{
						dayClickRemoveCalendar();
					}).click();
				
				dayClickRemoveCalendar();
			});
		}
	});
});