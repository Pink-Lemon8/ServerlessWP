<?php

/**
 * Model_Resource_DrugIngredient
 */
class Model_Resource_DrugIngredient extends Utility_ModelResourceBase
{
	/**
	 * Get the entity table name
	 *
	 */
	public function getEntityTable()
	{
		global $wpdb;
		return "{$wpdb->prefix}pw_drug_ingredient";
	}

	/**
	 * Get the entity id field
	 *
	 */
	public function getEntityIdField()
	{
		return array('drug_id', 'ingredient_id');
	}

	/**
	 * Get the updated column for the entity table
	 *
	 */
	public function getStaticFields()
	{
		return array(
			'drug_id',
			'ingredient_id',
			'ingredient_display_order',
			'catalog_updated'
		);
	}

	/**
	 * Delete entity using current object's data
	 *
	 * @param mixed $object
	 */
	public function deleteAllByDrugId($drug_id)
	{
		global $wpdb;



		$drugId = $drug_id;

		$entityIdFields = $this->getEntityIdField();

		$query = 'DELETE FROM ' . $this->getEntityTable() . ' WHERE ' . $entityIdFields[0] . "= '%s'";
		$sql 	= $wpdb->prepare($query, $drugId);
		$wpdb->query($sql);

		return $this;
	}

	//check exist in database

	public function isExist($drug_Id, $ingredientID)
	{
		global $wpdb;
		$entityIdFields = $this->getEntityIdField();
		$condition      = $entityIdFields[1] . '=' . $ingredientID . ' AND ' . $entityIdFields[0] . "='" . $drug_Id . "'";
		$sql = 'SELECT COUNT(*) FROM ' . $this->getEntityTable() . ' WHERE ' . $condition;
		$items = $wpdb->get_row($sql);
		$bResult = false;
		if (count($bResult) > 0) {
			$bResult = true;
		}

		return $bResult;
	}

	/**
	 * Save object collected data
	 *
	 * @param mixed $saveData
	 */
	protected function _processSaveData($arrValues)
	{
		global $wpdb;

		$insertEntity   = true;
		$entityIdFields = $this->getEntityIdField();
		$ingredientId   = $arrValues["ingredient_id"];
		$drugId         = $arrValues["drug_id"];
		$ingredientDisplayOrder = $arrValues["ingredient_display_order"];
		//$condition      = $entityIdFields[1].'="'.$ingredientId.'" AND '. $entityIdFields[0].'="'.$drugId.'"';

		$condition      = $entityIdFields[1] . '=' . $ingredientId . ' AND ' . $entityIdFields[0] . "='" . $drugId . "'";

		if (!empty($drugId)) {
			$select = 'SELECT * FROM ' . $this->getEntityTable() . ' WHERE ' . $condition;
			$items = $wpdb->get_row($select);

			if ($items && (count($items) > 0)) {
				$insertEntity = false;
			}
		}

		/**
		 * Process base row
		 */
		if ($insertEntity) {
			$wpdb->insert($this->getEntityTable(), $arrValues);
		} else {
			$wpdb->update($this->getEntityTable(), $arrValues, array($entityIdFields[0] => $ingredientId, $entityIdFields[1] => $drugId, 'ingredient_display_order' => $ingredientDisplayOrder));
		}

		return $this;
	}
}
