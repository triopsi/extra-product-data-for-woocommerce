<?php
/**
 * Template Helper Functions for Extra Product Data for WooCommerce
 *
 * Provides helper functions for use in templates.
 *
 * @package Extra_Product_Data_For_WooCommerce
 * @author Daniel Drevermann <info@triopsi.com>
 * @copyright Copyright (c) 2024-2026, IT-Dienstleistungen Drevermann
 * @since 1.9.0
 */

declare( strict_types=1 );
namespace Triopsi\Exprdawc\Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Exprdawc_Template_Helpers
 *
 * Static helper functions for templates.
 *
 * @package Exprdawc\Helper
 */
class Exprdawc_Template_Helpers {

	/**
	 * Join array with glue.
	 *
	 * @param array  $items Array to join.
	 * @param string $glue Glue string.
	 *
	 * @return string Joined string.
	 */
	public static function join( array $items, string $glue = ' ' ): string {
		return implode( $glue, array_filter( $items ) );
	}

	/**
	 * Build HTML attributes string.
	 *
	 * @param array $attributes Attributes array.
	 *
	 * @return string HTML attributes string.
	 */
	public static function attrs( array $attributes ): string {
		$output = array();

		foreach ( $attributes as $key => $value ) {
			if ( is_bool( $value ) ) {
				if ( $value ) {
					$output[] = esc_attr( $key );
				}
			} elseif ( null !== $value ) {
				$output[] = esc_attr( $key ) . '="' . esc_attr( $value ) . '"';
			}
		}

		return implode( ' ', $output );
	}

	/**
	 * Build CSS class string from array.
	 *
	 * @param array|string $classes Classes array or string.
	 *
	 * @return string CSS class string.
	 */
	public static function classes( $classes ): string {
		if ( is_string( $classes ) ) {
			return esc_attr( $classes );
		}

		if ( is_array( $classes ) ) {
			return esc_attr( implode( ' ', array_filter( $classes ) ) );
		}

		return '';
	}

	/**
	 * Check if value is in array.
	 *
	 * @param mixed $needle Value to search for.
	 * @param array $haystack Array to search in.
	 *
	 * @return bool Whether value is in array.
	 */
	public static function in_array( $needle, array $haystack ): bool {
		return in_array( $needle, $haystack, true );
	}

	/**
	 * Escape for HTML output.
	 *
	 * @param string $text Text to escape.
	 *
	 * @return string Escaped text.
	 */
	public static function e( string $text ): string {
		return esc_html( $text );
	}

	/**
	 * Escape for HTML attribute.
	 *
	 * @param string $text Text to escape.
	 *
	 * @return string Escaped text.
	 */
	public static function attr( string $text ): string {
		return esc_attr( $text );
	}

	/**
	 * Escape for JavaScript.
	 *
	 * @param string $text Text to escape.
	 *
	 * @return string Escaped text.
	 */
	public static function js( string $text ): string {
		return esc_js( $text );
	}

	/**
	 * Escape for URL.
	 *
	 * @param string $url URL to escape.
	 *
	 * @return string Escaped URL.
	 */
	public static function url( string $url ): string {
		return esc_url( $url );
	}

	/**
	 * Escape for textarea.
	 *
	 * @param string $text Text to escape.
	 *
	 * @return string Escaped text.
	 */
	public static function textarea( string $text ): string {
		return esc_textarea( $text );
	}

	/**
	 * Format price for display.
	 *
	 * @param float|string $price Price value.
	 *
	 * @return string Formatted price.
	 */
	public static function price( $price ): string {
		if ( function_exists( 'wc_price' ) ) {
			return wc_price( $price );
		}

		return number_format( (float) $price, 2 );
	}

	/**
	 * Sanitize ID string (replace spaces and underscores with hyphens).
	 *
	 * @param string $id ID to sanitize.
	 *
	 * @return string Sanitized ID.
	 */
	public static function id( string $id ): string {
		return str_replace( array( ' ', '_' ), '-', $id );
	}

