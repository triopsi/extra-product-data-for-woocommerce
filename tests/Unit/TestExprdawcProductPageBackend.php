<?php
declare( strict_types=1 );

use Triopsi\Exprdawc\Exprdawc_Product_Page_Backend;

/**
 * Class TestExprdawcProductPageBackend
 *
 * PHPUnit tests for Exprdawc_Product_Page_Backend class.
 *
 * @package Extra_Product_Data_For_WooCommerce\Tests\Unit
 */
class TestExprdawcProductPageBackend extends WP_UnitTestCase {

	/**
	 * Instance of the class being tested
	 *
	 * @var Exprdawc_Product_Page_Backend
	 */
	private $product_page_backend;

	/**
	 * Sets up the test environment before each test
	 *
	 * Expects: Instance of the class is created for testing.
	 *
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();
		$this->product_page_backend = new Exprdawc_Product_Page_Backend();
	}

	/**
	 * Cleans up the test environment after each test.
	 *
	 * Expects: All resources are cleaned up to prevent test pollution.
	 *
	 * @return void
	 */
	public function tearDown(): void {
		unset( $this->product_page_backend );
		parent::tearDown();
	}

	/**
	 * Tests that the Exprdawc_Product_Page_Backend class can be instantiated.
	 *
	 * Expects: The created object is an instance of Exprdawc_Product_Page_Backend.
	 *
	 * @covers Triopsi\Exprdawc\Exprdawc_Product_Page_Backend::__construct
	 */
	public function test_can_instantiate() {
		$this->assertInstanceOf(
			Exprdawc_Product_Page_Backend::class,
			$this->product_page_backend,
			'Instance should be of type Exprdawc_Product_Page_Backend.'
		);
	}

	/**
	 * Tests that constructor registers hooks when in admin context.
	 *
	 * Expects: All filters and actions are registered when is_admin() returns true.
	 *
	 * @covers Triopsi\Exprdawc\Exprdawc_Product_Page_Backend::__construct
	 */
	public function test_constructor_registers_hooks_in_admin_context() {
		global $wp_filter;

		// Simulate admin context.
		set_current_screen( 'edit-post' );

		// Create new instance in admin context.
		$backend = new Exprdawc_Product_Page_Backend();

		// Check filter is registered.
		$this->assertTrue(
			has_filter( 'woocommerce_product_data_tabs', array( $backend, 'exprdawc_add_custom_product_tab' ) ) !== false,
			'Filter woocommerce_product_data_tabs should be registered.'
		);

		// Check actions are registered.
		$this->assertTrue(
			has_action( 'woocommerce_product_data_panels', array( $backend, 'exprdawc_add_custom_product_fields' ) ) !== false,
			'Action woocommerce_product_data_panels should be registered.'
		);

		$this->assertTrue(
			has_action( 'woocommerce_process_product_meta', array( $backend, 'exprdawc_save_extra_product_fields' ) ) !== false,
			'Action woocommerce_process_product_meta should be registered.'
		);

		$this->assertTrue(
			has_action( 'admin_enqueue_scripts', array( $backend, 'exprdawc_show_general_tab' ) ) !== false,
			'Action admin_enqueue_scripts should be registered.'
		);

		$this->assertTrue(
			has_action( 'wp_ajax_exprdawc_import_custom_fields', array( $backend, 'exprdawc_import_custom_fields' ) ) !== false,
			'Action wp_ajax_exprdawc_import_custom_fields should be registered.'
		);
	}

