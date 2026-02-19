<?php
declare( strict_types=1 );

use Triopsi\Exprdawc\Exprdawc_Admin_Order;

/**
 * Class TestExprdawcAdminOrder
 *
 * PHPUnit tests for Exprdawc_Admin_Order class.
 *
 * @package Extra_Product_Data_For_WooCommerce\Tests\Unit
 */
class TestExprdawcAdminOrder extends WP_UnitTestCase {

	/**
	 * Instance of the class being tested.
	 *
	 * @var Exprdawc_Admin_Order
	 */
	private $admin_order;

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

		// Set up an administrator user for AJAX tests.
		$user_id = $this->factory()->user->create( array( 'role' => 'administrator' ) );
		$user    = get_user_by( 'id', $user_id );

		// Add capability. This is needed for the permission check in the AJAX handler.
		$user->add_cap( 'edit_shop_orders' );
		$user->add_cap( 'manage_woocommerce' );
		wp_set_current_user( $user_id );

		// Create test product with custom fields.
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

		// Add custom fields to product.
		$custom_fields = array(
			array(
				'label'        => 'Test Field',
				'type'         => 'text',
				'required'     => false,
				'adjust_price' => false,
			),
		);
		$this->product->update_meta_data( '_extra_product_fields', $custom_fields );
		$this->product->save();

		// Create test order.
		$this->order_id = wc_create_order()->get_id();
		$this->order    = wc_get_order( $this->order_id );
		$this->order->set_customer_id( $user_id );
		$this->order->add_product( $this->product, 1 );
		$this->order->set_status( 'pending' );
		$this->order->save();

