<?php
include_once(ABSPATH . 'wp-admin/includes/plugin.php');

class Page_Checkout_Thank extends Utility_PageBase
{
	/**
	 * Make the html output
	 *
	 * @return The content in html format
	 */
	public function _process()
	{
		$this->checkExistOrder();
		$this->processThank();

		function sendGoogleAnalytics()
		{
			$sendGoogleAnalytics = '';
			$sendGoogleAnalytics = '<script type="text/javascript">

				jQuery(document).ready(function() {
					// Stuff to do as soon as the DOM is ready;
					if ( window.sendGoogleAnalytics ) { sendGoogleAnalytics(); }
				});
				</script>';

			echo $sendGoogleAnalytics;
		}

		add_action('wp_head', 'sendGoogleAnalytics');
	}

	// process when user on billing tab
	public function processThank()
	{
		$this->setTemplate("page_checkout_thank");

		$this->setupAddress();

		$action = (string) $this->_getRequest('action');
		$action = strtoupper(trim($action));

		$orderIDs = Cart::getLastOrder();

		$userInfor 	= WebUser::getUserInfo();

		$patientModel = new Model_Patient();
		$patient = new Model_Entity_Patient();
		$patient->patientid = WebUser::getUserID();

		$orders = new Model_Entity_Order();
		$orders = $patientModel->getOrders($patient->patientid, $orderIDs[0]);
		$order = $orders[0];

		foreach ($orders->getData('orders') as $order) {
			$total = $order->getData('grandtotal');
		}

		$this->assign('ORDER_ID', $orderIDs[0]);
		$this->assign('ORDER_EMAIL', $userInfor->email);
		$this->assign('ORDER_GRANDTOTAL', number_format($total, 2));

		switch ($action) {

			case 'SUBMIT':
				$status = $this->saveComments();

				if ($status) {
					$orderUrl = '<a class="order-id view-order  view-recent-order" href="' . PC_getViewOrderUrl() . '?orderid=' . $orderIDs[0] . '" order-id="' . $orderIDs[0] . '">' . $orderIDs[0] . '</a>';

					$this->setupAddress();

					$this->assign('ORDER_NUMBER', $orderUrl);
					$this->parse('SUCCESS');

					Cart::clearLastOrder();
				}

				break;
			default:
				// default 'faxed' for sending prescriptions
				$this->assign("SCRIPT_2", 'checked="checked"');
		}

		$this->diplayPrescriptionComments();
	}

	/**
	 *
	 * take an array of key-value pairs and turn them into a url query string
	 */
	public function local_http_build_str($queryPartsArr)
	{
		$queryArr = array();

		foreach ($queryPartsArr as $key => $value) {
			$queryArr[] = urlencode($key) . '=' . urlencode($value);
		}

		return join('&', $queryArr);
	}

	/**
	 *
	 * take a url and append a query string to the end of the url while checking to see if a '?' or '&' is needed.
	 */
	public function local_http_build_url_append_query($url, $query)
	{
		$url_new = $url;
		if (preg_match('/\?.+/', $url_new)) {
			$url_new = $url_new . '&';
		} elseif (preg_match('/\?$/', $url_new)) {
			1;
		} else {
			$url_new = $url_new . '?';
		}

		return $url_new . $query;
	}
	/**
	 *
	 * Display PRECRIPTION section when the order has one or more product have prescription requirement
	 */
	public function diplayPrescriptionComments()
	{
		if (Cart::getPrescriptionStatus()) {
			$this->parse('COMMENT.PRESCRIPTION');
		}
		if ((Cart::getLastOrder() !== null) && count(Cart::getLastOrder()) > 0) {
			$this->parse('COMMENT');
		}
	}
	/**
	 *
	 * Save comments
	 */
	public function saveComments()
	{
		$bResult = true;
		$prescription 	= (int) $this->_getRequest('presription');
		$messages = new Utility_Messages;

		if ($prescription) {
			$this->assign("SCRIPT_$prescription", 'checked="checked"');
		}
		// Check if the user is uploading a document, and process it if so
		if ($prescription == 5) {
			if ($this->validData($messages)) {
				$bResult = $this->saveDocument($messages);
			} else {
				$bResult = false;
			}
		}
		$preComment		= $this->getPrescriptionContent($prescription);
		$comment = (string) $this->_getRequest('comment');
		$comment = trim($comment);
		$this->assign('COMMENTS', $comment);
		$orderIDs = Cart::getLastOrder();
		foreach ($orderIDs as $orderID) {
			// Check, if comment <> empty then execute save comments to order.
			if ($comment != "") {
				if ($bResult) {
					$bResult = $this->_saveComment(WebUser::getUserID(), $orderID, $comment);
				}
			}
			//Check, if preComment <> empty then execute save comments to order.
			if ($preComment != "") {
				if ($bResult) {
					/* 						$bResult = $this->_saveComment(WebUser::getUserID(), $orderID, $preComment); */
					//						_adjustOrder($orderID, $prescripton_comment, $child_resistant_packaging, $contact_patient)
					//						$prescripton_comment is a free form text
					//						$child_resistant_packaging is a 'Yes' or 'No'
					//						$contact_patient is a 'Yes' or 'No'
					//						using a null sting '' will result in the existings value to stay unchanged.
					$bResult = $this->_adjustOrder($orderID, $preComment, '', '');
				}
			}
		}
		//in the case, the comment and precomment is empty, then display error
		if ((trim($comment) == "") and (trim($preComment) == "")) {
			$bResult = false;
		}
		return $bResult;
	}
	/**
	 *
	 * Save comment to order
	 * @param int $patientID
	 * @param int $orderID
	 * @param string $comment
	 */
	public function _saveComment($patientID, $orderID, $comment)
	{
		$bResult = false;
		if (!isset($data)) {
			$data = new stdClass();
		}
		$data->patientID 		= $patientID;
		$data->orderID 			= $orderID;
		$data->orderComment	= $comment;
		$orderModel = new Model_Order();
		$save = $orderModel->addComment($data);
		// check if reply is failure
		$this->displayErrorRequest($save);

		if ($save->status == "success") {
			$bResult = true;
		} else {
			//dispaly error;
		}
		return $bResult;
	}

