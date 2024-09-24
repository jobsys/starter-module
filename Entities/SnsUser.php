<?php

namespace Modules\Starter\Entities;


use Illuminate\Database\Eloquent\Relations\MorphTo;

class SnsUser extends BaseModel
{

    protected $casts = [
        'bound_at' => 'datetime',
        'is_auto_login' => 'boolean'
    ];


    public function snsable(): MorphTo
    {
        return $this->morphTo();
    }
}
