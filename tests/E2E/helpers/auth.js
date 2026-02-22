/**
 * Authentication Helper Functions
 * Provides utilities for logging in and managing user sessions
 */

/**
 * Log in a user to WordPress admin
 * 
 * @param {Page} page - Playwright page object
 * @param {string} username - WordPress username
 * @param {string} password - WordPress password
 * @param {string} adminUrl - WordPress admin URL
 * @returns {Promise<void>}
 * 
 * @example
 * await loginAsAdmin(page, 'admin', 'password', 'http://localhost/wp-admin');
 */
export async function loginAsAdmin(page, username, password, adminUrl) {
  await page.goto(`${adminUrl}/`, { waitUntil: 'networkidle' });

  // Check if already logged in
  const isLoggedIn = await page.locator('a:text("Logout")').isVisible().catch(() => false);
  if (isLoggedIn) {
    return;
  }

  // Fill login form
  await page.fill('input[name="log"]', username);
  await page.fill('input[name="pwd"]', password);
  await page.click('input[type="submit"]');

  // Wait for redirect to dashboard
  await page.waitForURL(`${adminUrl}/`, { waitUntil: 'networkidle' });
}

/**
 * Log out from WordPress
 * 
 * @param {Page} page - Playwright page object
 * @returns {Promise<void>}
 * 
 * @example
 * await logout(page);
 */
export async function logout(page) {
  // Navigate to admin area
  await page.goto(process.env.WP_ADMIN_URL || 'http://localhost:8080/wp-admin');

  // Click user menu
  const userMenu = page.locator('a[aria-label*="Log out"]').first();
  if (await userMenu.isVisible()) {
    await userMenu.click();
    await page.waitForNavigation();
  }
}

/**
 * Get authentication cookies from WordPress session
 * 
 * @param {Page} page - Playwright page object
 * @returns {Promise<Array>}
 */
export async function getAuthCookies(page) {
  return await page.context().cookies();
}

/**
 * Set authentication cookies for a page context
 * 
 * @param {BrowserContext} context - Playwright context
 * @param {Array} cookies - Cookies to set
 * @returns {Promise<void>}
 */
export async function setAuthCookies(context, cookies) {
  await context.addCookies(cookies);
}
