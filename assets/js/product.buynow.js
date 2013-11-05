(function($){

	$('#submit-button').on('click', function(e){

		e.preventDefault();

		var form = $(this).closest('form');
		var errors = false;
		var amount = $(form).find('input[name="amount"]').val();

		$('.alert').html('');

		$(form).find('.required').each(function(){

			if ( $(this).val() == '' ) {
				$(this).addClass('error');
				errors = true;

				$('.alert').html('Some required fields are missing');
			}

		});

		if (!errors) {
			console.log($(form).find('input[name="stripeToken"]').val())
			// Show payment screen from Stripe if required
			if ( $(form).find('input[name="stripeToken"]').val() == '' ) {

				var token = function(res) {
					$(form).find('input[name="stripeToken"]').val(res.id);
					$(form).submit();
				};

				console.log($(form).find('input[name="stripe_public_key"]').val());
				StripeCheckout.open({
					key:         $(form).find('input[name="stripe_public_key"]').val(),
					amount:      amount * 100,
					currency:    'usd',
					name:        '<?php echo $post->post_title ?>',
					description: '<?php echo $post->post_title ?>',
					token:       token
				});

			// Submit the entry
			} else {

				$(form).submit();

			}

		}

	});

	// Remove errors when field has a value
	$('body').on('keyup change', '.error', function() {

		if ( $(this).val() != '' ) $(this).removeClass('error');

	});

})(jQuery);