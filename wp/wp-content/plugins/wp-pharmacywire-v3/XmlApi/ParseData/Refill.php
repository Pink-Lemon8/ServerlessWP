<?php

/**
 * XmlApi_ParseData_Refill
 */
class XmlApi_ParseData_Refill extends Utility_ParseDataBase
{
	/**
	 * Constructor
	 *
	 * @param mixed $xml_content
	 * @return XmlApi_ParseData_Refill
	 */
	public function __construct($xml_content)
	{
		parent::__construct($xml_content);
	}

	/**
	 * Override the base function
	 *
	 */
	public function _parseXml()
	{
		parent::_parseXml();
		$reply = new Model_Entity_Reply();
		$reply->status = (string)$this->_xml_doc->status;
		$reply->type = (string)$this->_xml_doc->type;

		$nodePrescriptions = $this->_getChildByName($this->_xml_doc, 'prescriptions', XML_PWIRE);
		$nodePrescriptionList = $this->_getChildrenByName($nodePrescriptions, 'prescription', XML_PWIRE);

		$prescriptions = array();
		foreach ($nodePrescriptionList as $nodePrescription) {
			$prescriptionEntity = new stdClass();

			$attrs = $nodePrescription->attributes(XML_NS_MOMEX);
			$prescriptionEntity->id = (string)$attrs['id'];
			$prescriptionEntity->rxNumber = (string)$this->_getChildByName($nodePrescription, 'rxnumber', XML_PWIRE);


			$nodeDrug = $this->_getChildByName($nodePrescription, 'drug', XML_PWIRE);
			if (!is_null($nodeDrug)) {
				$prescriptionEntity->drug = new stdClass();
				$attrs = $nodeDrug->attributes(XML_NS_PWIRE);
				$prescriptionEntity->drug->din = (string)$attrs['din'];
				$attrs = $nodeDrug->attributes(XML_NS_PWIRE);
				$prescriptionEntity->drug->drug_id = (string)$attrs['id'];
				$prescriptionEntity->drug->name = (string)$this->_getChildByName($nodeDrug, 'name', XML_PWIRE);
				$prescriptionEntity->drug->strengthfreeform = (string)$this->_getChildByName($nodeDrug, 'strengthfreeform', XML_PWIRE);
				$nodeStrength = $this->_getChildByName($nodeDrug, 'strength', XML_PWIRE);
				if (isset($nodeStrength)) {
					$prescriptionEntity->drug->strength = (string)$nodeStrength;
					$attrs = $nodeStrength->attributes(XML_NS_PWIRE);
					$prescriptionEntity->drug->strength_unit = (string)$attrs['unit'];
				}

				$node_generic = $this->_getChildByName($nodeDrug, 'generic', XML_PWIRE);
				if (isset($node_generic)) {
					if (strtolower((string)$node_generic) === 'true') {
						$prescriptionEntity->drug->generic = true;
					}
					if (strtolower((string)$node_generic) === 'false') {
						$prescriptionEntity->drug->generic = false;
					}
				}

				$node_ingredient_hash = $this->_getChildByName($nodeDrug, 'ingredient-hash', XML_PWIRE);
				if (isset($node_ingredient_hash)) {
					$prescriptionEntity->drug->ingredient_hash = (string)$node_ingredient_hash;
				}

				$nodeIngredientList = $this->_getChildrenByName($nodeDrug, 'ingredient', XML_PWIRE);
				$ingredients = array();
				foreach ($nodeIngredientList as $nodeIngredient) {
					$ingredient = new Model_Entity_Ingredient();
					$ingredient->ingredient_id = -1;
					$ingredient->ingredient_name = (string)$nodeIngredient;

					$ingredients[] = $ingredient;
				}
				$prescriptionEntity->drug->ingredient = $ingredients;

				$drugForms = $this->_getChildByName($nodePrescription, 'drug-forms', XML_PWIRE);
				if (!is_null($drugForms)) {
					$drugFormList = $this->_getChildrenByName($drugForms, 'drug-form', XML_PWIRE);
					foreach ($drugFormList as $drugForm) {
						if (!is_null($prescriptionEntity->drug->form)) {
							$prescriptionEntity->drug->form = $prescriptionEntity->drug->form . ", ";
						}
						$prescriptionEntity->drug->form = $prescriptionEntity->drug->form . (string)$drugForm;
					}
				}

				$nodeSchedule = $this->_getChildByName($nodeDrug, 'schedule', XML_PWIRE);
				if (isset($nodeSchedule)) {
					# _rxdruginfo tells other componets using this data that it contains the complete drug card info
					# submitted with the refill info.
					# April 17, 2012 - Remi
					$prescriptionEntity->drug->_rxdruginfo = true;
					# I used schedule to mark this flag, but could have been any of the others.
					# This can be removed once we are confident PharmacyWire - Refill module has been updated everywhere.
					#

					$scheduleName = (string)$this->_getChildByName($nodeSchedule, 'name', XML_PWIRE);
					$scheduleCountry = (string)$this->_getChildByName($nodeSchedule, 'country', XML_PWIRE);
					$scheduleExportable = (string)$this->_getChildByName($nodeSchedule, 'exportable', XML_PWIRE);
					$scheduleRxRequired = (string)$this->_getChildByName($nodeSchedule, 'prescriptionrequired', XML_PWIRE);

					$scheduleArr = array($scheduleName, $scheduleCountry, $scheduleExportable, $scheduleRxRequired);
					$prescriptionEntity->drug->schedule = implode(XML_JOIN_SYMBOL, $scheduleArr);
				}

				$nodeUDN = $this->_getChildByName($nodeDrug, 'udn', XML_PWIRE);
				if (isset($nodeUDN)) {
					$udnText = (string)$nodeUDN;
					$udnAttrs = $nodeUDN->attributes(XML_NS_PWIRE);
					$udnType = (string)$udnAttrs['udn-type'];

					$udnArr = array($udnText, $udnType);
					$prescriptionEntity->drug->udn = implode(XML_JOIN_SYMBOL, $udnArr);
				}

				$node_manufacturer = $this->_getChildByName($nodeDrug, 'manufacturer', XML_PWIRE);
				if (isset($node_manufacturer)) {
					$prescriptionEntity->drug->manufacturer = (string)$node_manufacturer;
				}

				$node_comment_list = $this->_getChildrenByName($nodeDrug, 'comment', XML_MOMEX);
				foreach ($node_comment_list as $nodeComment) {
					$comment_attr = $nodeComment->attributes(XML_NS_MOMEX);
					if ((string)$comment_attr['type'] === 'external') {
						$prescriptionEntity->drug->comment_external = (string)$nodeComment;
					}
				}

				$node_condition = $this->_getChildByName($nodeDrug, 'condition', XML_PWIRE);
				if (isset($node_condition)) {
					$prescriptionEntity->drug->condition = (string)$node_condition;
					$node_conditionArr = $node_condition->attributes(XML_NS_PWIRE);
					$prescriptionEntity->drug->condition_id = (string)$node_conditionArr['id'];
				}

				$node_category = $this->_getChildByName($nodeDrug, 'category', XML_PWIRE);
				if (isset($node_category)) {
					$node_categoryArr = $node_category->attributes(XML_NS_PWIRE);
					$categoryArr = array((string)$node_category, (string)$node_categoryArr['id']);
					$prescriptionEntity->drug->category = implode(XML_JOIN_SYMBOL, $categoryArr);
				}

				$packages = $this->_getChildByName($nodePrescription, 'packages', XML_PWIRE);
				if (!is_null($packages)) {
					$packageList = $this->_getChildrenByName($packages, 'package', XML_PWIRE);
					$packageItems = array();
					foreach ($packageList as $package) {
						$packageAttrs = $package->attributes(XML_NS_MOMEX);
						$packageID = (string)$packageAttrs['id'];
						$packageItems["DP-$packageID"] = $packageID;
					}
					$prescriptionEntity->drug->packages = $packageItems;
				}
			}

			$prescriptionEntity->fill = new stdClass();
			$fillNode = $this->_getChildByName($nodePrescription, 'fill', XML_PWIRE);
			$fillAttrs = $fillNode->attributes(XML_NS_PWIRE);
			$prescriptionEntity->fill->type = (string)$fillAttrs['type'];
			$prescriptionEntity->fill->frequency = (string)$fillAttrs['frequency'];
			$prescriptionEntity->fill->dispensed = (string)$fillNode;
			$remainingNode = $this->_getChildByName($nodePrescription, 'remaining', XML_PWIRE);
			$fillAttrs = $remainingNode->attributes(XML_NS_PWIRE);
			$prescriptionEntity->fill->expiry = (string)$fillAttrs['expirydate'];
			$prescriptionEntity->fill->remaining = (string)$remainingNode;

			$nodeInstructions = $this->_getChildByName($nodePrescription, 'instructions', XML_PWIRE);
			if (!is_null($nodeInstructions)) {
				$nodeInstructionList = $this->_getChildrenByName($nodeInstructions, 'instruction', XML_PWIRE);
				foreach ($nodeInstructionList as $nodeInstruction) {
					$instruction = (string)$nodeInstruction;
					$attrs = $nodeInstruction->attributes(XML_NS_PWIRE);
					$type = (string)$attrs['type'];
					if ($type == 'long') {
						$prescriptionEntity->instructions = $instruction;
					}
				}
			}

			$prescriptions[] = $prescriptionEntity;
		}
		$reply->prescriptions = $prescriptions;

		// get message
		$messageParser = new XmlApi_ParseData_Message($this->_xml_content);
		$replyMessages = $messageParser->process();
		$reply->messages = null;
		if ($replyMessages instanceof Model_Entity_Reply) {
			$reply->messages = $replyMessages->messages;
		}

		// return parsed data
		return $reply;
	}
}
