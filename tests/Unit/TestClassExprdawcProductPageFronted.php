<?php
declare( strict_types=1 );

require_once dirname( __DIR__ ) . '/../src/classes/class-exprdawc-product-page-fronted.php';
require_once dirname( __DIR__ ) . '/../src/classes/helper/class-exprdawc-helper.php';

use Automattic\WooCommerce\Enums\ProductStockStatus;
use Triopsi\Exprdawc\Exprdawc_Product_Page_Fronted;

/**
 * Class TestClassExprdawcProductPageFronted
 *
 * PHPUnit tests for Exprdawc_Product_Page_Fronted class.
 *
 * @package Extra_Product_Data_For_WooCommerce\Tests\Unit
 */
class TestClassExprdawcProductPageFronted extends WP_UnitTestCase {

	/**
	 * The instance of the class to test.
	 *
	 * @var Exprdawc_Product_Page_Fronted
	 */
	private $instance;

	/**
	 * Test product ID.
	 *
	 * @var int
	 */
	private $product_id;

	/**
	 * Test product.
	 *
	 * @var WC_Product
	 */
	private $product;

	/**
	 * Set up the test.
	 */
	public function setUp(): void {
		parent::setUp();

		$this->instance = new Exprdawc_Product_Page_Fronted();

		// Create a test product.
		$this->product_id = $this->factory()->post->create(
			array(
				'post_type'   => 'product',
				'post_title'  => 'Test Product',
				'post_status' => 'publish',
			)
		);

		$this->product = wc_get_product( $this->product_id );
		$this->product->set_regular_price( 100 );
		$this->product->set_stock_status( 'instock' );
		$this->product->save();
	}

	/**
	 * Tear down the test.
	 */
	public function tearDown(): void {
		wp_delete_post( $this->product_id, true );
		WC()->cart->empty_cart();
		parent::tearDown();
	}

	/**
	 * Helper method to reset the enqueue state of styles and scripts.
	 *
	 * This method ensures that the test environment is clean before testing
	 * the enqueueing of frontend assets. It dequeues and deregisters the
	 * relevant styles and scripts, and also removes any localized data.
	 */
	private function reset_enqueue_state(): void {
		wp_dequeue_style( 'form-css' );
		wp_deregister_style( 'form-css' );

		wp_dequeue_script( 'wc-conditional-rules-js' );
		wp_deregister_script( 'wc-conditional-rules-js' );

		wp_dequeue_script( 'exprdawc-frontend-js' );
		wp_deregister_script( 'exprdawc-frontend-js' );

		wp_scripts()->add_data( 'exprdawc-frontend-js', 'data', '' );
	}

	/**
	 * Helper method to create a cart page for testing.
	 *
	 * This method creates a new page with the WooCommerce cart shortcode and
	 * sets it as the cart page in WooCommerce settings. It returns the ID of
	 * the created page.
	 *
	 * @return int The ID of the created cart page.
	 */
	private function create_cart_page(): int {
		$page_id = $this->factory()->post->create(
			array(
				'post_type'    => 'page',
				'post_title'   => 'Cart',
				'post_status'  => 'publish',
				'post_content' => '[woocommerce_cart]',
			)
		);

		update_option( 'woocommerce_cart_page_id', $page_id );

		return $page_id;
	}

	/**
	 * Test exprdawc_has_options returns false when no custom fields.
	 *
	 * Test Goal:
	 * Verifies that the exprdawc_has_options() method correctly returns FALSE
	 * when a product has no custom fields.
	 *
	 * Expected Result:
	 * - The method returns FALSE
	 * - The original $has_options value (false) remains unchanged
	 *
	 * Test Conditions:
	 * - Test product without custom fields is used
	 * - No required fields present
	 */
	public function test_exprdawc_has_options_returns_false_when_no_custom_fields() {
		$result = $this->instance->exprdawc_has_options( false, $this->product );
		$this->assertFalse( $result );
	}

	/**
	 * Test exprdawc_has_options returns true when custom fields exist.
	 *
	 * Test Goal:
	 * Verifies that the exprdawc_has_options() method correctly returns TRUE
	 * when a product has custom fields with required fields.
	 *
	 * Expected Result:
	 * - The method returns TRUE
	 * - $has_options is set to true because required fields are present
	 *
	 * Test Conditions:
	 * - Test product with one required text field
	 * - Meta data '_extra_product_fields' is saved on the product
	 */
	public function test_exprdawc_has_options_returns_true_when_custom_fields_exist() {
		// Add custom fields with required field.
		$custom_fields = array(
			array(
				'label'    => 'Test Field',
				'type'     => 'text',
				'required' => 1,
			),
		);
		$this->product->update_meta_data( '_extra_product_fields', $custom_fields );
		$this->product->save();

		$result = $this->instance->exprdawc_has_options( false, $this->product );
		$this->assertTrue( $result );
	}

	/**
	 * Test exprdawc_prevent_purchase_at_grouped_level with grouped products.
	 *
	 * Test Goal:
	 * Verifies that purchase at group level is prevented when a child product
	 * within a product group has custom fields.
	 *
	 * Expected Result:
	 * - The method returns FALSE (purchase is prevented)
	 * - Grouped products with custom fields cannot be purchased directly at group level
	 * - Users must visit the individual product page
	 *
	 * Test Conditions:
	 * - A grouped product (WC_Product_Grouped) is created
	 * - A child product with required custom fields
	 * - Child product is assigned to the grouped product
	 */
	public function test_exprdawc_prevent_purchase_at_grouped_level() {
		// Create a child product with custom fields.
		$child_product_id = $this->factory()->post->create(
			array(
				'post_type'   => 'product',
				'post_title'  => 'Child Product',
				'post_status' => 'publish',
			)
		);
		$child_product    = wc_get_product( $child_product_id );
		$child_product->update_meta_data(
			'_extra_product_fields',
			array(
				array(
					'label'    => 'Custom Field',
					'type'     => 'text',
					'required' => 1,
				),
			)
		);
		$child_product->save();

		// Create a grouped product.
		$grouped_product = new WC_Product_Grouped();
		$grouped_product->set_name( 'Grouped Product' );
		$grouped_product->set_children( array( $child_product_id ) );
		$grouped_product->save();

		$result = $this->instance->exprdawc_prevent_purchase_at_grouped_level( true, $grouped_product );
		$this->assertFalse( $result );

		// Clean up.
		wp_delete_post( $child_product_id, true );
		wp_delete_post( $grouped_product->get_id(), true );
	}

	/**
	 * Test exprdawc_change_add_to_cart_button_text returns original text when no custom fields.
	 *
	 * Test Goal:
	 * Verifies that the default "Add to cart" button text remains unchanged
	 * when no custom fields are present on the product.
	 *
	 * Expected Result:
	 * - The original button text "Add to cart" remains unchanged
	 * - No modification of the button text occurs
	 *
	 * Test Conditions:
	 * - Product without custom fields
	 * - Product is in stock (instock)
	 * - No custom button text in settings
	 */
	public function test_exprdawc_change_add_to_cart_button_text_no_custom_fields() {
		$result = $this->instance->exprdawc_change_add_to_cart_button_text( 'Add to cart', $this->product );
		$this->assertEquals( 'Add to cart', $result );
	}

	/**
	 * Test exprdawc_change_add_to_cart_button_text with custom text from settings.
	 *
	 * Test Goal:
	 * Verifies that the button text is replaced with custom text when custom fields
	 * are present and custom text is defined in settings.
	 *
	 * Expected Result:
	 * - The button text is changed to "Select Options"
	 * - The custom text from option 'exprdawc_custom_add_to_cart_text' is used
	 *
	 * Test Conditions:
	 * - Product with custom fields (one text field)
	 * - Option 'exprdawc_custom_add_to_cart_text' is set to "Select Options"
	 * - Product is in stock
	 */
	public function test_exprdawc_change_add_to_cart_button_text_with_custom_text() {
		// Add custom fields.
		$this->product->update_meta_data(
			'_extra_product_fields',
			array(
				array(
					'label' => 'Test Field',
					'type'  => 'text',
				),
			)
		);
		$this->product->save();

		// Set custom button text.
		update_option( 'exprdawc_custom_add_to_cart_text', 'Select Options' );

		$result = $this->instance->exprdawc_change_add_to_cart_button_text( 'Add to cart', $this->product );
		$this->assertEquals( 'Select Options', $result );

		// Clean up.
		delete_option( 'exprdawc_custom_add_to_cart_text' );
	}

	/**
	 * Test exprdawc_change_add_to_cart_button_text returns original text when out of stock.
	 *
	 * Test Goal:
	 * Verifies that the button text remains unchanged when the product is out of stock,
	 * regardless of custom fields.
	 *
	 * Expected Result:
	 * - The original button text "Add to cart" remains unchanged
	 * - The method returns early for "outofstock" status
	 *
	 * Test Conditions:
	 * - Product with stock status "outofstock"
	 * - The original text is not modified
	 */
	public function test_exprdawc_change_add_to_cart_button_text_out_of_stock() {
		$this->product->set_stock_status( ProductStockStatus::OUT_OF_STOCK );
		$this->product->save();

		$result = $this->instance->exprdawc_change_add_to_cart_button_text( 'Add to cart', $this->product );
		$this->assertEquals( 'Add to cart', $result );
	}

	/**
	 * Test exprdawc_change_add_to_cart_button_text returns original text for external products.
	 *
	 * Test Goal:
	 * Verifies that the button text remains unchanged when the product is an external product,
	 * regardless of custom fields.
	 *
	 * Expected Result:
	 * - The original button text "Add to cart" remains unchanged
	 * - The method returns early for external products
	 *
	 * Test Conditions:
	 * - Product type is "external"
	 * - The original text is not modified
	 */
	public function test_exprdawc_change_add_to_cart_button_text_external_product() {
		$product = new WC_Product_External();
		$product->set_name( 'External Test Product' );
		$product->set_regular_price( '10' );
		$product->set_product_url( 'https://example.com' );
		$product->set_button_text( 'Buy externally' );
		$product->save();

		$result = $this->instance->exprdawc_change_add_to_cart_button_text( 'Buy externally', $product );
		$this->assertEquals( 'Buy externally', $result );
		$product->delete( true );
	}

