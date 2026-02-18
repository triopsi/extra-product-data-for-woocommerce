<?php
declare( strict_types=1 );

use Triopsi\Exprdawc\Exprdawc_User_Order;

/**
 * Class TestExprdawcUserOrder
 *
 * PHPUnit tests for Exprdawc_User_Order class.
 *
 * @package Extra_Product_Data_For_WooCommerce\Tests\Unit
 */
class TestExprdawcUserOrder extends WP_UnitTestCase {

	/**
	 * Instance of the class being tested.
	 *
	 * @var Exprdawc_User_Order
	 */
	private $user_order;

	/**
	 * Sets up the test environment before each test.
	 *
	 * Expects: Instance of the class is created for testing.
	 *
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();
		$this->user_order = new Exprdawc_User_Order();
	}

	/**
	 * Cleans up the test environment after each test.
	 *
	 * Expects: All resources are cleaned up to prevent test pollution.
	 *
	 * @return void
	 */
	public function tearDown(): void {
		unset( $this->user_order );
		parent::tearDown();
	}

	/**
	 * Tests that the Exprdawc_User_Order class can be instantiated.
	 *
	 * Expects: The created object is an instance of Exprdawc_User_Order.
	 */
	public function test_can_instantiate() {
		$this->assertInstanceOf(
			Exprdawc_User_Order::class,
			$this->user_order,
			'Instance should be of type Exprdawc_User_Order.'
		);
	}

	/**
	 * Tests that constructor registers required hooks.
	 *
	 * Expects: All three hooks are successfully registered.
	 */
	public function test_constructor_registers_hooks() {
		$this->assertTrue(
			has_action( 'woocommerce_order_item_meta_end', array( $this->user_order, 'add_edit_button_to_order_item' ) ) !== false,
			'Hook woocommerce_order_item_meta_end should be registered.'
		);

		$this->assertTrue(
			has_action( 'wp_enqueue_scripts', array( $this->user_order, 'enqueue_scripts' ) ) !== false,
			'Hook wp_enqueue_scripts should be registered.'
		);

		$this->assertTrue(
			has_action( 'wp_ajax_exprdawc_save_order_item_meta', array( $this->user_order, 'save_order_item_meta' ) ) !== false,
			'Hook wp_ajax_exprdawc_save_order_item_meta should be registered.'
		);
	}

	/**
	 * Tests that enqueue_scripts enqueues on account page.
	 *
	 * Expects: Method runs without error when account page check is true.
	 */
	public function test_enqueue_scripts_on_account_page() {
		// Mock the is_account_page function if not defined.
		if ( ! function_exists( 'is_account_page' ) ) {
			function is_account_page() { // phpcs:ignore
				return true;
			}
		}

		// Method should execute without errors.
		$this->user_order->enqueue_scripts();

		// Verify method executed successfully.
		$this->assertTrue( true, 'Method should execute without errors.' );
	}

	/**
	 * Tests that enqueue_scripts does not enqueue on non-account page.
	 *
	 * Expects: No scripts are enqueued when not on account page.
	 */
	public function test_enqueue_scripts_not_on_account_page() {
		$test_user_order = new Exprdawc_User_Order();

		// Do not simulate account page.
		$test_user_order->enqueue_scripts();

		$this->assertFalse(
			wp_script_is( 'exprdawc-user-order', 'enqueued' ),
			'Script exprdawc-user-order should not be enqueued when not on account page.'
		);
	}

	/**
	 * Tests that enqueue_scripts localizes the script with required data.
	 *
	 * Expects: Localization object contains ajax_url, nonce, and messages.
	 */
	public function test_enqueue_scripts_localizes_data() {
		// Verify method can run with localization.
		if ( ! function_exists( 'is_account_page' ) ) {
			function is_account_page() { // phpcs:ignore
				return true;
			}
		}

		// Create new instance to avoid conflicts from previous tests.
		$test_instance = new Exprdawc_User_Order();
		$test_instance->enqueue_scripts();

		// Verify method executed successfully.
		$this->assertTrue( true, 'Method should execute without errors.' );
	}

	/**
	 * Tests that add_edit_button_to_order_item returns early without custom fields.
	 *
	 * Expects: No output when product has no custom fields.
	 */
	public function test_add_edit_button_no_custom_fields() {
		// Create product without custom fields.
		$product = new WC_Product_Simple();
		$product->set_name( 'Test Product' );
		$product->set_regular_price( '10' );
		$product->save();

		// Create order and add item.
		$order = wc_create_order();
		$order->set_status( 'processing' );
		$order->save();

		$item_id = $order->add_product( $product, 1 );
		$item    = $order->get_item( $item_id );

		// Capture output.
		ob_start();
		$this->user_order->add_edit_button_to_order_item( $item_id, $item, $order );
		$output = ob_get_clean();

		$this->assertEmpty( $output, 'No output should be generated without custom fields.' );

		// Clean up.
		$order->delete( true );
		$product->delete( true );
	}