	/**
	 * Check if array/string is empty.
	 *
	 * @param mixed $value Value to check.
	 *
	 * @return bool Whether value is empty.
	 */
	public static function is_empty( $value ): bool {
		if ( is_array( $value ) ) {
			return empty( $value );
		}

		if ( is_string( $value ) ) {
			return '' === trim( $value );
		}

		return empty( $value );
	}

	/**
	 * Check if value is set and not null.
	 *
	 * @param mixed $value Value to check.
	 *
	 * @return bool Whether value is set.
	 */
	public static function is_set( $value ): bool {
		return null !== $value;
	}

	/**
	 * Get array value with default.
	 *
	 * @param array  $data Array to search.
	 * @param string $key Key to get.
	 * @param mixed  $default_value Default value.
	 *
	 * @return mixed Value or default.
	 */
	public static function get( array $data, string $key, $default_value = null ) {
		return $data[ $key ] ?? $default_value;
	}

	/**
	 * Translate string.
	 *
	 * @param string $text Text to translate.
	 * @param string $domain Text domain.
	 *
	 * @return string Translated text.
	 */
	public static function __( string $text, string $domain = 'extra-product-data-for-woocommerce' ): string {
		return __( $text, $domain ); // phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralDomain, WordPress.WP.I18n.NonSingularStringLiteralText
	}

	/**
	 * Translate and escape string.
	 *
	 * @param string $text Text to translate.
	 * @param string $domain Text domain.
	 *
	 * @return string Translated and escaped text.
	 */
	public static function _e( string $text, string $domain = 'extra-product-data-for-woocommerce' ): string { // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
		return esc_html__( $text, $domain ); // phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralDomain, WordPress.WP.I18n.NonSingularStringLiteralText
	}

	/**
	 * Build data attributes from array.
	 *
	 * @param array $data Data attributes array (without 'data-' prefix).
	 *
	 * @return string Data attributes string.
	 */
	public static function data_attrs( array $data ): string {
		$output = array();

		foreach ( $data as $key => $value ) {
			if ( null !== $value && '' !== $value ) {
				$output[] = 'data-' . esc_attr( $key ) . '="' . esc_attr( $value ) . '"';
			}
		}

		return implode( ' ', $output );
	}

	/**
	 * Generate unique ID.
	 *
	 * @param string $prefix ID prefix.
	 *
	 * @return string Unique ID.
	 */
	public static function unique_id( string $prefix = 'exprdawc' ): string {
		static $counter = 0;
		++$counter;

		return $prefix . '-' . $counter;
	}

	/**
	 * Check if option is selected.
	 *
	 * @param mixed        $value Current value.
	 * @param string|array $option Option value(s) to check.
	 *
	 * @return string 'selected' attribute or empty string.
	 */
	public static function selected( $value, $option ): string {
		if ( is_array( $value ) ) {
			return in_array( $option, $value, true ) ? 'selected' : '';
		}

		return selected( $value, $option, false );
	}

	/**
	 * Check if checkbox/radio is checked.
	 *
	 * @param mixed        $value Current value.
	 * @param string|array $option Option value(s) to check.
	 *
	 * @return string 'checked' attribute or empty string.
	 */
	public static function checked( $value, $option ): string {
		if ( is_array( $value ) ) {
			return in_array( $option, $value, true ) ? 'checked' : '';
		}

		if ( is_string( $value ) && strpos( $value, ', ' ) !== false ) {
			$value = explode( ', ', $value );
			return in_array( $option, $value, true ) ? 'checked' : '';
		}

		return checked( $value, $option, false );
	}

	/**
	 * WordPress nonce field.
	 *
	 * @param string $action Nonce action.
	 * @param string $name Nonce field name.
	 *
	 * @return string Nonce field HTML.
	 */
	public static function nonce_field( string $action, string $name = '_wpnonce' ): string {
		return wp_nonce_field( $action, $name, true, false );
	}
}
