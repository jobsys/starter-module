<?php

namespace Modules\Starter\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\SerializesModels;
use Modules\Starter\Traits\Taggable;

class TagRemoved
{
	use SerializesModels;

	/** @var Taggable|Model * */
	public $model;

	/*** @var string */
	public $tagSlug;

	/**
	 * Create a new event instance.
	 *
	 * @param Taggable|Model $model
	 * @param string $tagSlug
	 */
	public function __construct(Model|Taggable $model, string $tagSlug)
	{
		$this->model = $model;
		$this->tagSlug = $tagSlug;
	}
}
