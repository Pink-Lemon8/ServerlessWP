<?php
// defined pages of system
define('PHARMACY_HOME_PAGE', 'home');
define('PHARMACY_SEARCH_PAGE', 'search');
define('PHARMACY_DRUGDETAIL_PAGE', 'detail');
define('PHARMACY_CONTACT_PAGE', 'contact');
define('PHARMACY_CHECKOUT_SHOPPING', 'shopping');
define('PHARMACY_CHECKOUT_SHIPPING', 'shipping');
define('PHARMACY_CHECKOUT_BILLING', 'billing');
define('PHARMACY_CHECKOUT_CONFIRM', 'confirm');
define('PHARMACY_CHECKOUT_THANK_YOU', 'thankyou');
define('PHARMACY_CHECKOUT_EDIT_SHIPPING_ADRESS', 'change_shipping_address');
define('PHARMACY_REGISTER_PAGE', 'register');
define('PHARMACY_LOGIN_PAGE', 'login');
define('PHARMACY_LOGOUT_PAGE', 'logout');
define('PHARMACY_PROFILE_PAGE', 'profile');
define('PHARMACY_CHANGEPASSWORD_PAGE', 'change_password');
define('PHARMACY_FORGOTPASSWORD_PAGE', 'forgotpassword');
define('PHARMACY_PROFILEADDRESS_PAGE', 'profileaddress');
define('PHARMACY_PROFILE_INFO_PAGE', 'profile_info');
define('PHARMACY_PROFILE_EDIT_PAGE', 'profile_edit');
define('PHARMACY_REORDER_PAGE', 'reorder');
define('PHARMACY_VIEW_ORDER_PAGE', 'view_order');
define('PHARMACY_REPORT_DETAILS_PAGE', 'report_details');
define('PHARMACY_UPLOAD_DOCUMENT_PAGE', 'upload_document');

//get Url of Home page
function PC_getHomePageURL()
{
	//$PC_url = (PC_getUrl('Pharmacy_Home'));
	$PC_url = get_site_url() . "/";
	return $PC_url;
}
//get Url of Search page
function PC_getSearchURL()
{
	global $pw_page_list;
	$page_id = $pw_page_list["Pharmacy_Search_Drug"];
	$PC_url  = get_permalink($page_id);
	if (empty($PC_url)) {
		$PC_url = (PC_getUrl('Pharmacy_Search_Drug'));
	}
	return $PC_url;
}
//get Url of Search page
function PC_getSearchSlug()
{
	global $pw_page_list;
	$page_id = $pw_page_list["Pharmacy_Search_Drug"];
	$PC_slug  = get_post_field($page_id);
	if (empty($PC_slug)) {
		$PC_slug = (PC_getSlug('Pharmacy_Search_Drug'));
	}
	return 	$PC_slug;
}
//get Url of Search page
function PC_getSearchID()
{
	global $pw_page_list;
	$page_id = $pw_page_list["Pharmacy_Search_Drug"];
	return 	$page_id;
}
//get Url of Drug Detail page
function PC_getDrugDetail()
{
	global $pw_page_list;
	$page_id = $pw_page_list["Pharmacy_Search_Detail"];
	$PC_url  = get_permalink($page_id);
	if (empty($PC_url)) {
		$PC_url = (PC_getUrl('Pharmacy_Search_Detail'));
	}
	return $PC_url;
}
//get Url of Contact page
function PC_getContactURL()
{
	global $pw_page_list;
	$page_id = $pw_page_list["Pharmacy_Contact"];
	$PC_url  = get_permalink($page_id);
	if (empty($PC_url)) {
		$PC_url = (PC_getUrl('Pharmacy_Contact'));
	}
	return $PC_url;
}

// cleans the provided string of any unacceptable characters for a URL
function clean($string)
{
	$string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.
	$string = preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.

	return preg_replace('/-+/', '-', $string); // Replaces multiple hyphens with single one.
}

