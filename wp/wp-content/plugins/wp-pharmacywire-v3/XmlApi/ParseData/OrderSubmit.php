<?php

/**
 * XmlApi_ParseData_OrderSubmit
 */
class XmlApi_ParseData_OrderSubmit extends Utility_ParseDataBase
{
	/**
	 * Constructor
	 *
	 * @param mixed $xml_content
	 * @return XmlApi_ParseData_OrderSubmit
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
		$reply->status = (string) $this->_xml_doc->status;
		$reply->type = (string) $this->_xml_doc->type;
		$reply->order = (string) $this->_xml_doc->order;
		$reply->order_id = $reply->order;

		$billing_detail_node = $this->_xml_doc->billing_detail;
		if ($billing_detail_node) {
			$attrs = $billing_detail_node->attributes();
		}

		$billing_offsite_detail_node = $this->_xml_doc->billing_offsite_detail;
		if ($billing_offsite_detail_node) {
			$attrs = $billing_offsite_detail_node->attributes();
		}

		$billing_account_activation_node = $this->_xml_doc->billing_account_activation;
		if ($billing_account_activation_node) {
			$attrs = $billing_account_activation_node->attributes();
			$billing_account_activation = (string) $billing_account_activation_node;
			if (strlen($billing_account_activation)) {
				$reply->billing_account_activation = array(
					'kind' => (string) $attrs['kind'],
					'mime-type' => (string) $attrs['mime-type'],
					'mime-encoding' => (string) $attrs['mime-encoding'],
					'show_to_customer' => ((string) $attrs['show_to_customer'] === 'true' ? true : false),
					'content' => $billing_account_activation,
				);
			}
		}

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
