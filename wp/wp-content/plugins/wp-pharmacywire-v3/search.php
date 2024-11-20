<?php
define('DOING_AJAX', true);
define('SHORTINIT', true);
require_once('../../../wp-load.php');
require_once('./Utility/SuggestiveSearch.php');

function microtime_float()
{
	list($usec, $sec) = explode(" ", microtime());
	return ((float)$usec + (float)$sec);
}

$time_start = microtime_float();
$search = strtolower(urldecode($_POST['drugName']));
if ($search == 'initialize') {
	seed_data();
	exit(0);
}

if (!strlen($search) || strlen($search) < 3) {
	echo json_encode(array('success' => 0, 'error' => 'Search string must be defined', 'error_code' => '10000'));
	exit(0);
} else {
	$values = search_cache($search, 1);
	if ($values == 'none') {
		echo json_encode(array('success' => 0, 'error' => 'No matching results found', 'error_code' => '10001'));
		exit(0);
	}
	if (empty($values)) {
		$values = search_detail($search);
	}
	if ($values == 'none') {
		echo json_encode(array('success' => 0, 'error' => 'No matching results found', 'error_code' => '10002'));
		exit(0);
	}
	$results = array();
	if (!empty($values)) {
		foreach ($values as $value) {
			$display = str_replace('&comma;', ',', $value);
			$results[] = array('name' => $display);
		}
	}
}
echo json_encode(array('success' => 1, 'drug-lookup-results' => $results));

