const { expect } = require('@playwright/test');

class CheckoutPage {

    /**
     * Constructor for the CheckoutPage.
     * Initializes the page and sets up locators for checkout elements.
     *
     * @param {import('@playwright/test').Page} page - The Playwright page instance
     */
    constructor(page) {
        this.page = page;
    }

    /**
     * Fill the checkout form with the provided customer data. This function assumes a classic WooCommerce checkout form. Depending on the theme and plugins, the selectors might need to be adjusted.
     * Customer object structure:
     *    {
     *      firstName: 'John',
     *      lastName: 'Doe',
     *      address1: '123 Main St',
     *      postcode: '12345',
     *      city: 'Anytown',
     *      email: 'john.doe@example.com',
     *      phone: '123-456-7890',
     *      country: 'US'
     *    }
     * 
     * @param {*} customer 
     */
    async fillClassicCheckout(customer) {
        if (customer.country) {
            const select = this.page.locator('#billing-country');
            if (await select.count()) {
                await select.selectOption(customer.country);
            }
        }
        await this.page.fill('#billing-first_name', customer.firstName);
        await this.page.fill('#billing-last_name', customer.lastName);
        await this.page.fill('#billing-address_1', customer.address1);
        await this.page.fill('#billing-postcode', customer.postcode);
        await this.page.fill('#billing-city', customer.city);
        await this.page.fill('#email', customer.email);

        if (customer.phone) {
            await this.page.fill('#billing-phone', customer.phone);
        }
    }

    /**
     * Select a payment method on the checkout page. The method parameter should match the value attribute of the corresponding radio input (e.g., 'bacs', 'cheque', 'cod', 'paypal').
     * Note: Depending on the theme and plugins, the selectors might need to be adjusted.
     * Example method values:
     *    'bacs' for Direct Bank Transfer
     *    'cheque' for Check Payments
     *    'cod' for Cash on Delivery
     *    'paypal' for PayPal
     *
     * @param {*} method 
     */
    async selectPaymentMethod(method) {
        const selector = `#radio-control-wc-payment-method-options-${method}`;
        if (await this.page.locator(selector).count()) {
            await this.page.locator(selector).check({ force: true });
        }
    }

    /**
     * Get the total amount displayed on the checkout page. Assumes the total is displayed in a specific format. Depending on the theme and plugins, the selector might need to be adjusted.
     * @returns {import('@playwright/test').Locator} The locator for the total amount element
     */
    getTotal() {
        return this.page.locator('div.wc-block-components-totals-item__value span.wc-block-formatted-money-amount');
    }

    /**
     * Clicks the "Place Order" button on the checkout page to submit the order. Assumes the button is visible and enabled.
     *
     * @returns {Promise<void>}
     */
    async placeOrder() {
        const placeOrderBtn = this.page.getByRole('button', { name: 'Place Order' });
        await expect(placeOrderBtn).toBeVisible();
        await placeOrderBtn.click();
    }

}

module.exports = { CheckoutPage };