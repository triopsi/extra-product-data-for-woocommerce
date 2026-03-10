<?php
/**
 * Customer Order Handler
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

namespace Triopsi\Exprdawc\Orders\Customer;

use Triopsi\Exprdawc\Helpers\Helper;
use Triopsi\Exprdawc\Contracts\Hookable;
use Triopsi\Exprdawc\Orders\BaseOrder;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Customer Order Handler
 *
 * Handles customer-facing order functionality.
 */
class CustomerOrder extends BaseOrder implements Hookable {

	/**
	 * CustomerOrder constructor.
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
			wp_enqueue_style( 'form-css', EXPRDAWC_ASSETS_CSS . 'forms.css', array(), EXPRDAWC_VERSION, 'all' );
			wp_enqueue_style( 'order-frontend-css', EXPRDAWC_ASSETS_CSS . 'order-frontend.css', array(), EXPRDAWC_VERSION, 'all' );
			wp_enqueue_script( 'exprdawc-user-order', EXPRDAWC_ASSETS_JS . 'wc-user-order.min.js', array( 'jquery' ), EXPRDAWC_VERSION, true );
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
		$product = $item->get_product();
		if ( $product->is_type( 'variation' ) ) {
			$product = wc_get_product( $product->get_parent_id() );
		}

		$custom_fields = $product->get_meta( '_extra_product_fields', true );
		if ( empty( $custom_fields ) ) {
			return;
		}

		$item_meta_data  = $item->get_meta_data();
		$has_user_inputs = false;

		if ( ! empty( $item_meta_data ) && ! empty( $custom_fields ) ) {
			$all_user_inputs = array();

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

			foreach ( $all_user_inputs as $label_key => $user_data_value ) {
				foreach ( $custom_fields as $html_value ) {
					if ( 0 === strcasecmp( $html_value['label'], $label_key ) ) {
						$has_user_inputs = true;
						break;
					}
				}
			}

			if ( is_wc_endpoint_url( 'view-order' ) ) {
				if ( $has_user_inputs && Helper::is_order_editable( $order ) ) {
					echo '<button type="button" class="button alt wp-element-button exprdawc-edit-user-order-button exprdawc-edit-order-item" data-item-id="' . esc_attr( $item_id ) . '"><span class="dashicons dashicons-edit"></span> ' . esc_html__( 'Edit', 'extra-product-data-for-woocommerce' ) . '</button>';
					echo '<div class="exprdawc-order-item-fields" id="exprdawc-order-item-fields-' . esc_attr( $item_id ) . '" style="display:none;">';
					echo '<form action="" method="post" class="exprdawc-order-item-form">';
					echo '<input type="hidden" name="order_id" value="' . esc_attr( $order->get_id() ) . '">';
					foreach ( $custom_fields as $field ) {
						$value = isset( $post_data_product_item[ strtolower( str_replace( ' ', '_', $field['label'] ) ) ] ) ? $post_data_product_item[ strtolower( str_replace( ' ', '_', $field['label'] ) ) ] : '';
						Helper::generateInputField( $field, $value );
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
	public function save_order_item_meta() {
		check_ajax_referer( 'exprdawc_save_order_item_meta', 'security' );

		if ( $this->process_save_order() ) {
			wp_send_json_success(
				array(
					'message' => __( 'Item updated successfully.', 'extra-product-data-for-woocommerce' ),
				)
			);
		} else {
			wp_send_json_error(
				array(
					'message' => __( 'Failed to update item.', 'extra-product-data-for-woocommerce' ),
				)
			);
		}
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
