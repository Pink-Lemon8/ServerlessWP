<?php

/**
 * XmlApi_ParseData_PatientAuthenticateUser
 */
class XmlApi_ParseData_PatientAuthenticateUser extends Utility_ParseDataBase
{
	/**
	 * Constructor
	 *
	 * @param mixed $xml_content
	 * @return XmlApi_ParseData_PatientAuthenticateUser
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

		// get list user
		$listUser = array();

		$user = new Model_Entity_User();
		$nodeList = $this->_xml_doc->users->children();
		$node = $nodeList->user;

		// get authenticated attribute
		$attrs = $node->attributes();
		$user->authenticated = (string)$attrs[0];

		$msgNode = $node->children();
		$user->message = (string)$msgNode[0];
		$attrs = $node->attributes(XML_NS_MOMEX);
		$user->id = (string)$attrs['id'];
		$user->name = (string)$attrs['username'];


		$roleNode = $this->_xml_doc->users->user->children(XML_NS_MOMEX);
		$roleAttrs = $roleNode->role->attributes(XML_NS_MOMEX);
		$user->type = (string)$roleNode->role[0];

		$listUser[] = $user;

		$reply->users = $listUser;

		// get message
		$messageParser = new XmlApi_ParseData_Message($this->_xml_content);
		$replyMessages = $messageParser->process();
		$reply->messages = null;
		if ($replyMessages instanceof Model_Entity_Reply) {
			$reply->messages = $replyMessages->messages;
		}

		// get dummy
		$nodeList = $this->_xml_doc->xpath('//' . XML_MOMEX . ':dummy');
		$reply->dummy = $nodeList[0];

		return $reply;
	}
}
