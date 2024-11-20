<?php
define("CHECK_OUT_STATUS", 'Checkout_status');
class CheckoutStatus
{
	public static function setSatus($status)
	{
		self::intStatus();
		$_SESSION[CHECK_OUT_STATUS] = strtoupper($status);
	}
	public static function getCurrentStatus()
	{
		return $_SESSION[CHECK_OUT_STATUS];
	}
	public static function getListLink()
	{
		$status = self::getCurrentStatus();
		$strResult = "";
		switch ($status) {

			case "SHIPPING":
				break;
			case "BILLING":
				break;
			case "CONFIRM":
				break;
			case "THANKYOU":
				break;
			default:

				break;
		}
		return $strResult;
	}
	public function genLinksForCart()
	{
		$strResult = "";

		return $strResult;
	}
	private static function intStatus()
	{
		if ($_SESSION[CHECK_OUT_STATUS] == null) {
			$_SESSION[CHECK_OUT_STATUS] = "CART";
		}
	}
}
