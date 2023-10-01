<?php

namespace Modules\Starter\Entities;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Starter\Traits\Filterable;

class Category extends BaseModel
{
	use Filterable;

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