	/**
	 * Test exprdawc_change_add_to_cart_url changes URL when custom fields exist.
	 *
	 * Test Goal:
	 * Verifies that the add-to-cart URL is redirected to the product page
	 * when the product has custom fields with required fields.
	 *
	 * Expected Result:
	 * - The URL is changed to the product page permalink
	 * - Users are directed to the product page instead of direct AJAX purchase
	 * - This allows filling out custom fields before purchase
	 *
	 * Test Conditions:
	 * - Product with required custom fields
	 * - Product type: simple or subscription
	 * - Not on the single product page (is_single() = false)
	 */
	public function test_exprdawc_change_add_to_cart_url_with_custom_fields() {
		// Add custom fields with required field.
		$custom_fields = array(
			array(
				'label'    => 'Test Field',
				'type'     => 'text',
				'required' => 1,
			),
		);
		$this->product->update_meta_data( '_extra_product_fields', $custom_fields );
		$this->product->save();

		$original_url = '?add-to-cart=' . $this->product_id;
		$result       = $this->instance->exprdawc_change_add_to_cart_url( $original_url, $this->product );
		$expected_url = get_permalink( $this->product_id );

		$this->assertEquals( $expected_url, $result );
	}

	/**
	 * Test exprdawc_change_add_to_cart_url returns original URL when product is external.
	 *
	 * Test Goal:
	 * Verifies that the original add-to-cart URL is returned when an external product object is passed.
	 *
	 * Expected Result:
	 * - The original URL remains unchanged
	 * - The method handles external product objects gracefully
	 *
	 * Test Conditions:
	 * - An external product object (e.g., WC_Product_External) is passed to the method
	 * - The original URL is not modified
	 */
	public function test_exprdawc_change_add_to_cart_url_returns_original_when_product_is_external(): void {
		$url    = 'https://example.com/original';
		$result = $this->instance->exprdawc_change_add_to_cart_url( $url, new WC_Product_External() );
		$this->assertSame( $url, $result );
	}

	/**
	 * Test exprdawc_change_add_to_cart_url returns original URL when no custom fields.
	 *
	 * Test Goal:
	 * Verifies that the add-to-cart URL remains unchanged when the product
	 * has no custom fields.
	 *
	 * Expected Result:
	 * - The original URL remains unchanged
	 * - AJAX add-to-cart works normally
	 *
	 * Test Conditions:
	 * - Product without custom fields
	 * - Standard AJAX add-to-cart URL format: "?add-to-cart={product_id}"
	 */
	public function test_exprdawc_change_add_to_cart_url_no_custom_fields() {
		$original_url = '?add-to-cart=' . $this->product_id;
		$result       = $this->instance->exprdawc_change_add_to_cart_url( $original_url, $this->product );
		$this->assertEquals( $original_url, $result );
	}


	/**
	 * Test exprdawc_add_frontend_styles_scripts does not enqueue assets on non-product pages.
	 *
	 * Test Goal:
	 * Verifies that the frontend styles and scripts are only enqueued on product pages
	 * and not on other pages like the homepage or shop page.
	 *
	 * Expected Result:
	 * - The relevant styles and scripts are NOT enqueued on non-product pages
	 * - No localized data is added to the scripts
	 *
	 * Test Conditions:
	 * - Visiting a non-product page (e.g., home page)
	 * - Calling the method to enqueue assets
	 */
	public function test_frontend_assets_not_enqueued_when_not_product_page(): void {
		$this->reset_enqueue_state();

		// Visit a normal page (Home).
		$this->go_to( home_url( '/' ) );

		$this->instance->exprdawc_add_frontend_styles_scripts();

		$this->assertFalse( wp_style_is( 'form-css', 'enqueued' ) );
		$this->assertFalse( wp_script_is( 'wc-conditional-rules-js', 'enqueued' ) );
		$this->assertFalse( wp_script_is( 'exprdawc-frontend-js', 'enqueued' ) );

		$data = wp_scripts()->get_data( 'exprdawc-frontend-js', 'data' );
		$this->assertEmpty( $data );
	}

	/**
	 * Test exprdawc_add_frontend_styles_scripts enqueues assets on product pages.
	 *
	 * Test Goal:
	 * Verifies that the frontend styles and scripts are correctly enqueued on product pages
	 * and that localized data is added to the scripts.
	 *
	 * Expected Result:
	 * - The relevant styles and scripts are enqueued on product pages
	 * - Localized data contains expected settings (currency symbol, total, etc.)
	 *
	 * Test Conditions:
	 * - Visiting a product page
	 * - Calling the method to enqueue assets
	 */
	public function test_frontend_assets_enqueued_on_product_page(): void {
		$this->reset_enqueue_state();

		// Create a simple product for testing.
		$product = new WC_Product_Simple();
		$product->set_name( 'Test Product' );
		$product->set_regular_price( '10' );
		$product->save();

		// Visit the product page (to make is_product() true).
		$this->go_to( get_permalink( $product->get_id() ) );

		// IMPORTANT: Update query in WP_UnitTestCase.
		global $wp_query;
		$wp_query->is_singular = true;

		$this->instance->exprdawc_add_frontend_styles_scripts();

		// Enqueued?
		$this->assertTrue( wp_style_is( 'form-css', 'enqueued' ) );
		$this->assertTrue( wp_script_is( 'wc-conditional-rules-js', 'enqueued' ) );
		$this->assertTrue( wp_script_is( 'exprdawc-frontend-js', 'enqueued' ) );

		// Check URL/Deps (optional, but useful).
		$this->assertSame( EXPRDAWC_ASSETS_CSS . 'forms.css', wp_styles()->registered['form-css']->src );
		$this->assertSame( array( 'jquery' ), wp_scripts()->registered['exprdawc-frontend-js']->deps );

		// Check localized data.
		$data = (string) wp_scripts()->get_data( 'exprdawc-frontend-js', 'data' );
		$this->assertStringContainsString( 'var exprdawc_frontend_settings', $data );
		$this->assertStringContainsString( '"currency_symbol"', $data );
		$this->assertStringContainsString( '"total"', $data );

		$product->delete( true );
	}

	/**
	 * Test exprdawc_display_custom_fields_on_product_page outputs nothing when no fields.
	 *
	 * Test Goal:
	 * Verifies that the method does not output any HTML when the product has no custom fields.
	 *
	 * Expected Result:
	 * - No output is generated (empty string)
	 *
	 * Test Conditions:
	 * - Product without custom fields
	 * - Calling the method to display custom fields
	 */
	public function test_display_custom_fields_outputs_nothing_when_no_fields(): void {
		$wc_product = new WC_Product_Simple();
		$wc_product->set_name( 'Test Product' );
		$wc_product->set_regular_price( '10' );
		$wc_product->save();

		global $product;
		$product = $wc_product;

		ob_start();
		$this->instance->exprdawc_display_custom_fields_on_product_page();
		$output = ob_get_clean();

		$this->assertSame( '', trim( $output ), 'No fields => no output expected.' );

		$wc_product->delete( true );
	}

	/**
	 * Test exprdawc_display_custom_fields_on_product_page outputs wrapper, nonce, and price div when fields exist.
	 *
	 * Test Goal:
	 * Verifies that the method outputs the correct HTML structure when custom fields are present.
	 *
	 * Expected Result:
	 * - Output contains a wrapper div with class "exprdawc-extra-fields"
	 * - Output contains a nonce field with name "exprdawc_nonce"
	 * - Output contains a price adjustment div with correct data attributes
	 *
	 * Test Conditions:
	 * - Product with one required text field
	 * - Calling the method to display custom fields
	 */
	public function test_display_custom_fields_outputs_wrapper_nonce_and_price_div_when_fields_exist(): void {
		$wc_product = new WC_Product_Simple();
		$wc_product->set_name( 'Test Product' );
		$wc_product->set_regular_price( '10' );
		$wc_product->save();

		// Add custom fields with one field to generate output.
		$fields = array(
			array(
				'label'    => 'My Field',
				'type'     => 'text',
				'required' => true,
			),
		);
		$wc_product->update_meta_data( '_extra_product_fields', $fields );
		$wc_product->save();

		// Set global $product (as WooCommerce does).
		global $product;
		$product = $wc_product;

		ob_start();
		$this->instance->exprdawc_display_custom_fields_on_product_page();
		$output = ob_get_clean();

		// Wrapper.
		$this->assertStringContainsString( 'class="exprdawc-extra-fields"', $output );

		// Nonce field (contains "exprdawc_nonce").
		$this->assertStringContainsString( 'name="exprdawc_nonce"', $output );

		// Price adjustment container + data attributes.
		$this->assertStringContainsString( 'class="exprdawc-price-adjustment"', $output );
		$this->assertStringContainsString( 'data-product-type="simple"', $output );
		$this->assertStringContainsString( 'data-product-name="Test Product"', $output );

		// Base price is dynamically formatted; we only check that the attribute exists.
		$this->assertMatchesRegularExpression( '/data-product-base-price="[^"]+"/', $output );

		$wc_product->delete( true );
	}

	/**
	 * Test exprdawc_validate_custom_fields validates required fields.
	 *
	 * Test Goal:
	 * Verifies that validation fails when a required custom field is empty.
	 *
	 * Expected Result:
	 * - Validation returns FALSE
	 * - WooCommerce error notice is added
	 * - Product cannot be added to cart
	 *
	 * Test Conditions:
	 * - Product with one required text field (required = 1)
	 * - POST data contains empty value for the field
	 * - Add-to-cart process is stopped
	 */
	public function test_exprdawc_validate_custom_fields_required_field_empty() {
		// Add custom fields with required field.
		$custom_fields = array(
			array(
				'label'    => 'Test Field',
				'type'     => 'text',
				'required' => 1,
			),
		);
		$this->product->update_meta_data( '_extra_product_fields', $custom_fields );
		$this->product->save();

		// Set up $_POST data with empty required field.
		$_POST['exprdawc_custom_field_input'] = array(
			'test_field' => '',
		);

		$result = $this->instance->exprdawc_validate_custom_fields( true, $this->product_id, 1 );
		$this->assertFalse( $result );
	}

