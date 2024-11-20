<?php

/**
 * XmlApi_Request_AdjustOrder
 */
class XmlApi_Request_AdjustOrder extends Utility_XmlApiTransaction
{
	public function _prepareData($data)
	{
		$momex = ALIAS_MOMEX;
		$pw = ALIAS_PW;
		$tr = ALIAS_TR;
		$transaction = $this->_beginTransaction('AdjustOrder');

		if (isset($data->orderID) && !empty($data->orderID)) {
			$order = $this->_prepareNode($transaction, "$tr:order", null, array("$momex:id" => $data->orderID));

			if (isset($data->rx_forwarding)) {
				$this->_prepareNode($order, "$pw:Rx-forwarding", $data->rx_forwarding);
			}
			if (isset($data->child_resistant_packaging)) {
				$this->_prepareNode($order, "$pw:child-resistant-packaging", $data->child_resistant_packaging);
			}
			if (isset($data->contact_patient)) {
				$this->_prepareNode($order, "$pw:contact-patient", $data->contact_patient);
			}
		}
	}

	public function _parse($xml_content)
	{
		if ($this->_getStatus() == XML_STATUS_SUCCESS) {
			$objParse = new XmlApi_ParseData_AdjustOrder($xml_content);
		} else {
			$objParse = new XmlApi_ParseData_Message($xml_content);
		}
		$retObject = $objParse->process();
		return $retObject;
	}
}
