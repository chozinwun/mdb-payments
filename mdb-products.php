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
			'capability_type' => 'post',
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
			payment_type varchar(24) NOT NULL,
			card_type varchar(24) NOT NULL,
			last4 int(4) NOT NULL,
			created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
			UNIQUE KEY id (id)
		);";
		
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta($sql);
	}

	function mdb_products_admin_menu() {
		
		add_submenu_page( 'edit.php?post_type=product', 'Products Settings', 'Settings', 'manage_options', 'mdb-product-settings', 'mdb_page_product_settings' );

	}

	function mdb_product_load_scripts() {

		wp_register_script( 'mdb-product-buynow-js', plugins_url( '/assets/js/product.buynow.js', __FILE__ ), array('jquery'), false, true );
		wp_enqueue_script( 'mdb-product-buynow-js' );

	}

	function mdb_page_product_settings() {
		include( plugin_dir_path( __FILE__ ) . 'templates/admin-product-settings.php' );
	}

	function mdb_products_meta_boxes() {
		add_meta_box( 'mdb-product', 'Product Information', 'mdb_product_box', 'product', 'normal', 'high' );
	}

	function mdb_product_box( $post ) {

		$amount_type = get_post_meta( $post->ID, 'amount_type', true );
		$amount = get_post_meta( $post->ID, 'amount', true );
		$button_label = get_post_meta( $post->ID, 'button_label', true );
		$short_description = get_post_meta( $post->ID, 'short_description', true );

		echo "<p><strong>Amount Type</strong> $amount_type</p>";
		echo "<select name=\"amount_type\">";
		echo "<option>--</option>";
		echo "<option value=\"fixed\">Fixed</option>";
		echo "<option value=\"donation\">Donation</option>";
		echo "</select>";

		echo "<p><strong>Amount</strong></p>";
		echo "<input type=\"text\" name=\"amount\" value=\"$amount\" />";

		echo "<p><strong>Button Label</strong></p>";
		echo "<input type=\"text\" name=\"button_label\" value=\"$button_label\" />";

		echo "<p><strong>Short Description</strong></p>";
		echo "<input type=\"text\" name=\"short_description\" value=\"$short_description\" />";
	}

	function mdb_save_product( $post_id ) {

		if ( isset($_REQUEST['amount_type']) ) {
			update_post_meta( $post_id, 'amount_type', $_REQUEST['amount_type'] );
		}

		if ( isset($_REQUEST['amount']) ) {
			update_post_meta( $post_id, 'amount', $_REQUEST['amount'] );
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

	function mdb_post_payment( $stripe_key = '', $amount = 0 ) {

		global $wpdb, $post;
		$table_name = $wpdb->prefix . "mdb_payments";

		if ( isset( $_REQUEST['stripeToken']) ) {

			// Get cURL resource
			$curl = curl_init();
			$header[] = 'Content-type: application/x-www-form-urlencoded';
			$header[] = 'Authorization: Bearer ' . $stripe_key;

			// Set some options - we are passing in a useragent too here
			curl_setopt_array($curl, array(
			    CURLOPT_RETURNTRANSFER => 1,
			    CURLOPT_URL => 'https://api.stripe.com/v1/charges?card=' . $_REQUEST['stripeToken'] . '&amount=' . $amount * 100 . '&currency=usd' ,
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
					'amount' => $amount,
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

		if ( isset($mochila->uri[2]) && ($mochila->uri[2] == 'contributions') ) {
			mdb_get_contributions( $mochila );
			exit;
		}

		$args['post_type'] = 'product';
		$query = new WP_Query( $args );

		$products = array();

		foreach( $query->posts as $post ) {
			
			$contributions = array();

			$product['description'] = $post->post_title;
			$product['amount'] = get_post_meta( $post->ID, '_amount', true );
			$product['user_id'] = $mochila->user_id;

			foreach ( wp_get_post_terms( $post->ID, 'contribution' ) as $contribution ) {
				array_push( $contributions, $contribution->name );
			}

			$product['contribution_types'] = $contributions;

			array_push( $products, $product );
		}

		$mochila->print_json( array( $mochila->plural => $products, 'count' => count($products) ) );

	}

	function mdb_get_contributions( $mochila ) {

		$contribution_types = array();
		$contributions = get_terms( 'contribution', array(
		 	'hide_empty' => 0
		) );

		foreach ( $contributions as $contribution ) {
			array_push( $contribution_types, $contribution->name );
		}

		$mochila->print_json( array( 'contribution_types' => $contribution_types, 'count' => count($contributions) ) );
	}

	add_action( 'init', 'mdb_products_init' );
	add_action( 'admin_menu', 'mdb_products_admin_menu' );
	add_action( 'wp_enqueue_scripts', 'mdb_product_load_scripts');

	add_action( 'add_meta_boxes', 'mdb_products_meta_boxes' );
	add_action( 'save_post', 'mdb_save_product' );
	add_filter( 'the_content', 'mdb_filter_product_content' );

	add_action( 'mochila_get', 'mdb_get_products', 10, 1 );
	
?>