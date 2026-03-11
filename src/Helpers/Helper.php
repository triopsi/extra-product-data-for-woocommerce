<?php
/**
 * Helper Class
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

namespace Triopsi\Exprdawc\Helpers;

use Automattic\WooCommerce\Enums\ProductType;
use Automattic\WooCommerce\Utilities\OrderUtil;
use WC_Order;
use WC_Product;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Helper Class
 *
 * Contains helper functions for the plugin.
 * Refactored for improved code organization, type safety, and WordPress standards compliance.
 */
class Helper {

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
	 * Check if WooCommerce is active.
	 *
	 * @return bool True when WooCommerce is available or active.
	 */
	public static function isWooCommerceActive(): bool {
		return class_exists( 'WooCommerce' );
	}

	/**
	 * Generate and render an input field.
	 *
	 * @param array        $field               The field arguments.
	 * @param string|array $value               The field value.
	 * @param bool         $skipRequiredCheck   Whether to skip the required check.
	 * @param bool         $cartPage            Whether the field is rendered on the cart page.
	 *
	 * @return void
	 */
	public static function generateInputField( array $field, $value = '', bool $skipRequiredCheck = false, bool $cartPage = false ): void {
		$fieldArgs = self::prepareFieldArgs( $field, $value, $skipRequiredCheck, $cartPage );

		if ( ! self::validateFieldArgs( $fieldArgs ) ) {
			return;
		}

		self::renderFieldTemplate( $fieldArgs );
	}

	/**
	 * Prepare field arguments by merging with defaults and processing.
	 *
	 * @param array        $field               The field arguments.
	 * @param string|array $value               The field value.
	 * @param bool         $skipRequiredCheck   Whether to skip required check.
	 * @param bool         $cartPage            Whether rendered on cart page.
	 *
	 * @return array Processed field arguments.
	 */
	private static function prepareFieldArgs( array $field, $value, bool $skipRequiredCheck, bool $cartPage ): array {
		$fieldArgs = wp_parse_args( $field, self::getDefaultFieldArgs() );

		$fieldArgs                    = self::normalizeFieldProperties( $fieldArgs, $skipRequiredCheck, $cartPage );
		$fieldArgs                    = self::generateFieldIdAndName( $fieldArgs );
		$fieldArgs['value']           = self::getFieldValue( $fieldArgs, $value );
		$fieldArgs                    = self::prepareFieldClasses( $fieldArgs, $skipRequiredCheck );
		$fieldArgs                    = self::prepareCustomAttributes( $fieldArgs, $skipRequiredCheck );
		$fieldArgs['required_string'] = self::getRequiredString( $fieldArgs, $skipRequiredCheck );
		$fieldArgs                    = self::normalizeFieldType( $fieldArgs );

		if ( $fieldArgs['adjust_price'] ) {
			$fieldArgs = self::preparePriceAdjustment( $fieldArgs, $skipRequiredCheck );
			$fieldArgs = self::prepareOptionLabelsWithPrices( $fieldArgs );
		}

		return $fieldArgs;
	}

	/**
	 * Get default field arguments.
	 *
	 * @return array Default field arguments.
	 */
	private static function getDefaultFieldArgs(): array {
		return array(
			'id_prefix'             => 'exprdawc_custom_field_input',
			'id'                    => '',
			'css_id'                => '',
			'name'                  => '',
			'type'                  => 'text',
			'wrapper_class'         => array( 'form-row-wide' ),
			'label_class'           => array( 'exprdawc-label' ),
			'input_wrapper_class'   => array( 'wc-block-components-text-input', 'exprdawc-input-wrapper' ),
			'input_class'           => array( 'exprdawc-input' ),
			'description_class'     => array( 'exprdawc-description' ),
			'label'                 => '',
			'required'              => false,
			'placeholder'           => '',
			'description'           => '',
			'custom_attributes'     => array(),
			'options'               => array(),
			'maxlength'             => 255,
			'minlength'             => 0,
			'autocomplete'          => null,
			'autofocus'             => false,
			'disabled'              => false,
			'editable'              => true,
			'validate'              => array(),
			'data'                  => array(),
			'adjust_price'          => false,
			'price_adjustment_type' => null,
			'priceAdjustmentValue'  => 0,
			'default'               => '',
			'value'                 => '',
			'help_text'             => '',
			'placeholder_text'      => '',
			'conditional_logic'     => false,
			'conditional_rules'     => array(),
			'rows'                  => 2,
			'cols'                  => 5,
		);
	}

