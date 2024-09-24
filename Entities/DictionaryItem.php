<?php

namespace Modules\Starter\Entities;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Kalnoy\Nestedset\NodeTrait;

class DictionaryItem extends BaseModel
{
    use NodeTrait;

    protected $model_name = "字典项";

    protected $hidden = [
        'created_at',
        'updated_at',
        '_lft',
        '_rgt',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function dictionary(): BelongsTo
    {
        return $this->belongsTo(Dictionary::class);
    }

    public function allParent()
    {
        return $this->parent()->with(['allParent']);
    }
}
