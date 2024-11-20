<?php
define('WP_CACHE', false);
define( 'DONOTCACHEPAGE', true );
define( 'DONOTCACHEOBJECT', true );
define( 'DONOTCACHEDB', true );

if (file_exists('../../../wp/wp-load.php')) {
	require_once('../../../wp/wp-load.php');
} else {
	require_once('../../../wp-load.php');
}

send_origin_headers();
send_nosniff_header();
nocache_headers();
header( 'Content-Type: text/html; charset=' . get_option( 'blog_charset' ) );
header( 'X-Robots-Tag: noindex' );
status_header( 200 );

$sessionHandler = new Utility_SessionHandler();
if (session_status() !== PHP_SESSION_ACTIVE) {
	session_set_save_handler(array(&$sessionHandler, "open"), array(&$sessionHandler, "close"), array(&$sessionHandler, "read"), array(&$sessionHandler, "write"), array(&$sessionHandler, "destroy"), array(&$sessionHandler, "gc"));
	session_start();
}

// Close the session before $wpdb destructs itself. Quit early to avoid WP interferance/complaints in Site Health.
add_action( 'shutdown', 'session_write_close', 100, 0);

require_once(ROOT_PLUGIN . 'config.php');
require_once(MODEL_FOLDER . 'Country.php');
require_once(UTILITY_FOLDER . 'Cart.php');

/*
    File to handle common patient requests
*/

$patientModel = new Model_Patient();
$patient = new Model_Entity_Patient();
$patient->patientid = WebUser::getUserID();

function is_user_logged_in_pwire()
{
	if (!WebUser::isLoggedIn()) {
		if (!headers_sent()) {
			header('x-pwire-token-status: expired');
		}
		// Utility_PageBase::redirect(PC_getLoginUrl());
		die();
	}
	return;
}

switch ($_GET) {

	case (!empty($_GET['CatalogStatus'])):

		$catalogProgress = array('Status' => get_option('pw_catalog_update_progress'));

		echo json_encode($catalogProgress);

		break;

	case !empty($_GET['country-code']):

		$countryModel = new Model_Country();
		$countryCode = $_GET['country-code'];
		$provinces = $countryModel->getRegionsByCountry($countryCode);

		echo json_encode(array('Region' => $provinces));

		break;

	case isset($_GET['recent-orders']):

		is_user_logged_in_pwire();

		$reply = $patientModel->getRecentOrders(WebUser::getUserID());

		echo json_encode(array('RecentOrders' => $reply->orders));

		break;

	case (isset($_GET['recent-order']) && !empty($_GET['recent-order-id'])):

		is_user_logged_in_pwire();

		$recentOrderId = $_GET['recent-order-id'];

		$recentOrders = new Model_Entity_Order();
		$recentOrders = $patientModel->getOrdersJson($patient->patientid, $recentOrderId);

		echo $recentOrders;

		break;

	case isset($_GET['profile-info']):

		is_user_logged_in_pwire();

		$result = $patientModel->getPatientInfo($patient);
		$result = $patientModel->getPatientInfoJSON($result->patient);

		$result = json_decode($result);

		$birthdate = DateTime::createFromFormat('Y-m-d', $result->dateofbirth);
		$result->dateofbirth_long = '';
		if (!empty($birthdate)) {
			$result->dateofbirth_long = $birthdate->format('F d, Y');
		}
		if ($result->sex) {
			$result->sex = ($result->sex == 'M') ? 'Male' : 'Female';
		}
		if ($result->call_for_refills) {
			$result->call_for_refills = strtolower($result->call_for_refills) == 'true' ? 'Yes' : 'No';
		}
		$callForRefills = get_option('pw_checkoutq_call_for_refills', 1);
		$childResistantPackage = get_option('pw_checkoutq_child_resistant_packaging', 1);

		// if disabled, set to empty string for doT js template logic
		if (!$callForRefills) {
			if ($result->child_resistant_packaging) {
				$result->child_resistant_packaging = '';
			}
		}
		if (!$childResistantPackage) {
			if ($result->call_for_refills) {
				$result->call_for_refills = '';
			}
		}

		echo json_encode(array('ProfileInfo' => array($result)));

		break;

	case isset($_GET['address-manager']):

		is_user_logged_in_pwire();

		$shippingAddress = new Page_Checkout_Edit_ShippingAddress();
		$addressList = $shippingAddress->genAddressJson(Shipping::getAddressRef());

		echo json_encode(array('Address' => array('AddressList' => $addressList)));

		break;

	case (!empty($_GET['coupon-code']) && (isset($_GET['remove-coupon'])) && ($_GET['remove-coupon'] == 1)):
		// remove coupon
		$couponCode = $_GET['coupon-code'];

		if (!empty($couponCode)) {
			check_ajax_referer('coupon-nonce', 'coupon_nonce');

			$coupon = new Model_Coupon();
			$removeCouponResponse = $coupon->removeCouponSession($couponCode);

			echo $removeCouponResponse;
		}

		break;

	case !empty($_GET['coupon-code']):
		// check if coupon is valid and apply it to order if it is

		$couponCode = $_GET['coupon-code'];

		if (!empty($couponCode)) {
			check_ajax_referer('coupon-nonce', 'coupon_nonce');

			$coupon = new Model_Coupon();

			$patientID = $patient->patientid;
			if (!empty($patientID) && is_int($patientID)) {
				$patientID = $patient->patientid;
			} else {
				$patientID = null;
			}

			$couponResponse	= $coupon->applyCoupon($couponCode, $patientID);

			echo $couponResponse;
		}

		break;

	case (!empty($_GET['tag-code']) && !empty($_GET['tag-value'])):
		// check if tag is valid and apply it to order if it is

		$tagCode = $_GET['tag-code'];
		$tagValue = $_GET['tag-value'];

		if (!empty($tagCode) && !empty($tagValue)) {
			check_ajax_referer('tag-nonce', 'tag_nonce');

			$tagData['tag-code'] = $tagCode;
			$tagData['tag-value'] = $tagValue;
			$tag = new Model_OrderTag();
			$tagResponse	= $tag->applyTag($tagData);

			echo $tagResponse;
		}

		break;

	case (!empty($_GET['tag-code'])	&& (isset($_GET['remove-tag'])) && ($_GET['remove-tag'] == 1)):
		// remove tag
		$tagCode = $_GET['tag-code'];

		if (!empty($tagCode)) {
			check_ajax_referer('tag-nonce', 'tag_nonce');

			$orderTag = new Model_OrderTag();
			$removeTagResponse = $orderTag->removeTagSession($tagCode);

			echo $removeTagResponse;
		}

		break;

	case (!empty($_GET['cart']) && ($_GET['cart'] == 'info')):
		echo Cart::getCartJSON(true);

		break;


	case (!empty($_GET['cart']) && ($_GET['cart'] == 'add-package') && !empty($_GET['package-id'])):
		$result = Cart::add($_GET['package-id'], $_GET['quantity']);
		if (!$result) {
			$error = "Package doesn't exist";
		}
		echo Cart::getCartJSON($result, $error);

		break;

	case (isset($_GET['cart']) && ($_GET['cart'] == 'remove-package') && !empty($_GET['package-id'])):
		$result = Cart::remove($_GET['package-id']);
		if (!$result) {
			$error = "Package doesn't exist";
		}
		echo Cart::getCartJSON($result, $error);

		break;

	default:
		break;
}
