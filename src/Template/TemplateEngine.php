<?php
/**
 * Template Engine
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

namespace Triopsi\Exprdawc\Template;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Load old implementation temporarily.
require_once EXPRDAWC_CLASSES . 'class-exprdawc-template-engine.php';

/**
 * Template Engine
 *
 * Handles template rendering with variable interpolation and control structures.
 */
class TemplateEngine extends \Triopsi\Exprdawc\Exprdawc_Template_Engine {

	/**
	 * Create instance with modern static factory.
	 *
	 * @param string $templatePath   Path to templates.
	 * @param bool   $cacheEnabled   Enable template caching.
	 * @return self
	 */
	public static function create( string $templatePath, bool $cacheEnabled = true ): self {
		return new self( $templatePath, $cacheEnabled );
	}
}
