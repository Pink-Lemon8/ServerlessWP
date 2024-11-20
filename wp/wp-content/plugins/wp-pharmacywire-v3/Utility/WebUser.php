<?php
define("WEB_USER", 'Patient');
define("WEB_USERNAME", 'Patient_Username');
define("WEB_USER_INFOR", 'patient_info');

class WebUser
{
	public static function logOut()
	{
		self::intWebUser();
		$_SESSION[WEB_USER] = 0;
		$_SESSION[WEB_USER_INFOR] = null;
	}
	public static function setUserID($user_id)
	{
		self::intWebUser();
		$_SESSION[WEB_USER] = $user_id;
	}
	// Check user have loged ?
	public static function isLoggedIn()
	{
		$bResult = false;
		self::intWebUser();
		if ($_SESSION[WEB_USER] > 0) {
			$bResult = true;
		}
		return $bResult;
	}
	// get Current User ID have loged to the system
	public static function getUserID()
	{
		self::intWebUser();
		return (int)$_SESSION[WEB_USER];
	}
	//set User name
	public static function setUserName($username)
	{
		self::intWebUser();
		$_SESSION[WEB_USERNAME] = $username;
	}
	//set User name
	public static function getUserName()
	{
		self::intWebUser();
		return $_SESSION[WEB_USERNAME];
	}
	private static function intWebUser()
	{
		if (empty($_SESSION[WEB_USER])) {
			$_SESSION[WEB_USER] = 0;
			$_SESSION[WEB_USERNAME] = "";
			$_SESSION[WEB_USER_INFOR] = null;
		}
	}
	public static function getUserInfo()
	{
		$patientResult = new Model_Entity_Patient();
		if ($_SESSION[WEB_USER_INFOR] == null) {
			$patientModel = new Model_Patient();
			$patient = new Model_Entity_Patient();
			$patient->patientid = self::getUserID();
			$infor = $patientModel->getPatientInfo($patient);

			$_SESSION[WEB_USER_INFOR] = serialize($infor->patient);
			$patientResult = $infor->patient;
		} else {
			$patientResult  =  unserialize($_SESSION[WEB_USER_INFOR]);
		}

		return $patientResult;
	}
	public static function getFullName()
	{
		$strResult = "";
		$patient = self::getUserInfo();
		if ($patient != null) {
			$strResult = $patient->getFirstname() . ' ' . $patient->getLastname();
			return $strResult;
		}
	}
	public static function setLogin($user)
	{
		self::logOut();
		self::setUserID($user->id);
		self::setUserName($user->name);
		$userInfor = self::getUserInfo();
		$address = $userInfor->address;

		$shippingO = new Shipping;
		$shippingO->updateAddress($userInfor->default_delivery_address_id);

		Billing::setInfor($address->address1, $address->address2, $address->address3, $address->city, $address->province, $address->country, $address->postalcode, $userInfor->areacode_day, $userInfor->phone_day, null, null, null, null, null, null, $userInfor->firstname, $userInfor->lastname);

		$_SESSION['Account_phoneAreaCode'] = $userInfor->areacode;
		$_SESSION['Account_phone'] = $userInfor->phone;
	}

	// Temporary User ID store for changing password, etc. without setting up full userinfo
	public static function setTempUserID($user_id)
	{
		self::intWebUser();
		$_SESSION['TEMP_WEB_USER'] = $user_id;
	}
	public static function setTempUserName($username)
	{
		self::intWebUser();
		$_SESSION['TEMP_WEB_USERNAME'] = $username;
	}
	public static function getTempUserID()
	{
		self::intWebUser();
		if (isset($_SESSION['TEMP_WEB_USER'])) {
			return $_SESSION['TEMP_WEB_USER'];
		}
		return 0;
	}
	public static function getTempUserName()
	{
		self::intWebUser();
		return $_SESSION['TEMP_WEB_USERNAME'];
	}
	public static function isTempLogin()
	{
		$bResult = false;
		self::intWebUser();
		if (self::getTempUserID() > 0) {
			$bResult = true;
		}
		return $bResult;
	}
	public static function removeTempLoginInfo()
	{
		unset($_SESSION['TEMP_WEB_USER']);
		unset($_SESSION['TEMP_WEB_USERNAME']);
	}
}
