<?php

namespace Modules\Starter\Enums\Message;

enum MessageChannel: string
{
	const DATABASE = 'database'; // 数据库
	const WECHAT_WORK = 'work'; // 企业微信
	const WECHAT_OFFICIAL = 'official'; // 公众号
	const WECHAT_WEAPP = 'weapp'; // 小程序
	const SMS = 'sms'; // 短信
	const EMAIL = 'email'; // 邮件
}
