<?php
class Page_Reorder extends Utility_PageBase
{
	/**
	 * Make the html output
	 *
	 * @return The content in html format
	 */
	public function _process()
	{
		if (!WebUser::isLoggedIn()) {
			$this->redirect(PC_getLoginUrl());
		}
		$searchModel = new Model_Catalog();

		//getReorderInfo loads details of reorders from XML refill request
		$reorder = $this->_getReorderInfo();

		$this->_prepareReorderResult($reorder);
	}

	/**
	 * Prepare detail result
	 *
	 * @param mixed $result
	 */
	public function _prepareReorderResult($reorder)
	{
		$this->setTemplate('page_reorder');

		if (empty($reorder)) {
			$this->assign('NO_RECENT_PRODUCTS_FOUND', '<div class="no-recent-products-found">No recent products or prescriptions found. Please call us if you need assistance.</div>');
			$this->parse('noRecentProductsFound');
			return;
		}
		$this->_prepareReorder($reorder);
	}
	/**
	 * Prepare links to ingredient pages (if any)
	 *
	 * @param comma seperated list of ingredients
	 */
	public function _prepareIngredientUrls($ingredientsArr)
	{
		$ingredientLinks = array();
		foreach ($ingredientsArr as $ingredient) {
			$ingredientUrl = PC_getIngredientUrl($ingredient->getData('ingredient_name'));
			$ingredientLinks[] = $ingredientUrl;
		}
		$result = implode(', ', $ingredientLinks);
		return $result;
	}

