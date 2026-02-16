<?php
declare( strict_types=1 );

use Triopsi\Exprdawc\Exprdawc_User_Order;

/**
 * Class Tests_Exprdawc_User_Order
 *
 * PHPUnit tests for Exprdawc_User_Order class.
 *
 * @group exprdawc
 *
 * @package Extra_Product_Data_For_WooCommerce\Tests\Unit
 */
class TestExprdawcUserOrder extends WP_UnitTestCase {


	/**
	 * Sets up the test environment before each test.
	 *
	 * Expects: WooCommerce account page and endpoint are simulated, and option is set.
	 *
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();

		// Simulate WooCommerce account page.
		add_filter( 'woocommerce_is_account_page', '__return_true' );

		// Simulate WC endpoint "view-order".
		// is_wc_endpoint_url('view-order') checks Query Vars.
		if ( ! isset( $GLOBALS['wp'] ) ) {
			$GLOBALS['wp'] = new WP();
		}
		$GLOBALS['wp']->query_vars['view-order'] = 1;

		if ( isset( $GLOBALS['wp_query'] ) && is_object( $GLOBALS['wp_query'] ) ) {
			$GLOBALS['wp_query']->query_vars['view-order'] = 1;
		}

		// Option (Default would be 'processing' anyway, but explicit is cleaner).
		update_option( 'extra_product_data_max_order_status', 'processing' );
	}

	/**
	 * Cleans up the test environment after each test.
	 *
	 * Expects: All filters and globals are reset to avoid test pollution.
	 *
	 * @return void
	 */
	public function tearDown(): void {
		remove_all_filters( 'woocommerce_is_account_page' );
		unset( $GLOBALS['wp']->query_vars['view-order'] );
		if ( isset( $GLOBALS['wp_query'] ) && is_object( $GLOBALS['wp_query'] ) ) {
			unset( $GLOBALS['wp_query']->query_vars['view-order'] );
		}
		parent::tearDown();
	}

	/**
	 * Tests that enqueue_scripts() enqueues the correct styles and scripts on the account page.
	 *
	 * Expects: 'form-css', 'order-frontend-css' styles and 'exprdawc-user-order' script are enqueued.
	 *
	 * @covers Exprdawc_User_Order::enqueue_scripts
	 */
	public function test_enqueue_scripts_enqueues_assets() {

		$plugin = new \Triopsi\Exprdawc\Exprdawc_User_Order();
		$plugin->enqueue_scripts();

		// Check styles.
		$this->assertTrue(
			wp_style_is( 'form-css', 'enqueued' ),
			'form-css sollte enqueued sein'
		);

		$this->assertTrue(
			wp_style_is( 'order-frontend-css', 'enqueued' ),
			'order-frontend-css sollte enqueued sein'
		);

		// Check styles.
		$this->assertTrue(
			wp_script_is( 'exprdawc-user-order', 'enqueued' ),
			'exprdawc-user-order sollte enqueued sein'
		);
	}

	/**
	 * Tests that enqueue_scripts() localizes the script with the correct data.
	 *
	 * Expects: The localization object and ajax_url are present in the script data.
	 *
	 * @covers Exprdawc_User_Order::enqueue_scripts
	 */
	public function test_enqueue_scripts_localizes_script() {

		$plugin = new \Triopsi\Exprdawc\Exprdawc_User_Order();
		$plugin->enqueue_scripts();

		global $wp_scripts;

		$data = $wp_scripts->get_data( 'exprdawc-user-order', 'data' );

		$this->assertStringContainsString(
			'exprdawc_user_order',
			$data,
			'Localization object sollte existieren'
		);

		$this->assertStringContainsString(
			'ajax_url',
			$data,
			'ajax_url sollte lokalisiert sein'
		);
	}

	/**
	 * Tests that the Exprdawc_User_Order class can be instantiated.
	 *
	 * Expects: The created object is an instance of Exprdawc_User_Order.
	 *
	 * @covers Exprdawc_User_Order::__construct
	 */
	public function test_can_instantiate() {
		$user_order = new Exprdawc_User_Order();
		$this->assertInstanceOf( Exprdawc_User_Order::class, $user_order );
	}

	/**
	 * Helper method to create a simple product with custom fields.
	 *
	 * @param array $custom_fields The custom fields to add to the product.
	 * @return WC_Product The created product.
	 */
	private function create_simple_product_with_custom_fields( array $custom_fields ): WC_Product {
		$product = new WC_Product_Simple();
		$product->set_name( 'Test Product' );
		$product->set_regular_price( '10' );
		$product->set_meta_data(
			array(
				array(
					'key'   => '_extra_product_fields',
					'value' => $custom_fields,
				),
			)
		);
		$product->save();

		return wc_get_product( $product->get_id() );
	}

