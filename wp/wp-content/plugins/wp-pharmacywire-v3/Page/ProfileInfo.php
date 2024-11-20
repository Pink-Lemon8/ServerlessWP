<?php

/**
 * Page_Register
 */
class Page_ProfileInfo extends Utility_PageBase
{
	/**
	 * Make the html output
	 *
	 * @return The content in html format
	 */
	public function _process()
	{
		$this->setTemplate('page_profile_info');

		if (!WebUser::isLoggedIn()) {
			$this->redirect(PC_getLoginUrl());
		}

		$action = $this->_getRequest('action', '');
		switch ($action) {
			case 'update-account':
				$this->updateAccount();
				break;
			default:
				break;
		}

		$this->_populatePage();
	}

	public function _populatePage()
	{
		// Setup initial tabs data
		$this->displayRecentOrders();
		$this->displayProfileInfo();
		$this->displayDocumentUpload();
	}

	public function displayRecentOrders()
	{
		$this->recentOrdersHtml();
	}

	public function displayProfileInfo()
	{
		//$profileDetails = new Page_ProfileDetails();
		$this->profileInfoHtml();
		//$this->_initProfileForm();
		$this->displayEditProfile();
		$this->displayAddressManager();
	}

	public function displayDocumentUpload()
	{
		$documentUpload = do_shortcode('[PharmacyWire_DocumentUpload]');
		$this->assign('PHARMACYWIRE_DOCUMENT_UPLOAD', $documentUpload);
	}

	public function displayEditProfile()
	{
		$patientModel = new Model_Patient();
		$patientExisting = new Model_Entity_Patient();

		$patientExisting->patientid = WebUser::getUserID();
		$result = $patientModel->getPatientInfo($patientExisting);
		$patientInfo = $result->patient;

		$this->assign('VALUE_EDIT_FIRSTNAME', $patientInfo->firstname);
		$this->assign('VALUE_EDIT_LASTNAME', $patientInfo->lastname);
		$this->assign('VALUE_EDIT_AREACODEPHONE', $patientInfo->areacode);
		$this->assign('VALUE_EDIT_PHONE', $patientInfo->phone);
		$phoneDay = $patientInfo->phone_day;
		if (!empty($phoneDay)) {
			$this->assign('VALUE_EDIT_AREACODEPHONE_DAY', $patientInfo->areacode_day);
			$this->assign('VALUE_EDIT_PHONE_DAY', $phoneDay);
			$this->parse('phoneDay');
		}
		$this->assign('VALUE_EDIT_AREACODEFAX', $patientInfo->areacode_fax);
		$this->assign('VALUE_EDIT_FAX', $patientInfo->fax);
		$this->assign('VALUE_EDIT_USERNAME', $patientInfo->email);
		$this->assign('VALUE_EDIT_CONFIRM_USERNAME', $patientInfo->email);

		// If personal questions are disabled, don't validate/require these fields
		if (get_option('pw_disable_personal_questions', 0) != 1) {
			list($birthYear, $birthMonth, $birthDay) = explode("-", $patientInfo->dateofbirth);
			$this->assign('SELECT_EDIT_BIRTHDATE_MONTH', Utility_Html::htmlSelectMonth('BirthDate_MONTH', $birthMonth));
			$this->assign('VALUE_EDIT_BIRTHDATE_DAY', Utility_Common::getNumberValue($birthDay));
			$this->assign('VALUE_EDIT_BIRTHDATE_YEAR', Utility_Common::getNumberValue($birthYear));
			$this->assign('SELECT_EDIT_HEIGHTFEET', Utility_Html::htmlSelectHeightFeet('HeightFeet', $patientInfo->height->feet));
			$this->assign('SELECT_EDIT_HEIGHTINCHES', Utility_Html::htmlSelectHeightInches('HeightInches', $patientInfo->height->inches));
			$this->assign('VALUE_EDIT_WEIGHT', $patientInfo->weight->value);
			$this->assign('SELECT_EDIT_SEX', Utility_Html::htmlSelectGender('Sex', $patientInfo->sex));
		}
		
		$this->assign('SELECT_EDIT_CHILDRESISTANTPKG', Utility_Html::htmlSelectDrugPackaging('ChildResistantPkg', $patientInfo->child_resistant_packaging));
		$this->assign('SELECT_EDIT_REFILLNOTIFICATION', Utility_Html::htmlSelectCallforRefills('RefillNotification', $patientInfo->call_for_refills));

		$this->assign('PROFILE_UPDATE_URL', PC_getProfileEditUrl());

		$callForRefills = get_option('pw_checkoutq_call_for_refills', 1);
		$childResistantPackage = get_option('pw_checkoutq_child_resistant_packaging', 1);

		if ($callForRefills) {
			$this->parse('callForRefills');
			$this->parse('callForRefillsInput');
		}
		if ($childResistantPackage) {
			$this->parse('childResistantPackaging');
			$this->parse('childResistantPackagingInput');
		}
	}

