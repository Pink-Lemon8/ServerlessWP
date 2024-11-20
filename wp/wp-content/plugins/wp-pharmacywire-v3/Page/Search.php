<?php
class Page_Search extends Utility_PageBase
{
	/**
	 * Make the html output
	 *
	 * @return The content in html format
	 */
	public function _process($json = null, $refineSearch = null)
	{
		$searchModel = new Model_Catalog();
		$messages = new Utility_Messages;
		$drugId = $this->_getRequest('drugId');
		$hide = $this->_getRequest('hideNotifications');

		$filter = $this->_getRequest('filter', 'all');
		if ($filter != 'rx' && $filter != 'otc' && $filter != 'all' && $filter != 'generic') {
			$filter = 'all';
		}

		$rxRequired = $this->_getRequest('rxrequired', 'both');
		if ($rxRequired != 'yes' && $rxRequired != 'no') {
			$rxRequired = 'both';
		}
		$conditionSearch = false;

		$result = new stdClass();

		// search drug detail by ID
		if (!empty($drugId)) {
			$result = $searchModel->searchDrugDetail($drugId);
			$drugName = $this->_getRequest('drugName', '');
			$searchName = $drugName;
			$messages->setPageTitle('Medication: ' . $drugName);
		} else {
			$showAll = $this->_getRequest('showall');

			// search drug by name
			if (empty($showAll)) {
				$searchName = $refineSearch ? $refineSearch : $this->_getRequest('drugName', '');
				$conditionName = $this->_getRequest('condition', '');
			} else {
				$searchName = '';
			}

			$searchName = trim($searchName);

			if ($searchName != '') {
				// if ingredients (in brackets) were included in the search term, remove them to search against drug name
				// Ex. 'humalog vial (insulin lispro), searches for 'humalog vial' first
				// Then if 'humalog vial' has no matches, it will do a string split to search for 'humalog' lastly
				if (preg_match('/^([\w\s]+)\s*\(.*/', $searchName, $searchNameMatch)) {
					$searchName = trim($searchNameMatch[1]);
				}

				// function searchDrugs($keyword, $searchType = 'search', $filter = 'all', $strength = -1, $limit = -1,$related = "",$detail = "false")
				$result = $searchModel->searchDrugs($searchName, 'search', $filter, -1, -1, "", true, $rxRequired);
			}

			if (!empty($result->data) && $result->isUniqueProduct) {
				$drugLanding = PC_getDrugPageUrl($searchName);
				if ($drugLanding != '') {
					$this->redirect($drugLanding);
				}
			} elseif (empty($result->data)) {
				if ($searchName == '') {
					$searchName = $conditionName;
				} else {
					// Added support to display conditions as a result instead of show drugs. Looks much nicer
					$conditionResult = $searchModel->searchConditions($searchName);
					if (sizeof($conditionResult->data)) {
						$this->_prepareConditionResult($conditionResult, false);
						return;
					}
				}
				$result = $searchModel->searchDrugs($searchName, 'condition', 'all', -1, -1, '', 'never'); // Condition searches should not show detail

				$conditionSearch = true;
				if (count($result->data)) {
					$messages->setPageTitle('Medical Condition: ' . $searchName);
					if (!strlen($conditionName) && !$hide) {
						$messages->setNotification('Information', "Your search for '$searchName' found the following results:");
					}
					$conditionLanding = PC_getDrugPageUrl($searchName);
					if ($conditionLanding != '') {
						$this->redirect($conditionLanding);
					}
				}
			}
		}

		// display result
		if ($json) {
			if ($result->isDetailSearch || $this->_getRequest('showdetail')) {
				// method doesn't exist yet
				// return $this->_prepareJsonDetail($result);
			} else {
				return $this->_prepareJsonSummary($result, strlen($searchName) == 1, $searchName);
			}
		} elseif ($result->isDetailSearch || $this->_getRequest('showdetail')) {
			$this->_prepareDetailResult($result, $searchName, false);
		} else {
			if (!count($result->data)) {
				$searchTerms = explode(' ', trim($searchName));
				if (count($searchTerms) > 1) {
					return $this->_process($json, $searchTerms[0]);
				}
			}
			$this->_prepareSummaryResult($result, strlen($searchName) == 1, $searchName, false, $conditionSearch);
		}
	}

	/**
	 * Prepare detail result
	 *
	 * @param mixed $result
	 */
	public function _prepareDetailResult($result, $searchName, $isShortCode, $templateOverride = '')
	{
		$templateArr = ['page_search_detail'];
		if (!empty($templateOverride)) {
			array_unshift($templateArr, $templateOverride);
		}

		$this->setTemplate($templateArr);

		if (!$isShortCode) {
			$this->assign('PAGE_WRAPPER_OPEN', '<div class="pw-pharmacy-wrap">');
			$this->assign('PAGE_WRAPPER_CLOSE', '</div>');
			$this->parse('headings');
		}

		$this->_prepareDrugs($result->data, $searchName);

		$this->_prepareStructuredData();
	}

	/**
	 * Prepare condition result
	 *
	 * @param mixed $result
	 */
	public function _prepareConditionResult($result, $isShortCode)
	{
		$this->setTemplate('page_search_conditions');
		$conditions = $result->data;

		if (!$isShortCode) {
			$this->parse('headings');
		}

		$searchUrl = PC_getSearchURL();
		foreach ($conditions as $condition) {
			$encodedCondition = rawurlencode($condition['condition']);
			$link = PC_getDrugPageUrl($condition['condition']);
			if ($link == '') {
				$link =	 PC_reCreateUrl('condition=' . $encodedCondition, $searchUrl);
			}
			$this->assign('CONDITION_LINK', $link);
			$this->assign('CONDITION_ITEM', $condition['condition']);
			$this->parse("showconditions.condition");
		}
		$this->parse("showconditions");
	}


	/**
	 * Prepare links to ingredient pages (if any)
	 *
	 * @param comma seperated list of ingredients
	 */
	public function _prepareIngredientUrls($ingredients)
	{
		$ingredientsArr = explode(', ', $ingredients);
		$ingredientLinks = array();
		foreach ($ingredientsArr as $ingredient) {
			$ingredientUrl = PC_getIngredientUrl($ingredient);

			$ingredientLinks[] = $ingredientUrl;
		}
		$result =  implode(', ', $ingredientLinks);
		return $result;
	}

