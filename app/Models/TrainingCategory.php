<?php

namespace App\Models;

use App\Http\Requests\Training\TrainingCategoryRequest;
use App\Http\Requests\User\TrainingCategoryUserRequest;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * Class TrainingCategory
 *
 * class to implement the Training Categories functionality
 *
 * @package App\Models
 * @property int $id
 * @property string $name
 */
class TrainingCategory extends Model
{
    /** @var array $fillable */
    protected $fillable = ['name', 'slug'];

    public static function boot()
    {
        parent::boot();
        static::saving(function ($model) {
            $model->slug = Str::slug($model->name);
        });
    }


    /**
     * Relation to the users selected this category
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'training_category_user',
            'training_category_id', 'user_id');
    }

    /**
     * @return HasMany
     */
    public function trainings(): HasMany
    {
        return $this->hasMany(Training::class);
    }

    /**
     * Method to set/unset categories selection for User
     * @param TrainingCategoryUserRequest $request
     * @return boolean
     */
    public function setSelectionForUser(TrainingCategoryUserRequest $request): bool
    {
        if ($request->selected) {

            if (null === $this->users()->find($request->user_id)) {
                $this->users()->attach($request->user_id);
                return true;
            }

            return false;
        }

        return $this->users()->detach($request->user_id) > 0;
    }

    /**
     * Get all categories with the additional data
     *
     * @param User|null $user
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public static function getAllForUser(User $user): Collection
    {
        $categoriesIdsArray = [];

        if ($usersCategoriesSelected = $user->trainingCategories) {
            $categoriesIdsArray = $usersCategoriesSelected->pluck('id')->toArray();
        }

        $categories = self::all();

        foreach ($categories as $category) {
            if (in_array($category->id, $categoriesIdsArray, true)) {
                $category->selected = true;
            } else {
                $category->selected = false;
            }
        }

        return $categories;
    }

    /**
     * Get Training Categories with Trainings, filtered by Training Group, ordered by Training start date
     * @param TrainingGroup $group
     * @return TrainingCategory[]|Collection
     */
    public static function getCategoriesWithTrainingsFilteredByGroup(?TrainingGroup $group)
    {
        $trainingsGroupedByCategories = $group->trainingsGroupedByCategory();

        $trainingCategories = self::all();

        foreach ($trainingCategories as $key => $category) {
            if ($trainingArray = $trainingsGroupedByCategories[$category->id] ?? false) {
                $category->trainings_array = $trainingsGroupedByCategories[$category->id];
            } else {
                $trainingCategories->forget($key);
            }
        }

        return $trainingCategories;
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function createTrainingCategory(array $data)
    {
        return $this->firstOrCreate($data);
    }

    /**
     * @param TrainingCategoryRequest $request
     * @return bool
     */
    public function updateTrainingCategory(TrainingCategoryRequest $request)
    {
        return $this->update($request->all());
    }

}
