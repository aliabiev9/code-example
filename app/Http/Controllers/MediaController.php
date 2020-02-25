<?php

namespace App\Http\Controllers;

use App\Http\Resources\Media\PhotoMediaResource;
use App\Http\Resources\Media\VideoMediaResource;
use App\Models\MediaAssets;

/**
 * Class MediaController
 * @package App\Http\Controllers\
 */
class MediaController extends Controller
{
    /**
     * Method to get photo Media
     *
     * @OA\Get(
     *     tags={"Media"},
     *     path="/media/photo",
     *     summary="Get photo Media",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response="200", description="Get Photo Media collection"),
     *     @OA\Response(response="404", description="not found")
     * )
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function photoMedia()
    {
        return PhotoMediaResource::collection( MediaAssets::getPhotoMedia() );
    }

    /**
     * Method to get video Media
     *
     * @OA\Get(
     *     tags={"Media"},
     *     path="/media/video",
     *     summary="Get video Media",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response="200", description="Get Video Media collection"),
     *     @OA\Response(response="404", description="not found")
     * )
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function videoMedia()
    {
        return VideoMediaResource::collection( MediaAssets::getVideoMedia() );
    }
}
