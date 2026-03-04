<?php
/**
 * Admin Order Handler
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

namespace Triopsi\Exprdawc\Orders\Admin;

use Triopsi\Exprdawc\Contracts\Hookable;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Load old implementation temporarily.
require_once EXPRDAWC_CLASSES . 'order/admin/class-exprdawc-admin-order.php';

/**
 * Admin Order Handler
 *
 * Handles order management in WordPress admin.
 */
class AdminOrder extends \Triopsi\Exprdawc\Order\Admin\Exprdawc_Admin_Order implements Hookable {

	/**
	 * Register WordPress hooks.
	 *
	 * @return void
	 */
	public function registerHooks(): void {
		// Hooks are registered in parent constructor.
	}
}
