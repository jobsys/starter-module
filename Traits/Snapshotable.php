<?php

namespace Modules\Starter\Traits;


use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Modules\Starter\Entities\Snapshot;

/**
 * 快照表
 */
trait Snapshotable
{
	/**
	 *  Return collection of snapshot rows related to current model
	 *
	 * @return \Illuminate\Database\Eloquent\Collection
	 */
	public function snapshots(): MorphMany
	{
		return $this->morphMany(Snapshot::class, 'snapshotable');
	}

	/**
	 * 生成快照
	 * @param array|null $snapshot 快照内容，默认为当前模型的原始数据
	 * @param int|null $snapshot_user_id 操作者ID，默认为当前登录用户
	 * @param mixed $snapshot_at 快照时间，默认为当前时间
	 * @return Snapshot|null
	 */
	public function snapshot(array $snapshot = null, int $snapshot_user_id = null, mixed $snapshot_at = null): Snapshot|null
	{

		if (!$snapshot) {
			$snapshot = $this->getOriginal();
		}

		if (!$snapshot_user_id) {
			$snapshot_user_id = auth()->check() ? auth()->id() : null;
		}

		if (!$snapshot_at) {
			$snapshot_at = Carbon::now();
		}

		return $this->snapshots()->create(compact('snapshot', 'snapshot_user_id', 'snapshot_at'));
	}
}