	/**
	 * Normalize field properties.
	 *
	 * @param array $fieldArgs           Field arguments.
	 * @param bool  $skipRequiredCheck   Skip required check.
	 * @param bool  $cartPage            Cart page flag.
	 *
	 * @return array Normalized field arguments.
	 */
	private static function normalizeFieldProperties( array $fieldArgs, bool $skipRequiredCheck, bool $cartPage ): array {
		if ( ! $fieldArgs['editable'] && ! $skipRequiredCheck && ! $cartPage ) {
			$fieldArgs['disabled'] = true;
		}

		$fieldArgs['description'] = ! empty( $fieldArgs['help_text'] ) ? $fieldArgs['help_text'] : $fieldArgs['description'];
		$fieldArgs['placeholder'] = ! empty( $fieldArgs['placeholder_text'] ) ? $fieldArgs['placeholder_text'] : $fieldArgs['placeholder'];

		return $fieldArgs;
	}

	/**
	 * Generate field ID and name attributes.
	 *
	 * @param array $fieldArgs Field arguments.
	 *
	 * @return array Field arguments with ID and name.
	 */
	private static function generateFieldIdAndName( array $fieldArgs ): array {
		$index = sanitize_title( $fieldArgs['label'] );
		$index = strtolower( str_replace( array( ' ', '_' ), '-', $index ) );
		$id    = $fieldArgs['id'];

		if ( empty( $fieldArgs['css_id'] ) ) {
			$idPrefix = str_replace( array( ' ', '_' ), '-', $fieldArgs['id_prefix'] );

			if ( ! empty( $id ) ) {
				$index = $id;
			}

			$fieldArgs['css_id'] = $idPrefix . '-' . $index;
		}
		$fieldArgs['css_id'] = strtolower( $fieldArgs['css_id'] );

		if ( empty( $fieldArgs['name'] ) ) {
			$fieldArgs['name'] = str_replace( '-', '_', $index );
		}

		$fieldArgs['name'] = $fieldArgs['id_prefix'] . '[' . $fieldArgs['name'] . ']';
		return $fieldArgs;
	}

	/**
	 * Get the field value from request or default.
	 *
	 * @param array        $fieldArgs Field arguments.
	 * @param string|array $value     Passed value.
	 *
	 * @return mixed Field value.
	 */
	private static function getFieldValue( array $fieldArgs, $value ): mixed {
		if ( ! empty( $value ) ) {
			return $value;
		}

		$selectedKey = 'attribute_' . str_replace( '-', '_', $fieldArgs['css_id'] );

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( isset( $_REQUEST[ $selectedKey ] ) ) {
			return wc_clean( wp_unslash( $_REQUEST[ $selectedKey ] ) ); // phpcs:ignore
		}

		return $fieldArgs['default'];
	}

