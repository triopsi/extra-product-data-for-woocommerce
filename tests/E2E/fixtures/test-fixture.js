/**
 * Fixtures for Common Test Setups
 * Provides reusable test fixtures for various scenarios
 */

import { test as base, expect } from '@playwright/test';
import { loginAsAdmin, logout } from '../helpers/auth.js';
import { getCurrentLanguage } from '../helpers/i18n.js';
import * as common from '../helpers/common.js';

/**
 * Combined test fixture with all fixtures available
 */
export const test = base.extend({
  /**
   * Fixture: Authenticated page (logged in as admin)
   */
  authenticatedPage: async ({ page }, use) => {
    const adminUrl = process.env.WP_ADMIN_URL || 'http://localhost:8080/wp-admin';
    const username = process.env.TEST_USER_LOGIN || 'admin';
    const password = process.env.TEST_USER_PASSWORD || 'admin';

    await loginAsAdmin(page, username, password, adminUrl);
    await use(page);
    await logout(page);
  },

  /**
   * Fixture: Page with WordPress environment setup
   */
  wpPage: async ({ page }, use) => {
    const baseUrl = process.env.BASE_URL || 'http://localhost:8080';
    page.goto = async (url, options) => {
      const fullUrl = url.startsWith('http') ? url : `${baseUrl}${url}`;
      return page.goto(fullUrl, options);
    };
    await use(page);
  },

  /**
   * Fixture: Product page with test product
   */
  productPage: async ({ page }, use) => {
    const baseUrl = process.env.BASE_URL || 'http://localhost:8080';
    const productId = process.env.TEST_PRODUCT_ID || 1;
    const productUrl = `${baseUrl}/?p=${productId}`;

    await page.goto(productUrl, { waitUntil: 'networkidle' });
    await use(page);
  },

  /**
   * Fixture: Admin backend with product editor open
   */
  productEditor: async ({ page }, use) => {
    const adminUrl = process.env.WP_ADMIN_URL || 'http://localhost:8080/wp-admin';
    const username = process.env.TEST_USER_LOGIN || 'admin';
    const password = process.env.TEST_USER_PASSWORD || 'admin';
    const productId = process.env.TEST_PRODUCT_ID || 1;

    // Login
    await loginAsAdmin(page, username, password, adminUrl);

    // Navigate to editor
    const editorUrl = `${adminUrl}/post.php?post=${productId}&action=edit`;
    await page.goto(editorUrl, { waitUntil: 'networkidle' });

    await use(page);
    await logout(page);
  },

  /**
   * Fixture: WooCommerce cart setup
   */
  cartPage: async ({ page }, use) => {
    const baseUrl = process.env.BASE_URL || 'http://localhost:8080';
    const cartUrl = `${baseUrl}/cart/`;

    await page.goto(cartUrl, { waitUntil: 'networkidle' });
    await use(page);
  },

  /**
   * Fixture: Pre-populated with language settings
   */
  localizedPage: async ({ page }, use) => {
    const language = getCurrentLanguage();
    const baseUrl = process.env.BASE_URL || 'http://localhost:8080';

    // Navigate to a page
    await page.goto(baseUrl, { waitUntil: 'networkidle' });

    // Store language in page context for use in tests
    await page.evaluate((lang) => {
      window.testLanguage = lang;
    }, language);
    await use(page);
  },

  /**
   * Fixture: Helper utilities injected into context
   */
  helpers: async ({}, use) => {
    await use(common);
  },
});

export { expect };
