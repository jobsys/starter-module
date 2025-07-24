<?php

namespace Modules\Starter\Messages;


use Modules\Starter\Abstracts\Message;
use Modules\Starter\Entities\MessageBatch;
use Modules\Starter\Enums\Message\MessageType;

class BatchMessageMessage extends Message
{

	public function __construct(public MessageBatch $batch)
	{
	}

	public function via($receiver): array
	{
		return $this->batch->channels;
	}

	public function messageBag($receiver): array
	{
		return [
			'message_batch_id' => $this->batch->id,
			'title' => $this->batch->title,
			'content' => $this->batch->content,
			'type' => MessageType::NOTIFICATION,
		];
	}
}
