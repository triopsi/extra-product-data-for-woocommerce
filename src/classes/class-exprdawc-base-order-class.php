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
	 * Undocumented function
	 *
	 * @return int order ID
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

		// Check if the current user is the one who placed the order.
		if ( $order->get_user_id() !== $current_user_id && ! current_user_can( 'edit_shop_orders' ) ) { // phpcs:ignore
			wp_send_json_error( array( 'message' => __( 'You do not have permission to edit this order.', 'extra-product-data-for-woocommerce' ) ) );
		}

		// Check if the order status is allowed for editing.
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			$max_order_status = get_option( 'extra_product_data_max_order_status', 'processing' );
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
	 */
	protected function save_new_meta_data( $order, $item ) {

		/**
		 * Get the product data.
		 *
		 * @disregard
		 */
		$product = $item->get_product();
		if ( $product->is_type( 'variation' ) ) {
			$product = wc_get_product( $product->get_parent_id() );
		}

		// Get the Custom Fields.
		$custom_fields = $product->get_meta( '_extra_product_fields', true );

		// Get Values of the custom fields from meta _meta_extra_product_data.
		$item_meta_data = $item->get_meta( '_meta_extra_product_data', true );

		// Old Value as array.
		$item_meta_data = array_column( $item_meta_data, null, 'label' );
		$item_meta_data = array_combine(
			array_map(
				function ( $label ) {
					return strtolower( str_replace( array( ' ', '-' ), '_', $label ) );
				},
				array_keys( $item_meta_data )
			),
			$item_meta_data
		);

		if ( ! empty( $custom_fields ) ) {
			$field_meta = array();
			foreach ( $custom_fields as $field ) {

				// Get the field value from the $_POST array.
				$field_value = isset( $_POST['exprdawc_custom_field_input'][ strtolower( str_replace( array( ' ', '-' ), '_', $field['label'] ) ) ] ) // phpcs:ignore
				? $_POST['exprdawc_custom_field_input'][ strtolower( str_replace( array( ' ', '-' ), '_', $field['label'] ) ) ] // phpcs:ignore
				: '';

				// Sanitize the input.
				if ( is_array( $field_value ) ) {
					$field_value = array_map( 'sanitize_text_field', $field_value );
				} else {
					$field_value = sanitize_text_field( $field_value );
				}

				// Get Old Value.
				$old_value = isset( $item_meta_data[ strtolower( str_replace( array( ' ', '-' ), '_', $field['label'] ) ) ]['value'] )
				? $item_meta_data[ strtolower( str_replace( array( ' ', '-' ), '_', $field['label'] ) ) ]['value']
				: '';

				// Validate the input.
				if ( ! empty( $field['required'] ) && empty( $field_value ) ) {
					/* translators: %s is the field label. */
					wp_send_json_error( array( 'message' => sprintf( __( '%s is a required field.', 'extra-product-data-for-woocommerce' ), $field['label'] ) ) );
				}

				// Additional validation based on field type.
				switch ( $field['type'] ) {
					case 'email':
						if ( ! empty( $field_value ) && ! is_email( $field_value ) ) {
							/* translators: %s is the field value. */
							wp_send_json_error( array( 'message' => sprintf( __( '%s is not a valid email address.', 'extra-product-data-for-woocommerce' ), $field['label'] ) ) );
						}
						break;
					case 'number':
						if ( ! empty( $field_value ) && ! is_numeric( $field_value ) ) {
							/* translators: %s is the field value. */
							wp_send_json_error( array( 'message' => sprintf( __( '%s must be a number.', 'extra-product-data-for-woocommerce' ), $field['label'] ) ) );
						}
						break;
					case 'date':
						if ( ! empty( $field_value ) && ! strtotime( $field_value ) ) {
							/* translators: %s is the field value. */
							wp_send_json_error( array( 'message' => sprintf( __( '%s is not a valid date.', 'extra-product-data-for-woocommerce' ), $field['label'] ) ) );
						}
						break;
					case 'yes-no':
						if ( ! empty( $field_value ) && ! in_array( $field_value, array( 'yes', 'no' ), true ) ) {
							/* translators: %s is the field value. */
							wp_send_json_error( array( 'message' => sprintf( __( '%s must be either "Yes" or "No".', 'extra-product-data-for-woocommerce' ), $field['label'] ) ) );
						}
						break;
					case 'radio':
						$array_colum = array_column( $field['options'], 'value' );
						$intersect   = array_intersect( (array) $field_value, $array_colum );
						if ( ! empty( $field_value ) && empty( $intersect ) ) {
							/* translators: %s is the field label. */
							wp_send_json_error( array( 'message' => sprintf( __( '%s is not a valid option.', 'extra-product-data-for-woocommerce' ), $field['label'] ) ) );
						}
						break;
					case 'checkbox':
						$array_colum = array_column( $field['options'], 'value' );
						$intersect   = array_intersect( (array) $field_value, $array_colum );
						if ( ! empty( $field_value ) && empty( $intersect ) ) {
							/* translators: %s is the field label. */
							wp_send_json_error( array( 'message' => sprintf( __( '%s is not a valid option.', 'extra-product-data-for-woocommerce' ), $field['label'] ) ) );
						}
						break;
					case 'select':
						$array_colum = array_column( $field['options'], 'value' );
						$intersect   = array_intersect( (array) $field_value, $array_colum );
						if ( ! empty( $field_value ) && empty( $intersect ) ) {
							/* translators: %s is the field label. */
							wp_send_json_error( array( 'message' => sprintf( __( '%s is not a valid option.', 'extra-product-data-for-woocommerce' ), $field['label'] ) ) );
						}
						break;
					case 'long_text':
						$field_value = wp_kses_post( $field_value );
						break;
					case 'text':
					default:
						$field_value = sanitize_text_field( $field_value );
						break;
				}

				// Check for changes and add order note if there are any.
				if ( is_array( $field_value ) ) {
					$field_value_str = implode( ', ', $field_value );
					$old_value_str   = is_array( $old_value ) ? implode( ', ', $old_value ) : $old_value;
					if ( $field_value_str !== $old_value_str ) {
						/* translators: %1$s is the field label, %2$s is the old value, %3$s is the new value. */
						$order->add_order_note( sprintf( __( '%1$s changed from "%2$s" to "%3$s".', 'extra-product-data-for-woocommerce' ), $field['label'], $old_value_str, $field_value_str ) );
					}
				} elseif ( $field_value !== $old_value ) {
						/* translators: %1$s is the field label, %2$s is the old value, %3$s is the new value. */
						$order->add_order_note( sprintf( __( '%1$s changed from "%2$s" to "%3$s".', 'extra-product-data-for-woocommerce' ), $field['label'], $old_value, $field_value ) );
				}

				// if value are an array, than implode with comma.
				if ( is_array( $field_value ) ) {
					$field_value = implode( ', ', $field_value );
				}

				// Update the item meta data.
				$item->update_meta_data( $field['label'], $field_value );

				$field_meta[] = array(
					'label'     => sanitize_text_field( $field['label'] ),
					'value'     => sanitize_text_field( $field_value ),
					'raw_field' => $field,
				);
			}

			// Update the item meta data.
			$item->update_meta_data( '_meta_extra_product_data', $field_meta );
		}
	}

	/**
	 * Calculates the new price for the order item.
	 *
	 * @param WC_Order_Item $item The order item.
	 * @return float The new price for the item.
	 */
	protected function calculate_new_price( $item ) {

		/**
		 * Get the product data.
		 *
		 * @disregard
		 */
		$product = $item->get_product();
		if ( $product->is_type( 'variation' ) ) {
			$product = wc_get_product( $product->get_parent_id() );
		}

		$custom_fields = $product->get_meta( '_extra_product_fields', true );
		$extra_costs   = 0;

		if ( ! empty( $custom_fields ) ) {
			foreach ( $custom_fields as $field ) {
				// Get the field value from the $_POST array.
				$field_value = isset( $_POST['exprdawc_custom_field_input'][ strtolower( str_replace( array( ' ', '-' ), '_', $field['label'] ) ) ] ) // phpcs:ignore
				? $_POST['exprdawc_custom_field_input'][ strtolower( str_replace( array( ' ', '-' ), '_', $field['label'] ) ) ] // phpcs:ignore
				: '';

				// Sanitize the input.
				if ( is_array( $field_value ) ) {
					$field_value = array_map( 'sanitize_text_field', $field_value );
				} else {
					$field_value = sanitize_text_field( $field_value );
				}

				if ( ! empty( $field['adjust_price'] ) && ! empty( $field_value ) ) {
					$price_adjustment = $this->calculate_price_adjustment( $field, $field_value, $product );
					$extra_costs     += $price_adjustment;
				}
			}
		}

		return $product->get_price() + $extra_costs;
	}

	/**
	 * Get the adjustment value based on field options.
	 *
	 * @param array      $field Field configuration.
	 * @param WC_Product $product Product object.
	 * @return float Adjustment value.
	 */
	protected function get_adjustment_value( $field, $product ) {
		if ( 'fixed' === $field['price_adjustment_type'] ) {
			return floatval( $field['price_adjustment_value'] );
		} elseif ( 'percentage' === $field['price_adjustment_type'] ) {
			return ( $product->get_price() / 100 ) * floatval( $field['price_adjustment_value'] );
		}
		return 0;
	}

	/**
	 * Calculate price adjustment for a field.
	 *
	 * @param array      $field Field configuration.
	 * @param string     $field_value Field value.
	 * @param WC_Product $product Product object.
	 * @return float Price adjustment value.
	 */
	protected function calculate_price_adjustment( $field, $field_value, $product ) {
		$adjustment = 0;
		if ( in_array( $field['type'], array( 'checkbox', 'radio', 'select' ), true ) ) {
			foreach ( $field['options'] as $option ) {
				if ( is_array( $field_value ) && in_array( $option['value'], $field_value, true ) ) {
					$adjustment += $this->get_adjustment_value( $option, $product );
				} elseif ( $option['value'] === $field_value ) {
					$adjustment += $this->get_adjustment_value( $option, $product );
					break;
				}
			}
		} else {
			$adjustment = $this->get_adjustment_value( $field, $product );
		}
		return $adjustment;
	}
}