	/**
	 * Prepare CSS classes for all field elements.
	 *
	 * @param array $fieldArgs           Field arguments.
	 * @param bool  $skipRequiredCheck   Skip required check.
	 *
	 * @return array Field arguments with classes.
	 */
	private static function prepareFieldClasses( array $fieldArgs, bool $skipRequiredCheck ): array {
		$isRequired = $fieldArgs['required'] && ! $skipRequiredCheck;
		$typeSlug   = str_replace( '_', '-', $fieldArgs['type'] );

		$fieldArgs['wrapper_class'][] = 'exprdawc-field-wrapper';
		$fieldArgs['wrapper_class'][] = $fieldArgs['css_id'] . '-wrapper';
		$fieldArgs['wrapper_class'][] = 'exprdawc-field-wrapper-' . $typeSlug;
		if ( $isRequired ) {
			$fieldArgs['wrapper_class'][] = 'exprdawc-field-wrapper-required';
		}

		$fieldArgs['input_class'][] = $fieldArgs['css_id'] . '-input';
		$fieldArgs['input_class'][] = 'exprdawc-field-input-' . $typeSlug;
		if ( $isRequired ) {
			$fieldArgs['input_class'][] = 'exprdawc-field-input-required';
		}

		$fieldArgs['label_class'][] = $fieldArgs['css_id'] . '-label';
		$fieldArgs['label_class'][] = 'exprdawc-field-label-' . $typeSlug;
		if ( $isRequired ) {
			$fieldArgs['label_class'][] = 'exprdawc-field-label-required';
		}

		$fieldArgs['description_class'][] = $fieldArgs['css_id'] . '-description';
		$fieldArgs['description_class'][] = 'exprdawc-field-description-' . $typeSlug;
		if ( $isRequired ) {
			$fieldArgs['description_class'][] = 'exprdawc-field-description-required';
		}

		$fieldArgs['input_wrapper_class'][] = $fieldArgs['css_id'] . '-input-wrapper';
		$fieldArgs['input_wrapper_class'][] = 'exprdawc-field-input-wrapper-' . $typeSlug;
		if ( $isRequired ) {
			$fieldArgs['input_wrapper_class'][] = 'exprdawc-field-input-wrapper-required';
		}

		if ( in_array( $fieldArgs['type'], self::TEXT_FIELD_TYPES, true ) ) {
			$fieldArgs['input_class'][] = 'input-text';
		}

		if ( ! empty( $fieldArgs['validate'] ) ) {
			foreach ( $fieldArgs['validate'] as $validate ) {
				$fieldArgs['input_class'][] = 'validate-' . sanitize_html_class( $validate );
			}
		}

		return $fieldArgs;
	}

	/**
	 * Prepare custom attributes for the field.
	 *
	 * @param array $fieldArgs           Field arguments.
	 * @param bool  $skipRequiredCheck   Skip required check.
	 *
	 * @return array Field arguments with custom attributes.
	 */
	private static function prepareCustomAttributes( array $fieldArgs, bool $skipRequiredCheck ): array {
		$attrs = $fieldArgs['custom_attributes'];

		$attrs['data-placeholder'] = $fieldArgs['placeholder'];
		$attrs['data-label']       = $fieldArgs['label'];

		if ( ! empty( $fieldArgs['conditional_rules'] ) && $fieldArgs['conditional_logic'] ) {
			$attrs['data-conditional-rules'] = wp_json_encode( $fieldArgs['conditional_rules'] );
		}

		if ( $fieldArgs['maxlength'] > 0 ) {
			$attrs['maxlength'] = absint( $fieldArgs['maxlength'] );
		}

		if ( $fieldArgs['minlength'] > 0 ) {
			$attrs['minlength'] = absint( $fieldArgs['minlength'] );
		}

		if ( ! empty( $fieldArgs['autocomplete'] ) ) {
			$attrs['autocomplete'] = sanitize_text_field( $fieldArgs['autocomplete'] );
		}

		if ( $fieldArgs['autofocus'] ) {
			$attrs['autofocus'] = 'autofocus';
		}

		if ( $fieldArgs['disabled'] ) {
			$attrs['disabled']          = 'disabled';
			$fieldArgs['input_class'][] = 'disabled';
		}

		if ( $fieldArgs['required'] && ! $skipRequiredCheck ) {
			$attrs['required'] = 'required';
		}

		if ( ! empty( $fieldArgs['description'] ) ) {
			$attrs['aria-describedby'] = $fieldArgs['css_id'] . '-description';
		}

		if ( ! empty( $fieldArgs['data'] ) && is_array( $fieldArgs['data'] ) ) {
			foreach ( $fieldArgs['data'] as $key => $val ) {
				$dataKey           = 'data-' . strtolower( str_replace( '_', '-', $key ) );
				$attrs[ $dataKey ] = esc_attr( $val );
			}
		}

		$attrs                                 = array_filter( $attrs, 'strlen' );
		$fieldArgs['custom_attributes']        = $attrs;
		$fieldArgs['custom_attributes_string'] = self::buildAttributesString( $attrs );

		return $fieldArgs;
	}

