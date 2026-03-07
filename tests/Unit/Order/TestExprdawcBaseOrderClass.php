<?php
declare( strict_types=1 );

use PHPUnit\Framework\TestCase;
use Triopsi\Exprdawc\Orders\BaseOrder;
use Triopsi\Exprdawc\Helpers\OrderHelper;

/**
 * Class TestExprdawcBaseOrderClass
 *
 * PHPUnit tests for BaseOrder and OrderHelper classes.
 *
 * @package Extra_Product_Data_For_WooCommerce\Tests\Unit
 */
class TestExprdawcBaseOrderClass extends TestCase {
	/**
	 * Tests if OrderHelper::getAdjustmentValue returns the correct value for 'fixed'.
	 */
	public function test_getAdjustmentValue_fixed() {
		$field  = array(
			'price_adjustment_type' => 'fixed',
			'priceAdjustmentValue'  => '5.5',
		);
		$result = OrderHelper::getAdjustmentValue( $field, 100.0 );
		$this->assertEquals( 5.5, $result );
	}
}
