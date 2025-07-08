<?php

namespace Modules\Starter\Entities;


use Illuminate\Database\Eloquent\Relations\MorphTo;

class Snapshot extends BaseModel
{

	protected $model_name = "快照";

	protected $casts = [
		'snapshot_at' => 'datetime',
		'snapshot' => 'array'
	];

	/**
	 * 快照对象
	 * @return MorphTo
	 */
	public function snapshotable(): MorphTo
	{
		return $this->morphTo();
	}

	/**
	 * 快照操作者
	 * @return MorphTo
	 */
	public function snapshoter(): MorphTo
	{
		return $this->morphTo();
	}
}
