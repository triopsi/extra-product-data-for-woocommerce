<?php
/**
 * Product Backend Handler
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

namespace Triopsi\Exprdawc\Backend;

use Triopsi\Exprdawc\Contracts\Hookable;
use Triopsi\Exprdawc\Helpers\Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Product Backend Handler
 *
 * Handles product backend functionality and custom fields in admin.
 */
class ProductBackend implements Hookable {

	/**
	 * Contructor.
	 */
	public function __construct() {

		if ( is_admin() ) {
			add_action( 'add_meta_boxes', array( $this, 'exprdawc_add_custom_meta_box' ) );

			add_action( 'woocommerce_process_product_meta', array( $this, 'exprdawc_save_extra_product_fields' ) );

			add_action( 'admin_enqueue_scripts', array( $this, 'exprdawc_show_general_tab' ) );

			add_action( 'wp_ajax_exprdawc_import_custom_fields', array( $this, 'exprdawc_import_custom_fields' ) );
		}
	}

	/**
	 * Add a custom meta box in the product edit page.
	 */
	public function exprdawc_add_custom_meta_box(): void {
		add_meta_box(
			'exprdawc_extra_product_fields',
			__( 'Extra Product Input', 'extra-product-data-for-woocommerce' ),
			array( $this, 'exprdawc_render_custom_meta_box' ),
			'product',
			'normal',
			'default'
		);
	}