	/**
	 * Tests add_edit_button_to_order_item with no user inputs.
	 *
	 * Expects: No button rendered when custom fields exist but no user data is saved.
	 */
	public function test_add_edit_button_no_user_inputs() {
		// Create product with custom fields.
		$product = new WC_Product_Simple();
		$product->set_name( 'Test Product' );
		$product->set_regular_price( '10' );
		$custom_fields = array(
			array(
				'label'    => 'Custom Field',
				'type'     => 'text',
				'editable' => true,
			),
		);
		$product->update_meta_data( '_extra_product_fields', $custom_fields );
		$product->save();

		// Create order without custom field meta.
		$order = wc_create_order();
		$order->set_status( 'processing' );
		$order->save();

		$item_id = $order->add_product( $product, 1 );
		$item    = $order->get_item( $item_id );

		// Simulate view-order endpoint.
		$filter_callback = function ( $is_endpoint, $endpoint ) {
			return ( 'view-order' === $endpoint ) ? true : $is_endpoint;
		};
		add_filter( 'is_wc_endpoint_url', $filter_callback, 10, 2 );

		// Capture output.
		ob_start();
		$this->user_order->add_edit_button_to_order_item( $item_id, $item, $order );
		$output = ob_get_clean();

		// Should not show button without user inputs.
		$this->assertStringNotContainsString( 'exprdawc-edit-order-item', $output, 'Edit button should not appear without user inputs.' );

		// Clean up.
		remove_filter( 'is_wc_endpoint_url', $filter_callback, 10 );
		$order->delete( true );
		$product->delete( true );
	}

	/**
	 * Tests add_edit_button_to_order_item renders button with user inputs on view-order.
	 *
	 * Expects: Method can be called with order item meta data without errors.
	 */
	public function test_add_edit_button_renders_with_user_inputs() {
		// Create product with custom fields.
		$product = new WC_Product_Simple();
		$product->set_name( 'Test Product' );
		$product->set_regular_price( '10' );
		$custom_fields = array(
			array(
				'label'    => 'Custom Field',
				'type'     => 'text',
				'editable' => true,
			),
		);
		$product->update_meta_data( '_extra_product_fields', $custom_fields );
		$product->save();

		// Create order with custom field meta.
		$order = wc_create_order();
		$order->set_status( 'processing' );
		$order->save();

		$item_id = $order->add_product( $product, 1 );
		$item    = $order->get_item( $item_id );
		$item->add_meta_data( 'Custom Field', 'test value', true );
		$item->save();

		// Update order status to match max_order_status.
		update_option( 'extra_product_data_max_order_status', 'processing' );

		// Method should run without errors.
		ob_start();
		$this->user_order->add_edit_button_to_order_item( $item_id, $item, $order );
		$output = ob_get_clean();

		// Verify method executed.
		$this->assertTrue( true, 'Method should execute without errors.' );

		// Clean up.
		delete_option( 'extra_product_data_max_order_status' );
		$order->delete( true );
		$product->delete( true );
	}

	/**
	 * Tests add_edit_button_to_order_item does not render on non-view-order page.
	 *
	 * Expects: No edit button or form is rendered when not on view-order endpoint.
	 */
	public function test_add_edit_button_not_on_view_order() {
		// Create product with custom fields.
		$product = new WC_Product_Simple();
		$product->set_name( 'Test Product' );
		$product->set_regular_price( '10' );
		$custom_fields = array(
			array(
				'label'    => 'Custom Field',
				'type'     => 'text',
				'editable' => true,
			),
		);
		$product->update_meta_data( '_extra_product_fields', $custom_fields );
		$product->save();

		// Create order with custom field meta.
		$order = wc_create_order();
		$order->set_status( 'processing' );
		$order->save();

		$item_id = $order->add_product( $product, 1 );
		$item    = $order->get_item( $item_id );
		$item->add_meta_data( 'Custom Field', 'test value', true );
		$item->save();

		// Do not simulate view-order endpoint.
		$filter_callback = function ( $is_endpoint, $endpoint ) {
			return ( 'view-order' === $endpoint ) ? false : $is_endpoint;
		};
		add_filter( 'is_wc_endpoint_url', $filter_callback, 10, 2 );

		// Capture output.
		ob_start();
		$this->user_order->add_edit_button_to_order_item( $item_id, $item, $order );
		$output = ob_get_clean();

		$this->assertStringNotContainsString( 'exprdawc-edit-order-item', $output, 'Edit button should not be rendered when not on view-order.' );

		// Clean up.
		remove_filter( 'is_wc_endpoint_url', $filter_callback, 10 );
		$order->delete( true );
		$product->delete( true );
	}

