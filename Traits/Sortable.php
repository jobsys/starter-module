<?php

namespace Modules\Starter\Traits;


use Modules\Starter\Entities\Scope\SortScope;

/**
 * @method static \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder sortable(array $props = [], array $config = [])
 */
trait Sortable
{
    public static function bootSortable()
    {
        static::addGlobalScope(new SortScope());
    }
}
