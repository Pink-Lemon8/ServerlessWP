<?php
require_once PAGE_FOLDER . 'Search.php';
class Page_Shortcode extends Page_Search
{
	public function __construct($searchType, $keyword, $filter = 'all', $strength, $tier, $ingredients, $dosageform, $limit, $related, $detail = 'false', $match = '', $rxrequired = '', $country = '', $template = '', $debug = '')
	{
		$this->_searchType = $searchType;
		$this->_keyword = $keyword;
		$this->_filter = $filter;
		$this->_strength = $strength;
		$this->_tier = $tier;
		$this->_ingredients = $ingredients;
		// drug form to help narrow down the search
		$this->_dosage_form = $dosageform;
		$this->_limit = $limit;
		$this->_related = $related;
		// If showdetail is enabled -- similar products show in search results
		// Otherwise standard searches only show related products
		$this->_showdetail = $detail;
		$this->_match = $match;
		$this->_rxrequired = $rxrequired;
		$this->_country = $country;
		$this->_debug = false;
		if ((strtolower($debug) === 'yes') || (strtolower($debug) === 'true') || ($debug >= 1)) {
			$this->_debug = true;
		}
		$this->_template = $template;
	}


	/**
	 * Make the html output
	 *
	 * @return The content in html format
	 */
	public function _process($json = null, $refineSearch = null)
	{
		$searchModel = new Model_Catalog();
		$result = new stdClass();
		$isShortCode = true;

		if (strlen($this->_ingredients)) {
			$this->_showdetail = 'true';
			$this->_searchType = 'ingredient_list';
			$ingredients = array_map('trim', explode(',', $this->_ingredients));
			sort($ingredients);
			$ingredients = implode('; ', $ingredients);
			$this->_keyword = $ingredients;
		} elseif ($this->_searchType != 'condition' && (is_null($this->_keyword) || $this->_keyword == '')) {
			$this->_prepareSummaryResult($result, strlen($this->_keyword) == 1, $this->_keyword, true, false);
			return;
		} elseif ($this->_searchType == 'condition' && (is_null($this->_keyword) || $this->_keyword == '' || $this->_keyword == '<all>' || $this->_keyword == '<rx>' || $this->_keyword == '<otc>')) {
			$result = $searchModel->searchConditions($this->_keyword);
			$this->_prepareConditionResult($result, $isShortCode);
			return;
		}

		// If showdetail is enabled -- similar products show in search results
		// Otherwise standard searches only show related products
		$showDetail = $this->_showdetail;
		if ($this->_searchType == 'condition') {
			$showDetail = 'never';
		}
		if (strlen($this->_dosage_form) || strlen($this->_match)) {
			$showDetail = 'true';
		}

		// DrugID/packageID Search - keywords consist of drug id's single or otherwise
		if (preg_match('/(^DP?-\d+)(,\s*DP?-\d+)*/i', $this->_keyword)) {
			$result->isDetailSearch = 1;
			$result->data = [];

			// Handle DrugIDs in search
			if (preg_match('/(D-\d+)(,\s*D-\d+)*/i', $this->_keyword, $drugIDs)) {
				// remove full match and strip out dilimeters to then searcy by array of drug ids
				unset($drugIDs[0]);
				$drugIDs = preg_replace('/,\s*/', '', $drugIDs);
				$drugIDs = array_unique($drugIDs);
				$drugResult = $searchModel->searchDrugDetailStrict($drugIDs, $this->_strength, $this->_filter, $this->_keyword);
				$drugResultData = $drugResult->data;
				if (!empty($drugResultData)) {
					$result->data = array_merge($result->data, $drugResultData);
				}
			}

			// Handle PackageIDs in search
			$packageResults = new stdClass();
			if (preg_match('/(DP-\d+)(,\s*DP-\d+)*/i', $this->_keyword, $packageIDs)) {
				$searchObj = new Model_Resource_Search();
				// remove full match and strip out dilimeters to then searcy by array of drug ids
				unset($packageIDs[0]);
				$packageIDs = preg_replace('/,\s*/', '', $packageIDs);
				$packageIDs = array_unique($packageIDs);
				foreach ($packageIDs as $pID) {
					$packageObj = $searchObj->getPackageObj($pID);
					$drugResult = $searchModel->searchDrugDetailStrict($packageObj->drug_id, $this->_strength, $this->_filter, $this->_keyword);

					if (!empty($drugResult->data)) {
						// filter packages by package IDs
						$packageResult = $drugResult->data[0]->packages;
						foreach ($packageResult as $key => $packageR) {
							if ($packageR->package_id !== $pID) {
								unset($packageResult[$key]);
							}
						}
						// update packages on drug object itself and on drugResult packages
						$drugResult->data[0]->drug->setPackages($packageResult);
						$drugResult->data[0]->packages = $packageResult;
						$drugResult->data[0]->displayName = $packageObj->product;

						$result->data = array_merge($result->data, $drugResult->data);
					}
				}
			}
		} else {
			// Keyword search
			$result = $searchModel->searchDrugs($this->_keyword, $this->_searchType, $this->_filter, $this->_strength, $this->_limit, $this->_related, $showDetail);
		}

		if (strlen($this->_match)) {
			$my_matchingstring = $this->_match;
			if (!(preg_match('/^\//', $my_matchingstring))) {
				# default regex when using only a string to match
				# match anywhere the exact list of charaters with case-insentive.
				$my_matchingstring = '/^' . $my_matchingstring . '$/i';
			}
			$new_data = array();
			foreach ($result->data as $data) {
				if (Utility_Common::showPackageNameOnSearchResults()) {
					$new_package = array();
					foreach ($data->packages as $package) {
						if ($this->_debug) {
							print 'match by package name ' . $my_matchingstring . ' "' . $package->product;
						}
						if (preg_match($my_matchingstring, $package->product)) {
							$new_package[] = $package;
							if ($this->_debug) {
								print '" : result: match';
							}
						} else {
							if ($this->_debug) {
								print '" : result: NO match';
							}
						}
						if ($this->_debug) {
							print "<br>\n";
						}
					}

					if (count($new_package)) {
						$data->packages = $new_package;
						$new_data[] = $data;
					}
				} else {
					if ($this->_debug) {
						print 'match by drug name ' . $my_matchingstring . ' "' . $data->drug->name;
					}
					if (preg_match($my_matchingstring, $data->drug->name)) {
						$new_data[] = $data;
						if ($this->_debug) {
							print '" : result: match';
						}
					} else {
						if ($this->_debug) {
							print '" : result: NO match';
						}
					}
					if ($this->_debug) {
						print "<br>\n";
					}
				}
			}
			$result->data = $new_data;
		}

		if (strlen($this->_rxrequired) && strtolower($this->_rxrequired) !== 'both') {
			$new_data = array();
			foreach ($result->data as $data) {
				$new_package = array();
				foreach ($data->packages as $package) {
					if (strtolower($this->_rxrequired) === 'yes' && ($package->drug->prescriptionrequired)) {
						$new_package[] = $package;
					} elseif (strtolower($this->_rxrequired) === 'no' && !($package->drug->prescriptionrequired)) {
						$new_package[] = $package;
					}
				}
				if (count($new_package)) {
					$data->packages = $new_package;
					$new_data[] = $data;
				}
			}
			$result->data = $new_data;
		}

		if (strlen($this->_country)) {
			$countries = explode('|', strtolower($this->_country));
			if ($this->_debug) {
				print "Checking for countries: " . $this->_country . "<br/>\n";
			}
			$include_countries = array();
			$exclude_countries = array();
			foreach ($countries as $country) {
				$country_value = array();
				if (preg_match('/^!(.+)/', $country, $country_value)) {
					$exclude_countries[strtolower($country_value[1])] = 1;
				} else {
					$include_countries[strtolower($country)] = 1;
				}
			}
			$new_data = array();
			foreach ($result->data as $data) {
				$new_package = array();
				foreach ($data->packages as $package) {
					$package_country = strtolower($package->getScheduleCountry());
					if (!$exclude_countries[$package_country] && !(count($include_countries) xor $include_countries[$package_country])) {
						if ($this->_debug) {
							print "Drug Country '" . $package->getScheduleCountry() . "' found.<br/>\n";
						}
						$new_package[] = $package;
					} else {
						if ($this->_debug) {
							print "Drug Country '" . $package->getScheduleCountry() . "' not found.<br/>\n";
						}
						continue;
					}
				}

				if (count($new_package)) {
					$data->packages = $new_package;
					$new_data[] = $data;
				}
			}
			$result->data = $new_data;
		}

		if (strlen($this->_strength)) {
			$strengths = explode('|', strtolower($this->_strength));
			if ($this->_debug) {
				print "Checking for strengths: " . $this->_strength . "<br/>\n";
			}
			$new_data = array();
			foreach ($result->data as $data) {
				$new_package = array();
				foreach ($data->packages as $package) {
					$keep = false;
					foreach ($strengths as $strength) {
						$strength_regex = '~^' . $strength . '\s~i';
						if (strlen($package->drug->strengthfreeform)) {
							if (preg_match($strength_regex, $package->drug->strengthfreeform . ' ')) {
								$keep = true;
								if ($this->_debug) {
									print " Drug Strength Free Form '" . $package->drug->strengthfreeform . "' found with ($strength_regex).<br/>\n";
								}
							} else {
								if ($this->_debug) {
									print "Drug Strength Free Form '" . $package->drug->strengthfreeform . "' not found with ($strength_regex).<br/>\n";
								}
							}
						} else {
							$strength_to_match = $package->drug->strength . ' ' . $package->drug->strength_unit;
							if (preg_match($strength_regex, $strength_to_match)) {
								$keep = true;
								if ($this->_debug) {
									print "Drug Strength Free Form '" . $strength_to_match . "' found with ($strength_regex).<br/>\n";
								}
							} else {
								if ($this->_debug) {
									print "Drug Strength Free Form '" . $strength_to_match . "' not found with ($strength_regex).<br/>\n";
								}
							}
						}
						if ($keep) {
							break; // #end now because we matched.
						}
					}
					if ($keep) {
						$new_package[] = $package;
					}
				}

				if (count($new_package)) {
					$data->packages = $new_package;
					$new_data[] = $data;
				}
			}
			$result->data = $new_data;
		}
		if (strlen($this->_tier)) {
			$tierDecoded = html_entity_decode($this->_tier);
			// filter by quantity
			if ($this->_debug) {
				print "Checking for tier: " . $this->_tier . "<br/>\n";
			}

			if (preg_match('/\d+/', $tierDecoded, $tierMatch)) {
				$tier = $tierMatch[0];
				$operator = '';

				if (preg_match('/^[gl][et]\s/', $tierDecoded, $operatorMatch)) {
					$operator = trim($operatorMatch[0]);
				}
				$new_data = array();

				foreach ($result->data as $data) {
					$new_package = array();
					foreach ($data->packages as $package) {
						$keep = false;

						switch ($operator) {
							case 'lt':
								if ($package->quantity < $tier) {
									$keep = true;
								}
								break;
							case 'le':
								if ($package->quantity <= $tier) {
									$keep = true;
								}
								break;
							case 'gt':
								if ($package->quantity > $tier) {
									$keep = true;
								}
								break;
							case 'ge':
								if ($package->quantity >= $tier) {
									$keep = true;
								}
								break;
							default:
								if ($package->quantity === $tier) {
									$keep = true;
								}
								break;
						}

						if ($keep) {
							$new_package[] = $package;
						}
					}

					if (count($new_package)) {
						$data->packages = $new_package;
						$new_data[] = $data;
					}
				}
				$result->data = $new_data;
			}
		}
		if (strlen($this->_dosage_form)) {
			// filter by dosage form here...
			$dosageform = explode('|', strtolower($this->_dosage_form));
			if ($this->_debug) {
				print "Checking for dosage: " . $this->_dosage_form . "<br/>\n";
			}
			$new_data = array();
			foreach ($result->data as $data) {
				$new_package = array();
				foreach ($data->packages as $package) {
					$keep = false;
					foreach ($dosageform as $df) {
						if ($df == '/none/') {
							if (!strlen($package->drug->dosage_form)) {
								$keep = true;
							}
						} else {
							$df_regex = '/^' . $df . '$/i';
							if (strlen($package->drug->dosage_form)) {
								if (preg_match($df_regex, $package->drug->dosage_form)) {
									$keep = true;
									if ($this->_debug) {
										print " Drug Dosage Form '" . $package->drug->dosage_form . "' found with ($df_regex).<br/>\n";
									}
								} else {
									if ($this->_debug) {
										print "Drug Dosage Form '" . $package->drug->dosage_form . "' not found with ($df_regex).<br/>\n";
									}
								}
							}
						}
						if ($keep) {
							break; // #end now because we matched.
						}
					}
					if ($keep) {
						$new_package[] = $package;
					}
				}

				if (count($new_package)) {
					$data->packages = $new_package;
					$new_data[] = $data;
				}
			}
			$result->data = $new_data;
		}	
		// display result
		if (!empty($result->data) && $result->isDetailSearch) {
			$this->_prepareDetailResult($result, $this->_keyword, $isShortCode, $this->_template);
		} elseif (!empty($result->data)) {
			$this->_prepareSummaryResult($result, 0, 0, $isShortCode, $this->_searchType == 'condition');
		} else {
			$emptyTemplateArr = ['page_empty'];
			if (!empty($this->_template)) {
				array_unshift($emptyTemplateArr, $this->_template . '_empty');
			}
			$this->setTemplate($emptyTemplateArr);
		}
	}
}
