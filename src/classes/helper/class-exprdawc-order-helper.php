<?php
/**
 * Created on Feb 19 2025
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

declare( strict_types=1 );
namespace Triopsi\Exprdawc\Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use WC_Order;
use WC_Order_Item;
use WC_Product;

/**
 * Class Exprdawc_Order_Helper
 *
 * Helper class for order-specific operations and calculations.
 * Centralizes price adjustment calculations and field value processing for orders.
 *
 * @package Exprdawc\Helper
 */
class Exprdawc_Order_Helper {

	/**
	 * Calculate price adjustment for a field based on selected options/values.
	 *
	 * Supports both option-based fields (radio, checkbox, select) with individual
	 * price adjustments per option, and direct field adjustments.
	 *
	 * @param array $field_config Field configuration array.
	 * @param mixed $field_value  Selected value(s) (string or array).
	 * @param float $base_price   Base product price for percentage calculations.
	 * @return float The calculated price adjustment.
	 */
	public static function calculate_price_adjustment( array $field_config, $field_value, float $base_price = 0.0 ): float {
		if ( empty( $field_config['adjust_price'] ) || empty( $field_value ) ) {
			return 0.0;
		}

		$adjustment_type = $field_config['price_adjustment_type'] ?? 'fixed';
		$adjustment      = 0.0;

		// Handle option-based fields (radio, checkbox, select).
		if ( in_array( $field_config['type'], array( 'checkbox', 'radio', 'select' ), true ) ) {
			if ( empty( $field_config['options'] ) || ! is_array( $field_config['options'] ) ) {
				return 0.0;
			}

			// Convert field value to array for consistent handling.
			$field_values = is_array( $field_value ) ? $field_value : array( (string) $field_value );

			foreach ( $field_config['options'] as $option ) {
				if ( in_array( $option['value'], $field_values, true ) ) {
					$adjustment += self::get_adjustment_value( $option, $base_price );
				}
			}
		} else {
			// Direct field adjustment (text, number, etc.).
			$adjustment = self::get_adjustment_value( $field_config, $base_price );
		}

		return $adjustment;
	}

	/**
	 * Get the adjustment value for a single option or field.
	 *
	 * Handles both fixed and percentage-based adjustments.
	 *
	 * @param array $config     Option or field config with 'price_adjustment_value' and 'price_adjustment_type'.
	 * @param float $base_price Base price for percentage calculations.
	 * @return float The adjustment value.
	 */
	public static function get_adjustment_value( array $config, float $base_price = 0.0 ): float {
		$adjustment_value = (float) ( $config['price_adjustment_value'] ?? 0.0 );

		if ( 0.0 === $adjustment_value ) {
			return 0.0;
		}

		$adjustment_type = $config['price_adjustment_type'] ?? 'fixed';

		// For percentage adjustments, calculate based on base price.
		if ( in_array( $adjustment_type, array( 'percentage' ), true ) && $base_price > 0 ) {
			return ( $base_price / 100 ) * $adjustment_value;
		}

		// Fixed adjustment.
		return $adjustment_value;
	}

	/**
	 * Get normalized field metadata from order item.
	 *
	 * Converts field list to indexed array keyed by field_index for easier lookup.
	 *
	 * @param WC_Order_Item $item The order item.
	 * @return array Array of field data indexed by field key.
	 */
	public static function get_item_field_metadata( WC_Order_Item $item ): array {
		$item_meta_data = $item->get_meta( '_meta_extra_product_data', true );

		if ( ! is_array( $item_meta_data ) || empty( $item_meta_data ) ) {
			return array();
		}

		// Convert to indexed array by field key for easier lookup.
		$indexed_data = array();
		foreach ( $item_meta_data as $field_data ) {
			$label = $field_data['label'] ?? '';
			if ( ! empty( $label ) ) {
				$index                  = Exprdawc_Helper::get_field_index_from_label( $label );
				$indexed_data[ $index ] = $field_data;
			}
		}

		return $indexed_data;
	}

	/**
	 * Build new field metadata array for order item.
	 *
	 * Creates standardized field metadata with label, value, and original field config.
	 *
	 * @param array $custom_fields Field configuration array from product.
	 * @param array $field_values  Field values indexed by field key.
	 * @return array Array of field metadata for '_meta_extra_product_data'.
	 */
	public static function build_field_metadata_array( array $custom_fields, array $field_values ): array {
		$field_meta = array();

		foreach ( $custom_fields as $field ) {
			$field_index = Exprdawc_Helper::get_field_index_from_label( $field['label'] );
			$field_value = $field_values[ $field_index ] ?? '';

			$field_meta[] = array(
				'label'     => sanitize_text_field( $field['label'] ),
				'value'     => is_array( $field_value ) ? implode( ', ', $field_value ) : sanitize_text_field( $field_value ),
				'raw_field' => $field,
			);
		}

		return $field_meta;
	}

	/**
	 * Get the old (current) value for a field from item metadata.
	 *
	 * @param array  $item_metadata  Indexed item metadata.
	 * @param string $field_index    Field key.
	 * @return mixed The old value (string or array).
	 */
	public static function get_old_field_value( array $item_metadata, string $field_index ) {
		return $item_metadata[ $field_index ]['value'] ?? '';
	}

	/**
	 * Format field values for comparison and display.
	 *
	 * @param mixed $value The field value.
	 * @return string Formatted value for display.
	 */
	public static function format_field_value_for_display( $value ): string {
		if ( is_array( $value ) ) {
			return implode( ', ', $value );
		}

		return (string) $value;
	}

	/**
	 * Add order note for field value change.
	 *
	 * @param WC_Order $order       The order object.
	 * @param string   $field_label Field label.
	 * @param mixed    $old_value   Old value.
	 * @param mixed    $new_value   New value.
	 * @return void
	 */
	public static function add_order_note_for_change( WC_Order $order, string $field_label, $old_value, $new_value ): void {
		$old_display = self::format_field_value_for_display( $old_value );
		$new_display = self::format_field_value_for_display( $new_value );

		if ( $old_display !== $new_display ) {
			$note = sprintf(
				// translators: %1$s is the field label, %2$s is the old value, %3$s is the new value.
				__( '%1$s changed from "%2$s" to "%3$s".', 'extra-product-data-for-woocommerce' ),
				sanitize_text_field( $field_label ),
				esc_attr( $old_display ),
				esc_attr( $new_display )
			);

			$order->add_order_note( $note );
		}
	}

	/**
	 * Get product from order item, handling variations.
	 *
	 * @param WC_Order_Item $item The order item.
	 * @return WC_Product|false The product object or false.
	 */
	public static function get_product_from_item( WC_Order_Item $item ) {

		/**
		 * Get the product data.
		 *
		 * @disregard
		 */
		$product = $item->get_product();

		if ( ! $product instanceof WC_Product ) {
			return false;
		}

		// For variations, get parent product.
		if ( $product->is_type( 'variation' ) ) {
			$product = wc_get_product( $product->get_parent_id() );
		}

		return $product instanceof WC_Product ? $product : false;
	}
}
