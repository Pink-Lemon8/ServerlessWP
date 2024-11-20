<?php
class Page_Checkout_Confirm extends Utility_PageBase
{
	/**
	 * Make the html output
	 *
	 * @return The content in html format
	 */
	public function _process()
	{
		$this->checkCartReady();
		$this->checkCartEmpty();
		$this->processConfirm();
	}

	// process when user on confirm tab
	public function processConfirm()
	{
		$data = $_POST;

		$this->setTemplate("page_checkout_confirm");

		$action = strtoupper($this->_getRequest('action'));
		switch ($action) {
			case "SUBMITORDER":
				$this->_submitOrder();
				break;
			case "UPDATE":
				$this->_updateCart();
				break;
			default:
				$this->_populateForm($data);
		}

		$this->displayPaymentInfo();
		$this->displayBilling();
		$this->displayShipping();
		$this->displayShoppingCart();
	}

	// save information from cart to order.
	public function _submitOrder()
	{
		$data = $_POST;

		if ($this->_validate($data)) {
			$status = true;

			// if user don't have account, first, create user account for user based on billing / shipping information.
			if (!WebUser::isLoggedIn()) {
				$status = $this->createUser();
			}

			if ($status) {
				$this->addLegalAgreement();
				$save = $this->saveOrder();

				if ($save->status == "success") {

					// Sign up for RxRights if they've agreed to do so
					if (array_key_exists('rx-rights', $data) && ($data['rx-rights'] == 'true')) {
						$this->_RxRightsSignup($data);
					}

					Cart::isRxRequired();
					$this->deleteInforAfterSave();
					$url_thankyou = PC_getThankYouURL();
					$this->redirect($url_thankyou);
				}
			}
		}
		$this->_populateForm($data);
	}

	/**
	 * RxRightsSignup
	 *
	 * @param mixed $data
	 */
	public function _RxRightsSignup($data)
	{
		global $wpdb;
		$table_name = $wpdb->prefix . "pw_rxrights_signup";

		if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
			//table is not created. you may create the table here.
			$sql = "CREATE TABLE IF NOT EXISTS $table_name (
			    		id mediumint(9) NOT NULL AUTO_INCREMENT,
					    email VARCHAR(254) DEFAULT '' NOT NULL,
					    prefix VARCHAR(10) DEFAULT '',
					    last VARCHAR(50) NOT NULL,
						first VARCHAR(50) NOT NULL,
						phone VARCHAR(50) NOT NULL,
						address VARCHAR(256) NOT NULL,
						city VARCHAR(256) NOT NULL,
						state VARCHAR(256) NOT NULL,
						zip VARCHAR(20) NOT NULL,
						zip_4 VARCHAR(20) DEFAULT '' NULL,
					    created datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
					    INDEX created (created),
					    UNIQUE KEY email (email),
					    PRIMARY KEY id (id)
					    );";

