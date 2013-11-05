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
	}

	function mdb_products_meta_boxes() {
		add_meta_box( 'mdb-product', 'Product Information', 'mdb_product_box', 'product', 'normal', 'high' );
	}

	function mdb_product_box( $post ) {

		$amount_type = get_post_meta( $post->ID, 'amount_type', true );
		$amount = get_post_meta( $post->ID, 'amount', true );

		echo "<p><strong>Amount Type</strong> $amount_type</p>";
		echo "<select name=\"amount_type\">";
		echo "<option>--</option>";
		echo "<option value=\"fixed\">Fixed</option>";
		echo "<option value=\"donation\">Donation</option>";
		echo "</select>";

		echo "<p><strong>Amount</strong></p>";
		echo "<input type=\"text\" name=\"amount\" value=\"$amount\" />";

	}

	function mdb_save_product( $post_id ) {

		if ( isset($_REQUEST['amount_type']) ) {
			update_post_meta( $post_id, 'amount_type', $_REQUEST['amount_type'] );
		}

		if ( isset($_REQUEST['amount']) ) {
			update_post_meta( $post_id, 'amount', $_REQUEST['amount'] );
		}

	}

	function mdb_filter_product_content( $content ) {

		global $post;

		if ( $post->post_type == 'product' ) {

			return $content . "<button>Buy Ticket</button>";

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
	add_action( 'add_meta_boxes', 'mdb_products_meta_boxes' );
	add_action( 'save_post', 'mdb_save_product' );
	add_action( 'mochila_get', 'mdb_get_products', 10, 1 );

	add_filter( 'the_content', 'mdb_filter_product_content' );
?>