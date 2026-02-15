<?php
/**
 * PHPUnit tests for Exprdawc_User_Order class
 */

use PHPUnit\Framework\TestCase;
use Triopsi\Exprdawc\Exprdawc_User_Order;

/**
 * Class Test_Exprdawc_User_Order
 *
 * PHPUnit tests for Exprdawc_User_Order class.
 *
 * @package Extra_Product_Data_For_WooCommerce\Tests\Unit
 */
class Test_Exprdawc_User_Order extends TestCase {
	/**
	 * Tests if the Exprdawc_User_Order class can be instantiated.
	 *
	 * @covers Exprdawc_User_Order::__construct
	 */
	public function test_can_instantiate() {
		$user_order = new Exprdawc_User_Order();
		$this->assertInstanceOf( Exprdawc_User_Order::class, $user_order );
	}
}
