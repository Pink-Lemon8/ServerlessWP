<?php

/**
 * Model_User
 */
class Model_User extends Utility_ModelBase
{
	/**
	 * Checking User Status
	 *
	 * @param mixed $requestUsers List of Model_Entity_User
	 * @return Model_Entity_Reply
	 */
	public function requestStatus($requestUsers)
	{
		$reply = new Model_Entity_Reply();

		// prepare data to execute XML request

		// create the request via XmlApi Request
		$userRequest = new XmlApi_Request_UserRequestStatus();
		$userRequest->process($requestUsers);

		// return result
		$reply = $userRequest->getData();

		return $reply;
	}
}
