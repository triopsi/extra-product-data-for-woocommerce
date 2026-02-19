<?php
declare( strict_types=1 );

use PHPUnit\Framework\TestCase;
use Triopsi\Exprdawc\Exprdawc_Base_Order_Class;
use Triopsi\Exprdawc\Exprdawc_Order_Helper;

/**
 * Class TestExprdawcBaseOrderClass
 *
 * PHPUnit tests for Exprdawc_Base_Order_Class and Exprdawc_Order_Helper classes.
 *
 * @package Extra_Product_Data_For_WooCommerce\Tests\Unit
 */
class TestExprdawcBaseOrderClass extends TestCase {
	/**
	 * Tests if Exprdawc_Order_Helper::get_adjustment_value returns the correct value for 'fixed'.
	 */
	public function test_get_adjustment_value_fixed() {
		$field   = array(
			'price_adjustment_type'  => 'fixed',
			'price_adjustment_value' => '5.5',
		);
		$result  = Exprdawc_Order_Helper::get_adjustment_value( $field, 100.0 );
		$this->assertEquals( 5.5, $result );
	}
}
