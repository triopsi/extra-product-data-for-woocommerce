/**
 * Add custom fields to the product data meta box.
 */
jQuery(function ($) {

    /**
     * Class to handle the product meta box.
     * @class ExprdawcMetaBoxesProduct
     * @description Handles the functionality for the extra product data in the WooCommerce product data meta box
     * @since 1.0.0
     * @version 1.0.0
     * @package ExtraProductDataForWooCommerce/JS
     * @license GPL-2.0+
     * @link https://www.triopsi.dev
    */
    class ExprdawcMetaBoxesProduct {

        /**
         * Initialize the class.
         * @constructor
         * @returns {void}
         * @since 1.0.0
         * @version 1.0.0
         * @package ExtraProductDataForWooCommerce/JS
         * @license GPL-2.0+
         * @link https://www.triopsi.dev
         */
        constructor() {

            const $fields = $('#exprdawc_field_body').find('tr.exprdawc_attribute');
            this.fieldIndex = $fields.length;

            this.isDirty = false;

            this.init();
        }

        /**
         * Initialize the class.
         */
        init() {
            this.bindEvents();
            this.noEntryContent();
            this.bindFormValidation();
            this.validateUniqueLabels();
            this.validateUniqueOptionValues();
            this.initColorHexFields();
        }

        /**
         * Bind events.
         */
        bindEvents() {
            $('#exprdawc_add_custom_field').on('click', this.addCustomField.bind(this));
            $(document).on('click', '.exprdawc_remove_custom_field', this.removeCustomField.bind(this));
            $(document).on('change', '.exprdawc_attribute_type', this.toggleOptions.bind(this));
            $(document).on('click', '.exprdawc_attribute_type', this.openOptionsTable.bind(this));
            $(document).on('click', '.exprdawc_attribute_input_name', this.openOptionsTable.bind(this));
            $(document).on('click', '.toggle-options', this.toggleOptionsTable.bind(this));
            $(document).on('click', '.add_option', this.addOption.bind(this));
            $(document).on('click', '.remove_option', this.removeOption.bind(this));
            $(document).on('change', '.exprdawc_input', this.setDirty.bind(this));
            $(document).on('change', '.exprdawc_autocomplete_field', this.checkAutocompleteField.bind(this));
            $(document).on('click', '.add_rule_group', this.addRuleGroup.bind(this));
            $(document).on('click', '.add_rule', this.addRule.bind(this));
            $(document).on('click', '.remove_rule', this.removeRule.bind(this));
            $(document).on('change', '.exprdawc_conditional_operator', this.toggleConditionalValueField.bind(this));
            $(document).on('change', '.exprdawc_conditional_logic_field', this.toggleConditionalTable.bind(this));
            $(document).on('click', '.exprdawc_adjust_price_field', this.togglePriceAdjustmentTable.bind(this));
            $(document).on('change keyup keydown input', '.field_option_table_value_td input', this.syncOptionValueToDefault.bind(this));
            $(document).on('input', '.field_option_table_value_td input', this.validateUniqueOptionValues.bind(this));
            $(document).on('click', '.exprdawc_copy_custom_field', this.exprdawc_copy_custom_field.bind(this));
            $(document).on('change keyup keydown input', 'input.field_name', this.updateConditionalFieldOptions.bind(this));
            $(document).on('input', '.exprdawc_label', this.validateUniqueLabels.bind(this));
            $(document).on('input', '.exprdawc_color_default', this.handleColorPickerInput.bind(this));
            $(document).on('input', '.exprdawc_color_hex', this.handleColorHexInput.bind(this));
            $(document).on('blur', '.exprdawc_color_hex', this.handleColorHexBlur.bind(this));

            // Inits
            this.toggleConditionalValueFieldAll();
            this.initFieldTypeSettings();
            this.togglePriceAdjustmentTableAll();

            // Attribute ordering.
            $('.exprdawc_field_table tbody').sortable({
                items: 'tr.exprdawc_fields_wrapper',
                cursor: 'move',
                axis: 'y',
                handle: '.move',
                scrollSensitivity: 40,
                forcePlaceholderSize: true,
                helper: 'clone',
                opacity: 0.65,
                placeholder: 'wc-metabox-sortable-placeholder',
                start: function (event, ui) {
                    ui.item.css('background-color', '#f6f6f6');
                },
                stop: function (event, ui) {
                    ui.item.removeAttr('style');
                },
                update: (event, ui) => {
                    this.updateFieldIndices();
                },
            });

            // Option ordering.
            $(document).on('mouseenter', '.exprdawc_options_table tbody', function () {
                $(this).sortable({
                    items: 'tr',
                    cursor: 'move',
                    axis: 'y',
                    handle: '.move',
                    scrollSensitivity: 40,
                    forcePlaceholderSize: true,
                    helper: 'clone',
                    opacity: 0.65,
                    placeholder: 'wc-metabox-sortable-placeholder',
                    start: function (event, ui) {
                        ui.item.css('background-color', '#f6f6f6');
                    },
                    stop: function (event, ui) {
                        ui.item.removeAttr('style');
                    },
                    update: (event, ui) => {
                        this.updateFieldIndices();
                    },
                });
            });

            // Get the name of the input and set the header.
            $('#exprdawc_attribute_container').on(
                'input',
                '.exprdawc_attribute .exprdawc_attribute_input_name input',
                function () {
                    var text = $(this).val(),
                        target = $(this).closest('.exprdawc_attribute').find('.attribute_name');
                    if (text) {
                        target.text(text);
                    }
                }
            );
        }

        /**
         * Add a custom field.
         */
        addCustomField() {
            this.fieldIndex++;
            this.setDirty();
            const template = $('#exprdawc-field-template').html();
            if (!template) {
                console.error('exprdawc: field template not found');
                return;
            }

            const fieldHtml = template.replaceAll('__INDEX__', String(this.fieldIndex)).replaceAll('__ID__', this.generateID());

            $('#exprdawc_field_body').append(fieldHtml);
            this.noEntryContent();

            // Update all field indices
            this.updateFieldIndices();
            this.updateConditionalFieldOptions();

            // Trigger change event to show the options.
            const $newField = $('#exprdawc_field_body tr.exprdawc_fields_wrapper').last();
            $newField.find('.exprdawc_attribute_type').trigger('change');
            this.initColorHexFields($newField);
            this.togglePriceAdjustmentTableAll();
            this.validateUniqueLabels();
            this.validateUniqueOptionValues();
        }

        /**
         * Generate a unique ID.
         * @returns {string} The generated ID.
         */
        generateID() {
            return Math.floor(Math.random() * 10000000000).toString().padStart(10, '0');
        }

        /**
         * Remove a custom field.
         * @param {*} e
         * @returns {boolean} False to prevent default action.
         */
        removeCustomField(e) {
            if (confirm(exprdawc_admin_meta_boxes.confirm_delete)) {
                this.setDirty();
                $(e.currentTarget).closest('tr').next('.exprdawc_options').remove();
                $(e.currentTarget).closest('tr').remove();
                // Update all field indices
                this.updateFieldIndices();
                this.noEntryContent();
                this.validateUniqueLabels();
                this.validateUniqueOptionValues();
            }
            return false;
        }

        /**
         * Toggle options.
         * @param {*} e The event object.
         */
        toggleOptions(e) {
            this.setDirty();
            const $row = $(e.currentTarget).closest('tr');
            const $type = $(e.currentTarget).val();
            const $optionsRow = $row.next('.exprdawc_options');
            const $optionsTable = $optionsRow.find('.exprdawc_options_table');
            const $placeholderText = $optionsRow.find('.exprdawc_placeholder');
            const $adjustPriceCheckbox = $optionsRow.find('.exprdawc_adjust_price_field');

            if ($type === 'radio' || $type === 'checkbox' || $type === 'select' || $type === 'color_radio') {
                $placeholderText.prop('disabled', true);
                $optionsTable.show();
                // Hide Placeholder.
                $optionsRow.find('.exprdawc_attribute_placeholder_text').hide();
            } else {
                $placeholderText.prop('disabled', false);
                $optionsTable.hide();
                // Show Placeholder.
                $optionsRow.find('.exprdawc_attribute_placeholder_text').show();
            }

            if ($(e.currentTarget).val() === 'long_text') {
                $optionsRow.find('.exprdawc_long_text_table').show();
            } else {
                $optionsRow.find('.exprdawc_long_text_table').hide();
            }

            if ($(e.currentTarget).val() === 'text') {
                $optionsRow.find('.exprdawc_text_table').show();
            } else {
                $optionsRow.find('.exprdawc_text_table').hide();
            }

            if ($(e.currentTarget).val() === 'number') {
                $optionsRow.find('.exprdawc_number_table').show();
            } else {
                $optionsRow.find('.exprdawc_number_table').hide();
            }

            if ($(e.currentTarget).val() === 'email') {
                $optionsRow.find('.exprdawc_email_table').show();
            } else {
                $optionsRow.find('.exprdawc_email_table').hide();
            }

            if ($(e.currentTarget).val() === 'color') {
                $optionsRow.find('.exprdawc_color_table').show();
            } else {
                $optionsRow.find('.exprdawc_color_table').hide();
            }

            if ($(e.currentTarget).val() === 'color_radio') {
                $optionsRow.find('.exprdawc_color_radio_table').show();
            } else {
                $optionsRow.find('.exprdawc_color_radio_table').hide();
            }

            if ($adjustPriceCheckbox.length) {
                this.togglePriceAdjustmentTable({ currentTarget: $adjustPriceCheckbox.get(0) });
            }

            this.validateUniqueOptionValues();
        }

        /**
         * Toggle options table.
         * @param {*} e 
         */
        toggleOptionsTable(e) {
            const $icon = $(e.currentTarget);
            const $optionsRow = $icon.closest('tr').next('.exprdawc_options');
            $optionsRow.toggle();
            $icon.toggleClass('dashicons-arrow-down dashicons-arrow-up');
        }

        /**
         * Open options table (always expand).
         * @param {*} e
         */
        openOptionsTable(e) {
            const $target = $(e.currentTarget);
            const $row = $target.closest('tr.exprdawc_attribute');
            const $optionsRow = $row.next('.exprdawc_options');
            const $icon = $row.find('.toggle-options');

            $optionsRow.show();
            $icon.removeClass('dashicons-arrow-up').addClass('dashicons-arrow-down');
        }

        /**
         * Add an option.
         * @param {*} e 
         */
        addOption(e) {
            this.setDirty();
            const $optionsTable = $(e.currentTarget).closest('.exprdawc_options_table');
            const actual_index = $optionsTable.closest('.exprdawc_fields_table').data('index');

            // Guard: if actual_index is undefined or null, log and inform the user.
            if (typeof actual_index === 'undefined' || actual_index === null) {
                console.error('exprdawc: actual_index is undefined or null', $optionsTable);
                return;
            }

            const optionIndex = $optionsTable.find('tbody tr').length;
            const fieldType = $optionsTable.closest('.exprdawc_fields_table').find('.exprdawc_attribute_type').val();

            const templateByFieldType = {
                checkbox: '#exprdawc-option-template-multi',
                color_radio: '#exprdawc-option-template-color-radio',
            };

            const templateId = templateByFieldType[fieldType] || '#exprdawc-option-template-single';

            const optionTemplate = $(templateId).html();

            if (!optionTemplate) {
                console.error('exprdawc: option template not found');
                return;
            }

            const optionHtml = optionTemplate
                .replaceAll('__FIELD_INDEX__', String(actual_index))
                .replaceAll('__OPTION_INDEX__', String(optionIndex));

            $optionsTable.find('tbody').append(optionHtml);

            const isOptionBased = fieldType === 'radio' || fieldType === 'checkbox' || fieldType === 'select' || fieldType === 'color_radio';
            const isPriceAdjustmentEnabled = $optionsTable.closest('.exprdawc_options').find('.exprdawc_adjust_price_field').is(':checked');
            this.updateOptionPriceAdjustmentColumns($optionsTable, isOptionBased && isPriceAdjustmentEnabled);
            this.updateFieldIndices();

            this.checkOptions($optionsTable.closest('.exprdawc_options'));
            this.validateUniqueOptionValues();
        }

        /**
         * Remove an option.
         * @param {*} e 
         * @returns 
         */
        removeOption(e) {
            if (confirm(exprdawc_admin_meta_boxes.confirm_delete)) {
                this.setDirty();
                $(e.currentTarget).closest('tr').remove();
                this.updateFieldIndices();
                this.checkOptions($(e.currentTarget).closest('.exprdawc_options'));
                this.validateUniqueOptionValues();
            }
            return false;
        }

        /**
         * Check options.
         * @param {*} $optionsRow 
         */
        checkOptions($optionsRow) {
            const $optionsTable = $optionsRow.find('.exprdawc_options_table tbody');
            const $noEntryMessage = $optionsRow.find('.exprdawc_no_entry_message');
            if ($optionsTable.find('tr').length === 0) {
                $noEntryMessage.show();
            } else {
                $noEntryMessage.hide();
            }
        }

        /**
         * Sync option value to the default input value for radio/select types.
         * When an option's value input changes, the corresponding default input's value
         * (the radio input in case of radio/select) will be updated to match.
         * @param {*} e
         */
        syncOptionValueToDefault(e) {
            const $input = $(e.currentTarget);
            const $row = $input.closest('tr');
            const $optionsTable = $input.closest('.exprdawc_options_table');
            const optionIndex = $optionsTable.find('tbody tr').index($row);
            const actualIndex = $optionsTable.closest('.exprdawc_fields_table').data('index');

            if (typeof actualIndex === 'undefined' || actualIndex === null) {
                console.error('exprdawc: actualIndex is undefined or null', $optionsTable);
                return;
            }

            const newValue = $input.val();
            const fieldType = $optionsTable.closest('.exprdawc_fields_table').find('.exprdawc_attribute_type').val();

            // For radio/select types the default is a single value input (radio)
            if (fieldType === 'radio' || fieldType === 'select' || fieldType === 'checkbox' || fieldType === 'color_radio') {
                const $targetRadioOrCheckbox = $optionsTable.find('tbody tr').eq(optionIndex).find('input[type="radio"], input[type="checkbox"]').first();
                if ($targetRadioOrCheckbox.length) {
                    $targetRadioOrCheckbox.val(newValue);
                } else {
                    // Fallback: try to find radios by name pattern and set the matching index
                    const $targetRadioOrCheckbox = $optionsTable.find('input[type="radio"][name^="extra_product_fields"], input[type="checkbox"][name^="extra_product_fields"]');
                    if ($targetRadioOrCheckbox.length > optionIndex) {
                        $targetRadioOrCheckbox.eq(optionIndex).val(newValue);
                    }
                }
            }
        }

        /**
         * Export content.
         * @param {*} e 
         * @returns 
         */
        /**
         * Set dirty.
         */
        setDirty() {
            this.isDirty = true;
            this.disableExportLink();
        }

        /**
         * Disable export link.
         * @returns 
         */
        disableExportLink() {
            if (this.isDirty) {
                $('.exprdawc-export').hide();
                return;
            }
        }

        /**
         * No entry content.
         */
        noEntryContent() {
            var index = $('#exprdawc_field_body .exprdawc_attribute').length,
                $container = $('.exprdawc_no_entry_message'),
                $table_header = $('#exprdatawc_table_header'),
                $export_link = $('.exprdawc-export');

            if (index > 0) {
                $container.hide();
                $table_header.show();
                $export_link.show();
            } else {
                $container.show();
                $table_header.hide();
                $export_link.hide();
            }
        }

        /**
         * Check autocomplete field.
         * @param {*} e 
         */
        checkAutocompleteField(e) {
            const $currentCheckbox = $(e.currentTarget);
            const isChecked = $currentCheckbox.is(':checked');

            if (isChecked) {
                const $otherChecked = $('.exprdawc_autocomplete_field').not($currentCheckbox).filter(':checked');

                if ($otherChecked.length > 0) {
                    const confirmOverwrite = confirm(exprdawc_admin_meta_boxes.sureAnotherAutocompleCheckedQuestion);

                    if (!confirmOverwrite) {
                        $currentCheckbox.prop('checked', false);
                        return;
                    }

                    $otherChecked.prop('checked', false);
                }
            }
        }

        /**
         * Add a rule group.
         * @param {*} e 
         */
        addRuleGroup(e) {
            const $container = $(e.currentTarget).closest('.exprdawc_conditional_logic_table').find('.exprdawc_conditional_rules');
            const ruleGroupIndex = $container.find('.exprdawc_rule_group_container').length;
            const actualIndex = $(e.currentTarget).closest('.exprdawc_fields_table').data('index');
            const ruleGroupHtml = this.getRuleGroupHtml(actualIndex, ruleGroupIndex);
            $container.append(ruleGroupHtml);

            const $newRuleGroup = $container.find('.exprdawc_rule_group_container').last().find('.exprdawc_rule_group');
            const ruleHtml = this.getRuleHtml(actualIndex, ruleGroupIndex, 0);
            $newRuleGroup.append(ruleHtml);
            this.toggleConditionalValueFieldAll();
            this.updateFieldIndices();
        }

        /**
         * Add a rule.
         * @param {*} e 
         */
        addRule(e) {
            const $ruleGroupContainer = $(e.currentTarget).closest('.exprdawc_rule_group_container');
            const $ruleGroup = $ruleGroupContainer.find('.exprdawc_rule_group').first();
            const $allRuleGroups = $ruleGroupContainer.closest('.exprdawc_conditional_rules').find('.exprdawc_rule_group_container');
            const ruleGroupIndex = $allRuleGroups.index($ruleGroupContainer);
            const actualIndex = $(e.currentTarget).closest('.exprdawc_fields_table').data('index');
            const ruleIndex = $ruleGroup.find('.exprdawc_rule').length;
            const ruleHtml = this.getRuleHtml(actualIndex, ruleGroupIndex, ruleIndex);
            $ruleGroup.append(ruleHtml);
            this.toggleConditionalValueFieldAll();
            this.updateFieldIndices();
        }

        /**
         * Get rule group HTML.
         * @param {number} ruleGroupIndex 
         * @returns {string}
         */
        getRuleGroupHtml(actualIndex, ruleGroupIndex) {
            const template = $('#exprdawc-rule-group-template').html();
            if (!template) {
                console.error('exprdawc: rule group template not found');
                return '';
            }

            return template;
        }

        /**
         * Get rule HTML.
         * @param {number} actualIndex
         * @param {number} ruleGroupIndex 
         * @param {number} ruleIndex 
         * @returns {string}
         */
        getRuleHtml(actualIndex, ruleGroupIndex, ruleIndex) {
            const template = $('#exprdawc-rule-template').html();
            if (!template) {
                console.error('exprdawc: rule template not found');
                return '';
            }

            const fieldOptions = this.getAllFieldsOptions();

            return template
                .replaceAll('__FIELD_INDEX__', String(actualIndex))
                .replaceAll('__RULE_GROUP_INDEX__', String(ruleGroupIndex))
                .replaceAll('__RULE_INDEX__', String(ruleIndex))
                .replaceAll('__FIELD_OPTIONS__', fieldOptions);
        }

        /**
         * Toggle conditional value field visibility.
         * @param {*} e 
         */
        toggleConditionalValueField(e) {
            const $operator = $(e.currentTarget);
            const $valueField = $operator.closest('.exprdawc_rule').find('.exprdawc_conditional_value');
            if ($operator.val() === 'field_changed' || $operator.val() === 'field_is_empty' || $operator.val() === 'field_is_not_empty') {
                $valueField.hide();
            } else {
                $valueField.show();
            }
        }

        // Init all Rules toggleConditionalValueField
        toggleConditionalValueFieldAll() {
            $('.exprdawc_conditional_operator').each((index, element) => {
                this.toggleConditionalValueField({ currentTarget: element });
            });
        }

        // Init all Rules toggleConditionalValueField
        togglePriceAdjustmentTableAll() {
            $('.exprdawc_adjust_price_field').each((index, element) => {
                this.togglePriceAdjustmentTable({ currentTarget: element });
            });
        }

        /**
         * Remove a rule.
         * @param {*} e 
         * @returns 
         */
        removeRule(e) {
            if (confirm(exprdawc_admin_meta_boxes.confirm_delete_rule)) {
                const $ruleGroup = $(e.currentTarget).closest('.exprdawc_rule_group_container');
                $(e.currentTarget).closest('.exprdawc_rule').remove();
                if ($ruleGroup.find('.exprdawc_rule').length === 0) {
                    $ruleGroup.remove();
                }
                this.updateFieldIndices();
            }
            return false;
        }

        /**
         * Get all fields options.
         * @returns 
         */
        getAllFieldsOptions() {
            let options = '';
            $('#exprdawc_field_body tr.exprdawc_attribute').each(function () {
                const label = $(this).find('.exprdawc_attribute_input_name input').val();
                const safeLabel = $('<div>').text(label).html();
                options += `<option value="${safeLabel}">${safeLabel}</option>`;
            });
            return options;
        }

        /**
        * Enable or disable checkboxes based on a condition.
        */
        toggleConditionalTable(e) {
            const checkbox = $(e.currentTarget);
            const $table_setting = $(e.currentTarget).closest('.exprdawc_options').find('.exprdawc_conditional_logic_table');

            if (checkbox.is(':checked')) {
                $table_setting.show();
            } else {
                $table_setting.hide();
            }
        }

        /**
         * Toggle price adjustment table.
         * @param {*} e 
         */
        togglePriceAdjustmentTable(e) {
            const checkbox = $(e.currentTarget);
            const $optionsRow = checkbox.closest('.exprdawc_options');
            const $tableSetting = $optionsRow.find('.exprdawcPriceAdjustment_table, .exprdawc_price_adjustment_table');
            const $optionsTable = $optionsRow.find('.exprdawc_options_table');
            const fieldType = checkbox.closest('.exprdawc_fields_table').find('.exprdawc_attribute_type').val();
            const isOptionBased = fieldType === 'radio' || fieldType === 'checkbox' || fieldType === 'select' || fieldType === 'color_radio';
            const isEnabled = checkbox.is(':checked');

            if (isOptionBased) {
                $tableSetting.hide();
                this.updateOptionPriceAdjustmentColumns($optionsTable, isEnabled);
                return;
            }

            this.updateOptionPriceAdjustmentColumns($optionsTable, false);
            if (isEnabled) {
                $tableSetting.show();
            } else {
                $tableSetting.hide();
            }
        }

        updateOptionPriceAdjustmentColumns($optionsTable, shouldShow) {
            const $headerType = $optionsTable.find('thead .fieldPriceAdjustment_type_th');
            const $headerValue = $optionsTable.find('thead .fieldPriceAdjustment_val_th');
            const $rowType = $optionsTable.find('tbody .fieldPriceAdjustment_type');
            const $rowValue = $optionsTable.find('tbody .field_priceAdjustmentValue');

            if (shouldShow) {
                $headerType.show();
                $headerValue.show();
                $rowType.show();
                $rowValue.show();
                return;
            }

            $headerType.hide();
            $headerValue.hide();
            $rowType.hide();
            $rowValue.hide();
        }

        /**
         * Init Field Type specific settings.
         */
        initFieldTypeSettings() {
            const $optionsRow = $('.exprdawc_fields_wrapper');

            $optionsRow.each((index, element) => {
                // By exprdawc_attribute_type checkbox, radio and select hide placeholder text and show options.
                const fieldType = $(element).find('.exprdawc_attribute_type').val() || 'text';
                const $placeholderText = $(element).find('.exprdawc_attribute_placeholder_text');
                if (fieldType === 'radio' || fieldType === 'checkbox' || fieldType === 'select' || fieldType === 'color' || fieldType === 'color_radio') {
                    $placeholderText.hide();
                } else {
                    $placeholderText.show();
                }
            });
        }

        normalizeColorHex(value) {
            let normalized = (value || '').toString().trim();

            if (!normalized) {
                return '';
            }

            if (!normalized.startsWith('#')) {
                normalized = `#${normalized}`;
            }

            if (/^#([0-9a-f]{3})$/i.test(normalized)) {
                normalized = `#${normalized.slice(1).split('').map((char) => `${char}${char}`).join('')}`;
            }

            return normalized.toLowerCase();
        }

        isValidColorHex(value) {
            return /^#([0-9a-f]{6})$/i.test(value || '');
        }

        getColorFieldPair($element) {
            const $container = $element.closest('td');

            return {
                $colorInput: $container.find('.exprdawc_color_default').first(),
                $hexInput: $container.find('.exprdawc_color_hex').first(),
            };
        }

        initColorHexFields($scope = $(document)) {
            $scope.find('.exprdawc_color_table td').each((index, element) => {
                const $cell = $(element);
                const $colorInput = $cell.find('.exprdawc_color_default').first();
                const $hexInput = $cell.find('.exprdawc_color_hex').first();

                if (!$colorInput.length || !$hexInput.length) {
                    return;
                }

                const normalizedHex = this.normalizeColorHex($hexInput.val()) || this.normalizeColorHex($colorInput.val()) || '#1d2327';
                const validHex = this.isValidColorHex(normalizedHex) ? normalizedHex : '#1d2327';

                $colorInput.val(validHex);
                $hexInput.val(validHex);
            });
        }

        handleColorPickerInput(e) {
            const { $colorInput, $hexInput } = this.getColorFieldPair($(e.currentTarget));

            if (!$colorInput.length || !$hexInput.length) {
                return;
            }

            $hexInput.val(this.normalizeColorHex($colorInput.val()));
        }

        handleColorHexInput(e) {
            const { $colorInput, $hexInput } = this.getColorFieldPair($(e.currentTarget));
            const normalizedHex = this.normalizeColorHex($hexInput.val());

            if (this.isValidColorHex(normalizedHex)) {
                $colorInput.val(normalizedHex);
            }
        }

        handleColorHexBlur(e) {
            const { $colorInput, $hexInput } = this.getColorFieldPair($(e.currentTarget));
            const normalizedHex = this.normalizeColorHex($hexInput.val());
            const fallbackHex = this.normalizeColorHex($colorInput.val()) || '#1d2327';

            $hexInput.val(this.isValidColorHex(normalizedHex) ? normalizedHex : fallbackHex);
            $colorInput.val(fallbackHex);
        }

        /**
         * Copies a custom field row.
         *
         * @param {Event} e The event object.
         */
        exprdawc_copy_custom_field(e) {
            e.preventDefault();
            this.setDirty();
            const $row = $(e.currentTarget).closest('.exprdawc_fields_wrapper');
            const $clone = $row.clone();
            this.normalizeCopiedField($clone);

            $row.after($clone);
            this.updateFieldIndices();
            this.updateConditionalFieldOptions();

            $clone.find('.exprdawc_attribute_type').trigger('change');
            this.initColorHexFields($clone);

            $clone.find('.field_option_table_value_td input').each((index, element) => {
                this.syncOptionValueToDefault({ currentTarget: element });
            });

            this.toggleConditionalValueFieldAll();
            this.togglePriceAdjustmentTableAll();
            this.validateUniqueLabels();
            this.validateUniqueOptionValues();
        }

        normalizeCopiedField($clone) {
            const $labelInput = $clone.find('input.field_name').first();
            const labelValue = ($labelInput.val() || '').toString().trim();
            if (labelValue !== '') {
                $labelInput.val(this.getIncrementedCopyText(labelValue));
            }

            const $placeholderInput = $clone.find('input.exprdawc_placeholder').first();
            const placeholderValue = ($placeholderInput.val() || '').toString().trim();
            if (placeholderValue !== '') {
                $placeholderInput.val(this.getIncrementedCopyText(placeholderValue));
            }

            $clone.find('input.exprdawc-invalid-field').removeClass('exprdawc-invalid-field');
            $clone.removeClass('exprdawc-validation-error');

            $clone.find('.exprdawc_option_default').prop('checked', false);
            $clone.find('.exprdawc_conditional_field').val('');
            $clone.find('.exprdawc_conditional_operator').val('field_is_empty');
            $clone.find('.exprdawc_conditional_value').val('');
        }

        getIncrementedCopyText(value) {
            const numberMatch = value.match(/\d+$/);
            if (numberMatch) {
                return value.replace(/\d+$/, String(parseInt(numberMatch[0], 10) + 1));
            }

            return `${value} 2`;
        }

        /**
         * Updates all select.exprdawc_conditional_field options.
         */
        updateConditionalFieldOptions() {
            const options = this.getAllFieldsOptions();
            $('select.exprdawc_conditional_field').each(function () {
                const $select = $(this);
                const selectedValue = $select.val();
                $select.html(`<option value="">${exprdawc_admin_meta_boxes.selectFieldNone}</option>${options}`);
                $select.val(selectedValue);
            });
        }

        /**
         * Validate that all non-empty labels are unique.
         * Highlights duplicates and adds an inline note.
         *
         * @returns {boolean} True if labels are unique, false otherwise.
         */
        validateUniqueLabels() {
            const labelGroups = new Map();
            const $labelInputs = $('#exprdawc_field_body').find('input.exprdawc_label');

            $labelInputs.each((index, element) => {
                const $input = $(element);
                this.clearUniqueLabelError($input);

                const value = ($input.val() || '').toString().trim().toLowerCase();
                if (!value) {
                    return;
                }

                if (!labelGroups.has(value)) {
                    labelGroups.set(value, []);
                }

                labelGroups.get(value).push($input);
            });

            let hasDuplicate = false;

            labelGroups.forEach((group) => {
                if (group.length <= 1) {
                    return;
                }

                hasDuplicate = true;
                group.forEach(($input) => {
                    this.markUniqueLabelError($input);
                });
            });

            return !hasDuplicate;
        }

        markUniqueLabelError($input) {
            const $row = $input.closest('tr.exprdawc_fields_wrapper');
            const warningText = exprdawc_admin_meta_boxes.validation_unique_warning_inline || 'Label must be unique.';

            $row.addClass('exprdawc-validation-error exprdawc-duplicate-error');
            $input.addClass('exprdawc-invalid-field exprdawc-duplicate-field');

            if ($input.siblings('.exprdawc-unique-note').length === 0) {
                $('<div />', {
                    class: 'exprdawc-unique-note',
                    text: warningText,
                }).insertAfter($input);
            }
        }

        clearUniqueLabelError($input) {
            const $row = $input.closest('tr.exprdawc_fields_wrapper');
            const hasValue = ($input.val() || '').toString().trim() !== '';

            $row.removeClass('exprdawc-duplicate-error');
            $input.removeClass('exprdawc-duplicate-field');
            $input.siblings('.exprdawc-unique-note').remove();

            if (hasValue) {
                $input.removeClass('exprdawc-invalid-field');
            }

            if ($row.find('.exprdawc-invalid-field').length === 0) {
                $row.removeClass('exprdawc-validation-error');
            }
        }

        /**
         * Validate that option values are unique within each exprdawc_options_table.
         * Applies to select, checkbox and radio fields only.
         *
         * @returns {boolean} True if option values are unique per table, false otherwise.
         */
        validateUniqueOptionValues() {
            const $optionsRows = $('#exprdawc_field_body').find('tr.exprdawc_options');
            let hasDuplicate = false;

            $optionsRows.each((index, rowElement) => {
                const $optionsRow = $(rowElement);
                const wrapper = $optionsRow.closest('tr.exprdawc_fields_wrapper');
                const fieldType = wrapper.find('.exprdawc_attribute_type').val();
                const $valueInputs = $optionsRow.find('.exprdawc_options_table tbody .field_option_table_value_td input');

                $valueInputs.each((inputIndex, inputElement) => {
                    this.clearUniqueOptionValueError($(inputElement), wrapper);
                });

                if (!this.isOptionBasedFieldType(fieldType)) {
                    return;
                }

                const valueGroups = new Map();
                $valueInputs.each((inputIndex, inputElement) => {
                    const $input = $(inputElement);
                    const value = ($input.val() || '').toString().trim().toLowerCase();

                    if (!value) {
                        return;
                    }

                    if (!valueGroups.has(value)) {
                        valueGroups.set(value, []);
                    }

                    valueGroups.get(value).push($input);
                });

                valueGroups.forEach((group) => {
                    if (group.length <= 1) {
                        return;
                    }

                    hasDuplicate = true;
                    group.forEach(($input) => {
                        this.markUniqueOptionValueError($input, wrapper);
                    });
                });
            });

            return !hasDuplicate;
        }

        isOptionBasedFieldType(fieldType) {
            return fieldType === 'radio' || fieldType === 'checkbox' || fieldType === 'select';
        }

        markUniqueOptionValueError($input, wrapper) {
            const $row = $input.closest('tr');
            const warningText = exprdawc_admin_meta_boxes.validation_option_unique_warning_inline || 'Option value must be unique.';
            wrapper.addClass('exprdawc-validation-error exprdawc-duplicate-error');
            $row.addClass('exprdawc-validation-error exprdawc-duplicate-error');
            $input.addClass('exprdawc-invalid-field exprdawc-duplicate-field');

            if ($input.siblings('.exprdawc-option-unique-note').length === 0) {
                $('<div />', {
                    class: 'exprdawc-option-unique-note exprdawc-unique-note',
                    text: warningText,
                }).insertAfter($input);
            }
        }

        clearUniqueOptionValueError($input, wrapper) {
            const $row = $input.closest('tr');
            const hasValue = ($input.val() || '').toString().trim() !== '';

            wrapper.removeClass('exprdawc-validation-error exprdawc-duplicate-error');
            $row.removeClass('exprdawc-duplicate-error');
            $input.removeClass('exprdawc-duplicate-field');
            $input.siblings('.exprdawc-option-unique-note').remove();

            if (hasValue) {
                $input.removeClass('exprdawc-invalid-field');
            }

            if ($row.find('.exprdawc-invalid-field').length === 0) {
                $row.removeClass('exprdawc-validation-error');
            }
        }

        /**
         * Updates the indices of all fields.
         */
        updateFieldIndices() {
            $('#exprdawc_field_body tr.exprdawc_fields_wrapper').each((index, element) => {
                // Update the field index
                const $row = $(element);
                $row.find('.exprdawc_fields_table').attr('data-index', index);
                $row.find('.exprdawc_attribute_index').val(index);
                $(element).find('input, select, textarea, label').each(function () {
                    const $input = $(this);

                    // Update the name attribute with the new index
                    const name = $input.attr('name');
                    if (name) {
                        $input.attr('name', name.replace(/\[\d+\]/, `[${index}]`));
                    }

                    // Update the id attribute with the new index
                    const id = $input.attr('id');
                    if (id) {
                        $input.attr('id', id.replace(/_\d+$/, `_${index}`));
                    }

                    // For the label attribute for
                    const labelFor = $input.attr('for');
                    if (labelFor) {
                        $input.attr('for', labelFor.replace(/_\d+$/, `_${index}`));
                    }
                });

                this.reindexOptionRows($row, index);
                this.reindexConditionalRules($row, index);

                $row.find('.field_option_table_value_td input').each((optionIndex, optionElement) => {
                    this.syncOptionValueToDefault({ currentTarget: optionElement });
                });
            });

            this.fieldIndex = $('#exprdawc_field_body tr.exprdawc_fields_wrapper').length;
            this.validateUniqueOptionValues();
        }

        reindexOptionRows($row, fieldIndex) {
            $row.find('.exprdawc_options_table tbody tr').each((optionIndex, rowElement) => {
                $(rowElement).find('input, select, textarea').each(function () {
                    const $input = $(this);
                    const name = $input.attr('name');

                    if (!name || !name.includes('[options]')) {
                        return;
                    }

                    const optionName = name
                        .replace(/extra_product_fields\[\d+\]/, `extra_product_fields[${fieldIndex}]`)
                        .replace(/\[options\]\[\d+\]/, `[options][${optionIndex}]`);

                    $input.attr('name', optionName);
                });
            });
        }

        reindexConditionalRules($row, fieldIndex) {
            $row.find('.exprdawc_rule_group_container').each((ruleGroupIndex, groupElement) => {
                $(groupElement).find('.exprdawc_rule').each((ruleIndex, ruleElement) => {
                    $(ruleElement).find('input, select').each(function () {
                        const $input = $(this);
                        const name = $input.attr('name');

                        if (!name || !name.includes('[conditional_rules]')) {
                            return;
                        }

                        const conditionalName = name
                            .replace(/extra_product_fields\[\d+\]/, `extra_product_fields[${fieldIndex}]`)
                            .replace(/\[conditional_rules\]\[\d+\]\[\d+\]/, `[conditional_rules][${ruleGroupIndex}][${ruleIndex}]`);

                        $input.attr('name', conditionalName);
                    });
                });
            });
        }

        /**
         * Bindet das Form-Validierungs-Event zur WordPress Post-Form.
         * Prüft, ob alle exprdawc_attributes einen gefüllten Label haben, bevor gespeichert wird.
         */
        bindFormValidation() {
            // Prüfe Form-Submit auf der WordPress Post-Edit-Seite
            $('#post').on('submit', (e) => {
                if (!this.validateFields()) {
                    e.preventDefault();
                    e.stopPropagation();
                    return false;
                }
            });

            // Prüfe auch auf Schnellspeicher-Button und Veröffentlichungs-Buttons
            $('#publish, #save-post').on('click', (e) => {
                if (!this.validateFields()) {
                    e.preventDefault();
                    e.stopPropagation();
                    return false;
                }
            });
        }

        /**
         * Validiert alle exprdawc_attributes.
         * Prüft, dass jedes Feld einen non-empty Label hat.
         * 
         * @returns {boolean} true wenn Validierung erfolgreich, false wenn Fehler
         */
        validateFields() {
            const $fields = $('#exprdawc_field_body').find('tr.exprdawc_fields_wrapper');

            if ($fields.length === 0) {
                // Keine Felder vorhanden - das ist ok
                return true;
            }

            let hasErrors = false;
            const errorFields = [];

            $fields.each((index, element) => {
                const $row = $(element);
                const $labelInput = $row.find('input.exprdawc_label');
                const labelValue = $labelInput.val().trim();

                if (labelValue === '') {
                    hasErrors = true;
                    errorFields.push(index + 1); // 1-basiert für Benutzerlesbarkeit

                    // Visuell markieren des fehlerhaften Feldes
                    $row.addClass('exprdawc-validation-error');
                    $labelInput.addClass('exprdawc-invalid-field');
                } else {
                    // Fehlermarkierung entfernen wenn Feld ok ist
                    $row.removeClass('exprdawc-validation-error');
                    $labelInput.removeClass('exprdawc-invalid-field');
                }
            });

            const hasUniqueErrors = !this.validateUniqueLabels();
            const hasOptionUniqueErrors = !this.validateUniqueOptionValues();

            if (hasErrors) {
                const warningMessage = exprdawc_admin_meta_boxes.validation_warning;
                alert(warningMessage);
                return false;
            }

            if (hasUniqueErrors) {
                const uniqueWarningMessage = exprdawc_admin_meta_boxes.validation_unique_warning || 'Labels must be unique. Please use different label names.';
                alert(uniqueWarningMessage);
                return false;
            }

            if (hasOptionUniqueErrors) {
                const uniqueOptionWarningMessage = exprdawc_admin_meta_boxes.validation_option_unique_warning || 'Option values within one field must be unique. Please use different option values.';
                alert(uniqueOptionWarningMessage);
                return false;
            }

            return true;
        }

    }

    // Initialize the class.
    new ExprdawcMetaBoxesProduct();
});