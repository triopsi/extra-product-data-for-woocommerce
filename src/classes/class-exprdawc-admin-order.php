<?php
/**
 * Created on Mon Nov 25 2024
 *
 * Copyright (c) 2024 IT-Dienstleistungen Drevermann - All Rights Reserved
 *
 * @package Extra Product Data for WooCommerce
 * @author Daniel Drevermann <info@triopsi.com>
 * @copyright Copyright (c) 2024, IT-Dienstleistungen Drevermann
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 *
 * This file is part of the development of WordPress plugins.
 */

namespace Triopsi\Exprdawc;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Automattic\WooCommerce\Utilities\OrderUtil;
use WC_Order;
use WC_Order_Item;
use WC_Product;

/**
 * Class Exprdawc_Admin_Order
 *
 * This class is responsible for the admin order page.
 *
 * @package Exprdawc
 */
class Exprdawc_Admin_Order extends Exprdawc_Base_Order_Class {

	/**
	 * Order Obejct
	 *
	 * @var WC_Order
	 */
	protected static $order;

	/**
	 * Setup Admin class.
	 */
	public function __construct() {
		// Save order object to use in 'display_edit_button'.
		add_action( 'woocommerce_admin_order_item_headers', array( $this, 'set_order' ) );

		// Display "Configure/Edit" button next to configurable add-ons container items in the edit-order screen.
		add_action( 'woocommerce_after_order_itemmeta', array( $this, 'display_edit_button' ), 10, 3 );

		// Enqueue JS.
		add_action( 'admin_enqueue_scripts', array( $this, 'js_meta_boxes_enqueue' ), 100 );

		// Add JS template.
		add_action( 'admin_footer', array( $this, 'add_js_template' ) );

		// AJAX action for configuring addon order item.
		add_action( 'wp_ajax_woocommerce_configure_exprdawc_order_item', array( $this, 'woocommerce_configure_exprdawc_order_item' ) );

		// Ajax handler used to store updated order item.
		add_action( 'wp_ajax_woocommerce_edit_exprdawc_order_item', array( $this, 'ajax_edit_exprdawc_order_item' ) );
	}

	/**
	 * Save order object to use in 'display_edit_button'.
	 *
	 * Although the order object can be retrieved via 'WC_Order_Item::get_order', we've seen a significant performance hit when using that method.
	 *
	 * @param  WC_Order $order
	 */
	public static function set_order( $order ) {
		self::$order = $order;
	}

	/**
	 * Display "Configure/Edit" button next to configurable addons in the edit-order screen.
	 *
	 * @param  int           $item_id
	 * @param  WC_Order_Item $item
	 * @param  WC_Product    $product
	 * @return void
	 */
	public function display_edit_button( $item_id, $item, $product ) {
		$max_order_status = get_option( 'extra_product_data_max_order_status', 'processing' );

		if ( ! ( self::$order && 'line_item' === $item->get_type() ) ) {
			return;
		}

		if( ! self::$order->has_status( OrderUtil::remove_status_prefix( $max_order_status ) )){
			return;
		}

		if ( ! is_a( $product, 'WC_Product' ) ) {
			return;
		}

		// Get the product data.
		if ( $product->is_type( 'variation' ) ) {
			$product = wc_get_product( $product->get_parent_id() );
		}
		$custom_fields = $product->get_meta( '_extra_product_fields', true );

		if ( empty( $custom_fields ) ) {
			return;
		}

		// Display "Configure/Edit" button next to configurable addons in the edit-order screen.
		include EXPRDAWC_ADMIN_TEMPLATES_PATH . 'html-admin-order-edit-overview.php';
	}

	/**
	 * Enqueue JS.
	 */
	public function js_meta_boxes_enqueue() {
		if ( ! $this->is_current_screen( array( 'product' ) ) ) {
			wp_enqueue_script( 'woocommerce_exprdawc-admin-order-panel', EXPRDAWC_ASSETS_JS . 'wc-meta-boxes-order.js', array( 'wc-admin-order-meta-boxes', 'jquery-ui-datepicker', 'jquery' ), '1.0.0', true );
			wp_localize_script(
				'woocommerce_exprdawc-admin-order-panel',
				'wc_exprdawc_admin_order_params',
				array(
					'edit_exprdawc_nonce' => wp_create_nonce( 'wc_exprdawc_edit_exprdawc' ),
					'i18n_configure'      => __( 'Configure', 'extra-product-data-for-woocommerce' ),
					'i18n_edit'           => __( 'Edit', 'extra-product-data-for-woocommerce' ),
					'i18n_form_error'     => __( 'Failed to initialize form. If this issue persists, please reload the page and try again.', 'extra-product-data-for-woocommerce' ),
				)
			);
		}
	}

