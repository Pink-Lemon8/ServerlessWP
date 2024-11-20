<?php

/**
 * Page_Register
 */
class Page_Register extends Utility_PageBase
{
	/**
	 * Make the html output
	 *
	 * @return The content in html format
	 */
	public function _process()
	{
		$data = $_POST;

		$this->setTemplate('page_register');
		if (WebUser::isLoggedIn()) {
			$this->redirect(PC_getProfileUrl());
		}
		$action = $this->_getRequest('action', '');
		switch ($action) {
			case 'Create Account':
				$this->registerSubmit();
				break;
			case 'CHANGECOUNTRY':
				$this->_populateForm($data);
				break;
			default:
				$submitted = $this->_getRequest('submitted');
				if ($submitted == 1) {
					$this->_populateForm($data);
				} else {
					$this->displayForm();
				}
				break;
		}
	}

	public function displayForm()
	{
		$data = array();
		$this->_initForm();
	}

	/**
	 * Execute the submit register function
	 *
	 */
	public function registerSubmit()
	{
		$data = $_POST;
		$data = GUMP::xss_clean($data);
		$validateData = $this->_validate($data);
		if ($validateData['is_valid'] == 1) {
			$result = $this->_saveData($data);

			if (Utility_Common::isReplySuccess($result)) {
				$data['PatientId'] = $result->patient_id;

				// save shipping
				if (!$this->_useShippingForBilling($data)) {
					$addShipAddressResponse = new stdClass();
					$addShipAddressResponse = $this->_saveShippingAddress($data);
				}

				// Set status of user to logged
				$user = new stdClass();
				$user->id = $result->patient_id;
				$user->name = $data['Username'];
				WebUser::setLogin($user);

				$this->sendEmailNewPatient($data);

				$_SESSION['NewPatientCreated'] = 'page';

				$this->setTemplate('page_register_success');

				$homeUrl = PC_getHomePageURL();

				$this->assign('HOME', $homeUrl);

				$this->_displayResult($result);
			} else {
				$this->_displayResult($result);
				$this->_populateForm($data);
			}
		} else {
			$this->_populateForm($data);
		}
	}

	/**
	 * Create User Account/Patient via JSON
	 * Save shipping address can be disabled if the patient
	 * creation is being created in conjuction with an order
	 * submit that would also otherwise save the address
	 */
	public function registerSubmitJSON($saveShippingAddress = 1)
	{
		$data = $_POST;
		$data = GUMP::xss_clean($data);
		$success = 0;
		$message = '';
		$patientID = 0;

		$validateData = $this->_validate($data);
		if ($validateData['is_valid'] == 1) {
			$result = $this->_saveData($data);

			if (Utility_Common::isReplySuccess($result)) {

				$data['PatientId'] = $result->patient_id;

				// log the user in which also clears/preps the session
				$user = new stdClass();
				$user->id = $result->patient_id;
				$user->name = $data['Username'];
				WebUser::setLogin($user);
				
				// save shipping address after being logged in to setup/default the ref to the new shipping address
				if ($saveShippingAddress && !$this->_useShippingForBilling($data)) {
					$addShipAddressResponse = new stdClass();
					$addShipAddressResponse = $this->_saveShippingAddress($data);

					// Set shipping address to session
					if ($addShipAddressResponse->status == 'success') {
						$shipping = new Shipping();
						$shipping->setAddressRef($addShipAddressResponse->shippingaddress_id);
					}
				}

				$this->sendEmailNewPatient($data);

				$_SESSION['NewPatientCreated'] = 'json';

				$patientID = $result->patient_id;
				$success = 1;
			} else {
				foreach ($result->messages as $msg) {
					$message .= $msg->content;
				}
			}
		} else {
			$this->_populateForm($data);
			foreach ($validateData['validation_response'] as $msg) {
				$message .= $msg . '<br />';
			}
		}

		$replyContent = array();
		$replyContent['type'] = 'debug';
		$replyContent['content']['value'] = $message;

		$reply = new Model_Entity_Reply();
		$reply->success = $success;
		$reply->messages = array($replyContent);
		$reply->content = new stdClass();
		$reply->content->patient_id = $patientID;

		return $reply->toJSON();
	}

	/**
	 * Display result after submitting
	 *
	 * @param mixed $result
	 */
	public function _displayResult($result)
	{
		$html = '<div class="callout alert">';
		$html .= Utility_Html::displayResult($result);
		$html .= '</div>';

		$this->assign('RESULT_MESSAGES', $html);
	}

	public function _initForm()
	{

		/* Contact Info */
		$data['firstName'] = '';
		$data['lastName'] = '';
		$data['phoneAreaCode'] = '';
		$data['phone'] = '';

		$data['phoneAreaCode'] = '';
		$data['DayPhone'] = '';

		$data['Address'] = '';
		$data['Address2'] = '';
		$data['Address3'] = '';
		$data['City'] = '';
		$data['Province'] = '';
		$data['Country'] = 'USA';
		$data['PostalCode'] = '';

		$data['AreaCodeFax'] = '';
		$data['Fax'] = '';

		$data = $this->_setupShipping($data);
		$data = $this->_setupBilling($data);
		$data = $this->_setupLogin($data);
		$data = $this->_setupMedicalProfile($data);
		$this->_populateForm($data);
	}

