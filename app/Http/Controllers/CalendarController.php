<?php

namespace App\Http\Controllers;

use App\Http\Resources\Calendar\IndexFestivalCalendarResoursce;
use App\Http\Resources\Calendar\IndexTourCalendarResoursce;
use App\Http\Resources\Calendar\IndexTrainingCalendarResoursce;
use App\Models\Festival;
use App\Models\Tour;
use App\Models\Training;
use Carbon\Carbon;
use Illuminate\Http\Request;
use mysql_xdevapi\Exception;

class CalendarController extends Controller
{
    /**
     * @OA\Get(
     *     tags={"Calendar"},
     *     path="/calendar?date={date}",
     *     summary="Get calendar",
     *     security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *         name="date",
     *         in="path",
     *         description="date calendar (format Y-m)",
     *         required=true,
     *         example="2020-03",
     *         @OA\Schema(
     *         type="string",
     *         )
     *     ),
     *     @OA\Response(response="200", description="Get calendar success"),
     *     @OA\Response(response="404", description="not found")
     * )
     *
     * @param Request $request
     * @return array
     */
    public function index(Request $request)
    {
        $date = $request->get('date');

        $tours = IndexTourCalendarResoursce::collection(Tour::getToursByDate($date));
        $trainings = IndexTrainingCalendarResoursce::collection(Training::getTrainingsByDate($date));
        $festivals = IndexFestivalCalendarResoursce::collection(Festival::getFestivalsByDate($date));
        $calendar = collect($trainings)->merge($tours)->merge($festivals);

        return response()->json([
            'month' => $date,
            'items' => $calendar->sortBy('date_start')->values()->all()
        ]);
    }

    /**
     * @OA\Get(
     *     tags={"Calendar"},
     *     path="/time_now",
     *     summary="Get actual time in ms from 1970",
     *     security={{"bearerAuth":{}}},

     *     @OA\Response(response="200", description="Get actual time success"),
     *     @OA\Response(response="404", description="not found")
     * )
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function timeNow()
    {
        return response()->json(['time' => time()]);
    }
}
