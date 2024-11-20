<?php

/**
 * XmlApi_ParseData_PatientRecentOrders
 */
class XmlApi_ParseData_PatientRecentOrders extends Utility_ParseDataBase
{
	/**
	 * Constructor
	 *
	 * @param mixed $xml_content
	 * @return XmlApi_ParseData_PatientAddLegalAgreement
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

		$nodeOrders = $this->_getChildByName($this->_xml_doc, 'orders', XML_MOMEX);
		$nodeOrderList = $this->_getChildrenByName($nodeOrders, 'order', XML_MOMEX);

		$orders = array();
		foreach ($nodeOrderList as $nodeOrder) {
			$orderEntity = new Model_Entity_Order();

			$attrs = $nodeOrder->attributes(XML_NS_MOMEX);
			$supportedAttrs = ["id", "containsPrescriptions", "hasPendingTranscription"];
			foreach ($supportedAttrs as $sAttr) {
				if (!empty($attrs[$sAttr])) {
					$orderEntity->{$sAttr} = (string)$attrs[$sAttr];
				}
			}
			$orderEntity->created = (string)$this->_getChildByName($nodeOrder, 'created', XML_MOMEX);
			$orderEntity->status = (string)$this->_getChildByName($nodeOrder, 'status', XML_MOMEX);
			$orderEntity->visualstatus = $orderEntity->filterOrderVisualStatus((string)$this->_getChildByName($nodeOrder, 'visualstatus', XML_MOMEX));
			$orderEntity->trackingid = (string)$this->_getChildByName($nodeOrder, 'trackingid', XML_MOMEX);

			$invoices = array();
			$invoice_document_status_list = $nodeOrder->xpath('./' . XML_MOMEX . ':invoice_document_status');
			foreach ($invoice_document_status_list as $invoice_document_status_node) {
				$invoice_document_status_attrs = $invoice_document_status_node->attributes(XML_MOMEX, true);

				$invoice_id = (string)$invoice_document_status_attrs['id'];
				$invoice_locked = false;
				if ((string)$invoice_document_status_attrs['locked'] === "true") {
					$invoice_locked = true;
				}
				$invoices[$invoice_id] = array('locked' => $invoice_locked);
			}
			$orderEntity->invoices = $invoices;

			// crreate order items
			$orderItems = array();
			$nodeItems = $this->_getChildByName($nodeOrder, 'items', XML_MOMEX);
			$nodeItemList = $this->_getChildrenByName($nodeItems, 'item', XML_MOMEX);
			foreach ($nodeItemList as $nodeItem) {
				$orderItem = new stdClass();

				$attrs = $nodeItem->attributes(XML_NS_MOMEX);
				$orderItem->id = (string)$attrs["id"];
				$orderItem->description = (string)$this->_getChildByName($nodeItem, 'description', XML_MOMEX);
				$orderItem->quantity = (string)$this->_getChildByName($nodeItem, 'quantity', XML_MOMEX);
				$orderItem->unitprice = PC_formatPrice((float)$this->_getChildByName($nodeItem, 'unitprice', XML_MOMEX));
				$orderItems[] = $orderItem;
			}

			$orderEntity->items = $orderItems;
			$orders[] = $orderEntity;
		}
		$reply->orders = $orders;

		// get message
		$messageParser = new XmlApi_ParseData_Message($this->_xml_content);
		$replyMessages = $messageParser->process();
		$reply->messages = null;
		if ($replyMessages instanceof Model_Entity_Reply) {
			$reply->messages = $replyMessages->messages;
		}

		// return parsed data
		return $reply;
	}
}
