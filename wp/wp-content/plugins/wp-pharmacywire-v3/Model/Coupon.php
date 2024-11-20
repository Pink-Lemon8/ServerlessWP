<?php

/**
 * Model_Coupon
 **/
class Model_Coupon extends Utility_ModelBase
{
	public function getCoupon($couponCode, $activeCoupons, $patientID = null)
	{
		// create the returned object
		$reply = new Model_Entity_Reply();

		if (!empty($couponCode)) {
			// prepare data to execute XML request
			$data = new stdClass();
			$data->couponCode = $couponCode;
			$data->patientID = $patientID;
			$data->activeCoupons = $activeCoupons;

			$billingInfo = Billing::getInfo();
			if (!empty($billingInfo->methodtype)) {
				$data->paymentMethod = $billingInfo->methodtype;
				$data->paymentFirstName = $billingInfo->firstname;
				$data->paymentLastName =  $billingInfo->lastname;
			}

			// create the request via XmlApi Request to get list of coupons
			$couponStatus = new XmlApi_Coupon_GetCouponsVerify();

			$couponStatus->process($data);

			// return result
			$reply = $couponStatus->getData();
		}

		return $reply;
	}

	public function applyCoupon($newCouponCode, $patientID = null)
	{
		$couponResults = new stdClass();

		$activeCoupons = '';
		$sessionCoupons = $this->getCouponSession();
		// get active session coupons to validate against new coupon
		if (!empty($sessionCoupons)) {
			$activeCoupons = implode(",", array_keys($sessionCoupons));
		}
		
		$couponResults = $this->getCoupon($newCouponCode, $activeCoupons, $patientID);

		$couponCodes = '';
		if (!empty($couponResults['coupon'])) {
			$couponCodes = array_keys($couponResults['coupon']);
		}

		$couponsResponse['status'] = 'failed';

		// if coupons were found, and new coupon being added is in the list
		if (!empty($couponCodes) && in_array($newCouponCode, $couponCodes)) {
			$coupons = $couponResults['coupon'];

			foreach ($coupons as $couponCode => $couponData) {

				// Validate coupon and if valid for use, save to session and setup json response
				$couponValid = $this->validateCouponData($couponData);

				if ($couponValid) {

					$mandatoryCoupons = explode(',', get_option('pw_coupons_mandatory'));
					// if mandatory site coupon set removable to false
					if (!empty($mandatoryCoupons) && in_array($couponCode, $mandatoryCoupons)) {
						$couponData['removable'] = 'false';
					} elseif (!isset($couponData['removable'])) {
						$couponData['removable'] = 'true';
					}

					// if coupon code is valid and the one just added, setup response
					if ($newCouponCode == $couponCode) {
						$couponsResponse['status'] = 'success';
						$couponsResponse['coupon-code'] = $couponData['coupon-code'];
						$couponsResponse['coupons'][$couponCode]['coupon-code'] = $couponData['coupon-code'];
						$couponsResponse['coupons'][$couponCode]['description'] = $couponData['description'];
						$couponsResponse['coupons'][$couponCode]['status-message'] = $couponData['status-message'];
						$couponsResponse['coupons'][$couponCode]['removable'] = $couponData['removable'];
					}

					$this->setCouponSession($couponCode, $couponData);
				}
			}
		}

		return json_encode($couponsResponse);
	}

	public function applyMandatoryCoupons()
	{
		// add mandatory coupon if they haven't been already
		// and set removable to false (within applyCoupon)
		if (!empty(get_option('pw_coupons_mandatory'))) {
			$mandatoryCoupons = explode(',', get_option('pw_coupons_mandatory'));

			$patientID = WebUser::getUserID();
			foreach ($mandatoryCoupons as $index => $couponCode) {
				if (!empty($couponCode) && (empty($sessionCoupons) || (!array_key_exists($couponCode, $sessionCoupons)))) {
					$this->applyCoupon($couponCode, $patientID);
				}
			}
		}
	}

	public function validateCouponData($couponData)
	{
		$couponValid = true;

		if (empty($couponData['coupon-code'])) {
			$couponValid = false;
		} elseif ($couponData['usable'] == 'false') {
			// pwire:usable handles validation in pwire
			$couponValid = false;
		} elseif ($couponData['is-administrative-coupon'] == 'true') {
			$couponValid = false;
		} elseif (isset($couponData['billing-options']) && !$this->couponValidPaymentMethod($couponData['billing-options'])) {
			$couponValid = false;
		} elseif ($couponData['min-order-amount'] > Cart::getSubTotal()) {
			$couponValid = false;
		}
		return $couponValid;
	}

