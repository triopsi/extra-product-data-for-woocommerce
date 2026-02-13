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
?>
<input type="hidden" id="exprdawc_export_string" name="exprdawc_export_string" value="<?php echo $custom_fields ? wc_esc_json( json_encode( $custom_fields ) ) : ''; ?>" />
<div id="extra-product-data" class="panel woocommerce_options_panel exprdawc_panel_wrapper">
	<div class="toolbar toolbar-top">
		
	</div>
	<div class="exprdawc_panel">
		<h2><?php esc_html_e( 'Extra Attributes', 'extra-product-data-for-woocommerce' ); ?></h2>
		<p class="description">
			<?php esc_html_e( 'The fields you create here will be displayed in the product overview. You can use them to provide additional information about your products, such as material, dimensions, or any other relevant details.', 'extra-product-data-for-woocommerce' ); ?>
		</p>
		<div class="exprdawc_attributes">
			<div class="exprdawc_no_entry_message">
				<p>
					<?php esc_html_e( 'Add descriptive input fields to allow the customer to visualize your product in the product overview.', 'extra-product-data-for-woocommerce' ); ?>
				</p>
			</div>
			<table class="field-options wp-list-table widefat exprdawc_field_table">
				<tbody id="exprdawc_field_body">
				<?php
				if ( ! empty( $custom_fields ) ) {
					$all_fields = array();
					// Loop through all fields and include the template.
					foreach ( $custom_fields as $index => $field ) {
						$all_fields[ $field['label'] ] = $field['label'];
					}
					foreach ( $custom_fields as $index => $field ) {
						$field['price_adjustment_type'] = isset( $field['price_adjustment_type'] ) ? $field['price_adjustment_type'] : '';
						echo '<tr class="exprdawc_fields_wrapper">';
						echo '<td colspan="5">';
						include EXPRDAWC_TEMPLATES . 'html-tab-extra-attributes-fields.php';
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
