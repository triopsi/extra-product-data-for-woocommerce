const { test, expect } = require('@playwright/test');
import { env } from '../../helpers/env.js';
import { AdminLoginPage } from '../../pages/admin/AdminLoginPage.js';
import { ProductPage } from '../../pages/shop/ProductPage.js';

/**
 * @fileoverview Smoke tests for the Extra Product Data for WooCommerce plugin.
 * These tests cover basic functionality and critical paths to ensure that the plugin is working as expected.
 * They are designed to be run quickly and provide confidence in the stability of the plugin.
 */
test.describe('@P0 @SMOKE', () => {

  /**
   * Test case: SMK-01 Admin login works
   * Description: Verify that an admin user can log in successfully and access the admin dashboard.
   * Steps:
   * 1. Navigate to the admin login page.
   * 2. Enter valid admin credentials.
   * 3. Submit the login form.
   * 4. Verify that the URL changes to the admin dashboard.
   * 5. Verify that the admin toolbar is visible.
   * 6. Log out of the admin account.
   */
  test('SMK-01 Admin login works', async ({ page }) => {
    const adminUrl = env.wpAdminURL;
    const username = env.adminUser;
    const password = env.adminPass;

    const adminLoginPage = new AdminLoginPage(page);
    await adminLoginPage.goto(adminUrl);
    await adminLoginPage.login(username, password);

    await expect(page).toHaveURL(/.*\/wp-admin\/?$/);
    await expect(page.locator('#wpadminbar')).toBeVisible();

    await adminLoginPage.goto(adminUrl + '/plugins.php');
    await expect(page.locator('#the-list')).toContainText('Extra Product Data for WooCommerce');
    await expect(page.getByLabel('Deactivate Extra Product Data')).toContainText('Deactivate');
    await expect(page.locator('#the-list')).toContainText('Settings');
  });

  /**
   * Test case: SMK-03 Product page loads
   * Description: Verify that the product page for "Sunglasses" loads successfully and displays the expected elements.
   * Steps:
   * 1. Navigate to the product page URL for "Sunglasses".
   * 2. Wait for the page to load completely.
   * 3. Verify that the product title "Sunglasses" is visible.
   * 4. Verify that the "Add to cart" button is visible.
   * 5. Verify that the custom field "Branding" label and description are visible.
   * 6. Verify that the "Branding" input field is empty.
   */
  test('SMK-03 Product page loads', async ({ page }) => {
    const productPage = new ProductPage(page);
    await productPage.goToProductPage('Sunglasses');
    await expect(page.locator('[id="-wrapper-field"]')).toContainText('Branding *');
    await expect(page.locator('#exprdawc-custom-field-input-branding-description')).toContainText('Branding');
    await expect(page.getByRole('textbox', { name: 'Branding  *' })).toBeEmpty();
    await expect(page.getByRole('button', { name: 'Add to cart', exact: true })).toBeVisible();
  });
});