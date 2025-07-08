<?php

namespace Modules\Starter\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Starter\Traits\Accessable;
use Modules\Starter\Traits\Filterable;
use Modules\Starter\Traits\Paginatable;
use Modules\Starter\Traits\Sortable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class BaseModel extends Model
{
	use Paginatable, Sortable, Accessable, Filterable, LogsActivity;

	protected $guarded = [];
	protected $hidden = ['deleted_at'];

	/**
	 * @var array 自定义字段转换规则
	 * 如 ['avatar' => 'file']
	 * 如 ['area' => 'area'] 将地区代码转换成三级代码数组
	 * 如 ['some_number' => 'str'] 将数字转换成两位小数的字符串
	 * 如 ['created_at' => 'datetime', 'created_at' => 'date', 'created_at' => 'human']
	 */
	protected $accessors = [];

	protected $model_name = ""; //模型中文名称，在日志、审核等业务中显示
	protected $model_slug = ""; //模型标识，用于相同逻辑业务间传递，如 api.manager.{model_slug}.approve


	public static function getModelName(): string
	{
		return (new static)->model_name;
	}

	public static function getModelSlug(): string
	{
		return (new static)->model_slug;
	}

	public static function getTableName(): string
	{
		return (new static)->getTable();
	}

	public function getActivitylogOptions(): LogOptions
	{
		return LogOptions::defaults()
			->logUnguarded()
			->dontLogIfAttributesChangedOnly(['created_at', 'updated_at', '_lft', '_rgt'])
			->logOnlyDirty()
			->setDescriptionForEvent(function (string $event_name) {
				return match ($event_name) {
					'created' => '创建' . $this->model_name,
					'updated' => '更新' . $this->model_name,
					'deleted' => '删除' . $this->model_name,
					default => ''
				};
			});
	}
}
