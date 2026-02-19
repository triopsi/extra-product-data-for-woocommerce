<?php
/**
 * Created on Sat Jan 11 2025
 *
 * Copyright (c) 2025 IT-Dienstleistungen Drevermann - All Rights Reserved
 *
 * @package Extra Product Data for WooCommerce
 * @author Daniel Drevermann <info@triopsi.com>
 * @copyright Copyright (c) 2025, IT-Dienstleistungen Drevermann
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
 * Class Exprdawc_Base_Order_Class
 *
 * This class is responsible for the base order class.
 *
 * @package Exprdawc
 */
class Exprdawc_Base_Order_Class {

	/**
	 * Process the order item update.
	 *
	 * @return int|false The order ID on success, false on failure.
	 */
	protected function process_save_order() {

		// Get the necessary parameters.
		$item_id  = isset( $_POST['item_id'] ) ? intval( $_POST['item_id'] ) : 0;// phpcs:ignore
		$order_id = isset( $_POST['order_id'] ) ? intval( $_POST['order_id'] ) : 0;// phpcs:ignore

		if ( ! $item_id || ! $order_id ) {
			wp_send_json_error( array( 'message' => __( 'Invalid order or item ID.', 'extra-product-data-for-woocommerce' ) ) );
		}

		// Check if the user has permission to edit the order.
		// Check if the user is logged in.
		if ( ! is_user_logged_in() ) {
			wp_send_json_error( array( 'message' => __( 'You must be logged in to edit this order.', 'extra-product-data-for-woocommerce' ) ) );
		}

		// Get the current user ID.
		$current_user_id = get_current_user_id();

		// Get the order.
		$order = wc_get_order( $order_id );
		if ( ! $order || ! ( $order instanceof WC_Order ) ) {
			wp_send_json_error( array( 'message' => __( 'Order not found.', 'extra-product-data-for-woocommerce' ) ) );
		}

		// Get all capabilities for the current user.
		$user         = get_user_by( 'id', $current_user_id );
		$capabilities = $user ? $user->allcaps : array();
		var_dump( $capabilities ); // phpcs:ignore

		var_dump(current_user_can( 'edit_shop_orders' )); // phpcs:ignore
		var_dump($order->get_user_id() ); // phpcs:ignore
		var_dump($current_user_id ); // phpcs:ignore

		// Check if the current user is the one who placed the order.
		if ( $order->get_user_id() !== $current_user_id && ! current_user_can( 'edit_shop_orders' ) ) { // phpcs:ignore
			wp_send_json_error( array( 'message' => __( 'You do not have permission to edit this order.', 'extra-product-data-for-woocommerce' ) ) );
		}

		// Check if the order status is allowed for editing.
		var_dump(current_user_can( 'manage_woocommerce' )); // phpcs:ignore
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			$max_order_status = get_option( 'extra_product_data_max_order_status', 'processing' );
			var_dump($max_order_status); // phpcs:ignore
			if ( ! $order->has_status( OrderUtil::remove_status_prefix( $max_order_status ) ) ) {
				wp_send_json_error( array( 'message' => __( 'You do not have permission to edit this order.', 'extra-product-data-for-woocommerce' ) ) );
			}
		}

		$item = $order->get_item( $item_id );
		if ( ! $item || ! ( $item instanceof WC_Order_Item ) ) {
			wp_send_json_error( array( 'message' => __( 'Item not found.', 'extra-product-data-for-woocommerce' ) ) );
		}

		// Save new Meta Data.
		$this->save_new_meta_data( $order, $item );

		// Calculate new price adjustments and update the item.
		$new_price = $this->calculate_new_price( $item );

		/**
		 * Set the subtotal for the item.
		 *
		 * @disregard
		 */
		$item->set_subtotal( $new_price * $item->get_quantity() );

		/**
		 * Set the total for the item.
		 *
		 * @disregard
		 */
		$item->set_total( $new_price * $item->get_quantity() );

