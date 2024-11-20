<?php
/*
	Plugin Name: WP PharmacyWire (V3)
	Version: 3.9.250
	Description: Plugin to provide E-Commerce capability on Wordpress for PharmacyWire using Metrex PharmacyWire XMLConnect API.
	Author: Metrex
	Author URI: http://www.pharmacywire.com
	Plugin URI: http://www.pharmacywire.com
*/
define("PWIRE_VERSION", "3.9.250");

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

register_activation_hook(__FILE__, 'pharmacy_install');
register_deactivation_hook(__FILE__, 'flush_rewrite_rules');
add_action('plugins_loaded', 'pharmacy_update_db_check');

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Utility/Memcached.php';
require_once __DIR__ . '/Utility/SessionHandler.php';
require_once __DIR__ . '/Utility/CustomPosts/posts-init.php';
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/Utility/Messages.php';
require_once __DIR__ . '/Blocks/block-loader.php';
// require_once __DIR__ . '/Blocks/pw-cart/pw-cart-block.php';
// require_once __DIR__ . '/Blocks/pw-account-tools/pw-account-tools-block.php';

function pw_init_session() {

	// Start session on init as well as in xtemplate.class.php
	$sessionHandler = new Utility_SessionHandler();
	if (session_status() !== PHP_SESSION_ACTIVE) {
		session_set_save_handler(array(&$sessionHandler, "open"), array(&$sessionHandler, "close"), array(&$sessionHandler, "read"), array(&$sessionHandler, "write"), array(&$sessionHandler, "destroy"), array(&$sessionHandler, "gc"));
		session_start();
	}

	// Close the session before $wpdb destructs itself. Quit early to avoid WP interferance/complaints in Site Health.
	add_action( 'wp_loaded', 'session_write_close', 100, 0);
}

pw_init_session();

add_action('wp_head', 'pw_init_session', 1);
add_action( 'template_redirect', 'pw_init_session', 1);

if (!isset($_SESSION['Refer'])) {
	$_SESSION['Refer'] = '';
	// if HTTP_REFERER is set and not the current website, set as session Refer to pass with PatientCreate
	if (isset($_SERVER['HTTP_REFERER']) && (strpos($_SERVER['HTTP_REFERER'], PC_getHomePageURL()) === false)) {
		$_SESSION['Refer'] = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST);
	}
}

global $pw_page_list;
$pw_page_list = get_option('pw_page_list');

// Pharmacy plugin installation
function pharmacy_install()
{
	include('pharmacy_install.php');
	// with dbdelta and catalog changes, no reason to drop tables on activation
	// it upgrades the tables for any changes
	run_pharmacy_install(false);
	updatePwireXMLDataCache();
	pwire_register_custom_posts();
	pwire_rewrite_rules();
	flush_rewrite_rules();
}


function pharmacy_update_db_check()
{
	if (!empty(get_site_option('pw_db_version')) && (get_site_option('pw_db_version') != PWIRE_VERSION)) {
		pharmacy_install();
	}
}

function updatePwireXMLDataCache()
{
	// Run XML connect requests that can be stored in the DB
	// e.g. updating the forward prescription option methods from PharmacyWire 
	$catalogModel = new Model_Catalog();
	$reply = $catalogModel->updatePwireXMLDataCache();
}

// ST1RT SCHEDULE ------------
add_action('buildcache_event', 'buildcache');

if (get_option('pw_set_search_title')) {
	add_filter('wp_title', 'SearchTitle', 10, 3);
}

// if viewing frontend load json api
// if (!is_admin()) {
	if (!class_exists('PW_JSON', false)) {
		include_once JSON_API_FOLDER . '/class-pw-json.php';
	}
	$pwJson = new PW_JSON();
	$pwJson->init();
// }

function getPwPluginUrl()
{
	return PWIRE_PLUGIN_URL;
}
function getPwRequestUrl()
{
	return REQUEST_URL;
}
function getPwJsonRequestUrl()
{
	return JSON_REQUEST_URL;
}

function SearchTitle($title)
{
	$messages = new Utility_Messages();
	$PageTitle = $messages->getPageTitle();
	if ($PageTitle) {
		return $PageTitle;
	}
	return $title;
}

function buildcache()
{
	$catalogModel = new Model_Catalog();
	$reply = $catalogModel->buildCache();
	return $reply;
}
// END SCHEDULE ------------

// adding style sheet and javascript here
add_action('wp_head', 'WPPharmacywire_HeadAction');

function WPPharmacywire_HeadAction()
{
	if (get_option('yst_ga')) {
		$options  = get_option('yst_ga');
		$options = $options['ga_general'];
		$uaString = $options['manual_ua_code_field'] ? $options['manual_ua_code_field'] : '';
	} elseif (get_option('Yoast_Google_Analytics')) {
		$options  = get_option('Yoast_Google_Analytics');
		$uaString = $options['uastring'] ? $options['uastring'] : '';
	}

	if (is_plugin_active('google-analytics-for-wordpress/googleanalytics.php') && isset($uaString) && $uaString != '') {
		$orders = Cart::getAnalytics();
		$Store = get_option('pw_name');
		if (!empty($orders)) {
			foreach (array_keys($orders) as $orderID) {
				$order = $orders[$orderID];
				$address = $order["orderinfo"]->shippingAddress; ?>
				<script type="text/javascript">
					var _gaq = _gaq || [];
					_gaq.push(['_setAccount', '<?php echo ($uaString); ?>']);
					_gaq.push(['_trackPageview']);
					_gaq.push(['_addTrans', '<?php echo ($orderID); ?>', '<?php echo ($Store); ?>', '<?php echo ($order["total"]); ?>', '', '<?php echo ($order["orderinfo"]->shippingfee); ?>', '<?php echo ($address->city); ?>', '<?php echo ($address->province); ?>', '<?php echo ($address->country); ?>']);
					<?php
					foreach ($order["details"] as $item) {
						if (strlen($item->strength)) {
							$category = $item->strength . ' ' . $item->strength_unit;
						} else {
							$category = '' . $item->strengthfreeform;
						}
						$unit = $item->price;
						$quantity = $item->amount;
						if (strlen($item->packagequantity)) {
							$packagequantity = explode('@', $item->packagequantity);
							if ($packagequantity[0] > 0) {
								$unit /= $packagequantity[0];
								$quantity *= $packagequantity[0];
								if (strlen($packagequantity[1])) {
									$category .= '; (' . $packagequantity[1] . ')';
								}
							} else {
								$category .= '; (' . $item->packagequantity . ')';
							}
						} elseif (strlen($item->packagingfreeform)) {
							$category .= '; (' . $item->packagingfreeform . ')';
						} ?>
						_gaq.push(['_addItem', '<?php echo ($orderID); ?>', '<?php echo ($item->package_id); ?>', '<?php echo ($item->product); ?>', '<?php echo ($category); ?>', '<?php echo ($unit); ?>', '<?php echo ($quantity); ?>']);
					<?php
					} ?>
					_gaq.push(['_trackTrans']);
					(function() {
						var ga = document.createElement('script');
						ga.type = 'text/javascript';
						ga.async = true;
						ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
						var s = document.getElementsByTagName('script')[0];
						s.parentNode.insertBefore(ga, s);
					})();
				</script>
	<?php
			}
		}
	}
	Cart::resetAnalytics();
}

