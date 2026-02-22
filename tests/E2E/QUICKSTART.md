# E2E Testing - Quick Start Guide

Welcome to the End-to-End Test Suite with Playwright!

## 🚀 5-Minute Quickstart

### 1. Setup
```bash
cd tests/E2E

# Copy and configure .env
cp .env.example .env

# Edit in .env:
BASE_URL=http://localhost:8080
TEST_USER_LOGIN=admin
TEST_USER_PASSWORD=admin
```

### 2. Run Tests
```bash
# Simply start
npm run e2e

# Watch in browser
npm run e2e:headed

# Debug mode (step by step)
npm run e2e:debug

# Interactive UI
npm run e2e:ui

# View reports
npm run e2e:report
```

## 📁 Understanding the Structure

```
tests/E2E/
├── helpers/
│   ├── auth.js       ← Login/Logout functions
│   ├── common.js     ← Clicks, inputs, navigation
│   └── i18n.js       ← Languages/translations
├── fixtures/
│   └── test-fixture.js  ← Predefined test setups
└── specs/
    └── example.spec.js  ← Your tests here
```

## 💡 Writing Your First Test

New file: `tests/E2E/specs/my-test.spec.js`

```javascript
import { test, expect } from '../fixtures/test-fixture.js';
import { t } from '../helpers/i18n.js';

test.describe('My Tests', () => {
  test('Admin can log in', async ({ authenticatedPage }) => {
    expect(authenticatedPage.url()).toContain('/wp-admin');
  });

  test('Display product', async ({ productPage, helpers }) => {
    const title = await helpers.getElementText(productPage, '.product_title');
    expect(title).toBeTruthy();
  });

  test('Fill out form', async ({ authenticatedPage, helpers }) => {
    await helpers.fillInput(authenticatedPage, 'input.title', 'New Title');
    await helpers.clickElement(authenticatedPage, 'button.save');
  });
});
```

## 🎯 Working with Fixtures

Fixtures are pre-configured test environments:

### `authenticatedPage`
Already logged in as Admin:
```javascript
test('Admin Test', async ({ authenticatedPage }) => {
  // Already in /wp-admin
});
```

### `productPage`
Product page already loaded:
```javascript
test('Product Test', async ({ productPage }) => {
  // Already on product page
});
```

### `helpers`
Helper functions for common tasks:
```javascript
test('With Helpers', async ({ page, helpers }) => {
  await helpers.navigate(page, '/shop/');
  await helpers.clickElement(page, '.button');
  const text = await helpers.getElementText(page, '.title');
});
```

## 🌍 Multi-language Testing

In `.env`:
```env
TEST_LANGUAGE=en
```

In your test:
```javascript
import { t } from '../helpers/i18n.js';

test('English Test', async ({ productPage }) => {
  const text = t('Add to cart');
  expect(productPage.locator(`text="${text}"`)).toBeVisible();
});
```

## 🔧 Common Tasks

### Find & click element
```javascript
await helpers.clickElement(page, '.button');
await helpers.clickElement(page, '[data-test="my-button"]');
```

### Fill text input
```javascript
await helpers.fillInput(page, 'input.name', 'My Text');
```

### Read element text
```javascript
const text = await helpers.getElementText(page, '.title');
```

### Navigate
```javascript
await helpers.navigate(page, '/shop/');
await helpers.navigate(page, 'http://example.com/page');
```

### Wait for element
```javascript
await helpers.waitForElement(page, '.loading-complete');
```

### Take screenshot
```javascript
await helpers.takeScreenshot(page, 'my-screenshot.png');
```

## 🐛 Debugging

### Debug mode (step by step)
```bash
npm run e2e:debug
```
Browser opens and you can step through with `Step` button.

### View HTML
```javascript
const html = await page.content();
console.log(html);
```

### Screenshots on failure
Tests automatically capture screenshots on error → see `test-results/`

### Using `--headed` flag
```bash
npm run e2e:headed
```
Browser runs visibly, not headless.

## ✅ Best Practices

1. **Use descriptive test names**
   ```javascript
   // ✅ Good
   test('Admin can add custom field', async () => {
   
   // ❌ Bad
   test('Test 1', async () => {
   ```

2. **Use fixtures**
   ```javascript
   // ✅ Good - Pre-configured
   test('Admin Test', async ({ authenticatedPage }) => {
   
   // ❌ Bad - Manual login every time
   test('Admin Test', async ({ page }) => {
     await loginAsAdmin(...);
   ```

3. **Use waits**
   ```javascript
   // ✅ Good
   await helpers.waitForElement(page, '.loaded');
   
   // ❌ Bad
   await page.waitForTimeout(2000);
   ```

4. **Use accessible selectors**
   ```javascript
   // ✅ Good - stable
   await page.click('button:text("Save")');
   
   // ❌ Bad - fragile
   await page.click('button');
   ```

## 📚 Further Resources

- [Playwright Docs](https://playwright.dev/)
- [Best Practices](https://playwright.dev/docs/best-practices)
- [Debugging Guide](https://playwright.dev/docs/debug)
- [CI/CD Integration](https://playwright.dev/docs/ci)

## 🆘 Issues?

### Tests are failing
1. Check `.env` settings
2. Make sure WordPress is running
3. Use `npm run e2e:debug` to debug

### Selectors not working
```bash
npm run e2e:headed
```
Browser opens, then you can inspect elements.

### Timeout errors
Increase timeout in test:
```javascript
test.setTimeout(60000); // 60 seconds
```

Happy testing! 🎉
