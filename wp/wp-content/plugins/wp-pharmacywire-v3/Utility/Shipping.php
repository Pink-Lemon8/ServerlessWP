<?php
define("SHIPPING_INFOR", 'Shipping_infor');
define("SHIPPING_ADDRESS_REFER", 'Shipping_refer');
class Shipping
{
	public static function setInfor($firstname = "", $lastName = "", $address1 = "", $address2 = "", $address3 = "", $province = "", $country = "", $postalcode, $areacode = "", $areacode_day = '', $phone = "", $phoneday = "", $city = "", $areaCodeFax = "", $fax = "", $description = "")
	{
		$infor = new stdClass();
		$infor->address1		= $address1;
		$infor->address2		= $address2;
		$infor->address3		= $address3;
		$infor->city			= $city;
		$infor->province		= $province;
		$infor->country			= $country;
		$infor->postalcode		= $postalcode;
		$infor->regioncode		= $postalcode;
		$infor->phone           = $phone;
		if (empty($phone) && !empty($_SESSION['Account_phone'])) {
			$infor->phone = $_SESSION['Account_phone'];
		}
		$infor->areacode        = $areacode;
		if (empty($areacode) && !empty($_SESSION['Account_phoneAreaCode'])) {
			$infor->areacode = $_SESSION['Account_phoneAreaCode'];
		}
		$infor->phone_day		= $phoneday;
		$infor->areacode_day	= $areacode_day;
		$infor->areacodefax		= $areaCodeFax;
		$infor->fax				= $fax;
		$infor->description 	= $description;

		if (WebUser::isLoggedIn()) {
			$userInfor = WebUser::getUserInfo();
			$infor->firstname 	= $userInfor->firstname;
			$infor->lastname 	= $userInfor->lastname;
		} else {
			$infor->firstname 	= $firstname;
			$infor->lastname 	= $lastName;
		}

		$_SESSION[SHIPPING_INFOR]	= $infor;
	}
	public static function setAddressRef($ref)
	{
		$_SESSION[SHIPPING_ADDRESS_REFER] = $ref;
	}
	// remove item out cart
	public static function getInfo()
	{
		self::intShipping();
		return $_SESSION[SHIPPING_INFOR];
	}

	public static function getShippingJSON($success = true, $message = '')
	{
		$result = new stdClass();
		$result->shipping_info = self::getInfo();
		$result->info = $_SESSION;
		$result->success = $success ? 1 : 0;
		$result->message = $message;
		return json_encode($result);
	}

	public static function getAddressRef()
	{
		if (!isset($_SESSION[SHIPPING_ADDRESS_REFER])) {
			$_SESSION[SHIPPING_ADDRESS_REFER] = 0;
		}
		return $_SESSION[SHIPPING_ADDRESS_REFER];
	}
	private static function intShipping()
	{
		if (!isset($_SESSION[SHIPPING_INFOR])) {
			$infor = new stdClass();
			$infor->address1	= '';
			$infor->address2	= '';
			$infor->address3	= '';
			$infor->city		= '';
			$infor->province	= '';
			$infor->country		= 'USA';
			$infor->postalcode	= '';
			$infor->phone		= '';
			$infor->phone_day	= '';
			$infor->areacode	= '';
			$infor->areacode_day = '';
			$infor->firstname   = '';
			$infor->lastname	= '';
			$infor->areacodefax = '';
			$infor->fax         = '';
			$infor->description = '';

			$_SESSION[SHIPPING_INFOR] = $infor;
		}
		if (!isset($_SESSION[SHIPPING_ADDRESS_REFER])) {
			$_SESSION[SHIPPING_ADDRESS_REFER] = 0;
		}
	}
	public static function resetInfo()
	{
		$_SESSION[SHIPPING_ADDRESS_REFER] = 0;
		$_SESSION[SHIPPING_INFOR] = null;
	}

	public static function getAddessByAddressID($id)
	{
		$addResult = null;
		$patientModel = new Model_Patient();
		if (!isset($patient)) {
			$patient = new Model_Entity_Patient();
		}
		$patient->patientid = WebUser::getUserID();
		$lstAddress = $patientModel->getShippingAddresses($patient)->address;

		if (is_array($lstAddress)) {
			foreach ($lstAddress as $item) {
				if ($item->id == $id) {
					$addResult = $item;
					break;
				}
			}
			return $addResult;
		}
	}

