<?php

/**
 * Memcache  Handler
 *
 * @author Daniel Little <danl@metrex.net>
 */
class Utility_Memcached
{
	/**
	 * @var Memcache
	 */
	public $memcache;

	/**
	 * constructor of the handler - initialises Memcached object
	 *
	 * @return bool
	 */
	public function __construct()
	{
		$serverList = get_option('pw_memcached_servers');
		if (strlen($serverList) <= 0) {
			return false;
		}
		if (class_exists('Memcached')) {
			$this->memcache = new Memcached();
			$servers = explode(';', $serverList);
			foreach ($servers as $server) {
				$parts = explode(':', $server);
				$this->memcache->addServer($parts[0], $parts[1]);
			}
			// error_log('memcached loaded');
		}
		return true;
	}

	/**
	 * closing the session
	 *
	 * @return bool
	 */
	public function end()
	{
		$this->memcache = null;
		return true;
	}

	/**
	 * reading of the memcached data
	 * @param string $key
	 * @return string
	 */
	public function get($key)
	{
		if ($this->memcache) {
			// error_log("GET ATTEMPT: $key");
			$result = $this->memcache->get($key);
			// error_log("GET RESULT:" . var_dump($result));
			// error_log("GET Result Code: " . $this->memcache->getResultCode());
			if ($result) {
				// error_log("FOUND $key in memcached");
				return $result;
			} else {
				// error_log("$key NOT found from memcached");
			}
		} else {
			// error_log("$key was not resolved from memcached");
		}
		return null;
	}

	/**
	 * setting of the memcached data
	 * @param string $key, $data, $expiry
	 * @return string
	 */
	public function set($key, $data, $expiry)
	{
		if ($this->memcache) {
			// error_log("storing $key in memcached, data");
			$this->memcache->set($key, $data, $expiry);
			// error_log("SET Result Code: " . $this->memcache->getResultCode());
			return $data;
		}
		// error_log("$key is not being stored in memcached");
		return null;
	}

	/**
	 * delete entry from memcached data
	 * @param string $key
	 * @return string
	 */
	public function delete($key)
	{
		if ($this->memcache) {
			// error_log("deleting $key from memcached");
			return $this->memcache->delete($key);
		}
		// error_log("$key was not found to delete from memcached");
		return null;
	}

	/**
	 * return memcached stats
	 * @return string
	 */
	public function getStats()
	{
		if ($this->memcache) {
			return $this->memcache->getStats();
		}
	}
}
