<?php
/**
 * Checkbox Field Template (Template Engine Version)
 *
 * Variables available:
 * - $field: Complete field configuration
 * - $required_string: Required indicator HTML
 * - $custom_attributes: Array of custom HTML attributes
 *
 * @package Extra_Product_Data_For_WooCommerce\Helper
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

<label class="<?php echo H::classes( $field['label_class'] ); ?>">
	<?php echo H::e( $field['label'] ); ?>
	<?php echo $required_string; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
</label>

<span class="<?php echo H::classes( $field['input_wrapper_class'] ); ?>">
	<?php if ( ! empty( $field['options'] ) && is_array( $field['options'] ) ) : ?>
		<?php foreach ( $field['options'] as $option ) : ?>
			<?php
			$option_value = $option['value'] ?? '';
			$option_label = $option['label'] ?? '';
			$checked      = H::checked( $field['value'], $option_value );
			$option_id    = H::id( $field['id'] . '-' . $option_value );

			// Build data attributes for price adjustment.
			$data_attrs = array();
			if ( ! empty( $option['price_adjustment_value'] ) ) {
				$data_attrs['price-adjustment']      = $option['price_adjustment_value'];
				$data_attrs['price-adjustment-type'] = $option['price_adjustment_type'] ?? 'fixed';
				$data_attrs['label']                 = $option_label;
			}
			?>

			<div class="exprdawc-checkbox-option">
				<input type="checkbox"
					id="<?php echo H::attr( $option_id ); ?>"
					name="<?php echo H::attr( $field['name'] ); ?>[]"
					value="<?php echo H::attr( $option_value ); ?>"
					<?php echo $checked; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					class="<?php echo H::classes( $field['input_class'] ); ?>"
					<?php echo H::join( $custom_attributes ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<?php echo H::data_attrs( $data_attrs ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				/>
				<label for="<?php echo H::attr( $option_id ); ?>" class="exprdawc-label-checkbox">
					<?php echo H::e( $option_label ); ?>
				</label>
			</div>

		<?php endforeach; ?>

		<?php if ( isset( $field['unchecked_value'] ) ) : ?>
			<input type="hidden"
				name="<?php echo H::attr( $field['id'] ); ?>[]"
				value="<?php echo H::attr( $field['unchecked_value'] ); ?>"
			/>
		<?php endif; ?>
	<?php endif; ?>
</span>

<?php if ( ! empty( $field['description'] ) ) : ?>
	<span id="<?php echo H::attr( $field['id'] ); ?>-description"
		class="<?php echo H::classes( $field['description_class'] ); ?>">
		<?php echo H::e( $field['description'] ); ?>
	</span>
<?php endif; ?>

<?php if ( ! empty( $field['required'] ) ) : ?>
	<script>
		jQuery(document).ready(function($) {
			const $checkboxGroups = $('.<?php echo H::js( $field['id'] . '-input-wrapper' ); ?>, .exprdawc-field-input-wrapper-required');
			$checkboxGroups.each(function () {
				const $checkboxes = $(this).find('input[type="checkbox"]');
				$checkboxes.on('change', function() {
					const isChecked = $checkboxes.is(":checked");
					$checkboxes.prop("required", !isChecked);
				}).trigger('change');
			});
		});
	</script>
<?php endif; ?>
