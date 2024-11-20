<?php

/**
 * Page_Register
 */
class Page_ProfileEdit extends Utility_PageBase
{
	/**
	 * Make the html output
	 *
	 * @return The content in html format
	 */
	public function _process()
	{
		if (!WebUser::isLoggedIn()) {
			$this->redirect(PC_getLoginUrl());
		}

		$data = array();
		$data['PatientId'] = $this->_getPatientId();

		$action = $this->_getRequest('action', '');
		switch ($action) {
			case 'Update Account':
				$this->updateAccount();
				break;
			case "ADD":
				if ($data['PatientId']) {
					$this->_saveShippingAddress($data);
				}
				break;
			case "CHANGECOUNTRY":
				$this->saveShippingInfo();
				break;
			case "DELETE":
				if ($data['PatientId'] && $data['shipping-address-id']) {
					$this->_deleteShippingAddress($data);
				}
				break;
			case "EDIT":
				if ($data['PatientId']) {
					$this->_editShippingAddress($data);
				}
				if ($data['edit-shipping-address']) {
					// Set shipping address to use edited address
					$this->shipToAddress($data['edit-shipping-address']);
				}
				break;
			case "SAVE":
				$this->saveShippingInfo();
				$isValid = $this->validData($data);
				if ($isValid) {
					$url = PC_getBillingURL();
					$state = strtoupper(trim((string)$_GET["state"]));
					if ($state == "EDIT") {
						$url = PC_getConfirmURL();
					}
					$this->redirect($url);
					break;
				}
				// no break
			default:

				break;
		}

		$this->_displayProfileForm();
		$this->_initProfileForm($data);

		$this->displayAddressHtml();
		$this->_populateAddressForm($data);
	}

	/**
	 * Execute the submit register function
	 *
	 */
	public function updateAccount()
	{
		$this->setTemplate('page_profile_edit');

		$data = $_POST;
		if ($this->_validate($data)) {
			$result = $this->_saveData($data);
			if (Utility_Common::isReplySuccess($result)) {
				$user = new stdClass();
				$user->id = $this->_getPatientId();
				$user->name = $data['Username'];
				WebUser::setLogin($user);
				$this->redirect(PC_getProfileUrl());
			} else {
				$this->_displayResult($result);
			}
		}
		$this->_populateProfileForm($data);
	}

	public function _displayProfileForm()
	{
		$this->setTemplate('page_profile_edit');
		$this->_initProfileForm();
	}

	public function _getPatientInfo()
	{
		$patientModel = new Model_Patient();

		$patient = new Model_Entity_Patient();
		$patient->patientid = $this->_getPatientId();
		$result = $patientModel->getPatientInfo($patient);

		return $result;
	}

	/**
	 * Display result after submitting
	 *
	 * @param mixed $result
	 */
	public function _displayResult($result)
	{
		$html = Utility_Html::displayResult($result);
		$this->assign('RESULT_MESSAGES', $html);
	}

	public function _initProfileForm()
	{
		$result = $this->_getPatientInfo();
		$patient = $result->patient;
		$data = array();
		$data['Username'] = $patient->email;
		$data['ConfirmUsername'] = $patient->email;
		$data['firstName'] = $patient->firstname;
		$data['lastName'] = $patient->lastname;
		$data['DeliveryAddrID'] = $patient->default_delivery_address_id;
		$data['phoneAreaCode'] = $patient->areacode;
		$data['phone'] = $patient->phone;
		$data['AreaCodeFax'] = $patient->areacode_fax;
		$data['Fax'] = $patient->fax;
		$data['HeightFeet'] = $patient->height->feet;
		$data['HeightInches'] = $patient->height->inches;
		$data['Weight'] = $patient->weight->value;
		$data['Sex'] = trim($patient->sex);

		list($birthYear, $birthMonth, $birthDay) = explode('-', $patient->dateofbirth);
		$data['BirthDate_MONTH'] = $birthMonth;
		$data['BirthDate_DAY'] = $birthDay;
		$data['BirthDate_YEAR'] = $birthYear;
		$data['ChildResistantPkg'] = $patient->child_resistant_packaging;
		$data['RefillNotification'] = trim($patient->call_for_refills);

		$this->_populateProfileForm($data);
	}

