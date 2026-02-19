<?php
/**
 * Created on Fri Nov 01 2024
 *
 * Copyright (c) 2024 IT-Dienstleistungen Drevermann - All Rights Reserved
 *
 * @package Extra Product Data for WooCommerce
 * @author Daniel Drevermann <info@triopsi.com>
 * @copyright (c) 2024, IT-Dienstleistungen Drevermann
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
 * Class Exprdawc_User_Order
 *
 * This class is responsible for the user order.
 *
 * @package Exprdawc
 */
class Exprdawc_User_Order extends Exprdawc_Base_Order_Class {

	/**
	 * Exprdawc_User_Order constructor.
	 */
	public function __construct() {
		add_action( 'woocommerce_order_item_meta_end', array( $this, 'add_edit_button_to_order_item' ), 10, 3 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'wp_ajax_exprdawc_save_order_item_meta', array( $this, 'save_order_item_meta' ) );
	}

	/**
	 * Enqueue scripts and styles.
	 */
	public function enqueue_scripts() {
		if ( is_account_page() ) {
			wp_enqueue_style( 'form-css', EXPRDAWC_ASSETS_CSS . 'forms.css', array(), '1.0.0', 'all' );
			wp_enqueue_style( 'order-frontend-css', EXPRDAWC_ASSETS_CSS . 'order-frontend.css', array(), '1.0.0', 'all' );
			wp_enqueue_script( 'exprdawc-user-order', EXPRDAWC_ASSETS_JS . 'wc-user-order.min.js', array( 'jquery' ), '1.0.0', true );
			wp_localize_script(
				'exprdawc-user-order',
				'exprdawc_user_order',
				array(
					'ajax_url'        => admin_url( 'admin-ajax.php' ),
					'nonce'           => wp_create_nonce( 'exprdawc_save_order_item_meta' ),
					'error_message'   => esc_html__( 'An error occurred. Please try again.', 'extra-product-data-for-woocommerce' ),
					'success_message' => esc_html__( 'Successfully saved.', 'extra-product-data-for-woocommerce' ),
				)
			);
		}
	}

	/**
	 * Add edit button to order item.
	 *
	 * @param int    $item_id The item ID.
	 * @param object $item The item object.
	 * @param object $order The order object.
	 */
	public function add_edit_button_to_order_item( $item_id, $item, $order ) {
		// Get the Product. If this type variation get the parent id of the product.
		$product = $item->get_product();
		if ( $product->is_type( 'variation' ) ) {
			$product = wc_get_product( $product->get_parent_id() );
		}

		$custom_fields = $product->get_meta( '_extra_product_fields', true );
		if ( empty( $custom_fields ) ) {
			return;
		}

		$max_order_status = get_option( 'extra_product_data_max_order_status', 'processing' );

		// Get item meta data and initialize flag.
		$item_meta_data  = $item->get_meta_data();
		$has_user_inputs = false;

		// Check if item meta data and product user meta are not empty.
		if ( ! empty( $item_meta_data ) && ! empty( $custom_fields ) ) {
			$all_user_inputs = array();

			// Loop through item meta data and store in mail template data array.
			foreach ( $item_meta_data as $meta ) {
				if ( isset( $meta->key ) && ! empty( $meta->value ) ) {
					$all_user_inputs[ $meta->key ] = $meta;
				}
			}

			$post_data_product_item = array();
			foreach ( $custom_fields as $index => $input_field_array ) {
				$label_id                     = strtolower( str_replace( ' ', '_', $input_field_array['label'] ) );
				$post_data_product_item[ $label_id ] = isset( $all_user_inputs[ $input_field_array['label'] ] ) ? $all_user_inputs[ $input_field_array['label'] ]->value : '';
			}

			// Check if any label user data matches with product user meta.
			foreach ( $all_user_inputs as $label_key => $user_data_value ) {
				foreach ( $custom_fields as $html_value ) {
					if ( 0 === strcasecmp( $html_value['label'], $label_key ) ) {
						$has_user_inputs = true;
						break;
					}
				}
			}
			// Display edit ticket section if on view-order endpoint.
			if ( is_wc_endpoint_url( 'view-order' ) ) {
				if ( $has_user_inputs && $order->has_status( OrderUtil::remove_status_prefix( $max_order_status ) ) ) {
					echo '<button type="button" class="button alt wp-element-button exprdawc-edit-user-order-button exprdawc-edit-order-item" data-item-id="' . esc_attr( $item_id ) . '"><span class="dashicons dashicons-edit"></span> ' . esc_html__( 'Edit', 'extra-product-data-for-woocommerce' ) . '</button>';
					echo '<div class="exprdawc-order-item-fields" id="exprdawc-order-item-fields-' . esc_attr( $item_id ) . '" style="display:none;">';
					echo '<form action="" method="post" class="exprdawc-order-item-form">';
					echo '<input type="hidden" name="order_id" value="' . esc_attr( $order->get_id() ) . '">';
					foreach ( $custom_fields as $field ) {
						$value = isset( $post_data_product_item[ strtolower( str_replace( ' ', '_', $field['label'] ) ) ] ) ? $post_data_product_item[ strtolower( str_replace( ' ', '_', $field['label'] ) ) ] : '';
						Exprdawc_Helper::generate_input_field( $field, $value );
					}
					echo '</form>';
					echo '<button style="margin-top: 1em;" type="button" class="button alt wp-element-button exprdawc-save-order-item" data-item-id="' . esc_attr( $item_id ) . '"><span class="dashicons dashicons-yes"></span> ' . esc_html__( 'Save', 'extra-product-data-for-woocommerce' ) . '</button>';
					echo '</div>';
				}
			}
		}
	}

