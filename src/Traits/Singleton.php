<?php
/**
 * Singleton Trait
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

namespace Triopsi\Exprdawc\Traits;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Singleton Trait
 *
 * Provides singleton pattern implementation for classes.
 */
trait Singleton {

	/**
	 * The single instance of the class.
	 *
	 * @var static|null
	 */
	protected static ?self $instance = null;

	/**
	 * Prevents direct instantiation.
	 */
	protected function __construct() {}

	/**
	 * Prevents cloning of the instance.
	 *
	 * @return void
	 */
	protected function __clone() {}

	/**
	 * Prevents unserialization of the instance.
	 *
	 * @throws \Exception When attempting to unserialize.
	 * @return void
	 */
	public function __wakeup() {
		throw new \Exception( 'Cannot unserialize singleton' );
	}

	/**
	 * Gets the singleton instance.
	 *
	 * @return static The singleton instance.
	 */
	public static function getInstance(): self {
		if ( null === static::$instance ) {
			static::$instance = new static();
		}

		return static::$instance;
	}
}
