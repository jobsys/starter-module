<?php

namespace Modules\Starter\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Modules\Starter\Entities\MessageBatch;
use Modules\Starter\Messages\BatchMessageMessage;

class BatchMessageSendChannelJob implements ShouldQueue
{
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	public function __construct(public int $batch_id, public $receiver)
	{
	}

	public function handle(): void
	{
		$batch = MessageBatch::find($this->batch_id);
		$success = false;

		if ($batch->status === MessageBatch::STATUS_CANCELLED || $batch->status === MessageBatch::STATUS_PAUSED || $batch->status === MessageBatch::STATUS_FINISHED) {
			return;
		}

		try {
			$this->receiver->sendMessage(new BatchMessageMessage($batch));
			$success = true;
		} catch (\Exception $e) {
			Log::error("消息发送失败:: {$e->getMessage()}");
		}


		$batch->sent_count = $batch->sent_count + 1;

		if ($success) {
			$batch->success_count = $batch->success_count + 1;
		}
		if ($batch->sent_count === $batch->total) {
			$batch->status = MessageBatch::STATUS_FINISHED;
		}
		$batch->save();
	}
}