	public function _setupShipping($data)
	{

		$countryModel = new Model_Country();
		$countries = $countryModel->getAllowedCountryRegionsList();

		$allowedCountries = array_keys($countries);
		$defaultCountry = key($countries);
		if (in_array('USA', $allowedCountries)) {
			$defaultCountry = 'USA';
		}

		/* Shipping Info */
		$data['shipping_address1'] = '';
		$data['shipping_address2'] = '';
		$data['shipping_city'] = '';
		$data['shipping_region'] = '';
		$data['shipping_country'] = $defaultCountry;
		$data['shipping_regionCode'] = '';
		$data['shipping_phoneAreaCode'] = '';
		$data['shipping_phone'] = '';

		$shippingCountry    = Utility_Html::htmlSelectCountry('shipping_country', $defaultCountry);
		$shippingProvince   = Utility_Html::htmlSelectProvince('shipping_region', '', $defaultCountry);

		$this->assign('SELECT_SHIPPING_PROVINCE', $shippingProvince);
		$this->assign('SELECT_SHIPPING_COUNTRY', $shippingCountry);

		return $data;
	}

	public function _setupBilling($data)
	{

		$countryModel = new Model_Country();
		$countries = $countryModel->getAllowedCountryRegionsList();

		$allowedCountries = array_keys($countries);
		$defaultCountry = key($countries);
		if (in_array('USA', $allowedCountries)) {
			$defaultCountry = 'USA';
		}

		/* Billing Info */
		$data['billing_useShippingAddress'] = 'checked';
		$data['billing_address1'] = '';
		$data['billing_address2'] = '';
		$data['billing_city'] = '';
		$data['billing_region'] = '';
		$data['billing_country'] = $defaultCountry;
		$data['billing_regionCode'] = '';
		$data['phoneAreaCode'] = '';
		$data['phone'] = '';

		$billingCountry = Utility_Html::htmlSelectCountry('billing_country', $defaultCountry);
		$billingProvince = Utility_Html::htmlSelectProvince('billing_region', '', $defaultCountry);

		$this->assign('SELECT_BILLING_PROVINCE', $billingProvince);
		$this->assign('SELECT_BILLING_COUNTRY', $billingCountry);

		$this->assign('BILLING_PREVIEW_STYLES', ' style="display: none;"');

		return $data;
	}

	public function _setupLogin($data)
	{
		$data['Username'] = '';
		$data['ConfirmUsername'] = '';
		$data['Password'] = '';
		$data['ConfirmPassword'] = '';

		return $data;
	}

	public function _setupMedicalProfile($data)
	{
		$data['HeightFeet'] = '-1';
		$data['HeightInches'] = '0';
		$data['Weight'] = '';
		$data['Sex'] = '';
		$data['BirthDate_MONTH'] = '1';
		$data['BirthDate_DAY'] = '';
		$data['BirthDate_YEAR'] = '';
		$data['ChildResistantPkg'] = 'Yes';
		$data['RefillNotification'] = 'True';

		return $data;
	}

	/**
	 * Create the patient
	 *
	 * @param mixed $data
	 */
	public function _saveData($data)
	{
		$patientModel = new Model_Patient();

		// prepare input data
		$patient = new Model_Entity_Patient();
		$patient->affiliate_id = '';
		$patient->agent_id = '';
		$patient->username = $data['Username'];
		$patient->password = $data['Password'];

		$patient->firstname = $data['firstName'];
		$patient->lastname = $data['lastName'];
		$patient->dateofbirth = $data['BirthDate_YEAR'] . '-' . $data['BirthDate_MONTH'] . '-' . $data['BirthDate_DAY'];
		$patient->phone = $data['phone'];
		$patient->areacode = $data['phoneAreaCode'];
		$patient->fax = $data['Fax'];
		$patient->areacode_fax = $data['AreaCodeFax'];
		$patient->email = $data['Username'];
		$patient->sex = $data['Sex'];
		$patient->height = new stdClass();
		$patient->height->feet = $data['HeightFeet'];
		$patient->height->inches = $data['HeightInches'];
		$patient->weight = new stdClass();
		$patient->weight->unit = 'lbs';
		$patient->weight->value = $data['Weight'];
		$patient->child_resistant_packaging = (!empty($data['ChildResistantPkg'])) ? $data['ChildResistantPkg'] : $data['child_resistant_packaging'];
		$patient->call_for_refills = (!empty($data['RefillNotification'])) ? $data['RefillNotification'] : $data['call_for_refills'];
		$patient->preferred_vendor = new stdClass();
		$patient->address = new Model_Entity_PatientAddress();

		if ($this->_useShippingForBilling($data)) {
			$patient->address->address1 = $data['shipping_address1'];
			$patient->address->address2 = $data['shipping_address2'];
			$patient->address->city = $data['shipping_city'];
			$patient->address->province = $data['shipping_region'];
			$patient->address->country = $data['shipping_country'];
			$patient->address->postalcode = $data['shipping_regionCode'];

			if (strlen($data['shipping_phone'] >= 7)) {
				// if Shipping Address is entered, use this as phone (home)
				// and set phone to phone (day)
				$patient->phone_day = $patient->phone;
				$patient->areacode_day = $patient->areacode;
				$patient->phone = $data['shipping_phone'];
				$patient->areacode = $data['shipping_phoneAreaCode'];
			}
		} else {
			$patient->address->address1 = $data['billing_address1'];
			$patient->address->address2 = $data['billing_address2'];
			$patient->address->city = $data['billing_city'];
			$patient->address->province = $data['billing_region'];
			$patient->address->country = $data['billing_country'];
			$patient->address->postalcode = $data['billing_regionCode'];

			if (strlen($data['billing_phone'] >= 7)) {
				$patient->phone_day = $data['billing_phone'];
				$patient->areacode_day = $data['billing_phoneAreaCode'];
			}
		}
		$patient->marketing = new stdClass();

		// call method
		$result = $patientModel->createPatient($patient);

		return $result;
	}

