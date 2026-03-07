<?php
/**
 * Select Field Template (Template Engine Version)
 *
 * Variables available:
 * - $field: Complete field configuration
 * - $required_string: Required indicator HTML
 * - $custom_attributes: Array of custom HTML attributes
 *
 * @package Extra_Product_Data_For_WooCommerce
 * @since 1.9.0
 */

use Triopsi\Exprdawc\Helpers\TemplateHelper as H;

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
	<?php if ( ! empty( $field['options'] ) && is_array( $field['options'] ) ) : ?>
		<select class="select <?php echo H::classes( $field['input_class'] ); ?>"
			id="<?php echo H::attr( $field['id'] ); ?>"
			name="<?php echo H::attr( $field['name'] ); ?>"
			<?php echo H::join( $custom_attributes ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		>
			<?php if ( ! empty( $field['placeholder'] ) ) : ?>
				<option value="" disabled selected>
					<?php echo H::e( $field['placeholder'] ); ?>
				</option>
			<?php endif; ?>

			<?php foreach ( $field['options'] as $option ) : ?>
				<?php
				$option_value = $option['value'] ?? '';
				$option_label = $option['label'] ?? '';
				$selected     = H::selected( $field['value'] ?? '', $option_value );

				// Build data attributes for price adjustment.
				$dataAttrs = array();
				if ( ! empty( $option['priceAdjustmentValue'] ) ) {
					$dataAttrs['price-adjustment']      = $option['priceAdjustmentValue'];
					$dataAttrs['price-adjustment-type'] = $option['price_adjustment_type'] ?? 'fixed';
					$dataAttrs['label']                 = $option_label;
				}
				?>

				<option value="<?php echo H::attr( $option_value ); ?>"
					<?php echo $selected; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<?php echo H::dataAttrs( $dataAttrs ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				>
					<?php echo H::e( $option_label ); ?>
				</option>
			<?php endforeach; ?>
		</select>
	<?php endif; ?>
</span>

<?php if ( ! empty( $field['description'] ) ) : ?>
	<span id="<?php echo H::attr( $field['id'] ); ?>-description"
		class="<?php echo H::classes( $field['description_class'] ); ?>">
		<?php echo H::e( $field['description'] ); ?>
	</span>
<?php endif; ?>