		// Save the item.
		$item->save();

		// Recalculate the order totals.
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
		// Get the product from item.
		$product = Exprdawc_Order_Helper::get_product_from_item( $item );
		if ( ! $product ) {
			wp_send_json_error( array( 'message' => __( 'Product not found.', 'extra-product-data-for-woocommerce' ) ) );
		}

		// Get the Custom Fields from product.
		$custom_fields = $product->get_meta( '_extra_product_fields', true );
		if ( ! is_array( $custom_fields ) || empty( $custom_fields ) ) {
			wp_send_json_error( array( 'message' => __( 'No extra product data found.', 'extra-product-data-for-woocommerce' ) ) );
		}

		// Get normalized field metadata from item.
		$item_metadata = Exprdawc_Order_Helper::get_item_field_metadata( $item );

		// Process each field.
		$field_values = array();
		foreach ( $custom_fields as $field ) {
			$field_index = Exprdawc_Helper::get_field_index_from_label( $field['label'] );
			$field_value = Exprdawc_Helper::get_field_value_from_post( $field_index );

			// Validate required fields.
			if ( ! empty( $field['required'] ) && empty( $field_value ) ) {
				wp_send_json_error(
					array(
						'message' => sprintf(
							// translators: %s is the field label.
							__( '%s is a required field.', 'extra-product-data-for-woocommerce' ),
							esc_html( $field['label'] )
						),
					)
				);
			}

			// Validate field value based on type.
			$validation_result = Exprdawc_Helper::validate_field_by_type(
				$field_value,
				$field['type'],
				$field['options'] ?? array()
			);

			if ( ! $validation_result['valid'] ) {
				wp_send_json_error(
					array(
						'message' => sprintf(
							// translators: %1$s is the field label, %2$s is the validation message.
							__( '%1$s: %2$s', 'extra-product-data-for-woocommerce' ),
							esc_html( $field['label'] ),
							esc_html( $validation_result['message'] )
						),
					)
				);
			}

			// Store value for later processing.
			$field_values[ $field_index ] = $field_value;

			// Get old value and add order note for changes.
			$old_value = Exprdawc_Order_Helper::get_old_field_value( $item_metadata, $field_index );
			Exprdawc_Order_Helper::add_order_note_for_change( $order, $field['label'], $old_value, $field_value );

			// Update item meta data.
			$item->update_meta_data( $field['label'], $field_value );
		}

		// Build and save unified field metadata.
		$field_meta = Exprdawc_Order_Helper::build_field_metadata_array( $custom_fields, $field_values );
		$item->update_meta_data( '_meta_extra_product_data', $field_meta );
	}

	/**
	 * Calculates the new price for the order item.
	 *
	 * @param WC_Order_Item $item The order item.
	 * @return float The new price for the item.
	 */
	protected function calculate_new_price( WC_Order_Item $item ): float {
		// Get the product from item.
		$product = Exprdawc_Order_Helper::get_product_from_item( $item );
		if ( ! $product ) {
			return 0.0;
		}

		$custom_fields = $product->get_meta( '_extra_product_fields', true );
		if ( ! is_array( $custom_fields ) || empty( $custom_fields ) ) {
			return $product->get_price();
		}

		$extra_costs = 0.0;
		$base_price  = (float) $product->get_price();

		foreach ( $custom_fields as $field ) {
			// Get field value from POST.
			$field_index = Exprdawc_Helper::get_field_index_from_label( $field['label'] );
			$field_value = Exprdawc_Helper::get_field_value_from_post( $field_index );

			if ( ! empty( $field['adjust_price'] ) && ! empty( $field_value ) ) {
				$price_adjustment = Exprdawc_Order_Helper::calculate_price_adjustment( $field, $field_value, $base_price );
				$extra_costs     += $price_adjustment;
			}
		}

		return $base_price + $extra_costs;
	}
}