//get Url of shopping cart page
function PC_getShoppingURL($addTracking = false)
{
	global $pw_page_list;
	$page_id = $pw_page_list["Pharmacy_Checkout_Shopping"];
	$PC_url  = get_permalink($page_id);
	$TrackingOption = get_option('pw_tracking');
	$URL_extension = '';
	if ($addTracking && $TrackingOption) {
		$Referrer = explode('/', getenv('HTTP_REFERER'));
		$Slug = clean($Referrer[sizeof($Referrer) - 2]); // clean the slug in case the referrer is 'poisoned'
		$URL_extension = str_replace('SLUG', $Slug, $TrackingOption);
	}
	if (empty($PC_url)) {
		$PC_url = (PC_getUrl('Pharmacy_Checkout_Shopping'));
	}
	return 	$PC_url . $URL_extension;
}
//get Url of shipping page
function PC_getShippingURL()
{
	global $pw_page_list;
	$page_id = $pw_page_list["Pharmacy_Checkout_Shipping"];
	$PC_url  = get_permalink($page_id);
	if (empty($PC_url)) {
		$PC_url = (PC_getUrl('Pharmacy_Checkout_Shipping'));
	}
	return $PC_url;
}
//get Url of billing page
function PC_getBillingURL()
{
	global $pw_page_list;
	$page_id = $pw_page_list["Pharmacy_Checkout_Billing"];
	$PC_url  = get_permalink($page_id);
	if (empty($PC_url)) {
		$PC_url = (PC_getUrl('Pharmacy_Checkout_Billing'));
	}
	return $PC_url;
}
//get Url of confirm page
function PC_getConfirmURL()
{
	global $pw_page_list;
	$page_id = $pw_page_list["Pharmacy_Checkout_Confirm"];
	$PC_url  = get_permalink($page_id);
	if (empty($PC_url)) {
		$PC_url = (PC_getUrl('Pharmacy_Checkout_Confirm'));
	}
	return $PC_url;
}
//get Url of thank you page
function PC_getThankYouURL()
{
	global $pw_page_list;
	$page_id = $pw_page_list["Pharmacy_Checkout_Thank_You"];
	$PC_url  = get_permalink($page_id);
	if (empty($PC_url)) {
		$PC_url = (PC_getUrl('Pharmacy_Checkout_Thank_You'));
	}
	return $PC_url;
}
//get JSON Login Page
function PC_getJSONLogin()
{
	global $pw_page_list;
	//$page_id = $pw_page_list['PwireJSON template="login"'];
	//$PC_url  = get_permalink($page_id);
	if (empty($PC_url)) {
		$PC_url = (PC_getUrl('PwireJSON template="login"'));
	}
	return $PC_url;
}
//get JSON Checkout Page
function PC_getJSONCheckout()
{
	global $pw_page_list;
	//$page_id = $pw_page_list['PwireJSON template="checkout"'];
	//$PC_url  = get_permalink($page_id);
	if (empty($PC_url)) {
		$PC_url = (PC_getUrl('PwireJSON template="checkout"'));
	}
	return $PC_url;
}
//get Url of Register  page
function PC_getRegisterUrl()
{
	global $pw_page_list;
	$page_id = $pw_page_list["Pharmacy_Register"];
	$PC_url  = get_permalink($page_id);
	if (empty($PC_url)) {
		$PC_url = (PC_getUrl('Pharmacy_Register'));
	}
	return $PC_url;
}
//get Url of login  page
function PC_getLoginUrl()
{
	global $pw_page_list;
	$page_id = $pw_page_list["Pharmacy_login"];
	$PC_url  = get_permalink($page_id);
	if (empty($PC_url)) {
		$PC_url = (PC_getUrl('Pharmacy_login'));
	}
	return $PC_url;
}
//get Url of logout  page
function PC_getLogoutUrl()
{
	global $pw_page_list;
	$page_id = $pw_page_list["Pharmacy_logout"];
	$PC_url  = get_permalink($page_id);
	if (empty($PC_url)) {
		$PC_url = (PC_getUrl('Pharmacy_logout'));
	}
	return $PC_url;
}
//get Url of profile page
function PC_getProfileUrl()
{
	return PC_getProfileInfoUrl();
}
//get Url of checkout edit shipping
function PC_getCheckoutEditAddressUrl()
{
	global $pw_page_list;
	$page_id = $pw_page_list["Checkout_Edit_Shipping_Address"];
	//$PC_url  = get_permalink($page_id);
	//if(empty($PC_url))
	$PC_url = (PC_getUrl('Checkout_Edit_Shipping_Address'));
	return $PC_url;
}
//get URL of forgot password
function PC_getForgotUrl()
{
	global $pw_page_list;
	$page_id = $pw_page_list["Forgot_Password"];
	$PC_url  = get_permalink($page_id);
	if (empty($PC_url)) {
		$PC_url = (PC_getUrl('Forgot_Password'));
	}
	return $PC_url;
}
//get URL of forgot password
function PC_getChangePassUrl()
{
	global $pw_page_list;
	$page_id = $pw_page_list["Change_Password"];
	$PC_url  = get_permalink($page_id);
	if (empty($PC_url)) {
		$PC_url = (PC_getUrl('Change_Password'));
	}
	return $PC_url;
}
function PC_getChangePassPageID()
{
	global $pw_page_list;
	$page_id = $pw_page_list["Change_Password"];
	return 	$page_id;
}
// Get URL of profile address
function PC_getProfileAddressUrl()
{
	global $pw_page_list;
	$page_id = $pw_page_list["Pharmacy_ProfileAddress"];
	$PC_url  = get_permalink($page_id);
	if (empty($PC_url)) {
		$PC_url = PC_getUrl('Pharmacy_ProfileAddress');
	}
	return $PC_url;
}

// Get URL of profile info
function PC_getProfileInfoUrl()
{
	global $pw_page_list;
	$page_id = $pw_page_list["Pharmacy_ProfileInfo"];
	$PC_url  = get_permalink($page_id);
	if (empty($PC_url)) {
		$PC_url = PC_getUrl('Pharmacy_ProfileInfo');
	}
	return $PC_url;
}


// Get URL of reorder page
function PC_getReorderUrl()
{
	global $pw_page_list;
	$page_id = $pw_page_list["Pharmacy_Reorder"];
	if ($page_id) {
		$PC_url  = get_permalink($page_id);
	}
	if (empty($PC_url)) {
		$PC_url = PC_getUrl('Pharmacy_Reorder');
	}
	return $PC_url;
}

