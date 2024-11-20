<?php
define("CART_NAME", 'cart');
define("ORDER_ANALYTICS", 'analytics');
define("CART_SUBTOTAL", 'cart_subtotal');
define("LAST_ORDER", 'last_order');
define("CART_RX_REQUIRED", 'cart_rx_required');

class Cart
{
	// Add item to cart by packageID
	// Order limit rules in following priority:
	// MaxQty -> # of tiers -> RX_ORDER_LIMIT
	// multipleitemfactor - products need to be ordered in a multiple of this, if set
	public static function add($packageID, $amount)
	{
		self::initCart();
		$cartItems = &$_SESSION[CART_NAME];
		if (is_numeric($amount)) {
			if (self::isPackageInCart($packageID) && isset($cartItems[$packageID]["amount"])) {
				// Existing quantity on cart + additional quantity to be ordered
				$desiredAmount = $cartItems[$packageID]["amount"] + $amount;
			} else {
				$desiredAmount = $amount;
			}
			$validAmount = Cart::validatePackageQuantity($packageID, $desiredAmount);

			if ($validAmount > 0) {
				$cartItems[$packageID]["amount"] = $validAmount;
				Shipping::updateShippingProductAddons($packageID);
			}

			Cart::clearLastOrder(); // This needs to be done to clear last order number if a customer places two orders in a row
			return true;
		}
		return false;
	}

	public static function update($packageID, $amount)
	{
		self::initCart();
		$cartItems = &$_SESSION[CART_NAME];
		if (is_numeric($amount) && array_key_exists($packageID, $cartItems)) {
			if ($amount <= 0) {
				Cart::remove($packageID);
			} else {
				$amount = Cart::validatePackageQuantity($packageID, $amount);
				$cartItems[$packageID]["amount"] = $amount;
				Shipping::updateShippingProductAddons($packageID);
			}
			return true;
		}
		return false;
	}

	public static function validatePackageQuantity($packageID, $amount)
	{
		self::initCart();

		$search = new Model_Resource_Search();
		$drugPackage = $search->getPackage($packageID);
		if (!$drugPackage) {
			return false;
		}

		if (!is_numeric($amount)) {
			$amount = 1;
		} elseif ($amount < 0) {
			$amount = 0;
		}

		$allowedOrderQty = 0;
		$rxOrderLimit = RX_ORDER_LIMIT;
		$otcOrderLimit = OTC_ORDER_LIMIT;
		$cartItems = &$_SESSION[CART_NAME];
		$drugPackageMinQty = $drugPackage['minitemqty'];
		$drugPackageMaxQty = $drugPackage['maxitemqty'];
		$tierCount = (!empty($drugPackage['tier_count']) ? $drugPackage['tier_count'] : 1);
		$tierQuantities = (!empty($drugPackage['tier_quantity']) ? $drugPackage['tier_quantity'] : 1);
		$tierQuantities = explode(':', $tierQuantities);
		$tierMaxQty = end($tierQuantities);
		$multipleItemFactor = $drugPackage['multipleitemfactor'];

		$minOrderQty = (!empty($drugPackageMinQty) ? $drugPackageMinQty : 1);
		if (!empty($multipleItemFactor) && ($minOrderQty % $multipleItemFactor != 0)) {
			// round minOrderQty up to the nearest multiple of multipleItemFactor
			$minOrderQty = ceil($minOrderQty / $multipleItemFactor) * $multipleItemFactor;
		}

		// determine true max order quanity allowed
		// priority order: MaxQty -> Max Tier Qty -> RX_ORDER_LIMIT
		if ($drugPackage['prescriptionrequired']) {
			if (!empty($drugPackageMaxQty)) {
				$maxOrderQty = $drugPackageMaxQty;
			} elseif ($tierMaxQty > $rxOrderLimit) {
				$maxOrderQty = $tierMaxQty;
			} else {
				$maxOrderQty = $rxOrderLimit;
			}
		} else {
			if (!empty($drugPackageMaxQty)) {
				$maxOrderQty = $drugPackageMaxQty;
			} elseif ($tierMaxQty > $otcOrderLimit) {
				$maxOrderQty = $tierMaxQty;
			} else {
				$maxOrderQty = $otcOrderLimit;
			}
		}

		if (!empty($multipleItemFactor) && ($maxOrderQty % $multipleItemFactor != 0)) {
			// round maxOrderQty down to the nearest multiple of multipleItemFactor
			$maxOrderQty = floor($maxOrderQty / $multipleItemFactor) * $multipleItemFactor;
		}

		if (!empty($multipleItemFactor) && ($amount % $multipleItemFactor != 0)) {
			// round up to the nearest multiple of multipleItemFactor
			$amount = ceil($amount / $multipleItemFactor) * $multipleItemFactor;
		}

		if (($amount >= $minOrderQty) && ($amount <= $maxOrderQty)) {
			$allowedOrderQty = $amount;
		} else {
			if (($amount <= $minOrderQty) && ($amount != 0)) {
				$allowedOrderQty = $minOrderQty;
			} elseif ($amount >= $maxOrderQty) {
				$allowedOrderQty = $maxOrderQty;
			}
		}

		return $allowedOrderQty;
	}

