<?php
/**
 * Created on Mon Nov 25 2024
 *
 * Copyright (c) 2024 IT-Dienstleistungen Drevermann - All Rights Reserved
 *
 * @package Extra Product Data for WooCommerce
 * @author Daniel Drevermann <info@triopsi.com>
 * @copyright Copyright (c) 2024, IT-Dienstleistungen Drevermann
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

use Automattic\WooCommerce\Enums\ProductType;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Exprdawc_Helper
 *
 * This class contains helper functions for the plugin.
 * Refactored for improved code organization, type safety, and WordPress standards compliance.
 *
 * @package Exprdawc
 */
class Exprdawc_Helper {

	/**
	 * Text field types that should have the 'input-text' class.
	 *
	 * @var array
	 */
	private const TEXT_FIELD_TYPES = array( 'text', 'date', 'url', 'email', 'tel', 'number', 'textarea', 'select', 'multiselect' );

	/**
	 * Price adjustment field types.
	 *
	 * @var array
	 */
	private const PRICE_ADJUSTMENT_TYPES = array( 'checkbox', 'radio', 'select' );

	/**
	 * Generate and render an input field.
	 *
	 * @param array  $field               The field arguments.
	 * @param string $value               The field value.
	 * @param bool   $skip_required_check Whether to skip the required check.
	 * @param bool   $cart_page           Whether the field is rendered on the cart page.
	 *
	 * @return void
	 */
	public static function generate_input_field( array $field, string $value = '', bool $skip_required_check = false, bool $cart_page = false ): void {
		// Prepare field arguments with defaults.
		$field_args = self::prepare_field_args( $field, $value, $skip_required_check, $cart_page );

		// Validate field arguments.
		if ( ! self::validate_field_args( $field_args ) ) {
			return;
		}

		// Render the field template.
		self::render_field_template( $field_args );
	}

	/**
	 * Prepare field arguments by merging with defaults and processing.
	 *
	 * @param array  $field               The field arguments.
	 * @param string $value               The field value.
	 * @param bool   $skip_required_check Whether to skip required check.
	 * @param bool   $cart_page           Whether rendered on cart page.
	 *
	 * @return array Processed field arguments.
	 */
	private static function prepare_field_args( array $field, string $value, bool $skip_required_check, bool $cart_page ): array {
		$field_args = wp_parse_args( $field, self::get_default_field_args() );

		// Normalize field properties.
		$field_args = self::normalize_field_properties( $field_args, $skip_required_check, $cart_page );

		// Generate ID and name.
		$field_args = self::generate_field_id_and_name( $field_args );

		// Set field value.
		$field_args['value'] = self::get_field_value( $field_args, $value );

		// Prepare CSS classes.
		$field_args = self::prepare_field_classes( $field_args, $skip_required_check );

		// Prepare custom attributes.
		$field_args = self::prepare_custom_attributes( $field_args, $skip_required_check );

		// Prepare required/optional string.
		$field_args['required_string'] = self::get_required_string( $field_args, $skip_required_check );

		// Normalize field type (e.g., long_text -> textarea).
		$field_args = self::normalize_field_type( $field_args );

		// Handle price adjustments for field and options.
		if ( $field_args['adjust_price'] ) {
			$field_args = self::prepare_price_adjustment( $field_args, $skip_required_check );
			$field_args = self::prepare_option_labels_with_prices( $field_args );
		}

		return $field_args;
	}

	/**
	 * Get default field arguments.
	 *
	 * @return array Default field arguments.
	 */
	private static function get_default_field_args(): array {
		return array(
			'id_prefix'              => 'exprdawc_custom_field_input',
			'id'                     => '',
			'name'                   => '',
			'type'                   => 'text',
			'wrapper_class'          => array( 'form-row-wide' ),
			'label_class'            => array( 'exprdawc-label' ),
			'input_wrapper_class'    => array( 'wc-block-components-text-input', 'exprdawc-input-wrapper' ),
			'input_class'            => array( 'exprdawc-input' ),
			'description_class'      => array( 'exprdawc-description' ),
			'label'                  => '',
			'required'               => false,
			'placeholder'            => '',
			'description'            => '',
			'custom_attributes'      => array(),
			'options'                => array(),
			'maxlength'              => 255,
			'minlength'              => 0,
			'autocomplete'           => null,
			'autofocus'              => false,
			'disabled'               => false,
			'editable'               => true,
			'validate'               => array(),
			'data'                   => array(),
			'adjust_price'           => false,
			'price_adjustment_type'  => null,
			'price_adjustment_value' => 0,
			'default'                => '',
			'value'                  => '',
			'help_text'              => '',
			'placeholder_text'       => '',
			'conditional_logic'      => false,
			'conditional_rules'      => array(),
			'rows'                   => 2,
			'cols'                   => 5,
		);
	}

