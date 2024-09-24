<?php

use Illuminate\Database\Eloquent\Model;

if (!function_exists('log_access')) {
	/**
	 * @param string $log 动作
	 * @param Model|null $object 对象
	 * @param Model|int|string|null $causer 用户类型
	 * @return void
	 */
	function log_access(string $log, Model|null $object = null, Model|int|string|null $causer = null): void
	{
		$logger = activity();

		if (!$causer) {
			$causer = auth()->user();
		}

		$logger->by($causer);

		if ($object) {
			$logger->on($object);
		}

		$logger->log($log);
	}
}