	/**
	 * Save shipping address
	 *
	 * @param mixed $data
	 */
	public function _saveShippingAddress($data)
	{
		$modelPatient = new Model_Patient();
		$address = new stdClass();

		$address->patientID = $data['PatientId'];

		$address->address1 = $data['shipping_address1'];
		$address->address2 = $data['shipping_address2'];
		$address->address3 = '';
		$address->city = $data['shipping_city'];
		$address->province = $data['shipping_region'];
		$address->country = $data['shipping_country'];
		$address->postalcode = $data['shipping_regionCode'];
		$address->areacode = (!empty($data['shipping_phoneAreaCode'])) ? $data['shipping_phoneAreaCode'] : $data['phoneAreaCode'];
		$address->phone = (!empty($data['shipping_phone'])) ? $data['shipping_phone'] : $data['phone'];
		$addShipAddressResponse = $modelPatient->addShippingAddress($address);

		return $addShipAddressResponse;
	}

	/**
	 * Validate form data
	 *
	 * @param mixed $data
	 */
	public function _validate($data)
	{
		$isValid = true;
		$validationFieldsFailed = array();

		// check Email
		if (!Utility_Common::isEmail($data['Username'])) {
			$this->assign('CLASS_USERNAME', 'error');
			$this->assign('ERROR_USERNAME', $this->_getErrorMessage('ERROR_USERNAME'));
			$isValid = false;
			$validationFieldsFailed['Username'] = $this->_getErrorMessage('ERROR_USERNAME', 1);
		}

		// check Confirm Email
		if ((strcasecmp($data['ConfirmUsername'], $data['Username']) !== 0) || !Utility_Common::isEmail($data['ConfirmUsername'])) {
			$this->assign('CLASS_CONFIRM_USERNAME', 'error');
			$this->assign('ERROR_CONFIRM_USERNAME', $this->_getErrorMessage('ERROR_CONFIRM_USERNAME'));
			$isValid = false;
			$validationFieldsFailed['ConfirmUsername'] = $this->_getErrorMessage('ERROR_CONFIRM_USERNAME', 1);
		}

		// check Password
		if (empty($data['Password'])) {
			$this->assign('CLASS_PASSWORD', 'error');
			$this->assign('ERROR_PASSWORD', $this->_getErrorMessage('ERROR_PASSWORD'));
			$isValid = false;
			$validationFieldsFailed['Password'] = $this->_getErrorMessage('ERROR_PASSWORD', 1);
		}

		if (strlen($data['Password']) < 7) {
			$this->assign('CLASS_PASSWORD', 'error');
			$this->assign('ERROR_PASSWORD', $this->_getErrorMessage('ERROR_PASSWORD_LENGTH'));
			$isValid = false;
			$validationFieldsFailed['Password'] = $this->_getErrorMessage('ERROR_PASSWORD_LENGTH', 1);
		}

		// check RePassword
		if ((strcasecmp($data['ConfirmPassword'], $data['Password']) !== 0) || (empty($data['Password']))) {
			$this->assign('CLASS_CONFIRM_PASSWORD', 'error');
			$this->assign('ERROR_CONFIRM_PASSWORD', $this->_getErrorMessage('ERROR_CONFIRM_PASSWORD'));
			$isValid = false;
			$validationFieldsFailed['ConfirmPassword'] = $this->_getErrorMessage('ERROR_CONFIRM_PASSWORD', 1);
		}

		// check firstName
		if (empty($data['firstName'])) {
			$this->assign('CLASS_FIRSTNAME', 'error');
			$this->assign('ERROR_FIRSTNAME', $this->_getErrorMessage('ERROR_FIRSTNAME'));
			$isValid = false;
			$validationFieldsFailed['firstName'] = $this->_getErrorMessage('ERROR_FIRSTNAME', 1);
		}

		// check lastName
		if (empty($data['lastName'])) {
			$this->assign('CLASS_LASTNAME', 'error');
			$this->assign('ERROR_LASTNAME', $this->_getErrorMessage('ERROR_LASTNAME'));
			$isValid = false;
			$validationFieldsFailed['lastName'] = $this->_getErrorMessage('ERROR_LASTNAME', 1);
		}

		// check lastName
		if (empty($data['phone'])) {
			$this->assign('CLASS_PHONE', 'error');
			$this->assign('ERROR_PHONE', $this->_getErrorMessage('ERROR_PHONE'));
			$isValid = false;
			$validationFieldsFailed['phone'] = $this->_getErrorMessage('ERROR_PHONE', 1);
		}

		// check lastName
		if (empty($data['phoneAreaCode'])) {
			$this->assign('CLASS_AREACODEPHONE', 'error');
			$this->assign('ERROR_PHONE', $this->_getErrorMessage('ERROR_PHONE'));
			$isValid = false;
			$validationFieldsFailed['phoneAreaCode'] = $this->_getErrorMessage('ERROR_PHONE', 1);
		}

		// If personal questions are disabled, don't validate/require these fields
		if (get_option('pw_disable_personal_questions', 0) != 1) {
			// check Sex
			if (!isset($data['Sex']) || ((isset($data['Sex'])) && ($data['Sex'] != 'M') && ($data['Sex'] != 'F'))) {
				$this->assign('CLASS_SEX', 'error');
				$this->assign('ERROR_SEX', $this->_getErrorMessage('ERROR_SEX'));
				$isValid = false;
				$validationFieldsFailed['Sex'] = $this->_getErrorMessage('ERROR_SEX', 1);
			}

			// check BirthDate
			if (!is_numeric($data['BirthDate_MONTH']) || !is_numeric($data['BirthDate_DAY']) || !is_numeric($data['BirthDate_YEAR']) || (int) ($data['BirthDate_DAY']) <= 0 || (int) ($data['BirthDate_YEAR']) <= 1890 || (int) ($data['BirthDate_YEAR']) >= date("Y")) {
				$this->assign('CLASS_BIRTHDATE', 'error');
				$this->assign('ERROR_BIRTHDATE', $this->_getErrorMessage('ERROR_BIRTHDATE'));
				$isValid = false;
				$validationFieldsFailed['BirthDate'] = $this->_getErrorMessage('ERROR_BIRTHDATE', 1);
			}

			// check Weight
			if (empty(trim($data['Weight']))) {
				$this->assign('CLASS_WEIGHT', 'error');
				$this->assign('ERROR_WEIGHT', $this->_getErrorMessage('ERROR_WEIGHT'));
				$isValid = false;
				$validationFieldsFailed['Weight'] = $this->_getErrorMessage('ERROR_WEIGHT', 1);
			}

			// check Height
			if (empty($data['HeightFeet']) || ($data['HeightFeet'] < 3)) {
				$this->assign('CLASS_HEIGHT', 'error');
				$this->assign('ERROR_HEIGHT', $this->_getErrorMessage('ERROR_HEIGHT'));
				$isValid = false;
				$validationFieldsFailed['HeightFeet'] = $this->_getErrorMessage('ERROR_HEIGHT', 1);
			}
		}

		// check shipping address
		$validateShipping = $this->_validateShipping($data, $isValid);
		$isValid = $validateShipping['is_valid'];
		$validationFieldsFailed = $validationFieldsFailed + $validateShipping['validation_response'];

		$validateBilling = $this->_validateBilling($data, $isValid);
		$isValid = $validateBilling['is_valid'];
		$validationFieldsFailed = $validationFieldsFailed + $validateBilling['validation_response'];

		return array('is_valid' => $isValid, 'validation_response' => $validationFieldsFailed);
	}

