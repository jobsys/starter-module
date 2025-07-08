<?php

namespace Modules\Starter\Entities;

use Illuminate\Database\Eloquent\Relations\HasMany;

class Dictionary extends BaseModel
{
	protected $model_name = "字典";

	protected $casts = [
		'is_active' => 'boolean',
		'is_cascaded' => 'boolean',
	];

	public function items(): HasMany
	{
		return $this->hasMany(DictionaryItem::class);
	}
}
