<?php

/**
 * Utility_Configuration
 */
class Utility_Configuration
{
	// This function is to make sure that the configuration and the catalog are regularly scheduled for updates if necessary
	public function setupSchedule($override = false)
	{
		if (SCHEDULE_TIME_ENABLED === 'on') {
			date_default_timezone_set(get_option('timezone_string'));
			if ($override) {
				$scheduleTimes = explode(':', $override);
			} else {
				$scheduleTimes = explode(':', SCHEDULE_TIME_REFRESH);
			}
			$scheduleTime = mktime($scheduleTimes[0], $scheduleTimes[1], 59); // + (60*60*24);

			if (date('H:i', wp_next_scheduled('buildcache_event')) !== date('H:i', $scheduleTime)) {
				wp_clear_scheduled_hook('buildcache_event');
				wp_schedule_event($scheduleTime, 'daily', 'buildcache_event');
			}
		} else {
			wp_clear_scheduled_hook('buildcache_event');
		}
	}
}