	public function _getPatientId()
	{
		return WebUser::getUserID();
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
		$patient->patient_id = $this->_getPatientId();

		$patientModel = new Model_Patient();
		$patientExisting = new Model_Entity_Patient();
		$patientExisting->patientid = WebUser::getUserID();
		$result = $patientModel->getPatientInfo($patientExisting);
		$patientInfo = $result->patient;

		$patient->id = $patientInfo->id;
		$patient->email = $data['Username'];
		$patient->firstname = $data['firstName'];
		$patient->lastname = $data['lastName'];
		$patient->dateofbirth = $data['BirthDate_YEAR'] . '-' . $data['BirthDate_MONTH'] . '-' . $data['BirthDate_DAY'];
		$patient->phone = $data['phone'];
		$patient->areacode = $data['phoneAreaCode'];
		$patient->fax = $data['Fax'];
		$patient->areacode_fax = $data['AreaCodeFax'];
		$patient->sex = $data['Sex'];
		$patient->height = new stdClass();
		$patient->height->feet = $data['HeightFeet'];
		$patient->height->inches = $data['HeightInches'];
		$patient->weight = new stdClass();
		$patient->weight->unit = 'lbs';
		$patient->weight->value = $data['Weight'];
		$patient->child_resistant_packaging = $data['ChildResistantPkg'];
		$patient->call_for_refills = $data['RefillNotification'];

		$patient->preferred_vendor = new stdClass();
		$patient->address = new Model_Entity_PatientAddress();
		$patient->address->address1 = $patientInfo->address->address1;
		$patient->address->address2 = $patientInfo->address->address2;
		$patient->address->address3 = $patientInfo->address->address3;
		$patient->address->city = $patientInfo->address->city;
		$patient->address->province = $patientInfo->address->province;
		$patient->address->country = $patientInfo->address->country;
		$patient->address->postalcode = $patientInfo->address->postalcode;
		$patient->default_delivery_address_id = $patientInfo->default_delivery_address_id;
		$patient->marketing = new stdClass();

		// call method
		$result = $patientModel->setPatientInfo($patient);
		return $result;
	}

