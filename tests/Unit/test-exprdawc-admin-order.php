<?php
/**
 * PHPUnit tests for Exprdawc_Admin_Order class
 */

use PHPUnit\Framework\TestCase;
use Triopsi\Exprdawc\Exprdawc_Admin_Order;

/**
 * Class Test_Exprdawc_Admin_Order
 *
 * PHPUnit tests for Exprdawc_Admin_Order class.
 *
 * @package Extra_Product_Data_For_WooCommerce\Tests\Unit
 */
class Test_Exprdawc_Admin_Order extends TestCase {
	/**
	 * Tests if the Exprdawc_Admin_Order class can be instantiated.
	 *
	 * @covers Exprdawc_Admin_Order::__construct
	 */
	public function test_can_instantiate() {
		$admin_order = new Exprdawc_Admin_Order();
		$this->assertInstanceOf( Exprdawc_Admin_Order::class, $admin_order );
	}
}
