<?php
declare( strict_types=1 );

use Triopsi\Exprdawc\Exprdawc_Order_Helper;

/**
 * Class TestExprdawcOrderHelper
 *
 * PHPUnit tests for Exprdawc_Order_Helper class.
 *
 * @package Extra_Product_Data_For_WooCommerce\Tests\Unit
 */
class TestExprdawcOrderHelper extends WP_UnitTestCase {

	/**
	 * Test product ID.
	 *
	 * @var int
	 */
	private $product_id;

	/**
	 * Test order ID.
	 *
	 * @var int
	 */
	private $order_id;

	/**
	 * Test product object.
	 *
	 * @var WC_Product
	 */
	private $product;

	/**
	 * Test order object.
	 *
	 * @var WC_Order
	 */
	private $order;

	/**
	 * Sets up the test environment before each test.
	 *
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();

		// Create test product.
		$this->product_id = wp_insert_post(
			array(
				'post_title'  => 'Test Product',
				'post_type'   => 'product',
				'post_status' => 'publish',
			)
		);

		$this->product = wc_get_product( $this->product_id );
		$this->product->set_regular_price( 100 );
		$this->product->save();

		// Create test order.
		$this->order_id = wc_create_order()->get_id();
		$this->order    = wc_get_order( $this->order_id );
	}

	/**
	 * Cleans up the test environment after each test.
	 *
	 * @return void
	 */
	public function tearDown(): void {
		if ( $this->order instanceof WC_Order ) {
			$this->order->delete( true );
		}

		if ( $this->product_id ) {
			wp_delete_post( $this->product_id, true );
		}

		unset( $this->product, $this->order );
		parent::tearDown();
	}

	/**
	 * Tests calculate_price_adjustment with fixed type.
	 *
	 * Test Goal:
	 * Verifies that fixed price adjustments are calculated correctly.
	 */
	public function test_calculate_price_adjustment_fixed() {
		$field_config = array(
			'type'                   => 'text',
			'adjust_price'           => true,
			'price_adjustment_type'  => 'fixed',
			'price_adjustment_value' => 10.0,
		);

		$result = Exprdawc_Order_Helper::calculate_price_adjustment( $field_config, 'test', 100.0 );
		$this->assertEquals( 10.0, $result );
	}

	/**
	 * Tests calculate_price_adjustment with percentage type.
	 *
	 * Test Goal:
	 * Verifies that percentage-based price adjustments are calculated correctly.
	 */
	public function test_calculate_price_adjustment_percentage() {
		$field_config = array(
			'type'                   => 'text',
			'adjust_price'           => true,
			'price_adjustment_type'  => 'percentage',
			'price_adjustment_value' => 15.0,
		);

		$result = Exprdawc_Order_Helper::calculate_price_adjustment( $field_config, 'test', 100.0 );
		$this->assertEquals( 15.0, $result ); // 15% of 100
	}

	/**
	 * Tests calculate_price_adjustment with percent alias.
	 *
	 * Test Goal:
	 * Verifies that 'percent' type is handled same as 'percentage'.
	 */
	public function test_calculate_price_adjustment_percent_alias() {
		$field_config = array(
			'type'                   => 'text',
			'adjust_price'           => true,
			'price_adjustment_type'  => 'percent',
			'price_adjustment_value' => 20.0,
		);

		$result = Exprdawc_Order_Helper::calculate_price_adjustment( $field_config, 'test', 100.0 );
		$this->assertEquals( 20.0, $result ); // 20% of 100
	}

	/**
	 * Tests calculate_price_adjustment with empty value.
	 *
	 * Test Goal:
	 * Verifies that no adjustment is made when field value is empty.
	 */
	public function test_calculate_price_adjustment_empty_value() {
		$field_config = array(
			'type'                   => 'text',
			'adjust_price'           => true,
			'price_adjustment_type'  => 'fixed',
			'price_adjustment_value' => 10.0,
		);

		$result = Exprdawc_Order_Helper::calculate_price_adjustment( $field_config, '', 100.0 );
		$this->assertEquals( 0.0, $result );
	}

	/**
	 * Tests calculate_price_adjustment with adjust_price disabled.
	 *
	 * Test Goal:
	 * Verifies that no adjustment is made when adjust_price is false.
	 */
	public function test_calculate_price_adjustment_disabled() {
		$field_config = array(
			'type'                   => 'text',
			'adjust_price'           => false,
			'price_adjustment_type'  => 'fixed',
			'price_adjustment_value' => 10.0,
		);

		$result = Exprdawc_Order_Helper::calculate_price_adjustment( $field_config, 'test', 100.0 );
		$this->assertEquals( 0.0, $result );
	}

