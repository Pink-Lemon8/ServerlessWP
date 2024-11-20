<?php

/**
 * XmlApi_ParseData_PatientSetInfo
 */
class XmlApi_ParseData_GetInvoiceDocument extends Utility_ParseDataBase
{
	/**
	 * Constructor
	 *
	 * @param mixed $xml_content
	 * @return XmlApi_ParseData_Report
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
		//

		if (Utility_Common::isReplySuccess($reply)) {

			//get node root of report
			$invoices = array();
			$invoice_nodes = $this->_xml_doc->xpath('//' . XML_MOMEX . ':invoices/' . XML_MOMEX . ':invoice');
			foreach ($invoice_nodes as $invoice_node) {
				$momex_invoice_attrs = $invoice_node->attributes(XML_MOMEX, true);
				$invoice_id = (string)$momex_invoice_attrs['id'];

				$document_list = $invoice_node->xpath('./' . XML_MOMEX . ':document');
				$documents = array();
				foreach ($document_list as $document_node) {
					$document_attr = $document_node->attributes(XML_MOMEX, true);
					$mime_encoding = (string)$document_attr['mime-encoding'];
					$document_content = (string)$document_node;
					if ($mime_encoding  === 'base64') {
						$mime_encoding = 'raw';
						$document_content = base64_decode($document_content);
					} elseif ($mime_encoding  === 'raw') {
						1;
					}
					$document = array(
						'mime_type' => (string)$document_attr['mime-type'],
						'mime_encoding' => $mime_encoding,
						'kind' => (string)$document_attr['kind'],
						'pages' => (string)$document_attr['pages'],
						'content' => $document_content,
					);

					$documents[] = $document;
				}

				$invoice_detail = array('documents' => $documents);
				$invoices[$invoice_id] = $invoice_detail;
			}

			$reply->invoices = $invoices;

			// get message
			$messageParser = new XmlApi_ParseData_Message($this->_xml_content);
			$replyMessages = $messageParser->process();
			$reply->messages = null;
			if ($replyMessages instanceof Model_Entity_Reply) {
				$reply->messages = $replyMessages->messages;
			}
		}

		// return parsed data
		return $reply;
	}
	//parse data of header to array
	public function _getDataHeader($nodeProperty)
	{
		$arrDataHeader = array();
		//get property of repost
		foreach ($nodeProperty as $node) {
			$attrs = $node->attributes();
			$column_id = (string)$attrs['name'];
			$label = (string)$node->label;
			$arrDataHeader[$column_id] = $label;
		}
		//fill data to header
		return $arrDataHeader;
	}

	//parse data type of header to array
	public function _getDataTypeHeader($nodeProperty)
	{
		$arrDataType = array();
		//get property of repost
		foreach ($nodeProperty as $node) {
			$attrs = $node->attributes();
			$column_id = (string)$attrs['name'];
			$column_type = (string)$attrs['type'];
			$arrDataType[$column_id] = $column_type;
		}
		//fill data to header
		return $arrDataType;
	}

	//parse datarow collection to array
	public function _getDataRowCollection($dataNodes)
	{
		$arrDataRows = array();
		foreach ($dataNodes as $node) {
			//add data to array $arrProperty

			foreach ($node as $item) {
				$attrs = $item->attributes();
				$column_id = (string)$attrs['column'];
				$strvalue = $item->asXML();
				if (strpos($strvalue, '<!--') > 0) {
					$value = PC_getElementText($strvalue);
				} else {
					$value = (string)$item;
				}
				$arrProperty[$column_id] = $value;
			}
			//fill data to table row
			$arrDataRows[] = $arrProperty;
		}
		return $arrDataRows;
	}
}