	/**
	 * Tests that constructor does not register hooks when not in admin context
	 *
	 * Expects: No filters or actions are registered when is_admin() returns false.
	 *
	 * @covers Triopsi\Exprdawc\Exprdawc_Product_Page_Backend::__construct
	 */
	public function test_constructor_does_not_register_hooks_in_frontend_context() {
		// Remove admin screen (simulate frontend).
		set_current_screen( 'front' );

		// Store current filter/action state.
		global $wp_filter;
		$filters_before = array();
		$actions_before = array();

		$hooks_to_check = array(
			'woocommerce_product_data_tabs',
			'woocommerce_product_data_panels',
			'woocommerce_process_product_meta',
			'admin_enqueue_scripts',
			'wp_ajax_exprdawc_import_custom_fields',
		);

		foreach ( $hooks_to_check as $hook ) {
			if ( isset( $wp_filter[ $hook ] ) ) {
				$filters_before[ $hook ] = count( $wp_filter[ $hook ]->callbacks );
			} else {
				$filters_before[ $hook ] = 0;
			}
		}

		// Create new instance in frontend context.
		$backend = new Exprdawc_Product_Page_Backend();

		// Verify hooks were not added.
		foreach ( $hooks_to_check as $hook ) {
			$count_after = isset( $wp_filter[ $hook ] ) ? count( $wp_filter[ $hook ]->callbacks ) : 0;

			$this->assertEquals(
				$filters_before[ $hook ],
				$count_after,
				"Hook {$hook} should not have new callbacks in frontend context."
			);
		}
	}

	/**
	 * Tests that exprdawc_add_custom_product_tab adds a tab with the key 'custom_fields'.
	 *
	 * Expects: The returned array contains a 'custom_fields' key with proper structure.
	 *
	 * @covers Triopsi\Exprdawc\Exprdawc_Product_Page_Backend::exprdawc_add_custom_product_tab
	 */
	public function test_exprdawc_add_custom_product_tab_adds_tab() {
		$tabs   = array();
		$result = $this->product_page_backend->exprdawc_add_custom_product_tab( $tabs );

		$this->assertArrayHasKey( 'custom_fields', $result, 'Tab array should contain custom_fields key.' );
	}

	/**
	 * Tests that exprdawc_add_custom_product_tab returns correct tab structure.
	 *
	 * Expects: The custom_fields tab has label, target, and class properties.
	 *
	 * @covers Triopsi\Exprdawc\Exprdawc_Product_Page_Backend::exprdawc_add_custom_product_tab
	 */
	public function test_exprdawc_add_custom_product_tab_structure() {
		$tabs   = array();
		$result = $this->product_page_backend->exprdawc_add_custom_product_tab( $tabs );

		$this->assertIsArray( $result['custom_fields'], 'custom_fields should be an array.' );
		$this->assertArrayHasKey( 'label', $result['custom_fields'], 'Tab should have a label.' );
		$this->assertArrayHasKey( 'target', $result['custom_fields'], 'Tab should have a target.' );
		$this->assertArrayHasKey( 'class', $result['custom_fields'], 'Tab should have a class.' );
		$this->assertEquals( 'extra-product-data', $result['custom_fields']['target'], 'Target should be extra-product-data.' );
	}

	/**
	 * Tests that exprdawc_add_custom_product_tab preserves existing tabs.
	 *
	 * Expects: Original tabs remain in the array along with the new custom_fields tab.
	 *
	 * @covers Triopsi\Exprdawc\Exprdawc_Product_Page_Backend::exprdawc_add_custom_product_tab
	 */
	public function test_exprdawc_add_custom_product_tab_preserves_existing_tabs() {
		$tabs = array(
			'general' => array(
				'label'  => 'General',
				'target' => 'general_product_data',
			),
		);

		$result = $this->product_page_backend->exprdawc_add_custom_product_tab( $tabs );

		$this->assertArrayHasKey( 'general', $result, 'Original tabs should be preserved.' );
		$this->assertArrayHasKey( 'custom_fields', $result, 'New tab should be added.' );
		$this->assertCount( 2, $result, 'Result should contain 2 tabs.' );
	}