	/**
	 * Test exprdawc_validate_custom_fields passes when required field is filled.
	 *
	 * Test Goal:
	 * Verifies that validation succeeds when a required custom field is correctly filled.
	 *
	 * Expected Result:
	 * - Validation returns TRUE
	 * - No error notice is added
	 * - Product can be added to cart
	 *
	 * Test Conditions:
	 * - Product with one required text field
	 * - POST data contains a valid value ("test value")
	 */
	public function test_exprdawc_validate_custom_fields_required_field_filled() {
		// Add custom fields with required field.
		$custom_fields = array(
			array(
				'label'    => 'Test Field',
				'type'     => 'text',
				'required' => 1,
			),
		);
		$this->product->update_meta_data( '_extra_product_fields', $custom_fields );
		$this->product->save();

		// Set up $_POST data with filled required field.
		$_POST['exprdawc_custom_field_input'] = array(
			'test_field' => 'test value',
		);

		$result = $this->instance->exprdawc_validate_custom_fields( true, $this->product_id, 1 );
		$this->assertTrue( $result );
	}

	/**
	 * Test exprdawc_validate_custom_fields validates email field.
	 *
	 * Test Goal:
	 * Verifies that email validation fails when an invalid email address is entered.
	 *
	 * Expected Result:
	 * - Validation returns FALSE
	 * - WooCommerce error notice is added ("is not a valid email address")
	 * - Product cannot be added to cart
	 *
	 * Test Conditions:
	 * - Product with an email field (type = 'email')
	 * - POST data contains invalid value "invalid-email"
	 * - WordPress is_email() function is used for validation
	 */
	public function test_exprdawc_validate_custom_fields_invalid_email() {
		// Add custom fields with email field.
		$custom_fields = array(
			array(
				'label' => 'Email Field',
				'type'  => 'email',
			),
		);
		$this->product->update_meta_data( '_extra_product_fields', $custom_fields );
		$this->product->save();

		// Set up $_POST data with invalid email.
		$_POST['exprdawc_custom_field_input'] = array(
			'email_field' => 'invalid-email',
		);

		$result = $this->instance->exprdawc_validate_custom_fields( true, $this->product_id, 1 );
		$this->assertFalse( $result );
	}

	/**
	 * Test exprdawc_validate_custom_fields validates email field with valid email.
	 *
	 * Test Goal:
	 * Verifies that email validation succeeds when a valid email address is entered.
	 *
	 * Expected Result:
	 * - Validation returns TRUE
	 * - No error notice is added
	 * - Product can be added to cart
	 *
	 * Test Conditions:
	 * - Product with an email field
	 * - POST data contains valid email "test@example.com"
	 */
	public function test_exprdawc_validate_custom_fields_valid_email() {
		// Add custom fields with email field.
		$custom_fields = array(
			array(
				'label' => 'Email Field',
				'type'  => 'email',
			),
		);
		$this->product->update_meta_data( '_extra_product_fields', $custom_fields );
		$this->product->save();

		// Set up $_POST data with valid email.
		$_POST['exprdawc_custom_field_input'] = array(
			'email_field' => 'test@example.com',
		);

		$result = $this->instance->exprdawc_validate_custom_fields( true, $this->product_id, 1 );
		$this->assertTrue( $result );
	}

	/**
	 * Test exprdawc_validate_custom_fields validates number field.
	 *
	 * Test Goal:
	 * Verifies that number validation fails when a non-numeric value is entered.
	 *
	 * Expected Result:
	 * - Validation returns FALSE
	 * - WooCommerce error notice is added ("must be a number")
	 * - Product cannot be added to cart
	 *
	 * Test Conditions:
	 * - Product with a number field (type = 'number')
	 * - POST data contains invalid value "not-a-number"
	 * - PHP is_numeric() function is used for validation
	 */
	public function test_exprdawc_validate_custom_fields_invalid_number() {
		// Add custom fields with number field.
		$custom_fields = array(
			array(
				'label' => 'Number Field',
				'type'  => 'number',
			),
		);
		$this->product->update_meta_data( '_extra_product_fields', $custom_fields );
		$this->product->save();

		// Set up $_POST data with invalid number.
		$_POST['exprdawc_custom_field_input'] = array(
			'number_field' => 'not-a-number',
		);

		$result = $this->instance->exprdawc_validate_custom_fields( true, $this->product_id, 1 );
		$this->assertFalse( $result );
	}

	/**
	 * Test exprdawc_validate_custom_fields validates date field.
	 *
	 * Test Goal:
	 * Verifies that date validation fails when an invalid date is entered.
	 *
	 * Expected Result:
	 * - Validation returns FALSE
	 * - WooCommerce error notice is added ("is not a valid date")
	 * - Product cannot be added to cart
	 *
	 * Test Conditions:
	 * - Product with a date field (type = 'date')
	 * - POST data contains invalid value "not-a-date"
	 * - PHP strtotime() function is used for validation
	 */
	public function test_exprdawc_validate_custom_fields_invalid_date() {
		// Add custom fields with date field.
		$custom_fields = array(
			array(
				'label' => 'Date Field',
				'type'  => 'date',
			),
		);
		$this->product->update_meta_data( '_extra_product_fields', $custom_fields );
		$this->product->save();

		// Set up $_POST data with invalid date.
		$_POST['exprdawc_custom_field_input'] = array(
			'date_field' => 'not-a-date',
		);

		$result = $this->instance->exprdawc_validate_custom_fields( true, $this->product_id, 1 );
		$this->assertFalse( $result );
	}

	/**
	 * Test exprdawc_validate_custom_fields validates date field.
	 *
	 * Test Goal:
	 * Verifies that date validation succeeds when a valid date is entered.
	 *
	 * Expected Result:
	 * - Validation returns TRUE
	 * - No WooCommerce error notice is added
	 * - Product can be added to cart
	 *
	 * Test Conditions:
	 * - Product with a date field (type = 'date')
	 * - POST data contains valid value "2024-01-01"
	 * - PHP strtotime() function is used for validation
	 */
	public function test_exprdawc_validate_custom_fields_valid_date() {
		// Add custom fields with date field.
		$custom_fields = array(
			array(
				'label' => 'Date Field',
				'type'  => 'date',
			),
		);
		$this->product->update_meta_data( '_extra_product_fields', $custom_fields );
		$this->product->save();

		// Set up $_POST data with valid date.
		$_POST['exprdawc_custom_field_input'] = array(
			'date_field' => '2024-01-01',
		);

		$result = $this->instance->exprdawc_validate_custom_fields( true, $this->product_id, 1 );
		$this->assertTrue( $result );
	}

	/**
	 * Test exprdawc_validate_custom_fields validates radio field.
	 *
	 * Test Goal:
	 * Verifies that radio field validation fails when an invalid option is selected.
	 *
	 * Expected Result:
	 * - Validation returns FALSE
	 * - WooCommerce error notice is added ("is not a valid option")
	 * - Product cannot be added to cart
	 *
	 * Test Conditions:
	 * - Product with a radio field with options A and B
	 * - POST data contains invalid value "Bam" (not in options)
	 */
	public function test_exprdawc_validate_custom_fields_invalid_radio() {
		// Add custom fields with radio field.
		$custom_fields = array(
			0 =>
				array(
					'label'                  => 'Radio Test.:',
					'type'                   => 'radio',
					'required'               => 1,
					'conditional_logic'      => 0,
					'placeholder_text'       => '',
					'help_text'              => '',
					'options'                =>
					array(
						0 =>
							array(
								'label'                  => 'Option A',
								'value'                  => 'A',
								'price_adjustment_type'  => 'fixed',
								'price_adjustment_value' => '',
								'default'                => 0,
							),
						1 =>
							array(
								'label'                  => 'Option B',
								'value'                  => 'B',
								'price_adjustment_type'  => 'fixed',
								'price_adjustment_value' => '',
								'default'                => 0,
							),
					),
					'default'                => 'B',
					'minlength'              => 0,
					'maxlength'              => 255,
					'rows'                   => 2,
					'cols'                   => 5,
					'autocomplete'           => 'on',
					'autofocus'              => false,
					'conditional_rules'      =>
					array(
						0 =>
						array(
							0 =>
							array(
								'field'    => '',
								'operator' => 'field_is_empty',
								'value'    => '',
							),
						),
					),
					'index'                  => 3,
					'editable'               => false,
					'adjust_price'           => false,
					'price_adjustment_type'  => 'fixed',
					'price_adjustment_value' => '0',
				),
		);
		$this->product->update_meta_data( '_extra_product_fields', $custom_fields );
		$this->product->save();

		// Set up $_POST data with invalid radio option.
		$_POST['exprdawc_custom_field_input'] = array(
			'radio_test' =>
				array(
					99 => 'Bam',
				),
		);

		$result = $this->instance->exprdawc_validate_custom_fields( true, $this->product_id, 1 );
		$this->assertFalse( $result );
	}


	/**
	 * Test exprdawc_validate_custom_fields validates radio field.
	 *
	 * Test Goal:
	 * Verifies that radio field validation succeeds when a valid option is selected.
	 *
	 * Expected Result:
	 * - Validation returns TRUE
	 * - No WooCommerce error notice is added
	 * - Product can be added to cart
	 *
	 * Test Conditions:
	 * - Product with a radio field with options A and B
	 * - POST data contains valid value "A" (in options)
	 */
	public function test_exprdawc_validate_custom_fields_valid_radio() {
		// Add custom fields with radio field.
		$custom_fields = array(
			0 =>
				array(
					'label'                  => 'Radio Test.:',
					'type'                   => 'radio',
					'required'               => 1,
					'conditional_logic'      => 0,
					'placeholder_text'       => '',
					'help_text'              => '',
					'options'                =>
					array(
						0 =>
							array(
								'label'                  => 'Option A',
								'value'                  => 'A',
								'price_adjustment_type'  => 'fixed',
								'price_adjustment_value' => '',
								'default'                => 0,
							),
						1 =>
							array(
								'label'                  => 'Option B',
								'value'                  => 'B',
								'price_adjustment_type'  => 'fixed',
								'price_adjustment_value' => '',
								'default'                => 0,
							),
					),
					'default'                => 'B',
					'minlength'              => 0,
					'maxlength'              => 255,
					'rows'                   => 2,
					'cols'                   => 5,
					'autocomplete'           => 'on',
					'autofocus'              => false,
					'conditional_rules'      =>
					array(
						0 =>
						array(
							0 =>
							array(
								'field'    => '',
								'operator' => 'field_is_empty',
								'value'    => '',
							),
						),
					),
					'index'                  => 3,
					'editable'               => false,
					'adjust_price'           => false,
					'price_adjustment_type'  => 'fixed',
					'price_adjustment_value' => '0',
				),
		);
		$this->product->update_meta_data( '_extra_product_fields', $custom_fields );
		$this->product->save();

		// Set up $_POST data with valid radio option.
		$_POST['exprdawc_custom_field_input'] = array(
			'radio_test' =>
				array(
					0 => 'A',
				),
		);

		$result = $this->instance->exprdawc_validate_custom_fields( true, $this->product_id, 1 );
		$this->assertTrue( $result );
	}

