<?php

namespace Modules\Starter\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Starter\Entities\MessageBatch;
use Modules\Starter\Services\MessageService;

class DispatchBatchMessageJob implements ShouldQueue
{
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


	public function __construct(public int $batch_id)
	{
	}

	public function handle(MessageService $service): void
	{
		$batch = MessageBatch::find($this->batch_id);
		if (!$batch) {
			return;
		}

		$receivers = $service->queryReceivers($batch);

		if ($receivers->isEmpty()) {
			return;
		}

		$batch->update(['total' => $receivers->count(), 'status' => MessageBatch::STATUS_SENDING]);


		$receivers->each(function ($receiver) {
			BatchMessageSendChannelJob::dispatch($this->batch_id, $receiver);
		});
	}
}