	/**
	 *
	 * Update Address to Session variable
	 */
	public static function updateAddress($AddressID)
	{
		$userInfor = WebUser::getUserInfo();
		$shipping = self::getInfo();
		if ($AddressID == 0) {
			$address = $userInfor->address;
			$shipping->address1		= $address->address1;
			$shipping->address2		= $address->address2;
			$shipping->address3		= $address->address3;
			$shipping->city			= $address->city;
			$shipping->province		= $address->province;
			$shipping->country		= $address->country;
			$shipping->postalcode	= $address->postalcode;
			$shipping->phone		= $address->phone;
			$shipping->areacode		= $address->areacode;
			$shipping->phone_day	= $userInfor->phone_day;
			$shipping->areacode_day	= $userInfor->areacode_day;
			$shipping->firstname   	= $userInfor->firstname;
			$shipping->lastname		= $userInfor->lastname;
			$shipping->areacodefax  = $userInfor->areacodefax;
			$shipping->fax			= $userInfor->fax;
			$shipping->description	= $userInfor->description;

			self::setAddressRef(0);
			Billing::setUsingShippingAddress(1);
		} else {
			if ($AddressID > 0) {
				$address = self::getAddessByAddressID($AddressID);
				if (!is_null($address)) {
					$shipping->address1		= $address->address1;
					$shipping->address2		= $address->address2;
					$shipping->address3		= $address->address3;
					$shipping->city			= $address->city;
					$shipping->province		= $address->province;
					$shipping->country		= $address->country;
					$shipping->postalcode	= $address->postalcode;
					$shipping->phone		= $address->phone;
					$shipping->areacode		= $address->areacode;
					$shipping->phone_day	= $userInfor->phone_day;
					$shipping->areacode_day	= $userInfor->areacode_day;
					$shipping->firstname   	= $userInfor->firstname;
					$shipping->lastname		= $userInfor->lastname;
					$shipping->areacodefax  = $userInfor->areacodefax;
					$shipping->fax			= $userInfor->fax;
					$shipping->description	= $userInfor->description;

					self::setAddressRef($AddressID);
				}
			}
		}
	}

	public function _validData($data)
	{
		$isValid = true;

		$validationFieldsFailed = array();

		if (empty($data['firstName'])) {
			$isValid = false;
			$validationFieldsFailed['firstName'] = 'First name is a required field.';
		}
		if (empty($data['lastName'])) {
			$isValid = false;
			$validationFieldsFailed['lastName'] = 'Last name is a required field.';
		}
		if (empty($data['Address1'])) {
			$isValid = false;
			$validationFieldsFailed['Address1'] = 'Address is a required field.';
		}
		if (empty($data['City'])) {
			$isValid = false;
			$validationFieldsFailed['City'] = 'City is a required field.';
		}
		if (empty($data['PostalCode'])) {
			$isValid = false;
			$validationFieldsFailed['PostalCode'] = 'Postal code is a required field.';
		}
		if (empty($data['phoneAreaCode'])) {
			$isValid = false;
			$validationFieldsFailed['phoneAreaCode'] = 'phone area code is a required field.';
		}

		$response = array('valid' => $isValid, 'validation-errors' => $validationFieldsFailed);

		return $response;
	}

	public static function watchForCheck($shippingOption, $lstItems = null)
	{
		$watchForConditionsMet = false;
		if (!empty($shippingOption['watch_for'])) {
			if (empty($lstItems)) {
				$lstItems = Cart::getListItems();
			}
			$watchFor = $shippingOption['watch_for'];
			$watchRule = $shippingOption['watch_rule'] ?? 'match_or';
			$watchMatchCount = 0;
			foreach ($watchFor as $watch) {
				if (!empty($watch['type'])) {
					if (isset($watch['type']) && ($watch['type'] == 'tag')) {
						if (isset($watch['content']['name'])) {
							$lineItemAttributes = Cart::getLineItemAttributes();
							if (in_array($watch['content']['name'], $lineItemAttributes)) {
								$watchForConditionsMet = true;
								$watchMatchCount++;
							}
						}
					} elseif (isset($watch['type']) && ($watch['type'] == 'country')) {
						if (isset($watch['content']['code'])) {
							$validCountries = $watch['content']['code'];
							if (isset($watch['match']) && ($watch['match'] == 'any')) {
								if (Cart::containsAnItemFromCountry($lstItems, $validCountries)) {
									$watchForConditionsMet = true;
									$watchMatchCount++;
								}
							} else {
								if (Cart::containsItemsFromCountry($lstItems, $validCountries)) {
									$watchForConditionsMet = true;
									$watchMatchCount++;
								}
							}
						}
					}
				}
			}
			if ($watchRule == 'match_all') {						
				if ($watchMatchCount != count((array) $watchFor)) {
					$watchForConditionsMet = false;
				}
			}
		}
		return (bool) $watchForConditionsMet;
	}

