<?php
/**
 * Order Helper Class
 *
 * @package ExtraProductDataForWoo Commerce
 * @author Daniel Drevermann <info@triopsi.com>
 * @copyright Copyright (c) 2024, IT-Dienstleistungen Drevermann
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 */

declare(strict_types=1);

namespace Triopsi\Exprdawc\Helpers;

use WC_Order;
use WC_Order_Item;
use WC_Order_Item_Product;
use WC_Product;
use Triopsi\Exprdawc\Helpers\Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Order Helper Class
 *
 * Helper class for order-specific operations and calculations.
 */
class OrderHelper {

	/**
	 * Calculate the price adjustment based on the field configuration and value.
	 *
	 * @param array $field_config The field configuration array.
	 * @param mixed $field_value The value of the field.
	 * @param float $base_price The base price to calculate the adjustment from.
	 * @return float The calculated price adjustment.
	 */
	public static function calculatePriceAdjustment( array $field_config, $field_value, float $base_price = 0.0 ): float {
		if ( empty( $field_config['adjust_price'] ) || empty( $field_value ) ) {
			return 0.0;
		}

		$adjustment_type = $field_config['price_adjustment_type'] ?? 'fixed';
		$adjustment      = 0.0;

		if ( in_array( $field_config['type'], array( 'checkbox', 'radio', 'select' ), true ) ) {
			if ( empty( $field_config['options'] ) || ! is_array( $field_config['options'] ) ) {
				return 0.0;
			}

			$field_values = is_array( $field_value ) ? $field_value : array( (string) $field_value );

			foreach ( $field_config['options'] as $option ) {
				if ( in_array( $option['value'], $field_values, true ) ) {
					$adjustment += self::getAdjustmentValue( $option, $base_price );
				}
			}
		} else {
			$adjustment = self::getAdjustmentValue( $field_config, $base_price );
		}

		return $adjustment;
	}

	/**
	 * Get the calculated adjustment value based on the configuration and base price.
	 *
	 * @param array $config The configuration array for the adjustment.
	 * @param float $base_price The base price to calculate the adjustment from.
	 * @return float The calculated adjustment value.
	 */
	public static function getAdjustmentValue( array $config, float $base_price = 0.0 ): float {
		$adjustment_value = (float) ( $config['priceAdjustmentValue'] ?? 0.0 );

		if ( 0.0 === $adjustment_value ) {
			return 0.0;
		}

		$adjustment_type = $config['price_adjustment_type'] ?? 'fixed';

		if ( in_array( $adjustment_type, array( 'percentage' ), true ) && $base_price > 0 ) {
			return ( $base_price / 100 ) * $adjustment_value;
		}

		return $adjustment_value;
	}

	/**
	 * Get normalized field metadata from order item.
	 *
	 * @param WC_Order_Item $item The order item.
	 * @return array Array of field data indexed by field key.
	 */
	public static function getItemFieldMetadata( WC_Order_Item $item ): array {
		$item_meta_data = $item->get_meta( '_meta_extra_product_data', true );

		if ( ! is_array( $item_meta_data ) || empty( $item_meta_data ) ) {
			return array();
		}

		$indexed_data = array();
		foreach ( $item_meta_data as $field_data ) {
			$label = $field_data['label'] ?? '';
			if ( ! empty( $label ) ) {
				$index                  = Helper::getFieldIndexFromLabel( $label );
				$indexed_data[ $index ] = $field_data;
			}
		}

		return $indexed_data;
	}

	/**
	 * Build new field metadata array for order item.
	 *
	 * @param array $custom_fields Field configuration array from product.
	 * @param array $field_values  Field values indexed by field key.
	 * @return array
	 */
	public static function buildFieldMetadataArray( array $custom_fields, array $field_values ): array {
		$field_meta = array();

		foreach ( $custom_fields as $field ) {
			$field_index = Helper::getFieldIndexFromLabel( $field['label'] );
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
	 * @param array  $item_metadata Indexed item metadata.
	 * @param string $field_index Field key.
	 * @return mixed
	 */
	public static function getOldFieldValue( array $item_metadata, string $field_index ) {
		return $item_metadata[ $field_index ]['value'] ?? '';
	}

	/**
	 * Format field values for comparison and display.
	 *
	 * @param mixed $value The field value.
	 * @return string
	 */
	public static function formatFieldValueForDisplay( $value ): string {
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
	public static function addOrderNoteForChange( WC_Order $order, string $field_label, $old_value, $new_value ): void {
		$old_display = self::formatFieldValueForDisplay( $old_value );
		$new_display = self::formatFieldValueForDisplay( $new_value );

		if ( $old_display !== $new_display ) {
			$note = sprintf(
				/* translators: %1$s is the field label, %2$s is the old value, %3$s is the new value */
				__( '%1$s changed from "%2$s" to "%3$s".', 'extra-product-data-for-woocommerce' ),
				$field_label,
				$old_display,
				$new_display
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
	public static function getProductFromItem( WC_Order_Item $item ) {
		if ( ! $item instanceof WC_Order_Item_Product ) {
			return false;
		}
		$product = $item->get_product();

		if ( ! $product instanceof WC_Product ) {
			return false;
		}

		if ( $product->is_type( 'variation' ) ) {
			$product = wc_get_product( $product->get_parent_id() );
		}

		return $product instanceof WC_Product ? $product : false;
	}
}