	/**
	 * Tests calculate_price_adjustment with radio options.
	 *
	 * Test Goal:
	 * Verifies that option-based fields calculate adjustments correctly.
	 */
	public function test_calculate_price_adjustment_radio_option() {
		$field_config = array(
			'adjust_price' => true,
			'type'         => 'radio',
			'options'      => array(
				array(
					'value'                  => 'opt1',
					'price_adjustment_type'  => 'fixed',
					'price_adjustment_value' => 5.0,
				),
				array(
					'value'                  => 'opt2',
					'price_adjustment_type'  => 'fixed',
					'price_adjustment_value' => 8.0,
				),
			),
		);

		$result = Exprdawc_Order_Helper::calculate_price_adjustment( $field_config, 'opt1', 100.0 );
		$this->assertEquals( 5.0, $result );
	}

	/**
	 * Tests calculate_price_adjustment with checkbox options (multiple).
	 *
	 * Test Goal:
	 * Verifies that multiple selected options add up correctly.
	 */
	public function test_calculate_price_adjustment_checkbox_multiple() {
		$field_config = array(
			'adjust_price' => true,
			'type'         => 'checkbox',
			'options'      => array(
				array(
					'value'                  => 'opt1',
					'price_adjustment_type'  => 'fixed',
					'price_adjustment_value' => 5.0,
				),
				array(
					'value'                  => 'opt2',
					'price_adjustment_type'  => 'fixed',
					'price_adjustment_value' => 8.0,
				),
			),
		);

		$result = Exprdawc_Order_Helper::calculate_price_adjustment( $field_config, array( 'opt1', 'opt2' ), 100.0 );
		$this->assertEquals( 13.0, $result ); // 5 + 8
	}

	/**
	 * Tests calculate_price_adjustment with mixed option types.
	 *
	 * Test Goal:
	 * Verifies that mixed fixed and percentage options work together.
	 */
	public function test_calculate_price_adjustment_mixed_options() {
		$field_config = array(
			'adjust_price' => true,
			'type'         => 'checkbox',
			'options'      => array(
				array(
					'value'                  => 'opt1',
					'price_adjustment_type'  => 'fixed',
					'price_adjustment_value' => 10.0,
				),
				array(
					'value'                  => 'opt2',
					'price_adjustment_type'  => 'percentage',
					'price_adjustment_value' => 5.0,
				),
			),
		);

		$result = Exprdawc_Order_Helper::calculate_price_adjustment( $field_config, array( 'opt1', 'opt2' ), 100.0 );
		$this->assertEquals( 15.0, $result ); // 10 fixed + 5% of 100
	}

	/**
	 * Tests get_adjustment_value with fixed type.
	 *
	 * Test Goal:
	 * Verifies fixed adjustment value is returned correctly.
	 */
	public function test_get_adjustment_value_fixed() {
		$config = array(
			'price_adjustment_type'  => 'fixed',
			'price_adjustment_value' => 12.5,
		);

		$result = Exprdawc_Order_Helper::get_adjustment_value( $config, 100.0 );
		$this->assertEquals( 12.5, $result );
	}

	/**
	 * Tests get_adjustment_value with percentage type.
	 *
	 * Test Goal:
	 * Verifies percentage adjustment value is calculated correctly.
	 */
	public function test_get_adjustment_value_percentage() {
		$config = array(
			'price_adjustment_type'  => 'percentage',
			'price_adjustment_value' => 10.0,
		);

		$result = Exprdawc_Order_Helper::get_adjustment_value( $config, 200.0 );
		$this->assertEquals( 20.0, $result ); // 10% of 200
	}

	/**
	 * Tests get_adjustment_value with zero value.
	 *
	 * Test Goal:
	 * Verifies that zero adjustment value returns zero.
	 */
	public function test_get_adjustment_value_zero() {
		$config = array(
			'price_adjustment_type'  => 'fixed',
			'price_adjustment_value' => 0.0,
		);

		$result = Exprdawc_Order_Helper::get_adjustment_value( $config, 100.0 );
		$this->assertEquals( 0.0, $result );
	}

