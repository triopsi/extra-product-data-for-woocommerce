/**
 * Import/Export Modal Handler
 *
 * Manages import/export functionality using a modal dialog
 *
 * @package Extra Product Data for WooCommerce
 * @author Daniel Drevermann <info@triopsi.com>
 */

class ExprdawcImportExportModal {
	/**
	 * Constructor
	 */
	constructor() {
		this.modal = document.getElementById('exprdawc-import-export-modal');
		if (!this.modal) {
			console.warn('ExprdawcImportExportModal: Modal element not found');
			return;
		}

		this.exportSection = document.getElementById('exprdawc-export-section');
		this.importSection = document.getElementById('exprdawc-import-section');
		this.modalTitle = document.getElementById('exprdawc-modal-title');
		this.exportTextarea = document.getElementById('exprdawc-export-textarea');
		this.importTextarea = document.getElementById('exprdawc-import-textarea');
		this.copyExportBtn = document.getElementById('exprdawc-copy-export');
		this.importBtn = document.getElementById('exprdawc-import-button');
		this.modalCloseBtn = document.getElementById('exprdawc-modal-close-btn');
		this.modalCloseIcon = document.querySelector('.exprdawc-modal-close');
		this.importNotice = document.querySelector('.exprdawc-import-notice');
		this.importNoticeMessage = document.querySelector('.exprdawc-import-notice-message');

		this.init();
	}

	/**
	 * Initialize event listeners
	 */
	init() {
		
		// Find all export and import buttons
		const exportLink = document.querySelector('a.exprdawc-export');
		const importLink = document.querySelector('a.exprdawc-import');
		
		// Direct click handlers
		if (exportLink) {
			exportLink.addEventListener('click', (e) => {
				e.preventDefault();
				console.log('Export button clicked');
				this.openExportModal();
			});
		}
		
		if (importLink) {
			importLink.addEventListener('click', (e) => {
				e.preventDefault();
				console.log('Import button clicked');
				this.openImportModal();
			});
		}

		// Copy to clipboard
		if (this.copyExportBtn) {
			this.copyExportBtn.addEventListener('click', () => this.copyToClipboard());
		}

		// Import button
		if (this.importBtn) {
			this.importBtn.addEventListener('click', () => this.handleImport());
		}

		// Close buttons
		if (this.modalCloseBtn) {
			this.modalCloseBtn.addEventListener('click', () => this.closeModal());
		}

		if (this.modalCloseIcon) {
			this.modalCloseIcon.addEventListener('click', () => this.closeModal());
		}

		// Close on overlay click
		const overlay = this.modal.querySelector('.exprdawc-modal-overlay');
		if (overlay) {
			overlay.addEventListener('click', () => this.closeModal());
		}

		// Close on escape key
		document.addEventListener('keydown', (e) => {
			if (e.key === 'Escape' && this.modal.classList.contains('active')) {
				this.closeModal();
			}
		});
	}

	/**
	 * Open export modal
	 */
	openExportModal() {
		// Update title
		this.modalTitle.textContent = exprdawc_admin_meta_boxes.exportTitle || 'Export Custom Fields';

		// Get export string
		const exportString = document.getElementById('exprdawc_export_string');
		
		if (!exportString || !exportString.value) {
			this.showNotice(
				exprdawc_admin_meta_boxes.emptyExportMsg || 'No custom fields to export.',
				'error'
			);
			return;
		}

		// Set textarea content
		this.exportTextarea.value = exportString.value;

		// Show/hide sections
		this.exportSection.style.display = 'block';
		this.importSection.style.display = 'none';

		// Clear import textarea and notice
		this.importTextarea.value = '';
		this.hideNotice();

		// Open modal
		this.openModal();
	}

	/**
	 * Open import modal
	 */
	openImportModal() {
		// Update title
		this.modalTitle.textContent = exprdawc_admin_meta_boxes.importTitle || 'Import Custom Fields';

		// Show/hide sections
		this.exportSection.style.display = 'none';
		this.importSection.style.display = 'block';

		// Clear textareas
		this.importTextarea.value = '';
		this.hideNotice();

		// Open modal
		this.openModal();
	}

	/**
	 * Open modal
	 */
	openModal() {
		this.modal.classList.add('active');
		document.body.style.overflow = 'hidden';
	}

	/**
	 * Close modal
	 */
	closeModal() {
		this.modal.classList.remove('active');
		document.body.style.overflow = '';
	}

