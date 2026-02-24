const base = require('@playwright/test');

const { expect } = base;

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
 * @param {*} page 
 * @param {*} customer 
 */
async function fillClassicCheckout(page, customer) {
    if (customer.country) {
        const select = page.locator('#billing-country');
        if (await select.count()) {
            await select.selectOption(customer.country);
        }
    }
    await page.fill('#billing-first_name', customer.firstName);
    await page.fill('#billing-last_name', customer.lastName);
    await page.fill('#billing-address_1', customer.address1);
    await page.fill('#billing-postcode', customer.postcode);
    await page.fill('#billing-city', customer.city);
    await page.fill('#email', customer.email);

    if (customer.phone) {
        await page.fill('#billing-phone', customer.phone);
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
 * @param {*} page 
 * @param {*} method 
 */
async function selectPaymentMethod(page, method) {
    const selector = `#payment_method_${method}`;
    if (await page.locator(selector).count()) {
        await page.locator(selector).check({ force: true });
    }
}

/**
 * Place an order on the checkout page. This function clicks the "Place Order" button and waits for the order confirmation page to load. It returns an object containing the order number (if available) and the URL of the thank you page.
 * Note: Depending on the theme and plugins, the selectors might need to be adjusted.
 * @param {*} page 
 * @returns {Promise<{orderNumber: string|null, thankYouUrl: string}>}
 */
async function placeOrder(page) {
    const placeOrderBtn = page.getByRole('button', { name: 'Place Order' });

    await expect(placeOrderBtn).toBeVisible();

    await Promise.all([
        page.waitForLoadState('networkidle'),
        placeOrderBtn.click(),
    ]);

    const thankYou = page.locator('.woocommerce-thankyou-order-received');
    await expect(thankYou).toBeVisible({ timeout: 30000 });

    const orderNumberLocator =
        page.locator('.woocommerce-order-overview__order strong');

    const total =
        page.getByRole('strong').filter({ hasText: '€' }).locator('bdi');

    let orderNumber = null;

    if (await orderNumberLocator.count()) {
        orderNumber = (await orderNumberLocator.first().innerText()).trim();
    }

    return {
        orderNumber,
        total,
        thankYouUrl: page.url(),
    };
}

/**
 * Checkout fixture that provides helper functions to interact with the checkout page. It includes methods to navigate to the checkout, fill in customer details, select a payment method, and place an order. This fixture can be used in E2E tests to streamline checkout-related interactions.
 * Example usage in a test:
 * 
 * test('ORD-01 Values flow to cart, checkout and order meta', async ({ page, checkout }) => {
 *   // Add product to cart and navigate to checkout...
 *   await checkout.fillCustomer({
 *     firstName: 'John',
 *     lastName: 'Doe',
 *     address1: '123 Main St',
 *     postcode: '12345',
 *     city: 'Anytown',
 *     email: 'john.doe@example.com',
 *     phone: '123-456-7890',
 *     country: 'US'
 *   });
 * });
 * 
 */
const test = base.test.extend({
    checkout: async ({ page, baseURL }, use) => {

        const checkoutApi = {

            async goToCheckout() {
                await page.goto(`${baseURL}/checkout/`, {
                    waitUntil: 'domcontentloaded',
                });

                await expect(
                    page.locator('form.checkout, .woocommerce-checkout')
                ).toBeVisible();
            },

            async fillCustomer(customer) {
                await fillClassicCheckout(page, customer);
            },

            async selectPayment(method) {
                await selectPaymentMethod(page, method);
            },

            async placeOrder() {
                return await placeOrder(page);
            },
        };

        await use(checkoutApi);
    },
});

module.exports = { test, expect };