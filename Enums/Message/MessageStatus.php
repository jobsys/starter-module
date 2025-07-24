<?php

namespace Modules\Starter\Enums\Message;

enum MessageStatus: string
{
	const PENDING = 'pending';
	const SUCCESS = 'success';
	const FAILED = 'failed';
}