	/**
	 * Tests get_item_field_metadata.
	 *
	 * Test Goal:
	 * Verifies that item metadata is extracted and indexed correctly.
	 */
	public function test_get_item_field_metadata() {
		$item_id = $this->order->add_product( $this->product, 1 );
		$item    = $this->order->get_item( $item_id );

		// Add metadata.
		$metadata = array(
			array(
				'label'     => 'Test Field',
				'value'     => 'Test Value',
				'raw_field' => array(),
			),
		);
		$item->update_meta_data( '_meta_extra_product_data', $metadata );
		$item->save();

		$result = Exprdawc_Order_Helper::get_item_field_metadata( $item );

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'test_field', $result );
		$this->assertEquals( 'Test Value', $result['test_field']['value'] );
	}

	/**
	 * Tests get_item_field_metadata with empty data.
	 *
	 * Test Goal:
	 * Verifies that empty array is returned when no metadata exists.
	 */
	public function test_get_item_field_metadata_empty() {
		$item_id = $this->order->add_product( $this->product, 1 );
		$item    = $this->order->get_item( $item_id );

		$result = Exprdawc_Order_Helper::get_item_field_metadata( $item );

		$this->assertIsArray( $result );
		$this->assertEmpty( $result );
	}

	/**
	 * Tests build_field_metadata_array.
	 *
	 * Test Goal:
	 * Verifies that field metadata array is built correctly.
	 */
	public function test_build_field_metadata_array() {
		$custom_fields = array(
			array(
				'label' => 'Test Field 1',
				'type'  => 'text',
			),
			array(
				'label' => 'Test Field 2',
				'type'  => 'number',
			),
		);

		$field_values = array(
			'test_field_1' => 'Value 1',
			'test_field_2' => '42',
		);

		$result = Exprdawc_Order_Helper::build_field_metadata_array( $custom_fields, $field_values );

		$this->assertIsArray( $result );
		$this->assertCount( 2, $result );
		$this->assertEquals( 'Test Field 1', $result[0]['label'] );
		$this->assertEquals( 'Value 1', $result[0]['value'] );
		$this->assertEquals( 'Test Field 2', $result[1]['label'] );
		$this->assertEquals( '42', $result[1]['value'] );
	}

	/**
	 * Tests build_field_metadata_array with array values.
	 *
	 * Test Goal:
	 * Verifies that array values are converted to comma-separated strings.
	 */
	public function test_build_field_metadata_array_with_array_values() {
		$custom_fields = array(
			array(
				'label' => 'Checkbox Field',
				'type'  => 'checkbox',
			),
		);

		$field_values = array(
			'checkbox_field' => array( 'opt1', 'opt2', 'opt3' ),
		);

		$result = Exprdawc_Order_Helper::build_field_metadata_array( $custom_fields, $field_values );

		$this->assertIsArray( $result );
		$this->assertEquals( 'opt1, opt2, opt3', $result[0]['value'] );
	}

	/**
	 * Tests get_old_field_value.
	 *
	 * Test Goal:
	 * Verifies that old field value is retrieved correctly.
	 */
	public function test_get_old_field_value() {
		$item_metadata = array(
			'test_field' => array(
				'label' => 'Test Field',
				'value' => 'Old Value',
			),
		);

		$result = Exprdawc_Order_Helper::get_old_field_value( $item_metadata, 'test_field' );
		$this->assertEquals( 'Old Value', $result );
	}

	/**
	 * Tests get_old_field_value with missing field.
	 *
	 * Test Goal:
	 * Verifies that empty string is returned for non-existent field.
	 */
	public function test_get_old_field_value_missing() {
		$item_metadata = array();

		$result = Exprdawc_Order_Helper::get_old_field_value( $item_metadata, 'nonexistent' );
		$this->assertEquals( '', $result );
	}

	/**
	 * Tests format_field_value_for_display with string.
	 *
	 * Test Goal:
	 * Verifies that string values are formatted correctly.
	 */
	public function test_format_field_value_for_display_string() {
		$result = Exprdawc_Order_Helper::format_field_value_for_display( 'Test Value' );
		$this->assertEquals( 'Test Value', $result );
	}

	/**
	 * Tests format_field_value_for_display with array.
	 *
	 * Test Goal:
	 * Verifies that array values are converted to comma-separated string.
	 */
	public function test_format_field_value_for_display_array() {
		$result = Exprdawc_Order_Helper::format_field_value_for_display( array( 'val1', 'val2', 'val3' ) );
		$this->assertEquals( 'val1, val2, val3', $result );
	}

	/**
	 * Tests add_order_note_for_change.
	 *
	 * Test Goal:
	 * Verifies that order note is added when field value changes.
	 */
	public function test_add_order_note_for_change() {
		Exprdawc_Order_Helper::add_order_note_for_change(
			$this->order,
			'Test Field',
			'Old Value',
			'New Value'
		);

		$notes = wc_get_order_notes(
			array(
				'order_id' => $this->order_id,
				'limit'    => 1,
			)
		);

		$this->assertNotEmpty( $notes );
		$this->assertStringContainsString( 'Test Field', $notes[0]->content );
		$this->assertStringContainsString( 'Old Value', $notes[0]->content );
		$this->assertStringContainsString( 'New Value', $notes[0]->content );
	}

	/**
	 * Tests add_order_note_for_change with no change.
	 *
	 * Test Goal:
	 * Verifies that no order note is added when values are the same.
	 */
	public function test_add_order_note_for_change_no_change() {
		Exprdawc_Order_Helper::add_order_note_for_change(
			$this->order,
			'Test Field',
			'Same Value',
			'Same Value'
		);

		$notes = wc_get_order_notes(
			array(
				'order_id' => $this->order->get_id(),
			)
		);

		// No notes should be added for unchanged values.
		$this->assertEmpty( $notes );
	}

	/**
	 * Tests add_order_note_for_change with array values.
	 *
	 * Test Goal:
	 * Verifies that order note handles array values correctly.
	 */
	public function test_add_order_note_for_change_array_values() {
		Exprdawc_Order_Helper::add_order_note_for_change(
			$this->order,
			'Checkbox Field',
			array( 'opt1', 'opt2' ),
			array( 'opt1', 'opt3' )
		);

		$notes = wc_get_order_notes(
			array(
				'order_id' => $this->order_id,
				'limit'    => 1,
			)
		);

		$this->assertNotEmpty( $notes );
		$this->assertStringContainsString( 'Checkbox Field', $notes[0]->content );
	}

	/**
	 * Tests get_product_from_item with simple product.
	 *
	 * Test Goal:
	 * Verifies that product is retrieved correctly from order item.
	 */
	public function test_get_product_from_item() {
		$item_id = $this->order->add_product( $this->product, 1 );
		$item    = $this->order->get_item( $item_id );

		$result = Exprdawc_Order_Helper::get_product_from_item( $item );

		$this->assertInstanceOf( WC_Product::class, $result );
		$this->assertEquals( $this->product_id, $result->get_id() );
	}

	/**
	 * Tests get_product_from_item with variation.
	 *
	 * Test Goal:
	 * Verifies that parent product is retrieved for variations.
	 */
	public function test_get_product_from_item_variation() {
		// Create variable product.
		$parent_id = wp_insert_post(
			array(
				'post_title'  => 'Variable Product',
				'post_type'   => 'product',
				'post_status' => 'publish',
			)
		);
		$parent    = wc_get_product( $parent_id );
		$parent->set_regular_price( 100 );
		$parent->save();

		// Create variation.
		$variation_id = wp_insert_post(
			array(
				'post_title'  => 'Variation',
				'post_type'   => 'product_variation',
				'post_parent' => $parent_id,
				'post_status' => 'publish',
			)
		);
		$variation    = wc_get_product( $variation_id );
		$variation->set_regular_price( 100 );
		$variation->save();

		$item_id = $this->order->add_product( $variation, 1 );
		$item    = $this->order->get_item( $item_id );

		$result = Exprdawc_Order_Helper::get_product_from_item( $item );

		$this->assertInstanceOf( WC_Product::class, $result );
		$this->assertEquals( $parent_id, $result->get_id() );

		// Cleanup.
		wp_delete_post( $variation_id, true );
		wp_delete_post( $parent_id, true );
	}

	/**
	 * Tests get_product_from_item with invalid item.
	 *
	 * Test Goal:
	 * Verifies that false is returned for invalid items.
	 */
	public function test_get_product_from_item_invalid() {
		$item_id = $this->order->add_product( $this->product, 1 );
		$item    = $this->order->get_item( $item_id );

		// Delete product to make item invalid.
		wp_delete_post( $this->product_id, true );

		$result = Exprdawc_Order_Helper::get_product_from_item( $item );

		$this->assertFalse( $result );
	}
}
