<?php
/**
 * Product Frontend Handler
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

namespace Triopsi\Exprdawc\Frontend;

use Automattic\WooCommerce\Enums\ProductType;
use Triopsi\Exprdawc\Helpers\Helper;
use Triopsi\Exprdawc\Helpers\OrderHelper;
use Triopsi\Exprdawc\Contracts\Hookable;
use WC_Product;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Product Frontend Handler
 *
 * Handles product frontend functionality and custom fields display.
 */
class ProductFrontend implements Hookable {

	/**
	 * Constructor for the class.
	 */
	public function __construct() {
		add_filter( 'woocommerce_product_supports', array( $this, 'exprdawcCheckProductSupport' ), 10, 3 );
		add_action( 'woocommerce_before_add_to_cart_button', array( $this, 'exprdawcDisplayCustomFieldsOnProductPage' ) );
		add_filter( 'woocommerce_add_to_cart_validation', array( $this, 'exprdawcValidateCustomFields' ), 10, 3 );

		add_filter( 'woocommerce_product_has_options', array( $this, 'exprdawcHasOptions' ), 15, 2 );
		add_filter( 'woocommerce_product_add_to_cart_text', array( $this, 'exprdawcChangeAddToCartButtonText' ), 10, 2 );
		add_filter( 'woocommerce_product_add_to_cart_url', array( $this, 'exprdawcChangeAddToCartUrl' ), 10, 2 );

		add_filter( 'woocommerce_is_purchasable', array( $this, 'exprdawcPreventPurchaseAtGroupedLevel' ), 10, 2 );

		add_filter( 'woocommerce_add_cart_item_data', array( $this, 'exprdawcSaveExtraProductDataInCart' ), 10, 4 );
		add_filter( 'woocommerce_get_item_data', array( $this, 'exprdawcDisplayFieldsOnCartAndCheckout' ), 10, 2 );
		add_action( 'woocommerce_before_calculate_totals', array( $this, 'exprdawcAdjustCartItemPricing' ) );
		add_action( 'woocommerce_checkout_create_order_line_item', array( $this, 'exprdawcAddExtraProductDataToOrder' ), 10, 4 );
		add_filter( 'woocommerce_cart_item_class', array( $this, 'exprdawcAddCartItemClass' ), 10, 3 );

		add_action( 'wp_enqueue_scripts', array( $this, 'exprdawcAddFrontendStylesScripts' ) );
	}

	/**
	 * Adds custom CSS classes to cart items that contain extra product fields.
	 *
	 * This filter modifies the CSS classes applied to a cart item row (`<tr>`)
	 * in the WooCommerce cart table. It is used to mark products that contain
	 * extra product fields added by this plugin.
	 *
	 * @param string $className     Existing CSS classes applied to the cart item row.
	 * @param array  $cart_item     The cart item data.
	 * @param string $cart_item_key Unique key identifying the cart item.
	 *
	 * @return string Modified CSS class string.
	 */
	public function exprdawcAddCartItemClass( $className, $cart_item, $cart_item_key ): string {
		if ( isset( $cart_item[ EXPRDAWC_CART_ITEM_CUSTOM_FIELDS_KEY ] ) ) {
			$className .= ' exprdawc-cart-item-has-extra-data';
		}
		return $className;
	}

	/**
	 * Check if product has options.
	 *
	 * @param bool       $has_options Whether the product has options.
	 * @param WC_Product $product The WooCommerce product object.
	 * @return bool Whether the product has options.
	 */
	public function exprdawcHasOptions( bool $has_options, WC_Product $product ): bool {
		if ( Helper::checkRequiredFields( $product ) ) {
			$has_options = true;
		}
		return $has_options;
	}

	/**
	 * Prevents purchase at grouped level.
	 *
	 * @param bool       $purchasable Whether the product is purchasable.
	 * @param WC_Product $product The WooCommerce product object.
	 * @return bool Whether the product is purchasable.
	 */
	public function exprdawcPreventPurchaseAtGroupedLevel( bool $purchasable, WC_Product $product ): bool {
		if ( ProductType::GROUPED === $product->get_type() ) {
			$grouped_products = $product->get_children();
			foreach ( $grouped_products as $grouped_product_id ) {
				if ( Helper::checkRequiredFields( $grouped_product_id ) ) {
					$purchasable = false;
				}
			}
		}
		return $purchasable;
	}

