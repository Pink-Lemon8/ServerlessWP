<?php

/**
 * XmlApi_Request_PatientGetMedicalQuestions
 */
class XmlApi_Request_PatientGetMedicalQuestions extends Utility_XmlApiTransaction
{
	public function _prepareData($data)
	{
		$pw = ALIAS_PW;
		$momex = ALIAS_MOMEX;
		$tr = ALIAS_TR;
		$transaction = $this->_beginTransaction('GetMedicalQuestions');

		if (WebUser::isLoggedIn()) {
			$patientID = WebUser::GetUserID();
			$this->_prepareNode($transaction, "$pw:patient", null, array("$momex:id" => $patientID));
		}
	}

	public function _parse($xml_content)
	{
		if ($this->_getStatus() == XML_STATUS_SUCCESS) {
			$objParse = new XmlApi_ParseData_PatientGetMedicalQuestions($xml_content);
		} else {
			$objParse = new XmlApi_ParseData_Message($xml_content);
		}
		$retObject = $objParse->process();
		return $retObject;
	}
}
