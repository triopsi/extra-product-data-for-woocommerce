<?php
/**
 * Base Order Handler
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

namespace Triopsi\Exprdawc\Orders;

use Automattic\WooCommerce\Utilities\OrderUtil;
use WC_Order;
use WC_Order_Item;
use WC_Order_Item_Product;
use Triopsi\Exprdawc\Helpers\Helper;
use Triopsi\Exprdawc\Helpers\OrderHelper;
use Triopsi\Exprdawc\Helpers\Helpers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Base Order Handler
 *
 * Abstract base class for order-related functionality.
 */
class BaseOrder {

	/**
	 * Process save order.
	 *
	 * @param bool $admin Whether the save is triggered from admin or user side. Default false.
	 * @return int|false The order ID on success, false on failure.
	 */
	protected function process_save_order( bool $admin = false ) {

		$item_id  = isset( $_POST['item_id'] ) ? intval( $_POST['item_id'] ) : 0;// phpcs:ignore
		$order_id = isset( $_POST['order_id'] ) ? intval( $_POST['order_id'] ) : 0;// phpcs:ignore

		if ( ! $item_id || ! $order_id ) {
			wp_send_json_error( array( 'message' => __( 'Invalid order or item ID.', 'extra-product-data-for-woocommerce' ) ) );
		}

		if ( ! is_user_logged_in() ) {
			wp_send_json_error( array( 'message' => __( 'You must be logged in to edit this order.', 'extra-product-data-for-woocommerce' ) ) );
		}

		// Check user permissions.
		$current_user_id = get_current_user_id();

		// Load the order and check if it exists.
		$order = wc_get_order( $order_id );
		if ( ! $order || ! ( $order instanceof WC_Order ) ) {
			wp_send_json_error( array( 'message' => __( 'Order not found.', 'extra-product-data-for-woocommerce' ) ) );
		}
		// Check if the user has permission to edit the order.
		if ( $admin ) {
			// For admin users, check if they have the capability to edit shop orders.
			if ( ! current_user_can( 'edit_shop_orders' ) ) { // phpcs:ignore
				wp_send_json_error( array( 'message' => __( 'You do not have permission to edit this order.', 'extra-product-data-for-woocommerce' ) ) );
			}
		} else { // phpcs:ignore
			// For non-admin users, check if they are the owner of the order or have permission to edit it based on order status.
			if ( $order->get_user_id() !== $current_user_id ) { // phpcs:ignore
				wp_send_json_error( array( 'message' => __( 'You do not have permission to edit this order.', 'extra-product-data-for-woocommerce' ) ) );
			}
		}

		if ( $admin ) {
			if ( ! current_user_can( 'manage_woocommerce' ) ) {
				if ( ! Helper::is_order_editable( $order ) ) {
					wp_send_json_error( array( 'message' => __( 'You do not have permission to edit this order.', 'extra-product-data-for-woocommerce' ) ) );
				}
			}
		} else { // phpcs:ignore
			if ( ! Helper::is_order_editable( $order ) ) {
				wp_send_json_error( array( 'message' => __( 'You do not have permission to edit this order.', 'extra-product-data-for-woocommerce' ) ) );
			}
		}

		// Load the order item and check if it exists and is a product item.
		$item = $order->get_item( $item_id );
		if ( ! $item instanceof WC_Order_Item_Product ) {
			wp_send_json_error(
				array(
					'message' => __( 'Item not found.', 'extra-product-data-for-woocommerce' ),
				)
			);
		}

		// Save new meta data and update item price.
		$this->save_new_meta_data( $order, $item );

		// Calculate new price based on updated meta data.
		$new_price = $this->calculate_new_price( $item );

		// Update item price and totals.
		$item->set_subtotal( $new_price * $item->get_quantity() );
		$item->set_total( $new_price * $item->get_quantity() );

		// Save the item to update the price changes.
		$item->save();

		// Recalculate order totals after updating item price.
		$order->calculate_totals();
		return $order->save();
	}

	/**
	 * Save new meta data.
	 *
	 * @param WC_Order      $order The order object.
	 * @param WC_Order_Item $item  The order item object.
	 * @return void
	 */
	protected function save_new_meta_data( WC_Order $order, WC_Order_Item $item ): void {
		$product = OrderHelper::getProductFromItem( $item );
		if ( ! $product ) {
			wp_send_json_error( array( 'message' => __( 'Product not found.', 'extra-product-data-for-woocommerce' ) ) );
		}

		$custom_fields = $product->get_meta( '_extra_product_fields', true );
		if ( ! is_array( $custom_fields ) || empty( $custom_fields ) ) {
			wp_send_json_error( array( 'message' => __( 'No extra product data found.', 'extra-product-data-for-woocommerce' ) ) );
		}

		$item_metadata = OrderHelper::getItemFieldMetadata( $item );

		$field_values = array();
		foreach ( $custom_fields as $field ) {
			$field_index = Helper::getFieldIndexFromLabel( $field['label'] );
			$field_value = Helper::getFieldValueFromPost( $field_index );

			if ( ! empty( $field['required'] ) && empty( $field_value ) ) {
				wp_send_json_error(
					array(
						'message' => sprintf(
							/* translators: %s is the field label */
							__( '%s is a required field.', 'extra-product-data-for-woocommerce' ),
							esc_html( $field['label'] )
						),
					)
				);
			}

			$validation_result = Helper::validateFieldByType(
				$field_value,
				$field['type'],
				$field['options'] ?? array()
			);

			if ( ! $validation_result['valid'] ) {
				wp_send_json_error(
					array(
						'message' => sprintf(
							/* translators: %1$s is the field label, %2$s is the error message */
							__( '%1$s: %2$s', 'extra-product-data-for-woocommerce' ),
							esc_html( $field['label'] ),
							esc_html( $validation_result['message'] )
						),
					)
				);
			}

			$field_values[ $field_index ] = $field_value;

			$old_value = OrderHelper::getOldFieldValue( $item_metadata, $field_index );
			OrderHelper::addOrderNoteForChange( $order, $field['label'], $old_value, $field_value );

			$item->update_meta_data( $field['label'], $field_value );
		}

		$field_meta = OrderHelper::buildFieldMetadataArray( $custom_fields, $field_values );
		$item->update_meta_data( '_meta_extra_product_data', $field_meta );
	}

	/**
	 * Calculates the new price for the order item.
	 *
	 * @param WC_Order_Item $item The order item.
	 * @return float The new price for the item.
	 */
	protected function calculate_new_price( WC_Order_Item $item ): float {
		$product = OrderHelper::getProductFromItem( $item );
		if ( ! $product ) {
			return 0.0;
		}

		$custom_fields = $product->get_meta( '_extra_product_fields', true );
		if ( ! is_array( $custom_fields ) || empty( $custom_fields ) ) {
			return (float) $product->get_price();
		}

		$extra_costs = 0.0;
		$base_price  = (float) $product->get_price();

		foreach ( $custom_fields as $field ) {
			$field_index = Helper::getFieldIndexFromLabel( $field['label'] );
			$field_value = Helper::getFieldValueFromPost( $field_index );

			if ( ! empty( $field['adjust_price'] ) && ! empty( $field_value ) ) {
				$price_adjustment = OrderHelper::calculatePriceAdjustment( $field, $field_value, $base_price );
				$extra_costs     += (float) $price_adjustment;
			}
		}

		return $base_price + $extra_costs;
	}
}
