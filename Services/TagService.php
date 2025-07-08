<?php

namespace Modules\Starter\Services;

use Log;
use Modules\Starter\Entities\Tag;
use Overtrue\Pinyin\Collection;

/**
 * @see https://github.com/rtconner/laravel-tagging/blob/master/src/TaggingUtility.php
 */
class TagService extends BaseService
{
	/**
	 * Utility functions to help with various tagging functionality.
	 * /**
	 * Converts input into array
	 *
	 * @param array|string $tag_names
	 * @return array
	 */
	public static function makeTagArray(array|string $tag_names): array
	{
		if (is_array($tag_names) && count($tag_names) == 1) {
			$tag_names = reset($tag_names);
		}

		if (is_string($tag_names)) {
			$tag_names = explode(',', $tag_names);
		} elseif (!is_array($tag_names)) {
			$tag_names = [null];
		}

		$tag_names = array_map('trim', $tag_names);

		return array_values($tag_names);
	}

	public static function displayize($string)
	{
		$displayer = '\Illuminate\Support\Str::title';
		return call_user_func($displayer, $string);
	}

	public static function normalize($string): string
	{
		$slug = mb_strlen($string) > 10 ? pinyin_abbr($string) : pinyin_permalink($string);
		if ($slug instanceof Collection) {
			$slug = $slug->join("");
		}
		return strtolower($slug);
	}

	/**
	 * Private! Please do not call this function directly, just let the Tag library use it.
	 * Increment count of tag by one. This function will create tag record if it does not exist.
	 *
	 * @param string $name
	 * @param string $slug
	 * @param int $count
	 * @param $locale
	 */
	public static function incrementCount(string $name, string $slug, int $count, $locale = null): void
	{
		if ($count <= 0) {
			return;
		}
		$model = static::tagModelString();

		/** @var Tag $model |$tag */
		$tag = $model::where('slug', '=', $slug)->first();

		if (!$tag) {
			$tag = new $model;
			$tag->creator_id = auth()->id() ?? null;
			$tag->name = $name;
			$tag->slug = $slug;
			$tag->suggest = false;
			$tag->locale = $locale;
			$tag->save();
		}

		$tag->count = $tag->count + $count;
		$tag->save();
	}

	/**
	 * Private! Please do not call this function directly, let the Tag library use it.
	 * Decrement count of tag by one. This function will create tag record if it does not exist.
	 */
	public static function decrementCount($slug, $count): void
	{
		if ($count <= 0) {
			return;
		}

		/** @var Tag $model */
		$model = static::tagModelString();

		$tag = $model::where('slug', '=', $slug)->first();

		if ($tag) {
			$tag->count = $tag->count - $count;
			if ($tag->count < 0) {
				$tag->count = 0;
				Log::warning("The '.$model.' count for `$tag->name` was a negative number. This probably means your data got corrupted. Please assess your code and report an issue if you find one.");
			}
			$tag->save();
		}
	}

	/**
	 * Look at the tags table and delete any tags that are no longer in use by any taggable database rows.
	 * Does not delete tags where 'suggest' is true
	 *
	 * @return int
	 */
	public static function deleteUnusedTags(): int
	{
		/** @var Tag $model */
		$model = static::tagModelString();

		return $model::deleteUnused();
	}

	/**
	 * @return string
	 */
	public static function tagModelString(): string
	{
		return '\Modules\Starter\Entities\Tag';
	}

	/**
	 * @return string
	 */
	public static function taggedModelString(): string
	{
		return '\Modules\Starter\Entities\Tagged';
	}

	/**
	 * @return string
	 */
	public static function tagGroupModelString(): string
	{
		return '\Modules\Starter\Entities\TagGroup';
	}
}
