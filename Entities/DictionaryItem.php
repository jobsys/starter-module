<?php

namespace Modules\Starter\Entities;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DictionaryItem extends BaseModel
{
	protected $casts = [
		'is_active' => 'boolean',
	];

	public function parent(): BelongsTo
	{
		return $this->belongsTo(DictionaryItem::class, 'parent_id');
	}

	public function children(): HasMany
	{
		return $this->hasMany(DictionaryItem::class, 'parent_id');
	}

	public function dictionary(): BelongsTo
	{
		return $this->belongsTo(Dictionary::class);
	}

	public function allChildren(): HasMany
	{
		return $this->children()->with('allChildren');
	}
}
