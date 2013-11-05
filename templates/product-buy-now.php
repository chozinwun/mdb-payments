<?php
	$amount_type = get_post_meta( $post->ID, 'amount_type', true);
	$amount = get_post_meta( $post->ID, 'amount', true);
	$button_label = get_post_meta( $post->ID, 'button_label', true );

	$button_label = ($button_label) ? $button_label : 'Buy Now';


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
?>


<form>
	<?php if( $amount_type == 'donation' ): ?>

		<label>Amount: </label>
		<input type="text" name="amount" />

	<?php elseif ( $amount_type == 'fixed' ): ?>

		<label>Amount: $<?php echo $amount ?></label>
		<input type="hidden" name="amount" value="<?php echo $amount ?>" />

	<?php endif; ?>

	<input type="submit" value="<?php echo $button_label ?>" />
</form>
