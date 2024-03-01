<?php

namespace Modules\Starter\Traits;


use Illuminate\Database\Eloquent\Relations\MorphMany;
use Modules\Starter\Entities\Message;

/**
 * 消息接收者
 */
trait MessageReceiver
{
	public function messages(): MorphMany
	{
		return $this->morphMany(Message::class, 'receiver');
	}
}
