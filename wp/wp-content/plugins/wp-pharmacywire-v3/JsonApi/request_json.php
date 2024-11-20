<?php
define('WP_CACHE', false);
define( 'DONOTCACHEPAGE', true );
define( 'DONOTCACHEOBJECT', true );
define( 'DONOTCACHEDB', true );

require_once('../../../../wp-load.php');
require_once('../wp-pharmacywire.php');

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

if (!class_exists('PW_JSON', false)) {
	include_once dirname(__FILE__) . '/class-pw-json.php';
}

$request = '';
if (!empty($_POST['r'])) {
	$request = $_POST['r'];
} elseif (!empty($_GET['r'])) {
	$request = $_GET['r'];
}

$pwire_request = '';
if (!empty($_REQUEST['pwire_req'])) {
	$pwire_request = $_REQUEST['pwire_req'];
}

// Nonce verification for requests coming from PharmacyWire
function verifyPwireNonce($reqNonce = null) {
	$xmlConnectUrl = get_option('pw_url');
	$navCodeBase = preg_replace('/xmlconnect\/?/', '', $xmlConnectUrl);
	$updateLicense = get_option('pw_update_license');
	$noncePwireUrl = $navCodeBase . 'eventhandler/pw_vendor/WP/pw_key/' . $updateLicense . '/method/nonce/pw_nonce/' . $reqNonce;
	$response = wp_remote_request($noncePwireUrl);
	if(!is_wp_error($response) && ($response['response']['code'] == 200 || $response['response']['code'] == 201)) {
		$responseBody = wp_remote_retrieve_body($response);	
		$jsonResponse = json_decode($responseBody, true);
		if (strtolower($jsonResponse['status']) === "success") {
			return true;
		}
	}
	return false;
}

// Immediate http response mechanism for long process such as catalog update
function immediateJsonResponse($jsonReply) {
	// Buffer all upcoming output...
    ob_start();

    // Send your response.
    echo $jsonReply;

    // Get the size of the output.
    $size = ob_get_length();
    // Disable compression (in case content length is compressed).
    header("Content-Encoding: none");
    // Set the content length of the response.
    header("Content-Length: {$size}");
    // Close the connection.
    header("Connection: close");

    // Flush all output.
    ob_end_flush();
    ob_flush();
	flush();

	// Close current session (if it exists).
	if(session_id()) session_write_close();
}

// PharmacyWire requests
if (!empty($pwire_request) && !empty($_REQUEST['pw_nonce'])) {
	// check pharmacywire generated pw_nonce and terminate if it fails
	if (!verifyPwireNonce($_REQUEST['pw_nonce'])) {
		die();
	}

	switch($pwire_request) {
			//  1. Catalog

			// 1. Catalog
			// ---------

		case 'catalog_update':
			$reply = new Model_Entity_Reply();
			$reply->status = "success";
			$reply->message = "Catalog update request received.";
			immediateJsonResponse($reply->toJSON());

			$catalogModel = new Model_Catalog();
			$catalogModel->buildCache();
			break;
		case 'catalog_status':
			get_option('pw_catalog_last_update_time');
			$lastUpdateTime = date('Y-m-d H:i:s', get_option('pw_catalog_last_update_time'));
			$lastUpdateTime = get_date_from_gmt($lastUpdateTime, 'D M jS, Y; g:ia T');

			$reply = new Model_Entity_Reply();
			$reply->status = (get_option('pw_catalog_update_progress') == 'new') ? 'completed' : get_option('pw_catalog_update_progress');
			$reply->lastUpdate = $lastUpdateTime;
			echo $reply->toJSON();
			break;
	}

	die();
}

