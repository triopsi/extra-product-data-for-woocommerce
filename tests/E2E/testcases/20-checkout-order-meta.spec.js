const { test, expect } = require('@playwright/test');
import { ProductPage } from '../pages/shop/ProductPage.js';
import { CartPage } from '../pages/shop/CartPage.js';
import { CheckoutPage } from '../pages/shop/CheckoutPage.js';


test.describe('@P0 @CHECKOUT', () => {
    test('ORD-01 Values flow to cart, checkout and order meta (placeholder)', async ({ page }) => {

        const productPage = new ProductPage(page);
        const cartPage = new CartPage(page);
        const checkoutPage = new CheckoutPage(page);

        await productPage.goToProductPage('Sunglasses');

        await expect(page.getByRole('heading', { name: 'Sunglasses' })).toBeVisible();
        await page.getByRole('textbox', { name: 'Branding  *' }).click();
        await page.getByRole('textbox', { name: 'Branding  *' }).fill('Triopsi');
        await page.getByRole('button', { name: 'Add to cart', exact: true }).click();

        // Check Banner
        await expect(page.getByRole('alert')).toContainText('“Sunglasses” has been added to your cart. View cart');

        // Go to Cart
        await cartPage.goToCartPage();
        await expect(page.locator('tbody')).toContainText('Branding: Triopsi');
        await expect(cartPage.getCartTotal()).toContainText('€90.00');

        // Checkout
        await cartPage.proceedToCheckout();

        // Set customer details
        await checkoutPage.fillClassicCheckout({
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
        await checkoutPage.selectPaymentMethod('cod');

        // Verify total on checkout page
        await expect(checkoutPage.getTotal()).toContainText('€90.00');

        // Place order
        await checkoutPage.placeOrder();

        // Verify order confirmation
        await expect(page.getByText('Thank you. Your order has')).toBeVisible();
        await expect(page.getByText('Total: €90.00', { exact: true })).toBeVisible();
        await expect(page.getByText('Branding: Triopsi')).toBeVisible();
    });
});