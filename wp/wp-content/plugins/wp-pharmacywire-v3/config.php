<?php
// define CONSTANTS for system
define('SLASH', '/');

define('PWIRE_PLUGIN_FOLDERNAME', basename(__DIR__));
define('PWIRE_PLUGIN_URL', plugin_dir_url(__FILE__));
define('ADMIN_URL', PWIRE_PLUGIN_URL . 'Admin' . SLASH);
define('THEME_URL', PWIRE_PLUGIN_URL . 'Themes' . SLASH);

define('IS_RELEASE_SITE', !get_option('pw_is_dev_site_connection'));
define('ROOT_PLUGIN', dirname(__FILE__) . SLASH);
define('URL_PROVIDER', get_option('pw_url'));
define('POST_XML_VAR', 'xmldoc');
define('VENDOR_USERNAME', get_option('pw_user_id'));
define('VENDOR_PASSKEY', get_option('pw_passkey'));
define('SCHEDULE_TIME_REFRESH', get_option('pw_schedule_time', '03:00'));
define('SCHEDULE_TIME_ENABLED', get_option('pw_enable_schedule'));
define('UTILITY_FOLDER', ROOT_PLUGIN . 'Utility' . SLASH);
define('VENDOR_FOLDER', ROOT_PLUGIN . 'vendor' . SLASH);
define('VENDOR_URL', PWIRE_PLUGIN_URL . 'vendor' . SLASH);
define('THEMES_FOLDER', ROOT_PLUGIN . 'Themes' . SLASH);
define('XML_API_FOLDER', ROOT_PLUGIN . 'XmlApi' . SLASH);
define('XML_API_REQUEST', XML_API_FOLDER . 'Request' . SLASH);
define('XML_API_PARSE_DATA', XML_API_FOLDER . 'ParseData' . SLASH);
define('MODEL_FOLDER', ROOT_PLUGIN . 'Model' . SLASH);
define('MODEL_ENTITY_FOLDER', MODEL_FOLDER . 'Entity' . SLASH);
define('MODEL_RESOURCE_FOLDER', MODEL_FOLDER . 'Resource' . SLASH);
define('PAGE_FOLDER', ROOT_PLUGIN . 'Page' . SLASH);
define('ADMIN_FOLDER', ROOT_PLUGIN . 'Admin' . SLASH);
define('XTPL_DIR', ROOT_PLUGIN . 'Themes' . SLASH . 'templates' . SLASH);

define('REQUEST_URL', PWIRE_PLUGIN_URL . 'request.php');
define('JSON_API_FOLDER', ROOT_PLUGIN . 'JsonApi' . SLASH);
define('JSON_TEMPLATE_FOLDER', JSON_API_FOLDER . 'templates' . SLASH);
define('JSON_REQUEST_URL', PWIRE_PLUGIN_URL . 'JsonApi' . SLASH . 'request_json.php');
define('JSON_TEMPLATE_URL', PWIRE_PLUGIN_URL . 'JsonApi' . SLASH . 'templates' . SLASH);


// define XML namespace
define('XML_NS_PWIRE', 'http://www.pharmacywire.com/');
define('XML_NS_PWIRE5', 'http://www.pharmacywire.com/v5');
define('XML_NS_MOMEX', 'http://www.metrex.net/momex#');
define('XML_NS_MOMEX_TERMS', 'http://www.metrex.net/momex/terms#');
define('XML_JOIN_SYMBOL', '@');
define('XML_PWIRE', 'pwire');
define('XML_PWIRE5', 'pwire5');
define('XML_MOMEX', 'momex');
define('XML_PW', 'pw');
define('XML_MT', 'mt');
define('XML_STATUS_SUCCESS', 'success');
define('XML_STATUS_INVALID', 'invalid_xml');
define('XML_STATUS_FAILURE', 'failure');
define('EMAIL_COMPANY', get_option('pw_email'));
define('EMAIL_RX', get_option('pw_email_rx'));

$rxOrderLimit = get_option('rx_order_limit') ? get_option('rx_order_limit') : 3;
$otcOrderLimit = get_option('otc_order_limit') ? get_option('otc_order_limit') : 10;
define("RX_ORDER_LIMIT", $rxOrderLimit);
define("OTC_ORDER_LIMIT", $otcOrderLimit);

defined('OBJECT') || define('OBJECT', 'OBJECT');
defined('OBJECT_K') || define('OBJECT_K', 'OBJECT_K');
defined('ARRAY_N') || define('ARRAY_N', 'ARRAY_N');
defined('ARRAY_A') || define('ARRAY_A', 'ARRAY_A');

/**
 * Used to guarantee unique hash cookies.
 *
 */
if ( ! defined( 'COOKIEHASH' ) ) {
	$siteurl = get_site_option( 'siteurl' );
	if ( $siteurl ) {
		define( 'COOKIEHASH', md5( $siteurl ) );
	} else {
		define( 'COOKIEHASH', '' );
	}
}

if ( ! defined( 'COOKIEPATH' ) ) {
	define( 'COOKIEPATH', preg_replace( '|https?://[^/]+|i', '', get_option( 'home' ) . '/' ) );
}

if ( ! defined( 'SITECOOKIEPATH' ) ) {
	define( 'SITECOOKIEPATH', preg_replace( '|https?://[^/]+|i', '', get_option( 'siteurl' ) . '/' ) );
}

// include all file need for plugin
include(ROOT_PLUGIN . SLASH . "Utility" . SLASH . "Common.php");
Utility_Common::loadFileInFolder(UTILITY_FOLDER);
Utility_Common::loadFileInFolder(XML_API_FOLDER);
Utility_Common::loadFileInFolder(XML_API_REQUEST);
Utility_Common::loadFileInFolder(XML_API_PARSE_DATA);
Utility_Common::loadFileInFolder(MODEL_FOLDER);
Utility_Common::loadFileInFolder(MODEL_ENTITY_FOLDER);
Utility_Common::loadFileInFolder(MODEL_RESOURCE_FOLDER);
Utility_Common::loadFileInFolder(PAGE_FOLDER);
