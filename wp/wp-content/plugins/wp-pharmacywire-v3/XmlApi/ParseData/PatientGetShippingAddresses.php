<?php

/**
 * XmlApi_ParseData_PatientGetShippingAddresses
 */
class XmlApi_ParseData_PatientGetShippingAddresses extends Utility_ParseDataBase
{
	/**
	 * Constructor
	 *
	 * @param mixed $xml_content
	 * @return XmlApi_ParseData_PatientGetShippingAddresses
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

		// get shippingAddress list
		$addressList = array();
		$nodeList = $this->_xml_doc->shippingaddresses->children();
		foreach ($nodeList as $node) {
			$address = new Model_Entity_Address();
			$attrs = $node->attributes(XML_NS_MOMEX);
			$address->id = (string)$attrs['id'];
			$address->description = (string)$this->_getChildByName($node, 'description', XML_MOMEX);
			$address->address1 = (string)$this->_getChildByName($node, 'address1', XML_MOMEX);
			$address->address2 = (string)$this->_getChildByName($node, 'address2', XML_MOMEX);
			$address->address3 = (string)$this->_getChildByName($node, 'address3', XML_MOMEX);
			$address->city = (string)$this->_getChildByName($node, 'city', XML_MOMEX);
			$address->province = (string)$this->_getChildByName($node, 'province', XML_MOMEX);
			$address->region = (string)$this->_getChildByName($node, 'province', XML_MOMEX);
			$address->country = (string)$this->_getChildByName($node, 'country', XML_MOMEX);
			$address->postalcode = (string)$this->_getChildByName($node, 'postalcode', XML_MOMEX);
			$address->regioncode = (string)$this->_getChildByName($node, 'postalcode', XML_MOMEX);
			$address->phone = (string)$this->_getChildByName($node, 'phone', XML_MOMEX);
			$address->areacode = (string)$this->_getChildByName($node, 'areacode', XML_MOMEX);
			$addressList[] = $address;
		}

		$reply->address = $addressList;

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
