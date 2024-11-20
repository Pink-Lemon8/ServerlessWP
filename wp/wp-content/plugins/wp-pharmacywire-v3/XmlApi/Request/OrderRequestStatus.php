<?php

/**
 * XmlApi_Request_OrderRequestStatus
 **/
class XmlApi_Request_OrderRequestStatus extends Utility_XmlApiTransaction
{
	public function _prepareData($data)
	{
		$momex = ALIAS_MOMEX;
		$tr = ALIAS_TR;
		$transaction = $this->_beginTransaction('RequestStatus');

		$orders = $this->_prepareNode($transaction, "$tr:orders");
		foreach ($data as $orderItem) {
			if (isset($orderItem->orderID) && !empty($orderItem->orderID)) {
				$this->_prepareNode($orders, "$tr:order", null, array("$momex:id" => $orderItem->orderID));
			}
		}
	}

	public function _parse($xml_content)
	{
		if ($this->_getStatus() == XML_STATUS_SUCCESS) {
			$objParse = new XmlApi_ParseData_OrderRequestStatus($xml_content);
		} else {
			$objParse = new XmlApi_ParseData_Message($xml_content);
		}
		$retObject = $objParse->process();
		return $retObject;
	}
}
