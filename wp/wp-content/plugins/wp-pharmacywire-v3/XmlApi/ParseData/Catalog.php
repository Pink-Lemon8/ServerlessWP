<?php 
class XmlApi_ParseData_Catalog extends Utility_ParseDataBase
{

	var $element = '';
	var $elementAttrs;
	var $in_drug = false;
	var $in_schedule = false;
	var $in_tierprices = false;
	var $in_tierprice = false;
	var $in_condition = false;
	var $condition = '';
	var $in_species_group = false;
	var $species = array();
	var $arrSchedule = array();
	var $arrPrescriptionrequired = array();
	var $resource;
	var $haveData = false;

	// process data
	function __construct($_xml_content )
	{
		$this->resource = new Model_Resource_Package();
		parent::__construct($_xml_content);
	}

	public function _parseXml(){
		
		return $this->excute();
		
	}

	function excute()
	{
		
		//Initialize the XML parser
		$parser=xml_parser_create();

		//Specify element handler
		xml_set_element_handler($parser,array ( $this, 'startTag' ),array ( $this, 'endTag' ));

		//Specify data handler
		xml_set_character_data_handler($parser,array ( $this, 'tagContent' )) ;

		//Read data
		xml_parse($parser,$this->_xml_content,true);
		//Free the XML parser
		xml_parser_free($parser);
	
		// default is successfully
		$reply = new Model_Entity_Reply();
		$reply->messages = array();
		if ($this->haveData)		
		{			
			$reply->status = 'success';
		}
		else
		{
			// get message
			$messageParser = new XmlApi_ParseData_Message($this->_xml_content);
			$replyMessages = $messageParser->process();
			$reply->messages = null;
			if ($replyMessages instanceof Model_Entity_Reply) {
				$reply->messages = $replyMessages->messages;
			}
		}
		return $reply ;
	}



	//Function to use at the start of an element
	function startTag($parser,$element_name,$element_attrs)
	{
		$this->element = $element_name;
		$this->elementAttrs = $element_attrs;
		//
		switch($element_name)
		{
			case "PWIRE:PACKAGE":
				$this->haveData = true;
				//initialize the array package after update to database
				$this->arrPackage = array();
				$this->arrPackage["is_viewable"] = 1;
				break;

			case "PWIRE:DRUG":		
				//initialize the array drug after update to database
				$this->arrDrug = array();
				//set in_drug = true while parse node drug
				$this->in_drug = true;
				$this->schedule = '';
				break;

			case "MOMEX:ATTRIBUTES":
				//initialize the attributes array
				$this->attributes = array();
				$this->attrKey = NULL;
				break;

			case "PWIRE:INGREDIENT":
				//initialize the array ingredient after update to database
				$this->arrIngre = array();
				break;

			case "PWIRE:SCHEDULE":
				//set in_schedule = true while parse node schedule
				$this->in_schedule = true;
				break;

			case "MOMEX:TIERPRICES":
				//initialize the tier prices array
				$this->in_tierprices = true;
				$this->arrTierPrices = array();
				break;

			case "MOMEX:TIERPRICE":
				//initialize the tier price array
				$this->in_tierprice = true;
				$this->arrTierPrices[] = array('quantity' => '', 'price' => '');
				break;

			case "PWIRE:CONDITION":
				$this->in_condition = true;
				$this->condition = '';
				break;	

			case "PWIRE:SPECIES-GROUP":
				$this->in_species_group = true;
				$this->species = array();
				break;
		}
	}

  function preSaveCleanup() {
	// convert new lines to br's before save
	if (!empty($this->arrDrug["comment_external"])) {
		$this->arrDrug["comment_external"] = nl2br(trim($this->arrDrug["comment_external"]));
	}
	if (!empty($this->arrPackage["comment_external"])) {
		$this->arrPackage["comment_external"] = nl2br(trim($this->arrPackage["comment_external"]));
	}
  }

