<?php

/**
 * Model_Resource_Search
 */
class Model_Resource_Search
{
	/**
	 * Get drugs
	 *
	 * @param mixed $keyword
	 * @param mixed $searchType Search type drug|ingredient|ingredient_hash|condition|search
	 * @param mixed $filter generic|brand|all
	 * @return stdClass
	 */
	public function getDrugs($keyword, $searchType, $filter, $strength = -1, $limit = -1)
	{
		global $wpdb;

		$query = $this->_buildQuery($keyword, $searchType, $filter, $strength, $limit);
		$results = $wpdb->get_results($query, ARRAY_A);
		$drugs = array();

		foreach ($results as $item) {
			if (array_key_exists('drug_id', $item)) {
				// handle attributes such as #preferred, etc.
				$resourceAttributes = new Model_Resource_Attributes();
				if (!empty($item['drug_id'])) {
					$resourceAttributes->setId($item['drug_id']);
				}
				$attributes = $resourceAttributes->getPublicAttributes();
				foreach ($attributes as $attr) {
					$attrKey = ltrim($attr->attribute_key, '#');
					$item['public_attributes'][$attrKey] = $attr->attribute_value;
					// legacy format for preferred, rest of attributes stored in public_attributes to avoid potential conflicts with names
					if ($attr->attribute_key == '#preferred') {
						$item['preferred'] = $attr->attribute_value;
					}
				}

				if (get_option('pw_filter_by_tag')) {
					$forceShowDrug = false;

					if (preg_match('/^Reference/', $item['schedule'])) {
						$forceShowDrug = $this->referenceFilterOutDrugByTag($item);
					}

					// skip drug if it is to be filtered out, and not overridden/forced to show (reference)
					if ($this->filterOutDrugByTag($item['drug_id']) && !$forceShowDrug) {
						continue;
					}
				}

				$drug = new Model_Entity_Drug();
				$drug->setData($item);
				if (!$drug->familyname) {
					$drug->familyname = $drug->name;
				}
				if (!$drug->strength) {
					$drug->strength = $drug->strengthfreeform;
					$drug->strengthunit = '';
				}

				$drugs[] = $drug;
			}
		}

		return $drugs;
	}

	/**
	 * filterOutDrugByTag
	 * If pw_filter_by_tag is enabled, display only drugs that match set tags
	 * @param $attributeLookupId = drug ID or package ID to look up attributes against in attributes table
	 * @return true if drug should be skipped/removed (filtered out)
	 */
	public function filterOutDrugByTag($attributeLookupId)
	{
		if (get_option('pw_filter_by_tag')) {
			$filterTags = explode(',', get_option('pw_filter_by_tag'));

			$resourceAttributes = new Model_Resource_Attributes();
			$resourceAttributes->setId($attributeLookupId);
			$attributes = $resourceAttributes->getPublicAttributes();

			foreach ($attributes as $attr) {
				if (in_array($attr->attribute_key, $filterTags)) {
					$addDrug = true;
				}
			}

			if (!$addDrug) {
				// if filter by tag is enabled and the drug does not have that tag
				// don't add it and skip to next drug
				return true;
			}
		}

		return false;
	}

	/**
	 * referenceFilterOutDrugByTag
	 * If pw_filter_by_tag is enabled, display only reference drugs that have drug matching set tags
	 * @param $attributeLookupId = drug ID or package ID to look up attributes against in attributes table
	 * @return true if drug should be skipped/removed (filtered out)
	 */
	public function referenceFilterOutDrugByTag($item)
	{
		$referenceHasTag = false;

		if (preg_match('/^Reference/', $item['schedule']) && get_option('pw_filter_by_tag')) {

			// if it's a reference drug do the lookup against the drug that is being referenced
			$hash = $this->getIngredientHashById($item['drug_id']);
			$drugsFoundByHash = $this->getDrugsByHash($hash, true);

			// checked if any of the drugs being referenced are preferred
			foreach ($drugsFoundByHash as $drugH) {

				// if the target drug is preferred, force showing of the reference
				if (!$this->filterOutDrugByTag($drugH->drug_id)) {
					$referenceHasTag = true;
				}
			}
		}

		return $referenceHasTag;
	}

