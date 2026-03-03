const { expect } = require('@playwright/test');

class AdminLoginPage {
    /**
     * Constructor for the AdminLoginPage.
     * Initializes the page and sets up locators for login elements.
     *
     * @param {import('@playwright/test').Page} page - The Playwright page instance
     */
    constructor(page) {
        this.page = page;
        this.username = page.locator('#user_login');
        this.password = page.locator('#user_pass');
        this.submit = page.locator('#wp-submit');
    }

    /**
     * Navigates to the WordPress login page.
     *
     * @param {string} adminUrl - The URL of the WordPress admin login page
     * @returns {Promise<void>}
     */
    async goto(adminUrl) {
        await this.page.goto(`${adminUrl}`);
    }

    /**
     * Logs in with the provided credentials and verifies the user is redirected to the wp-admin page.
     *
     * @param {string} user - The username to log in with
     * @param {string} pass - The password to log in with
     * @returns {Promise<void>}
     * @throws Will throw an error if the login fails or redirection doesn't occur
     */
    async login(user, pass) {
        await this.username.fill(user);
        await this.password.fill(pass);
        await this.submit.click();
        await expect(this.page).toHaveURL(/wp-admin/);
    }

    /**
     * Checks if a user is currently logged in by verifying the logout link is visible.
     *
     * @returns {Promise<boolean>} True if the user is logged in, false otherwise
     */
    async isLoggedIn() {
        return await this.page.locator('a:text("Logout")').isVisible().catch(() => false);
    }

    /**
     * Logs out the current user by clicking the logout menu item and waiting for the page to load.
     * Only performs logout if the logout menu is visible.
     *
     * @returns {Promise<void>}
     */
    async logout() {
        await this.page.goto(process.env.WP_ADMIN_URL || 'http://localhost:8080/wp-admin', { waitUntil: 'domcontentloaded' });
        // Click user menu
        const userMenu = this.page.locator('a[aria-label*="Log out"]').first();
        if (await userMenu.isVisible()) {
            await userMenu.click();
            await this.page.waitForLoadState('domcontentloaded');
        }
    }
}

module.exports = { AdminLoginPage };