	/**
	 * Validate form data
	 *
	 * @param mixed $data
	 */
	public function _validate($data)
	{
		$isValid = true;

		// check Email
		if (!Utility_Common::isEmail($data['Username'])) {
			$this->assign('CLASS_USERNAME', 'error');
			$this->assign('ERROR_USERNAME', $this->_getErrorMessage('ERROR_USERNAME'));
			$isValid = false;
		}

		// check Confirm Email
		if ((strcasecmp($data['ConfirmUsername'], $data['Username']) !== 0) || !Utility_Common::isEmail($data['ConfirmUsername'])) {
			$this->assign('CLASS_CONFIRM_USERNAME', 'error');
			$this->assign('ERROR_CONFIRM_USERNAME', $this->_getErrorMessage('ERROR_CONFIRM_USERNAME'));
			$isValid = false;
		}

		// check firstName
		if (empty($data['firstName'])) {
			$this->assign('CLASS_FIRSTNAME', 'error');
			$this->assign('ERROR_FIRSTNAME', $this->_getErrorMessage('ERROR_FIRSTNAME'));
			$isValid = false;
		}

		// check lastName
		if (empty($data['lastName'])) {
			$this->assign('CLASS_LASTNAME', 'error');
			$this->assign('ERROR_LASTNAME', $this->_getErrorMessage('ERROR_LASTNAME'));
			$isValid = false;
		}

		// check lastName
		if (empty($data['phone'])) {
			$this->assign('CLASS_PHONE', 'error');
			$this->assign('ERROR_PHONE', $this->_getErrorMessage('ERROR_PHONE'));
			$isValid = false;
		}

		// check lastName
		if (empty($data['phoneAreaCode'])) {
			$this->assign('CLASS_AREACODEPHONE', 'error');
			$this->assign('ERROR_PHONE', $this->_getErrorMessage('ERROR_PHONE'));
			$isValid = false;
		}

		// check Sex
		if (empty($data['Sex'])) {
			$this->assign('CLASS_SEX', 'error');
			$this->assign('ERROR_SEX', $this->_getErrorMessage('ERROR_SEX'));
			$isValid = false;
		}

		// check BirthDate
		if (!is_numeric($data['BirthDate_MONTH']) || !is_numeric($data['BirthDate_DAY']) || !is_numeric($data['BirthDate_YEAR'])) {
			$this->assign('CLASS_BIRTHDATE', 'error');
			$this->assign('ERROR_BIRTHDATE', $this->_getErrorMessage('ERROR_BIRTHDATE'));
			$isValid = false;
		}

		// check Weight
		if (empty($data['Weight'])) {
			$this->assign('CLASS_WEIGHT', 'error');
			$this->assign('ERROR_WEIGHT', $this->_getErrorMessage('ERROR_WEIGHT'));
			$isValid = false;
		}

		// check Height
		if (empty($data['HeightFeet']) || ($data['HeightFeet'] < 3)) {
			$this->assign('CLASS_HEIGHT', 'error');
			$this->assign('ERROR_HEIGHT', $this->_getErrorMessage('ERROR_HEIGHT'));
			$isValid = false;
		}

		// check Child Resistant Packaging
		if (empty($data['ChildResistantPkg'])) {
			$this->assign('CLASS_CHILDRESISTANTPKG', 'error');
			$this->assign('ERROR_CHILDRESISTANTPKG', $this->_getErrorMessage('ERROR_CHILDRESISTANTPKG'));
			$isValid = false;
		}

		return $isValid;
	}

	/**
	 * Get the error message by key
	 *
	 * @param mixed $key Error key
	 * @return string The content of message
	 */
	public function _getErrorMessage($key)
	{
		static $errorMessages; // cache the message
		if (($errorMessages == null) || count($errorMessages) == 0) {
			$errorMessages = array();
			$errorMessages['ERROR_USERNAME']            = PC_genErrorMessage('Please provide a valid Email address.');
			$errorMessages['ERROR_CONFIRM_USERNAME']    = PC_genErrorMessage('Email address don\'t match.');
			$errorMessages['ERROR_FIRSTNAME']           = PC_genErrorMessage('First name is a required field.');
			$errorMessages['ERROR_LASTNAME']            = PC_genErrorMessage('Last name is a required field.');
			$errorMessages['ERROR_ADDRESS']             = PC_genErrorMessage('Address is a required field.');
			$errorMessages['ERROR_CITY']                = PC_genErrorMessage('City is a required field.');
			$errorMessages['ERROR_PROVINCE']            = PC_genErrorMessage('The State or Province is incorrect for the country chosen.');
			$errorMessages['ERROR_POSTALCODE']          = PC_genErrorMessage('Zip / Postal Code is a required field.');
			$errorMessages['ERROR_AREACODEPHONE']       = PC_genErrorMessage('AreaCode is a required field.');
			$errorMessages['ERROR_PHONE']               = PC_genErrorMessage('Home Phone is a required field.');
			$errorMessages['ERROR_SEX']                 = '<div style="clear: both;">' . PC_genErrorMessage('Gender info is required.') . '</div>';
			$errorMessages['ERROR_BIRTHDATE']           = PC_genErrorMessage('Birth Date is a required field.');
			$errorMessages['ERROR_WEIGHT']              = PC_genErrorMessage('Weight is a required field.');
			$errorMessages['ERROR_HEIGHT']              = '<div style="clear: both;">' . PC_genErrorMessage('Height is a required field.') . '</div>';
		}

		return $errorMessages[$key];
	}

