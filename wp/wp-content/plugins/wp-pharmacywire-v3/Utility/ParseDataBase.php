<?php

/**
 * The base class of parsing data
 */
class Utility_ParseDataBase
{
	//Code of xml file in xml-templete folders
	protected $_xml_content = null;
	protected $_xml_doc = null;
	protected $_debugMode = true;

	// process data
	public function __construct($_xml_content)
	{
		$this->_xml_content = $_xml_content;
	}

	/**
	 * Parse XML data
	 *
	 */
	public function _parseXml()
	{
		$prev = libxml_use_internal_errors(true);
		try {
			$this->_xml_doc = new SimpleXMLElement($this->_xml_content);
			$this->_xml_doc->registerXPathNamespace(XML_PWIRE, XML_NS_PWIRE);
			$this->_xml_doc->registerXPathNamespace(XML_MOMEX, XML_NS_MOMEX);
		} catch (Exception $e) {
			if (!isset($this->_xml_doc)) {
				$this->_xml_doc = new stdClass();
			}
			$this->_xml_doc->status = XML_STATUS_INVALID;
		}
		if (count(libxml_get_errors()) > 0) {
			error_log('invalid XML response.');
		}

		// Tidy up.
		libxml_clear_errors();
		libxml_use_internal_errors($prev);

		$returnObject = null;
		return $returnObject;
	}

	/**
	 * Process parsing
	 *
	 */
	public function process()
	{
		$returnObject = null;
		$xmlContent = $this->_xml_content;
		$returnObject = $this->_parseXml($xmlContent);

		return $returnObject;
	}

	/**
	 * register xpath for namespace
	 *
	 * @param mixed $xmlNode
	 */
	protected function _registerXPathNamespace(&$xmlNode)
	{
		$xmlNode->registerXPathNamespace(XML_PWIRE, XML_NS_PWIRE);
		$xmlNode->registerXPathNamespace(XML_MOMEX, XML_NS_MOMEX);

		return $xmlNode;
	}

	/**
	 * Get the first xml child element by tag name
	 *
	 * @param mixed $node
	 * @param mixed $name
	 * @param mixed $extraCondition
	 * @param mixed $prefix
	 * @return mixed
	 */
	public function _getChildByName($node, $name, $prefix = XML_PWIRE)
	{
		$resultList = $this->_getChildrenByName($node, $name, $prefix);

		if (count($resultList) > 0) {
			return $resultList[0];
		} else {
			return null;
		}
	}

	/**
	 * Get the list of child xml element by tag name
	 *
	 * @param mixed $node
	 * @param mixed $name
	 * @param mixed $prefix
	 */
	public function _getChildrenByName($node, $name, $prefix = XML_PWIRE)
	{
		$this->_registerXPathNamespace($node);

		$xpath = "./{$prefix}:{$name}";

		$resultList = $node->xpath($xpath);

		return $resultList;
	}
}
