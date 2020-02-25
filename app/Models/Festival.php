<?php

namespace App\Models;

use App\Http\Requests\Festival\FestivalUpdateRequest;
use App\Models\Traits\Picturable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Class Festival
 * @package App\Models
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string $description
 * @property string $place
 * @property string $link
 * @property integer $date_start
 * @property integer $date_ending
 * @property-read \App\Models\Picture $picture
 */
class Festival extends Model
{
    use Picturable;

    public static function boot()
    {
        parent::boot();
        static::saving(function ($model) {
            $model->slug = Str::slug($model->name);
        });
    }

    /**
     * @var array
     */
    protected $fillable = ['name', 'slug', 'description', 'place', 'link', 'date_start', 'date_ending'];

    public function getRouteKeyName()
    {
        return 'slug';
    }

    /**
     * @param $data
     * @return mixed
     */
    public function createFestival($data)
    {
        $festival = $this->firstOrCreate($data->except('picture'));

        $festival->saveImage($data->picture, 'FullHD');

        return $festival;
    }

    /**
     * @param FestivalUpdateRequest $request
     * @return bool
     */
    public function updateFestival(FestivalUpdateRequest $request)
    {
        return $this->update($request->all());
    }

    public function saveImageIfExist(Request $request, Festival $festival)
    {
        if ($request->picture && $request->picture !== 'undefined') {
            if ($festival->picture){
                $festival->picture->delete();
            }
            $festival->saveImage($request->picture, 'FullHD');
        }
    }

    public static function getFestivalsByDate (string $date)
    {
        return Festival::orWhere('date_ending', 'LIKE', $date . '%')
            ->orWhere('date_start', 'LIKE', $date . '%')
            ->distinct()
            ->get();
    }
}