	// remove item out cart
	public static function remove($packageID, $forceRemove = 0, $skipShippingAddonCheck = 0)
	{
		$cartItems = &$_SESSION[CART_NAME];

		if (array_key_exists($packageID, $cartItems)) {
			if (cart::isMandatoryProduct($packageID)) {
				if (!$forceRemove) {
					return false;
				}
				Shipping::removeShippingProductAddons($packageID);
			}

			unset($cartItems[$packageID]);
			// resolve status on product addons / mandatory products
			if (!$skipShippingAddonCheck) {
				Shipping::updateShippingProductAddons();
			}
			return true;
		}
		return false;
	}

	public static function addCartMandatoryProduct($packageID, $label = 'Mandatory Product') {
		if (!empty($packageID)) {
			$_SESSION['cart_mandatory_products'][$packageID] = $label;
		}
	}

	public static function emptyCartMandatoryProducts() {
		unset($_SESSION['cart_mandatory_products']);
	}
	public static function removeCartMandatoryProduct($packageID) {
		if (!empty($packageID)) {
			unset($_SESSION['cart_mandatory_products'][$packageID]);
			cart::remove($packageID, 1);
		}
	}

	public static function getCartMandatoryProducts() {
		return $_SESSION['cart_mandatory_products'] ?? [];
	}

	public static function isMandatoryProduct($packageID) {
		$mandatoryProducts = cart::getCartMandatoryProducts();
		if (!empty($mandatoryProducts)) {
			if (array_key_exists($packageID, $mandatoryProducts)) {	
				return true;
			}
		}
		return false;
	}

