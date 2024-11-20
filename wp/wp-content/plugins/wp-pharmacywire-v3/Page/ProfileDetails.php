<?php

/**
 * Page_Register
 */
class Page_ProfileDetails extends Utility_PageBase
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
		$this->profileInfoHtml();

		$this->recentOrdersHtml();
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
		$weight = '';
		if (!($patient->weight->value === '')) {
			$weight = $patient->weight->value . ' ' . $patient->weight->unit;
		}
		$height = '';
		if (!($patient->height->feet === '')) {
			$height = $patient->height->feet . '\'' . $patient->height->inches . '"';
		}
		$fullAddress = $patient->address->address1 . '<br/>' .
		$patient->address->city . ', ' . $patient->address->province . ' ' . $patient->address->postalcode . '<br/>' . $patient->address->country;

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
		$this->assign('VALUE_HEIGHT', $height);
		$this->assign('VALUE_FULL_ADDRESS', $fullAddress);
	}

	public function recentOrdersHtml()
	{
		// $this->setTemplate("page_profile_info");
		// display recent orders
		$patientModel = new Model_Patient();
		$reply = $patientModel->getRecentOrders(WebUser::getUserID());

		if (Utility_Common::isReplySuccess($reply)) {
			$rowFormat = '<tbody id="recent-order-%s"><tr class="recent-order-summary" order-id="%s"><td class="order-id">%s</td><td class="date">%s</td><td class="status">%s</td><td class="tracking">%s</td></tr><tr id="order-details-view-%s" class="order-details-view"><td colspan="4"><div class="order-container" style="display: none;"></div></td></tr></tbody>';
			if (count($reply->orders) > 0) {
				$order_html = '';
				foreach ($reply->orders as $order) {
					$trackingid = 'Unavailable';
					if (preg_match('/no\s+tracking/i', $order->trackingid)) {
						1;
					} elseif ($order->trackingid) {
						$trackingid = $order->trackingid;
					}
					$url = '<a class="order-id view-order  view-recent-order" href="' . PC_getViewOrderUrl() . '?orderid=' . $order->id . '" order-id="' . $order->id . '">' . $order->id . '</a>';
					$order_html .= sprintf($rowFormat, $order->id, $order->id, $url, $order->created, $order->visualstatus, $trackingid, $order->id);
				}
			}

			$this->assign('ORDER_HTML', $order_html);
			$this->parse('recentOrder');
		}
		return;
	}

	public function _getPatientInfo()
	{
		$patientModel = new Model_Patient();

		$patient = new Model_Entity_Patient();
		$patient->patientid = WebUser::getUserID();
		$result = $patientModel->getPatientInfo($patient);

		return $result;
	}
}