	/**
	 * Preparing display the drugs
	 *
	 * @param mixed $detailResult
	 */
	public function _prepareDrugs($detailResult, $searchName)
	{

		/* Let's get the related post's feature image. */
		/* Note that this uses capabilities of advance custom fields - so check the function from that plugin exists first */
		global $post;

		$drugDescriptionFromTheme = 0;
		$featureImage = '';
		$pageFeatureImage = '';
		if ($post && function_exists('get_field')) {
			$pageFeatureImage = wp_get_attachment_url(get_post_thumbnail_id($post->ID));
			$brandField = get_field('brand_image');
			if (!empty($brandField)) {
				$brandImage = $brandField['url'] ?? '';
			}
			$genericField = get_field('generic_image');
			if (!empty($genericField)) {
				$genericImage = $genericField['url'] ?? '';
			}
			$drugDescription = '';
			if (!empty(get_field('drug_description'))) {
				$drugDescriptionFromTheme = 1;
				$drugDescription = get_field('drug_description');
			}
		}

		if (count($detailResult)) {
			$keys = array_keys($detailResult);
			/* Create a unique identifier for the form */
			$formId = strval(rand()) . '-' . strval(rand());
			$genericCount = 0;
			$brandCount = 0;
			$rxStatusItem = 'rx';
			$packageCondition = '';
			$drugAggregateData = [];
			$drugLowPrice = 0;
			$drugHighPrice = 0;
			$packageCount = 0;
			$productSchemaDrugName = '';
			$productSchema = array();
			$drugSchema = array();
			// For each DRUG CARD
			foreach ($keys as $key) {
				$this->assign('FORM_ID', $formId);

				$drugResultObject = $detailResult[$key];

				if (count($detailResult[$key]->packages) == 0) {
					continue;
				}

				if ($detailResult[$key]->drug->generic) {
					$this->parse('brandDrug.genericDrug');
				}

				$brandName = str_replace('"', "'", $detailResult[$key]->displayName);
				$this->assign('BRAND_NAME', $brandName);
				$this->assign('DRUG_FAMILY_NAME', $detailResult[$key]->drug->familyname);

				$ingredients = $detailResult[$key]->ingredients;
				$this->assign('INGREDIENT_LIST', $this->_prepareIngredientUrls($ingredients));

				if ($detailResult[$key]->drug->condition) {
					$condition = $detailResult[$key]->drug->condition;
					$this->assign('CONDITION', $condition);
					$this->parse('brandDrug.drugCondition');
				}

				if ($detailResult[$key]->drug->isScheduleReference()) {
					$this->assign('REFERENCE_DRUG', 'reference-drug');
				} elseif (!$detailResult[$key]->drug->isAvailable()) {
					$message = $detailResult[$key]->drug->getScheduleMessage();
					$this->assign('SCHEDULE_MESSAGE', $message);
				} else {
					$checkout_url = PC_getShoppingURL();
					$this->assign('ADD_TO_CART_URL', $checkout_url);
					$firstRow = true;
					$rowCount = 0;

					$packages = array();

					if (get_option('pw_enable_bestprice')) {
						$bestCost = array();
						foreach ($detailResult[$key]->packages as $package) {
							$strengthKey = $package->drug->strengthfreeform;
							if ($strengthKey == "") {
								$strengthKey = $package->drug->strength;
							}
							if (is_null($bestCost[$strengthKey])) {
								$bestCost[$strengthKey] = $package;
							} else {
								$packagePrice = $package->price / $package->packagequantity;
								$bestPrice = $bestCost[$strengthKey]->price / $bestCost[$strengthKey]->packagequantity;
								if ($packagePrice < $bestPrice) {
									$bestCost[$strengthKey] = $package;
								}
							}
						}
						$packages = $bestCost;
					} else {
						$packages = $detailResult[$key]->packages;
					}

					$searchSettings = array();
					$searchSettings['tier-pricing'] = 0;

					foreach ($packages as $key => $package) {
						$packageDataArray = array();
						$drugDataArray = array();

						if ($firstRow) {
							$drugType = $package->drug->getCondition();
							$firstRow = false;
							$lastDrugStrength = '';
						} else {
							$drugType = '';
						}

						$rxRequired = '<div class="otc drug-info-icon">Over the Counter</div>';
						$rxStatusItem = 'otc';
						if ($package->drug->prescriptionrequired) {
							$rxRequired = '<div class="rx-required rx drug-info-icon">Prescription Required</div>';
							$rxStatusItem = 'rx';
							// Limit ordering of prescription products to 90 day supply limit
							$qtyOrderLimit = 3;
						} else {
							$qtyOrderLimit = 10;
						}

						$this->assign("ITEM_RX_STATUS", $rxStatusItem);

						$countryCode = $package->getScheduleCountry();

						$countryModel = new Model_Country();
						$countryCodeHuman = $countryModel->getCountryByCode($countryCode);
						$countryCodeHuman = (empty($countryCodeHuman) ? 'unknown' : $countryCodeHuman);

						$flagBaseURL = THEME_URL . 'images/flags/';
						$countryFlag = ($countryCodeHuman == 'unknown') ? '' : '<img class="country-flag" src="' . $flagBaseURL . $countryCode . '.png" alt="Sourced from ' . $countryCodeHuman . '" title="Sourced from ' . $countryCodeHuman . '" />';

						$vendorCountryModel = new Model_Country();
						$vendorCountryCode = $package->getVendorCountryCode();
						$vendorCountryHuman = $vendorCountryModel->getCountryByCode($vendorCountryCode);
						$vendorCountryHuman = (empty($vendorCountryHuman) ? 'unknown' : $vendorCountryHuman);

						$countryOfOModel = new Model_Country();
						$countryOfOriginCode = $package->origin_country_code;
						$countryOfOrigin = $countryOfOModel->getCountryByCode($countryOfOriginCode);

						$featureImage = $pageFeatureImage;
						if ($package->drug->generic) {
							$brandOrGeneric = '<div class="generic gen drug-info-icon">Generic</div>';
							$brandOrGenericAbbr = 'gen';
							$brandOrGenericLabel = 'generic';
							if (!empty($genericImage)) {
								$featureImage = $genericImage;
							}
							$genericCount++;
						} else {
							$brandOrGeneric = '<div class="brand brd drug-info-icon">Brand</div>';
							$brandOrGenericAbbr = 'brd';
							$brandOrGenericLabel = 'brand';
							if (!empty($brandImage)) {
								$featureImage = $brandImage;
							}
							$brandCount++;
						}

						$this->assign('BRAND_OR_GENERIC_ABBR', $brandOrGenericAbbr);
						$this->assign('BRAND_OR_GENERIC_LABEL', $brandOrGenericLabel);

						$drugStrength = Utility_Common::getFullValue($package->drug->strength);
						$drugClass = ($rowCount % 2) ? 'drug-row alt' : 'drug-row';

						if ($lastDrugStrength == $drugStrength) {
							$drugClass = $drugClass . '';
						} else {
							$drugClass = $drugClass . ' new-str';
							$lastDrugStrength = $drugStrength;
						}

						$drugStrengthClass = 'dose-' . preg_replace('/\W/', "-", $drugStrength);
						$drugClass .= ' ' . $drugStrengthClass;
						if ($package->preferred) {
							$drugClass .= ' preferred';
						}

						$buyButton = "<button class=\"addtocart_btn add-drugpackage-to-cart button\">" . get_option('pw_buy_label', 'Add to Cart') . "</button>";

						$pwExternalBuyUrl = get_option('pw_externalbuy_url');
						// If External Buy Url option has been set use that URL for the buyButton link
						if (strlen($pwExternalBuyUrl)) {
							$pwExternalBuyUrl = preg_replace('/\[PACKAGE\]/', $package->package_id, $pwExternalBuyUrl);
							$buyButton = "<a class=\"addtocart_btn button\" href=\"$pwExternalBuyUrl\">" . get_option('pw_buy_label', 'Add to Cart') . "</a>";
						}
						$packageQuantity = Utility_Common::getBaseQuantity($package->packagequantity, $package->packagingfreeform);
						if (!$packageQuantity) {
							$packageQuantity = 1;
						}
						$packagePrice = $package->price + 0;
						$unitPrice = $packagePrice / $packageQuantity;

						$tierQuantity = $package->tier_quantity;

						if (!Utility_Common::validTierQuantity($package, $tierQuantity)) {
							if (!empty($packages[$key + 1])) {
								// before skipping invalid tier, check to see if the dropdown should be closed off/output before moving onto next
								// such as if maxitemqty is reached
								$nextPackage = $packages[$key + 1];
								$nextDrugStrength = Utility_Common::getFullValue($nextPackage->drug->strength);
								// If option to split dropdown into seperate drug strengths is active,
								// output each unique strength into it's own dropdown
								if ((get_option('pw_drug_dropdown_seperate_str', 'on')) && (($drugStrength != $nextDrugStrength))) {
									$this->parse('brandDrug.brandPackageDropdown');
								}
							}
							continue;
						}

						if (is_numeric($tierQuantity)) {
							$packagePrice = PC_formatPrice($packagePrice * $tierQuantity);
							$searchSettings['tier-pricing'] = 1;
						}

						// explicitly set $drugPreferred to 0 if it's not set, for templating purposes
						$drugPreferred = ($package->drug->preferred == 1) ? 1 : 0;

						$packageQuantity = Utility_Common::getQuantity($package->packagequantity, $package->packagingfreeform, $tierQuantity);

						// If logged in as admin/editor show tooltip with pacakge ID to aid in looking up the
						// package in PharmacyWire
						$packageIdTooltip = '';
						if (current_user_can('editor') || current_user_can('administrator')) {
							$packageIdTooltip = 'title=" DrugID: ' . $package->drug->drug_id . ' // PackageID: ' . $package->package_id . ' - ' . $brandOrGenericLabel . ' // Str: ' . $drugStrength . Utility_Common::getFullValue($package->drug->strength_unit) . '"';
						}

						$packageAttributes = $package->public_attributes;
						$packageAttrClass = array();

						if (is_array($packageAttributes)) {
							foreach ($packageAttributes as $pAttrKey => $pAttrVal) {
								$packageDataArray['attributes'][$pAttrKey] = $pAttrVal;

								$pAttrK = 'attr-' . sanitize_html_class($pAttrKey);
								$pAttrV = sanitize_html_class($pAttrVal);
								$pClassAttrVal = $pAttrK . '-' . $pAttrV;
								// use sanitized attribute key as well as key-val compbination as classes
								array_push($packageAttrClass, $pAttrK);
								array_push($packageAttrClass, $pClassAttrVal);
							}
						}

						$drugAttributes = $package->drug->public_attributes;
						$drugAttrClass = array();

						if (is_array($drugAttributes)) {
							foreach ($drugAttributes as $dAttrKey => $dAttrVal) {
								$drugDataArray['attributes'][$dAttrKey] = $dAttrVal;
								$dAttrK = 'attr-' . sanitize_html_class($dAttrKey);
								$dAttrV = sanitize_html_class($dAttrVal);
								$dClassAttrVal = $dAttrK . '-' . $dAttrV;
								// use sanitized attribute key as well as key-val compbination as classes
								array_push($drugAttrClass, $dAttrK);
								array_push($drugAttrClass, $dClassAttrVal);
							}
						}

						$this->assign('DRUG_COMMENT_EXTERNAL', $package->drug->comment_external);

						$manufacturer = $package->manufacturer ? $package->manufacturer : $package->drug->manufacturer;
						$drugID = $package->drug->drug_id;
						$packageID = $package->package_id;

						$drugIngredients = $drugResultObject->ingredients;
						$dosageForm = $package->drug->form;
						$drugUnitDescription = $drugStrength;
						if (!empty($dosageForm)) {
							$drugUnitDescription = $drugStrength . ' ' . $dosageForm;
						}

						$drugSchemaIngredients = '';
						if (!empty($drugIngredients) && ($brandName != $drugIngredients)) {
							$drugSchemaIngredients = ' (' . $drugIngredients . ')';
						}
						$drugSchemaName = $brandName . $drugSchemaIngredients;

						if (preg_match('~^' . $searchName . '~i', $drugSchemaName)) {
							$productSchemaDrugName = $drugSchemaName;
						}

						$drugSchemaArray = array(
							'drugID' => $drugID,
							'drugName' => $drugSchemaName,
							'dosageForm' => $package->drug->form,
							'drugStrength' => $drugStrength,
							'ingredients' => $drugResultObject->ingredients,
							'rxrequired' => $package->drug->prescriptionrequired,
							'brandOrGeneric' => $brandOrGenericLabel,
							'drugUnit' => $drugUnitDescription,
						);
						// group by name
						if (!array_key_exists($drugSchemaName, $drugSchema)) {
							$drugSchema[$drugSchemaName] = $drugSchemaArray;
							$drugAggregateData[$drugSchemaName] = [
								'lowPrice' => 0,
								'highPrice' => 0,
								'packageCount' => 0,
							];
						}

						$packageSchemaArray = array(
							'drugUnit' => $drugUnitDescription,
							'costPerUnit' => PC_formatPrice($unitPrice),
							'brandOrGeneric' => $brandOrGenericLabel,
							'strengthUnit' => $drugStrength,
							'quantity'	=> $packageQuantity,
							'manufacturer' => $manufacturer,
						);

						$drugSchema[$drugSchemaName]['packages'][$drugStrength][] = $packageSchemaArray;

						$drugHighPrice = ($drugHighPrice <= $packagePrice) ? $packagePrice : $drugHighPrice;
						$drugLowPrice = (($drugLowPrice == 0) || ($drugLowPrice >= $packagePrice)) ? $packagePrice : $drugLowPrice;

						if (!empty($drugAggregateData[$drugSchemaName])) {
							$dLowPrice = $drugAggregateData[$drugSchemaName]['lowPrice'];
							$dHighPrice = $drugAggregateData[$drugSchemaName]['highPrice'];
							if ($dHighPrice <= $packagePrice) {
								$dHighPrice = $packagePrice;
							}
							if (($dLowPrice == 0) || $dLowPrice >= $packagePrice) {
								$dLowPrice = $packagePrice;
							}
							$drugAggregateData[$drugSchemaName]['lowPrice'] = $dLowPrice;
							$drugAggregateData[$drugSchemaName]['highPrice'] = $dHighPrice;
						}

						// Package Variables
						$packageInfo = array(
							'formId' => $formId,
							'category'	=> $drugType,
							'featureImage' => $featureImage,
							'form'		=> $package->drug->form,
							'manufacturer' => $manufacturer,
							'strength'	=> $drugStrength,
							'strengthUnit'	=> Utility_Common::getFullValue($package->drug->strength_unit),
							'strengthClass' => $drugStrengthClass,
							'quantity'	=> $packageQuantity,
							'doseType'  => Utility_Common::getDoseType($package->packagequantity, $package->packagingfreeform),
							'price'		=> PC_formatPrice($packagePrice),
							'unitPrice'	=> PC_formatPrice($unitPrice),
							'packageId' => $packageID,
							'drugId' => $drugID,
							'rxRequired' => $rxRequired,
							'flagBaseURL' => $flagBaseURL,
							'countryFlag' => $countryFlag,
							'country' => $countryCodeHuman,
							'countryCode' => $countryCode,
							'countryOfOrigin' => $countryOfOrigin,
							'countryOfOriginCode' => $countryOfOriginCode,
							'brandOrGeneric' => $brandOrGeneric,
							'drugClass' => trim($drugClass),
							'buyButton' => $buyButton,
							'preferred' => $package->preferred,
							'drugPreferred' => $drugPreferred,
							'tierQuantity' => $tierQuantity,
							'packageIdTooltip' => $packageIdTooltip,
							'packageAttributesClasses' => implode(' ', $packageAttrClass),
							'drugAttributesClasses' => implode(' ', $drugAttrClass),
							'packageCommentExternal' => $package->comment_external,
							'drugJson' => json_encode($drugDataArray, JSON_FORCE_OBJECT),
							'packageJson' => json_encode($packageDataArray, JSON_FORCE_OBJECT),
							'vendorCountryCode' => $vendorCountryCode,
							'vendorCountry' => $vendorCountryHuman,
							'vendor' => $package->getVendor(),
							'drugFamilyName' => $package->drug->familyname,
							'ingredients' => $drugResultObject->ingredients,
						);

						$this->assign('ROW', $packageInfo);

						$this->assign('PACKAGE_JSON', json_encode($packageDataArray));

						$this->parse('brandDrug.AllDrugs');

						if (get_option('pw_drug_dropdown', 'on')) {
							$this->assign('QTY_DROPDOWN', genQtyDropDown(1, $qtyOrderLimit));
							if ($rowCount == 0) {
								$this->assign('INITIAL_PACKAGEID', $package->package_id);
								$this->parse('brandDrug.brandPackageDropdown.dropdownBuy');
							}

							$this->parse('brandDrug.brandPackageDropdown.brandPackageDropdownOptions');

							if (!empty($packages[$key + 1])) {
								$nextPackage = $packages[$key + 1];
								$nextDrugStrength = Utility_Common::getFullValue($nextPackage->drug->strength);
								// If option to split dropdown into seperate drug strengths is active,
								// output each unique strength into it's own dropdown
								if ((get_option('pw_drug_dropdown_seperate_str', 'on')) && (($drugStrength != $nextDrugStrength))) {
									$this->parse('brandDrug.brandPackageDropdown');
								}
							}
						} else {
							$this->parse('brandDrug.brandPackage');
						}

						// grab one category to use for page meta
						if (($packageCondition == '') && ($drugType != '')) {
							$packageCondition = $drugType;
						}
						$packageCount++;
						if (!empty($drugAggregateData[$drugSchemaName])) {
							$drugAggregateData[$drugSchemaName]['packageCount']++;
						}
						$rowCount++;
					}
				}

				// use what they searched for as name, unless they're on a product page then use the title
				if (get_post_type() === 'product') {
					$productSchemaDrugName = get_the_title();
				}
				// if still empty, use the drug name
				if (empty($productSchemaDrugName)) {
					$productSchemaDrugName = $brandName;
				}
				if ($brandCount && $genericCount) {
					$itemOfferedName = 'Brand and Generic';
				} elseif ($brandCount) {
					$itemOfferedName = 'Brand';
				} elseif ($genericCount) {
					$itemOfferedName = 'Generic';
				}

				if (!($drugDescriptionFromTheme)) {
					$drugDescription = $productSchemaDrugName . ' - ' . $itemOfferedName . ', ' . strtoupper($rxStatusItem);
				}
				if ($packageCount > 0) {
					$productSchema = array(
						'lowPrice' => PC_formatPrice($drugLowPrice),
						'highPrice' => PC_formatPrice($drugHighPrice),
						'name' => $productSchemaDrugName,
						'category' => strtoupper($rxStatusItem),
						'sku' => $drugID,
						'mpn' => $drugID,
						'offerCount' => $packageCount,
						'itemOfferedName' => $itemOfferedName,
						'image' => $featureImage,
						'description' => $drugDescription,
					);
				}

				// marge new aggregate data back into drug schema
				if (!empty($drugAggregateData[$drugSchemaName])) {
					$drugSchema[$drugSchemaName]['aggregateData'] = $drugAggregateData[$drugSchemaName];
				}

				if (get_option('pw_drug_dropdown', 'on')) {
					$this->parse('brandDrug.brandPackageDropdown');
				}

				$this->parse('brandDrug');
			}

			// Meta value for page
			$this->assign('PACKAGE_CONDITION', $packageCondition);
			$encodedCondition = rawurlencode($packageCondition);
			$packageConditionLink = PC_getDrugPageUrl($encodedCondition);
			if ($packageConditionLink == '') {
				$searchUrl = PC_getSearchURL();
				$packageConditionLink =	 PC_reCreateUrl('condition=' . $encodedCondition, $searchUrl);
			}
			if ($searchSettings['tier-pricing'] == 1) {
				$this->parse('TierPackageQuantity');
			}

			$this->assign('PACKAGE_CONDITION_LINK', $packageCondition);
			$this->assign('DRUG_SEARCH_NAME', $this->_getRequest('drugName'));
			$this->assign('DRUG_SEARCH_ID', $this->_getRequest('drugId'));
			$this->assign('TIER_PRICING', $searchSettings['tier-pricing']);
			$this->parse('PageMeta');

			if (get_option('pw_enable_drug_schema', 1) || get_option('pw_enable_product_schema', 1)) {
				$this->_prepareSchema($drugSchema, $productSchema);
			}
		}
	}

