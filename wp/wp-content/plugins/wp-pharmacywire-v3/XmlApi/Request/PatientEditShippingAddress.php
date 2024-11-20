<?php

/**
 * XmlApi_Request_PatientEditShippingAddress
 */
class XmlApi_Request_PatientEditShippingAddress extends Utility_XmlApiTransaction
{
	public function _prepareData($data)
	{
		$momex = ALIAS_MOMEX;
		$tr = ALIAS_TR;
		$pw = ALIAS_PW;
		$transaction = $this->_beginTransaction('EditShippingAddress');

		// assign data patient
		$this->_prepareNode($transaction, "$pw:patient", null, array("$momex:id" => $data->patientID));
		$shippingaddress = $this->_prepareNode($transaction, "$tr:shippingaddress", null, array("$momex:id" => $data->shippingAddressID));

		if (!empty($data->description)) {
			$this->_prepareNode($shippingaddress, "$momex:description", $data->description);
		} else {
			$this->_prepareNode($shippingaddress, "$momex:description", 'Shipping Address');
		}

		$this->_prepareNode($shippingaddress, "$momex:address1", $data->address1);
		$this->_prepareNode($shippingaddress, "$momex:address2", $data->address2);
		$this->_prepareNode($shippingaddress, "$momex:address3", $data->address3);
		$this->_prepareNode($shippingaddress, "$momex:city", $data->city);
		$this->_prepareNode($shippingaddress, "$momex:province", $data->province);
		$this->_prepareNode($shippingaddress, "$momex:country", $data->country);
		$this->_prepareNode($shippingaddress, "$momex:postalcode", $data->postalcode);
		$this->_prepareNode($shippingaddress, "$momex:phone", $data->phone);
		$this->_prepareNode($shippingaddress, "$momex:areacode", $data->areacode);
	}

	public function _parse($xml_content)
	{
		if ($this->_getStatus() == XML_STATUS_SUCCESS) {
			$objParse = new XmlApi_ParseData_PatientEditShippingAddress($xml_content);
		} else {
			$objParse = new XmlApi_ParseData_Message($xml_content);
		}
		$retObject = $objParse->process();
		return $retObject;
	}
}