	//Function to use at the end of an element
	function endTag($parser,$element_name)
	{
		switch($element_name)
		{
			case "PWIRE:PACKAGE":
				// add value package to the drug
				if (!(($this->arrPackage['price'] > 0.00) || preg_match('/^Reference/', $this->arrDrug['schedule']))) {
					$this->arrPackage['public_viewable'] = 0;
				}
				
				$this->arrDrug["package"] = $this->arrPackage;

        $this->preSaveCleanup();
        
        //update to database after parse a note package
				$this->resource->save($this->arrDrug);
				break;

			case "PWIRE:DRUG":
				$this->in_drug = false;
				//check if sche_id is exist
				if(isset($this->arrDrug["sche_id"]) and $this->isNotNullOrNotEmptyString($this->arrDrug["sche_id"]))
				{
					$this->arrDrug["schedule"] = $this->schedule;
					$this->arrSchedule[$this->arrDrug["sche_id"]] = $this->schedule;
				}
				else //check if scheId_Ref is exist
				if(isset($this->arrDrug["scheId_Ref"]) and $this->isNotNullOrNotEmptyString($this->arrDrug["scheId_Ref"]))
				{
					if(isset($this->arrSchedule[$this->arrDrug["scheId_Ref"]]))
					{
						//get schedule of sche_id by scheId_Ref (map in array arrSchedule)
						$this->arrDrug["schedule"] = $this->arrSchedule[$this->arrDrug["scheId_Ref"]];
						//get prescriptionrequired of sche_id by scheId_Ref (map in array arrPrescriptionrequired)
						if(isset($this->arrPrescriptionrequired[$this->arrDrug["scheId_Ref"]]))
						{
							$value = $this->arrPrescriptionrequired[$this->arrDrug["scheId_Ref"]];
							$this->setObjetByValueIsBool($this->arrDrug["prescriptionrequired"],$value);
						}
					}

				}
				break;

			case "MOMEX:ATTRIBUTES":
				// add the attribute to the item being parsed
				if ($this->in_drug) {
					$this->arrDrug["attributes"] = $this->attributes;
				} else {
					$this->arrPackage["attributes"] = $this->attributes;
				}
				break;

			case "PWIRE:INGREDIENT":
				// add value ingredients to the drug
				$this->arrDrug["ingredients"][] = $this->arrIngre;
				break;

			case "PWIRE:SCHEDULE":
				$this->in_schedule = false;
				break;

			case "MOMEX:TIERPRICES":
				// done parsing add tierprices onto the package for save
				$this->arrPackage["tierprices"] = $this->arrTierPrices;
				$this->in_tierprices = false;
				break;

			case "MOMEX:TIERPRICE":
				$this->in_tierprice = false;
				break;

			case "PWIRE:CONDITION":
				$this->in_condition = false;
				if ($this->isNotNullOrNotEmptyString($this->condition)) {
					$this->arrDrug["condition"] = $this->condition;
				} else {
					$this->arrDrug["condition"] = NULL;
				}
				break;

			case "PWIRE:SPECIES-GROUP":
				$this->in_species_group = false;
				if (!empty($this->species)) {
					$this->arrDrug["species"] = implode(';', $this->species);
				} else {
					$this->arrDrug["species"] = NULL;
				}
				break;

		}

	}