	public function _parseReferenceName($orginalDrugName, &$drugName, &$refName, &$refLink)
	{
		$pattern1 = '/([^\(]+)\(See ([^\)]*)\)/i';

		if (preg_match($pattern1, $orginalDrugName, $matches)) {
			$drugName = $matches[1];
			$refName = $matches[2];
			$refName = rawurlencode($refName);
			$refLink = get_site_url() . "/product/$refName/";
			return;
		}

		$pattern2 = '/([^-]+)-[ ]*see(.*)/';
		if (preg_match($pattern2, $orginalDrugName, $matches)) {
			$drugName = $matches[1];
			$refName = $matches[2];
			$refName = rawurlencode($refName);
			$refLink = get_site_url() . "/product/$refName/";
			return;
		}

		$pattern3 = '/([^\(]+)\(This drug is also marketed under the name (.*)\)/';
		if (preg_match($pattern3, $orginalDrugName, $matches)) {
			$drugName = $matches[1];
			$refName = $matches[2];
			$refName = rawurlencode($refName);
			$refLink = get_site_url() . "/product/$refName/";
			return;
		}

		$refName = $drugName = $orginalDrugName;
		$refName = rawurlencode($refName);
		$refLink = get_site_url() . "/product/$refName/";
	}

	/**
	 * Aggregates drug data, finding brand names, generic names and alternative product names
	 *
	 * @param mixed $result
	 */
	public function _aggregateDrugResults($drugs, $isIndexSearch, $indexKey)
	{
		global $wpdb;
		$ingredientsArr = array();
		$brandsArr = array();
		foreach ($drugs as $drug) {
			$ingredient = $drug->ingredient_hash;
			if (!strlen($ingredient)) {
				$ingredient = $drug->drug_id;
				$parameters = "drug_id='{$ingredient}'";
				continue;
			} else {
				$parameters = "ingredient_hash = '{$ingredient}'";
			}
			if ($ingredientsArr[$ingredient]) {
				continue;
			}
			$drugIdsArr = array();
			$brandArr = array();
			$query = <<<EOQ
						SELECT IF(LENGTH(familyname), familyname, name) AS Name FROM {$wpdb->prefix}pw_drugs WHERE {$parameters} AND Generic = 0 UNION (
						SELECT attribute_value FROM {$wpdb->prefix}pw_drugs AS d LEFT JOIN {$wpdb->prefix}pw_attributes a ON (d.drug_id=a.attribute_id AND a.attribute_key LIKE '#brand-name%')
							WHERE d.{$parameters}
						)
EOQ;
			$brands = $wpdb->get_results($query, ARRAY_A);

			// See if we can match any of the brand names with drugs we've already seen
			$matchedDrug = $drug;
			foreach ($brands as $brand) {
				$brandName = $brand['Name'];
				if ($brandsArr[$brandName]) {
					$matchedDrug = $brandsArr[$brandName];
					$brandArr = $matchedDrug->brands;
					$drugIdsArr = $matchedDrug->drugIds;
					break;
				}
			}

			// Having find a matching drug (or if not, this drug), build $brandArr to be an associative array of all brand names for the drug(s)
			foreach ($brands as $brand) {
				$brandName = $brand['Name'];
				if (!$brandArr[$brandName]) {
					$brandArr[$brandName] = 1;
				}
			}

			// Now make sure all the brands on this drug are in the master list of matching drug brands
			$drugIdsArr[$drug->drug_id] = 1;
			$matchedDrug->drugIds = $drugIdsArr;
			$matchedDrug->brands = $brandArr;
			$ingredientsArr[$ingredient] = $matchedDrug;
			foreach ($brands as $brand) {
				$brandName = $brand['Name'];
				$brandsArr[$brandName] = $matchedDrug;
			}
		}

		// Having got all the brands tacked on to the matching drugs, get all the drug ids that match the search spec for the results
		$results = array();
		foreach ($brandsArr as $brandName => $drug) {
			if (!$isIndexSearch || ($isIndexSearch && substr($brandName, 0, 1) === $indexKey)) {
				// Get generic names for each item
				$ingredient = $drug->ingredient_hash;
				if (!strlen($ingredient)) {
					$parameters = 'drug_id IN ("' . implode('","', array_keys($drug->drugIds)) . '")';
				} else {
					$parameters = "ingredient_hash = '{$ingredient}'";
				}
				$genericArr = array();
				$query = <<<EOQ
						SELECT IF(LENGTH(familyname), familyname, name) AS Name FROM {$wpdb->prefix}pw_drugs WHERE {$parameters} AND Generic = 1 UNION (
						SELECT attribute_value FROM {$wpdb->prefix}pw_drugs AS d LEFT JOIN {$wpdb->prefix}pw_attributes a ON (d.drug_id=a.attribute_id AND a.attribute_key LIKE '#generic-name%')
							WHERE d.{$parameters}
						)
EOQ;

				$generics = $wpdb->get_results($query, ARRAY_A);
				foreach ($generics as $generic) {
					$genericName = $generic['Name'];
					if (!$drug->brands[$genericName] && strlen($genericName)) {
						$genericArr[$genericName] = 1;
					}
				}
				$drug->generics = $genericArr;
				$drugId = $drug->drug_id;
				$results[$drugId] = $drug;

				$brandKeys = array_keys($drug->brands);
				usort($brandKeys, function ($aa, $bb) use ($isIndexSearch, $indexKey) {
					if ($isIndexSearch) {
						if (substr($aa, 0, 1) === $indexKey && substr($bb, 0, 1) === $indexKey) {
							return strcmp($aa, $bb);
						} elseif (substr($aa, 0, 1) === $indexKey) {
							return -1;
						} else {
							return 1;
						}
					} else {
						return strcmp($aa, $bb);
					}
				});
				$brandArr = array();
				foreach ($brandKeys as $brand) {
					$brandArr[$brand] = 1;
				}
				$drug->brands = $brandArr;
			}
		}

		usort($results, function ($a, $b) {
			$aKeys = array_keys($a->brands);
			$bKeys = array_keys($b->brands);
			return strcmp($aKeys[0], $bKeys[0]);
		});
		return $results;
	}

