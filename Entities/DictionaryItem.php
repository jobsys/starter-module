<?php

namespace Modules\Starter\Entities;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DictionaryItem extends BaseClosureModel
{

	protected $hidden = [
		'created_at',
		'updated_at'
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
