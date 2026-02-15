<?php
/**
 * PHPUnit tests for Exprdawc_Base_Order_Class class
 */

use PHPUnit\Framework\TestCase;
use Triopsi\Exprdawc\Exprdawc_Base_Order_Class;

/**
 * Class Test_Exprdawc_Base_Order_Class
 *
 * PHPUnit tests for Exprdawc_Base_Order_Class class.
 *
 * @package Extra_Product_Data_For_WooCommerce\Tests\Unit
 */
class Test_Exprdawc_Base_Order_Class extends TestCase {
	/**
	 * Tests if get_adjustment_value returns the correct value for 'fixed'.
	 *
	 * @covers Exprdawc_Base_Order_Class::get_adjustment_value
	 */
	public function test_get_adjustment_value_fixed() {
		$base_order = $this->getMockForAbstractClass( Exprdawc_Base_Order_Class::class );
		$field      = array(
			'price_adjustment_type'  => 'fixed',
			'price_adjustment_value' => '5.5',
		);
		$product    = $this->createMock( stdClass::class ); // Dummy, not used for 'fixed'
		$method     = new \ReflectionMethod( $base_order, 'get_adjustment_value' );
		$method->setAccessible( true );
		$result = $method->invoke( $base_order, $field, $product );
		$this->assertEquals( 5.5, $result );
	}
}