	/**
	 * Copy to clipboard
	 */
	async copyToClipboard() {
		const text = this.exportTextarea.value;
		
		if (!text) {
			this.showNotice(
				exprdawc_admin_meta_boxes.emptyExportMsg || 'Nothing to copy.',
				'error'
			);
			return;
		}

		try {
			await navigator.clipboard.writeText(text);
			const originalText = this.copyExportBtn.innerHTML;
			this.copyExportBtn.innerHTML = '<span class="dashicons dashicons-yes-alt"></span>' + 
				(exprdawc_admin_meta_boxes.copySuccessMsg || 'Copied!');
			this.copyExportBtn.disabled = true;

			setTimeout(() => {
				this.copyExportBtn.innerHTML = originalText;
				this.copyExportBtn.disabled = false;
			}, 2000);
		} catch (err) {
			console.error('Could not copy text: ', err);
			this.showNotice(
				exprdawc_admin_meta_boxes.copyErrorMsg || 'Failed to copy to clipboard.',
				'error'
			);
		}
	}

	/**
	 * Handle import
	 */
	handleImport() {
		const jsonString = this.importTextarea.value.trim();

		if (!jsonString) {
			this.showNotice(
				exprdawc_admin_meta_boxes.enterExportString || 'Please paste the JSON code.',
				'error'
			);
			return;
		}

		// Validate JSON
		try {
			JSON.parse(jsonString);
		} catch (e) {
			this.showNotice(
				exprdawc_admin_meta_boxes.invalidJsonMsg || 'Invalid JSON format. Please check your input.',
				'error'
			);
			return;
		}

		// Confirm import
		const confirmMessage = exprdawc_admin_meta_boxes.sureImportQuestion || 
			'Are you sure you want to import these fields? This will replace all existing fields.';
		
		if (!confirm(confirmMessage)) {
			return;
		}

		// Send import request
		this.sendImportRequest(jsonString);
	}

	/**
	 * Send import request via AJAX
	 *
	 * @param {string} jsonString - JSON string to import
	 */
	sendImportRequest(jsonString) {
		const productId = document.getElementById('post_ID').value;

		// Show loading state
		this.importBtn.disabled = true;
		const originalText = this.importBtn.innerHTML;
		this.importBtn.innerHTML = '<span class="dashicons dashicons-admin-generic" style="animation: spin 1s linear infinite;"></span>' +
			(exprdawc_admin_meta_boxes.importingMsg || 'Importing...');

		jQuery.ajax({
			url: ajaxurl,
			method: 'POST',
			data: {
				action: 'exprdawc_import_custom_fields',
				product_id: productId,
				export_string: jsonString,
				security: exprdawc_admin_meta_boxes.edit_exprdawc_nonce
			},
			success: (response) => {
				this.importBtn.disabled = false;
				this.importBtn.innerHTML = originalText;

				if (response.success) {
					this.showNotice(
						exprdawc_admin_meta_boxes.importSuccessMsg || 'Fields imported successfully!',
						'success'
					);
					
					// Reload page after brief delay
					setTimeout(() => {
						window.location.reload();
					}, 1500);
				} else {
					this.showNotice(
						response.data || exprdawc_admin_meta_boxes.importErrorMsg || 'An error occurred during import.',
						'error'
					);
				}
			},
			error: () => {
				this.importBtn.disabled = false;
				this.importBtn.innerHTML = originalText;

				this.showNotice(
					exprdawc_admin_meta_boxes.importErrorMsg || 'Failed to communicate with server.',
					'error'
				);
			}
		});
	}

	/**
	 * Show notice/alert message
	 *
	 * @param {string} message - Message to show
	 * @param {string} type - Notice type: 'success', 'error', 'warning'
	 */
	showNotice(message, type = 'info') {
		if (!this.importNotice) {
			return;
		}

		this.importNoticeMessage.textContent = message;
		this.importNotice.className = 'exprdawc-import-notice ' + type;
		this.importNotice.style.display = 'block';
	}

	/**
	 * Hide notice message
	 */
	hideNotice() {
		if (this.importNotice) {
			this.importNotice.style.display = 'none';
			this.importNotice.className = 'exprdawc-import-notice';
		}
	}
}

// Initialize modal when document is ready
if (document.readyState === 'loading') {
	document.addEventListener('DOMContentLoaded', () => {
		new ExprdawcImportExportModal();
	});
} else {
	new ExprdawcImportExportModal();
}
