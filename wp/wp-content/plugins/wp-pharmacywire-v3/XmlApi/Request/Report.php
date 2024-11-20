<?php
class XmlApi_Request_Report extends Utility_XmlApiBase
{
	public function __construct()
	{
		parent::__construct('report');
	}

	public function _prepareData($data)
	{
		parent::_prepareData($data);
		$this->replaceField('report_type', $data->reportType);
	}

	public function _getStatus()
	{
		$int_result = 0;
		return $int_result;
	}

	public function _parse($xml_content)
	{
		$objParse = new XmlApi_ParseData_Report($xml_content);
		$retObject = $objParse->process();
		return $retObject;
	}
}
