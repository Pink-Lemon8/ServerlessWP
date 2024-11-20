<?php
class Page_Checkout_Billing extends Utility_PageBase
{
	/**
	 * Make the html output
	 *
	 * @return The content in html format
	 */
	public function _process()
	{
		$this->checkCartEmpty();
		$this->processBilling();
	}

	// process when user on billing tab
	public function processBilling()
	{
		$data = $_POST;

		$this->setTemplate("page_checkout_billing");

		$action = strtoupper($this->_getRequest('action'));
		if ($action == "CHANGECOUNTRY") {
			$this->saveDataToSession();
		}
		if ($action === "NEXT") {
			$this->saveDataToSession();
			$isValid = $this->validData($data);
			if ($isValid) {
				$url_confirm = PC_getConfirmURL();
				$this->redirect($url_confirm);
				exit;
			}
		}

		$infor = Billing::getInfo();
		$patientInfo = $this->_getPatientInfo();
		$paymentMethod = $this->_getRequest('PaymentMethod') ? $this->_getRequest('PaymentMethod') : $infor->methodtype;
		$option_payments = PC_getPaymentMethodOptionsHTML($paymentMethod, $patientInfo->attributes);
		$this->assign("OPTION_PAYMENT_METHOD", $option_payments);

		$this->_populateForm($data);
		$this->setupAddress();
		$this->setupBillingAddressPreview();
		if (PC_activatePaymentDraftCapture()) {
			$this->parse("BILLING_INFO.METHOD.DRAFT");
		}
		if (PC_activatePaymentEFTCapture()) {
			$this->process_EFTMethod();
		}
		if (!empty(PC_getCreditCartTypeOptionsHTML())) {
			$this->process_CreditCartMethod();
		}

		$this->parse("BILLING_INFO.METHOD");
		$this->parse("BILLING_INFO");
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

	public function setupBillingAddressPreview()
	{
		$shippingInfor = Shipping::getInfo();
		$this->assign("PREVIEW_BILLING_ADDRESS1", $shippingInfor->address1);
		$this->assign("PREVIEW_BILLING_ADDRESS2", $shippingInfor->address2);
		$this->assign("PREVIEW_BILLING_CITY", $shippingInfor->city);
		$this->assign("PREVIEW_BILLING_PROVINCE", $shippingInfor->province);
		$this->assign("PREVIEW_BILLING_COUNTRY", $shippingInfor->country);
		$this->assign("PREVIEW_BILLING_POSTALCODE", $shippingInfor->postalcode);

		$previewbilling_phone = $shippingInfor->phone ? '(' . $shippingInfor->areacode . ') ' . $shippingInfor->phone : '';
		$this->assign("PREVIEW_BILLING_PHONE", $previewbilling_phone);
	}

	public function process_EFTMethod()
	{
		$infor = Billing::getInfo();

		$this->assign("NAME_ON_CHEQUE", $infor->nameOnCheque);
		$this->assign("BANK_NAME", $infor->bankName);
		$this->assign("BANK_CITY", $infor->bankCity);
		$this->assign("BANK_STATE", $infor->bankState);
		$this->assign("BRANCH_TRANSIT", $infor->branchTransit);
		$this->assign("CHEQUE_ACCOUNT", $infor->chequeAccount);
		$this->assign("CHEQUE_NUMBER", $infor->chequeNumber);

		$this->parse("BILLING_INFO.METHOD.EFT");
	}

	public function process_CreditCartMethod()
	{
		$infor = Billing::getInfo();
		$creditCardType		= $infor->creditcardtype;
		$creditCardNumber	= $infor->creditcardnumber;
		$cvvNumber			= $infor->cvvnumber;
		$expiryMonth		= $infor->expirymonth;
		$expiryYear			= $infor->expiryyear;

		$creditCartType = PC_getCreditCartTypeOptionsHTML($creditCardType);
		$expiryMonth	= PC_getMonthOptions($expiryMonth);
		$expiryYear		= PC_getYearOptions($expiryYear);

		$maskedCreditCardNumber = $this->mask_number($creditCardNumber);

		if (preg_match('/^\d{3,4}$/', $cvvNumber)) {
			$cvvNumber = preg_replace('/\d/', '*', $cvvNumber);
		}

		$this->assign("CREDIT_CARD_NUMBER", $maskedCreditCardNumber);
		$this->assign("CVV_NUMBER", $cvvNumber);
		$this->assign("CREDIT_CARD_TYPE", $creditCartType);
		$this->assign("EXPIRY_MONTH", $expiryMonth);
		$this->assign("EXPIRY_YEAR", $expiryYear);

		$this->parse("BILLING_INFO.METHOD.CREDIT_CARD");
	}

	public function mask_number($number, $count = 4, $seperators = '-')
	{
		$masked = preg_replace('/\d/', '*', $number);
		$last = preg_match(sprintf('/([%s]?\d){%d}$/', preg_quote($seperators), $count), $number, $matches);
		if ($last) {
			list($clean) = $matches;
			$masked = substr($masked, 0, -strlen($clean)) . $clean;
		}
		return $masked;
	}

	//save data to billing session
	public function saveDataToSession()
	{
		$currentInfor = Billing::getInfo();

		// Hidden input set by jQuery method usingShippingCheckBox
		$useShippingInfor = (string)$this->_getRequest('billing_useShippingAddress');

		$isChecked = 0;
		if ($useShippingInfor === 'yes') {
			$isChecked = 1;
		}

		$firstName 		= (string)$this->_getRequest('firstName');
		$lastName 		= (string)$this->_getRequest('lastName');
		$address1 		= (string)$this->_getRequest('Address1');
		$address2 		= (string)$this->_getRequest('Address2');
		$address3 		= '';
		$city 			= (string)$this->_getRequest('City');
		$province 		= (string)$this->_getRequest('billing_region');
		$country 		= (string)$this->_getRequest('billing_country');
		$postalCode 	= (string)$this->_getRequest('PostalCode');
		$areaCode  		= (string)$this->_getRequest('phoneAreaCode');
		$phoneHome 		= (string)$this->_getRequest('phone');

		$billingCountry = Utility_Html::htmlSelectCountry('billing_country', $country, ' tabindex="80" ');
		$billingProvince = Utility_Html::htmlSelectProvince('billing_region', $province, $country, ' tabindex="90" ');
		$this->assign('SELECT_BILLING_PROVINCE', $billingProvince);
		$this->assign('SELECT_BILLING_COUNTRY', $billingCountry);

		$methodType = (string)$this->_getRequest('PaymentMethod');
		$creditCardType = (string)$this->_getRequest('CreditCardType');
		$creditCardNumber = (string)$this->_getRequest('CreditCardNumber');

		$maskedCard = preg_match("/^\*/", $creditCardNumber);
		if (isset($currentInfor->creditcardnumber) && $maskedCard) {
			$creditCardNumber = $currentInfor->creditcardnumber;
		}

		$cvvNumber = (string)$this->_getRequest('CvvNumber');
		$maskedCvv = preg_match("/^\*/", $cvvNumber);
		if (isset($currentInfor->cvvnumber) && $maskedCvv) {
			$cvvNumber = $currentInfor->cvvnumber;
		}

		$expiryMonth = (string)$this->_getRequest('ExpiryMonth');
		$expiryYear = (string)$this->_getRequest('ExpiryYear');

		# Get EFT information
		$nameOnCheque = (string)$this->_getRequest('NameOnCheque');
		$bankName = (string)$this->_getRequest('BankName');
		$bankCity = (string)$this->_getRequest('BankCity');
		$bankState = (string)$this->_getRequest('BankState');
		$branchTransit = (string)$this->_getRequest('BranchTransit');
		$chequeAccount = (string)$this->_getRequest('ChequeAccount');
		$chequeNumber = (string)$this->_getRequest('ChequeNumber');

		if ($isChecked) {
			$shippingInfo = Shipping::getInfo();

			$address1 		= $shippingInfo->address1;
			$address2 		= $shippingInfo->address2;
			$address3 		= '';
			$city 			= $shippingInfo->city;
			$province 		= $shippingInfo->province;
			$country 		= $shippingInfo->country;
			$postalCode 	= $shippingInfo->postalcode;
			$areaCode  		= $shippingInfo->areacode;
			$phoneHome 		= $shippingInfo->phone;
			$firstName = empty($firstName) ? $shippingInfo->firstname : $firstName;
			$lastName = empty($lastName) ? $shippingInfo->lastname : $lastName;
			$billingProvince = Utility_Html::htmlSelectProvince('billing_region', $province, $country, ' tabindex="60" ');
			$this->assign('SELECT_BILLING_PROVINCE', $billingProvince);
			$billingCountry = Utility_Html::htmlSelectCountry('billing_country', $province, ' tabindex="50" ');
			$this->assign('SELECT_BILLING_COUNTRY', $billingCountry);
		}

		Billing::setInfor($address1, $address2, $address3, $city, $province, $country, $postalCode, $areaCode, $phoneHome, $methodType, $creditCardType, $creditCardNumber, $cvvNumber, $expiryMonth, $expiryYear, $firstName, $lastName, $nameOnCheque, $bankName, $bankCity, $bankState, $branchTransit, $chequeAccount, $chequeNumber);

		$infor = &Billing::getInfo();

		$infor->methodtype = $methodType;
		$infor->creditcardtype = $creditCardType;
		$infor->creditcardnumber = $creditCardNumber;
		$infor->cvvnumber = $cvvNumber;
		$infor->expirymonth = $expiryMonth;
		$infor->expiryyear = $expiryYear;

		Billing::setUsingShippingAddress($isChecked);
	}

	// valid data
	public function validData($data)
	{
		$isValid = true;
		$infor = Billing::getInfo();

		$paymentMethod = $infor->methodtype;

		if (strtoupper($paymentMethod) === "DRAFT") {
			// If payment method is draft skip validation
			return $isValid;
		}

		if ((strtoupper($paymentMethod) === "EFT") && PC_activatePaymentEFTCapture()) {
			$matches = array();
			preg_match('/^([0123]\d{8})$/', $infor->branchTransit, $matches);
			if (!$matches[1]) {
				$this->assign('ERROR_BRANCH_TRANSIT', PC_genErrorMessage('Transit/Routing number must start with 0, 1, 2 or 3 and must be 9 digits.'));
				$isValid = false;
			}
			preg_match('/^([0-9]+)$/', $infor->chequeNumber, $matches);
			if (!$matches[1]) {
				$this->assign('ERROR_CHEQUE_NUMBER', PC_genErrorMessage('Cheque number must be numeric.'));
				$isValid = false;
			}
			preg_match('/^([0-9]+)$/', $infor->chequeAccount, $matches);
			if (!$matches[1]) {
				$this->assign('ERROR_CHEQUE_ACCOUNT', PC_genErrorMessage('Account number must be numeric.'));
				$isValid = false;
			}
		} else {
			$creditCardType		= $infor->creditcardtype;
			$creditCardNumber	= $infor->creditcardnumber;
			$cvvNumber			= $infor->cvvnumber;
			$expiryMonth		= $infor->expirymonth;
			$expiryYear			= $infor->expiryyear;
			$firstName			= $infor->firstname;
			$lastName			= $infor->lastname;

			if (trim($creditCardNumber) == "") {
				$msg = PC_genErrorMessage("Credit Card Number is invalid");
				$this->assign('ERROR_CREDIT_CARD_NUMBER', $msg);
				$isValid = false;
			} else {
				// Sep 30 2020, removed checkCreditCard utility/phpcreditcard.php file as it's not used
				// $errornumber = "";
				// $errortext = "";
				// $validCard = checkCreditCard($creditCardNumber, $creditCardType, $errornumber, $errortext);
				// if (!$validCard) {
				// 	$msg = PC_genErrorMessage("Credit Card Type is invalid with the credit card number given.");
				// 	$this->assign('ERROR_CREDIT_CARD_TYPE', $msg);
				// 	$isValid = false;
				// }
			}

			if (trim($cvvNumber) == "") {
				$msg = PC_genErrorMessage("CVV2 Number must be entered");
				$this->assign('ERROR_CVV_NUMBER', $msg);
				$isValid = false;
			} else {
				$errornumber = "";
				$errortext = "";
				if (!preg_match('/^\d{3,4}$/', $cvvNumber)) {
					$msg = PC_genErrorMessage("CVV2 Number is invalid.");
					$this->assign('ERROR_CVV_NUMBER', $msg);
					$isValid = false;
				}
			}


			$curDate = (int)date("Ym");
			$expireDate = (int)($expiryYear . $expiryMonth);

			if ($expireDate <= $curDate) {
				$msg = PC_genErrorMessage("Credit Card expire date is invalid.");
				$this->assign('ERROR_EXPIRATION_DATE', $msg);
				$isValid = false;
			}
			if (trim($firstName) == "") {
				$msg = PC_genErrorMessage("First name is a required field.");
				$this->assign('ERROR_FIRSTNAME', $msg);
				$isValid = false;
			}
			if (trim($lastName) == "") {
				$msg = PC_genErrorMessage("Last name is a required field.");
				$this->assign('ERROR_LASTNAME', $msg);
				$isValid = false;
			}
		}
		return $isValid;
	}

	public function _populateForm($data)
	{

		//BillingInfor from session variable
		$billingInfor = Billing::getInfo();
		$shippingInfor = Shipping::getInfo();

		$firstName = empty($billingInfor->firstname) ? $shippingInfor->firstname : $billingInfor->firstname;
		$lastName = empty($billingInfor->lastname) ? $shippingInfor->lastname : $billingInfor->lastname;
		$this->assign('VALUE_NAME_ON_CHEQUE', $firstName . ' ' . $lastName);
		$this->assign('VALUE_LASTNAME', $lastName);
		$this->assign('VALUE_FIRSTNAME', $firstName);
		$this->assign('VALUE_LASTNAME', $lastName);
		$this->assign('VALUE_ADDRESS1', $billingInfor->address1);
		$this->assign('VALUE_ADDRESS2', $billingInfor->address2);
		$this->assign('VALUE_ADDRESS3', $billingInfor->address3);
		$this->assign('VALUE_CITY', $billingInfor->city);

		$billingCountry = Utility_Html::htmlSelectCountry('billing_country', $billingInfor->country, ' tabindex="50" ');
		$billingProvince = Utility_Html::htmlSelectProvince('billing_region', $billingInfor->province, $billingInfor->country, ' tabindex="60" ');

		$this->assign('SELECT_BILLING_PROVINCE', $billingProvince);
		$this->assign('SELECT_BILLING_COUNTRY', $billingCountry);
		$this->assign('VALUE_POSTALCODE', $billingInfor->postalcode);
		$this->assign('VALUE_AREACODEPHONE', $billingInfor->areacode);
		$this->assign('VALUE_PHONE', $billingInfor->phone);

		$isChecked = false;

		// Use useShippingForBilling if set, otherwise determine default state
		if (isset($data['billing_useShippingAddress'])) {
			$useInfor = (string)$this->_getRequest('billing_useShippingAddress');

			if ($useInfor === 'yes') {
				$isChecked = true;
			}
		} else {
			if (WebUser::isLoggedIn() && !Billing::getUsingShippingAddress()) {
				$isChecked = false;
			} else {
				$isChecked = true;
			}
		}

		if ($isChecked) {
			$this->assign('VALUECHECKED', 'checked="checked"');
			$this->assign('VALUECHECKEDCSS', 'checked');
			$this->assign('BILLING_FORM_STYLES', ' style="display: none;"');
			$this->assign('BILLING_PREVIEW_STYLES', ' style="display: block;"');
			$this->assign('DISABLED', 'disabled="disabled"');
			$this->assign('USESHIPPINGFORBILLING', 'yes');
		} else {
			$this->assign('VALUECHECKED', '');
			$this->assign('VALUECHECKEDCSS', '');
			$this->assign('BILLING_PREVIEW_STYLES', ' style="display: none;"');
			$this->assign('BILLING_FORM_STYLES', ' style="display: block;"');
			$this->assign('USESHIPPINGFORBILLING', 'no');
		}

		$methodType = $billingInfor->methodtype;
		if (!$methodType) {
			$defaultPaymentType = '';
			if (!empty(PC_getCreditCartTypeOptionsHTML())) {
				$defaultPaymentType = 'creditcard';
			} elseif (PC_activatePaymentDraftCapture()) {
				$defaultPaymentType = 'draft';
			} elseif (PC_activatePaymentEFTCapture()) {
				$defaultPaymentType = 'eft';
			}
			$methodType = get_option('pw_default_billing_type') ? get_option('pw_default_billing_type') : $defaultPaymentType;
		}
		if ($methodType === 'draft') {
			$this->assign('CREDIT_CARD_VISIBILITY', 'style="display: none;"');
			$this->assign('EFT_VISIBILITY', 'style="display: none;"');
		} elseif ($methodType === 'eft') {
			$this->assign('DRAFT_VISIBILITY', 'style="display: none;"');
			$this->assign('CREDIT_CARD_VISIBILITY', 'style="display: none;"');
		} else {
			$this->assign('EFT_VISIBILITY', 'style="display: none;"');
			$this->assign('DRAFT_VISIBILITY', 'style="display: none;"');
		}
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

	public function checkCartEmpty()
	{
		if (!Cart::haveItems()) {
			$checkout_url = PC_getShoppingURL();
			$this->redirect($checkout_url);
			exit;
		}
	}

	public function _isBillToShippingAddr()
	{
		$billInfor = Billing::getInfo();
		return $billInfor->useShipping;
	}
}
