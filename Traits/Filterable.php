<?php

namespace Modules\Starter\Traits;


use Modules\Starter\Entities\Scope\FilterScope;


trait Filterable
{
    public static function bootFilterable()
    {
        static::addGlobalScope(new FilterScope());
    }
}
