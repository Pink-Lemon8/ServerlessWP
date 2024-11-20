<?php
class Page_UploadDocument extends Utility_PageBase
{
	/**
	 * Make the html output
	 *
	 * @return The content in html format
	 */
	public function _process()
	{
		$this->setTemplate("page_upload_document");
		$uploadMaxSize = (10 < wp_max_upload_size() / (1024 * 1024)) ? 10 : wp_max_upload_size() / (1024 * 1024);
		$this->assign('UPLOAD_SIZE_LIMIT', $uploadMaxSize . 'MB');
		$this->assign('UPLOAD_URL', PC_getUploadDocumentUrl());
		// check if file is valided
		if ($this->validData()) {
			//save file to server
			$this->saveDocument();
		}
	}
	//get content of file then save file to server
	public function saveDocument()
	{
		$reply = new Model_Entity_Reply();
		$requestState = new Model_Request_State();
		$tempBanLimit = (WebUser::isLoggedIn()) ? 30 : 10;
		$uploadReqState = $requestState->requestStateCheck('UploadDocument', array('noPermaBan' => 1, 'tempBanLimit' => $tempBanLimit, 'timePeriod' => '-1 day'));

		if (($uploadReqState->status != 'failure')) {
			$requestState->logRequestAttempt('UploadDocument');
			$user_id = WebUser::getUserID();
			$fileName = $this->getFileName();
			$fileType = $this->getFileType();
			//convert file to string binary
			$stringBinary = $this->getStringBinaryByFile();
			$stringBinary = $this->processImage($stringBinary);

			if ($stringBinary !== 0) {
				$mUpload  = new Model_UploadDocument();
				// send document to server
				$reply = $mUpload->uploadDocument($user_id, $fileName, $fileType, $stringBinary);
				//show messega is successful
				if (Utility_Common::isReplySuccess($reply)) {
					$this->uploadResponse('success', 'File upload successful');
				} else {
					$this->uploadResponse('failure');
				}
			} else {
				$this->uploadResponse('failure');
			}
		}
		$this->uploadResponse('failure');
		return false;
	}

	public function uploadResponse($status = 'failure', $message = 'File upload failed')
	{
		if ($status === 'success') {
			$httpStatusCode = 200;
		} else {
			$httpStatusCode = 400;
			$response['error'] = $message;
		}
		$response['status'] = $status;
		$response['message'] = $message;

		header_remove();
		header('Content-Type: application/json; charset=utf-8', true, $httpStatusCode);
		echo json_encode($response);
		die();
	}

	public function processImage($stringBinary)
	{
		if (!extension_loaded('imagick')) {
			error_log('Install php ImageMagick - PharmacyWire plugin requires it for uploads.');
			return 0;
		} else {
			try {
				$im = new Imagick();
				$im->readImageBlob($stringBinary);
				$imData = $im->identifyImage();
			} catch (ImagickException $e) {
				return 0;
			}
			if ($imData['mimetype'] == 'application/pdf') {
				// pdf mimetype detected 'ok', pass original binary on to pharmacywire
				return $stringBinary;
			}
			// return ImageMagick regenerated image
			return $im;
		}
	}

	//convert file to string binary
	public function getStringBinaryByFile()
	{
		$imgfile = $_FILES["file"]["tmp_name"];
		$imgbinary = fread(fopen($imgfile, "r"), filesize($imgfile));
		return $imgbinary;
	}
	public function getFileName()
	{
		return $_FILES["file"]["name"];
	}
	public function getFileSize()
	{
		return $_FILES["file"]["size"];
	}
	public function getFileType()
	{
		return $_FILES["file"]["type"];
	}
	//check data is valid
	public function validData()
	{
		if (isset($_FILES["file"])) {
			if ($_FILES["file"]["error"] > 0) {
				$this->uploadResponse('failure');
			} else {
				$fileType = $this->getFileType();
				$fileSize = $this->getFileSize();
				if (!$this->_checkFileExtensionIsValid($fileType)) {
					$this->uploadResponse('failure');
				}
				if (!$this->_checkFileSizeIsValid($fileSize)) {
					$this->uploadResponse('failure');
				}
			}
		} else {
			return false;
		}
		return true;
	}
	//check file size is valid
	public function _checkFileSizeIsValid($fileSize)
	{
		return ($fileSize <= wp_max_upload_size());
	}
	//check file type is valid
	public function _checkFileExtensionIsValid($fileType)
	{
		$arrTypes = array("application/pdf", "image/gif", "image/jpeg", "image/png", "image/tiff", "image/tiff", "image/heif", "image/heic", "image/heif-sequence", "image/heic-sequence");
		return in_array($fileType, $arrTypes);
	}
}