	/**
	 * Get conditions
	 *
	 * @return stdClass
	 */
	public function getConditions($keyword)
	{
		global $wpdb;

		$query = $this->_buildQuery('', 'condition', $keyword);
		$results = $wpdb->get_results($query, ARRAY_A);

		$conditions = array();
		foreach ($results as $condition) {
			if ($condition['ConditionCount'] > 0) {
				$conditions[] = $condition;
			}
		}
		return $conditions;
	}

	private function _lookupTaxonomy($taxonomy, $condition)
	{
		global $wpdb;

		// this function has to try and join two types of data - one is slug-ish and one is English-ish.
		// first check if what entered matched any taxonomys
		$prefix = $wpdb->prefix;
		$taxonomyQuery = "SELECT post_name as keyword from {$prefix}term_taxonomy LEFT JOIN {$prefix}terms USING (term_id) LEFT JOIN {$prefix}term_relationships USING (term_taxonomy_id) LEFT JOIN {$prefix}posts on (object_id = ID) where taxonomy = '{$taxonomy}' and name like '%{$condition}%'";
		$taxonomyResults = $wpdb->get_results($taxonomyQuery);
		if (!$wpdb->num_rows) {
			return $condition;
		}

		$conditionQuery = "SELECT d.condition FROM {$prefix}pw_drugs d WHERE LENGTH(d.condition) > 0 GROUP BY d.condition";
		$conditionResults = $wpdb->get_results($conditionQuery);

		$conditionsArr = array();
		foreach ($conditionResults as $condition) {
			$slug = strtolower($condition->condition);
			$slug = preg_replace('/\W/', "-", $slug);
			$slug = preg_replace('/-+/', "-", $slug);
			$slug = preg_replace('/-$/', "", $slug);
			$conditionsArr[$slug] = $condition->condition;
		}

		$return = '';
		foreach ($taxonomyResults as $taxonomyItem) {
			if ($conditionsArr[$taxonomyItem->keyword]) {
				if (strlen($return)) {
					$return .= '|';
				}
				$return .= $conditionsArr[$taxonomyItem->keyword];
			}
		}
		if (strlen($return)) {
			return $return;
		}
		return $condition;
	}