			require_once(ABSPATH . '/wp-admin/includes/upgrade.php');
			dbDelta($sql);
		}

		/* Setup values for insert */
		$patientInfo = $this->_getPatientInfo();

		$email = $patientInfo->email;
		$lastname = $patientInfo->lastname;
		$firstname = $patientInfo->firstname;
		$phone = $patientInfo->areacode . '-' . $patientInfo->phone;
		$address = $patientInfo->address->address1;
		$city = $patientInfo->address->city;
		$state = $patientInfo->address->province;
		$zip = $patientInfo->address->postalcode;
		$zip_4 = '';
		$created = current_time('mysql');

		$wpdb->query(
			$wpdb->prepare(
				"INSERT IGNORE INTO $table_name (email, last, first, phone, address, city, state, zip, zip_4, created)
					VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s)",
				$email,
				$lastname,
				$firstname,
				$phone,
				$address,
				$city,
				$state,
				$zip,
				$zip_4,
				$created
			)
		);
	}

	public function _getPatientInfo()
	{
		$patientModel = new Model_Patient();
		$patient = new Model_Entity_Patient();

		$patient->patientid = WebUser::getUserID();
		$result = $patientModel->getPatientInfo($patient);

		$patientInfo = $result->patient;

		return $patientInfo;
	}

	/**
	 * Validate form data
	 *
	 * @param mixed $data
	 */
	public function _validate($data)
	{
		if ($this->_getRequest('additional-note-enable')) {
			$additionalNotes = $this->_getRequest('additional-notes');
			if (!strlen($additionalNotes)) {
				$additionalNotes = 'Order includes items for family members and/or pets.';
			}
			Cart::setAdditionalNotes($additionalNotes);
		} else {
			Cart::setAdditionalNotes('');
		}

		$isValid = true;
		// check firstName
		if ($data['agree'] != 'true') {
			$this->assign('ERROR_AGREEMENT', Utility_Messages::getErrorMessage('ERROR_AGREEMENT'));
			$isValid = false;
		}

		if (!WebUser::isLoggedIn()) {
			// check Email
			if (!Utility_Common::isEmail($data['username'])) {
				$this->assign('ERROR_USERNAME', Utility_Messages::getErrorMessage('ERROR_EMAIL'));
				$isValid = false;
			}

			if (Cart::isRxRequired()) {
				// check Weight
				if (empty($data['Weight'])) {
					$this->assign('ERROR_WEIGHT', Utility_Messages::getErrorMessage('ERROR_WEIGHT'));
					$isValid = false;
				}
				// check Height
				if (empty($data['HeightFeet']) || ($data['HeightFeet'] < 3)) {
					$this->assign('CLASS_HEIGHT', 'error');
					$this->assign('ERROR_HEIGHT', Utility_Messages::getErrorMessage('ERROR_HEIGHT'));
					$isValid = false;
				}
				// check Sex
				if (empty($data['Sex'])) {
					$this->assign('ERROR_SEX', Utility_Messages::getErrorMessage('ERROR_SEX'));
					$isValid = false;
				}
			}

			// check BirthDate
			if (!is_numeric($data['BirthDate_MONTH']) || !is_numeric($data['BirthDate_DAY']) || !is_numeric($data['BirthDate_YEAR']) || (int) ($data['BirthDate_DAY']) <= 0 || (int) ($data['BirthDate_YEAR']) <= 1890 || (int) ($data['BirthDate_YEAR']) >= date("Y")) {
				$this->assign('ERROR_BIRTHDATE', Utility_Messages::getErrorMessage('ERROR_BIRTHDATE'));
				$isValid = false;
			}
		}
		return $isValid;
	}

	//update quantiy for shopping cart
	public function _updateCart()
	{
		$updateItem = (array) $this->_getRequest('qty');
		foreach ($updateItem as $key => $value) {
			Cart::update($key, $value);
		}
	}

	//parse data for payment section
	public function displayPaymentInfo()
	{
		$infor = Billing::getInfo();

		$paymentMethod = $infor->methodtype;
		$paymentMethod = ($paymentMethod === 'creditcard') ? 'Credit Card' : $paymentMethod;
		$paymentMethod = ucwords($paymentMethod);
		$paymentMethod = ($paymentMethod === 'Eft') ? 'EFT' : $paymentMethod;
		$this->assign("PAYMENT_METHOD", $paymentMethod);
		if ($paymentMethod == "Draft") {
			$this->setupAddress();
			$this->parse("BILLING.METHOD.DRAFT");
		} elseif ($paymentMethod == 'EFT') {
			// EFT method
			$creditCardType		= $infor->creditcardtype;
			$creditCardNumber	= PC_formatCreditCard($infor->creditcardnumber);
			$cvvNumber			= PC_formatCreditCard($infor->cvvnumber);
			$expiryMonth		= $infor->expirymonth;
			$expiryYear			= $infor->expiryyear;
			$name			= $infor->firstname . ' ' . $infor->lastname;

			$this->assign("NameOnCheque", $infor->nameOnCheque);
			$this->assign("BankName", $infor->bankName);
			$this->assign("BankCity", $infor->bankCity);
			$this->assign("BankState", $infor->bankState);
			$this->assign("BranchTransit", $infor->branchTransit);
			$this->assign("ChequeAccount", $infor->chequeAccount);
			$this->assign("ChequeNumber", $infor->chequeNumber);
			$this->parse("BILLING.METHOD.EFT");
		} else {
			// CreditCard method
			$creditCardType		= $infor->creditcardtype;
			$creditCardNumber	= PC_formatCreditCard($infor->creditcardnumber);
			$cvvNumber			= PC_formatCreditCard($infor->cvvnumber);
			$expiryMonth		= $infor->expirymonth;
			$expiryYear			= $infor->expiryyear;
			$name			= $infor->firstname . ' ' . $infor->lastname;

			$this->assign("CreditCardNumber", $creditCardNumber);
			$this->assign("CvvNumber", $cvvNumber);
			$this->assign("Name", $name);
			$this->assign("CreditCartType", $creditCardType);
			$this->assign("ExpiryMonth", $expiryMonth);
			$this->assign("ExpiryYear", $expiryYear);
			$this->parse("BILLING.METHOD.CREDIT_CARD");
		}
		$this->parse("BILLING.METHOD");
	}

	//parse data for shipping address
	public function displayShipping()
	{
		$shipping = Shipping::getInfo();

		$fullName	= $shipping->firstname . '	' .	 $shipping->lastname;
		$address1	= $shipping->address1;
		$postalCode = $shipping->postalcode;
		$city		= $shipping->city;
		$province	= $shipping->province;
		$country	= Model_Country::getCountryByCode($shipping->country);
		$areacode	= $shipping->areacode ? '(' . $shipping->areacode . ')' : '';
		$phone		= $shipping->phone;
		$areacodefax = $shipping->areacodefax ? '(' . $shipping->areacodefax . ')' : '';
		$fax		= $shipping->fax;
		$account_phone = $_SESSION['Account_phone'];
		$account_areacode = $_SESSION['Account_phoneAreaCode'] ? '(' . $_SESSION['Account_phoneAreaCode'] . ')' : '';

		$this->assign("SHIPPING_FULL_NAME", $fullName);
		$this->assign("SHIPPING_CITY", $city);
		$this->assign("SHIPPING_PROVINCE", $province);
		$this->assign("SHIPPING_ADDRESS", $address1);
		$this->assign("SHIPPING_POSTALCODE", $postalCode);
		$this->assign("SHIPPING_COUNTRY", $country);
		$this->assign("SHIPPING_AREACODE", $areacode);
		$this->assign("SHIPPING_PHONE", $phone);
		$this->assign("SHIPPING_AREACODEFAX", $areacodefax);
		$this->assign("SHIPPING_FAX", $fax);
		$this->assign("ACCOUNT_AREACODEPHONE", $account_areacode);
		$this->assign("ACCOUNT_PHONE", $account_phone);

		//assign shipping url
		$edit_shippingAddress_url = "";
		if (WebUser::isLoggedIn()) {
			$edit_shippingAddress_url = PC_getCheckoutEditAddressUrl();
		} else {
			$edit_shippingAddress_url = PC_getShippingURL();
			$edit_shippingAddress_url = PC_reCreateUrl('state=edit', $edit_shippingAddress_url);
		}
		$this->assign('EDIT_SHIPPING_URL', $edit_shippingAddress_url);
	}

	//parse data for billing address
	public function displayBilling()
	{
		$billing = Billing::getInfo();
		$shipping = Shipping::getInfo();

		$fullName	= $billing->firstname . '	' . $billing->lastname;
		$address1	= $billing->address1;
		$postalCode = $billing->postalcode;
		$city		= $billing->city;
		$province	= $billing->province;
		$country	= Model_Country::getCountryByCode($billing->country);

		if (Billing::getUsingShippingAddress()) {
			$areacode	= $shipping->areacode;
			$phone		= $shipping->phone;
		} else {
			$areacode	= $billing->areacode;
			$phone		= $billing->phone;
		}

		$areacode = $areacode ? '(' . $areacode . ')' : '';

		$this->assign("BILLING_FULL_NAME", $fullName);
		$this->assign("BILLING_CITY", $city);
		$this->assign("BILLING_PROVINCE", $province);
		$this->assign("BILLING_ADDRESS", $address1);
		$this->assign("BILLING_POSTALCODE", $postalCode);
		$this->assign("BILLING_COUNTRY", $country);
		$this->assign("BILLING_AREACODE", $areacode);
		$this->assign("BILLING_PHONE", $phone);

		//assign shipping url
		$edit_billingAddress_url = PC_getBillingURL();
		$this->assign('EDIT_BILLING_URL', $edit_billingAddress_url);

		$this->parse('BILLING');
	}

	//parse data for cart
	public function displayShoppingCart()
	{
		$lstItems = Cart::getListItems();
		//parse data when cart is empty

		$rxStatusCart = 'otc';

		foreach ($lstItems as $item) {
			$remove		 = "remove";
			$packageId	 = $item->package_id;

			if (get_option('pw_display_package_name_on_search_results')) {
				$name = $item->product;
			} else {
				$name = $item->drug_name;
			}

			$ingredients = $item->ingredients;
			$qty		 = $item->amount;

			if ($item->generic) {
				$name = 'Generic - ' . $name;
			}
			$strength	= Utility_Common::getFullValue($item->strength);
			$strengthUnit = $item->strength_unit;
			if ($strength != "") {
				$strength .= " $strengthUnit";
			} else {
				$strength = $item->strengthfreeform;
			}
			$quantity	= Utility_Common::getQuantity($item->packagequantity, $item->packagingfreeform);
			if ($quantity != "") {
				$quantity = " (" . $quantity . ")";
			}
			$orderQuantity	= Utility_Common::getOrderQuantity($item->packagequantity, $item->packagingfreeform, $qty);

			$price		= PC_formatPrice($item->price);
			$sub_total	= PC_formatPrice($item->sub_amount);

			$rxStatusItem = 'otc';
			$rxRequired = '<div class="otc drug-info-icon" title="Over the Counter">Over the Counter</div>';
			if ($item->prescriptionrequired) {
				$rxRequired = '<div class="rx-required rx drug-info-icon" title="Prescription Required">Prescription Required</div>';
				$rxStatusItem = 'rx';
				$rxStatusCart = 'rx';
			}
			$this->assign("ITEM_rxRequired", $rxRequired);

			$countryModel = new Model_Country();
			$countryCodeHuman = $countryModel->getCountryByCode($item->country);
			$countryCodeHuman = (empty($countryCodeHuman) ? 'unknown' : $countryCodeHuman);

			$countryFlag = ($countryCodeHuman == 'unknown') ? '' : '<img class="country-flag" src="' . THEME_URL . 'images/flags/' . $item->country . '.png" alt="Source: ' . $countryCodeHuman . '" title="Source: ' . $countryCodeHuman . '" />';
			$this->assign("ITEM_countryFlag", $countryFlag);
			$this->assign("ITEM_country", $countryCodeHuman);

			$vendorCountryModel = new Model_Country();
			$vendorCountryHuman = $vendorCountryModel->getCountryByCode($item->vendor_country_code);
			$vendorCountryHuman = (empty($vendorCountryHuman) ? 'unknown' : $vendorCountryHuman);
			$this->assign("ITEM_vendorCountryCode", $item->vendor_country_code);
			$this->assign("ITEM_vendorCountry", $vendorCountryHuman);

			if ($item->generic) {
				$brandOrGeneric = '<div class="generic gen drug-info-icon" title="Generic">Generic</div>';
			} else {
				$brandOrGeneric = '<div class="brand brd drug-info-icon" title="Brand">Brand</div>';
			}
			$this->assign("ITEM_brandOrGeneric", $brandOrGeneric);

			$packageAttributes = $item->package_attributes;
			$packageAttrClass = array();
			foreach ($packageAttributes as $pAttr) {
				$pAttrK = 'attr-' . sanitize_html_class($pAttr->attribute_key);
				$pAttrV = sanitize_html_class($pAttr->attribute_value);
				$pClassAttrVal = $pAttrK . '-' . $pAttrV;
				// use sanitized attribute key as well as key-val compbination as classes
				array_push($packageAttrClass, $pAttrK);
				array_push($packageAttrClass, $pClassAttrVal);
			}

			$drugAttributes = $item->drug_attributes;
			$drugAttrClass = array();
			foreach ($drugAttributes as $dAttr) {
				$dAttrK = 'attr-' . sanitize_html_class($dAttr->attribute_key);
				$dAttrV = sanitize_html_class($dAttr->attribute_value);
				$dClassAttrVal = $dAttrK . '-' . $dAttrV;
				// use sanitized attribute key as well as key-val compbination as classes
				array_push($drugAttrClass, $dAttrK);
				array_push($drugAttrClass, $dClassAttrVal);
			}

			$this->assign("ITEM_remove", $remove);
			$this->assign("ITEM_name", $name);
			$this->assign("ITEM_ingredients", $ingredients);
			$this->assign("ITEM_strength", $strength);
			$this->assign("ITEM_quantity", $quantity);
			$this->assign("ITEM_order_quantity", $orderQuantity);
			$this->assign("ITEM_packageId", $packageId);
			$this->assign("ITEM_qty", $qty);
			$this->assign("ITEM_price", $price);
			$this->assign("ITEM_RX_STATUS", $rxStatusItem);
			$this->assign("ITEM_sub_total", $sub_total);
			$this->assign("ITEM_PACKAGE_ATTRIBUTES", implode(' ', $packageAttrClass));
			$this->assign("ITEM_DRUG_ATTRIBUTES", implode(' ', $drugAttrClass));
			$this->parse("ITEM");
		}

		$sub_total = PC_formatPrice(Cart::getSubTotal());
		$shipping_fee = PC_formatPrice(Cart::calculateShippingFee($lstItems));
		// get original coupon fee
		$undiscountedShippingFee = PC_formatPrice(Cart::calculateShippingFee($lstItems, 1));
		$undiscountedTotal = $sub_total + $undiscountedShippingFee;

		// check session for active coupon
		$couponSession = new Model_Coupon();
		$currentCoupons = $couponSession->getCouponSession();

		if (!empty($currentCoupons)) {
			$couponNonce = wp_create_nonce('coupon-nonce');
			$this->assign('COUPON_NONCE', $couponNonce);

			// there is a coupon so setup display
			foreach ($currentCoupons as $couponCode => $couponData) {
				$couponLabel = 'Coupon: "' . $couponCode . '"';
				$couponDiscountMethodHuman = str_replace('$', '\$', $couponSession->getDiscountMethodHuman($couponCode));

				if (!empty($couponDiscountMethodHuman)) {
					$couponDiscountMethodHuman = ' - ' . $couponDiscountMethodHuman;
				}
				$this->assign('COUPON_DISCOUNT_METHOD_HUMAN', $couponDiscountMethodHuman);
				$this->assign('COUPON_CODE', $couponCode);
				$this->assign('COUPON_LABEL', $couponLabel);

				$couponLineItemClass = ($couponData['usable'] == 'false') ? 'invalid' : 'valid';
				$this->assign('COUPON_LINEITEM_CLASS', $couponLineItemClass);

				$couponData['description'] = str_replace('$', '\$', $couponData['description']);
				$this->assign('COUPON_LINE_DESCRIPTION', $couponData['description']);

				$removeCouponLink = '<a href="#" class="remove-coupon" data-coupon-code="' . $couponCode . '">remove</a>';
				$this->assign('REMOVE_COUPON', $removeCouponLink);

				$couponDiscountHuman = $couponSession->getDiscountHuman($sub_total, $couponCode);

				if (!empty($couponDiscountHuman)) {
					$this->assign('COUPON_DISCOUNT', $couponDiscountHuman);
				}

				$this->parse("TOTAL.COUPON_LINEITEM");
			}

			$total = $couponSession->applyDiscount($sub_total) + $shipping_fee;
		} else {
			$total = $sub_total + $shipping_fee;

			// hide the coupon line item but render it for template use
			$couponLineStyle = 'style="display: none;"';
			$this->assign('COUPON_LINEITEM_STYLE', $couponLineStyle);
			$this->parse("TOTAL.COUPON_LINEITEM");
		}

		$this->assign("SUB_TOTAL", $sub_total);
		$shipping_fee_human = ($shipping_fee == '0.00') ? 'FREE' : '\$' . $shipping_fee;
		$this->assign("SHIPPING_FEE", $shipping_fee_human);

		// used for javascript total update after coupon removal
		$this->assign("DATA_UNDISCOUNTED_SHIPPING", $undiscountedShippingFee);
		$this->assign("DATA_UNDISCOUNTED_TOTAL", $undiscountedTotal);
		$total = PC_formatPrice($total);
		$this->assign("TOTAL", $total);
		$this->assign("CART_RX_STATUS", $rxStatusCart);
		// parse data
		$this->parse("TOTAL");
		$edit_cart_url = PC_getShoppingURL();
		$this->assign('EDIT_CART_URL', $edit_cart_url);
	}

	public function _populateForm($data)
	{
		if ($data['agree'] == 'true') {
			$this->assign('VALUE_AGREEMENT', 'checked="checked"');
		}

		$additionalNotes = Cart::getAdditionalNotes();
		if (strlen($additionalNotes)) {
			$this->assign('CART_ADDITIONAL_NOTES_CHECKED', 'checked');
			$this->assign('CART_ADDITIONAL_NOTES', $additionalNotes);
		}
		if ($data['action'] != 'submitOrder' || (array_key_exists('rx-rights', $data) && ($data['rx-rights'] == 'true'))) {
			$this->assign('VALUE_RXRIGHTS', 'checked="checked"');
		} elseif (($data['action'] == 'submitOrder') &&  !array_key_exists('rx-rights', $data)) {
			$this->assign('VALUE_RXRIGHTS', '');
		}

		if (!WebUser::isLoggedIn()) {
			$this->assign('USER_NAME', $data['username']);
			$username = (string) $this->_getRequest("username");;

			$password = PC_genPassword(8);
			/* $error_pass = PC_genErrorMessage('Your default password is');
                $this->assign('ERROR_PASSWORD',$error_pass); */
			$this->assign("GEN_PASSWORD", $password);
			$this->parse('USER_INFO.LOGIN_INFO.PASSWORD_WARNING');

			$this->assign("USER_NAME", $username);
			$this->assign("PASSWORD", $password);
			$this->parse('USER_INFO.LOGIN_INFO');

			if (Cart::isRxRequired()) {
				$this->assign('SELECT_SEX', Utility_Html::htmlSelectGender('Sex', $data['Sex']));
				$this->assign('SELECT_HEIGHTFEET', Utility_Html::htmlSelectHeightFeet('HeightFeet', $data['HeightFeet']));
				$this->assign('SELECT_HEIGHTINCHES', Utility_Html::htmlSelectHeightInches('HeightInches', $data['HeightInches']));
				$this->assign('VALUE_WEIGHT', $data['Weight']);
				$this->parse('USER_INFO.PRESCRIPTION_REQUIRED');
			}

			$this->assign('SELECT_BIRTHDATE_MONTH', Utility_Html::htmlSelectMonth('BirthDate_MONTH', $data['BirthDate_MONTH']));
			$this->assign('VALUE_BIRTHDATE_DAY', $data['BirthDate_DAY']);
			$this->assign('VALUE_BIRTHDATE_YEAR', $data['BirthDate_YEAR']);
			$this->parse("USER_INFO.BIRTHDATE_REQUIRED");

			$this->parse("USER_INFO");
		}
	}

	public function createUser()
	{
		$bResult = false;
		$isValid = $this->validData();

		if ($isValid) {
			$shippingInfo = Shipping::getInfo();
			$billingInfo = Billing::getInfo();
			$useShippingAddress = Billing::getUsingShippingAddress();
			$username = (string)$this->_getRequest("username");
			$username = trim($username);
			$password = (string)$this->_getRequest("password");
			$password = trim($password);
			$patientModel = new Model_Patient();

			// prepare input data
			$patient = new Model_Entity_Patient();
			$patient->username						= $username;

			$patient->firstname					= $shippingInfo->firstname;
			$patient->lastname					= $shippingInfo->lastname;
			$patient->sex = (string)$this->_getRequest('Sex');
			$patient->weight = new stdClass();
			$patient->weight->unit = 'lbs';
			$patient->weight->value = (string)$this->_getRequest('Weight');
			$patient->height = new stdClass();
			$patient->height->feet = (string) $this->_getRequest('HeightFeet');
			$patient->height->inches = (string) $this->_getRequest('HeightInches');

			$birthdateMonth = (string) $this->_getRequest('BirthDate_MONTH');
			$birthdateDay = (string) $this->_getRequest('BirthDate_DAY');
			$birthdateYear = (string) $this->_getRequest('BirthDate_YEAR');
			$patient->dateofbirth					= $birthdateYear . '-' . $birthdateMonth . '-' . $birthdateDay;

			$patient->areacode					= $_SESSION['Account_phoneAreaCode'];
			$patient->phone						= $_SESSION['Account_phone'];
			$patient->areacode_fax 				= $shippingInfo->areacodefax;
			$patient->fax						= $shippingInfo->fax;
			$patient->email						= $username;
			$patient->child_resistant_packaging	= 'Yes';
			$patient->call_for_refills			= 'True';
			$patient->password					= $password;
			$patient->address					= new Model_Entity_PatientAddress();
			if ($useShippingAddress) {
				$patient->address->address1			= $shippingInfo->address1;
				$patient->address->address2			= $shippingInfo->address2;
				$patient->address->address3			= $shippingInfo->address3;
				$patient->address->city				= $shippingInfo->city;
				$patient->address->province			= $shippingInfo->province;
				$patient->address->country			= $shippingInfo->country;
				$patient->address->postalcode		= $shippingInfo->postalcode;

				if (strlen($shippingInfo->phone) >= 7) {
					$patient->areacode_day			= $shippingInfo->areacode;
					$patient->phone_day				= $shippingInfo->phone;
				}
			} else {
				$patient->address->address1			= $billingInfo->address1;
				$patient->address->address2			= $billingInfo->address2;
				$patient->address->address3			= $billingInfo->address3;
				$patient->address->city				= $billingInfo->city;
				$patient->address->province			= $billingInfo->province;
				$patient->address->country			= $billingInfo->country;
				$patient->address->postalcode		= $billingInfo->postalcode;

				if (strlen($billingInfo->phone) >= 7) {
					$patient->areacode_day			= $billingInfo->areacode === '' ? $_SESSION['Account_phoneAreaCode'] : $billingInfo->areacode;
					$patient->phone_day				= $billingInfo->phone === '' ? $_SESSION['Account_phone'] : $billingInfo->phone;
				}
			}

			// if an affiliate is set in session create new patient under that affiliate

			if (Model_Affiliate::sessionAffiliateExists()) {
				$patient->affiliate_id = Model_Affiliate::getSessionAffiliateID();
				$patient->agent_id = Model_Affiliate::getSessionAffiliateAgentID();
			}

			// call method
			$status = $patientModel->createPatient($patient);

			if (Utility_Common::isReplySuccess($status)) {
				WebUser::setUserID($status->patient_id);

				Page_Register::sendEmailNewPatient($patient);

				$bResult = true;
			} else {
				$error = Utility_Html::displayResult($status);
				$error = PC_genErrorMessage($error);
				$this->assign('ERROR_USERNAME', $error);
			}
		}
		return $bResult;
	}

	// save Order to Pharmacy system
	public function saveOrder()
	{
		$orderModel = new Model_Order();
		$userInfor = WebUser::getUserInfo();
		// prepare input data
		if (!isset($orderInfor)) {
			$orderInfor = new stdClass();
		}
		$orderInfor->patientid		= WebUser::getUserID();
		$orderInfor->forwarding		= $userInfor->email;
		$orderInfor->child_resistant_packaging = $userInfor->child_resistant_packaging;
		$orderInfor->contact_patient	= '';
		$orderInfor->special_handling	= '';

		if (Shipping::getAddressRef() > 0) {
			// set shipping refer
			$orderInfor->shippingAddressRef = Shipping::getAddressRef();
		} else {
			$shipping = Shipping::getInfo();
			$orderInfor->shippingAddress = $shipping;
		}

		//set data for payment
		$this->setPaymentInforToOrder($orderInfor);

		$lstItems = Cart::getListItems();
		$messages = new Utility_Messages;
		if (get_option('pw_split_multi_country_orders') == 'on') {
			$countries = Cart::getFillingVendorIDs($lstItems);
		} else {
			$countries = array('all');
		}
		$countryIndex = 0;
		while ($countryIndex < count($countries)) {
			$orderTotal = 0;
			$orderInfor->Items = array();
			$orderItems = array();
			foreach ($lstItems as $item) {
				if ($countries[$countryIndex] == 'all' || $countries[$countryIndex] == $item->filling_vendor_id) {
					$orderItems[] = $item;
					$product = new stdClass();
					$product->productID = $item->package_id;
					$product->quantity = $item->amount;
					$product->price = $item->price;
					$orderTotal = $product->quantity * $item->price;
					$orderInfor->Items[] = $product;
				}
			}
			$orderInfor->shippingfee = Cart::calculateShippingFee($orderItems);
			$orderTotal += $orderInfor->shippingfee;

			// Add coupon item(s) to order
			// get all coupons including admin coupons (1)
			$orderCoupons = new Model_Coupon;
			$orderInfor->Coupons = $orderCoupons->getCouponSession(1);

			$coupons = $orderInfor->Coupons;

			if (!isset($comments)) {
				$comments = array();
			}
			$additionalNotes = Cart::getAdditionalNotes();
			if (strlen($additionalNotes)) {
				$comments[] = 'Patient(s) taking medications: ' . $additionalNotes;
			}

			if (is_array($coupons)) {
				foreach ($coupons as $coupon) {
					if (array_key_exists('coupon-comment', $coupon)) {
						$comments[] = 'coupon ' . $coupon['coupon-code'] . ' comment: ' . $coupon['coupon-comment'];
					}
				}
			}

			$orderInfor->Comments = $comments;

			// call method
			$status = $orderModel->submitOrder($orderInfor);
			if ($status->status == "success") {
				Cart::setLastOrder($status->order, $orderInfor, $orderItems, $orderTotal, $status->show_billing_detail, $status->billing_account_activation);
				if ($countryIndex == 0 && count($countries) > 1) {
					$messages->setNotification('Information-Header', 'Individual orders were created for each filling pharmacy on your order as follows:');
				} elseif ($countryIndex == 0) {
					$orderIDs = Cart::getLastOrder();
					$messages->setNotification('Information-Header', 'Your order #' . $orderIDs[0] . ' has been submitted for processing.');
				}
				if (count($countries) > 1) {
					$messages->setNotification('Information', 'Order #' . $status->order);
				}
			}

			// check if reply is failure
			$this->displayErrorRequest($status);

			$countryIndex++;
		}

		return $status;
	}

	public function setPaymentInforToOrder(&$orderInfor)
	{
		if (!isset($payment)) {
			$payment = new stdClass();
		}
		
		$billingInfor = Billing::getInfo();

		$orderInfor->billingType = $billingInfor->methodtype;
		$methodType = strtoupper($orderInfor->billingType);

		switch ($methodType) {
			case "DRAFT":
				$shippingInfor = Shipping::getInfo();
				$shippingDestination = Cart::getShippingDestination();
				$payment->amount = Cart::getSubTotal() + Cart::getShippingFee($shippingDestination);
				$payment->draftnumber = '';
				$payment->firstname = empty($billingInfor->firstname) ? $shippingInfor->firstname : $billingInfor->firstname;
				$payment->middlename = '';
				$payment->lastname = empty($billingInfor->lastname) ? $shippingInfor->lastname : $billingInfor->lastname;
				break;
			case "EFT":
				$payment->bankName		= $billingInfor->bankName;
				$payment->bankCity		= $billingInfor->bankCity;
				$payment->bankState		= $billingInfor->bankState;
				$payment->nameOnCheque	= $billingInfor->nameOnCheque;
				$payment->branchTransit	= $billingInfor->branchTransit;
				$payment->chequeAccount	= $billingInfor->chequeAccount;
				$payment->chequeNumber	= $billingInfor->chequeNumber;
				$payment->address		= $billingInfor->address1;
				$payment->city			= $billingInfor->city;
				$payment->state			= $billingInfor->province;
				$payment->country		= $billingInfor->country;
				$payment->postalcode	= $billingInfor->postalcode;
				$payment->idnumber		= $billingInfor->idnumber;
				$payment->idtype		= $billingInfor->idtype;
				$payment->idstatecode	= $billingInfor->idstatecode;
				$payment->checktype		= $billingInfor->checktype;
				$payment->dob			= $billingInfor->dob;
				$payment->areacode		= empty($billingInfor->areacode) ? $_SESSION['Account_phoneAreaCode'] : $billingInfor->areacode;
				$payment->phone			= empty($billingInfor->phone) ? $_SESSION['Account_phone'] : $billingInfor->phone;

				break;
			case "CREDITCARD":

				$payment->cardtype		= $billingInfor->creditcardtype;
				$payment->cardnumber	= $billingInfor->creditcardnumber;
				$payment->cvvnumber		= $billingInfor->cvvnumber;
				$payment->expirymonth	= $billingInfor->expirymonth;
				$payment->expiryyear	= $billingInfor->expiryyear;

				$payment->firstname		= $billingInfor->firstname;
				$payment->middlename	= '';
				$payment->lastname		= $billingInfor->lastname;

				$payment->address		= $billingInfor->address1;
				$payment->city			= $billingInfor->city;
				$payment->state			= $billingInfor->province;
				$payment->country		= $billingInfor->country;
				$payment->postalcode	= $billingInfor->postalcode;
				$payment->areacode		= empty($billingInfor->areacode) ? $_SESSION['Account_phoneAreaCode'] : $billingInfor->areacode;
				$payment->phone			= empty($billingInfor->phone) ? $_SESSION['Account_phone'] : $billingInfor->phone;

				break;
		}
		

		$orderInfor->paymentinfo = $payment;
	}
	// delete information in shipping cart, shipping information and billing informtion
	public function deleteInforAfterSave()
	{
		Cart::resetInfo();
	}
	public function checkCartEmpty()
	{
		/*
            if(!WebUser::isLoggedIn()){
                $url = PC_getHomePageURL();
                $this->redirect($url);
            }
            */
		if (!Cart::haveItems()) {
			$url = PC_getHomePageURL();
			$this->redirect($url);
		}
	}
	public function checkCartReady()
	{
		// if billing has not been set on cart redirect to billing
		$billingInfor = Billing::getInfo();
		if ($billingInfor->methodtype == null) {
			$url = PC_getBillingURL();
			$this->redirect($url);
		}
	}
	public function addLegalAgreement()
	{
		$leggalInfor  = new stdClass();
		$leggalInfor->patient_id	= WebUser::getUserID();
		$leggalInfor->fullname		= WebUser::getFullName();
		$leggalInfor->agree			= "Yes";
		$leggalInfor->date			= date("Y-m-d");

		$patient = new Model_Patient();
		$status = $patient->addLegalAgreement($leggalInfor);
		return $status;
	}
	public function setupAddress()
	{
		$this->assign("PW_NAME", get_option('pw_name'));
		$this->assign("PW_ADDRESS", get_option('pw_address'));
		$this->assign("PW_POSTAL_CODE", get_option('pw_postal_code'));
		$this->assign("PW_CITY", get_option('pw_city'));
		$this->assign("PW_PROVINCE", get_option('pw_province'));
		$this->assign("PW_COUNTRY", get_option('pw_country'));
	}
	public function validData()
	{
		$isValid = true;
		$username = (string) $this->_getRequest('username');
		$password = (string) $this->_getRequest('password');
		$birthdateMonth = (string) $this->_getRequest('BirthDate_MONTH');
		$birthdateDay = (string) $this->_getRequest('BirthDate_DAY');
		$birthdateYear = (string) $this->_getRequest('BirthDate_YEAR');
		$username = trim($username);
		if (trim($username) == "") {
			$err_User = 'The email address is invalid!';
			$isValid = false;
		} else {
			if (!Utility_Common::isEmail($username)) {
				$err_User = 'The email address is invalid!';
				$isValid = false;
			}
		}

		if ($err_User !== "") {
			$err_User = PC_genErrorMessage($err_User);
			$this->assign('ERROR_USERNAME', $err_User);
		}
		if (trim($password) == "") {
			$isValid = false;
		}

		if (strlen($password) < 6) {
			$this->assign('ERROR_PASSWORD', 'Password is a required field.');
		}
		// check BirthDate
		if (!is_numeric($birthdateMonth) || !is_numeric($birthdateDay) || !is_numeric($birthdateYear)) {
			$this->assign('ERROR_BIRTHDATE', 'BirthDate is a required field.');
			$isValid = false;
		}

		return $isValid;
	}
}
