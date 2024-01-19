<?php

namespace Modules\Starter\Entities\Scope;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;


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
				if(is_string($sorters)){
					$sorters = json_decode($sorters, true);
				}
				if (empty($sorters)) {
					return $builder;
				}
				foreach ($sorters as $prop => $direction) {
					if (isset($props[$prop])) {
						$builder =  $props[$prop]($builder, $direction);
					} else {
                        $builder = land_sortable($prop, $builder, $direction);
					}
				}

				return $builder;
			});
	}
}