	/**
	 * Normalize field properties.
	 *
	 * @param array $field_args          Field arguments.
	 * @param bool  $skip_required_check Skip required check.
	 * @param bool  $cart_page           Cart page flag.
	 *
	 * @return array Normalized field arguments.
	 */
	private static function normalize_field_properties( array $field_args, bool $skip_required_check, bool $cart_page ): array {
		// Handle editable property.
		if ( ! $field_args['editable'] && ! $skip_required_check && ! $cart_page ) {
			$field_args['disabled'] = true;
		}

		// Map help_text to description and placeholder_text to placeholder.
		$field_args['description'] = ! empty( $field_args['help_text'] ) ? $field_args['help_text'] : $field_args['description'];
		$field_args['placeholder'] = ! empty( $field_args['placeholder_text'] ) ? $field_args['placeholder_text'] : $field_args['placeholder'];

		return $field_args;
	}

	/**
	 * Generate field ID and name attributes.
	 *
	 * @param array $field_args Field arguments.
	 *
	 * @return array Field arguments with ID and name.
	 */
	private static function generate_field_id_and_name( array $field_args ): array {
		$index = sanitize_title( $field_args['label'] );
		$index = strtolower( str_replace( array( ' ', '_' ), '-', $index ) );

		// Generate Field ID - maintain original behavior.
		if ( empty( $field_args['id'] ) ) {
			$id_prefix        = str_replace( array( ' ', '_' ), '-', $field_args['id_prefix'] );
			$field_args['id'] = $id_prefix . '-' . $index;
		}
		$field_args['id'] = strtolower( $field_args['id'] );

		// Generate Field Name - use only the index part, not the full id_prefix.
		if ( empty( $field_args['name'] ) ) {
			$field_args['name'] = str_replace( '-', '_', $index );
		}
		$field_args['name'] = $field_args['id_prefix'] . '[' . $field_args['name'] . ']';

		return $field_args;
	}

	/**
	 * Get the field value from request or default.
	 *
	 * @param array  $field_args Field arguments.
	 * @param string $value      Passed value.
	 *
	 * @return string Field value.
	 */
	private static function get_field_value( array $field_args, string $value ): string {
		if ( ! empty( $value ) ) {
			return $value;
		}

		// Check if value exists in request.
		$selected_key = 'attribute_' . str_replace( '-', '_', $field_args['id'] );

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only operation
		if ( isset( $_REQUEST[ $selected_key ] ) ) {
			return wc_clean( wp_unslash( $_REQUEST[ $selected_key ] ) ); // phpcs:ignore
		}

		return $field_args['default'];
	}