	/**
	 * Tests that exprdawc_add_custom_product_fields renders the custom fields template.
	 *
	 * Expects: The template output contains the custom field label.
	 */
	public function test_exprdawc_add_custom_product_fields_renders_template() {

		// Create Product.
		$product = new WC_Product_Simple();
		$product->set_name( 'Test Product' );
		$product->set_regular_price( '10' );
		$product->save();

		$product_id = $product->get_id();

		// Set custom fields meta for the product.
		$custom_fields = array(
			array(
				'label'                  => 'Select Field',
				'type'                   => 'select',
				'required'               => 1,
				'placeholder_text'       => 'Choose an option',
				'help_text'              => 'Select one option',
				'autocomplete'           => '',
				'autofocus'              => false,
				'index'                  => 0,
				'price_adjustment_type'  => '',
				'price_adjustment_value' => '',
				'conditional_logic'      => 0,
				'conditional_rules'      => array(),
				'editable'               => true,
				'adjust_price'           => false,
				'options'                => array(
					array(
						'label'                  => 'Option A',
						'value'                  => 'a',
						'price_adjustment_type'  => '',
						'price_adjustment_value' => '',
						'default'                => 0,
					),
					array(
						'label'                  => 'Option B',
						'value'                  => 'b',
						'price_adjustment_type'  => '',
						'price_adjustment_value' => '',
						'default'                => 0,
					),
				),
			),
		);

		$product->update_meta_data( '_extra_product_fields', $custom_fields );
		$product->save();

		// Simulate global $post (as in admin).
		global $post;
		$post = get_post( $product_id );

		// Capture output.
		ob_start();

		$this->product_page_backend->exprdawc_add_custom_product_fields();

		$output = ob_get_clean();

		$this->assertNotEmpty( $output, 'Template should render output.' );
		$this->assertStringContainsString( 'Select Field', $output );

		wp_delete_post( $product_id, true );
	}

	/**
	 * Tests that exprdawc_show_general_tab enqueues the required script.
	 *
	 * Expects: The script 'exprdawc-wc-meta-boxes-js' is registered after the method is called.
	 *
	 * @covers Triopsi\Exprdawc\Exprdawc_Product_Page_Backend::exprdawc_show_general_tab
	 */
	public function test_exprdawc_show_general_tab_enqueues_script() {
		$this->product_page_backend->exprdawc_show_general_tab();

		global $wp_scripts;
		$this->assertTrue(
			isset( $wp_scripts->registered['exprdawc-wc-meta-boxes-js'] ),
			'Script exprdawc-wc-meta-boxes-js should be registered.'
		);
	}

	/**
	 * Tests that exprdawc_show_general_tab localizes script with correct data.
	 *
	 * Expects: The script has localization data including edit_exprdawc_nonce and other strings.
	 *
	 * @covers Triopsi\Exprdawc\Exprdawc_Product_Page_Backend::exprdawc_show_general_tab
	 */
	public function test_exprdawc_show_general_tab_localizes_script() {
		$this->product_page_backend->exprdawc_show_general_tab();

		global $wp_scripts;
		$localized_data = $wp_scripts->get_data( 'exprdawc-wc-meta-boxes-js', 'data' );

		$this->assertNotEmpty( $localized_data, 'Script should have localized data.' );
		$this->assertStringContainsString( 'exprdawc_admin_meta_boxes', $localized_data, 'Localized data should contain object name.' );
		$this->assertStringContainsString( 'edit_exprdawc_nonce', $localized_data, 'Localized data should contain nonce.' );
	}

	/**
	 * Tests that exprdawc_save_extra_product_fields saves custom fields to product meta.
	 *
	 * Expects: Custom fields are saved as product meta data '_extra_product_fields'.
	 *
	 * @covers Triopsi\Exprdawc\Exprdawc_Product_Page_Backend::exprdawc_save_extra_product_fields
	 */
	public function test_exprdawc_save_extra_product_fields_saves_data() {
		$product = new WC_Product_Simple();
		$product->set_name( 'Test Product' );
		$product->set_regular_price( '10' );
		$product->save();
		$post_id = $product->get_id();

		$_POST['extra_product_fields'] = array(
			array(
				'label'                  => 'Test Field',
				'type'                   => 'text',
				'required'               => '1',
				'placeholder_text'       => 'Enter text',
				'help_text'              => 'Help text here',
				'index'                  => '0',
				'price_adjustment_type'  => '',
				'price_adjustment_value' => '',
			),
		);

		$this->product_page_backend->exprdawc_save_extra_product_fields( $post_id );

		$product       = wc_get_product( $post_id );
		$custom_fields = $product->get_meta( '_extra_product_fields', true );

		$this->assertIsArray( $custom_fields, 'Custom fields should be an array.' );
		$this->assertCount( 1, $custom_fields, 'Should have 1 custom field.' );
		$this->assertEquals( 'Test Field', $custom_fields[0]['label'], 'Field label should match.' );
		$this->assertEquals( 'text', $custom_fields[0]['type'], 'Field type should match.' );

		// Clean up.
		unset( $_POST['extra_product_fields'] );
		$product->delete();
	}

