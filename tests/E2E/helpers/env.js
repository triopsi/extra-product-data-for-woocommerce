/**
 * This file defines environment variables for the E2E tests.
 * It loads values from process.env and provides defaults if not set.
 * You can set these variables in a .env file or through your CI environment.
 */
export const env = {
    // WordPress Site URLs
    baseURL: process.env.BASE_URL ?? 'http://localhost:8889',
    wpAdminURL: process.env.WP_ADMIN_URL ?? 'http://localhost:8889/wp-admin',
    
    // Test User Credentials
    adminUser: process.env.TEST_USER_LOGIN ?? 'admin',
    adminPass: process.env.TEST_USER_PASSWORD ?? 'password',
    adminEmail: process.env.TEST_USER_EMAIL ?? 'admin@example.local',
    
    // Test Product IDs
    testProductId: process.env.TEST_PRODUCT_ID ?? '1',
    testVariableProductId: process.env.TEST_VARIABLE_PRODUCT_ID ?? '2',
    
    // Language for Tests
    testLanguage: process.env.TEST_LANGUAGE ?? 'en',
    
    // Playwright Configuration
    headless: process.env.PLAYWRIGHT_HEADLESS === 'true',
    slowMo: parseInt(process.env.PLAYWRIGHT_SLOW_MO ?? '0', 10),
    timeout: parseInt(process.env.PLAYWRIGHT_TIMEOUT ?? '30000', 10),
    
    // Debug Mode
    debug: process.env.DEBUG === 'true',
    
    // Plugin
    pluginSlug: process.env.PLUGIN_SLUG ?? 'extra-product-data-for-woocommerce'
};