	public function displayAddressManager()
	{
		$displayShipToButton = false;

		$shippingInfor 	= Shipping::getInfo();

		$shippingProvince 	= Utility_Html::htmlSelectProvince('shipping_region', $shippingInfor->province, $shippingInfor->country);
		$this->assign('SELECT_SHIPPING_PROVINCE', $shippingProvince);

		$shippingCountry 	= Utility_Html::htmlSelectCountry('shipping_country', $shippingInfor->country);
		$this->assign('SELECT_SHIPPING_COUNTRY', $shippingCountry);

		$this->assign('EDIT_ADDRESS_URL', PC_getCheckoutEditAddressUrl());

		$this->assign('EDIT_DISPLAY_SHIP_TO_BUTTON', 'false');

		$this->displayAddressHtml($displayShipToButton);
	}

	public function _getPatientId()
	{
		return WebUser::getUserID();
	}

	public function _getPatientInfo()
	{
		$patientModel = new Model_Patient();

		$patient = new Model_Entity_Patient();
		$patient->patientid = $this->_getPatientId();
		$result = $patientModel->getPatientInfo($patient);

		return $result;
	}

	public function updateAccount()
	{
		$data = $_POST;
		$data = GUMP::xss_clean($data);

		if ($this->_validate($data)) {
			$result = $this->_saveData($data);

			if (Utility_Common::isReplySuccess($result)) {
			} else {
			}
		} else {
			$this->displayProfileInfo();
		}
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
		$patient->firstname = $patientInfo->firstname;
		$patient->lastname = $patientInfo->lastname;
		$patient->phone = $data['phone'];
		$patient->areacode = $data['phoneAreaCode'];
		$patient->phone_day = $data['phoneDay'];
		$patient->areacode_day = $data['phoneAreaCodeDay'];
		$patient->fax = isset($data['Fax']) ? $data['Fax'] : '';
		$patient->areacode_fax = isset($data['AreaCodeFax']) ? $data['AreaCodeFax'] : '';
		
		// If personal questions are disabled, don't validate/require these fields
		if (get_option('pw_disable_personal_questions', 0) != 1) {
			$patient->sex = $data['Sex'];
			$patient->dateofbirth = $data['BirthDate_YEAR'] . '-' . $data['BirthDate_MONTH'] . '-' . $data['BirthDate_DAY'];
			$patient->height = new stdClass();
			$patient->height->feet = $data['HeightFeet'];
			$patient->height->inches = $data['HeightInches'];
			$patient->weight = new stdClass();
			$patient->weight->unit = 'lbs';
			$patient->weight->value = $data['Weight'];
		}	

		if (!empty($data['ChildResistantPkg']) && get_option('pw_checkoutq_child_resistant_packaging', 1)) {
			$patient->child_resistant_packaging = $data['ChildResistantPkg'];
		}
		if (!empty($data['RefillNotification']) && get_option('pw_checkoutq_call_for_refills', 1)) {
			$patient->call_for_refills = $data['RefillNotification'];
		}

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

		if (!Utility_Common::isEmail($data['Username'])) {
			$this->assign('CLASS_USERNAME', 'error');
			$this->assign('ERROR_USERNAME', $this->_getErrorMessage('ERROR_USERNAME'));
			$isValid = false;
		}

		if ((strcasecmp($data['ConfirmUsername'], $data['Username']) !== 0) || !Utility_Common::isEmail($data['ConfirmUsername'])) {
			$this->assign('CLASS_CONFIRM_USERNAME', 'error');
			$this->assign('ERROR_CONFIRM_USERNAME', $this->_getErrorMessage('ERROR_CONFIRM_USERNAME'));
			$isValid = false;
		}

		if (empty($data['phone'])) {
			$this->assign('CLASS_PHONE', 'error');
			$this->assign('ERROR_PHONE', $this->_getErrorMessage('ERROR_PHONE'));
			$isValid = false;
		}

		if (empty($data['phoneAreaCode'])) {
			$this->assign('CLASS_AREACODEPHONE', 'error');
			$this->assign('ERROR_PHONE', $this->_getErrorMessage('ERROR_PHONE'));
			$isValid = false;
		}

		// If personal questions are disabled, don't validate/require these fields
		if (get_option('pw_disable_personal_questions', 0) != 1) {
			if (($data['Sex'] != 'M') && ($data['Sex'] != 'F')) {
				$this->assign('CLASS_SEX', 'error');
				$this->assign('ERROR_SEX', $this->_getErrorMessage('ERROR_SEX'));
				$isValid = false;
			}

			if (!is_numeric($data['BirthDate_MONTH']) || !is_numeric($data['BirthDate_DAY']) || !is_numeric($data['BirthDate_YEAR'])) {
				$this->assign('CLASS_BIRTHDATE', 'error');
				$this->assign('ERROR_BIRTHDATE', $this->_getErrorMessage('ERROR_BIRTHDATE'));
				$isValid = false;
			}

			if (empty($data['Weight'])) {
				$this->assign('CLASS_WEIGHT', 'error');
				$this->assign('ERROR_WEIGHT', $this->_getErrorMessage('ERROR_WEIGHT'));
				$isValid = false;
			}

			if (empty($data['HeightFeet']) || ($data['HeightFeet'] < 3)) {
				$this->assign('CLASS_HEIGHT', 'error');
				$this->assign('ERROR_HEIGHT', $this->_getErrorMessage('ERROR_HEIGHT'));
				$isValid = false;
			}
		}

		if (!empty($data['ChildResistantPkg']) && (($data['ChildResistantPkg'] != 'Yes') && ($data['ChildResistantPkg'] != 'No'))) {
			$this->assign('CLASS_CHILDRESISTANTPKG', 'error');
			$this->assign('ERROR_CHILDRESISTANTPKG', $this->_getErrorMessage('ERROR_CHILDRESISTANTPKG'));
			$isValid = false;
		}

		if (!empty($data['RefillNotification']) && (($data['RefillNotification'] != 'True') && ($data['RefillNotification'] != 'False'))) {
			$this->assign('CLASS_REFILLNOTIFICATION', 'error');
			$this->assign('ERROR_REFILLNOTIFICATION', $this->_getErrorMessage('ERROR_REFILLNOTIFICATION'));
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


	public function recentOrdersHtml()
	{
		$this->assign('REORDER_URL', PC_getReOrderUrl());
		// display recent orders
		$patientModel = new Model_Patient();
		$reply = $patientModel->getRecentOrders(WebUser::getUserID());

		if (Utility_Common::isReplySuccess($reply)) {
			$rowFormat = '<tbody id="recent-order-%s" %s><tr class="recent-order-summary" order-id="%s"><td class="order-id">%s</td><td class="date">%s</td><td class="order-status">%s %s</td><td class="order-tracking">%s</td></tr><tr id="order-details-view-%s" class="order-details-view"><td colspan="4"><div class="order-container" style="display: none;"></div></td></tr></tbody>';
			$order_html = '';
			$pendingTranscriptionBlock = '';
			if (count($reply->orders) > 0) {
				foreach ($reply->orders as $order) {
					$trackingid = 'Unavailable';
					if (preg_match('/no\s+tracking/i', $order->trackingid)) {
						1;
					} elseif ($order->trackingid) {
						$trackingid = $order->trackingid;
					}
					$hasPendingTranscription = $order->hasPendingTranscription ?? 'false';
					$dataPendingRx = '';
					if ($hasPendingTranscription === 'true') {
						$pendingTranscriptionBlock = '<span class="has-pending-transcription" data-tooltip title="Prescription(s) have been uploaded and are waiting to be processed by our pharmacists." data-template-classes="pw-tooltip"><i class="fas fa-file-prescription alert"></i></span>';
						$dataPendingRx = 'data-has-pending-rx="true"';
					}

					$url = '<a class="order-id view-order  view-recent-order" href="' . PC_getViewOrderUrl() . '?orderid=' . $order->id . '" order-id="' . $order->id . '">' . $order->id . '</a>';
					$order_html .= sprintf($rowFormat, $order->id, $dataPendingRx, $order->id, $url, $order->created, $order->visualstatus, $pendingTranscriptionBlock, $trackingid, $order->id);
				}
			}
			$this->assign('PENDING_TRANSCRIPTION', $pendingTranscriptionBlock);
			$this->assign('ORDER_HTML', $order_html);
			$this->parse('recentOrder');
		}
		return;
	}

	public function profileInfoHtml()
	{
		$result = $this->_getPatientInfo();
		// check if reply is failure
		$this->displayErrorRequest($result);

		$patient = $result->patient;

		$fullname = $patient->firstname . ' ' . $patient->lastname;
		$birthdate = $patient->dateofbirth;
		$birthdate = DateTime::createFromFormat('Y-m-d', $birthdate);
		$sex = '';
		if (!($patient->sex === '')) {
			$sex = ($patient->sex === 'M') ? 'Male' : 'Female';
		}
		$weight = filter_var($patient->weight->value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
		$height = '';
		if (!($patient->height->feet === '')) {
			$height = $patient->height->feet . '\'' . $patient->height->inches . '"';
		}
		$fullAddress = $patient->address->address1 . '<br/>' .
		$patient->address->city . ', ' . $patient->address->province . ' ' . $patient->address->postalcode . '<br/>' . $patient->address->country;
		$referralCode = $patient->referral->referral_code ?? '';
		$referralBalance = $patient->referral->referral_balance ?? '';

		$this->assign('URL_PROFILE_EDIT', PC_getProfileEditUrl());
		$this->assign('URL_PROFILE_CHANGE_PASS', PC_getChangePassUrl());
		$this->assign('URL_REORDER', PC_getReorderUrl());
		$this->assign('VALUE_FULLNAME', $fullname);
		$this->assign('VALUE_EMAIL', $patient->email);
		$this->assign('VALUE_USERNAME', $patient->username);
		$this->assign('VALUE_AREACODEPHONE', $patient->areacode);
		$this->assign('VALUE_PHONE', $patient->phone);
		$this->assign('VALUE_AREACODEPHONE_DAY', $patient->areacode_day);
		$this->assign('VALUE_PHONE_DAY', $patient->phone_day);
		$areaCodeFax = $patient->areacode_fax ? '(' . $patient->areacode_fax . ')' : '';
		$this->assign('VALUE_AREACODEFAX', $areaCodeFax);
		if ($patient->fax === '') {
			$this->assign('VALUE_FAX', 'n/a');
		} else {
			$this->assign('VALUE_FAX', $patient->fax);
		}

		$this->assign('VALUE_CHILDRESISTANTPKG', $patient->child_resistant_packaging);
		if ($birthdate) {
			$this->assign('VALUE_BIRTHDATE', $birthdate->format('F d, Y'));
		} else {
			$this->assign('VALUE_BIRTHDATE', 'not entered');
		}
		$callForRefills = strtolower($patient->call_for_refills) == 'true' ? 'Yes' : 'No';

		$this->assign('VALUE_CALLFORREFILLS', $callForRefills);
		$this->assign('VALUE_SEX', $sex);
		$this->assign('VALUE_WEIGHT', $weight);
		$this->assign('VALUE_WEIGHT_UNIT', $patient->weight->unit);
		$this->assign('VALUE_HEIGHT', $height);
		$this->assign('VALUE_FULL_ADDRESS', $fullAddress);
		$this->assign('REFERRAL_CODE', $referralCode);
		$this->assign('REFERRAL_BALANCE', $referralBalance);
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

		$this->assign('SELECT_PROVINCE', Utility_Html::htmlSelectProvince('Province', $data['Province'], $data['Country'], ' id="Province"'));
		$this->assign('SELECT_COUNTRY', Utility_Html::htmlSelectCountry('Country', $data['Country'], $javascript));

		$this->assign('THEME_URL', THEME_URL);
		$addressUrl = PC_getProfileAddressUrl();
		$attribs = 'style="width:300px;" onchange="CheckDeliveryAddress(this, document.Sign_Up_Edit.DeliveryAddrID, \'' . $addressUrl . '\');"';
		$this->assign('SELECT_SHIPTO', $this->htmlSelectAddress('DeliveryAddrID', $data['DeliveryAddrID'], $attribs));

		$this->assign('VALUE_POSTALCODE', $data['PostalCode']);
		$this->assign('VALUE_AREACODEPHONE', $data['phoneAreaCode']);
		$this->assign('VALUE_PHONE', $data['phone']);
		$this->assign('VALUE_AREACODEDAYPHONE', $data['AreaCodeDayPhone']);
		$this->assign('VALUE_DAYPHONE', $data['DayPhone']);
		$this->assign('VALUE_AREACODEFAX', $data['AreaCodeFax']);
		$this->assign('VALUE_FAX', $data['Fax']);

		// select control
		$this->assign('SELECT_HEIGHTFEET', Utility_Html::htmlSelectHeightFeet('HeightFeet', $data['HeightFeet']));
		$this->assign('SELECT_HEIGHTINCHES', Utility_Html::htmlSelectHeightInches('HeightInches', $data['HeightInches']));

		$this->assign('VALUE_WEIGHT', filter_var($data['Weight'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION));

		$this->assign('SELECT_SEX', Utility_Html::htmlSelectGender('Sex', $data['Sex']));

		// select control
		$this->assign('SELECT_BIRTHDATE_MONTH', Utility_Html::htmlSelectMonth('BirthDate_MONTH', $data['BirthDate_MONTH']));

		$this->assign('VALUE_BIRTHDATE_DAY', Utility_Common::getNumberValue($data['BirthDate_DAY']));
		$this->assign('VALUE_BIRTHDATE_YEAR', Utility_Common::getNumberValue($data['BirthDate_YEAR']));

		$this->assign('SELECT_CHILDRESISTANTPKG', Utility_Html::htmlSelectDrugPackaging('ChildResistantPkg', $data['ChildResistantPkg']));
		$this->assign('SELECT_REFILLNOTIFICATION', Utility_Html::htmlSelectCallforRefills('RefillNotification', $data['RefillNotification']));

		$this->assign('VALUE_URL_CANCEL', PC_getProfileUrl());

		$urlProfileEdit = PC_reCreateUrl('action=edit', PC_getProfileUrl());
		$this->assign('URL_PROFILE_EDIT', $urlProfileEdit);
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
		$data['Weight'] = filter_var($patient->weight->value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
		$data['Sex'] = trim($patient->sex);

		list($birthYear, $birthMonth, $birthDay) = explode('-', $patient->dateofbirth);
		$data['BirthDate_MONTH'] = $birthMonth;
		$data['BirthDate_DAY'] = $birthDay;
		$data['BirthDate_YEAR'] = $birthYear;
		$data['ChildResistantPkg'] = $patient->child_resistant_packaging;
		$data['RefillNotification'] = trim($patient->call_for_refills);

		$this->_populateProfileForm($data);
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

	public function displayAddressHtml()
	{
		$htmlOptions = $this->genAddressHtml(Shipping::getAddressRef());
		$this->assign('ADDRESS_LIST', $htmlOptions);
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

			$shippingEditUrl = PC_getCheckoutEditAddressUrl();
			$cur_page = PC_getCurrentURL();
			// Had to change the following to ignore the http/https portion as front end proxy can allow the backend to be http only even tho
			// the front end is https

			$isCheckoutShippingEdit = stristr(preg_replace('#^https?://#', '', $cur_page), preg_replace('#^https?://#', '', $shippingEditUrl));

			$shipToButton = '';
			if ($isCheckoutShippingEdit && ($_POST['display-ship-to-button'] != 'false')) {
				$shipToButton = '<span class="controls large-3 small-6 cell"><button class="ship-to-address button">Use This Address</button></span>';
				$this->assign('DISPLAY_SHIP_TO_BUTTON', 'true');
			} else {
				$this->assign('DISPLAY_SHIP_TO_BUTTON', 'false');
			}

			$strResult .= '<li id="shipping-row-' . $key . '" class="address-line  pw-transparent grid-x grid-margin-x align-middle ' . $class . '" shipping-value="' . $key . '"><label class="address-line auto cell">' . $value . '</label>' . $shipToButton . '<span class="controls large-4 small-6 cell">' . $controls . '</span></li>';
		}
		return $strResult;
	}

	/**
	 *
	 * Get all address of Patitent
	 */
	public function getListAddress()
	{
		// generate billing address first
		$userInfor 	= WebUser::getUserInfo();
		$address 	= $userInfor->address;

		$strAddress = '<span class="address1">' . $address->address1 . '</span>, ';
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
		if (is_array($lstAddress)) {
			foreach ($lstAddress as $item) {
				$itemContent 	= Utility_Html::getAddressText($item);
				$arr[$item->id] = $itemContent;
			}
		}

		// Order list based on ID so newest show up last (for hilighting etc.)
		ksort($arr);

		return $arr;
	}
}
