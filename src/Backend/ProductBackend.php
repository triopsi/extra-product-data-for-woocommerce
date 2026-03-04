<?php
/**
 * Product Backend Handler
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

namespace Triopsi\Exprdawc\Backend;

use Triopsi\Exprdawc\Contracts\Hookable;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Load old implementation temporarily.
require_once EXPRDAWC_CLASSES . 'class-exprdawc-product-page-backend.php';

/**
 * Product Backend Handler
 *
 * Handles product backend functionality and custom fields in admin.
 */
class ProductBackend extends \Triopsi\Exprdawc\Exprdawc_Product_Page_Backend implements Hookable {

	/**
	 * Register WordPress hooks.
	 *
	 * @return void
	 */
	public function registerHooks(): void {
		// Hooks are registered in parent constructor.
	}
}
