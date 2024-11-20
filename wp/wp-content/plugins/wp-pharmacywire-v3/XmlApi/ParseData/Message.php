<?php

/**
 * XmlApi_ParseData_Message
 */
class XmlApi_ParseData_Message extends Utility_ParseDataBase
{
	/**
	 * Constructor
	 *
	 * @param mixed $xml_content
	 * @return XmlApi_ParseData_Catalog
	 */
	public function __construct($xml_content)
	{
		$this->_debugMode = false;
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
		$reply->messages = array();
		$reply->status = (string)$this->_xml_doc->status;
		$reply->type = (string)$this->_xml_doc->type;

		if ((string)$reply->status === XML_STATUS_INVALID) {
			return $reply;
		}

		// get message when parse data from server response
		$messages = $this->getMessageWhenParseData();
		$reply->messages = $messages;
		return $reply;
	}

	//set message when parse data from server response
	public function getMessageWhenParseData()
	{
		$messages = null;
		$nodeList = $this->_xml_doc->messages->children();
		foreach ($nodeList as $node) {
			$message = new Model_Entity_ReplyMessage();

			$attrs = $node->attributes();

			$message->type = (string)$attrs['type'];
			$message->content = (string)$node;
			$messages[] = $message;
		}
		return $messages;
	}
}
