<?php
/**
 * PHPUnit bootstrap file.
 *
 * @package Extra_Product_Data_For_Woocommerce
 */

define( 'TESTS_PLUGIN_DIR', dirname( __DIR__ ) );
define( 'UNIT_TESTS_DATA_PLUGIN_DIR', TESTS_PLUGIN_DIR . '/tests/Data/' );

// Define WP_CORE_DIR if not already defined.
if ( ! defined( 'WP_CORE_DIR' ) ) {
	$_wp_core_dir = getenv( 'WP_CORE_DIR' );
	if ( ! $_wp_core_dir ) {
		$_wp_core_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress';
	}
	define( 'WP_CORE_DIR', $_wp_core_dir );
}

$_tests_dir = getenv( 'WP_TESTS_DIR' );

if ( ! $_tests_dir ) {
	$_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
}

// Point the WP test bootstrap at the local PHPUnit Polyfills shim for PHPUnit 13.
if ( ! defined( 'WP_TESTS_PHPUNIT_POLYFILLS_PATH' ) ) {
	define( 'WP_TESTS_PHPUNIT_POLYFILLS_PATH', TESTS_PLUGIN_DIR . '/tests/phpunit-polyfills' );
}

if ( ! file_exists( "{$_tests_dir}/includes/functions.php" ) ) {
	echo "Could not find {$_tests_dir}/includes/functions.php, have you run bin/install-wp-tests.sh ?" . PHP_EOL; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	exit( 1 );
}

// Give access to tests_add_filter() function.
require_once "{$_tests_dir}/includes/functions.php";

/**
 * Manually load the plugin being tested.
 */
function _manually_load_plugin() {

	// Load WooCommerce first from the standard WordPress plugins directory.
	require_once WP_CORE_DIR . '/wp-content/plugins/woocommerce/woocommerce.php';

	// Load our plugin.
	require dirname( __DIR__ ) . '/extra-product-data-for-woocommerce.php';
}

tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

/**
 * Install WooCommerce after WordPress loads.
 *
 * @return void
 */
function _install_woocommerce() {
	// WooCommerce muss geladen sein.
	if ( ! class_exists( 'WooCommerce' ) || ! class_exists( 'WC_Install' ) ) {
		return;
	}

	// WC_ABSPATH sauber setzen (wird von WC intern oft erwartet).
	if ( ! defined( 'WC_ABSPATH' ) ) {
		define( 'WC_ABSPATH', trailingslashit( WP_CORE_DIR . '/wp-content/plugins/woocommerce' ) );
	}

	// Installer ausführen (legt DB-Tabellen/Optionen an).
	WC_Install::install();

	// In manchen Setups ist install() nicht “komplett genug” → explizit nachziehen.
	if ( method_exists( 'WC_Install', 'create_tables' ) ) {
		WC_Install::create_tables();
	}
	if ( method_exists( 'WC_Install', 'create_roles' ) ) {
		WC_Install::create_roles();
	}
	if ( method_exists( 'WC_Install', 'create_terms' ) ) {
		WC_Install::create_terms();
	}

	// typische Defaults für Tests (optional).
	update_option( 'woocommerce_enable_guest_checkout', 'yes' );
	update_option( 'woocommerce_enable_signup_and_login_from_checkout', 'yes' );
	update_option( 'woocommerce_status_options', array( 'uninstall_data' => 0 ) );

	// WooCommerce initialisieren (Taxonomies, Post Types, etc.).
	if ( function_exists( 'WC' ) ) {
		WC()->init();
	}
}
tests_add_filter( 'setup_theme', '_install_woocommerce' );

// Make wp_die() throw instead of exiting PHP (prevents PHPUnit from stopping).
tests_add_filter(
	'wp_die_handler',
	function () {
		return function ( $message = '' ) {
			throw new RuntimeException(
				is_string( $message ) ? $message : 'wp_die called' // phpcs:ignore
			);
		};
	}
);

tests_add_filter(
	'wp_die_ajax_handler',
	function () {
		return function ( $message = '' ) {
			throw new RuntimeException(
				is_string( $message ) ? $message : 'wp_die (ajax) called' // phpcs:ignore
			);
		};
	}
);

/**
 * Apply a minimal compatibility patch to the WP test library for PHPUnit 13.
 *
 * @param string $tests_dir Path to wordpress-tests-lib.
 *
 * @return void
 */
