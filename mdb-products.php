<?php
	
	/*
	Plugin Name: MDB Products
	Plugin URI: http://marcusbattle.com
	Description: Products Post Type
	Version: 1.0
	Author: Marcus Battle
	Author URI: http://marcusbattle.com
	License: DO NOT STEAL
	*/

	function mdb_products_init() {

		$labels = array(
			'name'               => _x( 'Products', 'post type general name' ),
			'singular_name'      => _x( 'Product', 'post type singular name' ),
			'add_new'            => _x( 'Add Product', 'Product' ),
			'add_new_item'       => __( 'Add New Product' ),
			'edit_item'          => __( 'Edit Product' ),
			'new_item'           => __( 'New Product' ),
			'all_items'          => __( 'All Products' ),
			'view_item'          => __( 'View Product' ),
			'search_items'       => __( 'Search Products' ),
			'not_found'          => __( 'No Products found' ),
			'not_found_in_trash' => __( 'No Products found in the Trash' ), 
			'parent_item_colon'  => '',
			'menu_name'          => 'Products',
			'can_export'			=> true
		);
		
		$args = array(
			'labels'        => $labels,
			'description'   => 'Holds our apps and app specific data',
			'public'        => true,
			'menu_position' => 5,
			'taxonomies' => array('category'),
			'supports'      => array( 'title', 'editor' ),
			'has_archive'   => true,
			'show_in_nav_menus' => true,
			'rewrite' 			=> array( 'slug' => 'products' ),
			'capability_type' => 'page',
			'publicly_queryable' => true
		);

		register_post_type( 'product', $args );

		add_option( 'mdb_product_stripe_secret_key', '', '', true );
		add_option( 'mdb_product_stripe_public_key', '', '', true );
		add_option( 'mdb_product_stripe_test_secret_key', '', '', true );
		add_option( 'mdb_product_stripe_test_public_key', '', '', true );

		global $wpdb;

		$table_name = $wpdb->prefix . "mdb_payments";

		$sql = "CREATE TABLE $table_name (
			id mediumint(11) NOT NULL AUTO_INCREMENT,
			token_id varchar(64) NOT NULL,
			name varchar(256) NOT NULL,
			amount float(11) NOT NULL,
			product_id mediumint(11) NOT NULL,
			user_id mediumint(11) NOT NULL,
			payment_type varchar(24) NOT NULL,
			card_type varchar(24) NOT NULL,
			email varchar(256) NOT NULL,
			last4 int(4) NOT NULL,
			created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
			UNIQUE KEY id (id)
		);";
		
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta($sql);
	}

	function mdb_products_admin_menu() {
		
		add_submenu_page( 'edit.php?post_type=product', 'Store Settings', 'Store Settings', 'manage_options', 'mdb-store-settings', 'mdb_page_store_settings' );

	}

	function mdb_product_load_scripts() {

		global $post;

		if ( $post->post_type == 'product' ) {
			
			wp_register_script( 'mdb-product-buynow-js', plugins_url( '/assets/js/product.buynow.js', __FILE__ ), array('jquery'), false, true );
			wp_enqueue_script( 'mdb-product-buynow-js' );

		}

	}

	function mdb_page_store_settings() {
		include( plugin_dir_path( __FILE__ ) . 'templates/admin-store-settings.php' );
	}

	function mdb_products_meta_boxes() {
		add_meta_box( 'mdb-product', 'Product Information', 'mdb_product_box', 'product', 'normal', 'high' );
	}

	function mdb_product_box( $post ) {

		$amount_type = get_post_meta( $post->ID, 'amount_type', true );
		$amount = get_post_meta( $post->ID, 'amount', true );
		$button_label = get_post_meta( $post->ID, 'button_label', true );
		$short_description = get_post_meta( $post->ID, 'short_description', true );
		$max_quantity = get_post_meta( $post->ID, 'max_quantity', true );
		$confirmation_message = get_post_meta( $post->ID, 'confirmation_message', true );
		$version = get_post_meta( $post->ID, 'version', true );
		$download_link = get_post_meta( $post->ID, 'download_link', true );

		echo "<p><strong>Amount Type</strong> $amount_type</p>";
		echo "<select name=\"amount_type\">";
		echo "<option>--</option>";
		echo "<option value=\"fixed\">Fixed</option>";
		echo "<option value=\"donation\">Donation</option>";
		echo "</select>";

		echo "<p><strong>Amount</strong></p>";
		echo "<input type=\"text\" name=\"amount\" value=\"$amount\" />";

		echo "<p><strong>Max Quantity</strong></p>";
		echo "<input type=\"number\" name=\"max_quantity\" value=\"$max_quantity\" />";

		echo "<p><strong>Button Label</strong></p>";
		echo "<input type=\"text\" name=\"button_label\" value=\"$button_label\" />";

		echo "<p><strong>Short Description</strong></p>";
		echo "<input type=\"text\" name=\"short_description\" value=\"$short_description\" />";

		echo "<p><strong>Payment Confirmation Message</strong></p>";
		echo "<textarea name=\"confirmation_message\" style=\"width: 100%;\">$confirmation_message</textarea>";

		echo "<p><strong>Version</strong></p>";
		echo "<input type=\"text\" name=\"version\" value=\"$version\" />";

		echo "<p><strong>Download Link</strong></p>";
		echo "<input type=\"text\" name=\"download_link\" value=\"$download_link\" />";

	}

	function mdb_save_product( $post_id ) {

		if ( isset($_REQUEST['amount_type']) ) {
			update_post_meta( $post_id, 'amount_type', $_REQUEST['amount_type'] );
		}

		if ( isset($_REQUEST['amount']) ) {
			update_post_meta( $post_id, 'amount', $_REQUEST['amount'] );
		}

		if ( isset($_REQUEST['max_quantity']) ) {
			update_post_meta( $post_id, 'max_quantity', $_REQUEST['max_quantity'] );
		}

		if ( isset($_REQUEST['button_type']) ) {
			update_post_meta( $post_id, 'amount', $_REQUEST['amount'] );
		}

		if ( isset($_REQUEST['button_label']) ) {
			update_post_meta( $post_id, 'button_label', $_REQUEST['button_label'] );
		}

		if ( isset($_REQUEST['short_description']) ) {
			update_post_meta( $post_id, 'short_description', $_REQUEST['short_description'] );
		}

		if ( isset($_REQUEST['confirmation_message']) ) {
			update_post_meta( $post_id, 'confirmation_message', $_REQUEST['confirmation_message'] );
		}

		if ( isset($_REQUEST['version']) ) {
			update_post_meta( $post_id, 'version', $_REQUEST['version'] );
		}

		if ( isset($_REQUEST['download_link']) ) {
			update_post_meta( $post_id, 'download_link', $_REQUEST['download_link'] );
		}

	}

	function mdb_filter_product_content( $content ) {

		global $post;

		if ( $post->post_type == 'product' ) {

			ob_start();
			include( plugin_dir_path( __FILE__ ) . 'templates/product-buy-now-stripe.php' );
			$content = $content . ob_get_clean();
			ob_flush();
			
			return $content;

		}

	}

	function mdb_post_payment() {

		global $wpdb, $post;
		$table_name = $wpdb->prefix . "mdb_payments";

		if ( $post->post_status == 'publish') {

			$stripe_secret_key = get_option('mdb_product_stripe_secret_key');

		} else {

			$stripe_secret_key = get_option('mdb_product_stripe_test_secret_key');

		}

		$amount = $_POST['total'] * 100;

		if ( isset( $_REQUEST['stripeToken']) ) {

			// Get cURL resource
			$curl = curl_init();
			$header[] = 'Content-type: application/x-www-form-urlencoded';
			$header[] = 'Authorization: Bearer ' . $stripe_secret_key;

			// Set some options - we are passing in a useragent too here
			curl_setopt_array($curl, array(
			    CURLOPT_RETURNTRANSFER => 1,
			    CURLOPT_URL => 'https://api.stripe.com/v1/charges?card=' . $_REQUEST['stripeToken'] . '&amount=' . $amount . '&currency=usd' ,
				CURLOPT_HTTPHEADER => $header,
			    CURLOPT_POST => 1,
			    CURLOPT_POSTFIELDS => array()
			));
			// Send the request & save response to $resp
			$resp = curl_exec($curl);
			// Close request to clear up some resources
			curl_close($curl);

			$stripe_response = json_decode( $resp );

			if ( isset($stripe_response->id) ) {

				$wpdb->insert( $table_name, array(
					'token_id' => $stripe_response->id,
					'name' => $stripe_response->card->name,
					'email' => $_POST['email'],
					'amount' => $_POST['total'],
					'product_id' => $post->ID,
					'payment_type' => 'debit/credit',
					'card_type' => $stripe_response->card->type,
					'last4' => $stripe_response->card->last4
				));

			}

			return $resp;

		}

	}

	// Action hooks for mochila api
	function mdb_get_products( $mochila ) {

		$args['post_type'] = 'product';
		
		if ( isset($mochila->uri[2]) ) {

			$args['tax_query'] = array(
				array(
					'taxonomy' => 'category',
					'field' => 'slug',
					'terms' => $mochila->uri[2]
				)
			);

			$mochila->plural = $mochila->uri[2];
		}

		$query = new WP_Query( $args );

		$products = array();

		foreach( $query->posts as $post ) {

			$product['id'] = $post->ID;
			$product['name'] = $post->post_title;
			$product['amount'] = get_post_meta( $post->ID, '_amount', true );

			array_push( $products, $product );
		}

		$mochila->print_json( array( $mochila->plural => $products, 'count' => count($products) ) );

	}

	function mdb_mochila_get_payments( $mochila ) {

		global $wpdb;

		$user = get_userdata( $mochila->user_id );
		$sql = "SELECT * FROM {$wpdb->prefix}mdb_payments WHERE email = {$user->data->user_email}";

	}

	function mdb_mochila_post_payments( $mochila ) {

		global $wpdb;

		$table_name = $wpdb->prefix . "mdb_payments";
		$user = get_userdata( $mochila->user_id );
		$stripe_secret_key = get_option('mdb_product_stripe_test_secret_key');

		$stripe_token = stripe_create_token();
		$stripe_charge = stripe_create_charge( $stripe_token );

		if ( isset($stripe_charge->id) ) {

			print_r( $_POST['products'] );

			foreach( $_POST['products'] as $product_id) {
			
				$wpdb->insert( $table_name, array(
					'token_id' => $stripe_token,
					'name' => $stripe_charge->card->name,
					'email' => $user->data->user_email,
					'amount' => $_POST['total'],
					'product_id' => 0,
					'payment_type' => 'debit/credit',
					'card_type' => $stripe_charge->card->type,
					'last4' => $stripe_charge->card->last4
				));

			}

			echo "you got paid";

		} else {

			echo "there was a problem";

		}

		exit;

	}

	function stripe_create_token() {

		$curl = curl_init();
		$header[] = 'Content-type: application/x-www-form-urlencoded';
		$header[] = 'Authorization: Bearer ' . get_option('mdb_product_stripe_test_secret_key');

		// Set some options - we are passing in a useragent too here
		curl_setopt_array($curl, array(
		    CURLOPT_RETURNTRANSFER => 1,
		    CURLOPT_URL => 'https://api.stripe.com/v1/tokens' ,
			CURLOPT_HTTPHEADER => $header,
		    CURLOPT_POST => 1,
		    CURLOPT_POSTFIELDS => 'card[number]=' . $_POST['card_number'] . '&card[exp_month]=' . $_POST['exp_month'] . '&card[exp_year]=' . $_POST['exp_year'] . '&card[cvc]=' . $_POST['cvc']
		));
		// Send the request & save response to $resp
		$resp = curl_exec($curl);
		// Close request to clear up some resources
		curl_close($curl);

		$stripe_response = json_decode( $resp );

		if ( isset($stripe_response->id) ) return $stripe_response->id;
		else return false;

	}

	function stripe_create_charge( $stripe_token ) {
		
		if ( $stripe_token ) {

			$amount = $_POST['total'] * 100;

			// Get cURL resource
			$curl = curl_init();
			$header[] = 'Content-type: application/x-www-form-urlencoded';
			$header[] = 'Authorization: Bearer ' . get_option('mdb_product_stripe_test_secret_key');

			// Set some options - we are passing in a useragent too here
			curl_setopt_array($curl, array(
			    CURLOPT_RETURNTRANSFER => 1,
			    CURLOPT_URL => 'https://api.stripe.com/v1/charges?card=' . $stripe_token . '&amount=' . $amount . '&currency=usd' ,
				CURLOPT_HTTPHEADER => $header,
			    CURLOPT_POST => 1,
			    CURLOPT_POSTFIELDS => array()
			));
			// Send the request & save response to $resp
			$resp = curl_exec($curl);
			// Close request to clear up some resources
			curl_close($curl);

			return json_decode( $resp );

		}

	}

	add_action( 'init', 'mdb_products_init' );
	add_action( 'admin_menu', 'mdb_products_admin_menu' );
	add_action( 'wp_enqueue_scripts', 'mdb_product_load_scripts');

	add_action( 'add_meta_boxes', 'mdb_products_meta_boxes' );
	add_action( 'save_post', 'mdb_save_product' );
	add_filter( 'the_content', 'mdb_filter_product_content' );

	add_action( 'mochila_get_products', 'mdb_get_products', 10, 1 );
	add_action( 'mochila_get_payments', 'mdb_mochila_get_payments', 10, 1 );
	add_action( 'mochila_post_payments', 'mdb_mochila_post_payments', 10, 1 );
?>