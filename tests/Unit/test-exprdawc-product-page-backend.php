<?php
/**
 * PHPUnit tests for Exprdawc_Product_Page_Backend class
 */

use PHPUnit\Framework\TestCase;
use Triopsi\Exprdawc\Exprdawc_Product_Page_Backend;

/**
 * Class Test_Exprdawc_Product_Page_Backend
 *
 * PHPUnit tests for Exprdawc_Product_Page_Backend class.
 *
 * @package Extra_Product_Data_For_WooCommerce\Tests\Unit
 */
class Test_Exprdawc_Product_Page_Backend extends TestCase {
	/**
	 * Tests if the Exprdawc_Product_Page_Backend class can be instantiated.
	 *
	 * @covers Exprdawc_Product_Page_Backend::__construct
	 */
	public function test_can_instantiate() {
		$product_page_backend = new Exprdawc_Product_Page_Backend();
		$this->assertInstanceOf( Exprdawc_Product_Page_Backend::class, $product_page_backend );
	}

	/**
	 * Tests if exprdawc_add_custom_product_tab adds a tab with the key 'custom_fields'.
	 *
	 * @covers Exprdawc_Product_Page_Backend::exprdawc_add_custom_product_tab
	 */
	public function test_exprdawc_add_custom_product_tab_adds_tab() {
		$product_page_backend = new Exprdawc_Product_Page_Backend();
		$tabs                 = array();
		$result               = $product_page_backend->exprdawc_add_custom_product_tab( $tabs );
		$this->assertArrayHasKey( 'custom_fields', $result );
	}
}
