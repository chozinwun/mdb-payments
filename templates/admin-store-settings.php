<?php

	if ( isset($_POST) && !empty($_POST) ) {
	
		$ssk = isset($_POST['mdb_product_stripe_secret_key']) ? $_POST['mdb_product_stripe_secret_key'] : '';
		update_option('mdb_product_stripe_secret_key',$_POST['mdb_product_stripe_secret_key']);
		update_option('mdb_product_stripe_public_key',$_POST['mdb_product_stripe_public_key']);
		update_option('mdb_product_stripe_test_secret_key',$_POST['mdb_product_stripe_test_secret_key']);
		update_option('mdb_product_stripe_test_public_key',$_POST['mdb_product_stripe_test_public_key']);

		echo "Page updated!";
	} 

	$stripe_secret_key = get_option('mdb_product_stripe_secret_key');
	$stripe_public_key = get_option('mdb_product_stripe_public_key');
	$stripe_test_secret_key = get_option('mdb_product_stripe_test_secret_key');
	$stripe_test_public_key = get_option('mdb_product_stripe_test_public_key');
?>

<div class="wrap">

	<h2>Store Settings</h2>

	<h3>Stripe Payment Details</h3>

	<form action="edit.php?post_type=product&page=mdb-product-settings" method="POST">
		<div class="form-field">
			<label>Stripe Secret Key</label>
			<input name="mdb_product_stripe_secret_key" type="text" value="<?php echo $stripe_secret_key ?>" size="40" />
		</div>
		<div class="form-field">
			<label>Stripe Public Key</label>
			<input name="mdb_product_stripe_public_key" type="text" value="<?php echo $stripe_public_key ?>" size="40" />
		</div>
		<div class="form-field">
			<label>Stripe Test Secret Key</label>
			<input name="mdb_product_stripe_test_secret_key" type="text" value="<?php echo $stripe_test_secret_key ?>" size="40" />
		</div>
		<div class="form-field">
			<label>Stripe Test Public Key</label>
			<input name="mdb_product_stripe_test_public_key" type="text" value="<?php echo $stripe_test_public_key ?>" size="40" />
		</div>

		<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Update Product Settings"></p>
	</form>
</div>