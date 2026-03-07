<?php
/**
 * Template Engine
 *
 * @package ExtraProductDataForWooCommerce
 * @author Daniel Drevermann <info@triopsi.com>
 * @copyright Copyright (c) 2024, IT-Dienstleistungen Drevermann
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 */

declare(strict_types=1);

namespace Triopsi\Exprdawc\Template;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Template Engine
 *
 * Handles template rendering with variable interpolation and control structures.
 */
class TemplateEngine {

	/**
	 * Template base path.
	 *
	 * @var string
	 */
	private $template_path;

	/**
	 * Template variables.
	 *
	 * @var array
	 */
	private $variables = array();

	/**
	 * Template cache.
	 *
	 * @var array
	 */
	private static $cache = array();

	/**
	 * Enable/disable caching.
	 *
	 * @var bool
	 */
	private $cache_enabled = true;

	/**
	 * Constructor.
	 *
	 * @param string $template_path Base path for templates.
	 * @param bool   $cache_enabled Enable template caching.
	 */
	public function __construct( string $template_path, bool $cache_enabled = true ) {
		$this->template_path = trailingslashit( $template_path );
		$this->cache_enabled = $cache_enabled;
	}

	/**
	 * Render a template with variables.
	 *
	 * @param string $template_name Template filename (relative to template_path).
	 * @param array  $variables Variables to pass to template.
	 * @param bool   $should_echo Whether to echo or return output.
	 *
	 * @return string|void HTML output or void if echoed.
	 */
	public function render( string $template_name, array $variables = array(), bool $should_echo = true ) {
		$this->variables = $variables;

		$template_file = $this->get_template_path( $template_name );

		if ( ! file_exists( $template_file ) ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_trigger_error
				trigger_error( sprintf( 'Template not found: %s', esc_html( $template_file ) ), E_USER_WARNING );
			}
			return $should_echo ? '' : '';
		}

		$output = $this->process_template( $template_file );

		if ( $should_echo ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $output;
			return;
		}

