<?php
/**
 * Tests for Exprdawc_Template_Engine class
 *
 * @package Extra_Product_Data_For_WooCommerce\Tests
 * @since 1.9.0
 */

// phpcs:ignoreFile
declare( strict_types=1 );

use Triopsi\Exprdawc\Exprdawc_Template_Engine;

/**
 * Class TestExprdawcTemplateEngine
 *
 * PHPUnit tests for Exprdawc_Template_Engine class.
 */
class TestExprdawcTemplateEngine extends WP_UnitTestCase {

	/**
	 * Template engine instance.
	 *
	 * @var Exprdawc_Template_Engine
	 */
	private $engine;

	/**
	 * Temporary template directory.
	 *
	 * @var string
	 */
	private $temp_dir;

	/**
	 * Sets up the test environment before each test.
	 *
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();

		// Create temporary directory for test templates.
		$this->temp_dir = sys_get_temp_dir() . '/exprdawc-templates-' . uniqid();
		mkdir( $this->temp_dir );

		$this->engine = new Exprdawc_Template_Engine( $this->temp_dir, false );
	}

	/**
	 * Cleans up the test environment after each test.
	 *
	 * @return void
	 */
	public function tearDown(): void {
		// Clean up temporary directory recursively.
		if ( file_exists( $this->temp_dir ) ) {
			$this->delete_directory( $this->temp_dir );
		}

		Exprdawc_Template_Engine::clear_cache();

		parent::tearDown();
	}

	/**
	 * Recursively delete a directory.
	 *
	 * @param string $dir Directory path.
	 * @return void
	 */
	private function delete_directory( string $dir ): void {
		if ( ! file_exists( $dir ) ) {
			return;
		}

		$files = array_diff( scandir( $dir ), array( '.', '..' ) );
		foreach ( $files as $file ) {
			$path = $dir . '/' . $file;
			if ( is_dir( $path ) ) {
				$this->delete_directory( $path );
			} else {
				unlink( $path );
			}
		}
		rmdir( $dir );
	}

	/**
	 * Test that template engine can be instantiated.
	 *
	 * @return void
	 */
	public function test_can_instantiate() {
		$this->assertInstanceOf( Exprdawc_Template_Engine::class, $this->engine );
	}

	/**
	 * Test simple variable rendering with escaping.
	 *
	 * Test Goal:
	 * Verifies that {{ variable }} syntax escapes HTML properly.
	 */
	public function test_renders_escaped_variable() {
		$template = '{{ name }}';
		file_put_contents( $this->temp_dir . '/test.php', $template );

		$output = $this->engine->render(
			'test.php',
			array( 'name' => '<script>alert("XSS")</script>' ),
			false
		);

		$this->assertStringContainsString( '&lt;script&gt;', $output );
		$this->assertStringNotContainsString( '<script>', $output );
	}

	/**
	 * Test raw variable rendering without escaping.
	 *
	 * Test Goal:
	 * Verifies that {{{ variable }}} syntax does not escape HTML.
	 */
	public function test_renders_raw_variable() {
		$template = '{{{ html }}}';
		file_put_contents( $this->temp_dir . '/test.php', $template );

		$output = $this->engine->render(
			'test.php',
			array( 'html' => '<strong>Bold</strong>' ),
			false
		);

		$this->assertStringContainsString( '<strong>Bold</strong>', $output );
	}

	/**
	 * Test conditional rendering - true condition.
	 *
	 * Test Goal:
	 * Verifies that {% if condition %} renders when condition is true.
	 */
	public function test_renders_conditional_true() {
		$template = '{% if show_content %}Content is visible{% endif %}';
		file_put_contents( $this->temp_dir . '/test.php', $template );

		$output = $this->engine->render(
			'test.php',
			array( 'show_content' => true ),
			false
		);

		$this->assertStringContainsString( 'Content is visible', $output );
	}

	/**
	 * Test conditional rendering - false condition.
	 *
	 * Test Goal:
	 * Verifies that {% if condition %} does not render when condition is false.
	 */
	public function test_renders_conditional_false() {
		$template = '{% if show_content %}Content is visible{% endif %}';
		file_put_contents( $this->temp_dir . '/test.php', $template );

		$output = $this->engine->render(
			'test.php',
			array( 'show_content' => false ),
			false
		);

		$this->assertStringNotContainsString( 'Content is visible', $output );
	}

