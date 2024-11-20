<?php

/**
 * Model_Entity_Package
 */
class Model_Entity_Ingredient extends Utility_ModelEntityBase
{
	public function __construct()
	{
		$this->setIdFieldName('ingredient_name');
		parent::__construct();
	}
}
