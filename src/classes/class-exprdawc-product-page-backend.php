<?php
/**
 * Created on Fri Nov 01 2024
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
 * Class Selection
 *
 * This class represents a selection of items. It provides methods to add, remove,
 * and retrieve items from the selection. The selection can be manipulated and queried
 * to perform various operations on the contained items.
 *
 * @package Exprdawc
 */
class Exprdawc_Product_Page_Backend {

	/**
	 * Contructor.
	 */
	public function __construct() {

		// Add custom tab in product edit page.
		add_filter( 'woocommerce_product_data_tabs', array( $this, 'exprdawc_add_custom_product_tab' ) );
		add_action( 'woocommerce_product_data_panels', array( $this, 'exprdawc_add_custom_product_fields' ) );

		// Save custom fields.
		add_action( 'woocommerce_process_product_meta', array( $this, 'exprdawc_save_extra_product_fields' ) );

		// Add Scripts in head and footer.
		add_action( 'admin_enqueue_scripts', array( $this, 'exprdawc_show_general_tab' ) );

		// Import custom fields.
		add_action( 'wp_ajax_exprdawc_import_custom_fields', array( $this, 'exprdawc_import_custom_fields' ) );
	}

	/**
	 * Add a custom tab in the product edit page.
	 *
	 * @param array $tabs Array of tabs.
	 * @return array
	 */
	public function exprdawc_add_custom_product_tab( array $tabs ): array {
		$class                 = apply_filters( 'exprdawc_custom_product_tab_class', 'show_if_simple show_if_variable' );
		$tabs['custom_fields'] = array(
			'label'  => __( 'Extra Product Input', 'extra-product-data-for-woocommerce' ),
			'target' => 'extra-product-data',
			'class'  => $class,
		);
		return $tabs;
	}

	/**
	 * Add custom fields to the product edit page.
	 *
	 * This function is responsible for adding custom fields to the product edit page
	 * in the WooCommerce admin interface. It ensures that the fields are displayed
	 * correctly and can be used to store additional product data.
	 */
	public function exprdawc_add_custom_product_fields() {
		global $post;
		$product       = wc_get_product( $post );
		$custom_fields = $product->get_meta( '_extra_product_fields', true );
		include EXPRDAWC_TEMPLATES . 'html-tab-extra-attributes.php';
	}