	public static function shippingOptionVisibility($shippingOption, $lstItems = null)
	{
		if (empty($lstItems)) {
			$lstItems = Cart::getListItems();
		}

		$visible = true;

		switch ($shippingOption['visibility']) {
			case 'always':
				// $visible = true;
				break;
			case 'express':
				$visible = false;
				// follow express shipping rules for visibility
				$Canadian = Cart::containsCanadianItems($lstItems);
				$internationalExpress = get_option('pw_international_express_allowed');
				$shippingDestination = Cart::getShippingDestination();

				if (!CART::isExpressShippingDisabled() && ($Canadian || $internationalExpress) && ($shippingDestination == 'USA' || $shippingDestination == 'CAN' || !strlen($shippingDestination))) {
					$visible = true;
				}
				break;
			case 'watch':
				$visible = Shipping::watchForCheck($shippingOption, $lstItems);
				break;
			default:
				// $visible = true;
		}

		return (bool) $visible;
	}

	// $callingPackageID - to prevent recursive calls
	public static function updateShippingProductAddons($callingPackageID = null, $lstItems = null) {
		$pwShippingExtendedJSON = get_option('pw_shipping_extended');
		if (!empty($pwShippingExtendedJSON) && is_json($pwShippingExtendedJSON)) {
			$pwShippingExtended = json_decode($pwShippingExtendedJSON, true);
			$shippingProductAddonKeys = array_keys(array_combine(array_keys($pwShippingExtended), array_column($pwShippingExtended, 'type')), 'product_addon');
			if (!empty($shippingProductAddonKeys)) {
				$lstItems = $lstItems ?? cart::getListItems();
				$shippingExtendedError = 0;
				foreach ($shippingProductAddonKeys as $key) {
					$shippingO = $pwShippingExtended[$key];
					$watchForCheck = Shipping::watchForCheck($shippingO, $lstItems);
					$productAddonPkgID = $shippingO['package_id'] ?? null;
					$productAddonPkgID = $shippingO['product_id'] ?? $productAddonPkgID; // alias
					
					if (!empty($productAddonPkgID) && (preg_match('/(DP-\d+)(,\s*DP-\d+)*/i', $productAddonPkgID))) {
						$productAddons = explode(',', $productAddonPkgID);
						foreach($productAddons as $addonPkgID) {
							if ($callingPackageID == $addonPkgID) {
								continue; // prevent recursive calls
							}
							if (($watchForCheck == true) && !empty($addonPkgID)) {
								if (!cart::isPackageInCart($addonPkgID)) {
									cart::add($addonPkgID, 1);
								} else {
									// if product in cart, make sure qty is correct
									// currently hardcoded to 1
									cart::update($addonPkgID, 1);	
								}
								cart::addCartMandatoryProduct($addonPkgID, 'shipping option - product addon');
							} else if (($watchForCheck != true) && !empty($addonPkgID)) {
								if (cart::isPackageInCart($addonPkgID)) {
									cart::removeCartMandatoryProduct($addonPkgID);
								}
							}
						}
					} else {
						$shippingExtendedError = 1;
					}
				}
				if ($shippingExtendedError) {
					error_log('Invalid/missing package_id in wp_options pw_shipping_extended on package_addon entry: ' . $key);
				}
			}
		}
	}

	public static function removeShippingProductAddons($callingPackageID = null, $lstItems = null) {
		$pwShippingExtendedJSON = get_option('pw_shipping_extended');
		if (!empty($pwShippingExtendedJSON) && is_json($pwShippingExtendedJSON)) {
			$pwShippingExtended = json_decode($pwShippingExtendedJSON, true);
			$shippingProductAddonKeys = array_keys(array_combine(array_keys($pwShippingExtended), array_column($pwShippingExtended, 'type')), 'product_addon');
			if (!empty($shippingProductAddonKeys)) {
				$lstItems = $lstItems ?? cart::getListItems();
				foreach ($shippingProductAddonKeys as $key) {
					$shippingO = $pwShippingExtended[$key];
					$productAddonPkgID = $shippingO['package_id'] ?? null;
					$productAddonPkgID = $shippingO['product_id'] ?? $productAddonPkgID; // alias
					
					if (!empty($productAddonPkgID) && (preg_match('/(DP-\d+)(,\s*DP-\d+)*/i', $productAddonPkgID))) {
						$productAddons = explode(',', $productAddonPkgID);
						foreach($productAddons as $addonPkgID) {
							if ($callingPackageID != $addonPkgID) {
								if (cart::isPackageInCart($addonPkgID)) {
									cart::removeCartMandatoryProduct($addonPkgID);
								}
							}
						}
					}
				}
			}
		}
	}

}