	public static function isPackageInCart($packageID)
	{
		$cartItems = &$_SESSION[CART_NAME];
		if (!empty($cartItems) && array_key_exists($packageID, $cartItems)) {
			return true;
		}
		return false;
	}
	public static function getListItems()
	{
		global $table_prefix;
		$packages_table	 = $table_prefix . 'pw_packages';
		$drugs_table	 = $table_prefix . 'pw_drugs';
		$tierprice_table = $table_prefix . 'pw_packages_tierprice';
		self::initCart();
		global $wpdb;
		$strWhere = "";

		$cart_items = &$_SESSION[CART_NAME];
		foreach ($cart_items as $key => $value) {
			if ($strWhere != "") {
				$strWhere .= ' OR';
			}
			$strWhere .= " P.package_id='" . $key . "'";
		}

		// if strWhere is not empty, setup & do select
		$list_items = array();
		if (!empty($strWhere)) {
			$sql_Select  = "SELECT P.package_id,P.product,P.price,P.packagequantity,P.packagingfreeform, P.origin_country_code, P.maxitemqty as maxqty, P.multipleitemfactor, D.name as drug_name, D.drug_id, D.schedule, D.strength,D.strength_unit,D.strengthfreeform,D.prescriptionrequired,D.generic, D.comment_external as drug_comment_external, P.vendor_country_code, P.filling_vendor_id, P.comment_external as package_comment_external, group_concat(concat_ws(':',P_TP.quantity,P_TP.price) ORDER BY P_TP.quantity) AS tier_prices
				FROM " . $packages_table . " AS P 
				LEFT JOIN " . $drugs_table . " AS D ON (P.drug_id=D.drug_id) 
				LEFT JOIN " . $tierprice_table . " AS P_TP ON (P.package_id=P_TP.package_id)
				WHERE P.drug_id=D.drug_id and (" . $strWhere . ")
				GROUP BY P.drug_id, P.package_id";

			$list_items = $wpdb->get_results($sql_Select);
		}

		$_SESSION[CART_SUBTOTAL] = 0;
		$resourceAttributes = new Model_Resource_Attributes();

		$foreignFill = false;

		foreach ($list_items as $item) {
			$packagequantity = explode(XML_JOIN_SYMBOL, $item->packagequantity);
			$item->package_quantity = $packagequantity[0];
			$item->package_quantity_units = $packagequantity[1];

			$scheduleInfo = explode(XML_JOIN_SYMBOL, $item->schedule);
			$item->country = $scheduleInfo[1];
			$item->amount = $cart_items[$item->package_id]['amount'];

			$item->order_quantity = Utility_Common::getOrderQuantity($item->packagequantity, $item->packagingfreeform, $item->amount, false);

			// use tier price if applicable
			foreach (explode(',', $item->tier_prices) as $tierPrice) {
				list($tQty, $tPrice) = explode(':', $tierPrice, 2);
				if (!empty($item->amount) && ($item->amount >= $tQty)) {
					$item->price = $tPrice;
				}
				if (!empty($item->maxqty) && ($item->maxqty < $tQty)) {
					$item->maxqty = $tQty;
				}
			}

			// If vendor country code is not local ('CAN'), product is a foreign fill

			if ($item->vendor_country_code != 'CAN') {
				$foreignFill = true;
			}

			$item->sub_amount = $item->amount * $item->price;
			$resourceAttributes->setId($item->package_id);
			$item->package_attributes = $resourceAttributes->getData();
			$resourceAttributes->setId($item->drug_id);
			$item->drug_attributes = $resourceAttributes->getData();

			$item->ingredients = self::_prepareIngredients($item->drug_id);

			foreach ($item->drug_attributes as $attribute) {
				if ($attribute->attribute_key === '#fridge' || $attribute->attribute_key === '#high-value') {
					$item->fridge = $attribute->attribute_value;
				}
			}
			foreach ($item->package_attributes as $attribute) {
				if ($attribute->attribute_key === '#fridge' || $attribute->attribute_key === '#high-value') {
					$item->fridge = $attribute->attribute_value;
				}
			}
			$_SESSION[CART_SUBTOTAL] += $item->sub_amount;
		}

		// if the option to limit express shipping to products filled locally ('CAN') is enabled
		// remove express shipping on the cart
		if (get_option('pw_localfill_only_expressshipping') && $foreignFill == true) {
			self::disableExpressShipping();
		} else {
			self::enableExpressShipping();
		}

		return $list_items;
	}
	public static function getCartJSON($success = true, $message = '')
	{
		$couponSession = new Model_Coupon();
		if ($couponSession->isCouponsEnabled()) {
			$couponSession->applyMandatoryCoupons();
		}

		$result = new stdClass();
		$lstItems = self::getListItems();
		$result->rx_required = Cart::isRxRequired($lstItems);

		foreach ($lstItems as $key => $item) {
			$price = $item->price;
			// option to show full tier precision for unit price
			if (get_option('pw_unitprice_full_precision', 'off') == 'on') {
				$price = sprintf("%.4f", $price);
				$price = preg_replace('/(\.[0-9]*?)0+$/', '$1', $price);
				// if less than 2 decimal places, force 2 - but leave higher prevision as is
				if (round($price, 1) == $price) {
					$price = sprintf("%.2F", $price);
				}
			} else {
				$price = PC_formatPrice($item->price);
			}

			$lstItems[$key]->price = $price;
		}

		$result->items = $lstItems;
		$result->shipping_cost = (float) Cart::calculateShippingFee($lstItems);
		$result->shipping_cost_human = ($result->shipping_cost == 0) ? 'FREE' : '$' . PC_formatPrice($result->shipping_cost);
		$result->sub_total = (float) Cart::getSubTotal();
		
		$result->coupons = [];
		if ($couponSession->isCouponsEnabled()) {
			$result->coupons = $couponSession->getCouponSession_PublicInfo();
			$result->discount_total = $couponSession->getDiscount($result->sub_total);
			$result->total = $couponSession->applyDiscount($result->sub_total) + $result->shipping_cost;
		} else {
			$result->discount_total = 0;
			$result->total = $result->sub_total + $result->shipping_cost;
		}

		$result->success = $success ? 1 : 0;
		$result->Message = $message;
		return json_encode($result);
	}
	public static function disableExpressShipping()
	{
		$_SESSION['DISABLE_EXPRESS_SHIPPING'] = 1;
		self::removeExpressShipping();
	}
	public static function enableExpressShipping()
	{
		$_SESSION['DISABLE_EXPRESS_SHIPPING'] = 0;
	}
	public static function isExpressShippingDisabled()
	{
		return $_SESSION['DISABLE_EXPRESS_SHIPPING'];
	}
	public static function setExpressShipping($expressShipping)
	{
		$_SESSION['CART_EXPRESS'] = $expressShipping;
	}
	public static function getExpressShipping()
	{
		if (self::isExpressShippingDisabled()) {
			return null;
		}
		// returns 'on' if active & not disabled
		return $_SESSION['CART_EXPRESS'] ?? null;
	}
	public static function removeExpressShipping()
	{
		$_SESSION['CART_EXPRESS'] = null;
		return;
	}
	public static function setShippingOptions($shippingOption)
	{
		$expressShipping = '';
		switch ($shippingOption) {
			case 'express_shipping':
				$expressShipping = 'on';
				self::setShippingMethod($shippingOption);
				break;
			case 'none':
				self::removeShippingMethod();
				break;
			default:
				// set shipping method
				self::setShippingMethod($shippingOption);
		}
		self::setExpressShipping($expressShipping);
	}