	/**
	 * Validate shipping address
	 *
	 * @param mixed $data
	 */
	public function _validateShipping($data, $isValid)
	{
		$validationFieldsFailed = array();

		// check Shipping_Address
		if (empty($data['shipping_address1'])) {
			$this->assign('CLASS_SHIPPING_ADDRESS1', 'error');
			$this->assign('ERROR_SHIPPING_ADDRESS1', $this->_getErrorMessage('ERROR_SHIPPING_ADDRESS'));
			$validationFieldsFailed['shipping_address1'] = $this->_getErrorMessage('ERROR_SHIPPING_ADDRESS', 1);
			$isValid = false;
		}

		// check Shipping_City
		if (empty($data['shipping_city'])) {
			$this->assign('CLASS_SHIPPING_CITY', 'error');
			$this->assign('ERROR_SHIPPING_CITY', $this->_getErrorMessage('ERROR_SHIPPING_CITY'));
			$validationFieldsFailed['shipping_city'] = $this->_getErrorMessage('ERROR_SHIPPING_CITY', 1);
			$isValid = false;
		}

		// check Shipping_Province
		if (empty($data['shipping_region'])) {
			$this->assign('CLASS_SHIPPING_PROVINCE', 'error');
			$this->assign('ERROR_SHIPPING_PROVINCE', $this->_getErrorMessage('ERROR_SHIPPING_PROVINCE'));
			$validationFieldsFailed['shipping_region'] = $this->_getErrorMessage('ERROR_SHIPPING_PROVINCE', 1);
			$isValid = false;
		}

		// check Shipping_Country
		if (empty($data['shipping_country'])) {
			$this->assign('CLASS_SHIPPING_COUNTRY', 'error');
			$this->assign('ERROR_SHIPPING_COUNTRY', $this->_getErrorMessage('ERROR_SHIPPING_COUNTRY'));
			$validationFieldsFailed['shipping_country'] = $this->_getErrorMessage('ERROR_SHIPPING_COUNTRY', 1);
			$isValid = false;
		}

		// check Shipping_PostalCode
		if (empty($data['shipping_regionCode'])) {
			$this->assign('CLASS_SHIPPING_POSTALCODE', 'error');
			$this->assign('ERROR_SHIPPING_POSTALCODE', $this->_getErrorMessage('ERROR_SHIPPING_POSTALCODE'));
			$validationFieldsFailed['shipping_regionCode'] = $this->_getErrorMessage('ERROR_SHIPPING_POSTALCODE', 1);
			$isValid = false;
		}

		if (!empty($data['shipping_regionCode'])) {
			$countryCode = $data['shipping_country'];
			$isValidRegionCode = $this->validateRegionCode($data['shipping_regionCode'], $countryCode);

			if (!$isValidRegionCode) {
				$this->assign('CLASS_SHIPPING_POSTALCODE', 'error');
				$errorKey = 'ERROR_SHIPPING_POSTALCODE_INVALID_' . $countryCode;
				$this->assign('ERROR_SHIPPING_POSTALCODE', $this->_getErrorMessage($errorKey));
				$validationFieldsFailed['shipping_regionCode'] = $this->_getErrorMessage($errorKey, 1);
				$isValid = false;
			}
		}

		return array('is_valid' => $isValid, 'validation_response' => $validationFieldsFailed);
	}

