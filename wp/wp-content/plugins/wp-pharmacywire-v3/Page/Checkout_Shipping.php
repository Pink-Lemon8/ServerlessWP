<?php
class Page_Checkout_Shipping extends Utility_PageBase
{
	/**
	 * Make the html output
	 *
	 * @return The content in html format
	 */
	public function _process()
	{
		$this->checkPermission();
		$this->setTemplate("page_checkout_shipping");

		$this->assign('PHARMACY_EMAIL', get_option('pw_email'));

		$state = strtoupper(trim($this->_getRequest("state")));

		switch ($state) {
			case "EDIT":
			case "SHIPPING":
				$this->processLogin();
				$this->processShipping();
				break;
			default:
				$this->processLogin();
				$this->processShipping();
				break;
		}

		$this->assign('SHIPPING_URL', PC_getCheckoutEditAddressUrl());
	}

	// process when user on shipping tab
	public function processShipping()
	{
		$data = $_POST;
		// get current action
		$action = strtoupper(trim($this->_getRequest("action")));
		//process Shipping
		if ($action == "CHANGECOUNTRY") {
			$this->saveShippingInfo();
		}
		if ($action == "SAVE") {
			$this->saveShippingInfo();
			$isValid = $this->validData($data);
			if ($isValid) {
				$url = PC_getBillingURL();
				$state = strtoupper(trim((string)$_GET["state"]));
				if ($state == "EDIT") {
					$url = PC_getConfirmURL();
				}
				$this->redirect($url);
			}
		} else {
			$this->_populateForm($data);
		}
		$this->assign('NEXT_BUTTON_VALUE', 'Proceed to Billing');
		$this->_populateForm($data);
		$this->parse('SHIPPING_INFO');
	}

	public function processLogin()
	{
		// get current action
		$action = strtoupper(trim($this->_getRequest("action")));
		if ($action == "SUBMIT") {
			$username = $this->_getRequest('username');
			$password = $this->_getRequest('password');
			if (trim($username) != "") {
				$loginModel = new Model_Patient();

				if (!isset($patient)) {
					$patient = new Model_Entity_Patient();
				}
				$patient->username = $username;
				$patient->password = $password;
				$listPatient[] = $patient;

				$result = $loginModel->authenticateUser($listPatient);

				$user = $result->users[0];

				if ($result->status != "success" || $user->type != 'Patient') {
					$this->assign("MESSAGE", 'Invalid User Name and/or Password entered.');
					$this->parse("CONTENT");
					$this->assign("LOGIN_SUCCESS", '<input type="hidden" id="login-success" name="login-success" value="false" />');
				} else {
					if ($user->authenticated === 'reset-password') {
						WebUser::setTempUserID($user->id);
						WebUser::setTempUserName($user->name);

						// tell ajax to redirect to change password
						$this->assign('SHIPPING_URL', PC_getChangePassUrl());
						//$change_pass_url = PC_getChangePassUrl();
						//$this->redirect($change_pass_url);
					} else {
						WebUser::setLogin($user);

						// check session for active coupon
						$couponSession = new Model_Coupon();
						$currentCoupons = $couponSession->getCouponSession();

						if (!empty($currentCoupons)) {
							$couponSession->revalidateCouponSession();
							$coupons = $couponSession->getCouponSession();

							foreach ($coupons as $couponCode => $couponData) {
								// if invalid coupon found redirect to cart

								if ($couponData['usable'] == 'false') {
									// used in javascript checkValueLogin() for redirect, set to go back to cart to show invalid coupons
									$this->assign('SHIPPING_URL', PC_getShoppingURL());

									$edit_cart_url = PC_getShoppingURL();
									$this->assign('EDIT_CART_URL', $edit_cart_url);
								}
							}
						}
					}
				}
			}
		}
		$this->displayLoginForm();
	}
	public function htmlError($error)
	{
		$html = '<div class="message">' . $error . '</div>';
		return $html;
	}
	public function displayLoginForm()
	{
		$strLogin_page		= PC_getLoginUrl();
		$urlForgot 			= PC_getForgotUrl();
		$this->assign('loginUrl', $strLogin_page);

		$this->assign('URL_FORGOT_PASSWORD', $urlForgot);
		$this->parse('SHIPPING_LOGIN');
	}
	// process Save ShippingInfo
	public function saveShippingInfo()
	{
		$firstName 		= (string)$this->_getRequest('firstName');
		$lastName 		= (string)$this->_getRequest('lastName');
		$address1 		= (string)$this->_getRequest('Address1');
		$address2 		= (string)$this->_getRequest('Address2');
		$address3 		= '';
		$city 			= (string)$this->_getRequest('City');
		$state 			= (string)$this->_getRequest('shipping_region');
		$country 		= (string)$this->_getRequest('shipping_country');
		$postalCode 	= (string)$this->_getRequest('PostalCode');
		$areaCode  		= (string)$this->_getRequest('shipping_phoneAreaCode');
		$phoneHome 		= (string)$this->_getRequest('shipping_phone');
		$areaCode_day	= '';
		$phoneDay 		= '';
		$areaCodeFax	= (string)$this->_getRequest('AreaCodeFax');
		$fax	 		= (string)$this->_getRequest('Fax');
		$description    = '';

		$_SESSION['Account_phoneAreaCode'] = (string)$this->_getRequest('phoneAreaCode');
		$_SESSION['Account_phone'] = (string)$this->_getRequest('phone');

		Shipping::setInfor($firstName, $lastName, $address1, $address2, $address3, $state, $country, $postalCode, $areaCode, $areaCode_day, $phoneHome, $phoneDay, $city, $areaCodeFax, $fax, $description);
	}