	/**
	 * Fill data into register form
	 *
	 * @param mixed $data
	 */
	public function _populateProfileForm($data)
	{
		$javascript = ' onchange="document.Sign_Up_Edit.submit()" ';
		// patient information part
		$this->assign('VALUE_USERNAME', $data['Username']);
		$this->assign('VALUE_CONFIRM_USERNAME', $data['Username']);

		$this->assign('VALUE_PASSWORD', $data['Password']);
		$this->assign('VALUE_REPASSWORD', $data['RePassword']);
		$this->assign('VALUE_LASTNAME', $data['lastName']);
		$this->assign('VALUE_FIRSTNAME', $data['firstName']);
		$this->assign('VALUE_ADDRESS', $data['Address']);
		$this->assign('VALUE_ADDRESS2', $data['Address2']);
		$this->assign('VALUE_ADDRESS3', $data['Address3']);
		$this->assign('VALUE_CITY', $data['City']);

		$this->assign('SELECT_PROVINCE', Utility_Html::htmlSelectProvince('Province', $data['Province'], $data['Country'], ' id="Province" tabindex="100"'));
		$this->assign('SELECT_COUNTRY', Utility_Html::htmlSelectCountry('Country', $data['Country'], $javascript . ' tabindex="110"'));

		$this->assign('THEME_URL', THEME_URL);
		$addressUrl = PC_getProfileAddressUrl();
		$attribs = 'style="width:300px;" onchange="CheckDeliveryAddress(this, document.Sign_Up_Edit.DeliveryAddrID, \'' . $addressUrl . '\');" tabindex="125"';
		$this->assign('SELECT_SHIPTO', $this->htmlSelectAddress('DeliveryAddrID', $data['DeliveryAddrID'], $attribs));

		$this->assign('VALUE_POSTALCODE', $data['PostalCode']);
		$this->assign('VALUE_AREACODEPHONE', $data['phoneAreaCode']);
		$this->assign('VALUE_PHONE', $data['phone']);
		$this->assign('VALUE_AREACODEDAYPHONE', $data['AreaCodeDayPhone']);
		$this->assign('VALUE_DAYPHONE', $data['DayPhone']);
		$this->assign('VALUE_AREACODEFAX', $data['AreaCodeFax']);
		$this->assign('VALUE_FAX', $data['Fax']);

		// select control
		$this->assign('SELECT_HEIGHTFEET', Utility_Html::htmlSelectHeightFeet('HeightFeet', $data['HeightFeet'], ' tabindex="190"'));
		$this->assign('SELECT_HEIGHTINCHES', Utility_Html::htmlSelectHeightInches('HeightInches', $data['HeightInches'], ' tabindex="200"'));

		$this->assign('VALUE_WEIGHT', $data['Weight']);

		$this->assign('SELECT_SEX', Utility_Html::htmlSelectGender('Sex', $data['Sex'], ' tabindex="240"'));

		// select control
		$this->assign('SELECT_BIRTHDATE_MONTH', Utility_Html::htmlSelectMonth('BirthDate_MONTH', $data['BirthDate_MONTH'], ' tabindex="250"'));

		$this->assign('VALUE_BIRTHDATE_DAY', Utility_Common::getNumberValue($data['BirthDate_DAY']));
		$this->assign('VALUE_BIRTHDATE_YEAR', Utility_Common::getNumberValue($data['BirthDate_YEAR']));

		$this->assign('SELECT_CHILDRESISTANTPKG', Utility_Html::htmlSelectDrugPackaging('ChildResistantPkg', $data['ChildResistantPkg'], ' tabindex="280"'));
		$this->assign('SELECT_REFILLNOTIFICATION', Utility_Html::htmlSelectCallforRefills('RefillNotification', $data['RefillNotification'], ' tabindex="290"'));

		$this->assign('VALUE_URL_CANCEL', PC_getProfileUrl());

		$urlProfileEdit = PC_reCreateUrl('action=edit', PC_getProfileUrl());
		$this->assign('URL_PROFILE_EDIT', $urlProfileEdit);
	}