function admin_scripts()
{
	/* Call the html code */
	add_action('admin_menu', 'WPPharmacywire_MenuAction');
	add_action('admin_print_scripts', 'WPPharmacywire_Admin_Scripts');
	add_action('admin_print_styles', 'WPPharmacywire_Admin_Styles');
}

// define global variables - instead of wp_localize_script as variables are used in multiple scripts
function pwire_js_variables()
{
	?>
	<script>
		var wp_pharmacywire = <?php
								echo json_encode(array(
									'plugin_url' => PWIRE_PLUGIN_URL
								)); ?>
	</script>
	<?php
}
add_action('wp_head', 'pwire_js_variables');

function frontend_scripts()
{
	/* Scripts */
	wp_register_script('pwire-plugin-scripts', plugins_url('/Themes/js/common.js', __FILE__), array('jquery', 'pw-spin-js'), PWIRE_VERSION, true);
	wp_enqueue_script('pwire-plugin-scripts');

	// WordPress core script load
	wp_enqueue_script('jquery-ui-core');
	wp_enqueue_script('jquery-ui-autocomplete');
	wp_enqueue_script('jquery-effects-core');

	// http://olado.github.com/doT/
	wp_register_script('doT-jsontemplate', plugins_url('/Themes/vendor/doT.min.js', __FILE__), array(), PWIRE_VERSION, true);
	wp_enqueue_script('doT-jsontemplate');

	// http://fgnass.github.com/spin.js/
	wp_register_script('pw-spin-js', plugins_url('/Themes/vendor/spin.umd.min.js', __FILE__), array(), PWIRE_VERSION, true);
	wp_enqueue_script('pw-spin-js');

	if (is_page()) {

		$currentPage = PC_getCurrentURL();

		if (($currentPage == PC_getJSONCheckout()) || ($currentPage == PC_getRegisterUrl())) {
			// Password suggestions - https://github.com/dropbox/zxcvbn
			// limit to checkout pages
			wp_register_script('password-zxcvbn-js', plugins_url('/Themes/vendor/zxcvbn.js', __FILE__), array(), PWIRE_VERSION, true);
			wp_enqueue_script('password-zxcvbn-js');
		}

		if (($currentPage == PC_getJSONCheckout()) || $currentPage == PC_getUploadDocumentUrl()) {
			// https://www.dropzonejs.com/#configuration
			// limit to upload and checkout pages
			wp_register_script('dropzone-js', plugins_url('/Themes/vendor/dropzone/js/dropzone.js', __FILE__), array(), PWIRE_VERSION, false);
			wp_enqueue_script('dropzone-js');
		}
		if ($currentPage == PC_getShoppingURL()) {
			wp_register_script('pw-cart-js', plugins_url('/Themes/js/cart.js', __FILE__), array(), PWIRE_VERSION, false);
			wp_enqueue_script('pw-cart-js');
		}
		if ($currentPage == PC_getRegisterUrl()) {
			wp_register_script('pw-register-js', plugins_url('/Themes/js/register.js', __FILE__), array(), PWIRE_VERSION, false);
			wp_enqueue_script('pw-register-js');
		}
		if (($currentPage == PC_getProfileUrl()) || ($currentPage == PC_getProfileEditUrl()) || ($currentPage ==  PC_getProfileAddressUrl()) || $currentPage == PC_getUrl('Pharmacy_Profile')) {
			wp_register_script('pw-profile-js', plugins_url('/Themes/js/profile.js', __FILE__), array(), PWIRE_VERSION, false);
			wp_enqueue_script('pw-profile-js');
		}
		if ($currentPage == PC_getReorderUrl()) {
			wp_register_script('pw-reorder-js', plugins_url('/Themes/js/reorder.js', __FILE__), array(), PWIRE_VERSION, false);
			wp_enqueue_script('pw-reorder-js');
		}
	}

	PW_JSON::pwire_localize_script('pwire-plugin-scripts');

	/* Stylesheets */
	if (get_option('pw_default_plugin_styles', 1)) {
		wp_register_style('pwire-plugin-styles', plugins_url('/Themes/styles/css/style.css', __FILE__), array(), PWIRE_VERSION);
		wp_enqueue_style('pwire-plugin-styles', false, 'foundation-app');
		wp_register_style('font-awesome-pro', plugins_url('/Themes/vendor/font-awesome/css/all.min.css', __FILE__), array(), PWIRE_VERSION);
		wp_enqueue_style('font-awesome-pro', false);
		wp_register_style('dropzone-css', plugins_url('/Themes/vendor/dropzone/css/dropzone.css', __FILE__), array(), PWIRE_VERSION);
		wp_enqueue_style('dropzone-css', false);
	}
}

function WPPharmacywire_Admin_Scripts()
{
	/* Load for Entire WP Admin Side */
	wp_register_script('pwire-admin-scripts', plugins_url('/Admin/js/pwire-admin.js', __FILE__), array('jquery'), PWIRE_VERSION, true);
	wp_enqueue_script('pwire-admin-scripts');
}
function WPPharmacywire_Admin_Styles()
{
	/* Load for Entire WP Admin Side */
	wp_register_style('pharmacywireAdminStyles', plugins_url('/Admin/css/admin-style.css', __FILE__), array(), PWIRE_VERSION);
	wp_enqueue_style('pharmacywireAdminStyles');
}

add_filter('script_loader_tag', 'pwire_async_scripts', 10, 3);
function pwire_async_scripts($tag, $handle, $src)
{
	$async_scripts = array(
		'password-zxcvbn-js',
	);

	$defer_scripts = array();

	if (in_array($handle, $async_scripts)) {
		return '<script type="text/javascript" src="' . $src . '" async="true"></script>' . "\n";
	}

	if (in_array($handle, $defer_scripts)) {
		return '<script type="text/javascript" src="' . $src . '" defer="defer"></script>' . "\n";
	}

	return $tag;
}

if (is_admin()) {
	add_action('admin_enqueue_scripts', 'admin_scripts');
	add_action('admin_notices', 'pw_licence_error_notices__error');
} else {
	// Load Foundation Styles & Scripts
	require_once 'foundation.php';
	add_action('wp_enqueue_scripts', 'frontend_scripts');
}

function WPPharmacywire_MenuAction()
{
	/*add_options_page('Pharmacywire', 'Pharmacywire Build Cache', 'administrator',
		'pharmacywire', 'WPPharmacywire_Page');
	*/
}

function WPPharmacywire_PluginAdmin_Scripts()
{
	// Plugin Admins scripts just on PharmacyWire Plugin pages
	// Load for custom media upload support
	wp_enqueue_script('media-upload');
}

function WPPharmacywire_PluginAdmin_Styles()
{
	// Plugin Admins styles just on PharmacyWire Plugin pages
	wp_register_style('pharmacywireAdminStyles', plugins_url('/Admin/css/admin-style.css', __FILE__), array(), PWIRE_VERSION);
	wp_enqueue_style('pharmacywireAdminStyles');
	wp_enqueue_style('thickbox');
}

// create custom plugin settings menu
add_action('admin_menu', 'pharmacy_create_menu');