	/**
	 * Prepare CSS classes for all field elements.
	 *
	 * @param array $field_args          Field arguments.
	 * @param bool  $skip_required_check Skip required check.
	 *
	 * @return array Field arguments with classes.
	 */
	private static function prepare_field_classes( array $field_args, bool $skip_required_check ): array {
		$is_required = $field_args['required'] && ! $skip_required_check;
		$type_slug   = str_replace( '_', '-', $field_args['type'] );

		// Set the wrapper class. With type and required class.
		$field_args['wrapper_class'][] = 'exprdawc-field-wrapper';
		$field_args['wrapper_class'][] = $field_args['id'] . '-wrapper';
		$field_args['wrapper_class'][] = 'exprdawc-field-wrapper-' . $type_slug;
		if ( $is_required ) {
			$field_args['wrapper_class'][] = 'exprdawc-field-wrapper-required';
		}

		// Set the input class. With type and required class.
		$field_args['input_class'][] = $field_args['id'] . '-input';
		$field_args['input_class'][] = 'exprdawc-field-input-' . $type_slug;
		if ( $is_required ) {
			$field_args['input_class'][] = 'exprdawc-field-input-required';
		}

		// Set the label class. With type and required class.
		$field_args['label_class'][] = $field_args['id'] . '-label';
		$field_args['label_class'][] = 'exprdawc-field-label-' . $type_slug;
		if ( $is_required ) {
			$field_args['label_class'][] = 'exprdawc-field-label-required';
		}

		// Set the description class. With type and required class.
		$field_args['description_class'][] = $field_args['id'] . '-description';
		$field_args['description_class'][] = 'exprdawc-field-description-' . $type_slug;
		if ( $is_required ) {
			$field_args['description_class'][] = 'exprdawc-field-description-required';
		}

		// Set the input wrapper class. With type and required class.
		$field_args['input_wrapper_class'][] = $field_args['id'] . '-input-wrapper';
		$field_args['input_wrapper_class'][] = 'exprdawc-field-input-wrapper-' . $type_slug;
		if ( $is_required ) {
			$field_args['input_wrapper_class'][] = 'exprdawc-field-input-wrapper-required';
		}

		// Add 'input-text' class to text fields for WC compatibility.
		if ( in_array( $field_args['type'], self::TEXT_FIELD_TYPES, true ) ) {
			$field_args['input_class'][] = 'input-text';
		}

		// Add validation classes.
		if ( ! empty( $field_args['validate'] ) ) {
			foreach ( $field_args['validate'] as $validate ) {
				$field_args['input_class'][] = 'validate-' . sanitize_html_class( $validate );
			}
		}

		return $field_args;
	}

	/**
	 * Prepare custom attributes for the field.
	 *
	 * @param array $field_args          Field arguments.
	 * @param bool  $skip_required_check Skip required check.
	 *
	 * @return array Field arguments with custom attributes.
	 */
	private static function prepare_custom_attributes( array $field_args, bool $skip_required_check ): array {
		$attrs = $field_args['custom_attributes'];

		// Add data attributes.
		$attrs['data-placeholder'] = $field_args['placeholder'];
		$attrs['data-label']       = $field_args['label'];

		// Add conditional rules.
		if ( ! empty( $field_args['conditional_rules'] ) && $field_args['conditional_logic'] ) {
			$attrs['data-conditional-rules'] = wp_json_encode( $field_args['conditional_rules'] );
		}

		// Add maxlength and minlength.
		if ( $field_args['maxlength'] > 0 ) {
			$attrs['maxlength'] = absint( $field_args['maxlength'] );
		}

		if ( $field_args['minlength'] > 0 ) {
			$attrs['minlength'] = absint( $field_args['minlength'] );
		}

		// Add autocomplete.
		if ( ! empty( $field_args['autocomplete'] ) ) {
			$attrs['autocomplete'] = sanitize_text_field( $field_args['autocomplete'] );
		}

		// Add boolean attributes.
		if ( $field_args['autofocus'] ) {
			$attrs['autofocus'] = 'autofocus';
		}

		if ( $field_args['disabled'] ) {
			$attrs['disabled']           = 'disabled';
			$field_args['input_class'][] = 'disabled';
		}

		if ( $field_args['required'] && ! $skip_required_check ) {
			$attrs['required'] = 'required';
		}

		// Add aria-describedby for accessibility.
		if ( ! empty( $field_args['description'] ) ) {
			$attrs['aria-describedby'] = $field_args['id'] . '-description';
		}

		// Process custom data attributes.
		if ( ! empty( $field_args['data'] ) && is_array( $field_args['data'] ) ) {
			foreach ( $field_args['data'] as $key => $val ) {
				$data_key           = 'data-' . strtolower( str_replace( '_', '-', $key ) );
				$attrs[ $data_key ] = esc_attr( $val );
			}
		}

		// Filter out empty values and build attribute string.
		$attrs                                  = array_filter( $attrs, 'strlen' );
		$field_args['custom_attributes']        = $attrs;
		$field_args['custom_attributes_string'] = self::build_attributes_string( $attrs );

		return $field_args;
	}

