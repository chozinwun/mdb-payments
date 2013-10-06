<?php
	
	/*
	Plugin Name: MDB Payments
	Plugin URI: http://marcusbattle.com
	Description: Payments Post Type for Non-profits
	Version: 1.0
	Author: Marcus Battle
	Author URI: http://marcusbattle.com
	License: DO NOT STEAL
	*/

	function mdb_payments_init() {

		$labels = array(
			'name'               => _x( 'Payments', 'post type general name' ),
			'singular_name'      => _x( 'Payment', 'post type singular name' ),
			'add_new'            => _x( 'Add Payment', 'Payment' ),
			'add_new_item'       => __( 'Add New Payment' ),
			'edit_item'          => __( 'Edit Payment' ),
			'new_item'           => __( 'New Payment' ),
			'all_items'          => __( 'All Payments' ),
			'view_item'          => __( 'View Payment' ),
			'search_items'       => __( 'Search Payments' ),
			'not_found'          => __( 'No Payments found' ),
			'not_found_in_trash' => __( 'No Payments found in the Trash' ), 
			'parent_item_colon'  => '',
			'menu_name'          => 'Payments',
			'can_export'			=> true
		);
		
		$args = array(
			'labels'        => $labels,
			'description'   => 'Holds our apps and app specific data',
			'public'        => true,
			'menu_position' => 5,
			'supports'      => array( 'title' ),
			'has_archive'   => true,
			'show_in_nav_menus' => true,
			'rewrite' 			=> array( 'slug' => 'payments' ),
			'capability_type' => 'post',
			'publicly_queryable' => false
		);

		register_post_type( 'payment', $args );
	}

	function mdb_create_contribution_taxonomies() {
		// Add new taxonomy, make it hierarchical (like categories)
		$labels = array(
			'name'              => _x( 'Contributions', 'taxonomy general name' ),
			'singular_name'     => _x( 'Contribution', 'taxonomy singular name' ),
			'search_items'      => __( 'Search Contributions' ),
			'all_items'         => __( 'All Contributions' ),
			'parent_item'       => __( 'Parent Contribution' ),
			'parent_item_colon' => __( 'Parent Contribution:' ),
			'edit_item'         => __( 'Edit Contribution' ),
			'update_item'       => __( 'Update Contribution' ),
			'add_new_item'      => __( 'Add New Contribution' ),
			'new_item_name'     => __( 'New Contribution Name' ),
			'menu_name'         => __( 'Contributions' ),
		);

		$args = array(
			'hierarchical'      => true,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => array( 'slug' => 'contribution' ),
		);

		register_taxonomy( 'contribution', 'payment', $args );
	}

	function mdb_payments_meta_boxes() {
		add_meta_box( 'mdb-payment', 'Payment Information', 'mdb_payment_box', 'payment', 'normal', 'high' );
	}

	function mdb_payment_box( $post ) {

		$amount = get_post_meta( $post->ID, '_amount', true );

		if ( $amount ) echo "Amount Paid: <input type=\"text\" name=\"amount\" value=\"$$amount\" disabled=\"true\" />";
		else echo "Amount: <input type=\"text\" name=\"amount\" value=\"\" />";
	}

	function mdb_save_payment( $post_id ) {

		if ( isset($_REQUEST['amount']) ) {
			update_post_meta( $post_id, '_amount', $_REQUEST['amount'] );
		}

	}

	// Action hooks for mochila api
	function mdb_get_payments( $mochila ) {

		if ( isset($mochila->uri[2]) && ($mochila->uri[2] == 'contributions') ) {
			mdb_get_contributions( $mochila );
			exit;
		}

		$args['post_type'] = 'payment';
		$query = new WP_Query( $args );

		$payments = array();

		foreach( $query->posts as $post ) {
			
			$contributions = array();

			$payment['description'] = $post->post_title;
			$payment['amount'] = get_post_meta( $post->ID, '_amount', true );
			$payment['user_id'] = $mochila->user_id;

			foreach ( wp_get_post_terms( $post->ID, 'contribution' ) as $contribution ) {
				array_push( $contributions, $contribution->name );
			}

			$payment['contribution_types'] = $contributions;

			array_push( $payments, $payment );
		}

		$mochila->print_json( array( $mochila->plural => $payments, 'count' => count($payments) ) );

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

	add_action( 'init', 'mdb_payments_init' );
	add_action( 'init', 'mdb_create_contribution_taxonomies', 0 );
	add_action( 'add_meta_boxes', 'mdb_payments_meta_boxes' );
	add_action( 'save_post', 'mdb_save_payment' );
	add_action( 'mochila_get', 'mdb_get_payments', 10, 1 );
?>