	/**
	 * Tests that exprdawc_save_extra_product_fields sanitizes field data correctly.
	 *
	 * Expects: HTML tags and malicious content are stripped from field values.
	 *
	 * @covers Triopsi\Exprdawc\Exprdawc_Product_Page_Backend::exprdawc_save_extra_product_fields
	 */
	public function test_exprdawc_save_extra_product_fields_sanitizes_data() {
		$product = new WC_Product_Simple();
		$product->set_name( 'Test Product' );
		$product->set_regular_price( '10' );
		$product->save();
		$post_id = $product->get_id();

		$_POST['extra_product_fields'] = array(
			array(
				'label'                  => '<script>alert("xss")</script>Test Field',
				'type'                   => 'text',
				'required'               => '1',
				'placeholder_text'       => '<b>Placeholder</b>',
				'help_text'              => 'Help text',
				'index'                  => '0',
				'price_adjustment_type'  => '',
				'price_adjustment_value' => '',
			),
		);

		$this->product_page_backend->exprdawc_save_extra_product_fields( $post_id );

		$product       = wc_get_product( $post_id );
		$custom_fields = $product->get_meta( '_extra_product_fields', true );

		$this->assertStringNotContainsString( '<script>', $custom_fields[0]['label'], 'Script tags should be removed.' );
		$this->assertStringNotContainsString( '<b>', $custom_fields[0]['placeholder_text'], 'HTML tags should be removed.' );

		// Clean up.
		unset( $_POST['extra_product_fields'] );
		$product->delete();
	}

	/**
	 * Tests that exprdawc_save_extra_product_fields handles options array correctly.
	 *
	 * Expects: Options for select/radio/checkbox fields are saved with proper structure.
	 *
	 * @covers Triopsi\Exprdawc\Exprdawc_Product_Page_Backend::exprdawc_save_extra_product_fields
	 */
	public function test_exprdawc_save_extra_product_fields_with_options() {
		$product = new WC_Product_Simple();
		$product->set_name( 'Test Product' );
		$product->set_regular_price( '10' );
		$product->save();
		$post_id = $product->get_id();

		$_POST['extra_product_fields'] = array(
			array(
				'label'                  => 'Select Field',
				'type'                   => 'select',
				'required'               => '0',
				'placeholder_text'       => '',
				'help_text'              => '',
				'index'                  => '0',
				'price_adjustment_type'  => '',
				'price_adjustment_value' => '',
				'options'                => array(
					array(
						'label'   => 'Option 1',
						'value'   => 'opt1',
						'default' => '1',
					),
					array(
						'label' => 'Option 2',
						'value' => 'opt2',
					),
				),
			),
		);

		$this->product_page_backend->exprdawc_save_extra_product_fields( $post_id );

		$product       = wc_get_product( $post_id );
		$custom_fields = $product->get_meta( '_extra_product_fields', true );

		$this->assertArrayHasKey( 'options', $custom_fields[0], 'Field should have options.' );
		$this->assertCount( 2, $custom_fields[0]['options'], 'Should have 2 options.' );
		$this->assertEquals( 'Option 1', $custom_fields[0]['options'][0]['label'], 'Option label should match.' );
		$this->assertEquals( 'opt1', $custom_fields[0]['options'][0]['value'], 'Option value should match.' );

		// Clean up.
		unset( $_POST['extra_product_fields'] );
		$product->delete();
	}

