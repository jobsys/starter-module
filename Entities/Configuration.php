<?php

namespace Modules\Starter\Entities;


use Modules\Starter\Traits\Snapshotable;

class Configuration extends BaseModel
{

	use Snapshotable;

	protected $model_name = "系统配置项";

	protected $casts = [
		'value' => 'array',
	];

	protected $accessors = [
		'value' => 'file|*',
	];
}
