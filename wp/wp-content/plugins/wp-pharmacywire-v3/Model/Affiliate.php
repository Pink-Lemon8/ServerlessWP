<?php

/**
 * Model_Affiliate
 */
class Model_Affiliate extends Utility_ModelBase
{
	/**
	 * constructor of the handler - initialises Memcached object
	 *
	 * @return bool
	 */
	public function __construct($affiliateCode)
	{
		$this->memcache = new Utility_Memcached;
		$this->lifeTime = intval(3600 * 12);

		try {
			return $this->affiliateExists($affiliateCode);
		} catch (Exception $e) {
			return null;
		}

		return true;
	}

	/**
	 * Requesting an Affiliate Info
	 *
	 * @param mixed $patient Model_Entity_Affiliate
	 * @return Model_Entity_Reply
	 */
	public function getAffiliates()
	{
		$patientId = WebUser::getUserID();
		// Check if affiliate exists in memcache server
		$searchKey = $_SERVER['SERVER_NAME'] . ':' . $patientId . ":getAffiliates";
		$reply = $this->memcache->get($searchKey);
		if ($reply) {
			return $reply;
		}

		// create the returned object
		$reply = new Model_Entity_Reply();

		// prepare data to execute XML request
		$data = new stdClass();
		// commenting out Sep 16 2020 - $affiliate not defined
		// $data = $affiliate;

		// create the request via XmlApi Request
		$affiliateRequest = new XmlApi_Request_GetAffiliates();
		$affiliateRequest->process($data);

		$reply = $affiliateRequest->getData();

		// return result
		return $reply;
	}

	// basic validation rules for affiliate codes
	public function isValidAffiliate($affiliateCode)
	{

		// alphanumeric, length 1-10
		if (preg_match('/^[A-Za-z0-9_]{1,10}$/', $affiliateCode)) {
			return true;
		}

		return false;
	}

	// Submission to XMLConnect to see if affiliate exists
	public function affiliateExists($affiliateCode)
	{
		if ($this->isValidAffiliate($affiliateCode)) {
			$affiliateInfo = $this->getAffiliates();

			if (!empty($affiliateInfo['affiliates'])) {
				foreach ($affiliateInfo['affiliates'] as $aff) {
					if ($aff['affiliate_code'] == $affiliateCode) {
						$this->affiliate_code = $aff['affiliate_code'];
						$this->id = $aff['id'];
						$this->agent_id = $aff['agent_id'];

						$this->saveToSession();

						return $this;
					}
				}
			}
		}

		throw new Exception('No affiliate found.');

		return false;
	}

	public function saveToSession()
	{
		$_SESSION['affiliate_code'] = $this->affiliate_code;
		$_SESSION['affiliate_id'] = $this->id;
		$_SESSION['agent_id'] = $this->agent_id;
	}

	public static function sessionAffiliateExists()
	{
		if ($affiliate_id = Model_Affiliate::getSessionAffiliateID()) {
			return true;
		}
		return false;
	}

	// get current affiliate code from session
	public static function getSessionAffiliateID()
	{
		if (isset($_SESSION['affiliate_id'])) {
			return $_SESSION['affiliate_id'];
		}
		return false;
	}

	// get current affiliate code from session
	public static function getSessionAffiliateAgentID()
	{
		if (isset($_SESSION['agent_id'])) {
			return $_SESSION['agent_id'];
		}
		return false;
	}

	// get current affiliate code from session
	public static function getSessionAffiliateCode()
	{
		if (isset($_SESSION['affiliate_code'])) {
			return $_SESSION['affiliate_code'];
		}
		return false;
	}

	// Clear out session variables
	public static function resetInfo()
	{
		$_SESSION['affiliate_id'] = null;
		$_SESSION['agent_id'] = null;
		$_SESSION['affiliate_code'] = null;
	}
}
