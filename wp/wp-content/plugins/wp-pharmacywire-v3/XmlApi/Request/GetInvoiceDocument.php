<?php
class XmlApi_Request_GetInvoiceDocument extends Utility_XmlApiTransaction
{
	public function _prepareData($data)
	{
		$momex = ALIAS_MOMEX;
		$tr = ALIAS_TR;
		$pw = ALIAS_PW;
		$transaction = $this->_beginTransaction('GetInvoiceDocument');

		$invoices = $this->_prepareNode($transaction, "$momex:invoices");

		foreach ($data as $invoiceItem) {
			$this->_prepareNode($invoices, "$momex:invoice", null, array("$momex:id" => $invoiceItem['invoice_id'], "$momex:return-method" => $invoiceItem['return_method'], "$momex:return-mime-type" => $invoiceItem['return_mime_type']));
		}
	}

	public function _parse($xml_content)
	{
		if ($this->_getStatus() == XML_STATUS_SUCCESS) {
			$objParse = new XmlApi_ParseData_GetInvoiceDocument($xml_content);
		} else {
			$objParse = new XmlApi_ParseData_Message($xml_content);
		}
		$retObject = $objParse->process();
		return $retObject;
	}
}
