# Changelog

<<<<<<< HEAD
=======
## [3.5.5](https://github.com/lengow/plugin-prestashop/compare/v3.5.4...v3.5.5) (2024-08-28)


### Bug Fixes

* **products:** [PST-21685] Prestashop problem display product with version 3.5.4 ([#21](https://github.com/lengow/plugin-prestashop/issues/21)) ([ac663ec](https://github.com/lengow/plugin-prestashop/commit/ac663ecf6e3673e3a3df8915f19cddeac71645ae))


### Miscellaneous

* add version composer ([#19](https://github.com/lengow/plugin-prestashop/issues/19)) ([f8202a3](https://github.com/lengow/plugin-prestashop/commit/f8202a3ace406d578385a3fc535a0200dcdb988c))
* add version in composer.json ([f8202a3](https://github.com/lengow/plugin-prestashop/commit/f8202a3ace406d578385a3fc535a0200dcdb988c))
* add version in composer.json ([#18](https://github.com/lengow/plugin-prestashop/issues/18)) ([6d3aa63](https://github.com/lengow/plugin-prestashop/commit/6d3aa63c6702e434d7d8483bbb9b2fd02e277307))

>>>>>>> c407e0f5d26e7633b7a080a00d7eb917b49c3ce3
## [3.5.4](https://github.com/lengow/plugin-prestashop/compare/v3.5.3...v3.5.4) (2024-08-22)


### Features

* **cicd:** Add a besic CI to the project ([#15](https://github.com/lengow/plugin-prestashop/issues/15)) ([3c6c19c](https://github.com/lengow/plugin-prestashop/commit/3c6c19c0aae423409828aaf8cdbffa911c9acf6b))

* **import:** [ECP-96] nb days sync could be inf at 1 ([#14](https://github.com/lengow/plugin-prestashop/issues/14)) ([5b2d575](https://github.com/lengow/plugin-prestashop/commit/5b2d575ba1853022fcdb240cda74b55795e6780a))


### Bug Fixes

* **plugins:** [ECP-101] fix syntax - change version ([#14](https://github.com/lengow/plugin-prestashop/issues/14)) ([5b2d575](https://github.com/lengow/plugin-prestashop/commit/5b2d575ba1853022fcdb240cda74b55795e6780a))

* **plugins:** [PST-21540] Prestashop hook callable in main class ([#14](https://github.com/lengow/plugin-prestashop/issues/14)) ([5b2d575](https://github.com/lengow/plugin-prestashop/commit/5b2d575ba1853022fcdb240cda74b55795e6780a))

* **plugins:** [PST-21540] config for disable email and order status change ([#14](https://github.com/lengow/plugin-prestashop/issues/14)) ([5b2d575](https://github.com/lengow/plugin-prestashop/commit/5b2d575ba1853022fcdb240cda74b55795e6780a))


### Miscellaneous

* **clean:** Remove obsolete files ([3c6c19c](https://github.com/lengow/plugin-prestashop/commit/3c6c19c0aae423409828aaf8cdbffa911c9acf6b))
* **docs:** Precise Changelog file type ([3c6c19c](https://github.com/lengow/plugin-prestashop/commit/3c6c19c0aae423409828aaf8cdbffa911c9acf6b))

## Changelog


=============================================================
Version 3.5.4
=============================================================
    - BugFix: nb days for sync could be less than 1
    - BugFix: config for disabled sending email when order status changes actionOrderStatusUpdate
=============================================================
Version 3.5.3
=============================================================
 - BugFix: fix same product id registred for lengow order line
 - BugFix: fix get order from lengow order returning false in specific case
 - BugFix: security fixes with Db escaping
 - BugFix: move module product key from versioning
 - Feature: add configuration select type of anonymization of email customer for order
 - Feature: change api plan to api restrictions
=============================================================
Version 3.5.2
=============================================================
  - BugFix: FBA orders amazon_us default address data
  - BugFix: admin orders urls fixed
  - Feature: log php error shutdown
=============================================================
Version 3.5.1
=============================================================
 - BugFix: address relay company updated
 - BugFix: parameters link in the footer
 - BugFix: nameParser typehint
 - Feature: Add phone number to the order information in the "Lengow" tab
 - Feature: Lengow tracker is disabled
=============================================================
Version 3.5.0
=============================================================
 - BugFix: Mutlishop get store on store 0
 - BugFix: fix semantic carrier matching by name and label
 - BugFix: identify business orders with payment_terms and billing infos
 - Feature: Return tracking management during shipment
 - Feature: Filters on product export page
 - Feature: Hook newOrders activation from config import orders
 - Feature: Add name parser if the only data available for customer name is fullname
=============================================================
Version 3.4.7
=============================================================
 - BugFix  : product_price, original_price in OrderDetail
 - BugFix  : synchronize order number 5 tries to API
=============================================================
Version 3.4.6
=============================================================
 - Feature : Switch env from config prod/preprod
 - Feature : partial_refunded new state to accept
 - BugFix  : flush lengow config on uninstall
 - BugFix  : add memory_limit for sync and export
=============================================================
Version 3.4.5
=============================================================

 - Feature:  Toolbox file details
 - Feature:  Log import params initialized
 - Feature:  Anonymize customer address e-mail in config
 - Bugfix:   Vat number sync update
 - Bugfix:   Order duplicate when delivery_address_id changes
 - Bugfix:   php8.1 address strings data not be null

=============================================================
Version 3.4.4
=============================================================

 - Bugfix: Fixed compatible versions to unlock module installation in Prestashop 8.0
 - Bugfix: Fixed Tools::jsonDecode() and Tools::jsonEncode() functions for json_decode() and json_encode
 - Bugfix: Fixed import of orders in Prestashop backoffice due to the error in LengowAddress.php
 - Bugfix: Fix import customer from order
 - Bugfix: Add ProductAttribute in LengowCart.php for compability for Prestashop 8 during import order in backoffice
 - Bugfix: Fix of the recording of the tracking_number when updating the status of an order on the backoffice side
 - Bugfix: Deprecated hooks update in LengowHook.php

=============================================================
Version 3.4.3
=============================================================

 - Bugfix: The display of the order list and export product catalog when mode debug of Prestashop is actived

=============================================================
Version 3.4.2
=============================================================

    - Feature: Integration of an internal toolbox with all Lengow information for support
    - Feature: Removal of compatibility with PrestaShop 1.5
    - Feature: Adding the PHP version in the toolbox
    - Feature: Modification of the fallback urls of the Lengow Help Center
    - Feature: Adding extra field update date in external toolbox
    - Feature: Regular update of marketplace data in carrier matching
    - Bugfix: Add compatibility with 1.7.8.1
    - BugFix: [Import] Loading of order types at each order synchronization

=============================================================
Version 3.4.1
=============================================================

    - Bugfix: Add compatibility with 1.7.8

=============================================================
Version 3.4.0
=============================================================

    - Feature: Integration of order synchronization in the toolbox webservice
    - Feature: Retrieving the status of an order in the toolbox webservice
    - Feature: Removal of compatibility with PrestaShop 1.4

=============================================================
Version 3.3.2
=============================================================

    - Bugfix: [export] Correction of the GET selection parameter for export

=============================================================
Version 3.3.1
=============================================================

    - Feature: Outsourcing of the toolbox via webservice
    - Feature: Setting up a modal for the plugin update
    - Feature: Addition of the JSON field in the order template
    - Bugfix: Retrieving the main shop url for feed and cron urls

=============================================================
Version 3.3.0
=============================================================

    - Feature: Integration of the new connection process

=============================================================
Version 3.2.4
=============================================================

    - Feature: [import] Add compatibility with 1.7.7.x versions of PrestaShop
    - Bugfix: [import] Correction of the SQL query retrieving the product by an attribute value
    - Bugfix: Use of the native SQL count to retrieve the number of elements in the grids

=============================================================
Version 3.2.3
=============================================================

    - Feature: Adding new links to the Lengow Help Center and Support
    - Feature: Adding customer_vat_number in admin order detail & lengow_order table
    - Bugfix: Always load iframe over HTTPS

=============================================================
Version 3.2.2
=============================================================

    - Feature: [import] Addition of order types in the order management screen
    - Feature: [import] Addition of the currency conversion option in the order option panel
    - Feature: [import] Integration of the region code in the delivery and billing addresses
    - Bugfix: Add isset in case some var were unset causing multiple php notice
    - Bugfix: Update of the access token when recovering an http 401 code

=============================================================
Version 3.2.1
=============================================================

    - Bugfix: [export] Change condition for find combinations attributes
    - Bugfix: Addition of the http 201 code in the success codes

=============================================================
Version 3.2.0
=============================================================

    - Feature: Adding compatibility with php 7.3
    - Feature: [import] Protection of the import of anonymized orders
    - Feature: [import] Protection of the import of orders older than 3 months
    - Feature: Refactoring and optimization of the connector class
    - Feature: Optimization of API calls for synchronisation of orders and actions
    - Feature: Display of an alert when the plugin is no longer up to date
    - Feature: Renaming from Preprod Mode to Debug Mode
    - Bugfix: Refactoring and optimization of dates with the correct locale
    - Bugfix: [import] Enhanced security for orders that change their marketplace name

=============================================================
Version 3.1.4
=============================================================

    - Bugfix: [import] Removed compatibility with version 3 of the Mondial Relay plugin

=============================================================
Version 3.1.3
=============================================================

    - Feature: [import] vat number registration on delivery and billing addresses
    - Bugfix: Recovery of all active shops when searching by token
    - Bugfix: Implementation of dynamic link in templates for JS and CSS scripts
    - Bugfix: [import] Use of semantic matching when shipping methods are empty

=============================================================
Version 3.1.2
=============================================================

    - Feature: [export] Refactoring and optimization of product data recovery
    - Feature: [export] Add isbn field in export feed for PrestaShop 1.7
    - Bugfix: [import] Moving email templates for PrestaShop 1.7
    - Bugfix: Fixed the module update process

=============================================================
Version 3.1.1
=============================================================

    - Feature: [import] Optimization of the order recovery system
    - Feature: [import] Setting up a cache for synchronizing catalogs ids
    - Feature: [action] Refactoring and optimization of actions on orders
    - Feature: [import] Add semantic search for carrier match
    - Feature: [export] Add price_wholesale field in export feed
    - Bugfix: [import] Updating Mondial Relay data in the specific plugin table
    - Bugfix: [export] Display of disabled products in grid and counter

=============================================================
Version 3.1.0
=============================================================

    - Feature: Registering marketplace data in a json file
    - Feature: Optimization of API calls between PrestaShop and Lengow
    - Feature: Disabling the Lengow tracker and changing the product ID
    - Feature: Add meta_title field in legacy export feed
    - Bugfix: [export] Improved deletion of duplicate headers
    - Bugfix: [action] Management of orders waiting to return from the marketplace
    - Bugfix: count() parameter must be an array for php 7.2
    - Bugfix: [action] Caching legacy export data
    - Bugfix: Correction on the number of products per page in the pagination
    - Bugfix: Update of the lengow_order table directly after the creation of the PrestaShop order

=============================================================
Version 3.0.5
=============================================================

    - Bugfix: [export] Deleting duplicate fields case-sensitive
    - Bugfix: [export] Add default value for excluded products array
    - Bugfix: [export] Recovering parent images when no image is selected for the combination
    - Bugfix: [export] Retrieving a default id_lang for product selection

=============================================================
Version 3.0.4
=============================================================

    - Feature: Adding links to the new Lengow help center
    - Feature: [action] Generating a generic error message when the Lengow API is unavailable
    - Bugfix: [export] Data recovery for custom fields
    - Bugfix: [import] Send email enabled for the report email
    - Bugfix: [import] Array to string conversion for order comments
    - Bugfix: [export] Modifying Legacy Fields for Image Fields
    - Bugfix: [import] New parameter in updateQty() function for version 1.7.3
    - Bugfix: [import] Improved security to avoid duplicate synchronization
    - Bugfix: [export] Product is ignored when it has faulty combinations

=============================================================
Version 3.0.3
=============================================================

    - Feature: [import] Improved logs on carrier matching
    - Bugfix: Switching from boolean to integer for checkbox type data
    - Bugfix: Additional verification for non-case sensitive Databases
    - Bugfix: [export] Improved recovery of the store's export settings

=============================================================
Version 3.0.2
=============================================================

    - Feature: Adding refunded status to order filters
    - Feature: Add drop-down list for number of items per page for products & orders
    - Feature: Protocol change to https for API calls
    - Feature: Implementation of the matching of shipping methods in carrier matching
    - Feature: Managing delivery_date and custom_carrier parameters for sending action
    - Feature: Check and complete an order not imported if it is canceled or refunded
    - Bugfix: Optimizing the display of errors in the order screen
    - Bugfix: [action] Correction of the dates of creation and update of the actions
    - Bugfix: [action] Deleting the shipping_date parameter in the action check request
    - Bugfix: Deleting the indefinite index user_id in the connector
    - Bugfix: Language code recovery for the Lengow iframe
    - Bugfix: Lengow multi-tab installation problem in the PrestaShop menu

=============================================================
Version 3.0.1
=============================================================

    - Feature: Complete refactoring of the installation and update processes
    - Bugfix: Add indexes to database to speed up the display of product and order grids
    - Bugfix: [export] Migration from global product selection to a multi-shop selection
    - Bugfix: [export] Retrieval of all export settings for older versions
    - Bugfix: [export] Recovering the right shop name for older versions
    - Bugfix: [export] Change image_product attribute for legacy fields
    - Bugfix: [action] Removing of action errors when orders are completed

=============================================================
Version 3.0.0
=============================================================

    - Feature: Full rewrite for the new platform Lengow
    - Feature: Add new lengow Dashboard
    - Feature: Add new product selection by shop
    - Feature: Add new lengow order with new page
    - Feature: Add new help page
    - Feature: Add new Toolbox page with all Lengow data
    - Feature: Add new legals page
    - Feature: Add new lengow simple tag
    - Feature: Add new carrier matching
    - Feature: New lengow settings with cleaning old options
    - Feature: Creating new accounts directly from the module
    - Feature: Management actions and error return
    - Feature: Add new actions: re-import, resend and re-sync orders

=============================================================
Version 2.2.11
=============================================================
    - [action] Sending an action in v3 only for orders imported in v3
    - Registering marketplace data in a json file
    - [import] Enhanced security to avoid duplicate orders following v2 / v3 migration
    - [import] Order management with multiple packages per address
    - [export] Change declaration for the setMedia() function for php 7.2
    - [export] Transforming the weight into float variable for php 7.2

=============================================================
Version 2.2.10
=============================================================
    - [export] Added product URL rewriting compatibility with PrestaShop > 1.7.1
    - [import] Added compatibility with the new 4.x version of the Colissimo Simplicite plugin
    - [export] Get a specific ean, upc or reference when product attribute is equal at 1
    - [import] Get delivery phone number when billing and shipping addresses are the same
    - [import] If orderDate -> comments is an empty array
    - Check IP server for the checklist of plugin configuration check
    - Adding class definition for lengow_logs_import for compatibility with version 1.7.4
    - Added a new parameter in the updateQty() function for compatibility with version 1.7.3
    - [export] Deleting a product from the Lengow selection directly in SQL query
    - Adding links to the new Lengow help center

=============================================================
Version 2.2.9
=============================================================

    - Bugfix: [export] Retrieval of the Lengow selection for the multi-shop
    - Bugfix: [export] Get specific product images by shop
    - Bugfix: [action] Get additional argument if argument list is empty
    - Bugfix: [action] Recovery and display of the return of the api
    - Bugfix: [action] Don't send default value for additional argument

=============================================================
Version 2.2.8
=============================================================

    - Bugfix: [import] Stop import for order without billing address
    - Bugfix: [import] Get a specific state for order shipping by marketplace

=============================================================
Version 2.2.7
=============================================================

    - Feature: [action] Add new carrier_name parameter for action
    - Bugfix: [import] Fix bug import order with tracking data

=============================================================
Version 2.2.6
=============================================================

    - Feature: [export] Get a specific format for csv stream
    - Feature: [import] Get product when reference begins by numbers
    - Bugfix: Change tabs attribute by lengowTabs for PrestaShop 1.7.1.1
    - Bugfix: [import] Delete import for orders in accepted status

=============================================================
Version 2.2.5
=============================================================

    - Feature: Rewrite for compatibility with the new PrestaShop validator
    - Feature: Deleting the override folder for compatibility with PrestaShop 1.7
    - Bugfix: [export] Add anchor for product url
    - Bugfix: [export] Add export features option for PrestaShop 1.4
    - Bugfix: [import] Add a correction for dom colissimo
    - Bugfix: [import] No translation in lengow log controller
    - Bugfix: [action] Get marketplace return and errors in logs
    - Bugfix: [action] Notice for default value in description argument
    - Bugfix: [tracking] Add a correction for PrestaShop 1.7 in hookOrderConfirmation

=============================================================
Version 2.2.4
=============================================================

    - Feature: [import] Add new verification for create generate email function
    - Feature: [import] Add new root for synchronise order with Lengow
    - Feature: [import] Add new verification in matching products for compatibility with older versions of Lengow plugins
    - Feature:[import] Add new function to removes non-Lengow products from cart
    - Bugfix: [import] Change $this by $this→id in changeIdOrderState() for Presta 1.4
    - Bugfix: [action] Fix bug with order action root

=============================================================
Version 2.2.3
=============================================================

    - Feature: Add new parameters in marketplace call action with default value
    - Bugfix: Change simple tag with new variables
    - Bugfix: [import] Get full_name field in order API

=============================================================
Version 2.2.2
=============================================================

    - Feature: [import] Add new legacy_code in marketplace class
    - Feature: Add compatibility with PrestaShop 1.7

=============================================================
Version 2.2.1
=============================================================

    - Feature: [import] Import process compatible with v2 and v3 platform Lengow at the same time

=============================================================
Version 2.2.0
=============================================================

    - Feature: [import] Compatibility V2/V3 for import process

=============================================================
Version 2.1.1
=============================================================

    - Feature: [export] Better data cleansing for Export
    - Bugfix: [export] Fix stream with false columns -> empty attributes
    - Bugfix: [export] Fix setAdditionalFields() and override
    - Bugfix: [export] Fix selection of default fields if no field is selected
    - Bugfix: [import] Fix import of several times the same product
    - Bugfix: [import] Fix Importing more than one product (version 1.4)
    - Bugfix: [import] Fix reimport order option
    - Bugfix: [import] Fix notice index not found in getStateLengow()

=============================================================
Version 2.1
=============================================================

    - Feature: Full rewrite of import and export processes
    - Feature: [import] Compatibility with advanced stock management
    - Feature: [export] New LengowFeed class
    - Feature: [export] New LengowFile class
    - Feature: Security updates - SQL injection
    - Feature: [import] Search markeplace carrier improved
    - Feature: Compatibility with PrestaShop Cron Editor
    - Feature: Better exception management
    - Feature: New log writing process
    - Feature: [export] Export in file improved
    - Feature: Create interface LengowObject
    - Feature: Compatibility with Prestasop 1.4 improved
    - Feature: [export] Export selected features
    - Feature: [import] Validate order process rewritten for orders imported
    - Feature: [import] Removal of "Force prices" option
    - Feature: Removal of Feed Management
    - Feature: [import] Order comment from marketplaces imported
    - Bugfix: [import] Fix Reset lengow import

=============================================================
Version 2.0.9
=============================================================

    - Feature: [import] Better compatibility with relay (SoColissimo & Mondial Relay)
    - Feature: [export] Export with timeout
    - Feature: [import] Multiple report email address option
    - Feature: File manager and loader

=============================================================
Version 2.0.7
=============================================================

    - Feature: [import] Possibility of importing a chosen order
    - Feature: [import] Force import orders shipped by markeplaces option
    - Feature: Check override folder
    - Feature: [import] Matching with marketplace shipping method option
    - Feature: [import] Fictitious email option
    - Feature: [import] Import report email option
    - Bugfix: Fix of several minor bugs

=============================================================
Version 2.0.5.3
=============================================================


    - Feature: Improve webservice (Show module version, prestashop version, get last logs)
    - Feature: [import] Add option to ignore processing fee
    - Feature: Tagcapsule - Take the first group
    - Feature: Add webservice to migrate products selection from module v1
    - Feature: Write un temp file during export
    - Bugfix: [export] Fix stream param for export
    - Bugfix: Fix warning with 1.4 version
    - Bugfix: Fix tracking amazon and cdiscount
    - Bugfix: Fix bug with tagcapulse

=============================================================
Version 2.0.5.1
=============================================================

    - Feature: [import] Force product option improvements
    - Feature: Add new internal IPS
    - Feature: [import] Disable mail during import
    - Bugfix: [export] Fix image export
    - Bugfix: Fix warning with tagcapsule
    - Bugfix: Fix bug with 1.4 installation

=============================================================
Version 2.0.5
=============================================================

    - Feature: Add option show or not feed management
    - Feature: Send tracking when it's filled
    - Feature: [export] Add function to add value in export
    - Feature: Use set instead of updateValue in Configuration for mail
    - Feature: Now compatible with PrestaShop 1.6
    - Feature: Improve zipcode verification
    - Feature: Improve logs report
    - Feature: Add try/catch on validateOrder
    - Feature: Add posibility to hide feed management
    - Bugfix: [import] Fix Payment error
    - Bugfix: [import] Fix wrong carrier after import
    - Bugfix: [import] Fix import bug with minimal quantity

=============================================================
Version 2.0.4.4
=============================================================

    - Feature: Add option show or not feed management
    - Feature: [export] Add option to merge parent and children image

=============================================================
Version 2.0.4.3
=============================================================

    - Feature: [import] Display message when order is already processing
    - Feature: Choose ID Product for tagcapsule
    - Feature: Traductions
    - Feature: [import] Matching carrier

=============================================================
Version 2.0.4.2
=============================================================

    - Feature: Add logs tab in B.O
    - Feature: [import] Full debug mode during import

=============================================================
Version 2.0.4.1
=============================================================

    - Feature: [import] Update debug message
    - Feature: [import] Add Hook ValidateLengowOrder
    - Feature: [import] public function hookActionValidateLengowOrder
    - Feature: [import] Add ZipCode validation
    - Feature: [import] Allow to export only product when specify IDs
    - Feature: [import] Update webservices log import
    - Bugfix: [export] Fix publish/Unpublish product in lengow tab
    - Bugfix: Fix Tagcapsule

=============================================================
Version 2.0.4
=============================================================

    - Feature: Warning if plugin is outdated
    - Feature: [import] Block import if another is already running
    - Feature: Always show IP Address in checklist configuration
    - Feature: Now save mail settings in database
    - Feature: [import] Skip order if is already imported or stopped during process
    - Feature: [import] Add import log table
    - Feature: [import] logs action in webservice Lengow
    - Feature: [import] Add option to force import disabled product or out of stock
    - Feature: [import] Now compatible with soColissimo
    - Feature: [import] Matching API Field for better product search
    - Feature: [import] Prevent PrestaShop bug when advanced stock is enabled
    - Feature: [import] Force carrier is modified by ValidateOrder
    - Feature: [import] Block commands when product came from different warehouse
    - Feature: [import] Add compatibility with Mondial Relay
    - Feature: [import] Add order action to reimport order
    - Feature: [import] Add order action to synchronize IDs
    - Bugfix: Fix with tagcapsule
    - Bugfix: [import] Fix bug with tax on invoice details
    - Bugfix: Fix fatal error when uninstalling module in 1.4 version
    - Bugfix: Fix bug during 1.4.2.5 installation
    - Bugfix: Fix update tracking info on Amazon

=============================================================
Version 2.0.3.2
=============================================================

    - Feature: [import] Lastname and firstname inverted during import in delivery address
    - Feature: [import] Add try/catch during import
    - Feature: [import] Replace DB::getValue by ExecuteS in import
    - Feature: [import] Search product by UPC, SKU, ID, Reference
    - Bugfix: Fix bug with tagcapsule (Confirmation and payment)
    - Bugfix: [import] Fix Clean phone method during import

=============================================================
Version 2.0.3
=============================================================

    - Feature: Allow override
    - Feature: Add check configuration
    - Feature: [export] Allow export feature without product variations
    - Feature: Add log api
    - Feature: Add log files list in admin modules
    - Bugfix: [export] Fix - Possible warning during export
    - Bugfix: [import] Fix - Disable mail during import (1.4)

=============================================================
Version 2.0.2
=============================================================

    - Feature: [export] Allow to export disabled products
    - Feature: [export] Add manual export get parameters
    - Feature: [export] Add attribute list selection to export
    - Bugfix: [import] Fix - Import product with ID, EAN and SKU
    - Bugfix: Fix - SSL mode on tracker
    - Bugfix: Fix - Url export and import where multishop is active
    - Bugfix: Fix other minors bug

=============================================================
Version 2.0.1
=============================================================

    - Feature: [import] Add cron to import
    - Bugfix: Fix - Tracker
    - Bugfix: Fix other minors bug
