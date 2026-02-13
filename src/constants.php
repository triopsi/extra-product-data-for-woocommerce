<?php
/**
 * Created on Wed Dec 25 2024
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

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Useful global constants.
$version = '1.8.0';
define( 'EXPRDAWC_VERSION', $version );
define( 'EXPRDAWC_PLUGIN_MAIN_FILE', 'extra-product-data-for-woocommerce.php' );

// Paths.
define( 'EXPRDAWC_DS', DIRECTORY_SEPARATOR );
define( 'EXPRDAWC_PATH', rtrim( realpath( __DIR__ . '/../' ), EXPRDAWC_DS ) . EXPRDAWC_DS );
define( 'EXPRDAWC_BASENAME', plugin_basename( EXPRDAWC_PATH . EXPRDAWC_PLUGIN_MAIN_FILE ) );
define( 'EXPRDAWC_SRC', EXPRDAWC_PATH . 'src/' );
define( 'EXPRDAWC_CLASSES', EXPRDAWC_PATH . 'src/classes/' );

// Assets.
define( 'EXPRDAWC_URL', plugin_dir_url( EXPRDAWC_PATH . EXPRDAWC_PLUGIN_MAIN_FILE ) );
define( 'EXPRDAWC_ASSETS_CSS', EXPRDAWC_URL . 'assets/css/' );
define( 'EXPRDAWC_ASSETS_JS', EXPRDAWC_URL . 'assets/js/' );
define( 'EXPRDAWC_TEMPLATES', EXPRDAWC_SRC . 'templates/view/' );

// Templates.
define( 'EXPRDAWC_ADMIN_TEMPLATES_PATH', EXPRDAWC_TEMPLATES . 'admin/' );
define( 'EXPRDAWC_FIELDS_TEMPLATES_PATH', EXPRDAWC_TEMPLATES . 'fields/' );