	public function htmlSelectAddress($name, $active, $attribs)
	{
		$patientModel = new Model_Patient();
		$patient = new stdClass();
		$patient->patientid = $this->_getPatientId();
		$result = $patientModel->getShippingAddresses($patient);
		$arr = array();
		$arr[] = Utility_Html::htmlOption(0, '-- Default address --');
		if (count($result->address) > 0) {
			foreach ($result->address as $address) {
				$addressText = Utility_Html::getAddressText($address);
				$arr[] = Utility_Html::htmlOption($address->id, $addressText);
			}
		}
		$arr[] = Utility_Html::htmlOption(-1, '-- Add new address --');

		return Utility_Html::htmlSelect($arr, $name, $attribs, 'value', 'text', $active);
	}







	/**
	 *
	 * Assign value to html template
	 */
	public function displayAddressHtml()
	{
		$htmlOptions = $this->genAddressHtml(Shipping::getAddressRef());
		$this->assign('ADDRESS_LIST', $htmlOptions);
	}
	/**
	 *
	 * Get all address of Patitent
	 */
	public function getListAddress()
	{
		// generate billing address first
		$userInfor 	= WebUser::getUserInfo();
		$strAddress = $userInfor->firstname . ' ' . $userInfor->lastname . ', ';
		$address 	= $userInfor->address;
		$strAddress .= '<span class="address1">' . $address->address1 . '</span>, ';
		if (strlen($address->address2)) {
			$strAddress .= '<span class="address2">' . $address->address2 . '</span>, ';
		}
		$strAddress .= '<span class="city">' . $address->city . '</span>, ';
		$strAddress .= '<span class="province">' . $address->province . '</span>, ';
		$strAddress .= '<span class="country">' . $address->country . '</span>, ';
		$strAddress .= '<span class="postalcode">' . $address->postalcode . '</span> ';

		if (strlen($userInfor->areacode_day)) {
			$strAddress .= '<span class="areacode">' . $userInfor->areacode_day . '</span> ';
		}
		if (strlen($userInfor->phone_day)) {
			$strAddress .= '<span class="phone">' . $userInfor->phone_day . '</span>';
		}

		$addressID = isset($address->id) ? $address->id : '0';
		$arr[0] = '<span id="address-id-' . $addressID . '"><span class="description">Billing Address</span>: ' . $strAddress . '</span>';

		$patientModel = new Model_Patient();
		$patient = new stdClass();
		$patient->patientid = WebUser::getUserID();
		$lstAddress = $patientModel->getShippingAddresses($patient)->address;

		// Add shipping addresses
		foreach ($lstAddress as $item) {
			$itemContent 	= Utility_Html::getAddressText($item);
			$arr[$item->id] = $itemContent;
		}

		// Order list based on ID so newest show up last (for hilighting etc.)
		ksort($arr);

		return $arr;
	}
	/**
	 *
	 * Make html option to assign to html template
	 * @param int $index
	 */
	public function genAddressHtml($index)
	{
		$lstAddress = $this->getListAddress();
		$strResult = "";
		foreach ($lstAddress as $key => $value) {
			$class = '';
			$checked = '';
			$selectedKey = 0;

			if ($key == $selectedKey) {
				// $class = 'selected';
				$checked = 'checked="checked"';
				$controls = '<button class="edit-address small button">Edit</button>';
			} else {
				$controls = '<button class="edit-address small button">Edit</button> <button class="remove-address small button">Remove</button>';
			}

			$strResult .= '<li id="shipping-row-' . $key . '" class="address-line row ' . $class . '" shipping-value="' . $key . '"><label class="address-line nine columns">' . $value . '</label> <span class="controls three columns">' . $controls . '</span></li>';
		}
		return $strResult;
	}
	/**
	 *
	 * Update Address to Seesion variable
	 */
	public function shipToAddress($addressID)
	{
		if ($addressID === 0) {
			$this->shipToBillingAddress();
		} else {
			Shipping::updateAddress($addressID);
		}
		// Shipping address changes so unflag use shipping address for billing
		Billing::setUsingShippingAddress(0);
	}
	public function shipToBillingAddress()
	{
		$billingInfo = Billing::getInfo();

		// Set up address from Patient Profile
		$patientModel = new Model_Patient();
		$patient = new Model_Entity_Patient();
		$patient->patientid = WebUser::getUserID();
		$result = $patientModel->getPatientInfo($patient);
		$patientInfo = $result->patient;

		$areaCode_day  	= '';
		$phoneDay 		= '';
		$areaCodeFax    = '';
		$fax			= '';
		$description	= '';

		Shipping::setInfor($patientInfo->firstname, $patientInfo->lastname, $patientInfo->address->address1, $patientInfo->address->address2, $patientInfo->address->address3, $patientInfo->address->province, $patientInfo->address->country, $patientInfo->address->postalcode, $patientInfo->areacode, $areaCode_day, $patientInfo->phone, $phoneDay, $patientInfo->address->city, $areaCodeFax, $fax, $description);

		Shipping::setAddressRef(0);
		Billing::setUsingShippingAddress(1);
	}