	/**
	 *
	 * Save comment to order
	 * @param int    $orderID
	 * @param string $prescripton_comment is a free form text
	 * @param string $child_resistant_packaging is a 'Yes' or 'No'
	 * @param string $contact_patient is a 'Yes' or 'No'
	 * Note: using a null sting '' will result in the existings value to stay unchanged.
	 */
	public function _adjustOrder($orderID, $prescripton_comment, $child_resistant_packaging, $contact_patient)
	{
		$request_adjustment = new XmlApi_Request_AdjustOrder();

		$bResult = false;

		if (!isset($data)) {
			$data = new stdClass();
		}
		$data->orderID = $orderID;
		if (isset($prescripton_comment) && $prescripton_comment !== '') {
			$data->rx_forwarding = $prescripton_comment;
		}
		if (isset($child_resistant_packaging) && $child_resistant_packaging !== '') {
			$data->child_resistant_packaging = $child_resistant_packaging;
		}
		if (isset($contact_patient) && $contact_patient !== '') {
			$data->contact_patient = $contact_patient;
		}

		$results = $request_adjustment->process($data);
		// check if reply is failure
		$this->displayErrorRequest($results);

		if ($results == "success") {
			$bResult = true;
		} else {
			//dispaly error;
		}
		return $bResult;
	}

	/**
	 *
	 * Get Prescription's content by index
	 * @param int $index
	 */
	public function getPrescriptionContent($index)
	{
		$arrContent[0] = "";
		$arrContent[1] = "My prescription is already on file";
		$arrContent[2] = "I will fax you my prescription ";
		$arrContent[3] = "I will mail you my prescription";
		$arrContent[4] = "Contact me to arrange - ";
		$arrContent[5] = "I will upload my prescription";
		$strContent = $arrContent[$index];
		return $strContent;
	}
	public function checkExistOrder()
	{
		$lastOrder = Cart::getLastOrder();
		if (!(Cart::getLastOrder())) {
			$url = PC_getHomePageURL();
			$this->redirect($url);
		}
	}

	public function setupAddress()
	{
		$this->assign("PW_NAME", get_option('pw_name'));
		$this->assign("PW_ADDRESS", get_option('pw_address'));
		$this->assign("PW_POSTAL_CODE", get_option('pw_postal_code'));
		$this->assign("PW_CITY", get_option('pw_city'));
		$this->assign("PW_PROVINCE", get_option('pw_province'));
		$this->assign("PW_COUNTRY", get_option('pw_country'));

		$this->assign("PW_FAX_AREA", get_option('pw_fax_area'));
		$this->assign("PW_FAX", get_option('pw_fax'));
		$this->assign("PW_PHONE_AREA", get_option('pw_phone_area'));
		$this->assign("PW_PHONE", get_option('pw_phone'));
	}

	//get content of file then save file to server
	public function saveDocument($messages)
	{
		//get user id
		$user_id = WebUser::getUserID();
		//get file name
		$fileName = $this->getFileName();
		//get file type
		$fileType = $this->getFileType();
		//convert file to string binary
		$stringBinary = $this->getStringBinaryByFile();

		$mUpload  = new Model_UploadDocument();
		// send document to server
		$reply = $mUpload->uploadDocument($user_id, $fileName, $fileType, $stringBinary);
		//show messega is successful
		if (Utility_Common::isReplySuccess($reply)) {
			$messages->setNotification('Information', "Your prescription document $fileName was successfully uploaded");
			return true;
		} else {
			$this->setMessageRequest($messages, $reply);
			return false;
		}
	}
	public function setMessageRequest($messages, $reply)
	{
		if (count($reply->messages)) {
			foreach ($reply->messages as $message) {
				$messages->setNotification('Error', $message->content);
			}
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
	public function validData($messages)
	{
		$result = true;
		if ($_FILES["file"]["error"] > 0) {
			$messages->setNotification('Error', 'File must be selected when choosing to upload a prescription document');
			$result = false;
		} else {
			$fileType = $this->getFileType();
			$fileSize = $this->getFileSize();
			if (!$this->_checkFileExtensionIsValid($fileType)) {
				$messages->setNotification('Error', 'Invalid file format submitted - only pdf, gif, jpg, png and tif are allowed.');
				$result = false;
			}
			if (!$this->_checkFileSizeIsValid($fileSize)) {
				$docSize = wp_max_upload_size() / (1024 * 1024) . 'MB';
				$messages->setNotification('Error', 'The maximum allowed size for your prescription document is $docSize. Please reduce the file size, or use an alternative method to send us your prescription document.');
				$result = false;
			}
		}

		return $result;
	}
	//check file size is valid
	public function _checkFileSizeIsValid($fileSize)
	{
		return ($fileSize <= wp_max_upload_size());
	}
	//check file type is valid
	public function _checkFileExtensionIsValid($fileType)
	{
		$arrTypes = array("application/pdf", "image/gif", "image/jpeg", "image/png", "image/tiff",);
		return in_array($fileType, $arrTypes);
	}
}