	/**
	 * Add JS template.
	 */
	public function add_js_template() {

		if ( wp_script_is( 'woocommerce_exprdawc-admin-order-panel' ) ) {
			// Add JS template.
			include EXPRDAWC_ADMIN_TEMPLATES_PATH . 'html-admin-order-edit-overview-js.php';
		}
	}

	/**
	 * Check if the current screen is one of the given screens.
	 *
	 * @param  string|array $screen
	 * @return bool
	 */
	public function is_current_screen( $screen ) {
		$screen         = (array) $screen;
		$current_screen = get_current_screen();
		return in_array( $current_screen->id, $screen, true );
	}

	/**
	 * AJAX action for configuring addon order item.
	 */
	public function woocommerce_configure_exprdawc_order_item() {

		// Check permissions and nonce.
		check_ajax_referer( 'wc_exprdawc_edit_exprdawc', 'security' );

		// Get the necessary parameters.
		$item_id  = isset( $_POST['item_id'] ) ? intval( $_POST['item_id'] ) : 0;
		$order_id = isset( $_POST['order_id'] ) ? intval( $_POST['order_id'] ) : 0;

		// Check if the parameters are valid.
		if ( ! $item_id || ! $order_id ) {
			wp_send_json_error( array( 'message' => __( 'Invalid order or item ID.', 'extra-product-data-for-woocommerce' ) ) );
		}

		// Get the order and item.
		$order = wc_get_order( $order_id );
		$item  = $order ? $order->get_item( $item_id ) : false;

		if ( ! $order || ! $item ) {
			wp_send_json_error( array( 'message' => __( 'Order or item not found.', 'extra-product-data-for-woocommerce' ) ) );
		}

		// Get the product data.
		/** @disregard */
		$product = $item->get_product();
		if ( $product->is_type( 'variation' ) ) {
			$product = wc_get_product( $product->get_parent_id() );
		}
		$custom_fields = $product->get_meta( '_extra_product_fields', true );

		if ( empty( $custom_fields ) ) {
			wp_send_json_error( array( 'message' => __( 'No extra product data found.', 'extra-product-data-for-woocommerce' ) ) );
		}

		// Get Values of the custom fields.
		$all_user_inputs = array();
		$item_meta_data  = $item->get_meta_data();

		// Loop through item meta data and store in mail template data array.
		foreach ( $item_meta_data as $meta ) {
			if ( isset( $meta->key ) && ! empty( $meta->value ) ) {
				$all_user_inputs[ $meta->key ] = $meta;
			}
		}
		$extra_user_data = array();
		foreach ( $custom_fields as $index => $input_field_array ) {
			$label_id                     = strtolower( str_replace( ' ', '_', $input_field_array['label'] ) );
			$extra_user_data[ $label_id ] = isset( $all_user_inputs[ $input_field_array['label'] ] ) ? $all_user_inputs[ $input_field_array['label'] ]->value : '';
		}

		// Generate the HTML for the form.
		ob_start();
		foreach ( $custom_fields as $index => $field ) {
			$value = isset( $extra_user_data[ strtolower( str_replace( ' ', '_', $field['label'] ) ) ] ) ? $extra_user_data[ strtolower( str_replace( ' ', '_', $field['label'] ) ) ] : '';
			Exprdawc_Helper::generate_input_field( $field, $value, true );
		}
		$html = ob_get_clean();

		// Send the response back.
		wp_send_json_success( array( 'html' => $html ) );
	}

	/**
	 * Ajax handler used to store updated order item.
	 */
	public function ajax_edit_exprdawc_order_item() {
		// Check permissions and nonce.
		check_ajax_referer( 'wc_exprdawc_edit_exprdawc', 'security' );

		// Process Data Save
		$order_id = $this->process_save_order();

		// Generate the updated HTML for the order items and notes.
		ob_start();
		$order = wc_get_order( $order_id );
		/** @disregard */
		include WC_ABSPATH . 'includes/admin/meta-boxes/views/html-order-items.php';
		$html = ob_get_clean();

		ob_start();
		$notes = wc_get_order_notes( array( 'order_id' => $order_id ) );
		/** @disregard */
		include WC_ABSPATH . 'includes/admin/meta-boxes/views/html-order-notes.php';
		$notes_html = ob_get_clean();

		wp_send_json_success(
			array(
				'message'    => __( 'Item updated successfully.', 'extra-product-data-for-woocommerce' ),
				'html'       => $html,
				'notes_html' => $notes_html,
			)
		);
	}
}
