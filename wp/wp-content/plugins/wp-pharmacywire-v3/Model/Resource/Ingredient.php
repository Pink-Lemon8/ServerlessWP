<?php

/**
 * Model_Resource_Package
 */
class Model_Resource_Ingredient extends Utility_ModelResourceBase
{
	/**
	 * Get the entity table name
	 *
	 */
	public function getEntityTable()
	{
		global $wpdb;
		return "{$wpdb->prefix}pw_ingredients";
	}

	/**
	 * Get the entity id field
	 *
	 */
	public function getEntityIdField()
	{
		return 'ingredient_name';
	}

	/**
	 * Get the updated column for the entity table
	 *
	 */
	public function getStaticFields()
	{
		return array(
			'ingredient_name',
			'catalog_updated'
		);
	}

	/**
	 * Get Ingredient object by name
	 *
	 * @param mixed $ingredientName
	 * @return Model_Entity_Ingredient
	 */
	public function loadByName($ingredientName)
	{
		global $wpdb;
		$condition = $this->getEntityIdField() . '="' . $ingredientName . '"';
		$select = 'SELECT ingredient_id FROM ' . $this->getEntityTable() . ' WHERE ' . $condition;
		$ingredientId = $wpdb->get_var($select);

		$ingredient = new Model_Entity_Ingredient();
		$ingredient->ingredient_id = $ingredientId;
		$ingredient->ingredient_name = $ingredientName;

		return $ingredient;
	}
}
