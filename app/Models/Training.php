<?php

namespace App\Models;

use App\Http\Requests\Training\TrainingUpdateRequest;
use App\Models\Traits\Picturable;
use App\Models\Traits\Reviewable;
use App\Models\Traits\Videoable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Class Training
 * @package App\Models
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string $description
 * @property string $additional_description
 * @property integer $date_start
 * @property string $place
 * @property string $category
 * @property string $group
 * @property string $promotional_price_name
 * @property string $promotional_price_description
 * @property string $promotional_price
 * @property-read \App\Models\Picture $picture
 * @property-read \App\Models\Video $video
 */
class Training extends Model
{
    use Picturable, Videoable, Reviewable, SoftDeletes;

    /** @var array $fillable */
    protected $fillable = ['name', 'slug', 'description', 'additional_description', 'date_start', 'place', 'promotional_price_name', 'promotional_price_description', 'promotional_price', 'training_group_id', 'training_category_id'];

    public static function boot()
    {
        parent::boot();
        static::saving(function ($model) {
            $model->slug = Str::slug($model->name);
        });
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    /**
     * Relation to the Training Category
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function category(): HasOne
    {
        return $this->hasOne(TrainingCategory::class, 'id', 'training_category_id');
    }

    /**
     * Relation to the Training Group
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function group(): HasOne
    {
        return $this->hasOne(TrainingGroup::class, 'id', 'training_group_id');
    }

    /**
     * Relation to the Training Plans
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function plans(): HasMany
    {
        return $this->hasMany(TrainingPlans::class, 'training_id', 'id');
    }

    /**
     * Method to get all Trainings grouped and sorted for specified User
     * @param int $userId
     * @return mixed
     */
    public static function allSortingAndGroupedTrainingsForUser(int $userId)
    {
        $allGroupTrainings = self::selectRaw('training_groups.name group_name, trainings.*')
            ->with(['picture', 'category'])
            ->Join('training_groups', 'training_groups.id', '=', 'trainings.training_group_id')
            ->leftJoin('training_categories', 'training_categories.id', '=', 'trainings.training_category_id')
            ->leftJoin('training_category_user', static function ($join) use ($userId) {
                $join->on('training_category_user.training_category_id', '=', 'training_categories.id');
                $join->on('training_category_user.user_id', '=', DB::raw("'" . $userId . "'"));
            })
            ->orderBy('training_groups.id')
            ->orderBy('training_category_user.user_id', 'DESC')
            ->orderBy('trainings.id')
            ->get();

        return $allGroupTrainings->mapToGroups(function ($item, $key) {
            return [$item['training_group_id'] => $item];
        });
    }

    /**
     * @param $data
     * @return Training
     */
    public function createTraining($data): Training
    {
        $training = $this->firstOrCreate($data->except('picture', 'video'));

        $training->saveVideo($data->video);
        $training->saveImage($data->picture, 'FullHD');

        return $training;
    }

    /**
     * @param TrainingUpdateRequest $request
     * @return bool
     */
    public function updateTraining(TrainingUpdateRequest $request)
    {
        return $this->update($request->all());
    }

    public function saveImageIfExist(Request $request, Training $training)
    {
        if ($request->picture && $request->picture !== 'undefined') {
            if ($training->picture){
                $training->picture->delete();
            }
            $training->saveImage($request->picture, 'FullHD');
        }
    }

    public function saveVideoIfExist(Request $request, Training $training)
    {
        if ($request->video && $request->video !== 'undefined') {
            if ($training->video) {
                $training->video->delete();
            }
            $training->updateVideo($request->video);
        }
    }

    public static function getTrainingsByDate (string $date)
    {
        return Training::with('group')
            ->where('date_start', 'LIKE', $date . '%')
            ->get();
    }
}
