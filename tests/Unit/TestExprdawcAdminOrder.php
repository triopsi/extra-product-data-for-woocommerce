<?php
declare( strict_types=1 );

use PHPUnit\Framework\TestCase;
use Triopsi\Exprdawc\Exprdawc_Admin_Order;

/**
 * Class TestExprdawcAdminOrder
 *
 * PHPUnit tests for Exprdawc_Admin_Order class.
 *
 * @package Extra_Product_Data_For_WooCommerce\Tests\Unit
 */
class TestExprdawcAdminOrder extends TestCase {
	/**
	 * Tests if the Exprdawc_Admin_Order class can be instantiated.
	 */
	public function test_can_instantiate() {
		$admin_order = new Exprdawc_Admin_Order();
		$this->assertInstanceOf( Exprdawc_Admin_Order::class, $admin_order );
	}
}
