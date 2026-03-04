<?php
/**
 * Order Helper Class
 *
 * @package ExtraProductDataForWoo Commerce
 * @author Daniel Drevermann <info@triopsi.com>
 * @copyright Copyright (c) 2024, IT-Dienstleistungen Drevermann
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 */

declare(strict_types=1);

namespace Triopsi\Exprdawc\Helpers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Load old implementation temporarily.
require_once EXPRDAWC_CLASSES . 'helper/class-exprdawc-order-helper.php';

/**
 * Order Helper Class
 *
 * Helper class for order-specific operations and calculations.
 */
class OrderHelper extends \Triopsi\Exprdawc\Helper\Exprdawc_Order_Helper {

	/**
	 * Calculate the price adjustment based on the field configuration and value.
	 *
	 * @param array $field_config The field configuration array.
	 * @param mixed $field_value The value of the field.
	 * @param float $base_price The base price to calculate the adjustment from.
	 * @return float The calculated price adjustment.
	 */
	public static function calculate_price_adjustment( array $field_config, $field_value, float $base_price = 0.0 ): float {
		return parent::calculate_price_adjustment( $field_config, $field_value, $base_price );
	}

	/**
	 * Get the calculated adjustment value based on the configuration and base price.
	 *
	 * @param array $config The configuration array for the adjustment.
	 * @param float $base_price The base price to calculate the adjustment from.
	 * @return float The calculated adjustment value.
	 */
	public static function get_adjustment_value( array $config, float $base_price = 0.0 ): float {
		return parent::get_adjustment_value( $config, $base_price );
	}
}
