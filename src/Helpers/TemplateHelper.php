<?php
/**
 * Template Helper Class
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

namespace Triopsi\Exprdawc\Helpers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Template Helper Class
 *
 * Static helper functions for templates.
 */
class TemplateHelper {

	/**
	 * Join array items into a string with a glue.
	 *
	 * @param array  $items The array of items to join.
	 * @param string $glue The glue string to use between items.
	 * @return string The joined string.
	 */
	public static function join( array $items, string $glue = ' ' ): string {
		return implode( $glue, array_filter( $items ) );
	}

	/**
	 * Convert an associative array of attributes into a string for HTML tags.
	 *
	 * @param array $attributes The associative array of attributes.
	 * @return string The formatted string of attributes.
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
	 * Convert an array of CSS classes into a string for HTML tags.
	 *
	 * @param array $classes The array of CSS classes.
	 * @return string The formatted string of classes.
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
	public static function inArray( $needle, array $haystack ): bool {
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
	public static function isEmpty( $value ): bool {
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
	public static function isSet( $value ): bool {
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
	public static function dataAttrs( array $data ): string {
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
	public static function uniqueId( string $prefix = 'exprdawc' ): string {
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
	public static function nonceField( string $action, string $name = '_wpnonce' ): string {
		return wp_nonce_field( $action, $name, true, false );
	}
}
