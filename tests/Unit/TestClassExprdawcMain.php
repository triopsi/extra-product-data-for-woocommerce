<?php
declare( strict_types=1 );

require_once dirname( __DIR__ ) . '/../src/classes/class-exprdawc-main.php';

use Triopsi\Exprdawc\Exprdawc_Main;

/**
 * Class TestClassExprdawcMain
 *
 * PHPUnit tests for Exprdawc_Main class.
 *
 * @package Extra_Product_Data_For_WooCommerce\Tests\Unit
 */
class TestClassExprdawcMain extends WP_UnitTestCase {

	/**
	 * Instance of the main class.
	 *
	 * @var Exprdawc_Main
	 */
	protected $exprdawc_main_instance;

	/**
	 * Set up the test environment.
	 */
	public function setUp(): void {
		parent::setUp();
		$this->exprdawc_main_instance = Exprdawc_Main::get_instance();
	}

	/**
	 * Tests if the singleton instance is returned correctly.
	 */
	public function test_singleton_instance() {
		$this->assertInstanceOf( Exprdawc_Main::class, $this->exprdawc_main_instance );
		$this->assertSame( $this->exprdawc_main_instance, Exprdawc_Main::get_instance() );
	}

	/**
	 * Tests if the autoloader is registered correctly.
	 */
	public function test_register_autoloader() {
		// Use reflection to access the protected method.
		$reflection = new ReflectionClass( $this->exprdawc_main_instance );
		$method     = $reflection->getMethod( 'register_autoloader' );
		$method->setAccessible( true );
		$method->invoke( $this->exprdawc_main_instance );

		$this->assertTrue( class_exists( 'Triopsi\Exprdawc\Autoloader' ) );
	}

	/**
	 * Tests if the components can be loaded.
	 */
	public function test_load_components() {
		$this->exprdawc_main_instance->load_components();

		$reflection = new ReflectionClass( $this->exprdawc_main_instance );

		$product_backend = $reflection->getProperty( 'exprdawc_product_backend' );
		$product_backend->setAccessible( true );
		$this->assertNotNull( $product_backend->getValue( $this->exprdawc_main_instance ) );

		$product_fronted = $reflection->getProperty( 'exprdawc_product_fronted' );
		$product_fronted->setAccessible( true );
		$this->assertNotNull( $product_fronted->getValue( $this->exprdawc_main_instance ) );

		$admin_order_edit = $reflection->getProperty( 'exprdawc_admin_order_edit' );
		$admin_order_edit->setAccessible( true );
		$this->assertNotNull( $admin_order_edit->getValue( $this->exprdawc_main_instance ) );

		$settings = $reflection->getProperty( 'exprdawc_settings' );
		$settings->setAccessible( true );
		$this->assertNotNull( $settings->getValue( $this->exprdawc_main_instance ) );
	}

	/**
	 * Test exprdawc_only_admin_enqueue_scripts method.
	 */
	public function test_exprdawc_only_admin_enqueue_scripts() {
		// Simulate admin environment.
		set_current_screen( 'dashboard' );
		$this->exprdawc_main_instance->exprdawc_only_admin_enqueue_scripts();
		// Add assertions to verify scripts and styles are enqueued.
		$this->assertTrue( wp_style_is( 'exprdawc-backend-css', 'enqueued' ) );
		$this->assertTrue( wp_style_is( 'form-css', 'enqueued' ) );
	}

	/**
	 * Test exprdawc_plugin_action_links method.
	 */
	public function test_exprdawc_plugin_action_links() {
		$links          = array();
		$modified_links = $this->exprdawc_main_instance->exprdawc_plugin_action_links( $links );

		$expected_link = '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=products&section=extra_product_data' ) . '">' . __( 'Settings', 'extra-product-data-for-woocommerce' ) . '</a>';

		$this->assertContains( $expected_link, $modified_links );
	}
}