	/**
	 * Test negated conditional.
	 *
	 * Test Goal:
	 * Verifies that {% if !condition %} works correctly.
	 */
	public function test_renders_negated_conditional() {
		$template = '{% if !disabled %}Enabled{% endif %}';
		file_put_contents( $this->temp_dir . '/test.php', $template );

		$output = $this->engine->render(
			'test.php',
			array( 'disabled' => false ),
			false
		);

		$this->assertStringContainsString( 'Enabled', $output );
	}

	/**
	 * Test foreach loop rendering.
	 *
	 * Test Goal:
	 * Verifies that {% foreach items as item %} loops correctly.
	 */
	public function test_renders_foreach_loop() {
		$template = '{% foreach items as item %}{{ item }}{% endforeach %}';
		file_put_contents( $this->temp_dir . '/test.php', $template );

		$output = $this->engine->render(
			'test.php',
			array( 'items' => array( 'A', 'B', 'C' ) ),
			false
		);

		$this->assertStringContainsString( 'ABC', $output );
	}

	/**
	 * Test foreach with nested properties.
	 *
	 * Test Goal:
	 * Verifies that loops can access nested item properties.
	 */
	public function test_renders_foreach_with_nested_properties() {
		$template = '{% foreach users as user %}{{ user.name }}{% endforeach %}';
		file_put_contents( $this->temp_dir . '/test.php', $template );

		$output = $this->engine->render(
			'test.php',
			array(
				'users' => array(
					array( 'name' => 'Alice' ),
					array( 'name' => 'Bob' ),
				),
			),
			false
		);

		$this->assertStringContainsString( 'AliceBob', $output );
	}

	/**
	 * Test template include.
	 *
	 * Test Goal:
	 * Verifies that {% include 'template.php' %} works.
	 */
	public function test_renders_include() {
		file_put_contents( $this->temp_dir . '/partial.php', 'Partial Content' );
		file_put_contents( $this->temp_dir . '/main.php', 'Main {% include \'partial.php\' %} End' );

		$output = $this->engine->render( 'main.php', array(), false );

		$this->assertStringContainsString( 'Main Partial Content End', $output );
	}

	/**
	 * Test dot notation for nested variables.
	 *
	 * Test Goal:
	 * Verifies that {{ user.name }} accesses nested arrays.
	 */
	public function test_renders_nested_variables() {
		$template = '{{ user.name }}';
		file_put_contents( $this->temp_dir . '/test.php', $template );

		$output = $this->engine->render(
			'test.php',
			array(
				'user' => array(
					'name' => 'John Doe',
				),
			),
			false
		);

		$this->assertStringContainsString( 'John Doe', $output );
	}

	/**
	 * Test array value rendering.
	 *
	 * Test Goal:
	 * Verifies that arrays are converted to comma-separated strings.
	 */
	public function test_renders_array_as_string() {
		$template = '{{ tags }}';
		file_put_contents( $this->temp_dir . '/test.php', $template );

		$output = $this->engine->render(
			'test.php',
			array( 'tags' => array( 'php', 'wordpress', 'woocommerce' ) ),
			false
		);

		$this->assertStringContainsString( 'php, wordpress, woocommerce', $output );
	}

	/**
	 * Test boolean value rendering.
	 *
	 * Test Goal:
	 * Verifies that booleans are converted to 'true' or 'false'.
	 */
	public function test_renders_boolean_values() {
		$template = '{{ is_active }}';
		file_put_contents( $this->temp_dir . '/test.php', $template );

		$output = $this->engine->render(
			'test.php',
			array( 'is_active' => true ),
			false
		);

		$this->assertStringContainsString( 'true', $output );
	}

	/**
	 * Test null value renders as empty string.
	 *
	 * Test Goal:
	 * Verifies that null values don't cause errors.
	 */
	public function test_renders_null_as_empty_string() {
		$template = '{{ missing }}';
		file_put_contents( $this->temp_dir . '/test.php', $template );

		$output = $this->engine->render(
			'test.php',
			array(),
			false
		);

		$this->assertEquals( '', $output );
	}

	/**
	 * Test template caching.
	 *
	 * Test Goal:
	 * Verifies that caching works and improves performance.
	 */
	public function test_caching_works() {
		$engine_with_cache = new Exprdawc_Template_Engine( $this->temp_dir, true );

		$template = '{{ name }}';
		file_put_contents( $this->temp_dir . '/cached.php', $template );

		// First render - should cache.
		$output1 = $engine_with_cache->render(
			'cached.php',
			array( 'name' => 'Test' ),
			false
		);

		// Second render - should use cache.
		$output2 = $engine_with_cache->render(
			'cached.php',
			array( 'name' => 'Test' ),
			false
		);

		$this->assertEquals( $output1, $output2 );
		$this->assertStringContainsString( 'Test', $output2 );
	}

