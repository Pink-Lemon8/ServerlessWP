<?php

/**
 * XmlApi_ParseData_PatientCreate
 */
class XmlApi_ParseData_PatientCreate extends Utility_ParseDataBase
{
	/**
	 * Constructor
	 *
	 * @param mixed $xml_content
	 * @return XmlApi_ParseData_PatientCreate
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

		$patientNode = $this->_getChildByName($this->_xml_doc, 'patient', XML_PW);
		$attrs = $patientNode->attributes(XML_NS_MOMEX);
		$reply->patient_id = (string)$attrs[0];

		$userList = array();
		$nodeList = $this->_xml_doc->messages->children();
		foreach ($nodeList as $node) {
			$user = new Model_Entity_User();
			$attrs = $node->attributes();
			$user->type = (string)$attrs['type'];

			$userList[] = $user;
		}
		$reply->users = $userList;

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