	/**
	 * Build HTML attribute string from array.
	 *
	 * @param array $attrs Attributes array.
	 *
	 * @return string HTML attributes string.
	 */
	private static function build_attributes_string( array $attrs ): string {
		$attributes = array();

		foreach ( $attrs as $key => $value ) {
			$attributes[] = esc_attr( $key ) . '="' . esc_attr( $value ) . '"';
		}

		return implode( ' ', $attributes );
	}

	/**
	 * Get required/optional string for label.
	 *
	 * @param array $field_args          Field arguments.
	 * @param bool  $skip_required_check Skip required check.
	 *
	 * @return string Required/optional HTML string.
	 */
	private static function get_required_string( array $field_args, bool $skip_required_check ): string {
		if ( $skip_required_check ) {
			return '';
		}

		if ( $field_args['required'] ) {
			return '&nbsp;<abbr class="required" title="' . esc_attr__( 'Required', 'extra-product-data-for-woocommerce' ) . '">*</abbr>';
		}

		return '&nbsp;<span class="optional">(' . esc_html__( 'Optional', 'extra-product-data-for-woocommerce' ) . ')</span>';
	}

	/**
	 * Prepare price adjustment attributes and classes.
	 *
	 * @param array $field_args          Field arguments.
	 * @param bool  $skip_required_check Skip required check.
	 *
	 * @return array Field arguments with price adjustment data.
	 */
	private static function prepare_price_adjustment( array $field_args, bool $skip_required_check ): array { // phpcs:ignore
		// Check if field has a price adjustment value.
		$adjustment_value = (float) ( $field_args['price_adjustment_value'] ?? 0 );

		// Only add price adjustment class and attributes if there's a non-zero adjustment.
		if ( 0 !== $adjustment_value ) {
			// Add class to all field types that support price adjustments.
			$field_args['input_class'][] = 'exprdawc-price-adjustment-field';

			// For non-choice fields (text, textarea, etc.), add data attributes to the field.
			if ( ! in_array( $field_args['type'], self::PRICE_ADJUSTMENT_TYPES, true ) ) {
				$field_args['custom_attributes']['data-price-adjustment-type'] = esc_attr( $field_args['price_adjustment_type'] );
				$field_args['custom_attributes']['data-price-adjustment']      = esc_attr( $field_args['price_adjustment_value'] );

				// Add price to required string.
				$plus_minus                     = ( $adjustment_value > 0 ) ? '+' : '-';
				$field_args['required_string'] .= ' (' . $plus_minus . wc_price( abs( $adjustment_value ) ) . ')';
			}
		}

		return $field_args;
	}

	/**
	 * Prepare option labels with price adjustments.
	 *
	 * Formats option labels to include price adjustments with +/- prefix
	 * and currency symbol or percentage sign.
	 *
	 * @param array $field_args Field arguments with options.
	 *
	 * @return array Field arguments with formatted option labels.
	 */
	private static function prepare_option_labels_with_prices( array $field_args ): array {
		if ( empty( $field_args['options'] ) || ! is_array( $field_args['options'] ) ) {
			return $field_args;
		}

		$field_args['options'] = array_map(
			function ( $option ) use ( $field_args ) {
				// Skip if no price adjustment value is set.
				if ( ! isset( $option['price_adjustment_value'] ) || empty( $option['price_adjustment_value'] ) ) {
					return $option;
				}

				$adjustment_value = (float) $option['price_adjustment_value'];
				$adjustment_type  = $option['price_adjustment_type'] ?? 'fixed';

				// Skip if adjustment value is zero.
				if ( 0 === $adjustment_value ) {
					return $option;
				}

				// Determine sign prefix.
				$sign      = $adjustment_value > 0 ? '+' : '-';
				$abs_value = abs( $adjustment_value );

				// Format adjustment string based on type.
				// Check for both 'percentage' and 'percentage' for compatibility.
				if ( 'percentage' === $adjustment_type ) {
					$price_text = sprintf( '%s%d%%', $sign, $abs_value );
				} else {
					// Default to fixed (currency) - use simple format without HTML.
					$currency_symbol = get_woocommerce_currency_symbol();
					$price_text      = sprintf( '%s%.2f %s', $sign, $abs_value, $currency_symbol );
				}

				// Append price adjustment to label.
				$option['label'] = $option['label'] . ' (' . $price_text . ')';

				return $option;
			},
			$field_args['options']
		);

		return $field_args;
	}

