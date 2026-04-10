const { test, expect } = require('@playwright/test');
const fs = require('fs');
import { ProductAdminPage } from '../../../pages/shop/ProductAdminPage.js';
import { AdminLoginPage } from '../../../pages/admin/AdminLoginPage.js';
import { env } from '../../../helpers/env.js';
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


        await page.locator('tr:nth-child(7) > td > .exprdawc_fields_table > tbody > .exprdawc_attribute > .cl-arr > .dashicons').click();
        await page.locator('tr:nth-child(6) > td > .exprdawc_fields_table > tbody > .exprdawc_attribute > .cl-arr > .dashicons').click();
        await page.locator('tr:nth-child(5) > td > .exprdawc_fields_table > tbody > .exprdawc_attribute > .cl-arr > .dashicons').click();
        await page.locator('tr:nth-child(4) > td > .exprdawc_fields_table > tbody > .exprdawc_attribute > .cl-arr > .dashicons').click();
        await page.locator('tr:nth-child(3) > td > .exprdawc_fields_table > tbody > .exprdawc_attribute > .cl-arr > .dashicons').click();
        await page.locator('tr:nth-child(2) > td > .exprdawc_fields_table > tbody > .exprdawc_attribute > .cl-arr > .dashicons').click();
        await page.locator('.dashicons.dashicons-arrow-up').click();
        await expect(page.getByRole('row', { name: '  Short Text   Require' }).getByPlaceholder('Name of the label')).toHaveValue('Short Text');
        await expect(page.getByRole('row', { name: '  Short Text   Require' }).getByLabel('User can edit the field')).toBeChecked();
        await expect(page.getByRole('row', { name: '  Short Text   Require' }).getByLabel('Require input')).toBeChecked();
        await expect(page.getByRole('row', { name: '  Short Text   Require' }).getByPlaceholder('Placeholder Text')).toHaveValue('Placeholder Text');
        await expect(page.getByRole('row', { name: '  Short Text   Require' }).getByPlaceholder('Help Text')).toHaveValue('Help Text Short Text');
        await expect(page.getByRole('cell', { name: 'Max length 20', exact: true }).getByLabel('Max length')).toHaveValue('20');
        await expect(page.getByRole('cell', { name: 'Min length 5', exact: true }).getByLabel('Min length')).toHaveValue('5');
        await expect(page.getByRole('cell', { name: 'Default Value Default Text', exact: true }).getByPlaceholder('Enter a default text')).toHaveValue('Default Text');
        await expect(page.getByRole('row', { name: '  Short Text   Require' }).getByLabel('Autocomplete Function')).toHaveValue('on');
        await expect(page.getByRole('cell', { name: '  Long Text   Require' }).getByPlaceholder('Placeholder Text')).toHaveValue('PlaceHolder');
        await expect(page.getByText('Hier steht ein Default Text')).toHaveValue('Hier steht ein\nDefault Text');
        await expect(page.getByRole('cell', { name: '  Long Text   Require' }).getByPlaceholder('Help Text')).toHaveValue('HelpText');
        await expect(page.getByRole('cell', { name: 'Max length 1000', exact: true }).getByLabel('Max length')).toHaveValue('1000');
        await expect(page.getByRole('spinbutton', { name: 'Rows' })).toHaveValue('2');
        await expect(page.getByRole('cell', { name: '  Email   Require input' }).getByLabel('Price Adjustment Type')).toHaveValue('fixed');
        await expect(page.getByRole('spinbutton', { name: 'Price Adjustment Value' })).toHaveValue('4.97');
        await expect(page.getByRole('cell', { name: '  Email   Require input' }).getByLabel('Enable price adjustment')).toBeChecked();
        await expect(page.getByRole('cell', { name: '  Email   Require input' }).getByLabel('Autofocus this field on')).toBeChecked();
        await expect(page.getByRole('spinbutton', { name: 'Max value' })).toHaveValue('25');
        await expect(page.getByRole('cell', { name: '  Number   Require input' }).getByLabel('User can edit the field')).toBeChecked();
        await expect(page.getByRole('spinbutton', { name: 'Default Value' })).toHaveValue('10');
        await expect(page.getByRole('cell', { name: '  Number   Require input' }).getByPlaceholder('Placeholder Text')).toHaveValue('PlaceHolder Text');
        await expect(page.getByRole('cell', { name: '  Radio   Require input' }).getByLabel('Require input')).toBeChecked();
        await expect(page.getByRole('row', { name: ' Option C Option C Remove', exact: true }).getByRole('radio')).toBeChecked();
        await expect(page.getByRole('cell', { name: '  Color   Require input' }).getByLabel('Require input')).toBeChecked();
        await expect(page.getByRole('cell', { name: '  Color   Require input' }).getByPlaceholder('Help Text')).toHaveValue('Color?');
        await expect(page.getByRole('textbox', { name: '#1d2327' })).toHaveValue('#ff0000');
        await expect(page.locator('#exprdawc_text_css_class_6')).toHaveValue('class_radio_multi');
        await expect(page.locator('#exprdawc_text_required_6')).toBeChecked();
        await expect(page.getByRole('cell', { name: '#ffae00', exact: true }).getByPlaceholder('Select a color')).toHaveValue('#ffae00');
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