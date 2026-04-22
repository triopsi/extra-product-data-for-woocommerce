<?php

namespace Yoast\PHPUnitPolyfills;

if ( ! class_exists( Autoload::class, false ) ) {
	final class Autoload {
		public const VERSION = '4.0.0';

		public static function load( string $class_name ): bool {
			if ( strpos( $class_name, __NAMESPACE__ . '\\' ) !== 0 ) {
				return false;
			}

			$relative_class = substr( $class_name, strlen( __NAMESPACE__ ) + 1 );
			$file           = __DIR__ . '/src/' . str_replace( '\\', '/', $relative_class ) . '.php';

			if ( is_file( $file ) ) {
				require_once $file;
				return true;
			}

			return false;
		}
	}

	spl_autoload_register( __NAMESPACE__ . '\\Autoload::load' );
}