	/**
	 * Build query for getting drugs
	 *
	 * @param mixed $keyword
	 * @param mixed $searchType Search type drug|ingredient|ingredient_hash|condition|search
	 * @param mixed $filter generic|brand|all
	 * @return stdClass
	 */
	private function _buildQuery($keyword, $searchType, $filter, $strength = -1, $limit = -1)
	{
		global $wpdb;

		switch ($filter) {
			case 'generic':
				$filterWhere = ' AND d.generic=1 AND d.public_viewable = 1 ';
				$filterWhere2 = ' WHERE B.generic=1 AND B.public_viewable = 1 ';
				break;
			case 'brand':
				$filterWhere = ' AND d.generic=0  AND d.public_viewable = 1 ';
				$filterWhere2 = ' WHERE B.generic=0 AND B.public_viewable = 1 ';
				break;
			default: // case 'all':
				$filterWhere = ' AND d.public_viewable = 1';
				$filterWhere2 = ' WHERE B.public_viewable = 1 ';
				break;
		}

		// check strength condition (hungnd 20110707)
		$strengthWhere = '';
		// if($strength>0)
		// $strengthWhere = ' And strength = '.$strength;

		#echo("_buildQuery(searchType: $keyword, $searchType, $filter, $strength, $limit)<br/>");
		switch ($searchType) {
			case 'drugid':
				$query = <<<EOD
						SELECT d.* FROM {$wpdb->prefix}pw_drugs as d
						WHERE d.drug_id = '{$keyword}' OR d.drug_id like '{$keyword}-%' {$filterWhere} {$strengthWhere}
						ORDER BY d.name, IF(d.strengthfreeform, d.strengthfreeform, d.strength) + 10000 ASC
EOD;
				break;
			case 'drugfamily':
				$query = <<<EOD
						SELECT d.* FROM {$wpdb->prefix}pw_drugs as d
						WHERE d.familyname = '{$keyword}' {$filterWhere} {$strengthWhere}
						ORDER BY IF(SUBSTRING(d.name, 1, LENGTH('$keyword')) = '$keyword', 0, 1), d.name, IF(d.strengthfreeform, d.strengthfreeform, d.strength) + 10000 ASC
EOD;
				break;
			case 'drug':
				$query = <<<EOD
						SELECT d.* FROM {$wpdb->prefix}pw_drugs as d
						WHERE d.name like '{$keyword}%' {$filterWhere} {$strengthWhere}
						ORDER BY IF(SUBSTRING(d.name, 1, LENGTH('$keyword')) = '$keyword', 0, 1), d.name, IF(d.strengthfreeform, d.strengthfreeform, d.strength) + 10000 ASC
EOD;
				break;
			case 'ingredient_hash': // added a search by ingredient_hash
				$query = <<<EOD
						SELECT DISTINCT d.* FROM {$wpdb->prefix}pw_drugs as d
						WHERE (d.ingredient_hash = '{$keyword}') {$filterWhere} {$strengthWhere}
						ORDER BY d.name, IF(d.strengthfreeform, d.strengthfreeform, d.strength) + 10000 ASC
EOD;
				break;
			case 'ingredient':
				$query = <<<EOD
						SELECT DISTINCT B.* FROM {$wpdb->prefix}pw_drugs as A
						INNER JOIN
						(SELECT DISTINCT d.*
							FROM {$wpdb->prefix}pw_drugs as d
							INNER JOIN {$wpdb->prefix}pw_drug_ingredient AS di
								ON d.drug_id=di.drug_id
							INNER JOIN {$wpdb->prefix}pw_ingredients AS i
								ON i.ingredient_id = di.ingredient_id
							WHERE (i.ingredient_name like '{$keyword}%') {$filterWhere} {$strengthWhere}
						) AS B
						ON A.ingredient_hash = B.ingredient_hash 
						{$filterWhere2}
						ORDER BY B.name, IF(B.strengthfreeform, B.strengthfreeform, B.strength) + 10000 ASC
EOD;
				break;

			case 'ingredient_list':
				$query = <<<EOD
						SELECT d.drug_id, d.name, group_concat(ingredient_name SEPARATOR '; ') 
							AS ingredient_list, count(i.ingredient_id) AS ingredient_count 
						FROM {$wpdb->prefix}pw_drugs d 
						LEFT JOIN {$wpdb->prefix}pw_drug_ingredient do using (drug_id) 
						LEFT JOIN {$wpdb->prefix}pw_ingredients i using (ingredient_id) group by d.drug_id having ingredient_list = '{$keyword}' order by ingredient_list;
EOD;
				break;

			case 'condition':
				if ($keyword && strlen($keyword) && $keyword != '<all>' && $keyword != '<rx>' && $keyword != '<otc>') {
					$keyword = esc_sql($keyword);
					$conditionsArr = explode('|', $keyword);
					$conditionFilter = '';
					foreach ($conditionsArr as $cond) {
						if (strlen($conditionFilter)) {
							$conditionFilter .= ' OR ';
						}
						$cond = trim($cond);
						$conditionFilter .= "d.condition = '{$cond}'";
					}
					$query = <<<EOD
							SELECT d.* FROM {$wpdb->prefix}pw_drugs as d
							WHERE {$conditionFilter} {$filterWhere} {$strengthWhere}
							ORDER BY d.name, IF(d.strengthfreeform, d.strengthfreeform, d.strength) + 10000 ASC
EOD;
				} else {
					// Added support to display conditions as a result instead of show drugs. Looks much nicer
					$rxFilter = 'and d.prescriptionrequired = ';
					$conditionFilter = "d.condition IS NOT NULL ";
					if ($filter == '<rx>') {
						$rxFilter .= '1';
					} elseif ($filter == '<otc>') {
						$rxFilter .= "0 and d.schedule not like 'Reference%'";
					} else {
						$rxFilter = '';
						if (strlen($filter) && $filter != 'all' && $filter != '<all>') {
							$taxonomy = get_option('pw_extended_taxonomy');
							$wildcard = 1;
							if (strlen($taxonomy)) {
								$taxonomyKeyword = $this->_lookupTaxonomy($taxonomy, $filter);
								if ($taxonomyKeyword != $filter) {
									$wildcard = 0;
									$filter = $taxonomyKeyword;
								}
							}

							$conditionsArr = explode('|', $filter);
							$conditionFilter = '';
							foreach ($conditionsArr as $cond) {
								if (strlen($conditionFilter)) {
									$conditionFilter .= ' OR ';
								}
								$cond = trim($cond);
								if ($wildcard) {
									$conditionFilter .= "d.condition like '{$cond}%'";
								} else {
									$conditionFilter .= "d.condition = '{$cond}'";
								}
							}
						}
					}
					$query = <<<EOD
							SELECT d.condition, count(d.drug_id) as ConditionCount FROM {$wpdb->prefix}pw_drugs as d
							INNER JOIN {$wpdb->prefix}pw_packages p using(drug_id)
							WHERE $conditionFilter and d.public_viewable = 1 and p.public_viewable=1
							$rxFilter group by d.condition order by d.condition ASC
EOD;
				}
				break;
			default: //case 'search':
				if (strlen($keyword) == 1) {
					$product_table = ' ';
					if (Utility_Common::showPackageNameOnSearchResults()) {
						$product_table = " INNER JOIN {$wpdb->prefix}pw_packages AS p ON d.drug_id=p.drug_id ";
					}

					if (is_numeric($keyword)) {
						$product_search = " d.name REGEXP '^[0-9]' ";
						if (Utility_Common::showPackageNameOnSearchResults()) {
							$product_search = " p.product REGEXP '^[0-9]' ";
						}
						$query = "SELECT d.* FROM {$wpdb->prefix}pw_drugs as d $product_table WHERE $product_search {$filterWhere} {$strengthWhere}";
					} else {
						$product_search = " d.name like '{$keyword}%' ";
						if (Utility_Common::showPackageNameOnSearchResults()) {
							$product_search = " p.product like '{$keyword}%' ";
						}

						if (get_option('pw_treat_familyname_as_alternate_drugname') == 'on') {
							$product_search .= " OR d.familyname like '{$keyword}%' ";
						}

						$query = <<<EOD
							SELECT d.* FROM {$wpdb->prefix}pw_drugs as d $product_table
							LEFT JOIN {$wpdb->prefix}pw_attributes da ON da.attribute_key LIKE '#brand-name%' AND d.drug_id=da.attribute_id
							WHERE ($product_search OR da.attribute_value like '{$keyword}%') {$filterWhere} {$strengthWhere}
							GROUP BY d.drug_id
							ORDER BY d.name, IF(d.strengthfreeform, d.strengthfreeform, d.strength) + 10000 ASC
EOD;
					}
				} else {
					$product_table = ' ';
					$product_search = ' ';
					if (Utility_Common::showPackageNameOnSearchResults()) {
						$product_table = " INNER JOIN {$wpdb->prefix}pw_packages AS p ON d.drug_id=p.drug_id ";
						$product_search = " OR p.product LIKE '{$keyword}%' ";
					}

					if (get_option('pw_treat_familyname_as_alternate_drugname') == 'on') {
						$product_search = " OR d.familyname like '{$keyword}%' ";
					}

					$query = <<<EOD
							SELECT C.* FROM (
							(SELECT DISTINCT B.* FROM {$wpdb->prefix}pw_drugs as A
							INNER JOIN
							(SELECT DISTINCT d.*
								FROM {$wpdb->prefix}pw_drugs as d
								INNER JOIN {$wpdb->prefix}pw_drug_ingredient AS di
									ON d.drug_id=di.drug_id
								INNER JOIN {$wpdb->prefix}pw_ingredients AS i
									ON i.ingredient_id = di.ingredient_id $product_table
								LEFT JOIN {$wpdb->prefix}pw_attributes da ON da.attribute_key LIKE '#brand-name%' AND d.drug_id=da.attribute_id
								WHERE (d.name like '{$keyword}%' OR i.ingredient_name like '{$keyword}%' OR da.attribute_value like '{$keyword}%' $product_search) {$filterWhere} {$strengthWhere}
							) AS B
							ON A.ingredient_hash = B.ingredient_hash AND length(A.ingredient_hash) != 0 
							{$filterWhere2})
							UNION
							(SELECT DISTINCT B.* FROM {$wpdb->prefix}pw_drugs as B {$filterWhere2} {$strengthWhere} AND B.name like '{$keyword}%' AND length(B.ingredient_hash)=0)
							) AS C
							ORDER BY IF(SUBSTRING(C.name, 1, LENGTH('$keyword')) = '$keyword', 0, 1), C.name, IF(C.strengthfreeform, C.strengthfreeform, C.strength) + 10000 ASC
EOD;
				}
				break;
		}

		return $query;
	}

