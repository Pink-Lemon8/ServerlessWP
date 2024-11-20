<?php

define('ALIAS_TR', 		getNSAliasTransaction(1));
define('ALIAS_MOMEX', 	getNSAliasMomex(1));
define('ALIAS_MT', 		getNSAliasTerms(1));
define('ALIAS_PW', 		getNSAliasPWire(1));
define('ALIAS_PW5', 	getNSAliasPWireV5(1));
// Examples:
// method requirements: beginTransaction($local, $type, $username, $password)
// $transaction = beginTransaction('true','GetManufacturers', 'xmlconnect_1', '6ns%qtW%NTR%VYX');
// method requirements: prepareNode($xml, $key, $value = null, $attributes = null)
// $patients = prepareNode($transaction, 'pw:patient', null, array('momex:id' => '8'));

class SimpleXMLExtended extends SimpleXMLElement {
  public function addCData($cdata_text) {
    $node = dom_import_simplexml($this); 
    $no   = $node->ownerDocument; 
	$node->appendChild($no->createCDATASection($cdata_text)); 
  } 
  
  public function prepareNode($key, $value = null, $attributes = null, $cdata = null){
	$xml = $this;
	// caution, duplicate prepareNode, changes here should also be applied to original below
  	$aliasNamespace = getNamespace($key); #ex. getNamespace(momex:authenticate)
  	// $aliasNamespace: http://www.pharmacywire.com/
	if (isset($cdata)) {
		1;
	} else {
		if(isset($value)) {
			$illegal = "<>";
			$cdata = (false !== strpbrk($value, $illegal));
		}
	}
	
  	//ex. $authenticate  = $transaction->addChild('momex:authenticate', null, $momex_ns);
  	if ($cdata && isset($value)) {
		  $node = $xml->addChild($key, '', $aliasNamespace);
		  $node->addCData($value);
  	} else {
  		$node = $xml->addChild($key, $value, $aliasNamespace);
  	}
	
  	if (is_array($attributes) || is_object($attributes)) {
  		foreach ($attributes as $key => $value){
  			$aliasNamespace = getNamespace($key);
  			$node->addAttribute($key, $value, $aliasNamespace);
  		}
  	}
  	return $node;
  }
}

function beginTransaction($local, $type, $username, $password, $attributes = null) {
	$Aliases = getNamespaceAlias();
	$string = '';
	foreach ($Aliases as $key => $value){
		if ($key === 'default'){//} || $key === 'tr'){
			$key = '';
			$xmlns = 'xmlns';
		} else {$xmlns = 'xmlns:';}
		$string .= $xmlns . $key . "=\"" . $value . "\" ";
		
	}
	$tr = ALIAS_TR;
	$momex = ALIAS_MOMEX;
	$attribute = ' ';
	if (is_array($attributes) || is_object($attributes)) {
		foreach ($attributes as $key => $value){
			$attribute = $attribute . $key . "='" . $value . "' ";
		}
	}
	$string = "<$tr:transaction " . $string . " $tr:local='$local'" . " $tr:type='$type'" . " $tr:flush-output='true'" . $attribute . "/>";
	$node = simplexml_load_string($string, 'SimpleXMLExtended');
	prepareNode($node, "$momex:authenticate", null, array("$momex:username" => $username, "$momex:password" => $password));
	return $node;
}

function getNamespace($key) { // ex. 'momex:authenticate' or 'patient'
	$Aliases = getNamespaceAlias();
	# explode array, use regex to find value before colon
	$parts = preg_split('/:/', $key);

	if (count($parts) != 2) {
		die("no namespace specified \n");
	}

	# see if $parts matches a key in the $NameSpaces array
	if (array_key_exists($parts[0], $Aliases)){
		return $Aliases[$parts[0]];
	}
	die($key.": does not use a valid nameSpace\n");
}

function prepareNode($xml, $key, $value = null, $attributes = null, $cdata = false) {
	//  caution, duplicate prepareNode, changes here should also be applied to original above
	$aliasNamespace = getNamespace($key); #ex. getNamespace(momex:authenticate)
	// $aliasNamespace: http://www.pharmacywire.com/
	
	//ex. $authenticate  = $transaction->addChild('momex:authenticate', null, $momex_ns);
	if ($cdata) {
		$node = $xml->addChild($key, '', $aliasNamespace);
		$node->addCData($value);
	} else {
		$node = $xml->addChild($key, $value, $aliasNamespace);
	}
	
	
	if (is_array($attributes) || is_object($attributes)) {
		foreach ($attributes as $key => $value){
			$aliasNamespace = getNamespace($key);
			$node->addAttribute($key, $value, $aliasNamespace);
		}
	}
	return $node;
}

function getNSAliasTransaction($alias) {
	$nameSpace = 'http://www.metrex.net/momex/transaction#';
	if (!$alias) {
		return $nameSpace;
	}
	return getNamespaces()[$nameSpace];
}

function getNSAliasMomex($alias) {
	$nameSpace = 'http://www.metrex.net/momex#';
	if (!$alias) {
		return $nameSpace;
	}
	return getNamespaces()[$nameSpace];
}

function getNSAliasTerms($alias) {
	$nameSpace = 'http://www.metrex.net/momex/terms#';
	if (!$alias) {
		return $nameSpace;
	}
	return getNamespaces()[$nameSpace];
}

function getNSAliasPWire($alias) {
	$nameSpace = 'http://www.pharmacywire.com/';
	if (!$alias) {
		return $nameSpace;
	}
	return getNamespaces()[$nameSpace];
}

function getNSAliasPWireV5($alias) {
	$nameSpace = 'http://www.pharmacywire.com/v5';
	if (!$alias) {
		return $nameSpace;
	}
	return getNamespaces()[$nameSpace];
}


function getNamespaces () {
	return array (
		'http://www.metrex.net/momex/transaction#' => 'tr',
		'http://www.metrex.net/momex#' => 'momex',
		'http://www.metrex.net/momex/terms#' => 'mt',
		'http://www.pharmacywire.com/' => 'pw',
		'http://www.pharmacywire.com/v5' => 'pwire5',
	);
}

function getNamespaceAlias () {
	$Namespaces = getNamespaces();
	$Aliases = array();
	foreach ($Namespaces as $key => $value) {
		$Aliases[$value] = $key;
	}
	return $Aliases;
}

function SendTransaction($XML, $URL) {
	//setting the curl parameters.
	$url = $URL;
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/xml'));
	curl_setopt($ch, CURLOPT_USERAGENT, "PhmaracyWire XML Client");
	curl_setopt($ch, CURLOPT_POSTFIELDS, $XML);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ch, CURLOPT_HEADER, 0);  // DO NOT RETURN HTTP HEADERS
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);  // RETURN THE CONTENTS OF THE CALL
	$response = curl_exec($ch);
	$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	if ($httpcode != 200) {
		return null;
	}
	return $response;
}
