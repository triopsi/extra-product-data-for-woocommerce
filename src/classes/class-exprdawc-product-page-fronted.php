<?php
/**
 * Created on Fri Nov 01 2024
 *
 * Copyright (c) 2024 IT-Dienstleistungen Drevermann - All Rights Reserved
 *
 * @package Extra Product Data for WooCommerce
 * @author Daniel Drevermann <info@triopsi.com>
 * @copyright Copyright (c) 2024, IT-Dienstleistungen Drevermann
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 *
 * This file is part of the development of WordPress plugins.
 */

declare( strict_types=1 );
namespace Triopsi\Exprdawc;

use Automattic\WooCommerce\Enums\ProductType;
use WC_Product;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class User
 *
 * This class represents a user in the system.
 *
 * @property int $id The unique identifier for the user.
 * @property string $name The name of the user.
 * @property string $email The email address of the user.
 * @property string $password The hashed password of the user.
 * @property \DateTime $created_at The date and time when the user was created.
 * @property \DateTime $updated_at The date and time when the user was last updated.
 *
 * @method void setPassword(string $password) Sets the user's password.
 * @method bool checkPassword(string $password) Checks if the provided password matches the user's password.
 * @method void save() Saves the user to the database.
 * @method void delete() Deletes the user from the database.
 */
class Exprdawc_Product_Page_Fronted {

	/**
	 * Constructor for the class.
	 */
	public function __construct() {

		// Add custom fields & validation to the product page.
		add_filter( 'woocommerce_product_supports', array( $this, 'exprdawc_check_product_support' ), 10, 3 );
		add_action( 'woocommerce_before_add_to_cart_button', array( $this, 'exprdawc_display_custom_fields_on_product_page' ) );
		add_filter( 'woocommerce_add_to_cart_validation', array( $this, 'exprdawc_validate_custom_fields' ), 10, 3 );

		// Change the add-to-cart button text and URL.
		add_filter( 'woocommerce_product_has_options', array( $this, 'exprdawc_has_options' ), 15, 2 );
		add_filter( 'woocommerce_product_add_to_cart_text', array( $this, 'exprdawc_change_add_to_cart_button_text' ), 10, 2 );
		add_filter( 'woocommerce_product_add_to_cart_url', array( $this, 'exprdawc_change_add_to_cart_url' ), 10, 2 );

		// Prevent purchase at grouped level.
		add_filter( 'woocommerce_is_purchasable', array( $this, 'exprdawc_prevent_purchase_at_grouped_level' ), 10, 2 );

		// Save custom fields in the cart.
		add_filter( 'woocommerce_add_cart_item_data', array( $this, 'exprdawc_save_extra_product_data_in_cart' ), 10, 4 );
		add_filter( 'woocommerce_get_item_data', array( $this, 'exprdawc_display_fields_on_cart_and_checkout' ), 10, 2 );
		add_action( 'woocommerce_before_calculate_totals', array( $this, 'exprdawc_adjust_cart_item_pricing' ) );
		add_action( 'woocommerce_checkout_create_order_line_item', array( $this, 'exprdawc_add_extra_product_data_to_order' ), 10, 4 );

		// Add Frontend CSS for custom fields.
		add_action( 'wp_enqueue_scripts', array( $this, 'exprdawc_add_frontend_styles_scripts' ) );
	}

	/**
	 * Check if product has options.
	 *
	 * @param bool       $has_options Whether the product has options.
	 * @param WC_Product $product The WooCommerce product object.
	 * @return bool Whether the product has options.
	 */
	public function exprdawc_has_options( bool $has_options, WC_Product $product ): bool {
		if ( Exprdawc_Helper::check_required_fields( $product ) ) {
			$has_options = true;
		}
		return $has_options;
	}

	/**
	 * Prevents purchase at grouped level.
	 *
	 * Don't let products in the group with custom extra data fields and have required fields be added to cart when viewing grouped products.
	 *
	 * @param bool       $purchasable Whether the product is purchasable.
	 * @param WC_Product $product The WooCommerce product object.
	 * @return bool Whether the product is purchasable.
	 */
	public function exprdawc_prevent_purchase_at_grouped_level( bool $purchasable, WC_Product $product ): bool {
		if ( ProductType::GROUPED === $product->get_type() ) {
			$grouped_products = $product->get_children();
			foreach ( $grouped_products as $grouped_product_id ) {
				if ( Exprdawc_Helper::check_required_fields( $grouped_product_id ) ) {
					$purchasable = false;
				}
			}
		}
		return $purchasable;
	}

