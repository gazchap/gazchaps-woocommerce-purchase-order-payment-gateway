<?php
	if ( !defined( 'ABSPATH' ) ) {
		exit;
	}

	class GazChap_WC_PurchaseOrder_Gateway_Admin {

		public function __construct() {
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_js_css' ) );
			add_action( 'woocommerce_admin_order_data_after_billing_address', array( $this, 'display_fields' ), 10, 1 );
			add_action( 'woocommerce_admin_order_data_after_billing_address', array( $this, 'add_edit_mode_fields' ), 10, 1 );
			add_action( 'woocommerce_process_shop_order_meta', array( $this, 'save_to_order' ), 45, 1 );
		}

		public function enqueue_js_css() {
			wp_register_script( 'gazchap_purchase_order_admin', GC_WC_POPG_URL . 'admin.min.js', array( 'jquery' ), GC_WC_POPG_VERSION, true );
			wp_enqueue_script( 'gazchap_purchase_order_admin' );
			wp_register_style( 'gazchap_purchase_order_admin_css', GC_WC_POPG_URL . 'admin.min.css', array(), GC_WC_POPG_VERSION );
			wp_enqueue_style( 'gazchap_purchase_order_admin_css' );
		}

		/**
		 * @param WC_Order $order
		 *
		 * @return void
		 */
		function display_fields( $order ) {
			if ( $order->get_payment_method() !== 'gazchap_wc_purchaseordergateway' ) return;

			$meta = $order->get_meta( '_gazchap_purchase_order' );
			if ( !empty( $meta['number'] ) ) {
				?>
				<div id="gazchap_purchase_order_fields" class="address">
					<p><strong><?php esc_html_e('Purchase Order Number', 'gazchaps-woocommerce-purchase-order-payment-gateway' ); ?>:</strong> <?php if ( !empty( $meta['number'] ) ) echo esc_html( $meta['number'] );?></p>

					<?php if ( !empty( $meta['contact'] ) ): ?>
					<p>
						<strong><?php esc_html_e('Contact Name', 'gazchaps-woocommerce-purchase-order-payment-gateway'); ?>:</strong>
						<?php if ( !empty( $meta['contact'] ) ) echo esc_html( $meta['contact'] ); ?>
					</p>
					<?php endif; ?>

					<?php if ( !empty( $meta['company'] ) ): ?>
					<p>
						<strong><?php esc_html_e('Company/Organisation', 'gazchaps-woocommerce-purchase-order-payment-gateway'); ?>:</strong>
						<?php if ( !empty( $meta['company'] ) ) echo esc_html( $meta['company'] ); ?>
					</p>
					<?php endif; ?>

					<?php if ( !empty( $meta['address1'] ) || !empty( $meta['address2'] ) || !empty( $meta['city'] ) || !empty( $meta['county'] ) || !empty( $meta['postcode'] ) ): ?>
					<p>
						<strong><?php esc_html_e('Address', 'gazchaps-woocommerce-purchase-order-payment-gateway'); ?>:</strong>
						<?php if ( !empty( $meta['address1'] ) ) echo esc_html( $meta['address1'] ) . "<br>"; ?>
						<?php if ( !empty( $meta['address2'] ) ) echo esc_html( $meta['address2'] ) . "<br>"; ?>
						<?php if ( !empty( $meta['city'] ) ) echo esc_html( $meta['city'] ) . "<br>"; ?>
						<?php if ( !empty( $meta['county'] ) ) echo esc_html( $meta['county'] ) . "<br>"; ?>
						<?php if ( !empty( $meta['postcode'] ) ) echo esc_html( $meta['postcode'] ) . "<br>"; ?>
					</p>
					<?php endif; ?>
				</div>
				<?php
			}
		}

		/**
		 * @param WC_Order $order
		 *
		 * @return void
		 */
		public function add_edit_mode_fields( $order ) {
			$gateway = new GazChap_WC_PurchaseOrder_Gateway();
			if ( !$gateway->ask_po_number && !$gateway->ask_address ) return;

			$meta = $order->get_meta( '_gazchap_purchase_order' );
			echo '<div id="gazchap_purchase_order_edit_fields" class="edit_address">';
			if ( $gateway->ask_po_number ) {
				woocommerce_wp_text_input( array(
					'id' => '_gazchap_purchase_order_number',
					'value' => !empty( $meta['number'] ) ? $meta['number'] : '',
					'type' => 'text',
					'wrapper_class' => !$gateway->ask_address ? 'form-field-wide' : '',
					'label' => __( 'Purchase Order Number', 'gazchaps-woocommerce-purchase-order-payment-gateway' ),
					'custom_attributes' => $gateway->require_po_number ? array( 'required' => 'required' ) : null,
				) );
			}

			if ( $gateway->ask_address ) {
				$new_fields = array(
					'_gazchap_purchase_order_contact' => __( 'Contact Name', 'gazchaps-woocommerce-purchase-order-payment-gateway' ),
					'_gazchap_purchase_order_company' => __( 'Company/Organisation', 'gazchaps-woocommerce-purchase-order-payment-gateway' ),
					'_gazchap_purchase_order_address1' => __( 'Address (Line 1)', 'gazchaps-woocommerce-purchase-order-payment-gateway' ),
					'_gazchap_purchase_order_address2' => __( 'Address (Line 2)', 'gazchaps-woocommerce-purchase-order-payment-gateway' ),
					'_gazchap_purchase_order_city' => __( 'City', 'gazchaps-woocommerce-purchase-order-payment-gateway' ),
					'_gazchap_purchase_order_county' => __( 'County', 'gazchaps-woocommerce-purchase-order-payment-gateway' ),
					'_gazchap_purchase_order_postcode' => __( 'Postcode', 'gazchaps-woocommerce-purchase-order-payment-gateway' ),
				);
				foreach( $new_fields as $id => $label ) {
					$meta_key = str_replace( "_gazchap_purchase_order_", "", $id );
					woocommerce_wp_text_input( array(
						'id' => $id,
						'value' => !empty( $meta[$meta_key] ) ? $meta[$meta_key] : '',
						'type' => 'text',
						'label' => $label,
					) );
				}
			}
			echo '</div>';
		}

		public function save_to_order( $order_id ) {
			$order = wc_get_order( $order_id );
			if ( $order->get_payment_method() !== 'gazchap_wc_purchaseordergateway' ) return;

			$fields = array(
				'_gazchap_purchase_order_number',
				'_gazchap_purchase_order_contact',
				'_gazchap_purchase_order_company',
				'_gazchap_purchase_order_address1',
				'_gazchap_purchase_order_address2',
				'_gazchap_purchase_order_city',
				'_gazchap_purchase_order_county',
				'_gazchap_purchase_order_postcode',
			);

			$array_meta = $order->get_meta( '_gazchap_purchase_order' );
			if ( !$array_meta || !is_array( $array_meta ) ) $array_meta = array();
			foreach( $fields as $field ) {
				$array_key = str_replace( '_gazchap_purchase_order_', '', $field );
				$value = ( isset( $_POST[$field] ) ) ? sanitize_text_field( $_POST[$field] ) : '';
				if ( !empty( $value ) ) {
					$array_meta[$array_key] = $value;
					$order->update_meta_data( $field, $value );
				} else {
					unset( $array_meta[$array_key] );
					$order->delete_meta_data( $field );
				}
			}
			$order->update_meta_data( '_gazchap_purchase_order', $array_meta );
			$order->save_meta_data();
		}

	}

	new GazChap_WC_PurchaseOrder_Gateway_Admin();
