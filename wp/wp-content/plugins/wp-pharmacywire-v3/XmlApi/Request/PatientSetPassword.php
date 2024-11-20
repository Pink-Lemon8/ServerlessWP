<?php

/**
 * XmlApi_Request_PatientSetPassword
 */
class XmlApi_Request_PatientSetPassword extends Utility_XmlApiTransaction
{
	public function _prepareData($data)
	{
		$momex = ALIAS_MOMEX;
		$pw = ALIAS_PW;
		$transaction = $this->_beginTransaction('SetPassword');
		// assign data patient
		$patient = $this->_prepareNode($transaction, "$pw:patient", null, array("$momex:id" => $data->patientid));
		$this->_prepareNode($patient, "$momex:password", $data->newpass, null, 1);
	}

	public function _parse($xml_content)
	{
		if ($this->_getStatus() == XML_STATUS_SUCCESS) {
			$objParse = new XmlApi_ParseData_PatientSetPassword($xml_content);
		} else {
			$objParse = new XmlApi_ParseData_Message($xml_content);
		}
		$retObject = $objParse->process();
		return $retObject;
	}
}
