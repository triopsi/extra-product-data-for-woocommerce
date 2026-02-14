#!/usr/bin/env bash
# Install WooCommerce for PHPUnit tests

set -e

WP_CORE_DIR=${WP_CORE_DIR-/tmp/wordpress}
WC_VERSION=${1-latest}

if [ ! -d "$WP_CORE_DIR" ]; then
    echo "WordPress not found at $WP_CORE_DIR"
    echo "Please run bin/install-wp-tests.sh first"
    exit 1
fi

WC_PLUGIN_DIR="$WP_CORE_DIR/wp-content/plugins/woocommerce"

echo "Installing WooCommerce for tests..."

# Remove existing WooCommerce installation
if [ -d "$WC_PLUGIN_DIR" ]; then
    echo "Removing existing WooCommerce installation..."
    rm -rf "$WC_PLUGIN_DIR"
fi

# Clone WooCommerce repository
echo "Cloning WooCommerce..."
if [ "$WC_VERSION" = "latest" ] || [ "$WC_VERSION" = "trunk" ]; then
    git clone --depth=1 --branch=trunk https://github.com/woocommerce/woocommerce.git "$WC_PLUGIN_DIR"
else
    git clone --depth=1 --branch="$WC_VERSION" https://github.com/woocommerce/woocommerce.git "$WC_PLUGIN_DIR"
fi

# Install Composer dependencies for WooCommerce
if [ -f "$WC_PLUGIN_DIR/composer.json" ]; then
    echo "Installing WooCommerce Composer dependencies..."
    cd "$WC_PLUGIN_DIR"
    composer install --no-dev --optimize-autoloader --quiet
    cd -
fi

echo "WooCommerce installed successfully at $WC_PLUGIN_DIR"
echo ""
echo "Now you can run PHPUnit tests with WooCommerce support:"
echo "  ./vendor/bin/phpunit"
