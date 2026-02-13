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
    class ExprdawcConditionalLogic {

        // Initialize the class
        constructor() {
            this.init();
        }

        // Initialize the class
        init() {
            this.applyConditionalLogic();

            // Reapply conditional logic on any change of exprdawc-input fields
            $(document).on('change keyup', '.exprdawc-input', () => {
                this.applyConditionalLogic();
            });
        }

        // Apply the conditional logic to the fields
        applyConditionalLogic() {
            $('.exprdawc-input').each((index, element) => {
                const $field = $(element);
                this.checkConditions($field);
            });
        }

        // Check the conditions for the field
        checkConditions($field) {
            const conditionalLogic = $field.data('conditional-rules');
            if (conditionalLogic) {
                const logic = conditionalLogic;
                let groupConditionMet = false;

                logic.forEach((group) => {
                    let groupMet = true;

                    // if group not an array, return
                    if (!Array.isArray(group)) {
                        console.log('Group is not an array');
                        return;
                    }

                    group.forEach((rule) => {

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
                        const targetField = 'exprdawc-custom-field-input-' + rule.field.replace(/\s+/g, '-').toLowerCase() + '-input';
                        const $targetField = $(`.${targetField}`);
                        const operator = rule.operator;
                        const value = rule.value;
                        const targetValue = $targetField.val();                       

                        let conditionMet = false;
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
    }

    // Initialize the class
    new ExprdawcConditionalLogic();
});