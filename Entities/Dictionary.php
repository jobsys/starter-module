<?php

namespace Modules\Starter\Entities;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Starter\Traits\Filterable;

class Dictionary extends BaseModel
{
    use Filterable;
    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(DictionaryItem::class);
    }
}
