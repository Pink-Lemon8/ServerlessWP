<?php

/**
 * XmlApi_Request_OrderAddComment
 **/
class XmlApi_Request_OrderAddComment extends Utility_XmlApiTransaction
{
	public function _prepareData($data)
	{
		$momex = ALIAS_MOMEX;
		$tr = ALIAS_TR;
		$pw = ALIAS_PW;
		$transaction = $this->_beginTransaction('AddComment');

		$this->_prepareNode($transaction, "$pw:patient", null, array("$momex:id" => $data->patientID));
		$order = $this->_prepareNode($transaction, "$tr:order", null, array("$momex:id" => $data->orderID));
		$this->_prepareNode($order, "$momex:comment", $data->orderComment, array("$momex:category" => "Alert", "$momex:completed" => "false"));
	}

	public function _parse($xml_content)
	{
		if ($this->_getStatus() == XML_STATUS_SUCCESS) {
			$objParse = new XmlApi_ParseData_OrderAddComment($xml_content);
		} else {
			$objParse = new XmlApi_ParseData_Message($xml_content);
		}
		$retObject = $objParse->process();
		return $retObject;
	}
}