	/**
	 * Build HTML attribute string from array.
	 *
	 * @param array $attrs Attributes array.
	 *
	 * @return string HTML attributes string.
	 */
	private static function buildAttributesString( array $attrs ): string {
		$attributes = array();

		foreach ( $attrs as $key => $value ) {
			$attributes[] = esc_attr( $key ) . '="' . esc_attr( $value ) . '"';
		}

		return implode( ' ', $attributes );
	}

	/**
	 * Get required/optional string for label.
	 *
	 * @param array $fieldArgs           Field arguments.
	 * @param bool  $skipRequiredCheck   Skip required check.
	 *
	 * @return string Required/optional HTML string.
	 */
	private static function getRequiredString( array $fieldArgs, bool $skipRequiredCheck ): string {
		if ( $skipRequiredCheck ) {
			return '';
		}

		if ( $fieldArgs['required'] ) {
			return '&nbsp;<abbr class="required" title="' . esc_attr__( 'Required', 'extra-product-data-for-woocommerce' ) . '">*</abbr>';
		}

		return '&nbsp;<span class="optional">(' . esc_html__( 'Optional', 'extra-product-data-for-woocommerce' ) . ')</span>';
	}

	/**
	 * Prepare price adjustment attributes and classes.
	 *
	 * @param array $fieldArgs           Field arguments.
	 * @param bool  $skipRequiredCheck   Skip required check.
	 *
	 * @return array Field arguments with price adjustment data.
	 */
	private static function preparePriceAdjustment( array $fieldArgs, bool $skipRequiredCheck ): array { // phpcs:ignore
		$adjustmentValue = (float) ( $fieldArgs['priceAdjustmentValue'] ?? 0 );

		if ( 0 !== $adjustmentValue ) {
			$fieldArgs['input_class'][] = 'exprdawc-price-adjustment-field';

			if ( ! in_array( $fieldArgs['type'], self::PRICE_ADJUSTMENT_TYPES, true ) ) {
				$fieldArgs['custom_attributes']['data-price-adjustment-type'] = esc_attr( $fieldArgs['price_adjustment_type'] );
				$fieldArgs['custom_attributes']['data-price-adjustment']      = esc_attr( $fieldArgs['priceAdjustmentValue'] );

				$plusMinus = ( $adjustmentValue > 0 ) ? '+' : '-';
				if ( 'percentage' === $fieldArgs['price_adjustment_type'] ) {
					$fieldArgs['required_string'] .= ' (' . $plusMinus . abs( $adjustmentValue ) . '%)';
				} else {
					$fieldArgs['required_string'] .= ' (' . $plusMinus . wc_price( abs( $adjustmentValue ) ) . ')';
				}
			}
		}

		return $fieldArgs;
	}