	/**
	 * Normalize field type (convert aliases to standard types).
	 *
	 * @param array $field_args Field arguments.
	 *
	 * @return array Field arguments with normalized type.
	 */
	private static function normalize_field_type( array $field_args ): array {
		switch ( $field_args['type'] ) {
			case 'long_text':
				$field_args['type']                      = 'textarea';
				$field_args['custom_attributes']['rows'] = $field_args['rows'] ?? 2;
				$field_args['custom_attributes']['cols'] = $field_args['cols'] ?? 5;
				break;

			case 'yes-no':
				$field_args['type']    = 'select';
				$field_args['options'] = array(
					array(
						'value' => 'yes',
						'label' => __( 'Yes', 'extra-product-data-for-woocommerce' ),
					),
					array(
						'value' => 'no',
						'label' => __( 'No', 'extra-product-data-for-woocommerce' ),
					),
				);
				break;
		}

		return $field_args;
	}

	/**
	 * Validate field arguments.
	 *
	 * @param array $field_args Field arguments.
	 *
	 * @return bool True if valid, false otherwise.
	 */
	private static function validate_field_args( array $field_args ): bool {
		// Check if required fields exist.
		if ( empty( $field_args['id'] ) || empty( $field_args['name'] ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Render the field template.
	 *
	 * @param array $field_args Prepared field arguments.
	 *
	 * @return void
	 */
	private static function render_field_template( array $field_args ): void {
		$template_path = EXPRDAWC_FIELDS_TEMPLATES_PATH;

		// Extract variables for template scope.
		$required_string   = $field_args['required_string'] ?? '';
		$custom_attributes = self::build_attributes_array( $field_args['custom_attributes'] );

		// Include field start template.
		if ( file_exists( $template_path . 'custom-field-start.php' ) ) {
			include $template_path . 'custom-field-start.php';
		}

		// Include field type template.
		$field_template = $template_path . $field_args['type'] . '.php';

		if ( file_exists( $field_template ) ) {
			include $field_template;
		} else {
			// Fallback to text template.
			include $template_path . 'text.php';
		}

		// Include field end template.
		if ( file_exists( $template_path . 'custom-field-end.php' ) ) {
			include $template_path . 'custom-field-end.php';
		}
	}

	/**
	 * Build HTML attribute string array for templates.
	 *
	 * @param array $attrs Attributes array.
	 *
	 * @return array Array of HTML attributes strings.
	 */
	private static function build_attributes_array( array $attrs ): array {
		$attributes = array();

		foreach ( $attrs as $key => $value ) {
			$attributes[] = esc_attr( $key ) . '="' . esc_attr( $value ) . '"';
		}

		return $attributes;
	}

	/**
	 * Check if the product have required fields in _extra_product_fields meta.
	 * If the product is a variation, check the parent product.
	 * Return true if the product have required fields, false otherwise.
	 *
	 * @param int $product_id The product ID.
	 *
	 * @return bool True if the product have required fields, false otherwise.
	 */
	public static function check_required_fields( $product_id ) {
		$product = wc_get_product( $product_id );
		if ( $product->is_type( ProductType::VARIATION ) ) {
			$product = wc_get_product( $product->get_parent_id() );
		}
		if ( ! $product ) {
			return false;
		}
		$custom_fields = $product->get_meta( '_extra_product_fields', true );

		if ( ! empty( $custom_fields ) ) {
			foreach ( $custom_fields as $index_num => $input_field_array ) {
				if ( ! empty( $input_field_array['required'] ) ) {
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * Render a template file with the given variables.
	 *
	 * @param string $template The template file to render, relative to the templates directory.
	 * @param array  $args     An associative array of variables to pass to the template.
	 */
	public static function render_template( string $template, array $args = array() ): void {
		$path = trailingslashit( EXPRDAWC_TEMPLATES ) . ltrim( $template, '/' );

		if ( ! file_exists( $path ) ) {
			return;
		}

		load_template( $path, true, $args );
	}
}
