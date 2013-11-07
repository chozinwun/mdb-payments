<?php
	$amount_type = get_post_meta( $post->ID, 'amount_type', true);
	$amount = get_post_meta( $post->ID, 'amount', true);
	$button_label = get_post_meta( $post->ID, 'button_label', true );
	$max_quantity = get_post_meta( $post->ID, 'max_quantity', true );
	$short_description = get_post_meta( $post->ID, 'short_description', true );

	$button_label = ($button_label) ? $button_label : 'Buy Now';
	
	$post_status = get_post_status( $post->ID );

	if ($post_status == 'publish') {
		$stripe_public_key = get_option('mdb_product_stripe_public_key');
		$stripe_secret_key = get_option('mdb_product_stripe_secret_key');
	} else {
		$stripe_public_key = get_option('mdb_product_stripe_test_public_key');
		$stripe_secret_key = get_option('mdb_product_stripe_test_secret_key');
	}

	/*
	ToDos:
	1. Send form to transaction api √
	2. check for payment gateway
	3. process transaction √
	4. Email receipt
	5. Redirect to "success" screen √

	Nice Haves:
	- Add check for logged in?

	*/
	if ( isset($_POST) && !empty($_POST) ) {

		// Process payment
		$response = json_decode( mdb_post_payment( $stripe_secret_key, $_POST['amount']) );
	}
?>

<?php if( isset($response->error) ): ?>

	<div class="msg error"><?php echo $response->error->message ?></div>

<?php elseif ( isset($response->id)): ?>
	
	<div class="msg success">Thank You. Your payment was successfully processed!</div>

<?php endif; ?>

<form class="form checkout" action="<?php echo get_permalink( $post->ID ) ?>" method="POST">

	<ul>
		<li>
			<label>Email</label>
			<input type="text" name="email" class="required" />
		</li>
		<li>
			<?php if ( $max_quantity > 1 ): ?>
				
				<label>Qauntity</label>
				<select name="quantity">
					<?php for( $x = 1; $x < $max_quantity + 1; $x++ ): ?>
						<option value="<?php echo $x ?>"><?php echo $x ?></option>
					<?php endfor; ?>
				</select>
			<?php endif; ?>
		</li>
		<li>
			<?php if( $amount_type == 'donation' ): ?>

				<label>Amount: </label>
				<input type="text" name="amount" class="required"  />

			<?php else: ?>

				<label>Amount: <span class="total">$<?php echo $amount ?></label>
				<input type="hidden" name="amount" value="<?php echo $amount ?>" />

			<?php endif; ?>
		</li>
	</ul>

	<input type="hidden" name="total" value="" />
	<input type="hidden" name="name" value="<?php echo $post->post_title ?>" />
	<input type="hidden" name="description" value="<?php echo $short_description ?>" />
	<input type="hidden" name="stripeToken" value="" />
	<input type="hidden" name="stripe_public_key" value="<?php echo $stripe_public_key ?>" />
	<button id="submit-button"><?php echo $button_label ?></button>
</form>

<script src="https://checkout.stripe.com/v2/checkout.js"></script>