	public function couponValidPaymentMethod($couponBillingOptions)
	{
		// check to see if the coupon billing options are active on the website, if not return false
		if (is_array($couponBillingOptions)) {
			$patient = new Model_Entity_Patient();
			$patientAttributes = null;
			if (WebUser::getUserID()) {
				$patientInfo = WebUser::getUserInfo();
				$patientAttributes = $patientInfo->attributes;
			}
			$validOptions = array_keys(PC_getPaymentMethodsUnfiltered($patientAttributes));

			// return coupon methods not found in valid website options
			$methodsNotFound = array_diff($couponBillingOptions, $validOptions);
			// if all of the billing methods are not found
			if ($methodsNotFound && (count($methodsNotFound) === count($couponBillingOptions))) {
				return false;
			};
		}
		return true;
	}

	public function revalidateCouponSession($softLookup = 0)
	{

		// revalidate coupons, such as after a user has logged in
		// basically just updates session coupon data with new results pulled with patientID, primarily of importance - the 'usable' field
		// was discussed/decided to leave 'usable' false coupons in session to warn user to remove them

		// $softLookup - don't do full getCoupon XML Requests to pull in new coupon data, validates against current session data for performance

		$sessionCoupons = $this->getCouponSession();

		$patientID = WebUser::getUserID();
		$patientID = !empty($patientID) ? $patientID : null;

		// remove existing coupons from session
		if (!empty($sessionCoupons)) {

			$this->removeCouponSessionAll();

			// refresh with new coupon data acquired with patientID
			foreach ($sessionCoupons as $couponCode => $couponData) {

				// send list of coupons to validate against each other
				$activeCoupons = implode(",", array_keys($sessionCoupons));

				if ($softLookup == 1) {
					// use original session data for validation
					if ($this->validateCouponData($couponData)) {
						$this->setCouponSession($couponCode, $couponData);
					}
				} else {
					// get current/live coupon xml data
					$couponResults = $this->getCoupon($couponCode, $activeCoupons, $patientID);

					if (!empty($couponResults['coupon'])) {
						foreach ($couponResults['coupon'] as $couponValidatedCode => $couponValidatedData) {
							if ($this->validateCouponData($couponValidatedData)) {
								$this->setCouponSession($couponValidatedCode, $couponValidatedData);
							}
						}
					}
				}
			}

			// return updated coupon session
			$sessionCoupons = $this->getCouponSession();
		}

		return $sessionCoupons;
	}

	public function setCouponSession($couponCode, $couponData)
	{
		$_SESSION['coupons'][$couponCode] = $couponData;
	}

	public function getCouponSession($includeAll = 0)
	{
		// default to get coupons valid for display
		// if includeAll set then return all coupons on session, such as including custom admin coupons for checkout

		$validSessionCoupons = array();

		if (!empty($_SESSION['coupons'])) {
			foreach ($_SESSION['coupons'] as $couponCode => $couponData) {
				if ($couponData['is-administrative-coupon'] != 'true' || $includeAll == 1) {
					$couponData['discount-human'] = $this->getDiscountMethodHuman($couponCode);
					$validSessionCoupons[$couponCode] = $couponData;
				}
				
			}
		}

		return $validSessionCoupons;
	}

	public function getCouponSession_PublicInfo() {
		// coupon info with private fields removed
		$sessionCoupons = $this->getCouponSession();

		$publicCouponInfo = array();
		if (!empty($sessionCoupons)) {
			foreach ($sessionCoupons as $couponCode => $couponData) {
				$publicFields = ['comments', 'coupon-code', 'description', 'discount', 'discount-method', 'discount-human', 'expired', 'includes-free-shipping', 'max-discount', 'min-order-amount', 'removable', 'usable', 'use-on-first-order-only'];
				foreach ($publicFields as $pField) {
					$publicCouponInfo[$couponCode][$pField] = $couponData[$pField];
				}
			}		
		}
		return $publicCouponInfo;
	}

	public function removeCouponSession($couponCode)
	{
		$status = 'fail';
		if (!empty($_SESSION['coupons'][$couponCode]) && ($_SESSION['coupons'][$couponCode]['removable'] != 'false')) {
			unset($_SESSION['coupons'][$couponCode]);

			// If after removing individual coupon there are no coupons left in session, unset coupons
			if (!count($_SESSION['coupons'])) {
				unset($_SESSION['coupons']);
			}

			$status = 'success';
		}
		return json_encode(array('status' => $status));
	}

	public function removeCouponSessionAll()
	{
		$status = 'fail';
		if (!empty($_SESSION['coupons'])) {
			unset($_SESSION['coupons']);
			$status = 'success';
		}
		return json_encode(array('status' => $status));
	}

