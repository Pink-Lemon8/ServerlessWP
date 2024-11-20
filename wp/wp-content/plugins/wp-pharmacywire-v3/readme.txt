=== WP PharmacyWire V3 ===
Requires at least: 5.0
Requires PHP: 7.3
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Plugin to provide E-Commerce capability on Wordpress for PharmacyWire using Metrex PharmacyWire XMLConnect API.

== Description ==

Since 2002, [PharmacyWire](https://www.pharmacywire.com/ "PharmacyWire") has been enabling mail order and online medical dispensaries to deliver medications around the world. This pharmacy platform was designed for the complex interactions between patients, doctors, pharmacists and distribution centres that don't exist in fulfilment centre software. To deliver medications safely to patients using software that scales with your distribution centres and feature requirements, PharmacyWire delivers.

While the XML Connect API offers the ability to create an entirely custom site from the ground up, the WordPress PharmacyWire (V3) plugin is a fast way to get up and running & still provides many ways to customize the shopping cart to meet your needs.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/wp-pharmacywire-v3` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Use the PharmacyWire->Settings Name screen to enter & save your XML Connect API credentials provided to you from Metrex. We will provide you with a set of credentials. Credentials for a dev site, and live credentials for when your website is launched.
4. Go to PharmacyWire->Catalog and click 'Refresh Catalog' to pull your PharmacyWire drug catalog into WordPress.
5. Review PharmacyWire->Store & PharmacyWire->Catalog settings to enter our Pharmacy information and configure as desired
7. In PharmacyWire->Email edit the new customer email template.
8. Place test orders to see initial cart functionality. Then proceed to work on customizing the theme.

== Frequently Asked Questions ==

= Products are not up to date or don't show on the website =

Refresh your catalog in PharmacyWire->Catalog->Refresh Catalog in order to pull in the latest changes from PharmacyWire.
At the top of PharmacyWire->Catalog you can schedule ongoing catalog updates (recommended to run during the night / outside of business hours).

= How do I customize templates =

Templates can be copied from the plugin's wp-pharmacywire/Themes/templates directory to your theme. Inside your theme create the folder structure {ThemeName}/pharmacywire/templates/ and place a copy of the template there (maintaining the template name). This local copy will be loaded in place of the plugin's with any changes you do to it.

Please refer to the PharmacyWire plugin documentation or request a copy from us if you do not have one.

= What Shortcodes are Available? =
See: <a href="https://www.pharmacywire.com/integrations/pharmacywire-shortcodes/" targe="_blank">Plugin Shortcodes</a>

== Changelog ==

**Note: Test changes on a dev server prior to updating your live site.** *Any line numbers for templates are provided as a rough guideline and are relevant for that releases files only. Future releases those line numbers may change as the files are updated. Do a diff file comparison to compare new templates with old when updating local templates and contact brian@metrex.net if you have questions.*

= 3.9.250 - March 15, 2023 =
* Updated `cart.coupon_discount` variable that was added in `3.9.200` for percentage based discounts.

= 3.9.230 - Feb 27, 2023 =
* Added Wordpress timezone (Settings -> Timezone) to catalog update completion response.
* Various bug fixes & improvements.

= 3.9.220 - Feb 9, 2023 =
* Further improvements to the Jan 14 `3.9.100` product search filter algorithm.

= 3.9.210 - Feb 8, 2023 =
* Updated Drug schema on product pages to include offers to satisfy new Google Drug schema validation rules, template updated: `page_search_detail.phtml` (line: 153-166). Please review the implementation & update your theme template accordingly to include the drug schema offers property.
* Further improvements to the Jan 14 `3.9.100` product search filter algorithm.

= 3.9.200 - Feb 3, 2023 =
* Modified the `pwire:cart:orderSubmitted` event hook (See page 17 of documentation) orderResponse `cart.order_total` to take into account the coupon discount.
	* Added new `cart.sub_total` and `cart.coupon_discount` variables to that response.
	* If you were doing any custom calculations to factor in the coupon discount to the `order_total` value, please update scripts accordingly as it is now done for you.

= 3.9.100 - Jan 14, 2023 =
* Added to Catalog -> Product Settings a filter/option to limit the PharmacyWire plugin search to specific post types rather than the default behavior of searching all post types when looking for custom page content overrides (custom created pages that show in place of standard/generic plugin search results). This can help alleviate some potential conflicts where the search finds non-product pages inadvertantly (e.g. blog post) when it searches for post titles that start with matching drug names. Also modified the query so Product pages are returned / have priority first when it does the lookup.

= 3.9.96 - Jan 13, 2023 =
* Various bug fixes & improvements.

= 3.9.95 - Jan 12, 2023 =
* Various bug fixes & improvements.

= 3.9.90 - Dec 14, 2022 =
* Javascript fix, template: `page_register_success.phtml`
	* Updated deprecated `jQuery('window').load ...` call to `jQuery('window').on('load') ...` used to trigger account registration javascript hooks in template.
* Minor/non-breaking template html validation cleanup:
	* `quick_login.phtml` - adjusted username & password label nesting.
	* `page_change_password.phtml` - removed duplicate id="action".
	* `page_checkout_cart.phtml` - removed inline width on coupon-code, tag-code input.
	* `page_login.phtml` - removed redundant method on form. Removed alt attribute from submit input. Added id to username to pair with label.
	* `page_register.phtml` - Added autocomplete="username" to username/confirm-username input. Removed redundant class attribute on username fields.
	
Update any custom templates accordingly.

= 3.9.83 - Nov 16, 2022 =
* Added a filter to override order status displayed on your website with a label of your choosing. In your WordPress websites `wp_options` table, you can now add an `option_name` of `pw_order_status`. This accepts a JSON object, with key `filter` containing an array of objects or a single object. For example: `{"filter" : [{"status" : "On Hold", "label" : "Ordered"},{"status" : "Ordered", "label" : "Order Placed"}]}` or `{"filter" : [{"status" : "On Hold", "label" : "Ordered"}]}`. Standard order status includes: `Ordered, Processing, Shipping, In Transit, On Hold, Closed`. There may be others depending on your PharmacyWire setup.
* Updated template `page_profile_info.phtml` to use `it.visualstatus` instead of `it.status` and the logic on line 35 to better display: `PENDING_TRANSCRIPTION` on ajax refresh (tab changes).

= 3.9.82 - Nov 7, 2022 =
* Updated styles to help avoid some known conflicts with Bootstrap & Foundation Framework that have recently come up (either from Bootstrap itself or changes in themes that use Bootstrap):
	* Added `.pw-tooltip` class to our tooltips, which then sets opacity to 1, which prevents bootstrap from hiding them (Bootsrap sets opacity to 0 tooltip). This primarily affects the My Account page, change done in backend so no template changes required.
	* Added more specificity to our `.collapse` override css rule, so it should once again override the bootstrap `.collapse` `display: none` styles that they use for something other than what Foundation Framework uses that class for. (This is specifically limited in scope to only affect `.collapse` class styles found within the `.pw-pharmacy-wrap` container which wraps all of our page templates. We've always had an override rule for this, but the Bootstrap rule has changed in their newer releases and/or various themes, so we've now adjusted for that.)

= 3.9.81 - Nov 3, 2022 =
* Fixed mobile responsive classes for phone number fields in Create Account default template: `page_register.phtml`

= 3.9.80 - Oct 18, 2022 =
* Fixed mismatched label tag in `page_checkout_cart.phtml`, line 102
* Various bug fixes & improvements.

= 3.9.79 - Aug. 23, 2022 =
* Updated Western Australia region code: WAU to WEA to match PharmacyWire.
	* **To update:** Update the plugin. Truncate your `{wp_prefix}_pw_country_region` table. Deactivate & activate the plugin to run the table plugin install process. Then refresh your catalog. (There will be a mechanism in the plugin added at a later date to refresh the country/region lists).

= 3.9.76 - Aug. 17, 2022 =
* Added option to `pw_shipping_extended` shipping options to allow you to force addition of another product if a product of a specific tag is present on the order. Required `ITEM_data_attributes` added to `page_checkout_cart.phtml` template and custom config setup.
* Various bug fixes & improvements.

= 3.9.72 - June 20, 2022 =
* Added 'loading' spinner to the PharmacyWire autocomplete search. Can be styled/customized via css. Classes: .pw-loader-container/.pw-loader-element[.pw-loader-element also uses ::after & ::before]. Adjusting font-size on either or parent containers will adjusts the spinner size.
* Further improvements to suggestive search

= 3.9.71 - June 6, 2022 =
* Account Profile - Added an indicator icon to the recent orders status to show when a patient has a document pending transcribing. Templates updated: `page_profile_info.phtml`.
* Added an extra check to account profile to redirect to login screen on ajax requests (such as when switching tabs) if the user session has expired, rather than have the requests silently fail due to not being logged in. (note: Refreshing the webpage when the session has expired already would take the user to the login page, this change was around json requests specificly).
* Background changes to suggestive search behavior.
* Various bug fixes & improvements. 

= 3.9.6 - May 16, 2022 =
* Increased max file size on the document upload script to 10MB, up from 5MB (assuming your server is configured to handle at minimum 10MB uploads, otherwise your server limit is used if less than 10mb). Changed the page template wording from "The document size" to "Each document uploaded". Template: `page_upload_document.html`
* Corrected some erroneous classes in Create Account template and added a few new helper classes. Templates updated: `page_register.phtml`. (Special thanks to the dev that pointed them out!)
* Various bug fixes & improvements.

= 3.9.5 - Apr. 20, 2022 =
* Added new blocks: `PharmacyWire Account Tools (Block)` & `PharmacyWire Cart (Summary Block)` to support WordPress 5.9+ change to use blocks in place of widgets (The existing widgets will remain at this time). If you are having display issues on WordPress 5.9+ then you should switch your site to use these new blocks.
* Set alternate phone number to be optional when editing profile. *Template Updated:* Added `optional` class to inputs named `phoneAreaCodeDay` & `phoneDay` in `page_profile_info.phtml`.

= 3.9.0 - Mar. 1, 2022 =
* Removed email validation from login forms so that a non-email usernames can be used to login. Typically the username is an email address, which is why it was validated as such. This change allows CSR to set a custom username in special cases where required. Templates updated: `page_login.phtml`, `quick_login.phtml`
* Changed re-order prescription wording 'Previously Filled' to 'Authorized doses' for clarity. Template updated: `page_reorder.phtml`
* Added dev notes to the Plugin Cache Options section for greater visibility that should be reviewed if you use cache. (The notes are from the plugin release notes)

= 3.8.50 - Jan. 24, 2022 =
* Adjustments to drugs being displayed on re-order screen to better filter matching alternative products by country.
* Updates to widgets so that they play nicer with WordPress new block editor screen, displaying better & preventing javascript errors.
* Note: PHP 8 will not be supported until later in the year sometime after WordPress has better finalized it's own support for 8.1.

= 3.8.45 - Dec. 9, 2021 =
* Various bug fixes & improvements.

= 3.8.43 - Nov. 8, 2021 =
* Fixed formatting/white-space of `page_search_detail.phtml` drug schema so the xtemplate blocks parse properly. (If you have schema errors in your search console on the product page and are using a custom template - copy line: 82-156 `structuredData` xtemplate block in the default template to your custom template.)

= 3.8.42 - Oct. 19, 2021 =
* Improved product searchform autocomplete search algorithm.

= 3.8.40 - Sep 21, 2021 =
* Fixed display bug, where if your custom shipping config rate disabled all other available shipping rates, then on first shopping cart page load it selected that rate properly, but didn't initially update the total price display until page reload.

= 3.8.31 - Sep 9, 2021 =
* Updated product 'add to cart' button behavior/script.
* Bugfix to better search for matching custom Product pages when 'Show package name on search results' option is turned on.

= 3.8.21 - May 13, 2021 =
* Backend improvements to coupon handling.

= 3.8.20 - April 29, 2021 =
* Removed hard-coded brackets from the Cart widgets: `ajax-shopping-cart-widget.php` and `shopping-cart-widget.php`. Added brackets back in using CSS so they can be styled/changed if desired more easily.

= 3.8.19 - April 13, 2021 =
* Added option 'Show ingredients on search results' to the PharmacyWire->Catalog settings page. Added 'drug-name' helper class to wrap the drug name and 'ingredients' class to wrap ingredients to the drug search results.

= 3.8.18 - April 6, 2021 = 
* Change to better validate Coupons where Combined coupons in PharmacyWire is set to none.

= 3.8.17 - April 6, 2021 =
* Added sort for medical questionnaire display/visibility based on the question label. If the label is empty the question is not displayed on the website.

= 3.8.16 - March 18, 2021 =
* Behind the scenes improvements

= 3.8.15 - March 09, 2021 =
* Updated theme_isLoginPage method to work with the V3 plugin json shopping cart pages.

= 3.8.14 - March 08, 2021 =
* Changed ajax loading 'spinner' script enqueue handle to be more unique to avoid possibility of not getting loaded.
* Other minor improvements.

= 3.8.13 - February 22, 2021 = 
* Added external comments for use on cart page within `page_checkout_cart.phtml` - using variables `{ITEM_drug_comment}` and `{ITEM_package_comment}`.

= 3.8.12 - February 09, 2021 =
* Adjustments to cache plugin integration behavior. 

= 3.8.11 - February 08, 2021 =
* Added the checkout login page to list of pages excluded from cache.
* Removed '+' from showing in new ajax shopping cart widget for coupons with no value, such as 'free shipping'.

= 3.8.10 - February 04, 2021 =
* Fixed WordPress Site Health 'An active PHP session detected' warning
* Adds the ability for catalog update requests to be pushed/sent from PharmacyWire to you WordPress site. (See PharmacyWire release notes v5.5.2 for further explanation.)
* Fixed 'Catalog Settings' page display issue. When page is refreshed or someone else views the 'Catalog Settings' page, it should now show the catalog update status and loading spinner if the update is already underway.
* Removed some outdated classes/attributes on .drug-result container in page_search_detail.phtml default template.
* Added `pwire_logged_in` cookie when a patient is logged in.
* Added `PharmacyWire Ajax Shopping Cart` widget that dynamically pulls in the cart details after the page has finished loading via ajax.
* Added `PharmacyWire Ajax Account Tools` widget that dynamically pulls in the account menu (Create account/account, login/logout, & the cart link with item count) after the page has finished loading via ajax as well as after an order is submitted it will update to show that you are now logged in & cart empty. Elements can be shown/hidden within the widget settings and the menu displayed as vertical or horizontal.
* Added new shortcode for alternate method of displaying these widgets. See: [PharmacyWire Shortcodes](https://www.pharmacywire.com/integrations/pharmacywire-shortcodes/)
* Added `DONOTCACHEPAGE`, `DONOTCACHEOBJECT`, `DONOTCACHEDB` as well as `no-cache` headers to the shopping cart page, account page, checkout pages, register, product, search and re-order pages for plugins that support them. Product/Search cache support can be enabled in PharmacyWire->Settings.
* Plugin will clear cache after catalog update for the following most popular cache plugins: WP Rocket/W3 Total Cache/WP Super Cache/Comet Cache. Other cache solutions you would need to exclude products from being cached or manually clear after catalog updates.
* ***Note:*** Page caching is not officially supported at this time, however these changes will help move in that direction (do not enable database/object cache in particular). Due to the number of caching plugins available, if you do wish to try out caching, ***please configure and test accordingly to ensure that data you do not wish to be cached is not being cached.*** Depending on your approach, you may need to manually exclude the above outlined pages and some plugins allow you to exclude caching when the `pwire_logged_in` cookie is set.

= 3.8.02 - December 16, 2020 =
* Added new Hours of Operation field in WordPress PharmacyWire->Store settings. In WordPress display using the shortcode: `[PharmacyWire storeinfo="hours_of_operation"]` or in PHP templates `echo do_shortcode('[PharmacyWire storeinfo="hours_of_operation"]');`. The rest of our store info shortcode options can be seen here: [PharmacyWire Shortcodes](https://www.pharmacywire.com/integrations/pharmacywire-shortcodes/).

= 3.8.01 - December 14, 2020 =
* Fixed missing delimeter error log warning for Storeinfo URL shortcode.
* Behind the scenes improvements

= 3.8 - December 7, 2020 =
* This release updates the PharmacyWire plugin scripts to work with & is *required* for WordPress 5.6.
* With the WordPress 5.6 release including jQuery library changes it is strongly recommended to update your plugins and test on a dev site before updating to 5.6 on live (as is always the case, but this has potential to cause more site breaking issues as was the case with the Wordpress 5.5 release and the upcoming 5.7 release that includes the final migration steps to jQuery 3.5.1 and the removal of jQuery Migrate).
* Added default catalog updated time of 3am system time if scheduled catalog updates are enabled, but no time set.
* Added option to disable 'Forgot Password' from being sent from the WordPress plugin. Note: please leave this enabled at this time until you have confirmed and tested forgot password emails are coming from PharmacyWire. This is for future support. The long-term goal is for all emails to be coming from PharmacyWire.
* UI improvement - Removed 'Continue Shopping' from shopping cart widget when on final checkout pages.
* UI improvement - Fixed a bug where deleting an address on the checkout screen address editor would reset the selected/displayed address back to the default address requiring the extra step of having to select a different adress again. Now if the selected address isn't the one being deleted it maintains that selection.

= 3.7 - November 26, 2020 =
* Streamlined backend XML requests sent to PharmacyWire XMLConnect.

= 3.6.22 - November 5, 2020 =
* Added option in PharmacyWire->Email settings to enable/disable sending of the Welcome email from the WP PharmacyWire V3 plugin (default remains as enabled).
* Made the search shortcode strength filter case insensitive and fixed a bug so it will work with slashes (eg. 100u/ml will now find 100u/mL).
* Bug fix for coupon description being displayed twice when coupons are added using the new coupon shortcode in conjunction with a mandatory coupon already set.

= 3.6.21 - October 27, 2020 =
* Adjustments to JsonApi 'get-cart' response to take coupons into account for total variable.

= 3.6.20 - October 21, 2020 =
* Added a new coupon shortcode for adding/removing coupons on WordPress pages or in PHP via `do_shortcode()`.  
	**Coupon shortcode format:** `[PharmacyWire coupon="{coupon code}" action="{apply/remove}"]`  
	(an action of `apply`, `add` or omitting the action parameter (`[PharmacyWire coupon="{COUPON CODE}"]`) will apply the coupon to the cart)  
	**WordPress example:** `[PharmacyWire coupon="FIRST20" action="add"]`  
	**PHP example:** `do_shortcode('[PharmacyWire coupon="FIRST20" action="add"]')`
* Change to better avoid potential issues finding a drug page containing a forward slash '/' in drug names, where custom drug pages weren't made.
* Added an extra checks to only run the `[PharmacyWire emailtest_address="{email address}"]` shortcode from the frontend, not when editing the page that has the shortcode.

= 3.6.12 - October 13, 2020 =
* Change to submit custom payment billing institution as label (full name) rather than the generated code.

= 3.6.11 - October 13, 2020 =
* Minor display change to hide coupon description for mandatory coupons where lable is set to be the same as the description.

= 3.6.1 - October 13, 2020 =
* Added Custom Payment Processor option to the store settings. Custom payment process will simply be submitted as draft with an institution that matches the provider name entered. This is to go along with any custom integration your developers have done on the frontend website with the 3rd party payment provider. Your management of payments, etc. with that payment provider then takes place external to PharmacyWire. The `quick_checkout.phtml` will have a javascript generated div with the class name of `billing_method_{payment code}` and an inner content container of `billing_method_content` ('payment code' to be used is displayed after saving your custom payment processor settings). This div is ready/available by listening for the `pwire:cart:billingMethod_{payment code}` event trigger in javascript and calling your function from there. Alternatively you can have a custom `quick_checkout.phtml` template and hardcode the same content block structure (including classes) which is easier, but requires more maintenance keeping that template up to date with the latest changes, such as:
* *Template Update* - `quick_checkout.phtml` - Changed 'billing_method_select' class on outer div container line 274 to 'billing_method_radiogroup' to avoid conflicts with the nested input class. Other minor changes. If you have a custom `quick_checkout.phtml` template please make that update.
* Foundation-sites library updated to 6.6.3

= 3.6 - September 28, 2020 = 
* Contains numerous backend code improvements/cleanup.
* You should now be able to do development work with WP_Debug enabled if desired. If you stumble upon any remaining debug notices from this plugin in your logs, please send them to support@pharmacywire.com and we will squash them.
* Optimizations to cart checkout speed/requests for new customers when creating a new account/placing an order during checkout.

= 3.5.1 - September 18, 2020 = 
* Added `billing_type` to the `pwireResponse` of the `pwire:cart:orderSubmitted` javascript even trigger (`pwireResponse.cart.billing_type`).

= 3.5 - September 9, 2020 = 
* Added the ability to search by drug ID and package ID in a comma sperated list to the PharmacyWire Search shortcode. eg. `[PharmacyWire Search="DP-13411"]`, `[PharmacyWire Search="D-1311, D-1342"]`
* Added 'tier' search criteria to the PharmacyWire Search shortcode to show/limit the number or tiers output. This is useful in particular with the new drug package ID search, or other cases where you might only want to show a certain number of tiers in the template. It can handle `gt, ge, lt, le` (greater than, greater than or equal to, less than, less than or equal to), no operator assumes 'equal to'. For example, `[PharmacyWire Search="DP-2342" tier="1"]` will show the first tier of product pricing. `[PharmacyWire Search="DP-2342" tier="le 3"]` would show up to 3 tiers of pricing that are setup on a package in PharmacyWire.
* You can now override the PharmacyWire Search template to create custom templates for items such as a top seller box, or any other unique display that you wish to deviate from the standard product display, this works well in conjunction with the new drug/product ID search capability. 
** To use a custom template you can define the template within the shortcode and place the PHTML template within your `{Theme Name}/pharmacywire/templates/` directory. Shortcode example: `[PharmacyWire Search="DP-13411" template="page_search_topseller"]` points to a template file: `{Theme Name}/pharmacywire/templates/page_search_topseller.phtml`. 
** To add an custom empty result template for display if the custom search template returns no result, you would name the template the same as the custom template with `_empty` on the end. Eg. `{Theme Name}/pharmacywire/templates/page_search_topseller_empty.phtml`.
* `page_register_success.phtml` - added `pwire:account:newAccount` & `pwire:account:newAccount_register` events that get triggered on account creation from registration page. They can be used by watching for the event on document ready, Eg. `$('.pw-pharmacy-wrap').on('pwire:account:newAccount', function() { // new account scripts });`.
* Added `pwire:account:newAccount` & `pwire:account:newAccount_checkout` events that get triggered on account creation from new customers going through the checkout process. They can be used by watching for the event on document ready, Eg. `$('.pw-pharmacy-wrap').on('pwire:account:newAccount_checkout', function() { // new account through checkout scripts });`.
* Added 'minimum order amount' coupon support to plugin.
* Account Profile Page - Fixed recent orders heading dissapearing/display issue when switching tabs.

= 3.4.08 - August 4, 2020 = 
* Added the ability to search for drugs by ID using the search shortcode (individual ID or a comma-seprated list of IDs), eg. [PharmacyWire search="D-1423, D-4654"].

= 3.4.07 - July 29, 2020 = 
* Behind the scenes improvements

= 3.4.06 - July 28, 2020 = 
* Behind the scenes improvements

= 3.4.05 - July 28, 2020 = 
* Removed redundant loading of profile.js

= 3.4.04 - July 28, 2020 =
* Removed tfoot selector in cart.js updateCartForm for custom theme templates no longer using tables

= 3.4.03 - July 27, 2020 =
* Minor bug fix of non-breaking JS error in common.js & reorder.js to stop it from producing warning in dev console.

= 3.4.02 - July 23, 2020 =
* Added an option to display the cart shipping options as a radio button instead of the default drop-down list. The option is located in PharmacyWire->Store settings, Shipping Information section.

= 3.4.01 - July 22, 2020 =
* **Note:** While there are no required theme changes in this release, this is a big plugin update to some core files - particularly javascript files. Substantial testing has been completed here, but please test thoroughly before going live.
* Updated common.js & profile.js to ES6 syntax standards. 
* Added register.js, reorder.js, cart.js to separate specific scripts out to where they're needed.
* General CSS/JS file restructuring and cleanup.
* Removed colorbox from loading within WordPress admin.
* Removed jQuery UI smoothness styles and replaced with minimal styling for jQuery autocomplete on the suggestive search results to accomplish the same thing in a smaller payload.
* Removed the 'loading' spinner gif which was rather large. The spinner animation is now done via js.
* Added support for Discover card (defaults to disabled, needs to be enabled in Store settings).

= 3.3.13 - July 21, 2020 = 
* Fixed a new issue where recently created accounts that return to place another order/refill may get an invalid address error on checkout when using the same billing address as their shipping address.

= 3.3.12 - July 7, 2020 =
* Bug fix to a recent change that was causing medical questionnaire answers to not be saved

= 3.3.11 - June 29, 2020 =
* Now hides 'HOW WILL YOU SEND IN YOUR PRESCRIPTION?' section on checkout if the order only contains OTC items and sets the forwarding RX method as OTC in PharmacyWire.
* Deprecated session Cart::getTotal() method, replaced Cart::getSubTotal().

= 3.3.10 - June 18, 2020 =
* Added a new 'country' check to custom shipping options config. If configured, you can check that all the products in the cart match/are from a list of countries. If they are then show a custom shipping option (tracking, etc.) 

= 3.3.09 - June 10, 2020 =
* *Template Update* - `JsonApi/templates/quick_checkout.phtml` - The options to submit prescriptions are now pulled from PharmacyWire. The hardcoded options that were in quick_checkout.phtml have been removed and the container left to be populated via javascript (`<div class="submission-instructions"></div>`).
* Settings page Memcached Configuration now shows hit ratio stats if a valid memcached server is entered.
* Added additional checks to prevent submitting an empty cart. Trying to access the final checkout url directly with no products in the cart now redirets to shopping cart.

= 3.3.08 - May 20, 2020 =
* Rx Upload now requires PHP ImageMagick extension be installed/enabled on your server (https://www.php.net/manual/en/book.imagick.php). Install ImageMagick if it is not already and test your document upload after updating to ensure it is working.
* *Template Update* - *page_search_detail.phtml* -- Removed "cost" markup from Drug structured markup in the page_search_detail.phtml template as Google no longer accepts it as valid.

= 3.3.07 - May 5, 2020 =
* 'Mandatory coupon' code adjustment to avoid rare/possible warning message.

= 3.3.06 - April 24, 2020 =
* Removed 'Coupon:' from the 'Mandatory Coupons' cart line item.

= 3.3.05 - April 24, 2020 =
* Added a logout page (defaults to /logout). This will make it more straightforward to add to menus, etc. No need to pass additional parmaters to /login, however that still works (old method, eg. was to goto /login/?action=logout).
* Added 'Mandatory Coupons' field in PharmacyWire->Store settings. This is for coupon(s) to be applied to all orders, that can't be removed, for special circumstances where a coupon needs to be forced onto the cart (negative coupons to tack on a fee, etc.). Enter in a comma seperated list if there are multiples, e.g. 'coupon1, coupon2' 

= 3.3.04 - April 15, 2020 =
* Added option 'Show quantity as dropdown for shopping cart (instead of the input with +/- buttons)?' to control cart quantity display
* *template* — *page_checkout_cart.phtml* - Added the calculated quantity display below the cart quantity dropdown (to show total number of tabs, etc.) (lines 65-66, 70-76)
* *template* - *page_login.phtml* - Template overhauled. Remove old page_login.phtml from your theme if you have one. If you want to customize that page, copy the new login template from the plugin (Themes/templates/page_login.phtml) to the theme and apply your customizations to the new template within your theme.
* Various bug fixes & improvements.

= 3.3.03 - March 31, 2020 =
* *Template Update* - *page_register.phtml* Added the password strength indicator. (Lines: 33-40)
* Calculation adjustment on determining max order quantity.

= 3.3.02 - March 26, 2020 = 
* *Template Update* - *page_profile_info.phtml* Added alternate (phone day) display to profile & edit form. (Lines: 153-156, 194-199, 251-268)
* Added stricter validation to entering the XML Connect URL to aid in entering it correctly.
* Removed '#' display requirement on drug/package tags (used in places such as search results ROW.drugJSON, ROW.packageJSON). All tags from PharmacyWire are now considered public viewable/displayed unless otherwise configured within PharmacyWire by setting the Display Permissions for each tag. 
* Various bug fixes & improvements.

= 3.3.01 - March 18, 2020 =
* Plugin now contains ability to update via WordPress. *Prior to updating* - please read the changelog/release notes to ensure there are no required template changes that could break your site prior to doing an update.
* When updating to this version please do a thorough test of your site before going live and contact us if you need help bringing things up to date. Future release will contain more in-depth changelog notes.
* PharmacyWire->Settings now contains a 'Plugin Update License' setting. Request a key from us and we can enable plugin auto-updates for your site. Production and development sites will each require a unique license key.
* Set required PHP version to 7.3
* Removed old PharmacyWire settings that are deprecated for the V3 plugin.
* Added new options in PharmacyWire->Store for enabling/disabling Order Questions and setting their defaults if they're disabled (Use child resistant packaging?, Call for Refills?	- default to Yes unless set to No)
* *Template Update* - *page_profile_info.phtml* 
** Removed ability to edit First & Last Name. Removed firstName, lastName input fields from template (Line 222). 
** Added logic around displaying Call for Refills & Child Resistant Packaging questions (Lines: 155-162, 192-199, 337-354). 
** Added {VALUE_WEIGHT_UNIT} (Lines: 205, 328,331).
** Added Document Upload tab. (Lines: 19-21, 495-501)
* Removed $password from the new account WordPress default email config templates to no longer be sent in emails. If you wish to remove it from your customized templates, you can do so within the plugin settings: PharmacyWire->Email, Email Message. The variable is still available if you wish to continue sending it in the email to new users upon account creation.
* *Template Update* - *page_register.phtml* Added logic around displaying Call for Refills & Child Resistant Packaging (Lines 274-306). Added button class to submit for proper styling (Lines 309).
* *New Shortcode* - `[PharmacyWire_DocumentUpload]` will allow you to display the upload form where desired.
* *page_reorder.phtml* - added new variable {ROW.countryName} to display the drug source country in addition to the existing {ROW.countryFlag} variable.