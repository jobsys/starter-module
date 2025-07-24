<?php

namespace Modules\Starter\Entities;

use Illuminate\Database\Eloquent\Relations\HasMany;

class MessageBatch extends BaseModel
{

    protected $model_name = '消息批次';

    const STATUS_PENDING = 'pending'; // 待发送
    const STATUS_SENDING = 'sending'; // 发送中
    const STATUS_PAUSED = 'paused'; // 暂停
    const STATUS_CANCELLED = 'cancelled'; // 已取消
    const STATUS_FINISHED = 'finished'; // 已完成
    const STATUS_FAILED = 'failed'; // 失败


    const SENT_TYPE_IMMEDIATE = 'immediate'; // 立即发送
    const SENT_TYPE_SCHEDULE = 'schedule'; // 延迟发送
    const SENT_TYPE_CRON = 'cron'; //定时循环发送


    protected $casts = [
        'channels' => 'array',
        'receivers' => 'array',
        'send_params' => 'array',
        'read_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    protected $accessors = [
        'read_at' => 'datetime',
        'created_at' => 'datetime'
    ];

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

}
