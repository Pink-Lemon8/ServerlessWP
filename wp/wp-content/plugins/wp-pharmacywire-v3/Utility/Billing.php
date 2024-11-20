<?php

define("BILLING_INFOR", 'Billing_infor');

class Billing
{
	// add item to cart
	public static function setInfor($Address1 = "", $Address2 = "", $Address3 = "", $City = "", $Province = "", $Country = "", $PostalCode = "", $AreaCode = "", $Phone = "", $MethodType = "", $CreditCardType = "", $CreditCardNumber = "", $CvvNumber = "", $ExpiryMonth = "", $ExpiryYear = "", $firstName = "", $lastName = "", $nameOnCheque = "", $bankName = "", $bankCity = "", $bankState = "", $branchTransit = "", $chequeAccount = "", $chequeNumber = "")
	{
		$infor = $_SESSION[BILLING_INFOR];
		$infor->firstname = $firstName;
		$infor->lastname = $lastName;
		$infor->address1 = $Address1;
		$infor->address2 = $Address2;
		$infor->address3 = $Address3;
		$infor->city = $City;
		$infor->province = $Province;
		$infor->country = $Country;
		$infor->postalcode = $PostalCode;
		$infor->areacode = $AreaCode;
		$infor->phone = $Phone;
		$infor->nameOnCheque = $nameOnCheque;
		$infor->bankName = $bankName;
		$infor->bankCity = $bankCity;
		$infor->bankState = $bankState;
		$infor->branchTransit = $branchTransit;
		$infor->chequeAccount = $chequeAccount;
		$infor->chequeNumber = $chequeNumber;

		$_SESSION[BILLING_INFOR] = $infor;
	}
	public static function setMethodType($type)
	{
		$infor = self::getInfo();
		$infor->methodtype = $type;
		$_SESSION[BILLING_INFOR] = $infor;
	}
	// remove item out cart
	public static function getInfo()
	{
		self::intBilling();
		return $_SESSION[BILLING_INFOR];
	}

	public static function getBillingJSON($success = true, $message = '')
	{
		$result = new stdClass();
		$result->billing_info = self::getInfo();
		$result->info = $_SESSION;
		$result->success = $success ? 1 : 0;
		$result->message = $message;
		return json_encode($result);
	}

	private static function intBilling()
	{
		if (empty($_SESSION[BILLING_INFOR])) {
			$infor = new stdClass();
			$infor->methodtype			= "";
			$infor->creditcardtype		= "";
			$infor->creditcardnumber	= "";
			$infor->cvvnumber			= "";
			$infor->expirymonth			= "";
			$infor->expiryyear			= "";
			$infor->firstname			= "";
			$infor->lastname			= "";
			$infor->address1			= "";
			$infor->address2			= "";
			$infor->address3			= "";
			$infor->city 				= "";
			$infor->province 			= "";
			$infor->country 			= "USA";
			$infor->postalcode 			= "";
			$infor->areacode 			= "";
			$infor->phone 				= "";
			$infor->useShipping 		= true;
			$infor->nameOnCheque		= "";
			$infor->bankName			= "";
			$infor->bankCity			= "";
			$infor->bankState			= "";
			$infor->branchTransit		= "";
			$infor->chequeAccount		= "";
			$infor->chequeNumber		= "";

			$_SESSION[BILLING_INFOR] 	= $infor;
		}
	}

	public static function getUsingShippingAddress()
	{
		$infor = self::getInfo();

		return (int)$infor->useShipping;
	}

	public static function setUsingShippingAddress($status)
	{
		$infor = self::getInfo();
		$infor->useShipping = (int)$status;
		$_SESSION[BILLING_INFOR] = $infor;
	}

	public static function resetInfo()
	{
		$_SESSION[BILLING_INFOR] = null;
	}
}
