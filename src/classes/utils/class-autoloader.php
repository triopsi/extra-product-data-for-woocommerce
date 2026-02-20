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
namespace Triopsi\Exprdawc\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
/**
 * Class Autoloader
 *
 * This class is responsible for autoloading classes from the classes directory.
 *
 * @package Exprdawc\Utils
 */
class Autoloader {

	/**
	 * Default path for autoloader.
	 *
	 * @var string
	 */
	private static $default_path;

	/**
	 * Default namespace for autoloader.
	 *
	 * @var string
	 */
	private static $default_namespace;

	/**
	 * Will look for Some_Class\Name in {base}/some-class/class-name.php
	 *
	 * @param string $class_name Class Name.
	 */
	private static function autoload( $class_name ) {

		// does the class use the namespace prefix?
		if ( 0 !== strpos( $class_name, self::$default_namespace . '\\' ) ) {
			// no, move to the next registered autoloader.
			return;
		}

		// get the relative class name.
		$relative_class = substr( $class_name, strlen( self::$default_namespace ) + 1 );

		// base directory for the namespace prefix.
		$relative_path   = strtolower( str_replace( array( '\\', '_' ), array( DIRECTORY_SEPARATOR, '-' ), $relative_class ) );
		$relative_dir    = dirname( $relative_path );
		$class_file_name = 'class-' . basename( $relative_path ) . '.php';
		$base_path       = rtrim( self::$default_path, '/\\' ) . DIRECTORY_SEPARATOR;
		$file            = $base_path . ( '.' === $relative_dir ? '' : $relative_dir . DIRECTORY_SEPARATOR ) . $class_file_name;

		// if the file exists, require it.
		if ( ! file_exists( $file ) ) {
			error_log( "Autoloader: File not found for class \"$class_name\" at path \"$file\"." ); // phpcs:ignore
			return;
		}

		require $file;
	}

	/**
	 * Setup the autoloader.
	 *
	 * @param string $default_path Default path for autoloader.
	 * @param string $default_namespace Default namespace for autoloader.
	 */
	public static function setup( $default_path = '', $default_namespace = '' ) {

		if ( '' === $default_path ) {
			define( 'DS', DIRECTORY_SEPARATOR );
			$default_path = dirname( __DIR__ ) . DS . 'classes' . DS;
		}

		if ( '' === $default_namespace ) {
			$default_namespace = __NAMESPACE__;
		}

		self::$default_path      = $default_path;
		self::$default_namespace = $default_namespace;

		// Registry Autoload.
		spl_autoload_register( array( __CLASS__, 'autoload' ) );
	}
}
