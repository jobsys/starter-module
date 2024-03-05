<?php

namespace Modules\Starter\Entities;


use Carbon\Carbon;
use Franzose\ClosureTable\Models\Entity;
use Modules\Starter\Traits\Accessable;
use Modules\Starter\Traits\Paginatable;
use Modules\Starter\Traits\Sortable;

/**
 * 有层级关系的 Closure 表继续这个
 */
class BaseClosureModel extends Entity
{
    use Paginatable, Sortable, Accessable;

    protected $hidden = ['deleted_at'];
}