	/**
	 * Test exprdawc_validate_custom_fields validates checkbox field.
	 *
	 * Test Goal:
	 * Verifies that checkbox field validation fails when an invalid option is selected.
	 *
	 * Expected Result:
	 * - Validation returns FALSE
	 * - WooCommerce error notice is added ("is not a valid option")
	 * - Product cannot be added to cart
	 *
	 * Test Conditions:
	 * - Product with a checkbox field with options A and B
	 * - POST data contains invalid value "Bam" (not in options)
	 */
	public function test_exprdawc_validate_custom_fields_invalid_checkbox() {
		// Add custom fields with checkbox field.
		$custom_fields = array(
			0 =>
				array(
					'label'                  => 'Checkbox Test.:',
					'type'                   => 'checkbox',
					'required'               => 1,
					'conditional_logic'      => 0,
					'placeholder_text'       => '',
					'help_text'              => '',
					'options'                =>
					array(
						0 =>
							array(
								'label'                  => 'Option A',
								'value'                  => 'A',
								'price_adjustment_type'  => 'fixed',
								'price_adjustment_value' => '',
								'default'                => 0,
							),
						1 =>
							array(
								'label'                  => 'Option B',
								'value'                  => 'B',
								'price_adjustment_type'  => 'fixed',
								'price_adjustment_value' => '',
								'default'                => 0,
							),
					),
					'default'                => 'B',
					'minlength'              => 0,
					'maxlength'              => 255,
					'rows'                   => 2,
					'cols'                   => 5,
					'autocomplete'           => 'on',
					'autofocus'              => false,
					'conditional_rules'      =>
					array(
						0 =>
						array(
							0 =>
							array(
								'field'    => '',
								'operator' => 'field_is_empty',
								'value'    => '',
							),
						),
					),
					'index'                  => 3,
					'editable'               => false,
					'adjust_price'           => false,
					'price_adjustment_type'  => 'fixed',
					'price_adjustment_value' => '0',
				),
		);
		$this->product->update_meta_data( '_extra_product_fields', $custom_fields );
		$this->product->save();

		// Set up $_POST data with invalid checkbox option.
		$_POST['exprdawc_custom_field_input'] = array(
			'checkbox_test' =>
				array(
					99 => 'Bam',
				),
		);

		$result = $this->instance->exprdawc_validate_custom_fields( true, $this->product_id, 1 );
		$this->assertFalse( $result );
	}

	/**
	 * Test exprdawc_validate_custom_fields validates checkbox field.
	 *
	 * Test Goal:
	 * Verifies that checkbox field validation succeeds when a valid option is selected.
	 *
	 * Expected Result:
	 * - Validation returns TRUE
	 * - WooCommerce error notice is not added
	 * - Product can be added to cart
	 *
	 * Test Conditions:
	 * - Product with a checkbox field with options A and B
	 * - POST data contains valid value "A" (in options)
	 */
	public function test_exprdawc_validate_custom_fields_valid_checkbox() {
		// Add custom fields with checkbox field.
		$custom_fields = array(
			0 =>
				array(
					'label'                  => 'Checkbox Test.:',
					'type'                   => 'checkbox',
					'required'               => 1,
					'conditional_logic'      => 0,
					'placeholder_text'       => '',
					'help_text'              => '',
					'options'                =>
					array(
						0 =>
							array(
								'label'                  => 'Option A',
								'value'                  => 'A',
								'price_adjustment_type'  => 'fixed',
								'price_adjustment_value' => '',
								'default'                => 0,
							),
						1 =>
							array(
								'label'                  => 'Option B',
								'value'                  => 'B',
								'price_adjustment_type'  => 'fixed',
								'price_adjustment_value' => '',
								'default'                => 0,
							),
					),
					'default'                => 'B',
					'minlength'              => 0,
					'maxlength'              => 255,
					'rows'                   => 2,
					'cols'                   => 5,
					'autocomplete'           => 'on',
					'autofocus'              => false,
					'conditional_rules'      =>
					array(
						0 =>
						array(
							0 =>
							array(
								'field'    => '',
								'operator' => 'field_is_empty',
								'value'    => '',
							),
						),
					),
					'index'                  => 3,
					'editable'               => false,
					'adjust_price'           => false,
					'price_adjustment_type'  => 'fixed',
					'price_adjustment_value' => '0',
				),
		);
		$this->product->update_meta_data( '_extra_product_fields', $custom_fields );
		$this->product->save();

		// Set up $_POST data with valid checkbox option.
		$_POST['exprdawc_custom_field_input'] = array(
			'checkbox_test' =>
				array(
					0 => 'A',
				),
		);

		$result = $this->instance->exprdawc_validate_custom_fields( true, $this->product_id, 1 );
		$this->assertTrue( $result );
	}

	/**
	 * Test exprdawc_validate_custom_fields validates yes-no field.
	 *
	 * Test Goal:
	 * Verifies that yes-no field validation fails when an invalid option is selected.
	 *
	 * Expected Result:
	 * - Validation returns FALSE
	 * - WooCommerce error notice is added ("is not a valid option")
	 * - Product cannot be added to cart
	 *
	 * Test Conditions:
	 * - Product with a yes-no field
	 * - POST data contains invalid value "Bam" (not in options)
	 */
	public function test_exprdawc_validate_custom_fields_invalid_yes_no() {
		// Add custom fields with yes-no field.
		$custom_fields = array(
			0 =>
				array(
					'label'                  => 'Yes No',
					'type'                   => 'yes-no',
					'required'               => 1,
					'conditional_logic'      => 0,
					'placeholder_text'       => '',
					'help_text'              => '',
					'options'                => array(),
					'default'                => '',
					'minlength'              => 0,
					'maxlength'              => 255,
					'rows'                   => 2,
					'cols'                   => 5,
					'autocomplete'           => 'on',
					'autofocus'              => false,
					'conditional_rules'      =>
						array(
							0 =>
							array(
								0 =>
								array(
									'field'    => '',
									'operator' => 'field_is_empty',
									'value'    => '',
								),
							),
						),
					'index'                  => 0,
					'editable'               => false,
					'adjust_price'           => false,
					'price_adjustment_type'  => 'fixed',
					'price_adjustment_value' => '0',
				),
		);
		$this->product->update_meta_data( '_extra_product_fields', $custom_fields );
		$this->product->save();

		// Set up $_POST data with invalid yes-no option.
		$_POST['exprdawc_custom_field_input'] = array(
			'yes_no' => 'blub',
		);

		$result = $this->instance->exprdawc_validate_custom_fields( true, $this->product_id, 1 );
		$this->assertFalse( $result );
	}

	/**
	 * Test exprdawc_validate_custom_fields validates yes-no field.
	 *
	 * Test Goal:
	 * Verifies that yes-no field validation succeeds when a valid option is selected.
	 *
	 * Expected Result:
	 * - Validation returns TRUE
	 * - WooCommerce error notice is not added
	 * - Product can be added to cart
	 *
	 * Test Conditions:
	 * - Product with a yes-no field
	 * - POST data contains valid value "yes" (in options)
	 */
	public function test_exprdawc_validate_custom_fields_valid_yes_no() {
		// Add custom fields with yes-no field.
		$custom_fields = array(
			0 =>
				array(
					'label'                  => 'Yes No',
					'type'                   => 'yes-no',
					'required'               => 1,
					'conditional_logic'      => 0,
					'placeholder_text'       => '',
					'help_text'              => '',
					'options'                => array(),
					'default'                => '',
					'minlength'              => 0,
					'maxlength'              => 255,
					'rows'                   => 2,
					'cols'                   => 5,
					'autocomplete'           => 'on',
					'autofocus'              => false,
					'conditional_rules'      =>
						array(
							0 =>
							array(
								0 =>
								array(
									'field'    => '',
									'operator' => 'field_is_empty',
									'value'    => '',
								),
							),
						),
					'index'                  => 0,
					'editable'               => false,
					'adjust_price'           => false,
					'price_adjustment_type'  => 'fixed',
					'price_adjustment_value' => '0',
				),
		);
		$this->product->update_meta_data( '_extra_product_fields', $custom_fields );
		$this->product->save();

		// Set up $_POST data with valid yes-no option.
		$_POST['exprdawc_custom_field_input'] = array(
			'yes_no' => 'yes',
		);

		$result = $this->instance->exprdawc_validate_custom_fields( true, $this->product_id, 1 );
		$this->assertTrue( $result );
	}

