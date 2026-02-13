/* global wc_exprdawc_admin_order_params, woocommerce_admin_meta_boxes, wcBackboneModal */
jQuery(function ($) {

    /**
     * Extra Product Data for WooCommerce Admin Order
     * @class ExtraProductDataAdminOrder
     * @description Handles the functionality for the extra product data in the WooCommerce admin order page
     * @since 1.0.0
     * @version 1.0.0
     * @package ExtraProductDataForWooCommerce/JS
     * @license GPL-2.0+
     * @link https://www.triopsi.dev
     */
    class ExtraProductDataAdminOrder {
        constructor() {
            this.$orderItemsContainer = $('#woocommerce-order-items');
            this.modalView = null;
            this.initialize();
        }

        // Initialize event handlers
        initialize() {
            this.setupEventHandlers();
        }

        // Setup event handlers for the order items container
        setupEventHandlers() {
            this.$orderItemsContainer.on('click', 'button.exprdawc_edit_addons', { action: 'edit' }, this.handleEditButtonClick.bind(this));
        }

        // Handle the click event for the edit button
        handleEditButtonClick(event) {
            event.preventDefault();

            // Extend wcBackboneModal to create a custom modal view
            const CustomBackboneModal = $.WCBackboneModal.View.extend({
                addButton: this.handleDoneButtonClick.bind(this)
            });

            // Get the closest table row and retrieve the order item ID
            const $itemRow = $(event.currentTarget).closest('tr.item');
            const orderItemId = $itemRow.attr('data-order_item_id');

            // Create a new instance of the custom modal view
            this.modalView = new CustomBackboneModal({
                target: 'wc-modal-edit-exprdawc',
                string: {
                    action: wc_exprdawc_admin_order_params.i18n_edit,
                    item_id: orderItemId
                }
            });

            // Populate the form inside the modal
            this.populateModalForm();

            return false;
        }

        // Populate the form inside the modal with data
        populateModalForm() {
            this.blockUI(this.modalView.$el.find('.wc-backbone-modal-content'));
            const requestData = {
                action: 'woocommerce_configure_exprdawc_order_item',
                item_id: this.modalView._string.item_id,
                dataType: 'json',
                order_id: woocommerce_admin_meta_boxes.post_id,
                security: wc_exprdawc_admin_order_params.edit_exprdawc_nonce
            };
            $.post(woocommerce_admin_meta_boxes.ajax_url, requestData, (response) => {
                if (response.data && response.success) {
                    this.modalView.$el.find('form').html(response.data.html);
                    this.unblockUI(this.modalView.$el.find('.wc-backbone-modal-content'));
                } else {
                    window.alert(wc_exprdawc_admin_order_params.i18n_form_error);
                    this.unblockUI(this.modalView.$el.find('.wc-backbone-modal-content'));
                    this.modalView.$el.find('.modal-close').trigger('click');
                }
            });
        }

        // Handle the click event for the done button
        handleDoneButtonClick(event) {
            const requestData = $.extend({}, {
                action: 'woocommerce_edit_exprdawc_order_item',
                item_id: this.modalView._string.item_id,
                dataType: 'json',
                order_id: woocommerce_admin_meta_boxes.post_id,
                security: wc_exprdawc_admin_order_params.edit_exprdawc_nonce
            });
            const formElement = this.modalView.$el.find('form')[0];

            if (formElement.reportValidity() !== true) {
                return;
            }

            const formData = new FormData(formElement);
            for (const property in requestData) {
                formData.append(property, requestData[property]);
            }

            this.blockUI(this.modalView.$el.find('.wc-backbone-modal-content'));

            $.post({
                url: woocommerce_admin_meta_boxes.ajax_url,
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                cache: false,
                success: (response) => {
                    if (response.data && response.success) {
                        this.$orderItemsContainer.find('.inside').empty();
                        this.$orderItemsContainer.find('.inside').append(response.data.html);

                        this.$orderItemsContainer.trigger('wc_order_items_reloaded');

                        // Update notes.
                        if (response.data.notes_html) {
                            $('ul.order_notes').empty();
                            $('ul.order_notes').append($(response.data.notes_html).find('li'));
                        }

                        this.unblockUI(this.modalView.$el.find('.wc-backbone-modal-content'));

                        // Make it look like something changed.
                        this.blockUI(this.$orderItemsContainer, { fadeIn: 0 });
                        setTimeout(() => {
                            this.unblockUI(this.$orderItemsContainer);
                        }, 250);

                        this.modalView.closeButton(event);
                    } else {
                        window.alert(response.data.message);
                        this.unblockUI(this.modalView.$el.find('.wc-backbone-modal-content'));
                    }
                },
                error: () => {
                    window.alert(wc_exprdawc_admin_order_params.i18n_validation_error);
                    this.unblockUI(this.modalView.$el.find('.wc-backbone-modal-content'));
                }
            });
        }

        // Block UI element
        blockUI($target, params) {
            const defaults = {
                message: null,
                overlayCSS: {
                    background: '#fff',
                    opacity: 0.6
                }
            };

            const options = $.extend({}, defaults, params || {});

            $target.block(options);
        }

        // Unblock UI element
        unblockUI($target) {
            $target.unblock();
        }
    }

    // Initialize the class
    new ExtraProductDataAdminOrder();
});







