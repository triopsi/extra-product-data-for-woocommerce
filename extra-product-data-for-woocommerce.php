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
 *
 * @package ExtraProductDataForWooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use function Triopsi\Exprdawc\tr_is_woocommerce_active;
use Triopsi\Exprdawc\Exprdawc_Main;

// Include constants.
require_once __DIR__ . '/src/constants.php';

// Include functions/helpers.
require_once EXPRDAWC_SRC . 'functions.php';

// Include Base Order Class.
require_once EXPRDAWC_CLASSES . 'class-exprdawc-base-order-class.php';

// Include main class.
require_once EXPRDAWC_CLASSES . 'class-exprdawc-main.php';

// Include Helper Class.
require_once EXPRDAWC_CLASSES . 'class-exprdawc-helper.php';

// Is WooCommerce active? Then not, display a notice.
if ( ! tr_is_woocommerce_active() ) {
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