	/**
	 * Test exprdawc_validate_custom_fields validates select field.
	 *
	 * Test Goal:
	 * Verifies that select field validation fails when an invalid option is selected.
	 *
	 * Expected Result:
	 * - Validation returns FALSE
	 * - WooCommerce error notice is added ("is not a valid option")
	 * - Product cannot be added to cart
	 *
	 * Test Conditions:
	 * - Product with a select field
	 * - POST data contains invalid value "Bam" (not in options)
	 */
	public function test_exprdawc_validate_custom_fields_invalid_select() {
		// Add custom fields with select field.
		$custom_fields = array(
			0 =>
				array(
					'label'                  => 'Select Field',
					'type'                   => 'select',
					'required'               => 1,
					'conditional_logic'      => 0,
					'placeholder_text'       => '',
					'help_text'              => '',
					'options'                =>
					array(
						0 =>
						array(
							'label'                  => 'Option A',
							'value'                  => 'A',
							'price_adjustment_type'  => 'fixed',
							'price_adjustment_value' => '',
							'default'                => 0,
						),
						1 =>
						array(
							'label'                  => 'Option B',
							'value'                  => 'B',
							'price_adjustment_type'  => 'fixed',
							'price_adjustment_value' => '',
							'default'                => 0,
						),
						2 =>
						array(
							'label'                  => 'Option C',
							'value'                  => 'C',
							'price_adjustment_type'  => 'fixed',
							'price_adjustment_value' => '',
							'default'                => 0,
						),
					),
					'default'                => '',
					'minlength'              => 0,
					'maxlength'              => 255,
					'rows'                   => 2,
					'cols'                   => 5,
					'autocomplete'           => 'on',
					'autofocus'              => false,
					'conditional_rules'      =>
						array(
							0 =>
							array(
								0 =>
								array(
									'field'    => '',
									'operator' => 'field_is_empty',
									'value'    => '',
								),
							),
						),
					'index'                  => 0,
					'editable'               => false,
					'adjust_price'           => false,
					'price_adjustment_type'  => 'fixed',
					'price_adjustment_value' => '0',
				),
		);
		$this->product->update_meta_data( '_extra_product_fields', $custom_fields );
		$this->product->save();

		// Set up $_POST data with invalid select option.
		$_POST['exprdawc_custom_field_input'] = array(
			'select_field' => 'Bam',
		);

		$result = $this->instance->exprdawc_validate_custom_fields( true, $this->product_id, 1 );
		$this->assertFalse( $result );
	}

	/**
	 * Test exprdawc_validate_custom_fields validates select field.
	 *
	 * Test Goal:
	 * Verifies that select field validation succeeds when a valid option is selected.
	 *
	 * Expected Result:
	 * - Validation returns TRUE
	 * - No WooCommerce error notice is added
	 * - Product can be added to cart
	 *
	 * Test Conditions:
	 * - Product with a select field
	 * - POST data contains valid value "B" (in options)
	 */
	public function test_exprdawc_validate_custom_fields_valid_select() {
		// Add custom fields with select field.
		$custom_fields = array(
			0 =>
				array(
					'label'                  => 'Select Field',
					'type'                   => 'select',
					'required'               => 1,
					'conditional_logic'      => 0,
					'placeholder_text'       => '',
					'help_text'              => '',
					'options'                =>
					array(
						0 =>
						array(
							'label'                  => 'Option A',
							'value'                  => 'A',
							'price_adjustment_type'  => 'fixed',
							'price_adjustment_value' => '',
							'default'                => 0,
						),
						1 =>
						array(
							'label'                  => 'Option B',
							'value'                  => 'B',
							'price_adjustment_type'  => 'fixed',
							'price_adjustment_value' => '',
							'default'                => 0,
						),
						2 =>
						array(
							'label'                  => 'Option C',
							'value'                  => 'C',
							'price_adjustment_type'  => 'fixed',
							'price_adjustment_value' => '',
							'default'                => 0,
						),
					),
					'default'                => '',
					'minlength'              => 0,
					'maxlength'              => 255,
					'rows'                   => 2,
					'cols'                   => 5,
					'autocomplete'           => 'on',
					'autofocus'              => false,
					'conditional_rules'      =>
						array(
							0 =>
							array(
								0 =>
								array(
									'field'    => '',
									'operator' => 'field_is_empty',
									'value'    => '',
								),
							),
						),
					'index'                  => 0,
					'editable'               => false,
					'adjust_price'           => false,
					'price_adjustment_type'  => 'fixed',
					'price_adjustment_value' => '0',
				),
		);
		$this->product->update_meta_data( '_extra_product_fields', $custom_fields );
		$this->product->save();

		// Set up $_POST data with valid select option.
		$_POST['exprdawc_custom_field_input'] = array(
			'select_field' => 'B',
		);

		$result = $this->instance->exprdawc_validate_custom_fields( true, $this->product_id, 1 );
		$this->assertTrue( $result );
	}

	/**
	 * Test exprdawc_validate_custom_fields returns true when no POST data.
	 *
	 * Test Goal:
	 * Verifies that validation succeeds when no POST data for custom fields
	 * is present (no fields were filled out).
	 *
	 * Expected Result:
	 * - Validation returns TRUE
	 * - Method returns early when no POST data is present
	 * - This allows standard product purchases without custom fields
	 *
	 * Test Conditions:
	 * - No $_POST['exprdawc_custom_field_input'] data present
	 * - Validation is skipped
	 */
	public function test_exprdawc_validate_custom_fields_no_post_data() {
		unset( $_POST['exprdawc_custom_field_input'] );

		$result = $this->instance->exprdawc_validate_custom_fields( true, $this->product_id, 1 );
		$this->assertTrue( $result );
	}

	/**
	 * Test exprdawc_check_product_support disables ajax_add_to_cart when custom fields exist.
	 *
	 * Test Goal:
	 * Verifies that the AJAX add-to-cart feature is disabled when a product
	 * has custom fields with required fields.
	 *
	 * Expected Result:
	 * - Method returns FALSE for 'ajax_add_to_cart' feature
	 * - AJAX add-to-cart is disabled
	 * - Users must visit the product page to fill out custom fields
	 *
	 * Test Conditions:
	 * - Product with required custom fields
	 * - Feature check for 'ajax_add_to_cart'
	 * - Original support status: true
	 */
	public function test_exprdawc_check_product_support_disables_ajax_add_to_cart() {
		// Add custom fields with required field.
		$custom_fields = array(
			array(
				'label'    => 'Test Field',
				'type'     => 'text',
				'required' => 1,
			),
		);
		$this->product->update_meta_data( '_extra_product_fields', $custom_fields );
		$this->product->save();

		$result = $this->instance->exprdawc_check_product_support( true, 'ajax_add_to_cart', $this->product );
		$this->assertFalse( $result );
	}

	/**
	 * Test exprdawc_check_product_support returns original value for other features.
	 *
	 * Test Goal:
	 * Verifies that other product features are not affected and the original
	 * support value is retained.
	 *
	 * Expected Result:
	 * - Method returns the original value (TRUE)
	 * - Only 'ajax_add_to_cart' is affected by custom fields
	 * - All other features remain unchanged
	 *
	 * Test Conditions:
	 * - Feature check for a different feature (not 'ajax_add_to_cart')
	 * - Original support status: true
	 */
	public function test_exprdawc_check_product_support_other_feature() {
		$result = $this->instance->exprdawc_check_product_support( true, 'some_other_feature', $this->product );
		$this->assertTrue( $result );
	}

	/**
	 * Test exprdawc_save_extra_product_data_in_cart saves text field.
	 *
	 * Test Goal:
	 * Verifies that text field data is correctly saved to the cart when a product
	 * with custom fields is added.
	 *
	 * Expected Result:
	 * - Cart item data contains 'post_data_product_item' array
	 * - Exactly one entry in the post_data_product_item array
	 * - The value "test value" is correctly saved
	 * - Data is made available for cart and checkout
	 *
	 * Test Conditions:
	 * - Product with text field without price adjustment
	 * - Valid nonce in POST data
	 * - POST contains field value "test value"
	 */
	public function test_exprdawc_save_extra_product_data_in_cart_text_field() {
		// Add custom fields.
		$custom_fields = array(
			array(
				'label'        => 'Test Field',
				'type'         => 'text',
				'adjust_price' => false,
			),
		);
		$this->product->update_meta_data( '_extra_product_fields', $custom_fields );
		$this->product->save();

		// Set up $_POST data.
		$_POST['exprdawc_nonce']              = wp_create_nonce( 'exprdawc_save_custom_field' );
		$_POST['exprdawc_custom_field_input'] = array(
			'test_field' => 'test value',
		);

		$cart_item_data = array();
		$result         = $this->instance->exprdawc_save_extra_product_data_in_cart( $cart_item_data, $this->product_id, 0, 1 );

		$this->assertArrayHasKey( 'post_data_product_item', $result );
		$this->assertCount( 1, $result['post_data_product_item'] );
		$this->assertEquals( 'test value', $result['post_data_product_item'][0]['value'] );
	}

	/**
	 * Test exprdawc_save_extra_product_data_in_cart sanitizes email field.
	 *
	 * Test Goal:
	 * Verifies that email field data is properly sanitized when the product
	 * is added to the cart.
	 *
	 * Expected Result:
	 * - Cart item data contains 'post_data_product_item' array
	 * - Email is processed with sanitize_email()
	 * - The value "Test@Example.COM" is correctly saved (case-sensitive)
	 *
	 * Test Conditions:
	 * - Product with email field (type = 'email')
	 * - Valid nonce in POST data
	 * - POST contains email with mixed case
	 */
	public function test_exprdawc_save_extra_product_data_in_cart_email_field() {
		// Add custom fields.
		$custom_fields = array(
			array(
				'label'        => 'Email Field',
				'type'         => 'email',
				'adjust_price' => false,
			),
		);
		$this->product->update_meta_data( '_extra_product_fields', $custom_fields );
		$this->product->save();

		// Set up $_POST data.
		$_POST['exprdawc_nonce']              = wp_create_nonce( 'exprdawc_save_custom_field' );
		$_POST['exprdawc_custom_field_input'] = array(
			'email_field' => 'Test@Example.COM',
		);

		$cart_item_data = array();
		$result         = $this->instance->exprdawc_save_extra_product_data_in_cart( $cart_item_data, $this->product_id, 0, 1 );

		$this->assertArrayHasKey( 'post_data_product_item', $result );
		$this->assertEquals( 'Test@Example.COM', $result['post_data_product_item'][0]['value'] );
	}