		$this->admin_order = new Exprdawc_Admin_Order();
	}

	/**
	 * Cleans up the test environment after each test.
	 *
	 * @return void
	 */
	public function tearDown(): void {
		// Clean up order properly.
		if ( $this->order instanceof WC_Order ) {
			$this->order->delete( true );
		}

		// Clean up product.
		if ( $this->product_id ) {
			wp_delete_post( $this->product_id, true );
		}

		unset( $this->admin_order, $this->product, $this->order );
		parent::tearDown();
	}

	/**
	 * Tests if the Exprdawc_Admin_Order class can be instantiated.
	 *
	 * Test Goal:
	 * Verifies that the class can be instantiated without errors.
	 */
	public function test_can_instantiate() {
		$this->assertInstanceOf( Exprdawc_Admin_Order::class, $this->admin_order );
	}

	/**
	 * Tests that constructor registers hooks properly.
	 *
	 * Test Goal:
	 * Verifies that all required WordPress/WooCommerce hooks are registered.
	 */
	public function test_constructor_registers_hooks() {
		$this->assertIsInt( has_action( 'woocommerce_admin_order_item_headers', array( $this->admin_order, 'set_order' ) ) );
		$this->assertIsInt( has_action( 'woocommerce_after_order_itemmeta', array( $this->admin_order, 'display_edit_button' ) ) );
		$this->assertIsInt( has_action( 'admin_enqueue_scripts', array( $this->admin_order, 'js_meta_boxes_enqueue' ) ) );
		$this->assertIsInt( has_action( 'admin_footer', array( $this->admin_order, 'add_js_template' ) ) );
		$this->assertIsInt( has_action( 'wp_ajax_woocommerce_configure_exprdawc_order_item', array( $this->admin_order, 'exprdawc_load_edit_modal_form' ) ) );
		$this->assertIsInt( has_action( 'wp_ajax_woocommerce_edit_exprdawc_order_item', array( $this->admin_order, 'exprdawc_save_edit_modal_form' ) ) );
	}

	/**
	 * Tests set_order method stores order object.
	 *
	 * Test Goal:
	 * Verifies that the static order property is set correctly.
	 */
	public function test_set_order() {
		Exprdawc_Admin_Order::set_order( $this->order );

		$reflection = new ReflectionClass( Exprdawc_Admin_Order::class );
		$property   = $reflection->getProperty( 'order' );
		$property->setAccessible( true );
		$stored_order = $property->getValue();

		$this->assertInstanceOf( WC_Order::class, $stored_order );
		$this->assertEquals( $this->order_id, $stored_order->get_id() );
	}

	/**
	 * Tests display_edit_button with no custom fields.
	 *
	 * Test Goal:
	 * Verifies that the edit button is not displayed when product has no custom fields.
	 */
	public function test_display_edit_button_no_custom_fields() {
		// Create product without custom fields.
		$product_id = wp_insert_post(
			array(
				'post_title'  => 'Test Product No Fields',
				'post_type'   => 'product',
				'post_status' => 'publish',
			)
		);
		$product    = wc_get_product( $product_id );
		$product->set_regular_price( 50 );
		$product->save();

		$order_id = wc_create_order();
		$order    = wc_get_order( $order_id );
		$item_id  = $order->add_product( $product, 1 );
		$order->set_status( 'pending' );
		$order->save();

		Exprdawc_Admin_Order::set_order( $order );

		$item = $order->get_item( $item_id );

		ob_start();
		$this->admin_order->display_edit_button( $item_id, $item, $product );
		$output = ob_get_clean();

		$this->assertEmpty( $output );

		// Cleanup.
		$order->delete( true );
		wp_delete_post( $product_id, true );
	}

	/**
	 * Tests display_edit_button displays when custom fields exist.
	 *
	 * Test Goal:
	 * Verifies that the edit button is displayed when product has custom fields.
	 */
	public function test_display_edit_button_with_custom_fields() {

		$order = $this->order;
		$order->set_status( 'processing' );
		Exprdawc_Admin_Order::set_order( $this->order );

		$items   = $this->order->get_items();
		$item    = reset( $items );
		$item_id = $item->get_id();
		ob_start();
		$this->admin_order->display_edit_button( $item_id, $item, $this->product );
		$output = ob_get_clean();

		// CSS class of the button.
		$this->assertStringContainsString( 'exprdawc_edit_addons', $output );
	}

	/**
	 * Tests display_edit_button respects order status.
	 *
	 * Test Goal:
	 * Verifies that edit button is only shown for allowed order statuses.
	 */
	public function test_display_edit_button_respects_order_status() {
		// Set order to completed status (beyond processing).
		$this->order->set_status( 'completed' );
		$this->order->save();

		Exprdawc_Admin_Order::set_order( $this->order );

		$items   = $this->order->get_items();
		$item    = reset( $items );
		$item_id = $item->get_id();

		ob_start();
		$this->admin_order->display_edit_button( $item_id, $item, $this->product );
		$output = ob_get_clean();

		// Should be empty as order status is beyond processing.
		$this->assertEmpty( $output );
	}

	/**
	 * Tests display_edit_button with variation product.
	 *
	 * Test Goal:
	 * Verifies that custom fields from parent product are used for variations.
	 */
	public function test_display_edit_button_with_variation() {
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

		// Add custom fields to parent.
		$custom_fields = array(
			array(
				'label'        => 'Variation Field',
				'type'         => 'text',
				'required'     => false,
				'adjust_price' => false,
			),
		);
		$parent->update_meta_data( '_extra_product_fields', $custom_fields );
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

		$order_id = wc_create_order();
		$order    = wc_get_order( $order_id );
		$item_id  = $order->add_product( $variation, 1 );
		$order->set_status( 'processing' );
		$order->save();

		Exprdawc_Admin_Order::set_order( $order );

		$item = $order->get_item( $item_id );

		ob_start();
		$this->admin_order->display_edit_button( $item_id, $item, $variation );
		$output = ob_get_clean();

		// CSS class of the button.
		$this->assertStringContainsString( 'exprdawc_edit_addons', $output );

		// Cleanup.
		$order->delete( true );
		wp_delete_post( $variation_id, true );
		wp_delete_post( $parent_id, true );
	}

	/**
	 * Tests is_current_screen method.
	 *
	 * Test Goal:
	 * Verifies that the method correctly identifies current screen.
	 */
	public function test_is_current_screen() {
		// Mock current screen.
		set_current_screen( 'edit-shop_order' );

		$result = $this->admin_order->is_current_screen( array( 'edit-shop_order' ) );
		$this->assertTrue( $result );

		$result = $this->admin_order->is_current_screen( array( 'product' ) );
		$this->assertFalse( $result );
	}

	/**
	 * Tests js_meta_boxes_enqueue on correct screen.
	 *
	 * Test Goal:
	 * Verifies that scripts are enqueued on order edit screen.
	 */
	public function test_js_meta_boxes_enqueue_on_order_screen() {
		set_current_screen( 'edit-shop_order' );

		$this->admin_order->js_meta_boxes_enqueue();

		$this->assertTrue( wp_script_is( 'woocommerce_exprdawc-admin-order-panel', 'enqueued' ) );
	}

	/**
	 * Tests js_meta_boxes_enqueue script localization.
	 *
	 * Test Goal:
	 * Verifies that script localization includes required data.
	 */
	public function test_js_meta_boxes_enqueue_localizes_script() {
		global $wp_scripts;

		set_current_screen( 'edit-shop_order' );
		$this->admin_order->js_meta_boxes_enqueue();

		$this->assertTrue( wp_script_is( 'woocommerce_exprdawc-admin-order-panel', 'enqueued' ) );

		// Check localized data.
		$data = $wp_scripts->get_data( 'woocommerce_exprdawc-admin-order-panel', 'data' );
		$this->assertNotEmpty( $data );
		$this->assertStringContainsString( 'wc_exprdawc_admin_order_params', $data );
	}

	/**
	 * Tests js_meta_boxes_enqueue not on product screen.
	 *
	 * Test Goal:
	 * Verifies that scripts are not enqueued on product screen.
	 */
	public function test_js_meta_boxes_enqueue_not_on_product_screen() {
		set_current_screen( 'product' );

		// Dequeue if already enqueued from other tests.
		wp_dequeue_script( 'woocommerce_exprdawc-admin-order-panel' );

		$this->admin_order->js_meta_boxes_enqueue();

		// Script should not be enqueued on product screen.
		$this->assertFalse( wp_script_is( 'woocommerce_exprdawc-admin-order-panel', 'enqueued' ) );
	}

	/**
	 * Tests add_js_template includes template when script enqueued.
	 *
	 * Test Goal:
	 * Verifies that JS template is included when script is enqueued.
	 */
	public function test_add_js_template_with_script() {
		// Skip if template path not defined.
		if ( ! defined( 'EXPRDAWC_ADMIN_TEMPLATES_PATH' ) ) {
			$this->markTestSkipped( 'EXPRDAWC_ADMIN_TEMPLATES_PATH not defined' );
		}

		set_current_screen( 'edit-shop_order' );
		$this->admin_order->js_meta_boxes_enqueue();

		ob_start();
		$this->admin_order->add_js_template();
		$output = ob_get_clean();

		if ( file_exists( EXPRDAWC_ADMIN_TEMPLATES_PATH . 'html-admin-order-edit-overview-js.php' ) ) {
			$this->assertNotEmpty( $output );
		} else {
			$this->markTestIncomplete( 'Template file not found' );
		}
	}

	/**
	 * Tests add_js_template does not include template without script.
	 *
	 * Test Goal:
	 * Verifies that JS template is not included when script is not enqueued.
	 */
	public function test_add_js_template_without_script() {
		// Ensure script is not enqueued.
		wp_dequeue_script( 'woocommerce_exprdawc-admin-order-panel' );

		ob_start();
		$this->admin_order->add_js_template();
		$output = ob_get_clean();

		$this->assertEmpty( $output );
	}

	/**
	 * Tests exprdawc_load_edit_modal_form with invalid parameters.
	 *
	 * Test Goal:
	 * Verifies that proper error is returned for invalid item/order IDs.
	 */
	public function test_load_edit_modal_form_invalid_parameters() {
		$_POST['security'] = wp_create_nonce( 'wc_exprdawc_edit_exprdawc' );
		$_POST['item_id']  = 0;
		$_POST['order_id'] = 0;
		if ( ! defined( 'DOING_AJAX' ) ) {
			define( 'DOING_AJAX', true );
		}
		$this->expectException( RuntimeException::class );
		$this->admin_order->exprdawc_load_edit_modal_form();
	}

	/**
	 * Tests exprdawc_load_edit_modal_form with valid parameters.
	 *
	 * Test Goal:
	 * Verifies that form HTML is generated correctly.
	 */
	public function test_load_edit_modal_form_with_valid_parameters() {

		$items   = $this->order->get_items();
		$item    = reset( $items );
		$item_id = $item->get_id();

		$nonce                = wp_create_nonce( 'wc_exprdawc_edit_exprdawc' );
		$_POST['security']    = $nonce;
		$_REQUEST['security'] = $nonce;
		$_POST['item_id']     = $item_id;
		$_POST['order_id']    = $this->order_id;
		$_REQUEST['action']   = 'woocommerce_configure_exprdawc_order_item';

		if ( ! defined( 'DOING_AJAX' ) ) {
			define( 'DOING_AJAX', true );
		}

		ob_start();
		try {
			$this->admin_order->exprdawc_load_edit_modal_form();
		} catch ( RuntimeException $e ) { // phpcs:ignore
			// Expected.
		}
		$output = ob_get_clean();

		$this->assertNotEmpty( $output, 'Response should not be empty' );
		$response = json_decode( $output, true );
		$this->assertIsArray( $response, 'Response should be valid JSON' );

		$this->assertTrue( $response['success'] );
		$this->assertArrayHasKey( 'html', $response['data'] );
		$this->assertStringContainsString( 'Test Field', $response['data']['html'] );
	}

	/**
	 * Tests exprdawc_load_edit_modal_form with no custom fields.
	 *
	 * Test Goal:
	 * Verifies proper error when product has no custom fields.
	 */
	public function test_load_edit_modal_form_no_custom_fields() {

		// Create order with product without custom fields.
		$product_id = wp_insert_post(
			array(
				'post_title'  => 'No Fields Product',
				'post_type'   => 'product',
				'post_status' => 'publish',
			)
		);
		$product    = wc_get_product( $product_id );
		$product->set_regular_price( 50 );
		$product->save();

		$order_id = wc_create_order()->get_id();
		$order    = wc_get_order( $order_id );
		$item_id  = $order->add_product( $product, 1 );
		$order->save();

		$items = $order->get_items();
		$item  = reset( $items );

		$nonce                = wp_create_nonce( 'wc_exprdawc_edit_exprdawc' );
		$_POST['security']    = $nonce;
		$_REQUEST['security'] = $nonce;
		$_POST['item_id']     = $item_id;
		$_POST['order_id']    = $this->order_id;
		$_REQUEST['action']   = 'woocommerce_configure_exprdawc_order_item';

		if ( ! defined( 'DOING_AJAX' ) ) {
			define( 'DOING_AJAX', true );
		}

		ob_start();
		try {
			$this->admin_order->exprdawc_load_edit_modal_form();
		} catch ( RuntimeException $e ) { // phpcs:ignore
			// Expected.
		}
		$output = ob_get_clean();

		$response = json_decode( $output, true );
		$this->assertFalse( $response['success'] );

		// Cleanup.
		wp_delete_post( $order_id, true );
		wp_delete_post( $product_id, true );
	}

	/**
	 * Tests exprdawc_save_edit_modal_form updates order item.
	 *
	 * Test Goal:
	 * Verifies that order item is updated correctly when form is saved.
	 */
	public function test_save_edit_modal_form_updates_item() {

		$items   = $this->order->get_items();
		$item    = reset( $items );
		$item_id = $item->get_id();

		if ( ! defined( 'DOING_AJAX' ) ) {
			define( 'DOING_AJAX', true );
		}

		$nonce                                = wp_create_nonce( 'wc_exprdawc_edit_exprdawc' );
		$_POST['security']                    = $nonce;
		$_REQUEST['security']                 = $nonce;
		$_POST['item_id']                     = $item_id;
		$_POST['order_id']                    = $this->order_id;
		$_POST['exprdawc_custom_field_input'] = array(
			'test_field' => 'Updated Value',
		);
		$_REQUEST['action']                   = 'woocommerce_edit_exprdawc_order_item';

		ob_start();
		try {
			$this->admin_order->exprdawc_save_edit_modal_form();
		} catch ( RuntimeException $e ) { // phpcs:ignore
			// Expected.
		}
		$output = ob_get_clean();

		$response = json_decode( $output, true );

		$this->assertTrue( $response['success'] );
		$this->assertArrayHasKey( 'html', $response['data'] );
		$this->assertArrayHasKey( 'notes_html', $response['data'] );

		// Verify order item was updated.
		$updated_order = wc_get_order( $this->order_id );
		$updated_items = $updated_order->get_items();
		$updated_item  = reset( $updated_items );

		$this->assertEquals( 'Updated Value', $updated_item->get_meta( 'Test Field' ) );
	}
}
