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

use WC_Order;
use WC_Order_Item;
use WC_Order_Item_Product;
use Triopsi\Exprdawc\Helpers\Helper;
use Triopsi\Exprdawc\Helpers\OrderHelper;
use WC_Product;

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
	protected function processSaveOrder( bool $admin = false ) {

		$item_id  = isset( $_POST['item_id'] ) ? intval( $_POST['item_id'] ) : 0; // phpcs:ignore
		$order_id = isset( $_POST['order_id'] ) ? intval( $_POST['order_id'] ) : 0; // phpcs:ignore

		if ( ! $item_id || ! $order_id ) {
			wp_send_json_error(
				array(
					'message' => __( 'Invalid order or item ID.', 'extra-product-data-for-woocommerce' ),
				)
			);
		}

		if ( ! is_user_logged_in() ) {
			wp_send_json_error(
				array(
					'message' => __( 'You must be logged in to edit this order.', 'extra-product-data-for-woocommerce' ),
				)
			);
		}

		// Get the current user ID for permission checks.
		$current_user_id = get_current_user_id();

		// Load the order and ensure it exists.
		$order = wc_get_order( $order_id );
		if ( ! $order || ! ( $order instanceof WC_Order ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Order not found.', 'extra-product-data-for-woocommerce' ),
				)
			);
		}

		// Check whether the current user is allowed to edit this order.
		if ( $admin ) {
			// Admin-side requests require the capability to edit shop orders.
			if ( ! current_user_can( 'edit_shop_orders' ) ) { // phpcs:ignore
				wp_send_json_error(
					array(
						'message' => __( 'You do not have permission to edit this order.', 'extra-product-data-for-woocommerce' ),
					)
				);
			}
		} else { // phpcs:ignore
			// Frontend users may only edit their own orders.
			if ( $order->get_user_id() !== $current_user_id ) { // phpcs:ignore
				wp_send_json_error(
					array(
						'message' => __( 'You do not have permission to edit this order.', 'extra-product-data-for-woocommerce' ),
					)
				);
			}
		}

		// Check whether the order is still editable according to plugin/business rules.
		if ( $admin ) {
			if ( ! current_user_can( 'manage_woocommerce' ) ) {
				if ( ! Helper::is_order_editable( $order ) ) {
					wp_send_json_error(
						array(
							'message' => __( 'You do not have permission to edit this order.', 'extra-product-data-for-woocommerce' ),
						)
					);
				}
			}
		} elseif ( ! Helper::is_order_editable( $order ) ) {
				wp_send_json_error(
					array(
						'message' => __( 'You do not have permission to edit this order.', 'extra-product-data-for-woocommerce' ),
					)
				);
		}

		// Load the order item and ensure it is a product line item.
		$item = $order->get_item( $item_id );
		if ( ! $item instanceof WC_Order_Item_Product ) {
			wp_send_json_error(
				array(
					'message' => __( 'Item not found.', 'extra-product-data-for-woocommerce' ),
				)
			);
		}

		// Save the submitted meta data and get the normalized payloads used for price calculation.
		$field_payloads = $this->saveNewMetaData( $order, $item );

		// Calculate the new unit price based on the updated item meta data.
		// This price is assumed to be gross if your pricing logic already includes tax.
		$new_price_gross = (float) $this->calculateNewPrice( $item, $field_payloads );

		// Make sure quantity is at least 1 to avoid invalid line totals.
		$quantity = max( 1, (int) $item->get_quantity() );

		// Get the related product from the order item.
		$product = $item->get_product();

		// Default to a direct multiplication in case no product is available.
		$line_total_net = $new_price_gross * $quantity;

		// Convert the gross unit price to a net line total when the order stores prices including tax.
		// WooCommerce order item totals must always be stored excluding tax.
		if (
		$product instanceof WC_Product &&
		$order->get_prices_include_tax() &&
		wc_tax_enabled() &&
		'taxable' === $item->get_tax_status()
		) {
			$line_total_net = (float) wc_get_price_excluding_tax(
				$product,
				array(
					'qty'   => $quantity,
					'price' => $new_price_gross,
				)
			);
		}

		// Set the order item totals as net values.
		$item->set_subtotal( $line_total_net );
		$item->set_total( $line_total_net );

		// Recalculate taxes for this item using the order address context.
		if ( wc_tax_enabled() && 'taxable' === $item->get_tax_status() ) {
			$item->calculate_taxes(
				array(
					'country'  => $order->get_shipping_country() ? $order->get_shipping_country() : $order->get_billing_country(),
					'state'    => $order->get_shipping_state() ? $order->get_shipping_state() : $order->get_billing_state(),
					'postcode' => $order->get_shipping_postcode() ? $order->get_shipping_postcode() : $order->get_billing_postcode(),
					'city'     => $order->get_shipping_city() ? $order->get_shipping_city() : $order->get_billing_city(),
				)
			);
		}

		// Persist the updated order item values.
		$item->save();

		// Recalculate order taxes based on the updated item values.
		$order->calculate_taxes();

		// Recalculate order totals without recalculating taxes a second time.
		$order->calculate_totals( false );

		// Save and return the updated order.
		return $order->save();
	}

	/**
	 * Save new meta data.
	 *
	 * @param WC_Order      $order The order object.
	 * @param WC_Order_Item $item  The order item object.
	 * @return array Normalized field payloads indexed by field key.
	 */
	protected function saveNewMetaData( WC_Order $order, WC_Order_Item $item ): array {
		$product        = $this->getOrderItemProductOrFail( $item );
		$base_price     = (float) $product->get_price();
		$custom_fields  = $this->getOrderItemCustomFieldsOrFail( $product );
		$item_metadata  = OrderHelper::getItemFieldMetadata( $item );
		$field_payloads = $this->buildUpdatedOrderItemPayloads( $custom_fields, $base_price );

		$this->syncOrderItemMetaData( $order, $item, $field_payloads, $item_metadata, $base_price );

		return $field_payloads;
	}

	/**
	 * Calculates the new price for the order item.
	 *
	 * When normalized payloads are provided, they are used directly so the order edit
	 * flow does not need to read and normalize submitted request data twice.
	 *
	 * @param WC_Order_Item $item           The order item.
	 * @param array         $field_payloads Optional normalized field payloads.
	 * @return float The new price for the item.
	 */
	protected function calculateNewPrice( WC_Order_Item $item, array $field_payloads = array() ): float {
		$product = OrderHelper::getProductFromItem( $item );
		if ( ! $product ) {
			return 0.0;
		}

		$custom_fields = Helper::getExtraProductFields( $product );
		if ( empty( $custom_fields ) ) {
			return (float) $product->get_price();
		}

		$quantity    = max( 1, (int) $item->get_quantity() );
		$extra_costs = 0.0;
		$base_price  = (float) $product->get_price();

		if ( ! empty( $field_payloads ) ) {
			foreach ( $field_payloads as $field_payload ) {
				$field_config = $field_payload['field_raw'] ?? array();
				$field_value  = $field_payload['raw_value'] ?? '';

				if ( ! empty( $field_config['adjust_price'] ) && ! empty( $field_value ) ) {
					$extra_costs += (float) OrderHelper::calculatePriceAdjustment( $field_config, $field_value, $base_price, $quantity );
				}
			}

			$unit_extra_costs = $extra_costs / $quantity;

			return $base_price + $unit_extra_costs;
		}

		foreach ( $custom_fields as $field ) {
			$field_index = Helper::getFieldKey( $field );
			$field_value = Helper::getFieldValueFromPost( $field_index );

			if ( ! empty( $field['adjust_price'] ) && ! empty( $field_value ) ) {
				$price_adjustment = OrderHelper::calculatePriceAdjustment( $field, $field_value, $base_price, $quantity );
				$extra_costs     += (float) $price_adjustment;
			}
		}

		$unit_extra_costs = $extra_costs / $quantity;

		return $base_price + $unit_extra_costs;
	}

	/**
	 * Get the product for an order item or stop the request with a JSON error.
	 *
	 * @param WC_Order_Item $item The order item.
	 * @return \WC_Product The resolved product.
	 */
	protected function getOrderItemProductOrFail( WC_Order_Item $item ) {
		$product = OrderHelper::getProductFromItem( $item );

		if ( ! $product ) {
			wp_send_json_error( array( 'message' => __( 'Product not found.', 'extra-product-data-for-woocommerce' ) ) );
		}

		return $product;
	}

	/**
	 * Get configured extra product fields for a product or stop the request.
	 *
	 * @param mixed $product The resolved WooCommerce product.
	 * @return array Product field configuration array.
	 */
	protected function getOrderItemCustomFieldsOrFail( $product ): array {
		$custom_fields = Helper::getExtraProductFields( $product );

		if ( empty( $custom_fields ) ) {
			wp_send_json_error( array( 'message' => __( 'No extra product data found.', 'extra-product-data-for-woocommerce' ) ) );
		}

		return $custom_fields;
	}

	/**
	 * Build normalized payloads for all submitted order item fields.
	 *
	 * This method centralizes request reading, validation and payload creation so the
	 * order edit flow mirrors the frontend cart/order creation flow.
	 *
	 * @param array $custom_fields Product field definitions.
	 * @param float $base_price    Product base price.
	 * @return array Normalized field payloads indexed by resolved field key.
	 */
	protected function buildUpdatedOrderItemPayloads( array $custom_fields, float $base_price ): array {
		$field_payloads = array();

		foreach ( $custom_fields as $field ) {
			$field_data  = OrderHelper::getSubmittedFieldData( $field );
			$field_index = $field_data['index'];
			$field_value = $field_data['value'];

			$this->validateSubmittedOrderItemField( $field, $field_value );

			$field_payloads[ $field_index ] = OrderHelper::buildSubmittedFieldPayload( $field, $field_value, $base_price );
		}

		return $field_payloads;
	}

	/**
	 * Validate a submitted order item field value.
	 *
	 * Stops the current AJAX request with a JSON error when a required field is empty
	 * or when the submitted value does not match the field type configuration.
	 *
	 * @param array $field       Field configuration array.
	 * @param mixed $field_value Submitted field value.
	 * @return void
	 */
	protected function validateSubmittedOrderItemField( array $field, $field_value ): void {
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
	}

	/**
	 * Synchronize updated field payloads back to the order item.
	 *
	 * Updates the per-label meta entries, rebuilds `_meta_extra_product_data` and adds
	 * order notes only when a value actually changed.
	 *
	 * @param WC_Order      $order          The order object.
	 * @param WC_Order_Item $item           The order item.
	 * @param array         $field_payloads Normalized field payloads indexed by field key.
	 * @param array         $item_metadata  Existing indexed item metadata.
	 * @param float         $base_price     Original item base price.
	 * @return void
	 */
	protected function syncOrderItemMetaData( WC_Order $order, WC_Order_Item $item, array $field_payloads, array $item_metadata, float $base_price ): void {
		foreach ( $field_payloads as $field_index => $field_payload ) {
			$field_index = (string) $field_index; // Prevent PHP int-cast of numeric string keys.
			$field_label = $field_payload['field_raw']['label'] ?? '';
			$new_value   = $field_payload['display_value'] ?? '';
			$old_value   = OrderHelper::getOldFieldValue( $item_metadata, $field_index );

			OrderHelper::addOrderNoteForChange( $order, $field_label, $old_value, $new_value );
			$item->update_meta_data( $field_label, $new_value );
		}

		$item->update_meta_data( EXPRDAWC_ORDER_META_EXTRA_PRODUCT_DATA, OrderHelper::buildFieldMetadataArray( $field_payloads ) );
		$item->update_meta_data( esc_html__( 'Original item price', 'extra-product-data-for-woocommerce' ), OrderHelper::formatPlainPrice( $base_price ) );
	}

	/**
	 * Extract order item meta data from raw field data stored in `_meta_extra_product_data`.
	 *
	 * Processes the raw field data and creates a normalized array keyed by resolved
	 * field key. Key priority mirrors Helper::getFieldKey():
	 *
	 * 1. 'id' from raw_field (dashes normalized to underscores) if present
	 * 2. 'index' from raw_field if present and no 'id'
	 * 3. Original field key (string) or label-derived fallback
	 *
	 * @param mixed $order_item_data Order item meta value (expects an array).
	 * @return array Normalized payload array keyed by resolved field key.
	 */
	protected function extractOrderItemMetaData( $order_item_data ): array {
		$all_user_inputs = array();

		if ( empty( $order_item_data ) || ! is_array( $order_item_data ) ) {
			return $all_user_inputs;
		}

		foreach ( $order_item_data as $field_key => $field_data ) {
			if ( ! isset( $field_data['raw_field'] ) ) {
				continue;
			}

			// Resolve the key using the same priority as Helper::getFieldKey().
			$raw_field = $field_data['raw_field'];
			if ( ! empty( $raw_field['id'] ) ) {
				$new_key = str_replace( '-', '_', $raw_field['id'] );
			} elseif ( ! empty( $raw_field['index'] ) ) {
				$new_key = $raw_field['index'];
			} else {
				$new_key = is_string( $field_key ) ? $field_key : Helper::getFieldIndexFromLabel( $raw_field['label'] ?? '' );
			}

			// Ensure key uniqueness by appending random suffix if needed.
			if ( array_key_exists( $new_key, $all_user_inputs ) ) {
				$new_key .= '_' . wp_rand();
			}

			$all_user_inputs[ $new_key ] = $field_data['raw_field'];
		}

		return $all_user_inputs;
	}
}
