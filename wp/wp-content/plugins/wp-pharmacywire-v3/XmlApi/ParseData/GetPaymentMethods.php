<?php

/**
 * XmlApi_ParseData_Payment_GetPaymentMethods
 */
class XmlApi_ParseData_Payment_GetPaymentMethods extends Utility_ParseDataBase
{
	/**
	 * Constructor
	 *
	 * @param mixed $xml_content
	 * @return XmlApi_ParseData_Payment_GetPaymentMethods
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

		// Parse to be done

		return $reply;
	}
}
