<?php

namespace Modules\Starter\Entities\Scope;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class OrderByPinyinScope implements Scope
{
	protected ?string $field = 'name';

	public function __construct(string $field = 'name')
	{
		$this->field = $field;
	}

	public function apply(Builder $builder, Model $model)
	{
		$builder->orderByRaw("CONVERT({$this->field} USING gbk) ASC");
	}
}
