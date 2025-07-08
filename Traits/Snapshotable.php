<?php

namespace Modules\Starter\Traits;


use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Modules\Starter\Entities\Snapshot;

/**
 * 快照表
 */
trait Snapshotable
{
	/**
	 * 快照关联
	 *
	 * @return MorphMany
	 */
	public function snapshots(): MorphMany
	{
		return $this->morphMany(Snapshot::class, 'snapshotable');
	}

	/**
	 * 生成快照
	 * @param array|null $snapshot 快照内容，默认为当前模型的原始数据
	 * @param Authenticatable|null $snapshoter
	 * @param mixed $snapshot_at 快照时间，默认为当前时间
	 * @return Snapshot|null
	 */
	public function snapshot(array $snapshot = null, Authenticatable $snapshoter = null, mixed $snapshot_at = null): Snapshot|null
	{

		if (!$snapshot) {
			$snapshot = $this->getOriginal();
		}

		if (!$snapshoter) {
			$snapshoter = auth()->user();
		}

		if (!$snapshot_at) {
			$snapshot_at = now();
		}

		return $this->snapshots()->create([
			'snapshot' => $snapshot,
			'snapshoter_type' => get_class($snapshoter),
			'snapshoter_id' => $snapshoter->getKey(),
			'snapshot_at' => $snapshot_at
		]);
	}
}