	/**
	 * Prepare summary data in JSON format
	 *
	 */
	public function _prepareJsonSummary($result, $isIndexSearch, $indexKey)
	{
		$data = $result->data;
		$searchUrl = PC_getSearchURL();
		$productLink = get_option('pw_product_permalink');

		$showdrugs_drug = array();
		foreach ($data as $drug) {
			$drug_key = '';
			$link = '';
			$package = array();

			if ($drug->isScheduleReference()) {
				$package['reference'] = 1;
				$this->_parseReferenceName($drug->name, $drugName, $refName, $refLink);
				$package['drugFamily'] = rawurlencode($drug->familyname);
				$mylink = PC_getDrugPageUrl($drug->familyname);

				if (strlen($mylink)) {
					$refLink = $mylink;
				} else {
					$refLink .= "drugId/$drug->drug_id/";
				}

				$package['url'] = $refLink;
				$package['rx'] = $drug->prescriptionrequired ? 1 : 0;
				$package['drugName'] = $drugName;
				$package['refName'] = $refName;

				$drug_key = $drugName;
				if (array_key_exists($drug_key, $showdrugs_drug)) {
					continue;
				}

				$showdrugs_drug[$drug_key] = $package;
			} elseif (!(Utility_Common::showPackageNameOnSearchResults())) {
				$drug_key = $drug->familyname;
				$package['drugFamily'] = rawurlencode($drug->familyname);
				$encodedDrugName = rawurlencode($drug->familyname);
				$link = PC_getDrugPageUrl($package['drugFamily']);
				if ($link == '') {
					if ($productLink) {
						$link = "/$productLink/$encodedDrugName/" . $drug->drug_id . "/";
					} else {
						$link =	 get_site_url() . "/product/$encodedDrugName/drugId/$drug->drug_id/";
					}
				}
				$package['url'] = $link;
				$package['rx'] = $drug->prescriptionrequired ? 1 : 0;
				$package['drugName'] = $drug->familyname;
				$showdrugs_drug[$drug_key] = $package;
			}

			if (Utility_Common::showPackageNameOnSearchResults()) {
				foreach ($drug->packages as $package) {
					$drug_key = $package->product;
					$package['drugFamily'] = rawurlencode($drug->familyname); // still use the family name for searhcing the product not the pr
					$encodedDrugName = rawurlencode($drug->familyname);
					$link = PC_getDrugPageUrl($package['drugFamily']);
					if ($link == '') {
						$link =	 get_site_url() . "/product/$encodedDrugName/drugId/$drug->drug_id/";
					}
					$package['url'] = $link;
					$package['rx'] = $drug->prescriptionrequired ? 1 : 0;
					$package['drugName'] = $package->product;
					$showdrugs_drug[$drug_key] = $package;
				}
			}
		}

		ksort($showdrugs_drug);
		$results = array();
		foreach ($showdrugs_drug as $drug => $result) {
			$results[] = $result;
		}

		return $results;
	}


