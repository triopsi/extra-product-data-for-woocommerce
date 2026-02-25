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

        const skipLink = page.getByRole('link', { name: 'No thanks, skip the tour' });
        if (await skipLink.isVisible().catch(() => false)) {
            await skipLink.click();
        }
        const publishedButton = page.getByRole('button', { name: 'Published', exact: true });

        if (!(await publishedButton.isVisible().catch(() => false))) {
            await page.getByRole('button', { name: 'Publish', exact: true }).click();
            await expect(publishedButton).toBeDisabled();
        }
    }

});
