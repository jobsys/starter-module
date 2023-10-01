<?php

namespace Modules\Starter\Entities;


class Configuration extends BaseModel
{

    protected $casts = [
        'value' => 'array',
    ];
}
