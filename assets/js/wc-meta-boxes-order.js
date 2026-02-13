/******/ (() => { // webpackBootstrap
/*!**********************************************!*\
  !*** ./src/assets/js/wc-meta-boxes-order.js ***!
  \**********************************************/
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function"); }
function _defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, _toPropertyKey(o.key), o); } }
function _createClass(e, r, t) { return r && _defineProperties(e.prototype, r), t && _defineProperties(e, t), Object.defineProperty(e, "prototype", { writable: !1 }), e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
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
  var ExtraProductDataAdminOrder = /*#__PURE__*/function () {
    function ExtraProductDataAdminOrder() {
      _classCallCheck(this, ExtraProductDataAdminOrder);
      this.$orderItemsContainer = $('#woocommerce-order-items');
      this.modalView = null;
      this.initialize();
    }

    // Initialize event handlers
    return _createClass(ExtraProductDataAdminOrder, [{
      key: "initialize",
      value: function initialize() {
        this.setupEventHandlers();
      }

      // Setup event handlers for the order items container
    }, {
      key: "setupEventHandlers",
      value: function setupEventHandlers() {
        this.$orderItemsContainer.on('click', 'button.exprdawc_edit_addons', {
          action: 'edit'
        }, this.handleEditButtonClick.bind(this));
      }

      // Handle the click event for the edit button
    }, {
      key: "handleEditButtonClick",
      value: function handleEditButtonClick(event) {
        event.preventDefault();

        // Extend wcBackboneModal to create a custom modal view
        var CustomBackboneModal = $.WCBackboneModal.View.extend({
          addButton: this.handleDoneButtonClick.bind(this)
        });

        // Get the closest table row and retrieve the order item ID
        var $itemRow = $(event.currentTarget).closest('tr.item');
        var orderItemId = $itemRow.attr('data-order_item_id');

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
    }, {
      key: "populateModalForm",
      value: function populateModalForm() {
        var _this = this;
        this.blockUI(this.modalView.$el.find('.wc-backbone-modal-content'));
        var requestData = {
          action: 'woocommerce_configure_exprdawc_order_item',
          item_id: this.modalView._string.item_id,
          dataType: 'json',
          order_id: woocommerce_admin_meta_boxes.post_id,
          security: wc_exprdawc_admin_order_params.edit_exprdawc_nonce
        };
        $.post(woocommerce_admin_meta_boxes.ajax_url, requestData, function (response) {
          if (response.data && response.success) {
            _this.modalView.$el.find('form').html(response.data.html);
            _this.unblockUI(_this.modalView.$el.find('.wc-backbone-modal-content'));
          } else {
            window.alert(wc_exprdawc_admin_order_params.i18n_form_error);
            _this.unblockUI(_this.modalView.$el.find('.wc-backbone-modal-content'));
            _this.modalView.$el.find('.modal-close').trigger('click');
          }
        });
      }

      // Handle the click event for the done button
    }, {
      key: "handleDoneButtonClick",
      value: function handleDoneButtonClick(event) {
        var _this2 = this;
        var requestData = $.extend({}, {
          action: 'woocommerce_edit_exprdawc_order_item',
          item_id: this.modalView._string.item_id,
          dataType: 'json',
          order_id: woocommerce_admin_meta_boxes.post_id,
          security: wc_exprdawc_admin_order_params.edit_exprdawc_nonce
        });
        var formElement = this.modalView.$el.find('form')[0];
        if (formElement.reportValidity() !== true) {
          return;
        }
        var formData = new FormData(formElement);
        for (var property in requestData) {
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
          success: function success(response) {
            if (response.data && response.success) {
              _this2.$orderItemsContainer.find('.inside').empty();
              _this2.$orderItemsContainer.find('.inside').append(response.data.html);
              _this2.$orderItemsContainer.trigger('wc_order_items_reloaded');

              // Update notes.
              if (response.data.notes_html) {
                $('ul.order_notes').empty();
                $('ul.order_notes').append($(response.data.notes_html).find('li'));
              }
              _this2.unblockUI(_this2.modalView.$el.find('.wc-backbone-modal-content'));

              // Make it look like something changed.
              _this2.blockUI(_this2.$orderItemsContainer, {
                fadeIn: 0
              });
              setTimeout(function () {
                _this2.unblockUI(_this2.$orderItemsContainer);
              }, 250);
              _this2.modalView.closeButton(event);
            } else {
              window.alert(response.data.message);
              _this2.unblockUI(_this2.modalView.$el.find('.wc-backbone-modal-content'));
            }
          },
          error: function error() {
            window.alert(wc_exprdawc_admin_order_params.i18n_validation_error);
            _this2.unblockUI(_this2.modalView.$el.find('.wc-backbone-modal-content'));
          }
        });
      }

      // Block UI element
    }, {
      key: "blockUI",
      value: function blockUI($target, params) {
        var defaults = {
          message: null,
          overlayCSS: {
            background: '#fff',
            opacity: 0.6
          }
        };
        var options = $.extend({}, defaults, params || {});
        $target.block(options);
      }

      // Unblock UI element
    }, {
      key: "unblockUI",
      value: function unblockUI($target) {
        $target.unblock();
      }
    }]);
  }(); // Initialize the class
  new ExtraProductDataAdminOrder();
});
/******/ })()
;