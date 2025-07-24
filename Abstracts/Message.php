<?php

namespace Modules\Starter\Abstracts;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use Modules\Starter\Enums\Message\MessageChannel;
use Modules\Starter\Enums\Message\MessageStatus;
use Modules\Wechat\Enums\WechatSns;
use Modules\Wechat\Services\WechatService;

abstract class Message implements ShouldQueue
{
	use Queueable, SerializesModels, Dispatchable;

	protected Authenticatable $to;

	public function to($receiver): static
	{
		$this->to = $receiver;
		return $this;
	}

	public function via($receiver): array
	{
		return [MessageChannel::DATABASE];
	}

	public function handle(): void
	{
		foreach ($this->via($this->to) as $channel) {
			$bag = $this->messageBag($this->to);
			$method = 'via' . ucfirst($channel);
			if (method_exists($this, $method)) {
				$this->{$method}($bag);
			}
		}
	}

	public function messageBag($receiver): array
	{
		//返回数据库数组
		return [];
	}

	public function viaDatabase(array $message_bag): void
	{
		$message_bag['channel'] = MessageChannel::DATABASE;
		$message_bag['status'] = MessageStatus::SUCCESS;
		$this->to->messages()->create($message_bag);
	}

	public function viaWork(array $message_bag): void
	{
		$message_bag['channel'] = MessageChannel::WECHAT_WORK;
		$message_bag['status'] = MessageStatus::PENDING;
		$message = $this->to->messages()->create($message_bag);
		$wechat_channel = config('conf.use_wechat_channel');
		//TODO
		$open_id = $this->to->sns?->sns_id ?? $this->to->name;
		if ($wechat_channel && Str::contains($wechat_channel, WechatSns::Work) && $open_id) {
			$service = app(WechatService::class);
			$landing_pages = config('starter.message.landing_page');
			$landing_page = $landing_pages[get_class($this->to)] ? route($landing_pages[get_class($this->to)], ['id' => $message->id]) : null;
			$result = $service->workSendMessage($open_id, $this->to?->nickname ?? $this->to?->name, $message_bag['title'] ?? '', $landing_page);
			if ($result) {
				$message->status = MessageStatus::SUCCESS;
			} else {
				$message->status = MessageStatus::FAILED;
			}
		} else {
			$message->status = MessageStatus::FAILED;
		}

		$message->save();
	}


	public function viaOfficial($receiver)
	{
	}

}
