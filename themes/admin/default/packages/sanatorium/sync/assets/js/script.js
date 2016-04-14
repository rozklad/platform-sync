$(function(){

	$('#import').change(function(){

		var data = new FormData(),
			$form = $(this).parents('form'),
			action = $form.attr('action'),
			setup_url = $form.find('[name="setup_url"]').val();

		$.each($('#import')[0].files, function(i, file) {
			data.append('import', file);
			// data.append('import-'+i, file);	- multiple files support
		});

		$.ajax({
			url: action,
			data: data,
			cache: false,
			contentType: false,
			processData: false,
			type: 'POST',
			success: function(data){
				var compiled = _.template( $('#tree').html() );
				$('#results').html( compiled({results: data}) );
				$form.attr('action', setup_url);
			}
		});

	});

});