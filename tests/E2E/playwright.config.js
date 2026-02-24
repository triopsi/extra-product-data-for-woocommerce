import { defineConfig, devices } from '@playwright/test';

/**
 * @see https://playwright.dev/docs/test-configuration
 */
export default defineConfig({
  testDir: './testcases',
  timeout: 30000,
  expect: { timeout: 20000 },
  fullyParallel: false,
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 2 : 0,
  workers: process.env.CI ? 1 : 1,
  reporter: process.env.CI
    ? [['github'], ['html', { outputFolder: 'playwright-report', open: 'never' }], ['list']]
    : [['html', { outputFolder: 'playwright-report', open: 'never' }], ['list']],
  use: {
    baseURL: process.env.BASE_URL || 'http://localhost:8889',
    trace: process.env.CI ? 'retain-on-failure' : 'on-first-retry',
    screenshot: 'only-on-failure',
    video: 'retain-on-failure',
  },
  projects: [
    {
      name: 'setup',
      testMatch: /global\.setup\.js/,
      testDir: './setup',
      retries: 0,
      workers: 1,
    },
    {
      name: 'chromium',
      testMatch: /.*\.spec\.js/,
      use: { ...devices['Desktop Chrome'] },
      dependencies: ['setup'],
    }
  ],
});
