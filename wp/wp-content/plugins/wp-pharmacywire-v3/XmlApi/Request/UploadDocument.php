<?php
class XmlApi_Request_UploadDocument extends Utility_XmlApiTransaction
{
	public function _prepareData($data)
	{
		$pw = ALIAS_PW;
		$momex = ALIAS_MOMEX;
		$transaction = $this->_beginTransaction('UploadDocument');
		if ($data->user_id) {
			$this->_prepareNode($transaction, "$pw:patient", null, array("$momex:id" => $data->user_id));
		}
		$document_content = $this->convertStringToBase64_Encode($data->document_content);
		// mime-encoding must be set
		$this->_prepareNode($transaction, "$momex:document", $document_content, array("$momex:file-name" => $data->file_name, "$momex:mime-type" => $data->mime_type, "$momex:mime-encoding" => "base64", "$pw:document-contains-rx" => "true"));
	}

	public function convertStringToBase64_Encode($value)
	{
		return  base64_encode($value);
	}
	// Get status of a request
	public function _getStatus()
	{
		$int_result = 0;
		return $int_result;
	}

	public function _parse($xml_content)
	{
		$objParse = new XmlApi_ParseData_UploadDocument($xml_content);
		$retObject = $objParse->process();
		return $retObject;
	}
}
