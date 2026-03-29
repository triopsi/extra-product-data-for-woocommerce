const { test, expect } = require('@playwright/test');
import { ProductAdminPage } from '../pages/shop/ProductAdminPage.js';
import { AdminLoginPage } from '../pages/admin/AdminLoginPage.js';
import { ProductPage } from '../pages/shop/ProductPage.js';
import { env } from '../helpers/env.js';

test.describe('@P10 @ADMIN', () => {
    test('ADM-01 Create extra product fields on product editor', async ({ page }) => {
        const adminUrl = env.wpAdminURL;
        const username = env.adminUser;
        const password = env.adminPass;

        const adminLogin = new AdminLoginPage(page);
        const productAdminPage = new ProductAdminPage(page);

        await adminLogin.goto(adminUrl);
        await adminLogin.login(username, password);
        await productAdminPage.goToProductPage('Polo');
        await productAdminPage.goToExtraProductDataTab();

        // 1. Add Short Text field
        await productAdminPage.clickAddOptionButton();
        await productAdminPage.fillExtraField('Short Text', 0, 'text', true);
        await page.getByRole('cell', { name: '  Short Text Short Text' }).getByPlaceholder('Help Text').click();
        await page.getByRole('cell', { name: '  Short Text Short Text' }).getByPlaceholder('Help Text').fill('Help Text Short Text');
        await page.getByRole('cell', { name: '  Short Text Short Text' }).getByPlaceholder('Placeholder Text').click();
        await page.getByRole('cell', { name: '  Short Text Short Text' }).getByPlaceholder('Placeholder Text').fill('Placeholder Text');
        await page.locator('#exprdawc_text_editable_0').check();
        await page.locator('#exprdawc_text_min_length_0').click();
        await page.locator('#exprdawc_text_min_length_0').fill('5');
        await page.locator('#exprdawc_text_max_length_0').click();
        await page.locator('#exprdawc_text_max_length_0').click();
        await page.locator('#exprdawc_text_max_length_0').fill('20');
        await page.getByRole('textbox', { name: 'Enter a default text' }).click();
        await page.getByRole('textbox', { name: 'Enter a default text' }).fill('Default Text');

        // 2. Add Long Text field
        await productAdminPage.clickAddOptionButton();
        await productAdminPage.fillExtraField('Long Text', 1, 'long_text', false);
        await page.locator('#exprdawc_attribute_type_1').selectOption('long_text');
        await page.getByRole('cell', { name: '  Long Text Long Text  ' }).getByPlaceholder('Placeholder Text').click();
        await page.getByRole('cell', { name: '  Long Text Long Text  ' }).getByPlaceholder('Placeholder Text').fill('PlaceHolder');
        await page.getByRole('cell', { name: '  Long Text Long Text  ' }).getByPlaceholder('Help Text').click();
        await page.getByRole('cell', { name: '  Long Text Long Text  ' }).getByPlaceholder('Help Text').fill('HelpText');
        await page.getByRole('cell', { name: 'Default Value', exact: true }).getByPlaceholder('Enter a default text').click();
        await page.getByRole('cell', { name: 'Default Value', exact: true }).getByPlaceholder('Enter a default text').fill('Hier steht ein\nDefault Text');
        await page.getByRole('spinbutton', { name: 'Min length' }).click();
        await page.getByRole('spinbutton', { name: 'Min length' }).fill('6');
        await page.getByRole('spinbutton', { name: 'Max length' }).dblclick();
        await page.getByRole('spinbutton', { name: 'Max length' }).fill('1000');
        await page.getByRole('checkbox', { name: 'Require input', exact: true }).check();

        // 3. Add Email field
        await productAdminPage.clickAddOptionButton();
        await productAdminPage.fillExtraField('Email', 2, 'email', false);
        await page.getByRole('cell', { name: '  Email Email   Require' }).getByPlaceholder('Placeholder Text').click();
        await page.getByRole('cell', { name: '  Email Email   Require' }).getByPlaceholder('Placeholder Text').fill('Place Text');
        await page.getByRole('cell', { name: '  Email Email   Require' }).getByPlaceholder('Help Text').click();
        await page.getByRole('cell', { name: '  Email Email   Require' }).getByPlaceholder('Help Text').fill('Help Text');
        await page.getByRole('cell', { name: '  Email Email   Require' }).getByLabel('Autofocus this field on').check();
        await page.getByRole('cell', { name: '  Email Email   Require' }).getByLabel('Enable price adjustment').check();
        await page.getByRole('spinbutton', { name: 'Price Adjustment Value' }).click();
        await page.getByRole('spinbutton', { name: 'Price Adjustment Value' }).fill('4.97');
        await page.getByRole('cell', { name: '  Email Email   Require' }).getByPlaceholder('Enter a default email').click();
        await page.getByRole('cell', { name: '  Email Email   Require' }).getByPlaceholder('Enter a default email').fill('examle@example.org');

        // 4. Add Number field
        await productAdminPage.clickAddOptionButton();
        await productAdminPage.fillExtraField('Number', 3, 'number', false);
        await page.getByRole('cell', { name: '  Number Number   Require' }).getByPlaceholder('Placeholder Text').click();
        await page.getByRole('cell', { name: '  Number Number   Require' }).getByPlaceholder('Placeholder Text').click();
        await page.getByRole('cell', { name: '  Number Number   Require' }).getByPlaceholder('Placeholder Text').fill('PlaceHolder Text');
        await page.getByRole('cell', { name: '  Number Number   Require' }).getByPlaceholder('Help Text').click();
        await page.getByRole('cell', { name: '  Number Number   Require' }).getByPlaceholder('Help Text').fill('Help Text');
        await page.getByRole('cell', { name: '  Number Number   Require' }).getByLabel('User can edit the field').check();
        await page.getByRole('cell', { name: '  Number Number   Require' }).getByLabel('Require input').check();
        await page.getByRole('spinbutton', { name: 'Min value' }).click();
        await page.getByRole('spinbutton', { name: 'Min value' }).fill('5');
        await page.getByRole('spinbutton', { name: 'Max value' }).click();
        await page.getByRole('spinbutton', { name: 'Max value' }).fill('25');
        await page.getByRole('spinbutton', { name: 'Step' }).click();
        await page.getByRole('spinbutton', { name: 'Step' }).fill('5');
        await page.getByRole('spinbutton', { name: 'Default Value' }).click();
        await page.getByRole('spinbutton', { name: 'Default Value' }).fill('10');

        // 5. Add Radio Button field
        await productAdminPage.clickAddOptionButton();
        await productAdminPage.fillExtraField('Radio', 4, 'radio', true);
        await page.getByRole('cell', { name: '  Radio Radio Button  ' }).getByPlaceholder('Help Text').click();
        await page.getByRole('cell', { name: '  Radio Radio Button  ' }).getByPlaceholder('Help Text').fill('Help Text');
        await productAdminPage.addOptions({
            'Option A': 'Option A',
            'Option B': 'Option B',
            'Option C': 'Option C',
            'Option D': 'Option D'
        }, 4, 'Option C');

        // 6. Add Color Picker field
        await productAdminPage.clickAddOptionButton();
        await productAdminPage.fillExtraField('Color', 5, 'color', true);
        await page.getByRole('cell', { name: '  Color Color   Require' }).getByPlaceholder('Select a default color').fill('#ff0000');
        await page.getByRole('cell', { name: '  Color Color   Require' }).getByPlaceholder('Help Text').click();
        await page.getByRole('cell', { name: '  Color Color   Require' }).getByPlaceholder('Help Text').fill('Color?');
        await page.getByRole('cell', { name: '  Color Color   Require' }).getByLabel('User can edit the field').check();

        // Save changes
        await page.getByRole('button', { name: 'Update' }).click();
        await expect(page.locator('#wpbody-content')).toContainText('Product updated. View ProductDismiss this notice.');

    });

    test('ADM-02 Verify extra product fields on product editor', async ({ page }) => {
        const adminUrl = env.wpAdminURL;
        const username = env.adminUser;
        const password = env.adminPass;

        const adminLogin = new AdminLoginPage(page);
        const productAdminPage = new ProductAdminPage(page);

        await adminLogin.goto(adminUrl);
        await adminLogin.login(username, password);
        await productAdminPage.goToProductPage('Polo');
        await productAdminPage.goToExtraProductDataTab();

        // Collapse all fields to check if the correct fields are present and in the correct order
        await page.locator('tr:nth-child(5) > td > .exprdawc_fields_table > tbody > .exprdawc_attribute > .cl-arr > .dashicons').click();
        await page.locator('tr:nth-child(4) > td > .exprdawc_fields_table > tbody > .exprdawc_attribute > .cl-arr > .dashicons').click();
        await page.locator('tr:nth-child(3) > td > .exprdawc_fields_table > tbody > .exprdawc_attribute > .cl-arr > .dashicons').click();
        await page.locator('tr:nth-child(2) > td > .exprdawc_fields_table > tbody > .exprdawc_attribute > .cl-arr > .dashicons').click();
        await page.locator('tr:nth-child(6) > td > .exprdawc_fields_table > tbody > .exprdawc_attribute > .cl-arr > .dashicons').click();
        await page.locator('.dashicons.dashicons-arrow-up').click();

        // 1. Validate Short Text field
        await expect(page.locator('#exprdawc_attribute_type_0')).toHaveValue('text');
        await expect(page.locator('#exprdawc_text_required_0')).toBeChecked();
        await expect(page.getByRole('cell', { name: 'Default Value Default Text', exact: true }).getByPlaceholder('Enter a default text')).toHaveValue('Default Text');
        await expect(page.getByRole('cell', { name: 'Min length 5', exact: true }).getByLabel('Min length')).toHaveValue('5');
        await expect(page.getByRole('cell', { name: 'Max length 20', exact: true }).getByLabel('Max length')).toHaveValue('20');
        await expect(page.getByRole('cell', { name: '  Short Text Short Text' }).getByPlaceholder('Placeholder Text')).toHaveValue('Placeholder Text');
        await expect(page.getByRole('cell', { name: '  Short Text Short Text' }).getByPlaceholder('Help Text')).toHaveValue('Help Text Short Text');
        await expect(page.getByRole('cell', { name: '  Short Text Short Text' }).getByLabel('Require input')).toBeChecked();
        await expect(page.getByRole('cell', { name: '  Short Text Short Text' }).getByLabel('User can edit the field')).toBeChecked();
        await expect(page.getByRole('cell', { name: '  Short Text Short Text' }).getByLabel('Autofocus this field on')).not.toBeChecked();

        // 2. Validate Long Text field
        await expect(page.locator('#exprdawc_attribute_type_1')).toHaveValue('long_text');
        await expect(page.getByRole('cell', { name: '  Long Text Long Text  ' }).getByPlaceholder('Name of the label')).toHaveValue('Long Text');
        await expect(page.getByRole('cell', { name: '  Long Text Long Text  ' }).getByLabel('Require input')).toBeChecked();
        await expect(page.getByRole('spinbutton', { name: 'Rows' })).toHaveValue('2');
        await expect(page.getByRole('spinbutton', { name: 'Columns' })).toHaveValue('5');
        await expect(page.getByRole('cell', { name: 'Min length 6', exact: true }).getByLabel('Min length')).toHaveValue('6');
        await expect(page.getByRole('cell', { name: 'Max length 1000', exact: true }).getByLabel('Max length')).toHaveValue('1000');
        await expect(page.getByText('Hier steht ein Default Text')).toHaveValue('Hier steht ein\nDefault Text');
        await expect(page.getByRole('cell', { name: '  Long Text Long Text  ' }).getByPlaceholder('Placeholder Text')).toHaveValue('PlaceHolder');
        await expect(page.getByRole('cell', { name: '  Long Text Long Text  ' }).getByPlaceholder('Help Text')).toHaveValue('HelpText');

        // 3. Validate Email field
        await expect(page.getByRole('cell', { name: '  Email Email   Require' }).getByPlaceholder('Name of the label')).toHaveValue('Email');
        await expect(page.locator('#exprdawc_attribute_type_2')).toHaveValue('email');
        await expect(page.getByRole('cell', { name: '  Email Email   Require' }).getByPlaceholder('Enter a default email')).toHaveValue('examle@example.org');
        await expect(page.getByRole('cell', { name: '  Email Email   Require' }).getByLabel('Autofocus this field on')).toBeChecked();
        await expect(page.getByRole('cell', { name: '  Email Email   Require' }).getByLabel('Enable price adjustment')).toBeChecked();
        await expect(page.getByRole('cell', { name: '  Email Email   Require' }).getByLabel('Price Adjustment Type')).toHaveValue('fixed');
        await expect(page.getByRole('spinbutton', { name: 'Price Adjustment Value' })).toHaveValue('4.97');
        await expect(page.getByRole('cell', { name: '  Email Email   Require' }).getByPlaceholder('Placeholder Text')).toHaveValue('Place Text');
        await expect(page.getByRole('cell', { name: '  Email Email   Require' }).getByPlaceholder('Help Text')).toHaveValue('Help Text');
        await expect(page.getByRole('cell', { name: '  Email Email   Require' }).getByLabel('User can edit the field')).not.toBeChecked();
        await expect(page.getByRole('cell', { name: '  Email Email   Require' }).getByLabel('Require input')).not.toBeChecked();

        // 4. Validate Number field
        await expect(page.locator('#exprdawc_attribute_type_3')).toHaveValue('number');
        await expect(page.getByRole('cell', { name: '  Number Number   Require' }).getByPlaceholder('Name of the label')).toHaveValue('Number');
        await expect(page.getByRole('spinbutton', { name: 'Min value' })).toHaveValue('5');
        await expect(page.getByRole('spinbutton', { name: 'Max value' })).toHaveValue('25');
        await expect(page.getByRole('spinbutton', { name: 'Step' })).toHaveValue('5');
        await expect(page.getByRole('spinbutton', { name: 'Default Value' })).toHaveValue('10');
        await expect(page.getByRole('cell', { name: '  Number Number   Require' }).getByPlaceholder('Placeholder Text')).toHaveValue('PlaceHolder Text');
        await expect(page.getByRole('cell', { name: '  Number Number   Require' }).getByPlaceholder('Help Text')).toHaveValue('Help Text');
        await expect(page.getByRole('cell', { name: '  Number Number   Require' }).getByLabel('User can edit the field')).toBeChecked();
        await expect(page.getByRole('cell', { name: '  Number Number   Require' }).getByLabel('Require input')).toBeChecked();
        await expect(page.getByRole('cell', { name: '  Number Number   Require' }).getByLabel('Autofocus this field on')).not.toBeChecked();

        // 5. Validate Radio Button field
        await expect(page.locator('#exprdawc_attribute_type_4')).toHaveValue('radio');
        await expect(page.getByRole('cell', { name: '  Radio Radio Button  ' }).getByPlaceholder('Name of the label')).toHaveValue('Radio');
        await expect(page.getByRole('row', { name: ' Option C Option C Remove', exact: true }).getByRole('radio')).toBeChecked();
        await expect(page.getByRole('row', { name: ' Option C Option C Remove', exact: true }).getByPlaceholder('Enter option label')).toHaveValue('Option C');
        await expect(page.getByRole('row', { name: ' Option C Option C Remove', exact: true }).getByRole('radio')).toBeChecked();
        await expect(page.getByRole('cell', { name: '  Radio Radio Button  ' }).getByPlaceholder('Help Text')).toHaveValue('Help Text');
        await expect(page.getByRole('cell', { name: '  Radio Radio Button  ' }).getByLabel('Require input')).toBeChecked();

        // 6. Validate Color Picker field
        await expect(page.locator('#exprdawc_attribute_type_5')).toHaveValue('color');
        await expect(page.getByRole('cell', { name: '  Color Color   Require' }).getByLabel('User can edit the field')).toBeChecked();
        await expect(page.getByRole('cell', { name: '  Color Color   Require' }).getByLabel('Require input')).toBeChecked();
        await expect(page.getByRole('cell', { name: '  Color Color   Require' }).getByPlaceholder('Help Text')).toHaveValue('Color?');
        await expect(page.getByRole('cell', { name: '  Color Color   Require' }).getByPlaceholder('Select a default color')).toHaveValue('#ff0000');
        await expect(page.getByRole('checkbox', { name: 'User can input a custom color' })).not.toBeChecked();

    });

    test('ADM-03 Delete extra product fields on product page', async ({ page }) => {
        const adminUrl = env.wpAdminURL;
        const username = env.adminUser;
        const password = env.adminPass;

        const adminLogin = new AdminLoginPage(page);
        const productAdminPage = new ProductAdminPage(page);

        await adminLogin.goto(adminUrl);
        await adminLogin.login(username, password);
        await productAdminPage.goToProductPage('Polo');
        await productAdminPage.goToExtraProductDataTab();

        for (let i = 0; i < 7; i++) {
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

    test('ADM-04 Add Color Picker field on product page', async ({ page }) => {
        const adminUrl = env.wpAdminURL;
        const username = env.adminUser;
        const password = env.adminPass;

        const adminLogin = new AdminLoginPage(page);
        const productAdminPage = new ProductAdminPage(page);
        const productPage = new ProductPage(page);

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

        // Add Color Picker field 2
        await productAdminPage.clickAddOptionButton();
        await productAdminPage.fillExtraField('Color 2', 1, 'color', false);
        await page.getByLabel('Help Text').click();
        await page.getByLabel('Help Text').fill('Help Text');
        await page.getByRole('checkbox', { name: 'User can input a custom color' }).check();
        await page.getByRole('cell', { name: '  Color 2 Color   Require' }).getByPlaceholder('#1d2327').click();
        await page.getByRole('cell', { name: '  Color 2 Color   Require' }).getByPlaceholder('#1d2327').fill('#ededed');

        // Save changes
        await page.getByRole('button', { name: 'Update' }).click();

        // Verify that the fields are saved correctly
        await productAdminPage.goToExtraProductDataTab();


        await page.locator('.dashicons.dashicons-arrow-up').first().click();
        await expect(page.getByRole('cell', { name: '  Color Color   Require' }).getByPlaceholder('Name of the label')).toHaveValue('Color');
        await expect(page.getByRole('textbox', { name: 'Help Text' })).toHaveValue('Color?');
        await expect(page.getByRole('checkbox', { name: 'Require input' })).toBeChecked();
        await expect(page.getByRole('checkbox', { name: 'User can edit the field' })).toBeChecked();
        await expect(page.getByRole('textbox', { name: 'Default Value' })).toHaveValue('#ff0000');
        await expect(page.getByRole('textbox', { name: '#1d2327' })).toHaveValue('#ff0000');
        await expect(page.getByRole('checkbox', { name: 'User can input a custom color' })).not.toBeChecked();
        await page.locator('.dashicons.dashicons-arrow-up').click();
        await expect(page.getByRole('cell', { name: '  Color 2 Color   Require' }).getByPlaceholder('Name of the label')).toHaveValue('Color 2');
        await expect(page.getByRole('cell', { name: '  Color 2 Color   Require' }).getByPlaceholder('Help Text')).toHaveValue('Help Text');
        await expect(page.getByRole('cell', { name: '  Color 2 Color   Require' }).getByLabel('User can input a custom color')).toBeChecked();
        await expect(page.getByRole('cell', { name: '  Color 2 Color   Require' }).getByPlaceholder('Select a default color')).toHaveValue('#ededed');
        await expect(page.getByRole('cell', { name: '  Color 2 Color   Require' }).getByPlaceholder('#1d2327')).toHaveValue('#ededed');
        await expect(page.getByRole('cell', { name: '  Color 2 Color   Require' }).getByLabel('Require input')).not.toBeChecked();
        await expect(page.getByRole('cell', { name: '  Color 2 Color   Require' }).getByLabel('Autofocus this field on')).not.toBeChecked();
        await expect(page.getByRole('cell', { name: '  Color 2 Color   Require' }).getByLabel('User can edit the field')).not.toBeChecked();
        await expect(page.getByRole('cell', { name: '  Color 2 Color   Require' }).getByLabel('Enable conditional logic')).not.toBeChecked();
        await expect(page.getByRole('cell', { name: '  Color 2 Color   Require' }).getByLabel('Enable price adjustment')).not.toBeChecked();

        // Frontend Check
        await productPage.goToProductPage('Cap');
        await expect(page.getByRole('textbox', { name: 'Color  *' })).toHaveValue('#ff0000');
        await expect(page.getByRole('textbox', { name: 'Color 2  (Optional)' })).toHaveValue('#ededed');

        await expect(page.getByTestId('color_hex_field_1')).toHaveValue('#ededed');
        await expect(page.getByText('Color?')).toBeVisible();
        await expect(page.getByText('Help Text')).toBeVisible();

    });

    test('ADM-05 Add Multi Color field on product page', async ({ page }) => {
        const adminUrl = env.wpAdminURL;
        const username = env.adminUser;
        const password = env.adminPass;

        const adminLogin = new AdminLoginPage(page);
        const productAdminPage = new ProductAdminPage(page);
        const productPage = new ProductPage(page);

        await adminLogin.goto(adminUrl);
        await adminLogin.login(username, password);
        await productAdminPage.goToProductPage('Single');
        await productAdminPage.goToExtraProductDataTab();

        // Add Color Picker field
        await productAdminPage.clickAddOptionButton();
        await productAdminPage.fillExtraField('Multi Color', 0, 'color_radio', false);

        await page.locator('#exprdawc_text_required_0').check();
        await page.locator('#exprdawc_text_editable_0').check();

        await page.getByRole('textbox', { name: 'Help Text ' }).click();
        await page.getByRole('textbox', { name: 'Help Text ' }).fill('Help Text');
        await page.getByRole('textbox', { name: 'CSS Class ' }).click();
        await page.getByRole('textbox', { name: 'CSS Class ' }).fill('css-class-custom');
        await page.getByRole('radio', { name: 'Badget' }).check();
        await page.getByRole('textbox', { name: 'Size (e.g. 75px) ' }).click();
        await page.getByRole('textbox', { name: 'Size (e.g. 75px) ' }).fill('100px');
        await page.getByRole('button', { name: 'Add Option' }).click();
        await page.getByRole('textbox', { name: 'Enter option label' }).click();
        await page.getByRole('textbox', { name: 'Enter option label' }).fill('Option A');
        await page.getByPlaceholder('Select a color').click();
        await page.getByPlaceholder('Select a color').fill('#f50000');
        await page.getByRole('button', { name: 'Add Option' }).click();
        await page.getByRole('row', { name: ' #000000 Remove', exact: true }).getByPlaceholder('Enter option label').click();
        await page.getByRole('row', { name: ' #000000 Remove', exact: true }).getByPlaceholder('Enter option label').fill('Option B');
        await page.getByRole('cell', { name: '#000000', exact: true }).getByPlaceholder('Select a color').click();
        await page.getByRole('cell', { name: '#000000', exact: true }).getByPlaceholder('Select a color').fill('#0400ff');
        await page.getByRole('row', { name: ' Option B #0400ff Remove', exact: true }).getByRole('radio').check();
        
        // Save changes
        await page.getByRole('button', { name: 'Update' }).click();

        // Verify that the fields are saved correctly
        await productAdminPage.goToExtraProductDataTab();

        await page.locator('.dashicons.dashicons-arrow-up').click();
        await expect(page.getByRole('textbox', { name: 'Help Text ' })).toHaveValue('Help Text');
        await expect(page.getByRole('textbox', { name: 'CSS Class ' })).toHaveValue('css-class-custom');
        await expect(page.getByRole('radio', { name: 'Badget' })).toBeChecked();
        await expect(page.getByRole('textbox', { name: 'Size (e.g. 75px) ' })).toHaveValue('100px');
        await expect(page.getByRole('cell', { name: 'Option A', exact: true }).getByPlaceholder('Enter option label')).toHaveValue('Option A');
        await expect(page.getByRole('cell', { name: '#f50000', exact: true }).getByPlaceholder('Select a color')).toHaveValue('#f50000');
        await expect(page.getByRole('row', { name: ' Option A #f50000 Remove', exact: true }).getByRole('radio')).not.toBeChecked();
        await expect(page.getByRole('cell', { name: 'Option B', exact: true }).getByPlaceholder('Enter option label')).toHaveValue('Option B');
        await expect(page.getByRole('cell', { name: '#0400ff', exact: true }).getByPlaceholder('Select a color')).toHaveValue('#0400ff');
        await expect(page.getByRole('row', { name: ' Option B #0400ff Remove', exact: true }).getByRole('radio')).toBeChecked();
        await expect(page.getByRole('checkbox', { name: 'Show label name under the' })).toBeChecked();
        await expect(page.getByRole('checkbox', { name: 'Require input' })).toBeChecked();
        await expect(page.getByRole('checkbox', { name: 'User can edit the field' })).toBeChecked();
        await expect(page.getByRole('textbox', { name: 'Name of the label' })).toHaveValue('Multi Color');

        // Frontend Check
        await productPage.goToProductPage('Single');

        // Check if the color swatch badge is visible and has the correct size
        await expect(page.locator('label:nth-child(4) > .exprdawc-color-swatch-badget')).toBeVisible();
        await expect(page.locator('.exprdawc-color-swatch-badget').first()).toBeVisible();
        await expect(page.getByText('Help Text')).toBeVisible();
        await page.locator('.exprdawc-color-swatch-badget').first().click();

        // Add to cart and check if the selected color is displayed in the cart and checkout page
        await page.getByRole('button', { name: 'Add to cart', exact: true }).click();
        await expect(page.getByRole('alert')).toContainText('“Single” has been added to your cart. View cart');
        await page.locator('#content').getByRole('link', { name: 'View cart ' }).click();
        await expect(page.locator('tbody')).toContainText('This is a simple, virtual product. Original item price: €2.00 / Multi Color: #f50000');

    });

});