	/**
	 * Changes the text of the add-to-cart button based on the product type.
	 *
	 * This function changes the text of the add-to-cart button based on the product type.
	 * The text is changed only if the product is in stock and is not of type 'grouped' or 'external'.
	 * The text is also changed if custom fields are associated with the product and a custom text
	 * has been set in the plugin settings.
	 *
	 * @param string     $text The text of the add-to-cart button.
	 * @param WC_Product $product The WooCommerce product object.
	 * @return string The modified text of the add-to-cart button.
	 */
	public function exprdawc_change_add_to_cart_button_text( string $text, WC_Product $product ): string {

		if ( ! $product->is_in_stock() ) {
			return $text;
		}

		if ( ! in_array( $product->get_type(), array( ProductType::SIMPLE, ProductType::VARIATION ), true ) ) {
			return $text;
		}

		$custom_fields = $product->get_meta( '_extra_product_fields', true );
		if ( ! empty( $custom_fields ) ) {
			$text_from_settings = get_option( 'exprdawc_custom_add_to_cart_text', __( 'Configure Product', 'extra-product-data-for-woocommerce' ) );
			if ( ! empty( $text_from_settings ) ) {
				return $text_from_settings;
			}
		}
		return $text;
	}

	/**
	 * Changes the URL of the add-to-cart button based on the product type and have required fields.
	 *
	 * This function changes the URL of the add-to-cart button based on the product type.
	 * The URL is changed only if the product is not of type 'simple' or 'variation'.
	 * The URL is also changed if custom fields are associated with the product and the product
	 * is not being viewed on the single product page.
	 *
	 * @param string     $url The URL of the add-to-cart button.
	 * @param WC_Product $product The WooCommerce product object.
	 * @return string The modified URL of the add-to-cart button.
	 */
	public function exprdawc_change_add_to_cart_url( string $url, WC_Product $product ): string {
		if ( ! is_single( $product->get_id() ) && in_array( $product->get_type(), array( ProductType::SIMPLE, ProductType::VARIATION ), true ) ) {
			if ( Exprdawc_Helper::check_required_fields( $product->get_id() ) ) {
				$url = get_permalink( $product->get_id() );
			}
		}
		return $url;
	}

