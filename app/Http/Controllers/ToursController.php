<?php

namespace App\Http\Controllers;

use App\Http\Resources\Tour\IndexAllYearTourResource;
use App\Http\Resources\Tour\IndexTourResource;
use App\Models\Tour;

class ToursController extends Controller
{
    /**
     * @OA\Get(
     *     tags={"Tours"},
     *     path="/tours",
     *     summary="Get all tours group by year",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response="200", description="Get all tours success"),
     *     @OA\Response(response="404", description="not found")
     * )
     *
     * @return mixed
     */
    public function index()
    {
        return IndexAllYearTourResource::collection(Tour::get(['year'])->unique('year')->sortBy('year'));
    }

    /**
     * @OA\Get(
     *     tags={"Tours"},
     *     path="/tours/{slug}",
     *     summary="Get one tour",
     *     security={{"bearerAuth":{}}},
     *       @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         description="Tour slug",
     *         required=true,
     *         @OA\Schema(
     *         type="string",
     *         )
     *     ),
     *     @OA\Response(response="200", description="Get one tour success"),
     *     @OA\Response(response="404", description="not found")
     * )
     *
     * @param Tour $tour
     * @return IndexTourResource
     */
    public function show(Tour $tour)
    {
        return IndexTourResource::make($tour);
    }


}