		return $output;
	}

	/**
	 * Process template file and return rendered output.
	 *
	 * @param string $template_file Full path to template file.
	 *
	 * @return string Rendered HTML.
	 */
	private function process_template( string $template_file ): string {
		$cache_key = md5( $template_file . wp_json_encode( $this->variables ) );

		if ( $this->cache_enabled && isset( self::$cache[ $cache_key ] ) ) {
			return self::$cache[ $cache_key ];
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$content = file_get_contents( $template_file );

		if ( false === $content ) {
			return '';
		}

		$content = $this->process_includes( $content );
		$content = $this->process_conditionals( $content );
		$content = $this->process_loops( $content );
		$content = $this->process_variables( $content );

		if ( $this->cache_enabled ) {
			self::$cache[ $cache_key ] = $content;
		}

		return $content;
	}

	/**
	 * Process {% include 'template.php' %} directives.
	 *
	 * @param string $content Template content.
	 *
	 * @return string Processed content.
	 */
	private function process_includes( string $content ): string {
		$pattern = '/{%\s*include\s+[\'\"]([^\'\"]+)[\'\"]\s*%}/';

		return preg_replace_callback(
			$pattern,
			function ( $matches ) {
				$include_template = $matches[1];
				$include_path     = $this->get_template_path( $include_template );

				if ( file_exists( $include_path ) ) {
					return $this->process_template( $include_path );
				}

				return '';
			},
			$content
		);
	}

	/**
	 * Process {% if condition %}...{% endif %} conditionals.
	 *
	 * @param string $content Template content.
	 *
	 * @return string Processed content.
	 */
	private function process_conditionals( string $content ): string {
		$pattern = '/{%\s*if\s+([^%]+)\s*%}(.*?){%\s*endif\s*%}/s';

		return preg_replace_callback(
			$pattern,
			function ( $matches ) {
				$condition = trim( $matches[1] );
				$block     = $matches[2];

				if ( $this->evaluate_condition( $condition ) ) {
					return $block;
				}

				return '';
			},
			$content
		);
	}

	/**
	 * Process {% foreach items as item %}...{% endforeach %} loops.
	 *
	 * @param string $content Template content.
	 *
	 * @return string Processed content.
	 */
	private function process_loops( string $content ): string {
		$pattern = '/{%\s*foreach\s+(\w+)\s+as\s+(\w+)\s*%}(.*?){%\s*endforeach\s*%}/s';

		return preg_replace_callback(
			$pattern,
			function ( $matches ) {
				$array_var = $matches[1];
				$item_var  = $matches[2];
				$block     = $matches[3];

				$items = $this->get_variable( $array_var );

				if ( ! is_array( $items ) ) {
					return '';
				}

				$output = '';
				foreach ( $items as $item ) {
					$original_value               = $this->variables[ $item_var ] ?? null;
					$this->variables[ $item_var ] = $item;

					$rendered = $this->process_variables( $block );
					$rendered = $this->process_conditionals( $rendered );

					$output .= $rendered;

					if ( null === $original_value ) {
						unset( $this->variables[ $item_var ] );
					} else {
						$this->variables[ $item_var ] = $original_value;
					}
				}

				return $output;
			},
			$content
		);
	}

	/**
	 * Process {{ variable }} and {{{ raw_variable }}} interpolations.
	 *
	 * @param string $content Template content.
	 *
	 * @return string Processed content.
	 */
	private function process_variables( string $content ): string {
		$content = preg_replace_callback(
			'/\{\{\{\s*([^\}]+)\s*\}\}\}/',
			function ( $matches ) {
				$var_path = trim( $matches[1] );
				return $this->get_variable_value( $var_path, false );
			},
			$content
		);

		$content = preg_replace_callback(
			'/\{\{\s*([^\}]+)\s*\}\}/',
			function ( $matches ) {
				$var_path = trim( $matches[1] );
				return $this->get_variable_value( $var_path, true );
			},
			$content
		);

		return $content;
	}

	/**
	 * Get variable value with optional escaping.
	 *
	 * @param string $var_path Variable path.
	 * @param bool   $escape Whether to escape the value.
	 *
	 * @return string Variable value.
	 */
	private function get_variable_value( string $var_path, bool $escape = true ): string {
		$value = $this->get_variable( $var_path );

		if ( null === $value ) {
			return '';
		}

		if ( is_array( $value ) ) {
			$value = implode( ', ', $value );
		}

		if ( is_bool( $value ) ) {
			$value = $value ? 'true' : 'false';
		}

		$value = (string) $value;

		if ( $escape ) {
			return $this->auto_escape( $value );
		}

		return $value;
	}

	/**
	 * Get variable by path (supports dot notation).
	 *
	 * @param string $path Variable path.
	 *
	 * @return mixed
	 */
	private function get_variable( string $path ) {
		$parts = explode( '.', $path );
		$value = $this->variables;

		foreach ( $parts as $part ) {
			if ( is_array( $value ) && isset( $value[ $part ] ) ) {
				$value = $value[ $part ];
			} else {
				return null;
			}
		}

		return $value;
	}

	/**
	 * Evaluate a condition.
	 *
	 * @param string $condition Condition to evaluate.
	 *
	 * @return bool
	 */
	private function evaluate_condition( string $condition ): bool {
		if ( '!' === substr( $condition, 0, 1 ) ) {
			$var = trim( substr( $condition, 1 ) );
			return ! $this->is_truthy( $this->get_variable( $var ) );
		}

		$value = $this->get_variable( $condition );

		return $this->is_truthy( $value );
	}

	/**
	 * Check if value is truthy.
	 *
	 * @param mixed $value Value to check.
	 *
	 * @return bool
	 */
	private function is_truthy( $value ): bool {
		if ( null === $value ) {
			return false;
		}

		if ( is_bool( $value ) ) {
			return $value;
		}

		if ( is_numeric( $value ) ) {
			return 0 !== (int) $value;
		}

		if ( is_string( $value ) ) {
			return '' !== $value;
		}

		if ( is_array( $value ) ) {
			return count( $value ) > 0;
		}

		return true;
	}

	/**
	 * Auto-escape value based on context.
	 *
	 * @param string $value Value to escape.
	 *
	 * @return string
	 */
	private function auto_escape( string $value ): string {
		return esc_html( $value );
	}

	/**
	 * Get full template path.
	 *
	 * @param string $template_name Template filename.
	 *
	 * @return string
	 */
	private function get_template_path( string $template_name ): string {
		return $this->template_path . $template_name;
	}

	/**
	 * Clear template cache.
	 *
	 * @return void
	 */
	public static function clear_cache(): void {
		self::$cache = array();
	}

	/**
	 * Helper method to render field with escaping context.
	 *
	 * @param string $template Template name.
	 * @param array  $variables Template variables.
	 *
	 * @return string Rendered output.
	 */
	public function render_field( string $template, array $variables ): string {
		return $this->render( 'fields/' . $template, $variables, false );
	}

	/**
	 * Create instance with modern static factory.
	 *
	 * @param string $templatePath   Path to templates.
	 * @param bool   $cacheEnabled   Enable template caching.
	 * @return self
	 */
	public static function create( string $templatePath, bool $cacheEnabled = true ): self {
		return new self( $templatePath, $cacheEnabled );
	}
}