function pharmacy_create_menu()
{
	//create new top-level menu
	if (current_user_can('pharmacywire_settings')) {
		$settingsPage = add_menu_page('Pharmacywire', 'Pharmacywire', 'pharmacywire_settings', __FILE__, 'pharmacy_settings_page', plugins_url('Admin/images/icon.png', __FILE__));
		add_submenu_page(__FILE__, 'Pwire_Settings', 'Settings', 'pharmacywire_settings', __FILE__, 'pharmacy_settings_page');
	}

	$configuration = new Utility_Configuration();

	//create submenu
	if (current_user_can('pharmacywire_settings')) {
		$storePage = add_submenu_page(__FILE__, 'Pwire_Store', 'Store', 'pharmacywire_settings', 'pharmacy_menu_store.php', 'pharmacy_menu_store');
		$catalogPage = add_submenu_page(__FILE__, 'Pwire_Catalog', 'Catalog', 'pharmacywire_settings', 'pharmacy_menu_catalog.php', 'pharmacy_menu_catalog');
		$emailPage = add_submenu_page(__FILE__, 'Pwire_Email', 'Email', 'pharmacywire_settings', 'pharmacy_menu_email.php', 'pharmacy_menu_email');
	}

	//call register settings function
	add_action('admin_init', 'register_mysettings');

	// Load PharmacyWire Plugin Admins scripts and styles just on PharmacyWire Plugin pages
	$pluginPages = array($settingsPage, $storePage, $catalogPage, $emailPage);
	foreach ($pluginPages as $page) {
		add_action("admin_print_scripts-$page", 'WPPharmacywire_PluginAdmin_Scripts');
		add_action("admin_print_styles-$page", 'WPPharmacywire_PluginAdmin_Styles');
	}

	add_filter('sanitize_option_pw_url', 'sanitize_option_pharmacy', 10, 2);
	add_filter('sanitize_option_pw_externalbuy_url', 'sanitize_option_pharmacy', 10, 2);
	add_filter('sanitize_option_pw_memcached_servers', 'sanitize_option_pharmacy', 10, 2);
	add_filter('sanitize_option_pw_user_id', 'sanitize_option_pharmacy', 10, 2);
	add_filter('sanitize_option_pw_passkey', 'sanitize_option_pharmacy', 10, 2);
	add_filter('sanitize_option_pw_update_license', 'sanitize_option_pharmacy', 10, 2);
	add_filter('sanitize_option_pw_schedule_time', 'sanitize_option_pharmacy', 10, 2);
	add_filter('sanitize_option_pw_shipping_fee', 'sanitize_option_pharmacy', 10, 2);
	add_filter('sanitize_option_pw_express_shipping_fee', 'sanitize_option_pharmacy', 10, 2);
	add_filter('sanitize_option_pw_intl_shipping_fee', 'sanitize_option_pharmacy', 10, 2);
	add_filter('sanitize_option_pw_express_shipping_message', 'sanitize_option_pharmacy', 10, 2);
	add_filter('sanitize_option_pw_intl_shipping_message', 'sanitize_option_pharmacy', 10, 2);
	add_filter('sanitize_option_pw_charge_shipping_per_country', 'sanitize_option_pharmacy', 10, 2);
	add_filter('sanitize_option_pw_fridge_express_shipping', 'sanitize_option_pharmacy', 10, 2);
	add_filter('sanitize_option_pw_international_express_allowed', 'sanitize_option_pharmacy', 10, 2);
	add_filter('sanitize_option_pw_split_multi_country_orders', 'sanitize_option_pharmacy', 10, 2);
	add_filter('sanitize_option_pw_disable_eft', 'sanitize_option_pharmacy', 10, 2);
	add_filter('sanitize_option_pw_disable_amex', 'sanitize_option_pharmacy', 10, 2);
	add_filter('sanitize_option_pw_disable_mastercard', 'sanitize_option_pharmacy', 10, 2);
	add_filter('sanitize_option_pw_disable_visa', 'sanitize_option_pharmacy', 10, 2);
	add_filter('sanitize_option_pw_disable_discover', 'sanitize_option_pharmacy', 10, 2);
	add_filter('sanitize_option_pw_name', 'sanitize_option_pharmacy', 10, 2);
	add_filter('sanitize_option_pw_enable_coupons', 'sanitize_option_pharmacy', 10, 2);
	add_filter('sanitize_option_pw_coupons_mandatory', 'sanitize_option_pharmacy', 10, 2);
}

global $WPPharmacywire_page;
global $WPPharmacywire_content;

add_action('wp_ajax_pharmacywire_query_posts', 'wp_pharmacywire_query_posts_callback');
add_action('wp_ajax_nopriv_pharmacywire_query_posts', 'wp_pharmacywire_query_posts_callback');
function wp_pharmacywire_query_posts_callback()
{
	$response = array();
	$response['result'] = 'success';
	switch ($_POST['query']) {
		case 'condition':
			$searchPage = new Page_Search();
			$response['data'] = $searchPage->render_json();
			break;
		default:
			$response['result'] = 'failure';
			$response['message'] = 'unknown query type';
			break;
	}
	exit(json_encode($response));
}

function WPPharmacywire_Start()
{
	//ideally this should create the page object and call process and that's it.
	global $WPPharmacywire_page, $WPPharmacywire_content;
	$WPPharmacywire_page = PC_getCurrentPage();
	$WPPharmacywire_content = '';
	if ($WPPharmacywire_page != PHARMACY_HOME_PAGE) {
		$WPPharmacywire_content = makeContentBaseOnUrl($WPPharmacywire_page);
	}
}

if (!(defined('WP_ADMIN') && WP_ADMIN)) {
	add_action('wp', 'WPPharmacywire_Start');
}

function WPPharmacywire_Page($content)
{
	//ideally this should use the page object from WPPharmacywire_Start and call render and return the content.

	global $WPPharmacywire_page, $WPPharmacywire_content;
	$messages = new Utility_Messages();
	$notifications = $messages->renderNotifications();
	if ($WPPharmacywire_page == PHARMACY_HOME_PAGE) {
		return $notifications . $content;
	}

	$Patterns = array('/\[Pharmacy[a-zA-Z_]*\]/', '/\[Forgot_Password\]/', '/\[Checkout_Edit_Shipping_Address\]/', '/\[Change_Password\]/');
	$content = preg_replace('/<p>(\[.*\])<\/p>/', '$1', $content);
	return $notifications . preg_replace(
		$Patterns,
		$WPPharmacywire_content,
		$content
	);
}
add_filter('the_content', 'WPPharmacywire_Page', 10, 1);

function processNotificationMessages($type)
{
	$notifications = $_SESSION[$type . 'Message'];
	if (count($notifications)) {
		$notificationString = '<ul class="notification-$type notification">';
		foreach ($notifications as $notification) {
			$notificationString .= '<li>$notification</li>';
		}
		$notificationString .= '</ul>';
	}

	return $notificationString;
}

