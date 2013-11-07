(function($){

	$('#submit-button').on('click', function(e){

		e.preventDefault();

		var form = $(this).closest('form');
		var errors = false;
		var amount = $(form).find('input[name="total"]').val();

		$('.alert').html('');

		$(form).find('.required').each(function(){

			if ( $(this).val() == '' ) {
				$(this).addClass('error');
				errors = true;

				$('.alert').html('Some required fields are missing');
			}

		});

		if (!errors) {

			// Show payment screen from Stripe if required
			if ( $(form).find('input[name="stripeToken"]').val() == '' ) {

				var token = function(res) {
					$(form).find('input[name="stripeToken"]').val(res.id);
					$(form).submit();
				};

				console.log($(form).find('input[name="stripe_public_key"]').val());
				StripeCheckout.open({
					key:         $(form).find('input[name="stripe_public_key"]').val(),
					address:     true,
					amount:      amount * 100,
					currency:    'usd',
					name:        $(form).find('input[name="name"]').val(),
					description: $(form).find('input[name="description"]').val(),
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

	$('body').on('change keyup', '.form.checkout select[name="quantity"],.form.checkout input[name="amount"]', function() {

		var total = $('.form.checkout select[name="quantity"]').val() * $('.form.checkout input[name="amount"]').val();

		$('.form.checkout input[name="total"]').val( total );
		$('.form.checkout span.total').text( '$' + total );
	});

})(jQuery);