	/**
	 *
	 * Get Address by AddressID
	 * @param int $id
	 */
	public function getAddessByAddressID($id)
	{
		$addResult = null;
		$patientModel = new Model_Patient();
		$patient = new stdClass();
		$patient->patientid = WebUser::getUserID();
		$lstAddress = $patientModel->getShippingAddresses($patient)->address;

		foreach ($lstAddress as $item) {
			if ($item->id == $id) {
				$addResult = $item;
				break;
			}
		}
		return $addResult;
	}
	public function checkPermission()
	{
		if (!WebUser::isLoggedIn()) {
			$url = PC_getShippingURL();
			$this->redirect($url);
		}
	}
	public function _populateAddressForm($data)
	{

		//GetShippingInfor from session variable
		$shippingInfor 	= Shipping::getInfo();

		$shippingDataProvince 	= $shippingInfor->province;
		$shippingProvince 	= Utility_Html::htmlSelectProvince('shipping_region', $shippingInfor->province, $shippingInfor->country, ' tabindex="8" ');
		$this->assign('SELECT_SHIPPING_PROVINCE', $shippingProvince);

		$shippingCountry 	= Utility_Html::htmlSelectCountry('shipping_country', $shippingInfor->country, ' tabindex="7" ');
		$this->assign('SELECT_SHIPPING_COUNTRY', $shippingCountry);
	}
	// process Save ShippingInfo
	public function saveShippingInfo()
	{
		$firstName 		= (string)$this->_getRequest('firstName');
		$lastName 		= (string)$this->_getRequest('lastName');
		$address1 		= (string)$this->_getRequest('Address1');
		$address2 		= (string)$this->_getRequest('Address2');
		$address3		= '';
		$city 			= (string)$this->_getRequest('City');
		$province 		= (string)$this->_getRequest('shipping_region');
		$country 		= (string)$this->_getRequest('shipping_country');
		$postalCode 	= (string)$this->_getRequest('PostalCode');
		$areaCode  		= (string)$this->_getRequest('phoneAreaCode');
		$phoneHome 		= (string)$this->_getRequest('phone');
		$areaCode_day  	= '';
		$phoneDay 		= '';
		$areaCodeFax    = '';
		$fax			= '';
		$description	= '';

		Shipping::setInfor($firstName, $lastName, $address1, $address2, $address3, $province, $country, $postalCode, $areaCode, $areaCode_day, $phoneHome, $phoneDay, $city, $areaCodeFax, $fax, $description);
	}