// Get URL for the invoice document helper script.
function PC_helperGetInvoiceDocumentUrl()
{
	$PC_url = PWIRE_PLUGIN_URL . 'helper_getinvoicedocument.php';
	return $PC_url;
}

// Get URL of View Order page
function PC_getViewOrderUrl()
{
	global $pw_page_list;
	$page_id = $pw_page_list["Pharmacy_ViewOrder"];
	if ($page_id) {
		$PC_url  = get_permalink($page_id);
	}
	if (empty($PC_url)) {
		$PC_url = PC_getUrl('Pharmacy_ViewOrder');
	}
	return $PC_url;
}

// Get URL of profile edit
function PC_getProfileEditUrl()
{
	global $pw_page_list;
	$page_id = $pw_page_list["Pharmacy_ProfileEdit"];
	$PC_url  = get_permalink($page_id);
	if (empty($PC_url)) {
		$PC_url = PC_getUrl('Pharmacy_ProfileEdit');
	}
	return $PC_url;
}

// Get URL of search all drugs
function PC_getSearchAllDrugUrl()
{
	$PC_url = PC_reCreateUrl('showall=1', PC_getSearchURL());
	return $PC_url;
}

// Get URL of upload document
function PC_getUploadDocumentUrl()
{
	global $pw_page_list;
	$page_id = $pw_page_list["Pharmacy_UploadDocument"];
	$PC_url  = get_permalink($page_id);
	if (empty($PC_url)) {
		$PC_url = PC_getUrl('Pharmacy_UploadDocument');
	}
	return $PC_url;
}
// re-create Url
function PC_reCreateUrl($prama, $url)
{
	$strResult = $url;
	if (!stristr($url, "?")) {
		$strResult = $strResult . "?" . $prama;
	} else {
		$strResult = $strResult . "&" . $prama;
	}
	return $strResult;
}
// get current page
function PC_getCurrentPage()
{
	global $wp_query;
	$cur_page = get_the_ID();

	$default_pages = array(
		'Pharmacy_Search_Drug' => PHARMACY_SEARCH_PAGE,
		'Pharmacy_Search_Detail' => PHARMACY_DRUGDETAIL_PAGE,
		'Pharmacy_Contact' => PHARMACY_CONTACT_PAGE,
		'Pharmacy_Checkout_Shopping' => PHARMACY_CHECKOUT_SHOPPING,
		'Pharmacy_Checkout_Shipping' => PHARMACY_CHECKOUT_SHIPPING,
		'Pharmacy_Checkout_Billing' => PHARMACY_CHECKOUT_BILLING,
		'Pharmacy_Checkout_Confirm' => PHARMACY_CHECKOUT_CONFIRM,
		'Pharmacy_Checkout_Thank_You' => PHARMACY_CHECKOUT_THANK_YOU,
		'Pharmacy_Register' => PHARMACY_REGISTER_PAGE,
		'Pharmacy_login' => PHARMACY_LOGIN_PAGE,
		'Pharmacy_logout' => PHARMACY_LOGOUT_PAGE,
		'Pharmacy_Profile' => PHARMACY_PROFILE_PAGE,
		'Checkout_Edit_Shipping_Address' => PHARMACY_CHECKOUT_EDIT_SHIPPING_ADRESS,
		'Forgot_Password' => PHARMACY_FORGOTPASSWORD_PAGE,
		'Change_Password' => PHARMACY_CHANGEPASSWORD_PAGE,
		'Pharmacy_ProfileAddress' => PHARMACY_PROFILEADDRESS_PAGE,
		'Pharmacy_ProfileInfo' => PHARMACY_PROFILE_INFO_PAGE,
		'Pharmacy_ProfileEdit' => PHARMACY_PROFILE_EDIT_PAGE,
		'Pharmacy_Reorder' => PHARMACY_REORDER_PAGE,
		'Pharmacy_ViewOrder' => PHARMACY_VIEW_ORDER_PAGE,
		'Pharmacy_UploadDocument' => PHARMACY_UPLOAD_DOCUMENT_PAGE
	);
	foreach ($default_pages as $k => $v) {
		$page_id  = PC_getPageIdByPageCode($k);
		$arrPages[$page_id] = $v;
	}
	//check page current
	if (!empty($cur_page) && array_key_exists($cur_page, $arrPages)) {
		return $arrPages[$cur_page];
	} else {
		return PHARMACY_HOME_PAGE;
	}
}

// get Url of a drug ingredient page
function PC_getIngredientUrl($ingredient)
{
	global $wpdb;
	$page_url = $ingredient;
	$post_name = $ingredient . '-drug-information';
	$post_name = preg_replace('/\W/', "-", $post_name);
	$post_name = preg_replace('/-+/', "-", $post_name);
	$page_details = $wpdb->get_row("SELECT * FROM `" . $wpdb->posts . "` WHERE `post_name` = '" . $post_name . "' AND `post_type`= 'page' AND post_status='publish'  LIMIT 1", ARRAY_A);
	if ($page_details !== null) {
		$post_id = $page_details['ID'];
		$page_url = get_permalink($post_id);
		$force_ssl = get_post_meta($post_id, 'force_ssl', true);
		if ($force_ssl && is_plugin_active('wordpress-https/wordpress-https.php')) {
			$page_url = str_replace('http:', 'https:', $page_url);
		}
		$page_url = "<a href=\"$page_url\" class=\"ingredient-link\">$ingredient</a>";
	}
	return $page_url;
}

