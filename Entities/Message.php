<?php

namespace Modules\Starter\Entities;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Message extends BaseModel
{
	protected $model_name = '消息';


	protected $casts = [
		'data' => 'array',
		'read_at' => 'datetime'
	];

	protected $accessors = [
		'read_at' => 'datetime',
		'created_at' => 'datetime'
	];

	public function batch(): BelongsTo
	{
		return $this->belongsTo(MessageBatch::class);
	}

	public function receiver(): MorphTo
	{
		return $this->morphTo();
	}
}
