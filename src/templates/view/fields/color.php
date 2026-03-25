<?php
/**
 * Color Field Template
 *
 * Variables available:
 * - $field: Complete field configuration
 * - $required_string: Required indicator HTML
 * - $custom_attributes: Array of custom HTML attributes
 *
 * @package Extra_Product_Data_For_WooCommerce
 * @since 3.0.0
 */

use Triopsi\Exprdawc\Helpers\TemplateHelper as H;

// phpcs:ignoreFile
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Alias field_args as field for template.
$field = $field_args ?? array();
?>

<label for="<?php echo H::attr( $field['css_id'] ); ?>" 
	class="<?php echo H::classes( $field['label_class'] ); ?>">
	<?php echo H::e( $field['label'] ); ?>
	<?php echo $required_string; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
</label>

<span class="<?php echo H::classes( $field['input_wrapper_class'] ); ?>">
	<input type="color"
		class="<?php echo H::classes( $field['input_class'] ); ?>"
		name="<?php echo H::attr( $field['name'] ); ?>"
		id="<?php echo H::attr( $field['css_id'] ); ?>"
		value="<?php echo H::attr( $field['value'] ?? '#000000' ); ?>"
		<?php echo H::join( $custom_attributes ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	/>

	<?php if ( ! empty( $field['description'] ) ) : ?>
		<span id="<?php echo H::attr( $field['css_id'] ); ?>-description"
			class="<?php echo H::classes( $field['description_class'] ); ?>">
			<?php echo H::e( $field['description'] ); ?>
		</span>
	<?php endif; ?>
</span>
