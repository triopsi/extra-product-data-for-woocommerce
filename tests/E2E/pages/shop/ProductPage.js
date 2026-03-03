const { expect } = require('@playwright/test');

class ProductPage {

    /**
     * Constructor for the ProductPage.
     * Initializes the page and sets up locators for product elements.
     *
     * @param {import('@playwright/test').Page} page - The Playwright page instance
     */
    constructor(page) {
        this.page = page;
    }

    /**
     * Navigates to the shop page.
     *
     * @returns {Promise<void>}
     */
    async goto() {
        await this.page.goto('/shop/');
    }

    async goToProductPage(productName) {
        await this.page.goto(`/product/${productName.toLowerCase().replace(/\s+/g, '-')}/`);
        await this.page.waitForLoadState('domcontentloaded');
        await expect(this.page.getByRole('heading', { name: productName })).toBeVisible();
    }

    /**
     * Adds a product to the cart by its name. Assumes that the product is visible on the shop page.
     *
     * @param {string} productName - The name of the product to add to the cart
     * @returns {Promise<void>}
     */
    async addProductToCart(productName) {
        const productCard = this.page.locator(`.product-card:has-text("${productName}")`);
        await expect(productCard).toBeVisible();
        const addToCartButton = productCard.getByRole('button', { name: 'Add to cart' });
        await expect(addToCartButton).toBeVisible();
        await addToCartButton.click();
    }
}

module.exports = { ProductPage };