	/**
	 * Get the ingredient-hash by drug name or ingredient name
	 *
	 * @param mixed $name Drug name or ingredient name
	 */
	public function getIngredientHash($name)
	{
		//return array('b5488aeff42889188d03c9895255cecc', 'abdbeb4d8dbe30df8430a8394b7218ef');
		global $wpdb;
		if (is_numeric($name)) {
			$where = "WHERE d.name REGEXP '^[0-9]' OR i.ingredient_name REGEXP '^[0-9]'";
		} else {
			$where = "WHERE d.name LIKE '{$name}%' OR i.ingredient_name LIKE '{$name}%'";
		}

		$query = "SELECT DISTINCT d.ingredient_hash 
					FROM {$wpdb->prefix}pw_drugs as d 
					INNER JOIN {$wpdb->prefix}pw_drug_ingredient AS di 
						ON d.drug_id=di.drug_id 
					INNER JOIN {$wpdb->prefix}pw_ingredients AS i 
						ON i.ingredient_id = di.ingredient_id  " .
			$where;

		return $wpdb->get_col($query);
	}

	/**
	 * Get the ingredient-hash by drug name or ingredient name
	 *
	 * @param mixed $name Drug name or ingredient name
	 */
	public function getIngredientHashById($drugId)
	{
		$drugIDs = array();
		if (is_array($drugId)) {
			$drugIDs = $drugId;
		} else {
			$drugIDs[] = $drugId;
		}
		global $wpdb;

		$query = "SELECT DISTINCT d.ingredient_hash 
					FROM {$wpdb->prefix}pw_drugs as d 
					WHERE d.drug_id IN ('" . implode("','", $drugIDs) . "') AND d.public_viewable = 1 ORDER BY IF(d.strengthfreeform, d.strengthfreeform, d.strength) + 10000 ASC ";

		$results = $wpdb->get_results($query);

		$ingredients = array();

		foreach ($results as $ingredient) {
			if ($ingredient->ingredient_hash != '') {
				$ingredients[] = $ingredient->ingredient_hash;
			}
		}
		return $ingredients;
	}

