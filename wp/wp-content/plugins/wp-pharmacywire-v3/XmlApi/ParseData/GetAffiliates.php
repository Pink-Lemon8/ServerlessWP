<?php

/**
 * XmlApi_ParseData_GetAffiliates
 */
class XmlApi_ParseData_GetAffiliates extends Utility_ParseDataBase
{
	/**
	 * Constructor
	 *
	 * @param mixed $xml_content
	 * @return XmlApi_ParseData_GetAffiliates
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

		$affiliates = array();
		$nodeAffiliates = $this->_getChildByName($this->_xml_doc, 'affiliates', XML_MOMEX);
		$nodeAffiliatesList = $this->_getChildrenByName($nodeAffiliates, 'affiliate', XML_MOMEX);

		foreach ($nodeAffiliatesList as $node) {
			$attrs = $node->attributes(XML_NS_MOMEX);

			$affID = (int) $attrs['id'];
			$affiliates[$affID]['affiliate_code'] = (string) $attrs['affiliate-code'];
			$affiliates[$affID]['id'] = (int) $attrs['id'];
			$affiliates[$affID]['agent_id'] = (int) $attrs['agent-id'];
		}

		$reply->affiliates = $affiliates;

		return $reply;
	}
}
