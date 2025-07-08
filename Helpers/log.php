<?php

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\ActivityLogger;

if (!function_exists('log_access')) {
	/**
	 * @param string $log 动作
	 * @param Model|null $object 对象
	 * @param Model|int|string|null $causer 用户类型
	 * @param string $event
	 * @return ActivityLogger
	 */
	function log_access(string $log, Model|null $object = null, Model|int|string|null $causer = null, string $event = 'view'): ActivityLogger
	{
		$logger = activity();

		if (!$causer) {
			$causer = auth()->user();
		}

		$logger->by($causer);

		$logger->event($event);

		if ($object) {
			$logger->on($object);
		}

		$logger->log($log);

		return $logger;
	}
}
