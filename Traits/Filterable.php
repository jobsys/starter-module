<?php

namespace Modules\Starter\Traits;


use Modules\Starter\Entities\Scope\FilterScope;

/**
 * @method static \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder filterable(array $props = [], array $config = [])
 */
trait Filterable
{
    public static function bootFilterable()
    {
        static::addGlobalScope(new FilterScope());
    }
}
