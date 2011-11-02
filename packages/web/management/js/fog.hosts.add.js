/****************************************************
 * FOG Dashboard JS
 *	Author:		Blackout
 *	Created:	12:22 PM 9/05/2011
 *	Revision:	$Revision$
 *	Last Update:	$LastChangedDate$
 ***/

var MACLookupTimer;
var MACLookupTimeout = 1000;

$(function()
{
	MACUpdate = function()
	{
		var $this = $(this);
		
		$this.val($this.val().replace(/-/g, ':').toUpperCase());
		
		if (MACLookupTimer) clearTimeout(MACLookupTimer);
		MACLookupTimer = setTimeout(function()
		{
			$('#priMaker').load('./ajax/mac-getman.php?prefix=' + $this.val());
		}, MACLookupTimeout);
	};
	
	$('#mac').keyup(MACUpdate).blur(MACUpdate);
});