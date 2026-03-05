<?php
/**
 * Admin Order Handler
 *
 * @package ExtraProductDataForWooCommerce
 * @author Daniel Drevermann <info@triopsi.com>
 * @copyright Copyright (c) 2024, IT-Dienstleistungen Drevermann
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 */

declare(strict_types=1);

namespace Triopsi\Exprdawc\Orders\Admin;

use Automattic\WooCommerce\Utilities\OrderUtil;
use WC_Order;
use WC_Order_Item;
use WC_Product;
use Triopsi\Exprdawc\Helpers\Helper;
use Triopsi\Exprdawc\Contracts\Hookable;
use Triopsi\Exprdawc\Orders\BaseOrder;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin Order Handler
 *
 * Handles order management in WordPress admin.
 */
class AdminOrder extends BaseOrder implements Hookable {

	/**
	 * Order object.
	 *
	 * @var WC_Order
	 */
	protected static $order;

	/**
	 * Setup Admin class.
	 */
	public function __construct() {
		add_action( 'woocommerce_admin_order_item_headers', array( $this, 'set_order' ) );
		add_action( 'woocommerce_after_order_itemmeta', array( $this, 'display_edit_button' ), 10, 3 );
		add_action( 'admin_enqueue_scripts', array( $this, 'js_meta_boxes_enqueue' ), 100 );
		add_action( 'admin_footer', array( $this, 'add_js_template' ) );
		add_action( 'wp_ajax_woocommerce_configure_exprdawc_order_item', array( $this, 'exprdawc_load_edit_modal_form' ) );
		add_action( 'wp_ajax_woocommerce_edit_exprdawc_order_item', array( $this, 'exprdawc_save_edit_modal_form' ) );
	}

	/**
	 * Save order object to use in display button.
	 *
	 * @param WC_Order $order WC Order.
	 */
	public static function set_order( $order ) {
		self::$order = $order;
	}

	/**
	 * Display edit button.
	 *
	 * @param int           $item_id Item ID.
	 * @param WC_Order_Item $item Order item object.
	 * @param WC_Product    $product Product object.
	 * @return void
	 */
	public function display_edit_button( $item_id, $item, $product ) {
		$max_order_status = get_option( 'extra_product_data_max_order_status', 'processing' );

		if ( ! ( self::$order && 'line_item' === $item->get_type() ) ) {
			return;
		}

		if ( ! self::$order->has_status( OrderUtil::remove_status_prefix( $max_order_status ) ) ) {
			return;
		}

		if ( ! is_a( $product, 'WC_Product' ) ) {
			return;
		}

		if ( $product->is_type( 'variation' ) ) {
			$product = wc_get_product( $product->get_parent_id() );
		}
		$custom_fields = $product->get_meta( '_extra_product_fields', true );

		if ( empty( $custom_fields ) ) {
			return;
		}

		include EXPRDAWC_ADMIN_TEMPLATES_PATH . 'html-admin-order-edit-overview.php';
	}

	/**
	 * Enqueue JS.
	 */
	public function js_meta_boxes_enqueue() {
		if ( ! $this->is_current_screen( array( 'shop_order', 'edit-shop_order', 'woocommerce_page_wc-orders' ) ) ) {
			return;
		}
		wp_enqueue_script( 'woocommerce_exprdawc-admin-order-panel', EXPRDAWC_ASSETS_JS . 'wc-meta-boxes-order.min.js', array( 'wc-admin-order-meta-boxes', 'jquery-ui-datepicker', 'jquery' ), '1.0.0', true );
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

	/**
	 * Add JS template.
	 */
	public function add_js_template() {
		if ( wp_script_is( 'woocommerce_exprdawc-admin-order-panel' ) ) {
			include EXPRDAWC_ADMIN_TEMPLATES_PATH . 'html-admin-order-edit-overview-js.php';
		}
	}

	/**
	 * Check if current screen matches.
	 *
	 * @param string|array $screen Screen IDs.
	 * @return bool
	 */
	public function is_current_screen( $screen ) {
		$screen         = (array) $screen;
		$current_screen = get_current_screen();
		return in_array( $current_screen->id, $screen, true );
	}

	/**
	 * Load edit form modal.
	 *
	 * @return void
	 */
	public function exprdawc_load_edit_modal_form() {
		check_ajax_referer( 'wc_exprdawc_edit_exprdawc', 'security' );

		$item_id  = isset( $_POST['item_id'] ) ? intval( $_POST['item_id'] ) : 0;
		$order_id = isset( $_POST['order_id'] ) ? intval( $_POST['order_id'] ) : 0;

		if ( ! $item_id || ! $order_id ) {
			wp_send_json_error( array( 'message' => __( 'Invalid order or item ID.', 'extra-product-data-for-woocommerce' ) ) );
		}

		$order = wc_get_order( $order_id );
		$item  = $order ? $order->get_item( $item_id ) : false;

		if ( ! $order || ! $item ) {
			wp_send_json_error( array( 'message' => __( 'Order or item not found.', 'extra-product-data-for-woocommerce' ) ) );
		}

		$product = $item->get_product();
		if ( $product->is_type( 'variation' ) ) {
			$product = wc_get_product( $product->get_parent_id() );
		}
		$custom_fields = $product->get_meta( '_extra_product_fields', true );

		if ( empty( $custom_fields ) ) {
			wp_send_json_error( array( 'message' => __( 'No extra product data found.', 'extra-product-data-for-woocommerce' ) ) );
		}

		$all_user_inputs = array();
		$item_meta_data  = $item->get_meta_data();

		foreach ( $item_meta_data as $meta ) {
			if ( isset( $meta->key ) && ! empty( $meta->value ) ) {
				$all_user_inputs[ $meta->key ] = $meta;
			}
		}
		$post_data_product_item = array();
		foreach ( $custom_fields as $index => $input_field_array ) {
			$label_id                            = strtolower( str_replace( ' ', '_', $input_field_array['label'] ) );
			$post_data_product_item[ $label_id ] = isset( $all_user_inputs[ $input_field_array['label'] ] ) ? $all_user_inputs[ $input_field_array['label'] ]->value : '';
		}

		ob_start();
		foreach ( $custom_fields as $index => $field ) {
			$value = isset( $post_data_product_item[ strtolower( str_replace( ' ', '_', $field['label'] ) ) ] ) ? $post_data_product_item[ strtolower( str_replace( ' ', '_', $field['label'] ) ) ] : '';
			Helper::generateInputField( $field, $value, true );
		}
		$html = ob_get_clean();

		wp_send_json_success( array( 'html' => $html ) );
	}

	/**
	 * Save the edit via ajax request.
	 *
	 * @return void
	 */
	public function exprdawc_save_edit_modal_form() {
		check_ajax_referer( 'wc_exprdawc_edit_exprdawc', 'security' );

		$order_id = $this->process_save_order( true );

		ob_start();
		$order = wc_get_order( $order_id );
		include WC_ABSPATH . 'includes/admin/meta-boxes/views/html-order-items.php';
		$html = ob_get_clean();

		ob_start();
		$notes = wc_get_order_notes( array( 'order_id' => $order_id ) );
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

	/**
	 * Register WordPress hooks.
	 *
	 * @return void
	 */
	public function registerHooks(): void {
		// Hooks are registered in constructor.
	}
}
