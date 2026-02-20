<?php
declare( strict_types=1 );

require_once dirname( __DIR__, 2 ) . '/../src/classes/utils/class-autoloader.php';

use Triopsi\Exprdawc\Utils\Autoloader;

/**
 * Class TestClassExprdawcAutoloader
 *
 * PHPUnit tests for Autoloader class.
 *
 * @package Extra_Product_Data_For_WooCommerce\Tests\Unit
 */
class TestClassExprdawcAutoloader extends WP_UnitTestCase {

	/**
	 * Temporary directory for autoload tests.
	 *
	 * @var string
	 */
	private $temp_dir;

	/**
	 * Set up the test environment.
	 */
	public function setUp(): void {
		parent::setUp();

		$this->temp_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/exprdawc-autoload-' . uniqid( '', true );
		mkdir( $this->temp_dir, 0777, true );

		Autoloader::setup( $this->temp_dir . DIRECTORY_SEPARATOR, 'My\\Namespace' );
	}

	/**
	 * Tear down the test environment.
	 */
	public function tearDown(): void {
		$this->delete_directory( $this->temp_dir );

		parent::tearDown();
	}

	/**
	 * Test autoloading a class from the base namespace directory.
	 */
	public function test_autoloads_root_class(): void {
		$class_name = 'My\\Namespace\\Root_Class';
		$file_path  = $this->temp_dir . DIRECTORY_SEPARATOR . 'class-root-class.php';

		file_put_contents(
			$file_path,
			"<?php\nnamespace My\\Namespace;\nclass Root_Class {}\n"
		);

		$this->assertTrue( class_exists( $class_name ) );
	}

	/**
	 * Test autoloading a class from a nested namespace directory.
	 */
	public function test_autoloads_nested_class(): void {
		$class_name = 'My\\Namespace\\Foo\\Bar_Baz';
		$dir_path   = $this->temp_dir . DIRECTORY_SEPARATOR . 'foo';
		$file_path  = $dir_path . DIRECTORY_SEPARATOR . 'class-bar-baz.php';

		mkdir( $dir_path, 0777, true );
		file_put_contents(
			$file_path,
			"<?php\nnamespace My\\Namespace\\Foo;\nclass Bar_Baz {}\n"
		);

		$this->assertTrue( class_exists( $class_name ) );
	}

	/**
	 * Recursively delete a directory.
	 *
	 * @param string $dir Directory path.
	 *
	 * @return void
	 */
	private function delete_directory( string $dir ): void {
		if ( ! is_dir( $dir ) ) {
			return;
		}

		$items = scandir( $dir );
		if ( false === $items ) {
			return;
		}

		foreach ( $items as $item ) {
			if ( '.' === $item || '..' === $item ) {
				continue;
			}

			$path = $dir . DIRECTORY_SEPARATOR . $item;
			if ( is_dir( $path ) ) {
				$this->delete_directory( $path );
			} else {
				unlink( $path );
			}
		}

		rmdir( $dir );
	}
}
