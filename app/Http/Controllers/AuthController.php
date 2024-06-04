<?php

namespace App\Http\Controllers;

use App\Exceptions\CustomJsonResponse;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt',['except'=>['login']] );
        $this->middleware('set_default_guard');
    }
    public function login(Request $request)
    {
      ;
        if (Request()->path() === 'api/v1/auth/user/login') {
            Config::set('auth.providers.users.model', User::class);

           Config::set('JWT.ttl', null);
            if ($request->has(['email', 'password'])) {

                $user = User::query()->where('email', $request['email'])->first();

                if (empty($user)) {

                    return CustomJsonResponse::response(
                        Response::HTTP_UNAUTHORIZED,
                        null,
                        "Unauthorized",
                        0
                    );
                }

                if ($token = auth('user-api')->login($user)) {
                    return CustomJsonResponse::response(
                        Response::HTTP_UNAUTHORIZED,
                        null,
                        "Unauthorized",
                        0
                    );
                }
            } else {
                return CustomJsonResponse::response(
                    Response::HTTP_NOT_FOUND,
                    null,
                    "Bad request",
                    0
                );
            }
        }
    }
}