// get Url of a drug page to replace in search results
// Uses page slug to match on
function PC_getDrugPageUrl($drugName)
{
	global $wpdb;
	if (strlen($drugName) <= 1) {
		return '';
	}
	$post_name = $drugName;
	$post_name = preg_replace('/\W/', "-", $post_name);
	$post_name = preg_replace('/-+/', "-", $post_name);
	$post_name = preg_replace('/-$/', "", $post_name);

	$pageSearchQuery = $wpdb->prepare("SELECT * FROM `" . $wpdb->posts . "` 
		WHERE `post_name` LIKE %s 
		AND NOT `post_name` LIKE %s 
		AND `post_status`='publish' 
		ORDER BY CASE WHEN `post_type` = 'product' THEN 1 ELSE 2 END, IF(post_name = %s, 0, 1)", 
		[$post_name . '%', $post_name . '-drug-information%', $post_name]
	);
	$page_details = $wpdb->get_results($pageSearchQuery, ARRAY_A);
	
	$page_url = '';
	if (!empty($page_details) && is_array($page_details)) {
		// if more than one result was returned, follow priority based rules
		// #1 if it's matches a post type they limited the post type to, in pw_product_search_post_type setting
		// #2 if it matches the plugin core post type 'product'
		// Otherwise stick with first result found
		$pageMatch = [];
		$post_type_to_search = get_option('pw_product_search_post_type');
		$post_type_to_search = !empty($post_type_to_search) ? $post_type_to_search : [];
		if (empty($post_type_to_search) || count($page_details) == 1) {
			// if no filter set, default to use first result or if it's the only result found
			$pageMatch = $page_details[0];
		}
		if (count($page_details) > 1) {
			foreach ($page_details as $pageArr) {
				$postType = $pageArr['post_type'];
				// if filter set to limit post types to search, only search those for priority
				if (!empty($post_type_to_search)) {
					if (in_array($postType, $post_type_to_search)) {
						$pageMatch = $pageArr;
						break;
					}
				// else give product post types priority
				} else {
					if ($postType == 'product') {
						$pageMatch = $pageArr;
						break;
					}
				}
			}
		}

		if (!empty($pageMatch)) {
			$page_url = get_permalink($pageMatch['ID']);
			$force_ssl = get_post_meta($pageMatch['ID'], 'force_ssl', true);
			if ($force_ssl && is_plugin_active('wordpress-https/wordpress-https.php')) {
				$page_url = str_replace('http:', 'https:', $page_url);
			}
		}
	}

	return $page_url;
}

// get Url of Pharmacy plugin by code page
function PC_getUrl($code_page)
{
	global $wpdb, $wp_rewrite;
	$page_url = "";
	$code_page = '%[' . $code_page . '%]%';
	$shortcodeQuery = $wpdb->prepare(
		"
            SELECT * FROM `" . $wpdb->posts . "` WHERE `post_content` LIKE %s AND `post_type`= 'page' AND post_status='publish' LIMIT 1",
		$code_page
	);
	$page_details = $wpdb->get_row($shortcodeQuery, ARRAY_A);

	if ($page_details !== null) {
		$post_id = $page_details['ID'];
		$page_url = get_permalink($post_id);
		$force_ssl = get_post_meta($post_id, 'force_ssl', true);
		if ($force_ssl && is_plugin_active('wordpress-https/wordpress-https.php')) {
			$page_url = str_replace('http:', 'https:', $page_url);
		}
	}
	return $page_url;
}

// get Url of Pharmacy plugin by code page
function PC_getSlug($code_page)
{
	global $wpdb, $wp_rewrite;
	$page_slug = "";
	$page_details = $wpdb->get_row("SELECT * FROM `" . $wpdb->posts . "` WHERE `post_content` LIKE '%[" . $code_page . "]%' AND `post_type`= 'page' AND post_status='publish'  LIMIT 1", ARRAY_A);
	if ($page_details !== null) {
		$post_id = $page_details['ID'];
		$page_slug = get_post_field('post_name', $post_id);
	}
	return $page_slug;
}

// get PageID of Pharmacy plugin by code page
function PC_getPageIdByPageCode($code_page)
{
	global $pw_page_list;
	$intPage_id = -1;
	if (!empty($pw_page_list[$code_page])) {
		$intPage_id = (int) $pw_page_list[$code_page];
	}
	$PC_url  = get_permalink($intPage_id);

	// checking, page is not exist in cache then get by code page from database
	if (empty($PC_url) or ($intPage_id < 1)) {
		$intPage_id = PC_getPageFromDataBase($code_page);
	}
	return $intPage_id;
}
function PC_getPageFromDataBase($code_page)
{
	global $wpdb;
	$post_id = null;

	$page_details = $wpdb->get_row("SELECT * FROM `" . $wpdb->posts . "` WHERE `post_content` LIKE '%[" . $code_page . "]%' AND `post_type`= 'page' AND post_status='publish'  LIMIT 1", ARRAY_A);

	$countItems = $wpdb->num_rows;

	if ($countItems > 0) {
		$post_id = $page_details['ID'];
	}
	return $post_id;
}

