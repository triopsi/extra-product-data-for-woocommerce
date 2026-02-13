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
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<table class="exprdawc_fields_table" data-index="<?php echo esc_html( $index ); ?>">
	<tbody>
		<tr class="exprdawc_attribute">
			<td class="move"><i class="dashicons dashicons-move"></i></td>
			<td class="cl-arr"><i class="dashicons dashicons-arrow-up toggle-options"></i></td>
			<td class="exprdawc_attribute_input_name">								
				<input type="text" class="exprdawc_input exprdawc_textinput exprdawc_label field_name" name="extra_product_fields[<?php echo esc_html( $index ); ?>][label]" value="<?php echo esc_attr( $field['label'] ); ?>" placeholder="<?php esc_html_e( 'Name of the label', 'extra-product-data-for-woocommerce' ); ?>" />
			</td>
			<td>
				<select id="exprdawc_attribute_type_<?php echo esc_html( $index ); ?>" name="extra_product_fields[<?php echo esc_html( $index ); ?>][type]" class="exprdawc_input exprdawc_attribute_type">
					<option value="text" <?php selected( $field['type'], 'text' ); ?>><?php esc_html_e( 'Short Text', 'extra-product-data-for-woocommerce' ); ?></option>
					<option value="long_text" <?php selected( $field['type'], 'long_text' ); ?>><?php esc_html_e( 'Long Text', 'extra-product-data-for-woocommerce' ); ?></option>
					<option value="email" <?php selected( $field['type'], 'email' ); ?>><?php esc_html_e( 'Email', 'extra-product-data-for-woocommerce' ); ?></option>
					<option value="number" <?php selected( $field['type'], 'number' ); ?>><?php esc_html_e( 'Number', 'extra-product-data-for-woocommerce' ); ?></option>
					<option value="date" <?php selected( $field['type'], 'date' ); ?>><?php esc_html_e( 'Date', 'extra-product-data-for-woocommerce' ); ?></option>
					<option value="yes-no" <?php selected( $field['type'], 'yes-no' ); ?>><?php esc_html_e( 'Yes/No', 'extra-product-data-for-woocommerce' ); ?></option>
					<option value="radio" <?php selected( $field['type'], 'radio' ); ?>><?php esc_html_e( 'Radio Button', 'extra-product-data-for-woocommerce' ); ?></option>
					<option value="checkbox" <?php selected( $field['type'], 'checkbox' ); ?>><?php esc_html_e( 'Checkbox', 'extra-product-data-for-woocommerce' ); ?></option>
					<option value="select" <?php selected( $field['type'], 'select' ); ?>><?php esc_html_e( 'Select', 'extra-product-data-for-woocommerce' ); ?></option>
				</select>
			</td>
			<td>
				<button type="button" class="button exprdawc_remove_custom_field"><i class="dashicons dashicons-trash"></i></button>
				<button type="button" class="button exprdawc_copy_custom_field"><i class="dashicons dashicons-admin-page"></i></button>
				<input type="hidden" class="exprdawc_attribute_index" name="extra_product_fields[<?php echo esc_html( $index ); ?>][index]" value="<?php echo esc_attr( $field['index'] ?? $index ); ?>"/>
			</td>
		</tr>
		<tr class="exprdawc_options" style="display: none;">
			<td colspan="5">

				<!-- General Option/Settings -->
				<table class="exprdawc_settings_table exprdawc_general_table">
					<tbody>
						<tr>
							<td class="exprdawc_attribute_require_checkbox">
								<label class="exprdawc_label" for="exprdawc_text_required_<?php echo esc_html( $index ); ?>">
									<input type="checkbox" id="exprdawc_text_required_<?php echo esc_html( $index ); ?>" class="exprdawc_input exprdawc_checkbox checkbox" name="extra_product_fields[<?php echo esc_html( $index ); ?>][required]" value="1" <?php echo checked( 1, $field['required'], false ); ?> />
									<?php esc_html_e( 'Require input', 'extra-product-data-for-woocommerce' ); ?>
								</label>
								<label class="exprdawc_label" for="exprdawc_text_autofocus_<?php echo esc_html( $index ); ?>">
									<input type="checkbox" id="exprdawc_text_autofocus_<?php echo esc_html( $index ); ?>" class="exprdawc_input exprdawc_checkbox exprdawc_autocomplete_field checkbox" name="extra_product_fields[<?php echo esc_html( $index ); ?>][autofocus]" value="1" <?php echo checked( 1, $field['autofocus'] ?? 0, false ); ?> />
									<?php esc_html_e( 'Autofocus this field on product page', 'extra-product-data-for-woocommerce' ); ?>
								</label>
								<label class="exprdawc_label" for="exprdawc_text_editable_<?php echo esc_html( $index ); ?>">
									<input type="checkbox" id="exprdawc_text_editable_<?php echo esc_html( $index ); ?>" class="exprdawc_input exprdawc_checkbox exprdawc_editable_field checkbox" name="extra_product_fields[<?php echo esc_html( $index ); ?>][editable]" value="1" <?php echo checked( 1, $field['editable'] ?? 0, false ); ?> />
									<?php esc_html_e( 'User can edit the field afterwards', 'extra-product-data-for-woocommerce' ); ?>
								</label>
								<!-- Enable Conditional Logic and show table -->
								<label class="exprdawc_label" for="exprdawc_text_conditional_logic_<?php echo esc_html( $index ); ?>">
									<input type="checkbox" id="exprdawc_text_conditional_logic_<?php echo esc_html( $index ); ?>" class="exprdawc_input exprdawc_checkbox exprdawc_conditional_logic_field checkbox" name="extra_product_fields[<?php echo esc_html( $index ); ?>][conditional_logic]" value="1" <?php echo checked( 1, $field['conditional_logic'] ?? 0, false ); ?> />
									<?php esc_html_e( 'Enable conditional logic', 'extra-product-data-for-woocommerce' ); ?>
								</label>
								<!-- Enable adjust price and show table -->
								<label class="exprdawc_label" for="exprdawc_text_adjust_price_<?php echo esc_html( $index ); ?>">
									<input type="checkbox" id="exprdawc_text_adjust_price_<?php echo esc_html( $index ); ?>" class="exprdawc_input exprdawc_checkbox exprdawc_adjust_price_field checkbox" name="extra_product_fields[<?php echo esc_html( $index ); ?>][adjust_price]" value="1" <?php echo checked( 1, $field['adjust_price'] ?? 0, false ); ?> />
									<?php esc_html_e( 'Enable price adjustment', 'extra-product-data-for-woocommerce' ); ?>
								</label>
							</td>
							<td class="exprdawc_attribute_placeholder_text">
								<label class="exprdawc_label" for="exprdawc_text_placeholder_text_<?php echo esc_html( $index ); ?>"><?php esc_html_e( 'Placeholder Text', 'extra-product-data-for-woocommerce' ); ?></label>
								<input type="text" id="exprdawc_text_placeholder_text_<?php echo esc_html( $index ); ?>" class="exprdawc_input exprdawc_textinput exprdawc_placeholder" name="extra_product_fields[<?php echo esc_html( $index ); ?>][placeholder_text]" value="<?php echo esc_attr( $field['placeholder_text'] ?? '' ); ?>" placeholder="<?php esc_html_e( 'Placeholder Text', 'extra-product-data-for-woocommerce' ); ?>" <?php echo in_array( $field['type'], array( 'radio', 'checkbox' ) ) ? 'disabled' : ''; ?> />
							</td>
							<td class="exprdawc_attribute_help_text">
								<label class="exprdawc_label" for="exprdawc_text_help_text_<?php echo esc_html( $index ); ?>"><?php esc_html_e( 'Help Text', 'extra-product-data-for-woocommerce' ); ?></label>
								<input type="text" id="exprdawc_text_help_text_<?php echo esc_html( $index ); ?>" class="exprdawc_input exprdawc_textinput exprdawc_helptext" name="extra_product_fields[<?php echo esc_html( $index ); ?>][help_text]" value="<?php echo esc_attr( $field['help_text'] ); ?>" placeholder="<?php esc_html_e( 'Help Text', 'extra-product-data-for-woocommerce' ); ?>" />
							</td>
							<td>
								<label class="exprdawc_label" for="exprdawc_autocomplete_function_<?php echo esc_html( $index ); ?>"><?php esc_html_e( 'Autocomplete Function', 'extra-product-data-for-woocommerce' ); ?></label>
								<select id="exprdawc_autocomplete_function_<?php echo esc_html( $index ); ?>" name="extra_product_fields[<?php echo esc_html( $index ); ?>][autocomplete]" class="exprdawc_input exprdawc_attribute_type">
									<option value="on" <?php selected( $field['autocomplete'], 'on' ); ?>><?php esc_html_e( 'On (default)', 'extra-product-data-for-woocommerce' ); ?></option>	
									<option value="off" <?php selected( $field['autocomplete'], 'off' ); ?>><?php esc_html_e( 'Off', 'extra-product-data-for-woocommerce' ); ?></option>
									<option value="address-level1" <?php selected( $field['autocomplete'], 'address-level1' ); ?>><?php esc_html_e( 'Address Level 1', 'extra-product-data-for-woocommerce' ); ?></option>
									<option value="address-level2" <?php selected( $field['autocomplete'], 'address-level2' ); ?>><?php esc_html_e( 'Address Level 2', 'extra-product-data-for-woocommerce' ); ?></option>
									<option value="address-level3" <?php selected( $field['autocomplete'], 'address-level3' ); ?>><?php esc_html_e( 'Address Level 3', 'extra-product-data-for-woocommerce' ); ?></option>
									<option value="address-level4" <?php selected( $field['autocomplete'], 'address-level4' ); ?>><?php esc_html_e( 'Address Level 4', 'extra-product-data-for-woocommerce' ); ?></option>
									<option value="address-line1" <?php selected( $field['autocomplete'], 'address-line1' ); ?>><?php esc_html_e( 'Address Line 1', 'extra-product-data-for-woocommerce' ); ?></option>
									<option value="address-line2" <?php selected( $field['autocomplete'], 'address-line2' ); ?>><?php esc_html_e( 'Address Line 2', 'extra-product-data-for-woocommerce' ); ?></option>
									<option value="address-line3" <?php selected( $field['autocomplete'], 'address-line3' ); ?>><?php esc_html_e( 'Address Line 3', 'extra-product-data-for-woocommerce' ); ?></option>
									<option value="bday" <?php selected( $field['autocomplete'], 'bday' ); ?>><?php esc_html_e( 'Birthday', 'extra-product-data-for-woocommerce' ); ?></option>
									<option value="bday-day" <?php selected( $field['autocomplete'], 'bday-day' ); ?>><?php esc_html_e( 'Birthday Day', 'extra-product-data-for-woocommerce' ); ?></option>
									<option value="bday-month" <?php selected( $field['autocomplete'], 'bday-month' ); ?>><?php esc_html_e( 'Birthday Month', 'extra-product-data-for-woocommerce' ); ?></option>
									<option value="bday-year" <?php selected( $field['autocomplete'], 'bday-year' ); ?>><?php esc_html_e( 'Birthday Year', 'extra-product-data-for-woocommerce' ); ?></option>
									<option value="cc-additional-name" <?php selected( $field['autocomplete'], 'cc-additional-name' ); ?>><?php esc_html_e( 'Credit Card Additional Name', 'extra-product-data-for-woocommerce' ); ?></option>
									<option value="cc-csc" <?php selected( $field['autocomplete'], 'cc-csc' ); ?>><?php esc_html_e( 'Credit Card CSC', 'extra-product-data-for-woocommerce' ); ?></option>
									<option value="cc-exp" <?php selected( $field['autocomplete'], 'cc-exp' ); ?>><?php esc_html_e( 'Credit Card Expiry', 'extra-product-data-for-woocommerce' ); ?></option>
									<option value="cc-exp-month" <?php selected( $field['autocomplete'], 'cc-exp-month' ); ?>><?php esc_html_e( 'Credit Card Expiry Month', 'extra-product-data-for-woocommerce' ); ?></option>
									<option value="cc-exp-year" <?php selected( $field['autocomplete'], 'cc-exp-year' ); ?>><?php esc_html_e( 'Credit Card Expiry Year', 'extra-product-data-for-woocommerce' ); ?></option>
									<option value="cc-family-name" <?php selected( $field['autocomplete'], 'cc-family-name' ); ?>><?php esc_html_e( 'Credit Card Family Name', 'extra-product-data-for-woocommerce' ); ?></option>
									<option value="cc-given-name" <?php selected( $field['autocomplete'], 'cc-given-name' ); ?>><?php esc_html_e( 'Credit Card Given Name', 'extra-product-data-for-woocommerce' ); ?></option>
									<option value="cc-name" <?php selected( $field['autocomplete'], 'cc-name' ); ?>><?php esc_html_e( 'Credit Card Name', 'extra-product-data-for-woocommerce' ); ?></option>
									<option value="cc-number" <?php selected( $field['autocomplete'], 'cc-number' ); ?>><?php esc_html_e( 'Credit Card Number', 'extra-product-data-for-woocommerce' ); ?></option>
									<option value="cc-type" <?php selected( $field['autocomplete'], 'cc-type' ); ?>><?php esc_html_e( 'Credit Card Type', 'extra-product-data-for-woocommerce' ); ?></option>
									<option value="country" <?php selected( $field['autocomplete'], 'country' ); ?>><?php esc_html_e( 'Country', 'extra-product-data-for-woocommerce' ); ?></option>
									<option value="country-name" <?php selected( $field['autocomplete'], 'country-name' ); ?>><?php esc_html_e( 'Country Name', 'extra-product-data-for-woocommerce' ); ?></option>
									<option value="email" <?php selected( $field['autocomplete'], 'email' ); ?>><?php esc_html_e( 'Email', 'extra-product-data-for-woocommerce' ); ?></option>
									<option value="language" <?php selected( $field['autocomplete'], 'language' ); ?>><?php esc_html_e( 'Language', 'extra-product-data-for-woocommerce' ); ?></option>
									<option value="photo" <?php selected( $field['autocomplete'], 'photo' ); ?>><?php esc_html_e( 'Photo', 'extra-product-data-for-woocommerce' ); ?></option>
									<option value="postal-code" <?php selected( $field['autocomplete'], 'postal-code' ); ?>><?php esc_html_e( 'Postal Code', 'extra-product-data-for-woocommerce' ); ?></option>
									<option value="sex" <?php selected( $field['autocomplete'], 'sex' ); ?>><?php esc_html_e( 'Sex', 'extra-product-data-for-woocommerce' ); ?></option>
									<option value="street-address" <?php selected( $field['autocomplete'], 'street-address' ); ?>><?php esc_html_e( 'Street Address', 'extra-product-data-for-woocommerce' ); ?></option>
									<option value="tel" <?php selected( $field['autocomplete'], 'tel' ); ?>><?php esc_html_e( 'Telephone', 'extra-product-data-for-woocommerce' ); ?></option>
									<option value="tel-area-code" <?php selected( $field['autocomplete'], 'tel-area-code' ); ?>><?php esc_html_e( 'Telephone Area Code', 'extra-product-data-for-woocommerce' ); ?></option>
									<option value="tel-country-code" <?php selected( $field['autocomplete'], 'tel-country-code' ); ?>><?php esc_html_e( 'Telephone Country Code', 'extra-product-data-for-woocommerce' ); ?></option>
									<option value="tel-extension" <?php selected( $field['autocomplete'], 'tel-extension' ); ?>><?php esc_html_e( 'Telephone Extension', 'extra-product-data-for-woocommerce' ); ?></option>
									<option value="tel-local" <?php selected( $field['autocomplete'], 'tel-local' ); ?>><?php esc_html_e( 'Telephone Local', 'extra-product-data-for-woocommerce' ); ?></option>
									<option value="tel-local-prefix" <?php selected( $field['autocomplete'], 'tel-local-prefix' ); ?>><?php esc_html_e( 'Telephone Local Prefix', 'extra-product-data-for-woocommerce' ); ?></option>
									<option value="tel-local-suffix" <?php selected( $field['autocomplete'], 'tel-local-suffix' ); ?>><?php esc_html_e( 'Telephone Local Suffix', 'extra-product-data-for-woocommerce' ); ?></option>
									<option value="tel-national" <?php selected( $field['autocomplete'], 'tel-national' ); ?>><?php esc_html_e( 'Telephone National', 'extra-product-data-for-woocommerce' ); ?></option>
									<option value="transaction-amount" <?php selected( $field['autocomplete'], 'transaction-amount' ); ?>><?php esc_html_e( 'Transaction Amount', 'extra-product-data-for-woocommerce' ); ?></option>
									<option value="transaction-currency" <?php selected( $field['autocomplete'], 'transaction-currency' ); ?>><?php esc_html_e( 'Transaction Currency', 'extra-product-data-for-woocommerce' ); ?></option>
									<option value="url" <?php selected( $field['autocomplete'], 'url' ); ?>><?php esc_html_e( 'URL', 'extra-product-data-for-woocommerce' ); ?></option>
								</select>
							</td>
						</tr>
					</tbody>
				</table>
				<hr>

				<!-- Price Adjustment -->
				<table class="exprdawc_settings_table exprdawc_price_adjustment_table" style="display:<?php echo ( $field['adjust_price'] ?? false ) && ! in_array( $field['type'], array( 'checkbox', 'radio', 'select' ) ) ? 'table' : 'none'; ?>">
					<tbody>
						<tr>
							<td>
								<label class="exprdawc_label" for="exprdawc_price_adjustment_type_<?php echo esc_html( $index ); ?>"><?php esc_html_e( 'Price Adjustment Type', 'extra-product-data-for-woocommerce' ); ?></label>
								<select id="exprdawc_price_adjustment_type_<?php echo esc_html( $index ); ?>" name="extra_product_fields[<?php echo esc_html( $index ); ?>][price_adjustment_type]" class="exprdawc_input exprdawc_price_adjustment_type">
									<option value="fixed" <?php selected( $field['price_adjustment_type'], 'fixed' ); ?>><?php esc_html_e( 'Fixed Price', 'extra-product-data-for-woocommerce' ); ?></option>
									<option value="quantity" <?php selected( $field['price_adjustment_type'], 'quantity' ); ?>><?php esc_html_e( 'Price per Quantity', 'extra-product-data-for-woocommerce' ); ?></option>
									<option value="percentage" <?php selected( $field['price_adjustment_type'], 'percentage' ); ?>><?php esc_html_e( 'Percentage Price', 'extra-product-data-for-woocommerce' ); ?></option>
								</select>
							</td>
							<td>
								<label class="exprdawc_label" for="exprdawc_price_adjustment_value_<?php echo esc_html( $index ); ?>"><?php esc_html_e( 'Price Adjustment Value', 'extra-product-data-for-woocommerce' ); ?></label>
								<input type="number" id="exprdawc_price_adjustment_value_<?php echo esc_html( $index ); ?>" class="exprdawc_input exprdawc_price_adjustment_value" name="extra_product_fields[<?php echo esc_html( $index ); ?>][price_adjustment_value]" placeholder="0.00" value="<?php echo esc_attr( $field['price_adjustment_value'] ?? '' ); ?>" step="0.01" />
							</td>
						</tr>
					</tbody>
				</table>

				<!-- Conditional Logic -->
				<table class="exprdawc_settings_table exprdawc_conditional_logic_table" style="display:<?php echo ( $field['conditional_logic'] ?? false ) ? 'table' : 'none'; ?>">
					<tbody>
						<tr>
							<td colspan="3">
								<label class="exprdawc_label"><?php esc_html_e( 'Conditionals', 'extra-product-data-for-woocommerce' ); ?></label>
								<p><?php esc_html_e( 'Only show this field when conditional rules are true.', 'extra-product-data-for-woocommerce' ); ?></p>
								<div class="exprdawc_conditional_rules">
									<?php if ( ! empty( $field['conditional_rules'] ) ) : ?>
										<?php foreach ( $field['conditional_rules'] as $rule_group_index => $rule_group ) : ?>
											<div class="exprdawc_rule_group_container">
												<div class="exprdawc_rule_group">
													<?php foreach ( $rule_group as $rule_index => $rule ) : ?>
														<div class="exprdawc_rule">
															<select name="extra_product_fields[<?php echo esc_html( $index ); ?>][conditional_rules][<?php echo esc_html( $rule_group_index ); ?>][<?php echo esc_html( $rule_index ); ?>][field]" class="exprdawc_input exprdawc_conditional_field">
																<option value=""><?php esc_html_e( 'Select Field', 'extra-product-data-for-woocommerce' ); ?></option>
																<?php foreach ( $all_fields as $field_key => $field_label ) : ?>
																	<option value="<?php echo esc_attr( $field_key ); ?>" <?php selected( $rule['field'], $field_key ); ?>><?php echo esc_html( $field_label ); ?></option>
																<?php endforeach; ?>
															</select>
															<select name="extra_product_fields[<?php echo esc_html( $index ); ?>][conditional_rules][<?php echo esc_html( $rule_group_index ); ?>][<?php echo esc_html( $rule_index ); ?>][operator]" class="exprdawc_input exprdawc_conditional_operator">
																<option value="field_is_empty" <?php selected( $rule['operator'], 'field_is_empty' ); ?>><?php esc_html_e( 'Field is empty', 'extra-product-data-for-woocommerce' ); ?></option>
																<option value="field_is_not_empty" <?php selected( $rule['operator'], 'field_is_not_empty' ); ?>><?php esc_html_e( 'Field is not empty', 'extra-product-data-for-woocommerce' ); ?></option>		
																<option value="equals" <?php selected( $rule['operator'], 'equals' ); ?>><?php esc_html_e( 'Equals', 'extra-product-data-for-woocommerce' ); ?></option>
																<option value="not_equals" <?php selected( $rule['operator'], 'not_equals' ); ?>><?php esc_html_e( 'Not Equals', 'extra-product-data-for-woocommerce' ); ?></option>
																<option value="greater_than" <?php selected( $rule['operator'], 'greater_than' ); ?>><?php esc_html_e( 'Greater Than', 'extra-product-data-for-woocommerce' ); ?></option>
																<option value="less_than" <?php selected( $rule['operator'], 'less_than' ); ?>><?php esc_html_e( 'Less Than', 'extra-product-data-for-woocommerce' ); ?></option>
															</select>
															<input type="text" name="extra_product_fields[<?php echo esc_html( $index ); ?>][conditional_rules][<?php echo esc_html( $rule_group_index ); ?>][<?php echo esc_html( $rule_index ); ?>][value]" class="exprdawc_input exprdawc_conditional_value" value="<?php echo esc_attr( $rule['value'] ?? '' ); ?>" placeholder="<?php esc_html_e( 'Enter value', 'extra-product-data-for-woocommerce' ); ?>" />
															<button type="button" class="button remove_rule"><i class="dashicons dashicons-trash"></i></button>
															<button type="button" class="button add_rule">+ <?php esc_html_e( 'AND', 'extra-product-data-for-woocommerce' ); ?></button>
														</div>
													<?php endforeach; ?>
													</div>
												</div>
												
												<?php if ( $rule_group_index < count( $field['conditional_rules'] ) - 1 ) : ?>
													<h2><?php esc_html_e( 'Or', 'extra-product-data-for-woocommerce' ); ?></h2>
												<?php endif; ?>
											</div>
										<?php endforeach; ?>
									<?php else : ?>
										<div class="exprdawc_rule_group_container">
											<div class="exprdawc_rule_group">
												<div class="exprdawc_rule">
													<select name="extra_product_fields[<?php echo esc_html( $index ); ?>][conditional_rules][0][0][field]" class="exprdawc_input exprdawc_conditional_field">
														<option value=""><?php esc_html_e( 'None', 'extra-product-data-for-woocommerce' ); ?></option>
														<?php foreach ( $all_fields as $field_key => $field_label ) : ?>
															<option value="<?php echo esc_attr( $field_key ); ?>"><?php echo esc_html( $field_label ); ?></option>
														<?php endforeach; ?>
													</select>
													<select name="extra_product_fields[<?php echo esc_html( $index ); ?>][conditional_rules][0][0][operator]" class="exprdawc_input exprdawc_conditional_operator">
														<option value="field_is_empty"><?php esc_html_e( 'Field is empty', 'extra-product-data-for-woocommerce' ); ?></option>
														<option value="field_is_not_empty"><?php esc_html_e( 'Field is not empty', 'extra-product-data-for-woocommerce' ); ?></option>	
														<option value="equals"><?php esc_html_e( 'Equals', 'extra-product-data-for-woocommerce' ); ?></option>
														<option value="not_equals"><?php esc_html_e( 'Not Equals', 'extra-product-data-for-woocommerce' ); ?></option>
														<option value="greater_than"><?php esc_html_e( 'Greater Than', 'extra-product-data-for-woocommerce' ); ?></option>
														<option value="less_than"><?php esc_html_e( 'Less Than', 'extra-product-data-for-woocommerce' ); ?></option>
													</select>
													<input type="text" name="extra_product_fields[<?php echo esc_html( $index ); ?>][conditional_rules][0][0][value]" class="exprdawc_input exprdawc_conditional_value" placeholder="<?php esc_html_e( 'Enter value', 'extra-product-data-for-woocommerce' ); ?>" />
													<button type="button" class="button remove_rule"><i class="dashicons dashicons-trash"></i></button>
													<button type="button" class="button add_rule">+ <?php esc_html_e( 'AND', 'extra-product-data-for-woocommerce' ); ?></button>
												</div>
											</div>
										</div>
									<?php endif; ?>
								</div>
								<button type="button" class="button add_rule_group">+ <?php esc_html_e( 'Add new rule group', 'extra-product-data-for-woocommerce' ); ?></button>
							</td>
						</tr>
					</tbody>
				</table>

				<!-- Text Area Option/Settings -->
				<table class="exprdawc_settings_table exprdawc_long_text_table" style="display:<?php echo 'long_text' === $field['type'] ? 'table' : 'none'; ?>">
					<tbody>
						<tr>
							<td>
								<label class="exprdawc_label" for="exprdawc_long_text_rows_<?php echo esc_html( $index ); ?>"><?php esc_html_e( 'Rows', 'extra-product-data-for-woocommerce' ); ?></label>
								<input type="number" id="exprdawc_long_text_rows_<?php echo esc_html( $index ); ?>" class="exprdawc_input exprdawc_long_text_rows" name="extra_product_fields[<?php echo esc_html( $index ); ?>][rows]" value="<?php echo esc_attr( $field['rows'] ?? '2' ); ?>" />
							</td>
							<td>
								<label class="exprdawc_label" for="exprdawc_long_text_cols_<?php echo esc_html( $index ); ?>"><?php esc_html_e( 'Columns', 'extra-product-data-for-woocommerce' ); ?></label>
								<input type="number" id="exprdawc_long_text_cols_<?php echo esc_html( $index ); ?>" class="exprdawc_input exprdawc_long_text_cols" name="extra_product_fields[<?php echo esc_html( $index ); ?>][cols]" value="<?php echo esc_attr( $field['cols'] ?? '5' ); ?>" />
							</td>
							<td>
								<label class="exprdawc_label" for="exprdawc_long_text_default_<?php echo esc_html( $index ); ?>"><?php esc_html_e( 'Default Value', 'extra-product-data-for-woocommerce' ); ?></label>
								<textarea id="exprdawc_long_text_default_<?php echo esc_html( $index ); ?>" class="exprdawc_textarea" rows="3" cols="30" placeholder="<?php esc_html_e( 'Enter a default text', 'extra-product-data-for-woocommerce' ); ?>" class="exprdawc_input exprdawc_long_text_cols" name="extra_product_fields[<?php echo esc_html( $index ); ?>][default]" value="<?php echo esc_attr( $field['default'] ?? '' ); ?>" ></textarea>
							</td>
						</tr>
					</tbody>
				</table>

				<!-- Text Option/Settings -->
				<table class="exprdawc_settings_table exprdawc_text_table" style="display:<?php echo 'text' === $field['type'] ? 'table' : 'none'; ?>">
					<tbody>
						<tr>
							<td>
								<label class="exprdawc_label" for="exprdawc_text_min_length_<?php echo esc_html( $index ); ?>"><?php esc_html_e( 'Min length', 'extra-product-data-for-woocommerce' ); ?></label>
								<input type="number" id="exprdawc_text_min_length_<?php echo esc_html( $index ); ?>" class="exprdawc_input exprdawc_text_min_length" name="extra_product_fields[<?php echo esc_html( $index ); ?>][minlength]" value="<?php echo esc_attr( $field['minlength'] ?? '0' ); ?>" />
							</td>
							<td>
								<label class="exprdawc_label" for="exprdawc_text_max_length_<?php echo esc_html( $index ); ?>"><?php esc_html_e( 'Max length', 'extra-product-data-for-woocommerce' ); ?></label>
								<input type="number" id="exprdawc_text_max_length_<?php echo esc_html( $index ); ?>" class="exprdawc_input exprdawc_text_max_length" name="extra_product_fields[<?php echo esc_html( $index ); ?>][maxlength]" value="<?php echo esc_attr( $field['maxlength'] ?? '255' ); ?>" />
							</td>
							<td>
								<label class="exprdawc_label" for="exprdawc_text_default_<?php echo esc_html( $index ); ?>"><?php esc_html_e( 'Default Value', 'extra-product-data-for-woocommerce' ); ?></label>
								<input type="text" id="exprdawc_text_default_<?php echo esc_html( $index ); ?>" class="exprdawc_input exprdawc_text_max_length" placeholder="<?php esc_html_e( 'Enter a default text', 'extra-product-data-for-woocommerce' ); ?>" name="extra_product_fields[<?php echo esc_html( $index ); ?>][default]" value="<?php echo esc_attr( $field['default'] ?? '' ); ?>" />
							</td>
						</tr>
					</tbody>
				</table>

				<!-- Checbox, Radio, Select Area Option/Settings -->
				<table class="exprdawc_options_table" style="display:<?php echo empty( $field['options'] ) ? 'none' : 'table'; ?>">
					<thead>
						<tr>
							<th></th>
							<th class="field_option_table_label_th">
								<?php esc_html_e( 'Option Label', 'extra-product-data-for-woocommerce' ); ?>
								<span class="dashicons dashicons-editor-help" title="<?php esc_html_e( 'This is the label for the option.', 'extra-product-data-for-woocommerce' ); ?>"></span>
							</th>
							<th class="field_option_table_value_th">
								<?php esc_html_e( 'Option Value', 'extra-product-data-for-woocommerce' ); ?>
								<span class="dashicons dashicons-editor-help" title="<?php esc_html_e( 'This is the value for the option.', 'extra-product-data-for-woocommerce' ); ?>"></span>
							</th>
							<th class="field_option_table_selected_th">
								<?php esc_html_e( 'Default', 'extra-product-data-for-woocommerce' ); ?>
								<span class="dashicons dashicons-editor-help" title="<?php esc_html_e( 'Set as default option.', 'extra-product-data-for-woocommerce' ); ?>"></span>
							</th>
							<th class="field_price_adjustment_type_th" style="<?php echo ( $field['adjust_price'] ?? false ) ? '' : 'display:none'; ?>">
								<?php esc_html_e( 'Price Adjustment Type', 'extra-product-data-for-woocommerce' ); ?>
								<span class="dashicons dashicons-editor-help" title="<?php esc_html_e( 'This is the price adjustment type for the option.', 'extra-product-data-for-woocommerce' ); ?>"></span>
							</th>
							<th class="field_price_adjustment_val_th" style="<?php echo ( $field['adjust_price'] ?? false ) ? '' : 'display:none'; ?>">
								<?php esc_html_e( 'Price Adjustment Value', 'extra-product-data-for-woocommerce' ); ?>
								<span class="dashicons dashicons-editor-help" title="<?php esc_html_e( 'This is the price adjustment value for the option.', 'extra-product-data-for-woocommerce' ); ?>"></span>
							</th>
							<th class="field_option_table_action_th"><?php esc_html_e( 'Action', 'extra-product-data-for-woocommerce' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php if ( ! empty( $field['options'] ) ) : ?>
							<?php foreach ( $field['options'] as $option_index => $option ) : ?>
								<?php if ( is_array( $option ) ) : ?>
									<tr>
										<td class="move"><i class="dashicons dashicons-move"></i></td>
										<td class="field_option_table_label_td">
											<input type="text" class="exprdawc_input exprdawc_option_label" name="extra_product_fields[<?php echo esc_html( $index ); ?>][options][<?php echo esc_html( $option_index ); ?>][label]" value="<?php echo esc_attr( $option['label'] ); ?>" placeholder="<?php esc_html_e( 'Enter option label', 'extra-product-data-for-woocommerce' ); ?>" />
										</td>
										<td class="field_option_table_value_td">
											<input type="text" class="exprdawc_input exprdawc_option_value" name="extra_product_fields[<?php echo esc_html( $index ); ?>][options][<?php echo esc_html( $option_index ); ?>][value]" value="<?php echo esc_attr( $option['value'] ); ?>" placeholder="<?php esc_html_e( 'Enter option value', 'extra-product-data-for-woocommerce' ); ?>" />
										</td>
										<td class="field_option_table_selected_td">
											<?php if ( 'checkbox' === $field['type'] ) : ?>
												<input type="checkbox" class="exprdawc_input exprdawc_checkbox exprdawc_option_default" name="extra_product_fields[<?php echo esc_html( $index ); ?>][options][<?php echo esc_html( $option_index ); ?>][default]" value="1" <?php checked( 1, $option['default'] ); ?> />
											<?php else : ?>
												<input type="radio" class="exprdawc_input exprdawc_radio exprdawc_option_default" name="extra_product_fields[<?php echo esc_html( $index ); ?>][default]" value="<?php echo esc_attr( $option['value'] ); ?>" <?php checked( isset( $field['default'] ) && $field['default'] === $option['value'] ); ?> />
											<?php endif; ?>
										</td>
										<td class="field_price_adjustment_type" style="<?php echo ( $field['adjust_price'] ?? false ) ? '' : 'display:none'; ?>">
											<select name="extra_product_fields[<?php echo esc_html( $index ); ?>][options][<?php echo esc_html( $option_index ); ?>][price_adjustment_type]" class="exprdawc_input exprdawc_price_adjustment_type">
												<option value="fixed" <?php selected( $option['price_adjustment_type'], 'fixed' ); ?>><?php esc_html_e( 'Fixed Price', 'extra-product-data-for-woocommerce' ); ?></option>
												<option value="quantity" <?php selected( $option['price_adjustment_type'], 'quantity' ); ?>><?php esc_html_e( 'Price per Quantity', 'extra-product-data-for-woocommerce' ); ?></option>
												<option value="percentage" <?php selected( $option['price_adjustment_type'], 'percentage' ); ?>><?php esc_html_e( 'Percentage Price', 'extra-product-data-for-woocommerce' ); ?></option>
											</select>
										</td>
										<td class="field_price_adjustment_value" style="<?php echo ( $field['adjust_price'] ?? false ) ? '' : 'display:none'; ?>">
											<input type="number" class="exprdawc_input exprdawc_price_adjustment_value" name="extra_product_fields[<?php echo esc_html( $index ); ?>][options][<?php echo esc_html( $option_index ); ?>][price_adjustment_value]" placeholder="0.00" value="<?php echo esc_attr( $option['price_adjustment_value'] ?? '' ); ?>" step="0.01" />
										</td>
										<td class="field_option_table_action_td">
											<button type="button" class="button remove_option"><i class="dashicons dashicons-trash"></i></button>
										</td>
									</tr>
								<?php endif; ?>
							<?php endforeach; ?>
						<?php endif; ?>
					</tbody>
					<tfoot>
						<tr>
							<td colspan="6">
								<button type="button" class="button add_option"><?php esc_html_e( 'Add Option', 'extra-product-data-for-woocommerce' ); ?></button>
							</td>
						</tr>
					</tfoot>
				</table>
				
			</td>
		</tr>
	</tbody>
</table>