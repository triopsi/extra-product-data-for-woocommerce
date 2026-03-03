const { expect } = require('@playwright/test');

class CartPage {

    /**
     * Constructor for the CartPage.
     * Initializes the page and sets up locators for cart elements.
     *
     * @param {import('@playwright/test').Page} page - The Playwright page instance
     */
    constructor(page) {
        this.page = page;
    }

    /**
     * Navigates to the cart page and verifies that the cart heading is visible. Assumes the cart page is located at "/cart/" and contains a heading with the text "Cart". Depending on the theme and plugins, the URL and selectors might need to be adjusted.
     *
     * @returns {Promise<void>}
     */
    async goToCartPage() {
        await this.page.goto('/cart/');
        await expect(this.page.getByRole('heading', { name: 'Cart', exact: true })).toBeVisible();
    }


    /**
     * Retrieves the cart total element from the cart page. Assumes the total is displayed in a specific format. Depending on the theme and plugins, the selector might need to be adjusted.
     * @returns {import('@playwright/test').Locator} The locator for the cart total element
     */
    getCartTotal() {
        return this.page.locator('div.wc-block-components-totals-item__value span.wc-block-formatted-money-amount');
    }

    /**
     * Clicks the "Proceed to Checkout" button on the cart page to navigate to the checkout page. Assumes the button is visible and enabled.
     *
     * @returns {Promise<void>}
     */
    async proceedToCheckout() {
        const checkoutButton = this.page.getByRole('link', { name: 'Proceed to Checkout' });
        await expect(checkoutButton).toBeVisible();
        await checkoutButton.click();
    }

}

module.exports = { CartPage };