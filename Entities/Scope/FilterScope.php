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
			function (Builder $builder, array $props = []) {
				$filters = request('newbieQuery');
				if (empty($filters)) {
					return $builder;
				}
				foreach ($filters as $prop => $query) {
					if (isset($props[$prop])) {
						$builder =  $props[$prop]($builder, $query);
					} else {
						switch ($query['type']) {
							case "input":
								$builder =  match ($query['condition']) {
									'equal' => $builder->where($prop, $query['value']),
									'notEqual' => $builder->where($prop, '!=', $query['value']),
									'include' => $builder->where($prop, 'like', "%{$query['value']}%"),
									'exclude' => $builder->where($prop, 'not like', "%{$query['value']}%"),
									'null' => $builder->whereNull($prop),
									'notNull' => $builder->whereNotNull($prop),
								};
								break;
							case "select":
								$builder =  match ($query['condition']) {
									'equal' => $builder->where($prop, $query['value']),
									'notEqual' => $builder->where($prop, '!=', $query['value']),
									'include' => $builder->whereIn($prop, $query['value']),
									'exclude' => $builder->whereNotIn($prop, $query['value']),
								};
								break;
							case "number":
								$builder =  match ($query['condition']) {
									'equal' => $builder->where($prop, $query['value']),
									'notEqual' => $builder->where($prop, '!=', $query['value']),
									'lessThan' => $builder->where($prop, '<', $query['value']),
									'greaterThan' => $builder->where($prop, '>', $query['value']),
									'between' => $builder->whereBetween($prop, $query['value']),
								};
								break;
                            case "date":

                                $query['value'] = is_array($query['value']) ? collect($query['value'])->map(fn ($v) => land_predict_date_time($v, 'date'))->toArray() : land_predict_date_time($query['value'], 'date');

                                $builder = match ($query['condition']) {
                                    'equal' => $builder->whereDate($prop, $query['value']),
                                    'lessThan' => $builder->whereDate($prop, '<', $query['value']),
                                    'greaterThan' => $builder->whereDate($prop, '>', $query['value']),
                                    'between' => $builder->whereBetween($prop, CarbonPeriod::between($query['value'][0], $query['value'][1]))
                                };
                                break;
							case "textarea":
								$builder =  match($query['condition']){
									'equal' => $builder->whereIn($prop, $query['value']),
									'exclude' => $builder->whereNotIn($prop, $query['value'])
								};
								break;
							default:
								break;
						}
					}
				}

				return $builder;
			});
	}
}
