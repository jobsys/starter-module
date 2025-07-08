<?php

namespace Modules\Starter\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Modules\Starter\Entities\Tag;
use Modules\Starter\Entities\Tagged;
use Modules\Starter\Events\TagAdded;
use Modules\Starter\Events\TagRemoved;
use Modules\Starter\Services\TagService;

/**
 * @mixin Model
 * @mixin Builder
 *
 * @method static Builder withAllTags(array $tags)
 * @method static Builder withAnyTag(array $tags)
 * @method static Builder withoutTags(array $tags)
 *
 * @property Collection|Tagged[] tagged
 * @property Collection|Tag[] tags
 * @property string[] tag_names
 * @see https://github.com/rtconner/laravel-tagging/blob/master/src/Taggable.php
 */
trait Taggable
{
	/**
	 * Temp storage for auto tag
	 *
	 * @var mixed
	 */
	protected $autoTagValue;

	/**
	 * Track if auto tag has been manually set
	 *
	 * @var bool
	 */
	protected $autoTagSet = false;

	/**
	 * Boot the soft taggable trait for a model.
	 *
	 * @return void
	 */
	public static function bootTaggable(): void
	{
		if (static::untagOnDelete()) {
			static::deleting(function ($model) {
				$model->untag();
			});
		}

		static::saved(function ($model) {
			$model->autoTagPostSave();
		});
	}

	/**
	 * Return collection of tagged rows related to the tagged model
	 *
	 * @return MorphMany
	 */
	public function tagged(): MorphMany
	{
		return $this
			->morphMany(TagService::taggedModelString(), 'taggable')
			->with('tag');
	}

	/**
	 * Return collection of tags related to the tagged model
	 * TODO : I'm sure there is a faster way to build this, but
	 * If anyone knows how to do that, me love you long time.
	 *
	 * @return Collection|Tagged[]
	 */
	public function getTagsAttribute(): Collection|array
	{
		return $this->tagged->map(function (Tagged $item) {
			return $item->tag;
		});
	}

	/**
	 * Get the tag names via attribute, example $model->tag_names
	 */
	public function getTagNamesAttribute(): array
	{
		return $this->tagNames();
	}

	/**
	 * Perform the action of tagging the model with the given string
	 *
	 * @param array|string $tag_names
	 * @param $locale
	 * @return void
	 */
	public function addTags(array|string $tag_names, $locale): void
	{
		$tag_names = TagService::makeTagArray($tag_names);

		foreach ($tag_names as $tag_name) {
			$this->addSingleTag($tag_name, $locale);
		}
	}

	/**
	 * Perform the action of tagging the model with the given string
	 *
	 * @param array|string $tag_names
	 * @param string $locale
	 * @return void
	 */
	public function tag(array|string $tag_names, string $locale = 'zh_CN'): void
	{
		$this->addTags($tag_names, $locale);
	}

	/**
	 * Return array of the tag names related to the current model
	 */
	public function tagNames(): array
	{
		return $this->tagged->map(function ($item) {
			return $item->tag_name;
		})->toArray();
	}

	/**
	 * Return array of the tag slugs related to the current model
	 */
	public function tagSlugs(): array
	{
		return $this->tagged->map(function ($item) {
			return $item->tag_slug;
		})->toArray();
	}

	/**
	 * Remove the tag from this model
	 *
	 * @param array|string|null $tag_names (or null to remove all tags)
	 */
	public function untag(array|string $tag_names = null): void
	{
		if (is_null($tag_names)) {
			$tag_names = $this->tagNames();
		}

		$tag_names = TagService::makeTagArray($tag_names);

		foreach ($tag_names as $tag_name) {
			$this->removeSingleTag($tag_name);
		}

		if (static::shouldDeleteUnused()) {
			TagService::deleteUnusedTags();
		}
	}

	/**
	 * Replace the tags from this model
	 *
	 * @param array|string $tag_names
	 */
	public function retag(array|string $tag_names): void
	{
		$tag_names = TagService::makeTagArray($tag_names);
		$current_tag_names = $this->tagNames();

		$deletions = array_diff($current_tag_names, $tag_names);
		$additions = array_diff($tag_names, $current_tag_names);

		$this->untag($deletions);

		foreach ($additions as $tag_name) {
			$this->addSingleTag($tag_name);
		}
	}

	/**
	 * Filter model to subset with the given tags
	 *
	 * @param Builder $query
	 * @param array|string $tag_names
	 * @return Builder
	 */
	public function scopeWithAllTags(Builder $query, array|string $tag_names): Builder
	{
		if (!is_array($tag_names)) {
			$tag_names = func_get_args();
			array_shift($tag_names);
		}

		$tag_names = TagService::makeTagArray($tag_names);

		$className = $query->getModel()->getMorphClass();

		foreach ($tag_names as $tag_slug) {
			$model = TagService::taggedModelString();

			/** @var Tag $tags */
			$tags = $model::query()
				->where('tag_slug', TagService::normalize($tag_slug))
				->where('taggable_type', $className)
				->get()
				->pluck('taggable_id')
				->unique();

			$primaryKey = $this->getKeyName();
			$query->whereIn($this->getTable() . '.' . $primaryKey, $tags);
		}

		return $query;
	}

