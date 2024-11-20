<?php

/**
 * XmlApi_Request_Catalog
 **/
class XmlApi_Request_Catalog extends Utility_XmlApiTransaction
{
	public function _prepareData($data)
	{
		$momex = ALIAS_MOMEX;
		$pw = ALIAS_PW;
		$transaction = $this->_beginTransaction('Catalog');
		$criteria = $this->_prepareNode($transaction, "$momex:criteria");
		$this->_prepareNode($criteria, "$pw:drug-package-option", null, array("$pw:include-zero-priced" => 'true', "$pw:include-inactive" => 'false'));
	}

	public function _getStatus()
	{
		$int_result = 0;
		return $int_result;
	}

	public function _parse($xml_content)
	{
		$objParse = new XmlApi_ParseData_Catalog($xml_content);
		$retObject = $objParse->process();
		return $retObject;
	}
}
