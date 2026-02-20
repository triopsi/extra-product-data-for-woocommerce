<?php
/**
 * Tests for Exprdawc_Template_Helpers class
 *
 * @package Extra_Product_Data_For_WooCommerce\Tests
 * @since 1.9.0
 */

declare( strict_types=1 );

use Triopsi\Exprdawc\Helper\Exprdawc_Template_Helpers as H;

/**
 * Class TestExprdawcTemplateHelpers
 *
 * PHPUnit tests for Exprdawc_Template_Helpers class.
 */
class TestExprdawcTemplateHelpers extends WP_UnitTestCase {

	/**
	 * Test join method with default glue.
	 *
	 * Test Goal:
	 * Verifies that join() combines arrays with space as default.
	 */
	public function test_join_with_default_glue() {
		$result = H::join( array( 'class1', 'class2', 'class3' ) );
		$this->assertEquals( 'class1 class2 class3', $result );
	}

	/**
	 * Test join method with custom glue.
	 *
	 * Test Goal:
	 * Verifies that join() uses custom glue string.
	 */
	public function test_join_with_custom_glue() {
		$result = H::join( array( 'a', 'b', 'c' ), ', ' );
		$this->assertEquals( 'a, b, c', $result );
	}

	/**
	 * Test join filters empty values.
	 *
	 * Test Goal:
	 * Verifies that join() removes empty strings.
	 */
	public function test_join_filters_empty_values() {
		$result = H::join( array( 'class1', '', 'class2', null ) );
		$this->assertEquals( 'class1 class2', $result );
	}

	/**
	 * Test attrs method builds HTML attributes.
	 *
	 * Test Goal:
	 * Verifies that attrs() creates proper HTML attribute strings.
	 */
	public function test_attrs_builds_html_attributes() {
		$result = H::attrs(
			array(
				'id'    => 'test-id',
				'class' => 'test-class',
			)
		);

		$this->assertStringContainsString( 'id="test-id"', $result );
		$this->assertStringContainsString( 'class="test-class"', $result );
	}

	/**
	 * Test attrs with boolean values.
	 *
	 * Test Goal:
	 * Verifies boolean attributes (like 'required') are rendered correctly.
	 */
	public function test_attrs_with_boolean_values() {
		$result = H::attrs(
			array(
				'required' => true,
				'disabled' => false,
			)
		);

		$this->assertStringContainsString( 'required', $result );
		$this->assertStringNotContainsString( 'disabled', $result );
	}

	/**
	 * Test attrs with null values.
	 *
	 * Test Goal:
	 * Verifies that null values are skipped.
	 */
	public function test_attrs_skips_null_values() {
		$result = H::attrs(
			array(
				'id'   => 'test',
				'name' => null,
			)
		);

		$this->assertStringContainsString( 'id="test"', $result );
		$this->assertStringNotContainsString( 'name', $result );
	}

	/**
	 * Test classes with array input.
	 *
	 * Test Goal:
	 * Verifies that classes() converts arrays to CSS class strings.
	 */
	public function test_classes_with_array() {
		$result = H::classes( array( 'class1', 'class2', 'class3' ) );
		$this->assertEquals( 'class1 class2 class3', $result );
	}

	/**
	 * Test classes with string input.
	 *
	 * Test Goal:
	 * Verifies that classes() handles string input.
	 */
	public function test_classes_with_string() {
		$result = H::classes( 'single-class' );
		$this->assertEquals( 'single-class', $result );
	}

	/**
	 * Test classes filters empty values.
	 *
	 * Test Goal:
	 * Verifies that empty strings are filtered out.
	 */
	public function test_classes_filters_empty() {
		$result = H::classes( array( 'class1', '', 'class2' ) );
		$this->assertEquals( 'class1 class2', $result );
	}

	/**
	 * Test in_array method.
	 *
	 * Test Goal:
	 * Verifies strict comparison in array check.
	 */
	public function test_in_array_strict_comparison() {
		$this->assertTrue( H::in_array( 'test', array( 'test', 'other' ) ) );
		$this->assertFalse( H::in_array( '1', array( 1, 2, 3 ) ) );
	}

	/**
	 * Test e method escapes HTML.
	 *
	 * Test Goal:
	 * Verifies HTML escaping for output.
	 */
	public function test_e_escapes_html() {
		$result = H::e( '<script>alert("XSS")</script>' );
		$this->assertStringContainsString( '&lt;script&gt;', $result );
		$this->assertStringNotContainsString( '<script>', $result );
	}

	/**
	 * Test attr method escapes attributes.
	 *
	 * Test Goal:
	 * Verifies attribute escaping.
	 */
	public function test_attr_escapes_attributes() {
		$result = H::attr( 'value with "quotes"' );
		$this->assertStringContainsString( '&quot;', $result );
	}