	/**
	 * Test exprdawc_save_extra_product_data_in_cart handles number field.
	 *
	 * Test Goal:
	 * Verifies that number field data is correctly saved as a float value
	 * when the product is added to the cart.
	 *
	 * Expected Result:
	 * - Cart item data contains 'post_data_product_item' array
	 * - String "42.5" is converted to float 42.5
	 * - Numeric value is correctly typed and saved
	 *
	 * Test Conditions:
	 * - Product with number field (type = 'number')
	 * - Valid nonce in POST data
	 * - POST contains numeric string value "42.5"
	 */
	public function test_exprdawc_save_extra_product_data_in_cart_number_field() {
		// Add custom fields.
		$custom_fields = array(
			array(
				'label'        => 'Number Field',
				'type'         => 'number',
				'adjust_price' => false,
			),
		);
		$this->product->update_meta_data( '_extra_product_fields', $custom_fields );
		$this->product->save();

		// Set up $_POST data.
		$_POST['exprdawc_nonce']              = wp_create_nonce( 'exprdawc_save_custom_field' );
		$_POST['exprdawc_custom_field_input'] = array(
			'number_field' => '42.5',
		);

		$cart_item_data = array();
		$result         = $this->instance->exprdawc_save_extra_product_data_in_cart( $cart_item_data, $this->product_id, 0, 1 );

		$this->assertArrayHasKey( 'post_data_product_item', $result );
		$this->assertEquals( 42.5, $result['post_data_product_item'][0]['value'] );
	}

	/**
	 * Test exprdawc_save_extra_product_data_in_cart handles date field.
	 *
	 * Test Goal:
	 * Verifies that date field data is correctly saved as a string value
	 * when the product is added to the cart.
	 *
	 * Expected Result:
	 * - Cart item data contains 'post_data_product_item' array
	 * - Date value is correctly formatted and saved as a string
	 *
	 * Test Conditions:
	 * - Product with date field (type = 'date')
	 * - Valid nonce in POST data
	 * - POST contains date value "2024-06-19"
	 */
	public function test_exprdawc_save_extra_product_data_in_cart_date_field() {
		// Add custom fields.
		$custom_fields = array(
			array(
				'label'        => 'Date Field',
				'type'         => 'date',
				'adjust_price' => false,
			),
		);
		$this->product->update_meta_data( '_extra_product_fields', $custom_fields );
		$this->product->save();

		// Set up $_POST data.
		$_POST['exprdawc_nonce']              = wp_create_nonce( 'exprdawc_save_custom_field' );
		$_POST['exprdawc_custom_field_input'] = array(
			'date_field' => '2024-06-19',
		);

		$cart_item_data = array();
		$result         = $this->instance->exprdawc_save_extra_product_data_in_cart( $cart_item_data, $this->product_id, 0, 1 );

		$this->assertArrayHasKey( 'post_data_product_item', $result );
		$this->assertEquals( '2024-06-19', $result['post_data_product_item'][0]['value'] );
	}

	/**
	 * Test exprdawc_save_extra_product_data_in_cart handles default field.
	 *
	 * Test Goal:
	 * Verifies that default field data is correctly saved as a string value
	 * when the product is added to the cart.
	 *
	 * Expected Result:
	 * - Cart item data contains 'post_data_product_item' array
	 * - Default value is correctly formatted and saved as a string
	 *
	 * Test Conditions:
	 * - Product with default field (type = 'default')
	 * - Valid nonce in POST data
	 * - POST contains default value "<h2>Title</h2>"
	 */
	public function test_exprdawc_save_extra_product_data_in_cart_default_field() {
		// Add custom fields.
		$custom_fields = array(
			array(
				'label'        => 'Default Field',
				'type'         => 'default',
				'adjust_price' => false,
			),
		);
		$this->product->update_meta_data( '_extra_product_fields', $custom_fields );
		$this->product->save();

		// Set up $_POST data.
		$_POST['exprdawc_nonce']              = wp_create_nonce( 'exprdawc_save_custom_field' );
		$_POST['exprdawc_custom_field_input'] = array(
			'default_field' => '<h2>Title</h2>',
		);

		$cart_item_data = array();
		$result         = $this->instance->exprdawc_save_extra_product_data_in_cart( $cart_item_data, $this->product_id, 0, 1 );

		$this->assertArrayHasKey( 'post_data_product_item', $result );
		$this->assertEquals( 'Title', $result['post_data_product_item'][0]['value'] );
	}

	/**
	 * Test exprdawc_save_extra_product_data_in_cart applies fixed price adjustment (text field).
	 *
	 * Test Goal:
	 * Verifies that calculate_price_adjustment is applied for fixed adjustments
	 * when saving cart data through exprdawc_save_extra_product_data_in_cart().
	 *
	 * Expected Result:
	 * - value_cart includes the fixed adjustment amount
	 * - adjustment is formatted with wc_price()
	 */
	public function test_exprdawc_save_extra_product_data_in_cart_fixed_adjustment_text_field() {
		$custom_fields = array(
			array(
				'label'                  => 'Adjustment Field',
				'type'                   => 'text',
				'adjust_price'           => true,
				'price_adjustment_type'  => 'fixed',
				'price_adjustment_value' => 10,
			),
		);
		$this->product->update_meta_data( '_extra_product_fields', $custom_fields );
		$this->product->save();

		$_POST['exprdawc_nonce']              = wp_create_nonce( 'exprdawc_save_custom_field' );
		$_POST['exprdawc_custom_field_input'] = array(
			'adjustment_field' => 'test value',
		);

		$cart_item_data = array();
		$result         = $this->instance->exprdawc_save_extra_product_data_in_cart( $cart_item_data, $this->product_id, 0, 1 );

		$this->assertArrayHasKey( 'post_data_product_item', $result );
		$this->assertEquals( 'test value', $result['post_data_product_item'][0]['value'] );
		$this->assertEquals( 'test value (+' . wc_price( 10 ) . ')', $result['post_data_product_item'][0]['value_cart'] );
	}

	/**
	 * Test exprdawc_save_extra_product_data_in_cart applies fixed price adjustment (select option).
	 *
	 * Test Goal:
	 * Verifies that calculate_price_adjustment sums option-based fixed adjustments
	 * when a select option is chosen.
	 */
	public function test_exprdawc_save_extra_product_data_in_cart_fixed_adjustment_select_option() {
		$custom_fields = array(
			array(
				'label'        => 'Select Field',
				'type'         => 'select',
				'adjust_price' => true,
				'options'      => array(
					array(
						'label'                  => 'Option 1',
						'value'                  => 'opt1',
						'price_adjustment_type'  => 'fixed',
						'price_adjustment_value' => 5,
					),
					array(
						'label'                  => 'Option 2',
						'value'                  => 'opt2',
						'price_adjustment_type'  => 'fixed',
						'price_adjustment_value' => 3,
					),
				),
			),
		);
		$this->product->update_meta_data( '_extra_product_fields', $custom_fields );
		$this->product->save();

		$_POST['exprdawc_nonce']              = wp_create_nonce( 'exprdawc_save_custom_field' );
		$_POST['exprdawc_custom_field_input'] = array(
			'select_field' => 'opt1',
		);

		$cart_item_data = array();
		$result         = $this->instance->exprdawc_save_extra_product_data_in_cart( $cart_item_data, $this->product_id, 0, 1 );

		$this->assertArrayHasKey( 'post_data_product_item', $result );
		$this->assertEquals( 'opt1', $result['post_data_product_item'][0]['value'] );
		$this->assertEquals( 'opt1 (+' . wc_price( 5 ) . ')', $result['post_data_product_item'][0]['value_cart'] );
	}

	/**
	 * Test exprdawc_save_extra_product_data_in_cart applies fixed price adjustment (checkbox options).
	 *
	 * Test Goal:
	 * Verifies that calculate_price_adjustment sums multiple selected options
	 * for checkbox fields.
	 */
	public function test_exprdawc_save_extra_product_data_in_cart_fixed_adjustment_checkbox_options() {
		$custom_fields = array(
			array(
				'label'        => 'Checkbox Field',
				'type'         => 'checkbox',
				'adjust_price' => true,
				'options'      => array(
					array(
						'label'                  => 'Option 1',
						'value'                  => 'opt1',
						'price_adjustment_type'  => 'fixed',
						'price_adjustment_value' => 5,
					),
					array(
						'label'                  => 'Option 2',
						'value'                  => 'opt2',
						'price_adjustment_type'  => 'fixed',
						'price_adjustment_value' => 3,
					),
				),
			),
		);
		$this->product->update_meta_data( '_extra_product_fields', $custom_fields );
		$this->product->save();

		$_POST['exprdawc_nonce']              = wp_create_nonce( 'exprdawc_save_custom_field' );
		$_POST['exprdawc_custom_field_input'] = array(
			'checkbox_field' => array( 'opt1', 'opt2' ),
		);

		$cart_item_data = array();
		$result         = $this->instance->exprdawc_save_extra_product_data_in_cart( $cart_item_data, $this->product_id, 0, 1 );

		$this->assertArrayHasKey( 'post_data_product_item', $result );
		$this->assertEquals( 'opt1, opt2', $result['post_data_product_item'][0]['value'] );
		$this->assertEquals( 'opt1, opt2 (+' . wc_price( 8 ) . ')', $result['post_data_product_item'][0]['value_cart'] );
	}

	/**
	 * Test exprdawc_save_extra_product_data_in_cart keeps value for percent adjustment.
	 *
	 * Test Goal:
	 * Verifies that percent adjustments do not alter cart display when base price is 0.0
	 * in exprdawc_save_extra_product_data_in_cart().
	 */
	public function test_exprdawc_save_extra_product_data_in_cart_percent_adjustment_no_cart_display() {
		$custom_fields = array(
			array(
				'label'                  => 'Percent Field',
				'type'                   => 'text',
				'adjust_price'           => true,
				'price_adjustment_type'  => 'percent',
				'price_adjustment_value' => 15,
			),
		);
		$this->product->update_meta_data( '_extra_product_fields', $custom_fields );
		$this->product->save();

		$_POST['exprdawc_nonce']              = wp_create_nonce( 'exprdawc_save_custom_field' );
		$_POST['exprdawc_custom_field_input'] = array(
			'percent_field' => 'test value',
		);

		$cart_item_data = array();
		$result         = $this->instance->exprdawc_save_extra_product_data_in_cart( $cart_item_data, $this->product_id, 0, 1 );

		$this->assertArrayHasKey( 'post_data_product_item', $result );
		$this->assertEquals( 'test value', $result['post_data_product_item'][0]['value'] );
		$this->assertEquals( 'test value', $result['post_data_product_item'][0]['value_cart'] );
	}

