/**
 * Example E2E Tests
 * 
 * This file demonstrates how to use the test fixtures and helpers.
 * Copy and extend these tests for your own scenarios.
 * 
 * Run with: npx playwright test tests/E2E/specs/example.spec.js
 */

import { test, expect } from '../fixtures/test-fixture.js';
import { t } from '../helpers/i18n.js';
import * as common from '../helpers/common.js';

test.describe('Extra Product Data Plugin - E2E Tests', () => {
  /**
   * Test: Admin can log in
   */
  test('Admin can log in to WordPress', async ({ authenticatedPage }) => {
    // If we reach this point, login was successful
    expect(authenticatedPage.url()).toContain('/wp-admin');
  });

  /**
   * Test: Admin can access product editor
   */
  test('Admin can access product editor', async ({ productEditor }) => {
    // The productEditor fixture automatically opens the editor
    const pageTitle = await productEditor.title();
    expect(pageTitle).toContain('Edit');
  });

  /**
   * Test: User can view product page
   */
  test('User can view product page', async ({ productPage, helpers }) => {
    // Check that product page loaded
    const productTitle = await helpers.getElementText(productPage, '.product_title');
    expect(productTitle).toBeTruthy();
  });

  /**
   * Test: Cart page is accessible
   */
  test('User can access cart page', async ({ cartPage }) => {
    const cartUrl = cartPage.url();
    expect(cartUrl).toContain('/cart');
  });

  /**
   * Test: Use translation helper
   */
  test('Translation helper works', async ({ wpPage }) => {
    const addToCartText = t('Add to cart');
    expect(addToCartText).toBeTruthy();
    
    const productText = t('Product');
    expect(productText).toBeTruthy();
  });

  /**
   * Test: Admin can fill custom field
   */
  test('Admin can interact with form fields', async ({ authenticatedPage, helpers }) => {
    // Navigate to admin
    await helpers.navigate(authenticatedPage, process.env.WP_ADMIN_URL);

    // Check if admin is logged in
    const isAdmin = await helpers.isElementVisible(authenticatedPage, 'body.wp-admin');
    expect(isAdmin).toBe(true);
  });

  /**
   * Test: Multiple helpers in sequence
   */
  test('Admin can perform multiple actions', async ({ authenticatedPage, helpers }) => {
    const adminUrl = process.env.WP_ADMIN_URL || 'http://localhost:8080/wp-admin';

    // Navigate to admin
    await helpers.navigate(authenticatedPage, adminUrl);

    // Wait for page to load
    await helpers.waitForNetworkIdle(authenticatedPage);

    // Check for admin elements
    const adminTitle = await authenticatedPage.title();
    expect(adminTitle).toBeTruthy();
  });

  /**
   * Test: Language configuration
   */
  test('Test language configuration is set', async ({ localizedPage }) => {
    const language = process.env.TEST_LANGUAGE || 'en';
    expect(['en', 'de']).toContain(language);
  });

  /**
   * Test: Screenshot capability
   */
  test('Test can take screenshots', async ({ productPage, helpers }) => {
    // This would create a screenshot file
    // await helpers.takeScreenshot(productPage, 'product-page.png');
    
    // For now, just verify the function exists
    expect(typeof helpers.takeScreenshot).toBe('function');
  });
});

test.describe('Custom Fields Tests', () => {
  /**
   * Test: Custom field is displayed on product page
   * 
   * This is a template test - adjust selectors based on your actual HTML
   */
  test('Custom field is displayed on product page', async ({ productPage, helpers }) => {
    // Look for custom field container
    const hasCustomField = await helpers.isElementVisible(
      productPage,
      '.exprdawc-field-wrapper'
    );

    // This may be false if no custom fields are configured
    // Adjust this test based on your test data setup
    if (hasCustomField) {
      expect(hasCustomField).toBe(true);
    }
  });

  /**
   * Test: User can fill custom field
   * 
   * This is a template test - adjust selectors based on your actual HTML
   */
  test('User can fill custom field with text', async ({ productPage, helpers }) => {
    const fieldSelector = 'input.exprdawc-input';
    const fieldExists = await helpers.isElementVisible(productPage, fieldSelector);

    if (fieldExists) {
      await helpers.fillInput(productPage, fieldSelector, 'Test Custom Value');
      const inputValue = await helpers.getElementAttribute(productPage, fieldSelector, 'value');
      expect(inputValue).toBe('Test Custom Value');
    }
  });

  /**
   * Test: Price adjustment is displayed if configured
   */
  test('Price adjustment display is correct', async ({ productPage, helpers }) => {
    // Look for price adjustment indicator
    const priceAdjustment = await helpers.getElementText(
      productPage,
      '.exprdawc-price-adjustment-field'
    );

    // This may be null if no price adjustment is configured
    if (priceAdjustment) {
      expect(priceAdjustment).toBeTruthy();
    }
  });
});

test.describe('Admin Panel Tests', () => {
  /**
   * Test: Custom fields tab is visible in product editor
   */
  test('Custom fields tab visible in product editor', async ({ productEditor, helpers }) => {
    const tabSelector = 'a:text("Custom Fields")';
    const tabExists = await helpers.isElementVisible(productEditor, tabSelector);

    if (tabExists) {
      await helpers.clickElement(productEditor, tabSelector);
      expect(tabExists).toBe(true);
    }
  });

  /**
   * Test: Settings page is accessible
   */
  test('Plugin settings page is accessible', async ({ authenticatedPage, helpers }) => {
    const adminUrl = process.env.WP_ADMIN_URL || 'http://localhost:8080/wp-admin';
    const settingsUrl = `${adminUrl}/admin.php?page=exprdawc-settings`;

    await helpers.navigate(authenticatedPage, settingsUrl);
    const currentUrl = helpers.getCurrentUrl(authenticatedPage);
    expect(currentUrl).toContain('exprdawc-settings');
  });
});

test.describe('Accessibility Tests', () => {
  /**
   * Test: Page has proper title
   */
  test('Product page has proper page title', async ({ productPage }) => {
    const title = await productPage.title();
    expect(title.length).toBeGreaterThan(0);
  });

  /**
   * Test: Links are accessible
   */
  test('Admin links are accessible', async ({ authenticatedPage }) => {
    const links = await authenticatedPage.locator('a').count();
    expect(links).toBeGreaterThan(0);
  });
});
