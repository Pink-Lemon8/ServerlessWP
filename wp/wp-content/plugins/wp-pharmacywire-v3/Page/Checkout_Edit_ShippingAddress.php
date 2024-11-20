<?php
class Page_Checkout_Edit_ShippingAddress extends Utility_PageBase
{
	/**
	 * Make the html output
	 *
	 * @return The content in html format
	 */
	public function __construct()
	{
		parent::__construct();
		$this->setTemplate('page_checkout_edit_shippingAddress');
	}
	/**
	 * Override _process function of base class;
	 * @see Utility_PageBase::_process()
	 */
	public function _process()
	{
		$data = $_POST;
		$data['PatientId'] = $this->_getPatientId();

		$this->checkPermission();
		// $url_redirect = PC_getBillingURL();

		$action = strtoupper($this->_getRequest('action'));

		switch ($action) {
			case "ADD":
				if ($data['PatientId']) {
					$this->_saveShippingAddress($data);
				}
				break;
			case "SHIPTO":
				$addressID = (int) $data['shipping-address-id'];
				if (isset($addressID)) {
					$this->shipToAddress($addressID);
					// $this->redirect($url_redirect);
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
		}
		$this->_populateAddressForm($data);
		$this->displayAddressHtml();
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
	public function _getPatientId()
	{
		return WebUser::getUserID();
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

			$shippingEditUrl = PC_getCheckoutEditAddressUrl();
			$cur_page = PC_getCurrentURL();
			// Had to change the following to ignore the http/https portion as front end proxy can allow the backend to be http only even tho
			// the front end is https

			$isCheckoutShippingEdit = stristr(preg_replace('#^https?://#', '', $cur_page), preg_replace('#^https?://#', '', $shippingEditUrl));

			$shipToButton = '';
			if ($isCheckoutShippingEdit && isset($_POST['display-ship-to-button']) && ($_POST['display-ship-to-button'] != 'false')) {
				$shipToButton = '<span class="controls large-3 small-6 cell"><button class="ship-to-address button">Use This Address</button></span>';
				$this->assign('DISPLAY_SHIP_TO_BUTTON', 'true');
			} else {
				$this->assign('DISPLAY_SHIP_TO_BUTTON', 'false');
			}

			$strResult .= '<li id="shipping-row-' . $key . '" class="address-line  pw-transparent grid-x grid-margin-x align-middle ' . $class . '" shipping-value="' . $key . '"><label class="address-line auto cell">' . $value . '</label>' . $shipToButton . '<span class="controls large-4 small-6 cell">' . $controls . '</span></li>';
		}
		return $strResult;
	}

	public function genAddressJson($index)
	{
		// generate billing address first
		$userInfor 	= WebUser::getUserInfo();

		$address 	= $userInfor->address;

		$addressObj = new StdClass;
		$userInfor->address->id = "0";
		$userInfor->address->description = 'Billing Address';
		$userInfor->address->areacode = $userInfor->areacode_day;
		$userInfor->address->phone = $userInfor->phone_day;
		$addressObj->BillingAddress = array($address);

		$patientModel = new Model_Patient();
		$patient = new Model_Entity_Patient();
		$patient->patientid = WebUser::getUserID();
		$lstAddress = $patientModel->getShippingAddresses($patient)->address;

		if (!empty($lstAddress)) {
			foreach ($lstAddress as $item) {
				$arr[$item->id] = json_decode($item->toJson());
			}
			// Order list based on ID so newest show up last (for hilighting etc.)
			if ($arr) {
				ksort($arr);
			}
			$addressObj->ShippingAddress = $arr;
		}

		return $addressObj;
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
			// Shipping address changes so unflag use shipping address for billing
			Billing::setUsingShippingAddress(0);
		}
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

		$this->assign('EDIT_ADDRESS_URL', PC_getCheckoutEditAddressUrl());
	}
	// process Save ShippingInfo
	public function saveShippingInfo()
	{
		$firstName 		= (string)$this->_getRequest('firstName');
		$lastName 		= (string)$this->_getRequest('lastName');
		$address1 		= (string)$this->_getRequest('Address1');
		$address2 		= (string)$this->_getRequest('Address2');
		$address3       = '';
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
		$userInfor 	= WebUser::getUserInfo();

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
		$address->areacode = $data['phoneAreaCode'] === '' ? $userInfor->areacode : $data['phoneAreaCode'];
		$address->phone = $data['phone'] === '' ? $userInfor->phone : $data['phone'];

		$modelPatient->addShippingAddress($address);
	}
	/**
	 * Save shipping address
	 *
	 * @param mixed $data
	 */
	public function _editShippingAddress($data)
	{
		$patientInfo = WebUser::getUserInfo();

		if ($data['edit-shipping-address'] == 0) {

			/* Updating Billing Address */
			$patientModel = new Model_Patient();

			$patientInfo->patient_id = WebUser::getUserID();

			$address = $patientInfo->address;
			$address->address1 = $data['Address1'];
			$address->address2 = $data['Address2'];
			$address->address3 = '';
			$address->city = $data['City'];
			$address->province = $data['shipping_region'];
			$address->country = $data['shipping_country'];
			$address->postalcode = $data['PostalCode'];

			$billingAreaCode = $patientInfo->areacode_day === '' ? $patientInfo->areacode : $patientInfo->areacode_day;
			$billingPhone = $patientInfo->phone_day === '' ? $patientInfo->phone : $patientInfo->phone_day;

			$patientInfo->areacode_day = $data['phoneAreaCode'] === '' ? $patientInfo->areacode_day : $data['phoneAreaCode'];
			$patientInfo->phone_day = $data['phone'] === '' ? $patientInfo->phone_day : $data['phone'];

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
			$address->areacode = $data['phoneAreaCode'] === '' ? $patientInfo->areacode : $data['phoneAreaCode'];
			$address->phone = $data['phone'] === '' ? $patientInfo->phone_day : $data['phone'];

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

		$reply = new Model_Entity_Reply();

		$reply = $modelPatient->deleteShippingAddress($address);

		if ($reply->status === 'failure') {
			// Add failure handling
		}
	}
}