// $suggestions - array of exact drug names to check against & lastly the partial search term
// returns array or std objects with name (string), FullMatch (bool), PartialMatch (bool), SoundexMatch (bool) -- in that order priority
function queryDrugs($suggestions, $search_term, $soundex)
{
	global $wpdb;
	$prefix = $wpdb->prefix;
	// add in the soundex, search_term for partial soundex & duplicate $suggestions for IF portions of the SQL statement
	$word_placeholders = implode(',', array_fill(0, count($suggestions), "'%s'"));
	$suggestions = array_merge($suggestions, (array) $search_term, (array) $soundex, (array) $search_term);
	$suggestions = array_merge($suggestions, $suggestions);

	$prepareStmnt = $wpdb->prepare("SELECT name, 
	IF (d.name in ({$word_placeholders}), 1, 0) as FullMatch,
	IF (d.name like CONCAT(%s, '%'), 1, 0) as PartialMatch,
	IF (SOUNDEX(d.name) = SOUNDEX(%s), 1, 0) as SoundexMatch,
	IF (SOUNDEX(SUBSTRING_INDEX(d.name, ' ', 1)) = SOUNDEX(%s), 1, 0) as SoundexPartialMatch
	FROM `{$prefix}pw_drugs` d
	LEFT JOIN `{$prefix}pw_packages` p USING (drug_id) 
	WHERE (
		(d.name in ({$word_placeholders}))
		OR 
		(d.name like CONCAT(%s, '%'))
		OR 
		(SOUNDEX(d.name) = SOUNDEX(%s))
		OR 
		(SOUNDEX(SUBSTRING_INDEX(d.name, ' ', 1)) = SOUNDEX(%s))
	) AND d.public_viewable = 1 and p.public_viewable = 1 GROUP BY name 
	ORDER BY FullMatch DESC, PartialMatch DESC, SoundexMatch DESC", $suggestions);
	
	$drugs = $wpdb->get_results($prepareStmnt);
	return $drugs;
}

function queryIngredients($suggestions, $search_term)
{
	global $wpdb;
	$prefix = $wpdb->prefix;
	$word_placeholders = implode(',', array_fill(0, count($suggestions), "'%s'"));
	$suggestions = array_merge($suggestions, (array) $search_term);

	$ingredients = $wpdb->get_results($wpdb->prepare("SELECT ingredient_name as name FROM {$prefix}pw_ingredients i LEFT JOIN {$prefix}pw_drug_ingredient di ON (i.ingredient_id=di.ingredient_id) LEFT JOIN {$prefix}pw_drugs d ON (di.drug_id=d.drug_id) LEFT JOIN {$prefix}pw_packages p ON (d.drug_id=p.drug_id) 
	WHERE (
		(i.ingredient_name in ({$word_placeholders})) 
		OR 
		(i.ingredient_name like CONCAT(%s, '%'))
	) AND d.public_viewable = 1 and p.public_viewable = 1 GROUP BY ingredient_name", $suggestions));

	return $ingredients;
}

function queryConditions($suggestions, $search_term)
{
	global $wpdb;
	$prefix = $wpdb->prefix;
	$word_placeholders = implode(',', array_fill(0, count($suggestions), "'%s'"));
	$suggestions = array_merge($suggestions, (array) $search_term);
	$conditions = $wpdb->get_results($wpdb->prepare("SELECT `condition` as name FROM {$prefix}pw_drugs d LEFT JOIN {$prefix}pw_packages p USING (drug_id) 
	WHERE (
		(d.`condition` in ({$word_placeholders})) 
		OR
		(d.`condition` like CONCAT(%s, '%'))
	) AND d.public_viewable = 1 and p.public_viewable = 1 GROUP BY `condition`", $suggestions));
	return $conditions;
}

function search_detail($term)
{
	global $wpdb;
	$prefix = $wpdb->prefix;
	$suggestiveSearch = new SuggestiveSearch();
	$suggestions = $suggestiveSearch->getAutocompleteSuggestions($term);

	// Now, from those suggestions, let's see which are actual ones that we'll "support" - as in,
	// they're in the drug, package or ingredients tables. Start off with building the word match portion
	if (empty($suggestions) || !is_array($suggestions)) {
		$suggestions[] = 'supercalifragilisticexpialidcious'; // minimum placeholder for sql query
	}

	$term_parts = explode(' ', $term);
	$valid_parts = array();
	foreach ($term_parts as $part) {
		if (!preg_match('/^[\s0-9]/', $part)) {
			$valid_parts[] = $part;
		}
	}

	$search_term = implode(' ', $valid_parts);
	$soundex = $valid_parts[0];
	$mergedResults = array(); // array of drug, ingredient and condition results
	// include soundex search against drugs, not ingredients/conditions 
	$drugs = queryDrugs($suggestions, $search_term, $soundex);
	if (!empty($drugs)) {
		foreach ($drugs as $drug) {
			$drugName = str_replace(',', '&comma;', $drug->name);
			$mergedResults[strtolower($drugName)] = 1;
		}
	}

	$ingredients = queryIngredients($suggestions, $search_term);
	if (!empty($ingredients)) {
		foreach ($ingredients as $ingredient) {
			$ingredientName = str_replace(',', '&comma;', $ingredient->name);
			$mergedResults[strtolower($ingredientName)] = 1;
		}
	}

	$conditions = queryConditions($suggestions, $search_term);
	if (!empty($conditions)) {
		foreach ($conditions as $condition) {
			$conditionName = str_replace(',', '&comma;', $condition->name);
			$mergedResults[strtolower($conditionName)] = 1;
		}
	}

	$mapped = array();
	foreach ($mergedResults as $item => $set) {
		$prepare = $wpdb->prepare("SELECT `attribute_value` as brand FROM {$prefix}pw_drugs d LEFT JOIN {$prefix}pw_attributes a ON (drug_id=attribute_id) WHERE (d.`name` = %s AND a.`attribute_key` like '#brand%') GROUP BY `attribute_value`", $item);
		$brands = $wpdb->get_results($prepare);

		$found_match = 0;
		if (count($brands)) {
			foreach ($brands as $brand) {
				if (strtolower($brand->brand) != $item) {
					$mapped[strtolower($brand->brand) . " ($item)"] = 1;
					$found_match = 1;
				}
			}
		}

		if (!$found_match) {
			$prepare = $wpdb->prepare("SELECT d.name, group_concat(ingredient_name SEPARATOR '| ') as ingredient_list, count(i.ingredient_id) as ingredient_count from {$prefix}pw_drugs d left join {$prefix}pw_drug_ingredient do using (drug_id) left join {$prefix}pw_ingredients i using (ingredient_id) WHERE d.name = %s GROUP BY d.drug_id order by ingredient_name", $item);
			$brands = $wpdb->get_results($prepare);

			if (count($brands)) {
				foreach ($brands as $brand) {
					$ingredients = explode('| ', $brand->ingredient_list);
					sort($ingredients);
					$ingredients = strtolower(implode('; ', $ingredients));

					if (strlen($ingredients)) {
						$ingredients = " (" . $ingredients . ")";
						$mapped[strtolower($brand->name) . $ingredients] = 1;
						$found_match = 1;
					}
				}
			}
		}

		if (!$found_match) {
			$mapped[$item] = 1;
		}
	}

	$results = array_keys($mapped);

	usort($results, function ($a, $b) use ($search_term) {
		// sort should not include ingredients in brackets added above, sort by the drug names
		$a = preg_replace('/\s?\(.*\)/', '', $a);
		$b = preg_replace('/\s?\(.*\)/', '', $b);
		similar_text($search_term, $a, $percentA);
		similar_text($search_term, $b, $percentB);
		return $percentA === $percentB ? 0 : ($percentA > $percentB ? -1 : 1);
	});
	$finalSuggestions = implode('|', $results);
	if (!strlen($finalSuggestions)) {
		search_cache($term, 0); // make sure to log unsuccessful term
		return 'none';
	}

	$wpdb->query($wpdb->prepare("INSERT INTO {$prefix}pw_search (Term, Results, Added) VALUES('%s','%s', NOW())", $term, $finalSuggestions));
	return search_cache($term, 0);
}

function seed_data()
{
	global $wpdb;
	$prefix = $wpdb->prefix;
	$drugs = $wpdb->get_results("SELECT name from {$prefix}pw_drugs");
	$ingredients = $wpdb->get_results("SELECT ingredient_name as name from {$prefix}pw_ingredients");
	$allsearches = array_merge($drugs, $ingredients);
	foreach ($allsearches as $item) {
		for ($i = 3; $i <= strlen($item->name); $i++) {
			$term = substr($item->name, 0, $i);
			if (!search_cache($term, 1)) {
				search_detail($term);
			}
		}
	}
}

// Removed as query/elsewhere handles more sort ordering now
//
// function sort_by_term($a, $b)
// {
// 	global $search;
// 	$suba = strpos('_' . $a, $search);
// 	$subb = strpos('_' . $b, $search);
// 	//error_log("search: $search; a: $a; b: $b; suba: $suba; subb: $subb");
// 	if ($suba == 1 && $subb != 1) {
// 		return -1;
// 	}
// 	if ($suba != 1 && $subb == 1) {
// 		return 1;
// 	}
// 	return strcasecmp($a, $b);
// }

function search_cache($term, $initialsearch)
{
	global $wpdb;
	$prefix = $wpdb->prefix;
	$results = $wpdb->get_row($wpdb->prepare("SELECT Results,DATEDIFF(NOW(), Added) > 30 AS Old FROM {$prefix}pw_search WHERE Term = '%s'", $term));
	if ($results !== null) {
		# lazy way to keep the cache relevant without slowing down immediate search result
		if ($results->Old) {
			$wpdb->query($wpdb->prepare("DELETE FROM {$prefix}pw_search WHERE Term = '%s'", $term));
		} else {
			$wpdb->query($wpdb->prepare("UPDATE {$prefix}pw_search SET UseCount=UseCount+1 WHERE Term = '%s'", $term));
		}
		if ($results->Results) {
			$array = explode('|', $results->Results);
			// Removed as query/elsewhere handles more sort ordering now
			// usort($array, 'sort_by_term');
			return $array;
		}
		return 'none';
	}
	if (!$initialsearch) {
		$wpdb->query($wpdb->prepare("INSERT INTO {$prefix}pw_search (Term, Added, UseCount) VALUES('%s', NOW(), 1)", $term));
	}
	return null;
}
