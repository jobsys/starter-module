<?php

namespace Modules\Starter\Entities\Scope;

use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;


class FilterScope implements Scope
{

	/**
	 * All of the extensions to be added to the builder.
	 *
	 * @var string[]
	 */
	protected array $extensions = ['Filter'];

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
	protected function addFilter(Builder $builder): void
	{
		$builder->macro('filterable',
			/**
			 *
			 * @param Builder $builder
			 * @param array $props 如果有自定义的查询方法，可以在这里添加
			 * @return Builder
			 */
			function (Builder $builder, array $props = [], array $config = []) {
				$filters = request('newbieQuery');
				if(is_string($filters)){
					$filters = json_decode($filters, true);
				}
				if (empty($filters)) {
					return $builder;
				}
				foreach ($filters as $prop => $query) {

					//如果是级联查询，且是包含关系，且配置了级联模型，则将查询值转换为级联模型的子级ID
					if($query['type'] === 'cascade' && $query['condition'] === 'include' && isset($config['cascade'][$prop])){
						$value = is_array($query['value']) ? end($query['value']) : $query['value'];
						$query['value'] = app($config['cascade'][$prop])->find($value)->descendantsWithSelf()->pluck('id')->toArray();
					}

					if (isset($props[$prop])) {
						$builder =  $props[$prop]($builder, $query);
					} else {
                        $builder = land_filterable($prop, $builder, $query);
					}
				}

				return $builder;
			});
	}
}