function makeContentBaseOnUrl($page)
{
	$content = "";

	switch ($page) {
			// process when is home page
		case PHARMACY_HOME_PAGE:
			//$content = 'To use the full checkout you must log into system. The page is automatically redirected when no user is logged in.';
			break;
			// process when is Search page
		case PHARMACY_SEARCH_PAGE:
			$searchPage = new Page_Search();
			$content = $searchPage->render();
			break;
			// process when is drug detail page
		case PHARMACY_DRUGDETAIL_PAGE:
			$content = PHARMACY_DRUGDETAIL_PAGE;
			break;
			// process when is contact page
		case PHARMACY_CONTACT_PAGE:
			$content = PHARMACY_CONTACT_PAGE;
			break;
			// process when is shopping cart page
		case PHARMACY_CHECKOUT_SHOPPING:
			$shopping = new Page_Checkout_Shopping();
			$content = $shopping->render();
			break;
			// process when is shipping page
		case PHARMACY_CHECKOUT_SHIPPING:
			$shipping = new Page_Checkout_Shipping();
			$content = $shipping->render();
			break;
			// process when is billing page
		case PHARMACY_CHECKOUT_BILLING:
			$billing = new Page_Checkout_Billing();
			$content = $billing->render();
			break;
			// process when is confirm page
		case PHARMACY_CHECKOUT_CONFIRM:
			$confirm = new Page_Checkout_Confirm();
			$content = $confirm->render();
			break;
			// process when is thank  page
		case PHARMACY_CHECKOUT_THANK_YOU:
			$thank = new Page_Checkout_Thank();
			$content = $thank->render();
			break;
			// process when is register page
		case PHARMACY_REGISTER_PAGE:
			$registerPage = new Page_Register();
			$content = $registerPage->render();
			break;
			// process when user login
		case PHARMACY_LOGIN_PAGE:
			$login = new Page_Login();
			$content = $login->render();
			break;
			// process when user logout
		case PHARMACY_LOGOUT_PAGE:
			$logout = new Page_Logout();
			$content = $logout->render();
			break;
			// process when is profile	page
		case PHARMACY_PROFILE_PAGE:
			wp_enqueue_script('pwire-profile-scripts');
			$profilePage = new Page_ProfileInfo();
			$content = $profilePage->render();
			break;
		case PHARMACY_CHECKOUT_EDIT_SHIPPING_ADRESS:
			$editAddress = new Page_Checkout_Edit_ShippingAddress();
			$content = $editAddress->render();
			break;
		case PHARMACY_FORGOTPASSWORD_PAGE:
			$forgotPassPage = new Page_ForgotPassword();
			$content = $forgotPassPage->render();
			break;
		case PHARMACY_CHANGEPASSWORD_PAGE:
			$changePassPage = new Page_ChangePass();
			$content = $changePassPage->render();
			break;
		case PHARMACY_PROFILEADDRESS_PAGE:
			$profilePage = new Page_ProfileAddress();
			$content = $profilePage->render();
			break;
		case PHARMACY_PROFILE_INFO_PAGE:
			$profilePage = new Page_ProfileInfo();
			$content = $profilePage->render();
			break;
		case PHARMACY_PROFILE_EDIT_PAGE:
			$profilePage = new Page_ProfileEdit();
			$content = $profilePage->render();
			break;
		case PHARMACY_UPLOAD_DOCUMENT_PAGE:
			$uploadPage = new Page_UploadDocument();
			$content = $uploadPage->render();
			break;
		case PHARMACY_REORDER_PAGE:
			$reorderPage = new Page_Reorder();
			$content = $reorderPage->render();
			break;
		case PHARMACY_VIEW_ORDER_PAGE:
			$viewOrderPage = new Page_ViewOrder();
			$content = $viewOrderPage->render();
			break;
	}

	return $content;
}


function sanitize_option_pharmacy($value, $option)
{
	switch ($option) {
		case 'pw_url':
			if ((bool)preg_match('#http(s?)://(.+)/momex/NavCode/xmlconnect$#i', $value)) {
				$value = esc_url_raw($value);
			} else {
				$value = get_option($option); // Resets option to stored value in the case of failed sanitization
				if (function_exists('add_settings_error')) {
					add_settings_error($option, 'invalid_url', __('The PharmacyWire XML Connect address you entered did not appear to be a valid URL. The URL path must include "/momex/NavCode/xmlconnect" on the end. e.g.: https://[*yoursubdomain*].pharmacywire.com/momex/NavCode/xmlconnect'));
				}
			}
			break;
		case 'pw_memcached_servers':
			$servers = explode(';', $value);
			$fail = 0;
			foreach ($servers as $server) {
				$parts = explode(':', $server);
				if (isset($parts[2])) {
					$fail = 1;
				}
				// regex port
				if (!isset($parts[1]) || (isset($parts[1]) && !preg_match("/\d+$/", $parts[1]))) {
					$fail = 1;
				}
			}
			if (!empty($value) && $fail) {
				$value = get_option($option); // Resets option to stored value in the case of failed sanitization
				if (function_exists('add_settings_error')) {
					add_settings_error($option, 'invalid_memcached_server', __("The memcached server address is not valid. Please enter 'server[:port][;server[:port]]'."));
				}
			}
			break;
		case 'pw_externalbuy_url':
			if (strlen($value)) {
				if ((bool)preg_match('#http(s?)://(.+)#i', $value)) {
					// $value = esc_url_raw($value);
				} else {
					$value = get_option($option); // Resets option to stored value in the case of failed sanitization
					if (function_exists('add_settings_error')) {
						add_settings_error($option, 'invalid_url', __('The backend processing site address you entered did not appear to be a valid URL. Please enter a valid URL.'));
					}
				}
			}
			break;
		case 'pw_user_id':
			if (empty($value) || (preg_match('/^\*+$/', $value))) {
				$value = get_option($option); // Resets option to stored value in the case of failed sanitization
				if (empty($value) && function_exists('add_settings_error')) {
					add_settings_error($option, 'invalid_user_id', __('UserID must be not empty. Please enter a UserID.'));
				}
			}
			break;
		case 'pw_passkey':
			if (empty($value) || (preg_match('/^\*+$/', $value))) {
				$value = get_option($option); // Resets option to stored value in the case of failed sanitization
				if (empty($value) && function_exists('add_settings_error')) {
					add_settings_error($option, 'invalid_pw_passkey', __('Passkey must be not empty. Please enter a Passkey.'));
				}
			}
			break;
		case 'pw_update_license':
			if (preg_match('/^\*+$/', $value)) {
				$value = get_option($option); // Resets option to stored value in the case of failed sanitization
			}
			break;
		case 'pw_schedule_time':
			if (!isset($_POST['pw_enable_schedule'])) {
				$value = get_option($option);
			} else {
				if (!(bool)preg_match('/^\d\d\:\d\d$/i', $value)) {
					$value = get_option($option); // Resets option to stored value in the case of failed sanitization
					if (function_exists('add_settings_error')) {
						add_settings_error($option, 'invalid_schedule_time', __('The shedule time you entered did not appear to be a valid format (hh:mm). Please enter a valid schedule time.'));
					}
				}
			}
			break;
		case 'pw_shipping_fee':
			if (!is_numeric($value)) {
				$value = get_option($option); // Resets option to stored value in the case of failed sanitization

				if (function_exists('add_settings_error')) {
					add_settings_error($option, 'invalid_shipping_fee', __('The shipping fee you entered did not appear to be a number. Please enter a valid shipping fee.'));
				}
			}
			break;
		case 'pw_express_shipping_fee':
			if (strlen($value) && !is_numeric($value)) {
				$value = get_option($option); // Resets option to stored value in the case of failed sanitization

				if (function_exists('add_settings_error')) {
					add_settings_error($option, 'invalid_shipping_fee', __('The express shipping fee you entered did not appear to be a number. Please enter a valid express shipping fee.'));
				}
			}
			break;
		case 'pw_intl_shipping_fee':
			if (strlen($value) && !is_numeric($value)) {
				$value = get_option($option); // Resets option to stored value in the case of failed sanitization

				if (function_exists('add_settings_error')) {
					add_settings_error($option, 'invalid_shipping_fee', __('The international shipping fee you entered did not appear to be a number. Please enter a valid international shipping fee.'));
				}
			}
			break;
		case 'pw_name':
			if (empty($value)) {
				$value = get_option($option); // Resets option to stored value in the case of failed sanitization
				if (function_exists('add_settings_error')) {
					add_settings_error($option, 'invalid_pw_name', __('Enter the pharmacy name.'));
				}
			}
			break;
		case 'pw_coupons_mandatory':
			if (!empty($value) && (preg_match('/^[a-zA-Z0-9_]+(,[ ]?[a-zA-Z0-9_]+)*$/', $value) === false)) {
				if (function_exists('add_settings_error')) {
					$value = get_option($option); // Resets option to stored value in the case of failed sanitization
					add_settings_error($option, 'invalid_coupon_mandatory', __('Coupon code can only contain _, A-Z, a-z, and 0-9 and be entered in a comma seperated list.'));
				}
			} else {
				$value = preg_replace('/\s+/', '', $value);
			}
			break;
		case 'pw_disable_eft': // Note these options are inverted because the option value is to disable but the screen value is to allow
			if ($value === 'on') {
				$value = 'off';
			} else {
				$value = 'on';
			}
			break;
		case 'pw_disable_amex': // Note these options are inverted because the option value is to disable but the screen value is to allow
			if ($value === 'on') {
				$value = 'off';
			} else {
				$value = 'on';
			}
			break;
		case 'pw_disable_mastercard': // Note these options are inverted because the option value is to disable but the screen value is to allow
			if ($value === 'on') {
				$value = 'off';
			} else {
				$value = 'on';
			}
			break;
		case 'pw_disable_visa': // Note these options are inverted because the option value is to disable but the screen value is to allow
			if ($value === 'on') {
				$value = 'off';
			} else {
				$value = 'on';
			}
			break;
		case 'pw_disable_discover': // Note these options are inverted because the option value is to disable but the screen value is to allow
			if ($value === 'on' && !empty($value)) {
				$value = 'off';
			} else {
				$value = 'on';
			}
			break;
	}
	return $value;
}


