<?php

/*
Plugin Name: The Garuda Express
Plugin URI:
Description: Logistic Company From Nepal
Version: 1.0
Author: TGE
Author URI: https://www.thegarudaexpress.com/
License: A "Slug" license name e.g. GPL2
*/


defined( 'ABSPATH' ) or die( 'Wait, What??' );

if ( ! class_exists( 'TGE' ) ):

	class TGE {

		public function __construct() {
			if ( in_array( 'woocommerce/woocommerce.php',
				apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
				add_action( 'admin_menu', array( &$this, 'tge_menu' ), 8 );
			}

			if ( get_option( 'tge_key' ) != '' ) {

				add_action( 'init', array( &$this, 'register_tge_order_status' ) );

				add_action( 'wp_ajax_tge_create_order', array( &$this, 'create_tge_order_callback' ) );

				add_filter( 'wc_order_statuses', array( &$this, 'add_order_status_to_tge' ) );
			}


		}

		function tge_menu() {
			if ( get_option( 'tge_key' ) == '' ) {
				add_menu_page( 'TGE', 'TGE', 'manage_options', 'tge_setup', '',
					plugins_url( '/assets/images/icon.png', __FILE__ ), 56.6 );
				add_submenu_page( 'tge_setup', __( 'Setup', '' ), __( 'Setup', '' ), 'manage_options',
					'tge_setup', array( &$this, 'tge_setup' ) );
			} else {
				add_menu_page( 'TGE', 'TGE', 'manage_options', 'tge_dashboard', '',
					plugins_url( 'assets/images/icon.png', __FILE__ ), 56.6 );

				add_submenu_page( 'tge_dashboard', __( 'Dashboard', '' ), __( 'Dashboard', '' ),
					'manage_options',
					'tge_dashboard', array( &$this, 'tge_dashboard' ) );

				add_submenu_page( 'tge_dashboard', __( 'Place Order', '' ), __( 'Place Order', '' ),
					'manage_options',
					'tge_create_order', array( &$this, 'tge_create_order' ) );

				add_submenu_page( 'tge_dashboard', __( 'My Order', '' ), __( 'My Order', '' ),
					'manage_options',
					'tge_my_order', array( &$this, 'tge_my_order' ) );

				add_submenu_page( 'tge_dashboard', __( 'Dispatch List', '' ), __( 'Dispatch List', '' ),
					'manage_options',
					'tge_dispatch_order', array( &$this, 'tge_dispatch_order' ) );

				add_submenu_page( 'tge_dashboard', __( 'Log Out', '' ), __( 'Log Out', '' ), 'manage_options',
					'reset', array( &$this, 'logout' ) );
			}
		}


		function register_tge_order_status() {
			register_post_status( 'wc-ship-to-tge', array(
				'label'                     => 'Ship to Garuda',
				'exclude_from_search'       => false,
				'public'                    => true,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				'label_count'               => _n_noop( 'Ship to Garuda (%s)',
					'Ship to Garuda (%s)' )
			) );

			register_post_status( 'wc-dispatch-to-tge', array(
				'label'                     => 'Dispatch to Garuda',
				'exclude_from_search'       => false,
				'public'                    => true,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				'label_count'               => _n_noop( 'Dispatch to Garuda (%s)',
					'Dispatch to Garuda (%s)' )
			) );


		}

		function add_order_status_to_tge( $order_statuses ) {
			$new_order_statuses = array();
			foreach ( $order_statuses as $key => $status ) {
				$new_order_statuses[ $key ] = $status;
				if ( 'wc-processing' === $key ) {
					$new_order_statuses['wc-ship-to-tge']     = 'Ship to Garuda';
					$new_order_statuses['wc-dispatch-to-tge'] = 'Dispatch to Garuda';
				}
			}

			return $new_order_statuses;
		}

		function create_tge_order_callback() {
			require_once( 'pages/config.php' );
			$order_id_list = $_POST['order_id'];
			$success       = 0;
			foreach ( $order_id_list as $order ) {
				$order         = sanitize_text_field( $order );
				$order_details = wc_get_order( $order );
				$price         = 0;
				if ( $order_details->get_payment_method() == 'cod' ) {
					$price = $order_details->get_total();
				}
				$name       = $order_details->get_formatted_billing_full_name();
				$phone      = $order_details->get_billing_phone();
				$address    = $order_details->get_shipping_address_1();
				$email      = $order_details->get_billing_email();
				$total_item = $order_details->get_item_count();

				$receiver_address = array(
					'address' => array(
						'name'    => $name,
						'email'   => $email,
						'mobile'  => $phone,
						'address' => $address
					)
				);

				$headers = array(
					'Content-Type' => 'application/json',
					'Accept'       => 'application/json',
					'apikey'       => $token,
				);

				$arg = array(
					'headers' => $headers,
					'body'    => json_encode( $receiver_address )
				);

				// Step 1: Create Receiver Address
				$res    = wp_remote_post( $create_address_url, $arg );
				$output = wp_remote_retrieve_body( $res );
				$output = json_decode( $output, true );

				$pickup_date = date_i18n('c', false, true);

				if ( $output['status'] == 'success' ) {
					$success       = 1;
					$order_details = array(
						'order' => array(
							'pickupAddress'      => $output['data']['id'],
							'deliveryAddress'    => $output['data']['id'],
							'packageWeight'      => 1,
							'packageTotalItems'  => $total_item,
							'packageOrderAmount' => $price,
							'pickupDate'         => $pickup_date,
							'note'               => ''
						)
					);

					$headers = array(
						'Content-Type' => 'application/json',
						'Accept'       => 'application/json',
						'apikey'       => $token,
					);

					$arg = array(
						'headers' => $headers,
						'body'    => json_encode( $order_details )
					);

					// Step 1: Create Order
					$res  = wp_remote_post( $create_order_url, $arg );
					$data = wp_remote_retrieve_body( $res );
					$data = json_decode( $data, true );
					if ( $data['status'] == 'success' ) {
						$success = 2;
						$orders  = wc_get_order( $order );
						if ( ! empty( $orders ) ) {
							$orders->update_status( 'wc-dispatch-to-tge' );
							$note = __( "Order Dispatch to  Garuda Express" );
							$orders->add_order_note( $note, true );
						}
					}
				}
			}
			wp_send_json( json_encode( $success ) );
			wp_die();
		}

		function style_order_status() {
			global $pagenow, $post;
			if ( $pagenow != 'edit.php' && get_post_type( $post->ID ) != 'shop_order' ) {
				return;
			}
			?>
            <style>
                .order-status.wc-dispatch-to-tge {
                    background: #decaff;
                    color: #223F8B;
                }

                .order-status.status-ship-to-tge {
                    background: #ffddce;
                    color: #DB1D3D;
                }
            </style>
			<?php
		}


		function tge_setup() {
			include( 'pages/setup.php' );
		}

		function tge_dashboard() {
			include( 'pages/dashboard.php' );
		}

		function tge_create_order() {
			include( 'pages/order-create.php' );
		}

		function tge_dispatch_order() {
			include( 'pages/order-dispatch.php' );
		}

		function tge_my_order() {
			include( 'pages/my-order.php' );
		}

		function logout() {
			include( 'pages/reset.php' );
		}
	}

	$GLOBALS['tge'] = new TGE();
endif;