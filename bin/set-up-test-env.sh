#!/bin/bash
# This script sets up the WooCommerce test environment by installing the WooCommerce plugin, configuring basic settings, and importing sample products.
# It is intended to be run after the WordPress environment has been started using `npm run wp:start`.
# Usage:
#   1. Start the WordPress environment: `npm run wp:start`
#   2. Run this setup script: `bash bin/set-up-test-env.sh`
# Note: This script assumes that the WordPress environment is running and that WP-CLI is available through `npm run wp:cli`.
set +e

# Install Storefront theme (optional, but provides a better testing experience)
echo "Installing Storefront theme..."
npm run wp:cli -- wp theme install storefront --activate

echo "Setting up WooCommerce test environment..."

# Install WooCommerce plugin
echo "Installing WooCommerce plugin..."
npm run wp:cli -- wp plugin install woocommerce --activate

# Activate own plugin
echo "Activating Extra Product Data for WooCommerce plugin..."
npm run wp:cli -- wp plugin activate extra-product-data-for-woocommerce

# Install WooCommerce pages and configure basic settings
echo "Installing WooCommerce pages..."
npm run wp:cli -- wp wc tool run install_pages --user=admin

echo "Updating permalink structure..."
npm run wp:cli -- wp option update permalink_structure "/%postname%/"

echo "Flushing rewrite rules..."
npm run wp:cli -- wp rewrite flush --hard

echo "Configuring store address..."
npm run wp:cli -- wp option update woocommerce_store_address "Bahnhofsplatz 1"

echo "Configuring store city..."
npm run wp:cli -- wp option update woocommerce_store_city "Wiesbaden"

echo "Configuring store country..."
npm run wp:cli -- wp option update woocommerce_default_country "DE:DE-HE"

echo "Configuring store postcode..."
npm run wp:cli -- wp option update woocommerce_store_postcode "65189"

echo "Setting currency to EUR..."
npm run wp:cli -- wp option update woocommerce_currency "EUR"

echo "Enabling tax calculations..."
npm run wp:cli -- wp option update woocommerce_calc_taxes "yes"

echo "Enabling tax display in cart and checkout..."
npm run wp:cli -- wp option update woocommerce_prices_include_tax yes
npm run wp:cli -- wp option update woocommerce_tax_display_cart "incl"
npm run wp:cli -- wp option update woocommerce_tax_display_shop "incl"
npm run wp:cli -- wp wc tax create \
  --country=DE \
  --rate=19 \
  --name="MwSt 19%" \
  --priority=1 \
  --compound=false \
  --shipping=true \
  --user=1

# Payment and shipping settings
echo "Enabling cash on delivery payment method..."
npm run wp:cli -- wp option update woocommerce_cod_settings '{"enabled":"yes","title":"Cash on Delivery","description":"Pay with cash upon delivery.","instructions":"","order_button_text":""}' --format=json

echo "Opting out of onboardings and tracking..."
npm run wp:cli -- wp option update woocommerce_onboarding_opt_in "no"
npm run wp:cli -- wp option update woocommerce_show_marketplace_suggestions "no"
npm run wp:cli -- wp option update woocommerce_allow_tracking "no"
npm run wp:cli -- wp option update woocommerce_task_list_complete "yes"
npm run wp:cli -- wp option update woocommerce_task_list_welcome_modal_dismissed "yes"
npm run wp:cli -- wp option update woocommerce_weight_unit kg
npm run wp:cli -- wp option update woocommerce_dimension_unit cm
npm run wp:cli -- wp option update woocommerce_onboarding_profile '{"business_choice":"im_just_starting_my_business","industry":["electronics_and_computers"],"is_agree_marketing":false,"store_email":"wordpress@example.com","is_store_country_set":true,"completed":true,"skipped":false,"is_plugins_page_skipped":true}' --format=json
# npm run wp:cli -- wp option update woocommerce_task_list_hidden "yes"

echo "Downloading sample products..."
echo "Install Wordpress-Import plugin for product import..."
npm run wp:cli -- wp plugin install wordpress-importer --activate

echo "Importing sample products..."
# npm run wp:cli -- wp import wp-content/plugins/woocommerce/sample-data/sample_products.xml --authors=create --skip=image_resize --user=1
npm run wp:cli -- wp import wp-content/plugins/extra-product-data-for-woocommerce/bin/data/e2e_product_data_import.xml --authors=create --skip=image_resize --user=1


echo "Set site visibility to public..."
npm run wp:cli -- wp option update woocommerce_coming_soon no

echo "Permalinks need to be flushed after product import..."
npm run wp:cli -- wp rewrite flush --hard


echo "###################################################"
echo "# WooCommerce setup complete!"
echo "# Demo data import complete!"
echo "# Test environment is ready!"
echo "###################################################"
