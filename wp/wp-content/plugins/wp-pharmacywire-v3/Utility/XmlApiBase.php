<?php

/**
 * Utility_XmlApiBase
 */
class Utility_XmlApiBase implements JsonSerializable
{
	/**
	 * Code of xml file in xml-templete folders
	 * @var mixed
	 */
	protected $_xml_code = null;

	/**
	 * Xml content load from xml-templete folder
	 * @var mixed
	 */
	protected $_xml_request = null;

	/**
	 * The varible contain xml data that returned from PharmacyWire system
	 * @var mixed
	 */
	protected $_xml_returned = null;

	/**
	 * Status from XML response
	 */
	protected $_status = null;

	/**
	 * Contructor method
	 * @param mixed $xml_code
	 * @return Utility_XmlApiBase
	 */
	public function __construct($xml_code)
	{
		$this->_xml_code = $xml_code;
	}

	/**
	 * Process data
	 * @param mixed $data
	 */
	public function process($data)
	{
		$int_result = -1;

		// Load xml template form api-templetes folder
		$this->_xml_request = $this->loadXmlTeplate();

		// prepare data
		$this->_prepareData($data);

		// 2. request
		if (get_option('pw_debug_mode', 0) == 1) {
			error_log('xml request: ' . print_r($this->_xml_request, 1));
		}

		$xml_content = $this->_send($this->_xml_request);

		if (get_option('pw_debug_mode', 0) == 1) {
			error_log('xml response: ' . print_r($xml_content, 1));
		}

		//$xml_content = file_get_contents('pharmacy.xml', true);
		// 3. set data
		$this->_xml_returned = $xml_content;
		// return status of request
		$int_result = $this->_getStatus();

		// return value
		return $int_result;
	}

	/**
	 * Get data after parse xml content that returend  from PharmacyWire system
	 */
	public function getData()
	{
		if (!empty($this->_xml_returned)) {
			$objReturn = $this->_parse($this->_xml_returned);
		} else {
			$objReturn = $this->makeDefaultMessage();
		}
		return $objReturn;
	}

	protected function __toJson(array $arrAttributes = array())
	{
		$arrData = $this->toArray($arrAttributes);
		//$json = Zend_Json::encode($arrData);
		if (function_exists('json_encode')) {
			$encodedResult = json_encode(
				$arrData,
				JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP
			);
		}
		return $encodedResult;
	}

	public function jsonSerialize()
	{
		return $this->toArray();
	}

	/**
	 * Public wrapper for __toJson
	 *
	 * @param array $arrAttributes
	 * @return string
	 */
	public function toJson(array $arrAttributes = array())
	{
		return $this->__toJson($arrAttributes);
	}

	// make default message for the case that have problem happen on pharmacy server
	//set message when server response is error
	public function makeDefaultMessage()
	{
		$reply = new Model_Entity_Reply();
		$reply->status = 'failure';
		$message = new Model_Entity_ReplyMessage();
		$message->content = 'There was an error processing your request on server';
		$messages[] = $message;
		$reply->messages = $messages;
		return $reply;
	}

	/**
	 * Convert object attributes to array
	 *
	 * @param  array $arrAttributes array of required attributes
	 * @return array
	 */
	public function __toArray(array $arrAttributes = array())
	{
		if (empty($arrAttributes)) {
			return $this->_data;
		}

		$arrRes = array();
		foreach ($arrAttributes as $attribute) {
			if (isset($this->_data[$attribute])) {
				$arrRes[$attribute] = $this->_data[$attribute];
			} else {
				$arrRes[$attribute] = null;
			}
		}
		return $arrRes;
	}

	/**
	 * Public wrapper for __toArray
	 *
	 * @param array $arrAttributes
	 * @return array
	 */
	public function toArray(array $arrAttributes = array())
	{
		return $this->__toArray($arrAttributes);
	}


	/**
	 * Parse data
	 * @param mixed $xml_content
	 */
	public function _parse($xml_content)
	{
		return $xml_content;
	}

	/**
	 * Prepare data
	 * @param mixed $data
	 */
	public function _prepareData($data)
	{
		$this->replaceField('username', VENDOR_USERNAME);
		$this->replaceField('password', VENDOR_PASSKEY);
	}

	/**
	 * Send data to harmacyWire  system
	 * @param mixed $xml_data
	 */
	public function _send($xml_data)
	{
		$strResult = "";
		// Initial Utility_TransferData object
		$objTranfer = new Utility_TransferData();
		// Call send method of Utility_TransferData object
		$objTranfer->send($xml_data);
		// get content after execute request to site
		$strResult  = $objTranfer->getContent();

		// return result
		return $strResult;
	}

	/**
	 * Get status of a request
	 */
	public function _getStatus()
	{
		if (is_null($this->_status)) {
			if (!empty($this->_xml_returned)) {
				$prev = libxml_use_internal_errors(true);
				try {
					$docNode = new SimpleXMLElement($this->_xml_returned);
					$this->_status = (string)$docNode->status;
				} catch (Exception $e) {
					$this->_status = XML_STATUS_INVALID;
				}
				if (count(libxml_get_errors()) > 0) {
					error_log('invalid XML response.');
				}

				// Tidy up.
				libxml_clear_errors();
				libxml_use_internal_errors($prev);
			}
		}
		return $this->_status;
	}

	/**
	 * Load XML template
	 */
	public function loadXmlTeplate()
	{
		// create path to folder to load data
		$xml_path = ROOT_PLUGIN . "XmlApi" . SLASH . "XmlTemplate"  . SLASH . $this->_xml_code . ".tpl";
		// load content of file
		$content =  Utility_Common::loadFileContent($xml_path);

		// return value
		return $content;
	}

	/**
	 * Replace field in xml file
	 * @param mixed $fieldName
	 * @param mixed $value
	 */
	public function replaceField($fieldName = 'test', $value, $scrub = 0)
	{
		if ($scrub) {
			$value = htmlentities($value, ENT_QUOTES | ENT_XML1);
		}
		$system_key = "{" . $fieldName  . "}";
		$this->_xml_request = str_replace($system_key, $value, $this->_xml_request);
	}
	// replace data
	public function replaceContent($key, $value, $orignString, $scrub = 0)
	{
		if ($scrub) {
			$value = htmlentities($value, ENT_QUOTES | ENT_XML1);
		}
		$strResult = $orignString;
		$strResult = str_replace($key, $value, $strResult);

		return $strResult;
	}
}
