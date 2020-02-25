<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Media\MediaAssetsRequest;
use App\Http\Requests\Media\MediaAssetsUpdateRequest;
use App\Models\MediaAssets;
use App\Services\Image\ImageProcessor;

class MediaAssetsController extends Controller
{
    /**
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function index()
    {
        return MediaAssets::orderBy('created_at', 'desc')->with('pictures')->whereHas('pictures')->paginate(15);
    }

    /**
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function indexVideo()
    {
        return MediaAssets::orderBy('created_at', 'desc')->with('video')->whereHas('video')->paginate(15);
    }

    /**
     * @param MediaAssetsRequest $request
     * @param MediaAssets $media_asset
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(MediaAssetsRequest $request, MediaAssets $media_asset)
    {
        $media_asset->createMediaAssets($request);

        return response()->json(['status' => 'ok']);
    }

    /**
     * @param MediaAssets $media_asset
     * @return MediaAssets
     */
    public function edit(MediaAssets $media_asset)
    {
        return $media_asset;
    }

    /**
     * @param MediaAssetsUpdateRequest $request
     * @param MediaAssets $media_asset
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(MediaAssetsUpdateRequest $request, MediaAssets $media_asset)
    {
        $media_asset->updateMediaAssets($request);

        $media_asset->saveImageIfExist($request, $media_asset);
        $media_asset->saveVideoIfExist($request, $media_asset);

        return response()->json(['status' => 'ok']);
    }

    /**
     * @param MediaAssets $media_asset
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function destroy(MediaAssets $media_asset)
    {
        $media_asset->delete();

        return response()->json(['status' => 'ok']);
    }

    /**
     * @param MediaAssets $media_asset
     * @param $id
     * @param ImageProcessor $imageProcessor
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteImage(MediaAssets $media_asset, $id, ImageProcessor $imageProcessor)
    {
        $picture = $media_asset->pictures->where('id', $id)->first();

        if ($picture) {
            $imageProcessor->deleteImageArray([$picture->path, $picture->thumbnail]);
            $picture->delete();

            if ($imageProcessor->errors_log) {
                return response()->json($imageProcessor->errors_log);
            } else {
                return response()->json(['status' => 'ok']);
            }

        } else {
            return response()->json(['error' => 'this picture id not found']);
        }
    }
}
