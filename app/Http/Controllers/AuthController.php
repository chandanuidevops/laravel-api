<?php

namespace App\Http\Controllers;

use App\Exceptions\CustomJsonResponse;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Vendor;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function __construct()
    {
        // $this->middleware('auth:api', ['except' => ['login']]);
        $this->middleware('jwt', ['except' => ['login']]);
        $this->middleware('set_default_guard');
    }
    public function login(Request $request)
    {

        $credentials = request(['email', 'password']);
        $request = request();
        $user = null;
        if (!$request->has(['email', 'password'])) {
            return CustomJsonResponse::response(
                Response::HTTP_NOT_FOUND,
                null,
                "Bad request",
                0
            );
        }
        if ($request->path() === 'api/v1/auth/vendors/login') {
            Config::set('auth.providers.users.model', Vendor::class);
            if (!$token = auth('vendors-api')->attempt($credentials, ['exp' => Carbon::maxValue()->timestamp])) {

                return CustomJsonResponse::response(
                    Response::HTTP_UNAUTHORIZED,
                    null,
                    "Unauthorized",
                    0
                );
            }
            $user = auth('vendors-api')->user();
        } else {
            if (!$token = auth()->attempt($credentials)) {
                return CustomJsonResponse::response(
                    Response::HTTP_UNAUTHORIZED,
                    null,
                    "Unauthorized",
                    0
                );
            }
            $user = auth()->user();
        }





        $token = JWTAuth::customClaims(['exp' => Carbon::now()->addYears(2)->timestamp])
            ->fromUser($user);
        // check user is active or not
        // if ($user && !(bool) $user->isActive) {
        //     return CustomJsonResponse::response(
        //         Response::HTTP_UNAUTHORIZED,
        //         null,
        //         "Your account is not Verified, Please contact administrator!",
        //         0
        //     );
        // }

        if ($user && !$user->isActive) {
            return CustomJsonResponse::response(
                Response::HTTP_UNAUTHORIZED,
                null,
                "Your account is Suspended, Please contact administrator!",
                0
            );
        }

        return $this->respondWithToken($token);
    }
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
           
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
        ]);
    }
}
