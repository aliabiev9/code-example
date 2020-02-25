<?php

namespace App\Http\Controllers;

use App\Http\Requests\Training\RegistrationVipTrainingRequest;
use App\Models\VipTraining;

class VipTrainingController extends Controller
{
    /**
     * @OA\Post(
     *      tags={"Trainings"},
     *      path="/vipRegistration",
     *      summary="User registration on VIP training",
     *      security={{"bearerAuth":{}}},
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/x-www-form-urlencoded",
     *              @OA\Schema(
     *                  @OA\Property(
     *                      property="name",
     *                      type="string",
     *                      required={"true"},
     *                  ),
     *                  @OA\Property(
     *                      property="phone",
     *                      type="string",
     *                      required={"true"},
     *                  ),
     *                  @OA\Property(
     *                      property="date",
     *                      type="date",
     *                      required={"true"},
     *                  ),
     *                  example={"name":"User Name", "phone":"380501234567", "date":"2020-01-30"}
     *              )
     *          )
     *      ),
     *      @OA\Response(response="200", description="status : ok"),
     *      @OA\Response(response="404", description="not found"),
     *  )
     *
     *
     * @param RegistrationVipTrainingRequest $request
     * @param VipTraining $vipTraining
     * @return \Illuminate\Http\JsonResponse
     */
    public function vipRegistration(RegistrationVipTrainingRequest $request, VipTraining $vipTraining)
    {
        if ($vipTraining->vipRegistrationExist($request->all())) {
            return response()->json(['Error' => 'registration processing'], 418);
        }

        if ($vipTraining->saveVipRegistration($request->all())) {
            return response()->json(['status' => 'ok']);
        }
        return response()->json(['Error' => 'registration to VIP training failed'], 500);
    }
}