function register_mysettings()
{
	//register our settings
	register_setting('pharmacy-settings-group', 'pw_is_dev_site_connection');
	register_setting('pharmacy-settings-group', 'pw_url');
	register_setting('pharmacy-settings-group', 'pw_user_id');
	register_setting('pharmacy-settings-group', 'pw_passkey');
	register_setting('pharmacy-settings-group', 'pw_memcached_servers');
	register_setting('pharmacy-settings-group', 'pw_update_license');

	register_setting('pharmacy-catalog-group', 'pw_enable_schedule');
	register_setting('pharmacy-catalog-group', 'pw_schedule_time');
	register_setting('pharmacy-catalog-group', 'pw_buy_label');
	register_setting('pharmacy-catalog-group', 'pw_checkout_url');
	register_setting('pharmacy-catalog-group', 'pw_continue_shopping_url');
	register_setting('pharmacy-catalog-group', 'pw_externalbuy_url');
	register_setting('pharmacy-catalog-group', 'pw_product_search_post_type');
	register_setting('pharmacy-catalog-group', 'pw_filter_by_tag');
	register_setting('pharmacy-catalog-group', 'pw_unitprice_full_precision');
	register_setting('pharmacy-catalog-group', 'pw_enable_bestprice');
	register_setting('pharmacy-catalog-group', 'pw_drug_dropdown');
	register_setting('pharmacy-catalog-group', 'pw_drug_dropdown_seperate_str');
	register_setting('pharmacy-catalog-group', 'pw_cart_quantity_dropdown');
	register_setting('pharmacy-catalog-group', 'pw_enable_splitdrugs');
	register_setting('pharmacy-catalog-group', 'pw_block_canucks');

	register_setting('pharmacy-catalog-group', 'pw_detailresults_groupby');
	register_setting('pharmacy-catalog-group', 'pw_detailresults_groupby_countrycodes');

	register_setting('pharmacy-catalog-group', 'pw_canadian_IP_exceptions');
	register_setting('pharmacy-catalog-group', 'pw_catalog_version');
	register_setting('pharmacy-catalog-group', 'pw_display_package_name_on_search_results');
	register_setting('pharmacy-catalog-group', 'pw_display_ingredients_on_search_results');
	register_setting('pharmacy-catalog-group', 'pw_treat_familyname_as_alternate_drugname');
	register_setting('pharmacy-catalog-group', 'pw_generic_finds_generic');
	register_setting('pharmacy-catalog-group', 'pw_enable_product_schema');
	register_setting('pharmacy-catalog-group', 'pw_enable_drug_schema');
	register_setting('pharmacy-catalog-group', 'pw_enable_foundation');
	register_setting('pharmacy-catalog-group', 'pw_default_plugin_styles');
	register_setting('pharmacy-catalog-group', 'pw_default_json_theme');
	register_setting('pharmacy-catalog-group', 'pw_enable_product_cache');

	register_setting('pharmacy-store-group', 'pw_name');
	register_setting('pharmacy-store-group', 'pw_license');
	register_setting('pharmacy-store-group', 'pw_pharmacy');
	register_setting('pharmacy-store-group', 'pw_address');
	register_setting('pharmacy-store-group', 'pw_postal_code');
	register_setting('pharmacy-store-group', 'pw_phone_area');
	register_setting('pharmacy-store-group', 'pw_phone');
	register_setting('pharmacy-store-group', 'pw_fax_area');
	register_setting('pharmacy-store-group', 'pw_fax');
	register_setting('pharmacy-store-group', 'pw_city');
	register_setting('pharmacy-store-group', 'pw_province');
	register_setting('pharmacy-store-group', 'pw_country');
	register_setting('pharmacy-store-group', 'pw_email');
	register_setting('pharmacy-store-group', 'pw_email_rx');
	register_setting('pharmacy-store-group', 'pw_hours_of_operation');

	register_setting('pharmacy-store-group', 'pw_shipping_fee');
	register_setting('pharmacy-store-group', 'pw_intl_shipping_fee');
	register_setting('pharmacy-store-group', 'pw_express_shipping_fee');
	register_setting('pharmacy-store-group', 'pw_shipping_fee_message');
	register_setting('pharmacy-store-group', 'pw_intl_shipping_message');
	register_setting('pharmacy-store-group', 'pw_express_shipping_message');
	register_setting('pharmacy-store-group', 'pw_express_shipping_on_tags');
	register_setting('pharmacy-store-group', 'pw_fridge_express_shipping');
	register_setting('pharmacy-store-group', 'pw_charge_shipping_per_country');
	register_setting('pharmacy-store-group', 'pw_localfill_only_expressshipping');
	register_setting('pharmacy-store-group', 'pw_international_express_allowed');
	register_setting('pharmacy-store-group', 'pw_shipping_option_display');

	register_setting('pharmacy-store-group', 'pw_split_multi_country_orders');
	register_setting('pharmacy-store-group', 'pw_allowed_countries');
	register_setting('pharmacy-store-group', 'pw_disable_eft');
	register_setting('pharmacy-store-group', 'pw_disable_amex');
	register_setting('pharmacy-store-group', 'pw_disable_mastercard');
	register_setting('pharmacy-store-group', 'pw_disable_visa');
	register_setting('pharmacy-store-group', 'pw_disable_discover');
	register_setting('pharmacy-store-group', 'pw_payment_method_custom');
	register_setting('pharmacy-store-group', 'pw_enable_coupons');
	register_setting('pharmacy-store-group', 'pw_coupons_mandatory');

	register_setting('pharmacy-store-group', 'pw_business_name');
	register_setting('pharmacy-store-group', 'pw_draft_intro_message');

	register_setting('pharmacy-store-group', 'pw_show_medq_on_checkout');
	register_setting('pharmacy-store-group', 'pw_checkoutq_contact_patient');
	register_setting('pharmacy-store-group', 'pw_checkoutq_child_resistant_packaging');
	register_setting('pharmacy-store-group', 'pw_checkoutq_call_for_refills');

	register_setting('pharmacy-store-group', 'pw_child_resistant_pkg_default');
	register_setting('pharmacy-store-group', 'pw_call_for_refills_default');

	register_setting('pharmacy-email-group', 'pw_email_welcome');
	register_setting('pharmacy-email-group', 'pw_email_forgot_pwd');
	register_setting('pharmacy-email-group', 'pw_emailLogo');
	register_setting('pharmacy-email-group', 'pw_emailHead');
	register_setting('pharmacy-email-group', 'pw_emailFoot');
	register_setting('pharmacy-email-group', 'pw_newPatientEmail');
}