	/**
	 * Get drugs by ingredient hash
	 *
	 * @param mixed $ingredientHash
	 * @return List of Model_Entity_Drug
	 */
	public function getDrugsByHash($ingredientHash, $ignoreReference = false)
	{
		global $wpdb;
		$query = "SELECT * FROM {$wpdb->prefix}pw_drugs WHERE ingredient_hash IN ('" . implode("','", $ingredientHash) . "') AND public_viewable = 1 ORDER BY IF(strengthfreeform, strengthfreeform, strength) + 10000 ASC";
		$results = $wpdb->get_results($query, ARRAY_A);

		$drugs = array();

		foreach ($results as $item) {
			if (get_option('pw_filter_by_tag')) {
				$forceShowDrug = false;

				if (preg_match('/^Reference/', $item['schedule']) && !$ignoreReference) {
					$forceShowDrug = $this->referenceFilterOutDrugByTag($item);
				}

				// skip drug if it is to be filtered out, and not overridden/forced to show (reference)
				if ($this->filterOutDrugByTag($item['drug_id']) && !$forceShowDrug) {
					continue;
				}
			}

			if (preg_match('/^Reference/', $item['schedule']) && $ignoreReference) {
				// if ignoreReference flag is set, return results without reference drugs
				// primarily used to avoid infinite loop searching against reference drugs
				// as in referenceFilterOutDrugByTag()
				continue;
			}

			$drug = new Model_Entity_Drug();
			$drug->setData($item);
			if (!$drug->familyname) {
				$drug->familyname = $drug->name;
			}
			if (!$drug->strength) {
				$drug->strength = $drug->strengthfreeform;
				$drug->strengthunit = '';
			}
			$drugs[] = $drug;
		}

		return $drugs;
	}

