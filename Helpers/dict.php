<?php

use Modules\Starter\Entities\BaseModel;
use Modules\Starter\Entities\Dictionary;

if (!function_exists('dict_get')) {
    /**
     * 获取字典
     * @param string $name
     * @param bool $only_options
     * @return array|BaseModel|Dictionary|null
     */
    function dict_get(string $name, bool $only_options = true): BaseModel|array|Dictionary|null
    {
        $dict = Dictionary::with(['items' => function ($query) {
            $query->where('is_active', 1)->orderBy('sort_order', 'desc')->orderBy('id', 'asc');
        }])
            ->where('name', $name)->where('is_active', 1)
            ->first(['id', 'name', 'display_name', 'description', 'is_active']);

        if (!$dict || !$dict->items->count()) {
            return $only_options ? [] : null;
        }

        return $only_options ? array_values($dict->items->map(function ($item) {
            //label for antdv, text for vant
            return ['label' => $item->display_name, 'text' => $item->display_name, 'value' => $item->value];
        })->toArray()) : $dict;

    }
}