	/**
	 * Change add-to-cart text.
	 *
	 * @param string     $text The text of the add-to-cart button.
	 * @param WC_Product $product The WooCommerce product object.
	 * @return string
	 */
	public function exprdawcChangeAddToCartButtonText( string $text, WC_Product $product ): string {
		if ( ! $product->is_in_stock() ) {
			return $text;
		}

		if ( ! in_array( $product->get_type(), array( ProductType::SIMPLE, ProductType::VARIATION ), true ) ) {
			return $text;
		}

		$custom_fields = Helper::getExtraProductFields( $product );
		if ( ! empty( $custom_fields ) ) {
			$text_from_settings = get_option( 'exprdawc_custom_add_to_cart_text', __( 'Configure Product', 'extra-product-data-for-woocommerce' ) );
			if ( ! empty( $text_from_settings ) ) {
				return $text_from_settings;
			}
		}
		return $text;
	}

	/**
	 * Changes add-to-cart URL.
	 *
	 * @param string     $url The URL of the add-to-cart button.
	 * @param WC_Product $product The WooCommerce product object.
	 * @return string
	 */
	public function exprdawcChangeAddToCartUrl( string $url, WC_Product $product ): string {
		if ( in_array( $product->get_type(), array( ProductType::SIMPLE, ProductType::VARIATION ), true ) ) {
			if ( Helper::checkRequiredFields( $product->get_id() ) ) {
				$url = get_permalink( $product->get_id() );
			}
		}
		return $url;
	}

	/**
	 * Enqueue frontend assets.
	 *
	 * @return void
	 */
	public function exprdawcAddFrontendStylesScripts(): void {
		if ( is_product() ) {
			wp_enqueue_style( 'form-css', EXPRDAWC_ASSETS_CSS . 'forms.css', array(), EXPRDAWC_VERSION, 'all' );
			wp_enqueue_script( 'wc-conditional-rules-js', EXPRDAWC_ASSETS_JS . 'wc-conditional-rules-js.min.js', array( 'jquery' ), EXPRDAWC_VERSION, true );
			wp_enqueue_script( 'exprdawc-frontend-js', EXPRDAWC_ASSETS_JS . 'wc-product-frontend.min.js', array( 'jquery' ), EXPRDAWC_VERSION, true );
			wp_localize_script(
				'exprdawc-frontend-js',
				'exprdawc_frontend_settings',
				array(
					'html'                         => true,
					'currency_symbol'              => get_woocommerce_currency_symbol( get_woocommerce_currency() ),
					'currency_position'            => get_option( 'woocommerce_currency_pos', true ),
					'decimal_separator'            => wc_get_price_decimal_separator(),
					'currency_format_trim_zeros'   => wc_get_price_thousand_separator(),
					'currency_format_num_decimals' => wc_get_price_decimals(),
					'price_format'                 => get_woocommerce_price_format(),
					'price_length'                 => wc_get_price_decimals(),
					'option'                       => esc_html__( 'Option', 'extra-product-data-for-woocommerce' ),
					'price'                        => esc_html__( 'Price', 'extra-product-data-for-woocommerce' ),
					'subtotal'                     => esc_html__( 'Subtotal', 'extra-product-data-for-woocommerce' ),
					'total'                        => esc_html__( 'Total', 'extra-product-data-for-woocommerce' ),
				)
			);
		}
	}

	/**
	 * Display custom fields on product page.
	 *
	 * @return void
	 */
	public function exprdawcDisplayCustomFieldsOnProductPage(): void {
		global $product;
		$custom_fields = Helper::getExtraProductFields( $product );

		if ( ! empty( $custom_fields ) ) {
			echo '<div class="exprdawc-extra-fields">';
			wp_nonce_field( 'exprdawc_save_custom_field', 'exprdawc_nonce' );
			foreach ( $custom_fields as $index => $field ) {
				Helper::generateInputField( $index, $field, '', false, true );
			}
			echo '</div>';

			echo '<div class="exprdawc-price-adjustment" data-product-type="' . esc_attr( $product->get_type() ) . '" data-product-name="' . esc_attr( $product->get_name() ) . '" data-product-base-price="' . esc_attr( wc_get_price_to_display( $product ) ) . '"></div>';
		}
	}

