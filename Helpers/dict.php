<?php

use Modules\Starter\Entities\BaseModel;
use Modules\Starter\Entities\Dictionary;
use Modules\Starter\Entities\DictionaryItem;

if (!function_exists('dict_get')) {
	/**
	 * 获取字典
	 * @param string $slug
	 * @param bool $only_options
	 * @param string $valueKey 值字段名
	 * @return array|BaseModel|Dictionary|null
	 */
	function dict_get(string $slug, bool $only_options = true, string $valueKey = 'value'): BaseModel|array|Dictionary|null
	{
		$dict = Dictionary::with(['items' => function ($query) {
			$query->where('is_active', 1)->orderBy('sort_order', 'desc')->orderBy('id', 'asc');
		}])
			->where('slug', $slug)->where('is_active', 1)
			->first(['id', 'name', 'slug', 'description', 'is_active']);

		if (!$dict || !$dict->items->count()) {
			return $only_options ? [] : null;
		}

		if ($only_options) {
			$tree = DictionaryItem::whereNull('parent_id')->where('dictionary_id', $dict->id)->get()->map(function (DictionaryItem $item) use ($valueKey) {
				$children = $item->getDescendants()->toTree()->toArray();
				if ($children && count($children)) {
					 $item->{'children'} = $children;
					 return $item;
				}
				return $item;

			})->toArray();

			return land_tidy_tree($tree, function ($item) use ($valueKey) {
				return ['label' => $item['name'], 'text' => $item['name'], 'value' => $item[$valueKey]];
			});
		}

		return $dict;


	}
}

