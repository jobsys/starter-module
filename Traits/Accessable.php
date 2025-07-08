<?php

namespace Modules\Starter\Traits;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * 原版本重写了所有的 GetAttribute，导致在程序中拿到的值也是转换过的，改版使用 BaseModel 的 toArray 方法，只在返回数据时才转换
 */
trait Accessable
{
	/**
	 * 重写序列化方法，加入 accessors 处理
	 * @return array
	 */
	public function toArray(): array
	{
		$attributes = parent::toArray();
		if (empty($this->accessors)) {
			return $attributes;
		}
		foreach ($attributes as $key => $value) {
			if (isset($this->accessors[$key])) {
				$attributes[$key] = $this->applyAccessors($value, $this->accessors[$key]);
			}
		}

		return $attributes;
	}

	/**
	 * @param $value
	 * @param $accessor
	 * @return mixed
	 */
	protected function applyAccessors($value, $accessor): mixed
	{

		list($mutator, $params) = $this->parseMutatorNameAndParams($accessor);
		return match ($mutator) {
			'file' => $this->mutateFiles($value, $params),
			'area' => $this->mutateArea($value),
			'date', 'datetime', 'human' => $this->mutateDatetime($value, $mutator),
			'str' => sprintf("%.2f", $value)
		};
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
	 * 将地区代码转换成三级代码数组
	 * @param $value
	 * @return array|null
	 */
	protected function mutateArea($value): ?array
	{

		//如果没有 $value 或者是 $value 不是 6位数字，直接返回 null
		if (empty($value) || !is_numeric($value) || strlen($value) !== 6) {
			return null;
		}

		$result = collect();

		while (strlen($value) >= 2) {
			$result->prepend(Str::padRight($value, '6', '0'));
			if (Str::endsWith($value, '00')) {
				$value = substr($value, 0, strlen($value) - 4);
			} else {
				$value = substr($value, 0, strlen($value) - 2);
			}
		}

		return $result->toArray();
	}

	/**
	 * 生成文件URL
	 * @param $value
	 * @param $params
	 * @return mixed
	 */
	protected function mutateFiles($value, $params): mixed
	{
		$is_mixed = isset($params[0]) && $params[0] === '*';

		if (empty($value)) {
			return $value;
		}

		if ($is_mixed) {
			//混合模式要递归遍历

			//不是数组，直接返回
			if (!is_array($value)) {
				return $value;
			}
			//如果 _type === file 则直接按文件处理
			if (($value['_type'] ?? null) === 'file') {
				return $this->processFile($value);
			}
			//其它的 Object 只能递归处理
			foreach ($value as $k => $v) {
				$value[$k] = $this->mutateFiles($v, $params);
			}
			return $value;
		} else {
			if (array_is_list($value)) {
				return array_map(fn($v) => $this->processFile($v), $value);
			}
			return $this->processFile($value);
		}
	}


	private function processFile($value)
	{
		if (($value['_type'] ?? null) !== 'file') {
			return $value;
		}

		$storage = null;

		if (($value['_disk'] ?? null) === 'private') {
			$storage = 'private';
		}

		$disk = Storage::disk($storage ?: config('filesystems.default'));

		$path = $value['path'] ?? null;

		if (!$path) {
			return [];
		}

		$value['url'] = $storage === 'private' ? $disk->temporaryUrl($path, now()->addMinutes(10)) : $disk->url($path);

		//生成缩略图Path用于检测是否存在
		$thumb_path = land_add_file_suffix($path);

		if (land_is_image($thumb_path) && $disk->exists($thumb_path)) {
			$value['thumbUrl'] = $storage === 'private' ? $disk->temporaryUrl($thumb_path, now()->addMinutes(10)) : $disk->url($thumb_path);
		}

		return $value;
	}
}
