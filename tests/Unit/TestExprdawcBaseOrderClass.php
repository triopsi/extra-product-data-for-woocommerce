<?php
declare( strict_types=1 );

use PHPUnit\Framework\TestCase;
use Triopsi\Exprdawc\Exprdawc_Base_Order_Class;

/**
 * Class TestExprdawcBaseOrderClass
 *
 * PHPUnit tests for Exprdawc_Base_Order_Class class.
 *
 * @package Extra_Product_Data_For_WooCommerce\Tests\Unit
 */
class TestExprdawcBaseOrderClass extends TestCase {
	/**
	 * Tests if get_adjustment_value returns the correct value for 'fixed'.
	 */
	public function test_get_adjustment_value_fixed() {
		$base_order = $this->getMockForAbstractClass( Exprdawc_Base_Order_Class::class );
		$field      = array(
			'price_adjustment_type'  => 'fixed',
			'price_adjustment_value' => '5.5',
		);
		$product    = $this->createMock( stdClass::class ); // Dummy, not used for 'fixed'.
		$method     = new \ReflectionMethod( $base_order, 'get_adjustment_value' );
		$method->setAccessible( true );
		$result = $method->invoke( $base_order, $field, $product );
		$this->assertEquals( 5.5, $result );
	}
}
