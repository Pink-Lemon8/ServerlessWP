<?php

/**
 * Model_Request
 **/
class Model_Request_State extends Utility_ModelBase
{
	public function logRequestAttempt($requestType)
	{
		global $wp;
		global $wpdb;
		$remoteIP = UTILITY_COMMON::getClientIP();
		$currentUrl = home_url(add_query_arg(array(), $wp->request));

		$prepare = $wpdb->prepare(
			"INSERT INTO `{$wpdb->prefix}pw_request_state`(remote_ip, type, req_time, init_time, url, attempts, total_attempts, state) 
            VALUES(%s, %s, '" . date("Y-m-d H:i:s", time()) . "', '" . date("Y-m-d H:i:s", time()) .
				"', %s, 1, 1, 'success')
            ON DUPLICATE KEY UPDATE
            req_time = '" . date("Y-m-d H:i:s", time()) . "',
            url = %s,
            attempts = attempts + 1,
            total_attempts = total_attempts + 1,
            state = 'success'",
			array($remoteIP, $requestType, $currentUrl, $currentUrl)
		);

		$results = $wpdb->get_results($prepare);
		return $results;
	}

	public function getRequestStateByIP($requestType, $remoteIP = null)
	{
		global $wpdb;
		if ($remoteIP === null) {
			$remoteIP = UTILITY_COMMON::getClientIP();
		}
		$query = $wpdb->prepare("SELECT * FROM `{$wpdb->prefix}pw_request_state` WHERE remote_ip = %s AND type = %s LIMIT 1", array($remoteIP, $requestType));
		return $wpdb->get_row($query);
	}

	public function clearRequestState($requestType)
	{
		global $wpdb;
		$remoteIP = UTILITY_COMMON::getClientIP();
		$wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}pw_request_state WHERE type = %s AND state != 'banned' AND remote_ip = %s", array($requestType, $remoteIP)));
	}

	public function requestStateCheck($requestType, $args = array())
	{

		// replace default params with sent params
		$args = array_replace(array(
			'noPermaBan' => 0,
			'tempBanLimit' => 10,
			'permaBanLimit' => 30,
			'timePeriod' => "-60 minutes"
		), $args);

		global $wpdb;
		$response = new StdClass();
		$response->status = 'success';
		$response->status_type = 'valid';
		$ipRequestState = $this->getRequestStateByIP($requestType);

		if (!empty($ipRequestState)) {
			if ($ipRequestState->state === 'banned') {
				// banned IP - artificial wait 0.7-1.35s
				$response->status = 'failure';
				$response->status_type  = 'banned';
				usleep(rand(800000, 1450000));
			} elseif (($ipRequestState->total_attempts >= $args['permaBanLimit']) && ($args['noPermaBan'] != 1)) {
				// ban IP from logging in if total attempts over limit
				// no ban option allows for the hourly rate limit, but no perma ban
				$response->status = 'failure';
				$response->status_type = 'banned';
				$wpdb->update($wpdb->prefix . 'pw_request_state', array('state' => 'banned'), array('remote_ip' => $ipRequestState->remote_ip, 'type' => $requestType), array('%s'), array('%s', '%s'));
				$wpdb->update("{$wpdb->prefix}pw_request_state", array('state' => 'banned'), array('remote_ip' => $ipRequestState->remote_ip, 'type' => $requestType), array('%s'), array('%s', '%s'));
			} elseif ((strtotime($ipRequestState->init_time) >= strtotime($args['timePeriod'])) && ($ipRequestState->attempts >= $args['tempBanLimit'])) {
				// temporary 1 hour ban for IP for too many failed attempts
				$response->status = 'failure';
				$response->status_type = 'temp-ban';
				$wpdb->update("{$wpdb->prefix}pw_request_state", array('state' => 'temp-ban'), array('remote_ip' => $ipRequestState->remote_ip, 'type' => $requestType), array('%s'), array('%s', '%s'));
			} elseif ((strtotime($ipRequestState->init_time) < strtotime($args['timePeriod']))) {
				// if it's been over an hour since last attempt reset attempts to 0 and set init_time to now()
				$wpdb->update("{$wpdb->prefix}pw_request_state", array('attempts' => 0, 'init_time' => date("Y-m-d H:i:s", time())), array('remote_ip' => $ipRequestState->remote_ip, 'type' => $requestType), array('%d', '%s'), array('%s', '%s'));
			}
		}
		return $response;
	}
}
