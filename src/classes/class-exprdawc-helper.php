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

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
/**
 * Class Exprdawc_Helper
 *
 * This class contains helper functions for the plugin.
 *
 * @package Exprdawc
 */
class Exprdawc_Helper {

	/**
	 * This function is responsible for generating the input field.
	 *
	 * @param array  $field The field arguments.
	 * @param string $value The field value.
	 * @param bool   $skip_required_check Whether to skip the required check or not.
	 * @param bool   $cart_page Whether the field is rendered on the cart page or not.
	 *
	 * @return void
	 */
	public static function generate_input_field( $field, $value = '', $skip_required_check = false, $cart_page = false ) {

		$defaults = array(
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
			'validate'               => array(),
			'data'                   => array(),
			'adjust_price'           => false,
			'price_adjustment_type'  => null,
			'price_adjustment_value' => null,
			'default'                => '',
			'value'                  => '',
			'help_text'              => '',
			'placeholder_text'       => '',
		);

		$field_args = array_merge( $defaults, $field );

		// If field have editable false, than set the disable attrubute auf true.
		if ( isset( $field_args['editable'] ) && ! $field_args['editable'] && ! $skip_required_check && ! $cart_page ) {
			$field_args['disabled'] = true;
		}

		// Unmap help_text to description and placeholder_text to placeholder.
		$field_args['description'] = $field_args['help_text'] ? $field_args['help_text'] : $field_args['description'];
		$field_args['placeholder'] = $field_args['placeholder_text'] ? $field_args['placeholder_text'] : $field_args['placeholder'];

		// Add Placeholder to the data-label attribute.
		$field_args['custom_attributes']['data-placeholder'] = $field_args['placeholder'];

		// Generate Field ID.
		$label_id         = strtolower( str_replace( ' ', '-', esc_html( $field_args['label'] ) ) );
		$field_args['id'] = strtolower( $field_args['id'] ? $field_args['id'] : str_replace( array( ' ', '_' ), '-', $field_args['id_prefix'] ) . '-' . $label_id );

		// Generate Field Name.
		$field_args['name'] = $field_args['name'] ? $field_args['name'] : str_replace( '-', '_', sanitize_title( $label_id ) );
		$field_args['name'] = $field_args['id_prefix'] . '[' . $field_args['name'] . ']';

		// Set Data label for the field.
		$field_args['custom_attributes']['data-label'] = $field_args['label'];

		// Generate Contotoinal Rules in json format.
		if ( isset( $field_args['conditional_rules'] ) && $field_args['conditional_logic'] ) {
			$field_args['data']['conditional_rules'] = wp_json_encode( $field_args['conditional_rules'] );
		}

		// Set the wrapper class. With type and required class.
		$field_args['wrapper_class'][] = 'exprdawc-field-wrapper';
		$field_args['wrapper_class'][] = 'exprdawc-field-wrapper-' . str_replace( '_', '-', $field_args['type'] );
		if ( $field_args['required'] && ! $skip_required_check ) {
			$field_args['wrapper_class'][] = 'exprdawc-field-wrapper-required';
		}

		// Set the input class. With type and required class.
		$field_args['input_class'][] = $field_args['id'] . '-input';
		$field_args['input_class'][] = 'exprdawc-field-input-' . str_replace( '_', '-', $field_args['type'] );
		if ( $field_args['required'] && ! $skip_required_check ) {
			$field_args['input_class'][] = 'exprdawc-field-input-required';
		}

		// Set the label class. With type and required class.
		$field_args['label_class'][] = $field_args['id'] . '-label';
		$field_args['label_class'][] = 'exprdawc-field-label-' . str_replace( '_', '-', $field_args['type'] );
		if ( $field_args['required'] && ! $skip_required_check ) {
			$field_args['label_class'][] = 'exprdawc-field-label-required';
		}

		// Set the description class. With type and required class.
		$field_args['description_class'][] = $field_args['id'] . '-description';
		$field_args['description_class'][] = 'exprdawc-field-description-' . str_replace( '_', '-', $field_args['type'] );
		if ( $field_args['required'] && ! $skip_required_check ) {
			$field_args['description_class'][] = 'exprdawc-field-description-required';
		}

		// Set the input wrapper class. With type and required class.
		$field_args['input_wrapper_class'][] = $field_args['id'] . '-input-wrapper';
		$field_args['input_wrapper_class'][] = 'exprdawc-field-input-wrapper-' . str_replace( '_', '-', $field_args['type'] );
		if ( $field_args['required'] && ! $skip_required_check ) {
			$field_args['input_wrapper_class'][] = 'exprdawc-field-input-wrapper-required';
		}

		// Add 'input-text' class to text fields for WC 2.4 compatibility. For text, date, url, email, tel, number, textarea, select, multiselect fields.
		if ( in_array( $field_args['type'], array( 'text', 'date', 'url', 'email', 'tel', 'number', 'textarea', 'select', 'multiselect' ), true ) ) {
			$field_args['input_class'][] = 'input-text';
		}

		// Set the value of the field. If empty than look on field_arg default value.
		$selected_key = 'attribute_' . str_replace( '-', '_', sanitize_title( $label_id ) );
		if ( isset( $_REQUEST[ $selected_key ] ) ) { // phpcs:ignore
			$value = wc_clean( wp_unslash( $_REQUEST[ $selected_key ] ) ); // phpcs:ignore
		}
		$field_args['value'] = $value ? $value : $field_args['default'];

		// Set the required attribute.
		$required_string = '';
		if ( $field_args['required'] && ! $skip_required_check ) {
			$field_args['custom_attributes']['required'] = 'required';
			$required_string                             = '&nbsp;<abbr class="required" title="' . esc_attr__( 'Required', 'extra-product-data-for-woocommerce' ) . '">*</abbr>';
		} elseif ( ! $field_args['required'] && ! $skip_required_check ) {
			$required_string = '&nbsp;<span class="optional">(' . esc_html__( 'Optional', 'extra-product-data-for-woocommerce' ) . ')</span>';
		}

		// Set a adjusted price hint if the field is a price adjustment field.
		if ( $field_args['adjust_price'] ) {
			$field_args['input_class'][] = 'exprdawc-price-adjustment-field';
		}

		if ( $field_args['adjust_price'] && ! in_array( $field_args['type'], array( 'checkbox', 'radio', 'select' ), true ) ) {
			$field_args['input_class'][]                                   = 'exprdawc-price-adjustment-field';
			$field_args['custom_attributes']['data-price-adjustment-type'] = $field_args['price_adjustment_type'];
			$field_args['custom_attributes']['data-price-adjustment']      = $field_args['price_adjustment_value'];
			// Add Price in $required_string.
			$plus_minus = 0 !== $field_args['price_adjustment_value'] ? ( 0 < $field_args['price_adjustment_value'] ? '+' : '-' ) : '';
			$required_string .= ' (' . $plus_minus . wc_price( $field_args['price_adjustment_value'] ) . ')';
		}

		// Set type specific classes and attributes.
		switch ( $field_args['type'] ) {
			case 'long_text':
			case 'textarea':
				$field_args['type']                      = 'textarea';
				$field_args['custom_attributes']['rows'] = isset( $field_args['rows'] ) ? $field_args['rows'] : 2;
				$field_args['custom_attributes']['cols'] = isset( $field_args['cols'] ) ? $field_args['cols'] : 5;
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

		// Set the custom attributes. Remove empty custom attributes and attributes with empty values.
		$field_args['custom_attributes'] = array_filter( (array) $field_args['custom_attributes'], 'strlen' );

		if ( $field_args['maxlength'] ) {
			$field_args['custom_attributes']['maxlength'] = absint( $field_args['maxlength'] );
		}

		if ( $field_args['minlength'] ) {
			$field_args['custom_attributes']['minlength'] = absint( $field_args['minlength'] );
		}

		if ( ! empty( $field_args['autocomplete'] ) ) {
			$field_args['custom_attributes']['autocomplete'] = $field_args['autocomplete'];
		}

		if ( true === $field_args['autofocus'] ) {
			$field_args['custom_attributes']['autofocus'] = 'autofocus';
		}

		if ( true === $field_args['disabled'] ) {
			$field_args['custom_attributes']['disabled'] = 'disabled';
			$field_args['input_class'][]                 = 'disabled';
		}

		if ( $field_args['description'] ) {
			$field_args['custom_attributes']['aria-describedby'] = $field_args['id'] . '-description';
		}

		if ( ! empty( $field_args['data'] ) && is_array( $field_args['data'] ) ) {
			foreach ( $field_args['data'] as $attribute => $attribute_value ) {
				// _ to - and string are lowercase.
				$attribute                                = str_replace( '_', '-', $attribute );
				$attribute                                = strtolower( $attribute );
				$name                                     = 'data-' . esc_attr( $attribute );
				$field_args['custom_attributes'][ $name ] = esc_attr( $attribute_value );
			}
		}

		if ( ! empty( $field_args['custom_attributes'] ) && is_array( $field_args['custom_attributes'] ) ) {
			foreach ( $field_args['custom_attributes'] as $attribute => $attribute_value ) {
				$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';
			}
		}

		if ( ! empty( $field_args['validate'] ) ) {
			foreach ( $field_args['validate'] as $validate ) {
				$field_args['input_class'][] = 'validate-' . $validate;
			}
		}

		// Include the field template.
		include EXPRDAWC_FIELDS_TEMPLATES_PATH . 'custom-field-start.php';

		if ( file_exists( EXPRDAWC_FIELDS_TEMPLATES_PATH . $field_args['type'] . '.php' ) ) {
			include EXPRDAWC_FIELDS_TEMPLATES_PATH . $field_args['type'] . '.php';
		} else {
			include EXPRDAWC_FIELDS_TEMPLATES_PATH . 'text.php';
		}

		include EXPRDAWC_FIELDS_TEMPLATES_PATH . 'custom-field-end.php';
	}

	/**
	 * Check if the product have required fields.
	 *
	 * @param int $product_id The product ID.
	 *
	 * @return bool True if the product have required fields, false otherwise.
	 */
	public static function check_required_fields( $product_id ) {
		$product = wc_get_product( $product_id );
		if ( $product->is_type( 'variation' ) ) {
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
}
