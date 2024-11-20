<?php

/**
 * XmlApi_Request_PatientGetShippingAddresses
 */
class XmlApi_Request_PatientGetShippingAddresses extends Utility_XmlApiTransaction
{
	public function _prepareData($data)
	{
		$momex = ALIAS_MOMEX;
		$pw = ALIAS_PW;
		$transaction = $this->_beginTransaction('GetShippingAddresses');

		$this->_prepareNode($transaction, "$pw:patient", null, array("$momex:id" => $data->patientid));
	}

	public function _parse($xml_content)
	{
		if ($this->_getStatus() == XML_STATUS_SUCCESS) {
			$objParse = new XmlApi_ParseData_PatientGetShippingAddresses($xml_content);
		} else {
			$objParse = new XmlApi_ParseData_Message($xml_content);
		}
		$retObject = $objParse->process();
		return $retObject;
	}
}