	/**
	 * Get drugs by family
	 *
	 * @param mixed $drugId
	 * @return List of Model_Entity_Drug
	 */
	public function getDrugFamilyById($drugId)
	{
		global $wpdb;

		$drugs = $this->getDrugById($drugId);

		if (!empty($drugs) && (strlen($drugs[0]->familyname) == 0) && (count($drugs[0]->ingredient) == 0)) {
			$drugname_search = $drugs[0]->name;
			$query = $wpdb->prepare("SELECT * FROM {$wpdb->prefix}pw_drugs WHERE name = %s AND public_viewable = 1 ORDER BY name, IF(strengthfreeform, strengthfreeform, strength) + 10000 ASC", [$drugname_search]);
		} elseif (!empty($drugs) && (strlen($drugs[0]->familyname) > 0)) {
			$family = $drugs[0]->familyname;
			$query = $wpdb->prepare("SELECT * FROM {$wpdb->prefix}pw_drugs WHERE familyname = %s AND public_viewable = 1 ORDER BY name, IF(strengthfreeform, strengthfreeform, strength) + 10000 ASC", [$family]);
		} else {
			return $drugs;
		}

		$results = $wpdb->get_results($query, ARRAY_A);

		$drugs = array();
		foreach ($results as $item) {
			$drug = new Model_Entity_Drug();
			$drug->setData($item);
			if (!$drug->familyname) {
				$drug->familyname = $drug->name;
			}
			if (!$drug->strength) {
				$drug->strength = $drug->strengthfreeform;
				$drug->strengthunit = '';
			}
			$drugs[] = $drug;
		}

		return $drugs;
	}


	/**
	 * Get drug by ID
	 *
	 * @param mixed $drugId
	 * @return Model_Entity_Drug
	 */
	public function getDrugById($drugId)
	{
		global $wpdb;

		$query = "SELECT * FROM {$wpdb->prefix}pw_drugs WHERE drug_id = '{$drugId}' AND public_viewable = 1 ";
		$results = $wpdb->get_results($query, ARRAY_A);

		$drugs = array();

		foreach ($results as $item) {
			if (get_option('pw_filter_by_tag')) {
				$forceShowDrug = false;

				if (preg_match('/^Reference/', $item['schedule'])) {
					$forceShowDrug = $this->referenceFilterOutDrugByTag($item);
				}

				// skip drug if it is to be filtered out, and not overridden/forced to show (reference)
				if ($this->filterOutDrugByTag($item['drug_id']) && !$forceShowDrug) {
					continue;
				}
			}

			$drug = new Model_Entity_Drug();
			$drug->setData($item);

			if (!$drug->strength) {
				$drug->strength = $drug->strengthfreeform;
				$drug->strengthunit = '';
			}
			$drug->ingredient = $this->getIngredients($drug->drug_id);
			$drugs[] = $drug;
		}

		return $drugs;
	}

	/**
	 * Get drug by ID
	 *
	 * @param mixed $drugId
	 * @return Model_Entity_Drug
	 */
	public function getDrugByPackageId($drugPackageId)
	{
		global $wpdb;

		$query = $wpdb->prepare("SELECT drug_id FROM {$wpdb->prefix}pw_packages WHERE package_id = %s LIMIT 1", $drugPackageId);
		$results = $wpdb->get_results($query, ARRAY_A);

		if (!empty($results)) {
			$drugs = $this->getDrugById($results[0]["drug_id"]);
			if (isset($drugs[0]) && ($drugs[0] instanceof Model_Entity_Drug)) {
				return $drugs[0];
			}
		}

		return null;
	}