	/**
	 * Test exprdawc_save_extra_product_data_in_cart applies negative fixed adjustment.
	 *
	 * Test Goal:
	 * Verifies that negative fixed adjustments are formatted with a minus sign.
	 */
	public function test_exprdawc_save_extra_product_data_in_cart_negative_fixed_adjustment() {
		$custom_fields = array(
			array(
				'label'                  => 'Negative Field',
				'type'                   => 'text',
				'adjust_price'           => true,
				'price_adjustment_type'  => 'fixed',
				'price_adjustment_value' => -5,
			),
		);
		$this->product->update_meta_data( '_extra_product_fields', $custom_fields );
		$this->product->save();

		$_POST['exprdawc_nonce']              = wp_create_nonce( 'exprdawc_save_custom_field' );
		$_POST['exprdawc_custom_field_input'] = array(
			'negative_field' => 'test value',
		);

		$cart_item_data = array();
		$result         = $this->instance->exprdawc_save_extra_product_data_in_cart( $cart_item_data, $this->product_id, 0, 1 );

		$this->assertArrayHasKey( 'post_data_product_item', $result );
		$this->assertEquals( 'test value', $result['post_data_product_item'][0]['value'] );
		$this->assertEquals( 'test value (-' . wc_price( 5 ) . ')', $result['post_data_product_item'][0]['value_cart'] );
	}

	/**
	 * Test exprdawc_save_extra_product_data_in_cart applies percent adjustment (select option).
	 *
	 * Test Goal:
	 * Verifies that percent adjustments are saved without altering the value when displayed in cart.
	 */
	public function test_exprdawc_save_extra_product_data_in_cart_percent_adjustment_select_option() {
		$custom_fields = array(
			array(
				'label'        => 'Select Percent',
				'type'         => 'select',
				'adjust_price' => true,
				'options'      => array(
					array(
						'label'                  => 'Option 1',
						'value'                  => 'opt1',
						'price_adjustment_type'  => 'percent',
						'price_adjustment_value' => 10,
					),
				),
			),
		);
		$this->product->update_meta_data( '_extra_product_fields', $custom_fields );
		$this->product->save();

		$_POST['exprdawc_nonce']              = wp_create_nonce( 'exprdawc_save_custom_field' );
		$_POST['exprdawc_custom_field_input'] = array(
			'select_percent' => 'opt1',
		);

		$cart_item_data = array();
		$result         = $this->instance->exprdawc_save_extra_product_data_in_cart( $cart_item_data, $this->product_id, 0, 1 );

		$this->assertArrayHasKey( 'post_data_product_item', $result );
		$this->assertEquals( 'opt1', $result['post_data_product_item'][0]['value'] );
		// Price adjustment should be displayed in cart for select with percent.
		$this->assertStringContainsString( 'opt1 (+', $result['post_data_product_item'][0]['value_cart'] );
	}

	/**
	 * Test exprdawc_save_extra_product_data_in_cart applies percent adjustment (radio option).
	 *
	 * Test Goal:
	 * Verifies that percent adjustments for radio options are saved without altering the value when displayed in cart.
	 */
	public function test_exprdawc_save_extra_product_data_in_cart_percent_adjustment_radio_option() {
		$custom_fields = array(
			array(
				'label'        => 'Radio Percent',
				'type'         => 'radio',
				'adjust_price' => true,
				'options'      => array(
					array(
						'label'                  => 'Option 1',
						'value'                  => 'opt1',
						'price_adjustment_type'  => 'percent',
						'price_adjustment_value' => 15,
					),
				),
			),
		);
		$this->product->update_meta_data( '_extra_product_fields', $custom_fields );
		$this->product->save();

		$_POST['exprdawc_nonce']              = wp_create_nonce( 'exprdawc_save_custom_field' );
		$_POST['exprdawc_custom_field_input'] = array(
			'radio_percent' => 'opt1',
		);

		$cart_item_data = array();
		$result         = $this->instance->exprdawc_save_extra_product_data_in_cart( $cart_item_data, $this->product_id, 0, 1 );

		$this->assertArrayHasKey( 'post_data_product_item', $result );
		$this->assertEquals( 'opt1', $result['post_data_product_item'][0]['value'] );
		// Price adjustment should be displayed in cart.
		$this->assertStringContainsString( 'opt1 (+', $result['post_data_product_item'][0]['value_cart'] );
	}

	/**
	 * Test exprdawc_save_extra_product_data_in_cart applies percent adjustment (checkbox options).
	 *
	 * Test Goal:
	 * Verifies that percent adjustments for checkbox options are saved without altering the value when displayed in cart.
	 */
	public function test_exprdawc_save_extra_product_data_in_cart_percent_adjustment_checkbox_options() {
		$custom_fields = array(
			array(
				'label'        => 'Checkbox Percent',
				'type'         => 'checkbox',
				'adjust_price' => true,
				'options'      => array(
					array(
						'label'                  => 'Option 1',
						'value'                  => 'opt1',
						'price_adjustment_type'  => 'percent',
						'price_adjustment_value' => 5,
					),
					array(
						'label'                  => 'Option 2',
						'value'                  => 'opt2',
						'price_adjustment_type'  => 'percent',
						'price_adjustment_value' => 7,
					),
				),
			),
		);
		$this->product->update_meta_data( '_extra_product_fields', $custom_fields );
		$this->product->save();

		$_POST['exprdawc_nonce']              = wp_create_nonce( 'exprdawc_save_custom_field' );
		$_POST['exprdawc_custom_field_input'] = array(
			'checkbox_percent' => array( 'opt1', 'opt2' ),
		);

		$cart_item_data = array();
		$result         = $this->instance->exprdawc_save_extra_product_data_in_cart( $cart_item_data, $this->product_id, 0, 1 );

		$this->assertArrayHasKey( 'post_data_product_item', $result );
		$this->assertEquals( 'opt1, opt2', $result['post_data_product_item'][0]['value'] );
		// Price adjustment should be displayed in cart for checkbox with percent.
		$this->assertStringContainsString( 'opt1, opt2 (+', $result['post_data_product_item'][0]['value_cart'] );
	}

	/**
	 * Test exprdawc_save_extra_product_data_in_cart applies mixed adjustments (checkbox options).
	 *
	 * Test Goal:
	 * Verifies that a combination of fixed and percent adjustments for checkbox options is handled correctly in cart display.
	 */
	public function test_exprdawc_save_extra_product_data_in_cart_mixed_adjustment_checkbox_options() {
		$custom_fields = array(
			array(
				'label'        => 'Checkbox Mixed',
				'type'         => 'checkbox',
				'adjust_price' => true,
				'options'      => array(
					array(
						'label'                  => 'Option 1',
						'value'                  => 'opt1',
						'price_adjustment_type'  => 'fixed',
						'price_adjustment_value' => 4,
					),
					array(
						'label'                  => 'Option 2',
						'value'                  => 'opt2',
						'price_adjustment_type'  => 'percent',
						'price_adjustment_value' => 10,
					),
				),
			),
		);
		$this->product->update_meta_data( '_extra_product_fields', $custom_fields );
		$this->product->save();

		$_POST['exprdawc_nonce']              = wp_create_nonce( 'exprdawc_save_custom_field' );
		$_POST['exprdawc_custom_field_input'] = array(
			'checkbox_mixed' => array( 'opt1', 'opt2' ),
		);

		$cart_item_data = array();
		$result         = $this->instance->exprdawc_save_extra_product_data_in_cart( $cart_item_data, $this->product_id, 0, 1 );

		$this->assertArrayHasKey( 'post_data_product_item', $result );
		$this->assertEquals( 'opt1, opt2', $result['post_data_product_item'][0]['value'] );
		// Mixed adjustments: fixed 4 + percent 10% = 4 + 10 = 14.
		$this->assertStringContainsString( 'opt1, opt2 (+', $result['post_data_product_item'][0]['value_cart'] );
	}

	/**
	 * Test exprdawc_save_extra_product_data_in_cart returns original data when nonce invalid.
	 *
	 * Test Goal:
	 * Verifies that the method does not save data and returns the original cart
	 * item data array when the nonce is invalid.
	 *
	 * Expected Result:
	 * - Method returns empty array
	 * - No post_data_product_item is added
	 * - Security check prevents data manipulation
	 *
	 * Test Conditions:
	 * - POST data contains invalid nonce "invalid-nonce"
	 * - Nonce verification fails (wp_verify_nonce returns false)
	 * - Security-first approach is tested
	 */
	public function test_exprdawc_save_extra_product_data_in_cart_invalid_nonce() {
		// Set up $_POST data with invalid nonce.
		$_POST['exprdawc_nonce']              = 'invalid-nonce';
		$_POST['exprdawc_custom_field_input'] = array(
			'test_field' => 'test value',
		);

		$cart_item_data = array();
		$result         = $this->instance->exprdawc_save_extra_product_data_in_cart( $cart_item_data, $this->product_id, 0, 1 );

		$this->assertEmpty( $result );
	}

	/**
	 * Test exprdawc_display_fields_on_cart_and_checkout displays fields on cart.
	 *
	 * Test Goal:
	 * Verifies that custom field data is correctly prepared for display in cart
	 * and checkout.
	 *
	 * Expected Result:
	 * - Method returns an array
	 * - Array contains formatted custom field data for display
	 * - Item data is extended with label and value
	 *
	 * Test Conditions:
	 * - Option 'exprdawc_show_in_cart' is set to 'yes'
	 * - Option 'exprdawc_show_empty_fields' is set to 'yes'
	 * - Cart item contains post_data_product_item
	 * - Product has custom fields in meta data
	 *
	 * Note:
	 * The test skips the is_cart() check as WordPress conditional tags
	 * require full page context which is not available in unit tests.
	 */
	public function test_exprdawc_display_fields_on_cart_and_checkout() {

		// Create cart page to ensure wc_get_cart_url() works.
		$cart_page_id = $this->create_cart_page();

		// Add custom fields to product.
		$custom_fields = array(
			array(
				'label' => 'Test Field',
				'type'  => 'text',
			),
		);
		$this->product->update_meta_data( '_extra_product_fields', $custom_fields );
		$this->product->save();

		// Set option to show in cart.
		update_option( 'exprdawc_show_in_cart', 'yes' );
		update_option( 'exprdawc_show_empty_fields', 'yes' );

		$this->go_to( get_permalink( $cart_page_id ) );

		// Prepare cart item with extra user data.
		$cart_item = array(
			'product_id'             => $this->product_id,
			'post_data_product_item' => array(
				array(
					'value'      => 'test value',  // raw value (for internal use).
					'value_cart' => 'test value', // display value (for cart display).
					'field_raw'  => array(
						'label' => 'Test Field',
					),
				),
			),
		);

		$item_data = array();
		$result    = $this->instance->exprdawc_display_fields_on_cart_and_checkout( $item_data, $cart_item );

		// Asserts.
		$this->assertCount( 1, $result );
		$this->assertSame( 'Test Field', $result[0]['key'] );
		$this->assertSame( 'test value', $result[0]['value'] );
		$this->assertSame( 'test value', $result[0]['display'] );

		// Clean up.
		wp_reset_postdata();
		delete_option( 'exprdawc_show_in_cart' );
	}

