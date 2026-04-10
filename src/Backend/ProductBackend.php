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
			add_action( 'add_meta_boxes', array( $this, 'exprdawcAddCustomMetaBox' ) );

			add_action( 'woocommerce_process_product_meta', array( $this, 'exprdawcSaveExtraProductFields' ) );

			add_action( 'admin_enqueue_scripts', array( $this, 'exprdawcShowGeneralTab' ) );

			add_action( 'wp_ajax_exprdawc_import_custom_fields', array( $this, 'exprdawcImportCustomFields' ) );
		}
	}

	/**
	 * Add a custom meta box in the product edit page.
	 */
	public function exprdawcAddCustomMetaBox(): void {
		add_meta_box(
			'exprdawc_extra_product_fields',
			__( 'Extra Product Input', 'extra-product-data-for-woocommerce' ),
			array( $this, 'exprdawcRenderCustomMetaBox' ),
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
	public function exprdawcRenderCustomMetaBox( \WP_Post $post ): void {
		$post_id = (int) $post->ID;
		if ( $post_id <= 0 ) {
			return;
		}

		echo $this->getCustomProductFieldsPanelHtml( $post_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Get the HTML for the custom product fields panel.
	 *
	 * @param int $product_id The ID of the product for which to get the panel HTML.
	 * @return string The HTML for the custom product fields panel.
	 */
	public function getCustomProductFieldsPanelHtml( int $product_id ): string {
		$product = wc_get_product( $product_id );
		if ( ! $product ) {
			return '';
		}

		$custom_fields = Helper::getExtraProductFields( $product );

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
	public function exprdawcShowGeneralTab() {
		wp_enqueue_script( 'exprdawc-wc-meta-boxes-js', EXPRDAWC_ASSETS_JS . 'wc-meta-boxes-product.min.js', array( 'jquery', 'jquery-ui-sortable' ), EXPRDAWC_VERSION, true );

		wp_enqueue_style( 'exprdawc-import-export-modal-css', EXPRDAWC_ASSETS_CSS . 'import-export-modal.css', array(), EXPRDAWC_VERSION );
		wp_enqueue_script( 'exprdawc-import-export-modal-js', EXPRDAWC_ASSETS_JS . 'import-export-modal.min.js', array( 'jquery' ), EXPRDAWC_VERSION, true );

		wp_localize_script(
			'exprdawc-wc-meta-boxes-js',
			'exprdawc_admin_meta_boxes',
			// phpcs:disable WordPress.Arrays.MultipleStatementAlignment.DoubleArrowNotAligned
			array(
				'edit_exprdawc_nonce'                        => wp_create_nonce( 'edit_exprdawc_nonce' ),
				'confirm_delete'                             => esc_html__( 'Are you sure you want to delete this field?', 'extra-product-data-for-woocommerce' ),
				'confirm_delete_rule'                        => esc_html__( 'Are you sure you want to delete this rule?', 'extra-product-data-for-woocommerce' ),
				'selectFieldNone'                            => esc_html__( 'None', 'extra-product-data-for-woocommerce' ),
				'sureAnotherAutocompleCheckedQuestion'       => esc_html__( 'Another autocomplete field is already checked. Do you want to uncheck it?', 'extra-product-data-for-woocommerce' ),
				'validation_warning'                         => esc_html__( 'Warning! No label text (Labels) was found. Please fill all fields with label text before saving.', 'extra-product-data-for-woocommerce' ),
				'validation_unique_warning'                  => esc_html__( 'Warning! Label names must be unique. Please use different label names before saving.', 'extra-product-data-for-woocommerce' ),
				'validation_unique_warning_inline'           => esc_html__( 'Label must be unique.', 'extra-product-data-for-woocommerce' ),
				'validation_option_unique_warning'           => esc_html__( 'Warning! Option values within one field must be unique. Please use different option values before saving.', 'extra-product-data-for-woocommerce' ),
				'validation_option_unique_warning_inline'    => esc_html__( 'Option value must be unique.', 'extra-product-data-for-woocommerce' ),
				'confirm_change_type_delete_options'         => esc_html__( 'Changing the field type will delete all options for this field. Do you want to proceed?', 'extra-product-data-for-woocommerce' ),
			)
			// phpcs:enable WordPress.Arrays.MultipleStatementAlignment.DoubleArrowNotAligned
		);
	}

	/**
	 * Saves custom product fields for a WooCommerce product.
	 *
	 * @param int $post_id The ID of the product being saved.
	 */
	public function exprdawcSaveExtraProductFields( $post_id ) {
		$product = wc_get_product( $post_id );
		if ( ! $product ) {
			return;
		}

		if ( isset( $_POST[EXPRDAWC_POST_KEY_EXTRA_PRODUCT_FIELDS] ) ) { // phpcs:ignore
			$extra_product_fields = wp_unslash( $_POST[EXPRDAWC_POST_KEY_EXTRA_PRODUCT_FIELDS] ); // phpcs:ignore

			if ( ! is_array( $extra_product_fields ) ) {
				$extra_product_fields = array();
			}

			$custom_fields = array_values(
				array_filter(
					array_map( array( $this, 'sanitizeCustomFieldForSave' ), $extra_product_fields )
				)
			);

			// Checks for duplicate labels and option values before saving. If duplicates are found, an error message is added and saving is aborted.
			if ( $this->hasDuplicateLabels( $custom_fields ) ) {
				if ( class_exists( 'WC_Admin_Meta_Boxes' ) ) {
					\WC_Admin_Meta_Boxes::add_error( esc_html__( 'Label names must be unique. Please use different label names before saving.', 'extra-product-data-for-woocommerce' ) );
				}
				return;
			}

			if ( $this->hasDuplicateOptionValues( $custom_fields ) ) {
				if ( class_exists( 'WC_Admin_Meta_Boxes' ) ) {
					\WC_Admin_Meta_Boxes::add_error( esc_html__( 'Option values within one field must be unique. Please use different option values before saving.', 'extra-product-data-for-woocommerce' ) );
				}
				return;
			}

			// Save custom fields to product meta. If no custom fields are provided, delete the meta to keep the database clean.
			$product->update_meta_data( EXPRDAWC_PRODUCT_META_EXTRA_PRODUCT_DATA, $custom_fields );
		} else {
			$product->delete_meta_data( EXPRDAWC_PRODUCT_META_EXTRA_PRODUCT_DATA );
		}

		$product->save();
	}

	/**
	 * Sanitize one custom field payload for storage.
	 *
	 * @param mixed $field Raw field payload from request.
	 * @return array<string, mixed>|null
	 */
	private function sanitizeCustomFieldForSave( $field ): ?array {
		if ( ! is_array( $field ) ) {
			return null;
		}

		$id                    = sanitize_text_field( $field['id'] ?? '' );
		$label                 = sanitize_text_field( $field['label'] ?? '' );
		$type                  = sanitize_text_field( $field['type'] ?? '' );
		$required              = isset( $field['required'] ) ? 1 : 0;
		$conditional_logic     = isset( $field['conditional_logic'] ) ? 1 : 0;
		$placeholder_text      = sanitize_text_field( $field['placeholder_text'] ?? '' );
		$help_text             = sanitize_text_field( $field['help_text'] ?? '' );
		$autocomplete          = isset( $field['autocomplete'] ) ? sanitize_text_field( $field['autocomplete'] ) : '';
		$autofocus             = isset( $field['autofocus'] );
		$index                 = isset( $field['index'] ) ? absint( $field['index'] ) : 0;
		$editable              = isset( $field['editable'] );
		$adjust_price          = isset( $field['adjust_price'] );
		$price_adjustment_type = sanitize_text_field( $field['price_adjustment_type'] ?? '' );
		$price_adjustment      = sanitize_text_field( $field['priceAdjustmentValue'] ?? '' );
		$disabled              = isset( $field['disabled'] ) ? absint( $field['disabled'] ) : 0;
		$css_class             = isset( $field['css_class'] ) ? sanitize_text_field( $field['css_class'] ) : '';

		if ( '' === $label ) {
			return null;
		}

		$conditional = $this->sanitizeConditionalRules( $field, $conditional_logic );
		$type_data   = $this->sanitizeTypeSpecificValues( $field, $type );
		$options     = $this->sanitizeOptions( $field );

		if ( ! is_numeric( $required ) ) {
			$required = 0;
		}

		$length_key = 'long_text' === $type ? 'min_length_longtext' : 'minlength';
		$max_key    = 'long_text' === $type ? 'max_length_longtext' : 'maxlength';

		// Keep specialized values for backward compatibility with previously saved data.
		$date_min      = isset( $field['date_min'] ) ? sanitize_text_field( $field['date_min'] ) : '';
		$date_max      = isset( $field['date_max'] ) ? sanitize_text_field( $field['date_max'] ) : '';
		$time_min      = isset( $field['time_min'] ) ? sanitize_text_field( $field['time_min'] ) : '';
		$time_max      = isset( $field['time_max'] ) ? sanitize_text_field( $field['time_max'] ) : '';
		$datetime_min  = isset( $field['datetime_min'] ) ? sanitize_text_field( $field['datetime_min'] ) : '';
		$datetime_max  = isset( $field['datetime_max'] ) ? sanitize_text_field( $field['datetime_max'] ) : '';
		$datetime_step = isset( $field['datetime_step'] ) ? sanitize_text_field( $field['datetime_step'] ) : '';

		return array(
			'id'                          => $id,
			'label'                       => $label,
			'type'                        => $type,
			'required'                    => $required,
			'conditional_logic'           => $conditional['enabled'],
			'placeholder_text'            => $placeholder_text,
			'help_text'                   => $help_text,
			'options'                     => $options,
			'minlength'                   => isset( $field[ $length_key ] ) ? absint( $field[ $length_key ] ) : 0,
			'maxlength'                   => isset( $field[ $max_key ] ) ? absint( $field[ $max_key ] ) : 0,
			'rows'                        => isset( $field['rows'] ) ? absint( $field['rows'] ) : 0,
			'cols'                        => isset( $field['cols'] ) ? absint( $field['cols'] ) : 0,
			'autocomplete'                => $autocomplete,
			'autofocus'                   => $autofocus,
			'conditional_rules'           => $conditional['rules'],
			'index'                       => $index,
			'editable'                    => $editable,
			'adjust_price'                => $adjust_price,
			'price_adjustment_type'       => $price_adjustment_type,
			'priceAdjustmentValue'        => $price_adjustment,
			'step'                        => $type_data['step'],
			'min'                         => $type_data['min'],
			'max'                         => $type_data['max'],
			'default'                     => $type_data['default'],
			'long_text_default'           => $type_data['long_text_default'],
			'email_default'               => $type_data['email_default'],
			'color_default'               => $type_data['color_default'],
			'number_default'              => $type_data['number_default'],
			'color_enable_frontend_input' => isset( $field['color_enable_frontend_input'] ) ? 1 : 0,
			'color_radio_style'           => isset( $field['color_radio_style'] ) ? sanitize_text_field( $field['color_radio_style'] ) : 'circle',
			'color_radio_size'            => isset( $field['color_radio_size'] ) ? sanitize_text_field( $field['color_radio_size'] ) : '75px',
			'color_radio_show_label'      => isset( $field['color_radio_show_label'] ) ? 1 : 0,
			'date_default_today'          => isset( $field['date_default_today'] ) ? 1 : 0,
			'datetime_default_now'        => isset( $field['datetime_default_now'] ) ? 1 : 0,
			'blocked'                     => true,
			'disabled'                    => $disabled,
			'css_class'                   => $css_class,
			'time_min'                    => $time_min,
			'time_max'                    => $time_max,
			'time_default'                => $type_data['time_default'],
			'datetime_default'            => $type_data['datetime_default'],
			'date_default'                => $type_data['date_default'],
			'datetime_min'                => $datetime_min,
			'datetime_max'                => $datetime_max,
			'date_min'                    => $date_min,
			'date_max'                    => $date_max,
			'datetime_step'               => $datetime_step,
		);
	}

	/**
	 * Sanitize conditional rule payload.
	 *
	 * @param array<string, mixed> $field Field payload.
	 * @param int                  $enabled Conditional flag.
	 * @return array{enabled:int,rules:array<int, mixed>}
	 */
	private function sanitizeConditionalRules( array $field, int $enabled ): array {
		if ( ! isset( $field['conditional_rules'] ) || ! is_array( $field['conditional_rules'] ) ) {
			return array(
				'enabled' => 0,
				'rules'   => array(),
			);
		}

		$sanitized_rules = array();
		foreach ( $field['conditional_rules'] as $group_index => $rule_group ) {
			if ( ! is_array( $rule_group ) ) {
				continue;
			}

			$sanitized_group = array();
			foreach ( $rule_group as $rule ) {
				if ( ! is_array( $rule ) ) {
					continue;
				}

				$sanitized_group[] = array(
					'field'    => sanitize_text_field( $rule['field'] ?? '' ),
					'operator' => sanitize_text_field( $rule['operator'] ?? '' ),
					'value'    => sanitize_text_field( $rule['value'] ?? '' ),
				);
			}

			$sanitized_rules[ $group_index ] = $sanitized_group;
		}

		return array(
			'enabled' => $enabled,
			'rules'   => $sanitized_rules,
		);
	}

	/**
	 * Sanitize selectable options.
	 *
	 * @param array<string, mixed> $field Field payload.
	 * @return array<int, array<string, mixed>>
	 */
	private function sanitizeOptions( array $field ): array {
		if ( ! isset( $field['options'] ) || ! is_array( $field['options'] ) ) {
			return array();
		}

		$options = array();
		foreach ( $field['options'] as $key => $option ) {
			if ( ! is_array( $option ) ) {
				continue;
			}

			$options[] = array(
				'label'                 => ! empty( $option['label'] ) ? sanitize_text_field( $option['label'] ) : 'Option ' . $key,
				'value'                 => ! empty( $option['value'] ) ? sanitize_text_field( $option['value'] ) : sanitize_text_field( $option['label'] ?? '' ),
				'price_adjustment_type' => isset( $option['price_adjustment_type'] ) ? sanitize_text_field( $option['price_adjustment_type'] ) : '',
				'priceAdjustmentValue'  => isset( $option['priceAdjustmentValue'] ) ? sanitize_text_field( $option['priceAdjustmentValue'] ) : '',
				'default'               => isset( $option['default'] ) ? sanitize_text_field( $option['default'] ) : 0,
			);
		}

		return $options;
	}

	/**
	 * Sanitize type-specific default/min/max/step values.
	 *
	 * @param array<string, mixed> $field Field payload.
	 * @param string               $type Field type.
	 * @return array<string, mixed>
	 */
	private function sanitizeTypeSpecificValues( array $field, string $type ): array {
		$result = array(
			'default'           => '',
			'long_text_default' => '',
			'email_default'     => '',
			'color_default'     => '',
			'number_default'    => '',
			'time_default'      => '',
			'date_default'      => '',
			'datetime_default'  => '',
			'min'               => isset( $field['min'] ) ? sanitize_text_field( $field['min'] ) : '',
			'max'               => isset( $field['max'] ) ? sanitize_text_field( $field['max'] ) : '',
			'step'              => isset( $field['step'] ) ? sanitize_text_field( $field['step'] ) : '',
		);

		switch ( $type ) {
			case 'long_text':
				$result['long_text_default'] = isset( $field['long_text_default'] ) ? sanitize_textarea_field( $field['long_text_default'] ) : '';
				$result['default']           = $result['long_text_default'];
				break;
			case 'email':
				$result['email_default'] = isset( $field['email_default'] ) ? sanitize_email( $field['email_default'] ) : '';
				$result['default']       = $result['email_default'];
				break;
			case 'color':
				$result['color_default'] = isset( $field['color_default'] ) ? sanitize_text_field( $field['color_default'] ) : '';
				$result['default']       = $result['color_default'];
				break;
			case 'checkbox':
			case 'radio':
			case 'select':
			case 'color_radio':
				$default_source    = $field['default'] ?? '';
				$result['default'] = is_array( $default_source ) ? array_map( 'sanitize_text_field', $default_source ) : sanitize_text_field( $default_source );
				break;
			case 'number':
				$result['number_default'] = isset( $field['number_default'] ) ? sanitize_text_field( $field['number_default'] ) : '';
				$result['default']        = $result['number_default'];
				break;
			case 'datetime':
				$result['datetime_default'] = isset( $field['datetime_default'] ) ? sanitize_text_field( $field['datetime_default'] ) : '';
				$result['default']          = $result['datetime_default'];
				$result['min']              = isset( $field['datetime_min'] ) ? sanitize_text_field( $field['datetime_min'] ) : $result['min'];
				$result['max']              = isset( $field['datetime_max'] ) ? sanitize_text_field( $field['datetime_max'] ) : $result['max'];
				$result['step']             = isset( $field['datetime_step'] ) ? sanitize_text_field( $field['datetime_step'] ) : $result['step'];
				break;
			case 'time':
				$result['time_default'] = isset( $field['time_default'] ) ? sanitize_text_field( $field['time_default'] ) : '';
				$result['default']      = $result['time_default'];
				$result['min']          = isset( $field['time_min'] ) ? sanitize_text_field( $field['time_min'] ) : $result['min'];
				$result['max']          = isset( $field['time_max'] ) ? sanitize_text_field( $field['time_max'] ) : $result['max'];
				$result['step']         = '' !== $result['step'] ? $result['step'] : '60';
				break;
			case 'date':
				$result['date_default'] = isset( $field['date_default'] ) ? sanitize_text_field( $field['date_default'] ) : '';
				$result['default']      = $result['date_default'];
				$result['min']          = isset( $field['date_min'] ) ? sanitize_text_field( $field['date_min'] ) : $result['min'];
				$result['max']          = isset( $field['date_max'] ) ? sanitize_text_field( $field['date_max'] ) : $result['max'];
				break;
			default:
				$default_source    = $field['default'] ?? '';
				$result['default'] = sanitize_text_field( is_array( $default_source ) ? '' : $default_source );
				break;
		}

		return $result;
	}

	/**
	 * Check if custom fields contain duplicate labels.
	 *
	 * @param array<int, array<string, mixed>> $custom_fields Custom fields to validate.
	 * @return bool True if duplicate labels exist.
	 */
	private function hasDuplicateLabels( array $custom_fields ): bool {
		$seen = array();

		foreach ( $custom_fields as $field ) {
			if ( ! isset( $field['label'] ) || ! is_string( $field['label'] ) ) {
				continue;
			}

			$label = trim( $field['label'] );
			if ( '' === $label ) {
				continue;
			}

			$normalized = function_exists( 'mb_strtolower' ) ? mb_strtolower( $label ) : strtolower( $label );

			if ( isset( $seen[ $normalized ] ) ) {
				return true;
			}

			$seen[ $normalized ] = true;
		}

		return false;
	}

	/**
	 * Check if any field contains duplicate option values.
	 *
	 * Validation is scoped per field (not globally across all fields) and only
	 * applies to option-based field types.
	 *
	 * @param array<int, array<string, mixed>> $custom_fields Custom fields to validate.
	 * @return bool True if duplicate option values exist within any single field.
	 */
	private function hasDuplicateOptionValues( array $custom_fields ): bool {
		foreach ( $custom_fields as $field ) {
			$type = $field['type'] ?? '';
			if ( ! in_array( $type, array( 'radio', 'checkbox', 'select' ), true ) ) {
				continue;
			}

			$options = $field['options'] ?? array();
			if ( ! is_array( $options ) || empty( $options ) ) {
				continue;
			}

			$seen = array();
			foreach ( $options as $option ) {
				if ( ! is_array( $option ) ) {
					continue;
				}

				$raw_value    = $option['value'] ?? '';
				$option_value = trim( (string) $raw_value );
				if ( '' === $option_value ) {
					continue;
				}

				$normalized = function_exists( 'mb_strtolower' ) ? mb_strtolower( $option_value ) : strtolower( $option_value );
				if ( isset( $seen[ $normalized ] ) ) {
					return true;
				}

				$seen[ $normalized ] = true;
			}
		}

		return false;
	}

	/**
	 * Import custom fields.
	 */
	public function exprdawcImportCustomFields() {
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

		$product->update_meta_data( EXPRDAWC_PRODUCT_META_EXTRA_PRODUCT_DATA, $custom_fields );
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
