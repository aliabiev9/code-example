<?php

namespace App\Http\Controllers;

use App\Http\Resources\Plan\IndexPlanResource;
use App\Models\Training;


class TrainingPlansController extends Controller
{
    /**
     * @OA\Get(
     *     tags={"Trainings"},
     *     path="/trainings/{slug}/plans",
     *     summary="Get all plans in training",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         description="Training slug",
     *         required=true,
     *         @OA\Schema(
     *         type="string",
     *         )
     *     ),
     *     @OA\Response(response="200", description="Get all plans in training success"),
     *     @OA\Response(response="404", description="not found")
     * )
     *
     * @param $training
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(Training $training){

        return IndexPlanResource::collection($training->plans);
    }

}
