<?php

/**
 * XmlApi_Request_GetCustomConfig
 */
class XmlApi_Request_GetCustomConfig extends Utility_XmlApiTransaction
{
	public function _prepareData($data)
	{
		// First, do an XML request to get the appropriate agent
		$newData = new stdClass();
		$requestAgents = new XmlApi_Request_GetAgents();
		$requestAgents->process($newData);
		$getAgentsReply = $requestAgents->getData();
		
		// Use agent ID for GetAgentInfo request
		$momex = ALIAS_MOMEX;
		$tr = ALIAS_TR;
		$transaction = $this->_beginTransaction('GetAgentInfo');
		
		$xmlconnectID = get_option('pw_user_id');
		$userid = substr($xmlconnectID, strpos($xmlconnectID, '_') + 1);
		if ($userid && !empty($getAgentsReply) && $getAgentsReply->agents[$userid]) {
			$this->_prepareNode($transaction, "$momex:agent", null, array("$momex:id" => $getAgentsReply->agents[$userid]));
		}
	}

	public function _parse($xml_content)
	{
		if ($this->_getStatus() == XML_STATUS_SUCCESS) {
			$objParse = new XmlApi_ParseData_GetCustomConfig($xml_content);
		} else {
			$objParse = new XmlApi_ParseData_Message($xml_content);
		}
		$retObject = $objParse->process();
		return $retObject;
	}
}
