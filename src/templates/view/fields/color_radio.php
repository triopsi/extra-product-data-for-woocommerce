<?php
/**
 * Radio Field Template (Template Engine Version)
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

<label class="<?php echo H::classes( $field['label_class'] ); ?>">
	<?php echo H::e( $field['label'] ); ?>
	<?php echo $required_string; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
</label>

<span class="<?php echo H::classes( $field['input_wrapper_class'] ); ?>">
	<?php if ( ! empty( $field['options'] ) && is_array( $field['options'] ) ) : ?>
	<span class="exprdawc-color-radio-options">
		<?php foreach ( $field['options'] as $option ) : ?>
			<?php
			$option_value = $option['value'] ?? '';
			$option_label = $option['label'] ?? '';
			$checked      = H::checked( $field['value'] ?? '', $option_value );
			$option_id    = H::id( $field['css_id'] . '-' . $option_value );

			// Build data attributes for price adjustment.
			$dataAttrs = array();
			if ( ! empty( $option['priceAdjustmentValue'] ) ) {
				$dataAttrs['price-adjustment']      = $option['priceAdjustmentValue'];
				$dataAttrs['price-adjustment-type'] = $option['price_adjustment_type'] ?? 'fixed';
				$dataAttrs['label']                 = $option_label;
			}
			?>

			<input type="radio"
				id="<?php echo H::attr( $option_id ); ?>"
				name="<?php echo H::attr( $field['name'] ); ?>"
				value="<?php echo H::attr( $option_value ); ?>"
				<?php echo $checked; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				class="<?php echo H::classes( $field['input_class'] ); ?>"
				<?php echo H::join( $custom_attributes ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<?php echo H::dataAttrs( $dataAttrs ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			/>
			<?php
				// Define inline styles for color swatch based on field settings.
				$swatchStyle = sprintf(
					'background-color: %s; width: %s; height: %s;',
					esc_attr( $option_value ),
					esc_attr( $field['color_radio_size'] ),
					esc_attr( $field['color_radio_size'] )
				);

				// if type is badget, than ration 20 for width and height
				if ( 'badget' === $field['color_radio_style'] ) {

					// color_radio_style have px or antoher unit, so we need to calculate the size for badge style. Remove this and store the size for badge in the database when we have time.
					$size = (float) $field['color_radio_size'];
					$unit = preg_replace( '/[\d.]/', '', $field['color_radio_size'] );


					$swatchStyle = sprintf(
						'background-color: %s; width: %s; height: %s;',
						esc_attr( $option_value ),
						esc_attr( $field['color_radio_size'] ),
						esc_attr( $size * 0.4 . $unit )
					);
				}
				$swatchClass = sprintf( 'exprdawc-color-swatch-%s', esc_attr( $field['color_radio_style'] ) );
			?>
			<label for="<?php echo H::attr( $option_id ); ?>" class="exprdawc-label-color-radio">
				<span class="<?php echo H::attr( $swatchClass ); ?>" style="<?php echo H::attr( $swatchStyle ); ?>"></span>
				<?php if ( ! empty( $field['color_radio_show_label'] ) ) : ?>
					<span class="exprdawc-color-label"><?php echo $option_label; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				<?php endif; ?>
			</label>

		<?php endforeach; ?>
	</span>
	<?php endif; ?>
</span>

<?php if ( ! empty( $field['description'] ) ) : ?>
	<span id="<?php echo H::attr( $field['css_id'] ); ?>-description"
		class="<?php echo H::classes( $field['description_class'] ); ?>">
		<?php echo H::e( $field['description'] ); ?>
	</span>
<?php endif; ?>
