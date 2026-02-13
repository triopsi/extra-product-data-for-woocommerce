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
echo '<input type="' . esc_attr( $field_args['type'] ) . '" 
    class="' . esc_attr( implode( ' ', $field_args['input_class'] ) ) . '" 
    name="' . esc_attr( $field_args['name'] ) . '" 
    id="' . esc_attr( $field_args['id'] ) . '" 
    placeholder="' . esc_attr( $field_args['placeholder'] ) . '" 
    value="' . esc_attr( $field_args['value'] ) . '" 
    ' . implode( ' ', $custom_attributes ) . ' />';

if ( ! empty( $field_args['description'] ) ) {
	echo '<span id="' . esc_attr( $field_args['id'] ) . '-description" class="' . esc_attr( implode( ' ', $field_args['description_class'] ) ) . '">' . esc_html( $field_args['description'] ) . '</span>';
}
echo '</span>';
