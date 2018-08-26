<?php
	/*
		Plugin Name: GazChap's WooCommerce Purchase Order Payment Gateway
		Plugin URI: https://www.gazchap.com/posts/woocommerce-purchase-order-payment-gateway
		Description: Adds a Purchase Order payment method to WooCommerce.
		Author: Gareth 'GazChap' Griffiths
		Author URI: https://www.gazchap.com
		Text Domain: gazchaps-woocommerce-purchase-order-payment-gateway
		Domain Path: /lang
		Version: 1.1
		WooCommerce requires at least version: 3.0.0
		WooCommerce tested up to version: 3.4.4
		License: GNU General Public License v2.0
		License URI: http://www.gnu.org/licenses/gpl-2.0.html
	*/

	if ( !defined( 'ABSPATH' ) ) {
		exit;
	}

	define('GC_WC_POPG_DIR', dirname(__FILE__) . DIRECTORY_SEPARATOR );

	class GC_WC_POPG {

		function __construct() {
			add_action( 'plugins_loaded', array( $this, 'load_languages' ) );
			add_action( 'plugins_loaded', array( $this, 'load_class' ), 15 );

			add_action( 'admin_init', array( $this, 'init_plugin' ) );
			add_filter( 'woocommerce_payment_gateways', array( $this, 'register_payment_gateway' ) );
			add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'add_settings_link' ) );
		}

		function load_class() {
			require GC_WC_POPG_DIR . 'class.gateway.php';
		}

		function load_languages() {
			load_plugin_textdomain( 'gazchaps-woocommerce-purchase-order-payment-gateway', false, GC_WC_POPG_DIR . 'lang' . DIRECTORY_SEPARATOR );
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
		    <div class="notice notice-error"><p><?php esc_html_e( 'This plugin requires WooCommerce to be installed and activated.', 'gazchaps-woocommerce-purchase-order-payment-gateway' ) ?></p></div>
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
	}

	new GC_WC_POPG();
