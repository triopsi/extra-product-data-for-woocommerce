const { test, expect } = require('@playwright/test');
const fs = require('fs');
import { ProductAdminPage } from '../pages/shop/ProductAdminPage.js';
import { AdminLoginPage } from '../pages/admin/AdminLoginPage.js';
import { env } from '../helpers/env.js';
import { exit } from 'process';

test.describe('@P10 @IMPORT @EXPORT', () => {
    test('IMPORT-01 Import JSON data', async ({ page }) => {
        const adminUrl = env.wpAdminURL;
        const username = env.adminUser;
        const password = env.adminPass;

        const adminLogin = new AdminLoginPage(page);
        const productAdminPage = new ProductAdminPage(page);

        await adminLogin.goto(adminUrl);
        await adminLogin.login(username, password);
        await productAdminPage.goToProductPage('Hoodie with Zipper');
        await productAdminPage.goToExtraProductDataTab();
        await productAdminPage.clickImportButton();

        const filePath = './data/import_string_test.json';
        let fileContent;
        try {
            fileContent = await fs.promises.readFile(filePath, 'utf-8');
        } catch (error) {
            console.error(`Error reading file at ${filePath}:`, error);
            throw error;
        }
        await page.getByRole('textbox', { name: 'Import JSON' }).fill(fileContent);
        page.once('dialog', async dialog => {
            await dialog.accept();
        });
        await page.getByRole('button', { name: ' Import Fields' }).click();

        // Fields imported successfully! message is shown and page reloads in the class .exprdawc-import-notice
        await expect(page.locator('.exprdawc-import-notice')).toContainText('Fields imported successfully!');

        // wait for the import to complete and the page to update
        await page.waitForLoadState('networkidle');
        await expect(page.getByRole('heading', { name: 'Extra Product Input' })).toBeVisible();
        await expect(page.locator('.exprdawc_no_entry_message')).not.toBeVisible();


        await page.locator('tr:nth-child(8) > td > .exprdawc_fields_table > tbody > .exprdawc_attribute > .cl-arr > .dashicons').click();
        await expect(page.getByRole('row', { name: ' Check B Check B Remove', exact: true }).locator('input[name="extra_product_fields[7][default][]"]')).toBeChecked();

        await expect(page.getByRole('textbox', { name: 'Help Text' })).toHaveValue('Help Text');
        await expect(page.locator('input[name="extra_product_fields[6][label]"]')).toHaveValue('Radio Field');
        await expect(page.getByRole('cell', { name: '  E-Mail Field Email  ' }).getByPlaceholder('Name of the label')).toHaveValue('E-Mail Field');
        await page.locator('tr:nth-child(3) > td > .exprdawc_fields_table > tbody > .exprdawc_attribute > .cl-arr > .dashicons').click();
        await expect(page.getByRole('textbox', { name: 'Default Value' })).toHaveValue('example@company.org');
        await page.locator('tr:nth-child(9) > td > .exprdawc_fields_table > tbody > .exprdawc_attribute > .cl-arr > .dashicons').click();
        await expect(page.locator('input[name="extra_product_fields[8][options][2][label]"]')).toHaveValue('Select C');
    });

    test('EXPORT-01 Export JSON data', async ({ page, context }) => {
        const adminUrl = env.wpAdminURL;
        const username = env.adminUser;
        const password = env.adminPass;

        const adminLogin = new AdminLoginPage(page);
        const productAdminPage = new ProductAdminPage(page);

        await adminLogin.goto(adminUrl);
        await adminLogin.login(username, password);
        await productAdminPage.goToProductPage('Hoodie with Zipper');
        await productAdminPage.goToExtraProductDataTab();
        await context.grantPermissions(['clipboard-read', 'clipboard-write']);

        await page.getByRole('link', { name: 'Export' }).click();
        await page.getByRole('button', { name: ' Copy to Clipboard' }).click();

        // Get the content of clipboard and compare it to the expected JSON string
        const clipboardContent = await page.evaluate(() => navigator.clipboard.readText());
        const expectedFilePath = './data/import_string_test.json';
        let expectedContent;
        try {
            expectedContent = await fs.promises.readFile(expectedFilePath, 'utf-8');
        } catch (error) {
            console.error(`Error reading file at ${expectedFilePath}:`, error);
            throw error;
        }
        expect(clipboardContent).toBe(expectedContent);
    });

});