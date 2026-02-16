<?php
/**
 * PHPUnit tests for Exprdawc_Settings class
 */

use PHPUnit\Framework\TestCase;
use Triopsi\Exprdawc\Exprdawc_Settings;

/**
 * Class Test_Exprdawc_Settings
 *
 * PHPUnit tests for Exprdawc_Settings class.
 *
 * @package Extra_Product_Data_For_WooCommerce\Tests\Unit
 */
class Test_Exprdawc_Settings extends TestCase {
	/**
	 * Tests if add_settings_section adds a new section key.
	 */
	public function test_add_settings_section_adds_section() {
		$exprdawc_settings = new Exprdawc_Settings();
		$sections          = array( 'general' => 'General' );
		$result            = $exprdawc_settings->add_settings_section( $sections );
		$this->assertArrayHasKey( 'extra_product_data', $result );
	}

	/**
	 * Tests if add_settings returns an array.
	 */
	public function test_add_settings_returns_array() {
		$exprdawc_settings = new Exprdawc_Settings();
		$settings_array    = array();
		$result            = $exprdawc_settings->add_settings( $settings_array, 'extra_product_data' );
		$this->assertIsArray( $result );
	}
}
