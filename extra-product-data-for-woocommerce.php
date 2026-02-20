<?php
/**
 * Plugin Name: Extra Product Data for WooCommerce
 * Description: Adds customizable input fields per product in WooCommerce, allowing users to enter extra details on the frontend .
 * Version: 1.8.2
 * Author: Triopsi
 * Author URI: https:// triopsi.dev
 * License: GPLv2 or later
 * License URI: https:// www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: extra-product-data-for-woocommerce
 * WC requires at least: 3.9
 * WC tested up to: 9.4
 * Requires Plugins: woocommerce
 * Requires PHP: 8.0
 * Requires at least: 6.0
 *
 * @package ExtraProductDataForWooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Triopsi\Exprdawc\Exprdawc_Main;
use Triopsi\Exprdawc\Helper\Exprdawc_Helper;

// Load Composer autoloader when available.
$autoload_path = __DIR__ . '/vendor/autoload.php';
if ( file_exists( $autoload_path ) ) {
	require_once $autoload_path;
}

// Include constants.
require_once __DIR__ . '/src/constants.php';

// Is WooCommerce active? Then not, display a notice.
if ( ! Exprdawc_Helper::is_woocommerce_active() ) {
	add_action( 'admin_notices', 'exprdawc_admin_notice' );
} else {
	// Initiate the main class.
	Exprdawc_Main::get_instance();
}

// Declare compatibility with WooCommerce HPOS.
add_action(
	'before_woocommerce_init',
	function () {
		if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
		}
	}
);

/**
 * Display an admin notice if WooCommerce is not active.
 */
function exprdawc_admin_notice() {
	?>
	<div class="notice notice-error is-dismissible">
		<p><?php esc_html_e( 'The "Extra Product Data for WooCommerce" Plugin requires WooCommerce to be installed and activated.', 'extra-product-data-for-woocommerce' ); ?></p>
	</div>
	<?php
}

/**
 * Deactivate the plugin if the PHP version is below 8.0.
 */
register_activation_hook(
	__FILE__,
	function () {
		if ( version_compare( PHP_VERSION, '8.0.0', '<' ) ) {
			deactivate_plugins( plugin_basename( __FILE__ ) );
			wp_die(
				esc_html__( 'This plugin requires PHP 8.0 or higher.', 'extra-product-data-for-woocommerce' ),
				esc_html__( 'Plugin Activation Error', 'extra-product-data-for-woocommerce' ),
				array( 'back_link' => true )
			);
		}
	}
);
