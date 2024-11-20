<?php

/**
 * Page_Register
 */
class Page_ProfileRecentOrders extends Utility_PageBase
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
		$this->_displayInfo();
	}

	public function getProfile()
	{
		$result = $this->_getPatientInfo();
		$patient = $result->patient;
		return $patient;
	}

	public function _displayInfo()
	{
		// $this->setTemplate('page_profile_info');

		$result = $this->_getPatientInfo();
		// check if reply is failure
		$this->displayErrorRequest($result);

		$patient = $result->patient;

		$fullname = $patient->firstname . ' ' . $patient->lastname;
		$birthdate = $patient->dateofbirth;
		$birthdate = DateTime::createFromFormat('Y-m-d', $birthdate);
		if ($patient->child_resistant_packaging == 'Yes') {
			$package = 'Please supply me with child resistant containers/packaging';
		} else {
			$package = 'No, do not supply me with child resistant containers/packaging';
		}
		$sex = ($patient->sex == 'M') ? 'Male' : 'Female';
		$weight = $patient->weight->value . ' ' . $patient->weight->unit;
		$height = $patient->height->feet . '\' ' . $patient->height->inches . '"';
		$fullAddress = $patient->address->address1 . '<br/>' .
		$patient->address->city . ', ' . $patient->address->province . ' ' . $patient->address->postalcode . '<br/>' .
		$patient->address->country . '<br/><br/>';

		$this->assign('URL_PROFILE_EDIT', PC_getProfileEditUrl());
		$this->assign('URL_PROFILE_CHANGE_PASS', PC_getChangePassUrl());
		$this->assign('URL_REORDER', PC_getReorderUrl());
		$this->assign('VALUE_FULLNAME', $fullname);
		$this->assign('VALUE_EMAIL', $patient->email);
		$this->assign('VALUE_USERNAME', $patient->username);
		$this->assign('VALUE_AREACODEPHONE', $patient->areacode);
		$this->assign('VALUE_PHONE', $patient->phone);
		$areaCodeFax = $patient->areacode_fax ? '(' . $patient->areacode_fax . ')' : '';
		$this->assign('VALUE_AREACODEFAX', $areaCodeFax);
		$this->assign('VALUE_FAX', $patient->fax);
		$this->assign('VALUE_CHILDRESISTANTPKG', $package);
		if ($birthdate) {
			$this->assign('VALUE_BIRTHDATE', $birthdate->format('F d, Y'));
		} else {
			$this->assign('VALUE_BIRTHDATE', 'not entered');
		}
		$this->assign('VALUE_SEX', $sex);
		$this->assign('VALUE_WEIGHT', $weight);
		$this->assign('VALUE_HEIGHT', $height);
		$this->assign('VALUE_FULL_ADDRESS', $fullAddress);

		// display recent orders
		$patientModel = new Model_Patient();
		$reply = $patientModel->getRecentOrders(WebUser::getUserID());
		if (Utility_Common::isReplySuccess($reply)) {
			$rowFormat = '<tr><td class="order-id">%s</td><td class="date">%s</td><td class="status">%s</td><td class="tracking">%s</td></tr>';
			if (count($reply->orders) > 0) {
				$order_html = '';
				foreach ($reply->orders as $order) {
					$trackingid = 'Unavailable';
					if (preg_match('/no\s+tracking/i', $order->trackingid)) {
						1;
					} elseif ($order->trackingid) {
						$trackingid = $order->trackingid;
					}
					$url = '<a class="view-order" href="' . PC_getViewOrderUrl() . '?orderid=' . $order->id . '">' . $order->id . '</a>';
					$order_html .= sprintf($rowFormat, $url, $order->created, $order->status, $trackingid);
				}
				$this->assign('ORDER_HTML', $order_html);
				$this->parse('recentOrder');
			}
		}
	}

	public function _getPatientInfo()
	{
		$patientModel = new Model_Patient();

		$patient = new Model_Entity_Patient();
		$patient->patientid = $this->_getPatientId();
		$result = $patientModel->getPatientInfo($patient);


		return $result;
	}

	public function _getPatientId()
	{
		return WebUser::getUserID();
	}
}
