<?php

namespace Modules\Starter\Traits;


use Illuminate\Database\Eloquent\Builder;


trait JsonArrayLikable
{
	public function scopeWhereJsonArrayLike(Builder $query, string $column, string $attribute, string $keyword): Builder
	{
		return $query->whereRaw(
			"JSON_SEARCH(JSON_UNQUOTE(JSON_EXTRACT(`$column`, '$[*].$attribute')), 'one', ?) IS NOT NULL",
			['%' . $keyword . '%']
		);
	}
}
