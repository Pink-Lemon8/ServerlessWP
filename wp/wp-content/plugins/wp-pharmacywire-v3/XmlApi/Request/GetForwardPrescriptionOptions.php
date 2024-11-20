<?php

/**
 * XmlApi_GetForwardPrescriptionOptions
 */
class XmlApi_GetForwardPrescriptionOptions extends Utility_XmlApiTransaction
{
	public function _prepareData($data)
	{
		$momex = ALIAS_MOMEX;
		$tr = ALIAS_TR;
		$transaction = $this->_beginTransaction('GetForwardPrescriptionOptions');
	}

	public function _parse($xml_content)
	{
		if ($this->_getStatus() == XML_STATUS_SUCCESS) {
			$objParse = new XmlApi_ParseData_GetForwardPrescriptionOptions($xml_content);
			$retObject = $objParse->process();
		} else {
			// setup default values if no successful XML Connect GetForwardPrescriptionOptions response
			$retObject = new Model_Entity_Reply();
			$retObject->status = 'success';
			$retObject->type = 'GetForwardPrescriptionOptions';
			$forwardPrescriptionOptions = [
				"OnFile" => ["value" => "OnFile", "label" => "Rx on file", "sort_order" => 1],
				"Upload" => ["value" => "Upload", "label" => "Upload", "sort_order" => 2],
				"Email" => ["value" => "Email", "label" => "Email", "sort_order" => 3],
				"Doctor" => ["value" => "Doctor", "label" => "Contact my doctor", "sort_order" => 4],
				"Mail" => ["value" => "Mail", "label" => "Mail", "sort_order" => 5],
			];

			$retObject->forward_prescription_options = $forwardPrescriptionOptions;
		}
		return $retObject;
	}

	public function getFwdRxOptions()
	{
		$data = new stdClass();
		$this->process($data);
		$reply = $this->getData();
		$forwardPrescriptionOptions = $reply->forward_prescription_options;
		return $forwardPrescriptionOptions;
	}
}
