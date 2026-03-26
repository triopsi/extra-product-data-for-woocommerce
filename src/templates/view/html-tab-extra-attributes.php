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

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$all_fields = array();
if ( ! empty( $args['custom_fields'] ) && is_array( $args['custom_fields'] ) ) {
	foreach ( $args['custom_fields'] as $existing_field ) {
		if ( ! empty( $existing_field['label'] ) ) {
			$all_fields[ $existing_field['label'] ] = $existing_field['label'];
		}
	}
}

$field_template_defaults = array(
	'id'                    => '__ID__',
	'label'                 => '',
	'type'                  => 'text',
	'index'                 => '__INDEX__',
	'required'              => 0,
	'autofocus'             => 0,
	'editable'              => 0,
	'conditional_logic'     => 0,
	'adjust_price'          => 0,
	'placeholder_text'      => '',
	'help_text'             => '',
	'autocomplete'          => 'on',
	'price_adjustment_type' => 'fixed',
	'priceAdjustmentValue'  => '',
	'rows'                  => '2',
	'cols'                  => '5',
	'default'               => '',
	'minlength'             => '0',
	'maxlength'             => '255',
	'options'               => array(),
	'conditional_rules'     => array(),

);
?>
<input type="hidden" id="exprdawc_export_string" name="exprdawc_export_string" value="<?php echo $args['custom_fields'] ? wc_esc_json( json_encode( $args['custom_fields'] ) ) : ''; // phpcs:ignore ?>" />

<?php
// Include the import/export modal template.
require EXPRDAWC_TEMPLATES . 'modal-import-export.php';
?>

<div id="extra-product-data" class="exprdawc_panel_wrapper">
	<div class="toolbar toolbar-top">
		
	</div>
	<div class="exprdawc_panel">
		<div class="exprdawc_panel_description">
			<p>
				<?php esc_html_e( 'Add extra attributes to your products to provide customers with more information and options. These attributes can be used to display additional product details, create variations, or offer customizable options.', 'extra-product-data-for-woocommerce' ); ?>
			</p>
		</div>
		<div class="exprdawc_attributes">
			<div class="exprdawc_no_entry_message">
				<p>
					<?php esc_html_e( 'Add descriptive input fields to allow the customer to visualize your product in the product overview.', 'extra-product-data-for-woocommerce' ); ?>
				</p>
			</div>
			<table class="field-options wp-list-table widefat exprdawc_field_table">
				<tbody id="exprdawc_field_body">
				<?php
				if ( ! empty( $args['custom_fields'] ) ) {
					// Loop through all fields and include the template.
					foreach ( $args['custom_fields'] as $index => $field ) {
						$field['price_adjustment_type'] = isset( $field['price_adjustment_type'] ) ? $field['price_adjustment_type'] : '';
						echo '<tr class="exprdawc_fields_wrapper">';
						echo '<td colspan="5">';
						require EXPRDAWC_TEMPLATES . 'html-tab-extra-attributes-fields.php';
						echo '</td>';
						echo '</tr>';
					}
				}
				?>
				</tbody>
				<tfoot>
					<tr>
						<td colspan="4" class="exprdawc_add_field_col">
							<button type="button" id="exprdawc_add_custom_field" class="button exprdawc_add_custom_field"><?php esc_html_e( '+ Add Field', 'extra-product-data-for-woocommerce' ); ?></button>
						</td>
						<td class="exprdawc_export_col">
							<a href="#" class="exprdawc-import"><?php esc_html_e( 'Import', 'extra-product-data-for-woocommerce' ); ?></a>
							<a href="#" class="exprdawc-export"><?php esc_html_e( 'Export', 'extra-product-data-for-woocommerce' ); ?></a>
						</td>
					</tr>
				</tfoot>
			</table>
		</div> 
	</div>
</div>

<template id="exprdawc-field-template">
	<tr class="exprdawc_fields_wrapper">
		<td colspan="5">
			<?php
			$index = '__INDEX__';
			$field = $field_template_defaults;
			require EXPRDAWC_TEMPLATES . 'html-tab-extra-attributes-fields.php';
			?>
		</td>
	</tr>
</template>

<template id="exprdawc-option-template-single">
	<tr>
		<td class="move"><i class="dashicons dashicons-move"></i></td>
		<td class="field_option_table_label_td">
			<input type="text" class="exprdawc_input exprdawc_option_label" name="extra_product_fields[__FIELD_INDEX__][options][__OPTION_INDEX__][label]" value="" placeholder="<?php esc_attr_e( 'Enter option label', 'extra-product-data-for-woocommerce' ); ?>" />
		</td>
		<td class="field_option_table_value_td">
			<input type="text" class="exprdawc_input exprdawc_option_value" name="extra_product_fields[__FIELD_INDEX__][options][__OPTION_INDEX__][value]" value="" placeholder="<?php esc_attr_e( 'Enter option value', 'extra-product-data-for-woocommerce' ); ?>" />
		</td>
		<td class="field_option_table_selected_td">
			<input type="radio" class="exprdawc_input exprdawc_radio exprdawc_option_default" name="extra_product_fields[__FIELD_INDEX__][default]" value="" />
		</td>
		<td class="fieldPriceAdjustment_type" style="display:none;">
			<select name="extra_product_fields[__FIELD_INDEX__][options][__OPTION_INDEX__][price_adjustment_type]" class="exprdawc_input exprdawcPriceAdjustment_type">
				<option value="fixed"><?php esc_html_e( 'Fixed Price +/-', 'extra-product-data-for-woocommerce' ); ?></option>
				<option value="percentage"><?php esc_html_e( 'Percentage Price +/- (%)', 'extra-product-data-for-woocommerce' ); ?></option>
				<option value="fixed_quantity"><?php esc_html_e( 'Fixed Price per Quantity +/-', 'extra-product-data-for-woocommerce' ); ?></option>
				<option value="percentage_quantity"><?php esc_html_e( 'Percentage Price per Quantity +/- (%)', 'extra-product-data-for-woocommerce' ); ?></option>
			</select>
		</td>
		<td class="field_priceAdjustmentValue" style="display:none;">
			<input type="number" class="exprdawc_input exprdawc_priceAdjustmentValue" name="extra_product_fields[__FIELD_INDEX__][options][__OPTION_INDEX__][priceAdjustmentValue]" placeholder="0.00" value="" step="0.01" />
		</td>
		<td class="field_option_table_action_td">
			<button type="button" class="button remove_option"><?php esc_html_e( 'Remove', 'extra-product-data-for-woocommerce' ); ?></button>
		</td>
	</tr>