	/**
	 * Tests that exprdawc_save_extra_product_fields deletes meta when no fields are provided.
	 *
	 * Expects: Product meta '_extra_product_fields' is deleted when POST data is empty.
	 *
	 * @covers Triopsi\Exprdawc\Exprdawc_Product_Page_Backend::exprdawc_save_extra_product_fields
	 */
	public function test_exprdawc_save_extra_product_fields_deletes_meta_when_empty() {
		$product = new WC_Product_Simple();
		$product->set_name( 'Test Product' );
		$product->set_regular_price( '10' );
		$product->save();
		$post_id = $product->get_id();

		// First, save some custom fields.
		$product->update_meta_data( '_extra_product_fields', array( array( 'label' => 'Test' ) ) );
		$product->save();

		// Now save without POST data.
		unset( $_POST['extra_product_fields'] );
		$this->product_page_backend->exprdawc_save_extra_product_fields( $post_id );

		$product       = wc_get_product( $post_id );
		$custom_fields = $product->get_meta( '_extra_product_fields', true );

		$this->assertEmpty( $custom_fields, 'Custom fields should be empty when no POST data is provided.' );

		// Clean up.
		$product->delete();
	}

	/**
	 * Tests that exprdawc_import_custom_fields imports valid JSON data.
	 *
	 * Expects: JSON string is decoded and saved as product meta data.
	 *
	 * @group ajax
	 * @covers Triopsi\Exprdawc\Exprdawc_Product_Page_Backend::exprdawc_import_custom_fields
	 */
	public function test_exprdawc_import_custom_fields_with_valid_json() {
		$product = new WC_Product_Simple();
		$product->set_name( 'Test Product' );
		$product->set_regular_price( '10' );
		$product->save();
		$product_id = $product->get_id();

		if ( ! defined( 'DOING_AJAX' ) ) {
			define( 'DOING_AJAX', true );
		}

		// Set current user capabilities FIRST.
		$user_id = $this->factory()->user->create( array( 'role' => 'administrator' ) );
		$user    = get_user_by( 'id', $user_id );
		$user->add_cap( 'edit_products' );
		$user->add_cap( 'edit_published_products' );
		$user->add_cap( 'edit_others_products' );
		$user->add_cap( 'edit_private_products' );
		wp_set_current_user( $user_id );

		$custom_fields = array(
			array(
				'label' => 'Imported Field',
				'type'  => 'text',
			),
		);

		$_POST['product_id']    = $product_id;
		$_POST['export_string'] = wp_json_encode( $custom_fields );

		// Create nonce AFTER setting current user.
		$_POST['security']    = wp_create_nonce( 'edit_exprdawc_nonce' );
		$_REQUEST['security'] = $_POST['security']; // @phpcs:ignore

		$_POST['action'] = 'exprdawc_import_custom_fields';
		ob_start();
		try {
			$this->product_page_backend->exprdawc_import_custom_fields();
		} catch ( Exception $e ) { // phpcs:ignore
			// WP Die expected.
		}

		$output = ob_get_clean();
		var_dump( $output );
		$product = wc_get_product( $product_id );
		var_dump( $product );
		$saved_fields = $product->get_meta( '_extra_product_fields', true );
		var_dump( $saved_fields );

		$this->assertIsArray( $saved_fields );
		$this->assertCount( 1, $saved_fields );
		$this->assertEquals( 'Imported Field', $saved_fields[0]['label'] );

		unset( $_POST['product_id'], $_POST['export_string'], $_POST['security'], $_POST['action'] );
		$product->delete();
	}

