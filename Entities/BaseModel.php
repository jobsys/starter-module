<?php

namespace Modules\Starter\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Starter\Traits\Accessable;
use Modules\Starter\Traits\Filterable;
use Modules\Starter\Traits\Paginatable;
use Modules\Starter\Traits\Sortable;

class BaseModel extends Model
{
	use Paginatable, Sortable, Accessable, Filterable;

	protected $hidden = ['deleted_at'];

	protected $accessors = [];

}
