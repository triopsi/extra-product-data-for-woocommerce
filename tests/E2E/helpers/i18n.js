/**
 * Internationalization (i18n) Helper Functions
 * Provides utilities for multi-language support in E2E tests
 */

/**
 * Translation dictionary
 * Can be extended with more languages and strings as needed
 */
const translations = {
  en: {
    // WordPress Backend
    'Dashboard': 'Dashboard',
    'Products': 'Products',
    'Orders': 'Orders',
    'Settings': 'Settings',
    'Add New': 'Add New',
    'Save Changes': 'Save Changes',
    'Delete': 'Delete',
    'Edit': 'Edit',
    'View': 'View',

    // WooCommerce
    'Add to cart': 'Add to cart',
    'Product': 'Product',
    'Price': 'Price',
    'Quantity': 'Quantity',
    'Checkout': 'Checkout',
    'Cart': 'Cart',

    // Plugin Specific
    'Custom Field': 'Custom Field',
    'Custom Fields': 'Custom Fields',
    'Price Adjustment': 'Price Adjustment',
    'Add Custom Field': 'Add Custom Field',
  },
  de: {
    // WordPress Backend
    'Dashboard': 'Dashboard',
    'Products': 'Produkte',
    'Orders': 'Bestellungen',
    'Settings': 'Einstellungen',
    'Add New': 'Neu hinzufügen',
    'Save Changes': 'Änderungen speichern',
    'Delete': 'Löschen',
    'Edit': 'Bearbeiten',
    'View': 'Anzeigen',

    // WooCommerce
    'Add to cart': 'In den Warenkorb',
    'Product': 'Produkt',
    'Price': 'Preis',
    'Quantity': 'Menge',
    'Checkout': 'Kasse',
    'Cart': 'Warenkorb',

    // Plugin Specific
    'Custom Field': 'Benutzerdefiniertes Feld',
    'Custom Fields': 'Benutzerdefinierte Felder',
    'Price Adjustment': 'Preisanpassung',
    'Add Custom Field': 'Benutzerdefiniertes Feld hinzufügen',
  },
};

/**
 * Get translated string for current language
 * 
 * @param {string} key - Translation key
 * @param {string} language - Language code (default: process.env.TEST_LANGUAGE)
 * @returns {string} Translated string or key if not found
 * 
 * @example
 * const text = t('Add to cart'); // Returns: "In den Warenkorb" for German
 */
export function t(key, language = process.env.TEST_LANGUAGE || 'en') {
  const lang = translations[language] || translations.en;
  return lang[key] || key;
}

/**
 * Add new translation entry
 * 
 * @param {string} language - Language code
 * @param {string} key - Translation key
 * @param {string} value - Translated value
 * 
 * @example
 * addTranslation('en', 'Custom', 'Custom Text');
 */
export function addTranslation(language, key, value) {
  if (!translations[language]) {
    translations[language] = {};
  }
  translations[language][key] = value;
}

/**
 * Add multiple translations at once
 * 
 * @param {string} language - Language code
 * @param {Object} dictionary - Dictionary object with key-value pairs
 * 
 * @example
 * addTranslations('en', { 'Hello': 'Hello World', 'Goodbye': 'Goodbye' });
 */
export function addTranslations(language, dictionary) {
  if (!translations[language]) {
    translations[language] = {};
  }
  Object.assign(translations[language], dictionary);
}

/**
 * Get current test language from environment
 * 
 * @returns {string} Language code
 */
export function getCurrentLanguage() {
  return process.env.TEST_LANGUAGE || 'en';
}

/**
 * Get all available languages
 * 
 * @returns {Array<string>} Array of language codes
 */
export function getAvailableLanguages() {
  return Object.keys(translations);
}

/**
 * Check if a translation key exists for the current language
 * 
 * @param {string} key - Translation key
 * @param {string} language - Language code (optional)
 * @returns {boolean}
 */
export function hasTranslation(key, language = getCurrentLanguage()) {
  return language in translations && key in translations[language];
}
