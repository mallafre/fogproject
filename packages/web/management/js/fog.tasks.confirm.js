// 12:23 PM 9/05/2011

jQuery(document).ready(function() {
	jQuery("#singlescheddatetime").dynDateTime({
		showsTime: true,
		ifFormat: "%Y/%m/%d %H:%M",
		daFormat: "%l;%M %p, %e %m,  %Y",
		align: "TL",
		electric: false,
		timeFormat: 24,
		singleClick: false,
		displayArea: ".siblings('.dtcDisplayArea')",
		button: ".next()" 
	});
});