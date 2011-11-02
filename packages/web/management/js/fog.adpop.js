
$(	function()
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
	}
);