	/**
	 * Get viewable packages by drugPackageId
	 *
	 * @param mixed $drugPackageId
	 * @return List of Model_Entity_Package
	 */
	public function getPackage($drugPackageId)
	{
		global $wpdb;

		$query = $wpdb->prepare("SELECT p.*, d.prescriptionrequired, count(*) as tier_count, 
			GROUP_CONCAT(tp.quantity ORDER BY tp.quantity ASC SEPARATOR ':') as tier_quantity, 
			GROUP_CONCAT(tp.price ORDER BY tp.price ASC SEPARATOR ':') tier_price 
			FROM {$wpdb->prefix}pw_packages p
			LEFT JOIN {$wpdb->prefix}pw_drugs d
			ON (p.drug_id=d.drug_id)
			LEFT JOIN {$wpdb->prefix}pw_packages_tierprice as tp
			ON (tp.package_id=p.package_id)
			WHERE p.package_id = %s GROUP BY p.package_id LIMIT 1 ", $drugPackageId);

		$results = $wpdb->get_results($query, ARRAY_A);

		// $package = new Model_Entity_Package();
		$package = $results[0];

		// handle attributes such as #preferred, etc.
		$resourceAttributes = new Model_Resource_Attributes();
		$resourceAttributes->setId($package['package_id']);
		$attributes = $resourceAttributes->getPublicAttributes();
		
		foreach ($attributes as $attr) {
			$attrKey = ltrim($attr->attribute_key, '#');
			$package['public_attributes'][$attrKey] = $attr->attribute_value;
			// legacy format for preferred, rest of attributes stored in public_attributes to avoid potential conflicts with names
			if ($attr->attribute_key == '#preferred') {
				$package['preferred'] = $attr->attribute_value;
			}
		}

		if (!isset($package['preferred'])) {
			$resourceAttributes->setId($package['drug_id']);
			$package['preferred'] = $resourceAttributes->getData('#preferred');
		}

		return $package;
	}

	public function getPackageObj($drugPackageId)
	{
		$package = new Model_Entity_Package();
		$package->setData($this->getPackage($drugPackageId));
		return $package;
	}

	/**
	 * Get viewable packages by drug ID
	 *
	 * @param mixed $drugId
	 * @return List of Model_Entity_Package
	 */
	public function getViewablePackages($drugId)
	{
		global $wpdb;

		// Find packages as well as any tier prices
		$query = $wpdb->prepare("SELECT *, p_tp.quantity AS tier_quantity, IFNULL(sort_value, cast(SUBSTRING_INDEX(p.packagequantity, '@', 1) AS DECIMAL(10,5) )) AS sort_value 
			FROM {$wpdb->prefix}pw_packages AS p 
			LEFT JOIN {$wpdb->prefix}pw_packages_tierprice AS p_tp ON (p.package_id=p_tp.package_id) 
			WHERE drug_id = %s AND public_viewable = 1 
			ORDER BY tier_quantity, sort_value", $drugId);
		$results = $wpdb->get_results($query, ARRAY_A);

		$packages = array();

		foreach ($results as $item) {

			// package attributes -- handle attributes such as #preferred, etc.
			$resourceAttributes = new Model_Resource_Attributes();
			$resourceAttributes->setId($item['package_id']);
			$attributes = $resourceAttributes->getPublicAttributes();

			foreach ($attributes as $attr) {
				$attrKey = ltrim($attr->attribute_key, '#');
				$item['public_attributes'][$attrKey] = $attr->attribute_value;
				// legacy format for preferred, rest of attributes stored in public_attributes to avoid potential conflicts with names
				if ($attr->attribute_key == '#preferred') {
					$item['preferred'] = $attr->attribute_value;
				}
			}

			if (!isset($item['preferred']) || !$item['preferred']) {
				$resourceAttributes->setId($drugId);
				$item['preferred'] = $resourceAttributes->getData('#preferred');
			}

			$package = new Model_Entity_Package();
			$package->setData($item);
			$packages[] = $package;
		}

		return $packages;
	}

	/**
	 * Get ingredients by drug ID
	 *
	 * @param mixed $drugId
	 * @return List of Model_Entity_Package
	 */
	public function getIngredients($drugId)
	{
		global $wpdb;

		$query = "SELECT DISTINCT i.ingredient_id, i.ingredient_name FROM {$wpdb->prefix}pw_ingredients AS i INNER JOIN {$wpdb->prefix}pw_drug_ingredient AS id ON i.ingredient_id=id.ingredient_id WHERE id.drug_id = '{$drugId}' ORDER BY id.ingredient_display_order";

		$results = $wpdb->get_results($query, ARRAY_A);

		$ingredients = array();

		foreach ($results as $item) {
			$ingredient = new Model_Entity_Ingredient();
			$ingredient->setData($item);
			$ingredients[] = $ingredient;
		}

		return $ingredients;
	}
}
