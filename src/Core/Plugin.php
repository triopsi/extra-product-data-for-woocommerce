<?php
/**
 * Plugin Core Class
 *
 * @package ExtraProductDataForWooCommerce
 * @author Daniel Drevermann <info@triopsi.com>
 * @copyright Copyright (c) 2024, IT-Dienstleistungen Drevermann
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 */

declare(strict_types=1);

namespace Triopsi\Exprdawc\Core;

use Triopsi\Exprdawc\Helpers\Helper;
use Triopsi\Exprdawc\Backend\ProductBackend;
use Triopsi\Exprdawc\Frontend\ProductFrontend;
use Triopsi\Exprdawc\Settings\Settings;
use Triopsi\Exprdawc\Orders\Admin\AdminOrder;
use Triopsi\Exprdawc\Orders\Customer\CustomerOrder;
use Triopsi\Exprdawc\Traits\Singleton;
use Triopsi\Exprdawc\Contracts\Hookable;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin Core Class
 *
 * Main orchestrator for the plugin, responsible for initialization
 * and component registration.
 */
class Plugin implements Hookable {

	use Singleton;

	/**
	 * Product Backend instance.
	 *
	 * @var ProductBackend|null
	 */
	protected ?ProductBackend $productBackend = null;

	/**
	 * Product Frontend instance.
	 *
	 * @var ProductFrontend|null
	 */
	protected ?ProductFrontend $productFrontend = null;

	/**
	 * Admin Order instance.
	 *
	 * @var AdminOrder|null
	 */
	protected ?AdminOrder $adminOrder = null;

	/**
	 * Settings instance.
	 *
	 * @var Settings|null
	 */
	protected ?Settings $settings = null;

	/**
	 * Customer Order instance.
	 *
	 * @var CustomerOrder|null
	 */
	protected ?CustomerOrder $customerOrder = null;

	/**
	 * Initialize the plugin.
	 */
	protected function __construct() {
		$this->registerHooks();
	}

	/**
	 * Register WordPress hooks.
	 *
	 * @return void
	 */
	public function registerHooks(): void {
		add_action( 'init', array( $this, 'loadComponents' ), 0 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueueAdminAssets' ) );
		add_filter( 'plugin_action_links_' . EXPRDAWC_BASENAME, array( $this, 'addPluginActionLinks' ) );
	}

	/**
	 * Add plugin action links.
	 *
	 * @param array $links The current links.
	 * @return array The modified links.
	 */
	public function addPluginActionLinks( array $links ): array {
		$settingsUrl  = admin_url( 'admin.php?page=wc-settings&tab=products&section=extra_product_data' );
		$settingsLink = sprintf(
			'<a href="%s">%s</a>',
			esc_url( $settingsUrl ),
			esc_html__( 'Settings', 'extra-product-data-for-woocommerce' )
		);

		array_unshift( $links, $settingsLink );

		return $links;
	}

	/**
	 * Load plugin components.
	 *
	 * @return void
	 */
	public function loadComponents(): void {
		if ( ! Helper::isWooCommerceActive() ) {
			return;
		}

		$this->productBackend  = new ProductBackend();
		$this->productFrontend = new ProductFrontend();
		$this->adminOrder      = new AdminOrder();
		$this->customerOrder   = new CustomerOrder();
		$this->settings        = new Settings();
	}

	/**
	 * Enqueue admin assets.
	 *
	 * @return void
	 */
	public function enqueueAdminAssets(): void {
		if ( ! is_admin() ) {
			return;
		}

		wp_enqueue_style(
			'exprdawc-backend-css',
			EXPRDAWC_ASSETS_CSS . 'admin-backend.css',
			array(),
			EXPRDAWC_VERSION
		);

		wp_enqueue_style(
			'exprdawc-forms-css',
			EXPRDAWC_ASSETS_CSS . 'forms.css',
			array(),
			EXPRDAWC_VERSION
		);
	}

	/**
	 * Get Product Backend instance.
	 *
	 * @return ProductBackend|null
	 */
	public function getProductBackend(): ?ProductBackend {
		return $this->productBackend;
	}

	/**
	 * Get Product Frontend instance.
	 *
	 * @return ProductFrontend|null
	 */
	public function getProductFrontend(): ?ProductFrontend {
		return $this->productFrontend;
	}

	/**
	 * Get Admin Order instance.
	 *
	 * @return AdminOrder|null
	 */
	public function getAdminOrder(): ?AdminOrder {
		return $this->adminOrder;
	}

	/**
	 * Get Settings instance.
	 *
	 * @return Settings|null
	 */
	public function getSettings(): ?Settings {
		return $this->settings;
	}

	/**
	 * Get Customer Order instance.
	 *
	 * @return CustomerOrder|null
	 */
	public function getCustomerOrder(): ?CustomerOrder {
		return $this->customerOrder;
	}
}
