<?php

/**
 * XmlApi_Request_PatientGetInfo
 */
class XmlApi_Request_PatientGetInfo extends Utility_XmlApiTransaction
{
	public function _prepareData($data)
	{
		$transaction = $this->_beginTransaction('GetPatientInfo');

		$pw = ALIAS_PW;
		$momex = ALIAS_MOMEX;
		$this->_prepareNode($transaction, "$pw:patient", null, array("$momex:id" => $data->patientid));
	}

	public function _parse($xml_content)
	{
		if ($this->_getStatus() == XML_STATUS_SUCCESS) {
			$objParse = new XmlApi_ParseData_PatientGetInfo($xml_content);
		} else {
			$objParse = new XmlApi_ParseData_Message($xml_content);
		}
		$retObject = $objParse->process();
		return $retObject;
	}
}