	/**
	 * Enqueues the frontend styles for the custom fields.
	 *
	 * This function enqueues the frontend styles for the custom fields on the product page.
	 * The styles are enqueued only if the current page is a product page.
	 *
	 * @return void
	 */
	public function exprdawc_add_frontend_styles_scripts(): void {
		if ( is_product() ) {
			wp_enqueue_style( 'form-css', EXPRDAWC_ASSETS_CSS . 'forms.css', array(), '1.0.0', 'all' );

			// Enqueue the wc-conditional-rules-js script.
			wp_enqueue_script( 'wc-conditional-rules-js', EXPRDAWC_ASSETS_JS . 'wc-conditional-rules-js.min.js', array( 'jquery' ), '1.0.0', true );

			// Enqueue the exprdawc-frontend-js script.
			wp_enqueue_script( 'exprdawc-frontend-js', EXPRDAWC_ASSETS_JS . 'wc-product-frontend.min.js', array( 'jquery' ), '1.0.0', true );
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
	 * Displays custom fields on the WooCommerce product page.
	 *
	 * This function retrieves custom fields associated with a product and displays them
	 * on the product page using WooCommerce's form field function. The custom fields are
	 * fetched from the post meta of the product using the meta key '_extra_product_fields'.
	 *
	 * @global WC_Product $product The current WooCommerce product object.
	 *
	 * @return void
	 */
	public function exprdawc_display_custom_fields_on_product_page(): void {
		global $product;
		$custom_fields = $product->get_meta( '_extra_product_fields', true );

		if ( ! empty( $custom_fields ) ) {
			echo '<div class="exprdawc-extra-fields">';
			wp_nonce_field( 'exprdawc_save_custom_field', 'exprdawc_nonce' );
			foreach ( $custom_fields as $index => $field ) {
				Exprdawc_Helper::generate_input_field( $field, '', false, true );
			}
			echo '</div>';

			// Add a div container for adjusted prices on the product page.
			echo '<div class="exprdawc-price-adjustment" data-product-type="' . esc_attr( $product->get_type() ) . '" data-product-name="' . esc_attr( $product->get_name() ) . '" data-product-base-price="' . esc_attr( wc_get_price_to_display( $product ) ) . '"></div>';
		}
	}

	/**
	 * Validates custom fields for a product during the add-to-cart process.
	 *
	 * This function checks if there are any custom fields associated with the product
	 * and ensures that all required fields are filled out by the user. If any required
	 * fields are missing, an error notice is added and the process is halted.
	 *
	 * @param bool $passed Whether the validation has passed. Default true.
	 * @param int  $product_id The ID of the product being added to the cart.
	 * @param int  $quantity The quantity of the product being added to the cart.
	 * @return bool Whether the validation has passed.
	 */
	public function exprdawc_validate_custom_fields( bool $passed, int $product_id, int $quantity ): bool { // phpcs:ignore

		// Validate input parameters.
		if ( ! is_bool( $passed ) || ! is_numeric( $product_id ) || ! is_numeric( $quantity ) ) {
			return $passed;
		}

		// If $_POST doesn't have the exprdawc_custom_field_input array, validation passes.
		if ( ! isset( $_POST['exprdawc_custom_field_input'] ) ) { // phpcs:ignore
			return $passed;
		}

		// Get the product safely.
		$product = wc_get_product( $product_id );
		if ( ! $product instanceof WC_Product ) {
			return $passed;
		}

		// Retrieve custom fields metadata.
		$custom_fields = $product->get_meta( '_extra_product_fields', true );
		if ( ! is_array( $custom_fields ) || empty( $custom_fields ) ) {
			return $passed;
		}

		// Validate each custom field.
		foreach ( $custom_fields as $input_field_array ) {

			// Validate field array structure.
			if ( ! is_array( $input_field_array ) || empty( $input_field_array['label'] ) ) {
				continue;
			}

			$field_label = sanitize_text_field( $input_field_array['label'] );
			$field_type  = isset( $input_field_array['type'] ) ? sanitize_text_field( $input_field_array['type'] ) : 'text';
			$is_required = ! empty( $input_field_array['required'] );

			// Get field index and value from POST data.
			$field_data  = $this->get_field_index_and_value( $input_field_array );
			$field_value = $field_data['value'];

			// Sanitize field value based on type.
			$field_value = $this->sanitize_field_value( $field_value );

			// Validate required fields.
			if ( $is_required && empty( $field_value ) ) {
				wc_add_notice(
					sprintf(
						/* translators: %s: field label */
						esc_html__( '%s is a required field.', 'extra-product-data-for-woocommerce' ),
						esc_html( $field_label )
					),
					'error'
				);
				$passed = false;
				continue;
			}

			// Skip validation if field is empty and not required.
			if ( empty( $field_value ) ) {
				continue;
			}

			// Perform type-specific validation.
			$validation_result = $this->validate_field_by_type( $field_value, $field_type, $input_field_array );
			if ( ! $validation_result['valid'] ) {
				wc_add_notice( $validation_result['message'], 'error' );
				$passed = false;
			}
		}

		return $passed;
	}

	/**
	 * Sanitizes a field value based on its type.
	 *
	 * @param mixed $field_value The raw field value.
	 * @return mixed The sanitized field value.
	 */
	private function sanitize_field_value( $field_value ) {
		// Handle array values (checkboxes, multi-select).
		if ( is_array( $field_value ) ) {
			return array_map(
				function ( $value ) {
					return sanitize_text_field( wp_unslash( $value ) );
				},
				$field_value
			);
		}

		// Handle string values.
		if ( ! is_string( $field_value ) && ! is_numeric( $field_value ) ) {
			return '';
		}

		// Basic sanitization for all field types.
		$sanitized = sanitize_text_field( wp_unslash( $field_value ) );

		return $sanitized;
	}

	/**
	 * Validates a field value based on its type.
	 *
	 * @param mixed  $field_value The sanitized field value.
	 * @param string $field_type The field type.
	 * @param array  $input_field_array The field configuration array.
	 * @return array An array with 'valid' boolean and 'message' string.
	 */
	private function validate_field_by_type( $field_value, string $field_type, array $input_field_array ): array {
		$field_label = sanitize_text_field( $input_field_array['label'] );

		switch ( $field_type ) {
			case 'email':
				if ( ! is_email( $field_value ) ) {
					return array(
						'valid'   => false,
						'message' => sprintf(
							/* translators: %s: field label */
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
							/* translators: %s: field label */
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
							/* translators: %s: field label */
							esc_html__( '%s is not a valid date.', 'extra-product-data-for-woocommerce' ),
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
							/* translators: %s: field label */
							esc_html__( '%s must be either "Yes" or "No".', 'extra-product-data-for-woocommerce' ),
							esc_html( $field_label )
						),
					);
				}
				break;

			case 'radio':
				if ( ! $this->validate_option_selection( $field_value, $input_field_array ) ) {
					return array(
						'valid'   => false,
						'message' => esc_html__( 'Invalid option selected for radio button.', 'extra-product-data-for-woocommerce' ),
					);
				}
				break;

			case 'checkbox':
				if ( ! $this->validate_option_selection( $field_value, $input_field_array ) ) {
					return array(
						'valid'   => false,
						'message' => esc_html__( 'Invalid option selected for checkbox.', 'extra-product-data-for-woocommerce' ),
					);
				}
				break;

			case 'select':
				if ( ! $this->validate_option_selection( $field_value, $input_field_array ) ) {
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
	 * Validates that selected options exist in the field's available options.
	 *
	 * @param mixed $field_value The field value (string or array).
	 * @param array $input_field_array The field configuration array.
	 * @return bool True if valid, false otherwise.
	 */
	private function validate_option_selection( $field_value, array $input_field_array ): bool {
		if ( ! isset( $input_field_array['options'] ) || ! is_array( $input_field_array['options'] ) ) {
			return false;
		}

		$valid_options = array_column( $input_field_array['options'], 'value' );
		if ( empty( $valid_options ) ) {
			return false;
		}

		// Convert field value to array for consistent comparison.
		$field_values = (array) $field_value;
		$field_values = array_map( 'sanitize_text_field', $field_values );

		// Check if all selected values are in the valid options.
		$intersect = array_intersect( $field_values, $valid_options );

		return ! empty( $intersect ) && count( $intersect ) === count( $field_values );
	}

	/**
	 * Gets the field index and value from POST data.
	 *
	 * Delegates to Exprdawc_Helper for normalized field handling.
	 *
	 * @param array $input_field_array The field configuration array.
	 * @return array An array with 'index' and 'value' keys.
	 */
	private function get_field_index_and_value( array $input_field_array ): array {
		$index = Exprdawc_Helper::get_field_index_from_label( $input_field_array['label'] );
		$value = Exprdawc_Helper::get_field_value_from_post( $index );

		return array(
			'index' => $index,
			'value' => $value,
		);
	}

	/**
	 * Calculates the price adjustment for a custom field.
	 *
	 * Delegates to Exprdawc_Order_Helper for consistent calculation logic.
	 *
	 * @param array $field_config The field configuration array.
	 * @param mixed $field_value The field value (string or array).
	 * @param float $base_price The base price for percentage calculations.
	 * @return float The calculated price adjustment.
	 */
	private function calculate_price_adjustment( array $field_config, $field_value, float $base_price = 0.0 ): float {
		return Exprdawc_Order_Helper::calculate_price_adjustment( $field_config, $field_value, $base_price );
	}

	/**
	 * Formats the cart display value with price adjustment.
	 *
	 * @param mixed $user_input_value The original user input value (string, float, or int).
	 * @param float $price_adjustment The calculated price adjustment.
	 * @param array $field_config The field configuration array.
	 * @return string The formatted cart display value.
	 */
	private function format_cart_value_with_price( $user_input_value, float $price_adjustment, array $field_config ): string {
		// Convert input value to string for display.
		$user_input_value = (string) $user_input_value;

		if ( 0.0 === $price_adjustment ) {
			return $user_input_value;
		}

		// For option-based or fixed price adjustments.
		if ( in_array( $field_config['type'], array( 'checkbox', 'radio', 'select' ), true ) || 'fixed' === $field_config['price_adjustment_type'] ) {
			$plus_minus = 0 < $price_adjustment ? '+' : '-';
			return $user_input_value . ' (' . $plus_minus . wc_price( abs( $price_adjustment ) ) . ')';
		}

		// For percentage adjustments.
		if ( 'percentage' === $field_config['price_adjustment_type'] ) {
			return $user_input_value . ' (+' . wc_price( $field_config['price_adjustment_value'] ) . '%)';
		}

		return $user_input_value;
	}

	/**
	 * This function is responsible for checking if the product supports the feature.
	 *
	 * @param bool   $supports The supports.
	 * @param string $feature The feature.
	 * @param object $product The product.
	 * @return bool
	 */
	public function exprdawc_check_product_support( bool $supports, string $feature, WC_Product $product ): bool { // phpcs:ignore
		// Check if the product supports the feature.
		if ( 'ajax_add_to_cart' === $feature && Exprdawc_Helper::check_required_fields( $product->get_id() ) ) {
			$supports = false;
		}
		return $supports;
	}

	/**
	 * Saves extra product data in the cart item data.
	 *
	 * This function is responsible for saving the extra product data in the cart item data when a product is added to the cart. It checks for the presence of custom fields and their values in the $_POST data, sanitizes the input, and then adds it to the cart item data array under the key 'post_data_product_item'.
	 *
	 * @param array $cart_item_data The existing cart item data.
	 * @param int   $product_id The ID of the product being added to the cart.
	 * @param int   $variation_id The ID of the variation being added to the cart (if applicable).
	 * @param int   $quantity The quantity of the product being added to the cart.
	 * @return array The modified cart item data with extra product data included.
	 */
	public function exprdawc_save_extra_product_data_in_cart( array $cart_item_data, int $product_id, int $variation_id, int $quantity ): array { // phpcs:ignore
		// Check if nonce is set and valid.
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
		$custom_fields = $product->get_meta( '_extra_product_fields', true );

		$cart_item_data_user_inputs = array();

		if ( ! empty( $custom_fields ) ) {
			foreach ( $custom_fields as $index_num => $input_field_array ) {

				// Get field index and value from POST data.
				$field_data  = $this->get_field_index_and_value( $input_field_array );
				$index       = $field_data['index'];
				$field_value = $field_data['value'];

				if ( isset( $field_value ) ) {
					switch ( $input_field_array['type'] ) {
						case 'text':
						case 'textarea':
							$user_input_value = sanitize_text_field( wp_unslash( $field_value ) );
							break;
						case 'number':
							$user_input_value = floatval( wp_unslash( $field_value ) );
							break;
						case 'email':
							$user_input_value = sanitize_email( wp_unslash( $field_value ) );
							break;
						case 'select':
						case 'radio':
						case 'checkbox':
							if ( is_array( $field_value ) ) {
								$user_input_value = implode( ', ', array_map( 'sanitize_text_field', wp_unslash( $field_value ) ) );
							} else {
								$user_input_value = sanitize_text_field( wp_unslash( $field_value ) );
							}
							break;
						case 'date':
							$user_input_value = sanitize_text_field( wp_unslash( $field_value ) );
							break;
						default:
							$user_input_value = sanitize_text_field( wp_unslash( $field_value ) );
							break;
					}

					// Create a value only for cart. (Extra price are in the value of price).
					$price_adjustment      = $this->calculate_price_adjustment( $input_field_array, $field_value, (float) $product->get_price() );
					$user_input_value_cart = $this->format_cart_value_with_price( $user_input_value, $price_adjustment, $input_field_array );

					// Save the user input in the cart item data.
					$cart_item_data_user_inputs[] = array(
						'index'      => $index,
						'value'      => $user_input_value,
						'field_raw'  => $input_field_array,
						'value_cart' => $user_input_value_cart,
					);
				}
			}
			// Save the user input in the cart item data.
			if ( ! empty( $cart_item_data_user_inputs ) ) {
				$cart_item_data['post_data_product_item'] = $cart_item_data_user_inputs;
			}
		}
		return $cart_item_data;
	}

	/**
	 * This function is responsible for displaying custom fields in the cart and checkout.
	 *
	 * @param array $item_data The item data.
	 * @param array $cart_item The cart item.
	 */
	public function exprdawc_display_fields_on_cart_and_checkout( array $item_data, array $cart_item ): array {

		if ( ! isset( $cart_item['post_data_product_item'] ) ) {
			return $item_data;
		}

		$product = wc_get_product( $cart_item['product_id'] );
		if ( ! $product ) {
			return $item_data;
		}

		$custom_fields = $product->get_meta( '_extra_product_fields', true );

		if ( ! empty( $custom_fields ) ) {
			if ( // phpcs:ignore
				( is_cart() && get_option( 'exprdawc_show_in_cart', 'yes' ) === 'yes' ) || // In Cart page and option is enabled. OR.
				( is_checkout() && get_option( 'exprdawc_show_in_checkout', 'yes' ) === 'yes' ) // In Checkout page and option is enabled.
			) {
				foreach ( $cart_item['post_data_product_item'] as $user_data ) {
					$show_empty_fields = get_option( 'exprdawc_show_empty_fields', 'yes' );
					if ( true === empty( $user_data['value'] ) && 'yes' !== $show_empty_fields ) {
						continue;
					}
					$item_data[] = array(
						'key'     => esc_html( $user_data['field_raw']['label'] ),
						'value'   => wc_clean( $user_data['value_cart'] ),
						'display' => wc_clean( $user_data['value_cart'] ),
					);
				}
			}
		}
		return $item_data;
	}

	/**
	 * This function is responsible for adjusting the cart item pricing.
	 *
	 * @param object $cart_object The cart object.
	 * @return void
	 */
	public function exprdawc_adjust_cart_item_pricing( object $cart_object ): void {

		// Loop through each cart item and adjust the price based on the extra user data.
		foreach ( $cart_object->get_cart() as $cart_item_key => $cart_item ) {
			if ( isset( $cart_item['post_data_product_item'] ) ) {
				foreach ( $cart_item['post_data_product_item'] as $user_data ) {
					if ( empty( $user_data['value'] ) ) {
						continue;
					}

					$base_price       = (float) $cart_item['data']->get_price();
					$price_adjustment = $this->calculate_price_adjustment( $user_data['field_raw'], $user_data['value'], $base_price );

					// Adjust the cart item price by adding the price adjustment to the base price.
					$cart_item['data']->set_price( $base_price + $price_adjustment );
				}
			}
		}
	}

	/**
	 * Displays custom fields in the order overview and on the payment page.
	 *
	 * This function is responsible for adding HTML code to the order overview page
	 * in the backend. It ensures that custom fields are visible to the admin when
	 * viewing order details and during the payment process.
	 *
	 * @param WC_Order_Item_Product $item The product item.
	 * @param string                $cart_item_key The cart item key.
	 * @param array                 $values The values of the custom fields.
	 * @param WC_Order              $order The order object.
	 * @return void
	 */
	public function exprdawc_add_extra_product_data_to_order( object $item, string $cart_item_key, array $values, object $order ): void { // phpcs:ignore

		if ( empty( $values['post_data_product_item'] ) ) {
			return;
		}

		$field_meta = array();
		// Loop through all fields and include the template.
		foreach ( $values['post_data_product_item'] as $field ) {
			$item->add_meta_data( sanitize_text_field( $field['field_raw']['label'] ), sanitize_text_field( $field['value'] ), true );
			$field_meta[] = array(
				'label'     => sanitize_text_field( $field['field_raw']['label'] ),
				'value'     => sanitize_text_field( $field['value'] ),
				'raw_field' => $field,
			);
		}

		if ( ! empty( $field_meta ) ) {
			$item->add_meta_data( '_meta_extra_product_data', $field_meta );
		}
	}
}