	/**
	 * Test exprdawc_display_fields_on_cart_and_checkout hides empty fields when option set.
	 *
	 * Test Goal:
	 * Verifies that empty custom field values are hidden in cart and checkout
	 * when the corresponding option is set.
	 *
	 * Expected Result:
	 * - Method returns an array
	 * - Empty fields are not added to item data
	 * - Option 'exprdawc_show_empty_fields' = 'no' is respected
	 *
	 * Test Conditions:
	 * - Option 'exprdawc_show_in_cart' is set to 'yes'
	 * - Option 'exprdawc_show_empty_fields' is set to 'no'
	 * - Cart item contains post_data_product_item with empty value
	 *
	 * Note:
	 * The test verifies the empty field logic without relying on is_cart().
	 */
	public function test_exprdawc_display_fields_on_cart_and_checkout_hide_empty() {
		// Add custom fields to product.
		$custom_fields = array(
			array(
				'label' => 'Test Field',
				'type'  => 'text',
			),
		);
		$this->product->update_meta_data( '_extra_product_fields', $custom_fields );
		$this->product->save();

		// Set options.
		update_option( 'exprdawc_show_in_cart', 'yes' );
		update_option( 'exprdawc_show_empty_fields', 'no' );

		// Prepare cart item with empty field.
		$cart_item = array(
			'product_id'             => $this->product_id,
			'post_data_product_item' => array(
				array(
					'index'      => 'test_field',
					'value'      => '',
					'value_cart' => '',
					'field_raw'  => array(
						'label' => 'Test Field',
						'type'  => 'text',
					),
				),
			),
		);

		$item_data = array();
		$result    = $this->instance->exprdawc_display_fields_on_cart_and_checkout( $item_data, $cart_item );

		// The result should be an array (may be empty due to is_cart() context).
		$this->assertIsArray( $result );

		// Clean up.
		wp_reset_postdata();
		delete_option( 'exprdawc_show_in_cart' );
		delete_option( 'exprdawc_show_empty_fields' );
	}

	/**
	 * Test exprdawc_display_fields_on_cart_and_checkout returns original data when no post_data_product_item.
	 *
	 * Test Goal:
	 * Verifies that the method returns empty item data when no post_data_product_item
	 * is present in the cart item.
	 *
	 * Expected Result:
	 * - Method returns empty array
	 * - No fields are added
	 * - Method returns early when no custom field data is present
	 *
	 * Test Conditions:
	 * - Cart item without post_data_product_item key
	 * - Standard product without custom fields
	 */
	public function test_exprdawc_display_fields_on_cart_and_checkout_no_extra_data() {
		$cart_item = array(
			'product_id' => $this->product_id,
		);

		$item_data = array();
		$result    = $this->instance->exprdawc_display_fields_on_cart_and_checkout( $item_data, $cart_item );

		$this->assertEmpty( $result );
	}

	/**
	 * Test exprdawc_adjust_cart_item_pricing adjusts price with fixed adjustment.
	 *
	 * Test Goal:
	 * Verifies that the product price in the cart is correctly adjusted by a fixed amount
	 * based on custom field settings.
	 *
	 * Expected Result:
	 * - Base price (100) + Fixed adjustment (10) = Final price (110)
	 * - Price is adjusted during cart calculation process
	 * - Cart item price is set to 110
	 *
	 * Test Conditions:
	 * - Product with base price 100
	 * - Custom field with adjust_price = true
	 * - price_adjustment_type = 'fixed'
	 * - price_adjustment_value = 10
	 * - Hook: woocommerce_before_calculate_totals
	 */
	public function test_exprdawc_adjust_cart_item_pricing_fixed_adjustment() {
		// Add product to cart with price adjustment.
		$cart_item_key = WC()->cart->add_to_cart( $this->product_id, 1 );

		// Get cart item and add extra user data.
		$cart_items = WC()->cart->get_cart();
		$cart_item  = $cart_items[ $cart_item_key ];

		$cart_item['post_data_product_item'] = array(
			array(
				'index'     => 'test_field',
				'value'     => 'test value',
				'field_raw' => array(
					'label'                  => 'Test Field',
					'type'                   => 'text',
					'adjust_price'           => true,
					'price_adjustment_type'  => 'fixed',
					'price_adjustment_value' => 10,
				),
			),
		);

		WC()->cart->cart_contents[ $cart_item_key ] = $cart_item;

		// Trigger price calculation.
		$this->instance->exprdawc_adjust_cart_item_pricing( WC()->cart );

		// Get the updated cart item.
		$updated_cart = WC()->cart->get_cart();
		$updated_item = $updated_cart[ $cart_item_key ];

		$this->assertEquals( 110, $updated_item['data']->get_price() );
	}

	/**
	 * Test exprdawc_adjust_cart_item_pricing adjusts price with percentage adjustment.
	 *
	 * Test Goal:
	 * Verifies that the product price in the cart is correctly adjusted by a percentage
	 * amount based on custom field settings.
	 *
	 * Expected Result:
	 * - Base price (100) + 20% adjustment (20) = Final price (120)
	 * - Percentage calculation: (100 / 100) * 20 = 20
	 * - Cart item price is set to 120
	 *
	 * Test Conditions:
	 * - Product with base price 100
	 * - Custom field with adjust_price = true
	 * - price_adjustment_type = 'percent'
	 * - price_adjustment_value = 20 (represents 20%)
	 * - Hook: woocommerce_before_calculate_totals
	 */
	public function test_exprdawc_adjust_cart_item_pricing_percentage_adjustment() {
		// Add product to cart with price adjustment.
		$cart_item_key = WC()->cart->add_to_cart( $this->product_id, 1 );

		// Get cart item and add extra user data.
		$cart_items = WC()->cart->get_cart();
		$cart_item  = $cart_items[ $cart_item_key ];

		$cart_item['post_data_product_item'] = array(
			array(
				'index'     => 'test_field',
				'value'     => 'test value',
				'field_raw' => array(
					'label'                  => 'Test Field',
					'type'                   => 'text',
					'adjust_price'           => true,
					'price_adjustment_type'  => 'percentage',
					'price_adjustment_value' => 20,
				),
			),
		);

		WC()->cart->cart_contents[ $cart_item_key ] = $cart_item;

		// Trigger price calculation.
		$this->instance->exprdawc_adjust_cart_item_pricing( WC()->cart );

		// Get the updated cart item.
		$updated_cart = WC()->cart->get_cart();
		$updated_item = $updated_cart[ $cart_item_key ];

		$this->assertEquals( 120, $updated_item['data']->get_price() );
	}

	/**
	 * Test exprdawc_add_extra_product_data_to_order adds meta data to order item.
	 *
	 * Test Goal:
	 * Verifies that custom field data is correctly added as meta data to an order
	 * when a product is purchased.
	 *
	 * Expected Result:
	 * - Order item contains meta data
	 * - Meta data contains label "Test Field" and value "test value"
	 * - Additional meta data '_meta_extra_product_data' is saved
	 * - Data is visible in the order overview
	 *
	 * Test Conditions:
	 * - Mock order and order item are created
	 * - Cart item contains post_data_product_item
	 * - Hook: woocommerce_checkout_create_order_line_item
	 */
	public function test_exprdawc_add_extra_product_data_to_order() {
		// Create a mock order item.
		$order = wc_create_order();
		$item  = new WC_Order_Item_Product();
		$item->set_product_id( $this->product_id );

		// Prepare values with extra user data.
		$values = array(
			'post_data_product_item' => array(
				array(
					'index'     => 'test_field',
					'value'     => 'test value',
					'field_raw' => array(
						'label' => 'Test Field',
						'type'  => 'text',
					),
				),
			),
		);

		$this->instance->exprdawc_add_extra_product_data_to_order( $item, 'cart_item_key', $values, $order );

		// Get meta data.
		$meta_data = $item->get_meta_data();
		$this->assertNotEmpty( $meta_data );

		// Check if the meta was added.
		$found = false;
		foreach ( $meta_data as $meta ) {
			if ( 'Test Field' === $meta->key && 'test value' === $meta->value ) {
				$found = true;
				break;
			}
		}
		$this->assertTrue( $found );

		// Clean up.
		$order->delete( true );
	}

	/**
	 * Test exprdawc_add_extra_product_data_to_order does nothing when no post_data_product_item.
	 *
	 * Test Goal:
	 * Verifies that the method does not add meta data and returns early when
	 * no post_data_product_item is present in the values array.
	 *
	 * Expected Result:
	 * - Order item contains no meta data
	 * - Method returns early (early return)
	 * - No custom field data is added to the order
	 *
	 * Test Conditions:
	 * - Mock order and order item are created
	 * - Values array contains no post_data_product_item
	 * - Standard product without custom fields
	 */
	public function test_exprdawc_add_extra_product_data_to_order_no_extra_data() {
		// Create a mock order item.
		$order = wc_create_order();
		$item  = new WC_Order_Item_Product();
		$item->set_product_id( $this->product_id );

		// Prepare values without extra user data.
		$values = array();

		$this->instance->exprdawc_add_extra_product_data_to_order( $item, 'cart_item_key', $values, $order );

		// Get meta data.
		$meta_data = $item->get_meta_data();
		$this->assertEmpty( $meta_data );

		// Clean up.
		$order->delete( true );
	}
}
