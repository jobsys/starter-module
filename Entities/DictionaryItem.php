<?php

namespace Modules\Starter\Entities;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Franzose\ClosureTable\Models\Entity;

class DictionaryItem extends Entity
{

	protected $hidden = [
		'created_at',
		'updated_at'
	];

	protected $casts = [
		'is_active' => 'boolean',
	];

	protected $fillable = [
		'dictionary_id',
		'parent_id',
		'name',
		'slug',
		'value',
		'description',
		'is_active',
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
