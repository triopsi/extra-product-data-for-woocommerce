/**
 * Common Helper Functions
 * Provides reusable utilities for E2E tests
 */

/**
 * Wait for an element to appear on the page
 * 
 * @param {Page} page - Playwright page object
 * @param {string} selector - CSS selector
 * @param {number} timeout - Timeout in ms (default: 10000)
 * @returns {Promise<void>}
 * 
 * @example
 * await waitForElement(page, '.product-title', 5000);
 */
export async function waitForElement(page, selector, timeout = 10000) {
  await page.waitForSelector(selector, { timeout });
}

/**
 * Check if element is visible
 * 
 * @param {Page} page - Playwright page object
 * @param {string} selector - CSS selector
 * @returns {Promise<boolean>}
 */
export async function isElementVisible(page, selector) {
  return await page.locator(selector).isVisible().catch(() => false);
}

/**
 * Click element and wait for navigation if needed
 * 
 * @param {Page} page - Playwright page object
 * @param {string} selector - CSS selector
 * @param {boolean} waitForNav - Wait for navigation (default: true)
 * @returns {Promise<void>}
 */
export async function clickElement(page, selector, waitForNav = false) {
  if (waitForNav) {
    await Promise.all([
      page.waitForNavigation({ waitUntil: 'networkidle' }),
      page.click(selector),
    ]);
  } else {
    await page.click(selector);
  }
}

/**
 * Fill input field with value
 * 
 * @param {Page} page - Playwright page object
 * @param {string} selector - CSS selector
 * @param {string} value - Value to fill
 * @returns {Promise<void>}
 */
export async function fillInput(page, selector, value) {
  await page.fill(selector, value);
}

/**
 * Get text content from element
 * 
 * @param {Page} page - Playwright page object
 * @param {string} selector - CSS selector
 * @returns {Promise<string>}
 */
export async function getElementText(page, selector) {
  return await page.locator(selector).textContent();
}

/**
 * Get attribute value from element
 * 
 * @param {Page} page - Playwright page object
 * @param {string} selector - CSS selector
 * @param {string} attribute - Attribute name
 * @returns {Promise<string|null>}
 */
export async function getElementAttribute(page, selector, attribute) {
  return await page.locator(selector).getAttribute(attribute);
}

/**
 * Check if element contains specific text
 * 
 * @param {Page} page - Playwright page object
 * @param {string} selector - CSS selector
 * @param {string} text - Text to search for
 * @returns {Promise<boolean>}
 */
export async function elementContainsText(page, selector, text) {
  const content = await getElementText(page, selector);
  return content && content.includes(text);
}

/**
 * Take a screenshot
 * 
 * @param {Page} page - Playwright page object
 * @param {string} filename - Filename without directory
 * @returns {Promise<void>}
 */
export async function takeScreenshot(page, filename) {
  await page.screenshot({ path: `./tests/E2E/screenshots/${filename}` });
}

/**
 * Wait for network to be idle
 * 
 * @param {Page} page - Playwright page object
 * @param {number} timeout - Timeout in ms
 * @returns {Promise<void>}
 */
export async function waitForNetworkIdle(page, timeout = 5000) {
  await page.waitForLoadState('networkidle', { timeout });
}

/**
 * Get current URL
 * 
 * @param {Page} page - Playwright page object
 * @returns {string}
 */
export function getCurrentUrl(page) {
  return page.url();
}

/**
 * Navigate to URL
 * 
 * @param {Page} page - Playwright page object
 * @param {string} url - URL to navigate to
 * @param {Object} options - Navigation options
 * @returns {Promise<Response>}
 */
export async function navigate(page, url, options = {}) {
  return await page.goto(url, { waitUntil: 'networkidle', ...options });
}

/**
 * Reload current page
 * 
 * @param {Page} page - Playwright page object
 * @returns {Promise<Response>}
 */
export async function reloadPage(page) {
  return await page.reload({ waitUntil: 'networkidle' });
}

/**
 * Execute JavaScript in the page context
 * 
 * @param {Page} page - Playwright page object
 * @param {Function} fn - Function to execute
 * @param {*} args - Arguments to pass
 * @returns {Promise<*>}
 * 
 * @example
 * const result = await evaluateScript(page, () => window.innerHeight);
 */
export async function evaluateScript(page, fn, ...args) {
  return await page.evaluate(fn, ...args);
}

/**
 * Press a keyboard key
 * 
 * @param {Page} page - Playwright page object
 * @param {string} key - Key name (e.g., 'Enter', 'Escape', 'Tab')
 * @returns {Promise<void>}
 */
export async function pressKey(page, key) {
  await page.press('body', key);
}

/**
 * Get console messages during test
 * 
 * @param {Page} page - Playwright page object
 * @returns {Array<string>}
 */
export function getConsoleMessages(page) {
  const messages = [];
  page.on('console', (msg) => {
    messages.push(`[${msg.type()}] ${msg.text()}`);
  });
  return messages;
}

/**
 * Assert that element with text exists
 * 
 * @param {Page} page - Playwright page object
 * @param {string} text - Text to find
 * @returns {Promise<void>}
 */
export async function assertElementWithTextExists(page, text) {
  await page.locator(`text="${text}"`).first().waitFor({ state: 'visible' });
}
