<?php

/**
 * Page_Register
 */
class Page_ProfileAddress extends Utility_PageBase
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

		$action = $this->_getRequest('action', '');
		switch ($action) {
			case 'Cancel':
				echo '<script type="text/javascript">window.close();</script>';
				return;
				break;
			case 'Save':
				$this->updateSubmit();
				break;
			default:
				$this->_displayForm();
				$submitted = $this->_getRequest('submitted');
				if ($submitted == 1) {
					$data = $_POST;
					$this->_populateForm($data);
				}
				break;
		}
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

	public function _displayForm()
	{
		$this->setTemplate('page_profile_address');

		$this->_initForm();
	}

	public function _initForm()
	{
		$data = array();
		$data['Shipping_Description'] = '';
		$data['Shipping_Address'] = '';
		$data['Shipping_Address2'] = '';
		$data['Shipping_Address3'] = '';
		$data['Shipping_City'] = '';
		$data['Shipping_Province'] = '';
		$data['Shipping_Country'] = 'USA';
		$data['Shipping_PostalCode'] = '';
		$data['Shipping_phoneAreaCode'] = '';
		$data['Shipping_phone'] = '';

		$this->_populateForm($data);
	}

	/**
	 * Fill data into register form
	 *
	 * @param mixed $data
	 */
	public function _populateForm($data)
	{
		// patient information part

		$this->assign('THEME_URL', THEME_URL);

		$this->assign('VALUE_SHIPPING_DESCRIPTION', $data['Shipping_Description']);
		$this->assign('VALUE_SHIPPING_ADDRESS', $data['Shipping_Address']);
		$this->assign('VALUE_SHIPPING_ADDRESS2', $data['Shipping_Address2']);
		$this->assign('VALUE_SHIPPING_ADDRESS3', $data['Shipping_Address3']);
		$this->assign('VALUE_SHIPPING_CITY', $data['Shipping_City']);

		$this->assign('SELECT_SHIPPING_PROVINCE', Utility_Html::htmlSelectProvince('Shipping_Province', $data['Shipping_Province'], $data['Shipping_Country'], 'tabindex="480"'));
		$this->assign('SELECT_SHIPPING_COUNTRY', Utility_Html::htmlSelectCountry('Shipping_Country', $data['Shipping_Country'], ' tabindex="470"'));

		$this->assign('VALUE_SHIPPING_POSTALCODE', $data['Shipping_PostalCode']);
		$this->assign('VALUE_SHIPPING_AREACODEPHONE', $data['Shipping_phoneAreaCode']);
		$this->assign('VALUE_SHIPPING_PHONE', $data['Shipping_phone']);
	}

	/**
	 * Execute the submit add function
	 *
	 */
	public function updateSubmit()
	{
		$this->setTemplate('page_profileaddress_edit');

		$data = $_POST;
		if ($this->_validate($data)) {
			$result = $this->_saveData($data);
			if (Utility_Common::isReplySuccess($result)) {
				$this->_finish($result);
			} else {
				$this->_displayResult($result);
				$this->_populateForm($data);
			}
		} else {
			$this->_populateForm($data);
		}
	}

	public function _finish($result)
	{
		$shippingAddressId = $result->shippingaddress_id;

		$patientModel = new Model_Patient();
		$patient = new stdClass();
		$patient->patientid = WebUser::getUserID();
		$result1 = $patientModel->getShippingAddresses($patient);
		$addressList = '';
		foreach ($result1->address as $address) {
			$addressText = Utility_Html::getAddressText($address);
			$addressLine = "[{$address->id}, '{$addressText}'],";
			$addressList .= $addressLine;
		}

		$shippingAddressHtml = <<<EOD
                AddrInfoArray1 = [
                 [0, '-- Default address --'],
                 {$addressList}
                 [-1, '-- Add new address --']
                 ];
EOD;
		$script = '<script type="text/javascript">' . $shippingAddressHtml . 'closeWin(' . $shippingAddressId . ');</script>';

		$this->assign('SCRIPT', $script);
	}

	/**
	 * Validate shipping address
	 *
	 * @param mixed $data
	 */
	public function _validate($data)
	{
		$isValid = true;

		// check Address
		if (empty($data['Shipping_Description'])) {
			$this->assign('ERROR_SHIPPING_DESCRIPTION', Utility_Messages::getErrorMessage('ERROR_SHIPPING_DESCRIPTION'));
			$isValid = false;
		}

		// check Shipping_Address
		if (empty($data['Shipping_Address'])) {
			$this->assign('ERROR_SHIPPING_ADDRESS', Utility_Messages::getErrorMessage('ERROR_SHIPPING_ADDRESS'));
			$isValid = false;
		}

		// check Shipping_City
		if (empty($data['Shipping_City'])) {
			$this->assign('ERROR_SHIPPING_CITY', Utility_Messages::getErrorMessage('ERROR_SHIPPING_CITY'));
			$isValid = false;
		}

		// check Shipping_Province
		if (empty($data['Shipping_Province'])) {
			$this->assign('ERROR_SHIPPING_PROVINCE', Utility_Messages::getErrorMessage('ERROR_SHIPPING_PROVINCE'));
			$isValid = false;
		}

		// check Shipping_Country
		if (empty($data['Shipping_Country'])) {
			$this->assign('ERROR_SHIPPING_COUNTRY', Utility_Messages::getErrorMessage('ERROR_SHIPPING_COUNTRY'));
			$isValid = false;
		}

		// check Shipping_PostalCode
		if (empty($data['Shipping_PostalCode'])) {
			$this->assign('ERROR_SHIPPING_POSTALCODE', Utility_Messages::getErrorMessage('ERROR_SHIPPING_POSTALCODE'));
			$isValid = false;
		}

		// check Shipping_PostalCode
		if (empty($data['Shipping_phone'])) {
			$this->assign('ERROR_SHIPPING_PHONE', Utility_Messages::getErrorMessage('ERROR_SHIPPING_PHONE'));
			$isValid = false;
		}

		return $isValid;
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

		$patient->patientID = WebUser::getUserID();
		$patient->description = $data['Shipping_Description'];
		$patient->address1 = $data['Shipping_Address'];
		$patient->address2 = $data['Shipping_Address2'];
		$patient->address3 = $data['Shipping_Address3'];
		$patient->city = $data['Shipping_City'];
		$patient->province = $data['Shipping_Province'];
		$patient->country =  $data['Shipping_Country'];
		$patient->postalcode = $data['Shipping_PostalCode'];
		$patient->phone = $data['Shipping_phone'];
		$patient->areacode = $data['Shipping_phoneAreaCode'];

		// call method
		$result = $patientModel->addShippingAddress($patient);
		return $result;
	}
}
