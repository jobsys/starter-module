<?php

namespace Modules\Starter\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\SerializesModels;
use Modules\Starter\Entities\Tagged;
use Modules\Starter\Traits\Taggable;

class TagAdded
{
	use SerializesModels;

	/** @var Taggable|Model * */
	public $model;

	/** @var string */
	public $tagSlug;

	/** @var Tagged */
	public $tagged;

	/**
	 * Create a new event instance.
	 *
	 * @param Model|Taggable $model
	 * @param string $tagSlug
	 * @param Tagged $tagged
	 */
	public function __construct(Model|Taggable $model, string $tagSlug, Tagged $tagged)
	{
		$this->model = $model;
		$this->tagSlug = $tagSlug;
		$this->tagged = $tagged;
	}
}
