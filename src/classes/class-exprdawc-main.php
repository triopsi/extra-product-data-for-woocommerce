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

declare( strict_types=1 );
namespace Triopsi\Exprdawc;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
use Triopsi\Exprdawc\Helper\Exprdawc_Helper;
use Triopsi\Exprdawc\Order\Admin\Exprdawc_Admin_Order;
use Triopsi\Exprdawc\Order\Customer\Exprdawc_User_Order;

/**
 * Class Exprdawc_Main
 *
 * This class is the main class of the plugin. It is responsible for the initialization
 * of the plugin and the registration of the sub-objects.
 *
 * @package Exprdawc
 */
class Exprdawc_Main {

	/**
	 * General.
	 *
	 * @var General
	 */
	protected static $single_instance = null;

	/**
	 * Product Backend Object holder.
	 *
	 * @var \Product_Page_Backend
	 */
	protected $exprdawc_product_backend = null;

	/**
	 * Product Fronted Object Holder.
	 *
	 * @var \Product_Page_Fronted
	 */
	protected $exprdawc_product_fronted = null;

	/**
	 * Overview Order Obejct Holder.
	 *
	 * @var \Overview_Order
	 */
	protected $exprdawc_admin_order_edit = null;

	/**
	 * Settings Object Holder.
	 *
	 * @var \Settings
	 */
	protected $exprdawc_settings = null;

	/**
	 * Creates or returns an instance of this class.
	 *
	 * @return Exprdawc_Main A single instance of this class.
	 */
	public static function get_instance() {
		if ( null === self::$single_instance ) {
			self::$single_instance = new self();
		}
		return self::$single_instance;
	}

	/**
	 * Register the autoloader.
	 */
	protected function register_autoloader() {
		require_once EXPRDAWC_CLASSES . 'utils/class-autoloader.php';
		Utils\Autoloader::setup( EXPRDAWC_CLASSES, __NAMESPACE__ );
	}

	/**
	 * Initiate our sub-objects.
	 */
	protected function __construct() {

		$this->register_autoloader();

		// Init Hooks.
		add_action( 'init', array( $this, 'load_components' ), 0 );

		// Add CSS an JS for backend.
		add_action( 'admin_enqueue_scripts', array( $this, 'exprdawc_only_admin_enqueue_scripts' ) );

		// Add Plugin Setting Links.
		add_filter( 'plugin_action_links_' . EXPRDAWC_BASENAME, array( $this, 'exprdawc_plugin_action_links' ) );
	}

	/**
	 * Add Plugin Action Links.
	 *
	 * @param array $links The current links.
	 * @return array The modified links.
	 */
	public function exprdawc_plugin_action_links( $links ) {
		$links[] = '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=products&section=extra_product_data' ) . '">' . __( 'Settings', 'extra-product-data-for-woocommerce' ) . '</a>';
		return $links;
	}

	/**
	 * Run the init method on our sub-objects (on the init hook).
	 *
	 * @return void
	 */
	public function load_components() {

		if ( Exprdawc_Helper::is_woocommerce_active() ) {

			// Product Create/Edit Page.
			$this->exprdawc_product_backend = new Exprdawc_Product_Page_Backend();

			// Product Product Fronted.
			$this->exprdawc_product_fronted = new Exprdawc_Product_Page_Fronted();

			// Admin Order Edit Page.
			$this->exprdawc_admin_order_edit = new Exprdawc_Admin_Order();

			// Add Settings in the WooCommerce Settings Page.
			$this->exprdawc_settings = new Exprdawc_Settings();

			// User Order.
			new Exprdawc_User_Order();
		}
	}

	/**
	 * Add CSS and JS for the backend.
	 *
	 * @return void
	 */
	public function exprdawc_only_admin_enqueue_scripts() {
		if ( is_admin() ) {
			wp_enqueue_style( 'exprdawc-backend-css', EXPRDAWC_ASSETS_CSS . 'admin-backend.css', array(), EXPRDAWC_VERSION, 'all' );
			wp_enqueue_style( 'form-css', EXPRDAWC_ASSETS_CSS . 'forms.css', array(), EXPRDAWC_VERSION, 'all' );
		}
	}
}
