<?php

namespace App\Models;

use App\Http\Requests\Tour\TourUpdateRequest;
use App\Models\Traits\Picturable;
use App\Models\Traits\Reviewable;
use App\Models\Traits\Videoable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Class Tour
 * @package App\Models
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string $description
 * @property string $service
 * @property string $place
 * @property integer $price
 * @property integer $date_start
 * @property integer $date_ending
 * @property integer $duration
 * @property integer $year
 * @property string $tour_link
 */
class Tour extends Model
{
    use Picturable, Videoable, Reviewable, SoftDeletes;

    public static function boot()
    {
        parent::boot();
        static::saving(function ($model) {
            $model->slug = Str::slug($model->name);
            $model->duration = (strtotime($model->date_ending) - strtotime($model->date_start)) / (60 * 60 * 24);
            $model->year = date("Y", strtotime($model->date_start));
        });
    }

    protected $fillable = ['name', 'slug', 'description', 'place', 'price', 'date_start', 'date_ending', 'service', 'duration', 'year', 'tour_link'];

    public function getRouteKeyName()
    {
        return 'slug';
    }

    /**
     * @param $data
     * @return Tour
     */
    public function createTour($data): Tour
    {
        $tour = $this->firstOrCreate($data->except('picture', 'video'));

        $tour->saveImage($data->picture, 'FullHD');

        return $tour;
    }

    /**
     * @param TourUpdateRequest $request
     */
    public function updateTour(TourUpdateRequest $request)
    {
        $this->update($request->all());
    }

    public function saveImageIfExist(Request $request, Tour $tour)
    {
        if ($request->picture && $request->picture !== 'undefined') {
            if ($tour->picture) {
                $tour->picture->delete();
            }
            $tour->saveImage($request->picture, 'FullHD');
        }
    }

    public function saveVideoIfExist(Request $request, Tour $tour)
    {
        if ($request->video && $request->video !== 'undefined') {
            if ($tour->video) {
                $tour->video->delete();
            }
            $tour->updateVideo($request->video);
        }
    }

    public static function getToursByDate (string $date)
    {
        return Tour::orWhere('date_ending', 'LIKE', $date . '%')
            ->orWhere('date_start', 'LIKE', $date . '%')
            ->distinct()
            ->get();
    }
}
