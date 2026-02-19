<?php
/**
 * Created on Tue Nov 26 2024
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
// phpcs:ignoreFile
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
echo '<label for="' . esc_attr( $field_args['id'] ) . '" class="' . esc_attr( implode( ' ', $field_args['label_class'] ) ) . '">' . esc_html( $field_args['label'] ) . $required_string . '</label>';
echo '<span class="' . esc_attr( implode( ' ', $field_args['input_wrapper_class'] ) ) . '">';
if ( isset( $field_args['options'] ) && is_array( $field_args['options'] ) ) {
	echo '<select class="select ' . esc_attr( implode( ' ', $field_args['input_class'] ) ) . '"
    id="' . esc_attr( $field_args['id'] ) . '"
    name="' . esc_attr( $field_args['name'] ) . '"
    ' . implode( ' ', $custom_attributes ) . '>';

	// If placeholder is set, add it as an option.
	if ( ! empty( $field_args['placeholder'] ) ) {
		echo '<option value="" disabled selected>' . esc_html( $field_args['placeholder'] ) . '</option>';
	}
	foreach ( $field_args['options'] as $option ) {
		$option_value = $option['value'];
		$option_label = $option['label']; // Label already formatted with price adjustment in helper.
		$selected     = selected( $field_args['value'], $option_value, false );

		// Build data attributes for price adjustment if present.
		$data_attrs = '';
		if ( isset( $option['price_adjustment_value'] ) && ! empty( $option['price_adjustment_value'] ) ) {
			$data_attrs = ' data-price-adjustment="' . esc_attr( $option['price_adjustment_value'] ) . '"';
			$data_attrs .= ' data-price-adjustment-type="' . esc_attr( $option['price_adjustment_type'] ?? 'fixed' ) . '"';
			$data_attrs .= ' data-label="' . esc_attr( $option_label ) . '"';
		}

		echo '<option value="' . esc_attr( $option_value ) . '" ' . $selected . $data_attrs . '>' . esc_html( $option_label ) . '</option>';
	}
	echo '</select>';
}
echo '</span>';
if ( ! empty( $field_args['description'] ) ) {
	echo '<span id="' . esc_attr( $field_args['id'] ) . '-description" class="' . esc_attr( implode( ' ', $field_args['description_class'] ) ) . '">' . esc_html( $field_args['description'] ) . '</span>';
}
