=== Extra Product Data for WooCommerce ===
Contributors: triopsi
Tags: WooCommerce Product Addons, WooCommerce product options, WooCommerce custom fields, WooCommerce product fields
Tested up to: 6.9
Stable tag: 3.2.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Add custom product fields in WooCommerce to collect buyer details. Show data in cart, checkout, and orders with conditional logic support.

== Description ==

Extra Product Data for WooCommerce is a WooCommerce plugin developed to gather additional user information for products. Once installed, this plugin adds custom input fields to WooCommerce product pages, allowing users to enter extra information during the checkout process. These additional data fields are then displayed in the cart, the order summary, and within the backend for easy access.

Extra Product Data for WooCommerce is free and can be easily extended to fit specific needs with just a few clicks.

**100% Free and Open Source**

This plugin is truly 100% free to use. There is no Pro version, no premium upgrade, and no paid feature lock.  
We build and maintain this plugin to support the open source community and to keep useful WooCommerce tools accessible to everyone.

== Features ==

* Adds custom input fields on WooCommerce product pages.
* Supports various input types (text, textarea, select, etc.).
* Works with both Simple Products and Variable Products.
* Captures and stores additional user data for each product.
* Displays the data in the cart, checkout, and order summary.
* Allows admins to view the additional data provided by customers.
* Ideal for products that require user-specific information.
* Easy to extend and customize for specific requirements.
* Added option to make fields conditional based on other field values.
* The admin can still edit the fields in the order overview.

== Supported Fields ==
The following field types are currently implemented:

* Text
* Textarea
* Number
* Email
* Yes/No Field
* Date
* Select/options
* Radio Boxes
* Checboxes 
* Color Picker
* Multi Color Choice (Color Radio Fields)

== Open Source Lover == 

If you love this plugin or would like to support its development, please consider leaving us a 5-star review on WordPress.org [Rate us](https://wordpress.org/support/plugin/extra-product-data-for-woocommerce/reviews/#new-post). We do not earn anything from this plugin, so your review is a meaningful sign of appreciation and support for our work. It is a small but valuable way to show love for the open source community.

== Contribute ==

If you would like to contribute, feel free to help by adding your language to the plugin. Contribute via [translate.wordpress.org](https://translate.wordpress.org/projects/wp-plugins/extra-product-data-for-woocommerce/).

== Have you Questions? ==

If you have any questions or run into any issues, please feel free to reach out via the [WordPress support forum](https://wordpress.org/support/plugin/extra-product-data-for-woocommerce/). We are always happy to help.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/extra-product-data-for-woocommerce` directory, or install the plugin directly through the WordPress plugins screen.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Go to any WooCommerce product page to see the additional input fields.

== Frequently Asked Questions ==

= Can the fields be customized? =
Yes, the plugin is designed to be extended, allowing developers to modify the fields as needed.

= Is the plugin compatible with the latest WordPress version? =
Yes, the plugin has been tested with the latest WordPress release and PHP 8.2.

== Screenshots ==
1. WooCommerce Produkt Editor
2. Export/Import Window
3. Product View with extra/custom fields
4. Cart/Checkout View
5. Order Summary
6. Order Details
7. Order Edit Custom Field (Admin View)
8. Order Edit Customer View
9. Settings
10. Multi Choice of Color on Product page with price addjustments
11. Product Editor - Edit the Multi Choice Field

== Changelog ==

= 3.2.0 (31.03.2026) =
* Feat: Add Multi Color Radio Field
* Feat: Add custom css class field
* Feat: Changing the type after saving the field is now disabled
* Fix: Update Readme Update Workflow 
* Feat: Add Rate Us Banner

= 3.1.0 (26.03.2026) =
* Add new Field (Color Picker)
* Bugfix: Save default text for type "long text"
* New App Logo and Banner 

= 3.0.0 (18.03.2026) =
* Refactor the plugin
* Added new screenshots in the readme.txt
* Fixes many bugs

= 2.0.2 (04.03.2026) =
* Fix: Radio default on admin product page now also listens to input event

= 2.0.1 (25.02.2026) =
* Fix and Bump Version to wg.org

= 2.0.0 (25.02.2026) =
* various bug fixes in calculation, display, and when adding fields
* fixed default selection issues for select, checkbox, and radio
* added E2E tests with Playwright
* added WP ENV for development
* achieved >90% path coverage in PHPUnit
* fix for checking whether WooCommerce has already loaded
* stricter checks for usage of actions and filters

= 1.8.2 (13.02.2026) =
* Fix: Required input name with full stops

= 1.8.1 (13.02.2026) =
* Chore: Change Readme

= 1.8.0 (13.02.2026) =
* Bugfix: Radio buttons/checkboxes could not be created
* Feat: Colors and usability slightly adjusted according to WooCommerce
* Chore: Refactor the code, clean it up, and move it to GitHub.
* Documentation: Updated Readme and Changelog for 1.8.0
* Chore: New logo and banner for the WordPress plugin page

= 1.7.11 (05.02.2025) =
* Bug fix: the product can only be purchased once

= 1.7.10 (18.01.2025) =
* Bug fix: calculate with empty fields

= 1.7.9 (13.01.2025) =
* Bug fix: css fix on user edit page

= 1.7.8 (13.01.2025) =
* Bug fix: css fix on user edit page
* Bug fix: admin can edit the custom fields

= 1.7.7 (13.01.2025) =
* Bug fix: input fields have same css style

= 1.7.6 (13.01.2025) =
* Bug fix: maintenance versions

= 1.7.5 (13.01.2025) =
* Bug fix: fix with normally artefacts

= 1.7.4 (13.01.2025) =
* Bug fix: fix version in files

= 1.7.3 (11.01.2025) =
* Bug fix: Admin and users who placed the order can manipulate the total price after editing the order.

= 1.7.2 (2024-12-26) =
* JS are reade for plugin

= 1.7.1 (2024-12-26) =
* Restructuring of the entire code
* Fields can now influence the base price
* Many minor bug fixes
* Support Variable Products ans Simple Products

= 1.7.0 (2024-12-12) =
* add editable section for user in the order overview
* compatibility with WooCommerce HPOS

= 1.6.0 (2024-12-09) =
* add setting page and add options for "Show on Cart" and "Show on Checkout"

= 1.5.1 (2024-12-09) =
* edit button dont visible on order overview

= 1.5.0 (2024-12-09) =
* plugin can enable for another woocommerce products

= 1.4.1 (2024-12-06) =
* export link are always broke

= 1.4.0 (2024-12-04) =
* Radio and checkboxes don't have input-text CSS classes
* Select overflow
* Add autocomplete function
* Add Conditional Logic for fields
* Add Conditional rules in backend

= 1.3.0 (2024-11-30) =
* Add more field types checkboxes, radio and selects
* Edit option for admins in order overview

= 1.2.0 (2024-11-27) =
* Add Edit Button in the order overview.

= 1.1.3 (2024-11-27) =
* Update for the WordPress Library

= 1.1.2 (2024-11-20) =
* Initial release with fixes

= 1.1.1 (2024-11-20) =
* Initial release with fixes

= 1.0.0 ((2024-11-20)) =
* Initial release.

== Upgrade Notice ==

= 3.0.0 =
*Important:* This is a major update. Some fields have been re-indexed internally. Due to this change, it may be necessary to recreate or re/save your existing fields manually. Unfortunately, there is no automatic upgrade or migration tool available at this time. Please review your configured fields after updating and recreate them if needed.
