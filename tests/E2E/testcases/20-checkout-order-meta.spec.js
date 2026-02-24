const { test, expect } = require('../fixtures/checkout');
import { loginAsAdmin, logout } from '../helpers/auth.js';
import { env } from '../helpers/env.js';


test.describe('@P0 @CHECKOUT', () => {
    test('ORD-01 Values flow to cart, checkout and order meta (placeholder)', async ({ page, checkout }) => {

        // Order product and go to checkout
        await page.goto('/product/sunglasses/');
        await page.waitForLoadState('domcontentloaded');
        await expect(page.getByRole('heading', { name: 'Sunglasses' })).toBeVisible();
        await page.getByRole('textbox', { name: 'Branding  *' }).click();
        await page.getByRole('textbox', { name: 'Branding  *' }).fill('Triopsi');
        await page.getByRole('button', { name: 'Add to cart', exact: true }).click();
        await expect(page.getByRole('alert')).toContainText('“Sunglasses” has been added to your cart. View cart');
        await page.locator('#content').getByRole('link', { name: 'View cart ' }).click();
        await expect(page.locator('tbody')).toContainText('Branding: Triopsi');
        await page.getByRole('cell', { name: '€90.00', exact: true }).click();

        // Checkout
        await checkout.goToCheckout();

        // Set customer details
        await checkout.fillCustomer({
            firstName: 'Max',
            lastName: 'Tester',
            email: 'daniel@example.com',
            phone: '015112345678',
            address1: 'Bahnhofsplatz 2A',
            postcode: '65189',
            city: 'Wiesbaden',
            country: 'DE',
        });

        // Select payment method
        // await checkout.selectPayment('bacs');

        // Place order
        const result = await checkout.placeOrder();

        // Verify order confirmation
        await expect(
            page.locator('.woocommerce-thankyou-order-received')
        ).toContainText('Thank you. Your order has been received.');

        expect(result.orderNumber).not.toBeNull();

        await expect(result.total).toContainText('€90.00');

        await expect(page.getByText('Branding: Triopsi')).toBeVisible();
    });
});