	// valid data
	public function validData($data)
	{
		$isValid = true;
		if (empty($data['firstName'])) {
			$msg = PC_genErrorMessage("<small class='error'>firstName is a required field.</small>");
			$this->assign('CLASS_FIRSTNAME', 'error');
			$this->assign('ERROR_FIRSTNAME', $msg);
			$isValid = false;
		}
		if (empty($data['lastName'])) {
			$msg = PC_genErrorMessage("<small class='error'>lastName is a required field.</small>");
			$this->assign('CLASS_LASTNAME', 'error');
			$this->assign('ERROR_LASTNAME', $msg);
			$isValid = false;
		}
		if (empty($data['Address1'])) {
			$msg = PC_genErrorMessage("<small class='error'>Street Address is a required field.</small>");
			$this->assign('CLASS_SHIPPING_ADDRESS1', 'error');
			$this->assign('ERROR_SHIPPING_ADDRESS1', $msg);
			$isValid = false;
		}
		if (empty($data['City'])) {
			$msg = PC_genErrorMessage("<small class='error'>City is a required field.</small>");
			$this->assign('CLASS_SHIPPING_CITY', 'error');
			$this->assign('ERROR_SHIPPING_CITY', $msg);
			$isValid = false;
		}
		if (empty($data['PostalCode'])) {
			$msg = PC_genErrorMessage("<small class='error'>PostalCode is a required field.</small>");
			$this->assign('CLASS_SHIPPING_POSTALCODE', 'error');
			$this->assign('ERROR_SHIPPING_POSTALCODE', $msg);
			$isValid = false;
		}
		if (empty($data['phoneAreaCode'])) {
			$msg = PC_genErrorMessage("<small class='error'>Phone Area Code is a required field.</small>");
			$this->assign('CLASS_AREACODEPHONE', 'error');
			$this->assign('ERROR_AREACODEPHONE', $msg);
			$isValid = false;
		}
		return $isValid;
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

		$address->address1 = $data['Address1'];
		$address->address2 = $data['Address2'];
		$address->address3 = '';
		$address->city = $data['City'];
		$address->province = $data['shipping_region'];
		$address->country = $data['shipping_country'];
		$address->postalcode = $data['PostalCode'];
		$address->areacode = $data['phoneAreaCode'];
		$address->phone = $data['phone'];

		$modelPatient->addShippingAddress($address);
	}
	/**
	 * Save shipping address
	 *
	 * @param mixed $data
	 */
	public function _editShippingAddress($data)
	{
		if ($data['edit-shipping-address'] == 0) {

			/* Updating Billing Address */
			$patientModel = new Model_Patient();

			$patientInfo = WebUser::getUserInfo();
			$patientInfo->patient_id = WebUser::getUserID();

			$address = $patientInfo->address;
			$address->address1 = $data['Address1'];
			$address->address2 = $data['Address2'];
			$address->address3 = '';
			$address->city = $data['City'];
			$address->province = $data['shipping_region'];
			$address->country = $data['shipping_country'];
			$address->postalcode = $data['PostalCode'];

			$patientInfo->areacode = $data['phoneAreaCode'];
			$patientInfo->phone = $data['phone'];

			$patientInfo->marketing = new stdClass();

			/* Update Patient Account in PharmacyWire */
			$patientModel->setPatientInfo($patientInfo);

			/* Update Session */
			$_SESSION['patient_info'] = serialize($patientInfo);
		} else {
			$modelPatient = new Model_Patient();
			$address = new stdClass();
			$address->patientID = $data['PatientId'];
			$address->description = '';
			$address->shippingAddressID = $data['edit-shipping-address'];
			$address->address1 = $data['Address1'];
			$address->address2 = $data['Address2'];
			$address->address3 = '';
			$address->city = $data['City'];
			$address->province = $data['shipping_region'];
			$address->country = $data['shipping_country'];
			$address->postalcode = $data['PostalCode'];
			$address->areacode = $data['phoneAreaCode'];
			$address->phone = $data['phone'];

			$modelPatient->editShippingAddress($address);
		}
	}
	/**
	 * Save shipping address
	 *
	 * @param mixed $data
	 */
	public function _deleteShippingAddress($data)
	{
		$modelPatient = new Model_Patient();
		$address = new stdClass();
		$addressID = (int) $data['shipping-address-id'];
		$address->patientID = $data['PatientId'];
		$address->shippingAddressID = $addressID;

		if (Shipping::getAddressRef() == $addressID) {
			// If current Shipping Address is set to deleted address, set Shipping Address to 0 (Billing)
			$this->shipToBillingAddress();
		}

		$modelPatient->deleteShippingAddress($address);
	}
}
