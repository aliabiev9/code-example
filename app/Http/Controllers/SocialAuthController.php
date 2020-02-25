<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\Social\SocialAccountService;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

/**
 * Class SocialAuthController
 * @package App\Http\Controllers
 */
class SocialAuthController extends Controller
{
    /**
     * Request after login from Mobile app
     *
     * Routes will have shape
     * /social-auth/twitter
     * /social-auth/facebook
     * /social-auth/{provider}
     *
     *
     * @OA\Post(
     *      tags={"Auth"},
     *      path="/auth/social-auth/{provider}",
     *      summary="New user registration on gerasyanov.com via Social Media",
     *     @OA\Parameter(
     *         name="provider",
     *         in="path",
     *         description="Social media provider name",
     *         required=true,
     *         @OA\Schema(
     *         type="string",
     *         )
     *     ),
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/x-www-form-urlencoded",
     *              @OA\Schema(
     *                  @OA\Property(
     *                      property="user_id",
     *                      type="string",
     *                      required={"true"},
     *                  ),
     *                  @OA\Property(
     *                      property="email",
     *                      type="string",
     *                     required={"true"},
     *                  ),
     *                  @OA\Property(
     *                      property="name",
     *                      type="string",
     *                     required={"true"},
     *                  ),
     *                  @OA\Property(
     *                      property="address",
     *                      type="string",
     *                  ),
     *                  example={"user_id":"Userh9ahds9v098dh98ag", "name":"User", "address":"Some user address 644", "email": "380501234887@exmpl.com"}
     *              )
     *          )
     *      ),
     *      @OA\Response(response="200", description="user is authenticated on gerasyanov.com"),
     *      @OA\Response(
     *          response=500,
     *          description="Internal server error"
     *      )
     *  )
     *
     * @param Request $request
     * @param SocialAccountService $service
     * @param string $provider  -  provider's of social auth functionality name (facebook, twitter, pinterest ... )
     * @return \Illuminate\Http\JsonResponse
     */
    public function requestFromMobileApp( Request $request, SocialAccountService $service, $provider )
    {
        // Mobile app resend the social response (users object)
        //$user = $service->createOrGetUser( Socialite::driver($provider)->user(), $provider );
        $user = $service->createOrGetUserWithMobileApp( (object)$request->all(), $provider );

        // the request body need to be the same the Social callback request

        $credentials = [
            'phone'     => '',
            'password'  => '',
        ];

        if ( $user ) {

            if ( $user->email !== '' ) {

                $credentials = [
                    'email'     => $user->email,
                    'password'  => User::getDefaultSocialMediaPassword(),
                ];
            }elseif ( $user->phone !== '' ) {

                $credentials = [
                    'phone'     => $user->phone,
                    'password'  => User::getDefaultSocialMediaPassword(),
                ];
            }
        }

        if ( !$token = $this->auth()->attempt($credentials) ) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // return jwt token
        return $this->respondWithToken($token);
    }



    /**
     * To redirect to some Social Provider (FB, VK, Google), with the web interface
     *
     * @param $provider
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function redirectToProvider($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    /**
     * To handle the callback from Social provider, for authentication by web interface
     *
     * @param SocialAccountService $service
     * @param $provider
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handleProviderCallback( SocialAccountService $service, $provider )
    {
        $user = $service->createOrGetUser( Socialite::driver($provider)->user(), $provider );

        auth()->login($user);

        return redirect()->route('home');
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
            'access_token'  => $token,
            'token_type'    => 'bearer',
            'expires_in'    => $this->auth()->factory()->getTTL() * 60
        ]);
    }
}
