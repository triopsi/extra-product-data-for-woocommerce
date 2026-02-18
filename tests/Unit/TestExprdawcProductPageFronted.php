<?php
declare( strict_types=1 );

use PHPUnit\Framework\TestCase;
use Triopsi\Exprdawc\Exprdawc_Product_Page_Fronted;

/**
 * Class TestExprdawcProductPageFronted
 *
 * PHPUnit tests for Exprdawc_Product_Page_Fronted class.
 *
 * @package Extra_Product_Data_For_WooCommerce\Tests\Unit
 */
class TestExprdawcProductPageFronted extends TestCase {
	/**
	 * Tests if the Exprdawc_Product_Page_Fronted class can be instantiated.
	 */
	public function test_can_instantiate() {
		$product_page_fronted = new Exprdawc_Product_Page_Fronted();
		$this->assertInstanceOf( Exprdawc_Product_Page_Fronted::class, $product_page_fronted );
	}
}
