<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Training\TrainingRequest;
use App\Http\Requests\Training\TrainingUpdateRequest;
use App\Http\Resources\Training\IndexTrainingResourceForAdminPanel;
use App\Models\Training;

class TrainingsController extends Controller
{
    /**
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function index()
    {
        return Training::orderBy('created_at', 'desc')->with('picture', 'category', 'group')->paginate(15);
    }

    /**
     * @param TrainingRequest $request
     * @param Training $training
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(TrainingRequest $request, Training $training)
    {
        $training->createTraining($request);

        return response()->json(['status' => 'ok']);
    }

    /**
     * @param Training $training
     * @return IndexTrainingResourceForAdminPanel
     */
    public function edit(Training $training)
    {
        return IndexTrainingResourceForAdminPanel::make($training);
    }

    /**
     * @param TrainingUpdateRequest $request
     * @param Training $training
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function update(TrainingUpdateRequest $request, Training $training)
    {
        $training->updateTraining($request);
        $training->saveImageIfExist($request, $training);
        $training->saveVideoIfExist($request, $training);

        return response()->json(['status' => 'ok']);
    }

    /**
     * @param Training $training
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function destroy(Training $training)
    {
        $training->delete();

        return response()->json(['status' => 'ok']);
    }

    /**
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function trashedTrainings()
    {
        return Training::onlyTrashed()->orderBy('created_at', 'desc')->with('picture', 'category', 'group')->paginate(15);
    }

    /**
     * @param string $slug
     * @return \Illuminate\Http\JsonResponse
     */
    public function restoreTrashedTraining(string $slug)
    {
        if (Training::withTrashed()->where('slug', $slug)->restore()) {
            return response()->json(['status' => 'ok']);
        }
        return response()->json(['Error' => 'Training not restored'], 403);
    }

    /**
     * @param string $slug
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteTrashedTraining(string $slug)
    {
        if (Training::withTrashed()->where('slug', $slug)->forceDelete()) {
            return response()->json(['status' => 'ok']);
        }
        return response()->json(['Error' => 'Training not deleted'], 403);
    }
}