	public static function setShippingMethod($shippingMethod)
	{
		$_SESSION['CART_SHIPPING_METHOD'] = $shippingMethod;
		return;
	}
	public static function getShippingMethod()
	{
		$shippingMethod = null;
		if (!empty($_SESSION['CART_SHIPPING_METHOD'])) {
			$shippingMethod = $_SESSION['CART_SHIPPING_METHOD'];
		}
		return $shippingMethod;
	}
	public static function removeShippingMethod()
	{
		unset($_SESSION['CART_SHIPPING_METHOD']);
		return;
	}

	public static function setupShippingAddons($enableShippingAddons)
	{
		$enableAddons = array();
		$disableAddons = array();

		if (get_option('pw_shipping_extended')) {
			$pwShippingExtendedJSON = get_option('pw_shipping_extended');
			if (!empty($pwShippingExtendedJSON) && is_json($pwShippingExtendedJSON)) {
				$pwShippingExtended = json_decode($pwShippingExtendedJSON, true);
				$shippingAddonKeys = array_keys(array_combine(array_keys($pwShippingExtended), array_column($pwShippingExtended, 'type')), 'addon');

				// check to see if valid addons
				if (is_array($enableShippingAddons)) {
					$enableAddons = array_intersect($shippingAddonKeys, $enableShippingAddons);
					$disableAddons = array_diff($shippingAddonKeys, $enableShippingAddons);

					foreach ($enableAddons as $addonKey) {
						// enable addon
						if (!empty($pwShippingExtended[$addonKey]['product_id'])) {
							self::add($pwShippingExtended[$addonKey]['product_id'], 1);
						}
						if (!empty($pwShippingExtended[$addonKey]['order_tag'])) {
							$orderTags = new Model_OrderTag;

							$tag = array(
								'tag-code' => $pwShippingExtended[$addonKey]['order_tag'],
								'tag-value' => '1'
							);
							$orderTags->applyTag($tag);
						}
					}

					self::setActiveShippingAddons($enableAddons);
				} else {
					$disableAddons = self::getActiveShippingAddons();
					self::removeActiveShippingAddons();
				}
			} else {
				error_log('PharmacyWire - invalid pw_shipping_extended: not valid JSON');
			}
		}

		if (!empty($disableAddons)) {
			foreach ($disableAddons as $addonKey) {
				// disable addon
				if (!empty($pwShippingExtended[$addonKey]['product_id'])) {
					self::remove($pwShippingExtended[$addonKey]['product_id'], 1);
				}
				if (!empty($pwShippingExtended[$addonKey]['order_tag'])) {
					Model_OrderTag::removeTagSession($pwShippingExtended[$addonKey]['order_tag']);
				}
			}
		}
	}

