<?php

/**
 * Model_Order
 **/
class Model_UploadDocument extends Utility_ModelBase
{
	// Create function submitOrder
	public function uploadDocument($user_id, $file_name, $mime_type, $document_content)
	{
		// create the returned object
		$reply = new Model_Entity_Reply();
		// prepare data to execute XML request
		$data = new stdClass();
		$data->user_id = $user_id;
		$data->file_name = urlencode($file_name);
		$data->mime_type = $mime_type;
		$data->document_content = $document_content;
		// create the request via XmlApi Request
		$newDocument = new XmlApi_Request_UploadDocument();
		$newDocument->process($data);

		// get result
		$reply = $newDocument->getData();

		// return result
		return $reply;
	}
}
