<?php

namespace Modules\Starter\Entities;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Modules\Starter\Services\TagService;

/**
 * @mixin Model
 * @mixin Builder
 *
 * @property int id
 * @property string taggable_id
 * @property string taggable_type
 * @property string tag_name
 * @property string tag_slug
 * @property Tag tag
 */
class Tagged extends BaseModel
{

	protected $model_name = '标签关联';

	protected $table = 'tagging_tagged';

	public $timestamps = false;

	protected $fillable = ['tag_name', 'tag_slug'];

	public function __construct(array $attributes = [])
	{
		parent::__construct($attributes);
	}

	/**
	 * Morph to the tag
	 *
	 * @return MorphTo
	 */
	public function taggable(): MorphTo
	{
		return $this->morphTo();
	}

	/**
	 * Get instance of tag linked to the tagged value
	 *
	 * @return BelongsTo
	 */
	public function tag(): BelongsTo
	{
		$model = TagService::tagModelString();

		return $this->belongsTo($model, 'tag_slug', 'slug');
	}
}
