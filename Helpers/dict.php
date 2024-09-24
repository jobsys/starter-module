<?php

use Modules\Starter\Entities\BaseModel;
use Modules\Starter\Entities\Dictionary;
use Modules\Starter\Entities\DictionaryItem;

if (!function_exists('dict_get')) {
	/**
	 * 获取字典
	 * @param string|array $slug
	 * @param bool $only_options
	 * @param string $valueKey 值字段名
	 * @return array|BaseModel|Dictionary|null
	 */
	function dict_get(string|array $slug, bool $only_options = true, string $valueKey = 'value'): BaseModel|array|Dictionary|null
	{

		$is_single = !is_array($slug);

		$dict_items = Dictionary::with(['items' => function ($query) {
			$query->where('is_active', true)->orderByDesc('sort_order');
		}])->where('is_active', true)
			->where(function ($query) use ($is_single, $slug) {
				if ($is_single) {
					$query->where('slug', $slug);
				} else {
					$query->whereIn('slug', $slug);
				}
			})->select(['id', 'name', 'slug', 'description', 'is_active'])->orderBy('id')->get();

		if ($is_single && (!$dict_items->count() || !$dict_items->first()->items->count())) {
			return $only_options ? [] : null;
		}


		$options = [];

		if ($only_options) {
			foreach ($dict_items as $dict) {
				if ($dict->is_cascaded) {
					$tree = $dict->items->toTree()->toArray();
					if ($is_single) {
						return land_tidy_tree($tree, fn($item) => ['label' => $item['name'], 'text' => $item['name'], 'value' => $item[$valueKey]]);
					}

					$options[$dict->slug] = land_tidy_tree($tree, fn($item) => ['label' => $item['name'], 'text' => $item['name'], 'value' => $item[$valueKey]]);
				} else {
					if ($is_single) {
						return $dict->items->map(fn($item) => ['label' => $item['name'], 'text' => $item['name'], 'value' => $item[$valueKey]])->toArray();
					}
					$options[$dict->slug] = $dict->items->map(fn($item) => ['label' => $item['name'], 'text' => $item['name'], 'value' => $item[$valueKey]])->toArray();
				}
			}

			return $options;
		}

		return $is_single ? $dict_items->first() : $dict_items;
	}
}