	/**
	 * Tests that exprdawc_import_custom_fields rejects invalid JSON.
	 *
	 * Expects: Error response is sent when JSON is malformed.
	 *
	 * @group ajax
	 * @covers Triopsi\Exprdawc\Exprdawc_Product_Page_Backend::exprdawc_import_custom_fields
	 */
	public function test_exprdawc_import_custom_fields_with_invalid_json() {
		$product = new WC_Product_Simple();
		$product->set_name( 'Test Product' );
		$product->set_regular_price( '10' );
		$product->save();
		$product_id = $product->get_id();

		if ( ! defined( 'DOING_AJAX' ) ) {
			define( 'DOING_AJAX', true );
		}

		// Set current user capabilities.
		$user_id = $this->factory()->user->create( array( 'role' => 'administrator' ) );
		$user    = get_user_by( 'id', $user_id );
		$user->add_cap( 'edit_products' );
		$user->add_cap( 'edit_published_products' );
		$user->add_cap( 'edit_others_products' );
		$user->add_cap( 'edit_private_products' );
		wp_set_current_user( $user_id );

		$_POST['product_id']    = $product_id;
		$_POST['export_string'] = 'invalid json string {{{';

		// Create nonce AFTER setting current user.
		$_POST['security']    = wp_create_nonce( 'edit_exprdawc_nonce' );
		$_REQUEST['security'] = $_POST['security']; // @phpcs:ignore

		$_POST['action'] = 'exprdawc_import_custom_fields';

		// Capture JSON output to check error.
		ob_start();
		try {
			$this->product_page_backend->exprdawc_import_custom_fields();
		} catch ( Exception $e ) { // phpcs:ignore
			// Error expected.
		}
		restore_error_handler();
		$output = ob_get_clean();

		// Check that error response is returned.
		$this->assertStringContainsString( 'Invalid JSON string', $output, 'Error message should mention invalid JSON.' );

		// Clean up.
		unset( $_POST['product_id'], $_POST['export_string'], $_POST['security'], $_POST['action'] );
		$product->delete();
	}

	/**
	 * Tests that exprdawc_import_custom_fields requires valid nonce.
	 *
	 * Expects: Function fails when security nonce is invalid.
	 *
	 * @group ajax
	 * @covers Triopsi\Exprdawc\Exprdawc_Product_Page_Backend::exprdawc_import_custom_fields
	 */
	public function test_exprdawc_import_custom_fields_requires_valid_nonce() {
		$this->markTestSkipped( 'Not run' );
		$product = new WC_Product_Simple();
		$product->set_name( 'Test Product' );
		$product->set_regular_price( '10' );
		$product->save();
		$product_id = $product->get_id();

		$_POST['product_id']    = $product_id;
		$_POST['export_string'] = wp_json_encode( array() );
		$_POST['security']      = 'invalid_nonce';
		$_POST['action']        = 'exprdawc_import_custom_fields';

		// Expect nonce check to fail.
		$this->expectException( 'WPAjaxDieStopException' );
		$this->product_page_backend->exprdawc_import_custom_fields();

		// Clean up.
		unset( $_POST['product_id'], $_POST['export_string'], $_POST['security'], $_POST['action'] );
		$product->delete();
	}

	/**
	 * Tests that exprdawc_save_extra_product_fields handles conditional logic rules.
	 *
	 * Expects: Conditional rules are saved correctly with field, operator, and value.
	 *
	 * @covers Triopsi\Exprdawc\Exprdawc_Product_Page_Backend::exprdawc_save_extra_product_fields
	 */
	public function test_exprdawc_save_extra_product_fields_with_conditional_logic() {
		$product = new WC_Product_Simple();
		$product->set_name( 'Test Product' );
		$product->set_regular_price( '10' );
		$product->save();
		$post_id = $product->get_id();

		$_POST['extra_product_fields'] = array(
			array(
				'label'                  => 'Conditional Field',
				'type'                   => 'text',
				'required'               => '0',
				'placeholder_text'       => '',
				'help_text'              => '',
				'index'                  => '0',
				'price_adjustment_type'  => '',
				'price_adjustment_value' => '',
				'conditional_logic'      => '1',
				'conditional_rules'      => array(
					array(
						array(
							'field'    => 'other_field',
							'operator' => 'equals',
							'value'    => 'test',
						),
					),
				),
			),
		);

		$this->product_page_backend->exprdawc_save_extra_product_fields( $post_id );

		$product       = wc_get_product( $post_id );
		$custom_fields = $product->get_meta( '_extra_product_fields', true );

		$this->assertEquals( 1, $custom_fields[0]['conditional_logic'], 'Conditional logic should be enabled.' );
		$this->assertIsArray( $custom_fields[0]['conditional_rules'], 'Conditional rules should be an array.' );

		// Clean up.
		unset( $_POST['extra_product_fields'] );
		$product->delete();
	}
}
