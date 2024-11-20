<?php

/**
 * XmlApi_Request_PatientRecentOrders
 */
class XmlApi_Request_PatientRecentOrders extends Utility_XmlApiTransaction
{
	public function _prepareData($data)
	{
		$pw = ALIAS_PW;
		$momex = ALIAS_MOMEX;
		$tr = ALIAS_TR;
		$transaction = $this->_beginTransaction('GetPatientOrders', array("$tr:limit" => $data->limitOrder));
		$this->_prepareNode($transaction, "$pw:patient", null, array("$momex:id" => $data->patientID));
	}

	public function _parse($xml_content)
	{
		if ($this->_getStatus() == XML_STATUS_SUCCESS) {
			$objParse = new XmlApi_ParseData_PatientRecentOrders($xml_content);
		} else {
			$objParse = new XmlApi_ParseData_Message($xml_content);
		}
		$retObject = $objParse->process();
		return $retObject;
	}
}