	public static function setActiveShippingAddons($activeAddons)
	{
		$_SESSION['ACTIVE_SHIPPING_ADDONS'] = $activeAddons;
		return;
	}

	public static function getActiveShippingAddons()
	{
		$activeShippingAddons = null;
		if (!empty($_SESSION['ACTIVE_SHIPPING_ADDONS'])) {
			$activeShippingAddons = $_SESSION['ACTIVE_SHIPPING_ADDONS'];
		}
		return $activeShippingAddons;
	}

	public static function removeActiveShippingAddon($removeAddonKey, $shippingAddon = null) {
		$activeAddons = (array) self::getActiveShippingAddons();
		if (!empty($activeAddons)) {
			foreach ($activeAddons as $index => $addonKey) {
				if ($removeAddonKey == $addonKey) {
					unset($activeAddons[$index]);
					if (!empty($shippingAddon)) {
						$productAddonPkgID = $shippingAddon['product_id'] ?? null;
						if (!empty($productAddonPkgID) && (preg_match('/(DP-\d+)(,\s*DP-\d+)*/i', $productAddonPkgID))) {
							$productAddons = explode(',', $productAddonPkgID);
							foreach($productAddons as $addonPkgID) {
								// remove package if exists, non-force, skip addon update
								Cart::remove($addonPkgID, 0, 1);
							}
						}
					}
				}
			}
			if (!empty($activeAddons)) {
				self::setActiveShippingAddons($activeAddons);
			} else {
				self::removeActiveShippingAddons();
			}
		}
	}

	public static function removeActiveShippingAddons()
	{
		unset($_SESSION['ACTIVE_SHIPPING_ADDONS']);
		return;
	}

	public static function setAdditionalNotes($additionalNotes = '')
	{
		$_SESSION['CART_ADDITIONAL_NOTES'] = $additionalNotes;
	}
	public static function getAdditionalNotes()
	{
		$cartAdditionalNotes = null;
		if (!empty($_SESSION['CART_ADDITIONAL_NOTES'])) {
			$cartAdditionalNotes = $_SESSION['CART_ADDITIONAL_NOTES'];
		}
		return $cartAdditionalNotes;
	}
	private static function initCart()
	{
		if (!isset($_SESSION[CART_NAME])) {
			$_SESSION[CART_NAME] = array();
			$_SESSION[CART_SUBTOTAL] = 0;
			$_SESSION['CART_EXPRESS'] = null;
		}
	}
	// check that the cart has items
	public static function haveItems()
	{
		$bResult = false;

		if (isset($_SESSION[CART_NAME]) && is_array($_SESSION[CART_NAME]) && (count($_SESSION[CART_NAME]) > 0)) {
			$bResult = true;
		}
		return $bResult;
	}