	public function validateRegionCode($regionCode, $countryCode)
	{
		$isValid = true;
		if (!empty($regionCode)) {
			$regionCode = preg_replace('/\s/', '', $regionCode);

			if ($countryCode === 'CAN') {
				$regex = '/^[a-z]\d[a-z]\d[a-z]\d$/i';
				if (!preg_match($regex, $regionCode)) {
					// $ResultObject->AddMessage('error', $Kind.' Postal code is incorrect for Canada.') unless ( $GoodZip );
					$isValid = false;
				}
			} elseif ($countryCode === 'USA') {
				$regex = '/^\d{5}(-\d{4})?$/';
				if (!preg_match($regex, $regionCode)) {
					// $ResultObject->AddMessage('error', $Kind.' Zip code is incorrect for USA.') unless ( $GoodZip );
					$isValid = false;
				}
			}
		}
		return $isValid;
	}

	public function _validateBilling($data, $isValid)
	{
		$validationFieldsFailed = array();

		if (!$this->_useShippingForBilling($data)) {

			// check Billing_Address
			if (empty($data['billing_address1'])) {
				$this->assign('CLASS_BILLING_ADDRESS1', 'error');
				$this->assign('ERROR_BILLING_ADDRESS1', $this->_getErrorMessage('ERROR_BILLING_ADDRESS'));
				$validationFieldsFailed['billing_address1'] = $this->_getErrorMessage('ERROR_BILLING_ADDRESS', 1);
				$isValid = false;
			}

			// check Billing_City
			if (empty($data['billing_city'])) {
				$this->assign('CLASS_BILLING_CITY', 'error');
				$this->assign('ERROR_BILLING_CITY', $this->_getErrorMessage('ERROR_BILLING_CITY'));
				$validationFieldsFailed['billing_city'] = $this->_getErrorMessage('ERROR_BILLING_CITY', 1);
				$isValid = false;
			}

			// check Billing_Province
			if (empty($data['billing_region'])) {
				$this->assign('CLASS_BILLING_PROVINCE', 'error');
				$this->assign('ERROR_BILLING_PROVINCE', $this->_getErrorMessage('ERROR_BILLING_PROVINCE'));
				$validationFieldsFailed['billing_region'] = $this->_getErrorMessage('ERROR_BILLING_PROVINCE', 1);
				$isValid = false;
			}

			// check Billing_Country
			if (empty($data['billing_country'])) {
				$this->assign('CLASS_BILLING_COUNTRY', 'error');
				$this->assign('ERROR_BILLING_COUNTRY', $this->_getErrorMessage('ERROR_BILLING_COUNTRY'));
				$validationFieldsFailed['billing_country'] = $this->_getErrorMessage('ERROR_BILLING_COUNTRY', 1);
				$isValid = false;
			}

			// check Billing_PostalCode
			if (empty($data['billing_regionCode'])) {
				$this->assign('CLASS_BILLING_POSTALCODE', 'error');
				$this->assign('ERROR_BILLING_POSTALCODE', $this->_getErrorMessage('ERROR_BILLING_POSTALCODE'));
				$validationFieldsFailed['billing_regionCode'] = $this->_getErrorMessage('ERROR_BILLING_POSTALCODE', 1);
				$isValid = false;
			}

			if (!empty($data['billing_regionCode'])) {
				$countryCode = $data['billing_country'];
				$isValidRegionCode = $this->validateRegionCode($data['billing_regionCode'], $countryCode);

				if (!$isValidRegionCode) {
					$this->assign('CLASS_BILLING_POSTALCODE', 'error');
					$errorKey = 'ERROR_BILLING_POSTALCODE_INVALID_' . $countryCode;
					$this->assign('ERROR_BILLING_POSTALCODE', $this->_getErrorMessage($errorKey));
					$validationFieldsFailed['billing_regionCode'] = $this->_getErrorMessage($errorKey, 1);
					$isValid = false;
				}
			}
		}

		return array('is_valid' => $isValid, 'validation_response' => $validationFieldsFailed);
	}

