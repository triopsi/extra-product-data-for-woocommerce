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

namespace Triopsi\Exprdawc;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Exprdawc_Settings
 *
 * This class contains the settings for the plugin.
 *
 * @package Exprdawc
 */
class Exprdawc_Settings {

	/**
	 * Exprdawc_Settings constructor.
	 */
	public function __construct() {
		add_filter( 'woocommerce_get_sections_products', array( $this, 'add_settings_section' ) );
		add_filter( 'woocommerce_get_settings_products', array( $this, 'add_settings' ), 10, 2 );
	}

	/**
	 * Add the settings section.
	 *
	 * @param array $sections The sections.
	 *
	 * @return array
	 */
	public function add_settings_section( $sections ) {
		$sections['extra_product_data'] = __( 'Extra Product Data', 'extra-product-data-for-woocommerce' );
		return $sections;
	}

	/**
	 * Add the settings.
	 *
	 * @param array  $settings The settings.
	 * @param string $current_section The current section.
	 *
	 * @return array
	 */
	public function add_settings( $settings, $current_section ) {
		if ( 'extra_product_data' === $current_section ) {
			$order_statuses = wc_get_order_statuses();
			$settings       = array(
				array(
					'title' => __( 'Extra Product Data Settings', 'extra-product-data-for-woocommerce' ),
					'type'  => 'title',
					'id'    => 'exprdawc_settings',
				),
				array(
					'title'    => __( 'Show custom data in cart', 'extra-product-data-for-woocommerce' ),
					'desc'     => __( 'Enable this option to show custom data in the cart.', 'extra-product-data-for-woocommerce' ),
					'desc_tip' => __( 'This will show the custom data in the cart.', 'extra-product-data-for-woocommerce' ),
					'id'       => 'exprdawc_show_in_cart',
					'default'  => 'yes',
					'type'     => 'checkbox',
				),
				array(
					'title'    => __( 'Show custom data in checkout', 'extra-product-data-for-woocommerce' ),
					'desc'     => __( 'Enable this option to show custom data in the checkout.', 'extra-product-data-for-woocommerce' ),
					'desc_tip' => __( 'This will show the custom data in the checkout.', 'extra-product-data-for-woocommerce' ),
					'id'       => 'exprdawc_show_in_checkout',
					'default'  => 'yes',
					'type'     => 'checkbox',
				),
				array(
					'title'    => __( 'Show empty fields', 'extra-product-data-for-woocommerce' ),
					'desc'     => __( 'Enable this option to show empty custom data fields in the cart, checkout, and order.', 'extra-product-data-for-woocommerce' ),
					'desc_tip' => __( 'This will show the empty custom data fields in the cart, checkout, and order.', 'extra-product-data-for-woocommerce' ),
					'id'       => 'exprdawc_show_empty_fields',
					'default'  => 'yes',
					'type'     => 'checkbox',
				),
				array(
					'title'   => __( 'Max Order Status for Editing', 'extra-product-data-for-woocommerce' ),
					'desc'    => __( 'Select the maximum order status up to which user inputs can be edited.', 'extra-product-data-for-woocommerce' ),
					'id'      => 'extra_product_data_max_order_status',
					'default' => 'wc-processing',
					'type'    => 'select',
					'options' => $order_statuses,
				),
				array(
					'title'   => __( 'Custom Add to CartText', 'extra-product-data-for-woocommerce' ),
					'desc'    => __( 'Enter custom text for the "Add to cart" button if the product has extra product data fields. If not specified, the WooCommerce default will be used.', 'extra-product-data-for-woocommerce' ),
					'id'      => 'exprdawc_custom_add_to_cart_text',
					'default' => '',
					'type'    => 'text',
				),
				array(
					'type' => 'sectionend',
					'id'   => 'exprdawc_settings',
				),
			);
		}
		return $settings;
	}
}