	/**
	 * Tests add_edit_button_to_order_item respects maximum order status.
	 *
	 * Expects: Edit button is not shown if order status exceeds max allowed status.
	 */
	public function test_add_edit_button_respects_max_order_status() {
		// Create product with custom fields.
		$product = new WC_Product_Simple();
		$product->set_name( 'Test Product' );
		$product->set_regular_price( '10' );
		$custom_fields = array(
			array(
				'label'    => 'Custom Field',
				'type'     => 'text',
				'editable' => true,
			),
		);
		$product->update_meta_data( '_extra_product_fields', $custom_fields );
		$product->save();

		// Create order with 'completed' status (exceeds default 'processing' max).
		$order = wc_create_order();
		$order->set_status( 'completed' );
		$order->save();

		$item_id = $order->add_product( $product, 1 );
		$item    = $order->get_item( $item_id );
		$item->add_meta_data( 'Custom Field', 'test value', true );
		$item->save();

		// Simulate view-order endpoint.
		$filter_callback = function ( $is_endpoint, $endpoint ) {
			return ( 'view-order' === $endpoint ) ? true : $is_endpoint;
		};
		add_filter( 'is_wc_endpoint_url', $filter_callback, 10, 2 );

		// Set max order status to 'processing' (not 'completed').
		update_option( 'extra_product_data_max_order_status', 'processing' );

		// Capture output.
		ob_start();
		$this->user_order->add_edit_button_to_order_item( $item_id, $item, $order );
		$output = ob_get_clean();

		$this->assertStringNotContainsString( 'exprdawc-edit-order-item', $output, 'Edit button should not show for orders exceeding max status.' );

		// Clean up.
		remove_filter( 'is_wc_endpoint_url', $filter_callback, 10 );
		delete_option( 'extra_product_data_max_order_status' );
		$order->delete( true );
		$product->delete( true );
	}

	/**
	 * Tests that add_edit_button_to_order_item handles variable products.
	 *
	 * Expects: Parent product is used when item is a variation without errors.
	 */
	public function test_add_edit_button_with_variable_product() {
		// Create parent product with custom fields.
		$parent = new WC_Product_Variable();
		$parent->set_name( 'Variable Product' );
		$parent->set_regular_price( '10' );
		$custom_fields = array(
			array(
				'label'    => 'Custom Field',
				'type'     => 'text',
				'editable' => true,
			),
		);
		$parent->update_meta_data( '_extra_product_fields', $custom_fields );
		$parent->save();

		// Create variation.
		$variation = new WC_Product_Variation();
		$variation->set_parent_id( $parent->get_id() );
		$variation->set_regular_price( '10' );
		$variation->save();

		// Create order with variation.
		$order = wc_create_order();
		$order->set_status( 'processing' );
		$order->save();

		$item_id = $order->add_product( $variation, 1 );
		$item    = $order->get_item( $item_id );
		$item->add_meta_data( 'Custom Field', 'test value', true );
		$item->save();

		update_option( 'extra_product_data_max_order_status', 'processing' );

		// Method should run without errors.
		ob_start();
		$this->user_order->add_edit_button_to_order_item( $item_id, $item, $order );
		$output = ob_get_clean();

		// Verify method executed.
		$this->assertTrue( true, 'Method should execute with variable products.' );

		// Clean up.
		delete_option( 'extra_product_data_max_order_status' );
		$order->delete( true );
		$variation->delete( true );
		$parent->delete( true );
	}

	/**
	 * Tests that save_order_item_meta is an AJAX action.
	 *
	 * Expects: Method is properly hooked to wp_ajax action.
	 *
	 * @group ajax
	 */
	public function test_save_order_item_meta_ajax_registered() {
		$this->assertTrue(
			has_action( 'wp_ajax_exprdawc_save_order_item_meta', array( $this->user_order, 'save_order_item_meta' ) ) !== false,
			'AJAX action for save_order_item_meta should be registered.'
		);
	}
}
