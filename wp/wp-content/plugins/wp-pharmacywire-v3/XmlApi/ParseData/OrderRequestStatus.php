<?php

/**
 * XmlApi_ParseData_OrderRequestStatus
 */
class XmlApi_ParseData_OrderRequestStatus extends Utility_ParseDataBase
{
	/**
	 * Constructor
	 *
	 * @param mixed $xml_content
	 * @return XmlApi_ParseData_OrderRequestStatus
	 */
	public function __construct($xml_content)
	{
		parent::__construct($xml_content);
	}

	/**
	 * Override the base function
	 *
	 */
	public function _parseXml()
	{
		parent::_parseXml();

		$reply = new Model_Entity_Reply();
		$reply->status = (string)$this->_xml_doc->status;
		$reply->type = (string)$this->_xml_doc->type;

		// get orderList
		$orderList = array();
		$nodeList = $this->_xml_doc->orders->children();

		foreach ($nodeList as $node) {
			$order = new Model_Entity_Order();
			$attrs = $node->attributes(XML_NS_MOMEX);
			$order->id = (string)$attrs['id'];
			$order->status = (string)$node->status;
			$order->_date = (string)$node->date;
			$order->status_note = (string)$node->status_note;
			$orderList[] = $order;
		}

		$reply->orders = $orderList;

		// get message
		$messageParser = new XmlApi_ParseData_Message($this->_xml_content);
		$replyMessages = $messageParser->process();
		$reply->messages = null;
		if ($replyMessages instanceof Model_Entity_Reply) {
			$reply->messages = $replyMessages->messages;
		}

		// get dump
		$reply->dummy = (string)$this->_getChildByName($node, 'dummy', XML_MOMEX);

		// return parsed data
		return $reply;
	}
}
