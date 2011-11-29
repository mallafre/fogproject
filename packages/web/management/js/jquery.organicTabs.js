// Updated: Blackout - 1:15 PM 28/11/2011
(function($)
{
	$.organicTabs = function(el, options)
	{
		// Base of class
		var base = this;
		
		// Options
		base.options = $.extend({},$.organicTabs.defaultOptions, options);
		
		// Variables
		base.$el = $(el);
		base.$nav = base.$el.find('> ul:eq(0)');
		base.$content = (base.options.targetID ? $(base.options.targetID) : base.$el.find('> div:eq(0)'));
		base.anchor = window.location.href.split('#')[1] || false;
		base.total = base.$nav.find('li > a').size();
		
		// Init function
		base.init = function()
		{
			// Nav: Hook click event
			base.$nav.delegate('li > a', 'click', function()
			{
				// New content and content ID
				var newContent = $(this);
				var newContentID = newContent.attr('href').substring(1);
				
				// Figure out current list via CSS class
				var currentContentID = base.$el.find('a.organic-tabs-current').attr('href').substring(1);
				
				// Set content's outer wrapper height to (static) height of current inner content
				base.$content.height( base.$content.height() );
				
				if ((newContentID != currentContentID) && (base.$el.find(':animated').length == 0))
				{
					// Fade out current list
					$('#' + currentContentID).fadeOut(base.options.speed, function()
					{
						$(this).hide();
					
						// Fade in new list on callback
						$('#' + newContentID).fadeIn(base.options.speed);
						
						// Adjust outer wrapper to fit new list snuggly
						base.$content.animate({
							'height'	: $('#' + newContentID).height()
						});
						
						// Remove highlighting - Add to just-clicked tab
						base.$nav.find('li a').removeClass('organic-tabs-current');
						newContent.addClass('organic-tabs-current');
					});
				}
				
				// Don't behave like a regular link
				// Stop propegation and bubbling
				return false;
			});
			
			// Content: Hide all tabs expect for the first tab
			base.$content.find('ul').addClass('organic-tabs-hidden').eq(0).removeClass('organic-tabs-hidden');
			
			// Content: On load -> Check anchor -> Click anchor link to change tab to content
			if (base.anchor)
			{
				$('a[href="#' + base.anchor + '"]').click();
			}		
		};
		
		// Function: Returns current active tab
		base.current = function()
		{
			return base.$nav.find('.organic-tabs-current').parent().index();
		};
		
		// Function: Selects a tab
		base.activate = function(position)
		{
			base.$nav.find('li > a').eq(position).click();
		};
		
		// Function: Activates the next tab
		base.next = function()
		{
			var current = base.current();
			var next = (current == (base.total - 1) ? 0 : current + 1);
			
			base.activate(next);
			
			return next;
		};
		
		// Function: Activates the previous tab
		base.prev = function()
		{
			var current = base.current();
			var prev = (current == 0 ? (base.total - 1) : current - 1);
			
			base.activate(prev);
			
			return prev;
		};
		
		// Add 'organicTabs' data to element - can be used to call organicTabs
		// i.e. $('#tabs').data('organicTabs').next();
		base.$el.data('organicTabs', base);
		
		// Run Init
		base.init();
	};
	
	// Defaults
	$.organicTabs.defaultOptions = {
		'speed'			: 300,
		'targetID'		: ''
	};
	
	// jQuery Function
	$.fn.organicTabs = function(options)
	{
		return this.each(function()
		{
			(new $.organicTabs(this, options));
		});
	};
	
})(jQuery);