function _patch_wp_test_lib_for_phpunit13( string $tests_dir ): void {
	$abstract_testcase_file = $tests_dir . '/includes/abstract-testcase.php';

	if ( ! is_readable( $abstract_testcase_file ) || ! is_writable( $abstract_testcase_file ) ) { // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_is_writable
		return;
	}

	$contents = file_get_contents( $abstract_testcase_file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents

	if ( false === $contents || false === strpos( $contents, 'parseTestMethodAnnotations' ) ) {
		return;
	}

	if ( false !== strpos( $contents, 'parseDocBlockAnnotations( $doc_comment )' ) ) {
		return;
	}

	$old_block = "\t\t} else {\n"
		. "\t\t\t// PHPUnit >= 9.5.0.\n"
		. "\t\t\t\$annotations = \\PHPUnit\\Util\\Test::parseTestMethodAnnotations(\n"
		. "\t\t\t\tstatic::class,\n"
		. "\t\t\t\t\$this->getName( false )\n"
		. "\t\t\t);\n"
		. "\t\t}";

	$new_block = "\t\t} else {\n"
		. "\t\t\t\$method_name = method_exists( \$this, 'name' ) ? \$this->name() : \$this->getName( false );\n\n"
		. "\t\t\tif ( method_exists( '\\\\PHPUnit\\\\Util\\\\Test', 'parseTestMethodAnnotations' ) ) {\n"
		. "\t\t\t\t// PHPUnit 9.5 - 12.x.\n"
		. "\t\t\t\t\$annotations = \\PHPUnit\\Util\\Test::parseTestMethodAnnotations(\n"
		. "\t\t\t\t\tstatic::class,\n"
		. "\t\t\t\t\t\$method_name\n"
		. "\t\t\t\t);\n"
		. "\t\t\t} else {\n"
		. "\t\t\t\t// PHPUnit 13+ removed the annotation parser utility.\n"
		. "\t\t\t\t\$annotations = array(\n"
		. "\t\t\t\t\t'class'  => \$this->parseDocBlockAnnotations( ( new \\ReflectionClass( static::class ) )->getDocComment() ),\n"
		. "\t\t\t\t\t'method' => \$this->parseDocBlockAnnotations( ( new \\ReflectionMethod( \$this, \$method_name ) )->getDocComment() ),\n"
		. "\t\t\t\t);\n"
		. "\t\t\t}\n"
		. "\t\t}";

	$contents = str_replace( $old_block, $new_block, $contents, $replace_count );

	if ( 1 !== $replace_count ) {
		return;
	}

	$insert_before = "\t/**\n\t * Handles a deprecated expectation.\n";
	$helper_method = "\t/**\n"
		. "\t * Parse the subset of docblock annotations used by the deprecation expectation logic.\n"
		. "\t *\n"
		. "\t * @param string|false \$doc_comment Raw docblock comment.\n"
		. "\t *\n"
		. "\t * @return array<string, array<int, string>>\n"
		. "\t */\n"
		. "\tprivate function parseDocBlockAnnotations( \$doc_comment ) {\n"
		. "\t\t\$annotations = array();\n\n"
		. "\t\tif ( ! is_string( \$doc_comment ) || '' === \$doc_comment ) {\n"
		. "\t\t\treturn \$annotations;\n"
		. "\t\t}\n\n"
		. "\t\tif ( preg_match_all( '/@([A-Za-z_\\\\-]+)\\\\s+([^\\\\r\\\\n*]+)/', \$doc_comment, \$matches, PREG_SET_ORDER ) ) {\n"
		. "\t\t\tforeach ( \$matches as \$match ) {\n"
		. "\t\t\t\t\$key   = \$match[1];\n"
		. "\t\t\t\t\$value = trim( \$match[2] );\n\n"
		. "\t\t\t\tif ( ! isset( \$annotations[ \$key ] ) ) {\n"
		. "\t\t\t\t\t\$annotations[ \$key ] = array();\n"
		. "\t\t\t\t}\n\n"
		. "\t\t\t\t\$annotations[ \$key ][] = \$value;\n"
		. "\t\t\t}\n"
		. "\t\t}\n\n"
		. "\t\treturn \$annotations;\n"
		. "\t}\n\n";

	$contents = str_replace( $insert_before, $helper_method . $insert_before, $contents, $insert_count );

	if ( 1 !== $insert_count ) {
		return;
	}

	file_put_contents( $abstract_testcase_file, $contents ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
}

_patch_wp_test_lib_for_phpunit13( $_tests_dir );

// Start up the WP testing environment.
require "{$_tests_dir}/includes/bootstrap.php";
