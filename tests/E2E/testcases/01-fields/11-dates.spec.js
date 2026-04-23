const { test, expect } = require('@playwright/test');
import { ProductAdminPage } from '../../pages/shop/ProductAdminPage.js';
import { AdminLoginPage } from '../../pages/admin/AdminLoginPage.js';
import { ProductPage } from '../../pages/shop/ProductPage.js';
import { env } from '../../helpers/env.js';

test.describe('@P10 @ADMIN', () => {

    test('ADMN-11 Date field input works', async ({ page }) => {
        const adminUrl = env.wpAdminURL;
        const username = env.adminUser;
        const password = env.adminPass;

        const adminLoginPage = new AdminLoginPage(page);
        const productAdminPage = new ProductAdminPage(page);
        const productPage = new ProductPage(page);

        await adminLoginPage.goto(adminUrl);
        await adminLoginPage.login(username, password);
        await productAdminPage.goToProductPage('Hoodie with Zipper');
        await productAdminPage.goToExtraProductDataTab();

        
        // Add Date, Time and Date Time field
        await page.getByRole('button', { name: '+ Add Field' }).click();
        await page.locator('#exprdawc_attribute_type_0').selectOption('date');
        await page.getByRole('textbox', { name: 'Name of the label' }).click();
        await page.getByRole('textbox', { name: 'Name of the label' }).fill('Date');
        await page.locator('.dashicons.dashicons-arrow-up').click();
        await page.getByRole('checkbox', { name: 'Require input' }).check();
        await page.getByRole('textbox', { name: 'Help Text ' }).click();
        await page.getByRole('textbox', { name: 'Help Text ' }).fill('Help Text');
        await page.getByRole('textbox', { name: 'CSS Class ' }).click();
        await page.getByRole('textbox', { name: 'CSS Class ' }).fill('css-class');
        await page.getByRole('textbox', { name: 'Min date' }).fill('2026-01-01');
        await page.getByRole('textbox', { name: 'Max date' }).fill('2026-01-31');
        await page.getByRole('textbox', { name: 'Default Value' }).fill('2026-01-10');
        await page.getByRole('button', { name: '+ Add Field' }).click();
        await page.locator('#exprdawc_attribute_type_1').selectOption('time');
        await page.locator('.dashicons.dashicons-arrow-up').click();
        await page.getByRole('row', { name: '  Time   Require input' }).getByPlaceholder('Name of the label').click();
        await page.getByRole('row', { name: '  Time   Require input' }).getByPlaceholder('Name of the label').fill('Time');
        await page.getByRole('cell', { name: '  Time Time   Require' }).getByLabel('Require input').check();
        await page.getByRole('cell', { name: '  Time Time   Require' }).getByPlaceholder('Help Text').click();
        await page.getByRole('cell', { name: '  Time Time   Require' }).getByPlaceholder('Help Text').fill('Time Help Text');
        await page.getByRole('cell', { name: '  Time Time   Require' }).getByPlaceholder('CSS Class').click();
        await page.getByRole('cell', { name: '  Time Time   Require' }).getByPlaceholder('CSS Class').fill('time-class');
        await page.getByRole('textbox', { name: 'Min time' }).click();
        await page.getByRole('textbox', { name: 'Min time' }).fill('10:00');
        await page.getByRole('textbox', { name: 'Max time' }).click();
        await page.getByRole('textbox', { name: 'Max time' }).fill('15:00');
        await page.locator('#exprdawc_time_default_1').click();
        await page.locator('#exprdawc_time_default_1').fill('12:00');
        await page.getByRole('button', { name: '+ Add Field' }).click();
        await page.locator('#exprdawc_attribute_type_2').selectOption('datetime');
        await page.locator('.dashicons.dashicons-arrow-up').click();
        await page.getByRole('cell', { name: '  Date Time   Require' }).getByPlaceholder('Name of the label').click();
        await page.getByRole('cell', { name: '  Date Time   Require' }).getByPlaceholder('Name of the label').fill('Date Time');
        await page.getByRole('cell', { name: '  Date Time Date Time  ' }).getByLabel('Require input').check();
        await page.getByRole('cell', { name: '  Date Time Date Time  ' }).getByPlaceholder('Help Text').click();
        await page.getByRole('cell', { name: '  Date Time Date Time  ' }).getByPlaceholder('Help Text').fill('Date Time Help Text');
        await page.getByRole('cell', { name: '  Date Time Date Time  ' }).getByPlaceholder('CSS Class').click();
        await page.getByRole('cell', { name: '  Date Time Date Time  ' }).getByPlaceholder('CSS Class').fill('datetime-class');
        await page.getByRole('textbox', { name: 'Earliest date & time' }).click();
        await page.getByRole('textbox', { name: 'Earliest date & time' }).click();
        await page.getByRole('textbox', { name: 'Earliest date & time' }).click();
        await page.getByRole('textbox', { name: 'Earliest date & time' }).click();
        await page.getByRole('textbox', { name: 'Earliest date & time' }).click();
        await page.getByRole('textbox', { name: 'Earliest date & time' }).dblclick();
        await page.getByRole('textbox', { name: 'Earliest date & time' }).click();
        await page.getByRole('textbox', { name: 'Latest date & time' }).click();
        await page.getByRole('checkbox', { name: 'Set default to current date' }).check();
        await page.getByRole('textbox', { name: 'Earliest date & time' }).press('ArrowRight');
        await page.getByRole('textbox', { name: 'Earliest date & time' }).press('ArrowRight');
        await page.getByRole('textbox', { name: 'Earliest date & time' }).press('ArrowRight');
        await page.getByRole('textbox', { name: 'Earliest date & time' }).fill('2026-05-05T10:00');
        await page.getByRole('textbox', { name: 'Latest date & time' }).click();
        await page.getByRole('textbox', { name: 'Latest date & time' }).press('ArrowRight');
        await page.getByRole('textbox', { name: 'Latest date & time' }).press('ArrowRight');
        await page.getByRole('textbox', { name: 'Latest date & time' }).press('ArrowRight');
        await page.getByRole('textbox', { name: 'Latest date & time' }).fill('2026-05-20T15:00');


        // Save product
        await page.getByRole('button', { name: 'Update' }).click();
        await expect(page.locator('#wpbody-content')).toContainText('Product updated. View Product');

        // Verify fields
        await page.locator('tr:nth-child(3) > td > .exprdawc_fields_table > tbody > .exprdawc_attribute > .cl-arr > .dashicons').click();
        await page.locator('tr:nth-child(2) > td > .exprdawc_fields_table > tbody > .exprdawc_attribute > .cl-arr > .dashicons').click();
        await page.locator('.dashicons.dashicons-arrow-up').click();

        // Date Field
        await expect(page.getByRole('row', { name: '  Date   Require input' }).getByPlaceholder('Name of the label')).toHaveValue('Date');
        await expect(page.getByRole('row', { name: '  Date   Require input' }).getByLabel('Require input')).toBeChecked();
        await expect(page.getByRole('row', { name: '  Date   Require input' }).getByPlaceholder('Help Text')).toHaveValue('Help Text');
        await expect(page.getByRole('row', { name: '  Date   Require input' }).getByPlaceholder('CSS Class')).toHaveValue('css-class');
        await expect(page.getByRole('textbox', { name: 'Min date' })).toHaveValue('2026-01-01');
        await expect(page.getByRole('textbox', { name: 'Max date' })).toHaveValue('2026-01-31');
        await expect(page.getByRole('cell', { name: 'Set default to today Default Value 2026-01-10', exact: true }).getByLabel('Default Value')).toHaveValue('2026-01-10');
        await expect(page.getByRole('checkbox', { name: 'Set default to today' })).not.toBeChecked();

        // Time Field
        await expect(page.getByRole('row', { name: '  Time   Require input' }).getByPlaceholder('Name of the label')).toHaveValue('Time');
        await expect(page.getByRole('row', { name: '  Time   Require input' }).getByLabel('Require input')).toBeChecked();
        await expect(page.getByRole('row', { name: '  Time   Require input' }).getByPlaceholder('Help Text')).toHaveValue('Time Help Text');
        await expect(page.getByRole('row', { name: '  Time   Require input' }).getByPlaceholder('CSS Class')).toHaveValue('time-class');
        await expect(page.getByRole('textbox', { name: 'Min time' })).toHaveValue('10:00');
        await expect(page.getByRole('textbox', { name: 'Max time' })).toHaveValue('15:00');
        await expect(page.locator('#exprdawc_time_default_1')).toHaveValue('12:00');

        // Date Time Field
        await expect(page.getByRole('cell', { name: '  Date Time   Require' }).getByPlaceholder('Name of the label')).toHaveValue('Date Time');
        await expect(page.getByRole('cell', { name: '  Date Time   Require' }).getByLabel('Require input')).toBeChecked();
        await expect(page.getByRole('cell', { name: '  Date Time   Require' }).getByPlaceholder('Help Text')).toHaveValue('Date Time Help Text');
        await expect(page.getByRole('cell', { name: '  Date Time   Require' }).getByPlaceholder('CSS Class')).toHaveValue('datetime-class');
        await expect(page.getByRole('textbox', { name: 'Earliest date & time' })).toHaveValue('2026-05-05T10:00');
        await expect(page.getByRole('textbox', { name: 'Latest date & time' })).toHaveValue('2026-05-20T15:00');
        await expect(page.getByRole('cell', { name: '  Date Time   Require' }).getByLabel('Time interval')).toHaveValue('300');
        await expect(page.getByRole('checkbox', { name: 'Set default to current date' })).toBeChecked();
        await expect(page.locator('#exprdawc_datetime_default_2')).toBeEmpty();


        // Go to product page and verify fields are shown correctly
        await productPage.goToProductPage('Hoodie with Zipper');
        await expect(page.getByRole('textbox', { name: 'Date  *' })).toHaveValue('2026-01-10');
        await expect(page.getByRole('textbox', { name: 'Time  *', exact: true })).toHaveValue('12:00');
        await expect(page.getByRole('textbox', { name: 'Date Time  *' })).not.toBeEmpty();
    });
});