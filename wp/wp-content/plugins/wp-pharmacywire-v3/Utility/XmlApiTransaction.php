<?php
	/**
	* Utility_XmlApiTransaction
	*/

	include_once("XmlTransactionHelper.php");

	class Utility_XmlApiTransaction implements JsonSerializable
	{
		/**
		* Code of xml file in xml-templete folders
		* @var mixed
		*/
		protected $_xml_code = null ;
		
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
		* @return Utility_XmlApiTransaction
		*/
/*		 public function __construct()
		{
		} */
		
		/**
		* Process data
		* @param mixed $data
		*/
		public function process($data)
		{
			$int_result =-1;
			
			// prepare data
			$this->_prepareData($data);

			// 2. request
			if (get_option('pw_debug_mode', 0) == 1) {
				error_log('xml request: ' . print_r($this->_xml_request->asXML(), 1));
			}

			$xml_content = $this->_send($this->_xml_request->asXML());

			if (get_option('pw_debug_mode', 0) == 1) {
				error_log('xml response: ' . print_r($xml_content, 1));
			}

			//$xml_content = file_get_contents('pharmacy.xml', true);
			// 3. set data
			$this->_xml_returned = $xml_content ;
			// return status of request
			$int_result = $this->_getStatus();
			
			// return value
			return $int_result ;
		}
		
		public function _prepareData($data)
		{
			// call child implementation
			$this->_prepareData($data);
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
			return $objReturn ;
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
		public function _beginTransaction($type, $attributes = null)
		{
			$this->_xml_request = beginTransaction('true',$type, VENDOR_USERNAME, VENDOR_PASSKEY, $attributes);
			return '/'.ALIAS_TR.':transaction';
		}
		
		public function _prepareNode($path = null, $key, $value = null, $attributes = null, $cdata = false) {
			$xml = null;
			if (!$path) {
				$xml = $this->_xml_request;
				$base = '/'.ALIAS_TR.':transaction';
			} else {
				$xml = $this->_xml_request->xpath($path);
				$base = $path;
			}
			$base = $base.'/'.$key;
			if (is_array($xml)) {
				foreach ($xml as $node) {
					prepareNode($node, $key, $value, $attributes, $cdata);				
				}
			} else {
				prepareNode($xml, $key, $value, $attributes, $cdata);				
			}
			return $base;
		}

		/**
		* Send data to PharmacyWire  system
		* @param mixed $xml_data
		*/
		public function _send($xml_data)
		{
			$strResult = "";
			// Initial Utility_TransferData object
			$objTranfer = new Utility_TransferData();
			// Call send method of Utility_TransferData object
			$objTranfer->send($xml_data) ;
			// get content after execute request to site
			$strResult  = $objTranfer->getContent();
			// return result
			return $strResult ;
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
					} catch(Exception $e) {
						$this->_status = XML_STATUS_INVALID;
					}
					if(count(libxml_get_errors()) > 0) {
						error_log('invalid XML response.');
					}				   

					// Tidy up.
					libxml_clear_errors();
					libxml_use_internal_errors($prev);
				}
			}
			return $this->_status;
		}
	}
