<?php

use Illuminate\Database\Eloquent\Collection;
use Modules\Starter\Entities\Configuration;

if (!function_exists('configuration_get')) {

	/**
	 * 获取配置，可能存在同名的多个配置，所以返回一个集合，再根据具体业务决定如何使用
	 * @param $module
	 * @param null $group
	 * @param null $name
	 * @param null $lang
	 * @return Collection
	 */
	function configuration_get($module, $group = null, $name = null, $lang = null): Collection
	{
		if (!$lang) {
			$lang = App::getLocale();
		}

		return Configuration::where('module', $module)->when($group, function ($query, $group) {
			$query->where('group', $group);
		})->when($name, function ($query, $name) {
			$query->where('name', $name);
		})->when($lang, function ($query, $lang) {
			$query->where('lang', $lang);
		})->get();
	}
}

if (!function_exists('configuration_get_first')) {

	/**
	 * 获取单一配置
	 * @param $module
	 * @param $group
	 * @param $name
	 * @param mixed|null $default_value
	 * @param string|null $lang
	 * @return mixed
	 */
	function configuration_get_first($module, $group, $name, mixed $default_value = null, string $lang = null): mixed
	{
		$lang = $lang ?? App::getLocale();
		return Configuration::where('module', $module)->where('group', $group)->where('name', $name)->where('lang', $lang)->value('value') ?? $default_value;
	}
}

if (!function_exists('configuration_save')) {
	/**
	 * @param array $configuration
	 * @return Configuration
	 */
	function configuration_save(array $configuration): Configuration
	{

		if (!isset($configuration['lang']) || !$configuration['lang']) {
			$configuration['lang'] = App::getLocale();
		}

		$exist_configuration = Configuration::where('module', $configuration['module'])->where('group', $configuration['group'])->where('name', $configuration['name'])->where('lang', $configuration['lang'])->first();

		if ($exist_configuration) {
			$exist_configuration->snapshot();
			$exist_configuration->update($configuration);
		} else {
			$exist_configuration = Configuration::create($configuration);
		}

		return $exist_configuration;
	}
}