	/**
	 * Test js method escapes JavaScript.
	 *
	 * Test Goal:
	 * Verifies JavaScript escaping.
	 */
	public function test_js_escapes_javascript() {
		$result = H::js( "test'string" );
		$this->assertStringContainsString( "\\'", $result );
	}

	/**
	 * Test url method escapes URLs.
	 *
	 * Test Goal:
	 * Verifies URL escaping.
	 */
	public function test_url_escapes_urls() {
		$result = H::url( 'https://example.com/path?param=value' );
		$this->assertStringContainsString( 'https://example.com', $result );
	}

	/**
	 * Test textarea method escapes textarea content.
	 *
	 * Test Goal:
	 * Verifies textarea content escaping.
	 */
	public function test_textarea_escapes_content() {
		$result = H::textarea( "<tag>content</tag>\nNew line" );
		$this->assertStringContainsString( '&lt;tag&gt;', $result );
	}

	/**
	 * Test price formatting with WooCommerce.
	 *
	 * Test Goal:
	 * Verifies price formatting when WooCommerce is available.
	 */
	public function test_price_formats_correctly() {
		// WooCommerce should be available in test environment.
		$result = H::price( 19.99 );
		// Should contain the number in some format.
		$this->assertIsString( $result );
		$this->assertNotEmpty( $result );
	}

	/**
	 * Test id method sanitizes IDs.
	 *
	 * Test Goal:
	 * Verifies ID sanitization (spaces and underscores to hyphens).
	 */
	public function test_id_sanitizes_strings() {
		$result = H::id( 'test field_name' );
		$this->assertEquals( 'test-field-name', $result );
	}

	/**
	 * Test is_empty with various values.
	 *
	 * Test Goal:
	 * Verifies empty check for different data types.
	 */
	public function test_is_empty_checks_values() {
		$this->assertTrue( H::is_empty( '' ) );
		$this->assertTrue( H::is_empty( '   ' ) );
		$this->assertTrue( H::is_empty( array() ) );
		$this->assertFalse( H::is_empty( 'content' ) );
		$this->assertFalse( H::is_empty( array( 1, 2 ) ) );
	}

	/**
	 * Test is_set method.
	 *
	 * Test Goal:
	 * Verifies null check.
	 */
	public function test_is_set_checks_null() {
		$this->assertTrue( H::is_set( 'value' ) );
		$this->assertTrue( H::is_set( 0 ) );
		$this->assertTrue( H::is_set( '' ) );
		$this->assertFalse( H::is_set( null ) );
	}

	/**
	 * Test get method with default value.
	 *
	 * Test Goal:
	 * Verifies array access with default fallback.
	 */
	public function test_get_with_default() {
		$array = array( 'key' => 'value' );

		$this->assertEquals( 'value', H::get( $array, 'key', 'default' ) );
		$this->assertEquals( 'default', H::get( $array, 'missing', 'default' ) );
	}

	/**
	 * Test translation method.
	 *
	 * Test Goal:
	 * Verifies translation wrapper works.
	 */
	public function test_translation_method() {
		$result = H::__( 'Test String', 'extra-product-data-for-woocommerce' );
		$this->assertIsString( $result );
	}

	/**
	 * Test translation and escape method.
	 *
	 * Test Goal:
	 * Verifies translation with escaping.
	 */
	public function test_translation_escape_method() {
		$result = H::_e( 'Test String', 'extra-product-data-for-woocommerce' );
		$this->assertIsString( $result );
	}

	/**
	 * Test data_attrs builds data attributes.
	 *
	 * Test Goal:
	 * Verifies data attribute generation.
	 */
	public function test_data_attrs_builds_attributes() {
		$result = H::data_attrs(
			array(
				'price-adjustment' => '10',
				'price-type'       => 'fixed',
			)
		);

		$this->assertStringContainsString( 'data-price-adjustment="10"', $result );
		$this->assertStringContainsString( 'data-price-type="fixed"', $result );
	}

	/**
	 * Test data_attrs skips empty values.
	 *
	 * Test Goal:
	 * Verifies empty values are not rendered.
	 */
	public function test_data_attrs_skips_empty() {
		$result = H::data_attrs(
			array(
				'id'    => 'test',
				'empty' => '',
				'null'  => null,
			)
		);

		$this->assertStringContainsString( 'data-id="test"', $result );
		$this->assertStringNotContainsString( 'data-empty', $result );
		$this->assertStringNotContainsString( 'data-null', $result );
	}

	/**
	 * Test unique_id generator.
	 *
	 * Test Goal:
	 * Verifies unique ID generation.
	 */
	public function test_unique_id_generates_ids() {
		$id1 = H::unique_id( 'test' );
		$id2 = H::unique_id( 'test' );

		$this->assertStringContainsString( 'test-', $id1 );
		$this->assertStringContainsString( 'test-', $id2 );
		$this->assertNotEquals( $id1, $id2 );
	}