	public function _useShippingForBilling($data)
	{
		$useShipAddr = false;
		if (isset($data['billing_useShippingAddress']) && (strtolower($data['billing_useShippingAddress']) == 'yes')) {
			$useShipAddr = true;
		}
		return $useShipAddr;
	}

	/**
	 * Fill data into register form
	 *
	 * @param mixed $data
	 */
	public function _populateForm($data)
	{
		$this->assign('THEME_URL', THEME_URL);

		// Login Info
		$this->assign('VALUE_USERNAME', $data['Username']);
		$this->assign('VALUE_CONFIRM_USERNAME', $data['ConfirmUsername']);
		$this->assign('VALUE_PASSWORD', '');
		$this->assign('VALUE_CONFIRM_PASSWORD', '');

		// Patient Info
		$this->assign('VALUE_LASTNAME', $data['lastName']);
		$this->assign('VALUE_FIRSTNAME', $data['firstName']);

		$this->assign('VALUE_PHONE', $data['phone']);
		$this->assign('VALUE_AREACODEPHONE', $data['phoneAreaCode']);

		$this->assign('VALUE_AREACODEFAX', $data['AreaCodeFax']);
		$this->assign('VALUE_FAX', $data['Fax']);

		// Shipping Address
		$shipCountry = $data['shipping_country'] ? $data['shipping_country'] : 'USA';
		$shipProvince = $data['shipping_region'] ? $data['shipping_region'] : '';
		$shippingCountry = Utility_Html::htmlSelectCountry('shipping_country', $shipCountry);
		$shippingProvince = Utility_Html::htmlSelectProvince('shipping_region', $shipProvince, $shipCountry);
		$this->assign('SELECT_SHIPPING_PROVINCE', $shippingProvince);
		$this->assign('SELECT_SHIPPING_COUNTRY', $shippingCountry);
		$this->assign('VALUE_SHIPPING_ADDRESS1', $data['shipping_address1']);
		$this->assign('VALUE_SHIPPING_ADDRESS2', $data['shipping_address2']);
		$this->assign('VALUE_SHIPPING_CITY', $data['shipping_city']);
		$this->assign('VALUE_SHIPPING_POSTALCODE', $data['shipping_regionCode']);
		$this->assign('VALUE_SHIPPING_AREACODE', $data['shipping_phoneAreaCode']);
		$this->assign('VALUE_SHIPPING_PHONE', $data['shipping_phone']);

		/* Billing */

		if ($this->_useShippingForBilling($data)) {
			$this->assign('VALUECHECKED', 'checked="checked"');
			$this->assign('VALUECHECKEDCSS', 'checked');
			$this->assign('BILLING_FORM_STYLES', ' style="display: none;"');
			$this->assign('DISABLED', 'disabled="disabled"');
			$this->assign('USESHIPPINGFORBILLING', 'yes');
		} else {
			$this->assign('VALUECHECKED', '');
			$this->assign('VALUECHECKEDCSS', '');
			$this->assign('BILLING_FORM_STYLES', ' style="display: block;"');
			$this->assign('USESHIPPINGFORBILLING', 'no');
		}

		if (isset($data['billing_address1'])) {
			$this->assign('VALUE_BILLING_ADDRESS1', $data['billing_address1']);
		}
		if (isset($data['billing_address2'])) {
			$this->assign('VALUE_BILLING_ADDRESS2', $data['billing_address2']);
		}
		if (isset($data['billing_city'])) {
			$this->assign('VALUE_BILLING_CITY', $data['billing_city']);
		}
		if (isset($data['billing_city'])) {
			$this->assign('VALUE_BILLING_POSTALCODE', $data['billing_regionCode']);
		}
		if (isset($data['billing_phoneAreaCode'])) {
			$this->assign('VALUE_BILLING_AREACODE', $data['billing_phoneAreaCode']);
		}
		if (isset($data['billing_phone'])) {
			$this->assign('VALUE_BILLING_PHONE', $data['billing_phone']);
		}
		
		$billCountry = isset($data['billing_country']) ? $data['billing_country'] : 'USA';
		$billProvince = isset($data['billing_region']) ? $data['billing_region'] : '';
		$billingCountry = Utility_Html::htmlSelectCountry('billing_country', $billCountry);
		$billingProvince = Utility_Html::htmlSelectProvince('billing_region', $billProvince, $billCountry);
		$this->assign('SELECT_BILLING_PROVINCE', $billingProvince);
		$this->assign('SELECT_BILLING_COUNTRY', $billingCountry);

		/* Medical Profile */

		$this->assign('VALUE_BIRTHDATE_DAY', Utility_Common::getNumberValue($data['BirthDate_DAY']));
		$this->assign('VALUE_BIRTHDATE_YEAR', Utility_Common::getNumberValue($data['BirthDate_YEAR']));
		$this->assign('SELECT_BIRTHDATE_MONTH', Utility_Html::htmlSelectMonth('BirthDate_MONTH', $data['BirthDate_MONTH']));

		if (isset($data['Sex'])) {
			$this->assign('SELECT_SEX', Utility_Html::htmlSelectGender('Sex', $data['Sex']));
		} else {
			$this->assign('SELECT_SEX', Utility_Html::htmlSelectGender('Sex'));
		}

		$this->assign('SELECT_HEIGHTFEET', Utility_Html::htmlSelectHeightFeet('HeightFeet', $data['HeightFeet']));
		$this->assign('SELECT_HEIGHTINCHES', Utility_Html::htmlSelectHeightInches('HeightInches', $data['HeightInches']));

		$this->assign('VALUE_WEIGHT', $data['Weight']);

		if (!empty($data['ChildResistantPkg'])) {
			$this->assign('SELECT_CHILDRESISTANTPKG', Utility_Html::htmlSelectDrugPackaging('ChildResistantPkg', $data['ChildResistantPkg']));
		}
		if (!empty($data['RefillNotification'])) {
			$this->assign('SELECT_REFILLNOTIFICATION', Utility_Html::htmlSelectCallforRefills('RefillNotification', $data['RefillNotification']));
		}

		$callForRefills = get_option('pw_checkoutq_call_for_refills', 1);
		$childResistantPackage = get_option('pw_checkoutq_child_resistant_packaging', 1);

		if ($callForRefills || $childResistantPackage) {
			if ($callForRefills) {
				$this->parse('profileQuestions.callForRefills');
			}
			if ($childResistantPackage) {
				$this->parse('profileQuestions.childResistantPackaging');
			}
			$this->parse('profileQuestions');
		}
	}

