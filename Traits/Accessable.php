<?php

namespace Modules\Starter\Traits;

use Carbon\Carbon;
use Carbon\CarbonTimeZone;
use DateTimeInterface;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ReflectionClass;

trait Accessable
{
	public static function cacheMutatedAttributes($classOrInstance): void
	{
		parent::cacheMutatedAttributes($classOrInstance);

		$reflection = new ReflectionClass($classOrInstance);

		$class = $reflection->getName();

		if (property_exists($class, 'accessors')) {
			static::$mutatorCache[$class] = array_merge(static::$mutatorCache[$class], array_keys(with(new $class())->accessors));
		}
	}

	public function getAttributeValue($key)
	{
		$value = parent::getAttributeValue($key);

		return $this->applyAccessors($key, $value);
	}

	/**
	 * @param $key
	 * @param $value
	 *
	 * @return mixed
	 */
	protected function applyAccessors($key, $value): mixed
	{
		if (property_exists($this, 'accessors')) {
			foreach ($this->accessors as $accessor => $params) {

				if ($key !== $accessor) {
					continue;
				}

				list($mutator, $params) = $this->parseMutatorNameAndParams($params);


				$value = match ($mutator) {
					'file' => $this->mutateFiles($value, $params),
					'date', 'datetime', 'human' => $this->mutateDatetime($value, $mutator),
					'str' => sprintf("%.2f", $value)
				};

			}
		}
		return $value;
	}


	/** Get the value of an attribute using its mutator.
	 *
	 * @param string $key
	 * @param mixed $value
	 *
	 * @return mixed
	 */
	protected function mutateAttribute($key, $value): mixed
	{
		if (!array_key_exists($key, $this->accessors ?: [])) {
			$value = parent::mutateAttribute($key, $value);
		} elseif (method_exists($this, 'get' . Str::studly($key) . 'Attribute')) {
			$value = parent::mutateAttribute($key, $value);
		}

		return $this->applyAccessors($key, $value);
	}


	/**
	 * Separates the accessors name from its optional parameters.
	 *
	 * @param string $mutator
	 *
	 * @return array
	 */
	protected function parseMutatorNameAndParams(string $mutator): array
	{
		$params = explode('|', $mutator);
		$mutator = array_shift($params);

		return [$mutator, count($params) ? explode(",", $params[0]) : []];

	}

	/**
	 * Laravel 使用 Carbon toJSON 转成标准 ISO 格式，时区为 UTC，这里不使用 ISO，避免时区信息丢失
	 * Prepare a date for array / JSON serialization.
	 *
	 * @param \DateTimeInterface $date
	 * @return string
	 */
	protected function serializeDate(DateTimeInterface $date): string
	{
		return $date->format('Y-m-d H:i:s');
	}


	/**
	 * 转换时间
	 * @param $value
	 * @param $type
	 * @return string|null
	 */
	protected function mutateDatetime($value, $type): ?string
	{
		if (!$value) {
			return null;
		}

		$datetime = Carbon::parse($value);

		return match ($type) {
			'date' => $datetime->toDateString(),
			'datetime' => $datetime->format('Y-m-d H:i'),
			'human' => $datetime->diffForHumans()
		};

	}


	/**
	 * 生成文件URL
	 * @param $value
	 * @param $params
	 * @return array|string[]|null
	 */
	protected function mutateFiles($value, $params): ?array
	{
		$is_array = isset($params[0]) && $params[0];
		$storage = $file_params[1] ?? null;

		if (empty($value)) {
			return $is_array ? [] : null;
		}

		$disk = Storage::disk($storage ?: config('filesystems.default'));

		if ($is_array) {
			$values = json_decode($value, true);

			if (!$values || count($values) === 0) {
				return [];
			} else {
				return array_map(function ($value) use ($disk) {
					return array_merge($value, $value['path'] ? ['url' => $disk->url($value['path'])] : []);
				}, $values);
			}
		} else {
			$value = json_decode($value, true);
			return array_merge($value, $value['path'] ? ['url' => $disk->url($value['path'])] : []);
		}
	}
}