	/**
	 * Prepare expanded summar showing brands, generics and alternative product names
	 *
	 */
	public function _prepareExpandedSummary($result, $isIndexSearch, $indexKey, $isShortCode)
	{
		$this->setTemplate('page_search_expanded_summary');
		$productLink = get_option('pw_product_permalink');
		
		$data = $result->data;
		$data = $this->_aggregateDrugResults($data, $isIndexSearch, $indexKey);
		if (count($data) == 0) {
			if (!$isShortCode) {
				$noResultsMsg = '';
				if (!empty($this->_getRequest('drugName'))) {
					$noResultsMsg = 'No drugs were found.';
				}
				if (!empty($this->_getRequest('condition'))) {
					$noResultsMsg = 'Condition not found.';
				}
				$searchForm = pwire_searchform();
				$this->assign('SEARCH_FORM', $searchForm);
				$this->assign('SEARCH_MESSAGE_NORESULTS', $noResultsMsg);
				$this->parse('no_drug');
			}
			return;
		}

		if (!$isShortCode) {
			$this->parse('headings');
		}
		
		$splitOTC = get_option('pw_enable_splitdrugs');

		foreach ($data as $drug) {
			if (!$splitOTC || ($splitOTC && $drug->prescriptionrequired)) {
				//$drug = $data[$key];
				$drug_key = '';
				$drugHtml = '';
				$link = '';

				if ($drug->isScheduleReference()) {
				} elseif (!(Utility_Common::showPackageNameOnSearchResults())) {
					$resource = new Model_Resource_Search();
					$ingredients = $resource->getIngredients($drug->drug_id);
					$ingredientsArr = array();
					foreach ($ingredients as $ingredient) {
						$ingredientsArr[] = $ingredient->ingredient_name;
					}

					$drug_key = $drug->familyname;
					$encodedDrugName = rawurlencode($drug->familyname);
					$link = PC_getDrugPageUrl($encodedDrugName);
					if ($link == '') {
						if ($productLink) {
							$link = "/$productLink/$encodedDrugName/drugId/$drug->drug_id/";
						} else {
							$link =	 get_site_url() . "/product/$encodedDrugName/drugId/$drug->drug_id/";
						}
					}

					$drugHtml = $this->formatDrug('expanded', $drug, $drug->familyname, $drug->prescriptionrequired, $link);
					$showdrugs_drug[$drug_key] = array('html' => $drugHtml, 'url' => $link);
					$this->assign('DRUG_ITEM', $drugHtml);
					$this->assign('CHEMICAL_NAME', join(', ', $ingredientsArr));
					$this->assign('ALTERNATIVE_NAMES', join(', ', array_keys($drug->generics)));
					$this->parse('showdrugs.drug');
				}
			}
		}
		$this->parse('showdrugs');
	}

