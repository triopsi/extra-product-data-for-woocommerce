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

namespace Triopsi\Exprdawc;

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
		add_filter( 'add_to_cart_text', array( $this, 'exprdawc_change_add_to_cart_button_text' ), 15 );
		add_filter( 'woocommerce_product_add_to_cart_text', array( $this, 'exprdawc_change_add_to_cart_button_text' ), 10, 2 );
		add_filter( 'woocommerce_product_add_to_cart_url', array( $this, 'exprdawc_change_add_to_cart_url' ), 10, 2 );
		add_filter( 'woocommerce_add_to_cart_url', array( $this, 'exprdawc_change_add_to_cart_url' ), 10, 1 );

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
	public function exprdawc_has_options( $has_options, $product ) {
		if ( Exprdawc_Helper::check_required_fields( $product ) ) {
			$has_options = true;
		}
		return $has_options;
	}

	/**
	 * Prevents purchase at grouped level.
	 *
	 * Don't let products in the group with custom extra data fields be added to cart when viewing grouped products.
	 *
	 * @param bool       $purchasable Whether the product is purchasable.
	 * @param WC_Product $product The WooCommerce product object.
	 * @return bool Whether the product is purchasable.
	 */
	public function exprdawc_prevent_purchase_at_grouped_level( $purchasable, $product ) {
		if ( 'grouped' === $product->get_type() ) {
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
	public function exprdawc_change_add_to_cart_button_text( $text, $product ) {

		if ( ! $product->is_in_stock() ) {
			return $text;
		}

		if ( in_array( $product->get_type(), array( 'grouped', 'external' ), true ) ) {
			return $text;
		}

		$custom_fields = $product->get_meta( '_extra_product_fields', true );

		if ( ! empty( $custom_fields ) ) {
			$text_from_settings = get_option( 'exprdawc_custom_add_to_cart_text' );
			if ( ! empty( $text_from_settings ) ) {
				return $text_from_settings;
			}
		}
		return $text;
	}

	/**
	 * Changes the URL of the add-to-cart button based on the product type.
	 *
	 * This function changes the URL of the add-to-cart button based on the product type.
	 * The URL is changed only if the product is not of type 'grouped' or 'external'.
	 * The URL is also changed if custom fields are associated with the product and the product
	 * is not being viewed on the single product page.
	 *
	 * @param string     $url The URL of the add-to-cart button.
	 * @param WC_Product $product The WooCommerce product object.
	 * @return string The modified URL of the add-to-cart button.
	 */
	public function exprdawc_change_add_to_cart_url( $url, $product = null ) {
		if ( null === $product ) {
			global $product;
		}
		if ( ! is_a( $product, 'WC_Product' ) ) {
			return $url;
		}

		if ( ! is_single( $product->get_id() ) && in_array( $product->get_type(), array( 'subscription', 'simple' ), true ) ) {
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
	public function exprdawc_add_frontend_styles_scripts() {
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
	public function exprdawc_display_custom_fields_on_product_page() {
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
	public function exprdawc_validate_custom_fields( $passed, $product_id, $quantity ) { // phpcs:ignore

		// if $_Post not have the exprdawc_custom_field_input array then return true.
		if ( ! isset( $_POST['exprdawc_custom_field_input'] ) ) { // phpcs:ignore
			return $passed;
		}

		$product = wc_get_product( $product_id );
		if ( ! $product ) {
			return $passed;
		}

		$custom_fields = $product->get_meta( '_extra_product_fields', true );

		if ( ! empty( $custom_fields ) ) {

			// Check if the product can only be purchased once.
			if ( $product->get_sold_individually() ) {
				foreach ( WC()->cart->get_cart() as $cart_item ) {
					if ( $cart_item['product_id'] === $product_id ) {
						wc_add_notice( __( 'This product can only be purchased once.', 'extra-product-data-for-woocommerce' ), 'error' );
						return false;
					}
				}
			}
			foreach ( $custom_fields as $index_num => $input_field_array ) {

				// Actual label lowercase and without spaces and _ are -.
				$index = esc_attr( strtolower( str_replace( array( ' ', '-' ), '_', sanitize_title( $input_field_array['label'] ) ) ) );

				// Get the field value from the $_POST array.
				$field_value = isset( $_POST['exprdawc_custom_field_input'][ $index ] ) ? $_POST['exprdawc_custom_field_input'][ $index ] : ''; // phpcs:ignore

				// Handle different field types.
				if ( is_array( $field_value ) ) {
					$field_value = array_map( 'sanitize_text_field', $field_value );
				} else {
					$field_value = sanitize_text_field( $field_value );
				}

				if ( ! empty( $input_field_array['required'] ) && empty( $field_value ) ) {
					/* translators: %s: field label */
					wc_add_notice( sprintf( esc_html__( '%s is a required field.', 'extra-product-data-for-woocommerce' ), $input_field_array['label'] ), 'error' );
					$passed = false;
				}

				// Additional validation based on field type.
				switch ( $input_field_array['type'] ) {
					case 'email':
						if ( ! empty( $field_value ) && ! is_email( $field_value ) ) {
							/* translators: %s: field label */
							wc_add_notice( sprintf( esc_html__( '%s is not a valid email address.', 'extra-product-data-for-woocommerce' ), $input_field_array['label'] ), 'error' );
							$passed = false;
						}
						break;
					case 'number':
						if ( ! empty( $field_value ) && ! is_numeric( $field_value ) ) {
							/* translators: %s: field label */
							wc_add_notice( sprintf( esc_html__( '%s must be a number.', 'extra-product-data-for-woocommerce' ), $input_field_array['label'] ), 'error' );
							$passed = false;
						}
						break;
					case 'date':
						if ( ! empty( $field_value ) && ! strtotime( $field_value ) ) {
							/* translators: %s: field label */
							wc_add_notice( sprintf( esc_html__( '%s is not a valid date.', 'extra-product-data-for-woocommerce' ), $input_field_array['label'] ), 'error' );
							$passed = false;
						}
						break;
					case 'yes-no':
						if ( ! empty( $field_value ) && ! in_array( $field_value, array( 'yes', 'no' ), true ) ) {
							/* translators: %s: field label */
							wc_add_notice( sprintf( esc_html__( '%s must be either "Yes" or "No".', 'extra-product-data-for-woocommerce' ), $input_field_array['label'] ), 'error' );
							$passed = false;
						}
						break;
					case 'radio':
						$array_colum = array_column( $input_field_array['options'], 'value' );
						$intersect   = array_intersect( (array) $field_value, $array_colum );
						if ( ! empty( $field_value ) && empty( $intersect ) ) {
							wc_add_notice( esc_html__( 'Invalid option selected for radio button.', 'extra-product-data-for-woocommerce' ), 'error' );
							$passed = false;
						}
						break;
					case 'checkbox':
						$array_colum = array_column( $input_field_array['options'], 'value' );
						$intersect   = array_intersect( (array) $field_value, $array_colum );
						if ( ! empty( $field_value ) && empty( $intersect ) ) {
							wc_add_notice( esc_html__( 'Invalid option selected for checkbox.', 'extra-product-data-for-woocommerce' ), 'error' );
							$passed = false;
						}
						break;
					case 'select':
						$array_colum = array_column( $input_field_array['options'], 'value' );
						$intersect   = array_intersect( (array) $field_value, $array_colum );
						if ( ! empty( $field_value ) && empty( $intersect ) ) {
							wc_add_notice( esc_html__( 'Invalid option selected for select field.', 'extra-product-data-for-woocommerce' ), 'error' );
							$passed = false;
						}
						break;
				}
			}
		}
		return $passed;
	}

	/**
	 * This function is responsible for checking if the product supports the feature.
	 *
	 * @param bool   $supports The supports.
	 * @param string $feature The feature.
	 * @param object $product The product.
	 * @return bool
	 */
	public function exprdawc_check_product_support( $supports, $feature, $product ) {
		// Check if the product supports the feature.
		if ( 'ajax_add_to_cart' === $feature && Exprdawc_Helper::check_required_fields( $product->get_id() ) ) {
			$supports = false;
		}
		return $supports;
	}

	/**
	 * Saves extra product data in the cart item data.
	 *
	 * This function is responsible for saving the extra product data in the cart item data when a product is added to the cart. It checks for the presence of custom fields and their values in the $_POST data, sanitizes the input, and then adds it to the cart item data array under the key 'extra_user_data'.
	 *
	 * @param array $cart_item_data The existing cart item data.
	 * @param int   $product_id The ID of the product being added to the cart.
	 * @param int   $variation_id The ID of the variation being added to the cart (if applicable).
	 * @param int   $quantity The quantity of the product being added to the cart.
	 * @return array The modified cart item data with extra product data included.
	 */
	public function exprdawc_save_extra_product_data_in_cart( $cart_item_data, $product_id, $variation_id, $quantity ) { // phpcs:ignore
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

				// Actual label lowercase and without spaces and _ are -.
				$index = esc_attr( strtolower( str_replace( array( ' ', '-' ), '_', sanitize_title( $input_field_array['label'] ) ) ) );

				// Get the field value from the $_POST array.
				$field_value = isset( $_POST['exprdawc_custom_field_input'][ $index ] ) ? $_POST['exprdawc_custom_field_input'][ $index ] : ''; // phpcs:ignore

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
						case 'url':
							$user_input_value = esc_url_raw( wp_unslash( $field_value ) );
							break;
						case 'tel':
							$user_input_value = sanitize_text_field( wp_unslash( $field_value ) );
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
					$user_input_value_cart = $user_input_value;

					if ( $input_field_array['adjust_price'] ) {
						if ( in_array( $input_field_array['type'], array( 'checkbox', 'radio', 'select' ), true ) ) {
							$total_adjustment = 0;
							foreach ( $input_field_array['options'] as $option ) {
								if ( is_array( $field_value ) && in_array( $option['value'], $field_value, true ) ) {
									if ( 'fixed' === $option['price_adjustment_type'] ) {
										$total_adjustment += $option['price_adjustment_value'];
									} elseif ( 'percent' === $option['price_adjustment_type'] ) {
										$total_adjustment += ( $input_field_array['price_adjustment_value'] / 100 ) * $option['price_adjustment_value'];
									}
								} elseif ( $option['value'] === $field_value ) {
									if ( 'fixed' === $option['price_adjustment_type'] ) {
										$total_adjustment = $option['price_adjustment_value'];
									} elseif ( 'percent' === $option['price_adjustment_type'] ) {
										$total_adjustment = ( $input_field_array['price_adjustment_value'] / 100 ) * $option['price_adjustment_value'];
									}
									break;
								}
							}
							if ( 0 !== $total_adjustment ) {
								$plus_minus            = 0 < $total_adjustment ? '+' : '-';
								$user_input_value_cart = $user_input_value . ' (' . $plus_minus . wc_price( $total_adjustment ) . ')';
							}
						} elseif ( 'fixed' === $input_field_array['price_adjustment_type'] ) {
								$plus_minus            = 0 < $input_field_array['price_adjustment_value'] ? '+' : '-';
								$user_input_value_cart = $user_input_value . ' (' . $plus_minus . wc_price( $input_field_array['price_adjustment_value'] ) . ')';
						} elseif ( 'percent' === $input_field_array['price_adjustment_type'] ) {
							$user_input_value_cart = $user_input_value . ' (+' . wc_price( $input_field_array['price_adjustment_value'] ) . '%)';
						}
					}

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
				$cart_item_data['extra_user_data'] = $cart_item_data_user_inputs;
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
	public function exprdawc_display_fields_on_cart_and_checkout( $item_data, $cart_item ) {

		if ( ! isset( $cart_item['extra_user_data'] ) ) {
			return $item_data;
		}

		$product = wc_get_product( $cart_item['product_id'] );
		if ( ! $product ) {
			return $item_data;
		}

		$custom_fields = $product->get_meta( '_extra_product_fields', true );

		if ( ! empty( $custom_fields ) ) {
			if ( ( is_cart() && get_option( 'exprdawc_show_in_cart', 'yes' ) === 'yes' ) || ( is_checkout() && get_option( 'exprdawc_show_in_checkout', 'yes' ) === 'yes' ) ) {
				foreach ( $cart_item['extra_user_data'] as $user_data ) {
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
	public function exprdawc_adjust_cart_item_pricing( $cart_object ) {
		if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
			return;
		}

		if ( is_admin() && defined( 'DOING_AJAX' ) && ! is_ajax() ) {
			return;
		}

		if ( did_action( 'woocommerce_before_calculate_totals' ) >= 2 ) {
			return;
		}

		foreach ( $cart_object->get_cart() as $cart_item_key => $cart_item ) {
			if ( isset( $cart_item['extra_user_data'] ) ) {
				foreach ( $cart_item['extra_user_data'] as $user_data ) {
					$price_adjustment = 0;
					if ( ( empty( $user_data['value'] ) ) ) {
						continue;
					}
					if ( $user_data['field_raw']['adjust_price'] ) {
						if ( in_array( $user_data['field_raw']['type'], array( 'checkbox', 'radio', 'select' ), true ) ) {
							$total_adjustment = 0;
							foreach ( $user_data['field_raw']['options'] as $option ) {
								$cart_value = explode( ', ', $user_data['value'] );
								if ( is_array( $cart_value ) && in_array( $option['value'], $cart_value, true ) ) {
									if ( 'fixed' === $option['price_adjustment_type'] ) {
										$total_adjustment += $option['price_adjustment_value'];
									} elseif ( 'percent' === $option['price_adjustment_type'] ) {
										$total_adjustment += ( $cart_item['data']->get_price() / 100 ) * $option['price_adjustment_value'];
									}
								} elseif ( $option['value'] === $user_data['value'] ) {
									if ( 'fixed' === $option['price_adjustment_type'] ) {
										$total_adjustment = $option['price_adjustment_value'];
									} elseif ( 'percent' === $option['price_adjustment_type'] ) {
										$total_adjustment = ( $cart_item['data']->get_price() / 100 ) * $option['price_adjustment_value'];
									}
									break;
								}
							}
							$price_adjustment = $total_adjustment;
						} elseif ( 'fixed' === $user_data['field_raw']['price_adjustment_type'] ) {
							$price_adjustment = $user_data['field_raw']['price_adjustment_value'];
						} elseif ( 'percent' === $user_data['field_raw']['price_adjustment_type'] ) {
							$price_adjustment = ( $cart_item['data']->get_price() / 100 ) * $user_data['field_raw']['price_adjustment_value'];
						}
					}
					$cart_item['data']->set_price( $cart_item['data']->get_price() + $price_adjustment );
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
	public function exprdawc_add_extra_product_data_to_order( $item, $cart_item_key, $values, $order ) { // phpcs:ignore

		if ( empty( $values['extra_user_data'] ) ) {
			return;
		}

		$field_meta = array();
		// Loop through all fields and include the template.
		foreach ( $values['extra_user_data'] as $field ) {
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
