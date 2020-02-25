<?php

namespace App\Services\Image;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Intervention\Image\ImageManager;
use PHPUnit\Util\Filesystem;

class ImageProcessor
{
    /**
     * @var ImageConfiguration
     */
    protected $configuration;

    /**
     * @var ImageManager
     */
    protected $image;

    /**
     * @var Filesystem|FilesystemAdapter
     */
    protected $storage;

    public $errors_log = [];

    /**
     * ImageProcessorV2 constructor.
     * @param ImageConfiguration $configuration
     * @param ImageManager $image
     * @param FilesystemManager $fs
     */
    public function __construct(ImageConfiguration $configuration, ImageManager $image, FilesystemManager $fs)
    {
        $this->configuration = $configuration;
        $this->image = $image;
        $this->storage = $fs->disk('public');
    }

    protected function resize(string $source, string $destination)
    {
        return $this->image->make($source)
            ->fit($this->configuration->getThumbnailWidth(), $this->configuration->getThumbnailHeight())
            ->save($destination);
    }

    protected function resizeTo($source, $destination)
    {
        try {
            return $this->image->make($source)
                ->resize($this->configuration->getPictureWidth(), null, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                })
                ->save($destination);
        } catch (\Exception $exception) {
            Log::debug($exception->getMessage());
            return false;
        }
    }

    /**
     * @param UploadedFile $file
     * @param string $option
     * @return array
     */
    public function saveImage(UploadedFile $file, $option = 'FullHD')
    {
        $pathInfo = $this->configuration->makePath($file->getPathname(), $file->guessExtension());
        $this->storage->makeDirectory($pathInfo['path']);

        switch ($option) {
            case 'FullHD':
                $this->resizeTo($file->getPathname(), $this->storage->path($pathInfo['imagePath']));
                $this->resize($file->getPathname(), $this->storage->path($pathInfo['thumbnailPath']));
                break;
            case 'Avatar':
                $this->resize($file->getPathname(), $this->storage->path($pathInfo['imagePath']));
                break;
        }

        return [
            'image' => $pathInfo['imagePath'],
            'thumbnail' => $pathInfo['thumbnailPath'],
        ];
    }

    /**
     * @param string $image64
     * @param string $option
     * @return array
     */
    public function saveImage64(string $image64, $option = 'FullHD')
    {

        $image64 = str_replace('data:image/jpeg;base64,', '', $image64);
        $image64 = str_replace(' ', '+', $image64);
        $imageName = md5(now()).'.jpeg';
        $this->storage->put('base64/'.$imageName, base64_decode($image64));
        $pathInfo = $this->configuration->makePath($this->storage->path('base64/'.$imageName),'jpeg');

        $this->storage->makeDirectory($pathInfo['path']);

        switch ($option) {
            case 'FullHD':
                $this->resizeTo($this->storage->path('base64/'.$imageName), $this->storage->path($pathInfo['imagePath']));
                $this->resize($this->storage->path('base64/'.$imageName), $this->storage->path($pathInfo['thumbnailPath']));
                break;
            case 'Avatar':
                $this->resize($this->storage->path('base64/'.$imageName), $this->storage->path($pathInfo['imagePath']));
                break;
        }
        $this->storage->delete('base64/'.$imageName);
        return [
            'image' => $pathInfo['imagePath'],
            'thumbnail' => $pathInfo['thumbnailPath'],
        ];
    }


    public function deleteImage(string $path)
    {
        if (!$this->storage->delete($path)) {
            Log::error('picture not deleted: ' . $path);
            array_push($this->errors_log, 'picture not deleted:  ' . $path);
        }
    }

    public function deleteImageArray(array $path_array)
    {
        foreach ($path_array as $path) {
            $this->deleteImage($path);
        }
    }
}
