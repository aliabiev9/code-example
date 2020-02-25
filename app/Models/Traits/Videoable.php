<?php

namespace App\Models\Traits;

use App\Models\Video;

/**
 * Trait Videoable
 *
 *
 *
 * @package App\Models\Traits
 */
trait Videoable
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function videos()
    {
        return $this->morphMany(Video::class, 'videoable');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function video()
    {
        return $this->morphOne(Video::class, 'videoable');
    }

    /**
     * @param $data
     * @return mixed
     */
    public function saveVideo($data)
    {
        $video = Video::make([
            'path' => $data,
        ]);
        $this->videos()->save($video);

        $this->setRelation('video', $video);

        return $video;
    }

    public function updateVideo($request)
    {
        $video = Video::make(['path' => $request]);

        $this->videos()->save($video);

        $this->setRelation('video', $video);

        return $video;
    }
}
