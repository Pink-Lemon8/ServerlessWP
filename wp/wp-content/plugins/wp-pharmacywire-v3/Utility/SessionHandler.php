<?php

/**
 * Memcache & MySQL PHP Session Handler
 *
 * Modified from:
 * @author Jakub Matějka <jakub@keboola.com>
 * @see http://pureform.wordpress.com/2009/04/08/memcache-mysql-php-session-handler/
 */
class Utility_SessionHandler
{
	/**
	 * @var int
	 */
	public $lifeTime;

	/**
	 * @var Memcached
	 */
	public $memcache;

	/**
	 * @var string
	 */
	public $initSessionData;

	/**
	 * interval for session expiration update in the DB
	 * @var int
	 */
	private $_refreshTime = 300; //5 minutes

	/**
	 * constructor of the handler - initialises Memcached object
	 *
	 * @return bool
	 */
	public function __construct()
	{
		#this ensures to write down and close the session when destroying the handler object
		register_shutdown_function("session_write_close");

		$this->memcache = new Utility_Memcached();
		$this->lifeTime = intval(ini_get("session.gc_maxlifetime"));
		$this->initSessionData = null;

		return true;
	}

	/**
	 * opening of the session - mandatory arguments won't be needed
	 * we'll get the session id and load session data, it the session exists
	 *
	 * @param string $savePath
	 * @param string $sessionName
	 * @return bool
	 */
	public function open($savePath, $sessionName)
	{
		$sessionId = session_id();
		if ($sessionId !== "") {
			$this->initSessionData = $this->read($sessionId);
		}

		return true;
	}

	/**
	 * closing the session
	 *
	 * @return bool
	 */
	public function close()
	{
		$this->lifeTime = null;
		$this->memcache = null;
		$this->initSessionData = null;

		return true;
	}

	/**
	 * reading of the session data
	 * if the data couldn't be found in the Memcache, we try to load it from the DB
	 * we have to update the time of data expiration in the db using _updateDbExpiration()
	 * the life time in Memcache is updated automatically by write operation
	 *
	 * @param string $sessionId
	 * @return string
	 */
	public function read($sessionId)
	{
		global $wpdb;
		$now = time(); // unix timestamp
		// SessionHandler::read php7+ must return '' if no value read
		$data = $this->memcache->get($_SERVER['SERVER_NAME'] . $sessionId) ?? '';
		
		if (!empty($data)) {
			#entry found in memcache
			#get memcache session expiration
			$expiration = $this->memcache->get('db-expiration-' . $_SERVER['SERVER_NAME'] . $sessionId);
		} else {
			#the record could not be found in the Memcache, loading from the db
			$r = $wpdb->get_results($wpdb->prepare("SELECT expiration, data FROM {$wpdb->prefix}pw_sessions WHERE sessionId='%s'", $sessionId));
			$data = $r[0]->data ?? '';
			$expiration = $r[0]->expiration ?? null;
		}

		if (empty($expiration)) {
			#no expiration set yet, make sure to set one
			$this->_updateDbExpiration($sessionId, $now, $data);
		} else if ($expiration > $now) {
			#valid future expiration
			#if we didn't write into the db for at least $this->_refreshTime (5 minutes),
			#we need to refresh the expiration time in the db
			if ($now - $this->_refreshTime > $expiration - $this->lifeTime) {
				$this->_updateDbExpiration($sessionId, $now, $data);
			}
		} else {
			#exired, empty data found & destroy expired session
			$data = '';
			$this->destroy($sessionId);
		}

		$this->memcache->set($_SERVER['SERVER_NAME'] . $sessionId, $data, $this->lifeTime);
		
		return $data ?? '';
	}