function PC_getMonthOptions($month)
{
	$strResult = "";
	$arrMonths = array(
		"01" => "January - 01",
		"02" => "February - 02",
		"03" => "March - 03",
		"04" => "April - 04",
		"05" => "May - 05",
		"06" => "June - 06",
		"07" => "July - 07",
		"08" => "August - 08",
		"09" => "September - 09",
		"10" => "October - 10",
		"11" => "November - 11",
		"12" => "December - 12"
	);

	if (trim($month) == "") {
		$month = "01";
	}
	foreach ($arrMonths as $key => $value) {
		$strSelected = "";
		if ($month == $key) {
			$strSelected = ' selected ';
		}
		$strResult .= '<option ' . $strSelected  . ' value="' . $key . '">' . $value . '</option>';
	}
	return $strResult;
}

function PC_getPaymentMethodsUnfiltered($patientAttributes = null)
{
	$paymentMethods = array();
	if (PC_activatePaymentCreditCardCapture($patientAttributes)) {
		$ccTypes = [];
		if (get_option('pw_disable_amex') !== 'on') {
			$ccTypes['amex'] = array('label' => 'American Express', 'value' => 'amex');
		}
		if (get_option('pw_disable_discover', 'on') !== 'on') {
			$ccTypes['discover'] = array('label' => 'Discover', 'value' => 'discover');
		}
		if (get_option('pw_disable_mastercard') !== 'on') {
			$ccTypes['mastercard'] = array('label' => 'MasterCard', 'value' => 'mastercard');
		}
		if (get_option('pw_disable_visa') !== 'on') {
			$ccTypes['visa'] = array('label' => 'Visa', 'value' => 'visa');
		}
		$paymentMethods["creditcard"] = array("code" => "creditcard", "label" => "Credit Card", "types" => $ccTypes);
	}
	if (PC_activatePaymentDraftCapture($patientAttributes)) {
		$paymentMethods["draft"] = array("code" => "draft", "label" => "Check / Draft / Money Order");
	}
	if (PC_activatePaymentEFTCapture($patientAttributes)) {
		$paymentMethods["eft"] = array("code" => "eft", "label" => "EFT (Electronic Funds Transfer)");
	}
	if (!empty(get_option('pw_payment_method_custom'))) {
		$paymentMethods["custom"] = [];

		$customPaymentMethod = explode(',', get_option('pw_payment_method_custom'));
		foreach ($customPaymentMethod as $index => $paymentM) {
			if (!empty($paymentM)) {
				$code = lcfirst(sanitize_html_class(ucwords($paymentM)));
				$paymentMethods["custom"][strtolower($code)] = array("code" => $code, "label" => trim($paymentM));
			}
		}
	}
	return $paymentMethods;
}

function PC_getPaymentMethods($patientAttributes = null)
{
	$paymentMethods = array();

	if (WebUser::isLoggedIn() && is_null($patientAttributes)) {
		$patient = new Model_Entity_Patient();
		$patientAttributes = null;
		$patientInfo = WebUser::getUserInfo();
		$patientAttributes = $patientInfo->attributes;
	}
	$paymentMethods = PC_getPaymentMethodsUnfiltered($patientAttributes);
	$couponModel = new Model_Coupon;
	$couponRestrictedPaymentMethods = $couponModel->couponRestrictedPaymentMethods();
	if (count($couponRestrictedPaymentMethods)) {
		$paymentMethods = array_intersect_key($paymentMethods, array_flip($couponRestrictedPaymentMethods));
	}
	return $paymentMethods;
}

