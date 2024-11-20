<?php

class Utility_TransferData
{
	// varible of class
	private $_returnedContent = "";
	// send data to
	public function send($data)
	{
		$ch = curl_init(URL_PROVIDER);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, IS_RELEASE_SITE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, IS_RELEASE_SITE);

		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/xml'));
		curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);  // DO NOT RETURN HTTP HEADERS
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);  // RETURN THE CONTENTS OF THE CALL
		$Rec_Data = curl_exec($ch);

		$this->_returnedContent = html_entity_decode($Rec_Data);
		curl_close($ch);
	}
	public function getContent()
	{
		return $this->_returnedContent;
	}
}
