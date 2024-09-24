<?php

namespace Modules\Starter\Entities;


use Illuminate\Database\Eloquent\Relations\MorphTo;

class Snapshot extends BaseModel
{

    protected $model_name = "快照";

    protected $casts = [
        'snapshot_at' => 'datetime',
        'snapshot' => 'array'
    ];


    public function snapshotable(): MorphTo
    {
        return $this->morphTo();
    }
}
