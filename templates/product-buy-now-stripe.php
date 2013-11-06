<?php
	$amount_type = get_post_meta( $post->ID, 'amount_type', true);
	$amount = get_post_meta( $post->ID, 'amount', true);
	$button_label = get_post_meta( $post->ID, 'button_label', true );
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
	1. Send form to transaction api
	2. check for payment gateway
	3. process transaction
	4. Email receipt
	5. Redirect to "success" screen

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

<form action="<?php echo get_permalink( $post->ID ) ?>" method="POST">

	<?php if( $amount_type == 'donation' ): ?>

		<label>Amount: </label>
		<input type="text" name="amount" class="required"  />

	<?php else: ?>

		<label>Amount: $<?php echo $amount ?></label>
		<input type="hidden" name="amount" value="<?php echo $amount ?>" />

	<?php endif; ?>

	<input type="hidden" name="name" value="<?php echo $post->post_title ?>" />
	<input type="hidden" name="description" value="<?php echo $short_description ?>" />
	<input type="hidden" name="stripeToken" value="" />
	<input type="hidden" name="stripe_public_key" value="<?php echo $stripe_public_key ?>" />
	<button id="submit-button"><?php echo $button_label ?></button>
</form>

<script src="https://checkout.stripe.com/v2/checkout.js"></script>