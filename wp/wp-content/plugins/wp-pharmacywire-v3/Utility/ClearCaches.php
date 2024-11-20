<?php

/**
 * Clear caches - support for most popular WP plugins
 * to attempt to clear cache when required, such as after a catalog update
 * @since 3.8.10
 */

class ClearCaches
{

	/**
	 * Initialize
	 * @since 3.8.10
	 */
	public function init()
	{
	}

	/**
	 * clearCaches
	 * @since 3.8.10
	 */
	public static function clear()
	{
		// Cloudflare
		// Cloudflare not likely to work for page cache at this time due to cookies being set on every load, such as phpsessionid
		// Cloudflare does not cache when cookies are set in header
		if (class_exists("\CF\WordPress\Hooks", false) && method_exists('\CF\WordPress\Hooks', 'purgeCacheEverything')) {
			$cloudflareHooks = @new \CF\WordPress\Hooks();
		 	@$cloudflareHooks->purgeCacheEverything();
		}

		// WP Rocket
		if (function_exists('rocket_clean_domain')) {
			@\rocket_clean_domain();
		}

		// WP Super Cache
		if (function_exists('wp_cache_clean_cache')) {
			global $file_prefix;    // Global from WP Super cache, not the best way for a plugin, but thats how it is
			@\wp_cache_clean_cache($file_prefix, true);
		}

		// W3 Total cache
		if (function_exists('w3tc_flush_all')) {
			@\w3tc_flush_all();
		}

		// WP Fastest cache - Not supported yet
		if (false) {
		}

		// Comet Cache
		if (class_exists("\WebSharks\CometCache\Classes\ApiBase", false) && method_exists(\WebSharks\CometCache\Classes\ApiBase::class, "clear")) {
			@\WebSharks\CometCache\Classes\ApiBase::clear();
			@\WebSharks\CometCache\Classes\ApiBase::wipe();
			@\WebSharks\CometCache\Classes\ApiBase::purge();
		}
		// WordPress object cache
		wp_cache_flush();
	}
}
