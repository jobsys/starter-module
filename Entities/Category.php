<?php

namespace Modules\Starter\Entities;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Kalnoy\Nestedset\NodeTrait;

class Category extends BaseModel
{
	use NodeTrait;

	protected $model_name = "分类";

	protected $hidden = [
		'_lft',
		'_rgt',
		'updated_at',
		'created_at',
	];

	public function homologies(): HasMany
	{
		return $this->hasMany(Category::class, 'homology_id', 'id');
	}

	public function children(): HasMany
	{
		return $this->hasMany(Category::class, 'parent_id', 'id');
	}

	public function parent(): BelongsTo
	{
		return $this->belongsTo(Category::class, 'parent_id', 'id');
	}
}
