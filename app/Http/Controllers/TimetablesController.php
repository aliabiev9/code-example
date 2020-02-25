<?php

namespace App\Http\Controllers;

use App\Http\Resources\Category\IndexTimetableCategoryResource;
use App\Http\Resources\Festival\IndexAllFestivalsResource;
use App\Http\Resources\Timetable\IndexTimetableResource;
use App\Http\Resources\Tour\IndexAllTourResource;
use App\Models\Festival;
use App\Models\Tour;
use App\Models\TrainingCategory;
use App\Models\TrainingGroup;

class TimetablesController extends Controller
{
    /**
     * @OA\Get(
     *     tags={"Timetable"},
     *     path="/timetable",
     *     summary="Get index timetable",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response="200", description="Get index timetabl success"),
     *     @OA\Response(response="404", description="not found")
     * )
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index()
    {
        return IndexTimetableResource::collection(TrainingGroup::select(['name', 'slug'])->get());
    }

    /**
     * @OA\Get(
     *     tags={"Timetable"},
     *     path="/timetable/{slug}",
     *     summary="Show timetable item",
     *     security={{"bearerAuth":{}}},
     *       @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         description="Timetable slug",
     *         required=true,
     *         @OA\Schema(
     *         type="string",
     *         )
     *     ),
     *     @OA\Response(response="200", description="Show timetable item success"),
     *     @OA\Response(response="404", description="not found")
     * )
     *
     * @param string $slug
     * @return array|\Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function showTimetable(string $slug)
    {
        if ($slug === 'yoga-tury') {
            return IndexAllTourResource::collection(Tour::all()->sortBy('date_start'));
        } elseif ($slug === 'festivali') {
            return IndexAllFestivalsResource::collection(Festival::all()->sortBy('date_start'));
        } else {

            if ($group = TrainingGroup::where('slug', '=', $slug)->first()) {
                $trainingCategories = TrainingCategory::getCategoriesWithTrainingsFilteredByGroup($group);

                return IndexTimetableCategoryResource::collection($trainingCategories);
            }
            return response()->json(['error'=>'Wrong parameters'], 404);
        }
    }
}
