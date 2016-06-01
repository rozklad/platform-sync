$(function(){

	// If any of the important inputs change, revoke operation
	$('#import, [name="delimiter"]').change(function(){

		var data = new FormData(),
				$form = $(this).parents('form'),
				action = $form.attr('action'),
				setup_url = $form.find('[name="setup_url"]').val();

		// There is no file yet
		if ( $('#import')[0].files.length == 0 ) {
			return false;
		}

		$.each($('#import')[0].files, function(i, file) {
			data.append('import', file);
			// data.append('import-'+i, file);	- multiple files support
		});

		data.append('delimiter', $('[name="delimiter"]').val());
		data.append('enclosure', $('[name="enclosure"]').val());
		data.append('newline', $('[name="newline"]').val());
		data.append('dictionary', $('[name="dictionary"]').val());
		data.append('entity', $('[name="entity"]').val());

		$.ajax({
			url: action,
			data: data,
			cache: false,
			contentType: false,
			processData: false,
			type: 'POST',
			success: function(data){

				/*var compiled = _.template( $('#tree').html() );
				$('#results').html( compiled({results: data}) );
*/
				var compiled = _.template( $('#table').html() );
				$('#results-table').html( compiled({results: data}) );

				$('html, body').animate({
					scrollTop: $("#results-table").offset().top
				}, 500);

				synchronizeSelects();
			}
		});

	});

	$('[type="submit"]').click(function(event){

		var $form = $(this).parents('form'),
				setup_url = $form.find('[name="setup_url"]').val();

		$form.attr('action', setup_url);
	});

});

function synchronizeSelects() {

	$('select').change(function(event){

		var name = $(this).attr('name'),
				value = $(this).val();

		$('select[name="'+name+'"]').val(value);

	});

}
