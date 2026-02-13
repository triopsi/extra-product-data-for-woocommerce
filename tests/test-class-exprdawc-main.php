<?php
/**
 * Class Test_Exprdawc_Main
 *
 * @package Extra_Product_Data_For_WooCommerce
 */

require_once dirname( __DIR__ ) . '/src/classes/class-exprdawc-main.php';

use Triopsi\Exprdawc\Exprdawc_Main;

/**
 * Test class for Exprdawc_Main.
 */
class Test_Exprdawc_Main extends WP_UnitTestCase {

	/**
	 * Instance of the main class.
	 *
	 * @var Exprdawc_Main
	 */
	protected $instance;

	/**
	 * Set up the test environment.
	 */
	public function setUp(): void {
		parent::setUp();
		$this->instance = Exprdawc_Main::get_instance();
	}

	/**
	 * Test singleton instance.
	 */
	public function test_singleton_instance() {
		$this->assertInstanceOf( Exprdawc_Main::class, $this->instance );
		$this->assertSame( $this->instance, Exprdawc_Main::get_instance() );
	}

	/**
	 * Test register_autoloader method.
	 */
	public function test_register_autoloader() {
		// Use reflection to access the protected method
		$reflection = new ReflectionClass( $this->instance );
		$method = $reflection->getMethod( 'register_autoloader' );
		$method->setAccessible( true );
		$method->invoke( $this->instance );

		$this->assertTrue( class_exists( 'Triopsi\Exprdawc\Autoloader' ) );
	}

	/**
	 * Test load_components method.
	 */
	public function test_load_components() {
		$this->instance->load_components();

		$reflection = new ReflectionClass( $this->instance );

		$product_backend = $reflection->getProperty( 'exprdawc_product_backend' );
		$product_backend->setAccessible( true );
		$this->assertNotNull( $product_backend->getValue( $this->instance ) );

		$product_fronted = $reflection->getProperty( 'exprdawc_product_fronted' );
		$product_fronted->setAccessible( true );
		$this->assertNotNull( $product_fronted->getValue( $this->instance ) );

		$admin_order_edit = $reflection->getProperty( 'exprdawc_admin_order_edit' );
		$admin_order_edit->setAccessible( true );
		$this->assertNotNull( $admin_order_edit->getValue( $this->instance ) );

		$settings = $reflection->getProperty( 'exprdawc_settings' );
		$settings->setAccessible( true );
		$this->assertNotNull( $settings->getValue( $this->instance ) );
	}

	/**
	 * Test exprdawc_only_admin_enqueue_scripts method.
	 */
	public function test_exprdawc_only_admin_enqueue_scripts() {
		// Simulate admin environment.
		set_current_screen( 'dashboard' );
		$this->instance->exprdawc_only_admin_enqueue_scripts();
		// Add assertions to verify scripts and styles are enqueued.
        $this->assertTrue( wp_style_is( 'exprdawc-backend-css', 'enqueued' ) );
        $this->assertTrue( wp_style_is( 'form-css', 'enqueued' ) );
	}

    /**
     * Test exprdawc_plugin_action_links method.
     */
    public function test_exprdawc_plugin_action_links() {
        $links = array();
        $modified_links = $this->instance->exprdawc_plugin_action_links( $links );

        $expected_link = '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=products&section=extra_product_data' ) . '">' . __( 'Settings', 'extra-product-data-for-woocommerce' ) . '</a>';

        $this->assertContains( $expected_link, $modified_links );
    }
}