	/**
	 * Validates custom fields.
	 *
	 * @param bool $passed Whether the validation has passed.
	 * @param int  $product_id Product ID.
	 * @param int  $quantity Quantity.
	 * @return bool
	 */
	public function exprdawcValidateCustomFields( bool $passed, int $product_id, int $quantity ): bool { // phpcs:ignore
		if ( ! is_bool( $passed ) || ! is_numeric( $product_id ) || ! is_numeric( $quantity ) ) {
			return $passed;
		}

		if ( ! isset( $_POST['exprdawc_custom_field_input'] ) ) { // phpcs:ignore
			return $passed;
		}

		$product = wc_get_product( $product_id );
		if ( ! $product instanceof WC_Product ) {
			return $passed;
		}

		$custom_fields = Helper::getExtraProductFields( $product );
		if ( empty( $custom_fields ) ) {
			return $passed;
		}

		foreach ( $custom_fields as $input_field_array ) {
			if ( ! is_array( $input_field_array ) || empty( $input_field_array['label'] ) ) {
				continue;
			}

			$field_label = sanitize_text_field( $input_field_array['label'] );
			$field_type  = isset( $input_field_array['type'] ) ? sanitize_text_field( $input_field_array['type'] ) : 'text';
			$is_required = ! empty( $input_field_array['required'] );

			$field_data  = OrderHelper::getSubmittedFieldData( $input_field_array );
			$field_value = $field_data['value'];
			$field_value = $this->sanitizeFieldValue( $field_value );

			if ( $is_required && empty( $field_value ) ) {
				wc_add_notice(
					sprintf(
						/* translators: %s is the field label */
						esc_html__( '%s is a required field.', 'extra-product-data-for-woocommerce' ),
						esc_html( $field_label )
					),
					'error'
				);
				$passed = false;
				continue;
			}

			if ( empty( $field_value ) ) {
				continue;
			}

			$validation_result = $this->validateFieldByType( $field_value, $field_type, $input_field_array );
			if ( ! $validation_result['valid'] ) {
				wc_add_notice( $validation_result['message'], 'error' );
				$passed = false;
			}
		}

		return $passed;
	}

	/**
	 * Sanitizes a field value.
	 *
	 * @param mixed $field_value The raw field value.
	 * @return mixed
	 */
	private function sanitizeFieldValue( $field_value ) {
		if ( is_array( $field_value ) ) {
			return array_map(
				function ( $value ) {
					return sanitize_text_field( wp_unslash( $value ) );
				},
				$field_value
			);
		}

		if ( ! is_string( $field_value ) && ! is_numeric( $field_value ) ) {
			return '';
		}

		return sanitize_text_field( wp_unslash( $field_value ) );
	}