	/**
	 * Helper method to create an order with a single item for a given product and item meta.
	 *
	 * @param WC_Product $product The product to add to the order.
	 * @param array      $item_meta The meta data to add to the order item.
	 * @return array An array containing the created order, order item, and item ID.
	 */
	private function create_order_with_item( WC_Product $product, array $item_meta = array() ): array {
		$order = wc_create_order();
		$order->set_status( 'processing' );
		$order->save();

		$item_id = $order->add_product( $product, 1 );
		$items   = $order->get_items();
		$item    = $items[ $item_id ];

		foreach ( $item_meta as $key => $value ) {
			$item->add_meta_data( $key, $value, true );
		}
		$item->save();
		$order->save();

		return array( $order, $item, $item_id );
	}

	/**
	 * Tests that no output is generated if there are no custom fields.
	 *
	 * Expects: The output is empty if _extra_product_fields is not set.
	 *
	 * @covers Exprdawc_User_Order::add_edit_button_to_order_item
	 */
	public function test_no_custom_fields_outputs_nothing() {
		$product                        = $this->create_simple_product_with_custom_fields( array() );
		list( $order, $item, $item_id ) = $this->create_order_with_item( $product );

		$sut = new \Triopsi\Exprdawc\Exprdawc_User_Order();

		ob_start();
		$sut->add_edit_button_to_order_item( $item_id, $item, $order );
		$html = ob_get_clean();

		$this->assertSame( '', trim( $html ), 'Ohne _extra_product_fields sollte nichts ausgegeben werden.' );
	}

	/**
	 * Tests that no edit UI is rendered if there are custom fields but no matching item meta.
	 *
	 * Expects: The output is empty and no edit button is present if item meta does not match custom field label.
	 *
	 * @covers Exprdawc_User_Order::add_edit_button_to_order_item
	 */
	public function test_custom_fields_but_no_matching_item_meta_outputs_nothing() {
		$custom_fields = array(
			array(
				'label' => 'Ticket Name',
				'type'  => 'text',
			),
		);

		$product = $this->create_simple_product_with_custom_fields( $custom_fields );
		// Item meta hat NICHT den Key "Ticket Name" → has_user_inputs bleibt false.
		list( $order, $item, $item_id ) = $this->create_order_with_item(
			$product,
			array(
				'Something Else' => 'abc',
			)
		);

		$sut = new \Triopsi\Exprdawc\Exprdawc_User_Order();

		ob_start();
		$sut->add_edit_button_to_order_item( $item_id, $item, $order );
		$html = ob_get_clean();

		$this->assertStringNotContainsString( 'exprdawc-edit-order-item', $html );
		$this->assertSame( '', trim( $html ), 'Ohne passende Item-Metas sollte kein Edit-UI erscheinen.' );
	}

	/**
	 * Tests that the edit UI is rendered if endpoint, status, and item meta match.
	 *
	 * Expects: The edit button, form, and save button are present in the output if all conditions are met.
	 *
	 * @covers Exprdawc_User_Order::add_edit_button_to_order_item
	 */
	public function test_when_endpoint_status_and_meta_match_outputs_edit_ui() {
		$custom_fields = array(
			array(
				'label' => 'Ticket Name',
				'type'  => 'text',
			),
		);

		$product = $this->create_simple_product_with_custom_fields( $custom_fields );

		// Item meta Key muss exakt zum Label passen (case-insensitive wird geprüft).
		list( $order, $item, $item_id ) = $this->create_order_with_item(
			$product,
			array(
				'Ticket Name' => 'Daniel',
			)
		);

		$sut = new \Triopsi\Exprdawc\Exprdawc_User_Order();

		ob_start();
		$sut->add_edit_button_to_order_item( $item_id, $item, $order );
		$html = ob_get_clean();

		// Core UI present?
		$this->assertStringContainsString( 'exprdawc-edit-order-item', $html, 'Edit-Button CSS-Klasse fehlt.' );
		$this->assertStringContainsString( 'data-item-id="' . $item_id . '"', $html, 'data-item-id fehlt.' );

		// Form and save button present?
		$this->assertStringContainsString( 'exprdawc-order-item-form', $html );
		$this->assertStringContainsString( 'name="order_id"', $html );
		$this->assertStringContainsString( 'value="' . $order->get_id() . '"', $html );
		$this->assertStringContainsString( 'exprdawc-save-order-item', $html );
	}
}