	//Function to use when finding character data
	function tagContent($parser,$data)
	{
    // xml_set_character_data_handler splits on special chars (line returns, etc.) and gets called multiple times 
    // save originalData for cases where we need, such as comments concatination
    $originalData = $data;
		$data = trim($data);

		switch($this->element)
		{
			//fill data to Package
			case "PWIRE:PACKAGE":
				//add value package_id to the array package
				$this->setObjectByElementText($this->arrPackage["package_id"],$this->getValueAttribute($this->elementAttrs,'PWIRE:ID'));
				//add value public_viewable to the array package
				$this->setObjectByElementText($this->arrPackage["public_viewable"],$this->getObjetByValueIsBoolDefaultTrue($this->getValueAttribute($this->elementAttrs,'PWIRE:PUBLIC-VIEWABLE')));
				break;

			case "MOMEX:PRODUCT":
				//add value product to the array package
				$this->setObjectByElementText($this->arrPackage["product"],$data);
				break;

			case "MOMEX:PRICE":
				if ($this->in_tierprices && $this->in_tierprice && $this->isNotNullOrNotEmptyString($data)) {
					// insert price into last arrTierPrices index
					end($this->arrTierPrices);
					$this->setObjectByElementText($this->arrTierPrices[key($this->arrTierPrices)]['price'], $data);
				} else {
					//add value price to the array package
					$this->setObjectByElementText($this->arrPackage["price"],$data);
				}
				break;

			case "MOMEX:QUANTITY":
				if ($this->in_tierprices && $this->in_tierprice && $this->isNotNullOrNotEmptyString($data)) {
					// insert quantity into last arrTierPrices index
					end($this->arrTierPrices);
					$this->setObjectByElementText($this->arrTierPrices[key($this->arrTierPrices)]['quantity'], $data);
				}
				break;

			case "MOMEX:MANUFACTURER":
				//add value manufacturer to the array package
				$this->setObjectByElementText($this->arrPackage["manufacturer"],$data);
				break;
					
			case "MOMEX:PACKAGEQUANTITY":
				//add value packagequantity to the array package
				if(strlen(trim($data)) > 0) {
					$this->arrPackage["packagequantity"] = $data . '@' . $this->getValueAttribute($this->elementAttrs,'MOMEX:UNIT');
					$this->arrPackage["sort_value"] = $data;
				}
				break;

			case "MOMEX:UPC":
				//add value upc to the array package
				$this->setObjectByElementText($this->arrPackage["upc"],$data);
				break;

			case "MOMEX:FILLINGVENDORID":
				//add value vendor to the array package
				$this->arrPackage["filling_vendor_id"] = $this->getValueAttribute($this->elementAttrs,'MOMEX:ID');
				break;

			case "MOMEX:VENDOR":
				//add value vendor to the array package
				$value = $this->getElementText($data);
				if ($this->isNotNullOrNotEmptyString($value)) {
					$this->arrPackage["vendor"] = $value . '@' . $this->getValueAttribute($this->elementAttrs,'MOMEX:ID');
					$this->arrPackage["vendor_country_code"] = $this->getValueAttribute($this->elementAttrs,'MOMEX:COUNTRY-CODE');
				}
				break;

			case "PWIRE:MINITEMQTY":
				//add value minitempty to the array package
				$this->setObjectByElementText($this->arrPackage["minitemqty"],$data);
				break;

			case "PWIRE:MAXITEMQTY":
				//add value maxitemqty to the array package
				$this->setObjectByElementText($this->arrPackage["maxitemqty"],$data);
				break;

			case "PWIRE:MULTIPLEITEMFACTOR":
				//add value multipleitemfactor to the array package
				$this->setObjectByElementText($this->arrPackage["multipleitemfactor"],$data);
				break;

			case "PWIRE:FEATURE":
				//add value feature to the array package
				$this->setObjectByElementText($this->arrPackage["feature"],$data);
				break;

			case "MOMEX:CREATED":
				//add value created to the array package
				$this->setObjectByElementText($this->arrPackage["created"],$data);
				break;

			case "MOMEX:UPDATED":
				//add value updated to the array package
				$this->setObjectByElementText($this->arrPackage["updated"],$data);
				break;

			case "PWIRE:ORIGIN-COUNTRY-CODE":
				//add value origin_country_code to the array package
				$this->setObjectByElementText($this->arrPackage["origin_country_code"],$data);
				break;

			case "PWIRE:CATEGORY":
				//add value category to the array drug
				if($this->in_drug)
				{
					$attrValue = trim($this->getValueAttribute($this->elementAttrs,'PWIRE:ID'));
					if(strlen(trim($data) . $attrValue)>0)
					$this->arrDrug["category"] = $data . '@' . $attrValue;

				}
				else //add value category to the array package
				$this->setObjectByElementText($this->arrPackage["category"],$data);
				break;

			case "MOMEX:PACKAGINGFREEFORM":
				//add value packagingfreeform to the array package
				$this->setObjectByElementText($this->arrPackage["packagingfreeform"],$data);
				break;

			case "MOMEX:COMMENT":
				$commentType = $this->getValueAttribute($this->elementAttrs,'MOMEX:TYPE');
				
				if($this->in_drug) {
          			//add value comment_external to the array drug
					if ($commentType=='external') {
						$this->setObjectByElementText($this->arrDrug["comment_external"], $this->arrDrug["comment_external"] . $originalData);
          			} 
				} else {
          			//add value comment_external to the array package
					if ($commentType=='external') {
						$this->setObjectByElementText($this->arrPackage["comment_external"], $this->arrPackage["comment_external"] . $originalData);
          			} 
				}
				break;

				//fill data to Drug
			case "PWIRE:DRUG":
				//add value drug_id to the array drug
				$this->setObjectByElementText($this->arrDrug["drug_id"],$this->getValueAttribute($this->elementAttrs,'PWIRE:ID'));
				//add value drugid_ref to the array drug
				$this->setObjectByElementText($this->arrDrug["drugId_Ref"], $this->getValueAttribute($this->elementAttrs,'PWIRE:REF'));
				// boolean
				//add value public_viewable to the array drug
				$this->setObjectByElementText($this->arrDrug["public_viewable"],$this->getObjetByValueIsBoolDefaultTrue($this->getValueAttribute($this->elementAttrs,'PWIRE:PUBLIC-VIEWABLE')));
				break;

			case "PWIRE:NAME":
				if($this->in_schedule)
				{
					$value = $this->getElementText($data);
					if($this->isNotNullOrNotEmptyString($value))
					$this->schedule = $this->schedule . $value . '@';
				}
				else //add value name to the array drug
				$this->setObjectByElementText($this->arrDrug["name"],$data);
				break;

			case "PWIRE:DRUG_FAMILY_NAME":
				$this->setObjectByElementText($this->arrDrug["familyname"],$data);
				break;

			case "PWIRE:STRENGTHFREEFORM":
				//add value strengthfreeform to the array drug
				$this->setObjectByElementText($this->arrDrug["strengthfreeform"],$data);
				break;

			case "PWIRE:STRENGTH":
				//add value strength and strength_unit to the array drug
				if($this->isNotNullOrNotEmptyString($data))
				{
					$this->arrDrug["strength"] = $data;
					$this->arrDrug["strength_unit"] = $this->getValueAttribute($this->elementAttrs,'PWIRE:UNIT');
				}
				break;

			case "PWIRE:FORM":
				//add value form to the array drug
				$this->setObjectByElementText($this->arrDrug["form"],$data);
				break;

			case "PWIRE:INGREDIENT-HASH":
				//add value ingredient_hash to the array drug
				$this->setObjectByElementText($this->arrDrug["ingredient_hash"],$data);
				break;

			case "PWIRE:UDN":
				//add value und to the array drug
				if(strlen(trim($data)) >0)
				$this->arrDrug["udn"] = $data . '@' . $this->getValueAttribute($this->elementAttrs,'PWIRE:UDN-TYPE');
				break;

			case "PWIRE:MANUFACTURER":
				//add value manufacturer to the array drug
				$this->setObjectByElementText($this->arrDrug["manufacturer"],$data);
				break;
				// boolean
			case "PWIRE:GENERIC":
				//add value generic to the array drug
				if(strlen(trim($data)) >0)
				$this->setObjetByValueIsBool($this->arrDrug["generic"],$data);

				break;

			case "MOMEX:ATTRIBUTE":
				$this->attrKey = $this->getValueAttribute($this->elementAttrs,'MOMEX:NAME');
				$this->attrType = $this->getValueAttribute($this->elementAttrs,'MOMEX:TYPE');
				$this->attributes[$this->attrKey] = array();
				break;

			case "MOMEX:VALUE":
				if (strlen($data) && $this->attrKey) {
					if ($this->attrType === 'boolean') {
						$this->attributes[$this->attrKey][] = $this->getObjetByValueIsBoolDefaultTrue($data);
					} else {
						$is_null = $this->getValueAttribute($this->elementAttrs,'IS_NULL');
						if ($is_null === 'true') {
							$this->attributes[$this->attrKey][] = NULL;
						} else {
							$this->attributes[$this->attrKey][] = $this->getElementText($data);
						}
					}
				}
				break;

			case "PWIRE:CONDITION":
				//add value condition and condition_id to the array drug
				$value = $this->getElementText($data);
				if($this->in_condition && $this->isNotNullOrNotEmptyString($value)) {
					$this->condition .= $value;
				}
				$this->arrDrug["condition_id"] = $this->getValueAttribute($this->elementAttrs,'PWIRE:ID');
				break;
					
				//fill data to schedule
			case "PWIRE:SCHEDULE":
				//add value sche_id and scheid_ref to the array drug
				$this->setObjectByElementText($this->arrDrug["sche_id"],$this->getValueAttribute($this->elementAttrs,'PWIRE:ID'));
				$this->setObjectByElementText($this->arrDrug["scheId_Ref"], $this->getValueAttribute($this->elementAttrs,'PWIRE:REF'));

				break;

			case "PWIRE:COUNTRY":
				//add value country to the string schedule
				$value = $this->getElementText($data);
				if ($this->isNotNullOrNotEmptyString($value)) {
					$this->schedule = $this->schedule . $value . '@';
				}
				break;
				// boolean
			case "PWIRE:EXPORTABLE":
				//add value exportable to the string schedule
				$value = $this->getElementText($data);
				if ($this->isNotNullOrNotEmptyString($value)) {
					$this->schedule = $this->schedule . $value . '@';
				}
				break;
				// boolean
			case "PWIRE:PRESCRIPTIONREQUIRED":

				if(strlen(trim($data)) >0)
				{
					$this->schedule = $this->schedule . $data;
					$this->setObjetByValueIsBool($this->arrDrug["prescriptionrequired"],$data);
					$this->arrPrescriptionrequired[$this->arrDrug["sche_id"]] = $data;
				}

				break;

				//fill data to Ingredien
			case "PWIRE:INGREDIENT":
				//add value ingredient_name to the a ingredient
				$this->setObjectByElementText($this->arrIngre["ingredient_name"],$data);
				$this->setObjectByElementText($this->arrIngre["ingredient_display_order"], $this->getValueAttribute($this->elementAttrs,'PWIRE:DISPLAY-ORDER'));
				break;

			case "PWIRE:DOSAGEFORM":
				$this->setObjectByElementText($this->arrDrug["dosage_form"],$data);
				break;

			case "PWIRE:SPECIES":
				$species = $this->getElementText($data);
				if($this->in_species_group && $this->isNotNullOrNotEmptyString($species)) {
					array_push($this->species, $species);
				}
				break;
		}
	}
	function getValueAttribute($arrAttrs,$key)
	{
		$result = '';
		if(isset($arrAttrs) and isset($arrAttrs[$key]))
		$result = $arrAttrs[$key];
		return $result;
	}
	function isNotNullOrNotEmptyString($question){
		return !(!isset($question) || trim($question)==='' || is_null($question));
	}
	function setObjetByValueIsBool(&$obj,$data)
	{
		if(strtolower(trim($data))=="true")
		$data = 1;
		else
		$data = 0;
		$obj = $data;
	}
	function getObjetByValueIsBoolDefaultTrue($data)
	{
		if(strtolower(trim($data))=="false")
		$data = 0;
		else
		$data = 1;
		return $data;
	}
	function getObjetByValueIsBool($data)
	{
		if(strtolower(trim($data))=="true")
		$data = 1;
		else
		$data = 0;
		return $data;
	}
	function setObjectByElementText(&$obj,$data)
	{
		$result = $this->getElementText($data);
		if($this->isNotNullOrNotEmptyString($result))
		$obj = $result;
	}
	function getElementText($data)
	{
		$pattern = '<!\[CDATA\[(.*)\]\]>';
		$pattern2 = '<!--\[CDATA\[(.*)\]\]-->';
		$result = $data;
		if(strpos($data,'CDATA') >-1) {
			if(preg_match($pattern, $data, $match)) {
				$result = $match[1];
			} else {
				if(preg_match($pattern2, $data, $match)) {
					$result = $match[1];
				}
			}
		}

		return $result;
	}
	function trimCharFirstLast($value,$char)
	{
		return ltrim(rtrim($value, $char), $char);
	}
}