	/**
	 * Prepare option labels with price adjustments.
	 *
	 * @param array $fieldArgs Field arguments with options.
	 *
	 * @return array Field arguments with formatted option labels.
	 */
	private static function prepareOptionLabelsWithPrices( array $fieldArgs ): array {
		if ( empty( $fieldArgs['options'] ) || ! is_array( $fieldArgs['options'] ) ) {
			return $fieldArgs;
		}

		$fieldArgs['options'] = array_map(
			function ( $option ) use ( $fieldArgs ) {
				if ( ! isset( $option['priceAdjustmentValue'] ) || empty( $option['priceAdjustmentValue'] ) ) {
					return $option;
				}

				$adjustmentValue = (float) $option['priceAdjustmentValue'];
				$adjustmentType  = $option['price_adjustment_type'] ?? 'fixed';

				if ( 0 === $adjustmentValue ) {
					return $option;
				}

				$sign     = $adjustmentValue > 0 ? '+' : '-';
				$absValue = abs( $adjustmentValue );

				if ( 'percentage' === $adjustmentType ) {
					$priceText = sprintf( '%s%d%%', $sign, $absValue );
				} else {
					$currencySymbol = get_woocommerce_currency_symbol();
					$priceText      = sprintf( '%s%.2f %s', $sign, $absValue, $currencySymbol );
				}

				$option['label'] = $option['label'] . ' (' . $priceText . ')';

				return $option;
			},
			$fieldArgs['options']
		);

		return $fieldArgs;
	}

