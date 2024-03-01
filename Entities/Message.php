<?php

namespace Modules\Starter\Entities;

use Illuminate\Database\Eloquent\Relations\MorphTo;

class Message extends BaseModel
{

	const CHANNEL_WECHAT_OFFICIAL = 'official'; // 公众号
	const CHANNEL_WECHAT_WORK = 'work'; // 企业微信
	const CHANNEL_WECHAT_WEAPP = 'weapp'; // 小程序
	const CHANNEL_SMS = 'sms'; // 短信

	const STATUS_PENDING = 'pending';
	const STATUS_SUCCESS = 'success';
	const STATUS_FAIL = 'failed';

	protected $casts = [
		'data' => 'array',
	];

	public function receiver(): MorphTo
	{
		return $this->morphTo();
	}
}