	/**
	 * Preparing display the drugs
	 *
	 * @param mixed $detailResult
	 */
	public function _prepareReorder($reorders)
	{
		$checkout_url = PC_getShoppingURL();
		$this->assign('ADD_TO_CART_URL', $checkout_url);
		$formId = strval(rand()) . '-' . strval(rand());
		$this->assign('FORM_ID', $formId);

		$showCanadianDrugsOnly = Utility_Common::showOnlyCanadianDrugs();

		foreach ($reorders as $reorder) {
			$drug = $reorder->Drug;
			$prescription = null;

			if (isset($reorder->Prescription)) {
				$prescription = $reorder->Prescription;
			}
			// $purchasedProducts = $reorder->PurchasedProduct;
			$ingredient = $drug->ingredient;
			$ingredientList = trim($this->_prepareIngredientUrls($ingredient));
			$this->assign('INGREDIENT_LIST', $ingredientList);
			if ($ingredientList) {
				$this->parse('prescription.drugIngredientsList');
			}

			if (!empty($reorder->LastOrderDate)) {
				$this->assign('LAST_ORDER_DATE', $reorder->LastOrderDate);
				$this->parse('prescription.lastOrderedDate');
			}

			if (!empty($reorder->LastOrderID)) {
				$this->assign('LAST_ORDER_ID', $reorder->LastOrderID);
			}

			if ($drug->condition) {
				$condition = $drug->condition;
				$this->assign('CONDITION', $condition);
				$this->parse('prescription.drugCondition');
			}
			$PurchasedProduct_count_total = 0;
			$AlternativeProduct_count_total = 0;
			$package_settings = array('PurchasedProduct', 'AlternativeProduct');

			foreach ($package_settings as $package_setting) {
				$packages = $reorder->$package_setting;
				$productInCart = false;

				$first_pass = true; // don't flush on the first pass.
				$current_brand_heading = 0;

				if (Utility_Common::showPackageNameOnSearchResults()) {
					uasort($packages, function ($a, $b) {
						$str_a = (string) $a->getData('product');
						$str_b = (string) $b->getData('product');
						$nameCompare = strcmp($str_a, $str_b);
						if ($nameCompare != 0) {
							return $nameCompare;
						}
						return ($a->getData('price') <=> $b->getData('price'));
					});
				} else {
					// sort by name followed by price low -> high
					uasort($packages, function ($a, $b) use ($reorder) {
						$str_a = strtolower($reorder->AlternativeDrugs[$a->getData('drug_id')]->name);
						$str_b = strtolower($reorder->AlternativeDrugs[$b->getData('drug_id')]->name);
						$nameCompare = strcmp($str_a, $str_b);
						if ($nameCompare !== 0) {
							return $nameCompare;
						}
						return ($a->getData('price') <=> $b->getData('price'));
					});
				}

				$rowCount = 0;
				$lastDrugStrength = null;
				foreach ($packages as $package) {
					if ($package_setting === 'PurchasedProduct') {
						$drug_alternative = $reorder->Drug;
					} elseif ($package_setting === 'AlternativeProduct') {

						$packageCountryCode = $package->getScheduleCountry();
						if ($showCanadianDrugsOnly && ($packageCountryCode != 'CAN') ) {
							continue;
						}

						$drug_alternative = $reorder->AlternativeDrugs[(string) $package->getData('drug_id')];
					}

					if (Utility_Common::showPackageNameOnSearchResults()) {
						if (!($first_pass) && $current_brand_heading !== $package->product) {
							$this->parse('prescription.alternative_section.alternative');
						}
						$current_brand_heading = $package->product;
					} else {
						if (!($first_pass) && $current_brand_heading !== $drug_alternative->name) {
							$this->parse('prescription.alternative_section.alternative');
						}
						$current_brand_heading = $drug_alternative->name;
					}

					$first_pass = false;

					$packageID = $package->package_id;
					$drugType = $drug_alternative->getCondition();
					$prama = '';
					$url_addtocart = PC_reCreateUrl($prama, $checkout_url);

					$rxRequired = '<div class="otc drug-info-icon">Over the Counter</div>';

					if ($drug_alternative->prescriptionrequired) {
						$rxRequired = '<div class="rx-required rx drug-info-icon">Prescription Required</div>';
					}
					$countryCode = $drug_alternative->getScheduleCountry();
					### NEEDS TO BE COMPLETED BY DANIEL
					//$countryCode = $package->getScheduleCountry();

					$countryModel = new Model_Country();
					$countryCodeHuman = $countryModel->getCountryByCode($countryCode);
					$countryCodeHuman = (empty($countryCodeHuman) ? 'unknown' : $countryCodeHuman);

					$countryFlag = ($countryCodeHuman == 'unknown') ? '' : '<img class="country-flag" src="' . THEME_URL . 'images/flags/' . $countryCode . '.png" alt="Source: ' . $countryCodeHuman . '" title="Source: ' . $countryCodeHuman . '" />';

					if ($drug_alternative->generic) {
						$brandOrGeneric = '<div class="generic gen drug-info-icon">Generic</div>';
						$brandOrGenericLabel = 'generic';
					} else {
						$brandOrGeneric = '<div class="brand brd drug-info-icon">Brand</div>';
						$brandOrGenericLabel = 'brand';
					}

					$this->assign('BRAND_OR_GENERIC_LABEL', $brandOrGenericLabel);

					$drugStrength = Utility_Common::getFullValue($drug_alternative->strength);

					$drugClass = ($rowCount % 2) ? 'drug-row' : 'drug-row alt';

					if ($lastDrugStrength == $drugStrength) {
						$drugClass = $drugClass . '';
					} else {
						$drugClass = $drugClass . ' new-str';
						$lastDrugStrength = $drugStrength;
					}

					$lstItems = Cart::getListItems();

					$countItems = count($lstItems);
					$inCart = false;
					foreach ($lstItems as $item) {
						if ($item->package_id == $package->package_id) {
							$inCart = true;
						}
					}

					$shoppingCartUrl = PC_getShoppingURL();

					if ($inCart == true) {
						$addToCart = '<div class="action shoppingcart-checkout"><div class="product-in-cart">Product in Cart</div><input type="button" class="button" alt="Submit" value="Go to Checkout" name="checkout" onclick="window.location =\'' . $shoppingCartUrl . '\'"/></div>';
						$productInCart = true;
					} else {
						if ($package_setting === 'AlternativeProduct') {
							$orderLabel = 'Add to Cart';
						} else {
							$orderLabel = 'Re-Order';
						}

						$addToCart = "<div class=\"action shoppingcart-checkout\"><button class=\"addtocart_btn add-drugpackage-to-cart button\">" . $orderLabel . "</button></div>";
					}

					if ($productInCart) {
						$drugClass = $drugClass . ' in-cart';
						$alternativeLabel = 'Hide Alternatives';
						$introMessage = '<div class="reorder-notification">Product added to cart: <a href="' . $shoppingCartUrl . '">go to checkout</a>.</div>';
						$this->assign('INTRO_MESSAGE', $introMessage);
					} else {
						$alternativeLabel = 'Show Alternatives';
					}

					$this->assign('ROW', array(
						'formId' => $formId,
						'category' => $drugType,
						'form' => $drug_alternative->form,
						'strength' => $drugStrength,
						'strengthUnit' => Utility_Common::getFullValue($drug_alternative->strength_unit),
						'quantity' => Utility_Common::getQuantity($package->packagequantity, $package->packagingfreeform),
						'price' => PC_formatPrice($package->price),
						'packageId' => $package->package_id,
						'rxRequired' => $rxRequired,
						'countryFlag' => $countryFlag,
						'country' => $countryCodeHuman,
						'brandOrGeneric' => $brandOrGeneric,
						'drugClass' => trim($drugClass),
						'addToCart' => $addToCart,
						'alternativeLabel' => $alternativeLabel,
					));

					if ($package_setting === 'PurchasedProduct') {
						$PurchasedProduct_count_total = $PurchasedProduct_count_total + 1;
						if ($rowCount === 0) {
							$this->parse('prescription.prescriptionPackageHead');
						}
						$this->parse('prescription.prescriptionPackage');
					} elseif ($package_setting === 'AlternativeProduct') {
						$AlternativeProduct_count_total = $AlternativeProduct_count_total + 1;
						$this->parse('prescription.alternative_section.alternative.alternativePackage');
					}
					$rowCount++;

					if (Utility_Common::showPackageNameOnSearchResults()) {
						$this->assign('BRAND_NAME', $package->product);
					} else {
						$this->assign('BRAND_NAME', $drug_alternative->name);
					}
				}

				if ($package_setting === 'AlternativeProduct' && $AlternativeProduct_count_total > 0) {
					// flush as this was the last package to be processed.
					if ($PurchasedProduct_count_total == 0) {
						$this->parse('prescription.alternative_section.text_force_alternatives');
						$this->parse('prescription.alternative_section.force_alternatives');
					} else {
						$this->parse('prescription.alternative_section.text_toggle_alternatives');
						$this->parse('prescription.alternative_section.toggle_alternatives');
					}
					$this->parse('prescription.alternative_section.alternative');
					$this->parse('prescription.alternative_section');
				}
			}

			if (0 == ($PurchasedProduct_count_total + $AlternativeProduct_count_total)) {
				// no packagegs found or used then call the no selections possible..
				$this->parse('prescription.no_selections_available');
			}

			$this->assign('BRAND_NAME', $drug->name . ' ' . $drug->strength . ' ' . Utility_Common::getFullValue($drug->strength_unit));
			if (isset($prescription)) {
				$this->assign('INSTRUCTIONS', $prescription->instructions);
				$this->assign('REMAINING', $prescription->fill->remaining);
				$this->assign('TYPE', $prescription->fill->type);
				$this->assign('PRESCRIPTION_EXPIRY', 'n/a');
				if ($prescription->fill->expiry) {
					$this->assign('PRESCRIPTION_EXPIRY', $prescription->fill->expiry);
				}
				$this->assign('FILLED', $prescription->fill->dispensed);
				$rxnumber = '';
				if ($prescription->rxNumber) {
					$rxnumber = $prescription->rxNumber;
				}
				if (!empty($rxnumber)) {
					$this->assign('PRESCRIPTION_NUMBER', $rxnumber);
					$this->parse('prescription.prescriptionNumber');
				}
				$this->parse('prescription.prescriptionInfo');
			} else {
				$this->assign('INSTRUCTIONS', '');
				$this->assign('REMAINING', '');
				$this->assign('TYPE', '');
				$this->assign('PRESCRIPTION_EXPIRY', '');
				$this->assign('FILLED', '');
				$this->assign('PRESCRIPTION_NUMBER', '');
				$this->assign('PHARMACY_RX_EMAIL', EMAIL_COMPANY);
				$this->parse('prescription.noActivePrescription');
			}

			$this->parse('prescription');
		}
	}

