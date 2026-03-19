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
		$item_meta_data = $item->get_meta( EXPRDAWC_META_EXTRA_PRODUCT_DATA, true );

		if ( ! is_array( $item_meta_data ) || empty( $item_meta_data ) ) {
			return array();
		}

		$indexed_data = array();
		foreach ( $item_meta_data as $field_data ) {
			// Resolve key: prefer id from raw_field (same priority as getFieldKey()),
			// fall back to deriving from the stored label.
			$raw_field = $field_data['raw_field'] ?? array();
			if ( ! empty( $raw_field ) ) {
				$index = Helper::getFieldKey( $raw_field );
			} else {
				$label = $field_data['label'] ?? '';
				$index = $label ? Helper::getFieldIndexFromLabel( $label ) : '';
			}
			if ( ! empty( $index ) ) {
				$indexed_data[ $index ] = $field_data;
			}
		}

		return $indexed_data;
	}

	/**
	 * Get submitted field data for a field configuration.
	 *
	 * Resolves the effective field key using the shared id-or-label logic and reads
	 * the submitted value from the request payload.
	 *
	 * @param array  $field    Field configuration array.
	 * @param string $post_key Request array key containing submitted field values.
	 * @return array{index: string, value: mixed} Field key and submitted value.
	 */
	public static function getSubmittedFieldData( array $field, string $post_key = 'exprdawc_custom_field_input' ): array {
		$field_index = Helper::getFieldKey( $field );
		$field_value = Helper::getFieldValueFromPost( $field_index, $post_key );

		return array(
			'index' => $field_index,
			'value' => $field_value,
		);
	}

	/**
	 * Build normalized submitted field payload.
	 *
	 * The returned structure matches the payload used in cart item data and inside
	 * the order item's `_meta_extra_product_data` entry. This keeps create and update
	 * flows consistent and reduces maintenance effort.
	 *
	 * @param array $field_config Field configuration array.
	 * @param mixed $field_value  Submitted field value.
	 * @param float $base_price   Product base price used for price adjustments.
	 * @return array Normalized field payload.
	 */
	public static function buildSubmittedFieldPayload( array $field_config, $field_value, float $base_price = 0.0 ): array {
		$field_index      = Helper::getFieldKey( $field_config );
		$display_value    = self::sanitizeFieldValueForStorage( $field_config, $field_value );
		$price_adjustment = self::calculatePriceAdjustment( $field_config, $field_value, $base_price );
		$value_cart       = self::formatFieldValueWithPrice( $display_value, $price_adjustment, $field_config );

		return array(
			'id'                    => $field_config['id'] ?? '',
			'index'                 => $field_index,
			'value'                 => $display_value,
			'field_raw'             => $field_config,
			'value_cart'            => $value_cart,
			'price_adjustment'      => $price_adjustment,
			'price_adjustment_type' => $field_config['price_adjustment_type'] ?? 'fixed',
			'raw_value'             => $field_value,
		);
	}

	/**
	 * Build order item meta entry from a normalized field payload.
	 *
	 * @param array $field_payload Normalized field payload.
	 * @return array Order item metadata entry.
	 */
	public static function buildOrderItemMetaEntry( array $field_payload ): array {
		$field_label = $field_payload['field_raw']['label'] ?? '';
		$field_value = $field_payload['value'] ?? '';

		return array(
			'label'     => sanitize_text_field( $field_label ),
			'value'     => is_array( $field_value ) ? implode( ', ', array_map( 'sanitize_text_field', $field_value ) ) : sanitize_text_field( (string) $field_value ),
			'raw_field' => $field_payload,
		);
	}

	/**
	 * Build new field metadata array for an order item.
	 *
	 * @param array $field_payloads Normalized field payloads.
	 * @return array Field metadata entries for `_meta_extra_product_data`.
	 */
	public static function buildFieldMetadataArray( array $field_payloads ): array {
		$field_meta = array();

		foreach ( $field_payloads as $field_payload ) {
			$field_meta[] = self::buildOrderItemMetaEntry( $field_payload );
		}

		return $field_meta;
	}

	/**
	 * Sanitize a submitted field value for storage/display.
	 *
	 * This mirrors the frontend cart payload generation so order updates use the same
	 * normalized value format as newly created order items.
	 *
	 * @param array $field_config Field configuration array.
	 * @param mixed $field_value  Submitted field value.
	 * @return string Sanitized display value.
	 */
	public static function sanitizeFieldValueForStorage( array $field_config, $field_value ): string {
		switch ( $field_config['type'] ?? 'text' ) {
			case 'long_text':
				return is_array( $field_value )
					? implode( ', ', array_map( 'sanitize_textarea_field', $field_value ) )
					: sanitize_textarea_field( (string) $field_value );

			case 'number':
				return (string) floatval( $field_value );

			case 'email':
				return sanitize_email( (string) $field_value );

			case 'select':
			case 'radio':
			case 'checkbox':
				if ( is_array( $field_value ) ) {
					return implode( ', ', array_map( 'sanitize_text_field', $field_value ) );
				}

				return sanitize_text_field( (string) $field_value );

			case 'date':
			case 'text':
			default:
				return sanitize_text_field( (string) $field_value );
		}
	}

	/**
	 * Format a field value with price information for cart and order display.
	 *
	 * @param mixed $field_value       User submitted display value.
	 * @param float $price_adjustment  Calculated price adjustment.
	 * @param array $field_config      Field configuration.
	 * @return string Display value with optional price suffix.
	 */
	public static function formatFieldValueWithPrice( $field_value, float $price_adjustment, array $field_config ): string {
		$field_value = is_array( $field_value ) ? implode( ', ', array_map( 'sanitize_text_field', $field_value ) ) : (string) $field_value;

		if ( 0.0 === $price_adjustment ) {
			return $field_value;
		}

		if ( in_array( $field_config['type'] ?? 'text', array( 'checkbox', 'radio', 'select' ), true ) || 'fixed' === ( $field_config['price_adjustment_type'] ?? 'fixed' ) ) {
			$plus_minus = 0 < $price_adjustment ? '+' : '-';
			return $field_value . ' (' . $plus_minus . html_entity_decode( strip_tags( wc_price( abs( $price_adjustment ) ) ) ) . ')';
		}

		if ( 'percentage' === ( $field_config['price_adjustment_type'] ?? 'fixed' ) ) {
			return $field_value . ' (+' . html_entity_decode( strip_tags( wc_price( $field_config['priceAdjustmentValue'] ?? 0 ) ) ) . '%)';
		}

		return $field_value;
	}

	/**
	 * Get the old (current) value for a field from item metadata.
	 *
	 * @param array      $item_metadata Indexed item metadata.
	 * @param int|string $field_index   Field key (PHP may cast numeric string keys to int).
	 * @return mixed
	 */
	public static function getOldFieldValue( array $item_metadata, $field_index ) {
		return $item_metadata[ (string) $field_index ]['value'] ?? '';
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
