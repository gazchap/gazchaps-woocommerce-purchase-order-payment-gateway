<?php
/*
 * Plugin Name: GazChap's WooCommerce Purchase Order Payment Gateway
 * Plugin URI: https://www.gazchap.com/posts/woocommerce-purchase-order-payment-gateway
 * Version: 3.0
 * Author: Gareth 'GazChap' Griffiths
 * Author URI: https://www.gazchap.com
 * Description: Adds a Purchase Order payment method to WooCommerce.
 * Tested up to: 6.2
 * WC requires at least: 3.0.0
 * WC tested up to: 7.4.0
 * Text Domain: gazchaps-woocommerce-purchase-order-payment-gateway
 * Domain Path: /lang
 * License: GNU General Public License v2.0
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Donate link: https://paypal.me/gazchap
 */

	if ( !defined( 'ABSPATH' ) ) {
		exit;
	}

	define('GC_WC_POPG_DIR', dirname(__FILE__) . DIRECTORY_SEPARATOR );
	define('GC_WC_POPG_URL', plugin_dir_url( __FILE__ ) );
	define('GC_WC_POPG_VERSION', '3.0' );
	define('GC_WC_POPG_GATEWAY_ID', 'gazchap_wc_purchaseordergateway');

	class GC_WC_POPG {

		function __construct() {
			add_action( 'plugins_loaded', array( $this, 'load_languages' ) );
			add_action( 'plugins_loaded', array( $this, 'load_class' ), 15 );

			add_action( 'admin_init', array( $this, 'init_plugin' ) );
			add_filter( 'woocommerce_payment_gateways', array( $this, 'register_payment_gateway' ) );
			add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'add_settings_link' ) );

			add_action( 'rest_api_init', array( $this, 'rest_api_init' ), 10 );
		}

		function load_class() {
			require GC_WC_POPG_DIR . 'class.gateway.php';

			if ( is_admin() ) {
				require GC_WC_POPG_DIR . 'class.admin.php';
			}
		}

		function load_languages() {
			load_plugin_textdomain( 'gazchaps-woocommerce-purchase-order-payment-gateway', false, dirname( plugin_basename( __FILE__ ) ) . '/lang' );
		}

		/**
		 * Check if WooCommerce is active - if not, then deactivate this plugin and show a suitable error message
		 */
		function init_plugin(){
		    if ( is_admin() ) {
		        if ( !class_exists( 'WooCommerce' ) ) {
		            add_action( 'admin_notices', array( $this, 'woocommerce_deactivated_notice' ) );
		            deactivate_plugins( plugin_basename( __FILE__ ) );
		        }
		    }
		}

		function woocommerce_deactivated_notice() {
		    ?>
		    <div class="notice notice-error"><p><?php esc_html_e( 'GazChap\'s WooCommerce Purchase Order Gateway requires WooCommerce to be installed and activated.', 'gazchaps-woocommerce-purchase-order-payment-gateway' ) ?></p></div>
		    <?php
		}

		function register_payment_gateway( $payment_gateways ) {
			$payment_gateways[] = 'GazChap_WC_PurchaseOrder_Gateway';
			return $payment_gateways;
		}

		function add_settings_link( $links ) {
			if ( !is_array( $links ) ) {
				$links = array();
			}
			$links[] = '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=gazchap_wc_purchaseorder_gateway' ) . '">' . __( 'Settings', 'gazchaps-woocommerce-purchase-order-payment-gateway' ) . '</a>';
			return $links;
		}

		function rest_api_init() {
			$payment_gateway = WC()->payment_gateways->payment_gateways()[GC_WC_POPG_GATEWAY_ID];
			if ( $payment_gateway->add_po_number_to_rest_api ) {
				register_rest_field( 'shop_order', 'gazchap_purchase_order_number', array(
					'get_callback' => function( $order ) {
						if ( empty( $order['id'] ) || empty( $order['payment_method'] ) || $order['payment_method'] != GC_WC_POPG_GATEWAY_ID ) return null;

						$po_data = maybe_unserialize( get_post_meta( $order['id'], '_gazchap_purchase_order', true ) );
						if ( !empty( $po_data['number'] ) ) {
							return $po_data['number'];
						}
						return null;
					},
				) );
			}

			if ( $payment_gateway->add_address_to_rest_api ) {
				register_rest_field( 'shop_order', 'gazchap_purchase_order_address', array(
					'get_callback' => function( $order ) {
						if ( empty( $order['id'] ) || empty( $order['payment_method'] ) || $order['payment_method'] != GC_WC_POPG_GATEWAY_ID ) return null;

						$po_data = maybe_unserialize( get_post_meta( $order['id'], '_gazchap_purchase_order', true ) );
						$return = [];
						$fields = array( 'contact', 'company', 'address1', 'address2', 'city', 'county', 'postcode' );
						foreach( $fields as $field ) {
							if ( !empty( $po_data[$field] ) ) {
								$return[ $field ] = $po_data[$field];
							} else {
								$return[ $field ] = null;
							}
						}
						return $return;
					},
				) );
			}
		}
	}

	new GC_WC_POPG();
