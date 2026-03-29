const { expect } = require('@playwright/test');

class ProductAdminPage {

    /**
     * Constructor for the ProductPage.
     * Initializes the page and sets up locators for product elements.
     *
     * @param {import('@playwright/test').Page} page - The Playwright page instance
     */
    constructor(page) {
        this.page = page;
    }

    /**
     * Navigates to the product admin page.
     *
     * @returns {Promise<void>}
     */
    async goto() {
        await this.page.goto('/wp-admin/edit.php?post_type=product');
    }

    /**
     * Navigates to a specific product's admin page.
     *
     * @param {string} productName - The name of the product to navigate to
     * @returns {Promise<void>}
     */
    async goToProductPage(productName) {
        await this.page.goto('/wp-admin/edit.php?post_type=product');
        await this.page.click(`a:has-text("${productName}")`);
    }

    /**
     * Navigates to the "Extra Product Input" tab on the product admin page.
     *
     * @returns {Promise<void>}
     */
    async goToExtraProductDataTab() {
        await this.page.waitForLoadState('networkidle');
        const tab = this.page.getByRole('heading', { name: 'Extra Product Input' });
        await expect(tab).toBeVisible();
        await expect(this.page.locator('#extra-product-data')).toBeVisible();

        // await this.page.click('a:has-text("Extra Product Input")');
    }

    /**
     * Clicks the "Add Option" button to add a new custom field.
     *
     * @returns {Promise<void>}
     */
    async clickAddOptionButton() {
        await this.page.click(`button#exprdawc_add_custom_field`);
    }

    async clickImportButton() {
        await this.page.click(`a.exprdawc-import`);
    }

    /**
     * Fills in a custom field with required checkbox.
     *
     * @param {string} fieldName - The name to assign to the field
     * @param {number} index - The index of the field
     * @param {string} type - The type of the field (e.g., 'text', 'long_text', 'email', 'number', 'date', 'yes-no', 'radio', 'checkbox', 'select')
     * @param {boolean} required - Whether the field should be marked as required
     * @returns {Promise<void>}
     */
    async fillExtraField(fieldName, index, type, required = true) {
        console.log(`Filling field: ${fieldName}, Index: ${index}, Type: ${type}, Required: ${required}`);
        await this.page.locator(`#exprdawc_attribute_type_${index}`).click();
        await this.page.locator(`input[name="extra_product_fields[${index}][label]"]`).fill(fieldName);
        await this.page.locator(`#exprdawc_attribute_type_${index}`).selectOption(type);
        if (required) {
            await this.page.locator(`#exprdawc_text_required_${index}`).check();
        }
    }

    /**
     * Adds options to a field from a dictionary.
     *
     * @param {Object} options - Dictionary with option labels as keys and option values as values
     * @param {number} index - The index of the field to which options should be added
     * @param {string} defaultOption - The value of the option to set as default (optional)
     * @returns {Promise<void>}
     */
    async addOptions(options, index, defaultOption = null) {
        for (const [label, value] of Object.entries(options)) {
            await this.page.getByRole('button', { name: 'Add Option' }).click();
            const optionIndex = await this.page.locator(`input[name^="extra_product_fields[${index}][options]"][name$="[label]"]`).count() - 1;
            await this.page.locator(`input[name="extra_product_fields[${index}][options][${optionIndex}][label]"]`).fill(label);
            await this.page.locator(`input[name="extra_product_fields[${index}][options][${optionIndex}][value]"]`).fill(value);
            if (defaultOption === value) {
                await this.page.locator(`input[name="extra_product_fields[${index}][default]"][value="${defaultOption}"]`).check();
            }
        }
    }

}

module.exports = { ProductAdminPage };