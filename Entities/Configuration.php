<?php

namespace Modules\Starter\Entities;


use Illuminate\Database\Eloquent\Model;
use Modules\Starter\Traits\Accessable;

class Configuration extends Model
{

    use Accessable;

    protected $casts = [
        'value' => 'array',
    ];

    protected $accessors = [
        'value' => 'file|*',
    ];
}