	/**
	 * Enqueue scripts for the general tab.
	 *
	 * @return void
	 */
	public function exprdawc_show_general_tab() {
		wp_enqueue_script( 'exprdawc-wc-meta-boxes-js', EXPRDAWC_ASSETS_JS . 'wc-meta-boxes-product.js', array( 'jquery', 'jquery-ui-sortable' ), '1.0.0', true );
		wp_localize_script(
			'exprdawc-wc-meta-boxes-js',
			'exprdawc_admin_meta_boxes',
			array(
				'edit_exprdawc_nonce'                  => wp_create_nonce( 'edit_exprdawc_nonce' ),
				'label_placeholder'                    => esc_html__( 'Name of the label', 'extra-product-data-for-woocommerce' ),
				'short_text'                           => esc_html__( 'Short Text', 'extra-product-data-for-woocommerce' ),
				'long_text'                            => esc_html__( 'Long Text', 'extra-product-data-for-woocommerce' ),
				'email'                                => esc_html__( 'Email', 'extra-product-data-for-woocommerce' ),
				'number'                               => esc_html__( 'Number', 'extra-product-data-for-woocommerce' ),
				'date'                                 => esc_html__( 'Date', 'extra-product-data-for-woocommerce' ),
				'yes_no'                               => esc_html__( 'Yes/No', 'extra-product-data-for-woocommerce' ),
				'radio'                                => esc_html__( 'Radio Button', 'extra-product-data-for-woocommerce' ),
				'checkbox'                             => esc_html__( 'Checkbox', 'extra-product-data-for-woocommerce' ),
				'select'                               => esc_html__( 'Select', 'extra-product-data-for-woocommerce' ),
				'placeholder_text'                     => esc_html__( 'Placeholder Text', 'extra-product-data-for-woocommerce' ),
				'help_text'                            => esc_html__( 'Help Text', 'extra-product-data-for-woocommerce' ),
				'remove'                               => esc_html__( 'Remove', 'extra-product-data-for-woocommerce' ),
				'action'                               => esc_html__( 'Action', 'extra-product-data-for-woocommerce' ),
				'option_label'                         => esc_html__( 'Option Label', 'extra-product-data-for-woocommerce' ),
				'option_value'                         => esc_html__( 'Option Value', 'extra-product-data-for-woocommerce' ),
				'add_option'                           => esc_html__( 'Add Option', 'extra-product-data-for-woocommerce' ),
				'confirm_delete'                       => esc_html__( 'Are you sure you want to delete this field?', 'extra-product-data-for-woocommerce' ),
				'option_label_placeholder'             => esc_html__( 'Enter option label', 'extra-product-data-for-woocommerce' ),
				'option_value_placeholder'             => esc_html__( 'Enter option value', 'extra-product-data-for-woocommerce' ),
				'option_label_help'                    => esc_html__( 'This is the label for the option.', 'extra-product-data-for-woocommerce' ),
				'option_value_help'                    => esc_html__( 'This is the value for the option.', 'extra-product-data-for-woocommerce' ),
				'default_option_help'                  => esc_html__( 'Set as default option.', 'extra-product-data-for-woocommerce' ),
				'copySuccessMsg'                       => esc_html__( 'Copied to clipboard', 'extra-product-data-for-woocommerce' ),
				'copyErrorMsg'                         => esc_html__( 'Failed to copy', 'extra-product-data-for-woocommerce' ),
				'enterExportString'                    => esc_html__( 'Please enter the export string.', 'extra-product-data-for-woocommerce' ),
				'sureImportQuestion'                   => esc_html__( 'Are you sure you want to import the custom fields? All existing customs fields are delete.', 'extra-product-data-for-woocommerce' ),
				'importSuccessMsg'                     => esc_html__( 'Import successful', 'extra-product-data-for-woocommerce' ),
				'importErrorMsg'                       => esc_html__( 'Import failed', 'extra-product-data-for-woocommerce' ),
				'emptyExportMsg'                       => esc_html__( 'No custom fields to export', 'extra-product-data-for-woocommerce' ),
				'require_input'                        => esc_html__( 'Require input', 'extra-product-data-for-woocommerce' ),
				'enable_autofocus'                     => esc_html__( 'Autofocus this field on product page', 'extra-product-data-for-woocommerce' ),
				'placeholder_text_help'                => esc_html__( 'Placeholder Text', 'extra-product-data-for-woocommerce' ),
				'rows'                                 => esc_html__( 'Rows', 'extra-product-data-for-woocommerce' ),
				'collumns'                             => esc_html__( 'Collumns', 'extra-product-data-for-woocommerce' ),
				'default_value'                        => esc_html__( 'Default Value', 'extra-product-data-for-woocommerce' ),
				'min_length'                           => esc_html__( 'Min Length', 'extra-product-data-for-woocommerce' ),
				'max_length'                           => esc_html__( 'Max Length', 'extra-product-data-for-woocommerce' ),
				'enter_default_text'                   => esc_html__( 'Enter default text', 'extra-product-data-for-woocommerce' ),
				'sureAnotherAutocompleCheckedQuestion' => esc_html__( 'Another autocomplete field is already checked. Do you want to uncheck it?', 'extra-product-data-for-woocommerce' ),
				'autocomplete_function'                => esc_html__( 'Autocomplete Function', 'extra-product-data-for-woocommerce' ),
				'autocomplete_on'                      => esc_html__( 'On (default)', 'extra-product-data-for-woocommerce' ),
				'autocomplete_off'                     => esc_html__( 'Off', 'extra-product-data-for-woocommerce' ),
				'address_level1'                       => esc_html__( 'Address Level 1', 'extra-product-data-for-woocommerce' ),
				'address_level2'                       => esc_html__( 'Address Level 2', 'extra-product-data-for-woocommerce' ),
				'address_level3'                       => esc_html__( 'Address Level 3', 'extra-product-data-for-woocommerce' ),
				'address_level4'                       => esc_html__( 'Address Level 4', 'extra-product-data-for-woocommerce' ),
				'address_line1'                        => esc_html__( 'Address Line 1', 'extra-product-data-for-woocommerce' ),
				'address_line2'                        => esc_html__( 'Address Line 2', 'extra-product-data-for-woocommerce' ),
				'address_line3'                        => esc_html__( 'Address Line 3', 'extra-product-data-for-woocommerce' ),
				'bday'                                 => esc_html__( 'Birthday', 'extra-product-data-for-woocommerce' ),
				'bday_day'                             => esc_html__( 'Birthday Day', 'extra-product-data-for-woocommerce' ),
				'bday_month'                           => esc_html__( 'Birthday Month', 'extra-product-data-for-woocommerce' ),
				'bday_year'                            => esc_html__( 'Birthday Year', 'extra-product-data-for-woocommerce' ),
				'cc_additional_name'                   => esc_html__( 'Credit Card Additional Name', 'extra-product-data-for-woocommerce' ),
				'cc_csc'                               => esc_html__( 'Credit Card CSC', 'extra-product-data-for-woocommerce' ),
				'cc_exp'                               => esc_html__( 'Credit Card Expiry', 'extra-product-data-for-woocommerce' ),
				'cc_exp_month'                         => esc_html__( 'Credit Card Expiry Month', 'extra-product-data-for-woocommerce' ),
				'cc_exp_year'                          => esc_html__( 'Credit Card Expiry Year', 'extra-product-data-for-woocommerce' ),
				'cc_family_name'                       => esc_html__( 'Credit Card Family Name', 'extra-product-data-for-woocommerce' ),
				'cc_given_name'                        => esc_html__( 'Credit Card Given Name', 'extra-product-data-for-woocommerce' ),
				'cc_name'                              => esc_html__( 'Credit Card Name', 'extra-product-data-for-woocommerce' ),
				'cc_number'                            => esc_html__( 'Credit Card Number', 'extra-product-data-for-woocommerce' ),
				'cc_type'                              => esc_html__( 'Credit Card Type', 'extra-product-data-for-woocommerce' ),
				'country'                              => esc_html__( 'Country', 'extra-product-data-for-woocommerce' ),
				'country_name'                         => esc_html__( 'Country Name', 'extra-product-data-for-woocommerce' ),
				'language'                             => esc_html__( 'Language', 'extra-product-data-for-woocommerce' ),
				'photo'                                => esc_html__( 'Photo', 'extra-product-data-for-woocommerce' ),
				'postal_code'                          => esc_html__( 'Postal Code', 'extra-product-data-for-woocommerce' ),
				'sex'                                  => esc_html__( 'Sex', 'extra-product-data-for-woocommerce' ),
				'street_address'                       => esc_html__( 'Street Address', 'extra-product-data-for-woocommerce' ),
				'tel'                                  => esc_html__( 'Telephone', 'extra-product-data-for-woocommerce' ),
				'tel_area_code'                        => esc_html__( 'Telephone Area Code', 'extra-product-data-for-woocommerce' ),
				'tel_country_code'                     => esc_html__( 'Telephone Country Code', 'extra-product-data-for-woocommerce' ),
				'tel_extension'                        => esc_html__( 'Telephone Extension', 'extra-product-data-for-woocommerce' ),
				'tel_local'                            => esc_html__( 'Telephone Local', 'extra-product-data-for-woocommerce' ),
				'tel_local_prefix'                     => esc_html__( 'Telephone Local Prefix', 'extra-product-data-for-woocommerce' ),
				'tel_local_suffix'                     => esc_html__( 'Telephone Local Suffix', 'extra-product-data-for-woocommerce' ),
				'tel_national'                         => esc_html__( 'Telephone National', 'extra-product-data-for-woocommerce' ),
				'transaction_amount'                   => esc_html__( 'Transaction Amount', 'extra-product-data-for-woocommerce' ),
				'transaction_currency'                 => esc_html__( 'Transaction Currency', 'extra-product-data-for-woocommerce' ),
				'url'                                  => esc_html__( 'URL', 'extra-product-data-for-woocommerce' ),
				'and'                                  => esc_html__( 'AND', 'extra-product-data-for-woocommerce' ),
				'or'                                   => esc_html__( 'OR', 'extra-product-data-for-woocommerce' ),
				'selectFieldNone'                      => esc_html__( 'None', 'extra-product-data-for-woocommerce' ),
				'equals'                               => esc_html__( 'Equals', 'extra-product-data-for-woocommerce' ),
				'notEquals'                            => esc_html__( 'Not equals', 'extra-product-data-for-woocommerce' ),
				'greaterThan'                          => esc_html__( 'Greater than', 'extra-product-data-for-woocommerce' ),
				'lessThan'                             => esc_html__( 'Less than', 'extra-product-data-for-woocommerce' ),
				'enterValue'                           => esc_html__( 'Enter value', 'extra-product-data-for-woocommerce' ),
				'confirm_delete_rule'                  => esc_html__( 'Are you sure you want to delete this rule?', 'extra-product-data-for-woocommerce' ),
				'field_changed'                        => esc_html__( 'Field changed', 'extra-product-data-for-woocommerce' ),
				'field_is_empty'                       => esc_html__( 'Field is empty', 'extra-product-data-for-woocommerce' ),
				'field_is_not_empty'                   => esc_html__( 'Field is not empty', 'extra-product-data-for-woocommerce' ),
				'enable_conditional_logic'             => esc_html__( 'Enable conditional logic', 'extra-product-data-for-woocommerce' ),
				'conditionals'                         => esc_html__( 'Conditionals', 'extra-product-data-for-woocommerce' ),
				'conditionals_description'             => esc_html__( 'Only show this field when conditional rules are true.', 'extra-product-data-for-woocommerce' ),
				'pleaseSaveBeforeExportMsg'            => esc_html__( 'Please save your changes before exporting.', 'extra-product-data-for-woocommerce' ),
				'enable_editable'                      => esc_html__( 'User can edit the field afterwards', 'extra-product-data-for-woocommerce' ),
				'enable_price_adjustment'              => esc_html__( 'Enable price adjustment', 'extra-product-data-for-woocommerce' ),
				'price_adjustment_type'                => esc_html__( 'Price Adjustment Type', 'extra-product-data-for-woocommerce' ),
				'price_adjustment_value'               => esc_html__( 'Price Adjustment Value', 'extra-product-data-for-woocommerce' ),
				'fixed'                                => esc_html__( 'Fixed Price', 'extra-product-data-for-woocommerce' ),
				'percentage'                           => esc_html__( 'Percentage Price', 'extra-product-data-for-woocommerce' ),
				'quantity'                             => esc_html__( 'Price per Quantity', 'extra-product-data-for-woocommerce' ),
				'default_selected'                     => esc_html__( 'Default selected', 'extra-product-data-for-woocommerce' ),
			)
		);
	}

