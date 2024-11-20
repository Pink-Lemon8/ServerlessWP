<?php

/**
 * XmlApi_ParseData_GetOrders
 */
class XmlApi_ParseData_GetOrders extends Utility_ParseDataBase
{
	/**
	 * Constructor
	 *
	 * @param mixed $xml_content
	 * @return XmlApi_ParseData_GetOrders
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
		$node = $this->_xml_doc;
		$ordersNode = $this->_getChildByName($node, 'orders', XML_MOMEX);
		$order_Nodes = $this->_getChildrenByName($ordersNode, 'order', XML_MOMEX);

		foreach ($order_Nodes as $order_Node) {
			$order = new Model_Entity_Order();
			$attrs = $order_Node->attributes(XML_NS_MOMEX);

			$order->id = (string)$attrs['id'];
			$order->status = (string)$this->_getChildByName($order_Node, 'status', XML_MOMEX);
			$order->visualstatus = $order->filterOrderVisualStatus((string)$this->_getChildByName($order_Node, 'visualstatus', XML_MOMEX));
			$order->_date = (string)$this->_getChildByName($order_Node, 'created', XML_MOMEX);
			$order->shippingcost = (string)$this->_getChildByName($order_Node, 'shippingcost', XML_MOMEX);
			$order->trackingid = (string)$this->_getChildByName($order_Node, 'trackingid', XML_MOMEX);

			$invoices = array();
			$invoice_document_status_list = $order_Node->xpath('./' . XML_MOMEX . ':invoice_document_status');
			foreach ($invoice_document_status_list as $invoice_document_status_node) {
				$invoice_document_status_attrs = $invoice_document_status_node->attributes(XML_MOMEX, true);

				$invoice_id = (string)$invoice_document_status_attrs['id'];
				$invoice_locked = false;
				if ((string)$invoice_document_status_attrs['locked'] === "true") {
					$invoice_locked = true;
				}
				$invoices[$invoice_id] = array('locked' => $invoice_locked);
			}
			$order->invoices = $invoices;

			foreach (array('billing', 'shipping') as $addresstype) {
				$address_node = $this->_getChildByName($order_Node, $addresstype, XML_MOMEX);
				$address_info = array();
				foreach (array('firstname', 'lastname', 'address', 'address2', 'address3', 'city', 'state', 'country', 'postalcode') as $addresstype_info) {
					$address_info[$addresstype_info] = (string)$this->_getChildByName($address_node, $addresstype_info, XML_MOMEX);
				}
				$order[$addresstype] = $address_info;

				if ($addresstype === 'billing') {
					$payement_node = $this->_getChildByName($address_node, 'payment', XML_MOMEX);
					$payement_node_attr = $payement_node->attributes(XML_NS_MOMEX);

					$paymeny_info = array();
					$paymeny_info['type'] = (string)$payement_node_attr['type'];
					if ($paymeny_info['type'] === 'credit card') {
						$cc_node = $this->_getChildByName($payement_node, 'credit-card', XML_MOMEX);
						$cc_node_attr = $cc_node->attributes(XML_NS_MOMEX);
						$paymeny_info['creditcardnumber'] = (string)$cc_node;
						$paymeny_info['creditcardexpiry'] = (string)$cc_node_attr['expiry'];
					}
					$order['billing_info'] = $paymeny_info;
				}
			}

			$items = array();

			$itemsNode = $this->_getChildByName($order_Node, 'items', XML_MOMEX);
			$item_Nodes = $this->_getChildrenByName($itemsNode, 'item', XML_MOMEX);

			$orderSubTotal = 0;

			foreach ($item_Nodes as $item_node) {
				$item_node_attr = $item_node->attributes(XML_NS_MOMEX);
				$item = array();

				$item['part-id'] = (string)$item_node_attr['part-id'];
				foreach (array('description', 'quantity', 'unitprice', 'created', 'updated') as $item_info) {
					$item[$item_info] = (string)$this->_getChildByName($item_node, $item_info, XML_MOMEX);
				}

				$item['line-total'] = number_format(($item['unitprice'] * $item['quantity']), 2, '.', '');
				$item['unitprice'] = PC_formatPrice($item['unitprice']);

				$items[] = $item;

				$orderSubTotal += $item['line-total'];
			}

			$order->subtotal = number_format($orderSubTotal, 2, '.', '');

			$order->items = $items;
			$grandTotal = (string)$this->_getChildByName($order_Node, 'total', XML_MOMEX);
			$order->grandtotal = number_format($grandTotal, 2, '.', '');

			$order->status_note = '';
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
