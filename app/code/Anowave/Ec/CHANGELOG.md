# Changelog

All notable changes to this project will be documented in this file.

##[16.0.2]

### Fixed

- Add to cart not firing for bundle product type

##[16.0.1]

### Added

- Extended support for static brands (text fields)

##[16.0.0]

### Fixed

- Changed default bind to "Use jQuery() on". Deprecated binding using onclick attribute
- Minor improvements in licensing instructions

##[15.0.9]

### Fixed

- Extended support for unicode characters (Greek, Arabic etc.) + Reduced JSON payload size for unicode characters

##[15.0.8]

### Fixed

- Minor updates

##[15.0.7]

### Fixed

- ReferenceError: data is not defined (on product click)

##[15.0.6]

### Fixed

- Invalid entity_type specified: customer error while running: php bin/magento setup:install

##[15.0.5]

### Fixed

- Wrong 'value' parameter at InitiateCheckout (Facebook Pixel tracking)

##[15.0.4]

### Added

- Ability to send transactions to Google Analytics via Mass Actions (Order Grid)

##[15.0.3]

### Fixed

- Updated dependent Anowave_Package extension to remove Undefined offset 1 error.

##[15.0.2]

### Fixed

- Bug related to cancellation of pending orders.

##[15.0.1]

### Fixed

- Minor updates in localStorage feature

### Added

- Ability to show/hide remove from cart confirmation popup

##[15.0.0]

### Added

- Backreference for categories based on localStorage. Allows for assigning correct category in checkout push (e.g. category from which product was added in cart). Fixes multi-category products issues.

##[14.0.9]

### Added

- Optional order cancel tracking / Ability to disable order cancel tracking

##[Events]

ec_get_widget_click_attributes 			- Allows 3rd party modules to modify widget click attributes e.g. data-attributes="{[]}"
ec_get_widget_add_list_attributes 		- Allows 3rd party modules to modify widget add to cart attributes e.g. data-attributes="{[]}"
ec_get_click_attributes 				- Allows 3rd party modules to modify product click attributes e.g. data-attributes="{[]}"
ec_get_add_list_attributes 				- Allows 3rd party modules to modify add to cart from categories attributes e.g. data-attributes="{[]}"
ec_get_click_list_attributes 			- Allows 3rd party modules to modify category click attributes e.g. data-attributes="{[]}"
ec_get_remove_attributes				- Allows 3rd party modules to modify remove click attributes e.g. data-attributes="{[]}"
ec_get_add_attributes					- Allows 3rd party modules to modify add to cart attributes e.g. data-attributes="{[]}"
ec_get_search_click_attributes			- Allows 3rd party modules to modify search list attributes e.g. data-attributes="{[]}"
ec_get_checkout_attributes 				- Allows 3rd party modules to modify checkout step attributes e.g. data-attributes="{[]}"
ec_get_impression_item_attributes		- Allows 3rd party modules to modify single item from impressions
ec_get_impression_data_after			- Allows 3rd party modules to modify impressions array []
ec_get_detail_attributes				- Allows 3rd party modules to modify detail attributes array []
ec_get_impression_related_attributes	- Allows 3rd party modules to modify related attributes
ec_get_impression_upsell_attributes		- Allows 3rd party modules to modify upsell attributes
ec_get_detail_data_after				- Allows 3rd party modules to modify detail array []
ec_order_products_product_get_after		- Allows 3rd party modules to modify single transaction product []
ec_order_products_get_after				- Allows 3rd party modules to modify transaction products array
ec_get_purchase_attributes				- Allows 3rd party modules to modify purchase attributes
ec_get_search_attributes				- Allows 3rd party modules to modify search array attributes
ec_api_measurement_protocol_purchase	- Allows 3rd party modules to modify payload for measurement protocol


##[14.0.8]

### Added

- Added selectable brand attribute in Stores -> Configuration -> Anowave Extensions -> Google Tag Manager -> Enhanced Ecomerce Tracking Preferences

##[14.0.7]

### Added

- gtag.js based AdWords Conversion Tracking

##[14.0.6]

### Changed

- Minor updates and tidying system options (for better usability)

##[14.0.5]

### Changed

- Refactored Cookie consent feature to load via AJAX (overcome FPC related issues)

##[14.0.4]

### Fixed

- Typo addNoticeM() to addNoticeMessage() in credit memos

##[14.0.3]

### Fixed

- Illegal string offset 'qty' error related to Gift cards 

##[14.0.2]

### Added 

- Added new custom event - ec_order_products_get_after
- Added new custom event - ec_get_purchase_attributes

##[14.0.1]

### Added

- ec_get_detail_data_after event

##[14.0.0]

### Added

- Transaction reversal
- Adjustable ecomm_prodid attribute. Can be now ID or SKU depending on configuration 

##[13.0.9]

### Added 

- Ability to customize cookie consent dialog.

##[13.0.8]

### Added

- GTM frienldy, built-in Cookie Law Directive Consent
- Adjustable tax settings 

##[13.0.7]

### Changed 

- Minor updates

##[13.0.6]

- Fixed problems with products distributed in categories from different stores. 

##[13.0.5]

## New

- Added 3rd step "Place order" in checkout step tracking. This is to confirm whether customer actually clicked "Place order" button. 
Existing funnel step labels (Google Analytics -> Admin -> E-Commerce -> Funnel step labels) should be updated to:

