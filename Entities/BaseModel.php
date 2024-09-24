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

    protected $accessors = [];

    protected $model_name = ""; //模型中文名称，在日志、审核等业务中显示
    protected $model_slug = ""; //模型标识，用于相同逻辑业务间传递，如 api.manager.{model_slug}.approve


    public function getModelName()
    {
        return $this->model_name;
    }

    public function getModelSlug()
    {
        return $this->model_slug;
    }

    /**
     * 获取实体名称，用于多态模式下获取实体名称
     * 如 $this->name, $this->title
     * @return string
     */
    public function getEntityName(): string
    {
        return '';
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
