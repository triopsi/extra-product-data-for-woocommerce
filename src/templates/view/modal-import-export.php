<?php
/**
 * Import/Export Modal Template
 *
 * Modal for importing and exporting custom fields as JSON
 *
 * @package Extra Product Data for WooCommerce
 * @author Daniel Drevermann <info@triopsi.com>
 * @copyright Copyright (c) 2024, IT-Dienstleistungen Drevermann
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div id="exprdawc-import-export-modal" class="exprdawc-modal">
	<div class="exprdawc-modal-overlay"></div>
	<div class="exprdawc-modal-content">
		<div class="exprdawc-modal-header">
			<h2 id="exprdawc-modal-title"><?php esc_html_e( 'Import/Export', 'extra-product-data-for-woocommerce' ); ?></h2>
			<button type="button" class="exprdawc-modal-close" aria-label="<?php esc_attr_e( 'Close', 'extra-product-data-for-woocommerce' ); ?>">
				<span class="dashicons dashicons-no"></span>
			</button>
		</div>

		<div class="exprdawc-modal-body">
			<!-- Export Section -->
			<div id="exprdawc-export-section" class="exprdawc-modal-section" style="display: none;">
				<h3><?php esc_html_e( 'Export Custom Fields', 'extra-product-data-for-woocommerce' ); ?></h3>
				<p class="description"><?php esc_html_e( 'Copy the JSON below to export your custom fields.', 'extra-product-data-for-woocommerce' ); ?></p>
				<textarea 
					id="exprdawc-export-textarea" 
					class="exprdawc-modal-textarea" 
					readonly 
					aria-label="<?php esc_attr_e( 'Export JSON', 'extra-product-data-for-woocommerce' ); ?>"
				></textarea>
				<div class="exprdawc-modal-actions">
					<button type="button" class="button button-primary" id="exprdawc-copy-export">
						<span class="dashicons dashicons-admin-page"></span>
						<?php esc_html_e( 'Copy to Clipboard', 'extra-product-data-for-woocommerce' ); ?>
					</button>
				</div>
			</div>

			<!-- Import Section -->
			<div id="exprdawc-import-section" class="exprdawc-modal-section" style="display: none;">
				<h3><?php esc_html_e( 'Import Custom Fields', 'extra-product-data-for-woocommerce' ); ?></h3>
				<p class="description"><?php esc_html_e( 'Paste the JSON code in the textarea below to import custom fields.', 'extra-product-data-for-woocommerce' ); ?></p>
				<textarea 
					id="exprdawc-import-textarea" 
					class="exprdawc-modal-textarea" 
					placeholder="<?php esc_attr_e( 'Paste exported JSON here...', 'extra-product-data-for-woocommerce' ); ?>"
					aria-label="<?php esc_attr_e( 'Import JSON', 'extra-product-data-for-woocommerce' ); ?>"
				></textarea>
				<div class="exprdawc-import-notice" style="display: none;" role="alert">
					<p class="exprdawc-import-notice-message"></p>
				</div>
				<div class="exprdawc-modal-actions">
					<button type="button" class="button button-primary" id="exprdawc-import-button">
						<span class="dashicons dashicons-upload"></span>
						<?php esc_html_e( 'Import Fields', 'extra-product-data-for-woocommerce' ); ?>
					</button>
				</div>
			</div>
		</div>

		<div class="exprdawc-modal-footer">
			<button type="button" class="button" id="exprdawc-modal-close-btn">
				<?php esc_html_e( 'Close', 'extra-product-data-for-woocommerce' ); ?>
			</button>
		</div>
	</div>
</div>