function pharmacy_settings_page()
{
	include('pharmacy_menu_settings.php');
}

function pharmacy_menu_store()
{
	if (!current_user_can('pharmacywire_settings')) {
		wp_die(__('You do not have sufficient permissions to access this page.'));
	}

	include_once('pharmacy_menu_store.php');
}

function pharmacy_menu_email()
{
	if (!current_user_can('pharmacywire_settings')) {
		wp_die(__('You do not have sufficient permissions to access this page.'));
	}

	include_once('pharmacy_menu_email.php');
}

function pharmacy_menu_catalog()
{
	if (!current_user_can('pharmacywire_settings')) {
		wp_die(__('You do not have sufficient permissions to access this page.'));
	}

	include_once('pharmacy_menu_catalog.php');
}

if (is_admin()) {
	function pwire_plugin_action_links($links)
	{
		$pwire_links = array(
			'XML Connect Settings' => '<a href="' . esc_url(admin_url('?page=wp-pharmacywire-v3/wp-pharmacywire.php')) . '">' . __('XML Connect Settings', 'textdomain') . '</a>',
			'License' => '<a href="' . esc_url(admin_url('?page=wp-pharmacywire-v3/wp-pharmacywire.php')) . '">' . __('License', 'textdomain') . '</a>'
		);
		$links = array_merge($pwire_links, $links);
		return $links;
	}
	add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'pwire_plugin_action_links');
}

// Core rewrite rules loaded on init as required
function pwire_rewrite_rules()
{
	$changePassID = PC_getChangePassPageID();
	// apple keychain rewrite rule to sent to change password
	// currently sends to login which takes user to account page that contains change password link
	// extend later to send directly to change password screen after login
	add_rewrite_rule('^.well-known/change-password', 'index.php?p=' . $changePassID, 'top');
	add_rewrite_rule('^pw_json_request/([a-zA-Z0-9-_]+)/pw_nonce/([a-zA-Z0-9-_]+)[/]?$', 'index.php?pw_json_request=$matches[1]&pw_nonce=$matches[2]', 'top');
}
add_action('init', 'pwire_rewrite_rules');

function redirect_to_another_page() {
	if (get_query_var('pw_json_request') && get_query_var('pw_nonce')) {
		wp_safe_redirect(esc_url_raw(add_query_arg( array('pwire_req' => get_query_var('pw_json_request'), 'pw_nonce' => get_query_var('pw_nonce')), JSON_REQUEST_URL)));
		exit();
	}
}
add_action('template_redirect', 'redirect_to_another_page');

function pwire_query_vars( $qvars ) {
	$qvars[] = 'pw_json_request';
	$qvars[] = 'pw_nonce';
    return $qvars;
}
add_filter( 'query_vars', 'pwire_query_vars' );
  
// Pharmacy shortcodes
function pharmacy_shortcode($atts)
{
	$searchTypes = array('drug', 'ingredients', 'condition', 'search', 'drugfamily', 'ifuser', 'unlessuser', 'storeinfo', 'userinfo', 'login', 'emailtest_address', 'affiliate', 'logout', 'searchform', 'template', 'coupon', 'widget', '');

	$html = '';
	foreach ($searchTypes as $type) {
		if (array_key_exists($type, $atts)) {
			extract(shortcode_atts(array(
				'filter' => '',
				'strength' => '',
				'tier' => '',
				'dosageform' => '',
				'limit' => '',
				'related' => '',
				'detail' => '',
				'match' => '',
				'rxrequired' => '', # 'yes', 'no', and 'both' == ''
				'country' => '',
				'debug' => '', # 'yes' or 1 or 'true'
				'ifuser' => '', # redirect to specified URL if user logged in
				'unlessuser' => '', # redirect to specified URL if user not logged in
				'userinfo' => '', # info about logged in user
				'storeinfo' => '', # info about store the site is running on
				'emailtest_address' => '', # shortcode for testing email template
				'affiliatecode' => '', #shortcode to set active affiliate, will display if valid/invalid if admin user logged in
				'logout' => '', # shortcode to log the user out
				'searchform' => '',
				'ingredients' => '',
				'template' => '',
				'coupon' => '',
				'widget' => '',
			), $atts));

			$html = getShortcodeHtml($type, $atts, $atts[$type], $filter, $strength, $tier, $ingredients, $dosageform, $limit, $related, $detail, $match, $rxrequired, $country, $template, $debug);
			break;
		}
	}

	return $html;
}

