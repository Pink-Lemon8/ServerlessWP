<?php

/**
 * XmlApi_Request_PatientAddShippingAddress
 */
class XmlApi_Request_PatientAddShippingAddress extends Utility_XmlApiTransaction
{
	public function _prepareData($data)
	{
		$momex = ALIAS_MOMEX;
		$tr = ALIAS_TR;
		$pw = ALIAS_PW;
		$transaction = $this->_beginTransaction('AddShippingAddress');

		// assign data patient
		$this->_prepareNode($transaction, "$pw:patient", null, array("$momex:id" => $data->patientID));
		$shippingAddress = $this->_prepareNode($transaction, "$tr:shippingaddress");
		$this->_prepareNode($shippingAddress, "$momex:address1", $data->address1);
		$this->_prepareNode($shippingAddress, "$momex:address2", $data->address2);
		$this->_prepareNode($shippingAddress, "$momex:address3", $data->address3);
		$this->_prepareNode($shippingAddress, "$momex:city", $data->city);
		$this->_prepareNode($shippingAddress, "$momex:province", $data->province);
		$this->_prepareNode($shippingAddress, "$momex:country", $data->country);
		$this->_prepareNode($shippingAddress, "$momex:postalcode", $data->postalcode);
		$this->_prepareNode($shippingAddress, "$momex:phone", $data->phone);
		$this->_prepareNode($shippingAddress, "$momex:areacode", $data->areacode);
	}

	public function _parse($xml_content)
	{
		if ($this->_getStatus() == XML_STATUS_SUCCESS) {
			$objParse = new XmlApi_ParseData_PatientAddShippingAddress($xml_content);
		} else {
			$objParse = new XmlApi_ParseData_Message($xml_content);
		}
		$retObject = $objParse->process();
		return $retObject;
	}
}