</template>

<template id="exprdawc-option-template-multi">
	<tr>
		<td class="move"><i class="dashicons dashicons-move"></i></td>
		<td class="field_option_table_label_td">
			<input type="text" class="exprdawc_input exprdawc_option_label" name="extra_product_fields[__FIELD_INDEX__][options][__OPTION_INDEX__][label]" value="" placeholder="<?php esc_attr_e( 'Enter option label', 'extra-product-data-for-woocommerce' ); ?>" />
		</td>
		<td class="field_option_table_value_td">
			<input type="text" class="exprdawc_input exprdawc_option_value" name="extra_product_fields[__FIELD_INDEX__][options][__OPTION_INDEX__][value]" value="" placeholder="<?php esc_attr_e( 'Enter option value', 'extra-product-data-for-woocommerce' ); ?>" />
		</td>
		<td class="field_option_table_selected_td">
			<input type="checkbox" class="exprdawc_input exprdawc_checkbox exprdawc_option_default" name="extra_product_fields[__FIELD_INDEX__][default][]" value="" />
		</td>
		<td class="fieldPriceAdjustment_type" style="display:none;">
			<select name="extra_product_fields[__FIELD_INDEX__][options][__OPTION_INDEX__][price_adjustment_type]" class="exprdawc_input exprdawcPriceAdjustment_type">
				<option value="fixed"><?php esc_html_e( 'Fixed Price +/-', 'extra-product-data-for-woocommerce' ); ?></option>
				<option value="percentage"><?php esc_html_e( 'Percentage Price +/- (%)', 'extra-product-data-for-woocommerce' ); ?></option>
				<option value="fixed_quantity"><?php esc_html_e( 'Fixed Price per Quantity +/-', 'extra-product-data-for-woocommerce' ); ?></option>
				<option value="percentage_quantity"><?php esc_html_e( 'Percentage Price per Quantity +/- (%)', 'extra-product-data-for-woocommerce' ); ?></option>
			</select>
		</td>
		<td class="field_priceAdjustmentValue" style="display:none;">
			<input type="number" class="exprdawc_input exprdawc_priceAdjustmentValue" name="extra_product_fields[__FIELD_INDEX__][options][__OPTION_INDEX__][priceAdjustmentValue]" placeholder="0.00" value="" step="0.01" />
		</td>
		<td class="field_option_table_action_td">
			<button type="button" class="button remove_option"><?php esc_html_e( 'Remove', 'extra-product-data-for-woocommerce' ); ?></button>
		</td>
	</tr>
</template>

<template id="exprdawc-rule-group-template">
	<div class="exprdawc_rule_group_container">
		<h2><?php esc_html_e( 'Or', 'extra-product-data-for-woocommerce' ); ?></h2>
		<div class="exprdawc_rule_group"></div>
	</div>
</template>

<template id="exprdawc-rule-template">
	<div class="exprdawc_rule">
		<select name="extra_product_fields[__FIELD_INDEX__][conditional_rules][__RULE_GROUP_INDEX__][__RULE_INDEX__][field]" class="exprdawc_input exprdawc_conditional_field">
			<option value=""><?php esc_html_e( 'None', 'extra-product-data-for-woocommerce' ); ?></option>
			__FIELD_OPTIONS__
		</select>
		<select name="extra_product_fields[__FIELD_INDEX__][conditional_rules][__RULE_GROUP_INDEX__][__RULE_INDEX__][operator]" class="exprdawc_input exprdawc_conditional_operator">
			<option value="field_is_empty"><?php esc_html_e( 'Field is empty', 'extra-product-data-for-woocommerce' ); ?></option>
			<option value="field_is_not_empty"><?php esc_html_e( 'Field is not empty', 'extra-product-data-for-woocommerce' ); ?></option>
			<option value="equals"><?php esc_html_e( 'Equals', 'extra-product-data-for-woocommerce' ); ?></option>
			<option value="not_equals"><?php esc_html_e( 'Not Equals', 'extra-product-data-for-woocommerce' ); ?></option>
			<option value="greater_than"><?php esc_html_e( 'Greater Than', 'extra-product-data-for-woocommerce' ); ?></option>
			<option value="less_than"><?php esc_html_e( 'Less Than', 'extra-product-data-for-woocommerce' ); ?></option>
		</select>
		<input type="text" name="extra_product_fields[__FIELD_INDEX__][conditional_rules][__RULE_GROUP_INDEX__][__RULE_INDEX__][value]" class="exprdawc_input exprdawc_conditional_value" value="" placeholder="<?php esc_attr_e( 'Enter value', 'extra-product-data-for-woocommerce' ); ?>" style="display:none;" />
		<button type="button" class="button remove_rule"><i class="dashicons dashicons-trash"></i></button>
		<button type="button" class="button add_rule">+ <?php esc_html_e( 'AND', 'extra-product-data-for-woocommerce' ); ?></button>
	</div>
</template>
