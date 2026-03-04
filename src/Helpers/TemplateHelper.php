<?php
/**
 * Template Helper Class
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

namespace Triopsi\Exprdawc\Helpers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Load old implementation temporarily.
require_once EXPRDAWC_CLASSES . 'helper/class-exprdawc-template-helpers.php';

/**
 * Template Helper Class
 *
 * Static helper functions for templates.
 */
class TemplateHelper extends \Triopsi\Exprdawc\Helper\Exprdawc_Template_Helpers {

	/**
	 * Join array items into a string with a glue.
	 *
	 * @param array  $items The array of items to join.
	 * @param string $glue The glue string to use between items.
	 * @return string The joined string.
	 */
	public static function join( array $items, string $glue = ' ' ): string {
		return parent::join( $items, $glue );
	}

	/**
	 * Convert an associative array of attributes into a string for HTML tags.
	 *
	 * @param array $attributes The associative array of attributes.
	 * @return string The formatted string of attributes.
	 */
	public static function attrs( array $attributes ): string {
		return parent::attrs( $attributes );
	}

	/**
	 * Convert an array of CSS classes into a string for HTML tags.
	 *
	 * @param array $classes The array of CSS classes.
	 * @return string The formatted string of classes.
	 */
	public static function classes( $classes ): string {
		return parent::classes( $classes );
	}
}
