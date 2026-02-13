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

namespace Triopsi\Exprdawc;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
/**
 * Class Autoloader
 *
 * This class is responsible for autoloading classes from the classes directory.
 *
 * @package Exprdawc
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
	 * Will look for Some_Class\Name in /includes/classes/some-class/class.name.php
	 *
	 * @param String $class_name Clas Name.
	 */
	private static function autoload( $class_name ) {

		if ( 0 !== strpos( $class_name, self::$default_namespace . '\\' ) ) {
			return;
		}

		// project-specific namespace prefix.
		$prefix = __NAMESPACE__ . '\\';

		// does the class use the namespace prefix?
		$len = strlen( $prefix );
		if ( 0 !== strncmp( $prefix, $class_name, $len ) ) {
			// no, move to the next registered autoloader.
			return;
		}

		// get the relative class name.
		$relative_class = substr( $class_name, $len );

		// base directory for the namespace prefix.
		$path       = strtolower( str_replace( array( '\\', '_' ), array( '/', '-' ), $relative_class ) );
		$class_name = 'class-' . basename( $path );
		$file       = self::$default_path . $class_name . '.php';
		// if the file exists, require it.
		if ( ! file_exists( $file ) ) {
			error_log( "Autoloader: File not found for class \"$class_name\" at path \"$file\"." );
			return;
		}
		if ( file_exists( $file ) ) {
			require $file;
		}
	}


	/**
	 * Default setup routine. Register a function as `__autoload()` implementation.
	 *
	 * @return void
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
