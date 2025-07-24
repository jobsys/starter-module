<?php

namespace Modules\Starter\Traits;


use Illuminate\Database\Eloquent\Relations\MorphMany;
use Modules\Starter\Entities\Message;
use Modules\Starter\Enums\Message\MessageChannel;

/**
 * 消息接收者
 */
trait MessageReceiver
{
	public function sendMessage($message): void
	{
		// 设置消息的接收者
		dispatch($message->to($this));
	}

	public function messages($channel = null): MorphMany
	{
		return $this->morphMany(Message::class, 'receiver')->when($channel, fn($query) => $query->where('channel', $channel))->latest();
	}

	/**
	 * Get the entity's read messages.
	 *
	 */
	public function read_messages(): MorphMany
	{
		return $this->messages()->whereNotNull('read_at')->where('channel', MessageChannel::DATABASE);
	}

	/**
	 * Get the entity's unread notifications.
	 *
	 */
	public function unread_messages(): MorphMany
	{
		return $this->messages()->whereNull('read_at')->where('channel', MessageChannel::DATABASE);
	}
}
