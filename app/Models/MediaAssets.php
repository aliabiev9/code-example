<?php

namespace App\Models;

use App\Http\Requests\Media\MediaAssetsUpdateRequest;
use App\Models\Traits\Picturable;
use App\Models\Traits\Videoable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;

/**
 * Class MediaAssets
 *
 * Model to implement media content of web resource
 *
 * @package App\Models
 */
class MediaAssets extends Model
{
    use SoftDeletes, Picturable, Videoable;

    /** @var array $fillable */
    protected $fillable = [
        'title', 'description'
    ];

    /**
     * Get Photo (picture) Media
     * @return mixed
     */
    public static function getPhotoMedia()
    {
        return self::whereHas('pictures')->get();
    }

    /**
     * Get Video Media
     * @return mixed
     */
    public static function getVideoMedia()
    {
        return self::whereHas('video')->get();
    }

    /**
     * @param Request $data
     * @return mixed
     */
    public function createMediaAssets(Request $data)
    {
        $mediaAssets = $this->firstOrCreate($data->except('pictures', 'video'));

        if (isset($data->video)) {
            $mediaAssets->saveVideo($data->video);
        }

        if (isset($data->pictures)) {
            foreach ($data->pictures as $picture) {
                $mediaAssets->saveImage($picture, 'FullHD');
            }
        }

        return $mediaAssets;
    }

    /**
     * @param MediaAssetsUpdateRequest $request
     * @return bool
     */
    public function updateMediaAssets(MediaAssetsUpdateRequest $request)
    {
        return $this->update($request->all());
    }

    public function saveImageIfExist(Request $request, MediaAssets $media_asset)
    {
        if ($request->pictures && $request->pictures !== 'undefined') {
            foreach ($request->pictures as $picture) {

                $media_asset->saveImage($picture, 'FullHD');
            }
        }
    }

    public function saveVideoIfExist(Request $request, MediaAssets $media_asset)
    {
        if ($request->video && $request->video !== 'undefined') {
            if ($media_asset->video) {
                $media_asset->video->delete();
            }
            $media_asset->updateVideo($request->video);
        }
    }
}