// POST requests
if (!empty($request) && $_SERVER['REQUEST_METHOD'] === 'POST') {
	// check plugin generated pw-nonce and terminate if it fails
	check_ajax_referer( 'pw-nonce', 'pw_nonce');
	
	switch ($request) {

			//  1. General
			//  2. Session
			//  3. Patient
			//  4. Billing
			//  5. Shipping
			//  6. Cart
			//  7. Orders
			//  8. Medical Questions
			//  9. Widgets


			// 1. General
			// ---------

		case 'get-pharmacy-components':
			$pwjPharmacy = new PW_JSON_Pharmacy();
			echo $pwjPharmacy->getPharmacyComponents();
			break;

			// 2. Session
			// ---------

		case 'set-pw-autosave':
			$pwjSession = new PW_JSON_Session();
			$data = $_POST['dataset'];
			$datasetName = $_POST['datasetName'];
			$pwjSession->setAutosave($datasetName, $data);

			$reply = new Model_Entity_Reply();
			$reply->success = 1;
			$reply->message = 'session stored.';

			echo $reply->toJSON();

			break;

			// 3. Patient
			// ---------

		case 'create-patient':
			$pwjPatient = new PW_JSON_Patient();
			echo $pwjPatient->createPatient();
			break;

		case 'get-patient':
			$pwjPatient = new PW_JSON_Patient();
			echo $pwjPatient->getPatient();
			break;

		case 'get-patient-addresses':
			$pwjPatient = new PW_JSON_Patient();
			echo $pwjPatient->getAddresses();
			break;

		case 'login':
			$pwjPatient = new PW_JSON_Patient();
			$onSuccessRedirect = '';
			if (isset($_POST['onsuccessredirect']) && !empty($_POST['onsuccessredirect'])) {
				$onSuccessRedirect = $_POST['onsuccessredirect'];
			}
			echo $pwjPatient->login($onSuccessRedirect);
			break;

		case 'logout':
			$pwjPatient = new PW_JSON_Patient();
			echo $pwjPatient->logout();
			break;

		case 'logged-in':
			$reply = new Model_Entity_Reply();
			$reply->success = 1;
			$reply->logged_in = (WebUser::isLoggedIn()) ? 1 : 0;
			echo $reply->toJSON();
			break;
			
			// 4. Billing
			// ---------

		case 'get-billing':
			$pwjBilling = new PW_JSON_Billing();
			$pwjBilling->getBilling();
			break;

		case 'set-billing':
			break;


			// 5. Shipping
			// ---------

		case 'get-shipping':
			$pwjShipping = new PW_JSON_Shipping();
			$pwjShipping->getShipping();
			break;

		case 'set-shipping':
			$pwjShipping = new PW_JSON_Shipping();
			$pwjShipping->setShipping();
			break;

		case 'edit-shipping':
			$pwjShipping = new PW_JSON_Shipping();
			$pwjShipping->editShipping();
			break;

		case 'delete-shipping':
			$pwjShipping = new PW_JSON_Shipping();
			$pwjShipping->deleteShipping();
			break;

			// 6. Cart
			// ---------

		case 'get-cart':
			$pwjCart = new PW_JSON_Cart();
			$pwjCart->getCart();
			break;


			// 7. Orders
			// ---------

		case 'submit-order':
			// if patient details included attempt to create the patient first
			if (isset($_POST['Username']) && !WebUser::isLoggedIn()) {
				$pwjPatient = new PW_JSON_Patient();
				$createPatientResponseJson = $pwjPatient->createPatient(0);
				$createPatientResponse = json_decode($createPatientResponseJson);
				// only stop & echo json if create patient failed
				if ($createPatientResponse->success == 0) {
					echo $createPatientResponseJson;
					return;
				}
			}

			// if patient logged in
			if (WebUser::isLoggedIn()) {
				$pwjCart = new PW_JSON_Cart();
				$submitOrderResponseJson = $pwjCart->submitOrder();
				$submitOrderResponse = json_decode($submitOrderResponseJson);
				echo $submitOrderResponseJson;
				return;
			}
			break;

		case 'set-order-comment':
			if (isset($_POST['orderID']) && isset($_POST['orderComment']) && WebUser::isLoggedIn()) {
				$data = new stdClass();
				$data->patientID        = WebUser::getUserID();
				$data->orderID 			= $_POST['orderID'];
				$data->orderComment	= $_POST['orderComment'];
				$orderModel = new Model_Order();
				$reply = new Model_Entity_Reply();
				$reply = $orderModel->addComment($data);
				echo $reply->toJSON();
			}
			break;

			// 8. Medical Questions
			// ---------

		case 'get-medical-questions':
			$pwjMedicalQuestions = new PW_JSON_MedicalQuestions();
			$pwjMedicalQuestions->getMedicalQuestions();
			break;

		case 'set-medical-answers':
			$pwjMedicalQuestions = new PW_JSON_MedicalQuestions();
			$pwjMedicalQuestions->setMedicalAnswers();
			break;

			// 8. Medical Questions
			// ---------
		case 'get-account-tools-info':
			$reply = new Model_Entity_Reply();
			$reply->success = 1;
			$reply->logged_in = (WebUser::isLoggedIn()) ? 1 : 0;
			$reply->cart = array('cart_item_count' => Cart::getItemCount());
			echo $reply->toJSON();
			break;
	}
}

die();
