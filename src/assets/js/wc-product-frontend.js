jQuery(document).ready(function ($) {

    class ExprdawcProductFrontend {

        // Initialize the class
        constructor() {
            this.init();
        }

        // Initialize the class
        init() {
            $(document).on('keyup change', '.exprdawc-input', this.updatePriceAdjustmentTable.bind(this));
            $(".woocommerce").on("keyup change", "input.qty", this.updatePriceAdjustmentTable.bind(this));
            $('input[name="variation_id"]').on("change", this.updatePriceAdjustmentTable.bind(this));

            // Inits.
            this.updatePriceAdjustmentTable();
        }

        /**
         * Format the price with a currency symbol.
         *
         * @param float price The product price.
         * @param mixed exprdawc_frontend_settings WooCommerce price options defined. 
         * 
         * @return float The formatted product price.
         */
        exprdawc_product_price(price) {
            price = parseFloat(price).toFixed(2); // Ensure the price always has two decimal places
            var default_args = {
                decimal_sep: exprdawc_frontend_settings.decimal_separator,
                currency_position: exprdawc_frontend_settings.currency_position,
                currency_symbol: exprdawc_frontend_settings.currency_symbol,
                trim_zeros: exprdawc_frontend_settings.currency_format_trim_zeros,
                num_decimals: exprdawc_frontend_settings.currency_format_num_decimals,
                html: true
            };
            if (default_args.num_decimals > 0) {
                var wc_price_length = parseInt(price).toString().length;
                var wc_int_end_sep = wc_price_length + default_args.num_decimals;
                price = price.toString().substr(0, wc_int_end_sep + 1);
            } else {
                price = parseInt(price);
            }
            price = price.toString().replace('.', default_args.decimal_sep);
            var formatted_price = price;
            console.log(price);
            var formatted_symbol = default_args.html ? '<span class="woocommerce-Price-currencySymbol">' + default_args.currency_symbol + '</span>' : default_args.currency_symbol;
            console.log(formatted_symbol);
            if ('left' === default_args.currency_position) {
                formatted_price = formatted_symbol + formatted_price;
            } else if ('right' === default_args.currency_position) {
                formatted_price = formatted_price + formatted_symbol;
            } else if ('left_space' === default_args.currency_position) {
                formatted_price = formatted_symbol + ' ' + formatted_price;
            } else if ('right_space' === default_args.currency_position) {
                formatted_price = formatted_price + ' ' + formatted_symbol;
            }
            console.log(formatted_price);
            formatted_price = default_args.html ? '<span class="woocommerce-Price-amount amount">' + formatted_price + '</span>' : formatted_price;
            return formatted_price;
        }

        sanitizeValue(value) {
            return value.replace(/[^\p{L}\p{N}]/gu, ' ').replace(/\s+/g, ' ').trim();
        }

        /**
         * Check the conditions for the field
         * 
         * @param $field The field to check the conditions for.
         * @return void
         */
        getFieldType($field) {
            if ($field.is("input")) {
                return $field.attr("type");
            }
            if ($field.is("select")) {
                return "select";
            }
            if ($field.is("textarea")) {
                return "textarea";
            }
            if ($field.is("option")) {
                return "option";
            }
            return "";
        }

        /**
         * Get the current product price based on the product type and selected variation.
         *
         * @return float The current product price.
         */
        getCurrentProductPrice() {
            const productType = $('.exprdawc-price-adjustment').data("product-type");
            const variationId = $('input[name="variation_id"]').val();
            const variations = $("[data-product_variations]").data("product_variations");
            if (productType === "variable" && variations) {
                const selectedVariation = variations.find(variation => variation.variation_id == variationId);
                return selectedVariation ? selectedVariation.display_price : parseFloat($('.exprdawc-price-adjustment').data('product-base-price'));
            } else {
                return parseFloat($('.exprdawc-price-adjustment').data('product-base-price'));
            }
        }

        /**
        * Update the price adjustment table.
        */
        updatePriceAdjustmentTable() {
            let subtotal = 0;
            const $table = $('.exprdawc-price-adjustment');

            // if exit a .exprdawc-price-adjustment-field field, then create table in .exprdawc-price-adjustment
            if ($('.exprdawc-price-adjustment-field').length) {
                $table.empty();
                $table.append(`
                    <table class="exprdawc_price_adjustment_table">
                        <thead>
                            <tr>
                                <th>${exprdawc_frontend_settings.option}</th>
                                <th>${exprdawc_frontend_settings.price}</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                `);


                const qty = parseInt($('.woocommerce input.qty').val()) || 1;
                const basePrice = qty * parseFloat(this.getCurrentProductPrice()) || 0;

                const productName = $('.exprdawc-price-adjustment').data('product-name') || '';
                const $tableBody = $table.find('tbody');
                $tableBody.empty();
                // Get the Product base price and Product name as td
                $tableBody.append(`
                    <tr>
                        <td>${qty} x ${productName}</td>
                        <td>${this.exprdawc_product_price(basePrice)}</td>
                    </tr>
                `);

                const self = this;
                $('.exprdawc-input').each(function () {
                    //only then input have a value
                    if ($(this).val() == '') {
                        return;
                    }

                    // if field disabled, then return
                    if ($(this).prop('disabled')) {
                        return;
                    }

                    const fieldType = self.getFieldType($(this));
                    const isCheckboxOrRadio = ["checkbox", "radio"].includes(fieldType);
                    const isOption = fieldType === "option";
                    const isFieldChecked = $(this).is(":checked");
                    const isFieldSelected = $(this).is(":selected");
                    const isFieldDisabled = $(this).prop("disabled");
                    const isSelectDisabled = $(this).closest("select").prop("disabled");

                    // By checkbox, radio, option, select return if not checked or selected
                    if ((isCheckboxOrRadio && !isFieldChecked) || (isOption && (!isFieldSelected || isSelectDisabled))) {
                        return;
                    }
                    const fieldName = $(this).data('label') || '';
                    const value = $(this).val();

                    // By Select give me the option selected as object
                    if (fieldType === "select") {
                        const $selectedOption = $(this).find("option:selected");
                    }

                    // Have this feidl the data price adjustment. If not than append to the table without price adjustment
                    if (fieldType === "select") {
                        const $selectedOption = $(this).find("option:selected");
                        if (!$selectedOption.data('price-adjustment')) {
                            $tableBody.append(`
                                <tr>
                                    <td>${fieldName}<p><small>${self.sanitizeValue($selectedOption.text())}</small></p></td>
                                    <td>${self.exprdawc_product_price(0)}</td>
                                </tr>
                            `);
                            return;
                        }
                    } else if (!$(this).data('price-adjustment')) {
                        $tableBody.append(`
                            <tr>
                                <td>${fieldName}<p><small>${self.sanitizeValue(value)}</small></p></td>
                                <td>${self.exprdawc_product_price(0)}</td>
                            </tr>
                        `);
                        return;
                    }


                    let fieldPrice = 0;
                    let adjustmentType = 'fixed';
                    if (fieldType === "select") {
                        const $selectedOption = $(this).find("option:selected");
                        if ($selectedOption.data('price-adjustment')) {
                            fieldPrice = parseFloat($selectedOption.data('price-adjustment')) || 0;
                            adjustmentType = $selectedOption.data('price-adjustment-type') || 'fixed';
                        }
                    } else {
                        fieldPrice = parseFloat($(this).data('price-adjustment')) || 0;
                        adjustmentType = $(this).data('price-adjustment-type') || 'fixed';
                    }

                    // Calculate percentage-based price adjustments
                    if (adjustmentType === 'percentage' && fieldPrice !== 0) {
                        fieldPrice = (basePrice * fieldPrice) / 100;
                    }

                    if ($("[data-qty-based]").length) {
                        fieldPrice = fieldPrice * qty;
                    }

                    // get the right plus/minus symbol for the price
                    let plus_minus_symbol = '';
                    if (fieldPrice > 0) {
                        plus_minus_symbol = '+';
                    } else if (fieldPrice < 0) {
                        plus_minus_symbol = '-';
                    }

                    subtotal += fieldPrice;
                    $tableBody.append(`
                        <tr>
                            <td>${fieldName}<p><small>${self.sanitizeValue(value)}</small></p></td>
                            <td>${plus_minus_symbol}${self.exprdawc_product_price(fieldPrice)}</td>
                        </tr>
                    `);
                });

                const total = basePrice + subtotal; // Additional calculations for total can be added here

                $tableBody.append(`
                    <tr>
                        <td><strong>${exprdawc_frontend_settings.total}</strong></td>
                        <td><strong>${this.exprdawc_product_price(total)}</strong></td>
                    </tr>
                `);
            }
        }
    }

    // Initialize the class
    new ExprdawcProductFrontend();
});
