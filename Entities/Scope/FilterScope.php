<?php

namespace Modules\Starter\Entities\Scope;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Str;
use Kalnoy\Nestedset\NodeTrait;


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
			 * @param array $config
			 * @param array $newbieQuery 手动传递的 newbieQuery 参数
			 * @return Builder
			 */
			function (Builder $builder, array $props = [], array $config = [], array $newbieQuery = []) {
				$filters = request('_q', false);//简约版，节省字符长度
				if (!$filters) {
					$filters = request('newbieQuery', $newbieQuery);
				}
				if (is_string($filters)) {
					$filters = json_decode($filters, true);
				}
				if (empty($filters)) {
					return $builder;
				}
				foreach ($filters as $prop => $query) {

					if (!empty($query['t'])) {
						$query['type'] = $query['t'];
					}
					if (!empty($query['c'])) {
						$query['condition'] = $query['c'];
					}
					if (!empty($query['v'])) {
						$query['value'] = $query['v'];
					}

					if (!isset($query['type']) || in_array($query['type'], ['i', 'in', 'input'])) {
						$query['type'] = 'input';
					} elseif (in_array($query['type'], ['s', 'select'])) {
						$query['type'] = 'select';
					} elseif (in_array($query['type'], ['n', 'num', 'number'])) {
						$query['type'] = 'number';
					} elseif (in_array($query['type'], ['d', 'date', 'datetime'])) {
						$query['type'] = 'datetime';
					} elseif (in_array($query['type'], ['t', 'text', 'textarea'])) {
						$query['type'] = 'textarea';
					} elseif (in_array($query['type'], ['c', 'cascade'])) {
						$query['type'] = 'cascade';
					} elseif (in_array($query['type'], ['switch', 'boolean', 'bool'])) {
						$query['type'] = 'boolean';
					}

					//如果是级联查询，且是包含关系，且配置了级联模型，则将查询值转换为级联模型的子级ID
					if ($query['type'] === 'cascade' && in_array($query['condition'], ['include', 'in']) && isset($config['cascade'][$prop])) {
						$value = is_array($query['value']) ? end($query['value']) : $query['value'];

						/**
						 * @var NodeTrait $entity
						 */
						$entity = app($config['cascade'][$prop]);
						$query['value'] = $entity->descendantsAndSelf($value)->pluck('id')->toArray();
					}

					if (isset($props[$prop])) { //判断自定规则
						$builder = $props[$prop]($builder, $query);
					} else if (Str::contains($prop, '.')) { //判断关联规则，使用关联规则前需自行使用 with 加载关联
						$relations = collect(explode('.', $prop));
						$relation_prop = $relations->pop(); // 最后一项是属性，前面都是关联
						$builder = $builder->whereHas($relations->join('.'), fn($sub_query) => land_filterable($relation_prop, $sub_query, $query));
					} else { //普通规则
						$builder = land_filterable($prop, $builder, $query);
					}
				}

				return $builder;
			});
	}
}