	/**
	 * Render the custom meta box content.
	 *
	 * @param \WP_Post $post The post object.
	 */
	public function exprdawc_render_custom_meta_box( \WP_Post $post ): void {
		$post_id = (int) $post->ID;
		if ( $post_id <= 0 ) {
			return;
		}

		echo $this->get_custom_product_fields_panel_html( $post_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Get the HTML for the custom product fields panel.
	 *
	 * @param int $product_id The ID of the product for which to get the panel HTML.
	 * @return string The HTML for the custom product fields panel.
	 */
	public function get_custom_product_fields_panel_html( int $product_id ): string {
		$product = wc_get_product( $product_id );
		if ( ! $product ) {
			return '';
		}

		$custom_fields = $product->get_meta( '_extra_product_fields', true );
		if ( ! is_array( $custom_fields ) ) {
			$custom_fields = array();
		}

		ob_start();
		Helper::renderTemplate(
			'html-tab-extra-attributes.php',
			array(
				'product'       => $product,
				'custom_fields' => $custom_fields,
				'product_id'    => $product_id,
			)
		);
		return (string) ob_get_clean();
	}

	/**
	 * Enqueue scripts for the general tab.
	 *
	 * @return void
	 */
	public function exprdawc_show_general_tab() {
		wp_enqueue_script( 'exprdawc-wc-meta-boxes-js', EXPRDAWC_ASSETS_JS . 'wc-meta-boxes-product.min.js', array( 'jquery', 'jquery-ui-sortable' ), EXPRDAWC_VERSION, true );

		wp_enqueue_style( 'exprdawc-import-export-modal-css', EXPRDAWC_ASSETS_CSS . 'import-export-modal.css', array(), EXPRDAWC_VERSION );
		wp_enqueue_script( 'exprdawc-import-export-modal-js', EXPRDAWC_ASSETS_JS . 'import-export-modal.min.js', array( 'jquery' ), EXPRDAWC_VERSION, true );

		wp_localize_script(
			'exprdawc-wc-meta-boxes-js',
			'exprdawc_admin_meta_boxes',
			array(
				'confirm_delete'                       => esc_html__( 'Are you sure you want to delete this field?', 'extra-product-data-for-woocommerce' ),
				'confirm_delete_rule'                  => esc_html__( 'Are you sure you want to delete this rule?', 'extra-product-data-for-woocommerce' ),
				'selectFieldNone'                      => esc_html__( 'None', 'extra-product-data-for-woocommerce' ),
				'sureAnotherAutocompleCheckedQuestion' => esc_html__( 'Another autocomplete field is already checked. Do you want to uncheck it?', 'extra-product-data-for-woocommerce' ),
				'validation_warning'                   => esc_html__( 'Warning! No label text (Labels) was found. Please fill all fields with label text before saving.', 'extra-product-data-for-woocommerce' ),
			)
		);
	}

	/**
	 * Saves custom product fields for a WooCommerce product.
	 *
	 * @param int $post_id The ID of the product being saved.
	 */
	public function exprdawc_save_extra_product_fields( $post_id ) {
		if ( isset( $_POST['extra_product_fields'] ) ) { // phpcs:ignore
			$product              = wc_get_product( $post_id );
			$extra_product_fields = wp_unslash( $_POST['extra_product_fields'] ); // phpcs:ignore

			$custom_fields = array_map(
				function ( $field ) {
					$label                 = sanitize_text_field( $field['label'] );
					$type                  = sanitize_text_field( $field['type'] );
					$required              = isset( $field['required'] ) ? 1 : 0;
					$conditional_logic     = isset( $field['conditional_logic'] ) ? 1 : 0;
					$placeholder_text      = sanitize_text_field( $field['placeholder_text'] );
					$help_text             = sanitize_text_field( $field['help_text'] );
					$autocomplete          = isset( $field['autocomplete'] ) ? sanitize_text_field( $field['autocomplete'] ) : '';
					$autofocus             = isset( $field['autofocus'] ) ? true : false;
					$index                 = isset( $field['index'] ) ? absint( $field['index'] ) : 0;
					$editable              = isset( $field['editable'] ) ? true : false;
					$adjust_price          = isset( $field['adjust_price'] ) ? true : false;
					$price_adjustment_type = sanitize_text_field( $field['price_adjustment_type'] );
					$priceAdjustmentValue  = sanitize_text_field( $field['priceAdjustmentValue'] );

					if ( isset( $field['conditional_rules'] ) ) {
						foreach ( $field['conditional_rules'] as $rule_group ) {
							foreach ( $rule_group as $rule ) {
								$rule['field']    = sanitize_text_field( $rule['field'] );
								$rule['operator'] = sanitize_text_field( $rule['operator'] );
								$rule['value']    = sanitize_text_field( $rule['value'] );
							}
						}
						$conditional_logic_rules = $field['conditional_rules'];
					} else {
						$conditional_logic_rules = array();
						$conditional_logic       = 0;
					}

					$options = array();
					if ( isset( $field['options'] ) && is_array( $field['options'] ) ) {
						foreach ( $field['options'] as $key => $option ) {
							$options[] = array(
								'label'                 => ! empty( $option['label'] ) ? sanitize_text_field( $option['label'] ) : 'Option ' . $key,
								'value'                 => ! empty( $option['value'] ) ? sanitize_text_field( $option['value'] ) : sanitize_text_field( $option['label'] ),
								'price_adjustment_type' => isset( $option['price_adjustment_type'] ) ? sanitize_text_field( $option['price_adjustment_type'] ) : '',
								'priceAdjustmentValue'  => isset( $option['priceAdjustmentValue'] ) ? sanitize_text_field( $option['priceAdjustmentValue'] ) : '',
								'default'               => isset( $option['default'] ) ? sanitize_text_field( $option['default'] ) : 0,
							);
						}
					}

					// Set default value, ensuring it's properly sanitized and of the correct type.
					$default_source = $field['default'] ?? '';
					if ( is_array( $default_source ) ) {
						$default = array_map( 'sanitize_text_field', $default_source );
					} else {
						$default = sanitize_text_field( $default_source );
					}

					$long_text_default = isset( $field['long_text_default'] ) ? sanitize_textarea_field( $field['long_text_default'] ) : '';
					if ( ! empty( $long_text_default ) ) {
						$default = $long_text_default;
					}

					$minlength = isset( $field['minlength'] ) ? absint( $field['minlength'] ) : 0;
					$maxlength = isset( $field['maxlength'] ) ? absint( $field['maxlength'] ) : 0;
					$rows      = isset( $field['rows'] ) ? absint( $field['rows'] ) : 0;
					$cols      = isset( $field['cols'] ) ? absint( $field['cols'] ) : 0;

					if ( empty( $label ) || ! is_string( $label ) ) {
						return;
					}
					if ( ! is_numeric( $required ) ) {
						$required = 0;
					}
					if ( ! is_string( $placeholder_text ) ) {
						$placeholder_text = '';
					}
					if ( ! is_string( $help_text ) ) {
						$help_text = '';
					}
					return array(
						'label'                 => $label,
						'type'                  => $type,
						'required'              => $required,
						'conditional_logic'     => $conditional_logic,
						'placeholder_text'      => $placeholder_text,
						'help_text'             => $help_text,
						'options'               => $options,
						'default'               => $default,
						'long_text_default'     => $long_text_default,
						'minlength'             => $minlength,
						'maxlength'             => $maxlength,
						'rows'                  => $rows,
						'cols'                  => $cols,
						'autocomplete'          => $autocomplete,
						'autofocus'             => $autofocus,
						'conditional_rules'     => $conditional_logic_rules,
						'index'                 => $index,
						'editable'              => $editable,
						'adjust_price'          => $adjust_price,
						'price_adjustment_type' => $price_adjustment_type,
						'priceAdjustmentValue'  => $priceAdjustmentValue,
					);
				},
				$extra_product_fields
			);

			$custom_fields = array_filter( $custom_fields );

			$product->update_meta_data( '_extra_product_fields', $custom_fields );
		} else {
			$product = wc_get_product( $post_id );
			$product->delete_meta_data( '_extra_product_fields' );
		}

		$product->save();
	}

	/**
	 * Import custom fields.
	 */
	public function exprdawc_import_custom_fields() {
		check_ajax_referer( 'edit_exprdawc_nonce', 'security' );

		if ( ! current_user_can( 'edit_product', $_POST['product_id'] ) ) { // phpcs:ignore
			wp_send_json_error( 'You do not have permission to edit this product.' );
		}

		$product_id = intval( $_POST['product_id'] ); // phpcs:ignore

		if ( 0 === $product_id ) {
			wp_send_json_error( 'Invalid product ID.' );
		}

		$product = wc_get_product( $product_id );
		if ( ! $product ) {
			wp_send_json_error( 'Invalid product ID.' );
		}

		$export_string = wp_unslash( $_POST['export_string'] ); // phpcs:ignore
		$custom_fields = json_decode( $export_string, true );

		if ( json_last_error() !== JSON_ERROR_NONE ) {
			wp_send_json_error( 'Invalid JSON string.' );
		}

		$product->update_meta_data( '_extra_product_fields', $custom_fields );
		$product->save();

		wp_send_json_success();
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
