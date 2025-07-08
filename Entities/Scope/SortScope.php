<?php

namespace Modules\Starter\Entities\Scope;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Str;


class SortScope implements Scope
{

	/**
	 * All of the extensions to be added to the builder.
	 *
	 * @var string[]
	 */
	protected array $extensions = ['Sort'];

	/**
	 * @param Builder $builder
	 * @param Model $model
	 * @return void
	 */
	public function apply(Builder $builder, Model $model)
	{
	}


	/**
	 * Extend the query builder with the needed functions.
	 *
	 * @param Builder $builder
	 * @return void
	 */
	public function extend(Builder $builder): void
	{
		foreach ($this->extensions as $extension) {
			$this->{"add{$extension}"}($builder);
		}
	}


	/**
	 * Add the restore extension to the builder.
	 *
	 * @param Builder $builder
	 * @return void
	 */
	protected function addSort(Builder $builder): void
	{
		$builder->macro('sortable',
			/**
			 *
			 * @param Builder $builder
			 * @param array $props 如果有自定义的查询方法，可以在这里添加
			 * @return Builder
			 */
			function (Builder $builder, array $props = []) {
				$sorters = request('newbieSort');
				if (is_string($sorters)) {
					$sorters = json_decode($sorters, true);
				}
				if (empty($sorters)) {
					return $builder;
				}
				foreach ($sorters as $prop => $direction) {
					if (isset($props[$prop])) {
						$builder = $props[$prop]($builder, $direction);
					} else if (Str::contains($prop, '.')) { //判断关联规则，使用关联规则前需自行使用 with 加载关联
						$relations = collect(explode('.', $prop));
						$relation_prop = $relations->pop(); // 最后一项是属性，前面都是关联
						$builder = $builder->withAggregate($relations->join('.'), $relation_prop);
						$builder = land_sortable($relations->join('.') . '_' . $relation_prop, $builder, $direction);
					} else {
						$builder = land_sortable($prop, $builder, $direction);
					}
				}

				return $builder;
			});
	}
}