function PC_getPaymentMethodOptionsHTML($selected = null, $patientAttributes = null)
{
	$strResult = "";
	$paymentMethods = array();

	if (WebUser::isLoggedIn() && is_null($patientAttributes)) {
		$patient = new Model_Entity_Patient();
		$patientAttributes = null;
		$patientInfo = WebUser::getUserInfo();
		$patientAttributes = $patientInfo->attributes;
	}

	$paymentMethods = PC_getPaymentMethodsUnfiltered($patientAttributes);

	if (trim($selected) == "") {
		$selected = get_option('pw_default_payment_type') ? get_option('pw_default_payment_type') : "creditcard";
	}
	$couponModel = new Model_Coupon;
	$couponRestrictedPaymentMethods = $couponModel->couponRestrictedPaymentMethods();

	foreach ($paymentMethods as $key => $value) {
		if (!empty($couponRestrictedPaymentMethods)) {
			if (!in_array($key, $couponRestrictedPaymentMethods)) {
				continue;
			}
		}
		$strSelected = "";
		if (!is_null($selected) && ($selected == $key)) {
			$strSelected = " selected ";
		}
		$strResult .= '<option ' . $strSelected . ' value="' . $key . '">' . $value . '</option>';
	}

	return $strResult;
}
function PC_getCreditCartTypeOptionsHTML($selected = "")
{
	$strResult = "";
	$paymentMethods = array();
	if (get_option('pw_disable_amex') !== 'on') {
		$paymentMethods['American Express'] = 'American Express';
	}
	if (get_option('pw_disable_discover', 'on') !== 'on') {
		$paymentMethods['Discover'] = 'Discover';
	}
	if (get_option('pw_disable_mastercard') !== 'on') {
		$paymentMethods['MasterCard'] = 'MasterCard';
	}
	if (get_option('pw_disable_visa') !== 'on') {
		$paymentMethods['Visa'] = 'Visa';
	}
	//		$paymentMethods = array("MasterCard"=>"Master Card","Visa"=>"Visa");

	if (trim($selected) == "") {
		$selected = "Visa";
	}
	foreach ($paymentMethods as $key => $value) {
		$strSelected = "";
		if ($selected == $key) {
			$strSelected = " selected ";
		}
		$strResult .= '<option ' . $strSelected . ' value="' . $key . '">' . $value . '</option>';
	}

	return $strResult;
}
function PC_getYearOptions($year)
{
	$strResult = "";

	$curYear = (int) date("Y");
	if (!is_numeric($year)) {
		$year = $curYear;
	} else {
		if ($year < $curYear) {
			$year = $curYear;
		}
	}
	for ($i = $curYear; $i < $curYear + 15; $i++) {
		$strSelected = "";
		if ($year == $i) {
			$strSelected = ' selected ';
		}
		$strResult .= '<option ' . $strSelected  . ' value="' . $i . '">' . $i . '</option>';
	}
	return $strResult;
}

function PC_genErrorMessage($msg)
{
	// removed old styling elements
	$strResult = $msg;
	return $strResult;
}
function PC_formatPrice($value)
{
	return number_format($value, 2, '.', '');
}
function PC_getElementText($data)
{
	$pattern = '<!\[CDATA\[(.*)]]>';
	$pattern2 = '<!--\[CDATA\[(.*)]]-->';
	$result = $data;
	if (strpos($data, 'CDATA') > -1) {
		if (preg_match($pattern, $data, $match)) {
			$result = $match[1];
		} elseif (preg_match($pattern2, $data, $match)) {
			$result = $match[1];
		}
	}

	return $result;
}
function PC_formatCreditCard($number)
{
	$length = strlen($number);
	$lastedChar = substr($number, $length - 4, 4);
	$strResult = "";
	for ($i = 0; $i < $length - 4; $i++) {
		if ($i % 4 == 0) {
			$strResult .= " ";
		}
		$strResult .= "*";
	}
	$strResult  .= " " . $lastedChar;
	return $strResult;
}
function PC_getCurrentURL()
{
	$port = $_SERVER["SERVER_PORT"];
	$host_port = "";
	if (($port != 80) && ($port != 443)) {
		$host_port = ":" . $port;
	}
	$url = (!empty($_SERVER['HTTPS'])) ? "https://" . $_SERVER['SERVER_NAME'] . $host_port . $_SERVER['REQUEST_URI'] : "http://" . $_SERVER['SERVER_NAME'] . $host_port . $_SERVER['REQUEST_URI'];
	return $url;
}
function PC_parsePhone($phone)
{
	$valuePhoneHome 	= explode('-', $phone);
	return $valuePhoneHome;
}
function PC_genPassword($length = 6, $level = 2)
{

	/*
		list($usec, $sec) = explode(' ', microtime());
		srand((float) $sec + ((float) $usec * 100000));
	*/
	$validchars[1] = "0123456789abcdfghjkmnpqrstvwxyz";
	$validchars[2] = "0123456789abcdfghjkmnpqrstvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
	$validchars[3] = "0123456789_!@#$%&*()-=+/abcdfghjkmnpqrstvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_!@#$%&*()-=+/";

	$password  = "";
	$counter   = 0;

	while ($counter < $length) {
		$actChar = substr($validchars[$level], rand(0, strlen($validchars[$level]) - 1), 1);

		// All character must be different
		if (!strstr($password, $actChar)) {
			$password .= $actChar;
			$counter++;
		}
	}
	return $password;
}

