<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\TrainingCategoryUserRequest;
use App\Http\Requests\User\UpdatePasswordRequest;
use App\Http\Requests\User\UserUpdateRequest;
use App\Http\Resources\Category\IndexCategoryResource;
use App\Http\Resources\Training\IndexAllTrainingForUserResource;
use App\Http\Resources\User\IndexUserResource;
use App\Models\TrainingCategory;
use App\Models\TrainingOrders;
use App\Models\Traits\Picturable;
use App\Models\User;


class PersonalAreaController extends Controller
{
    use Picturable;

    /**
     * PersonalAreaController constructor.
     */
    public function __construct()
    {
        $this->middleware('auth:jwt');
    }

    /**
     * @OA\Get (
     *     tags={"Personal Area"},
     *     path="/user",
     *     summary="Personal area user on gerasyanov.com",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *          response=200,
     *          description="Get user",
     *          @OA\JsonContent(type="object",
     *              @OA\Property(property="user", type="object",
     *                   @OA\Property(property="id", type="integer"),
     *                   @OA\Property(property="name", type="string"),
     *                   @OA\Property(property="email", type="string"),
     *                   @OA\Property(property="phone", type="string"),
     *                   @OA\Property(property="address", type="string"),
     *                   @OA\Property(property="date_of_birth", type="date"),
     *                   @OA\Property(property="gender", type="string"),
     *                   @OA\Property(property="picture", type="object"),
     *              ),
     *              @OA\Property(property="categories", type="object",
     *                   @OA\Property(property="id", type="integer"),
     *                   @OA\Property(property="name", type="string"),
     *                   @OA\Property(property="selected", type="boolean"),
     *              ),
     *               @OA\Property(property="subscription trainings", type="object",
     *                   @OA\Property(property="id", type="integer"),
     *                   @OA\Property(property="name", type="string"),
     *                   @OA\Property(property="picture", type="object"),
     *              ),
     *          ),
     *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=404, description="Not Found"),
     * ),
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        /** @var User $user */
        $user = $this->auth()->user();

        $allCategories = IndexCategoryResource::collection(TrainingCategory::getAllForUser($user));
        $trainingOrdersToUser = IndexAllTrainingForUserResource::collection(TrainingOrders::TrainingOrdersToUser($user)->get());

        return response()->json([
            'User' => IndexUserResource::make($user),
            'categories' => $allCategories,
            'subscriptionTrainings' => $trainingOrdersToUser->unique('training.name')->values()->all(),
        ]);
    }

    /**
     * @OA\Post (
     *     tags={"Personal Area"},
     *     path="/user/{id}",
     *     summary="Update user on gerasyanov.com",
     *     security={{"bearerAuth":{}}},
     *       @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="User id",
     *         required=true,
     *         @OA\Schema(
     *         type="integer",
     *         )
     *     ),
     *     @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  @OA\Property(
     *                      property="_method",
     *
     *                      default="put",
     *
     *                  ),
     *                  @OA\Property(
     *                      property="name",
     *                      type="string",
     *                      required={"true"},
     *                  ),
     *                  @OA\Property(
     *                      property="email",
     *                      type="email",
     *                      required={"true"},
     *                  ),
     *                  @OA\Property(
     *                      property="phone",
     *                      type="string",
     *                      required={"true"},
     *                  ),
     *                  @OA\Property(
     *                      property="address",
     *                      type="string",
     *                      required={"true"},
     *                  ),
     *                  @OA\Property(
     *                      property="date_of_birth",
     *                      type="date",
     *                      required={"true"},
     *                  ),
     *                  @OA\Property(
     *                      property="gender",
     *                      type="string",
     *                      required={"true"},
     *                  ),
     *                 @OA\Property(
     *                      property="picture",
     *                      type="file",
     *                      required={"true"},
     *                  ),
     *              )
     *          )
     *      ),
     *     @OA\Response(
     *          response=200,
     *          description="User update success",
     *       ),
     *       @OA\Response(response=401, description="Unauthorized"),
     *       @OA\Response(response=404, description="Not Found"),
     * )
     *
     * @param UserUpdateRequest $request
     * @param User $user
     * @return IndexUserResource
     */
    public function update(UserUpdateRequest $request, User $user)
    {
        $user = $this->auth()->user();
        $user->updateUser($request);
        if ($request->picture) {
            $user->removeImage();
            $user->saveImage($request->picture, 'Avatar');
        } else if ($request->picture64) {
            $user->removeImage();
            $user->saveImage64($request->picture64, 'Avatar');
        }

        return IndexUserResource::make($user);
    }


    /**
     *
     * @OA\Post (
     *     tags={"Personal Area"},
     *     path="/user/select-training-category",
     *     summary="Select or unselect the Training category for authorized user",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="training_category_id",
     *                     type="integer",
     *                     required={"true"},
     *                     description="Id training category chosen by user",
     *                 ),
     *                 @OA\Property(
     *                     property="selected",
     *                     type="boolean",
     *                     enum={1, 0},
     *                     default=1,
     *                     description="true = 1, false = 0",
     *                 ),
     *                 example={"training_category_id": 1, "selected": 1}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *          response=200,
     *          description="get result true",
     *          @OA\JsonContent(type="object",
     *              @OA\Items(type="object",
     *                  @OA\Property(property="status", type="boolean"),
     *              ),
     *          ),
     *       ),
     *       @OA\Response(response=401, description="Unauthorized"),
     *       @OA\Response(response=404, description="Not Found"),
     * )
     *
     *
     * @param TrainingCategoryUserRequest $request
     * @return mixed
     */
    public function setTrainingCategoryUserRelation(TrainingCategoryUserRequest $request)
    {
        /** @var User $user */
        $user = $this->auth()->user();

        $request->user_id = $user->id;

        /** @var TrainingCategory $category */
        $category = TrainingCategory::find($request->training_category_id);

        $result = $category->setSelectionForUser($request);

        return response()->json(['id' => $category->id, 'status' => $result]);
    }


    /**
     * @OA\Post (
     *     tags={"Personal Area"},
     *     path="/user/change_password",
     *     summary="Update user password on gerasyanov.com",
     *     security={{"bearerAuth":{}}},
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/x-www-form-urlencoded",
     *              @OA\Schema(
     *                  @OA\Property(
     *                      property="old_password",
     *                      type="string",
     *                      required={"true"},
     *                  ),
     *                  @OA\Property(
     *                      property="new_password",
     *                      type="string",
     *                      required={"true"},
     *                  ),
     *              )
     *          )
     *      ),
     *     @OA\Response(response=200, description="Update user password success" ),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=403, description="Старый пароль не верный"),
     * )
     *
     * @param UpdatePasswordRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function changePassword(UpdatePasswordRequest $request)
    {
        $user = $this->auth()->user();

        if (password_verify($request->old_password, $user->password)) {
            $user->updatePassword($request);
            return response()->json(['status' => 'ok']);
        } else {
            return response()->json(['error' => 'Старый пароль не верный'], 403);
        }
    }

}