	public function _populateForm($data)
	{

		//GetShippingInfor and BillingInfor from session variable
		$shippingInfor 	= Shipping::getInfo();
		$billingInfor	= Billing::getInfo();

		// Parse Shipping Information
		$this->assign('VALUE_FIRSTNAME', $shippingInfor->firstname);
		$this->assign('VALUE_LASTNAME', $shippingInfor->lastname);
		$this->assign('VALUE_ADDRESS1', $shippingInfor->address1);
		$this->assign('VALUE_ADDRESS2', $shippingInfor->address2);
		$this->assign('VALUE_CITY', $shippingInfor->city);

		$shippingDataProvince 	= $shippingInfor->province;
		$shippingCountry 	= Utility_Html::htmlSelectCountry('shipping_country', $shippingInfor->country, ' tabindex="100" ');
		$shippingProvince 	= Utility_Html::htmlSelectProvince('shipping_region', $shippingInfor->province, $shippingInfor->country, ' tabindex="110" ');

		$this->assign('SELECT_SHIPPING_PROVINCE', $shippingProvince);
		$this->assign('SELECT_SHIPPING_COUNTRY', $shippingCountry);
		$this->assign('VALUE_POSTALCODE', $shippingInfor->postalcode);
		$this->assign('VALUE_AREACODEPHONE', $_SESSION['Account_phoneAreaCode']);
		$this->assign('VALUE_PHONE', $_SESSION['Account_phone']);
		$this->assign('VALUE_FIRSTPHONEDAY', $shippingInfor->areacode_day);
		$this->assign('VALUE_LASTPHONEDAY', $shippingInfor->phone_day);
		$this->assign('VALUE_SHIPPING_AREACODE', $shippingInfor->areacode);
		$this->assign('VALUE_SHIPPING_PHONE', $shippingInfor->phone);
	}

	// valid data
	public function validData($data)
	{
		$isValid = true;
		if (empty($data['firstName'])) {
			$msg = PC_genErrorMessage("firstName is a required field.");
			$this->assign('CLASS_FIRSTNAME', 'error');
			$this->assign('ERROR_FIRSTNAME', $msg);
			$isValid = false;
		}
		if (empty($data['lastName'])) {
			$msg = PC_genErrorMessage("lastName is a required field.");
			$this->assign('CLASS_LASTNAME', 'error');
			$this->assign('ERROR_LASTNAME', $msg);
			$isValid = false;
		}
		if (empty($data['Address1'])) {
			$msg = PC_genErrorMessage("Street Address is a required field.");
			$this->assign('CLASS_SHIPPING_ADDRESS1', 'error');
			$this->assign('ERROR_SHIPPING_ADDRESS1', $msg);
			$isValid = false;
		}
		if (empty($data['City'])) {
			$msg = PC_genErrorMessage("City is a required field.");
			$this->assign('CLASS_SHIPPING_CITY', 'error');
			$this->assign('ERROR_SHIPPING_CITY', $msg);
			$isValid = false;
		}
		if (empty($data['PostalCode']) || strlen(trim($data['PostalCode'])) < 5) {
			$msg = PC_genErrorMessage("PostalCode is a required field.");
			$this->assign('CLASS_SHIPPING_POSTALCODE', 'error');
			$this->assign('ERROR_SHIPPING_POSTALCODE', $msg);
			$isValid = false;
		}
		if (empty($data['phoneAreaCode'])) {
			$msg = PC_genErrorMessage("Phone Area Code is a required field.");
			$this->assign('CLASS_AREACODEPHONE', 'error');
			$this->assign('ERROR_AREACODEPHONE', $msg);
			$isValid = false;
		}
		if (empty($data['phone'])) {
			$msg = PC_genErrorMessage("Phone is a required field.");
			$this->assign('CLASS_PHONE', 'error');
			$this->assign('ERROR_PHONE', $msg);
			$isValid = false;
		}
		return $isValid;
	}
	public function checkPermission()
	{
		if (WebUser::isLoggedIn()) {
			$url = PC_getCheckoutEditAddressUrl();
			$this->redirect($url);
		}
	}
}
