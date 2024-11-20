<?php
class Page_Checkout_Shopping extends Utility_PageBase
{
	/**
	 * Make the html output
	 *
	 * @return The content in html format
	 */
	public function _process()
	{
		add_filter('body_class', 'pw_cart_class');

		function pw_cart_class($classes)
		{
			$classes[] = 'pw-checkout-page';
			return $classes;
		}

		$this->processShoppingcart();
	}

	// process on shopping cart tab
	public function processShoppingcart()
	{
		$this->setTemplate("page_checkout_cart");
		$action = strtoupper($this->_getRequest('action'));

		if ($action) {
			if ($this->_getRequest('additional-note-enable')) {
				$additionalNotes = $this->_getRequest('additional-notes');
				if (!strlen($additionalNotes)) {
					$additionalNotes = 'Order includes items for family members and/or pets.';
				}
				Cart::setAdditionalNotes($additionalNotes);
			} else {
				Cart::setAdditionalNotes(null);
			}
		}

		// check session for active coupon
		$couponSession = new Model_Coupon();

		switch ($action) {
			case "NEXT":
				$checkoutURL = get_option('pw_checkout_url', '/shopping-cart/checkout-login/');
				$this->redirect($checkoutURL);
				exit;
				break;

			case "UPDATE":
				$shippingOptions = $this->_getRequest('pw_shipping_options');
				if ($shippingOptions) {
					Cart::setShippingOptions($shippingOptions);
				}

				$shippingAddons = $this->_getRequest('pw_shipping_addons');
				if ($shippingAddons) {
					$shippingAddons = array_keys($shippingAddons);
				}
				Cart::setupShippingAddons($shippingAddons);

				$updateItem = (array) $this->_getRequest('qty');
				foreach ($updateItem as $key => $value) {
					Cart::update($key, $value);
				}
				break;

			case "DELETE":
				$packageId = $this->_getRequest('deleteItem');
				Cart::remove($packageId);
				break;

			default:
				$package = $this->_getRequest('package');
				$qty = $this->_getRequest('qty');

				if (!$qty) {
					$qty = 1;
				}

				if ($package !== "") {
					Cart::add($package, $qty);

					$reorderSubmit = $this->_getRequest('reorder-submit');
					if ($this->_getRequest('reorder-submit')) {
						$reorderUrl = PC_getReorderUrl();
						$this->redirect($reorderUrl);
					}

					// redirect to self (shopping cart) URL to clear package/qty get params
					// to prevent duplicate adds on refresh, after a delete, etc.
					$checkout_url = PC_getShoppingURL(true);
					$this->redirect($checkout_url);
				}
		}
		
		// revalidate shipping method set on cart
		// based on current line items
		$lstItems = [];
		$pwShippingExtendedJSON = get_option('pw_shipping_extended');
		if (!empty($pwShippingExtendedJSON) && is_json($pwShippingExtendedJSON)) {
			$pwShippingExtended = json_decode($pwShippingExtendedJSON, true);
			$shippingSelectOverrideKeys = array_keys(array_combine(array_keys($pwShippingExtended), array_column($pwShippingExtended, 'type')), 'override');
			$shippingAddonKeys = array_keys(array_combine(array_keys($pwShippingExtended), array_column($pwShippingExtended, 'type')), 'addon');

			// get current line items for shipping method & addon validation
			$lstItems = Cart::getListItems();

			// Check if shipping method is valid for products currently in cart
			$shippingKey = Cart::getShippingMethod();
			if (!empty($shippingKey)) {
				$shippingO = $pwShippingExtended[$shippingKey];
				$watchForCheck = Shipping::watchForCheck($shippingO, $lstItems);
				if (!$watchForCheck) {
					Cart::removeShippingMethod();
				}
			}

			// Check if shipping addons are valid for products currently in cart
			// If an addon is not valid, remove it and any products associated with that addon (i.e. styrofoam containers)
			$activeShippingAddons = Cart::getActiveShippingAddons();
			if (!empty($activeShippingAddons)) {
				foreach ($activeShippingAddons as $index => $addonKey) {
					$shippingAddon = $pwShippingExtended[$addonKey];
					$visible = Shipping::shippingOptionVisibility($shippingAddon, $lstItems);
					if ($visible != true) {
						Cart::removeActiveShippingAddon($addonKey, $shippingAddon);
					}
				}
			}
		}

		// get List Items after above revalidation incase products have been removed (via shipping addons, etc.)
		$lstItems = Cart::getListItems();

		// apply coupon after cart updates and getListItems updates $_SESSION[CART_SUBTOTAL]
		$couponSession->applyMandatoryCoupons();

		$countItems = count($lstItems);
		//parse data when cart is empty
		if ($countItems < 1) {
			$this->parse("NO_ITEM");
			Cart::removeShippingMethod();
		} else { // execute when cart have item.
			// revalidate and get coupon session
			$currentCoupons = $couponSession->revalidateCouponSession(1);

			$rxStatusCart = 'otc';

			foreach ($lstItems as $item) {
				$searchModel = new Model_Catalog();
				$results = (array) $searchModel->searchDrugDetail($item->drug_id)->data;
				$firstRow = true;

				$countryModel = new Model_Country();
				$countryCodeHuman = $countryModel->getCountryByCode($item->country);
				$countryCodeHuman = (empty($countryCodeHuman) ? 'unknown' : $countryCodeHuman);

				$packageId = $item->package_id;

				if (get_option('pw_display_package_name_on_search_results')) {
					$name = $item->product;
				} else {
					$name = $item->drug_name;
				}

				$ingredients = $item->ingredients;
				$qty = $item->amount;
				$sub_total = PC_formatPrice($item->sub_amount);
				$strength = Utility_Common::getFullValue($item->strength);
				$strengthUnit = $item->strength_unit;
				if ($strength != "") {
					$strength .= " $strengthUnit";
				} else {
					$strength = $item->strengthfreeform;
				}
				$quantity = Utility_Common::getQuantity($item->packagequantity, $item->packagingfreeform);
				if ($quantity != "") {
					$quantity = " (" . $quantity . ")";
				}
				$orderQuantity = Utility_Common::getOrderQuantity($item->packagequantity, $item->packagingfreeform, $qty);

				$remove = '<div id="remove[' . $packageId . ']" name="remove[' . $packageId . ']" class="remove-button">&nbsp;</div>';
				$rxRequired = '<div class="otc drug-info-icon" title="Over the Counter">Over the Counter</div>';
				$rxStatusItem = 'otc';

				$tierPriceArray = explode(',', $item->tier_prices);
				$tierCount = (!empty($tierPriceArray)) ? count($tierPriceArray) : 1;

				$tierMaxValues = $tierPriceArray[$tierCount - 1];
				list($tierMaxQty, $tierMaxPrice) = explode(':', $tierMaxValues, 2);

				if ($item->prescriptionrequired) {
					$rxRequired = '<div class="rx-required rx drug-info-icon" title="Prescription Required">Prescription Required</div>';
					$rxStatusItem = 'rx';

					// determine true max order quanity allowed
					// priority order: MaxQty -> Max Tier Qty -> RX_ORDER_LIMIT
					if (!empty($item->maxqty)) {
						$qtyOrderLimit = $item->maxqty;
					} elseif ($tierMaxQty > RX_ORDER_LIMIT) {
						$qtyOrderLimit = $tierMaxQty;
					} else {
						$qtyOrderLimit = RX_ORDER_LIMIT;
					}
				} else {
					if (!empty($item->maxqty)) {
						$qtyOrderLimit = $item->maxqty;
					} elseif ($tierMaxQty > OTC_ORDER_LIMIT) {
						$qtyOrderLimit = $tierMaxQty;
					} else {
						$qtyOrderLimit = OTC_ORDER_LIMIT;
					}
				}
				$multipleItemFactor = $item->multipleitemfactor;
				if (!empty($multipleItemFactor) && ($qtyOrderLimit % $multipleItemFactor != 0)) {
					// round up to the nearest multiple of multipleItemFactor while staying under the order limit
					$qtyOrderLimit = floor($qtyOrderLimit / $multipleItemFactor) * $multipleItemFactor;
				}

				$this->assign("ITEM_rxRequired", $rxRequired);

				$flagBaseURL = THEME_URL . 'images/flags/';
				$this->assign("ITEM_flagBaseURL", $flagBaseURL);

				$countryFlag = ($countryCodeHuman == 'unknown') ? '' : '<img class="country-flag" src="' . $flagBaseURL . $item->country . '.png" alt="Sourced from ' . $countryCodeHuman . '" title="Sourced from ' . $countryCodeHuman . '" />';
				$this->assign("ITEM_countryFlag", $countryFlag);
				$this->assign("ITEM_country", $countryCodeHuman);

				$vendorCountryModel = new Model_Country();
				$vendorCountryHuman = $vendorCountryModel->getCountryByCode($item->vendor_country_code);
				$vendorCountryHuman = (empty($vendorCountryHuman) ? 'unknown' : $vendorCountryHuman);
				$this->assign("ITEM_vendorCountryCode", $item->vendor_country_code);
				$this->assign("ITEM_vendorCountry", $vendorCountryHuman);

				$countryOfOModel = new Model_Country();

				// $searchModel = new Model_Resource_Search();
				// $package = $searchModel->getPackage($item->package_id);
				$countryOfOriginCode = $item->origin_country_code;
				$countryOfOriginHuman = $countryOfOModel->getCountryByCode($countryOfOriginCode);
				$this->assign("ITEM_countryOfOriginCode", $countryOfOriginCode);
				$this->assign("ITEM_countryOfOrigin", $countryOfOriginHuman);

				if ($item->generic) {
					$brandOrGeneric = 'Generic';
					$brandOrGenericIcon = '<div class="generic gen drug-info-icon" title="Generic">Generic</div>';
				} else {
					$brandOrGeneric = 'Brand';
					$brandOrGenericIcon = '<div class="brand brd drug-info-icon" title="Brand">Brand</div>';
				}
				$this->assign("ITEM_brandOrGeneric", $brandOrGeneric);
				$this->assign("ITEM_brandOrGenericIcon", $brandOrGenericIcon);

				$packageAttributes = $item->package_attributes;
				$packageAttrClass = array();
				foreach ($packageAttributes as $pAttr) {
					$pAttrK = 'attr-' . sanitize_html_class($pAttr->attribute_key);
					$pAttrV = sanitize_html_class($pAttr->attribute_value);
					$pClassAttrVal = $pAttrK . '-' . $pAttrV;
					// use sanitized attribute key as well as key-val compbination as classes
					array_push($packageAttrClass, $pAttrK);
					array_push($packageAttrClass, $pClassAttrVal);
				}

				$drugAttributes = $item->drug_attributes;
				$drugAttrClass = array();
				foreach ($drugAttributes as $dAttr) {
					$dAttrK = 'attr-' . sanitize_html_class($dAttr->attribute_key);
					$dAttrV = sanitize_html_class($dAttr->attribute_value);
					$dClassAttrVal = $dAttrK . '-' . $dAttrV;
					// use sanitized attribute key as well as key-val compbination as classes
					array_push($drugAttrClass, $dAttrK);
					array_push($drugAttrClass, $dClassAttrVal);
				}

				$lineItemDataAttributes = [];
				if (cart::isMandatoryProduct($packageId)) {
					$lineItemDataAttributes['mandatory-product'] = 1;
				}
				$liDataAttr = "";
				foreach ($lineItemDataAttributes as $liDAKey => $liDA) {
					$liDataAttr .= ' data-attr-' . $liDAKey . '="' . $liDA . '"';
				}
				
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
				
				$this->assign("ITEM_data_attributes", $liDataAttr);
				$this->assign("ITEM_remove", $remove);
				$this->assign("ITEM_name", $name);
				$this->assign("ITEM_ingredients", $ingredients);
				$this->assign("ITEM_strength", $strength);
				$this->assign("ITEM_quantity", $quantity);
				$this->assign("ITEM_order_quantity", $orderQuantity);
				$this->assign("ITEM_order_limit", $qtyOrderLimit);
				$this->assign('QTY_DROPDOWN', genQtyDropDown($qty, $qtyOrderLimit, 0));
				$this->assign("ITEM_packageId", $packageId);
				$this->assign("ITEM_qty", $qty);
				$this->assign("ITEM_multipleItemFactor", $multipleItemFactor);
				$this->assign("ITEM_price", $price);
				$this->assign("ITEM_unitPriceFull", round($item->price, 4));
				$this->assign("ITEM_unitPrice", $price);
				$this->assign("ITEM_RX_STATUS", $rxStatusItem);
				$this->assign("ITEM_PACKAGE_ATTRIBUTES", implode(' ', $packageAttrClass));
				$this->assign("ITEM_DRUG_ATTRIBUTES", implode(' ', $drugAttrClass));
				$this->assign("ITEM_drug_comment", $item->drug_comment_external);
				$this->assign("ITEM_package_comment", $item->package_comment_external);
				$unitForm = explode("@", $item->packagequantity)[1];
				if (!empty($unitForm)) {
					$this->assign("ITEM_unitForm", $unitForm);
				}
				$this->parse('ITEM.description');
				if (get_option('pw_cart_quantity_dropdown')) {
					$this->parse('ITEM.brandQuantityDropdown');
				} else {
					$this->parse('ITEM.brandQuantity');
				}
				$this->assign("ITEM_sub_total", $sub_total);
				$this->parse("ITEM");
			}

			$mandatory_express = 0;
			if ((get_option('pw_fridge_express_shipping') && Cart::getFridge($lstItems)) || Cart::requiresExpressShipping($lstItems)) {
				$expressShipping = 'on';
				$mandatory_express = 1;
				Cart::setExpressShipping($expressShipping);
			}

			$sub_total = PC_formatPrice(Cart::getSubTotal());
			$shipping_fee = PC_formatPrice(Cart::calculateShippingFee($lstItems));

			if (!empty($currentCoupons)) {
				// there is a coupon so setup display
				foreach ($currentCoupons as $couponCode => $couponData) {
					$couponDiscountMethodHuman = '';
					$couponLabel = '';
					$couponData['description'] = str_replace('$', '\$', $couponData['description']);

					if ($couponData['removable'] != 'false') {
						$couponLabel = 'Coupon: "' . $couponCode . '"';
						$couponDiscountMethodHuman = str_replace('$', '\$', $couponSession->getDiscountMethodHuman($couponCode));
						if (!empty($couponDiscountMethodHuman)) {
							$couponDiscountMethodHuman = ' &mdash; ' . $couponDiscountMethodHuman;
						}

						$this->assign('COUPON_LINE_DESCRIPTION', $couponData['description']);
					} else {
						// Mandatory coupons use the description as the label, hide normal coupon wording
						$couponLabel = $couponData['description'];
						$this->assign('COUPON_LINE_DESCRIPTION', '');
					}

					$this->assign('COUPON_DISCOUNT_METHOD_HUMAN', $couponDiscountMethodHuman);
					$this->assign('COUPON_CODE', $couponCode);
					$this->assign('COUPON_LABEL', $couponLabel);

					$couponLineItemClass = ($couponData['usable'] == 'false') ? 'invalid' : 'valid';
					$this->assign('COUPON_LINEITEM_CLASS', $couponLineItemClass);



					if ($couponData['removable'] != 'false') {
						$removeCouponLink = '<a href="#" class="remove-coupon" data-coupon-code="' . $couponCode . '">remove</a>';
						$this->assign('REMOVE_COUPON', $removeCouponLink);
					} else {
						$this->assign('REMOVE_COUPON', '');
					}

					$couponDiscountHuman = $couponSession->getDiscountHuman($sub_total, $couponCode);
					if (!empty($couponDiscountHuman)) {
						$this->assign('COUPON_DISCOUNT', $couponDiscountHuman);
					}

					$this->parse("TOTAL.COUPON_INPUT");
					$this->parse("TOTAL.COUPON_LINEITEM");
				}

				$total = $couponSession->applyDiscount($sub_total) + $shipping_fee;
			} else {
				$total = $sub_total + $shipping_fee;

				// hide the coupon line item but render it for template use
				$couponLineStyle = 'style="display: none;"';
				$this->assign('COUPON_LINEITEM_STYLE', $couponLineStyle);
				$this->parse("TOTAL.COUPON_LINEITEM");
			}

			$this->assign("SUB_TOTAL", $sub_total);
			$shipping_fee_human = ($shipping_fee == '0.00') ? 'FREE' : '\$' . $shipping_fee;
			$this->assign("SHIPPING_FEE", $shipping_fee_human);

			$total = PC_formatPrice($total);
			$this->assign("TOTAL", $total);
			$this->assign("CART_RX_STATUS", $rxStatusItem);

			$this->assign("RX_ORDER_LIMIT", RX_ORDER_LIMIT);
			$this->assign("OTC_ORDER_LIMIT", OTC_ORDER_LIMIT);

			$checkoutURL = get_option('pw_continue_shopping_url', '/');

			$search_url = PC_getSearchURL();
			$this->assign("SEARCH_PAGE", $search_url);

			$pw_continue_shopping_url = (!empty($checkoutURL)) ? $checkoutURL : $search_url;
			$this->assign("CONTINUE_SHOPPING_URL", $pw_continue_shopping_url);

			/**************************
			 * Shipping Dropdown
			 */

			$pwShippingExtended = null;
			$shippingSelectOptionsKeys = array();
			$shippingAddonKeys = array();
				$pwShippingExtendedJSON = get_option('pw_shipping_extended');
			if (!empty($pwShippingExtendedJSON) && is_json($pwShippingExtendedJSON)) {
					$pwShippingExtended = json_decode($pwShippingExtendedJSON, true);
					$shippingSelectOptionsKeys = array_keys(array_combine(array_keys($pwShippingExtended), array_column($pwShippingExtended, 'type')), 'option');
					$shippingAddonKeys = array_keys(array_combine(array_keys($pwShippingExtended), array_column($pwShippingExtended, 'type')), 'addon');
					$shippingSelectOverrideKeys = array_keys(array_combine(array_keys($pwShippingExtended), array_column($pwShippingExtended, 'type')), 'override');
				}

			$overrideShippingPrice = ($mandatory_express) ? 1 : 0;
			if (!empty($shippingSelectOverrideKeys) && ($overrideShippingPrice === 0)) {
				foreach ($shippingSelectOverrideKeys as $key) {
					$shippingO = $pwShippingExtended[$key];
					if ($shippingO['type'] === 'override') {
						$watchForCheck = Shipping::watchForCheck($shippingO, $lstItems);
						if ($watchForCheck === true) {
							$overrideShippingPrice = 1;
						}
					}
				}
			}

			// Display shipping method options in dropdown if there is more than one method available and the price isn't being 'overriden'
			if (!$overrideShippingPrice && (get_option("pw_express_shipping_fee") || !empty($shippingSelectOptionsKeys))) {
				$selected = null;
				if (Cart::getExpressShipping() == 'on') {
					$selected = 'express_shipping';
				} elseif (!empty(Cart::getShippingMethod())) {
					$selected = Cart::getShippingMethod();
				}

				$baseShippingFee = get_option('pw_shipping_fee');
				$shippingLabel = str_replace('$', '\$', get_option('pw_shipping_fee_message'));

				if (!strlen($shippingLabel)) {
					if (($baseShippingFee === '0') || ($baseShippingFee === '0.00') || ($baseShippingFee === '\$0.00')) {
						$baseShippingFee = 'FREE';
					} else {
						$baseShippingFee = '\$' . $baseShippingFee;
					}
					$shippingLabel = 'Standard Shipping - ' . $baseShippingFee;
				}

				$expressShippingFee = get_option("pw_express_shipping_fee");
				$expressLabel = str_replace('$', '\$', get_option('pw_express_shipping_message'));
				if (!strlen($expressLabel)) {
					$expressLabel = 'Express Shipping (Canadian products shipped within North America only) - \$' . $expressShippingFee;
				}

				$shippingOptions = array(
					array(
						'text' => $shippingLabel,
						'value' => 'standard'
					)
				);

				$shippingDestination = Cart::getShippingDestination();
				if (get_option("pw_express_shipping_fee")) {
					$Canadian = Cart::containsCanadianItems($lstItems);
					$internationalExpress = get_option('pw_international_express_allowed');
					if (!CART::isExpressShippingDisabled() && ($Canadian || $internationalExpress) && ($shippingDestination == 'USA' || $shippingDestination == 'CAN' || !strlen($shippingDestination))) {
						array_push($shippingOptions, array('text' => $expressLabel, 'value' => 'express_shipping'));
					}
				}

				if (!empty($shippingSelectOptionsKeys)) {
					foreach ($shippingSelectOptionsKeys as $key) {
						$shippingO = $pwShippingExtended[$key];
						$visible = Shipping::shippingOptionVisibility($shippingO, $lstItems);
						if ($visible == true) {
							$shippingLabel = str_replace('$', '\$', $shippingO['label']);
							array_push($shippingOptions, array('text' => $shippingLabel, 'value' => $key));
						}
					}
					// after all options are added, perform actions
					foreach ($shippingSelectOptionsKeys as $key) {
						$shippingOption = $pwShippingExtended[$key];
						$visible = Shipping::shippingOptionVisibility($shippingOption, $lstItems);
						if ($visible == true) {
							// actions that are only triggered if the watch_for condition is matched
							if (!empty($shippingOption['watch_for'])) {
								$watchFor = $shippingOption['watch_for'];
								$watchForActive = Shipping::watchForCheck($shippingOption, $lstItems);
								if ($watchForActive) {
									foreach ($watchFor as $watch) {
										if (!empty($watch['action'])) {
											foreach ($watch['action'] as $action => $disableShippingOptionName) {

												switch ($action) {
													case 'disable-shipping':
														// check to see if shipping by key (such as standard exists, if so remove from shipping options)
														if (empty($disableShippingOptionName)) {
															break;
														}
														$disabledKey = [];
														$disableShippingOptionName = (array) $disableShippingOptionName;
	
														foreach ($shippingOptions as $shipIndex => $shipO) {
															if (in_array($shipO['value'], $disableShippingOptionName)) {
																$disabledKey[] = $shipO['value'];
																unset($shippingOptions[$shipIndex]);

															}
														}
														if (!empty($disabledKey)) {
															$shippingOptions = array_merge($shippingOptions);
														}
														
														// if the current shipping rate matches the one being removed,
														// set the rate to the first available rate
														$currentShippingMethod = Cart::getShippingMethod();

														if (in_array($currentShippingMethod, $disabledKey) || (empty($currentShippingMethod))) {
															Cart::setShippingMethod($shippingOptions[0]['value']);
															$shipping_fee = PC_formatPrice(Cart::calculateShippingFee($lstItems));
															$shipping_fee_human = ($shipping_fee == '0.00') ? 'FREE' : '\$' . $shipping_fee;
															$this->assign("SHIPPING_FEE", $shipping_fee_human);
															// update the total as well to reflect the new rate
															$total = $couponSession->applyDiscount($sub_total) + $shipping_fee;
															$total = PC_formatPrice($total);
															$this->assign("TOTAL", $total);
														}
														break;
												}
											}
										}
									}
								}
							}
							// if (!empty($shippingOption['action']) {
							//     // other actions that could occur always regardless if there is a watch_for condition or not
							// }
						}
					}
				}

				if (get_option('pw_shipping_option_display', 'dropdown') === 'radio') {
					// convert shipping options to radioList object format
					$shippingOptionsRadio = array();
					foreach ($shippingOptions as $shipOpt) {
						$shippingOptionsRadio[] = Utility_Html::htmlOption($shipOpt['value'], $shipOpt['text']);
					}
					// if no shipping option selected, choose the first one
					if (is_null($selected)) {
						$selected = $shippingOptionsRadio[0]->value;
					}
					$shippingRadioList = Utility_Html::radioList($shippingOptionsRadio, 'pw_shipping_options', null, 'value', 'text', $selected);
					$this->assign('SHIPPING_OPTIONS', $shippingRadioList);
				} else {
					$shippingOptionsSelect = Utility_Html::htmlSelect($shippingOptions, 'pw_shipping_options', null, 'value', 'text', $selected);
					$this->assign('SHIPPING_OPTIONS', $shippingOptionsSelect);
				}

				$this->parse('TOTAL.SHIPPING_OPTIONS');
			}

			/**************************
			 * Shipping Addons (e.g. Styrofoam conatiners, etc.)
			 */

			if (!empty($shippingAddonKeys)) {
				foreach ($shippingAddonKeys as $key) {
					$shippingAddon = $pwShippingExtended[$key];
					$visible = Shipping::shippingOptionVisibility($shippingAddon, $lstItems);

					if ($visible == true) {
						$this->assign('SHIPPING_ADDON_KEY', $key);
						$this->assign('SHIPPING_ADDON_PRODUCT_ID', $shippingAddon['product_id']);
						$shippingLabel = str_replace('$', '\$', $shippingAddon['label']);
						$this->assign('SHIPPING_ADDON_LABEL', $shippingLabel);

						$activeAddons = CART::getActiveShippingAddons();
						$shippingAddonState = '';
						if (!empty($activeAddons)) {
							$shippingAddonState = (in_array($key, $activeAddons)) ? 'checked' : '';
						}

						$this->assign('SHIPPING_ADDON_STATE', $shippingAddonState);

						$this->parse('TOTAL.SHIPPING_ADDONS.SHIPPING_ADDON');
					}
				}
				$this->parse('TOTAL.SHIPPING_ADDONS');
			}

			$this->parse("TOTAL");

			$couponsEnabled = get_option('pw_enable_coupons', 0);
			$additionalNotes = Cart::getAdditionalNotes();
			if (strlen($additionalNotes)) {
				$this->assign('CART_ADDITIONAL_NOTES_CHECKED', 'checked');
				$this->assign('CART_ADDITIONAL_NOTES', $additionalNotes);
			}

			if ((isset($couponsEnabled) && intval($couponsEnabled) === 1) && (get_option('pw_v4_legacy_mode', 0) != 1)) {
				$couponNonce = wp_create_nonce('coupon-nonce');
				$this->assign('COUPON_NONCE', $couponNonce);

				// check session for active coupon
				$couponSession = new Model_Coupon();
				$currentCoupons = $couponSession->getCouponSession();

				// no coupon in session
				$this->parse("COUPON.COUPON_INPUT");

				if (!empty($currentCoupons)) {
					// there is a coupon so setup display
					foreach ($currentCoupons as $couponCode => $couponData) {
						$this->assign('COUPON_CODE', $couponCode);

						// escape for xtemplate assign
						$couponData['description'] = str_replace('$', '\$', $couponData['description']);

						if ($couponData['usable'] == 'false') {
							$couponDescriptionClass = 'fail';
							$couponDescription = 'can not be used. Please remove the coupon before placing your order or contact customer support for assistance.';
						} else {
							$couponDescriptionClass = 'success';
							$couponDescription = $couponData['description'] . ' has been applied to your order.';
						}
						$this->assign('COUPON_DESCRIPTION_CLASS', $couponDescriptionClass);
						$this->assign('COUPON_DESCRIPTION', $couponDescription);
					}
				}

				$this->parse("COUPON");
			}

			if (!empty(get_option('pw_order_tags')) && (get_option('pw_v4_legacy_mode', 0) != 1)) {
				$orderTag = new Model_OrderTag();
				$pwTags = $orderTag->getValidTags();
				$sessionTags = $orderTag->getTagSession();

				foreach ($pwTags as $tagCode => $tagValue) {
					$tagNonce = wp_create_nonce('tag-nonce');
					$this->assign('TAG_NONCE', $tagNonce);

					$tagResponse = '';
					$tagDisabled = '';
					$tagValue = '';
					$tagAction = 'apply';

					if (isset($sessionTags[$tagCode])) {
						$tagValue = $sessionTags[$tagCode]['value'];
						$tagDisabled = 'disabled';
						$tagAction = 'remove';
						$tagResponse = '<span class="tag-description">' . $sessionTags[$tagCode]['label'] . '</span>: <span class="tag-value">' . $tagValue . '</span> has been applied to your order.';
					}

					$tagData = array(
						'code' => $tagCode,
						'label' => $pwTags[$tagCode]['label'],
						'type' => $pwTags[$tagCode]['type'],
						'value' => $tagValue,
						'response' => $tagResponse,
						'disabled' => $tagDisabled,
						'action' => $tagAction,
					);
					$this->assign('TAG', $tagData);
					$this->parse("TAGS.TAG");
				};

				$this->parse("TAGS");
			}

			$this->parse("BUTTON");

			$checkoutURL = get_option('pw_checkout_url', '/shopping-cart/checkout-login/');
			if (!empty($checkoutURL)) {
				$this->assign("ADD_TO_CART", $checkoutURL);
			}
		}
	}
}
