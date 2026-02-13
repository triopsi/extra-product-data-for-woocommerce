/******/ (() => { // webpackBootstrap
/*!**************************************************!*\
  !*** ./src/assets/js/wc-conditional-rules-js.js ***!
  \**************************************************/
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function"); }
function _defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, _toPropertyKey(o.key), o); } }
function _createClass(e, r, t) { return r && _defineProperties(e.prototype, r), t && _defineProperties(e, t), Object.defineProperty(e, "prototype", { writable: !1 }), e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
jQuery(document).ready(function ($) {
  /**
   * Extra Product Data for WooCommerce Conditional Logic.
   * @class ExprdawcConditionalLogic
   * @description Handles the functionality for the conditional logic in the WooCommerce product data metabox
   * @since 1.0.0
   * @version 1.0.0
   * @package ExtraProductDataForWooCommerce/JS
   * @license GPL-2.0+
   * @link https://www.triopsi.dev
   */
  var ExprdawcConditionalLogic = /*#__PURE__*/function () {
    // Initialize the class
    function ExprdawcConditionalLogic() {
      _classCallCheck(this, ExprdawcConditionalLogic);
      this.init();
    }

    // Initialize the class
    return _createClass(ExprdawcConditionalLogic, [{
      key: "init",
      value: function init() {
        var _this = this;
        this.applyConditionalLogic();

        // Reapply conditional logic on any change of exprdawc-input fields
        $(document).on('change keyup', '.exprdawc-input', function () {
          _this.applyConditionalLogic();
        });
      }

      // Apply the conditional logic to the fields
    }, {
      key: "applyConditionalLogic",
      value: function applyConditionalLogic() {
        var _this2 = this;
        $('.exprdawc-input').each(function (index, element) {
          var $field = $(element);
          _this2.checkConditions($field);
        });
      }

      // Check the conditions for the field
    }, {
      key: "checkConditions",
      value: function checkConditions($field) {
        var conditionalLogic = $field.data('conditional-rules');
        if (conditionalLogic) {
          var logic = conditionalLogic;
          var groupConditionMet = false;
          logic.forEach(function (group) {
            var groupMet = true;

            // if group not an array, return
            if (!Array.isArray(group)) {
              console.log('Group is not an array');
              return;
            }
            group.forEach(function (rule) {
              // if rule have empty field, return
              if (!rule.field) {
                return;
              }

              // if rule have empty operator, return
              if (!rule.operator) {
                return;
              }

              // Generate the targetField from esc_html( $field_array['label'] ) to use in the frontend. Whitespaces are replaced by hyphens and the string is lowercased.
              // This is the same as the targetField in the backend.
              var targetField = 'exprdawc-custom-field-input-' + rule.field.replace(/\s+/g, '-').toLowerCase() + '-input';
              var $targetField = $(".".concat(targetField));
              var operator = rule.operator;
              var value = rule.value;
              var targetValue = $targetField.val();
              var conditionMet = false;
              switch (operator) {
                case 'equals':
                  conditionMet = targetValue === value;
                  break;
                case 'not_equals':
                  conditionMet = targetValue !== value;
                  break;
                case 'greater_than':
                  conditionMet = parseFloat(targetValue) > parseFloat(value);
                  break;
                case 'less_than':
                  conditionMet = parseFloat(targetValue) < parseFloat(value);
                  break;
                case 'field_is_empty':
                  conditionMet = targetValue === '';
                  break;
                case 'field_is_not_empty':
                  conditionMet = targetValue !== '';
                  break;
              }
              if (!conditionMet) {
                groupMet = false;
              }
            });
            if (groupMet) {
              groupConditionMet = true;
            }
          });
          if (groupConditionMet) {
            $field.closest('.form-row-wide').show();
          } else {
            $field.closest('.form-row-wide').hide();
          }
        }
      }
    }]);
  }(); // Initialize the class
  new ExprdawcConditionalLogic();
});
/******/ })()
;