<?php
declare( strict_types=1 );

require_once dirname( __DIR__, 2 ) . '/../src/classes/helper/class-exprdawc-helper.php';
use Triopsi\Exprdawc\Helper\Exprdawc_Helper;

/**
 * Class TestClassExprdawcHelper
 *
 * PHPUnit tests for Exprdawc_Helper class.
 *
 * @package Extra_Product_Data_For_WooCommerce\Tests\Unit
 */
class TestClassExprdawcHelper extends WP_UnitTestCase {

	/**
	 * Tests generate_input_field with various fields.
	 *
	 * Expects the generated fields to match the HTML output in the comparison file.
	 */
	public function test_generate_input_field() {
		$json_export_string = '{
            "0": {
            "label": "Input 1",
            "type": "text",
            "required": 1,
            "conditional_logic": 0,
            "placeholder_text": "Input",
            "help_text": "Please input this",
            "options": [],
            "default": "",
            "minlength": 1,
            "maxlength": 5,
            "rows": 2,
            "cols": 5,
            "autocomplete": "address-level1",
            "autofocus": false,
            "conditional_rules": [
                [
                {
                    "field": "Input 1",
                    "operator": "field_is_empty",
                    "value": ""
                }
                ]
            ],
            "index": 0,
            "editable": true,
            "adjust_price": true,
            "price_adjustment_type": "fixed",
            "price_adjustment_value": "1.55"
            },
            "1": {
            "label": "Input 2",
            "type": "long_text",
            "required": 0,
            "conditional_logic": 0,
            "placeholder_text": "Long Text",
            "help_text": "Login Text",
            "options": [],
            "default": "",
            "minlength": 0,
            "maxlength": 255,
            "rows": 2,
            "cols": 5,
            "autocomplete": "on",
            "autofocus": false,
            "conditional_rules": [
                [
                {
                    "field": "Input 1",
                    "operator": "field_is_empty",
                    "value": ""
                }
                ]
            ],
            "index": 1,
            "editable": true,
            "adjust_price": false,
            "price_adjustment_type": "fixed",
            "price_adjustment_value": ""
            },
            "2": {
            "label": "Input 3",
            "type": "email",
            "required": 0,
            "conditional_logic": 0,
            "placeholder_text": "Mail",
            "help_text": "Mail",
            "options": [],
            "default": "",
            "minlength": 0,
            "maxlength": 255,
            "rows": 2,
            "cols": 5,
            "autocomplete": "on",
            "autofocus": false,
            "conditional_rules": [
                [
                {
                    "field": "Input 2",
                    "operator": "field_is_empty",
                    "value": ""
                }
                ]
            ],
            "index": 2,
            "editable": false,
            "adjust_price": false,
            "price_adjustment_type": "fixed",
            "price_adjustment_value": ""
            },
            "3": {
            "label": "Input 4",
            "type": "number",
            "required": 0,
            "conditional_logic": 0,
            "placeholder_text": "Number",
            "help_text": "Number",
            "options": [],
            "default": "",
            "minlength": 0,
            "maxlength": 255,
            "rows": 2,
            "cols": 5,
            "autocomplete": "on",
            "autofocus": false,
            "conditional_rules": [
                [
                {
                    "field": "Input 3",
                    "operator": "field_is_empty",
                    "value": ""
                }
                ]
            ],
            "index": 3,
            "editable": false,
            "adjust_price": false,
            "price_adjustment_type": "fixed",
            "price_adjustment_value": ""
            },
            "4": {
            "label": "Input 5",
            "type": "date",
            "required": 0,
            "conditional_logic": 0,
            "placeholder_text": "Date",
            "help_text": "Date",
            "options": [],
            "default": "",
            "minlength": 0,
            "maxlength": 255,
            "rows": 2,
            "cols": 5,
            "autocomplete": "on",
            "autofocus": false,
            "conditional_rules": [
                [
                {
                    "field": "Input 4",
                    "operator": "field_is_empty",
                    "value": ""
                }
                ]
            ],
            "index": 4,
            "editable": false,
            "adjust_price": false,
            "price_adjustment_type": "fixed",
            "price_adjustment_value": ""
            },
            "5": {
            "label": "Input 6",
            "type": "yes-no",
            "required": 0,
            "conditional_logic": 0,
            "placeholder_text": "Yes/No",
            "help_text": "Yes/No",
            "options": [],
            "default": "",
            "minlength": 0,
            "maxlength": 255,
            "rows": 2,
            "cols": 5,
            "autocomplete": "on",
            "autofocus": false,
            "conditional_rules": [
                [
                {
                    "field": "Input 5",
                    "operator": "field_is_empty",
                    "value": ""
                }
                ]
            ],
            "index": 5,
            "editable": false,
            "adjust_price": false,
            "price_adjustment_type": "fixed",
            "price_adjustment_value": ""
            },
            "7": {
            "label": "Input 7",
            "type": "radio",
            "required": 0,
            "conditional_logic": 0,
            "placeholder_text": "",
            "help_text": "Help Text",
            "options": {
                "1": {
                "label": "Option 2",
                "value": "Option 2"
                },
                "0": {
                "label": "Option 1",
                "value": "Option 1"
                }
            },
            "default": "Option 1",
            "minlength": 0,
            "maxlength": 255,
            "rows": 2,
            "cols": 5,
            "autocomplete": "on",
            "autofocus": false,
            "conditional_rules": [
                [
                {
                    "field": "Input 6",
                    "operator": "field_is_empty",
                    "value": ""
                }
                ]
            ],
            "index": 7,
            "editable": false,
            "adjust_price": false,
            "price_adjustment_type": "fixed",
            "price_adjustment_value": ""
            },
            "8": {
            "label": "Check",
            "type": "checkbox",
            "required": 0,
            "conditional_logic": 0,
            "placeholder_text": "",
            "help_text": "Helper Text",
            "options": {
                "0": {
                "label": "Option A",
                "value": "Option A"
                },
                "2": {
                "label": "Option C",
                "value": "Option C"
                },
                "1": {
                "label": "Option B",
                "value": "Option B"
                }
            },
            "default": "Option B",
            "minlength": 0,
            "maxlength": 255,
            "rows": 2,
            "cols": 5,
            "autocomplete": "on",
            "autofocus": false,
            "conditional_rules": [
                [
                {
                    "field": "Input 7",
                    "operator": "field_is_empty",
                    "value": ""
                }
                ]
            ],
            "index": 8,
            "editable": false,
            "adjust_price": false,
            "price_adjustment_type": "fixed",
            "price_adjustment_value": ""
            },
            "9": {
            "label": "Name",
            "type": "text",
            "required": 0,
            "conditional_logic": 0,
            "placeholder_text": "",
            "help_text": "",
            "options": [],
            "default": "",
            "minlength": 0,
            "maxlength": 255,
            "rows": 2,
            "cols": 5,
            "autocomplete": "on",
            "autofocus": false,
            "conditional_rules": [
                [
                {
                    "field": "",
                    "operator": "field_is_empty",
                    "value": ""
                }
                ]
            ],
            "index": 9,
            "editable": true,
            "adjust_price": false,
            "price_adjustment_type": "fixed",
            "price_adjustment_value": ""
            }
        }';

