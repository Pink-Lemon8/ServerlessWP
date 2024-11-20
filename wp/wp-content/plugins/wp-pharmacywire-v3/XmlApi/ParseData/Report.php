<?php

/**
 * XmlApi_ParseData_PatientSetInfo
 */
class XmlApi_ParseData_Report extends Utility_ParseDataBase
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
			$nodeRoot = $this->_getChildByName($this->_xml_doc, 'metric_results', XML_MOMEX);

			//get node configuration of report
			$nodes = $nodeRoot->configuration->children();
			//get all properties
			$nodeProperty = $nodes->properties->children();
			//get all note have data row
			$nodeDatas = $nodes->data_collection->children();

			//fill all rows to variable '$dataRowCollection'
			$dataRowCollection = $this->_getDataRowCollection($nodeDatas);
			$reply->datarows = $dataRowCollection;

			//fill data of header to variable '$arrDataHeader'
			$arrDataHeader = $this->_getDataHeader($nodeProperty);
			$reply->dataheader = $arrDataHeader;

			//fill data of header to variable '$arrDataType'
			$arrDataType = $this->_getDataTypeHeader($nodeProperty);
			$reply->datatype = $arrDataType;

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
