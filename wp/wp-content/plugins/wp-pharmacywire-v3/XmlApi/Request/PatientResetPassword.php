<?php

/**
 * XmlApi_Request_PatientResetPassword
 */
class XmlApi_Request_PatientResetPassword extends Utility_XmlApiTransaction
{
	public function _prepareData($data)
	{
		$momex = ALIAS_MOMEX;
		$pw = ALIAS_PW;
		$pwire5 = ALIAS_PW5;
		$transaction = $this->_beginTransaction('ResetPassword');

		// Send forgot password email from WordPress, default setting and required for V4
		if ((get_option('pw_email_forgot_pwd', 'on') == 'on') || (get_option('pw_v4_legacy_mode', 0) == 1)) {									
			$this->_prepareNode($transaction, "$pw:patient", null, array("$momex:id" => $data->patientid));
		} else {
			// Send forgot password email from PharmacyWire
			$this->_prepareNode($transaction, "$pwire5:customer", null, array("$pwire5:id" => $data->patientid));
		}
	}

	public function _parse($xml_content)
	{
		if ($this->_getStatus() == XML_STATUS_SUCCESS) {
			$objParse = new XmlApi_ParseData_PatientResetPassword($xml_content);
		} else {
			$objParse = new XmlApi_ParseData_Message($xml_content);
		}
		$retObject = $objParse->process();
		return $retObject;
	}
}
