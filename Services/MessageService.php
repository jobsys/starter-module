<?php

namespace Modules\Starter\Services;


use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Modules\Starter\Entities\Message;
use Modules\Starter\Entities\MessageBatch;
use Modules\Starter\Enums\Message\MessageChannel;
use Modules\Starter\Jobs\DispatchBatchMessageJob;

class MessageService extends BaseService
{

	/**
	 * 获取渠道选项
	 * @return array[]
	 */
	public function getChannelOptions(): array
	{
		$channels = [
			['label' => '站内通知', 'text' => '站内通知', 'value' => MessageChannel::DATABASE, 'color' => 'orange'],
		];

		if (config('conf.use_wechat_channel')) {
			$wechat_channel = config('conf.use_wechat_channel');
			if (Str::contains($wechat_channel, 'work')) {
				$channels[] = ['label' => '企业微信', 'text' => '企业微信', 'value' => MessageChannel::WECHAT_WORK, 'color' => 'blue'];
			}

			if (Str::contains($wechat_channel, 'official')) {
				$channels[] = ['label' => '公众号消息', 'text' => '公众号消息', 'value' => MessageChannel::WECHAT_OFFICIAL, 'color' => 'green'];
			}
		}

		return $channels;
	}

	/**
	 * 生成指消息任务
	 * @param MessageBatch $batch
	 * @return array
	 */
	public function createMessageBatchTask(MessageBatch $batch): array
	{
		if ($batch->is_active) {
			switch ($batch->send_type) {
				case 'immediate': //立即发送
					DispatchBatchMessageJob::dispatch($batch->id);
					break;
				case 'schedule': // 定时发送
					$send_at = land_predict_date_time($batch->send_params['send_at'] ?? null, 'dateTime');

					if (!$send_at) {
						return [null, '发送时间不能为空'];
					}
					DispatchBatchMessageJob::dispatch($batch->id)->delay($send_at->diffInSeconds(now()));
					break;
				case 'cron': //不做处理，由定时任务处理
					break;
				default:
					return [null, '发送时间设定有误'];
			}
		}

		return [true, null];
	}

	/**
	 * 查询接收者
	 * @param MessageBatch $batch
	 * @return Collection
	 */
	public function queryReceivers(MessageBatch $batch): Collection
	{
		if (config("starter.message.receivers_query.$batch->receiver_type.query")) {
			return config("starter.message.receivers_query.$batch->receiver_type.query")($batch);
		}
		return collect();
	}

	/**
	 * 标记已读
	 * @param Message $message
	 * @return void
	 */
	public function markAsRead(Message $message): void
	{
		if (!$message->message_batch_id) {
			if (!$message->read_at) {
				$message->update(['read_at' => now()]);
			}
		} else {
			//如果每没渠道是已读的，就更新一下批次的状态
			$dont_update_batch_read_count = Message::where('message_batch_id', $message->message_batch_id)
				->where('receiver_type', $message->receiver_type)->where('receiver_id', $message->receiver_id)
				->whereNotNull('read_at')->exists();
			if (!$message->read_at) {
				$message->update(['read_at' => now()]);
			}
			if (!$dont_update_batch_read_count) {
				MessageBatch::where('id', $message->message_batch_id)->increment('read_count');
			}
		}
	}
}
