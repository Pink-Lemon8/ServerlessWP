<?php

/**
 * Model_Order
 **/
class Model_Report extends Utility_ModelBase
{
	public function getDataByType($report_type)
	{
		// create the returned object
		$reply = new Model_Entity_Reply();

		// prepare data to execute XML request
		$data = new stdClass();
		$data->reportType = $report_type;
		// create the request via XmlApi Request
		$neworder = new XmlApi_Request_Report();
		$neworder->process($data);

		// return result
		$reply = $neworder->getData();
		// return result
		return $reply;
	}
}