	/**
	 * Prepare summary result
	 *
	 * @param mixed $result
	 */
	public function _prepareSummaryResult($result, $isIndexSearch, $indexKey, $isShortCode, $isConditionSearch = false)
	{
		$expandedResults = get_option('pw_expanded_summary');
		if ($expandedResults) {
			$this->_prepareExpandedSummary($result, $isIndexSearch, $indexKey, $isShortCode);
			return;
		}

		$this->setTemplate('page_search_summary');
		$data = $result->data;
		if (count($data) == 0) {
			if (!$isShortCode) {
				$noResultsMsg = '';
				if (!empty($this->_getRequest('drugName'))) {
					$noResultsMsg = 'No drugs were found.';
				}
				if (!empty($this->_getRequest('condition'))) {
					$noResultsMsg = 'Condition not found.';
				}
				$searchForm = pwire_searchform();
				$this->assign('SEARCH_FORM', $searchForm);
				$this->assign('SEARCH_MESSAGE_NORESULTS', $noResultsMsg);
				$this->parse('no_drug');
			}
			return;
		}

		$keys = array_keys($data);
		$searchUrl = PC_getSearchURL();
		$productLink = get_option('pw_product_permalink');

		if (!$isShortCode) {
			$this->parse('headings');
		}

		$splitOTC = get_option('pw_enable_splitdrugs');
		$rxCount = 0;

		$showdrugs_drug = array();

		foreach ($data as $drug) {
			if (!$splitOTC || ($splitOTC && $drug->prescriptionrequired)) {
				//$drug = $data[$key];
				$drug_key = '';
				$drugHtml = '';
				$link = '';
				$rxCount++;
				$matchedPage = false;

				if ($drug->isScheduleReference()) {
					$this->_parseReferenceName($drug->name, $drugName, $refName, $refLink);
					$mylink = PC_getDrugPageUrl($drug->familyname);

					if (strlen($mylink)) {
						$refLink = $mylink;
						$matchedPage = true;
					} else {
						$refLink .= "drugId/$drug->drug_id/";
					}

					if (empty($refName)) {
						if (strlen($refLink)) {
							$drugHtml = $this->formatDrug('custom', $drug, $drugName, $drug->prescriptionrequired, $refLink, $refName);
						} else {
							$drugHtml = $this->formatDrug('reference', $drug, $drugName, $drug->prescriptionrequired, $refLink, $refName);
						}
					} else {
						// changed to default to custom rather than reflink
						$drugHtml = $this->formatDrug('custom', $drug, $drugName, $drug->prescriptionrequired, $refLink, $refName);
					}
					$drug_key = $drugName;
					if (array_key_exists($drug_key, $showdrugs_drug)) {
						continue;
					}
					$showdrugs_drug[$drug_key] = array('html' => $drugHtml, 'url' => $link, 'matched' => $matchedPage);
				} elseif (!(Utility_Common::showPackageNameOnSearchResults())) {
					$drug_key = $drug->familyname;
					$encodedDrugName = rawurlencode($drug->familyname);
					$link = PC_getDrugPageUrl($drug->familyname);
					if ($link == '') {
						if ($productLink) {
							$link = "/$productLink/$encodedDrugName/" . $drug->drug_id . "/";
						} else {
							$link =	 get_site_url() . "/product/$encodedDrugName/drugId/$drug->drug_id/";
						}
					} else {
						$matchedPage = true;
					}

					// If treat family name as alternative drug name is enabled
					if (get_option('pw_treat_familyname_as_alternate_drugname') == 'on') {

						// Display either Drug Name or Drug Family Name based on whichever matches the search
						if (preg_match('/^' . strtolower($indexKey) . '/', strtolower($drug->name)) === 1) {
							// Add Drug Name to Results
							$drugHtml = $this->formatDrug($expandedResults ? 'expanded' : 'regular', $drug, $drug->name, $drug->prescriptionrequired, $link);
							$showdrugs_drug[$drug->name] = array('html' => $drugHtml, 'url' => $link, 'matched' => $matchedPage);
						} else {
							$drugHtml = $this->formatDrug($expandedResults ? 'expanded' : 'regular', $drug, $drug->familyname, $drug->prescriptionrequired, $link);
							$showdrugs_drug[$drug_key] = array('html' => $drugHtml, 'url' => $link, 'matched' => $matchedPage);
						}
					} else {
						$drugHtml = $this->formatDrug($expandedResults ? 'expanded' : 'regular', $drug, $drug->familyname, $drug->prescriptionrequired, $link);
						$showdrugs_drug[$drug_key] = array('html' => $drugHtml, 'url' => $link, 'matched' => $matchedPage);
					}
				}

				if (Utility_Common::showPackageNameOnSearchResults()) {
					foreach ($drug->packages as $package) {
						$drug_key = $package->product;
						// still use the family name for searching the product not the product / package name
						$encodedDrugName = rawurlencode($drug->familyname);
						// Bugfix Sep 8 2021, altered -> $link = PC_getDrugPageUrl($encodedDrugName);
						// should not use encodedDrugname initially when searching against WordPress page titles
						// but still use encodedDrugName for the link generation if no matching pages were found
						$link = PC_getDrugPageUrl($drug->familyname);
						$matchedPage = true;
						if ($link == '') {
							$link =	 get_site_url() . "/product/$encodedDrugName/drugId/$drug->drug_id/";
							$matchedPage = false;
						}
						$drugHtml = $this->formatDrug($expandedResults ? 'expanded' : 'regular', $drug, $package->product, $drug->prescriptionrequired, $link);
						$showdrugs_drug[$drug_key] = array('html' => $drugHtml, 'url' => $link, 'matched' => $matchedPage);
					}
				}
			}
		}

		if (!$isShortCode) {
			if (!$splitOTC && (count($showdrugs_drug) == 1) && !$isIndexSearch) {
				//redirect only one drug in the list....
				$keys = array_keys($showdrugs_drug);
				$this->redirect($showdrugs_drug[$keys[0]]['url']);
				return;
			}
		}

		$showMatchedPagesOnly = get_option('pw_show_matched_pages_only');
		ksort($showdrugs_drug);
		foreach ($showdrugs_drug as $drug_key => $drugInfo) {
			$showResult = true;
			if ($showMatchedPagesOnly && ($isConditionSearch || $isIndexSearch) && !$drugInfo['matched']) {
				$showResult = false;
			}
			if ($isIndexSearch && (strtolower(substr($drug_key, 0, 1)) !== strtolower($indexKey))) {
				$showResult = false;
			}
			if ($showResult) {
				$this->assign('DRUG_ITEM', $drugInfo['html']);
				$this->parse('showdrugs.drug');
			}
		}

		if ($rxCount) {
			$this->parse('showdrugs');
		}

		if ($splitOTC) {
			$showdrugs_drug_otc = array();
			foreach ($data as $drug) {
				if (!$drug->prescriptionrequired) {
					$drugHtml = '';
					if (!$drug->isScheduleReference()) {
						$drug_key = $drug->familyname;
						$encodedDrugName = rawurlencode($drug->familyname);
						$link = PC_getDrugPageUrl($encodedDrugName);
						if ($link == '') {
							$link =	 get_site_url() . "/product/$encodedDrugName/drugId/$drug->drug_id/";
						}
						$drugHtml = $this->formatDrug($expandedResults ? 'expanded' : 'regular', $drug, $drug->familyname, $drug->prescriptionrequired, $link);
						$showdrugs_drug_otc[$drug_key] = array('html' => $drugHtml, 'url' => $link);
					}

					if (Utility_Common::showPackageNameOnSearchResults()) {
						foreach ($drug->packages as $package) {
							$drug_key = $package->product;
							$encodedDrugName = rawurlencode($drug->familyname); // still use the family name for searhcing the product not the pr
							$link = PC_getDrugPageUrl($encodedDrugName);
							if ($link == '') {
								$link =	 get_site_url() . "/product/$encodedDrugName/drugId/$drug->drug_id/";
							}
							$drugHtml = $this->formatDrug($expandedResults ? 'expanded' : 'regular', $drug, $package->product, $drug->prescriptionrequired, $link);
							$showdrugs_drug_otc[$drug_key] = array('html' => $drugHtml, 'url' => $link);
						}
					}
				}
			}

			if (((count($showdrugs_drug) + count($showdrugs_drug_otc)) == 1) && !$isShortCode) {
				//redirect only one drug in the list....
				if (count($showdrugs_drug)) {
					$keys = array_keys($showdrugs_drug);

					$this->redirect($showdrugs_drug[$keys[0]]['url']);
					return;
				}

				$keys = array_keys($showdrugs_drug_otc);

				$this->redirect($showdrugs_drug_otc[$keys[0]]['url']);
				return;
			}

			ksort($showdrugs_drug_otc);
			foreach ($showdrugs_drug_otc as $drug_key => $drugInfo) {
				$this->assign('OTC_ITEM', $drugInfo['html']);
				$this->parse('showotc.otc');
			}

			$this->parse('showotc');
		}
	}

