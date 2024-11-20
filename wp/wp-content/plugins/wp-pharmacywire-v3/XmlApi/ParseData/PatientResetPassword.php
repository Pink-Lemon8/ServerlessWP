<?php

/**
 * XmlApi_ParseData_PatientSetPassword
 */
class XmlApi_ParseData_PatientResetPassword extends Utility_ParseDataBase
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

		// get patient
		$patient = new Model_Entity_Patient();
		$node =  $this->_xml_doc;
		
		// Parse old/V4 response - email sent from WordPress
		if ((get_option('pw_email_forgot_pwd', 'on') == 'on') || (get_option('pw_v4_legacy_mode', 0) == 1)) {									
			$patientNode = $this->_getChildByName($node, 'patient', XML_PW);
			$attrs = $patientNode->attributes(XML_NS_MOMEX);
			$patient->id = (string)$attrs['id'];
			$patient->username = (string)$attrs['username'];
			$patient->password = (string)$this->_getChildByName($patientNode, 'password', XML_MOMEX);
		} else {
			// Parse newer V5 response - email sent by PharmacyWire
			$patientNode = $this->_getChildByName($node, 'customer', XML_PWIRE5);
			$attrs = $patientNode->attributes(XML_NS_PWIRE5);
			$patient->id = (string)$attrs['id'];
			$patient->password = (string)$this->_getChildByName($patientNode, 'password', XML_MOMEX);
		}

		$reply->patient = $patient;

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