a) Step 1 (Shipping address)
b) Step 2 (Review & Payments)
c) Step 3 (Place order) 

## Added

- Non-cached private data pushed to dataLayer[] object (beta feature, to be evolved in future)
- Click/Add to cart tracking for homepage widgets (NewProduct widget)
- New selectors for homepage widgets tracking
- Fixed empty widget scenario
- Custom cache for homepage widgets
- New tag (EE NewProducts View)
- New tigger (Event Equals NewProducts View Non Interactive)

##[13.0.4]

## Fixed

- Fixed Fatal error in detail page for products unassigned to any category

##[13.0.3]

## Changed

- Cast 'price','ecomm_pvalue','ecomm_totalvalue' to float insetad of strings. Values are also no longer single quoted.

##[13.0.2]

## Fixed

- Added missing namespace declarations in vendor/Google API

##[13.0.1]

## Fixed

- Cast ecomm_total value to numeric (Facebook Pixel)

##[13.0.0]

## Changed

- Refactored the Google Tag Manager API library

##[12.0.0]

## Changed 

- Updated Google Tag Manager API to use Google Analytics Settings variable for all tags (common)
- Removed unused API files

##[11.0.9]

### Fixed

- Fixed fatal error for Out of stock grouped products
- Refactored/removed direct calls to ObjectManager

##[11.0.8]

### Fixed

- Cast ecomm_totalvalue to float in cart page to remove quotes

##[11.0.7(6)]

### Fixed

- Missing brand value in checkout push

##[11.0.5]

### Fixed

- Fixed stackable "Add to cart" products array.
- Fixed incorrect grouped products array [] passed with addToCart event

##[11.0.4]

### Checked

- Checked Magento 2.2.x compatibility. 

### Fixed

- Fixed wrong product category in Search results. 

##[11.0.3]

### Added

- Flexible affiliation tracking (NEW)

### Fixed

- Fixed Payment method selection not working when module is disabled from configuration screen

##[11.0.2]

### Changed

- Refactored ObjectManager calls
- Disabled "Add to cart" from lists for configurable/grouped products with required variants/options

##[11.0.1]

### Changed

- Refactored to use mixins instead of rewrite in terms of shipping/payment method tracking

##[11.0.0]

### Fixed

- Visual Swatches price change not working in previous version

##[10.0.9]

### Changed

- Minor updates, added a few self-checks regarding module output
- Added self-check regarding 3rd party checkout solutions

##[10.0.8]

### Added

- Added Google Analytics Measurement Protocol / Offline orders tracking

##[10.0.7]

### Added

- Added ability to create Universal Analytics via the API itself.

##[10.0.6]

### Fixed

- Added afterFetchView() method in app\code\Anowave\Ec\Block\Result.php

##[10.0.5]

### Fixed

- Changed from getBaseGrandTotal() to getGrandTotal() at success page to obtain correct revenue correlated to selected currency

##[10.0.5]

### Fixed

- Added missing 'value' parameter on InitiateCheckout (Facebook Pixel)

##[10.0.4]

### Fixed

- Improved Product list attribution / Product position to event correlation
- Fixed wrong remove from cart ID

##[10.0.3]

### Added

- Ability to switch between onclick() binding and jQuery on() binding to increase support for 3rd party AJAX based solutions

##[10.0.2]

### Added

- Added Facebook Pixel Search

##[10.0.1]

### Added

- Cart update tracking (smart addFromCart and removeFromCart)
 
##[9.0.8]

###Added

- Combined product detail views with Related/Upsells/Cross-Sell impressions

##[9.0.7]

###Added

- Mini Cart update tracking (smart addFromCart and removeFromCart)

##[9.0.6]

###Changed

- Cleanup

##[9.0.5]

###Changed

- Refactored DI() (more)

##[9.0.4]

###Changed

- Refactored DI()

##[9.0.3]

###Changed

 - Added explicit "Adwords Conversion Tracking" activating. All previous versions MUST enable it to continue using AdWords Conversion Tracking

##[9.0.2]

### Fixed

- Non-standard Facebook Pixel ViewCategory event
 
##[9.0.1] 

### Changed

### Fixed

- Unable to continue to Payment if license is invalid
- Removed AEC.checkoutStep() method and created AEC.Checkout() with step() and stepOption() methods

##[9.0.0] - 13.06.2017


## [8.0.9] - 07.06.2017

### Added

- controller_front_send_response_before listener to allow for response modification in FPC

## [8.0.8] - 07.06.2017

### Fixed

data-category attribute in "Remove from cart" event

## [4.0.3 - 8.0.7] - 07.06.2017

### Added

- Contact form submission tracking
- Newsletter submission tracking

## [4.0.3]

### Fixed

- Shipping and payment method options tracking for Magento 2.1.3+

## [4.0.2]

### Added

- Added custom cache for categories, minor improvements

## [2.0.8]

### Changed

- GTM snippet insertion approach to match the new splited GTM code. May affect older versions if upgraded.

## [2.0.1 - 2.0.3]

### Fixed

- Incorrect configuration readings in multi-store environment.

## [2.0.0]

### Added

- "Search results" impressions tracking.

## [1.0.9]

### Fixed

- Fixed bug(s) related to using both double and single quotes in product/category names

## [1.0.0]

- Initial version