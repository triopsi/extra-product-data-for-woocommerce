<?php
declare( strict_types=1 );

use Triopsi\Exprdawc\Core\Plugin;

/**
 * Class TestClassExprdawcMain
 *
 * PHPUnit tests for Plugin core class.
 *
 * @package Extra_Product_Data_For_WooCommerce\Tests\Unit
 */
class TestClassExprdawcMain extends WP_UnitTestCase {

	/**
	 * Instance of the main plugin class.
	 *
	 * @var Plugin
	 */
	protected $plugin_instance;

	/**
	 * Set up the test environment.
	 */
	public function setUp(): void {
		parent::setUp();
		$this->plugin_instance = Plugin::getInstance();
	}

	/**
	 * Tests if the singleton instance is returned correctly.
	 */
	public function test_singleton_instance() {
		$this->assertInstanceOf( Plugin::class, $this->plugin_instance );
		$this->assertSame( $this->plugin_instance, Plugin::getInstance() );
	}

	/**
	 * Tests if constructor hooks are registered.
	 */
	public function test_constructor_hooks_registered() {
		$this->assertNotFalse( has_action( 'init', array( $this->plugin_instance, 'loadComponents' ) ) );
		$this->assertNotFalse( has_action( 'admin_enqueue_scripts', array( $this->plugin_instance, 'enqueueAdminAssets' ) ) );
		$this->assertNotFalse( has_filter( 'plugin_action_links_' . EXPRDAWC_BASENAME, array( $this->plugin_instance, 'addPluginActionLinks' ) ) );
	}

	/**
	 * Tests if the components can be loaded.
	 */
	public function test_load_components() {
		$this->plugin_instance->loadComponents();

		$reflection = new ReflectionClass( $this->plugin_instance );

		$product_backend = $reflection->getProperty( 'productBackend' );
		$product_backend->setAccessible( true );
		$this->assertNotNull( $product_backend->getValue( $this->plugin_instance ) );

		$product_frontend = $reflection->getProperty( 'productFrontend' );
		$product_frontend->setAccessible( true );
		$this->assertNotNull( $product_frontend->getValue( $this->plugin_instance ) );

		$admin_order = $reflection->getProperty( 'adminOrder' );
		$admin_order->setAccessible( true );
		$this->assertNotNull( $admin_order->getValue( $this->plugin_instance ) );

		$settings = $reflection->getProperty( 'settings' );
		$settings->setAccessible( true );
		$this->assertNotNull( $settings->getValue( $this->plugin_instance ) );
	}

	/**
	 * Test enqueueAdminAssets method.
	 */
	public function test_exprdawc_only_admin_enqueue_scripts() {
		// Simulate admin environment.
		set_current_screen( 'dashboard' );
		$this->plugin_instance->enqueueAdminAssets();
		// Add assertions to verify scripts and styles are enqueued.
		$this->assertTrue( wp_style_is( 'exprdawc-backend-css', 'enqueued' ) );
		$this->assertTrue( wp_style_is( 'exprdawc-forms-css', 'enqueued' ) );
	}

	/**
	 * Test addPluginActionLinks method.
	 */
	public function test_exprdawc_plugin_action_links() {
		$links          = array();
		$modified_links = $this->plugin_instance->addPluginActionLinks( $links );

		$settings_url  = admin_url( 'admin.php?page=wc-settings&tab=products&section=extra_product_data' );
		$expected_link = '<a href="' . esc_url( $settings_url ) . '">' . esc_html__( 'Settings', 'extra-product-data-for-woocommerce' ) . '</a>';

		$this->assertContains( $expected_link, $modified_links );
		$this->assertCount( 1, $modified_links );
	}
}
