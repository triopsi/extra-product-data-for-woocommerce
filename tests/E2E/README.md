# E2E Tests with Playwright

End-to-End tests for the Extra Product Data for WooCommerce plugin using Playwright.

## Setup

### 1. Install Dependencies

Playwright has been added as a dev dependency. Install it with:

```bash
npm install
```

### 2. Configure Environment

Copy the example environment file:

```bash
cp tests/E2E/.env.example tests/E2E/.env
```

Edit `.env` with your local WordPress settings:

```env
BASE_URL=http://localhost:8080
WP_ADMIN_URL=http://localhost:8080/wp-admin
TEST_USER_LOGIN=admin
TEST_USER_PASSWORD=admin
TEST_LANGUAGE=en
```

### 3. Run Tests

Run all tests:

```bash
npx playwright test tests/E2E/specs/
```

Run a specific test file:

```bash
npx playwright test tests/E2E/specs/example.spec.js
```

Run tests in headed mode (see the browser):

```bash
npx playwright test tests/E2E/specs/ --headed
```

Run tests in debug mode:

```bash
npx playwright test tests/E2E/specs/ --debug
```

## Project Structure

```
tests/E2E/
├── .env                      # Environment configuration (local, not versioned)
├── .env.example              # Example environment file
├── fixtures/
│   └── test-fixture.js       # Reusable test fixtures
├── helpers/
│   ├── auth.js              # Authentication helpers (login, logout, cookies)
│   ├── common.js            # Common utilities (click, fill, navigate, etc.)
│   └── i18n.js              # Internationalization/translation helpers
├── specs/
│   └── example.spec.js      # Example tests
└── README.md                # This file
```

## Using Fixtures

Fixtures are pre-configured test contexts. Use them in your tests:

### authenticatedPage
Logs in as admin before test and logs out after:

```javascript
import { test } from '../fixtures/test-fixture.js';

test('Admin sees dashboard', async ({ authenticatedPage }) => {
  await expect(authenticatedPage).toHaveTitle(/Dashboard/);
});
```

### wpPage
Standard WordPress page with BASE_URL configured:

```javascript
test('Visit product page', async ({ wpPage }) => {
  await wpPage.goto('/shop/');
});
```

### productPage
Pre-loaded with test product:

```javascript
test('View product', async ({ productPage }) => {
  const title = await productPage.locator('.product_title').textContent();
  expect(title).toBeTruthy();
});
```

### productEditor
Logged in admin page with product editor open:

```javascript
test('Edit product', async ({ productEditor }) => {
  await productEditor.fill('.product-title', 'New Title');
});
```

### cartPage
Pre-loaded WooCommerce cart page:

```javascript
test('View cart', async ({ cartPage }) => {
  const items = await cartPage.locator('.cart-item').count();
  expect(items).toBeGreaterThanOrEqual(0);
});
```

### localizedPage
Page with language configuration:

```javascript
test('Language is set', async ({ localizedPage }) => {
  const lang = await localizedPage.evaluate(() => window.testLanguage);
  expect(lang).toBeTruthy();
});
```

### helpers
Common utility functions available as fixtures:

```javascript
test('Use helpers', async ({ page, helpers }) => {
  await helpers.clickElement(page, '.button');
  const text = await helpers.getElementText(page, '.title');
  expect(text).toBeTruthy();
});
```

## Common Helpers

### Authentication

```javascript
import { loginAsAdmin, logout } from '../helpers/auth.js';

await loginAsAdmin(page, username, password, adminUrl);
await logout(page);
```

### Navigation & Elements

```javascript
import * as common from '../helpers/common.js';

await common.navigate(page, '/shop/');
await common.clickElement(page, '.button');
await common.fillInput(page, 'input', 'value');
const text = await common.getElementText(page, '.title');
const attr = await common.getElementAttribute(page, 'img', 'src');
```

### Internationalization

