<?php

namespace App\Models;

use App\Http\Requests\Training\TrainingGroupRequest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * Class TrainingGroup
 * @package App\Models
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string $description
 * @property string $affiliation
 * @property-read \App\Models\TrainingGroup $trainings
 */
class TrainingGroup extends Model
{
    /** @var array $fillable */
    protected $fillable = [
        'name', 'description', 'slug', 'affiliation'
    ];

    public static function boot()
    {
        parent::boot();
        static::saving(function ($model) {
            $model->slug = Str::slug($model->name);
        });
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function trainings(): HasMany
    {
        return $this->hasMany(Training::class);
    }

    /**
     * Get training of the Training Group, ordered by start date
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function trainingsGroupedByCategory()
    {
        return $this
            ->trainings()
            ->orderBy('date_start')
            ->get()
            ->mapToGroups(function ($item, $key) {
                return [$item['training_category_id'] => $item];
            });
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function createTrainingGroup(array $data)
    {
        return $this->firstOrCreate($data);
    }

    /**
     * @param TrainingGroupRequest $request
     * @return bool
     */
    public function updateTrainingGroup(TrainingGroupRequest $request)
    {
        return $this->update($request->all());
    }
}