	public function _registrationErrorMessage($msg, $textResponse = 0)
	{
		if ($textResponse == 0) {
			// return with html wrapper else return just the text
			$msg = '<label class="form-error is-visible">' . $msg . '</label>';
		}
		return $msg;
	}

	/**
	 * Get the error message by key
	 *
	 * @param mixed $key Error key
	 * @return string The content of message
	 */
	public function _getErrorMessage($key, $textResponse = 0)
	{
		$errorMessages = array();
		$errorMessages['ERROR_USERNAME']            = $this->_registrationErrorMessage('Please provide a valid Email address.', $textResponse);
		$errorMessages['ERROR_CONFIRM_USERNAME']    = $this->_registrationErrorMessage('Email address don\'t match.', $textResponse);
		$errorMessages['ERROR_PASSWORD']            = $this->_registrationErrorMessage('Password is a required field.', $textResponse);
		$errorMessages['ERROR_CONFIRM_PASSWORD']    = $this->_registrationErrorMessage('Passwords don\'t match.', $textResponse);
		$errorMessages['ERROR_REPASSWORD']          = $this->_registrationErrorMessage('Re-Type Password.', $textResponse);
		$errorMessages['ERROR_FIRSTNAME']           = $this->_registrationErrorMessage('First name is a required field.', $textResponse);
		$errorMessages['ERROR_LASTNAME']            = $this->_registrationErrorMessage('Last name is a required field.', $textResponse);
		$errorMessages['ERROR_ADDRESS']             = $this->_registrationErrorMessage('Address is a required field.', $textResponse);
		$errorMessages['ERROR_CITY']                = $this->_registrationErrorMessage('City is a required field.', $textResponse);
		$errorMessages['ERROR_PROVINCE']            = $this->_registrationErrorMessage('The State or Province is incorrect for the country chosen.', $textResponse);
		$errorMessages['ERROR_POSTALCODE']          = $this->_registrationErrorMessage('Zip / Postal Code is a required field.', $textResponse);
		$errorMessages['ERROR_AREACODEPHONE']       = $this->_registrationErrorMessage('AreaCode is a required field.', $textResponse);
		$errorMessages['ERROR_PHONE']               = $this->_registrationErrorMessage('Home Phone is a required field.', $textResponse);
		$errorMessages['ERROR_SEX']                 = $this->_registrationErrorMessage('Gender info is required.', $textResponse);
		$errorMessages['ERROR_BIRTHDATE']           = $this->_registrationErrorMessage('Birth Date is a required field.', $textResponse);
		$errorMessages['ERROR_WEIGHT']              = $this->_registrationErrorMessage('Weight is a required field.', $textResponse);
		$errorMessages['ERROR_HEIGHT']              = $this->_registrationErrorMessage('Height is a required field.', $textResponse);

		$errorMessages['ERROR_SHIPPING_ADDRESS']    = $this->_registrationErrorMessage('Please specify shipping address', $textResponse);
		$errorMessages['ERROR_SHIPPING_CITY']       = $this->_registrationErrorMessage('Please specify city for shipping destination', $textResponse);
		$errorMessages['ERROR_SHIPPING_PROVINCE']   = $this->_registrationErrorMessage('Please select a State/Province.', $textResponse);
		$errorMessages['ERROR_SHIPPING_COUNTRY']    = $this->_registrationErrorMessage('Please specify country for shipping destination', $textResponse);
		$errorMessages['ERROR_SHIPPING_POSTALCODE'] = $this->_registrationErrorMessage('Please specify zip/postal code for shipping destination', $textResponse);
		$errorMessages['ERROR_SHIPPING_POSTALCODE_INVALID_CAN'] = $this->_registrationErrorMessage('Shipping Postal Code is invalid for Canada.', $textResponse);
		$errorMessages['ERROR_SHIPPING_POSTALCODE_INVALID_USA'] = $this->_registrationErrorMessage('Shipping Zip Code is invalid for USA.', $textResponse);

		$errorMessages['ERROR_BILLING_ADDRESS']     = $this->_registrationErrorMessage('Please specify billing address', $textResponse);
		$errorMessages['ERROR_BILLING_CITY']        = $this->_registrationErrorMessage('Please specify city for billing destination', $textResponse);
		$errorMessages['ERROR_BILLING_PROVINCE']    = $this->_registrationErrorMessage('Please select a State/Province.', $textResponse);
		$errorMessages['ERROR_BILLING_COUNTRY']     = $this->_registrationErrorMessage('Please specify country for billing destination', $textResponse);
		$errorMessages['ERROR_BILLING_POSTALCODE']  = $this->_registrationErrorMessage('Please specify zip/postal code for billing destination', $textResponse);
		$errorMessages['ERROR_BILLING_POSTALCODE_INVALID_CAN'] = $this->_registrationErrorMessage('Billing Postal Code is invalid for Canada.', $textResponse);
		$errorMessages['ERROR_BILLING_POSTALCODE_INVALID_USA'] = $this->_registrationErrorMessage('Billing Zip Code is invalid for USA.', $textResponse);
		$errorMessages['ERROR_PASSWORD_LENGTH']     = $this->_registrationErrorMessage('Password must be greater than 7 characters.', $textResponse);

		return $errorMessages[$key];
	}