	// quick item count
	public static function getItemCount() {
		$bResult = 0;

		if (isset($_SESSION[CART_NAME]) && is_array($_SESSION[CART_NAME]) && (count($_SESSION[CART_NAME]) > 0)) {
			$bResult = count($_SESSION[CART_NAME]);
		}
		return $bResult;
	}
	// remove all items in cart
	public static function removeAllItems()
	{
		$_SESSION[CART_NAME] = array();
		$_SESSION[CART_SUBTOTAL] = 0;
		unset($_SESSION['coupons']);
		self::emptyCartMandatoryProducts();
		self::removeShippingMethod();
		self::removeActiveShippingAddons();
		Model_OrderTag::removeTagSessionAll();
		unset($_SESSION['NewPatientCreated']);
	}
	public static function getTotal()
	{
		// getTotal deprecated as it used to refer to subTotal
		return Cart::getSubTotal();
	}
	public static function getSubTotal()
	{
		// check cart_total if CART_SUBTOTAL not set, for legacy support - deprecated
		$currentTotal = !empty($_SESSION['cart_total']) ? $_SESSION['cart_total'] : 0;
		$total  = (float) (!empty($_SESSION[CART_SUBTOTAL])) ? $_SESSION[CART_SUBTOTAL] : $currentTotal;
		return PC_formatPrice($total);
	}
	public static function calculateShippingFee($lstItems, $ignoreCoupon = 0)
	{
		$Canadian = self::containsCanadianItems($lstItems);
		$FillingVendorIDs = self::getFillingVendorIDs($lstItems);
		$shippingDestination = self::getShippingDestination();
		$expressShipping = self::getExpressShipping();
		$baseFee = self::getShippingFee($shippingDestination);
		$expressFee = self::getExpressShippingFee();
		$selectedShippingMethod = self::getShippingMethod();
		$pwShippingExtendedJSON = null;
		if (get_option('pw_shipping_extended')) {
			$pwShippingExtendedJSON = get_option('pw_shipping_extended');
		}
		// Cart::overrideShippingRequirements($lstItems);

		if ((get_option('pw_fridge_express_shipping') && Cart::getFridge($lstItems)) || Cart::requiresExpressShipping($lstItems)) {
			if (!empty($expressFee)) {
				$baseFee = $expressFee;
			}
		}

		$internationalExpress = get_option('pw_international_express_allowed');
		$countryCount = count($FillingVendorIDs) || 1;
		$shippingFee = $baseFee;
		if ($expressShipping == 'on' && ($Canadian || $internationalExpress) && $expressFee > 0 && ($shippingDestination == 'USA' || $shippingDestination == 'CAN' || !strlen($shippingDestination))) {
			$shippingFee = $expressFee;
		} elseif (!empty($selectedShippingMethod)) {
			if (!empty($pwShippingExtendedJSON) && is_json($pwShippingExtendedJSON)) {
				$pwShippingExtended = json_decode($pwShippingExtendedJSON, true);
				if (!empty($pwShippingExtended[$selectedShippingMethod])) {
					$shippingFee = $pwShippingExtended[$selectedShippingMethod]['price'];
				}
			}
		}

		if (get_option('pw_charge_shipping_per_country')) {
			$shippingFee = ($shippingFee * $countryCount);
		}

		// Apply override shipping rate if applicable (e.g. watch_for #fridge and line item has #fidge attribute)
		$overrideShippingPrice = 0;
		if (!empty($pwShippingExtendedJSON) && is_json($pwShippingExtendedJSON)) {
			$overrideShippingFee = 0;

			$pwShippingExtended = json_decode($pwShippingExtendedJSON, true);
			$shippingSelectOverrideKeys = array_keys(array_combine(array_keys($pwShippingExtended), array_column($pwShippingExtended, 'type')), 'override');

			if (!empty($shippingSelectOverrideKeys)) {
				foreach ($shippingSelectOverrideKeys as $key) {
					$shippingO = $pwShippingExtended[$key];
					if ($shippingO['type'] === 'override') { // looks redundant remove later
						$lineItemAttributes = Cart::getLineItemAttributes(); // this too
						$watchForCheck = Shipping::watchForCheck($shippingO, $lstItems);
						if ($watchForCheck == true) {
							$overrideShippingPrice = 1;
							if (($shippingO['price'] >= $overrideShippingFee)) {
								$overrideShippingFee = $shippingO['price'];
							}
						}
					}
				}
			}
		}

		if ($overrideShippingPrice === 1) {
			$shippingFee = $overrideShippingFee;
		}

		// Check for coupons
		$coupon = new Model_Coupon();
		$freeShipping = $coupon->freeShipping();
		if ($freeShipping && !$ignoreCoupon) {
			$shippingFee = '0.00';
		}

		// If any shipping addons are selected, add onto shipping fee
		$activeShippingAddons = self::getActiveShippingAddons();
		if (!empty($activeShippingAddons)) {
			$pwShippingExtendedJSON = get_option('pw_shipping_extended');
			if (!empty($pwShippingExtendedJSON) && is_json($pwShippingExtendedJSON)) {
				$pwShippingExtended = json_decode($pwShippingExtendedJSON, true);
				foreach ($activeShippingAddons as $addon) {
					if (!empty($pwShippingExtended[$addon]['price']) && is_numeric($pwShippingExtended[$addon]['price'])) {
						$shippingFee += $pwShippingExtended[$addon]['price'];
					}
				}
			}
		}

		return $shippingFee;
	}

