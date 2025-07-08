<?php

namespace Modules\Starter\Entities;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Modules\Starter\Services\TagService;

/**
 * @mixin Model
 * @mixin Builder
 *
 * @property string id
 * @property string slug
 * @property string name
 * @property-read Collection|Tag[] tags
 *
 *
 * @see https://github.com/rtconner/laravel-tagging
 */
class TagGroup extends BaseModel
{
	protected $table = 'tagging_tag_groups';

	public $timestamps = false;


	public function __construct(array $attributes = [])
	{
		parent::__construct($attributes);
	}

	/**
	 * Get suggested tags
	 */
	public function tags(): Builder|HasMany|TagGroup
	{
		$model = TagService::tagModelString();

		return $this->hasMany($model, 'tag_group_id');
	}

	public function setNameAttribute($value): void
	{
		$this->attributes['name'] = $value;
		$this->attributes['slug'] = TagService::normalize($value);
	}
}