	/**
	 * Validate value by field type.
	 *
	 * @param mixed  $field_value The sanitized field value.
	 * @param string $field_type The field type.
	 * @param array  $input_field_array The field configuration array.
	 * @return array
	 */
	private function validateFieldByType( $field_value, string $field_type, array $input_field_array ): array {
		$field_label = sanitize_text_field( $input_field_array['label'] );

		switch ( $field_type ) {
			case 'email':
				if ( ! is_email( $field_value ) ) {
					return array(
						'valid'   => false,
						'message' => sprintf(
							/* translators: %s is the field label */
							esc_html__( '%s is not a valid email address.', 'extra-product-data-for-woocommerce' ),
							esc_html( $field_label )
						),
					);
				}
				break;

			case 'number':
				if ( ! is_numeric( $field_value ) ) {
					return array(
						'valid'   => false,
						'message' => sprintf(
							/* translators: %s is the field label */
							esc_html__( '%s must be a number.', 'extra-product-data-for-woocommerce' ),
							esc_html( $field_label )
						),
					);
				}
				break;

			case 'date':
				if ( ! strtotime( $field_value ) ) {
					return array(
						'valid'   => false,
						'message' => sprintf(
							/* translators: %s is the field label */
							esc_html__( '%s is not a valid date.', 'extra-product-data-for-woocommerce' ),
							esc_html( $field_label )
						),
					);
				}

				$min_date = isset( $input_field_array['min'] ) ? sanitize_text_field( (string) $input_field_array['min'] ) : '';
				$max_date = isset( $input_field_array['max'] ) ? sanitize_text_field( (string) $input_field_array['max'] ) : '';

				if ( ! empty( $min_date ) && strtotime( $field_value ) < strtotime( $min_date ) ) {
					return array(
						'valid'   => false,
						'message' => sprintf(
							/* translators: %1$s is the field label, %2$s is the minimum date */
							esc_html__( '%1$s cannot be earlier than %2$s.', 'extra-product-data-for-woocommerce' ),
							esc_html( $field_label ),
							esc_html( $min_date )
						),
					);
				}

				if ( ! empty( $max_date ) && strtotime( $field_value ) > strtotime( $max_date ) ) {
					return array(
						'valid'   => false,
						'message' => sprintf(
							/* translators: %1$s is the field label, %2$s is the maximum date */
							esc_html__( '%1$s cannot be later than %2$s.', 'extra-product-data-for-woocommerce' ),
							esc_html( $field_label ),
							esc_html( $max_date )
						),
					);
				}
				break;

			case 'time':
				$normalized_time = is_string( $field_value ) ? $this->normalizeTimeToMinute( $field_value ) : null;
				if ( null === $normalized_time ) {
					return array(
						'valid'   => false,
						'message' => sprintf(
							/* translators: %s is the field label */
							esc_html__( '%s is not a valid time.', 'extra-product-data-for-woocommerce' ),
							esc_html( $field_label )
						),
					);
				}

				$min_time = isset( $input_field_array['min'] ) ? sanitize_text_field( (string) $input_field_array['min'] ) : '';
				$max_time = isset( $input_field_array['max'] ) ? sanitize_text_field( (string) $input_field_array['max'] ) : '';
				$min_time = '' !== $min_time ? $this->normalizeTimeToMinute( $min_time ) : null;
				$max_time = '' !== $max_time ? $this->normalizeTimeToMinute( $max_time ) : null;

				if ( null !== $min_time && $normalized_time < $min_time ) {
					return array(
						'valid'   => false,
						'message' => sprintf(
							/* translators: %s is the field label */
							esc_html__( '%s is earlier than the minimum allowed time.', 'extra-product-data-for-woocommerce' ),
							esc_html( $field_label )
						),
					);
				}

				if ( null !== $max_time && $normalized_time > $max_time ) {
					return array(
						'valid'   => false,
						'message' => sprintf(
							/* translators: %s is the field label */
							esc_html__( '%s is later than the maximum allowed time.', 'extra-product-data-for-woocommerce' ),
							esc_html( $field_label )
						),
					);
				}
				break;

			case 'color':
				// Validate hex color format (#RRGGBB or #RGB).
				if ( ! preg_match( '/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $field_value ) ) {
					return array(
						'valid'   => false,
						'message' => sprintf(
							/* translators: %s is the field label */
							esc_html__( '%s must be a valid color (hex format).', 'extra-product-data-for-woocommerce' ),
							esc_html( $field_label )
						),
					);
				}
				break;

			case 'yes-no':
				if ( ! in_array( $field_value, array( 'yes', 'no' ), true ) ) {
					return array(
						'valid'   => false,
						'message' => sprintf(
							/* translators: %s is the field label */
							esc_html__( '%s must be either "Yes" or "No".', 'extra-product-data-for-woocommerce' ),
							esc_html( $field_label )
						),
					);
				}
				break;

			case 'radio':
				if ( ! $this->validateOptionSelection( $field_value, $input_field_array ) ) {
					return array(
						'valid'   => false,
						'message' => esc_html__( 'Invalid option selected for radio button.', 'extra-product-data-for-woocommerce' ),
					);
				}
				break;

			case 'checkbox':
				if ( ! $this->validateOptionSelection( $field_value, $input_field_array ) ) {
					return array(
						'valid'   => false,
						'message' => esc_html__( 'Invalid option selected for checkbox.', 'extra-product-data-for-woocommerce' ),
					);
				}
				break;

			case 'select':
				if ( ! $this->validateOptionSelection( $field_value, $input_field_array ) ) {
					return array(
						'valid'   => false,
						'message' => esc_html__( 'Invalid option selected for select field.', 'extra-product-data-for-woocommerce' ),
					);
				}
				break;
		}

		return array( 'valid' => true );
	}

	/**
	 * Validate option selections.
	 *
	 * @param mixed $field_value The field value (string or array).
	 * @param array $input_field_array The field configuration array.
	 * @return bool
	 */
	private function validateOptionSelection( $field_value, array $input_field_array ): bool {
		if ( ! isset( $input_field_array['options'] ) || ! is_array( $input_field_array['options'] ) ) {
			return false;
		}

		$valid_options = array_column( $input_field_array['options'], 'value' );
		if ( empty( $valid_options ) ) {
			return false;
		}

		$field_values = (array) $field_value;
		$field_values = array_map( 'sanitize_text_field', $field_values );

		$intersect = array_intersect( $field_values, $valid_options );

		return ! empty( $intersect ) && count( $intersect ) === count( $field_values );
	}

	/**
	 * Normalize a time string to HH:MM.
	 *
	 * Accepts HH:MM or HH:MM:SS. When seconds are provided,
	 * only :00 is accepted for minute precision.
	 *
	 * @param string $value Raw time value.
	 * @return string|null Normalized HH:MM value or null if invalid.
	 */
	private function normalizeTimeToMinute( string $value ): ?string {
		$value = trim( $value );

		if ( preg_match( '/^([01]\d|2[0-3]):([0-5]\d)$/', $value ) ) {
			return $value;
		}

		if ( preg_match( '/^([01]\d|2[0-3]):([0-5]\d):([0-5]\d)$/', $value, $matches ) ) {
			if ( '00' !== $matches[3] ) {
				return null;
			}

			return $matches[1] . ':' . $matches[2];
		}

		return null;
	}

	/**
	 * Checks product support.
	 *
	 * @param bool   $supports The supports.
	 * @param string $feature The feature.
	 * @param object $product The product.
	 * @return bool
	 */
	public function exprdawcCheckProductSupport( bool $supports, string $feature, WC_Product $product ): bool { // phpcs:ignore
		if ( 'ajax_add_to_cart' === $feature && Helper::checkRequiredFields( $product->get_id() ) ) {
			$supports = false;
		}
		return $supports;
	}

	/**
	 * Save extra data to cart item data.
	 *
	 * @param array $cart_item_data The existing cart item data.
	 * @param int   $product_id Product ID.
	 * @param int   $variation_id Variation ID.
	 * @param int   $quantity Quantity.
	 * @return array
	 */
	public function exprdawcSaveExtraProductDataInCart( array $cart_item_data, int $product_id, int $variation_id, int $quantity ): array { // phpcs:ignore
		if ( isset( $_POST['exprdawc_nonce'] ) ) {
			$post_nonce = sanitize_text_field( wp_unslash( $_POST['exprdawc_nonce'] ) );
			if ( ! wp_verify_nonce( $post_nonce, 'exprdawc_save_custom_field' ) ) {
				return $cart_item_data;
			}
		}

		$product = wc_get_product( $product_id );
		if ( ! $product ) {
			return $cart_item_data;
		}
		$custom_fields = Helper::getExtraProductFields( $product );

		$cart_item_data_user_inputs = array();

		if ( ! empty( $custom_fields ) ) {
			foreach ( $custom_fields as $input_field_array ) {
				$field_data  = OrderHelper::getSubmittedFieldData( $input_field_array );
				$field_value = $field_data['value'];

				if ( isset( $field_value ) ) {
					$cart_item_data_user_inputs[] = OrderHelper::buildSubmittedFieldPayload(
						$input_field_array,
						$field_value,
						(float) $product->get_price()
					);
				}
			}
			if ( ! empty( $cart_item_data_user_inputs ) ) {
				$cart_item_data[ EXPRDAWC_CART_ITEM_CUSTOM_FIELDS_KEY ] = $cart_item_data_user_inputs;

				// Save original price for display in cart and checkout.
				$formatted_original_price                            = OrderHelper::formatPlainPrice( (float) $product->get_price() );
				$cart_item_data[ EXPRDAWC_META_ORIGINAL_ITEM_PRICE ] = $formatted_original_price;
			}
		}
		return $cart_item_data;
	}

	/**
	 * Display fields on cart and checkout.
	 *
	 * @param array $item_data The item data.
	 * @param array $cart_item The cart item.
	 * @return array
	 */
	public function exprdawcDisplayFieldsOnCartAndCheckout( array $item_data, array $cart_item ): array {
		if ( empty( $cart_item[ EXPRDAWC_CART_ITEM_CUSTOM_FIELDS_KEY ] ) || ! is_array( $cart_item[ EXPRDAWC_CART_ITEM_CUSTOM_FIELDS_KEY ] ) ) {
			return $item_data;
		}

		$show_empty_fields = get_option( 'exprdawc_show_empty_fields', 'yes' );
		$show_in_cart      = get_option( 'exprdawc_show_in_cart', 'yes' );
		$show_in_checkout  = get_option( 'exprdawc_show_in_checkout', 'yes' );

		$should_display =
			( is_cart() && 'yes' === $show_in_cart ) ||
			( is_checkout() && 'yes' === $show_in_checkout ) ||
			wp_doing_ajax() ||
			wp_is_json_request();

		if ( ! $should_display ) {
			return $item_data;
		}

		$item_data[] = array(
			'key'     => esc_html__( 'Original item price', 'extra-product-data-for-woocommerce' ),
			'value'   => esc_html( $cart_item[ EXPRDAWC_META_ORIGINAL_ITEM_PRICE ] ),
			'display' => esc_html( $cart_item[ EXPRDAWC_META_ORIGINAL_ITEM_PRICE ] ),
		);

		foreach ( $cart_item[ EXPRDAWC_CART_ITEM_CUSTOM_FIELDS_KEY ] as $user_data ) {
			$label         = $user_data['field_raw']['label'] ?? '';
			$value         = $user_data['value'] ?? '';
			$display_value = $user_data['display_value'] ?? $value;

			if ( '' === $label ) {
				continue;
			}

			if ( '' === (string) $value && 'yes' !== $show_empty_fields ) {
				continue;
			}

			$item_data[] = array(
				'key'     => esc_html( $label ),
				'value'   => $display_value,
				'display' => nl2br( esc_html( $display_value ) ),
			);
		}

		return $item_data;
	}

	/**
	 * Adjust cart item prices.
	 *
	 * @param object $cart_object The cart object.
	 * @return void
	 */
	public function exprdawcAdjustCartItemPricing( object $cart_object ): void {
		if ( did_action( 'woocommerce_before_calculate_totals' ) > 1 ) {
			return;
		}

		foreach ( $cart_object->get_cart() as $cart_item_key => $cart_item ) {
			if ( isset( $cart_item[ EXPRDAWC_CART_ITEM_CUSTOM_FIELDS_KEY ] ) ) {
				$base_price            = (float) $cart_item['data']->get_price();
				$quantity              = max( 1, (int) $cart_item['quantity'] );
				$line_price_adjustment = 0.0;

				foreach ( $cart_item[ EXPRDAWC_CART_ITEM_CUSTOM_FIELDS_KEY ] as $user_data ) {
					if ( empty( $user_data['value'] ) ) {
						continue;
					}

					$line_price_adjustment += (float) OrderHelper::calculatePriceAdjustment(
						$user_data['field_raw'],
						$user_data['raw_value'],
						$base_price,
						$quantity
					);
				}

				$unit_price_adjustment = $line_price_adjustment / $quantity;
				$cart_item['data']->set_price( $base_price + $unit_price_adjustment );
			}
		}
	}

	/**
	 * Add custom field data to order line item.
	 *
	 * @param object $item The product item.
	 * @param string $cart_item_key Cart item key.
	 * @param array  $values Values.
	 * @param object $order The order object.
	 * @return void
	 */
	public function exprdawcAddExtraProductDataToOrder( object $item, string $cart_item_key, array $values, object $order ): void { // phpcs:ignore
		if ( empty( $values[ EXPRDAWC_CART_ITEM_CUSTOM_FIELDS_KEY ] ) ) {
			return;
		}

		if ( isset( $values[ EXPRDAWC_META_ORIGINAL_ITEM_PRICE ] ) ) {
			$item->add_meta_data(
				esc_html__( 'Original item price', 'extra-product-data-for-woocommerce' ),
				esc_html( $values[ EXPRDAWC_META_ORIGINAL_ITEM_PRICE ] ),
				true
			);
		}

		foreach ( $values[ EXPRDAWC_CART_ITEM_CUSTOM_FIELDS_KEY ] as $field ) {
			// Add each field as individual meta data for the order item.
			$item->add_meta_data(
				sanitize_text_field( $field['field_raw']['label'] ),
				$field['display_value'], // phpcs:ignore
				true
			);
		}

		$field_meta = OrderHelper::buildFieldMetadataArray( $values[ EXPRDAWC_CART_ITEM_CUSTOM_FIELDS_KEY ] );

		if ( ! empty( $field_meta ) ) {
			$item->add_meta_data( EXPRDAWC_ORDER_META_EXTRA_PRODUCT_DATA, $field_meta );
		}
	}

	/**
	 * Register WordPress hooks.
	 *
	 * @return void
	 */
	public function registerHooks(): void {
		// Hooks are registered in constructor.
	}
}