function PC_sendEmail($to = "", $subject = "", $message = "")
{
	$from = EMAIL_COMPANY;

	// Set the from name to the Pharmacy Name rather than the default 'WordPress'
	$bracket_pos = strpos($from, '<');
	if ($bracket_pos == false) {
		$from = trim($from);
		$from = trim(get_option('pw_name')) . ' <' . $from . '>';
	}

	$headers  = "From: $from\r\n";
	$headers .= "Content-type: text/html\r\n";

	$pharmacyName = get_option('pw_name');
	$siteURL = PC_getHomePageURL();
	$siteURL = rtrim($siteURL, "/");
	$siteName = get_bloginfo('name');
	$loginURL = PC_getLoginUrl();
	$email = get_option('pw_email');
	$phoneAreaCode = get_option('pw_phone_area');
	$phoneNumber = get_option('pw_phone');
	$faxAreaCode = get_option('pw_fax_area');
	$faxNumber = get_option('pw_fax');
	$currentYear = date('Y');

	//options to send to cc+bcc
	//$headers .= "Cc: [email]maa@p-i-s.cXom[/email]";
	//$headers .= "Bcc: [email]email@maaking.cXom[/email]";

	$logoUrl = get_option('pw_emailLogo', '');
	$logoDisplay = '<img src="' . $logoUrl . '" style="display:block;margin-bottom:10px;">';
	if (empty($logoUrl)) {
		$logoDisplay = '';
	}

	$defaultEmailHead = '<html><head><META http-equiv="Content-Type" content="text/html; charset=utf-8"></head>
			<body><div>
					<table border="0" cellspacing="0" cellpadding="0" style="width:654px;border:none;margin:0 auto">
						<tr>
							<td><img src="' . PWIRE_PLUGIN_URL . 'Themes/images/email/email-head.png" style="float:left"></td>
						</tr>
					</table>
					<table border="0" cellspacing="0" cellpadding="0" style="width:629px;border:none;margin:0 auto">
						<tr>
							<td style="padding-right:20px;padding-left:20px;background-color:#f6f5f5;font-family:arial;font-size:13px;color:#333">
								';

	$defaultEmailFoot = '			</td>
					</tr>
				</table>
				<table border="0" cellspacing="0" cellpadding="0" style="width:654px;border:none;margin:0 auto">
					<tr>
						<td><img src="' . PWIRE_PLUGIN_URL . 'Themes/images/email/email-foot.png" style="float:left"></td>
					</tr>
					<tr>
						<td style="padding-left:40px;padding-right:40px;font-family:arial;color:#999;font-size:11px">Copyright &copy; $currentYear <a href="' . $siteURL . '" target="_blank" style="color: #999;">' . $siteName . '</a> | <a href="http://www.pharmacywire.com/" style="color: #999;">Powered by PharmacyWire</a></td>
					</tr>
				</table>
			</div>
			</body></html>';

	$emailHead 	= get_option('pw_emailHead', $defaultEmailHead);
	if (empty($emailHead)) {
		$emailHead = $defaultEmailHead;
	}
	$emailHead = $emailHead . $logoDisplay;
	$emailFoot = get_option('pw_emailFoot', $defaultEmailFoot);
	if (empty($emailFoot)) {
		$emailFoot = $defaultEmailFoot;
	}

	$allowedVars = array(
		'/\$pharmacyName/',
		'/\$siteURL/',
		'/\$siteName/',
		'/\$loginURL/',
		'/\$email/',
		'/\$phoneAreaCode/',
		'/\$phoneNumber/',
		'/\$faxAreaCode/',
		'/\$faxNumber/',
		'/\$currentYear/'
	);
	$allowedVarValues = array(
		$pharmacyName,
		$siteURL,
		$siteName,
		$loginURL,
		$email,
		$phoneAreaCode,
		$phoneNumber,
		$faxAreaCode,
		$faxNumber,
		$currentYear
	);

	$message = $emailHead . $message . $emailFoot;

	// replace allowed variables with their respective values
	$message = preg_replace($allowedVars, $allowedVarValues, $message);

	// now lets send the email.
	add_filter('wp_mail_content_type', 'pwire_set_html_mail_content_type');

	wp_mail($to, $subject, $message, $headers);

	// Reset content-type to avoid conflicts -- https://core.trac.wordpress.org/ticket/23578
	remove_filter('wp_mail_content_type', 'pwire_set_html_mail_content_type');
}

function pwire_set_html_mail_content_type()
{
	return 'text/html';
}

function srange($s)
{
	preg_match_all("/([A-Z0-9]{1,2})-?([A-Z0-9]{0,2}) ?,?;?/", $s, $a);
	$n = array();
	foreach ($a[1] as $k => $v) {
		$n  = array_merge($n, range($v, (empty($a[2][$k]) ? $v : $a[2][$k])));
	}
	return ($n);
}

/**
 * Gen search function by Alphabet
 */
function theme_GenSearchByAlphabet()
{
	$search = "";

	$url_base = PC_getSearchURL();
	$browse_by_letter = get_option('pw_browse_by_letter');
	foreach (srange('A-Z 9-9') as $character) {
		$display = ($character == 9) ? '#' : $character;
		$search_criteria = "drugName=" . $character;
		$url_search = PC_reCreateUrl($search_criteria, $url_base);
		if ($browse_by_letter) {
			$search .= '<a href="/' . $browse_by_letter . '/' . $character . '/">' . $display . '</a>';
		} else {
			$search .= '<a href="' . $url_search . '">' . $display . '</a>';
		}
	}
	//		$search_criteria = "drugName=9";
	//		$url_search = PC_reCreateUrl($search_criteria,$url_base) ;
	//		$search .='<a href="' . $url_search .'">#</a>';
	return $search;
}
/**
 * Create message for cart-mini on side bar;
 */
function theme_GenMessageForCartSideBar()
{
	$strContent = "";
	//there is item on shopping cart;
	$items = Cart::getListItems();
	if (Cart::haveItems()) {
		$countItem = count($items);
		$strContent = "You have " .  $countItem . " item" . (($countItem > 1) ? "s" : "") . " in your cart. Your order total is $" . Cart::getSubTotal() . " plus shipping.";
	} else {
		$strContent = 'Your shopping cart is empty. To put something in your Shopping Cart, start by searching or browsing through our <a href="/online-store">Online Store</a>.';
	}
	return $strContent;
}
/**
 *
 * Make checkout button on mini-cart
 */