		$fields = json_decode( $json_export_string, true );

		ob_start();
		foreach ( $fields as $field ) {
			Exprdawc_Helper::generate_input_field( $field );
		}
		$output = ob_get_clean();

        $this->assertEquals( file_get_contents( dirname( __DIR__ ) . '/resources/soll_field_output_test_generate_input_field.html' ), $output ); // phpcs:ignore
	}

	/**
	 * Test check_required_fields method.
	 */
	public function test_check_required_fields() {
		$product = $this->create_product_with_custom_fields( true );

		$this->assertTrue( Exprdawc_Helper::check_required_fields( $product->get_id() ) );

		$product = $this->create_product_with_custom_fields( false );

		$this->assertFalse( Exprdawc_Helper::check_required_fields( $product->get_id() ) );
	}

	/**
	 * Test WooCommerce active helper and function.
	 */
	public function test_woocommerce_active_check() {
		$this->assertTrue( Exprdawc_Helper::is_woocommerce_active() );
	}

	/**
	 * Test validate_field_by_type with empty values.
	 */
	public function test_validate_field_by_type_allows_empty() {
		$result = Exprdawc_Helper::validate_field_by_type( '', 'email' );
		$this->assertTrue( $result['valid'] );
		$this->assertSame( '', $result['message'] );
	}

	/**
	 * Test validate_field_by_type for email values.
	 */
	public function test_validate_field_by_type_email() {
		$invalid = Exprdawc_Helper::validate_field_by_type( 'not-an-email', 'email' );
		$this->assertFalse( $invalid['valid'] );

		$valid = Exprdawc_Helper::validate_field_by_type( 'user@example.com', 'email' );
		$this->assertTrue( $valid['valid'] );
	}

	/**
	 * Test validate_field_by_type for number values.
	 */
	public function test_validate_field_by_type_number() {
		$invalid = Exprdawc_Helper::validate_field_by_type( 'abc', 'number' );
		$this->assertFalse( $invalid['valid'] );

		$valid = Exprdawc_Helper::validate_field_by_type( '123.45', 'number' );
		$this->assertTrue( $valid['valid'] );
	}

	/**
	 * Test validate_field_by_type for date values.
	 */
	public function test_validate_field_by_type_date() {
		$invalid = Exprdawc_Helper::validate_field_by_type( 'not-a-date', 'date' );
		$this->assertFalse( $invalid['valid'] );

		$valid = Exprdawc_Helper::validate_field_by_type( '2026-02-20', 'date' );
		$this->assertTrue( $valid['valid'] );
	}

	/**
	 * Test validate_field_by_type for URL values.
	 */
	public function test_validate_field_by_type_url() {
		$invalid = Exprdawc_Helper::validate_field_by_type( 'not-a-url', 'url' );
		$this->assertFalse( $invalid['valid'] );

		$valid = Exprdawc_Helper::validate_field_by_type( 'https://example.com', 'url' );
		$this->assertTrue( $valid['valid'] );
	}

	/**
	 * Test validate_field_by_type for option selections.
	 */
	public function test_validate_field_by_type_options() {
		$options = array(
			array( 'value' => 'a' ),
			array( 'value' => 'b' ),
		);

		$invalid = Exprdawc_Helper::validate_field_by_type( 'c', 'select', $options );
		$this->assertFalse( $invalid['valid'] );

		$valid = Exprdawc_Helper::validate_field_by_type( 'a', 'radio', $options );
		$this->assertTrue( $valid['valid'] );

		$valid_multi = Exprdawc_Helper::validate_field_by_type( array( 'a', 'b' ), 'multiselect', $options );
		$this->assertTrue( $valid_multi['valid'] );
	}

	/**
	 * Test get_option_values extracts values from options array.
	 */
	public function test_get_option_values() {
		$options = array(
			array(
				'value' => 'option1',
				'label' => 'Option 1',
			),
			array(
				'value' => 'option2',
				'label' => 'Option 2',
			),
			array(
				'value' => 'option3',
				'label' => 'Option 3',
			),
		);

		$result = Exprdawc_Helper::get_option_values( $options );

		$this->assertIsArray( $result );
		$this->assertCount( 3, $result );
		$this->assertEquals( array( 'option1', 'option2', 'option3' ), $result );
	}

	/**
	 * Test get_option_values with empty array.
	 */
	public function test_get_option_values_empty() {
		$result = Exprdawc_Helper::get_option_values( array() );

		$this->assertIsArray( $result );
		$this->assertEmpty( $result );
	}

	/**
	 * Test validate_option_selection with single valid value.
	 */
	public function test_validate_option_selection_single_valid() {
		$options = array(
			array( 'value' => 'red' ),
			array( 'value' => 'green' ),
			array( 'value' => 'blue' ),
		);

		$result = Exprdawc_Helper::validate_option_selection( 'red', $options );
		$this->assertTrue( $result );

		$result = Exprdawc_Helper::validate_option_selection( 'green', $options );
		$this->assertTrue( $result );
	}

	/**
	 * Test validate_option_selection with single invalid value.
	 */
	public function test_validate_option_selection_single_invalid() {
		$options = array(
			array( 'value' => 'red' ),
			array( 'value' => 'green' ),
		);

		$result = Exprdawc_Helper::validate_option_selection( 'yellow', $options );
		$this->assertFalse( $result );
	}

	/**
	 * Test validate_option_selection with multiple valid values.
	 */
	public function test_validate_option_selection_multiple_valid() {
		$options = array(
			array( 'value' => 'red' ),
			array( 'value' => 'green' ),
			array( 'value' => 'blue' ),
		);

		$result = Exprdawc_Helper::validate_option_selection( array( 'red', 'blue' ), $options );
		$this->assertTrue( $result );

		$result = Exprdawc_Helper::validate_option_selection( array( 'red', 'green', 'blue' ), $options );
		$this->assertTrue( $result );
	}

	/**
	 * Test validate_option_selection with multiple invalid values.
	 */
	public function test_validate_option_selection_multiple_invalid() {
		$options = array(
			array( 'value' => 'red' ),
			array( 'value' => 'green' ),
		);

		// One valid, one invalid.
		$result = Exprdawc_Helper::validate_option_selection( array( 'red', 'yellow' ), $options );
		$this->assertFalse( $result );

		// All invalid.
		$result = Exprdawc_Helper::validate_option_selection( array( 'yellow', 'purple' ), $options );
		$this->assertFalse( $result );
	}

	/**
	 * Test validate_option_selection with empty options array.
	 */
	public function test_validate_option_selection_empty_options() {
		$result = Exprdawc_Helper::validate_option_selection( 'red', array() );
		$this->assertFalse( $result );
	}

	/**
	 * Test prepare_option_labels_with_prices for fixed price adjustments.
	 */
	public function test_prepare_option_labels_with_prices_fixed() {
		$field_args = array(
			'options' => array(
				array(
					'label'                  => 'Small',
					'value'                  => 'small',
					'price_adjustment_type'  => 'fixed',
					'price_adjustment_value' => '5.00',
				),
				array(
					'label'                  => 'Large',
					'value'                  => 'large',
					'price_adjustment_type'  => 'fixed',
					'price_adjustment_value' => '10.50',
				),
			),
		);

		$reflection = new ReflectionClass( Exprdawc_Helper::class );
		$method     = $reflection->getMethod( 'prepare_option_labels_with_prices' );
		$method->setAccessible( true );

		$result = $method->invokeArgs( null, array( $field_args ) );

		$currency_symbol = get_woocommerce_currency_symbol();
		$this->assertStringContainsString( '+5.00', $result['options'][0]['label'] );
		$this->assertStringContainsString( $currency_symbol, $result['options'][0]['label'] );
		$this->assertStringContainsString( '+10.50', $result['options'][1]['label'] );
	}

	/**
	 * Test prepare_option_labels_with_prices for percentage adjustments.
	 */
	public function test_prepare_option_labels_with_prices_percentage() {
		$field_args = array(
			'options' => array(
				array(
					'label'                  => 'Premium',
					'value'                  => 'premium',
					'price_adjustment_type'  => 'percentage',
					'price_adjustment_value' => '20',
				),
				array(
					'label'                  => 'Discount',
					'value'                  => 'discount',
					'price_adjustment_type'  => 'percentage',
					'price_adjustment_value' => '-10',
				),
			),
		);

		$reflection = new ReflectionClass( Exprdawc_Helper::class );
		$method     = $reflection->getMethod( 'prepare_option_labels_with_prices' );
		$method->setAccessible( true );

		$result = $method->invokeArgs( null, array( $field_args ) );

		$this->assertStringContainsString( '+20%', $result['options'][0]['label'] );
		$this->assertStringContainsString( 'Premium', $result['options'][0]['label'] );
		$this->assertStringContainsString( '-10%', $result['options'][1]['label'] );
		$this->assertStringContainsString( 'Discount', $result['options'][1]['label'] );
	}

	/**
	 * Test prepare_option_labels_with_prices skips zero values.
	 */
	public function test_prepare_option_labels_with_prices_zero_value() {
		$field_args = array(
			'options' => array(
				array(
					'label'                  => 'Standard',
					'value'                  => 'standard',
					'price_adjustment_type'  => 'fixed',
					'price_adjustment_value' => '0',
				),
			),
		);

		$reflection = new ReflectionClass( Exprdawc_Helper::class );
		$method     = $reflection->getMethod( 'prepare_option_labels_with_prices' );
		$method->setAccessible( true );

		$result = $method->invokeArgs( null, array( $field_args ) );

		// Label should remain unchanged as value is 0.
		$this->assertEquals( 'Standard', $result['options'][0]['label'] );
	}

	/**
	 * Test prepare_option_labels_with_prices with empty values.
	 */
	public function test_prepare_option_labels_with_prices_empty_value() {
		$field_args = array(
			'options' => array(
				array(
					'label'                  => 'Basic',
					'value'                  => 'basic',
					'price_adjustment_type'  => 'fixed',
					'price_adjustment_value' => '',
				),
			),
		);

		$reflection = new ReflectionClass( Exprdawc_Helper::class );
		$method     = $reflection->getMethod( 'prepare_option_labels_with_prices' );
		$method->setAccessible( true );

		$result = $method->invokeArgs( null, array( $field_args ) );

		// Label should remain unchanged as value is empty.
		$this->assertEquals( 'Basic', $result['options'][0]['label'] );
	}

	/**
	 * Test prepare_option_labels_with_prices with no options.
	 */
	public function test_prepare_option_labels_with_prices_no_options() {
		$field_args = array(
			'options' => array(),
		);

		$reflection = new ReflectionClass( Exprdawc_Helper::class );
		$method     = $reflection->getMethod( 'prepare_option_labels_with_prices' );
		$method->setAccessible( true );

		$result = $method->invokeArgs( null, array( $field_args ) );

		$this->assertEmpty( $result['options'] );
	}

	/**
	 * Helper method to create a product with custom fields.
	 *
	 * @param bool $required Whether the custom field is required.
	 * @return WC_Product
	 */
	protected function create_product_with_custom_fields( $required ) {
		$product = new WC_Product_Simple();
		$product->set_name( 'Test Product' );
		$product->save();

		$custom_fields = array(
			array(
				'label'    => 'Custom Field',
				'type'     => 'text',
				'required' => $required,
			),
		);

		$product->update_meta_data( '_extra_product_fields', $custom_fields );
		$product->save();

		return $product;
	}
}
