<?php

/**
 * XmlApi_Request_PatientAuthenticateUser
 */
class XmlApi_Request_PatientAuthenticateUser extends Utility_XmlApiTransaction
{
	public function _prepareData($data)
	{
		$tr = ALIAS_TR;
		$momex = ALIAS_MOMEX;

		$transaction = $this->_beginTransaction('AuthenticateUser');
		$users = $this->_prepareNode($transaction, "$tr:users");
		
		if (get_option('pw_v4_legacy_mode', 0) == 1) {
			// V4 use old format, uses attributes to pass values
			// $strTemp ='<user momex:username="{username}" momex:password="{password}"/>';
			foreach ($data as $userItem) {
				$this->_prepareNode($users, "$tr:user", null, array("$momex:username" => $userItem->username, "$momex:password" => $userItem->password));
			}
		} else {
			// new format in V5 as attributes don't support cdata
			// $strTemp = '<user><username><![CDATA[{username}]]></username><password><![CDATA[{password}]]></password></user>';
			foreach ($data as $userItem) {
				$user = $this->_prepareNode($users, "$tr:user");
				$this->_prepareNode($user, "$tr:username", $userItem->username, null, 1);
				$this->_prepareNode($user, "$tr:password", $userItem->password, null, 1);
			}
		}
	}

	public function _parse($xml_content)
	{
		if ($this->_getStatus() == XML_STATUS_SUCCESS) {
			$objParse = new XmlApi_ParseData_PatientAuthenticateUser($xml_content);
		} else {
			$objParse = new XmlApi_ParseData_Message($xml_content);
		}
		$retObject = $objParse->process();
		return $retObject;
	}
}
