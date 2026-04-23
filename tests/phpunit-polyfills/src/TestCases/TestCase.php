<?php

namespace Yoast\PHPUnitPolyfills\TestCases;

use PHPUnit\Framework\TestCase as PHPUnit_TestCase;
use Yoast\PHPUnitPolyfills\Polyfills\AssertArrayWithListKeys;
use Yoast\PHPUnitPolyfills\Polyfills\AssertClosedResource;
use Yoast\PHPUnitPolyfills\Polyfills\AssertContainsOnly;
use Yoast\PHPUnitPolyfills\Polyfills\AssertFileEqualsSpecializations;
use Yoast\PHPUnitPolyfills\Polyfills\AssertIgnoringLineEndings;
use Yoast\PHPUnitPolyfills\Polyfills\AssertionRenames;
use Yoast\PHPUnitPolyfills\Polyfills\AssertIsList;
use Yoast\PHPUnitPolyfills\Polyfills\AssertObjectEquals;
use Yoast\PHPUnitPolyfills\Polyfills\AssertObjectNotEquals;
use Yoast\PHPUnitPolyfills\Polyfills\AssertObjectProperty;
use Yoast\PHPUnitPolyfills\Polyfills\EqualToSpecializations;
use Yoast\PHPUnitPolyfills\Polyfills\ExpectExceptionMessageMatches;
use Yoast\PHPUnitPolyfills\Polyfills\ExpectUserDeprecation;

abstract class TestCase extends PHPUnit_TestCase {
	use AssertArrayWithListKeys;
	use AssertClosedResource;
	use AssertContainsOnly;
	use AssertFileEqualsSpecializations;
	use AssertIgnoringLineEndings;
	use AssertionRenames;
	use AssertIsList;
	use AssertObjectEquals;
	use AssertObjectNotEquals;
	use AssertObjectProperty;
	use EqualToSpecializations;
	use ExpectExceptionMessageMatches;
	use ExpectUserDeprecation;

	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();
		static::set_up_before_class();
	}

	protected function setUp(): void {
		parent::setUp();
		$this->set_up();
	}

	protected function assertPreConditions(): void {
		parent::assertPreConditions();
		$this->assert_pre_conditions();
	}

	protected function assertPostConditions(): void {
		parent::assertPostConditions();
		$this->assert_post_conditions();
	}

	protected function tearDown(): void {
		$this->tear_down();
		parent::tearDown();
	}

	public static function tearDownAfterClass(): void {
		static::tear_down_after_class();
		parent::tearDownAfterClass();
	}

	public static function set_up_before_class() {}

	protected function set_up() {}

	protected function assert_pre_conditions() {}

	protected function assert_post_conditions() {}

	protected function tear_down() {}

	public static function tear_down_after_class() {}
}