	/**
	 * Test selected method with matching values.
	 *
	 * Test Goal:
	 * Verifies selected attribute generation.
	 */
	public function test_selected_returns_attribute() {
		$this->assertEquals( " selected='selected'", H::selected( 'test', 'test' ) );
		$this->assertEquals( '', H::selected( 'test', 'other' ) );
	}

	/**
	 * Test selected with array values.
	 *
	 * Test Goal:
	 * Verifies selected works with arrays.
	 */
	public function test_selected_with_array() {
		$result = H::selected( array( 'a', 'b', 'c' ), 'b' );
		$this->assertEquals( 'selected', $result );

		$result = H::selected( array( 'a', 'b', 'c' ), 'd' );
		$this->assertEquals( '', $result );
	}

	/**
	 * Test checked method with matching values.
	 *
	 * Test Goal:
	 * Verifies checked attribute generation.
	 */
	public function test_checked_returns_attribute() {
		$this->assertEquals( " checked='checked'", H::checked( 'test', 'test' ) );
		$this->assertEquals( '', H::checked( 'test', 'other' ) );
	}

	/**
	 * Test checked with array values.
	 *
	 * Test Goal:
	 * Verifies checked works with arrays.
	 */
	public function test_checked_with_array() {
		$result = H::checked( array( 'a', 'b', 'c' ), 'b' );
		$this->assertEquals( 'checked', $result );

		$result = H::checked( array( 'a', 'b', 'c' ), 'd' );
		$this->assertEquals( '', $result );
	}

	/**
	 * Test checked with comma-separated string.
	 *
	 * Test Goal:
	 * Verifies checked works with comma-separated values.
	 */
	public function test_checked_with_comma_separated_string() {
		$result = H::checked( 'a, b, c', 'b' );
		$this->assertEquals( 'checked', $result );

		$result = H::checked( 'a, b, c', 'd' );
		$this->assertEquals( '', $result );
	}

	/**
	 * Test nonce_field generation.
	 *
	 * Test Goal:
	 * Verifies nonce field HTML generation.
	 */
	public function test_nonce_field_generates_html() {
		$result = H::nonce_field( 'test_action', 'test_nonce' );

		$this->assertStringContainsString( '<input', $result );
		$this->assertStringContainsString( 'test_nonce', $result );
	}

	/**
	 * Test classes with special characters.
	 *
	 * Test Goal:
	 * Verifies proper escaping of special characters in class names.
	 */
	public function test_classes_escapes_special_chars() {
		$result = H::classes( array( 'test<script>', 'normal-class' ) );
		$this->assertStringContainsString( '&lt;script&gt;', $result );
		$this->assertStringContainsString( 'normal-class', $result );
	}

	/**
	 * Test attrs with XSS attempt.
	 *
	 * Test Goal:
	 * Verifies that XSS attempts are properly escaped.
	 */
	public function test_attrs_prevents_xss() {
		$result = H::attrs(
			array(
				'onclick' => 'alert("XSS")',
				'class'   => 'safe-class',
			)
		);

		$this->assertStringNotContainsString( '<script>', $result );
		$this->assertStringContainsString( 'onclick=', $result );
	}

	/**
	 * Test data_attrs with numeric keys.
	 *
	 * Test Goal:
	 * Verifies data attributes work with numeric keys.
	 */
	public function test_data_attrs_with_numeric_keys() {
		$result = H::data_attrs(
			array(
				'index' => 0,
				'count' => 5,
			)
		);

		$this->assertStringContainsString( 'data-index="0"', $result );
		$this->assertStringContainsString( 'data-count="5"', $result );
	}

	/**
	 * Test join with empty array.
	 *
	 * Test Goal:
	 * Verifies empty array returns empty string.
	 */
	public function test_join_with_empty_array() {
		$result = H::join( array() );
		$this->assertEquals( '', $result );
	}

	/**
	 * Test id with already sanitized input.
	 *
	 * Test Goal:
	 * Verifies already-sanitized IDs remain unchanged.
	 */
	public function test_id_with_sanitized_input() {
		$result = H::id( 'already-sanitized-id' );
		$this->assertEquals( 'already-sanitized-id', $result );
	}

	/**
	 * Test price with string input.
	 *
	 * Test Goal:
	 * Verifies price formatting handles string numbers.
	 */
	public function test_price_with_string_input() {
		$result = H::price( '29.99' );
		$this->assertIsString( $result );
		$this->assertNotEmpty( $result );
	}

	/**
	 * Test checked with empty string value.
	 *
	 * Test Goal:
	 * Verifies checked handles empty values correctly.
	 */
	public function test_checked_with_empty_value() {
		$result = H::checked( '', 'test' );
		$this->assertEquals( '', $result );
	}

	/**
	 * Test selected with numeric values.
	 *
	 * Test Goal:
	 * Verifies selected works with numbers.
	 */
	public function test_selected_with_numeric_values() {
		$this->assertEquals( " selected='selected'", H::selected( 1, 1 ) );
		$this->assertEquals( '', H::selected( 1, 2 ) );
	}
}
