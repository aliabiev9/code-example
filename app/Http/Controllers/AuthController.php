<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\AuthUserRequest;
use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Http\Requests\Auth\RegisterUserRequest;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Transformers\Auth\AuthUserTransformer;
use App\Http\Transformers\Auth\RegisterUserTransformer;
use App\Mail\ForgotPassword;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    /**
     * @OA\Post (
     *     tags={"Auth"},
     *     path="/auth/login/",
     *     summary="Login on gerasyanov.com",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="login",
     *                     type="string",
     *                     required={"true"},
     *                     default="admin@test.com"
     *                 ),
     *                 @OA\Property(
     *                     property="password",
     *                     type="string",
     *                     required={"true"},
     *                     default="123456789"
     *                 ),
     *                 example={"login": "admin@test.com", "password": "123456789"}
     *             )
     *         )
     *     ),
     *     @OA\Response(response="200", description="login OK")
     * )
     *
     * @param AuthUserRequest $request
     * @param AuthUserTransformer $transformer
     * @return \Illuminate\Http\JsonResponse
     *
     */

    public function login(AuthUserRequest $request, AuthUserTransformer $transformer)
    {
        if ($transformer->isEmail($request)) {
            $credentials = $transformer->getEmailCredentials($request);
        } else {
            $credentials = $transformer->getPhoneCredentials($request);
        };

        if (!$token = $this->auth()->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }


    /**
     * @OA\Post (
     *     tags={"Auth"},
     *     path="/auth/me/",
     *     summary="Me on gerasyanov.com",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *          response=200,
     *          description="Get user",
     *          @OA\JsonContent(type="object",
     *              @OA\Property(property="data", type="array",
     *                  @OA\Items(type="object",
     *                      @OA\Property(property="id", type="integer"),
     *                      @OA\Property(property="name", type="string"),
     *                      @OA\Property(property="email", type="string"),
     *                      @OA\Property(property="phone", type="string"),
     *                  ),
     *              ),
     *          ),
     *       ),
     *       @OA\Response(response=401, description="Unauthorized"),
     *       @OA\Response(response=404, description="Not Found"),
     * )
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        /** @var User $user */
        $user = auth()->user();

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
        ]);
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @OA\Post(
     *      tags={"Auth"},
     *      path="/auth/logout",
     *      summary="User logout",
     *      security={{"bearerAuth":{}}},
     *      @OA\Response(response="200", description="object with the property status")
     *  )
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        $this->auth()->logout();

        return response()->json(['status' => 'ok']);
    }

    /**
     * @OA\Post (
     *     tags={"Auth"},
     *     path="/auth/refresh/",
     *     summary="Refresh token on gerasyanov.com",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *          response=200,
     *          description="Refresh token user",
     *       ),
     *       @OA\Response(response=401, description="Unauthorized"),
     *       @OA\Response(response=404, description="Not Found"),
     * )
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken($this->auth()->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $this->auth()->factory()->getTTL() * 60
        ]);
    }

    /**
     *
     * @OA\Post(
     *      tags={"Auth"},
     *      path="/auth/registration",
     *      summary="New user registration on gerasyanov.com",
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
     *                      property="address",
     *                      type="string",
     *                     required={"true"},
     *                  ),
     *                  @OA\Property(
     *                      property="login",
     *                      type="string",
     *                     required={"true"},
     *                      description="Users email or phone number",
     *                  ),
     *                  @OA\Property(
     *                      property="password",
     *                      type="string",
     *                     required={"true"},
     *                  ),
     *                  example={"name":"User", "address":"Some user address 644", "login": 380501234887, "password": "123456789"}
     *              )
     *          )
     *      ),
     *      @OA\Response(response="200", description="New user object")
     *  )
     *
     * @param RegisterUserRequest $request
     * @param RegisterUserTransformer $transformer
     * @param AuthUserTransformer $login_transformer
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function registration(RegisterUserRequest $request, RegisterUserTransformer $transformer, AuthUserTransformer $login_transformer)
    {
        if ($transformer->isEmail($request)) {
            User::createUserByEmail($transformer->emailRegistration($request));

            $token = $this->auth()->attempt($login_transformer->getEmailCredentials($request));
            return $this->respondWithToken($token);
        }
        if ($transformer->isPhone($request)) {
            User::createUserByPhone($transformer->phoneRegistration($request));

            $token = $this->auth()->attempt($login_transformer->getPhoneCredentials($request));
            return $this->respondWithToken($token);
        }

        return response()->json(['error' => 'Используйте корректный Телефон или E-mail'], 400);
    }

    /**
     * @OA\Post (
     *     tags={"Auth"},
     *     path="/auth/reset-password",
     *     summary="Reset password on gerasyanov.com",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="email",
     *                     type="string",
     *                     required={"true"},
     *                     default="admin@test.com"
     *                 ),
     *                 example={"email": "admin@test.com"}
     *             )
     *         )
     *     ),
     *     @OA\Response(response="200", description="Email with reset password link was successfully sent")
     * )
     *
     * @param ForgotPasswordRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function forgotPasswordForMobileApp(ForgotPasswordRequest $request)
    {
        $user = User::getUserByEmail($request->email);

        if ($user !== null && $user->socialAccounts->isEmpty()) {
            try {
                $user->updateSecureCode();
                Mail::to($user->email)->send(new ForgotPassword($user));
            } catch (\Exception $exception) {
                return response()->json([
                    "error" => "Email wasn't send",
                    "message" => $exception->getMessage()
                ], 424);
            }
            return response()->json([
                "status" => "OK", "message" => "письмо с токеном сброса пароля успешно отправлено"
            ]);

        } elseif ($user !== null && $user->socialAccounts->isNotEmpty()) {
            return response()->json([
                "error" => "Пользователь с таким email зарегистрирован с помощью социальных сетей, выполните вход в приложение используя социальную сеть"
            ], 403);
        }

        return response()->json(['error' => 'Пользователь с таким адресом электронной почты не существует.'], 403);
    }

    /**
     * @param AuthUserRequest $request
     * @param AuthUserTransformer $transformer
     * @return \Illuminate\Http\JsonResponse
     */
    public function loginToAdminPanel(AuthUserRequest $request, AuthUserTransformer $transformer)
    {
        if (!($token = $this->auth()->attempt($transformer->getCredentials($request))) || !$this->auth()->user()->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        return $this->respondWithToken($token);
    }

    /**
     * @param ChangePasswordRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function forgotPassword(ChangePasswordRequest $request)
    {
        $user = User::whereSecureCode($request->code)->first();

        if ($user) {
            $user->update([
                'password' => bcrypt($request->new_password),
                'secure_code' => null
            ]);
            return response()->json(['status' => 'ok']);
        }
        return response()->json(['error' => 'Пользователь не найден'], 403);
    }

}
