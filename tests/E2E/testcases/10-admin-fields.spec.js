const { test, expect } = require('@playwright/test');
import { ProductAdminPage } from '../pages/shop/ProductAdminPage.js';
import { AdminLoginPage } from '../pages/admin/AdminLoginPage.js';
import { env } from '../helpers/env.js';

test.describe('@P10 @ADMIN', () => {
    test('ADM-01 Create extra product fields on product page (Simple)', async ({ page }) => {
        const adminUrl = env.wpAdminURL;
        const username = env.adminUser;
        const password = env.adminPass;

        const adminLogin = new AdminLoginPage(page);
        const productAdminPage = new ProductAdminPage(page);

        await adminLogin.goto(adminUrl);
        await adminLogin.login(username, password);
        await productAdminPage.goToProductPage('Polo');
        await productAdminPage.goToExtraProductDataTab();

        // Add Short Text field
        await productAdminPage.clickAddOptionButton();
        await productAdminPage.fillExtraField('Short Text', 0, 'text', true);

        // Add Long Text field
        await productAdminPage.clickAddOptionButton();
        await productAdminPage.fillExtraField('Long Text', 1, 'long_text', false);
        await page.locator('#exprdawc_attribute_type_1').selectOption('long_text');

        await page.getByRole('spinbutton', { name: 'Max length' }).click();
        await page.getByRole('spinbutton', { name: 'Max length' }).fill('1000');
        await page.getByRole('checkbox', { name: 'Require input', exact: true }).check();

        // Add Email field
        await productAdminPage.clickAddOptionButton();
        await productAdminPage.fillExtraField('Email', 2, 'email', false);
        await page.getByRole('cell', { name: '  Email Email   Require' }).getByPlaceholder('Enter a default email').click();
        await page.getByRole('cell', { name: '  Email Email   Require' }).getByPlaceholder('Enter a default email').fill('examle@example.org');

        // Add Number field
        await productAdminPage.clickAddOptionButton();
        await productAdminPage.fillExtraField('Number', 3, 'number', false);

        await page.getByRole('spinbutton', { name: 'Min value' }).click();
        await page.getByRole('spinbutton', { name: 'Min value' }).fill('5');
        await page.getByRole('spinbutton', { name: 'Max value' }).click();
        await page.getByRole('spinbutton', { name: 'Max value' }).fill('25');
        await page.getByRole('spinbutton', { name: 'Step' }).click();
        await page.getByRole('spinbutton', { name: 'Step' }).fill('5');
        await page.getByRole('spinbutton', { name: 'Default Value' }).click();
        await page.getByRole('spinbutton', { name: 'Default Value' }).fill('10');

        // Add Radio Button field
        await productAdminPage.clickAddOptionButton();
        await productAdminPage.fillExtraField('Radio', 4, 'radio', true);

        await productAdminPage.addOptions({
            'Option A': 'Option A',
            'Option B': 'Option B',
            'Option C': 'Option C',
            'Option D': 'Option D'
        }, 4, 'Option C');

        // Save changes
        await page.getByRole('button', { name: 'Update' }).click();

        // Verify that the fields are saved correctly
        // await page.getByRole('link', { name: 'Extra Product Input' }).click();
        await productAdminPage.goToExtraProductDataTab();


        await page.locator('tr:nth-child(5) > td > .exprdawc_fields_table > tbody > .exprdawc_attribute > .cl-arr > .dashicons').click();
        await page.locator('tr:nth-child(4) > td > .exprdawc_fields_table > tbody > .exprdawc_attribute > .cl-arr > .dashicons').click();
        await page.locator('tr:nth-child(3) > td > .exprdawc_fields_table > tbody > .exprdawc_attribute > .cl-arr > .dashicons').click();
        await page.locator('tr:nth-child(2) > td > .exprdawc_fields_table > tbody > .exprdawc_attribute > .cl-arr > .dashicons').click();
        await page.locator('.dashicons.dashicons-arrow-up').click();

        await expect(page.locator('#exprdawc_attribute_type_0')).toHaveValue('text');
        await expect(page.locator('#exprdawc_text_required_0')).toBeChecked();
        await expect(page.locator('#exprdawc_attribute_type_1')).toHaveValue('long_text');
        await expect(page.getByRole('cell', { name: '  Long Text Long Text  ' }).getByPlaceholder('Name of the label')).toHaveValue('Long Text');
        await expect(page.getByRole('cell', { name: '  Long Text Long Text  ' }).getByLabel('Require input')).toBeChecked();
        await expect(page.getByRole('spinbutton', { name: 'Rows' })).toHaveValue('2');

        await expect(page.getByRole('cell', { name: 'Max length 1000', exact: true }).getByLabel('Max length')).toHaveValue('1000');
        await expect(page.getByRole('cell', { name: '  Email Email   Require' }).getByPlaceholder('Name of the label')).toHaveValue('Email');
        await expect(page.locator('#exprdawc_attribute_type_2')).toHaveValue('email');
        await expect(page.getByRole('cell', { name: '  Email Email   Require' }).getByPlaceholder('Enter a default email')).toHaveValue('examle@example.org');

        await expect(page.locator('#exprdawc_attribute_type_3')).toHaveValue('number');
        await expect(page.getByRole('cell', { name: '  Number Number   Require' }).getByPlaceholder('Name of the label')).toHaveValue('Number');
        await expect(page.getByRole('spinbutton', { name: 'Min value' })).toHaveValue('5');
        await expect(page.getByRole('spinbutton', { name: 'Max value' })).toHaveValue('25');
        await expect(page.getByRole('spinbutton', { name: 'Step' })).toHaveValue('5');
        await expect(page.getByRole('spinbutton', { name: 'Default Value' })).toHaveValue('10');

        await expect(page.locator('#exprdawc_attribute_type_4')).toHaveValue('radio');
        await expect(page.getByRole('cell', { name: '  Radio Radio Button  ' }).getByPlaceholder('Name of the label')).toHaveValue('Radio');
        await expect(page.getByRole('row', { name: ' Option C Option C Remove', exact: true }).getByRole('radio')).toBeChecked();
        await expect(page.getByRole('row', { name: ' Option C Option C Remove', exact: true }).getByPlaceholder('Enter option label')).toHaveValue('Option C');
        await expect(page.getByRole('row', { name: ' Option C Option C Remove', exact: true }).getByRole('radio')).toBeChecked();

    });

    test('ADM-02 Delete extra product fields on product page', async ({ page }) => {
        const adminUrl = env.wpAdminURL;
        const username = env.adminUser;
        const password = env.adminPass;

        const adminLogin = new AdminLoginPage(page);
        const productAdminPage = new ProductAdminPage(page);

        await adminLogin.goto(adminUrl);
        await adminLogin.login(username, password);
        await productAdminPage.goToProductPage('Polo');
        await productAdminPage.goToExtraProductDataTab();

        for (let i = 0; i < 6; i++) {
            await page.locator(`tr > td > .exprdawc_fields_table > tbody > .exprdawc_attribute > td:nth-child(5) > .button.exprdawc_remove_custom_field`).first().click();
            page.once('dialog', dialog => {
                dialog.accept().catch(() => { });
            });
        }

        await expect(page.locator('#extra-product-data')).toContainText('Add descriptive input fields to allow the customer to visualize your product in the product overview.');
        await expect(page.locator('.exprdawc_no_entry_message')).toBeVisible();
        await expect(page.getByRole('link', { name: 'Import' })).toBeVisible();
        await expect(page.getByRole('link', { name: 'Export' })).toBeHidden();
        await page.getByRole('button', { name: 'Update' }).click();
        await expect(page.getByRole('heading', { name: 'Extra Product Input' })).toBeVisible();
        await expect(page.locator('.exprdawc_no_entry_message')).toBeVisible();
    });

    test('ADM-03 Add Color Picker field on product page', async ({ page }) => {
        const adminUrl = env.wpAdminURL;
        const username = env.adminUser;
        const password = env.adminPass;

        const adminLogin = new AdminLoginPage(page);
        const productAdminPage = new ProductAdminPage(page);

        await adminLogin.goto(adminUrl);
        await adminLogin.login(username, password);
        await productAdminPage.goToProductPage('Cap');
        await productAdminPage.goToExtraProductDataTab();

        // Add Color Picker field
        await productAdminPage.clickAddOptionButton();
        await productAdminPage.fillExtraField('Color', 0, 'color', false);

        await page.locator('#exprdawc_text_required_0').check();
        await page.locator('#exprdawc_text_editable_0').check();
        await page.getByPlaceholder('Select a default color').click();
        await page.getByPlaceholder('Select a default color').fill('#ff0000');
        await page.getByRole('textbox', { name: 'Help Text' }).click();
        await page.getByRole('textbox', { name: 'Help Text' }).fill('Color?');


        // Save changes
        await page.getByRole('button', { name: 'Update' }).click();

        // Verify that the fields are saved correctly
        await productAdminPage.goToExtraProductDataTab();


        await page.locator('.dashicons.toggle-options').click();
        await expect(page.getByRole('checkbox', { name: 'Require input' })).toBeChecked();
        await expect(page.getByRole('checkbox', { name: 'User can edit the field' })).toBeChecked();
        await expect(page.getByRole('textbox', { name: 'Default Value' })).toHaveValue('#ff0000');
        await expect(page.getByRole('textbox', { name: 'Help Text' })).toHaveValue('Color?');

    });

});