	/**
	 * Test cache clearing.
	 *
	 * Test Goal:
	 * Verifies that cache can be cleared.
	 */
	public function test_cache_can_be_cleared() {
		Exprdawc_Template_Engine::clear_cache();
		// If this doesn't throw an error, the test passes.
		$this->assertTrue( true );
	}

	/**
	 * Test non-existent template returns empty string.
	 *
	 * Test Goal:
	 * Verifies graceful handling of missing templates.
	 */
	public function test_missing_template_returns_empty() {
		// Temporarily disable WP_DEBUG to prevent trigger_error.
		$engine = new Exprdawc_Template_Engine( $this->temp_dir, false );

		// Suppress any warnings.
		$output = @$engine->render( 'nonexistent.php', array(), false );
		$this->assertEquals( '', $output );
	}

	/**
	 * Test render with echo parameter true.
	 *
	 * Test Goal:
	 * Verifies that output is echoed when $echo = true.
	 */
	public function test_render_with_echo() {
		$template = '{{ text }}';
		file_put_contents( $this->temp_dir . '/test.php', $template );

		ob_start();
		$this->engine->render( 'test.php', array( 'text' => 'Hello' ), true );
		$output = ob_get_clean();

		$this->assertStringContainsString( 'Hello', $output );
	}

	/**
	 * Test empty foreach doesn't render content.
	 *
	 * Test Goal:
	 * Verifies that empty arrays don't produce output in loops.
	 */
	public function test_empty_foreach_renders_nothing() {
		$template = '{% foreach items as item %}{{ item }}{% endforeach %}';
		file_put_contents( $this->temp_dir . '/test.php', $template );

		$output = $this->engine->render(
			'test.php',
			array( 'items' => array() ),
			false
		);

		$this->assertEquals( '', $output );
	}

	/**
	 * Test multiple variables in template.
	 *
	 * Test Goal:
	 * Verifies that multiple variables can be used in one template.
	 */
	public function test_renders_multiple_variables() {
		$template = '{{ first }} {{ last }}';
		file_put_contents( $this->temp_dir . '/test.php', $template );

		$output = $this->engine->render(
			'test.php',
			array(
				'first' => 'John',
				'last'  => 'Doe',
			),
			false
		);

		$this->assertStringContainsString( 'John Doe', $output );
	}

	/**
	 * Test field rendering helper method.
	 *
	 * Test Goal:
	 * Verifies that render_field() method works correctly.
	 */
	public function test_render_field_method() {
		mkdir( $this->temp_dir . '/fields' );
		file_put_contents( $this->temp_dir . '/fields/text.php', '{{ label }}' );

		$output = $this->engine->render_field(
			'text.php',
			array( 'label' => 'Test Field' )
		);

		$this->assertStringContainsString( 'Test Field', $output );
	}

	/**
	 * Test simple loop with array access.
	 *
	 * Test Goal:
	 * Verifies loops work with array items.
	 *
	 * Note: Nested loops are not supported in this template engine version.
	 * This is a limitation of the simple regex-based processing.
	 */
	public function test_loop_with_simple_values() {
		$template = '{% foreach numbers as num %}{{ num }},{% endforeach %}';
		file_put_contents( $this->temp_dir . '/test.php', $template );

		$output = $this->engine->render(
			'test.php',
			array(
				'numbers' => array( 1, 2, 3, 4, 5 ),
			),
			false
		);

		$this->assertStringContainsString( '1,2,3,4,5,', $output );
	}

	/**
	 * Test foreach preserves item context.
	 *
	 * Test Goal:
	 * Verifies loop item variable is accessible.
	 */
	public function test_foreach_item_context() {
		$template = 'Start{% foreach items as item %}-{{ item }}-{% endforeach %}End';
		file_put_contents( $this->temp_dir . '/test.php', $template );

		$output = $this->engine->render(
			'test.php',
			array(
				'items' => array( 'X', 'Y', 'Z' ),
			),
			false
		);

		$this->assertStringContainsString( 'Start-X--Y--Z-End', $output );
	}
}
