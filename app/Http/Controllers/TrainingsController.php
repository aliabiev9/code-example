<?php

namespace App\Http\Controllers;

use App\Http\Resources\Group\IndexGroupResource;
use App\Http\Resources\Training\IndexAllTrainingResource;
use App\Http\Resources\Training\IndexTrainingResource;
use App\Http\Resources\Training\IndexTrainingWithoutPlansResource;
use App\Models\Training;
use App\Models\TrainingCategory;
use App\Models\TrainingGroup;
use App\Models\TrainingPlans;
use Illuminate\Support\Facades\Auth;


class TrainingsController extends Controller
{
    /**
     * @OA\Get(
     *     tags={"Trainings"},
     *     path="/trainings",
     *     summary="Get all trainings",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response="200", description="Get all trainings success"),
     *     @OA\Response(response="404", description="not found")
     * )
     *
     * @return mixed
     */
    public function index()
    {
        $allGroups = TrainingGroup::has('trainings')->where('affiliation', '=', 'trainings')->get();

        $allGroupedTrainings = Training::allSortingAndGroupedTrainingsForUser(Auth::user()->id);

        foreach ($allGroups as $group) {
            $group->trains_array = $allGroupedTrainings[$group->id] ?? collect(new Training());
        }

        return IndexGroupResource::collection($allGroups);
    }

    /**
     * @OA\Get(
     *     tags={"Trainings"},
     *     path="/trainings/{slug}",
     *     summary="Get one training",
     *     security={{"bearerAuth":{}}},
     *       @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         description="Training slug",
     *         required=true,
     *         @OA\Schema(
     *         type="string",
     *         )
     *     ),
     *     @OA\Response(response="200", description="Get one training success"),
     *     @OA\Response(response="404", description="not found")
     * )
     *
     * @param Training $training
     * @return mixed
     */
    public function show(Training $training)
    {
        $trainingPlansForTraining = TrainingPlans::where('training_id', $training->id)->first();
        if ($trainingPlansForTraining) {
            return IndexTrainingResource::make($training);
        }
        return IndexTrainingWithoutPlansResource::make($training);
    }

    /**
     * @OA\Get(
     *     tags={"Trainings"},
     *     path="/trainingsForCategory/{trainingCategory}",
     *     summary="Get all trainings for category",
     *     security={{"bearerAuth":{}}},
     *       @OA\Parameter(
     *         name="trainingCategory",
     *         in="path",
     *         description="Training category slug",
     *         required=true,
     *         @OA\Schema(
     *         type="string",
     *         )
     *     ),
     *     @OA\Response(response="200", description="Get all trainings success"),
     *     @OA\Response(response="404", description="not found")
     * )
     *
     * @param TrainingCategory $trainingCategory
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function showAllTrainingsForCategory($trainingCategory)
    {
        $trainingCategoryId = TrainingCategory::where('slug', $trainingCategory)->first();
        return IndexAllTrainingResource::collection(Training::where('training_category_id', $trainingCategoryId->id)->get());
    }


}
