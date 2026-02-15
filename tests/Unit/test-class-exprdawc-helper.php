<?php
/**
 * Class Test_Exprdawc_Helper
 *
 * @package Extra_Product_Data_For_WooCommerce
 */

require_once dirname( __DIR__ ) . '/../src/classes/class-exprdawc-helper.php';

use Triopsi\Exprdawc\Exprdawc_Helper;

/**
 * Class Test_Exprdawc_Helper
 *
 * PHPUnit tests for Exprdawc_Helper class.
 *
 * @package Extra_Product_Data_For_WooCommerce\Tests\Unit
 */
class Test_Exprdawc_Helper extends WP_UnitTestCase {

	/**
	 * Tests generate_input_field with various fields.
	 *
	 * Expects the generated fields to match the HTML output in the comparison file.
	 *
	 * @covers Exprdawc_Helper::generate_input_field
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

		// save output to file in the test directory unter resources directory
		// file_put_contents( __DIR__ . '/resources/soll_field_output_test_generate_input_field.html', $output );

		$this->assertEquals( file_get_contents( __DIR__ . '/resources/soll_field_output_test_generate_input_field.html' ), $output );
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
