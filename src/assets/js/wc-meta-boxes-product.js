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
            this.fieldIndex = $('#exprdawc_field_body tr.exprdawc_attribute').length;
            this.isDirty = false;

            this.init();
        }

        /**
         * Initialize the class.
         */
        init() {
            this.bindEvents();
            this.noEntryContent();
        }

        /**
         * Bind events.
         */
        bindEvents() {
            $('#exprdawc_add_custom_field').on('click', this.addCustomField.bind(this));
            $(document).on('click', '.exprdawc_remove_custom_field', this.removeCustomField.bind(this));
            $(document).on('change', '.exprdawc_attribute_type', this.toggleOptions.bind(this));
            $(document).on('click', '.toggle-options', this.toggleOptionsTable.bind(this));
            $(document).on('click', '.add_option', this.addOption.bind(this));
            $(document).on('click', '.remove_option', this.removeOption.bind(this));
            $(document).on('click', 'a.exprdawc-export', this.exportContent.bind(this));
            $(document).on('click', 'a.exprdawc-import', this.importContent.bind(this));
            $(document).on('change', '.exprdawc_input', this.setDirty.bind(this));
            $(document).on('change', '.exprdawc_autocomplete_field', this.checkAutocompleteField.bind(this));
            $(document).on('click', '.add_rule_group', this.addRuleGroup.bind(this));
            $(document).on('click', '.add_rule', this.addRule.bind(this));
            $(document).on('click', '.remove_rule', this.removeRule.bind(this));
            $(document).on('change', '.exprdawc_conditional_operator', this.toggleConditionalValueField.bind(this));
            $(document).on('change', '.exprdawc_conditional_logic_field', this.toggleConditionalTable.bind(this));
            $(document).on('click', '.exprdawc_adjust_price_field', this.togglePriceAdjustmentTable.bind(this));
            $(document).on('click', '.exprdawc_copy_custom_field', this.exprdawc_copy_custom_field.bind(this));
            $(document).on('change keyup', 'input.field_name', this.updateConditionalFieldOptions.bind(this));

            // Inits
            this.toggleConditionalValueFieldAll();
            this.initFieldTypeSettings();

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
                update: function (event, ui) {
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
            $('#exprdawc_field_body').append(
                `
                <tr class="exprdawc_fields_wrapper">
                <td colspan="5">
                <table class="exprdawc_fields_table" data-index="${this.fieldIndex}">
	                <tbody>
                        <tr class="exprdawc_attribute">
                            <td class="move"><i class="dashicons dashicons-move"></i></td>
                            <td class="cl-arr"><i class="dashicons dashicons-arrow-up toggle-options"></i></td>
                            <td class="exprdawc_attribute_input_name">
                                <input type="text" class="exprdawc_input exprdawc_textinput exprdawc_label field_name" name="extra_product_fields[${this.fieldIndex}][label]" placeholder="${exprdawc_admin_meta_boxes.label_placeholder}" />
                            </td>
                            <td>
                                <select id="exprdawc_attribute_type_${this.fieldIndex}" name="extra_product_fields[${this.fieldIndex}][type]" class="exprdawc_attribute_type">
                                    <option value="text">${exprdawc_admin_meta_boxes.short_text}</option>
                                    <option value="long_text">${exprdawc_admin_meta_boxes.long_text}</option>
                                    <option value="email">${exprdawc_admin_meta_boxes.email}</option>
                                    <option value="number">${exprdawc_admin_meta_boxes.number}</option>
                                    <option value="date">${exprdawc_admin_meta_boxes.date}</option>
                                    <option value="yes-no">${exprdawc_admin_meta_boxes.yes_no}</option>
                                    <option value="radio">${exprdawc_admin_meta_boxes.radio}</option>
                                    <option value="checkbox">${exprdawc_admin_meta_boxes.checkbox}</option>
                                    <option value="select">${exprdawc_admin_meta_boxes.select}</option>
                                </select>
                            </td>
                            <td>
                                <button type="button" class="exprdawc_remove_custom_field button"><i class="dashicons dashicons-trash"></i></button>
                                <button type="button" class="button exprdawc_copy_custom_field"><i class="dashicons dashicons-admin-page"></i></button>
                                <input type="hidden" class="exprdawc_attribute_index" name="extra_product_fields[${this.fieldIndex}][index]" value="${this.fieldIndex}"/>
                            </td>
                        </tr>
                        <tr class="exprdawc_options" style="display: none;">
                            <td colspan="5">

                                <table class="exprdawc_settings_table exprdawc_general_table">
                                    <tbody>

                                        <!-- Text Area Option/Settings -->
                                        <tr>
                                            <td class="exprdawc_attribute_require_checkbox">
                                                <label class="exprdawc_label" for="exprdawc_text_required_${this.fieldIndex}">
                                                    <input type="checkbox" id="exprdawc_text_required_${this.fieldIndex}" class="exprdawc_input exprdawc_checkbox checkbox" name="extra_product_fields[${this.fieldIndex}][required]" value="1" />
                                                    ${exprdawc_admin_meta_boxes.require_input}
                                                </label>                                       
                                                <label class="exprdawc_label" for="exprdawc_text_autofocus_${this.fieldIndex}">
                                                    <input type="checkbox" id="exprdawc_text_autofocus_${this.fieldIndex}" class="exprdawc_input exprdawc_checkbox checkbox" name="extra_product_fields[${this.fieldIndex}][autofocus]" value="1" />
                                                    ${exprdawc_admin_meta_boxes.enable_autofocus}
                                                </label>
                                                <label class="exprdawc_label" for="exprdawc_text_editable_${this.fieldIndex}">
                                                    <input type="checkbox" id="exprdawc_text_editable_${this.fieldIndex}" class="exprdawc_input exprdawc_checkbox exprdawc_editable_field checkbox" name="extra_product_fields[${this.fieldIndex}][editable]" value="1" />
                                                    ${exprdawc_admin_meta_boxes.enable_editable}
                                                </label>

                                                <!-- Enable Conditional Logic and show table -->
                                                <label class="exprdawc_label" for="exprdawc_text_conditional_logic_${this.fieldIndex}">
                                                    <input type="checkbox" id="exprdawc_text_conditional_logic_${this.fieldIndex}" class="exprdawc_input exprdawc_checkbox exprdawc_conditional_logic_field checkbox" name="extra_product_fields[${this.fieldIndex}][conditional_logic]" value="1" />
                                                    ${exprdawc_admin_meta_boxes.enable_conditional_logic}
                                                </label>

                                                <!-- Enable Price Adjustment and show table -->
                                                <label class="exprdawc_label" for="exprdawc_text_price_adjustment_${this.fieldIndex}">
                                                    <input type="checkbox" id="exprdawc_text_price_adjustment_${this.fieldIndex}" class="exprdawc_input exprdawc_checkbox exprdawc_adjust_price_field checkbox" name="extra_product_fields[${this.fieldIndex}][adjust_price]" value="1" />
                                                    ${exprdawc_admin_meta_boxes.enable_price_adjustment}
                                                </label>

                                            </td>
                                            <td class="exprdawc_attribute_placeholder_text">
                                                <label class="exprdawc_label" for="exprdawc_text_placeholder_text_${this.fieldIndex}">${exprdawc_admin_meta_boxes.placeholder_text}</label>
                                                <input type="text" id="exprdawc_text_placeholder_text_${this.fieldIndex}" class="exprdawc_input exprdawc_textinput exprdawc_placeholder" name="extra_product_fields[${this.fieldIndex}][placeholder_text]" placeholder="${exprdawc_admin_meta_boxes.placeholder_text}" />
                                            </td>
                                            <td class="exprdawc_attribute_help_text">
                                                <label class="exprdawc_label" for="exprdawc_text_help_text_${this.fieldIndex}">${exprdawc_admin_meta_boxes.help_text}</label>
                                                <input type="text" id="exprdawc_text_help_text_${this.fieldIndex}" class="exprdawc_input exprdawc_textinput exprdawc_helptext" name="extra_product_fields[${this.fieldIndex}][help_text]" placeholder="${exprdawc_admin_meta_boxes.help_text}" />
                                            </td>
                                            <td>
                                                <label class="exprdawc_label" for="exprdawc_autocomplete_function_${this.fieldIndex}">${exprdawc_admin_meta_boxes.autocomplete_function}</label>
                                                <select id="exprdawc_autocomplete_function_${this.fieldIndex}" name="extra_product_fields[${this.fieldIndex}][autocomplete]" class="exprdawc_input exprdawc_attribute_type">
                                                    <option value="on">${exprdawc_admin_meta_boxes.autocomplete_on}</option>
                                                    <option value="off">${exprdawc_admin_meta_boxes.autocomplete_off}</option>
                                                    <option value="address-level1">${exprdawc_admin_meta_boxes.address_level1}</option>
                                                    <option value="address-level2">${exprdawc_admin_meta_boxes.address_level2}</option>
                                                    <option value="address-level3">${exprdawc_admin_meta_boxes.address_level3}</option>
                                                    <option value="address-level4">${exprdawc_admin_meta_boxes.address_level4}</option>
                                                    <option value="address-line1">${exprdawc_admin_meta_boxes.address_line1}</option>
                                                    <option value="address-line2">${exprdawc_admin_meta_boxes.address_line2}</option>
                                                    <option value="address-line3">${exprdawc_admin_meta_boxes.address_line3}</option>
                                                    <option value="bday">${exprdawc_admin_meta_boxes.bday}</option>
                                                    <option value="bday-day">${exprdawc_admin_meta_boxes.bday_day}</option>
                                                    <option value="bday-month">${exprdawc_admin_meta_boxes.bday_month}</option>
                                                    <option value="bday-year">${exprdawc_admin_meta_boxes.bday_year}</option>
                                                    <option value="cc-additional-name">${exprdawc_admin_meta_boxes.cc_additional_name}</option>
                                                    <option value="cc-csc">${exprdawc_admin_meta_boxes.cc_csc}</option>
                                                    <option value="cc-exp">${exprdawc_admin_meta_boxes.cc_exp}</option>
                                                    <option value="cc-exp-month">${exprdawc_admin_meta_boxes.cc_exp_month}</option>
                                                    <option value="cc-exp-year">${exprdawc_admin_meta_boxes.cc_exp_year}</option>
                                                    <option value="cc-family-name">${exprdawc_admin_meta_boxes.cc_family_name}</option>
                                                    <option value="cc-given-name">${exprdawc_admin_meta_boxes.cc_given_name}</option>
                                                    <option value="cc-name">${exprdawc_admin_meta_boxes.cc_name}</option>
                                                    <option value="cc-number">${exprdawc_admin_meta_boxes.cc_number}</option>
                                                    <option value="cc-type">${exprdawc_admin_meta_boxes.cc_type}</option>
                                                    <option value="country">${exprdawc_admin_meta_boxes.country}</option>
                                                    <option value="country-name">${exprdawc_admin_meta_boxes.country_name}</option>
                                                    <option value="email">${exprdawc_admin_meta_boxes.email}</option>
                                                    <option value="language">${exprdawc_admin_meta_boxes.language}</option>
                                                    <option value="photo">${exprdawc_admin_meta_boxes.photo}</option>
                                                    <option value="postal-code">${exprdawc_admin_meta_boxes.postal_code}</option>
                                                    <option value="sex">${exprdawc_admin_meta_boxes.sex}</option>
                                                    <option value="street-address">${exprdawc_admin_meta_boxes.street_address}</option>
                                                    <option value="tel">${exprdawc_admin_meta_boxes.tel}</option>
                                                    <option value="tel-area-code">${exprdawc_admin_meta_boxes.tel_area_code}</option>
                                                    <option value="tel-country-code">${exprdawc_admin_meta_boxes.tel_country_code}</option>
                                                    <option value="tel-extension">${exprdawc_admin_meta_boxes.tel_extension}</option>
                                                    <option value="tel-local">${exprdawc_admin_meta_boxes.tel_local}</option>
                                                    <option value="tel-local-prefix">${exprdawc_admin_meta_boxes.tel_local_prefix}</option>
                                                    <option value="tel-local-suffix">${exprdawc_admin_meta_boxes.tel_local_suffix}</option>
                                                    <option value="tel-national">${exprdawc_admin_meta_boxes.tel_national}</option>
                                                    <option value="transaction-amount">${exprdawc_admin_meta_boxes.transaction_amount}</option>
                                                    <option value="transaction-currency">${exprdawc_admin_meta_boxes.transaction_currency}</option>
                                                    <option value="url">${exprdawc_admin_meta_boxes.url}</option>
                                                </select>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <hr>

                                <!-- Price Adjustment -->
                                <table class="exprdawc_settings_table exprdawc_price_adjustment_table" style="display:none;">
                                    <tbody>
                                        <tr>
                                            <td>
                                                <label class="exprdawc_label" for="exprdawc_price_adjustment_type_${this.fieldIndex}">${exprdawc_admin_meta_boxes.price_adjustment_type}</label>
                                                <select id="exprdawc_price_adjustment_type_${this.fieldIndex}" name="extra_product_fields[${this.fieldIndex}][price_adjustment_type]" class="exprdawc_input exprdawc_price_adjustment_type">
                                                    <option value="fixed">${exprdawc_admin_meta_boxes.fixed}</option>
                                                    <option vlaue="quantity">${exprdawc_admin_meta_boxes.quantity}</option>
                                                    <option value="percentage">${exprdawc_admin_meta_boxes.percentage}</option>
                                                </select>
                                            </td>
                                            <td>
                                                <label class="exprdawc_label" for="exprdawc_price_adjustment_value_${this.fieldIndex}">${exprdawc_admin_meta_boxes.price_adjustment_value}</label>
                                                <input type="number" id="exprdawc_price_adjustment_value_${this.fieldIndex}" class="exprdawc_input exprdawc_price_adjustment_value" placeholder="0.00" name="extra_product_fields[${this.fieldIndex}][price_adjustment_value]" value="0" />
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>

                                <!-- Conditional Logic -->
				                <table class="exprdawc_settings_table exprdawc_conditional_logic_table" style="display:none;">
                                    <tbody>
                                        <tr>
                                           <td colspan="3">
                                                <label class="exprdawc_label">${exprdawc_admin_meta_boxes.conditionals}</label>
								                <p>${exprdawc_admin_meta_boxes.conditionals_description}</p>
                                                <div class="exprdawc_conditional_rules">
                                                    <div class="exprdawc_rule_group_container">
                                                        <div class="exprdawc_rule_group">
                                                            <div class="exprdawc_rule">
                                                                <select name="extra_product_fields[${this.fieldIndex}][conditional_rules][0][0][field]" class="exprdawc_input exprdawc_conditional_field">
                                                                <option value="">${exprdawc_admin_meta_boxes.selectFieldNone}</option>
                                                                ${this.getAllFieldsOptions()}
                                                                </select>
                                                                <select name="extra_product_fields[${this.fieldIndex}][conditional_rules][0][0][operator]" class="exprdawc_input exprdawc_conditional_operator">
                                                                    <option value="field_is_empty">${exprdawc_admin_meta_boxes.field_is_empty}</option>
                                                                    <option value="field_is_not_empty">${exprdawc_admin_meta_boxes.field_is_not_empty}</option>
                                                                    <option value="equals">${exprdawc_admin_meta_boxes.equals}</option>
                                                                    <option value="not_equals">${exprdawc_admin_meta_boxes.notEquals}</option>
                                                                    <option value="greater_than">${exprdawc_admin_meta_boxes.greaterThan}</option>
                                                                    <option value="less_than">${exprdawc_admin_meta_boxes.lessThan}</option>
                                                                </select>
                                                                <input type="text" name="extra_product_fields[${this.fieldIndex}][conditional_rules][0][0][value]" class="exprdawc_input exprdawc_conditional_value" placeholder="${exprdawc_admin_meta_boxes.enterValue}" style="display:none;" />
                                                                <button type="button" class="button remove_rule"><i class="dashicons dashicons-trash"></i></button>
                                                                <button type="button" class="button add_rule">${exprdawc_admin_meta_boxes.and}</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>                                            
                                        </tr>
                                    </tbody>
                                </table>


                                <!-- Text Area Option/Settings -->
                                <table class="exprdawc_settings_table exprdawc_long_text_table" style="display:none;">
                                    <tbody>
                                        <tr>
                                            <td>
                                                <label class="exprdawc_label" for="exprdawc_long_text_rows_${this.fieldIndex}">${exprdawc_admin_meta_boxes.rows}</label>
                                                <input type="number" id="exprdawc_long_text_rows_${this.fieldIndex}" class="exprdawc_input exprdawc_long_text_rows" name="extra_product_fields[${this.fieldIndex}][rows]" value="2" />
                                            </td>
                                            <td>
                                                <label class="exprdawc_label" for="exprdawc_long_text_cols_${this.fieldIndex}">${exprdawc_admin_meta_boxes.columns}</label>
                                                <input type="number" id="exprdawc_long_text_cols_${this.fieldIndex}" class="exprdawc_input exprdawc_long_text_cols" name="extra_product_fields[${this.fieldIndex}][cols]" value="5" />
                                            </td>
                                            <td>
                                                <label class="exprdawc_label" for="exprdawc_long_text_default_${this.fieldIndex}">${exprdawc_admin_meta_boxes.default_value}</label>
                                                <textarea id="exprdawc_long_text_default_${this.fieldIndex}" class="exprdawc_textarea" rows="3" cols="30" placeholder="${exprdawc_admin_meta_boxes.enter_default_text}" name="extra_product_fields[${this.fieldIndex}][default]"></textarea>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>

                                <!-- Text Option/Settings for radio, checkboxes and slects -->
                                <table class="exprdawc_settings_table exprdawc_text_table" style="display:none;">
                                    <tbody>
                                        <tr>
                                            <td>
                                                <label class="exprdawc_label" for="exprdawc_text_min_length_${this.fieldIndex}">${exprdawc_admin_meta_boxes.min_length}</label>
                                                <input type="number" id="exprdawc_text_min_length_${this.fieldIndex}" class="exprdawc_input exprdawc_text_min_length" name="extra_product_fields[${this.fieldIndex}][minlength]" value="0" />
                                            </td>
                                            <td>
                                                <label class="exprdawc_label" for="exprdawc_text_max_length_${this.fieldIndex}">${exprdawc_admin_meta_boxes.max_length}</label>
                                                <input type="number" id="exprdawc_text_max_length_${this.fieldIndex}" class="exprdawc_input exprdawc_text_max_length" name="extra_product_fields[${this.fieldIndex}][maxlength]" value="255" />
                                            </td>
                                            <td>
                                                <label class="exprdawc_label" for="exprdawc_text_default_${this.fieldIndex}">${exprdawc_admin_meta_boxes.default_value}</label>
                                                <input type="text" id="exprdawc_text_default_${this.fieldIndex}" class="exprdawc_input exprdawc_text_default" placeholder="${exprdawc_admin_meta_boxes.enter_default_text}" name="extra_product_fields[${this.fieldIndex}][default]" />
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>

                                <table class="exprdawc_options_table" style="display:none;">
                                    <thead>
                                        <tr>
                                            <th></th>
                                            <th class="field_option_table_label_th">${exprdawc_admin_meta_boxes.option_label}</th>
                                            <th class="field_option_table_value_th">${exprdawc_admin_meta_boxes.option_value}</th>
                                            <th class="field_option_table_selected_th">${exprdawc_admin_meta_boxes.default_selected}</th>
                                            <th class="field_option_table_action_th">${exprdawc_admin_meta_boxes.action}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Options will be dynamically added here -->
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="6">
                                                <button type="button" class="button add_option">${exprdawc_admin_meta_boxes.add_option}</button>
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                                <p class="exprdawc_no_entry_message" style="display: none;">${exprdawc_admin_meta_boxes.no_options}</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
                </td>
                </tr>
                `
            );
            this.noEntryContent();

            // Update all field indices
            this.updateFieldIndices();

            // Trigger change event to show the options.
            $('#exprdawc_attribute_type_' + this.fieldIndex).trigger('change');
        }

        /**
         * Remove a custom field.
         * @param {*} e
         * @returns 
         */
        removeCustomField(e) {
            if (confirm(exprdawc_admin_meta_boxes.confirm_delete)) {
                this.setDirty();
                $(e.currentTarget).closest('tr').next('.exprdawc_options').remove();
                $(e.currentTarget).closest('tr').remove();
                // Update all field indices
                this.updateFieldIndices();
                this.noEntryContent();
            }
            return false;
        }

        /**
         * Toggle options.
         * @param {*} e 
         */
        toggleOptions(e) {
            this.setDirty();
            const $row = $(e.currentTarget).closest('tr');
            const $optionsRow = $row.next('.exprdawc_options');
            const $optionsTable = $optionsRow.find('.exprdawc_options_table');
            const $placeholderText = $optionsRow.find('.exprdawc_placeholder');

            if ($(e.currentTarget).val() === 'radio' || $(e.currentTarget).val() === 'checkbox') {
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

            if ($(e.currentTarget).val() === 'text' || $(e.currentTarget).val() === 'email' || $(e.currentTarget).val() === 'number' || $(e.currentTarget).val() === 'date') {
                $optionsRow.find('.exprdawc_text_table').show();
            } else {
                $optionsRow.find('.exprdawc_text_table').hide();
            }
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
         * Add an option.
         * @param {*} e 
         */
        addOption(e) {
            this.setDirty();
            const $optionsTable = $(e.currentTarget).closest('.exprdawc_options_table');
            const optionIndex = $optionsTable.find('tbody tr').length;
            const fieldType = $optionsTable.closest('.exprdawc_fields_table').find('.exprdawc_attribute_type').val();
            const isPriceAdjustmentEnabled = $optionsTable.closest('.exprdawc_options').find('.exprdawc_adjust_price_field').is(':checked');

            let priceAdjustmentColumns = '';
            if (isPriceAdjustmentEnabled) {
                priceAdjustmentColumns = `
                    <td class="field_price_adjustment_type_${optionIndex} field_price_adjustment_type">
                    <select name="extra_product_fields[${this.fieldIndex}][options][${optionIndex}][price_adjustment_type]" class="exprdawc_input exprdawc_price_adjustment_type">
                        <option value="fixed">${exprdawc_admin_meta_boxes.fixed}</option>
                        <option value="quantity">${exprdawc_admin_meta_boxes.quantity}</option>
                        <option value="percentage">${exprdawc_admin_meta_boxes.percentage}</option>
                    </select>
                    </td>
                    <td class="field_price_adjustment_value_${optionIndex} field_price_adjustment_value">
                        <input type="number" name="extra_product_fields[${this.fieldIndex}][options][${optionIndex}][price_adjustment_value]" class="exprdawc_input exprdawc_price_adjustment_value" step="0.01" placeholder="0.00" value="0" />
                    </td>
                `;
            }

            if (fieldType === 'radio' || fieldType === 'select') {
                $optionsTable.find('tbody').append(
                    `
                    <tr>
                    <td class="move"><i class="dashicons dashicons-move"></i></td>
                    <td class="field_option_table_label_td">
                        <input type="text" name="extra_product_fields[${this.fieldIndex}][options][${optionIndex}][label]" placeholder="${exprdawc_admin_meta_boxes.option_label_placeholder}" />
                    </td>
                    <td class="field_option_table_value_td">
                        <input type="text" name="extra_product_fields[${this.fieldIndex}][options][${optionIndex}][value]" placeholder="${exprdawc_admin_meta_boxes.option_value_placeholder}" />
                    </td>
                    <td class="field_option_table_selected_td">
                        <input type="radio" name="extra_product_fields[${this.fieldIndex}][default]" value="${optionIndex}" />
                    </td>
                    ${priceAdjustmentColumns}
                    <td class="field_option_table_action_td">
                        <button type="button" class="button remove_option">${exprdawc_admin_meta_boxes.remove}</button>
                    </td>
                    </tr>
                    `
                );
            } else {
                $optionsTable.find('tbody').append(
                    `
                    <tr>
                    <td class="move"><i class="dashicons dashicons-move"></i></td>
                    <td class="field_option_table_label_td">
                        <input type="text" name="extra_product_fields[${this.fieldIndex}][options][${optionIndex}][label]" placeholder="${exprdawc_admin_meta_boxes.option_label_placeholder}" />
                    </td>
                    <td class="field_option_table_value_td">
                        <input type="text" name="extra_product_fields[${this.fieldIndex}][options][${optionIndex}][value]" placeholder="${exprdawc_admin_meta_boxes.option_value_placeholder}" />
                    </td>
                    <td class="field_option_table_selected_td">
                        <input type="checkbox" name="extra_product_fields[${this.fieldIndex}][options][${optionIndex}][default]" value="1" />
                    </td>
                    ${priceAdjustmentColumns}
                    <td class="field_option_table_action_td">
                        <button type="button" class="button remove_option">${exprdawc_admin_meta_boxes.remove}</button>
                    </td>
                    </tr>
                    `
                );
            }

            this.checkOptions($optionsTable.closest('.exprdawc_options'));
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
                this.checkOptions($(e.currentTarget).closest('.exprdawc_options'));
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
         * Export content.
         * @param {*} e 
         * @returns 
         */
        exportContent(e) {
            e.preventDefault();
            // Update all field indices
            this.updateFieldIndices();
            if (this.isDirty) {
                alert(`${exprdawc_admin_meta_boxes.pleaseSaveBeforeExportMsg}`);
                return;
            }
            const $exportString = $('#exprdawc_export_string');
            const exportContent = $exportString.val();
            if (!exportContent) {
                alert(`${exprdawc_admin_meta_boxes.emptyExportMsg}`);
                return;
            }
            navigator.clipboard.writeText(exportContent).then(function () {
                alert(`${exprdawc_admin_meta_boxes.copySuccessMsg}`);
            }, function (err) {
                console.error('Could not copy text: ', err);
                alert(`${exprdawc_admin_meta_boxes.copyErrorMsg}`);
            });
        }

        /**
         * Import content.
         * @param {*} e 
         * @returns 
         */
        importContent(e) {
            e.preventDefault();
            const exportString = prompt(exprdawc_admin_meta_boxes.enterExportString);
            if (exportString) {
                const sureImportQuestion = confirm(exprdawc_admin_meta_boxes.sureImportQuestion);
                if (!sureImportQuestion) {
                    return;
                }
                const productId = $('#post_ID').val();
                $.ajax({
                    url: ajaxurl,
                    method: 'POST',
                    data: {
                        action: 'exprdawc_import_custom_fields',
                        product_id: productId,
                        export_string: exportString,
                        security: exprdawc_admin_meta_boxes.edit_exprdawc_nonce
                    },
                    success: function (response) {
                        if (response.success) {
                            alert(exprdawc_admin_meta_boxes.importSuccessMsg);
                            location.reload();
                        } else {
                            alert(exprdawc_admin_meta_boxes.importErrorMsg);
                        }
                    },
                    error: function () {
                        alert(exprdawc_admin_meta_boxes.importErrorMsg);
                    },
                });
            }
        }

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
            const ruleGroupIndex = $container.find('.exprdawc_rule_group').length;
            const actualIndex = $(e.currentTarget).closest('.exprdawc_fields_table').data('index');
            const ruleGroupHtml = this.getRuleGroupHtml(actualIndex, ruleGroupIndex);
            $container.append(ruleGroupHtml);
        }

        /**
         * Add a rule.
         * @param {*} e 
         */
        addRule(e) {
            const $ruleGroup = $(e.currentTarget).closest('.exprdawc_rule_group');
            const ruleGroupIndex = $ruleGroup.index();
            const actualIndex = $(e.currentTarget).closest('.exprdawc_fields_table').data('index');
            const ruleIndex = $ruleGroup.find('.exprdawc_rule').length;
            const ruleHtml = this.getRuleHtml(actualIndex, ruleGroupIndex, ruleIndex);
            $ruleGroup.append(ruleHtml);
        }

        /**
         * Get rule group HTML.
         * @param {number} ruleGroupIndex 
         * @returns {string}
         */
        getRuleGroupHtml(actualIndex, ruleGroupIndex) {
            return `
            <div class="exprdawc_rule_group_container">
                <h2>${exprdawc_admin_meta_boxes.or}</h2>
                <div class="exprdawc_rule_group">
                    ${this.getRuleHtml(actualIndex, ruleGroupIndex, 0)}
                </div>
            </div>
            `;
        }

        /**
         * Get rule HTML.
         * @param {number} actualIndex
         * @param {number} ruleGroupIndex 
         * @param {number} ruleIndex 
         * @returns {string}
         */
        getRuleHtml(actualIndex, ruleGroupIndex, ruleIndex) {
            return `
            <div class="exprdawc_rule">
                <select name="extra_product_fields[${actualIndex}][conditional_rules][${ruleGroupIndex}][${ruleIndex}][field]" class="exprdawc_input exprdawc_conditional_field">
                <option value="">${exprdawc_admin_meta_boxes.selectFieldNone}</option>
                ${this.getAllFieldsOptions()}
                </select>
                <select name="extra_product_fields[${actualIndex}][conditional_rules][${ruleGroupIndex}][${ruleIndex}][operator]" class="exprdawc_input exprdawc_conditional_operator">
                    <option value="field_is_empty">${exprdawc_admin_meta_boxes.field_is_empty}</option>
                    <option value="field_is_not_empty">${exprdawc_admin_meta_boxes.field_is_not_empty}</option>
                    <option value="equals">${exprdawc_admin_meta_boxes.equals}</option>
                    <option value="not_equals">${exprdawc_admin_meta_boxes.notEquals}</option>
                    <option value="greater_than">${exprdawc_admin_meta_boxes.greaterThan}</option>
                    <option value="less_than">${exprdawc_admin_meta_boxes.lessThan}</option>
                </select>
                <input type="text" name="extra_product_fields[${actualIndex}][conditional_rules][${ruleGroupIndex}][${ruleIndex}][value]" class="exprdawc_input exprdawc_conditional_value" placeholder="${exprdawc_admin_meta_boxes.enterValue}" style="display:none;" />
                <button type="button" class="button remove_rule"><i class="dashicons dashicons-trash"></i></button>
                <button type="button" class="button add_rule">+ ${exprdawc_admin_meta_boxes.and}</button>
            </div>
            `;
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
            $('.exprdawc_conditional_operator').each((index, element) => {
                this.toggleConditionalTable({ currentTarget: element });
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
                options += `<option value="${label}">${label}</option>`;
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
            const $table_setting = $(e.currentTarget).closest('.exprdawc_options').find('.exprdawc_price_adjustment_table');
            const fieldType = $(e.currentTarget).closest('.exprdawc_fields_table').find('.exprdawc_attribute_type').val();

            console.log(fieldType);

            // Only show if type are not radio, checkbox or select.
            if (fieldType !== 'radio' && fieldType !== 'checkbox' && fieldType !== 'select') {
                if (checkbox.is(':checked')) {
                    $table_setting.show();
                } else {
                    $table_setting.hide();
                }

                // Remove extra columns if they exist
                $('.field_price_adjustment_type_th, .field_price_adjustment_val_th').remove();
                $('.field_price_adjustment_type, .field_price_adjustment_value').remove();
            } else {
                $table_setting.hide();

                if (!checkbox.is(':checked')) {
                    $('.field_price_adjustment_type_th, .field_price_adjustment_val_th').hide();
                    $('.field_price_adjustment_type, .field_price_adjustment_value').hide();
                } else {
                    $('.field_price_adjustment_type_th, .field_price_adjustment_val_th').show();
                    $('.field_price_adjustment_type, .field_price_adjustment_value').show();
                }

                const $optionsTable = $(e.currentTarget).closest('.exprdawc_options').find('.exprdawc_options_table');
                console.log($optionsTable);

                const optionIndex = $optionsTable.find('tbody tr').length;

                // Add extra columns if they don't exist
                if ($optionsTable.find('thead th.field_price_adjustment_type_th').length === 0) {

                    console.log('Adding extra columns');
                    console.log($optionsTable.find('thead th.field_option_table_action_th'));

                    $optionsTable.find('thead th.field_option_table_action_th').before(`
                        <th class="field_price_adjustment_type_th">${exprdawc_admin_meta_boxes.price_adjustment_type}</th>
                        <th class="field_price_adjustment_val_th">${exprdawc_admin_meta_boxes.price_adjustment_value}</th>
                    `);
                } else {
                    console.log('Extra columns already exist');
                }

                // Add extra columns to each row if they don't exist
                $optionsTable.find('tbody tr').each(function () {
                    if ($(this).find('.field_price_adjustment_type').length === 0) {
                        $(this).find('.field_option_table_action_td').before(
                            `
                            <td class="field_price_adjustment_type_${optionIndex} field_price_adjustment_type">
                                <select name="extra_product_fields[${this.fieldIndex}][options][${optionIndex}][price_adjustment_type]" class="exprdawc_input exprdawc_price_adjustment_type">
                                    <option value="fixed">${exprdawc_admin_meta_boxes.fixed}</option>
                                    <option value="quantity">${exprdawc_admin_meta_boxes.quantity}</option>
                                    <option value="percentage">${exprdawc_admin_meta_boxes.percentage}</option>
                                </select>
                            </td>
                            <td class="field_price_adjustment_value_${optionIndex} field_price_adjustment_value">
                                <input type="number" name="extra_product_fields[${this.fieldIndex}][options][${optionIndex}][price_adjustment_value]" class="exprdawc_input exprdawc_price_adjustment_value" placeholder="0.00" value="0" />
                            </td>
                            `
                        );
                    }
                });
            }
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
                if (fieldType === 'radio' || fieldType === 'checkbox') {
                    $placeholderText.hide();
                } else {
                    $placeholderText.show();
                }
            });
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

            // Reset input values in the cloned row and update the fieldIndex
            $clone.find('input, select').each(function () {
                const $input = $(this);

                // If the input is a field_name and contains a number, increment the number
                if ($input.hasClass('field_name') || $input.hasClass('exprdawc_placeholder')) {
                    const value = $input.val();
                    const numberMatch = value.match(/\d+$/);
                    if (numberMatch) {
                        const newValue = value.replace(/\d+$/, (parseInt(numberMatch[0], 10) + 1));
                        $input.val(newValue);
                    }
                }

                // If the input is a select.exprdawc_conditional_field and has a value, select the last option
                if ($input.is('select.exprdawc_conditional_field') && $input.val()) {
                    $input.find('option:last').prop('selected', true);
                }
            });

            // Append the cloned row to the table
            $row.after($clone);

            $('.exprdawc_attribute_type').trigger('change');

            // Update all field indices
            this.updateFieldIndices();

            // Update conditional field options
            this.updateConditionalFieldOptions();
        }

        /**
         * Updates all select.exprdawc_conditional_field options.
         */
        updateConditionalFieldOptions() {
            const options = this.getAllFieldsOptions();
            $('select.exprdawc_conditional_field').each(function () {
                const $select = $(this);
                const selectedValue = $select.val();
                $select.html(options);
                $select.val(selectedValue);
            });
        }

        /**
         * Updates the indices of all fields.
         */
        updateFieldIndices() {
            $('#exprdawc_field_body tr.exprdawc_fields_wrapper').each((index, element) => {
                // Update the field index
                const $row = $(element);
                $row.find('.exprdawc_fields_table').attr('data-index', index);
                $(element).find('input, select').each(function () {
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
                });
            });
        }

    }

    // Initialize the class.
    new ExprdawcMetaBoxesProduct();
});