	public static function sendEmailNewPatient($patient)
	{
		// Send welcome email to new customers if enabled which is the default setting.
		if (get_option('pw_email_welcome', 'on') != 'on') {
			// don't send an email
			return;
		}

		$pharmacyName = get_option('pw_name');
		$siteURL = PC_getHomePageURL();
		$siteURL = rtrim($siteURL, "/");
		$siteName = get_bloginfo('name');
		$loginURL = PC_getLoginUrl();
		$username = $patient['Username'];
		$password = $patient['Password'];
		$email = get_option('pw_email');
		$phoneAreaCode = get_option('pw_phone_area');
		$phoneNumber = get_option('pw_phone');
		$faxAreaCode = get_option('pw_fax_area');
		$faxNumber = get_option('pw_fax');
		$currentYear = date('Y');

		$to = $username;
		$subject = "Welcome to $pharmacyName";

		$defaultMessage = 'Thank you for choosing $pharmacyName. To log in when visiting our site just click <a href="$loginURL">Login</a> and then enter your e-mail address and password.<br /><br />If you have any questions please feel free to contact us at $email or by phone at ($phoneAreaCode) $phoneNumber. <br />';

		$message = get_option('pw_newPatientEmail', $defaultMessage);

		$allowedVars = array(
			'/\$pharmacyName/',
			'/\$siteURL/',
			'/\$siteName/',
			'/\$loginURL/',
			'/\$username/',
			'/\$password/',
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
			$username,
			$password,
			$email,
			$phoneAreaCode,
			$phoneNumber,
			$faxAreaCode,
			$faxNumber,
			$currentYear
		);

		$message = preg_replace($allowedVars, $allowedVarValues, $message);
		PC_sendEmail($to, $subject, $message);
	
		return;
	}
}
