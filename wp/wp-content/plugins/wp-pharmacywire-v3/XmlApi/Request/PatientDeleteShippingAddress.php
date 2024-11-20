<?php

/**
 * XmlApi_Request_PatientDeleteShippingAddress
 */
class XmlApi_Request_PatientDeleteShippingAddress extends Utility_XmlApiTransaction
{
	public function _prepareData($data)
	{
		$momex = ALIAS_MOMEX;
		$tr = ALIAS_TR;
		$pw = ALIAS_PW;
		$transaction = $this->_beginTransaction('DeleteShippingAddress');

		$this->_prepareNode($transaction, "$pw:patient", null, array("$momex:id" => $data->patientID));
		$this->_prepareNode($transaction, "$tr:shippingaddress", null, array("$momex:id" => $data->shippingAddressID));
	}

	public function _parse($xml_content)
	{
		if ($this->_getStatus() == XML_STATUS_SUCCESS) {
			$objParse = new XmlApi_ParseData_PatientDeleteShippingAddress($xml_content);
		} else {
			$objParse = new XmlApi_ParseData_Message($xml_content);
		}
		$retObject = $objParse->process();
		return $retObject;
	}
}
