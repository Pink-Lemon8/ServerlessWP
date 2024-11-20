<?php

/**
 * XmlApi_ParseData_GetAgents
 */
class XmlApi_ParseData_GetAgents extends Utility_ParseDataBase
{
	/**
	 * Constructor
	 *
	 * @param mixed $xml_content
	 * @return XmlApi_ParseData_GetAgents
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

		$agents = array();
		$nodeAgents = $this->_getChildByName($this->_xml_doc, 'agents', XML_MOMEX);
		$nodeAgentList = $this->_getChildrenByName($nodeAgents, 'agent', XML_MOMEX);
		foreach ($nodeAgentList as $node) {
			$attrs = $node->attributes(XML_NS_MOMEX);
			if ($attrs['headoffice'] == 'true') {
				$agents[$attrs['affiliate-id'] . ''] = $attrs['id'];
			}
		}
		$reply->agents = $agents;

		// get messages
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