	public static function getLineItemAttributes()
	{
		$lstItems = Cart::getListItems();

		$itemAttributesKeys = array();
		foreach ($lstItems as $item) {
			$itemAttributes = array_merge($item->drug_attributes, $item->package_attributes);
			$itemAttributesKeys = array_merge($itemAttributesKeys, array_column($itemAttributes, 'attribute_key'));
		}

		return $itemAttributesKeys;
	}

	// check if the cart items all match a set list of countries
	public static function containsItemsFromCountry($lstItems, $validCountries)
	{
		$lstItemCountries = self::getCountries($lstItems);
		if (!is_array($validCountries)) {
			$validCountries = array_map('trim', explode(',', $validCountries));
		}
		$countriesMatch = 1;
		foreach ($lstItemCountries as $country) {
			if (!in_array($country, $validCountries)) {
				$countriesMatch = 0;
			}
		}
		return $countriesMatch;
	}

	// check if the cart contains a single item from a set list of countries
	public static function containsAnItemFromCountry($lstItems, $validCountries)
	{
		$lstItemCountries = self::getCountries($lstItems);
		if (!is_array($validCountries)) {
			$validCountries = array_map('trim', explode(',', $validCountries));
		}
		$countriesMatch = 0;
		foreach ($lstItemCountries as $country) {
			if (in_array($country, $validCountries)) {
				$countriesMatch = 1;
			}
		}
		return $countriesMatch;
	}