	public function _getReorderInfo()
	{
		$patientModel = new Model_Patient();

		$patient = new Model_Entity_Patient();
		$patient->patientid = $this->_getPatientId();
		$result = $patientModel->getRefillInfo($patient->patientid);
		$reorder = array();
		$searchModel = new Model_Resource_Search();
		if (is_countable($result->prescriptions)) {
			foreach ($result->prescriptions as $prescription) {
				if (array_key_exists((string) $prescription->drug->id, $reorder)) {
					continue;
				}
				$item = new stdClass();

				$drugs = $searchModel->getDrugById($prescription->drug->drug_id);
				if (!isset($drugs[0])) {
					//use drug info from refill info
					if (!(isset($prescription->drug->_rxdruginfo) && $prescription->drug->_rxdruginfo)) {
						continue;
					}
					$drug_instance = array(
						'drug_id' => $prescription->drug->drug_id,
						'public_viewable' => $prescription->drug->public_viewable,
						'name' => $prescription->drug->name,
						'familyname' => $prescription->drug->familyname,
						'strengthfreeform' => $prescription->drug->strengthfreeform,
						'strength' => $prescription->drug->strength,
						'strength_unit' => $prescription->drug->strength_unit,
						'form' => $prescription->drug->form,
						'ingredient_hash' => $prescription->drug->ingredient_hash,
						'ingredient' => $prescription->drug->ingredient,
						'udn' => $prescription->drug->udn,
						'schedule' => $prescription->drug->schedule,
						'manufacturer' => $prescription->drug->manufacturer,
						'generic' => $prescription->drug->generic,
						'comment_external' => $prescription->drug->comment_external,
						'condition' => $prescription->drug->condition,
						'condition_id' => $prescription->drug->condition_id,
						'category' => $prescription->drug->category,
						'prescriptionrequired' => $prescription->drug->prescriptionrequired,
					);

					$drug = new Model_Entity_Drug();
					$drug->setData($drug_instance);
					if (!$drug->familyname) {
						$drug->familyname = $drug->name;
					}
					if (!$drug->strength) {
						$drug->strength = $drug->strengthfreeform;
						$drug->strengthunit = '';
					}
					$item->Drug = $drug;
				} else {
					$item->Drug = $drugs[0];
				}

				$item->Prescription = $prescription;
				$item->PurchasedProduct = array();
				$reorder[(string) $item->Drug->drug_id] = $item;
			}
		}

		$result_recentOrder = $patientModel->getRecentOrders($patient->patientid);
		if (Utility_Common::isReplySuccess($result_recentOrder)) {
			if (count($result_recentOrder->orders) > 0) {
				foreach ($result_recentOrder->orders as $order) {
					foreach ($order->items as $line_item) {
						$drug = $searchModel->getDrugByPackageId($line_item->id);
						if (!isset($drug)) {
							continue;
						}
						if (array_key_exists((string) $drug->drug_id, $reorder)) {
							$item = $reorder[(string) $drug->drug_id];
						} else {
							$item = new stdClass();
							$item->Drug = $drug;
							$reorder[(string) $item->Drug->drug_id] = $item;
						}

						if (empty($item->LastOrderID) || (!empty($item->LastOrderID) && ($order->id > $item->LastOrderID))) {
							$item->LastOrderID = $order->id;
							$item->LastOrderDate = $order->created;
						}

						if (isset($item->PurchasedProduct[(string) $line_item->id])) {
							continue;
						} else {
							$search_package = new Model_Entity_Package();
							$search_package = $searchModel->getPackageObj($line_item->id);

							if ($search_package->public_viewable) {
								$item->PurchasedProduct[(string) $line_item->id] = $search_package;
							}
						}
					}
				}
			}
		}

		// load alternative products per drug.
		foreach ($reorder as $item) {
			$drug = $item->Drug;
			$item->AlternativeProduct = array();
			$item->AlternativeProductByDrug = array();

			$ingredientHashes = array($drug->getData('ingredient_hash'));
			// get alternative drugs and ignore reference drugs
			$alternative_drugs = $searchModel->getDrugsByHash($ingredientHashes, 1);
			foreach ($alternative_drugs as $alternative_drug) {
				$drugActualStrength = $drug->getData('strengthfreeform');
				if (empty($drugActualStrength)) {
					$drugActualStrength = $drug->getData('strength') . $drug->getData('strength_unit');
				}
				$altActualStrength = $alternative_drug->getData('strengthfreeform');
				if (empty($altActualStrength)) {
					$altActualStrength = $alternative_drug->getData('strength') . $alternative_drug->getData('strength_unit');
				}
				if ($drugActualStrength !== $altActualStrength) {
					continue;
				}

				$packages = $searchModel->getViewablePackages($alternative_drug->drug_id);
				$alternative_drug->setPreferred($packages);
				foreach ($packages as $package) {
					if (isset($item->AlternativeProduct[(string) $package->package_id]) || isset($item->PurchasedProduct[(string) $package->package_id])) {
						continue;
					} else {
						$item->AlternativeProduct[(string) $package->package_id] = $package;
						$item->AlternativeDrugs[(string) $alternative_drug->drug_id] = $alternative_drug;
						if (!(isset($item->AlternativeProductByDrug[(string) $alternative_drug->drug_id]))) {
							$item->AlternativeProductByDrug[(string) $alternative_drug->drug_id] = array();
						}
						$item->AlternativeProductByDrug[(string) $alternative_drug->drug_id][] = $package;
					}
				}
			}
		}

		return $reorder;
	}

	public function _getPatientId()
	{
		return WebUser::getUserID();
	}
}
