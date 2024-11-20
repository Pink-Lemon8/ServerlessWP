<?php 
	/**
	* Model_Catalog
	*/
	class Model_Catalog extends Utility_ModelBase 
	{
		/**
		 * constructor of the handler - initialises Memcached object
		 *
		 * @return bool
		 */
		function __construct()
		{
			$this->memcache = new Utility_Memcached;
			$this->version = $this->catalogVersion();
			$this->lifeTime = intval(3600*12);

			return true;
		}
		
		/**
		* Build cache data for catalog
		* 
		*/
		function buildCache() 
		{
			// Force a configuration update
			set_time_limit(0);
			
			$reply = new Model_Entity_Reply();
			$catalogReq = new XmlApi_Request_Catalog();
			$data = new stdClass();
			$this->catalogClearViewable();
			$this->catalogClearCatalogUpdated();

			// started
			update_option('pw_catalog_update_progress', 'started');
			update_option('pw_catalog_last_update_time', time());

			try {
				// in progress
				update_option('pw_catalog_update_progress', 'downloading');
				$catalogReq->process($data);

				// importing catalog data
				update_option('pw_catalog_update_progress', 'importing');
				$reply = $catalogReq->getData();
			} catch(Exception $e) {							
				throw new Exception("requestxml");
			}				  

			if ($reply->status == 'success') {
				$this->catalogVersionUpdate();
				$this->catalogSetViewable();
				$this->catalogDeleteOldEntries();
				// catalog complete
				update_option('pw_catalog_update_progress', 'completed');
				error_log('PharmacyWire - Catalog update completed: ' . current_time('D M jS, Y; g:ia') . ' - ' . get_option('timezone_string'));
				// clear supported 3rd-party caches
				ClearCaches::clear();
				$this->pwireTablesGC();
				$this->updatePwireXMLDataCache();
				do_action('pw_catalog_update_complete');
			} else {
				// last catalog update failed
				update_option('pw_catalog_update_progress', 'failed');
			}
			return $reply ;
		}

		// Perform any Garbage Collection that should be run on a regular basis
		function pwireTablesGC() {
			Utility_SessionHandler::manual_db_gc();
			return;
		}

		function updatePwireXMLDataCache() {
			// Run XML connect requests that can be stored ('cached') in the DB
			// e.g. updating the forward prescription option methods from PharmacyWire 
			$xmlPwireDataCacheArray = [];
			$getFwdRxOptions = new XmlApi_GetForwardPrescriptionOptions();
			$xmlPwireDataCacheArray['forward_prescription_options'] = $getFwdRxOptions->getFwdRxOptions();
			update_option('pw_xml_pwire_data_cache', json_encode($xmlPwireDataCacheArray));
		}

		function catalogVersion()
		{
			return (int) get_option('pw_catalog_version', 1);
		}

		function catalogVersionUpdate()
		{
			$this->version = $this->catalogVersion() + 1;
			update_option('pw_catalog_version', $this->version);
		}

		function catalogClearCatalogUpdated() {
			global $wpdb;
			$wpdb->update("{$wpdb->prefix}pw_packages", array( 'catalog_updated' => 0 ), array( 'catalog_updated' => 1 ) );
			$wpdb->update("{$wpdb->prefix}pw_drugs", array( 'catalog_updated' => 0 ), array( 'catalog_updated' => 1 ) );
			$wpdb->update("{$wpdb->prefix}pw_packages_tierprice", array( 'catalog_updated' => 0 ), array( 'catalog_updated' => 1 ) );
			$wpdb->update("{$wpdb->prefix}pw_attributes", array( 'catalog_updated' => 0 ), array( 'catalog_updated' => 1 ) );
			$wpdb->update("{$wpdb->prefix}pw_drug_ingredient", array( 'catalog_updated' => 0 ), array( 'catalog_updated' => 1 ) );
			$wpdb->update("{$wpdb->prefix}pw_ingredients", array( 'catalog_updated' => 0 ), array( 'catalog_updated' => 1 ) );
		}

		function catalogDeleteOldEntries() {
			global $wpdb;
			$wpdb->delete("{$wpdb->prefix}pw_packages", array( 'catalog_updated' => 0 ) );
			$wpdb->delete("{$wpdb->prefix}pw_drugs", array( 'catalog_updated' => 0 ) );
			$wpdb->delete("{$wpdb->prefix}pw_packages_tierprice", array( 'catalog_updated' => 0 ) );
			$wpdb->delete("{$wpdb->prefix}pw_attributes", array( 'catalog_updated' => 0 ) );
			$wpdb->delete("{$wpdb->prefix}pw_drug_ingredient", array( 'catalog_updated' => 0 ) );
			$wpdb->delete("{$wpdb->prefix}pw_ingredients", array( 'catalog_updated' => 0 ) );
		}

		function catalogClearViewable ()
		{
			global $wpdb;
			$wpdb->query("UPDATE {$wpdb->prefix}pw_packages SET is_viewable = 0");
		}

		function catalogSetViewable ()
		{
			global $wpdb;
			$wpdb->query("UPDATE {$wpdb->prefix}pw_packages SET public_viewable = 0 WHERE is_viewable = 0");
		}
		
		/**
		* Search drugs
		* 
		* @param mixed $keyword
		* @param mixed $searchType Search type drug|ingredient|condition|search
		* @param mixed $filter generic|brand|all
		* @return stdClass
		*/
		function searchDrugs($keyword, $searchType = 'search', $filter = 'all', $strength = -1, $limit = -1,$related = "",$detail = "false", $rxRequired = 'both')
		{	
			$memcachedKey = str_replace(' ', '_', $keyword);
			$searchKey = $_SERVER['SERVER_NAME'].':v'.$this->version.':'.$memcachedKey.':'.$searchType.':'.$filter.':'.$strength.':'.$limit.':'.$related.':'.$detail.':'.Utility_Common::showOnlyCanadianDrugs();

			// lookup results in memcache first
			$result = $this->memcache->get($searchKey);

			if (!$result) {
				// 2.a. Drug search
				// 2.a.i. First match the search criteria against the drug name field and/or the ingredient name
				// 2.a.ii. Use the ingredient-hash field to find all other matching drugs

				$resource = new Model_Resource_Search();
				$drugs = $resource->getDrugs($keyword, $searchType, 'all',$strength,$limit);
				// 2.a.iii Discard any product packages that are not viewable
				$filterDrugs = array();
				foreach ($drugs as $drug) {
					$packages = $resource->getViewablePackages($drug->drug_id);
					if (!empty($packages)) {
						$filterDrugs[] = $drug;
						$drug->setPreferred($packages);
						$drug->setPackages($packages);					  
					}
				}

				// 2.a.iv. Once all matching drugs are found, the data then needs to be aggregated on a results screen
				$result = new stdClass();


				if ($searchType == 'search' && strlen($keyword) == 1) {
					$isUniqueProduct = 0;
				} else {
					$isUniqueProduct = $this->isUniqueProduct($filterDrugs);
				}

				if (strtolower($detail) == 'true' || (strtolower($detail) != 'never' && $isUniqueProduct)) {
					// detail result
					$drugIDs = array();
					if ( ($searchType === 'drugfamily') || ( $detail && !($isUniqueProduct))) {
						foreach ($filterDrugs as $filterDrug) {
							$drugIDs[] = $filterDrug->drug_id;
						}
					} else {
						$drugIDs[] = $filterDrugs[0]->drug_id;
					}
					if ( $searchType === 'drugfamily' ) {
						$result = $this->searchDrugDetailStrict($drugIDs, $strength, $filter, $keyword);
					} else {
						$result = $this->searchDrugDetail($drugIDs, $strength, $filter, $keyword);
					}
					$result->isUniqueProduct = true;
				} else {
					// summary result				 
					$result->isDetailSearch = false;
					$result->isUniqueProduct = false;
					$filterDrugs = $this->prepareSummaryResultRandom($filterDrugs,false,$limit,$filter,$related,$strength, $rxRequired);
					$result->data = $filterDrugs;
				}
//				error_log("$searchKey was not in memcache");
			} else {
//				error_log("got $searchKey from memcache");
			}

			$this->memcache->set($searchKey, $result, $this->lifeTime);

			return $result;
		}

		/**
		* Search conditions
		* 
		* @return stdClass
		*/
		function searchConditions($keyword)
		{
			$memcachedKey = str_replace(' ', '_', $keyword);
			$searchKey = $_SERVER['SERVER_NAME'].':v'.$this->version.':'.$memcachedKey.':searchConditions';
			// lookup results in memcache first
			$result = $this->memcache->get($searchKey);

			if (!$result) {
				$resource = new Model_Resource_Search();
				$conditions = $resource->getConditions($keyword);

				// 2.a.iv. Once all matching drugs are found, the data then needs to be aggregated on a results screen
				$result = new stdClass();
				$result->data = $conditions;
			}

			$this->memcache->set($searchKey, $result, $this->lifeTime);

			return $result;
		}


		/**
		* Check unique product
		* 
		* @param mixed $drugs
		*/
		function isUniqueProduct($drugs)
		{
			$isUniqueProduct = false;
			if (count($drugs) == 0) {
				$isUniqueProduct = false;
			} else if (count($drugs) == 1) {
				$isUniqueProduct = true;
			} else {
				$compareIngredient = $drugs[0]->ingredient_hash;
				// This function is confusing as ****, however if there is a 'drug' without ingredients
				// then isUniqueProduct should return false. -- Remi - Feb 7, 2012
				if ( !(strlen( $drugs[0]->ingredient_hash ) )) {
					return false ;
				}
				$isUniqueProduct = true;
				for ($i=1; $i<count($drugs); $i++)
				{
					if ($compareIngredient != $drugs[$i]->ingredient_hash) {
						$isUniqueProduct = false;
						break;
					}
				}
			}
			return $isUniqueProduct;
		}
		
		/**
		* Prepare detail search result
		* 
		* @param mixed $drugs
		*/
		function prepareDetailResult($drugs, $keyword)
		{	
			$detailResult = array();
			$showCanadianDrugs = Utility_Common::showOnlyCanadianDrugs();
			if (!empty($drugs)) { # prevents a warning in the server logs about looping on empty array
				foreach ($drugs as $drug) {
					if ( Utility_Common::showPackageNameOnSearchResults() ) {
						if (count($drug->packages) > 0) {
							$key = $drug->packages[0]->getData('product');
						} else {
							continue;
						}
					} else {
						$key = $drug->name;
					}
					$displayName = $key;
	
					$drugCountryCode = $drug->getScheduleCountry();
					if ($showCanadianDrugs && ($drugCountryCode != 'CAN') ) {
						continue;
					}
					if ( $drug->isScheduleReference() ) {
						// If single reference drug, don't skip over it so it can still setup data
						// to redirect to page
						if ( count($drugs) != 1 ) {
							continue;
						}
					}
	
					// Apply selected split
					$groupByKey = $this->detailResultsGroupBy($drug, $drugCountryCode);
					$key .= $groupByKey;

					// Merge drugs into GroupBy keys
					if ( empty($detailResult[$key]) ) {

						$detailResult[$key] = new stdClass();
						$detailResult[$key]->keyword = $keyword;
						$detailResult[$key]->drug = $drug;
						$detailResult[$key]->displayName = $displayName;
						$detailResult[$key]->ingredients = $this->_prepareIngredients($drug->drug_id);
						$detailResult[$key]->detailresult_groupby_index = 0;

						if (count($drug->packages) > 0) {
							$detailResult[$key]->packages = $drug->packages;
						} else {
							$detailResult[$key]->packages = array();
						}
					} else {											 
						$detailResult[$key]->packages = array_merge($detailResult[$key]->packages, $drug->packages);
					}
	
				}

				$detailResult = $this->detailResultsSetGroupByIndex($detailResult);
			}
			
			if (count($detailResult)) {
				usort($detailResult, array("Model_Catalog", "drugCompare"));
			}
			
			return $detailResult;
		}
		
		/**
		 * Drug Results GroupBy Options
		 * Group drugs by preferred (legacy default), country or none (no seperation)
		 */
		function detailResultsGroupBy($drug, $drugCountryCode = '') {
			$groupByKey = ':';

			$groupByOption = get_option('pw_detailresults_groupby');

			if ($groupByOption == 'preferred') {
				$groupByKey .= $drug->preferred;
			} else if ($groupByOption == 'country') {
				$groupByKey .= $drugCountryCode;
			} else {
				// no groupby
			}
			return $groupByKey;
		}

		function detailResultsSetGroupByIndex($detailResult) {
					
			if (get_option('pw_detailresults_groupby') == 'country') {
				
				// Get pharmacy specified country sort order, then sort remaining countries alphabetically
				$countryOrder = strtoupper(get_option('pw_detailresults_groupby_countrycodes', ''));

				$countryOrder = array_map('trim', explode(',', $countryOrder));

				$countryModel = new Model_Country();
				$countryList = $countryModel->getCountryCodes();

				// merge desired country order list with default alphabetical (by country name) ordered list
				// get the unique country codes and reindex maintaining original sort orders
				$countryOrderedList = array_values(array_unique(array_merge($countryOrder, $countryList))); 

				foreach ($detailResult as $key => $result) {

					$keyPieces = explode(':', $key);
					$keyDrugName = $keyPieces[0];
					$keyCountry = $keyPieces[1];

					$indexOfCountry = array_search($keyCountry, $countryOrderedList);
					$detailResult[$key]->detailresult_groupby_index = $indexOfCountry;

				}

			}

			return $detailResult;
		}

		/**
		* Drug compare function
		* 
		* @param mixed $a
		* @param mixed $b
		* @return mixed
		*/
		function drugCompare($a, $b)
		{
			global $post;
			$post_title = $post->post_title;

			if ($a->detailresult_groupby_index < $b->detailresult_groupby_index) {
				return -1;
			} elseif ($a->detailresult_groupby_index > $b->detailresult_groupby_index) {
				return 1;
			} elseif ($a->drug->preferred && !$b->drug->preferred) { //strtolower(substr($a->drug->name, 0, strlen($a->keyword))) == strtolower($a->keyword) && strtolower(substr($b->drug->name, 0, strlen($b->keyword))) != strtolower($b->keyword)) {
				return -1;
			} elseif ($b->drug->preferred && !$a->drug->preferred) { //strtolower(substr($a->drug->name, 0, strlen($a->keyword))) == strtolower($a->keyword) && strtolower(substr($b->drug->name, 0, strlen($b->keyword))) != strtolower($b->keyword)) {
				return 1;
			} elseif (strlen($post_title) && strtolower(substr($a->drug->name, 0, strlen($post_title))) == strtolower($post_title) && strtolower(substr($b->drug->name, 0, strlen($post_title))) != strtolower($post_title)) {
				return -1;
			} elseif (strlen($post_title) && strtolower(substr($b->drug->name, 0, strlen($post_title))) == strtolower($post_title) && strtolower(substr($a->drug->name, 0, strlen($post_title))) != strtolower($post_title)) {
				return 1;
			} elseif (strlen($a->keyword) && strtolower(substr($a->drug->name, 0, strlen($a->keyword))) == strtolower($a->keyword) && strtolower(substr($b->drug->name, 0, strlen($b->keyword))) != strtolower($b->keyword)) {
				return -1;
			} elseif (strlen($b->keyword) && strtolower(substr($b->drug->name, 0, strlen($b->keyword))) == strtolower($b->keyword) && strtolower(substr($a->drug->name, 0, strlen($a->keyword))) != strtolower($a->keyword)) {
				return 1;
			} elseif ($a->drug->generic == $b->drug->generic) {
				return (strcasecmp($a->drug->name, $b->drug->name));
			} elseif ($a->drug->generic < $b->drug->generic) { 
				return 1;
			} else {
				return -1;
			}
		}

		
		function _prepareIngredients($drugId)
		{
			$resourceSearch = new Model_Resource_Search();
			$ingredients = $resourceSearch->getIngredients($drugId);
			
			$output = array();
			foreach ($ingredients as $ingredient)
			{
				$output[] = $ingredient->ingredient_name;
			}
			
			return implode(', ', $output);
		}
		
		/**
		* Prepare summary search result
		* 
		* @param mixed $drugs
		*/
		function prepareSummaryResult($drugs)
		{
		   
			$summaryResult = array();
			foreach ($drugs as $drug) {
				$drug_key = trim($drug->name);
				if ( array_key_exists($drug_key , $summaryResult ) && !($summaryResult[$drug_key]->isScheduleReference())) {
					continue;
				}
				$summaryResult[$drug_key] = $drug;
			}
		   
			return $summaryResult;
		}



		//process random drugs related to the named drug		 
		function prepareSummaryResultRandom($drugs,$isdetail,$limit,$filter,$related,$strength, $rxRequired = 'both')
		{
			$limit = $limit > 0 ? $limit : 0;
			$count = 0; 
			$summaryResult = array();			
			$related = trim($related); 

			$filter_type = -1;
			switch (strtolower($filter))
			{
				case 'generic':
					$filter_type = 1;
					break;
				case 'brand':
					$filter_type = 0;
					break;
			}

			if ($related != '') shuffle($drugs);

			$showCanadianDrugs = Utility_Common::showOnlyCanadianDrugs();
			foreach ($drugs as $drug) {
				// check filter
				if($filter_type > 0 && $drug->generic != $filter_type) {
					continue;
				}

				if ($rxRequired == 'yes' && $drug->prescriptionrequired != 1) {
					continue;
				} elseif ($rxRequired == 'no' && $drug->prescriptionrequired == 1) {
					continue;
				}

				if ($showCanadianDrugs && $drug->getScheduleCountry() != 'CAN' ) 
					continue;

				$strength_drugs[] = $drug;
				//filter by strength
				$strength_is_match = true;
				if($strength > 0)										
					$strength_is_match = ($drug->strength != '' and $drug->strength == $strength);
				
				if(!$strength_is_match)
					continue;
				
				//filter by related and limit
				


				if($related == '' or strtolower($related) != strtolower(trim($drug->familyname)))
				{					
					$drug_key = trim($drug->familyname);
					if($isdetail)
						$summaryResult[] = $drug;
					else
					if ( ( !isset($summaryResult[$drug_key]) && ($count < $limit || $limit == 0) ) || ( array_key_exists($drug_key , $summaryResult ) && $summaryResult[$drug_key]->isScheduleReference() ) ) 
					{

						if (get_option('pw_treat_familyname_as_alternate_drugname') == 'on') {
							// Setup keys using drug name
							$count++;
							$summaryResult[trim($drug->name)] = $drug;
						} else {
							// Setup keys with drugs grouped under family name
							$count++;
							$summaryResult[$drug_key] = $drug;
						}

					}
				}
			}
			return $summaryResult;
		}
		/**
		* Search drug detail
		* 
		* @param mixed $drugId
		* @return mixed
		*/
		function searchDrugDetail($drugId,$strength=0,$filter="all",$keyword="",$first = 1)
		{
			if (is_array($drugId)) {
				$memcacheDrugID = join('|', $drugId); 	
			} else {
				$memcacheDrugID = $drugId;
			}
			$memcachedKey = str_replace(' ', '_', $keyword);
			$searchKey = $_SERVER['SERVER_NAME'].':v'.$this->version.':'.$memcacheDrugID.':'.$strength.':'.$filter.':'.$memcachedKey.':'.$first.':'.'searchDrugDetail:'.Utility_Common::showOnlyCanadianDrugs();

			// lookup results in memcache first
			$result = $this->memcache->get($searchKey);
			if ($result) {
				$this->memcache->set($searchKey, $result, $this->lifeTime);
				return $result;
			}

			$resource = new Model_Resource_Search();
			$drugIDs = array(); 
			if (is_array($drugId)) {
				$drugIDs = $drugId;
			} else {
				$drugIDs[] = $drugId;
			}

			if (!empty($drugIDs)) {
				$hash = $resource->getIngredientHashById($drugIDs);
				// 2.a.ii. Use the ingredient-hash field to find all other matching drugs
				if (count($hash)) {
					$drugs = $resource->getDrugsByHash($hash);
				} else {
					$drugs = $resource->getDrugFamilyById($drugIDs[0]);
				}
			}

			$filter_type = -1;
			switch (strtolower($filter))
			{
				case 'generic':
					$filter_type = 1;
					break;
				case 'brand':
					$filter_type = 0;
					break;
			}

			///remove drug by strength
			$strength_drugs = [];
			if (!empty($drugs)) {
				foreach ($drugs as $drug) {
				
					//filter by strength
					$strength_is_match = true;
					if($strength > 0)
						$strength_is_match = (($drug->strength != '') && (strcasecmp($drug->strength, $strength) == 0));
				
					if(!$strength_is_match)
						continue;		   

					// check filter
					if($filter_type == -1 or $drug->generic == $filter_type)				
						$strength_drugs[] = $drug;

				}
			}
			
			// 2.a.iii Discard any product packages that are not viewable
			$generic_search = 0; $brand_search = 0;

			// If no keyword is supplied, use the drugName as keyword for sorting
			$searchName = $keyword;
			if (empty($searchName)) {
				$searchName = !empty($_GET['drugName']) ? $_GET['drugName'] : '';
			}
			
			if (is_array($strength_drugs) && !empty($strength_drugs)) {
				$strengthPreferred = array();
				$generic_only = get_option('pw_generic_finds_generic');

				foreach ($strength_drugs as $drug) {
					if ($generic_only && stripos($drug->name, $searchName) === 0 && $drug->generic && ($filter == 'all' || $filter == '<all>') && $first) {
						return $this->searchDrugDetail($drugId,$strength,'generic',$searchName,0);
					}
					if (!$drug->generic && stripos($drug->name, $searchName) === 0) {
						$brand_search = 1;
					}
					$packages = $resource->getViewablePackages($drug->drug_id);
					$preferredDrug = $drug->setPreferred($packages);
					$drug->setPackages($packages);
					$strengthUnits = $drug->strength.$drug->strength_unit.$drug->strengthfreeform;
					if ( (isset($strengthPreferred[$strengthUnits])) && (!$strengthPreferred[$strengthUnits]) ) {
						$strengthPreferred[$strengthUnits] = array();
						$strengthPreferred[$strengthUnits]['generic'] = array();
						$strengthPreferred[$strengthUnits]['brand'] = array();
					}
					if ($drug->generic) { $preferredType = 'generic'; } else { $preferredType = 'brand'; }
					if ($preferredDrug) {
						$strengthPreferred[$strengthUnits][$preferredType]['preferred'] = $drug;
					} else {
						if (empty($strengthPreferred[$strengthUnits][$preferredType]['first'])) {
							$strengthPreferred[$strengthUnits][$preferredType]['first'] = $drug;
						}
					}

					// add drug attributes onto drug
					$resourceAttributes = new Model_Resource_Attributes();
					$resourceAttributes->setId($drug->drug_id);
					$attributes = $resourceAttributes->getPublicAttributes();
					$item['public_attributes'] = array();

					foreach ($attributes as $attr) {
						$attrKey = ltrim($attr->attribute_key, '#'); 
						$item['public_attributes'][$attrKey] = $attr->attribute_value;
						// legacy format for preferred, rest of attributes stored in public_attributes to avoid potential conflicts with names
						if ($attr->attribute_key == '#preferred') $item['preferred'] = $attr->attribute_value; 
					}

					$drug->setData('public_attributes', $item['public_attributes']);

				}

				$package = new Model_Entity_Package();

				// Modify the first element of each brand / generic (by strength) to be preferred, if there is not already a preferred item for that strength
				foreach ($strengthPreferred as $item) {
					if (!empty($item['generic'])) {
						$preferredGeneric = null;
						if (!empty($item['generic']['preferred'])) {
							$preferredGeneric = $item['generic']['preferred'];
						} else {
							$preferredGeneric = $item['generic']['first'];
							if ($preferredGeneric) { $preferredGeneric->preferred = 1; }
						}
						if ($preferredGeneric && !empty($preferredGeneric->packages[0])) {
							$package = $preferredGeneric->packages[0];
							$package->preferred = 1;
						}
					}
					if (!empty($item['brand'])) {
						$preferredBrand = null;
						if (!empty($item['brand']['preferred'])) {
							$preferredBrand = $item['brand']['preferred'];
						} else {
							$preferredBrand = $item['brand']['first'];
							if ($preferredBrand) { $preferredBrand->preferred = 1; }
							$drug = $item['brand']['first'];
							if ($drug) { $drug->preferred = 1; }
						}						
						if ($preferredBrand && !empty($preferredBrand->packages[0])) {
							if (!empty($preferredBrand->packages[0])) {
								$package = $preferredBrand->packages[0];
								$package->preferred = 1;
							}
						}
					}
				}

				// last check - when generic search only shows generic results, brand search should make sure there are brand results
				if ($generic_only && $brand_search) {
					$result_drugs = array();
					foreach ($strength_drugs as $drug) {
						$strengthUnits = $drug->strength.$drug->strength_unit.$drug->strengthfreeform;
						if ($strengthPreferred[$strengthUnits]['brand']) {
							$result_drugs[] = $drug;
						}
					}
					$strength_drugs = $result_drugs;
				}

				if ($package instanceof Model_Entity_Package) {

					// add package attributes onto package
					$resourceAttributes = new Model_Resource_Attributes();
					$resourceAttributes->setId($package->package_id);
					$attributes = $resourceAttributes->getPublicAttributes();
					$item['public_attributes'] = array();

					foreach ($attributes as $attr) {
						$attrKey = ltrim($attr->attribute_key, '#'); 
						$item['public_attributes'][$attrKey] = $attr->attribute_value;
						// legacy format for preferred, rest of attributes stored in public_attributes to avoid potential conflicts with names
						if ($attr->attribute_key == '#preferred') $item['preferred'] = $attr->attribute_value; 
					}

					$package->setData('public_attributes', $item['public_attributes']);
				}

			}
			$result = new stdClass();
			$result->isDetailSearch = (is_array($strength_drugs) && count($strength_drugs) > 0);
			
			$result->data = $this->prepareDetailResult($strength_drugs, $searchName);

			$this->memcache->set($searchKey, $result, $this->lifeTime);
			return $result;
		}

		/**
		* Search drug detail
		* 
		* @param mixed $drugId
		* @return mixed
		*/
		function searchDrugDetailStrict($drugId,$strength=0,$filter="all",$keyword)
		{
			$resource = new Model_Resource_Search();
			$drugIDs = array(); 
			$drugs = array(); 
			if (is_array($drugId)) {
				$drugIDs = $drugId;
			} else {
				$drugIDs[] = $drugId;
			}

			foreach ( $drugIDs as $drug_id ) { 
				$drug_list = $resource->getDrugById($drug_id);
				$drugs = array_merge($drugs, $drug_list);
			}

			$filter_type = -1;
			switch (strtolower($filter))
			{
				case 'generic':
					$filter_type = 1;
					break;
				case 'brand':
					$filter_type = 0;
					break;
			}

			///remove drug by strength
			$strength_drugs = [];
			foreach ($drugs as $drug) {
			
				//filter by strength
				$strength_is_match = true;
				if($strength > 0)
					$strength_is_match = ($drug->strength != '' and $drug->strength == $strength);
			
				if(!$strength_is_match)
					continue;		   

				// check filter
				if($filter_type == -1 or $drug->generic == $filter_type)				
					$strength_drugs[] = $drug;
			}

			// 2.a.iii Discard any product packages that are not viewable	
			if (is_array($strength_drugs) && !empty($strength_drugs)) {		
				foreach ($strength_drugs as $drug) {			
					$packages = $resource->getViewablePackages($drug->drug_id);
					$drug->setPreferred($packages);
					$drug->setPackages($packages);
				}
			}
			$result = new stdClass();
			$result->isDetailSearch = (is_array($strength_drugs) && count($strength_drugs) > 0);
			$result->data = $this->prepareDetailResult($strength_drugs, $keyword);

			return $result;
		}
	}
