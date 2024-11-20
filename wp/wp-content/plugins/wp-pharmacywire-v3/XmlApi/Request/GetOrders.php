<?php

/**
 * XmlApi_Request_GetOrders
 **/
class XmlApi_Request_GetOrders extends Utility_XmlApiTransaction
{
	public function _prepareData($data)
	{
		$momex = ALIAS_MOMEX;
		$tr = ALIAS_TR;
		$transaction = $this->_beginTransaction('GetOrders');
		$orders = $this->_prepareNode($transaction, "$tr:orders");

		foreach ($data as $key => $value) {
			$this->_prepareNode($orders, "$tr:order", null, array("$momex:id" => $value));
		}
	}

	public function _parse($xml_content)
	{
		if ($this->_getStatus() == XML_STATUS_SUCCESS) {
			$objParse = new XmlApi_ParseData_GetOrders($xml_content);
		} else {
			$objParse = new XmlApi_ParseData_Message($xml_content);
		}
		$retObject = $objParse->process();
		return $retObject;
	}
}
