<?php

namespace Modules\Starter\Entities;

use Illuminate\Database\Eloquent\Relations\HasMany;

class Dictionary extends BaseModel
{
    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(DictionaryItem::class);
    }
}
