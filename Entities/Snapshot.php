<?php

namespace Modules\Starter\Entities;


use Illuminate\Database\Eloquent\Relations\MorphTo;

class Snapshot extends BaseModel
{

	protected $casts = [
		'snapshot_at' => 'datetime',
		'snapshot' => 'array'
	];


	public function snapshotable(): MorphTo
	{
		return $this->morphTo();
	}
}
