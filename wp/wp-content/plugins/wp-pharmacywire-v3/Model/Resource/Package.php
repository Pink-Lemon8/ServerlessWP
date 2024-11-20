<?php

/**
 * Model_Resource_Package
 */
class Model_Resource_Package extends Utility_ModelResourceBase
{
	public function save($arrDrug)
	{
		// DRUG - save drug
		if (isset($arrDrug["drugId_Ref"]) and $arrDrug["drugId_Ref"] != "") {
			$arrDrug["package"]["drug_id"] = $arrDrug["drugId_Ref"];
		} else {
			$resourceDrug = new Model_Resource_Drug();
			$resourceDrug->setId($arrDrug["drug_id"]);
			// flag as an updated entry
			$arrDrug["catalog_updated"] = 1;
			$resourceDrug->save($arrDrug);
		}

		// ATTRIBUTES - Save drug and package attributes
		$resourceAttributes = new Model_Resource_Attributes();
		$resourceAttributes->setId($arrDrug["drug_id"]);
		$resourceAttributes->delete();
		if (isset($arrDrug["attributes"])) {
			$resourceAttributes->save($arrDrug["attributes"]);
		}
		$resourceAttributes->setId($arrDrug["package"]["package_id"]);
		$resourceAttributes->delete();
		if (isset($arrDrug["package"]["attributes"])) {
			$resourceAttributes->save($arrDrug["package"]["attributes"]);
		}

		// TIER PRICES - Remove existing tier prices for the package and add new ones
		$resourceTierPrices = new Model_Resource_TierPrices();
		$resourceTierPrices->setId($arrDrug["package"]["package_id"]);
		$resourceTierPrices->delete();

		if (isset($arrDrug["package"]["tierprices"])) {
			$resourceTierPrices->save($arrDrug["package"]["tierprices"]);
		} elseif (get_option('pw_v4_legacy_mode', 0) == 1) {
			// V4 doesn't have tiers, so set up a single default tier
			$tierPrices = [];
			$tierPrices[0] = ['quantity' => 1, 'price' => $arrDrug["package"]["price"]];
			$arrDrug["package"]["tierprices"] = $tierPrices;
			$resourceTierPrices->save($arrDrug["package"]["tierprices"]);
		}

		// INGREDIENTS - delete all items relation table between Drugs and Gredients
		$resourceDrugIngredient = new Model_Resource_DrugIngredient();
		$resourceDrugIngredient->deleteAllByDrugId($arrDrug["drug_id"]);
		$resourceIngredient = new Model_Resource_Ingredient();

		if (isset($arrDrug["ingredients"])) {
			foreach ($arrDrug["ingredients"] as $itemIngre) {
				//save ingredients
				$resourceIngredient->setId($itemIngre["ingredient_name"]);
				$itemIngre["catalog_updated"] = 1;
				$resourceIngredient->save($itemIngre);
				$ingredientObj = $resourceIngredient->loadByName($itemIngre["ingredient_name"]);

				//save ingredientDrugs
				$drugIngredient["ingredient_id"] = $ingredientObj->ingredient_id;
				$drugIngredient["drug_id"]       = $arrDrug["drug_id"];
				$drugIngredient["ingredient_display_order"] = $itemIngre["ingredient_display_order"];
				$drugIngredient["catalog_updated"] = 1;
				$resourceDrugIngredient->save($drugIngredient);
			}
		}
		// PACKAGE - save package
		if (isset($arrDrug["drugId_Ref"]) and $arrDrug["drugId_Ref"] != "") {
			$arrDrug["package"]["drug_id"] = $arrDrug["drugId_Ref"];
		} else {
			$arrDrug["package"]["drug_id"] = $arrDrug["drug_id"];
		}
		$this->setId($arrDrug["package"]["package_id"]);
		// flag as an updated entry
		$arrDrug["package"]["catalog_updated"] = 1;
		parent::save($arrDrug["package"]);
	}

	/**
	 * Get the entity table name
	 *
	 */
	public function getEntityTable()
	{
		global $wpdb;
		return "{$wpdb->prefix}pw_packages";
	}

	/**
	 * Get the entity id field
	 *
	 */
	public function getEntityIdField()
	{
		return 'package_id';
	}

	/**
	 * Get the updated column for the entity table
	 *
	 * Columns for update / insert to match what was parsed in XmlApi/ParseData/Catalog.php
	 */
	public function getStaticFields()
	{
		return array(
			'package_id',
			'public_viewable',
			'product',
			'manufacturer',
			'origin_country_code',
			'upc',
			'category',
			'packagingfreeform',
			'packagequantity',
			'price',
			'minitemqty',
			'maxitemqty',
			'multipleitemfactor',
			'feature',
			'comment_external',
			'filling_vendor_id',
			'vendor',
			'vendor_country_code',
			'created',
			'updated',
			'drug_id',
			'sort_value',
			'is_viewable',
			'catalog_updated'
		);
	}
}
