<?php
/**
 * PHPUnit bootstrap file.
 *
 * @package Extra_Product_Data_For_Woocommerce
 */
// phpcs:ignoreFile

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

// Forward custom PHPUnit Polyfills configuration to PHPUnit bootstrap file.
$_phpunit_polyfills_path = getenv( 'WP_TESTS_PHPUNIT_POLYFILLS_PATH' );
if ( false !== $_phpunit_polyfills_path ) {
	define( 'WP_TESTS_PHPUNIT_POLYFILLS_PATH', $_phpunit_polyfills_path );
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
	// Load WooCommerce first.
	$woocommerce_plugin = WP_CORE_DIR . '/wp-content/plugins/woocommerce/woocommerce.php';

	if ( file_exists( $woocommerce_plugin ) ) {
		require_once $woocommerce_plugin;
		// Create WooCommerce database tables.
		global $wpdb;
		$wpdb->suppress_errors = false;
		$wpdb->show_errors     = true;

		try {
			WC_Install::install();
		} catch ( Exception $e ) {
			echo "Error installing WooCommerce: " . $e->getMessage() . PHP_EOL;
		}
		
	} else {
		echo "Warning: WooCommerce plugin not found at: {$woocommerce_plugin}" . PHP_EOL;
		echo "Tests will run with WooCommerce mocks instead." . PHP_EOL;
	}
	
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
	if ( class_exists( 'WC_Install' ) ) {
		// Define WooCommerce constants.
		if ( ! defined( 'WC_ABSPATH' ) ) {
			define( 'WC_ABSPATH', WP_CORE_DIR . '/wp-content/plugins/woocommerce/' );
		}

		// Update WooCommerce options.
		update_option( 'woocommerce_status_options', array( 'uninstall_data' => 0 ) );
		update_option( 'woocommerce_enable_guest_checkout', 'yes' );
		update_option( 'woocommerce_enable_signup_and_login_from_checkout', 'yes' );
	}
}

tests_add_filter( 'setup_theme', '_install_woocommerce' );

// Start up the WP testing environment.
require "{$_tests_dir}/includes/bootstrap.php";