function theme_GenCheckOutButton()
{
	$strResult = "";
	$shoppingCartUrl = PC_getShoppingURL();
	$shoppingCartUrl = preg_replace('#^https?://#', '', $shoppingCartUrl);

	$cur_page = PC_getCurrentURL();
	$cur_page = preg_replace('#^https?://#', '', $cur_page);

	$isCheckoutPage = stristr($cur_page, $shoppingCartUrl);


	if (Cart::haveItems() && !$isCheckoutPage) {
		$strResult .= '<div class="action">';
		$strResult .= '				<input type="button" class="button" alt="Submit" value="Go to Checkout" name="checkout" id="shoppingcart-checkout" onclick="window.location =\'' . $shoppingCartUrl . '\'"/>';
		$strResult .= '</div>';
	}
	return $strResult;
}

/**
 *
 * Check current page, if it is login page -> return true else return false;
 */
function theme_IsLoginPage()
{
	$cur_page = PC_getCurrentURL();
	$cur_page = preg_replace('#^https?://#', '', $cur_page);

	$url_loginPage = PC_getLoginUrl();
	$url_loginPage = preg_replace('#^https?://#', '', $url_loginPage);

	$result = stristr($cur_page, $url_loginPage);
	if (!$result) {
		$url_jsonLoginPage = PC_getJSONLogin();
		$url_jsonLoginPage = preg_replace('#^https?://#', '', $url_jsonLoginPage);

		$result = stristr($cur_page, $url_jsonLoginPage);
	}

	return $result;
}

// Get category by slug
function theme_GetCatIdBySlug($slug = 'policies')
{
	global $wpdb;
	global $table_prefix;
	$table_Terms = $table_prefix . 'terms';
	$table_TermTaxonomy = $table_prefix . 'term_taxonomy';
	$sql_Select = "SELECT term.* FROM " . $table_Terms . " term," . $table_TermTaxonomy . " taxonomy WHERE term.term_id = taxonomy.term_id And slug ='" . $slug . "'";
	$cate = $wpdb->get_row($sql_Select);
	return $cate;
}

function theme_GenListLinkBySlug($slug = 'policies')
{
	$strResult = "";
	$category = theme_getCatIdBySlug($slug);
	$arr = array(
		'numberposts'     => -1,
		'category'        => $category->term_id,
		'post_status'     => 'publish',
		'order'           => 'ASC'
	);
	$posts = get_posts($arr);
	foreach ($posts as $post) {
		$strResult .= '<li><a href="' . get_permalink($post->ID) . '">' . $post->post_title  . '</a></li>';
	}
	return $strResult;
}
require_once(THEMES_FOLDER . '/widgets/shopping-cart-widget.php');
require_once(THEMES_FOLDER . '/widgets/ajax-shopping-cart-widget.php');
require_once(THEMES_FOLDER . '/widgets/ajax-account-tools-widget.php');

function PC_activatePaymentDraftCapture($patientAttributes = null)
{
	return true;
}

function PC_activatePaymentCreditCardCapture($patientAttributes = null)
{
	$required_attribute = get_option('pw_cc_requires_attribute');
	if ($required_attribute) {
		foreach ($patientAttributes as $attribute) {
			if ($attribute->name === $required_attribute) {
				if (($attribute->type === 'boolean' && $attribute->value === 'true') || ($attribute->type !== 'boolean')) {
					return true;
				}
			}
		}
		return false;
	}
	if (!empty(PC_getCreditCartTypeOptionsHTML())) {
		return true;
	}
	return false;
}

function PC_activatePaymentEFTCapture($patientAttributes = null)
{
	if (get_option('pw_disable_eft') && (get_option('pw_disable_eft') == 'on')) {
		return false;
	}
	return true;
}

if (!function_exists('is_countable')) :
	/**
	 * Verify that the content of a variable is an array or an object
	 * implementing Countable
	 *
	 * @param mixed $var The value to check.
	 * @return bool Returns TRUE if var is countable, FALSE otherwise.
	 */
	function is_countable($var)
	{
		return is_array($var)
			|| $var instanceof \Countable
			|| $var instanceof \SimpleXMLElement
			|| $var instanceof \ResourceBundle;
	}
endif;

function genQtyDropDown($selected, $max = 10, $i = 1)
{
	$html = "";
	for ($i; $i <= $max; $i++) {
		$html_selected = "";
		if ($selected == $i) {
			$html_selected = " selected ";
		}
		$html .= '<option value="' . $i . '"' . $html_selected . '>' . $i . '</option>';
	}
	return $html;
}

if (!function_exists('is_json')) {
	function is_json($string)
	{
		$valid = true;
		$jsonString = json_decode($string);

		if (json_last_error() !== JSON_ERROR_NONE) {
			error_log('PharmacyWire - invalid json supplied, error: ' . json_last_error());
			$valid = false;
		}
		return $valid;
	}
}