	public function formatDrug($type, $drug, $drugName, $rxRequired, $refLink, $refName = '')
	{
		$drugName = '<span class="drug-name">' . $drugName . "</span>";
		$rxClass = 'otc';
		if ($rxRequired) {
			$rxClass = 'rxrequired';
		}

		// show ingredients on search summary results if enabled
		if (get_option('pw_display_ingredients_on_search_results', 0)) {
			$resourceSearch = new Model_Resource_Search();
			$ingredients = $resourceSearch->getIngredients($drug->drug_id);

			if (!empty($ingredients)) {
				$ingredientNames = array();
				foreach ($ingredients as $ing) {
					array_push($ingredientNames, $ing->ingredient_name);
				}
				$drugName = $drugName . ' <span class="ingredients">' . implode(', ', $ingredientNames) . '</span>';
			}
		}

		// remove '/' if included to avoid breaking URL in the otherwise 
		// already encoded $refLink - DrugID will still find product
		$refLink = preg_replace('/%2F/', "-", $refLink);		

		switch ($type) {
			case 'reflink': // reference
				$drugHtml = sprintf("<span class=\"reference reflink\">%s (See <a class=\"reference %s\" href=\"%s\">%s</a>)</span>", $drugName, $rxClass, $refLink, $refName);
				break;
			case 'reference': // not link
				$drugHtml = sprintf("<span class=\"reference standard\">%s</span>", $drugName);
				break;
			case 'custom': // custom page link
				$drugHtml = sprintf("<span class=\"reference custom\"><a class=\"reference %s\" href=\"%s\">%s</a></span>", '', $refLink, $drugName);
				break;
			case 'expanded': // expanded format including brand, ingredients and others
				$drugHtml = sprintf("<a class=\"%s expanded\" href=\"%s\">%s</a>", $rxClass, $refLink, join(', ', array_keys($drug->brands)));
				break;
			default: // regular links
				$drugHtml = sprintf("<a class=\"%s default\" href=\"%s\">%s</a>", $rxClass, $refLink, $drugName);
				break;
		}

		return $drugHtml;
	}