	/**
	 * Normalize field type (convert aliases to standard types).
	 *
	 * @param array $fieldArgs Field arguments.
	 *
	 * @return array Field arguments with normalized type.
	 */
	private static function normalizeFieldType( array $fieldArgs ): array {
		switch ( $fieldArgs['type'] ) {
			case 'long_text':
				$fieldArgs['type']                      = 'textarea';
				$fieldArgs['custom_attributes']['rows'] = $fieldArgs['rows'] ?? 2;
				$fieldArgs['custom_attributes']['cols'] = $fieldArgs['cols'] ?? 5;
				break;

			case 'yes-no':
				$fieldArgs['type']    = 'select';
				$fieldArgs['options'] = array(
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

		return $fieldArgs;
	}

	/**
	 * Validate field arguments.
	 *
	 * @param array $fieldArgs Field arguments.
	 *
	 * @return bool True if valid, false otherwise.
	 */
	private static function validateFieldArgs( array $fieldArgs ): bool {
		return ! empty( $fieldArgs['css_id'] ) && ! empty( $fieldArgs['name'] );
	}

	/**
	 * Render the field template.
	 *
	 * @param array $fieldArgs Prepared field arguments.
	 *
	 * @return void
	 */
	private static function renderFieldTemplate( array $fieldArgs ): void {
		$templatePath = EXPRDAWC_FIELDS_TEMPLATES_PATH;

		$requiredString   = $fieldArgs['required_string'] ?? '';
		$customAttributes = self::buildAttributesArray( $fieldArgs['custom_attributes'] );
		// Make variables available to templates using legacy variable names.
		$field_args        = $fieldArgs;
		$required_string   = $requiredString;
		$custom_attributes = $customAttributes;

		if ( file_exists( $templatePath . 'custom-field-start.php' ) ) {
			include $templatePath . 'custom-field-start.php';
		}

		$fieldTemplate = $templatePath . $fieldArgs['type'] . '.php';

		if ( file_exists( $fieldTemplate ) ) {
			include $fieldTemplate;
		} elseif ( file_exists( $templatePath . 'text.php' ) ) {
			include $templatePath . 'text.php';
		}

		if ( file_exists( $templatePath . 'custom-field-end.php' ) ) {
			include $templatePath . 'custom-field-end.php';
		}
	}

	/**
	 * Build HTML attribute string array for templates.
	 *
	 * @param array $attrs Attributes array.
	 *
	 * @return array Array of HTML attributes strings.
	 */
	private static function buildAttributesArray( array $attrs ): array {
		$attributes = array();

		foreach ( $attrs as $key => $value ) {
			$attributes[] = esc_attr( $key ) . '="' . esc_attr( $value ) . '"';
		}

		return $attributes;
	}

	/**
	 * Check if the product have required fields.
	 *
	 * @param int $productId The product ID.
	 *
	 * @return bool True if the product have required fields.
	 */
	public static function checkRequiredFields( $productId ): bool {
		$product = wc_get_product( $productId );
		if ( ! $product ) {
			return false;
		}

		if ( $product->is_type( ProductType::VARIATION ) ) {
			$product = wc_get_product( $product->get_parent_id() );
		}

		if ( ! $product ) {
			return false;
		}

		$customFields = self::getExtraProductFields( $product );

		if ( ! empty( $customFields ) ) {
			foreach ( $customFields as $inputFieldArray ) {
				if ( ! empty( $inputFieldArray['required'] ) ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Render a template file with the given variables.
	 *
	 * @param string $template The template file to render.
	 * @param array  $args     Variables to pass to the template.
	 */
	public static function renderTemplate( string $template, array $args = array() ): void {
		$path = trailingslashit( EXPRDAWC_TEMPLATES ) . ltrim( $template, '/' );

		if ( ! file_exists( $path ) ) {
			return;
		}

		// Extract args to make them available as variables in the template.
		// phpcs:ignore WordPress.PHP.DontExtract.extract_extract
		extract( $args );

		// Use require instead of load_template to avoid require_once issues in tests.
		require $path;
	}

	/**
	 * Convert a field label to a standardized index/key.
	 *
	 * @param string $label The field label to convert.
	 * @return string The standardized field index.
	 */
	public static function getFieldIndexFromLabel( string $label ): string {
		return strtolower( str_replace( array( ' ', '-' ), '_', sanitize_title( $label ) ) );
	}

	/**
	 * Resolve the effective field key for a field definition.
	 *
	 * Uses the field's 'id' when available (normalizing dashes to underscores
	 * to match the generated HTML name attribute), otherwise falls back to
	 * deriving the key from the field label via getFieldIndexFromLabel().
	 *
	 * This key is used for:
	 * - Reading POST data (exprdawc_custom_field_input[<key>])
	 * - Indexing $field_values during save
	 * - Matching stored metadata back to the field definition
	 *
	 * @param array $field The field definition array (must contain at least 'label').
	 * @return string The resolved field key.
	 */
	public static function getFieldKey( array $field ): string {
		if ( ! empty( $field['id'] ) ) {
			return str_replace( '-', '_', $field['id'] );
		}

		return self::getFieldIndexFromLabel( $field['label'] ?? '' );
	}

	/**
	 * Get the extra product fields defined for a product.
	 *
	 * Centralizes access to the `_extra_product_fields` product meta so that all
	 * callers use a single, consistent entry point. Always returns an array.
	 *
	 * @param WC_Product $product The WooCommerce product.
	 * @return array Field definition array, or empty array when none are configured.
	 */
	public static function getExtraProductFields( WC_Product $product ): array {
		$fields = $product->get_meta( '_extra_product_fields', true );
		return is_array( $fields ) ? $fields : array();
	}

	/**
	 * Sanitize a field value based on its type.
	 *
	 * @param mixed $fieldValue The field value to sanitize.
	 * @return mixed The sanitized field value.
	 */
	public static function sanitizeFieldValue( $fieldValue ) {
		if ( is_array( $fieldValue ) ) {
			return array_map( 'sanitize_textarea_field', $fieldValue );
		}

		if ( is_string( $fieldValue ) || is_numeric( $fieldValue ) ) {
			return sanitize_textarea_field( $fieldValue );
		}

		return $fieldValue;
	}

	/**
	 * Get value from POST data for a field.
	 *
	 * @param string $fieldIndex The field index/key.
	 * @param string $postKey    The POST array key.
	 * @return mixed The field value or empty string.
	 */
	public static function getFieldValueFromPost( string $fieldIndex, string $postKey = 'exprdawc_custom_field_input' ) {

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.NonceVerification.Missing
		if ( isset( $_POST[ $postKey ][ $fieldIndex ] ) ) {
			$value = wp_unslash( $_POST[ $postKey ][ $fieldIndex ] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.NonceVerification.Missing
			return self::sanitizeFieldValue( $value );
		}

		return '';
	}

	/**
	 * Convert option values to indexed array.
	 *
	 * @param array $options Array of option arrays with 'value' key.
	 * @return array Array of option values.
	 */
	public static function getOptionValues( array $options ): array {
		return array_column( $options, 'value' );
	}

	/**
	 * Validate that selected values exist in available options.
	 *
	 * @param mixed $selectedValues    The selected value(s).
	 * @param array $availableOptions  Array of option arrays.
	 * @return bool True if valid.
	 */
	public static function validateOptionSelection( $selectedValues, array $availableOptions ): bool {
		$validOptions = self::getOptionValues( $availableOptions );
		if ( empty( $validOptions ) ) {
			return false;
		}

		$selected  = is_array( $selectedValues ) ? $selectedValues : array( $selectedValues );
		$intersect = array_intersect( $selected, $validOptions );

		return ! empty( $intersect ) && count( $intersect ) === count( $selected );
	}

	/**
	 * Validate field value based on its type.
	 *
	 * @param mixed  $fieldValue    The field value to validate.
	 * @param string $fieldType     The field type.
	 * @param array  $fieldOptions  Optional options for validation.
	 * @return array Array with 'valid' and 'message' keys.
	 */
	public static function validateFieldByType( $fieldValue, string $fieldType, array $fieldOptions = array() ): array {
		if ( empty( $fieldValue ) ) {
			return array(
				'valid'   => true,
				'message' => '',
			);
		}

		switch ( $fieldType ) {
			case 'email':
				if ( ! is_email( $fieldValue ) ) {
					return array(
						'valid'   => false,
						'message' => __( 'Please enter a valid email address.', 'extra-product-data-for-woocommerce' ),
					);
				}
				break;

			case 'number':
				if ( ! is_numeric( $fieldValue ) ) {
					return array(
						'valid'   => false,
						'message' => __( 'Please enter a valid number.', 'extra-product-data-for-woocommerce' ),
					);
				}
				break;

			case 'date':
				if ( ! strtotime( (string) $fieldValue ) ) {
					return array(
						'valid'   => false,
						'message' => __( 'Please enter a valid date.', 'extra-product-data-for-woocommerce' ),
					);
				}
				break;

			case 'radio':
			case 'checkbox':
			case 'select':
			case 'multiselect':
				if ( ! empty( $fieldOptions ) && ! self::validateOptionSelection( $fieldValue, $fieldOptions ) ) {
					return array(
						'valid'   => false,
						'message' => __( 'Please select a valid option.', 'extra-product-data-for-woocommerce' ),
					);
				}
				break;

			case 'url':
				if ( ! filter_var( $fieldValue, FILTER_VALIDATE_URL ) ) {
					return array(
						'valid'   => false,
						'message' => __( 'Please enter a valid URL.', 'extra-product-data-for-woocommerce' ),
					);
				}
				break;
		}

		return array(
			'valid'   => true,
			'message' => '',
		);
	}

	/**
	 * Checks whether editing is allowed for the given order based on the plugin setting.
	 *
	 * The setting stores a list of allowed order statuses (e.g. wc-pending, wc-processing).
	 * This method removes the `wc-` prefix and checks if the order has one of the allowed statuses.
	 *
	 * @param WC_Order $order The WooCommerce order object.
	 * @return bool True if editing is allowed, false otherwise.
	 */
	public static function is_order_editable( WC_Order $order ): bool {

		if ( ! $order ) {
			return false;
		}

		$allowed_statuses = get_option(
			'extra_product_data_allowed_order_statuses',
			array( 'wc-pending', 'wc-on-hold', 'wc-processing' )
		);

		if ( ! is_array( $allowed_statuses ) || empty( $allowed_statuses ) ) {
			return false;
		}

		// Remove wc- prefix.
		$allowed_statuses = array_map(
			array( OrderUtil::class, 'remove_status_prefix' ),
			$allowed_statuses
		);

		return $order->has_status( $allowed_statuses );
	}
}
