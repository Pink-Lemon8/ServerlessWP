<?php

/**
 * XmlApi_ParseData_GetCustomConfig
 */
class XmlApi_ParseData_GetCustomConfig extends Utility_ParseDataBase
{
	/**
	 * Constructor
	 *
	 * @param mixed $xml_content
	 * @return XmlApi_ParseData_GetCustomConfig
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

		$config = array();
		$nodeAgents = $this->_getChildByName($this->_xml_doc, 'agents', XML_MOMEX);
		$nodeAgent = $this->_getChildByName($nodeAgents, 'agent', XML_MOMEX);
		$nodeConfig = $this->_getChildByName($nodeAgent, 'custom_config', XML_MOMEX);
		if (is_null($nodeConfig)) {
			$reply->status = 'error';
		} else {
			$parameters = $this->_getChildrenByName($nodeConfig, 'parameter', XML_MOMEX);

			foreach ($parameters as $parameter) {
				$attrs = $parameter->attributes();
				$config[(string)$attrs['name']] = (string)$parameter;
			}
			$reply->config = $config;

			// get messages
			$messageParser = new XmlApi_ParseData_Message($this->_xml_content);
			$replyMessages = $messageParser->process();
			$reply->messages = null;
			if ($replyMessages instanceof Model_Entity_Reply) {
				$reply->messages = $replyMessages->messages;
			}
		}

		// return parsed data
		return $reply;
	}
}
