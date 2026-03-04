<?php
/**
 * Base Order Handler
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

namespace Triopsi\Exprdawc\Orders;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Load old implementation temporarily.
require_once EXPRDAWC_CLASSES . 'order/class-exprdawc-base-order-class.php';

/**
 * Base Order Handler
 *
 * Abstract base class for order-related functionality.
 */
abstract class BaseOrder extends \Triopsi\Exprdawc\Order\Exprdawc_Base_Order_Class {
	// Extension point for future refactoring.
}