	public function getDiscount($grandTotal, $couponCode = null)
	{
		if (!empty($_SESSION['coupons'])) {
			$totalDiscount = 0;

			if (!empty($couponCode)) {
				$coupons[$couponCode] = $_SESSION['coupons'][$couponCode];
			} else {
				$coupons = $_SESSION['coupons'];
			}

			foreach ($coupons as $couponCode => $couponData) {
				$couponDiscountMethod = strtolower($couponData['discount-method']);
				$maxDiscount = (is_numeric($couponData['max-discount'])) ? $couponData['max-discount'] : null;

				$couponDiscount = (float) $couponData['discount'];
				$minOrderAmount = (float) $couponData['min-order-amount'];

				if (!empty($minOrderAmount) && ($minOrderAmount >= 0.001)) {
					$couponDiscount = ($grandTotal >= $minOrderAmount) ? $couponDiscount : 0;
				}

				if ($couponDiscountMethod == 'percentage') {
					$couponDiscount = $couponDiscount  / 100 * $grandTotal;
				}

				if (($couponData['usable'] == 'true')) {
					if (isset($maxDiscount) && ($couponDiscount > $maxDiscount)) {
						$couponDiscount = $maxDiscount;
					}

					$totalDiscount += $couponDiscount;
				}
			}

			$totalDiscount = round($totalDiscount, 2, PHP_ROUND_HALF_DOWN);

			return $totalDiscount;
		}
		return 0;
	}

	public function getDiscountHuman($grandTotal, $couponCode = null)
	{
		if (!empty($_SESSION['coupons']) && is_numeric($grandTotal)) {
			$couponDiscount = $this->getDiscount($grandTotal, $couponCode);

			if ($couponDiscount == 0) {
				// no discount being applied (such as for free shipping only coupon)
				// show --- for display purpases
				$couponDiscountHuman = '---';
			} else {
				if ($couponDiscount >= 0) {
					$couponDiscountHuman = sprintf('- \$%0.2f', $couponDiscount);
				} else {
					$couponDiscount = str_replace('-', '', $couponDiscount);
					$couponDiscountHuman = sprintf('+ \$%0.2f', $couponDiscount);
				}
			}

			return $couponDiscountHuman;
		}
		return 0;
	}

	public function getDiscountMethodHuman($couponCode)
	{
		if (!empty($_SESSION['coupons'])) {
			$couponDiscountMethod = strtolower($_SESSION['coupons'][$couponCode]['discount-method']);
			$couponDiscount =  $_SESSION['coupons'][$couponCode]['discount'];
			$couponDiscountHuman = '';

			if ($couponDiscount == 0) {
				// no discount being applied on the price (such as for free shipping only coupon)
				$couponDiscountHuman = '';
			} elseif ($couponDiscountMethod == 'fixed') {
				$couponDiscount = number_format((float) $couponDiscount, 2);

				// escaped for xtemplate use
				if ($couponDiscount >= 0) {
					$couponDiscountHuman = '$' . $couponDiscount . ' off';
				} else {
					$couponDiscount = str_replace('-', '', $couponDiscount);
					$couponDiscountHuman = '$' . $couponDiscount;
				}
			} elseif ($couponDiscountMethod == 'percentage') {
				if (preg_match("/\.?0+$/", $couponDiscount)) {
					// strip trailing zeros for display
					$couponDiscount = preg_replace("/\.?0+$/", "", $couponDiscount);
				}
				if ($couponDiscount >= 0) {
					$couponDiscountHuman = $couponDiscount . '% off';
				} else {
					$couponDiscount = str_replace('-', '', $couponDiscount);
					$couponDiscountHuman = $couponDiscount . '%';
				}
			}
			return $couponDiscountHuman;
		}
		return false;
	}

	public function applyDiscount($subTotal)
	{
		$discount = $this->getDiscount($subTotal);

		$subTotal = $subTotal - $discount;

		// Don't give money back ;) .. as there's no min amount specified to use a coupon on
		if ($subTotal <= 0) {
			$subTotal = 0.00;
		}

		return $subTotal;
	}

	public function freeShipping()
	{
		$freeShipping = false;
		if (!empty($_SESSION['coupons'])) {
			$coupons = $_SESSION['coupons'];

			foreach ($coupons as $couponCode => $couponData) {
				if (($couponData['usable'] == 'true') && ($couponData['includes-free-shipping'] == 'true')) {
					$freeShipping = true;
				}
			}

			return $freeShipping;
		}
	}

	public function couponRestrictedPaymentMethods()
	{
		$restrictedBillingOptions = array();

		if (!empty($_SESSION['coupons'])) {
			$coupons = $_SESSION['coupons'];

			// gather all payment options available
			foreach ($coupons as $couponCode => $couponData) {
				if (isset($couponData['billing-options']) && is_array($couponData['billing-options'])) {
					$restrictedBillingOptions = array_unique(array_merge($couponData['billing-options'], $restrictedBillingOptions));
				}
			}

			// leave only payment methods supported by each of the coupons on the order
			foreach ($coupons as $couponCode => $couponData) {
				if (isset($couponData['billing-options']) && is_array($couponData['billing-options'])) {
					$restrictedBillingOptions = array_intersect($restrictedBillingOptions, $couponData['billing-options']);
				}
			}
		}

		// returns restricted payment types, or empty array for no restrictions
		return $restrictedBillingOptions;
	}

	public function isCouponsEnabled() {
		$couponsEnabled = get_option('pw_enable_coupons', 0);
		if ((isset($couponsEnabled) && intval($couponsEnabled) === 1) && (get_option('pw_v4_legacy_mode', 0) != 1)) {
			return true;
		}
		return false;
	}
}
