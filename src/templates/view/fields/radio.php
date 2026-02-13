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
echo '<label class="' . esc_attr( implode( ' ', $field_args['label_class'] ) ) . '">' . esc_html( $field_args['label'] ) . $required_string . '</label>';
echo '<span class="' . esc_attr( implode( ' ', $field_args['input_wrapper_class'] ) ) . '">';
if ( isset( $field_args['options'] ) && is_array( $field_args['options'] ) ) {
	foreach ( $field_args['options'] as $option ) {
		$option_value = $option['value'];
		$option_label = $option['label'];
		$checked      = in_array( $option_value, (array) $field_args['value'] ) ? 'checked' : '';
		$id           = $field_args['id'] . '-' . str_replace( array( ' ', '_' ), '-', $option_value );

		// if option have adjustable price than add price to the label.
		$price_adjustment = array();
		if ( isset( $option['price_adjustment_value'] ) && $field_args['adjust_price'] ) {
			$plus_minus    = $option['price_adjustment_value'] != 0 ? ( $option['price_adjustment_value'] > 0 ? '+' : '-' ) : '';
			$option_label .= ' (' . $plus_minus . wc_price( $option['price_adjustment_value'] ) . ')';
			// Add custom data attribute to the input field and type.
			$price_adjustment[] = 'data-price-adjustment="' . esc_attr( $option['price_adjustment_value'] ) . '"';
			$price_adjustment[] = 'data-price-adjustment-type="' . esc_attr( $option['price_adjustment_type'] ) . '"';
			// add data label to the input field.
			$price_adjustment[] = 'data-label="' . esc_attr( $option_label ) . '"';
		}

		echo '<div class="exprdawc-checkbox-option">';
		echo '<input type="radio" 
            id="' . esc_attr( $id ) . '"
            name="' . esc_attr( $field_args['name'] ) . '[]"
            value="' . esc_attr( $option_value ) . '"
            ' . $checked . ' 
            class="' . esc_attr( implode( ' ', $field_args['input_class'] ) ) . '"
            ' . implode( ' ', $custom_attributes ) . '
            ' . implode( ' ', $price_adjustment ) . '/>';

		echo '<label for="' . esc_attr( $id ) . '" class="exprdawc-label-checkbox">' . $option_label . '</label>';
		echo '</div>';
	}
}
echo '</span>';
if ( ! empty( $field_args['description'] ) ) {
	echo '<span id="' . esc_attr( $field_args['id'] ) . '-description" class="' . esc_attr( implode( ' ', $field_args['description_class'] ) ) . '">' . esc_html( $field_args['description'] ) . '</span>';
}