	/**
	 * Filter model to subset with the given tags
	 *
	 * @param Builder $query
	 * @param array|string $tag_names
	 * @return Builder
	 */
	public function scopeWithAnyTag(Builder $query, array|string $tag_names): Builder
	{
		$tags = $this->assembleTagsForScoping($query, $tag_names);

		return $query->whereIn($this->getTable() . '.' . $this->getKeyName(), $tags);
	}

	/**
	 * Filter model to subset without the given tags
	 *
	 * @param Builder $query
	 * @param array|string $tag_names
	 * @return Builder
	 */
	public function scopeWithoutTags(Builder $query, array|string $tag_names): Builder
	{
		$tags = $this->assembleTagsForScoping($query, $tag_names);

		return $query->whereNotIn($this->getTable() . '.' . $this->getKeyName(), $tags);
	}

	/**
	 * Adds a single tag
	 *
	 * @param string $tag_name
	 * @param string $locale
	 */
	private function addSingleTag(string $tag_name, string $locale = 'zh_CN'): void
	{
		$tag_name = trim($tag_name);

		if (strlen($tag_name) == 0) {
			return;
		}

		$tag_slug = TagService::normalize($tag_name);

		$previousCount = $this->tagged()->where('tag_slug', '=', $tag_slug)->take(1)->count();
		if ($previousCount >= 1) {
			return;
		}

		$model = TagService::taggedModelString();

		$tagged = new $model([
			'tag_name' => TagService::displayize($tag_name),
			'tag_slug' => $tag_slug,
			'locale' => $locale,
		]);

		$this->tagged()->save($tagged);

		TagService::incrementCount($tag_name, $tag_slug, 1, $locale);

		unset($this->relations['tagged']);

		event(new TagAdded($this, $tag_slug, $tagged));
	}

	/**
	 * Removes a single tag
	 *
	 * @param  $tag_name  string
	 */
	private function removeSingleTag(string $tag_name): void
	{
		$tag_name = trim($tag_name);

		$tag_slug = TagService::normalize($tag_name);

		if ($count = $this->tagged()->where('tag_slug', '=', $tag_slug)->delete()) {
			TagService::decrementCount($tag_slug, $count);
		}

		unset($this->relations['tagged']); // clear the "cache"

		event(new TagRemoved($this, $tag_slug));
	}

	/**
	 * Return an array of all the tags that are in use by this model
	 *
	 * @return Collection
	 */
	public static function existingTags(): Collection
	{
		$model = TagService::taggedModelString();

		return $model::query()
			->distinct()
			->join('tagging_tags', 'tag_slug', '=', 'tagging_tags.slug')
			->where('taggable_type', '=', (new static)->getMorphClass())
			->orderBy('tag_slug', 'ASC')
			->get(['tag_slug as slug', 'tag_name as name', 'tagging_tags.count as count']);
	}

	/**
	 * Return an array of all the tags that are in use by this model
	 *
	 * @param array $groups
	 * @return Collection
	 */
	public static function existingTagsInGroups($groups): Collection
	{
		$model = TagService::taggedModelString();

		return $model::query()
			->distinct()
			->join('tagging_tags', 'tag_slug', '=', 'tagging_tags.slug')
			->join('tagging_tag_groups', 'tag_group_id', '=', 'tagging_tag_groups.id')
			->where('taggable_type', '=', (new static)->getMorphClass())
			->whereIn('tagging_tag_groups.name', $groups)
			->orderBy('tag_slug', 'ASC')
			->get(['tag_slug as slug', 'tag_name as name', 'tagging_tags.count as count']);
	}

	/**
	 * When deleting a model, remove all the tags first
	 */
	public static function untagOnDelete(): bool
	{
		return false;
	}

	/**
	 * Delete tags that are not used anymore
	 *
	 * Auto-delete unused tags from the 'tags' database table (when they are used zero times)
	 */
	public static function shouldDeleteUnused(): bool
	{
		return false;
	}

	/**
	 * Set tag names to be set on save
	 *
	 * @param mixed $value Data for retag
	 */
	public function setTagNamesAttribute($value): void
	{
		$this->autoTagValue = $value;
		$this->autoTagSet = true;
	}

	/**
	 * AutoTag post-save hook
	 *
	 * Tags model based on data stored in tmp property, or untags if manually
	 * set to false value
	 */
	public function autoTagPostSave(): void
	{
		if ($this->autoTagSet) {
			if ($this->autoTagValue) {
				$this->retag($this->autoTagValue);
			} else {
				$this->untag();
			}
		}
	}

	private function assembleTagsForScoping($query, $tag_names)
	{
		if (!is_array($tag_names)) {
			$tag_names = func_get_args();
			array_shift($tag_names);
		}

		$tag_names = TagService::makeTagArray($tag_names);

		$normalizer = [TagService::class, 'normalize'];

		$tag_names = array_map($normalizer, $tag_names);
		$className = $query->getModel()->getMorphClass();

		$model = TagService::taggedModelString();

		$tags = $model::query()
			->whereIn('tag_slug', $tag_names)
			->where('taggable_type', $className)
			->get()
			->pluck('taggable_id')
			->unique();

		return $tags;
	}
}
