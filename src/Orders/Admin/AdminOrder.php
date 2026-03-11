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

use WC_Order;
use WC_Order_Item;
use WC_Order_Item_Product;
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
		add_action( 'woocommerce_admin_order_item_headers', array( $this, 'setOrder' ) );
		add_action( 'woocommerce_after_order_itemmeta', array( $this, 'displayEditButton' ), 10, 3 );
		add_action( 'admin_enqueue_scripts', array( $this, 'jsMetaBoxesEnqueue' ), 100 );
		add_action( 'admin_footer', array( $this, 'addJsTemplate' ) );
		add_action( 'wp_ajax_woocommerce_configure_exprdawc_order_item', array( $this, 'loadEditModalForm' ) );
		add_action( 'wp_ajax_woocommerce_edit_exprdawc_order_item', array( $this, 'saveEditModalForm' ) );
	}

	/**
	 * Save order object to use in display button.
	 *
	 * @param WC_Order $order WC Order.
	 */
	public static function setOrder( $order ) {
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
	public function displayEditButton( $item_id, $item, $product ) {

		if ( ! ( self::$order && 'line_item' === $item->get_type() ) ) {
			return;
		}

		if ( ! Helper::is_order_editable( self::$order ) ) {
			return;
		}

		if ( ! is_a( $product, 'WC_Product' ) ) {
			return;
		}

		if ( $product->is_type( 'variation' ) ) {
			$product = wc_get_product( $product->get_parent_id() );
		}
		$custom_fields = Helper::getExtraProductFields( $product );

		if ( empty( $custom_fields ) ) {
			return;
		}

		include EXPRDAWC_ADMIN_TEMPLATES_PATH . 'html-admin-order-edit-overview.php';
	}

	/**
	 * Enqueue JS.
	 */
	public function jsMetaBoxesEnqueue() {
		if ( ! $this->isCurrentScreen( array( 'shop_order', 'edit-shop_order', 'woocommerce_page_wc-orders' ) ) ) {
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
	public function addJsTemplate() {
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
	public function isCurrentScreen( $screen ) {
		$screen         = (array) $screen;
		$current_screen = get_current_screen();
		return in_array( $current_screen->id, $screen, true );
	}

	/**
	 * Load edit form modal.
	 *
	 * @return void
	 */
	public function loadEditModalForm() {
		check_ajax_referer( 'wc_exprdawc_edit_exprdawc', 'security' );

		$load_data        = $this->getOrderItemForAdminModalOrFail();
		$item             = $load_data['item'];
		$product          = $this->getAdminOrderItemProductOrFail( $item );
		$custom_fields    = $this->getCustomFieldsOrFail( $product );
		$stored_field_map = $this->extractOrderItemMetaData( $item->get_meta( EXPRDAWC_META_EXTRA_PRODUCT_DATA, true ) );
		$html             = $this->buildAdminModalFieldsHtml( $custom_fields, $stored_field_map );

		wp_send_json_success( array( 'html' => $html ) );
	}

	/**
	 * Save the edit via ajax request.
	 *
	 * @return void
	 */
	public function saveEditModalForm() {
		check_ajax_referer( 'wc_exprdawc_edit_exprdawc', 'security' );

		$order_id = $this->processSaveOrder( true );

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
	 * Resolve posted order and item for admin modal loading.
	 *
	 * Validates incoming AJAX payload (`order_id`, `item_id`) and ensures both
	 * entities exist and the item is a product line item.
	 *
	 * @return array{order: WC_Order, item: WC_Order_Item_Product}
	 */
	private function getOrderItemForAdminModalOrFail(): array {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce is verified in caller (loadEditModalForm).
		$item_id = isset( $_POST['item_id'] ) ? intval( $_POST['item_id'] ) : 0;
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce is verified in caller (loadEditModalForm).
		$order_id = isset( $_POST['order_id'] ) ? intval( $_POST['order_id'] ) : 0;

		if ( ! $item_id || ! $order_id ) {
			wp_send_json_error( array( 'message' => __( 'Invalid order or item ID.', 'extra-product-data-for-woocommerce' ) ) );
		}

		$order = wc_get_order( $order_id );
		$item  = $order ? $order->get_item( $item_id ) : false;

		if ( ! $order || ! $item ) {
			wp_send_json_error( array( 'message' => __( 'Order or item not found.', 'extra-product-data-for-woocommerce' ) ) );
		}

		if ( ! $item instanceof WC_Order_Item_Product ) {
			wp_send_json_error(
				array(
					'message' => __( 'Order item is not a product item.', 'extra-product-data-for-woocommerce' ),
				)
			);
		}

		return array(
			'order' => $order,
			'item'  => $item,
		);
	}

	/**
	 * Resolve product for an order item.
	 *
	 * Handles variation items by resolving the parent product.
	 *
	 * @param WC_Order_Item_Product $item Order item product.
	 * @return WC_Product
	 */
	private function getAdminOrderItemProductOrFail( WC_Order_Item_Product $item ): WC_Product {
		$product = $item->get_product();

		if ( ! $product instanceof WC_Product ) {
			wp_send_json_error( array( 'message' => __( 'Product not found.', 'extra-product-data-for-woocommerce' ) ) );
		}

		if ( $product->is_type( 'variation' ) ) {
			$product = wc_get_product( $product->get_parent_id() );
		}

		if ( ! $product instanceof WC_Product ) {
			wp_send_json_error( array( 'message' => __( 'Product not found.', 'extra-product-data-for-woocommerce' ) ) );
		}

		return $product;
	}

	/**
	 * Get extra product fields from the product or stop request.
	 *
	 * @param WC_Product $product Product object.
	 * @return array Field definitions.
	 */
	private function getCustomFieldsOrFail( WC_Product $product ): array {
		$custom_fields = Helper::getExtraProductFields( $product );

		if ( empty( $custom_fields ) ) {
			wp_send_json_error( array( 'message' => __( 'No extra product data found.', 'extra-product-data-for-woocommerce' ) ) );
		}

		return $custom_fields;
	}

	/**
	 * Render modal field HTML for admin order item edit.
	 *
	 * Uses shared field key resolution so lookup is consistent with save/update flow.
	 *
	 * @param array $custom_fields    Product field definitions.
	 * @param array $stored_field_map Stored field payloads indexed by resolved key.
	 * @return string
	 */
	private function buildAdminModalFieldsHtml( array $custom_fields, array $stored_field_map ): string {
		ob_start();

		foreach ( $custom_fields as $field_args ) {
			$field_key = Helper::getFieldKey( $field_args );
			$value     = $stored_field_map[ $field_key ]['raw_value'] ?? '';

			Helper::generateInputField( $field_args, $value, true );
		}

		return (string) ob_get_clean();
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
