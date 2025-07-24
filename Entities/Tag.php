<?php

namespace Modules\Starter\Entities;

use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Modules\Permission\Interfaces\WithCustomizeAuthorisation;
use Modules\Permission\Traits\Authorisations;
use Modules\Starter\Services\TagService;
use RuntimeException;
use Staudenmeir\EloquentJsonRelations\HasJsonRelationships;
use Staudenmeir\EloquentJsonRelations\Relations\BelongsToJson;

/**
 * @mixin Model
 * @mixin Builder
 *
 * @property string id
 * @property string name
 * @property string slug
 * @property bool suggest
 * @property string locale
 * @property int count
 * @property int tag_group_id
 * @property TagGroup group
 * @property string description
 *
 * @method static suggested()
 * @method static inGroup(string $group)
 *
 * @see https://github.com/rtconner/laravel-tagging
 */
class Tag extends BaseModel implements WithCustomizeAuthorisation
{
	use HasJsonRelationships, Authorisations;

	protected $model_name = '标签';

	protected $table = 'tagging_tags';

	protected $casts = [
		'pusher_ids' => 'array'
	];

	protected $accessors = [
		'created_at' => 'datetime'
	];

	/**
	 * {@inheritDoc}
	 */
	public function save(array $options = []): bool
	{
		if (strlen($this->name) < 1) {
			throw new RuntimeException('Cannot save a tag with an empty name');
		}

		if (!$this->slug) {
			$this->slug = TagService::normalize($this->name);
		}

		return parent::save($options);
	}

	/**
	 * Tag group setter
	 *
	 * @param string $group
	 * @return Tag
	 */
	public function setGroup(string $group): static
	{
		$model = TagService::tagGroupModelString();

		$tag_group = $model::query()
			->where('slug', TagService::normalize($group))
			->first();

		if (!$tag_group) {
			$tag_group = $model::query()->create([
				'slug' => TagService::normalize($group),
				'name' => $group
			]);
		}

		$this->group()->associate($tag_group);
		$this->save();

		return $this;
	}

	/**
	 * Tag group remove
	 *
	 * @return Tag
	 */
	public function removeGroup(): static
	{
		$this->group()->dissociate();
		$this->save();

		return $this;
	}

	/**
	 * Tag group helper function
	 *
	 * @param string $group_name
	 * @return bool
	 */
	public function isInGroup(string $group_name): bool
	{
		if ($this->group && ($this->group->slug == TagService::normalize($group_name))) {
			return true;
		}

		return false;
	}

	/**
	 * Tag group relationship
	 */
	public function group(): BelongsTo
	{
		return $this->belongsTo(TagService::tagGroupModelString(), 'tag_group_id');
	}

	/**
	 * Get suggested tags
	 */
	public function scopeSuggested($query)
	{
		return $query->where('suggest', true);
	}

	/**
	 * Get suggested tags
	 *
	 * @param Builder $query
	 * @param $group_name
	 * @return Builder
	 */
	public function scopeInGroup(Builder $query, $group_name): Builder
	{
		$group_slug = TagService::normalize($group_name);

		return $query->whereHas('group', function (Builder $query) use ($group_slug) {
			$query->where('slug', $group_slug);
		});
	}

	/**
	 * Set the name of the tag : $tag->name = 'myname';
	 */
	public function setNameAttribute(string $value): void
	{
		$this->attributes['name'] = TagService::displayize($value);
	}

	/**
	 * Look at the tags table and delete any tags that are no longer in use by any taggable database rows.
	 * Does not delete tags where 'suggest' value is true
	 *
	 * @return mixed
	 */
	public static function deleteUnused(): mixed
	{
		return (new static)->newQuery()
			->where('count', '=', 0)
			->where('suggest', false)
			->delete();
	}

	public function creator(): BelongsTo
	{
		return $this->belongsTo(User::class, 'creator_id', 'id');
	}

	public function pushers(): BelongsToJson
	{
		return $this->belongsToJson(User::class, 'pusher_ids');
	}

	/**
	 * #ServiceOnly
	 * @return MorphToMany
	 */
	public function students(): MorphToMany
	{
		return $this->morphedByMany(Student::class, 'taggable', Tagged::getTableName(), 'tag_slug', 'taggable_id', 'slug', 'id');
	}

	/**
	 * #ServiceOnly
	 * @return MorphToMany
	 */
	public function in_school_students(): MorphToMany
	{
		$in_school_year = get_in_school_year();
		return $this->morphedByMany(Student::class, 'taggable', Tagged::getTableName(), 'tag_slug', 'taggable_id', 'slug', 'id')->where('students.graduation_year', '>=', $in_school_year);
	}


	public function getCustomAuthorisationRule(Builder $query): Builder
	{
		return $query->where('creator_id', auth()->id())->orWhereJsonContains('pusher_ids', auth()->id());
	}
}
