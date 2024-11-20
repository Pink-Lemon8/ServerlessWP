<?php

/**
 * Model_Resource_Drug
 */
class Model_Resource_Drug extends Utility_ModelResourceBase
{
	/**
	 * Get the entity table name
	 *
	 */
	public function getEntityTable()
	{
		global $wpdb;
		return "{$wpdb->prefix}pw_drugs";
	}

	/**
	 * Get the entity id field
	 *
	 */
	public function getEntityIdField()
	{
		return 'drug_id';
	}

	/**
	 * Get the updated column for the entity table
	 *
	 */
	public function getStaticFields()
	{
		return array(
			'drug_id',
			'public_viewable',
			'name',
			'familyname',
			'strengthfreeform',
			'strength',
			'strength_unit',
			'form',
			'ingredient_hash',
			'udn',
			'schedule',
			'manufacturer',
			'generic',
			'comment_external',
			'condition',
			'condition_id',
			'category',
			'prescriptionrequired',
			'catalog_updated',
			'dosage_form',
			'species'
		);
	}


	/**
	 * Delete entity using current object's data
	 *
	 * @param mixed $object
	 */
	public function delete($object = null)
	{
		// delete the package objects
		$resource = new Model_Resource_Package();
		foreach ($object->packages as $package) {
			$resource->delete($package);
		}

		// delete the relationship drug & ingredient dependent
		$resource = new Model_Resource_DrugIngredient();
		$resource->delete($object->getId());

		// delete the attributes associated with this drug
		$attributes = new Model_Resource_Attributes();
		$attributes->delete('drug', $object->getId());

		parent::delete($object);
	}
}