	// check if the cart items all match Canada
	public static function containsCanadianItems($lstItems)
	{
		$countries = self::getCountries($lstItems);
		foreach ($countries as $country) {
			if ($country == 'CAN') {
				return 1;
			}
		}
		return 0;
	}
	public static function _prepareIngredients($drugId)
	{
		$resourceSearch = new Model_Resource_Search();
		$ingredients = $resourceSearch->getIngredients($drugId);

		$output = array();
		foreach ($ingredients as $ingredient) {
			$output[] = $ingredient->ingredient_name;
		}

		return implode(', ', $output);
	}
	public static function requiresExpressShipping($lstItems)
	{
		$requiresExpressShipping = 0;

		$pwExpressShippingOnTags = array_map('trim', explode(',', get_option('pw_express_shipping_on_tags')));

		foreach ($lstItems as $item) {
			if (!empty($item->drug_attributes) && is_array($item->drug_attributes)) {
				foreach ($item->drug_attributes as $attribute) {
					if (in_array($attribute->attribute_key, $pwExpressShippingOnTags)) {
						$requiresExpressShipping = 1;
					}
				}
			}
			if (!empty($item->package_attributes) && is_array($item->package_attributes)) {
				foreach ($item->package_attributes as $attribute) {
					if (in_array($attribute->attribute_key, $pwExpressShippingOnTags)) {
						$requiresExpressShipping = 1;
					}
				}
			}
		}
		return $requiresExpressShipping;
	}
	public static function getFridge($lstItems)
	{
		$fridgeItemsByVendor = array();

		foreach ($lstItems as $item) {
			if (isset($item->fridge) && ($item->fridge == 1)) {
				if (isset($fridgeItemsByVendor[$item->filling_vendor_id])) {
					$fridgeItemsByVendor[$item->filling_vendor_id]++;
				} else {
					$fridgeItemsByVendor[$item->filling_vendor_id] = 1;
				}
			}
		}
		return array_keys($fridgeItemsByVendor);
	}
	public static function getCountries($lstItems)
	{
		$countries = array();
		foreach ($lstItems as $item) {
			if (!empty($item->country)) {
				$countries[$item->country] = 1;
			}
		}
		return array_keys($countries);
	}
	public static function getFillingVendorIDs($lstItems)
	{
		$FillingVendorIDs = array();
		foreach ($lstItems as $item) {
			$FillingVendorIDs[$item->filling_vendor_id] = 1;
		}
		return array_keys($FillingVendorIDs);
	}
	public static function getShippingDestination()
	{
		$shippingO = new Shipping;
		if ($shippingO->getAddressRef() > 0) {
			// set shipping refer
			$shippingID = $shippingO->getAddressRef();
			$shippingInfo = $shippingO->getAddessByAddressID($shippingID);
		} else {
			$shippingInfo = $shippingO->getInfo();
		}
		return $shippingInfo->country;
	}
	public static function getShippingFee($shippingDestination)
	{
		if ($shippingDestination == 'USA' || $shippingDestination == 'CAN' || !strlen($shippingDestination)) {
			return get_option('pw_shipping_fee', '9.99');
		} else {
			return self::getInternationalShippingFee();
		}
	}
	public static function getExpressShippingFee()
	{
		return get_option("pw_express_shipping_fee");
	}
	public static function getInternationalShippingFee()
	{
		$intFee = get_option("pw_intl_shipping_fee");
		if (empty($intFee)) {
			$intFee = get_option('pw_shipping_fee', '9.99');
		}
		return $intFee;
	}
	public static function resetInfo()
	{
		if (WebUser::isLoggedIn()) {
			$patient = new Model_Patient();
			$patient->resetRecentOrders(WebUser::getUserID());
		}
		Billing::resetInfo();
		Shipping::resetInfo();
		self::removeAllItems();
		self::setAdditionalNotes();
	}
	public static function clearLastOrder()
	{
		$_SESSION[LAST_ORDER] = null;
	}
	public static function setLastOrder($orderId, $orderInfo, $orderItems, $orderTotal, $show_billing_detail = array(), $billing_activation = '')
	{
		$orders = &$_SESSION[ORDER_ANALYTICS];
		if (is_null($orders)) {
			$orders = array();
		}
		$orders[$orderId]["orderinfo"] = $orderInfo;
		$orders[$orderId]["details"] = $orderItems;
		$orders[$orderId]["total"] = $orderTotal;
		$_SESSION[ORDER_ANALYTICS] = $orders;

		$lastOrder = $_SESSION[LAST_ORDER];
		if (strlen($lastOrder) && $lastOrder) {
			$lastOrder .= ";$orderId";
		} else {
			$lastOrder = $orderId;
		}
		$_SESSION[LAST_ORDER] = $lastOrder;
	}
	public static function getAnalytics()
	{
		return $_SESSION[ORDER_ANALYTICS];
	}
	public static function resetAnalytics()
	{
		$_SESSION[ORDER_ANALYTICS] = null;
	}
	public static function getLastOrder()
	{
		$lastOrder = $_SESSION[LAST_ORDER];
		if (!$lastOrder) {
			return null;
		}
		return explode(';', $_SESSION[LAST_ORDER]);
	}
	public static function isRxRequired($lstItems = null)
	{
		if (empty($lstItems)) {
			$lstItems = self::getListItems();
		}
		$rxRequired = 0;
		foreach ($lstItems as $item) {
			if ($item->prescriptionrequired) {
				$rxRequired = 1;
			}
		}
		$_SESSION[CART_RX_REQUIRED] = $rxRequired;
		return $rxRequired;
	}
	public static function getPrescriptionStatus()
	{
		$bResult = false;
		if (!is_null($_SESSION[CART_RX_REQUIRED])) {
			$bResult = $_SESSION[CART_RX_REQUIRED];
		}
		return $bResult;
	}
	public static function addCartClassToBody($classes)
	{
		add_filter('body_class', 'pw_cart_class');

		function pw_cart_class($classes)
		{
			$classes[] = 'pw-checkout-page';
			return $classes;
		}
	}
}
