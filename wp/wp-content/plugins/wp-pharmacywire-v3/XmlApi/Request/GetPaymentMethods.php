<?php

/**
 * XmlApi_Payment_MethodOptions
 */
class XmlApi_Payment_GetPaymentMethods extends Utility_XmlApiTransaction
{
	public function _prepareData($data)
	{
		$momex = ALIAS_MOMEX;
		$tr = ALIAS_TR;
		$pw = ALIAS_PW;

		// ***** GetPaymentMethods - not yet implemented in pharmacywire 

		$transaction = $this->_beginTransaction('GetPaymentMethods');
	}

	public function _parse($xml_content)
	{
		if ($this->_getStatus() == XML_STATUS_SUCCESS) {
			$objParse = new XmlApi_ParseData_Payment_GetPaymentMethods($xml_content);
		} else {
			$objParse = new XmlApi_ParseData_Message($xml_content);
		}

		$retObject = $objParse->process();
		return $retObject;
	}
}