// search short code drug
function getShortcodeHtml($type, $atts, $keyword, $filter, $strength, $tier, $ingredients, $dosageform, $limit, $related, $detail, $match, $rxrequired, $country, $template, $debug)
{
	$Page = new Page_Shortcode($type, $keyword, $filter, $strength, $tier, $ingredients, $dosageform, $limit, $related, $detail, $match, $rxrequired, $country, $template, $debug);
	switch ($type) {
		case 'unlessuser':
			if (!WebUser::isLoggedIn()) {
				$Page->redirect($keyword);
			}
			break;
		case 'ifuser':
			if (WebUser::isLoggedIn()) {
				$Page->redirect($keyword);
			}
			break;
		case 'storeinfo':
			switch ($keyword) {
				case 'name':
				case 'license':
				case 'pharmacy':
				case 'address':
				case 'city':
				case 'province':
				case 'country':
				case 'postal_code':
				case 'email':
				case 'fax_area':
				case 'fax':
				case 'phone_area':
				case 'phone':
					return get_option('pw_' . $keyword);
				case 'postalcode':
					return get_option('pw_postal_code');
				case 'state':
					return get_option('pw_province');
				case 'url':
					$url = site_url();
					$url = preg_replace("/^(https?):\/\//", "", $url);
					return $url;
				case 'hours_of_operation':
					$hours = str_replace(array("\r\n", "\n\r", "\r", "\n"), "<br>", get_option('pw_' . $keyword));
					return trim($hours);
			}
			break;
		case 'userinfo':
			if (WebUser::isLoggedIn()) {
				$patient = new stdClass();
				$patient->patientid = WebUser::getUserID();
				$patientModel = new Model_Patient();
				$result = $patientModel->getPatientInfo($patient);
				$patient = $result->patient;

				switch ($keyword) {
					case 'username':
						return $patient->username;
					case 'firstname':
						return $patient->firstname;
					case 'lastname':
						return $patient->lastname;
					case 'address1':
						return $patient->address->address1;
					case 'address2':
						return $patient->address->address2;
					case 'city':
						return $patient->address->city;
					case 'province':
						return $patient->address->province;
					case 'country':
						return $patient->address->country;
					case 'postalcode':
						return $patient->address->postalcode;
					case 'email':
						return $patient->email;
					case 'fax_area':
						return $patient->areacode_fax;
					case 'fax':
						return $patient->fax;
					case 'phone_area':
						return $patient->areacode;
					case 'phone':
						return $patient->phone;
					case 'zip_code':
						return $patient->postalcode;
					case 'zip code':
						return $patient->postalcode;
					case 'postal_code':
						return $patient->postalcode;
					case 'state':
						return $patient->province;
					case 'name':
						return $patient->firstname . ' ' . $patient->lastname;
					case 'userid':
						return $patient->id;
					case 'referral_code':
						return $patient->referral->referral_code ?? '';
					case 'referral_balance':
						return $patient->referral->referral_balance ?? '0.00';
				}
			}
			break;

		case 'login':
			$login = new Page_Login();
			return $login->render();

		case 'logout':
			if (WebUser::isLoggedIn()) {
				$login = new Page_Login();
				return $login->processLogout();
			}
			return 'you are not logged in';

		case 'emailtest_address':
			// if valid email, send a test email to that address on load
			if (is_email($keyword)) {
				// execute shortcode only when viewing the frontend page as a logged in user
				if (is_user_logged_in() && !is_admin() && is_singular()) {
					$to = $keyword;
					$subject = "Test email: submission & template";
					$message = "Test email message content area.";
					PC_sendEmail($to, $subject, $message);

					// Log that the message was sent
					error_log("Test Pharmacywire plugin email sent to: " . $to);
					return "Test Pharmacywire plugin email sent to: " . $to;
				}
			} else {
				return "Invalid email address supplied. Please change it in the shortcode to a valid one and refresh the page.";
			}
			break;

		case 'affiliate':
			// shortcode to set active affiliate, will display if valid/invalid if admin user logged in
			if (!empty($keyword)) {
				$affiliateCode = $keyword;
				$affiliate = new Model_Affiliate($affiliateCode);

				if (current_user_can('administrator')) {
					if (isset($affiliate->id)) {
						return '<span class="note">Code: ' . $affiliateCode . ' is a valid affiliate.</span><br />';
					} else {
						return '<span class="note">Code: ' . $affiliateCode . ' is not a valid affiliate.</span><br />';
					}
				}
			}
			break;

		case 'searchform':
			$pwSearchForm = pwire_searchform();
			return $pwSearchForm;
			break;

		case 'coupon':
			if (!empty($keyword)) {
				// execute shortcode only when viewing the frontend page as a logged in user
				if (!is_admin()) {
					$couponCode = $keyword;
					$coupon = new Model_Coupon();
					if (empty($atts['action']) || $atts['action'] == 'apply' || $atts['action'] == 'add') {
						$coupon->applyCoupon($couponCode);
					} elseif ($atts['action'] == 'remove') {
						$coupon->removeCouponSession($couponCode);
					}
				}
			}
			break;
		
		case 'widget':
			// Return widget content
			ob_start();

			if ($keyword == 'account tools') {
				$instance = array(
					'hide_heading' => 'off',
					'vertical_menu' => 'off',
					'hide_account' => 'off',
					'hide_login' => 'off',
					'hide_cart' => 'off',
				);

				if (!empty($atts['options'])) {
					$options = array_map('trim', explode(';', $atts['options']));
					foreach ($options as $option) {
						if (array_key_exists($option, $instance)) {
							$instance[$option] = 'on';
						}
					}
				}
				
				the_widget( 'PWIRE_Ajax_AccountTools_Widget', $instance );
			} elseif($keyword == 'shopping cart') {
				the_widget( 'PWIRE_Ajax_ShoppingCart_Widget' );
			}

			$content = ob_get_contents();
			ob_end_clean();

			return $content;
			break;

		default:
			return stripslashes($Page->render());
	}
}

add_shortcode('PharmacyWire', 'pharmacy_shortcode');

function pw_shortcode_documentupload()
{
	// https://www.dropzonejs.com/#configuration
	// limit to upload and checkout pages
	wp_register_script('dropzone-js', plugins_url('/Themes/vendor/dropzone/js/dropzone.js', __FILE__), array(), PWIRE_VERSION, false);
	wp_enqueue_script('dropzone-js');

	$uploadPage = new Page_UploadDocument();
	$content = $uploadPage->render();
	return $content;
}
add_shortcode('PharmacyWire_DocumentUpload', 'pw_shortcode_documentupload');

//add dashboard
function report_dashboard_widget()
{
	$reportPage = new Page_Report();
	echo $reportPage->render();
}

// function report_add_dashboard_widget() {

// 	if ( !current_user_can('pharmacywire_reports') )  {
// 		return;
// 	}
// 	$arrReportType = array('recent_orders'=>'Recent Orders','commission_last_month'=>'Recent Sales Summary');
// 	$reportType = 'recent_orders';
// 	if (isset($_REQUEST["hdReportType"])) {
// 		$reportType = $_REQUEST["hdReportType"];
// 	}
// 	$titleReport = 'PharmacyWire - '. $arrReportType[$reportType];
// 	wp_add_dashboard_widget( 'report-custom-widget', $titleReport, 'report_dashboard_widget' );
// }
// add_action( 'wp_dashboard_setup', 'report_add_dashboard_widget' );

add_filter('manage_edit-page_columns', 'pwire_edit_page_columns');

function pwire_edit_page_columns($columns)
{
	$columns['post_name'] = __('Slug');

	return $columns;
}

add_action('manage_page_posts_custom_column', 'pwire_manage_page_columns', 10, 2);

function pwire_manage_page_columns($column, $post_id)
{
	global $post;

	switch ($column) {
			/* If displaying the 'page_name' column. */
		case 'post_name':
			echo $post->post_name;
			break;
		default:
			break;
	}
}

