<?php

/**
 * XmlApi_ParseData_PatientSetPassword
 */
class XmlApi_ParseData_PatientSetPassword extends Utility_ParseDataBase
{
	/**
	 * Constructor
	 *
	 * @param mixed $xml_content
	 * @return XmlApi_ParseData_PatientSetPassword
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