	function _prepareSchema($drugSchema, $productSchema)
	{
		global $wp;

		if (get_option('pw_enable_drug_schema', 1)) {
			foreach ($drugSchema as $drugID => $dSchema) {
				if ($dSchema['rxrequired'] == 1) {
					$drugLegalStatusName = 'prescription';
					$drugPrescriptionStatusSchema = 'http://schema.org/PrescriptionOnly';
				} else {
					$drugLegalStatusName = 'over-the-counter';
					$drugPrescriptionStatusSchema = 'http://schema.org/OTC';
				}

				$drugIngredients = '';
				if (!empty($dSchema['ingredients']) && ($dSchema['drugName'] != $dSchema['ingredients'])) {
					$drugIngredients = ' (' . $dSchema['ingredients'] . ')';
				}

				$isProprietary = 'http://schema.org/False';
				if ($dSchema['brandOrGeneric'] == 'brand') {
					$isProprietary = 'http://schema.org/True';
				}

				// Feb 2023 - new product schema fields added for offer data to meet Google's new schema requirements
				$drugSchemaFormatted = array(
					'drugID' => $dSchema['drugID'],
					'drugUnit' => $dSchema['drugUnit'],
					'strength' => $dSchema['drugStrength'],
					'name' => $dSchema['drugName'],
					'dosageForm' => $dSchema['dosageForm'],
					'legalStatusName' =>  $drugLegalStatusName,
					'activeIngredient' => $dSchema['ingredients'],
					'prescriptionStatus' => $drugPrescriptionStatusSchema,
					'isProprietary' => $isProprietary,
					'lowPrice' => PC_formatPrice($dSchema['aggregateData']['lowPrice']),
					'highPrice' => PC_formatPrice($dSchema['aggregateData']['highPrice']),
					'offerCount' => $dSchema['aggregateData']['packageCount'],
					'siteurl' => home_url(),
					'pharmacyname' => get_option('pw_pharmacy', get_option('pw_name', ''))
				);

				$strSchemaIndex = 0;
				$strSchemaLen = count($dSchema['packages']);
				ksort($dSchema['packages'], SORT_NUMERIC);
				foreach ($dSchema['packages'] as $pStrKey => $pSchemaByStr) {

					$pSchemaIndex = 0;
					$pSchemaLen = count($pSchemaByStr);

					$manufacturerNames = array();
					foreach ($pSchemaByStr as $packageSchema) {
						// May 7 2020 - disabling as Google no longer accepts 'cost' as valid for Drug schema
						// Removed from template & commented out here for now - likely can remove from code
						// "cost": [
						//     <!-- BEGIN: costItem -->
						//     {
						//       "@type": "DrugCost",
						//       "drugUnit": "{COST.drugUnit}",
						//       "costPerUnit": "{COST.costPerUnit}",
						//       "description": "{COST.quantity} ({COST.brandOrGeneric}) - ${COST.costPerUnit}"
						//     }<!-- BEGIN: seperator -->,<!-- END: seperator -->
						//     <!-- END: costItem -->
						//   ],
						//
						// $costSchema = array(
						//     'drugUnit' => $packageSchema['drugUnit'],
						//     'costPerUnit' => $packageSchema['costPerUnit'],
						//     'brandOrGeneric' => $packageSchema['brandOrGeneric'],
						//     'quantity' => $packageSchema['quantity']
						// );

						$availableStrengthSchema = array(
							'strengthUnit' => $packageSchema['strengthUnit']
						);

						if (!in_array($packageSchema['manufacturer'], $manufacturerNames)) {
							$manufacturerNames[] = $packageSchema['manufacturer'];
						}

						// $this->assign('COST', $costSchema);
						$this->assign('AVAILABLESTRENGTH', $availableStrengthSchema);

						// add comma seperator to json except for last iteration
						// if (!(($strSchemaIndex == ($strSchemaLen - 1)) && ($pSchemaIndex == ($pSchemaLen - 1)))) {
						//     $this->parse('structuredData.drugSchema.costItem.seperator');
						// }

						// $this->parse('structuredData.drugSchema.costItem'); 
						$pSchemaIndex++;
					}

					// add comma seperator to json except for last iteration
					if (($strSchemaIndex != ($strSchemaLen - 1))) {
						$this->parse('structuredData.drugSchema.availableStrengthItem.seperator');
					}

					$this->parse('structuredData.drugSchema.availableStrengthItem');
					$strSchemaIndex++;
				}

				$mfgSchemaIndex = 0;
				$mfgSchemaLen = count($manufacturerNames);
				foreach ($manufacturerNames as $mfgName) {
					$mfgSchema = array(
						'name' => $mfgName
					);
					// add comma seperator to json except for last iteration
					if ($mfgSchemaIndex != ($mfgSchemaLen - 1)) {
						$this->parse('structuredData.drugSchema.manufacturer.seperator');
					}
					$this->assign('MANUFACTURER', $mfgSchema);
					$this->parse('structuredData.drugSchema.manufacturer');
					$mfgSchemaIndex++;
				}
				$this->assign('DRUGSCHEMA', $drugSchemaFormatted);
				$this->parse('structuredData.drugSchema');
			}
		}

		if (get_option('pw_enable_product_schema', 1)) {
			if (!empty($productSchema)) {
				// For each Drug output an aggregate of the packages
				$productSchemaFormatted = array(
					'name' => $productSchema['name'],
					'category' => $productSchema['category'],
					'lowPrice' => $productSchema['lowPrice'],
					'highPrice' => $productSchema['highPrice'],
					'sku' => $productSchema['sku'],
					'mpn' => $productSchema['mpn'],
					'offerCount' => $productSchema['offerCount'],
					'itemOfferedName' => $productSchema['itemOfferedName'],
					'url' => home_url($wp->request . '/'),
					'siteurl' => home_url(),
					'pharmacyname' => get_option('pw_pharmacy', get_option('pw_name', '')),
					'image' => $productSchema['image'],
					'description' => $productSchema['description']
				);

				$this->assign('PRODUCTSCHEMA', $productSchemaFormatted);
				if (!empty($productSchemaFormatted['image'])) {
					$this->parse('structuredData.productSchema.image');
				}
				$this->parse('structuredData.productSchema');
			}
		}
	}

	function _prepareStructuredData()
	{
		$this->parse('structuredData');
	}
}