function restrict_access_to_pharmacywire_created_pages()
{
	// if not administrator, kill WordPress execution and provide a message

	// get pharmacywire pages
	$pw_page_list = get_option('pw_page_list');

	// flip the keys for the values so that the keys are the post_ids
	$pw_page_list_by_postid = array_flip($pw_page_list);

	// get the post_id
	if (isset($_GET['post'])) {
		$post_id = (int) $_GET['post'];
	} elseif (isset($_POST['post_ID'])) {
		$post_id = (int) $_POST['post_ID'];
	} else {
		$post_id = 0;
	}

	if ($_SERVER['PHP_SELF'] === '/wp-admin/post.php' && array_key_exists($post_id, $pw_page_list_by_postid) && !current_user_can('pharmacywire_settings')) {
		wp_die(__('You are not allowed access to edit this PharmacyWire page.'));
	}
}
add_action('admin_init', 'restrict_access_to_pharmacywire_created_pages', 1);

/*
if(!function_exists('preRenderData')){
	function preRenderData(){
		global $pharmacy_redirect;
		if(!is_null($pharmacy_redirect) and !empty($pharmacy_redirect)){
			header('Location:' . $pharmacy_redirect );exit;
		}
	}
}
*/

function pwire_custom_login_logo()
{
	echo '<style type="text/css">
		body.login { background-color: #2B3439; }
		.login h1 a { background-image:url(' . ADMIN_URL . '/images/pwire-login-logo.png) !important; background-size: 100%; width:100%; height: 72px !important; margin-bottom: 0; }
		.login #loginform { margin-top: 10px; }
		.login #nav a, .login #backtoblog a { color: #FFF !important; }
		.login #nav a:hover, .login #backtoblog a:hover { color: #00AEEF !important; }
		.login #nav, .login #backtoblog { text-shadow: 0 1px 0 #000; }
	</style>';
}

add_action('login_head', 'pwire_custom_login_logo');

function my_custom_login_url()
{
	return site_url();
}
add_filter('login_headerurl', 'my_custom_login_url');

function login_header_text()
{
	return get_bloginfo('name');
}
add_filter('login_headertext', 'login_header_text');

function pwire_searchform()
{
	$form = '<div class="pwire-search-container search-box grid-container"><form action="' . PC_getSearchURL() . '" class="pwire-search-form drug-search-form pw-search-autocomplete grid-x" method="get">
			<div class="pwire-search-name small-12 medium-auto cell">
				<input type="text" placeholder="Search for medication" name="drugName" id="drugName" autocomplete="off" />
			</div>
			<div class="pwire-search-button small-12 medium-3 large-3 cell">
				<input type="submit" class="button" value="search" />
			</div>
	</form></div>';

	//  *** Supports filter to override standard form in theme, eg: ***
	//  add_action('pwire_custom_searchform', 'pwire_theme_searchform');
	//	function pwire_theme_searchform($form) {
	//		$form = return_searchBlock(); // custom form HTML
	//
	//		return $form;
	//	}

	if (has_filter('pwire_custom_searchform')) {
		$form = apply_filters('pwire_custom_searchform', $form);
	}

	return $form;
}

// affiliate code handling
if (isset($_GET['ac']) && ($_GET['ac'] && !empty($_GET['ac']))) {
	$affiliateCode = $_GET['ac'];
	$affiliate = new Model_Affiliate($affiliateCode);
}

// https://github.com/YahnisElsts/plugin-update-checker
if (get_option('pw_disable_plugin_update', 0) != 1) {
	require_once VENDOR_FOLDER . 'plugin-update-checker/plugin-update-checker.php';

	$pwUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
		'https://www.pharmacywire.com/pw-plugin-updater/?action=get_metadata&slug=wp-pharmacywire-v3',
		__FILE__, //Full path to the main plugin file or functions.php.
		'wp-pharmacywire-v3'
	);
	//Add the license key to query arguments.
	$pwUpdateChecker->addQueryArgFilter('wsh_filter_update_checks');

	function wsh_filter_update_checks($queryArgs)
	{
		$license = get_option('pw_update_license', '');
		if (!empty($license)) {
			$queryArgs['license_key'] = urlencode($license);
		}
		$xmlConnectUrl = get_option('pw_url');
		if (!empty($xmlConnectUrl)) {
			$queryArgs['xmlc_url'] = urlencode($xmlConnectUrl);
		}
		return $queryArgs;
	}
}

/**
 * Outputs unset license key notices.
 */
function pw_licence_error_notices__error()
{
	if (empty(get_option('pw_update_license', '')) && (current_user_can('pharmacywire_settings'))) :
	?>
		<div class="updated">
			<p class="wp-pw-updater-dismiss" style="float:right;"></p>
			<p><?php printf('To get updates for the WP-PharmacyWire (V3) Plugin please enter your <a href="%s">plugin update license key</a>.', esc_url(admin_url('admin.php?page=wp-pharmacywire-v3/wp-pharmacywire.php'))); ?></p>
		</div>
<?php
	endif;
}

function pwire_maybe_define_constant( $name, $value ) {
	if ( ! defined( $name ) ) {
		define( $name, $value );
	}
}

/* Nocache constants for 3rd party plugins that support them */
function set_nocache_constants( $return = true ) {
	pwire_maybe_define_constant( 'WP_CACHE', false );
	pwire_maybe_define_constant( 'DONOTCACHEPAGE', true );
	pwire_maybe_define_constant( 'DONOTCACHEOBJECT', true );
	pwire_maybe_define_constant( 'DONOTCACHEDB', true );

	return $return;
}

/**
 * Prevent caching on certain pages, using approach from WC
 */
function prevent_caching() {
	$doNotCache = 0;

	if ( ! is_blog_installed() ) {
		return;
	}
	
	// Never cache when logged in and set pwire_logged_in cookie for 3rd party cache plugins manual integration
	if (WebUser::isLoggedIn()) {
		$doNotCache = 1;
		$nonce = wp_create_nonce( 'pwire_logged_in' . get_site_url() . WebUser::getUserID() );
		if (!isset($_COOKIE['pwire_logged_in'])) {
			setcookie('pwire_logged_in', $nonce, time() + WEEK_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN);
		}
	} else {
		// not logged in - make sure cookie is expired/removed
		setcookie('pwire_logged_in', 1, time() - WEEK_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN);

		$currentPage = strtok(PC_getCurrentURL(), '?');
		$pageUrls = array_filter( 
			array( 
				PC_getShoppingURL(), 
				PC_getJSONCheckout(), 
				PC_getJSONLogin(),
				PC_getLoginUrl(),
				PC_getLogoutUrl(),
				PC_getForgotUrl(),
				PC_getChangePassUrl(),
				PC_getProfileUrl(),
				PC_getReorderUrl(),
				PC_getRegisterUrl(),
			) 
		);
		
		// Check core pages that are not to be cached and set doNotCache flag
		foreach($pageUrls as $pUrl) {
			if (strpos($currentPage, $pUrl) !== false) {
				$doNotCache = 1;
			}
		}
		// If doNotCache not yet set, and product cache not enabled - check to set doNotCache flag
		if (($doNotCache == 0) && (get_option('pw_enable_product_cache', 0) == 0)) {
			$productUrls = array_filter(
				array(
					PC_getSearchURL(),
				)
			);
			foreach($productUrls as $prodUrl) {
				if ((strpos($currentPage, $prodUrl) !== false) || (strpos($currentPage, '/product/') !== false) || ('product' == get_post_type())) {
					$doNotCache = 1;
				}
			}
		}
	}

	if ($doNotCache) {
		set_nocache_constants();
		nocache_headers();
	} 
}

add_action('wp', 'prevent_caching');

?>