<?php

/**
 * XmlApi_Request_User
 */
class XmlApi_Request_UserRequestStatus extends Utility_XmlApiTransaction
{
	public function _prepareData($data)
	{
		$momex = ALIAS_MOMEX;
		$tr = ALIAS_TR;
		$transaction = $this->_beginTransaction('UserStatus');

		$users = $this->_prepareNode($transaction, "$tr:users");

		foreach ($data as $user) {
			if (isset($user->id) && !empty($user->id)) {
				$this->_prepareNode($users, "$tr:user", null, array("$momex:id" => $user->id));
			} else {
				$this->_prepareNode($users, "$tr:user", null, array("$momex:username" => $user->username));
			}
		}
	}

	public function _parse($xml_content)
	{
		if ($this->_getStatus() == XML_STATUS_SUCCESS) {
			// new parse object
			$objParse = new XmlApi_ParseData_UserRequestStatus($xml_content);
		} else {
			// new parse object
			$objParse = new XmlApi_ParseData_Message($xml_content);
		}
		$retObject = $objParse->process();
		return $retObject;
	}
}
