/****************************************************
 * FOG Host Management JS
 *	Author:		Blackout
 *	Created:	2:36 PM 8/05/2011
 *	Revision:	$Revision$
 *	Last Update:	$LastChangedDate$
 ***/

$(function()
{
	// Host ping
	$('.host-ping').fogPing({ 'Delay': 0, 'UpdateStatus': 0 }).removeClass('host-ping');
	
	// Fetch MAC Manufactors
	$('.mac-manufactor').each(function()
	{
		var $this = $(this);
		var input = $this.parent().find('input');
		var mac = (input.size() ? input.val() : $this.parent().find('.mac').html());
		$this.load('./ajax/mac-getman.php?prefix=' + mac);
	});
	
	// Remove MAC Buttons
	$('.remove-mac').unbind().live('click', function()
	{
		$(this).parent().remove();
		$('.tipsy').remove();
		
		if ($('#additionalMACsCell').find('.additionalMAC').size() == 0)
		{
			$('#additionalMACsRow').hide();
		}
		
		return false;
	});
	
	// Add MAC Buttons - TODO: Rewrite OLD CODE
	$('.add-mac').click(function()
	{
		$('#additionalMACsRow').show();
		$('#additionalMACsCell').append('<div><input class="addMac" type="text" name="additionalMACs[]" /> <span class="icon icon-remove remove-mac hand" title="Remove MAC"></span> <span class="mac-manufactor"></span></div>');
		
		HookTooltips();
		
		return false;
	});
	
	if ($('.additionalMAC').size())
	{
		$('#additionalMACsRow').show();
	}
});