<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Exceptions\CustomJsonResponse;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {

        $collection = User::query();
        $limit = @$request->has('limit') ? (int)$request->limit : 50;
        $page_no = @$request->has('page') ? $request->page : 1;
        $collection = UserResource::collection($collection->orderBy('created_at', 'DESC')->skip(($page_no - 1) * $limit)->take($limit)->get());
        return $this->paginate(
            $collection,
            User::query()->count(),
            $limit,
            ['all' => $request->query('all', false)],
            false
        );
        // return $collection;
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:60'],
            'email' => [
                'required', 'regex:/\S+@\S+\.\S+/',
                function ($attributes, $value, $fail) {
                    $user = User::where('email', '=', $value)->whereNull('deleted_at')->first();
                    if ($user !== null) {
                        $fail($attributes . ' is not unique.');
                    }
                },
            ],
            'mobile' => [
                'required',  'string', 'max:10',
                function ($attributes, $value, $fail) {
                    $user = User::where('mobile', '=', $value)->whereNull('deleted_at')->first();
                    if ($user !== null) {
                        $fail($attributes . ' is not unique.');
                    }
                },
            ],
            'password' => [
                'required',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#$%^&*])(?=.{6,})/',
            ],
        ]);
        if ($validator->fails()) {
            return CustomJsonResponse::validatorResponse(
                Response::HTTP_BAD_REQUEST,
                null,
                $validator->errors()
            );
        }
        $validated_data = $validator->validated();
        if (array_key_exists('password', $validated_data)) {
            $validated_data['password'] = bcrypt($validated_data['password']);
        }
        $validated_data['isActive'] = true;
        $user = User::create($validated_data);
        return CustomJsonResponse::response(
            Response::HTTP_OK,
            new UserResource($user),
            null,
            1
        );
    }
    public function show($id)
    {
        $user = User::getWithUUID($id);
        if ($user === null) {
            return CustomJsonResponse::response(
                Response::HTTP_NOT_FOUND,
                null,
                "No record was found with that 'id'",
                0
            );
        }
        return CustomJsonResponse::response(
            Response::HTTP_OK,
            new UserResource($user),
            null,
            1
        );
    }
    public function update(Request $request, $id)
    {
        if (!$user = User::getWithUUID($id)) {
            return CustomJsonResponse::response(
                Response::HTTP_NOT_FOUND,
                null,
                "No user was found with that 'id'",
                0
            );
        }
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:60'],
            'email' => [
                'max:150', 'regex:/\S+@\S+\.\S+/',
                Rule::unique('users')
                    ->ignore($user)->whereNull('deleted_at'),

            ],
            'mobile' => [
                'required',  'string', 'max:10',
                Rule::unique('users')
                    ->ignore($user)->whereNull('deleted_at'),
            ],
            // 'password' => [
            //     'required',
            //     'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#$%^&*])(?=.{6,})/',
            // ],
        ]);
        if ($validator->fails()) {
            return CustomJsonResponse::validatorResponse(
                Response::HTTP_BAD_REQUEST,
                null,
                $validator->errors()
            );
        }
        $validated_data = $validator->validated();
        $validated_data['updated_at'] = now();
        $user->update($validated_data);
        return CustomJsonResponse::response(
            Response::HTTP_OK,
            new UserResource($user),
            null,
            1
        );
    }
    public function destroy($id)
    {
        if (!$user = User::getWithUUID($id)) {
            return CustomJsonResponse::response(
                Response::HTTP_NOT_FOUND,
                null,
                "No Customer was found with that 'id'",
                0
            );
        }
        try {
            $user->delete();
        } catch (\Throwable $th) {
            return CustomJsonResponse::response(
                Response::HTTP_NOT_FOUND,
                null,
                $th->getMessage(),
                1
            );
        }

        return CustomJsonResponse::response(
            Response::HTTP_OK,
            null,
            "Record deleted successfully",
            1
        );
    }
}
