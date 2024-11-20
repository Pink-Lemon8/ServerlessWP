<?php

/**
 * XmlApi_ParseData_UserRequestStatus
 */
class XmlApi_ParseData_UserRequestStatus extends Utility_ParseDataBase
{
	/**
	 * Constructor
	 *
	 * @param mixed $xml_content
	 * @return XmlApi_ParseData_Catalog
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

		// create output result
		$reply = new Model_Entity_Reply();
		$reply->type = (string)$this->_xml_doc->type;
		$reply->status = (string)$this->_xml_doc->status;

		// get user list
		$userList = array();
		$nodeList = $this->_xml_doc->users->children();
		foreach ($nodeList as $node) {
			$user = new Model_Entity_User();

			$attrs = $node->attributes(XML_NS_MOMEX);

			$user->id = (string)$attrs['id'];
			$user->username = (string)$attrs['username'];
			$user->status = (string)$node->status;
			$user->status_note = (string)$node->status->note;
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

		return $reply;
	}
}