	/**
	 * Saves custom product fields for a WooCommerce product.
	 *
	 * This function is hooked to the save post action and is responsible for
	 * saving custom product fields submitted via the product edit page.
	 *
	 * @param int $post_id The ID of the product being saved.
	 */
	public function exprdawc_save_extra_product_fields( $post_id ) {

		// Check if extra product fields are set.
		if ( isset( $_POST['extra_product_fields'] ) ) { // phpcs:ignore

			// Get the product.
			$product = wc_get_product( $post_id );

			// UNSLASH: Remove slashes from the input data. Sanitized below.
			$extra_product_fields = wp_unslash( $_POST['extra_product_fields'] ); // phpcs:ignore

			// SANITIZE: Clean the input data.
			$custom_fields = array_map(
				function ( $field ) {

					// SANITIZE: Clean the input data.
					$label                  = sanitize_text_field( $field['label'] );
					$type                   = sanitize_text_field( $field['type'] );
					$required               = isset( $field['required'] ) ? 1 : 0;
					$conditional_logic      = isset( $field['conditional_logic'] ) ? 1 : 0;
					$placeholder_text       = sanitize_text_field( $field['placeholder_text'] );
					$help_text              = sanitize_text_field( $field['help_text'] );
					$autocomplete           = isset( $field['autocomplete'] ) ? sanitize_text_field( $field['autocomplete'] ) : '';
					$autofocus              = isset( $field['autofocus'] ) ? true : false;
					$index                  = isset( $field['index'] ) ? absint( $field['index'] ) : 0;
					$editable               = isset( $field['editable'] ) ? true : false;
					$adjust_price           = isset( $field['adjust_price'] ) ? true : false;
					$price_adjustment_type  = sanitize_text_field( $field['price_adjustment_type'] );
					$price_adjustment_value = sanitize_text_field( $field['price_adjustment_value'] );

					// Conditional Logic.
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

					// For Checbox, Radio and Select.
					$options = array();
					if ( isset( $field['options'] ) && is_array( $field['options'] ) ) {
						foreach ( $field['options'] as $key => $option ) {
							$options[] = array(
								'label'                  => ! empty( $option['label'] ) ? sanitize_text_field( $option['label'] ) : 'Option ' . $key,
								'value'                  => ! empty( $option['value'] ) ? sanitize_text_field( $option['value'] ) : sanitize_text_field( $option['label'] ),
								'price_adjustment_type'  => isset( $option['price_adjustment_type'] ) ? sanitize_text_field( $option['price_adjustment_type'] ) : '',
								'price_adjustment_value' => isset( $option['price_adjustment_value'] ) ? sanitize_text_field( $option['price_adjustment_value'] ) : '',
								'default'                => isset( $option['default'] ) ? sanitize_text_field( $option['default'] ) : 0,
							);
						}
					}

					$default = isset( $field['default'] ) ? sanitize_text_field( $field['default'] ) : '';

					// For Text.
					$minlength = isset( $field['minlength'] ) ? absint( $field['minlength'] ) : 0;
					$maxlength = isset( $field['maxlength'] ) ? absint( $field['maxlength'] ) : 0;

					// For Textarea.
					$rows = isset( $field['rows'] ) ? absint( $field['rows'] ) : 0;
					$cols = isset( $field['cols'] ) ? absint( $field['cols'] ) : 0;

					// VALIDATE: Ensure the data meets the required criteria.
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
						'label'                  => $label,
						'type'                   => $type,
						'required'               => $required,
						'conditional_logic'      => $conditional_logic,
						'placeholder_text'       => $placeholder_text,
						'help_text'              => $help_text,
						'options'                => $options,
						'default'                => $default,
						'minlength'              => $minlength,
						'maxlength'              => $maxlength,
						'rows'                   => $rows,
						'cols'                   => $cols,
						'autocomplete'           => $autocomplete,
						'autofocus'              => $autofocus,
						'conditional_rules'      => $conditional_logic_rules,
						'index'                  => $index,
						'editable'               => $editable,
						'adjust_price'           => $adjust_price,
						'price_adjustment_type'  => $price_adjustment_type,
						'price_adjustment_value' => $price_adjustment_value,
					);
				},
				$extra_product_fields
			);

			// Remove any empty values.
			$custom_fields = array_filter( $custom_fields );

			// Save the custom fields to the product.
			$product->update_meta_data( '_extra_product_fields', $custom_fields );

		} else {
			// Get the product.
			$product = wc_get_product( $post_id );

			// Delete the custom fields from the product.
			$product->delete_meta_data( '_extra_product_fields' );

		}

