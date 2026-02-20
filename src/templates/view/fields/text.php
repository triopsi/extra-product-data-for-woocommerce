<?php
/**
 * Text Field Template (Template Engine Version)
 *
 * Variables available:
 * - $field: Complete field configuration
 * - $required_string: Required indicator HTML
 * - $custom_attributes: Array of custom HTML attributes
 *
 * @package Extra_Product_Data_For_WooCommerce
 * @since 1.9.0
 */

use Triopsi\Exprdawc\Helper\Exprdawc_Template_Helpers as H;

// phpcs:ignoreFile
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Alias field_args as field for template.
$field = $field_args ?? array();
?>

<label for="<?php echo H::attr( $field['id'] ); ?>" 
	class="<?php echo H::classes( $field['label_class'] ); ?>">
	<?php echo H::e( $field['label'] ); ?>
	<?php echo $required_string; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
</label>

<span class="<?php echo H::classes( $field['input_wrapper_class'] ); ?>">
	<input type="<?php echo H::attr( $field['type'] ); ?>"
		class="<?php echo H::classes( $field['input_class'] ); ?>"
		name="<?php echo H::attr( $field['name'] ); ?>"
		id="<?php echo H::attr( $field['id'] ); ?>"
		placeholder="<?php echo H::attr( $field['placeholder'] ?? '' ); ?>"
		value="<?php echo H::attr( $field['value'] ?? '' ); ?>"
		<?php echo H::join( $custom_attributes ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	/>

	<?php if ( ! empty( $field['description'] ) ) : ?>
		<span id="<?php echo H::attr( $field['id'] ); ?>-description"
			class="<?php echo H::classes( $field['description_class'] ); ?>">
			<?php echo H::e( $field['description'] ); ?>
		</span>
	<?php endif; ?>
</span>
