<?php

namespace Modules\Starter\Entities;

class DictionaryItem extends BaseModel
{
    protected $casts = [
        'is_active' => 'boolean',
    ];
}