	/**
	 * Save order item meta.
	 */
	public function save_order_item_meta_old() {
		check_ajax_referer( 'exprdawc_save_order_item_meta', 'security' );

		$max_order_status = get_option( 'extra_product_data_max_order_status', 'processing' );

		// Get the necessary parameters.
		$item_id  = isset( $_POST['item_id'] ) ? intval( $_POST['item_id'] ) : 0;
		$order_id = isset( $_POST['order_id'] ) ? intval( $_POST['order_id'] ) : 0;

		// Check if the parameters are valid.
		if ( ! $item_id || ! $order_id ) {
			wp_send_json_error( array( 'message' => __( 'Invalid order or item ID.', 'extra-product-data-for-woocommerce' ) ) );
		}

		// Get the order and item.
		$order = wc_get_order( $order_id );

		if ( ! $order || ! ( $order instanceof WC_Order ) ) {
			wp_send_json_error( array( 'message' => __( 'Order not found.', 'extra-product-data-for-woocommerce' ) ) );
		}

		if ( ! $order->has_status( OrderUtil::remove_status_prefix( $max_order_status ) ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have permission to edit this order.', 'extra-product-data-for-woocommerce' ) ) );
		}

		$item = $order->get_item( $item_id );

		if ( ! $item || ! ( $item instanceof WC_Order_Item ) ) {
			wp_send_json_error( array( 'message' => __( 'Item not found.', 'extra-product-data-for-woocommerce' ) ) );
		}

		// Get the product data.
		$product = $item->get_product();
		if ( $product->is_type( 'variation' ) ) {
			$product = wc_get_product( $product->get_parent_id() );
		}
		$custom_fields = $product->get_meta( '_extra_product_fields', true );

		// Check if the product and custom fields are valid.
		if ( ! $product || ! ( $product instanceof WC_Product ) ) {
			wp_send_json_error( array( 'message' => __( 'Product not found.', 'extra-product-data-for-woocommerce' ) ) );
		}

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

		$post_data_product_item = array();
		$extra_costs     = 0;
		foreach ( $custom_fields as $index => $input_field_array ) {
			if ( ! $input_field_array['editable'] ) {
				continue;
			}
			// Actual label lowercase and without spaces and _ are -.
			$index = strtolower( str_replace( array( ' ', '-' ), '_', $input_field_array['label'] ) );

			// Get the field value from the $_POST array.
			$field_value = isset( $_POST['exprdawc_custom_field_input'][ $index ] ) ? $_POST['exprdawc_custom_field_input'][ $index ] : ''; // phpcs:ignore

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

			$old_value = isset( $item_meta_data[ $index ]['value'] ) ? $item_meta_data[ $index ]['value'] : '';

			// Validate the input.
			if ( ! empty( $field_value ) ) {
				// Handle different field types.
				if ( is_array( $field_value ) ) {
					$field_value = array_map( 'sanitize_text_field', $field_value );
				} else {
					$field_value = sanitize_text_field( $field_value );
				}
			}

			if ( ! empty( $input_field_array['required'] ) && empty( $field_value ) ) {
				/* translators: %s is the field label. */
				wp_send_json_error( array( 'message' => sprintf( __( '%s is a required field.', 'extra-product-data-for-woocommerce' ), $input_field_array['label'] ) ) );
			}

			// Additional validation based on field type.
			switch ( $input_field_array['type'] ) {
				case 'email':
					if ( ! empty( $field_value ) && ! is_email( $field_value ) ) {
						/* translators: %s is the field label. */
						wp_send_json_error( array( 'message' => sprintf( __( '%s is not a valid email address.', 'extra-product-data-for-woocommerce' ), $input_field_array['label'] ) ) );
					}
					break;
				case 'number':
					if ( ! empty( $field_value ) && ! is_numeric( $field_value ) ) {
						/* translators: %s is the field label. */
						wp_send_json_error( array( 'message' => sprintf( __( '%s must be a number.', 'extra-product-data-for-woocommerce' ), $input_field_array['label'] ) ) );
					}
					break;
				case 'date':
					if ( ! empty( $field_value ) && ! strtotime( $field_value ) ) {
						/* translators: %s is the field label. */
						wp_send_json_error( array( 'message' => sprintf( __( '%s is not a valid date.', 'extra-product-data-for-woocommerce' ), $input_field_array['label'] ) ) );
					}
					break;
				case 'yes-no':
					if ( ! empty( $field_value ) && ! in_array( $field_value, array( 'yes', 'no' ), true ) ) {
						/* translators: %s is the field label. */
						wp_send_json_error( array( 'message' => sprintf( __( '%s must be either "Yes" or "No".', 'extra-product-data-for-woocommerce' ), $input_field_array['label'] ) ) );
					}
					break;
				case 'radio':
					$array_colum = array_column( $input_field_array['options'], 'value' );
					$intersect   = array_intersect( (array) $field_value, $array_colum );
					if ( ! empty( $field_value ) && empty( $intersect ) ) {
						/* translators: %s is the field label. */
						wp_send_json_error( array( 'message' => sprintf( __( '%s is not a valid option.', 'extra-product-data-for-woocommerce' ), $input_field_array['label'] ) ) );
					}
					break;
				case 'checkbox':
					$array_colum = array_column( $input_field_array['options'], 'value' );
					$intersect   = array_intersect( (array) $field_value, $array_colum );
					if ( ! empty( $field_value ) && empty( $intersect ) ) {
						/* translators: %s is the field label. */
						wp_send_json_error( array( 'message' => sprintf( __( '%s is not a valid option.', 'extra-product-data-for-woocommerce' ), $input_field_array['label'] ) ) );
					}
					break;
				case 'select':
					$array_colum = array_column( $input_field_array['options'], 'value' );
					$intersect   = array_intersect( (array) $field_value, $array_colum );
					if ( ! empty( $field_value ) && empty( $intersect ) ) {
						/* translators: %s is the field label. */
						wp_send_json_error( array( 'message' => sprintf( __( '%s is not a valid option.', 'extra-product-data-for-woocommerce' ), $input_field_array['label'] ) ) );
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
					$order->add_order_note( sprintf( __( '%1$s changed from "%2$s" to "%3$s".', 'extra-product-data-for-woocommerce' ), $input_field_array['label'], $old_value_str, $field_value_str ) );
				}
			} elseif ( $field_value !== $old_value ) {
					/* translators: %1$s is the field label, %2$s is the old value, %3$s is the new value. */
					$order->add_order_note( sprintf( __( '%1$s changed from "%2$s" to "%3$s".', 'extra-product-data-for-woocommerce' ), $input_field_array['label'], $old_value, $field_value ) );
			}

			// if value are an array, than implode with comma.
			if ( is_array( $field_value ) ) {
				$field_value = implode( ', ', $field_value );
			}

			if ( $input_field_array['adjust_price'] ) {
				$price_adjustment = 0;
				if ( $input_field_array['adjust_price'] ) {
					if ( in_array( $input_field_array['type'], array( 'checkbox', 'radio', 'select' ), true ) ) {
						$total_adjustment = 0;
						foreach ( $input_field_array['options'] as $option ) {
							$cart_value = explode( ', ', $field_value );
							if ( is_array( $cart_value ) && in_array( $option['value'], $cart_value, true ) ) {
								if ( 'fixed' === $option['price_adjustment_type'] ) {
									$total_adjustment += $option['price_adjustment_value'];
								} elseif ( 'percentage' === $option['price_adjustment_type'] ) {
									$total_adjustment += ( $product->get_price() / 100 ) * $option['price_adjustment_value'];
								}
							} elseif ( $field_value === $option['value'] ) {
								if ( 'fixed' === $option['price_adjustment_type'] ) {
									$total_adjustment = $option['price_adjustment_value'];
								} elseif ( 'percentage' === $option['price_adjustment_type'] ) {
									$total_adjustment = ( $product->get_price() / 100 ) * $option['price_adjustment_value'];
								}
								break;
							}
						}
						$price_adjustment = $total_adjustment;
					} elseif ( 'fixed' === $input_field_array['price_adjustment_type'] ) {
						$price_adjustment = $input_field_array['price_adjustment_value'];
					} elseif ( 'percentage' === $input_field_array['price_adjustment_type'] ) {
						$price_adjustment = ( $product->get_price() / 100 ) * $input_field_array['price_adjustment_value'];
					}
				}

				// Update the item price.
				$product_preice = $product->get_price();

				$new_price = $product_preice + $extra_costs + $price_adjustment;

				$item['subtotal'] = $new_price * $item->get_quantity();

				$item['total'] = $new_price * $item->get_quantity();

				$extra_costs += $price_adjustment;
			}

			// Update the item meta data.
			$item->update_meta_data( $input_field_array['label'], $field_value );
		}

		// Save item meta data.
		$item->save_meta_data();

		// Save the item.
		$item->save();

		// Recalculate the order totals.
		$new_order_price = $order->calculate_totals();

		// Save Order.
		$order->save();

		$response = array(
			'message' => __( 'Item updated successfully.', 'extra-product-data-for-woocommerce' ),
		);

		// Send the response back.
		wp_send_json_success( $response );
	}

	/**
	 * Save order item meta.
	 */
	public function save_order_item_meta() {
		// Check permissions and nonce.
		check_ajax_referer( 'exprdawc_save_order_item_meta', 'security' );

		// Process Data Save.
		$this->process_save_order();

		wp_send_json_success(
			array(
				'message' => __( 'Item updated successfully.', 'extra-product-data-for-woocommerce' ),
			)
		);
	}
}