```javascript
import { t, addTranslation, getCurrentLanguage } from '../helpers/i18n.js';

const buttonText = t('Add to cart'); // Respects TEST_LANGUAGE env var
addTranslation('en', 'Custom', 'Custom Value');
const lang = getCurrentLanguage(); // 'en', 'de', etc.
```

## Writing Tests

### Basic Test Structure

```javascript
import { test, expect } from '../fixtures/test-fixture.js';

test.describe('Feature Group', () => {
  test('Test name', async ({ authenticatedPage }) => {
    // Test code here
    expect(true).toBe(true);
  });
});
```

### Using Multiple Fixtures

```javascript
test('Use all available fixtures', async ({
  authenticatedPage,
  wpPage,
  productPage,
  helpers
}) => {
  // authenticatedPage: logged in admin page
  // wpPage: regular page with BASE_URL set
  // productPage: product page pre-loaded
  // helpers: common utility functions
});
```

### Translation in Tests

```javascript
import { t } from '../helpers/i18n.js';

test('English text', async ({ productPage }) => {
  const buttonText = t('Add to cart'); // "Add to cart" for EN, "In den Warenkorb" for DE
  await expect(productPage.locator(`text="${buttonText}"`)).toBeVisible();
});
```

### Screenshots & Debugging

```javascript
test('Debug test', async ({ productPage, helpers }) => {
  await helpers.takeScreenshot(productPage, 'page.png');
  
  // Use --debug flag for step-by-step debugging
  // npx playwright test --debug
});
```

## Configuration

Edit `playwright.config.js` to customize:

- **testDir**: Directory containing tests
- **projects**: Browser configurations (Chromium, Firefox, WebKit)
- **use**: Playwright settings (screenshots, videos, traces)
- **webServer**: Local server command

## Multi-Language Testing

Set language in `.env`:

```env
TEST_LANGUAGE=de
```

Then use translation helpers in tests:

```javascript
import { t } from '../helpers/i18n.js';

const text = t('Add to cart'); // "In den Warenkorb" if TEST_LANGUAGE=de
```

Add translations in `helpers/i18n.js`:

```javascript
export const translations = {
  en: { 'My Text': 'English' },
  de: { 'My Text': 'Deutsch' },
  es: { 'My Text': 'Español' },
};
```

## Reports & Artifacts

After running tests, view the HTML report:

```bash
npx playwright show-report
```

Artifacts are stored in:
- `tests/E2E/reports/` - HTML test report
- `test-results/` - Videos and screenshots (on failure)

## CI/CD Integration

In GitHub Actions or CI environment:

```bash
npm run e2e
```

Or configure in your CI provider to run:

```bash
npx playwright test tests/E2E/specs/
```

## Tips & Tricks

### Only run specific tests

```bash
# By name
npx playwright test -g "should display"

# By file
npx playwright test example.spec.js

# By tag
npx playwright test @smoke
```

### See all available tests

```bash
npx playwright test --list
```

### Update snapshots

```bash
npx playwright test --update-snapshots
```

### Run in specific browser

```bash
npx playwright test --project=firefox
```

## Troubleshooting

### Tests timeout
Increase timeout in tests:
```javascript
test.setTimeout(60000); // 60 seconds
```

### Can't log in
- Check `TEST_USER_LOGIN` and `TEST_USER_PASSWORD` in `.env`
- Verify WordPress is running
- Check that user exists in database

### Selectors not found
Use `--headed` mode to see what's on the page:
```bash
npx playwright test --headed --debug
```

### Clear browser cache
```bash
rm -rf ~/.cache/ms-playwright/
```

## Resources

- [Playwright Documentation](https://playwright.dev/)
- [Playwright Test Configuration](https://playwright.dev/docs/test-configuration)
- [Playwright Best Practices](https://playwright.dev/docs/best-practices)
- [CSS Selectors Guide](https://developer.mozilla.org/en-US/docs/Web/CSS/CSS_Selectors)

## License

See main LICENSE file.
