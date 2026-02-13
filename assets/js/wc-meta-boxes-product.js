/******/ (() => { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ "./src/assets/js/wc-meta-boxes-product.js"
/*!************************************************!*\
  !*** ./src/assets/js/wc-meta-boxes-product.js ***!
  \************************************************/
() {

function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _readOnlyError(r) { throw new TypeError('"' + r + '" is read-only'); }
function _classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function"); }
function _defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, _toPropertyKey(o.key), o); } }
function _createClass(e, r, t) { return r && _defineProperties(e.prototype, r), t && _defineProperties(e, t), Object.defineProperty(e, "prototype", { writable: !1 }), e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
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
  var ExprdawcMetaBoxesProduct = /*#__PURE__*/function () {
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
    function ExprdawcMetaBoxesProduct() {
      _classCallCheck(this, ExprdawcMetaBoxesProduct);
      var $fields = $('#exprdawc_field_body').find('tr.exprdawc_attribute');
      this.fieldIndex = $fields.length;
      this.fieldIndex = $('#exprdawc_field_body tr.exprdawc_attribute').length;
      this.isDirty = false;
      this.init();
    }

    /**
     * Initialize the class.
     */
    return _createClass(ExprdawcMetaBoxesProduct, [{
      key: "init",
      value: function init() {
        this.bindEvents();
        this.noEntryContent();
      }

      /**
       * Bind events.
       */
    }, {
      key: "bindEvents",
      value: function bindEvents() {
        $('#exprdawc_add_custom_field').on('click', this.addCustomField.bind(this));
        $(document).on('click', '.exprdawc_remove_custom_field', this.removeCustomField.bind(this));
        $(document).on('change', '.exprdawc_attribute_type', this.toggleOptions.bind(this));
        $(document).on('click', '.exprdawc_attribute_type', this.openOptionsTable.bind(this));
        $(document).on('click', '.exprdawc_attribute_input_name', this.openOptionsTable.bind(this));
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
        $(document).on('change keyup', '.field_option_table_value_td input', this.syncOptionValueToDefault.bind(this));
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
          start: function start(event, ui) {
            ui.item.css('background-color', '#f6f6f6');
          },
          stop: function stop(event, ui) {
            ui.item.removeAttr('style');
          },
          update: function update(event, ui) {
            this.updateFieldIndices();
          }
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
            start: function start(event, ui) {
              ui.item.css('background-color', '#f6f6f6');
            },
            stop: function stop(event, ui) {
              ui.item.removeAttr('style');
            }
          });
        });

        // Get the name of the input and set the header.
        $('#exprdawc_attribute_container').on('input', '.exprdawc_attribute .exprdawc_attribute_input_name input', function () {
          var text = $(this).val(),
            target = $(this).closest('.exprdawc_attribute').find('.attribute_name');
          if (text) {
            target.text(text);
          }
        });
      }

      /**
       * Add a custom field.
       */
    }, {
      key: "addCustomField",
      value: function addCustomField() {
        this.fieldIndex++;
        this.setDirty();
        $('#exprdawc_field_body').append("\n                <tr class=\"exprdawc_fields_wrapper\">\n                <td colspan=\"5\">\n                <table class=\"exprdawc_fields_table\" data-index=\"".concat(this.fieldIndex, "\">\n\t                <tbody>\n                        <tr class=\"exprdawc_attribute\">\n                            <td class=\"move\"><i class=\"dashicons dashicons-move\"></i></td>\n                            <td class=\"cl-arr\"><i class=\"dashicons dashicons-arrow-up toggle-options\"></i></td>\n                            <td class=\"exprdawc_attribute_input_name\">\n                                <input type=\"text\" class=\"exprdawc_input exprdawc_textinput exprdawc_label field_name\" name=\"extra_product_fields[").concat(this.fieldIndex, "][label]\" placeholder=\"").concat(exprdawc_admin_meta_boxes.label_placeholder, "\" />\n                            </td>\n                            <td>\n                                <select id=\"exprdawc_attribute_type_").concat(this.fieldIndex, "\" name=\"extra_product_fields[").concat(this.fieldIndex, "][type]\" class=\"exprdawc_attribute_type\">\n                                    <option value=\"text\">").concat(exprdawc_admin_meta_boxes.short_text, "</option>\n                                    <option value=\"long_text\">").concat(exprdawc_admin_meta_boxes.long_text, "</option>\n                                    <option value=\"email\">").concat(exprdawc_admin_meta_boxes.email, "</option>\n                                    <option value=\"number\">").concat(exprdawc_admin_meta_boxes.number, "</option>\n                                    <option value=\"date\">").concat(exprdawc_admin_meta_boxes.date, "</option>\n                                    <option value=\"yes-no\">").concat(exprdawc_admin_meta_boxes.yes_no, "</option>\n                                    <option value=\"radio\">").concat(exprdawc_admin_meta_boxes.radio, "</option>\n                                    <option value=\"checkbox\">").concat(exprdawc_admin_meta_boxes.checkbox, "</option>\n                                    <option value=\"select\">").concat(exprdawc_admin_meta_boxes.select, "</option>\n                                </select>\n                            </td>\n                            <td>\n                                <button type=\"button\" class=\"exprdawc_remove_custom_field button\"><i class=\"dashicons dashicons-trash\"></i></button>\n                                <button type=\"button\" class=\"button exprdawc_copy_custom_field\"><i class=\"dashicons dashicons-admin-page\"></i></button>\n                                <input type=\"hidden\" class=\"exprdawc_attribute_index\" name=\"extra_product_fields[").concat(this.fieldIndex, "][index]\" value=\"").concat(this.fieldIndex, "\"/>\n                            </td>\n                        </tr>\n                        <tr class=\"exprdawc_options\" style=\"display: none;\">\n                            <td colspan=\"5\">\n\n                                <table class=\"exprdawc_settings_table exprdawc_general_table\">\n                                    <tbody>\n\n                                        <!-- Text Area Option/Settings -->\n                                        <tr>\n                                            <td class=\"exprdawc_attribute_require_checkbox\">\n                                                <label class=\"exprdawc_label\" for=\"exprdawc_text_required_").concat(this.fieldIndex, "\">\n                                                    <input type=\"checkbox\" id=\"exprdawc_text_required_").concat(this.fieldIndex, "\" class=\"exprdawc_input exprdawc_checkbox checkbox\" name=\"extra_product_fields[").concat(this.fieldIndex, "][required]\" value=\"1\" />\n                                                    ").concat(exprdawc_admin_meta_boxes.require_input, "\n                                                </label>                                       \n                                                <label class=\"exprdawc_label\" for=\"exprdawc_text_autofocus_").concat(this.fieldIndex, "\">\n                                                    <input type=\"checkbox\" id=\"exprdawc_text_autofocus_").concat(this.fieldIndex, "\" class=\"exprdawc_input exprdawc_checkbox checkbox\" name=\"extra_product_fields[").concat(this.fieldIndex, "][autofocus]\" value=\"1\" />\n                                                    ").concat(exprdawc_admin_meta_boxes.enable_autofocus, "\n                                                </label>\n                                                <label class=\"exprdawc_label\" for=\"exprdawc_text_editable_").concat(this.fieldIndex, "\">\n                                                    <input type=\"checkbox\" id=\"exprdawc_text_editable_").concat(this.fieldIndex, "\" class=\"exprdawc_input exprdawc_checkbox exprdawc_editable_field checkbox\" name=\"extra_product_fields[").concat(this.fieldIndex, "][editable]\" value=\"1\" />\n                                                    ").concat(exprdawc_admin_meta_boxes.enable_editable, "\n                                                </label>\n\n                                                <!-- Enable Conditional Logic and show table -->\n                                                <label class=\"exprdawc_label\" for=\"exprdawc_text_conditional_logic_").concat(this.fieldIndex, "\">\n                                                    <input type=\"checkbox\" id=\"exprdawc_text_conditional_logic_").concat(this.fieldIndex, "\" class=\"exprdawc_input exprdawc_checkbox exprdawc_conditional_logic_field checkbox\" name=\"extra_product_fields[").concat(this.fieldIndex, "][conditional_logic]\" value=\"1\" />\n                                                    ").concat(exprdawc_admin_meta_boxes.enable_conditional_logic, "\n                                                </label>\n\n                                                <!-- Enable Price Adjustment and show table -->\n                                                <label class=\"exprdawc_label\" for=\"exprdawc_text_price_adjustment_").concat(this.fieldIndex, "\">\n                                                    <input type=\"checkbox\" id=\"exprdawc_text_price_adjustment_").concat(this.fieldIndex, "\" class=\"exprdawc_input exprdawc_checkbox exprdawc_adjust_price_field checkbox\" name=\"extra_product_fields[").concat(this.fieldIndex, "][adjust_price]\" value=\"1\" />\n                                                    ").concat(exprdawc_admin_meta_boxes.enable_price_adjustment, "\n                                                </label>\n\n                                            </td>\n                                            <td class=\"exprdawc_attribute_placeholder_text\">\n                                                <label class=\"exprdawc_label\" for=\"exprdawc_text_placeholder_text_").concat(this.fieldIndex, "\">").concat(exprdawc_admin_meta_boxes.placeholder_text, "</label>\n                                                <input type=\"text\" id=\"exprdawc_text_placeholder_text_").concat(this.fieldIndex, "\" class=\"exprdawc_input exprdawc_textinput exprdawc_placeholder\" name=\"extra_product_fields[").concat(this.fieldIndex, "][placeholder_text]\" placeholder=\"").concat(exprdawc_admin_meta_boxes.placeholder_text, "\" />\n                                            </td>\n                                            <td class=\"exprdawc_attribute_help_text\">\n                                                <label class=\"exprdawc_label\" for=\"exprdawc_text_help_text_").concat(this.fieldIndex, "\">").concat(exprdawc_admin_meta_boxes.help_text, "</label>\n                                                <input type=\"text\" id=\"exprdawc_text_help_text_").concat(this.fieldIndex, "\" class=\"exprdawc_input exprdawc_textinput exprdawc_helptext\" name=\"extra_product_fields[").concat(this.fieldIndex, "][help_text]\" placeholder=\"").concat(exprdawc_admin_meta_boxes.help_text, "\" />\n                                            </td>\n                                            <td>\n                                                <label class=\"exprdawc_label\" for=\"exprdawc_autocomplete_function_").concat(this.fieldIndex, "\">").concat(exprdawc_admin_meta_boxes.autocomplete_function, "</label>\n                                                <select id=\"exprdawc_autocomplete_function_").concat(this.fieldIndex, "\" name=\"extra_product_fields[").concat(this.fieldIndex, "][autocomplete]\" class=\"exprdawc_input exprdawc_attribute_type\">\n                                                    <option value=\"on\">").concat(exprdawc_admin_meta_boxes.autocomplete_on, "</option>\n                                                    <option value=\"off\">").concat(exprdawc_admin_meta_boxes.autocomplete_off, "</option>\n                                                    <option value=\"address-level1\">").concat(exprdawc_admin_meta_boxes.address_level1, "</option>\n                                                    <option value=\"address-level2\">").concat(exprdawc_admin_meta_boxes.address_level2, "</option>\n                                                    <option value=\"address-level3\">").concat(exprdawc_admin_meta_boxes.address_level3, "</option>\n                                                    <option value=\"address-level4\">").concat(exprdawc_admin_meta_boxes.address_level4, "</option>\n                                                    <option value=\"address-line1\">").concat(exprdawc_admin_meta_boxes.address_line1, "</option>\n                                                    <option value=\"address-line2\">").concat(exprdawc_admin_meta_boxes.address_line2, "</option>\n                                                    <option value=\"address-line3\">").concat(exprdawc_admin_meta_boxes.address_line3, "</option>\n                                                    <option value=\"bday\">").concat(exprdawc_admin_meta_boxes.bday, "</option>\n                                                    <option value=\"bday-day\">").concat(exprdawc_admin_meta_boxes.bday_day, "</option>\n                                                    <option value=\"bday-month\">").concat(exprdawc_admin_meta_boxes.bday_month, "</option>\n                                                    <option value=\"bday-year\">").concat(exprdawc_admin_meta_boxes.bday_year, "</option>\n                                                    <option value=\"cc-additional-name\">").concat(exprdawc_admin_meta_boxes.cc_additional_name, "</option>\n                                                    <option value=\"cc-csc\">").concat(exprdawc_admin_meta_boxes.cc_csc, "</option>\n                                                    <option value=\"cc-exp\">").concat(exprdawc_admin_meta_boxes.cc_exp, "</option>\n                                                    <option value=\"cc-exp-month\">").concat(exprdawc_admin_meta_boxes.cc_exp_month, "</option>\n                                                    <option value=\"cc-exp-year\">").concat(exprdawc_admin_meta_boxes.cc_exp_year, "</option>\n                                                    <option value=\"cc-family-name\">").concat(exprdawc_admin_meta_boxes.cc_family_name, "</option>\n                                                    <option value=\"cc-given-name\">").concat(exprdawc_admin_meta_boxes.cc_given_name, "</option>\n                                                    <option value=\"cc-name\">").concat(exprdawc_admin_meta_boxes.cc_name, "</option>\n                                                    <option value=\"cc-number\">").concat(exprdawc_admin_meta_boxes.cc_number, "</option>\n                                                    <option value=\"cc-type\">").concat(exprdawc_admin_meta_boxes.cc_type, "</option>\n                                                    <option value=\"country\">").concat(exprdawc_admin_meta_boxes.country, "</option>\n                                                    <option value=\"country-name\">").concat(exprdawc_admin_meta_boxes.country_name, "</option>\n                                                    <option value=\"email\">").concat(exprdawc_admin_meta_boxes.email, "</option>\n                                                    <option value=\"language\">").concat(exprdawc_admin_meta_boxes.language, "</option>\n                                                    <option value=\"photo\">").concat(exprdawc_admin_meta_boxes.photo, "</option>\n                                                    <option value=\"postal-code\">").concat(exprdawc_admin_meta_boxes.postal_code, "</option>\n                                                    <option value=\"sex\">").concat(exprdawc_admin_meta_boxes.sex, "</option>\n                                                    <option value=\"street-address\">").concat(exprdawc_admin_meta_boxes.street_address, "</option>\n                                                    <option value=\"tel\">").concat(exprdawc_admin_meta_boxes.tel, "</option>\n                                                    <option value=\"tel-area-code\">").concat(exprdawc_admin_meta_boxes.tel_area_code, "</option>\n                                                    <option value=\"tel-country-code\">").concat(exprdawc_admin_meta_boxes.tel_country_code, "</option>\n                                                    <option value=\"tel-extension\">").concat(exprdawc_admin_meta_boxes.tel_extension, "</option>\n                                                    <option value=\"tel-local\">").concat(exprdawc_admin_meta_boxes.tel_local, "</option>\n                                                    <option value=\"tel-local-prefix\">").concat(exprdawc_admin_meta_boxes.tel_local_prefix, "</option>\n                                                    <option value=\"tel-local-suffix\">").concat(exprdawc_admin_meta_boxes.tel_local_suffix, "</option>\n                                                    <option value=\"tel-national\">").concat(exprdawc_admin_meta_boxes.tel_national, "</option>\n                                                    <option value=\"transaction-amount\">").concat(exprdawc_admin_meta_boxes.transaction_amount, "</option>\n                                                    <option value=\"transaction-currency\">").concat(exprdawc_admin_meta_boxes.transaction_currency, "</option>\n                                                    <option value=\"url\">").concat(exprdawc_admin_meta_boxes.url, "</option>\n                                                </select>\n                                            </td>\n                                        </tr>\n                                    </tbody>\n                                </table>\n                                <hr>\n\n                                <!-- Price Adjustment -->\n                                <table class=\"exprdawc_settings_table exprdawc_price_adjustment_table\" style=\"display:none;\">\n                                    <tbody>\n                                        <tr>\n                                            <td>\n                                                <label class=\"exprdawc_label\" for=\"exprdawc_price_adjustment_type_").concat(this.fieldIndex, "\">").concat(exprdawc_admin_meta_boxes.price_adjustment_type, "</label>\n                                                <select id=\"exprdawc_price_adjustment_type_").concat(this.fieldIndex, "\" name=\"extra_product_fields[").concat(this.fieldIndex, "][price_adjustment_type]\" class=\"exprdawc_input exprdawc_price_adjustment_type\">\n                                                    <option value=\"fixed\">").concat(exprdawc_admin_meta_boxes.fixed, "</option>\n                                                    <option vlaue=\"quantity\">").concat(exprdawc_admin_meta_boxes.quantity, "</option>\n                                                    <option value=\"percentage\">").concat(exprdawc_admin_meta_boxes.percentage, "</option>\n                                                </select>\n                                            </td>\n                                            <td>\n                                                <label class=\"exprdawc_label\" for=\"exprdawc_price_adjustment_value_").concat(this.fieldIndex, "\">").concat(exprdawc_admin_meta_boxes.price_adjustment_value, "</label>\n                                                <input type=\"number\" id=\"exprdawc_price_adjustment_value_").concat(this.fieldIndex, "\" class=\"exprdawc_input exprdawc_price_adjustment_value\" placeholder=\"0.00\" name=\"extra_product_fields[").concat(this.fieldIndex, "][price_adjustment_value]\" value=\"0\" />\n                                            </td>\n                                        </tr>\n                                    </tbody>\n                                </table>\n\n                                <!-- Conditional Logic -->\n\t\t\t\t                <table class=\"exprdawc_settings_table exprdawc_conditional_logic_table\" style=\"display:none;\">\n                                    <tbody>\n                                        <tr>\n                                           <td colspan=\"3\">\n                                                <label class=\"exprdawc_label\">").concat(exprdawc_admin_meta_boxes.conditionals, "</label>\n\t\t\t\t\t\t\t\t                <p>").concat(exprdawc_admin_meta_boxes.conditionals_description, "</p>\n                                                <div class=\"exprdawc_conditional_rules\">\n                                                    <div class=\"exprdawc_rule_group_container\">\n                                                        <div class=\"exprdawc_rule_group\">\n                                                            <div class=\"exprdawc_rule\">\n                                                                <select name=\"extra_product_fields[").concat(this.fieldIndex, "][conditional_rules][0][0][field]\" class=\"exprdawc_input exprdawc_conditional_field\">\n                                                                <option value=\"\">").concat(exprdawc_admin_meta_boxes.selectFieldNone, "</option>\n                                                                ").concat(this.getAllFieldsOptions(), "\n                                                                </select>\n                                                                <select name=\"extra_product_fields[").concat(this.fieldIndex, "][conditional_rules][0][0][operator]\" class=\"exprdawc_input exprdawc_conditional_operator\">\n                                                                    <option value=\"field_is_empty\">").concat(exprdawc_admin_meta_boxes.field_is_empty, "</option>\n                                                                    <option value=\"field_is_not_empty\">").concat(exprdawc_admin_meta_boxes.field_is_not_empty, "</option>\n                                                                    <option value=\"equals\">").concat(exprdawc_admin_meta_boxes.equals, "</option>\n                                                                    <option value=\"not_equals\">").concat(exprdawc_admin_meta_boxes.notEquals, "</option>\n                                                                    <option value=\"greater_than\">").concat(exprdawc_admin_meta_boxes.greaterThan, "</option>\n                                                                    <option value=\"less_than\">").concat(exprdawc_admin_meta_boxes.lessThan, "</option>\n                                                                </select>\n                                                                <input type=\"text\" name=\"extra_product_fields[").concat(this.fieldIndex, "][conditional_rules][0][0][value]\" class=\"exprdawc_input exprdawc_conditional_value\" placeholder=\"").concat(exprdawc_admin_meta_boxes.enterValue, "\" style=\"display:none;\" />\n                                                                <button type=\"button\" class=\"button remove_rule\"><i class=\"dashicons dashicons-trash\"></i></button>\n                                                                <button type=\"button\" class=\"button add_rule\">").concat(exprdawc_admin_meta_boxes.and, "</button>\n                                                            </div>\n                                                        </div>\n                                                    </div>\n                                                </div>\n                                            </td>                                            \n                                        </tr>\n                                    </tbody>\n                                </table>\n\n\n                                <!-- Text Area Option/Settings -->\n                                <table class=\"exprdawc_settings_table exprdawc_long_text_table\" style=\"display:none;\">\n                                    <tbody>\n                                        <tr>\n                                            <td>\n                                                <label class=\"exprdawc_label\" for=\"exprdawc_long_text_rows_").concat(this.fieldIndex, "\">").concat(exprdawc_admin_meta_boxes.rows, "</label>\n                                                <input type=\"number\" id=\"exprdawc_long_text_rows_").concat(this.fieldIndex, "\" class=\"exprdawc_input exprdawc_long_text_rows\" name=\"extra_product_fields[").concat(this.fieldIndex, "][rows]\" value=\"2\" />\n                                            </td>\n                                            <td>\n                                                <label class=\"exprdawc_label\" for=\"exprdawc_long_text_cols_").concat(this.fieldIndex, "\">").concat(exprdawc_admin_meta_boxes.columns, "</label>\n                                                <input type=\"number\" id=\"exprdawc_long_text_cols_").concat(this.fieldIndex, "\" class=\"exprdawc_input exprdawc_long_text_cols\" name=\"extra_product_fields[").concat(this.fieldIndex, "][cols]\" value=\"5\" />\n                                            </td>\n                                            <td>\n                                                <label class=\"exprdawc_label\" for=\"exprdawc_long_text_default_").concat(this.fieldIndex, "\">").concat(exprdawc_admin_meta_boxes.default_value, "</label>\n                                                <textarea id=\"exprdawc_long_text_default_").concat(this.fieldIndex, "\" class=\"exprdawc_textarea\" rows=\"3\" cols=\"30\" placeholder=\"").concat(exprdawc_admin_meta_boxes.enter_default_text, "\" name=\"extra_product_fields[").concat(this.fieldIndex, "][default]\"></textarea>\n                                            </td>\n                                        </tr>\n                                    </tbody>\n                                </table>\n\n                                <!-- Text Option/Settings for radio, checkboxes and slects -->\n                                <table class=\"exprdawc_settings_table exprdawc_text_table\" style=\"display:none;\">\n                                    <tbody>\n                                        <tr>\n                                            <td>\n                                                <label class=\"exprdawc_label\" for=\"exprdawc_text_min_length_").concat(this.fieldIndex, "\">").concat(exprdawc_admin_meta_boxes.min_length, "</label>\n                                                <input type=\"number\" id=\"exprdawc_text_min_length_").concat(this.fieldIndex, "\" class=\"exprdawc_input exprdawc_text_min_length\" name=\"extra_product_fields[").concat(this.fieldIndex, "][minlength]\" value=\"0\" />\n                                            </td>\n                                            <td>\n                                                <label class=\"exprdawc_label\" for=\"exprdawc_text_max_length_").concat(this.fieldIndex, "\">").concat(exprdawc_admin_meta_boxes.max_length, "</label>\n                                                <input type=\"number\" id=\"exprdawc_text_max_length_").concat(this.fieldIndex, "\" class=\"exprdawc_input exprdawc_text_max_length\" name=\"extra_product_fields[").concat(this.fieldIndex, "][maxlength]\" value=\"255\" />\n                                            </td>\n                                            <td>\n                                                <label class=\"exprdawc_label\" for=\"exprdawc_text_default_").concat(this.fieldIndex, "\">").concat(exprdawc_admin_meta_boxes.default_value, "</label>\n                                                <input type=\"text\" id=\"exprdawc_text_default_").concat(this.fieldIndex, "\" class=\"exprdawc_input exprdawc_text_default\" placeholder=\"").concat(exprdawc_admin_meta_boxes.enter_default_text, "\" name=\"extra_product_fields[").concat(this.fieldIndex, "][default]\" />\n                                            </td>\n                                        </tr>\n                                    </tbody>\n                                </table>\n\n                                <table class=\"exprdawc_options_table\" style=\"display:none;\">\n                                    <thead>\n                                        <tr>\n                                            <th></th>\n                                            <th class=\"field_option_table_label_th\">").concat(exprdawc_admin_meta_boxes.option_label, "</th>\n                                            <th class=\"field_option_table_value_th\">").concat(exprdawc_admin_meta_boxes.option_value, "</th>\n                                            <th class=\"field_option_table_selected_th\">").concat(exprdawc_admin_meta_boxes.default_selected, "</th>\n                                            <th class=\"field_option_table_action_th\">").concat(exprdawc_admin_meta_boxes.action, "</th>\n                                        </tr>\n                                    </thead>\n                                    <tbody>\n                                        <!-- Options will be dynamically added here -->\n                                    </tbody>\n                                    <tfoot>\n                                        <tr>\n                                            <td colspan=\"6\">\n                                                <button type=\"button\" class=\"button add_option\">").concat(exprdawc_admin_meta_boxes.add_option, "</button>\n                                            </td>\n                                        </tr>\n                                    </tfoot>\n                                </table>\n                                <p class=\"exprdawc_no_entry_message\" style=\"display: none;\">").concat(exprdawc_admin_meta_boxes.no_options, "</p>\n                            </td>\n                        </tr>\n                    </tbody>\n                </table>\n                </td>\n                </tr>\n                "));
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
    }, {
      key: "removeCustomField",
      value: function removeCustomField(e) {
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
    }, {
      key: "toggleOptions",
      value: function toggleOptions(e) {
        this.setDirty();
        var $row = $(e.currentTarget).closest('tr');
        var $optionsRow = $row.next('.exprdawc_options');
        var $optionsTable = $optionsRow.find('.exprdawc_options_table');
        var $placeholderText = $optionsRow.find('.exprdawc_placeholder');
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
    }, {
      key: "toggleOptionsTable",
      value: function toggleOptionsTable(e) {
        var $icon = $(e.currentTarget);
        var $optionsRow = $icon.closest('tr').next('.exprdawc_options');
        $optionsRow.toggle();
        $icon.toggleClass('dashicons-arrow-down dashicons-arrow-up');
      }

      /**
       * Open options table (always expand).
       * @param {*} e
       */
    }, {
      key: "openOptionsTable",
      value: function openOptionsTable(e) {
        var $target = $(e.currentTarget);
        var $row = $target.closest('tr.exprdawc_attribute');
        var $optionsRow = $row.next('.exprdawc_options');
        var $icon = $row.find('.toggle-options');
        $optionsRow.show();
        $icon.removeClass('dashicons-arrow-up').addClass('dashicons-arrow-down');
      }

      /**
       * Add an option.
       * @param {*} e 
       */
    }, {
      key: "addOption",
      value: function addOption(e) {
        this.setDirty();
        var $optionsTable = $(e.currentTarget).closest('.exprdawc_options_table');
        var actual_index = $optionsTable.closest('.exprdawc_fields_table').data('index');

        // Guard: if actual_index is undefined or null, log and inform the user.
        if (typeof actual_index === 'undefined' || actual_index === null) {
          console.error('exprdawc: actual_index is undefined or null', $optionsTable);
          return;
        }
        var optionIndex = $optionsTable.find('tbody tr').length;
        var fieldType = $optionsTable.closest('.exprdawc_fields_table').find('.exprdawc_attribute_type').val();
        var isPriceAdjustmentEnabled = $optionsTable.closest('.exprdawc_options').find('.exprdawc_adjust_price_field').is(':checked');
        var priceAdjustmentColumns = '';
        if (isPriceAdjustmentEnabled) {
          priceAdjustmentColumns = "\n                    <td class=\"field_price_adjustment_type_".concat(optionIndex, " field_price_adjustment_type\">\n                    <select name=\"extra_product_fields[").concat(actual_index, "][options][").concat(optionIndex, "][price_adjustment_type]\" class=\"exprdawc_input exprdawc_price_adjustment_type\">\n                        <option value=\"fixed\">").concat(exprdawc_admin_meta_boxes.fixed, "</option>\n                        <option value=\"quantity\">").concat(exprdawc_admin_meta_boxes.quantity, "</option>\n                        <option value=\"percentage\">").concat(exprdawc_admin_meta_boxes.percentage, "</option>\n                    </select>\n                    </td>\n                    <td class=\"field_price_adjustment_value_").concat(optionIndex, " field_price_adjustment_value\">\n                        <input type=\"number\" name=\"extra_product_fields[").concat(actual_index, "][options][").concat(optionIndex, "][price_adjustment_value]\" class=\"exprdawc_input exprdawc_price_adjustment_value\" step=\"0.01\" placeholder=\"0.00\" value=\"0\" />\n                    </td>\n                ");
        }
        if (fieldType === 'radio' || fieldType === 'select') {
          $optionsTable.find('tbody').append("\n                    <tr>\n                    <td class=\"move\"><i class=\"dashicons dashicons-move\"></i></td>\n                    <td class=\"field_option_table_label_td\">\n                        <input type=\"text\" name=\"extra_product_fields[".concat(actual_index, "][options][").concat(optionIndex, "][label]\" placeholder=\"").concat(exprdawc_admin_meta_boxes.option_label_placeholder, "\" />\n                    </td>\n                    <td class=\"field_option_table_value_td\">\n                        <input type=\"text\" name=\"extra_product_fields[").concat(actual_index, "][options][").concat(optionIndex, "][value]\" placeholder=\"").concat(exprdawc_admin_meta_boxes.option_value_placeholder, "\" />\n                    </td>\n                    <td class=\"field_option_table_selected_td\">\n                        <input type=\"radio\" name=\"extra_product_fields[").concat(actual_index, "][default]\" value=\"").concat(optionIndex, "\" />\n                    </td>\n                    ").concat(priceAdjustmentColumns, "\n                    <td class=\"field_option_table_action_td\">\n                        <button type=\"button\" class=\"button remove_option\">").concat(exprdawc_admin_meta_boxes.remove, "</button>\n                    </td>\n                    </tr>\n                    "));
        } else {
          $optionsTable.find('tbody').append("\n                    <tr>\n                    <td class=\"move\"><i class=\"dashicons dashicons-move\"></i></td>\n                    <td class=\"field_option_table_label_td\">\n                        <input type=\"text\" name=\"extra_product_fields[".concat(actual_index, "][options][").concat(optionIndex, "][label]\" placeholder=\"").concat(exprdawc_admin_meta_boxes.option_label_placeholder, "\" />\n                    </td>\n                    <td class=\"field_option_table_value_td\">\n                        <input type=\"text\" name=\"extra_product_fields[").concat(actual_index, "][options][").concat(optionIndex, "][value]\" placeholder=\"").concat(exprdawc_admin_meta_boxes.option_value_placeholder, "\" />\n                    </td>\n                    <td class=\"field_option_table_selected_td\">\n                        <input type=\"checkbox\" name=\"extra_product_fields[").concat(actual_index, "][options][").concat(optionIndex, "][default]\" value=\"1\" />\n                    </td>\n                    ").concat(priceAdjustmentColumns, "\n                    <td class=\"field_option_table_action_td\">\n                        <button type=\"button\" class=\"button remove_option\">").concat(exprdawc_admin_meta_boxes.remove, "</button>\n                    </td>\n                    </tr>\n                    "));
        }
        this.checkOptions($optionsTable.closest('.exprdawc_options'));
      }

      /**
       * Remove an option.
       * @param {*} e 
       * @returns 
       */
    }, {
      key: "removeOption",
      value: function removeOption(e) {
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
    }, {
      key: "checkOptions",
      value: function checkOptions($optionsRow) {
        var $optionsTable = $optionsRow.find('.exprdawc_options_table tbody');
        var $noEntryMessage = $optionsRow.find('.exprdawc_no_entry_message');
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
    }, {
      key: "syncOptionValueToDefault",
      value: function syncOptionValueToDefault(e) {
        var $input = $(e.currentTarget);
        var $row = $input.closest('tr');
        var $optionsTable = $input.closest('.exprdawc_options_table');
        var optionIndex = $optionsTable.find('tbody tr').index($row);
        var actualIndex = $optionsTable.closest('.exprdawc_fields_table').data('index');
        if (typeof actualIndex === 'undefined' || actualIndex === null) {
          console.error('exprdawc: actualIndex is undefined or null', $optionsTable);
          return;
        }
        var newValue = $input.val();
        var fieldType = $optionsTable.closest('.exprdawc_fields_table').find('.exprdawc_attribute_type').val();

        // For radio/select types the default is a single value input (radio)
        if (fieldType === 'radio' || fieldType === 'select') {
          var $targetRadio = $optionsTable.find('tbody tr').eq(optionIndex).find('input[type="radio"]');
          if ($targetRadio.length) {
            $targetRadio.val(newValue);
          } else {
            // Fallback: try to find radios by name pattern and set the matching index
            var $radios = $optionsTable.find('input[type="radio"][name^="extra_product_fields"]');
            if ($radios.length > optionIndex) {
              $radios.eq(optionIndex).val(newValue);
            }
          }
        }
      }

      /**
       * Export content.
       * @param {*} e 
       * @returns 
       */
    }, {
      key: "exportContent",
      value: function exportContent(e) {
        e.preventDefault();
        // Update all field indices
        this.updateFieldIndices();
        if (this.isDirty) {
          alert("".concat(exprdawc_admin_meta_boxes.pleaseSaveBeforeExportMsg));
          return;
        }
        var $exportString = $('#exprdawc_export_string');
        var exportContent = $exportString.val();
        if (!exportContent) {
          alert("".concat(exprdawc_admin_meta_boxes.emptyExportMsg));
          return;
        }
        navigator.clipboard.writeText(exportContent).then(function () {
          alert("".concat(exprdawc_admin_meta_boxes.copySuccessMsg));
        }, function (err) {
          console.error('Could not copy text: ', err);
          alert("".concat(exprdawc_admin_meta_boxes.copyErrorMsg));
        });
      }

      /**
       * Import content.
       * @param {*} e 
       * @returns 
       */
    }, {
      key: "importContent",
      value: function importContent(e) {
        e.preventDefault();
        var exportString = prompt(exprdawc_admin_meta_boxes.enterExportString);
        if (exportString) {
          var sureImportQuestion = confirm(exprdawc_admin_meta_boxes.sureImportQuestion);
          if (!sureImportQuestion) {
            return;
          }
          var productId = $('#post_ID').val();
          $.ajax({
            url: ajaxurl,
            method: 'POST',
            data: {
              action: 'exprdawc_import_custom_fields',
              product_id: productId,
              export_string: exportString,
              security: exprdawc_admin_meta_boxes.edit_exprdawc_nonce
            },
            success: function success(response) {
              if (response.success) {
                alert(exprdawc_admin_meta_boxes.importSuccessMsg);
                location.reload();
              } else {
                alert(exprdawc_admin_meta_boxes.importErrorMsg);
              }
            },
            error: function error() {
              alert(exprdawc_admin_meta_boxes.importErrorMsg);
            }
          });
        }
      }

      /**
       * Set dirty.
       */
    }, {
      key: "setDirty",
      value: function setDirty() {
        this.isDirty = true;
        this.disableExportLink();
      }

      /**
       * Disable export link.
       * @returns 
       */
    }, {
      key: "disableExportLink",
      value: function disableExportLink() {
        if (this.isDirty) {
          $('.exprdawc-export').hide();
          return;
        }
      }

      /**
       * No entry content.
       */
    }, {
      key: "noEntryContent",
      value: function noEntryContent() {
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
    }, {
      key: "checkAutocompleteField",
      value: function checkAutocompleteField(e) {
        var $currentCheckbox = $(e.currentTarget);
        var isChecked = $currentCheckbox.is(':checked');
        if (isChecked) {
          var $otherChecked = $('.exprdawc_autocomplete_field').not($currentCheckbox).filter(':checked');
          if ($otherChecked.length > 0) {
            var confirmOverwrite = confirm(exprdawc_admin_meta_boxes.sureAnotherAutocompleCheckedQuestion);
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
    }, {
      key: "addRuleGroup",
      value: function addRuleGroup(e) {
        var $container = $(e.currentTarget).closest('.exprdawc_conditional_logic_table').find('.exprdawc_conditional_rules');
        var ruleGroupIndex = $container.find('.exprdawc_rule_group').length;
        var actualIndex = $(e.currentTarget).closest('.exprdawc_fields_table').data('index');
        var ruleGroupHtml = this.getRuleGroupHtml(actualIndex, ruleGroupIndex);
        $container.append(ruleGroupHtml);
      }

      /**
       * Add a rule.
       * @param {*} e 
       */
    }, {
      key: "addRule",
      value: function addRule(e) {
        var $ruleGroup = $(e.currentTarget).closest('.exprdawc_rule_group');
        var ruleGroupIndex = $ruleGroup.index();
        var actualIndex = $(e.currentTarget).closest('.exprdawc_fields_table').data('index');
        var ruleIndex = $ruleGroup.find('.exprdawc_rule').length;
        var ruleHtml = this.getRuleHtml(actualIndex, ruleGroupIndex, ruleIndex);
        $ruleGroup.append(ruleHtml);
      }

      /**
       * Get rule group HTML.
       * @param {number} ruleGroupIndex 
       * @returns {string}
       */
    }, {
      key: "getRuleGroupHtml",
      value: function getRuleGroupHtml(actualIndex, ruleGroupIndex) {
        return "\n            <div class=\"exprdawc_rule_group_container\">\n                <h2>".concat(exprdawc_admin_meta_boxes.or, "</h2>\n                <div class=\"exprdawc_rule_group\">\n                    ").concat(this.getRuleHtml(actualIndex, ruleGroupIndex, 0), "\n                </div>\n            </div>\n            ");
      }

      /**
       * Get rule HTML.
       * @param {number} actualIndex
       * @param {number} ruleGroupIndex 
       * @param {number} ruleIndex 
       * @returns {string}
       */
    }, {
      key: "getRuleHtml",
      value: function getRuleHtml(actualIndex, ruleGroupIndex, ruleIndex) {
        return "\n            <div class=\"exprdawc_rule\">\n                <select name=\"extra_product_fields[".concat(actualIndex, "][conditional_rules][").concat(ruleGroupIndex, "][").concat(ruleIndex, "][field]\" class=\"exprdawc_input exprdawc_conditional_field\">\n                <option value=\"\">").concat(exprdawc_admin_meta_boxes.selectFieldNone, "</option>\n                ").concat(this.getAllFieldsOptions(), "\n                </select>\n                <select name=\"extra_product_fields[").concat(actualIndex, "][conditional_rules][").concat(ruleGroupIndex, "][").concat(ruleIndex, "][operator]\" class=\"exprdawc_input exprdawc_conditional_operator\">\n                    <option value=\"field_is_empty\">").concat(exprdawc_admin_meta_boxes.field_is_empty, "</option>\n                    <option value=\"field_is_not_empty\">").concat(exprdawc_admin_meta_boxes.field_is_not_empty, "</option>\n                    <option value=\"equals\">").concat(exprdawc_admin_meta_boxes.equals, "</option>\n                    <option value=\"not_equals\">").concat(exprdawc_admin_meta_boxes.notEquals, "</option>\n                    <option value=\"greater_than\">").concat(exprdawc_admin_meta_boxes.greaterThan, "</option>\n                    <option value=\"less_than\">").concat(exprdawc_admin_meta_boxes.lessThan, "</option>\n                </select>\n                <input type=\"text\" name=\"extra_product_fields[").concat(actualIndex, "][conditional_rules][").concat(ruleGroupIndex, "][").concat(ruleIndex, "][value]\" class=\"exprdawc_input exprdawc_conditional_value\" placeholder=\"").concat(exprdawc_admin_meta_boxes.enterValue, "\" style=\"display:none;\" />\n                <button type=\"button\" class=\"button remove_rule\"><i class=\"dashicons dashicons-trash\"></i></button>\n                <button type=\"button\" class=\"button add_rule\">+ ").concat(exprdawc_admin_meta_boxes.and, "</button>\n            </div>\n            ");
      }

      /**
       * Toggle conditional value field visibility.
       * @param {*} e 
       */
    }, {
      key: "toggleConditionalValueField",
      value: function toggleConditionalValueField(e) {
        var $operator = $(e.currentTarget);
        var $valueField = $operator.closest('.exprdawc_rule').find('.exprdawc_conditional_value');
        if ($operator.val() === 'field_changed' || $operator.val() === 'field_is_empty' || $operator.val() === 'field_is_not_empty') {
          $valueField.hide();
        } else {
          $valueField.show();
        }
      }

      // Init all Rules toggleConditionalValueField
    }, {
      key: "toggleConditionalValueFieldAll",
      value: function toggleConditionalValueFieldAll() {
        var _this = this;
        $('.exprdawc_conditional_operator').each(function (index, element) {
          _this.toggleConditionalValueField({
            currentTarget: element
          });
        });
      }

      // Init all Rules toggleConditionalValueField
    }, {
      key: "togglePriceAdjustmentTableAll",
      value: function togglePriceAdjustmentTableAll() {
        var _this2 = this;
        $('.exprdawc_conditional_operator').each(function (index, element) {
          _this2.toggleConditionalTable({
            currentTarget: element
          });
        });
      }

      /**
       * Remove a rule.
       * @param {*} e 
       * @returns 
       */
    }, {
      key: "removeRule",
      value: function removeRule(e) {
        if (confirm(exprdawc_admin_meta_boxes.confirm_delete_rule)) {
          var $ruleGroup = $(e.currentTarget).closest('.exprdawc_rule_group_container');
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
    }, {
      key: "getAllFieldsOptions",
      value: function getAllFieldsOptions() {
        var options = '';
        $('#exprdawc_field_body tr.exprdawc_attribute').each(function () {
          var label = $(this).find('.exprdawc_attribute_input_name input').val();
          options += "<option value=\"".concat(label, "\">").concat(label, "</option>");
        });
        return options;
      }

      /**
      * Enable or disable checkboxes based on a condition.
      */
    }, {
      key: "toggleConditionalTable",
      value: function toggleConditionalTable(e) {
        var checkbox = $(e.currentTarget);
        var $table_setting = $(e.currentTarget).closest('.exprdawc_options').find('.exprdawc_conditional_logic_table');
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
    }, {
      key: "togglePriceAdjustmentTable",
      value: function togglePriceAdjustmentTable(e) {
        var checkbox = $(e.currentTarget);
        var $table_setting = $(e.currentTarget).closest('.exprdawc_options').find('.exprdawc_price_adjustment_table');
        var fieldType = $(e.currentTarget).closest('.exprdawc_fields_table').find('.exprdawc_attribute_type').val();
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
          var $optionsTable = $(e.currentTarget).closest('.exprdawc_options').find('.exprdawc_options_table');
          console.log($optionsTable);
          var optionIndex = $optionsTable.find('tbody tr').length;

          // Add extra columns if they don't exist
          if ($optionsTable.find('thead th.field_price_adjustment_type_th').length === 0) {
            console.log('Adding extra columns');
            console.log($optionsTable.find('thead th.field_option_table_action_th'));
            $optionsTable.find('thead th.field_option_table_action_th').before("\n                        <th class=\"field_price_adjustment_type_th\">".concat(exprdawc_admin_meta_boxes.price_adjustment_type, "</th>\n                        <th class=\"field_price_adjustment_val_th\">").concat(exprdawc_admin_meta_boxes.price_adjustment_value, "</th>\n                    "));
          } else {
            console.log('Extra columns already exist');
          }

          // Add extra columns to each row if they don't exist
          $optionsTable.find('tbody tr').each(function () {
            if ($(this).find('.field_price_adjustment_type').length === 0) {
              $(this).find('.field_option_table_action_td').before("\n                            <td class=\"field_price_adjustment_type_".concat(optionIndex, " field_price_adjustment_type\">\n                                <select name=\"extra_product_fields[").concat(this.fieldIndex, "][options][").concat(optionIndex, "][price_adjustment_type]\" class=\"exprdawc_input exprdawc_price_adjustment_type\">\n                                    <option value=\"fixed\">").concat(exprdawc_admin_meta_boxes.fixed, "</option>\n                                    <option value=\"quantity\">").concat(exprdawc_admin_meta_boxes.quantity, "</option>\n                                    <option value=\"percentage\">").concat(exprdawc_admin_meta_boxes.percentage, "</option>\n                                </select>\n                            </td>\n                            <td class=\"field_price_adjustment_value_").concat(optionIndex, " field_price_adjustment_value\">\n                                <input type=\"number\" name=\"extra_product_fields[").concat(this.fieldIndex, "][options][").concat(optionIndex, "][price_adjustment_value]\" class=\"exprdawc_input exprdawc_price_adjustment_value\" placeholder=\"0.00\" value=\"0\" />\n                            </td>\n                            "));
            }
          });
        }
      }

      /**
       * Init Field Type specific settings.
       */
    }, {
      key: "initFieldTypeSettings",
      value: function initFieldTypeSettings() {
        var $optionsRow = $('.exprdawc_fields_wrapper');
        $optionsRow.each(function (index, element) {
          // By exprdawc_attribute_type checkbox, radio and select hide placeholder text and show options.
          var fieldType = $(element).find('.exprdawc_attribute_type').val() || 'text';
          var $placeholderText = $(element).find('.exprdawc_attribute_placeholder_text');
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
    }, {
      key: "exprdawc_copy_custom_field",
      value: function exprdawc_copy_custom_field(e) {
        e.preventDefault();
        this.setDirty();
        var $row = $(e.currentTarget).closest('.exprdawc_fields_wrapper');
        var $clone = $row.clone();

        // Reset input values in the cloned row and update the fieldIndex
        $clone.find('input, select').each(function () {
          var $input = $(this);

          // If the input is a field_name and contains a number, increment the number
          if ($input.hasClass('field_name') || $input.hasClass('exprdawc_placeholder')) {
            var value = $input.val();
            var numberMatch = value.match(/\d+$/);
            if (numberMatch) {
              var newValue = value.replace(/\d+$/, parseInt(numberMatch[0], 10) + 1);
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
    }, {
      key: "updateConditionalFieldOptions",
      value: function updateConditionalFieldOptions() {
        var options = this.getAllFieldsOptions();
        $('select.exprdawc_conditional_field').each(function () {
          var $select = $(this);
          var selectedValue = $select.val();
          $select.html(options);
          $select.val(selectedValue);
        });
      }

      /**
       * Updates the indices of all fields.
       */
    }, {
      key: "updateFieldIndices",
      value: function updateFieldIndices() {
        $('#exprdawc_field_body tr.exprdawc_fields_wrapper').each(function (index, element) {
          // Update the field index
          var $row = $(element);
          $row.find('.exprdawc_fields_table').attr('data-index', index);
          $(element).find('input, select').each(function () {
            var $input = $(this);

            // Update the name attribute with the new index
            var name = $input.attr('name');
            if (name) {
              $input.attr('name', name.replace(/\[\d+\]/, "[".concat(index, "]")));
            }

            // Update the id attribute with the new index
            var id = $input.attr('id');
            if (id) {
              $input.attr('id', id.replace(/_\d+$/, "_".concat(index)));
            }
          });
        });
      }
    }]);
  }(); // Initialize the class.
  new ExprdawcMetaBoxesProduct();
});

/***/ },

/***/ "./src/assets/scss/admin-backend.scss"
/*!********************************************!*\
  !*** ./src/assets/scss/admin-backend.scss ***!
  \********************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ },

/***/ "./src/assets/scss/forms.scss"
/*!************************************!*\
  !*** ./src/assets/scss/forms.scss ***!
  \************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ },

/***/ "./src/assets/scss/order-frontend.scss"
/*!*********************************************!*\
  !*** ./src/assets/scss/order-frontend.scss ***!
  \*********************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ }

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Check if module exists (development only)
/******/ 		if (__webpack_modules__[moduleId] === undefined) {
/******/ 			var e = new Error("Cannot find module '" + moduleId + "'");
/******/ 			e.code = 'MODULE_NOT_FOUND';
/******/ 			throw e;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = __webpack_modules__;
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/chunk loaded */
/******/ 	(() => {
/******/ 		var deferred = [];
/******/ 		__webpack_require__.O = (result, chunkIds, fn, priority) => {
/******/ 			if(chunkIds) {
/******/ 				priority = priority || 0;
/******/ 				for(var i = deferred.length; i > 0 && deferred[i - 1][2] > priority; i--) deferred[i] = deferred[i - 1];
/******/ 				deferred[i] = [chunkIds, fn, priority];
/******/ 				return;
/******/ 			}
/******/ 			var notFulfilled = Infinity;
/******/ 			for (var i = 0; i < deferred.length; i++) {
/******/ 				var [chunkIds, fn, priority] = deferred[i];
/******/ 				var fulfilled = true;
/******/ 				for (var j = 0; j < chunkIds.length; j++) {
/******/ 					if ((priority & 1 === 0 || notFulfilled >= priority) && Object.keys(__webpack_require__.O).every((key) => (__webpack_require__.O[key](chunkIds[j])))) {
/******/ 						chunkIds.splice(j--, 1);
/******/ 					} else {
/******/ 						fulfilled = false;
/******/ 						if(priority < notFulfilled) notFulfilled = priority;
/******/ 					}
/******/ 				}
/******/ 				if(fulfilled) {
/******/ 					deferred.splice(i--, 1)
/******/ 					var r = fn();
/******/ 					if (r !== undefined) result = r;
/******/ 				}
/******/ 			}
/******/ 			return result;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/jsonp chunk loading */
/******/ 	(() => {
/******/ 		// no baseURI
/******/ 		
/******/ 		// object to store loaded and loading chunks
/******/ 		// undefined = chunk not loaded, null = chunk preloaded/prefetched
/******/ 		// [resolve, reject, Promise] = chunk loading, 0 = chunk loaded
/******/ 		var installedChunks = {
/******/ 			"/assets/js/wc-meta-boxes-product": 0,
/******/ 			"assets/css/order-frontend": 0,
/******/ 			"assets/css/forms": 0,
/******/ 			"assets/css/admin-backend": 0
/******/ 		};
/******/ 		
/******/ 		// no chunk on demand loading
/******/ 		
/******/ 		// no prefetching
/******/ 		
/******/ 		// no preloaded
/******/ 		
/******/ 		// no HMR
/******/ 		
/******/ 		// no HMR manifest
/******/ 		
/******/ 		__webpack_require__.O.j = (chunkId) => (installedChunks[chunkId] === 0);
/******/ 		
/******/ 		// install a JSONP callback for chunk loading
/******/ 		var webpackJsonpCallback = (parentChunkLoadingFunction, data) => {
/******/ 			var [chunkIds, moreModules, runtime] = data;
/******/ 			// add "moreModules" to the modules object,
/******/ 			// then flag all "chunkIds" as loaded and fire callback
/******/ 			var moduleId, chunkId, i = 0;
/******/ 			if(chunkIds.some((id) => (installedChunks[id] !== 0))) {
/******/ 				for(moduleId in moreModules) {
/******/ 					if(__webpack_require__.o(moreModules, moduleId)) {
/******/ 						__webpack_require__.m[moduleId] = moreModules[moduleId];
/******/ 					}
/******/ 				}
/******/ 				if(runtime) var result = runtime(__webpack_require__);
/******/ 			}
/******/ 			if(parentChunkLoadingFunction) parentChunkLoadingFunction(data);
/******/ 			for(;i < chunkIds.length; i++) {
/******/ 				chunkId = chunkIds[i];
/******/ 				if(__webpack_require__.o(installedChunks, chunkId) && installedChunks[chunkId]) {
/******/ 					installedChunks[chunkId][0]();
/******/ 				}
/******/ 				installedChunks[chunkId] = 0;
/******/ 			}
/******/ 			return __webpack_require__.O(result);
/******/ 		}
/******/ 		
/******/ 		var chunkLoadingGlobal = self["webpackChunkextra_product_data_for_woocommerce"] = self["webpackChunkextra_product_data_for_woocommerce"] || [];
/******/ 		chunkLoadingGlobal.forEach(webpackJsonpCallback.bind(null, 0));
/******/ 		chunkLoadingGlobal.push = webpackJsonpCallback.bind(null, chunkLoadingGlobal.push.bind(chunkLoadingGlobal));
/******/ 	})();
/******/ 	
/************************************************************************/
/******/ 	
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	// This entry module depends on other loaded chunks and execution need to be delayed
/******/ 	__webpack_require__.O(undefined, ["assets/css/order-frontend","assets/css/forms","assets/css/admin-backend"], () => (__webpack_require__("./src/assets/js/wc-meta-boxes-product.js")))
/******/ 	__webpack_require__.O(undefined, ["assets/css/order-frontend","assets/css/forms","assets/css/admin-backend"], () => (__webpack_require__("./src/assets/scss/admin-backend.scss")))
/******/ 	__webpack_require__.O(undefined, ["assets/css/order-frontend","assets/css/forms","assets/css/admin-backend"], () => (__webpack_require__("./src/assets/scss/forms.scss")))
/******/ 	var __webpack_exports__ = __webpack_require__.O(undefined, ["assets/css/order-frontend","assets/css/forms","assets/css/admin-backend"], () => (__webpack_require__("./src/assets/scss/order-frontend.scss")))
/******/ 	__webpack_exports__ = __webpack_require__.O(__webpack_exports__);
/******/ 	
/******/ })()
;