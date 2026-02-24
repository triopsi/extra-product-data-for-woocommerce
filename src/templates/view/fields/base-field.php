<?php
/**
 * Base Field Wrapper Template
 *
 * This template wraps all field types and can be extended.
 *
 * @package Extra_Product_Data_For_WooCommerce
 * @var array $field Field arguments
 * @var string $required_string Required indicator string
 * @var array $custom_attributes Custom HTML attributes
 */

use Triopsi\Exprdawc\Helper\Exprdawc_Template_Helpers as H;

// phpcs:ignoreFile
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div id="{{ field.id }}-wrapper-field" class="{{ H::classes( field.wrapper_class ) }}">
	{% include 'fields/partials/field-content.php' %}
</div>
