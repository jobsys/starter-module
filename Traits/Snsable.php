<?php

namespace Modules\Starter\Traits;


use Illuminate\Database\Eloquent\Relations\MorphOne;
use Modules\Starter\Entities\SnsUser;

/**
 * 社交账号关联
 */
trait Snsable
{
	public function sns(): MorphOne
	{
		return $this->morphOne(SnsUser::class, 'snsable');
	}
}