	/**
	 * update of the expiration time of the db record
	 *
	 * @param string $sessionId
	 * @param int $now UNIX timestamp
	 */
	private function _updateDbExpiration($sessionId, $now = null, $data = null)
	{
		global $wpdb;

		if (!$now) {
			$now = time();
		}
		$expiration = $this->lifeTime + $now;
		// Changed to ensure entry exists in db, as we will now have a separate garbage cleanup to flush the db regularly (see: manual_db_gc() method below)
		$wpdb->query($wpdb->prepare("INSERT IGNORE INTO {$wpdb->prefix}pw_sessions (sessionId, expiration, data) VALUES(%s, %s, %s)", $sessionId, $expiration, $data));
		$wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}pw_sessions SET expiration = %s WHERE sessionId = %s", $expiration, $sessionId));
		#we store the time of the new expiration into the Memcache separately
		$this->memcache->set('db-expiration-' . $_SERVER['SERVER_NAME'] . $sessionId, $expiration, $this->lifeTime);
	}

	/**
	 * cache write - this is called when the script is about to finish, or when session_write_close() is called
	 * data are written only when something has changed
	 *
	 * @param string $sessionId
	 * @param string $data
	 * @return bool
	 */
	public function write($sessionId, $data)
	{
		global $wpdb;
		$now = time();
		$expiration = $this->lifeTime + $now;
		
		#we store time of the db record expiration in the Memcache
		$this->memcache->set($_SERVER['SERVER_NAME'] . $sessionId, $data, $this->lifeTime);

		if ($this->initSessionData !== $data) {
			$wpdb->query($wpdb->prepare("INSERT IGNORE INTO {$wpdb->prefix}pw_sessions (sessionId, expiration, data) VALUES('%s', %s, '%s')", $sessionId, $expiration, $data));		
			$wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}pw_sessions SET data = '%s', expiration = %d WHERE sessionId = '%s'", $data, $expiration, $sessionId));
			$this->memcache->set('db-expiration-' . $_SERVER['SERVER_NAME'] . $sessionId, $expiration, $this->lifeTime);
		}

		return true;
	}

	/**
	 * destroy of the session - both DB & Memcache
	 *
	 * @param string $sessionId
	 * @return bool
	 */
	public function destroy($sessionId)
	{
		$this->deleteMemcacheSessionById($sessionId);
		$this->deleteDbSessionById($sessionId);
		return true;
	}

	/**
	 * destroy memcache session by ID
	 *
	 * @param string $sessionId
	 * @return bool
	 */
	public function deleteMemcacheSessionById($sessionId)
	{
		// This probably does not need to be here, as memcache should clear itself
		// leaving for now as it does not look like the read logic actually checks
		// the expiry either
		$this->memcache->delete($_SERVER['SERVER_NAME'] . $sessionId);
		$this->memcache->delete('db-expiration-' . $_SERVER['SERVER_NAME'] . $sessionId);
		return true;
	}

	/**
	 * destroy database session by ID
	 *
	 * @param string $sessionId
	 * @return bool
	 */
	public function deleteDbSessionById($sessionId) {
		global $wpdb;
		$wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}pw_sessions WHERE sessionId=%s", $sessionId));
		return true;
	}

	/**
	 * called by the garbage collector
	 *
	 * @param int $maxlifetime
	 * @return bool
	 */
	public function gc($maxlifetime)
	{
		global $wpdb;
		$r = $wpdb->get_results("SELECT sessionId FROM {$wpdb->prefix}pw_sessions WHERE expiration < UNIX_TIMESTAMP() LIMIT 10000", ARRAY_A);
		if (is_array($r) && !empty($r)) {
			for ($i = 0; $i < count($r); $i++) {
				$this->deleteMemcacheSessionById($r[$i]['sessionId']);
			}
			$wpdb->query("DELETE FROM {$wpdb->prefix}pw_sessions WHERE expiration < UNIX_TIMESTAMP() LIMIT 10000");
		}
		
		return true;
	}

	/**
	 * Force a manual garbage collection of the database
	 * This is an alternate gc to the standard session gc above
	 * As when servers have php ini gc_probability set to 0, the sessions won't expire automatically
	 * Leading to the session table never propertly getting cleaned up
	 * 
	 * This method is more efficient than the above gc as it strictly takes care of the database
	 */
	public static function manual_db_gc() {
		global $wpdb;
		$wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}pw_sessions WHERE expiration < UNIX_TIMESTAMP() LIMIT 150000"));
		return true;
	}
}
