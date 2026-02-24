import { test, expect } from '@playwright/test';
import { loginAsAdmin, logout } from '../helpers/auth.js';
import { env } from '../helpers/env.js';

test('setup storefront starter pack', async ({ page }) => {
    const adminUrl = env.wpAdminURL;
    const username = env.adminUser;
    const password = env.adminPass;

    await loginAsAdmin(page, username, password, adminUrl);
    await page.goto('/wp-admin/themes.php?page=storefront-welcome');
    
    const alreadyInstalled = await page
        .locator('#wpbody-content')
        .getByText('Hello! You might be interested in the following Storefront extensions and designs.')
        .isVisible()
        .catch(() => false);

    if (!alreadyInstalled) {
        await page.getByRole('button', { name: "Let's go!" }).click();
        await page.getByRole('button', { name: 'Publish', exact: true }).click();

        await expect(
            page.getByRole('button', { name: 'Publish', exact: true })
        ).toBeDisabled();
    }

    await logout(page);
});
