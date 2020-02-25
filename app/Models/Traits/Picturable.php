<?php

namespace App\Models\Traits;

use App\Models\Picture;
use App\Services\Image\ImageProcessor;
use Illuminate\Http\UploadedFile;

/**
 * Trait Picturable
 * @package App\Models\Traits
 */
trait Picturable
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function pictures()
    {
        return $this->morphMany(Picture::class, 'picturable');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function picture()
    {
        return $this->morphOne(Picture::class, 'picturable');
    }

    /**
     * @param UploadedFile $uploaded
     * @param string $option
     * @return mixed
     */
    public function saveImage(UploadedFile $uploaded, $option = 'FullHD')
    {
        $pathInfo = $this->getImageProcessor()->saveImage($uploaded, $option);

        $picture = Picture::make([
            'name' => $uploaded->getClientOriginalName(),
            'path' => $pathInfo['image'],
            'thumbnail' => $pathInfo['thumbnail'],
        ]);

        $this->pictures()->save($picture);

        $this->setRelation('picture', $picture);

        return $picture;
    }

    /**
     * @param string $uploaded
     * @param string $option
     * @return mixed
     */
    public function saveImage64(string $uploaded, $option = 'Avatar')
    {
        $pathInfo = $this->getImageProcessor()->saveImage64($uploaded, $option);

        $picture = Picture::make([
            'name' =>  md5(now()),
            'path' => $pathInfo['image'],
            'thumbnail' => $pathInfo['thumbnail'],
        ]);

        $this->pictures()->save($picture);

        $this->setRelation('picture', $picture);

        return $picture;
    }

    public function removeImage()
    {
        if ($this->picture) {
            $this->picture->delete();
            $this->unsetRelation('picture');
        }
    }

    /**
     * @return ImageProcessor
     */
    private function getImageProcessor()
    {
        return app(ImageProcessor::class);
    }
}