		// Save the product.
		$product->save();
	}

	/**
	 * Import custom fields.
	 *
	 * This function is responsible for importing custom fields from a JSON string
	 * and saving them to the product meta data.
	 */
	public function exprdawc_import_custom_fields() {

		// SECURITY: Check if the request is valid.
		check_ajax_referer( 'edit_exprdawc_nonce', 'security' );

		// VALIDATE: Ensure the product ID is set.
		if ( ! current_user_can( 'edit_product', $_POST['product_id'] ) ) { // phpcs:ignore
			wp_send_json_error( 'You do not have permission to edit this product.' );
		}

		// VALIDATE: Ensure the product ID is valid.
		$product_id = intval( $_POST['product_id'] ); // phpcs:ignore

		// VALIDATE: Ensure the product ID is valid.
		if ( 0 === $product_id ) {
			wp_send_json_error( 'Invalid product ID.' );
		}

		// Get Product.
		$product = wc_get_product( $product_id );
		if ( ! $product ) {
			wp_send_json_error( 'Invalid product ID.' );
		}

		// UNSLASH: Remove slashes from the input data.
		$export_string = wp_unslash( $_POST['export_string'] ); // phpcs:ignore

		// SANITIZE: Clean the input data.
		$custom_fields = json_decode( $export_string, true );

		// Validate JSON string.
		if ( json_last_error() !== JSON_ERROR_NONE ) {
			wp_send_json_error( 'Invalid JSON string.' );
		}

		// Save the sanitized and validated data.
		$product->update_meta_data( '_extra_product_fields', $custom_fields );

		// Save the product.
		$product->save();

		// Return success message.
		wp_send_json_success();
	}
}
